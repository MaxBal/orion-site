(function () {
    'use strict';

    var DEBOUNCE_MS = 180;
    var STRINGS = {
        ru: { battles: 'боёв' },
        uk: { battles: 'боїв' },
        en: { battles: 'battles' }
    };

    function text(key) {
        var lang = (document.documentElement.getAttribute('lang') || '').toLowerCase().split('-')[0];
        return (STRINGS[lang] || STRINGS.ru)[key];
    }

    function initializeRankingTabs() {
        var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-player-ranking-tab]'));
        var panels = Array.prototype.slice.call(document.querySelectorAll('[data-player-ranking-panel]'));
        if (!tabs.length || !panels.length) return;

        function activate(tab, moveFocus) {
            var key = tab.getAttribute('data-player-ranking-tab');
            tabs.forEach(function (candidate) {
                var selected = candidate === tab;
                candidate.classList.toggle('is-active', selected);
                candidate.setAttribute('aria-selected', selected ? 'true' : 'false');
                candidate.tabIndex = selected ? 0 : -1;
            });
            panels.forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-player-ranking-panel') !== key;
            });
            if (moveFocus) tab.focus();
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () { activate(tab, false); });
            tab.addEventListener('keydown', function (event) {
                if (['ArrowLeft', 'ArrowRight', 'Home', 'End'].indexOf(event.key) === -1) return;
                event.preventDefault();
                var next = index;
                if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
                if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
                if (event.key === 'Home') next = 0;
                if (event.key === 'End') next = tabs.length - 1;
                activate(tabs[next], true);
            });
        });
    }

    function initializeAutocomplete() {
        var input = document.querySelector('[data-player-input]');
        var suggestions = document.querySelector('[data-player-suggestions]');
        if (!input || !suggestions) return;

        var timer = null;
        var requestSerial = 0;
        var activeIndex = -1;

        function links() {
            return Array.prototype.slice.call(suggestions.querySelectorAll('[data-player-suggestion]'));
        }

        function closeList() {
            requestSerial += 1;
            if (timer !== null) {
                window.clearTimeout(timer);
                timer = null;
            }
            activeIndex = -1;
            suggestions.hidden = true;
            suggestions.textContent = '';
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        }

        function setActive(index) {
            var items = links();
            activeIndex = items.length ? (index + items.length) % items.length : -1;
            items.forEach(function (item, itemIndex) {
                item.setAttribute('aria-selected', itemIndex === activeIndex ? 'true' : 'false');
            });
            if (activeIndex >= 0) input.setAttribute('aria-activedescendant', items[activeIndex].id);
            else input.removeAttribute('aria-activedescendant');
        }

        function renderList(items) {
            suggestions.textContent = '';
            activeIndex = -1;
            if (!items.length) {
                closeList();
                return;
            }
            items.forEach(function (item, index) {
                var link = document.createElement('a');
                var battles = Number(item.total_battles) || 0;
                link.id = 'player-suggestion-' + index;
                link.href = 'players.php?username=' + encodeURIComponent(String(item.username || ''));
                link.setAttribute('role', 'option');
                link.setAttribute('aria-selected', 'false');
                link.setAttribute('data-player-suggestion', '');
                link.textContent = String(item.username || '') + (battles ? ' · ' + battles + ' ' + text('battles') : '');
                suggestions.appendChild(link);
            });
            suggestions.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function request(query) {
            var serial = ++requestSerial;
            window.fetch('players.php?ajax=player-suggestions&q=' + encodeURIComponent(query), {
                headers: { Accept: 'application/json' }
            }).then(function (response) {
                if (!response.ok) throw new Error('autocomplete request failed');
                return response.json();
            }).then(function (payload) {
                if (serial !== requestSerial) return;
                renderList(Array.isArray(payload.suggestions) ? payload.suggestions : []);
            }).catch(function () {
                if (serial === requestSerial) closeList();
            });
        }

        input.addEventListener('input', function () {
            var query = String(input.value || '').trim();
            closeList();
            if (query.length < 2) {
                return;
            }
            timer = window.setTimeout(function () { request(query); }, DEBOUNCE_MS);
        });

        input.addEventListener('keydown', function (event) {
            var items = links();
            if (event.key === 'ArrowDown' && items.length) {
                event.preventDefault();
                setActive(activeIndex + 1);
            } else if (event.key === 'ArrowUp' && items.length) {
                event.preventDefault();
                setActive(activeIndex - 1);
            } else if (event.key === 'Enter' && activeIndex >= 0 && items[activeIndex]) {
                event.preventDefault();
                window.location.href = items[activeIndex].href;
            } else if (event.key === 'Escape') {
                closeList();
            }
        });

        document.addEventListener('click', function (event) {
            if (!input.parentElement.contains(event.target)) closeList();
        });
    }

    function initializePlayers() {
        initializeRankingTabs();
        initializeAutocomplete();
    }

    window.OrionPlayers = { initialize: initializePlayers };
    document.addEventListener('DOMContentLoaded', initializePlayers);
})();
