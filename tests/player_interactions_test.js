'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

let activeDocument;

class FakeClassList {
    constructor(value) {
        this.values = new Set((value || '').split(/\s+/).filter(Boolean));
    }

    add(value) { this.values.add(value); }
    remove(value) { this.values.delete(value); }
    contains(value) { return this.values.has(value); }
    toggle(value, force) {
        const enabled = force === undefined ? !this.contains(value) : Boolean(force);
        if (enabled) this.add(value);
        else this.remove(value);
        return enabled;
    }
}

class FakeElement {
    constructor(id, className, attributes) {
        this.id = id || '';
        this.classList = new FakeClassList(className);
        this.attributes = Object.assign({}, attributes);
        this.children = [];
        this.listeners = {};
        this.parentElement = null;
        this.hidden = false;
        this.tabIndex = Object.prototype.hasOwnProperty.call(this.attributes, 'tabindex')
            ? Number(this.attributes.tabindex)
            : 0;
        this.value = '';
        this.href = '';
        this._textContent = '';
    }

    get textContent() {
        return this._textContent + this.children.map((child) => child.textContent).join('');
    }

    set textContent(value) {
        this._textContent = String(value);
        this.children = [];
    }

    addEventListener(type, callback) { this.listeners[type] = callback; }

    appendChild(child) {
        child.parentElement = this;
        this.children.push(child);
        return child;
    }

    querySelectorAll(selector) {
        if (selector === '[data-player-suggestion]') {
            return this.children.filter((child) => child.getAttribute('data-player-suggestion') !== null);
        }
        return [];
    }

    getAttribute(name) {
        if (name === 'id') return this.id;
        return Object.prototype.hasOwnProperty.call(this.attributes, name) ? this.attributes[name] : null;
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
        if (name === 'id') this.id = String(value);
    }

    removeAttribute(name) { delete this.attributes[name]; }

    contains(candidate) {
        let element = candidate;
        while (element) {
            if (element === this) return true;
            element = element.parentElement;
        }
        return false;
    }

    focus() {
        if (activeDocument) activeDocument.activeElement = this;
    }
}

function createHarness() {
    const documentEvents = {};
    const tabs = [
        ['win_rate', 'Win Rate'],
        ['wins', 'Wins'],
        ['frags', 'Frags'],
        ['avg_damage', 'Average Damage'],
        ['avg_xp', 'Average XP'],
    ].map(([key]) => new FakeElement('', 'player-ranking-tab', {
        'data-player-ranking-tab': key,
        'aria-selected': key === 'win_rate' ? 'true' : 'false',
        tabindex: key === 'win_rate' ? '0' : '-1',
    }));
    const panels = tabs.map((tab) => new FakeElement('', 'player-ranking-panel', {
        'data-player-ranking-panel': tab.getAttribute('data-player-ranking-tab'),
    }));
    panels.forEach((panel, index) => { panel.hidden = index !== 0; });

    const activeTab = tabs[0];
    const nextTab = tabs[1];
    const field = new FakeElement('', 'player-search-field');
    const input = new FakeElement('playerUsername', 'form-control', {
        'data-player-input': '',
        'aria-expanded': 'false',
    });
    const suggestions = new FakeElement('playerSuggestions', 'player-suggestions', {
        'data-player-suggestions': '',
    });
    const outside = new FakeElement('', 'outside');
    field.appendChild(input);
    field.appendChild(suggestions);
    input.parentElement = field;
    suggestions.parentElement = field;

    const documentElement = new FakeElement('', '', { lang: 'ru' });
    const document = {
        documentElement,
        activeElement: input,
        addEventListener(type, callback) { documentEvents[type] = callback; },
        createElement() { return new FakeElement(); },
        querySelector(selector) {
            if (selector === '[data-player-input]') return input;
            if (selector === '[data-player-suggestions]') return suggestions;
            return null;
        },
        querySelectorAll(selector) {
            if (selector === '[data-player-ranking-tab]') return tabs;
            if (selector === '[data-player-ranking-panel]') return panels;
            return [];
        },
    };
    activeDocument = document;

    const pendingTimers = [];
    const clock = {
        delays: [],
        setTimeout(callback, delay) {
            const timer = { callback, delay, cancelled: false };
            pendingTimers.push(timer);
            this.delays.push(delay);
            return timer;
        },
        clearTimeout(timer) {
            if (timer) timer.cancelled = true;
        },
        runNext() {
            while (pendingTimers.length) {
                const timer = pendingTimers.shift();
                if (timer.cancelled) continue;
                timer.callback();
                return;
            }
            throw new Error('No pending timer to run');
        },
    };

    const fetchCalls = [];
    const window = {
        location: { href: '' },
        setTimeout: clock.setTimeout.bind(clock),
        clearTimeout: clock.clearTimeout.bind(clock),
        fetch(url, options) {
            let resolvePromise;
            let rejectPromise;
            const promise = new Promise((resolve, reject) => {
                resolvePromise = resolve;
                rejectPromise = reject;
            });
            fetchCalls.push({
                url,
                options,
                resolve(payload) {
                    resolvePromise({
                        ok: true,
                        json() { return Promise.resolve(payload); },
                    });
                },
                reject(error) { rejectPromise(error || new Error('network failure')); },
            });
            return promise;
        },
    };

    return {
        activeTab,
        nextTab,
        input,
        suggestions,
        tabs,
        tabEvents: activeTab.listeners,
        tabEventsByIndex: tabs.map((tab) => tab.listeners),
        inputEvents: input.listeners,
        fetchCalls,
        resolveFetch(index, payload) { fetchCalls[index].resolve(payload); },
        rejectFetch(index) { fetchCalls[index].reject(new Error('network failure')); },
        clock,
        documentElement,
        documentEvents,
        panels,
        outside,
        window,
        document,
        flushMicrotasks: async function () {
            await Promise.resolve();
            await Promise.resolve();
            await Promise.resolve();
        },
    };
}

function loadPlayers(harness) {
    const source = fs.readFileSync(path.join(__dirname, '..', 'js', 'players.js'), 'utf8');
    vm.runInNewContext(source, {
        window: harness.window,
        document: harness.document,
        console,
        Promise,
        Array,
        String,
        Number,
        Error,
        encodeURIComponent,
    });
    harness.window.OrionPlayers.initialize();
}

(async function () {
    const harness = createHarness();
    loadPlayers(harness);

    const {
        activeTab,
        nextTab,
        input,
        suggestions,
        tabs,
        tabEvents,
        tabEventsByIndex,
        inputEvents,
        fetchCalls,
        resolveFetch,
        rejectFetch,
        clock,
        documentElement,
        documentEvents,
        panels,
        outside,
        window,
        flushMicrotasks,
    } = harness;

    function assertRovingTabIndex(selectedIndex, message) {
        assert.deepStrictEqual(
            tabs.map((tab) => tab.tabIndex),
            tabs.map((tab, tabIndex) => tabIndex === selectedIndex ? 0 : -1),
            message
        );
    }

    assert.strictEqual(activeTab.getAttribute('aria-selected'), 'true', 'Win Rate tab should start selected');
    assertRovingTabIndex(0, 'only the first ranking tab should be keyboard reachable initially');
    assert.strictEqual(panels[0].hidden, false, 'selected ranking panel should be visible');
    assert.strictEqual(panels[1].hidden, true, 'unselected ranking panel should be hidden');

    tabEvents.keydown({ key: 'ArrowRight', preventDefault() {} });
    assert.strictEqual(activeTab.getAttribute('aria-selected'), 'false', 'ArrowRight should move to the next ranking');
    assert.strictEqual(nextTab.getAttribute('aria-selected'), 'true', 'next ranking should become selected');
    assertRovingTabIndex(1, 'ArrowRight should move the roving tabIndex to the second tab');
    assert.strictEqual(panels[1].hidden, false, 'next ranking panel should become visible');

    tabEventsByIndex[1].keydown({ key: 'ArrowRight', preventDefault() {} });
    assert.strictEqual(tabs[2].getAttribute('aria-selected'), 'true', 'ArrowRight should select the third ranking');
    assert.strictEqual(panels[2].hidden, false, 'third ranking panel should become visible');
    assertRovingTabIndex(2, 'third ranking should receive the roving tabIndex');

    tabEventsByIndex[2].keydown({ key: 'Home', preventDefault() {} });
    assertRovingTabIndex(0, 'Home should return the roving tabIndex to the first tab');
    tabEventsByIndex[0].keydown({ key: 'End', preventDefault() {} });
    assert.strictEqual(tabs[4].getAttribute('aria-selected'), 'true', 'End should select the fifth ranking');
    assertRovingTabIndex(4, 'End should move the roving tabIndex to the fifth tab');
    tabEventsByIndex[4].keydown({ key: 'ArrowLeft', preventDefault() {} });
    assert.strictEqual(tabs[3].getAttribute('aria-selected'), 'true', 'ArrowLeft should move to the previous ranking');
    assertRovingTabIndex(3, 'ArrowLeft should move the roving tabIndex to the fourth tab');

    input.value = 'a b';
    inputEvents.input({ target: input });
    clock.runNext();
    assert.strictEqual(clock.delays[0], 180, 'autocomplete should use the exact 180ms debounce');
    assert.strictEqual(fetchCalls[0].url, 'players.php?ajax=player-suggestions&q=a%20b', 'autocomplete URL must encode the prefix');

    resolveFetch(0, { suggestions: [
        { username: 'Alpha', total_battles: 12 },
        { username: 'Abel', total_battles: 4 },
    ] });
    await flushMicrotasks();
    assert.strictEqual(suggestions.hidden, false, 'matching suggestions should open the listbox');
    assert.strictEqual(input.getAttribute('aria-expanded'), 'true', 'open listbox must be exposed to accessibility APIs');
    assert.ok(suggestions.textContent.includes('боёв'), 'Russian suggestion metadata should be localized');
    assert.strictEqual(suggestions.querySelectorAll('[data-player-suggestion]').length, 2, 'rendered suggestions should be queryable as options');

    inputEvents.keydown({ key: 'ArrowDown', preventDefault() {} });
    inputEvents.keydown({ key: 'ArrowUp', preventDefault() {} });
    const initialLinks = suggestions.querySelectorAll('[data-player-suggestion]');
    assert.strictEqual(initialLinks[0].getAttribute('aria-selected'), 'false', 'ArrowUp should move away from the first suggestion');
    assert.strictEqual(initialLinks[1].getAttribute('aria-selected'), 'true', 'ArrowUp should wrap to the previous suggestion');

    inputEvents.keydown({ key: 'Escape' });
    assert.strictEqual(suggestions.hidden, true, 'Escape should close the listbox');
    assert.strictEqual(input.getAttribute('aria-expanded'), 'false', 'Escape should update the listbox ARIA state');

    input.value = 'a';
    const fetchCountBeforeShortInput = fetchCalls.length;
    inputEvents.input({ target: input });
    assert.strictEqual(suggestions.hidden, true, 'one-character input should hide the listbox');
    assert.strictEqual(fetchCalls.length, fetchCountBeforeShortInput, 'one-character input must not call fetch');

    input.value = 'ac';
    inputEvents.input({ target: input });
    clock.runNext();
    resolveFetch(1, { suggestions: [{ username: 'Active', total_battles: 20 }] });
    await flushMicrotasks();
    resolveFetch(0, { suggestions: [{ username: 'Stale', total_battles: 99 }] });
    await flushMicrotasks();
    assert.ok(suggestions.textContent.includes('Active'), 'newer autocomplete results should render');
    assert.ok(!suggestions.textContent.includes('Stale'), 'stale responses must not replace newer results');

    inputEvents.keydown({ key: 'ArrowDown', preventDefault() {} });
    const selectedLink = suggestions.querySelectorAll('[data-player-suggestion]')[0];
    assert.strictEqual(selectedLink.getAttribute('aria-selected'), 'true', 'ArrowDown should select the first suggestion');
    assert.strictEqual(input.getAttribute('aria-activedescendant'), selectedLink.id, 'selected suggestion should be exposed to accessibility APIs');
    inputEvents.keydown({ key: 'Enter', preventDefault() { this.prevented = true; } });
    assert.strictEqual(window.location.href, 'players.php?username=Active', 'Enter should open the selected profile');

    documentElement.setAttribute('lang', 'uk');
    input.value = 'ук';
    inputEvents.input({ target: input });
    clock.runNext();
    resolveFetch(2, { suggestions: [{ username: 'Гравець', total_battles: 3 }] });
    await flushMicrotasks();
    assert.ok(suggestions.textContent.includes('боїв'), 'Ukrainian suggestion metadata should be localized');
    assert.strictEqual(
        suggestions.querySelectorAll('[data-player-suggestion]')[0].href,
        'players.php?username=' + encodeURIComponent('Гравець'),
        'profile links must encode the username'
    );

    input.value = 'no';
    inputEvents.input({ target: input });
    clock.runNext();
    rejectFetch(3);
    await flushMicrotasks();
    assert.strictEqual(suggestions.hidden, true, 'a rejected fetch should hide the listbox');
    assert.strictEqual(suggestions.textContent, '', 'a rejected fetch should not expose an exception message in the DOM');

    input.value = 'cl';
    inputEvents.input({ target: input });
    clock.runNext();
    resolveFetch(4, { suggestions: [{ username: 'ClickAway', total_battles: 0 }] });
    await flushMicrotasks();

    inputEvents.keydown({ key: 'ArrowDown', preventDefault() {} });
    const previousSuggestionHref = suggestions.querySelectorAll('[data-player-suggestion]')[0].href;
    const locationBeforeQueryChange = window.location.href;
    input.value = 'old';
    inputEvents.input({ target: input });
    assert.strictEqual(suggestions.hidden, true, 'a valid query change should hide the previous suggestion list immediately');
    assert.strictEqual(suggestions.textContent, '', 'a valid query change should clear previous suggestions immediately');
    inputEvents.keydown({ key: 'Enter', preventDefault() { this.prevented = true; } });
    assert.strictEqual(window.location.href, locationBeforeQueryChange, 'Enter during debounce must not navigate to the previous query result');
    assert.strictEqual(previousSuggestionHref, 'players.php?username=ClickAway', 'the regression must start with a selected previous result');
    clock.runNext();
    assert.strictEqual(fetchCalls[5].url, 'players.php?ajax=player-suggestions&q=old', 'the replacement query should still run after its debounce');

    documentEvents.click({ target: outside });
    assert.strictEqual(suggestions.hidden, true, 'clicking outside the search field should close the listbox');

    input.value = 'new';
    inputEvents.input({ target: input });
    clock.runNext();
    resolveFetch(6, { suggestions: [{ username: 'Newer', total_battles: 2 }] });
    await flushMicrotasks();
    resolveFetch(5, { suggestions: [{ username: 'Older', total_battles: 1 }] });
    await flushMicrotasks();
    assert.ok(suggestions.textContent.includes('Newer'), 'out-of-order newer autocomplete results should render');
    assert.ok(!suggestions.textContent.includes('Older'), 'serial stale-response guard should reject older results');

    const fetchCountBeforeEarlyEscape = fetchCalls.length;
    input.value = 'escape-before';
    inputEvents.input({ target: input });
    inputEvents.keydown({ key: 'Escape' });
    assert.throws(() => clock.runNext(), /No pending timer/, 'Escape should cancel a queued debounce timer');
    assert.strictEqual(fetchCalls.length, fetchCountBeforeEarlyEscape, 'Escape before debounce should not start a fetch');
    assert.strictEqual(suggestions.hidden, true, 'Escape before debounce should keep the listbox hidden');
    assert.strictEqual(suggestions.textContent, '', 'Escape before debounce should leave no rendered text');

    const fetchCountBeforeEarlyOutsideClick = fetchCalls.length;
    input.value = 'outside-before';
    inputEvents.input({ target: input });
    documentEvents.click({ target: outside });
    assert.throws(() => clock.runNext(), /No pending timer/, 'outside click should cancel a queued debounce timer');
    assert.strictEqual(fetchCalls.length, fetchCountBeforeEarlyOutsideClick, 'outside click before debounce should not start a fetch');
    assert.strictEqual(suggestions.hidden, true, 'outside click before debounce should keep the listbox hidden');
    assert.strictEqual(suggestions.textContent, '', 'outside click before debounce should leave no rendered text');

    input.value = 'escape';
    inputEvents.input({ target: input });
    clock.runNext();
    inputEvents.keydown({ key: 'Escape' });
    resolveFetch(7, { suggestions: [{ username: 'LateEscape', total_battles: 1 }] });
    await flushMicrotasks();
    assert.strictEqual(suggestions.hidden, true, 'Escape should keep a late response from reopening the listbox');
    assert.strictEqual(input.getAttribute('aria-expanded'), 'false', 'Escape should keep the listbox collapsed after a late response');
    assert.strictEqual(suggestions.textContent, '', 'Escape should keep late results out of the DOM');

    input.value = 'outside';
    inputEvents.input({ target: input });
    clock.runNext();
    documentEvents.click({ target: outside });
    resolveFetch(8, { suggestions: [{ username: 'LateOutside', total_battles: 1 }] });
    await flushMicrotasks();
    assert.strictEqual(suggestions.hidden, true, 'clicking outside should keep a late response from reopening the listbox');
    assert.strictEqual(input.getAttribute('aria-expanded'), 'false', 'outside click should keep the listbox collapsed after a late response');
    assert.strictEqual(suggestions.textContent, '', 'outside click should keep late results out of the DOM');

    input.value = 'empty';
    inputEvents.input({ target: input });
    clock.runNext();
    resolveFetch(9, { suggestions: [] });
    await flushMicrotasks();
    assert.strictEqual(suggestions.hidden, true, 'an empty suggestion response should keep the listbox hidden');
    assert.strictEqual(input.getAttribute('aria-expanded'), 'false', 'an empty suggestion response should collapse the listbox');
    assert.strictEqual(suggestions.textContent, '', 'an empty suggestion response should leave no rendered text');

    process.stdout.write('Player interaction DOM checks passed.\n');
}()).catch((error) => {
    console.error(error && error.stack ? error.stack : error);
    process.exitCode = 1;
});
