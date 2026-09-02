'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

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

let fakeDocument;

class FakeElement {
    constructor(id, className, attributes) {
        this.id = id || '';
        this.classList = new FakeClassList(className);
        this.attributes = Object.assign({}, attributes);
        this.parentElement = null;
        this.hidden = false;
        this.tabIndex = 0;
        this.textContent = '';
        this.style = { setProperty() {} };
    }

    getAttribute(name) {
        if (name === 'id') return this.id;
        return Object.prototype.hasOwnProperty.call(this.attributes, name) ? this.attributes[name] : null;
    }

    setAttribute(name, value) { this.attributes[name] = String(value); }
    matches(selector) {
        if (selector.startsWith('.')) return this.classList.contains(selector.slice(1));
        const attribute = selector.match(/^\[([^=\]]+)(?:="([^"]*)")?\]$/);
        if (!attribute) return false;
        if (!Object.prototype.hasOwnProperty.call(this.attributes, attribute[1])) return false;
        return attribute[2] === undefined || this.attributes[attribute[1]] === attribute[2];
    }

    closest(selector) {
        const selectors = selector.split(',').map((part) => part.trim());
        let element = this;
        while (element) {
            if (selectors.some((part) => element.matches(part))) return element;
            element = element.parentElement;
        }
        return null;
    }

    contains(candidate) {
        let element = candidate;
        while (element) {
            if (element === this) return true;
            element = element.parentElement;
        }
        return false;
    }

    focus() { fakeDocument.activeElement = this; }
    querySelector(selector) {
        if (selector === '.modal-dialog') return this.dialog || null;
        return null;
    }

    querySelectorAll() { return this.focusable || []; }
}

function eventFor(target, extra) {
    return Object.assign({
        target,
        prevented: false,
        preventDefault() { this.prevented = true; },
    }, extra || {});
}

function createHarness(options) {
    options = options || {};
    const listeners = {};
    const elements = new Map();
    const body = new FakeElement('', '', { 'data-show-popup': options.autoPopup ? '1' : '0' });
    body.appendChild = function () {};
    body.removeChild = function () {};

    const nav = new FakeElement('siteNav', 'site-nav');
    const navToggle = new FakeElement('', 'nav-toggle', {
        'data-nav-toggle': '',
        'aria-controls': 'siteNav',
        'aria-expanded': 'false',
    });
    const opener = new FakeElement('', 'btn', { 'data-modal-open': 'donateModal' });
    const modal = new FakeElement('donateModal', 'donate-modal', { 'aria-hidden': 'true' });
    const dialog = new FakeElement('', 'modal-dialog', { tabindex: '-1' });
    const closer = new FakeElement('', 'modal-close', { 'data-modal-close': '' });
    const modalAction = new FakeElement('', 'btn');
    const backdrop = new FakeElement('', 'donate-modal-bg', { 'data-modal-close': '' });
    const outside = new FakeElement('', 'outside-control');
    dialog.parentElement = modal;
    closer.parentElement = modal;
    modalAction.parentElement = modal;
    backdrop.parentElement = modal;
    modal.dialog = dialog;
    modal.focusable = [closer, modalAction];

    const copySource = new FakeElement('copySource', 'wallet-address');
    copySource.textContent = 'orion-wallet-address';
    const copyButton = new FakeElement('', 'btn', { 'data-copy': '#copySource' });
    copyButton.textContent = 'Скопировать адрес';

    const tablist = new FakeElement('', 'dl-tabs');
    const clientTab = new FakeElement('dl-tab-client', 'dl-tab active', { 'data-tab': 'client' });
    const patchTab = new FakeElement('dl-tab-patch', 'dl-tab', { 'data-tab': 'patch' });
    const clientPane = new FakeElement('pane-client', 'dl-pane active');
    const patchPane = new FakeElement('pane-patch', 'dl-pane');
    const reveal = new FakeElement('', 'reveal');
    const counter = new FakeElement('', 'stat-value', { 'data-target': '42' });
    clientTab.parentElement = tablist;
    patchTab.parentElement = tablist;

    [nav, modal, clientTab, patchTab, clientPane, patchPane, copySource].forEach((element) => {
        if (element.id) elements.set(element.id, element);
    });

    // <html lang> — источник языка для строк, которые ставит сам JS
    // (подписи меню и обратная связь копирования). Фильтр lang.php переключает
    // этот атрибут на uk, поэтому скрипты обязаны его читать.
    const documentElement = new FakeElement('', '', { lang: options.lang || 'ru' });

    let execCopyCalls = 0;
    fakeDocument = {
        body,
        documentElement,
        activeElement: opener,
        addEventListener(type, callback) { listeners[type] = callback; },
        contains(element) { return Boolean(element); },
        createElement() {
            const element = new FakeElement();
            element.select = function () {};
            return element;
        },
        execCommand(command) {
            if (command === 'copy') execCopyCalls += 1;
            return true;
        },
        getElementById(id) { return elements.get(id) || null; },
        querySelector(selector) {
            if (selector === '[data-nav-toggle]') return navToggle;
            if (selector === '[data-nav-toggle][aria-expanded="true"]') {
                return navToggle.getAttribute('aria-expanded') === 'true' ? navToggle : null;
            }
            if (selector === '.dl-tabs') return tablist;
            if (selector === '.dl-tab.active') {
                return [clientTab, patchTab].find((tab) => tab.classList.contains('active')) || null;
            }
            if (selector === '.dl-tab') return clientTab;
            if (selector === '.donate-modal.is-open') {
                return modal.classList.contains('is-open') ? modal : null;
            }
            if (selector === '#copySource') return copySource;
            return null;
        },
        querySelectorAll(selector) {
            if (selector === '.dl-tab') return [clientTab, patchTab];
            if (selector === '.dl-pane') return [clientPane, patchPane];
            if (selector === '.reveal, .news-item') return [reveal];
            if (selector === '.stat-value[data-target]') return [counter];
            return [];
        },
    };

    const replacedHashes = [];
    const copyFeedbackSnapshots = [];
    let observerCreations = 0;
    const window = {
        history: { replaceState(state, title, hash) { replacedHashes.push(hash); } },
        location: { hash: '#patch' },
        matchMedia() { return { matches: Boolean(options.reducedMotion) }; },
        setTimeout(callback) {
            copyFeedbackSnapshots.push(copyButton.textContent);
            callback();
        },
    };
    if (options.observerAvailable) {
        window.IntersectionObserver = function () {
            observerCreations += 1;
            this.observe = function () {};
            this.unobserve = function () {};
        };
    }
    const navigator = {};

    const source = fs.readFileSync(path.join(__dirname, '..', 'js', 'site.js'), 'utf8');
    vm.runInNewContext(source, {
        window,
        document: fakeDocument,
        navigator,
        console,
        Boolean,
        Map,
        parseInt,
    });
    listeners.DOMContentLoaded();

    return {
        body,
        backdrop,
        clientPane,
        clientTab,
        closer,
        copyButton,
        copyFeedbackSnapshots,
        counter,
        dialog,
        execCopyCalls() { return execCopyCalls; },
        listeners,
        modal,
        modalAction,
        nav,
        navToggle,
        documentElement,
        observerCreations() { return observerCreations; },
        opener,
        outside,
        patchPane,
        patchTab,
        replacedHashes,
        reveal,
        window,
    };
}

const harness = createHarness();

assert.strictEqual(harness.patchTab.getAttribute('aria-selected'), 'true', 'hash-selected patch tab should be active');
assert.strictEqual(harness.clientTab.getAttribute('aria-selected'), 'false', 'only one tab should be selected');
assert.strictEqual(harness.patchPane.hidden, false, 'selected patch pane should be visible');
assert.strictEqual(harness.clientPane.hidden, true, 'unselected client pane should be hidden');
assert.strictEqual(harness.counter.textContent, 42, 'counter should settle without IntersectionObserver');
assert.strictEqual(harness.reveal.classList.contains('visible'), true, 'reveal should settle without IntersectionObserver');

harness.listeners.click(eventFor(harness.navToggle));
assert.strictEqual(harness.nav.classList.contains('is-open'), true, 'navigation should open with the shared state');
assert.strictEqual(harness.navToggle.getAttribute('aria-expanded'), 'true', 'navigation ARIA state should open');

fakeDocument.activeElement = harness.opener;
harness.listeners.click(eventFor(harness.opener));
assert.strictEqual(harness.modal.classList.contains('is-open'), true, 'modal should open with the shared state');
assert.strictEqual(harness.modal.getAttribute('aria-hidden'), 'false', 'modal should be exposed to accessibility APIs');
assert.strictEqual(harness.body.classList.contains('modal-open'), true, 'modal should lock body scrolling');
assert.strictEqual(fakeDocument.activeElement, harness.dialog, 'modal dialog should receive focus');

harness.listeners.click(eventFor(harness.backdrop));
assert.strictEqual(harness.modal.classList.contains('is-open'), false, 'backdrop should close the modal');
assert.strictEqual(fakeDocument.activeElement, harness.opener, 'backdrop close should return focus to the opener');

harness.listeners.click(eventFor(harness.opener));
harness.listeners.click(eventFor(harness.closer));
assert.strictEqual(harness.modal.classList.contains('is-open'), false, 'close button should close the modal');
assert.strictEqual(fakeDocument.activeElement, harness.opener, 'close button should return focus to the opener');

harness.listeners.click(eventFor(harness.opener));

fakeDocument.activeElement = harness.modalAction;
const lastForward = eventFor(harness.modalAction, { key: 'Tab', shiftKey: false });
harness.listeners.keydown(lastForward);
assert.strictEqual(lastForward.prevented, true, 'forward Tab from the last control should be trapped');
assert.strictEqual(fakeDocument.activeElement, harness.closer, 'forward Tab from last should wrap to first');

fakeDocument.activeElement = harness.closer;
const firstReverse = eventFor(harness.closer, { key: 'Tab', shiftKey: true });
harness.listeners.keydown(firstReverse);
assert.strictEqual(firstReverse.prevented, true, 'reverse Tab from the first control should be trapped');
assert.strictEqual(fakeDocument.activeElement, harness.modalAction, 'reverse Tab from first should wrap to last');

fakeDocument.activeElement = harness.outside;
const outsideReverse = eventFor(harness.outside, { key: 'Tab', shiftKey: true });
harness.listeners.keydown(outsideReverse);
assert.strictEqual(outsideReverse.prevented, true, 'reverse Tab from outside should be trapped');
assert.strictEqual(fakeDocument.activeElement, harness.modalAction, 'reverse Tab from outside should move to last');

fakeDocument.activeElement = harness.outside;
const outsideForward = eventFor(harness.outside, { key: 'Tab', shiftKey: false });
harness.listeners.keydown(outsideForward);
assert.strictEqual(outsideForward.prevented, true, 'forward Tab from outside should be trapped');
assert.strictEqual(fakeDocument.activeElement, harness.closer, 'forward Tab from outside should move to first');

harness.listeners.keydown(eventFor(harness.dialog, { key: 'Escape' }));
assert.strictEqual(harness.modal.classList.contains('is-open'), false, 'first Escape should close the modal');
assert.strictEqual(harness.nav.classList.contains('is-open'), true, 'first Escape should leave navigation open');
assert.strictEqual(fakeDocument.activeElement, harness.opener, 'modal should return focus to its opener');

harness.listeners.keydown(eventFor(harness.navToggle, { key: 'Escape' }));
assert.strictEqual(harness.nav.classList.contains('is-open'), false, 'second Escape should close navigation');
assert.strictEqual(harness.navToggle.getAttribute('aria-expanded'), 'false', 'navigation ARIA state should close');

harness.window.dlTab('client');
assert.strictEqual(harness.clientTab.getAttribute('aria-selected'), 'true', 'legacy tab caller should select client');
assert.strictEqual(harness.patchPane.hidden, true, 'legacy tab caller should hide the other pane');
assert.strictEqual(harness.replacedHashes.at(-1), '#client', 'tab selection should synchronize the hash');

harness.listeners.click(eventFor(harness.copyButton));
assert.strictEqual(harness.execCopyCalls(), 1, 'clipboard fallback should execute a copy command');
assert.strictEqual(harness.copyFeedbackSnapshots.at(-1), 'Скопировано!', 'clipboard fallback should show success feedback');
assert.strictEqual(harness.copyButton.textContent, 'Скопировать адрес', 'clipboard feedback should restore the original label');

const reducedHarness = createHarness({ autoPopup: true, reducedMotion: true, observerAvailable: true });
assert.strictEqual(reducedHarness.modal.classList.contains('is-open'), true, 'server-requested donation popup should open automatically');
assert.strictEqual(reducedHarness.counter.textContent, 42, 'reduced motion should settle counters immediately');
assert.strictEqual(reducedHarness.reveal.classList.contains('visible'), true, 'reduced motion should settle reveals immediately');
assert.strictEqual(reducedHarness.observerCreations(), 0, 'reduced motion should not construct observers');

// Строки, которые ставит JS, не проходят через output-фильтр lang.php,
// поэтому язык берётся из <html lang>. Проверяем обе ветки.
assert.strictEqual(harness.navToggle.getAttribute('aria-label'), 'Открыть меню', 'Russian navigation label should be the default');

const ukHarness = createHarness({ lang: 'uk' });
ukHarness.listeners.click(eventFor(ukHarness.navToggle));
assert.strictEqual(ukHarness.navToggle.getAttribute('aria-label'), 'Закрити меню', 'Ukrainian page should localize the open navigation label');
ukHarness.listeners.click(eventFor(ukHarness.navToggle));
assert.strictEqual(ukHarness.navToggle.getAttribute('aria-label'), 'Відкрити меню', 'Ukrainian page should localize the closed navigation label');

ukHarness.listeners.click(eventFor(ukHarness.copyButton));
assert.strictEqual(ukHarness.copyFeedbackSnapshots.at(-1), 'Скопійовано!', 'Ukrainian page should localize clipboard feedback');

process.stdout.write('Site interaction DOM checks passed.\n');
