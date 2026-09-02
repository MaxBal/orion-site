/* Early theme controller: runs in <head> so the selected palette is painted immediately. */
(function () {
    'use strict';

    var STORAGE_KEY = 'orion-theme';
    var root = document.documentElement;

    // Подписи задаются из JS, поэтому output-фильтр (lang.php) их не видит.
    // Язык берём из <html lang>, который задаётся текущей сессией.
    var LABELS = {
        ru: { light: 'Включить светлую тему', dark: 'Включить тёмную тему' },
        uk: { light: 'Увімкнути світлу тему', dark: 'Увімкнути темну тему' },
        en: { light: 'Enable light theme', dark: 'Enable dark theme' }
    };

    function labels() {
        var language = (root.getAttribute('lang') || '').toLowerCase().split('-')[0];
        return LABELS[language] || LABELS.ru;
    }

    function readStoredTheme() {
        try {
            var value = window.localStorage.getItem(STORAGE_KEY);
            return value === 'light' || value === 'dark' ? value : null;
        } catch (error) {
            return null;
        }
    }

    function storeTheme(theme) {
        try {
            window.localStorage.setItem(STORAGE_KEY, theme);
        } catch (error) {
            // The theme remains active even when storage is unavailable.
        }
    }

    function currentTheme() {
        return root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    }

    function syncControls() {
        var theme = currentTheme();
        var isDark = theme === 'dark';
        var label = isDark ? labels().light : labels().dark;

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
            button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        });
    }

    function applyTheme(theme, persist) {
        var nextTheme = theme === 'light' ? 'light' : 'dark';
        root.setAttribute('data-theme', nextTheme);
        root.style.colorScheme = nextTheme;
        if (persist) storeTheme(nextTheme);
        syncControls();
    }

    function toggleTheme() {
        applyTheme(currentTheme() === 'dark' ? 'light' : 'dark', true);
    }

    applyTheme(readStoredTheme() || 'dark', false);

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-theme-toggle]')) toggleTheme();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncControls);
    } else {
        syncControls();
    }

    window.addEventListener('storage', function (event) {
        if (event.key === STORAGE_KEY && (event.newValue === 'light' || event.newValue === 'dark')) {
            applyTheme(event.newValue, false);
        }
    });

    window.OrionTheme = {
        get: currentTheme,
        set: function (theme) { applyTheme(theme, true); },
        toggle: toggleTheme,
    };
})();
