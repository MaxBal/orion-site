/* Shared progressive-enhancement controller for Project Orion. */
(function () {
    'use strict';

    var REDUCED = Boolean(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    var modalReturnFocus = null;

    // Строки, которые ставит JS, не проходят через output-фильтр (lang.php),
    // поэтому язык читаем из <html lang>, который задаётся текущей сессией.
    var STRINGS = {
        ru: { navOpen: 'Открыть меню', navClose: 'Закрыть меню', copied: 'Скопировано!' },
        uk: { navOpen: 'Відкрити меню', navClose: 'Закрити меню', copied: 'Скопійовано!' },
        en: { navOpen: 'Open menu', navClose: 'Close menu', copied: 'Copied!' }
    };

    function t(key) {
        var lang = (document.documentElement.getAttribute('lang') || '').toLowerCase().split('-')[0];
        return (STRINGS[lang] || STRINGS.ru)[key];
    }

    function setNav(isOpen, navBtn) {
        navBtn = navBtn || document.querySelector('[data-nav-toggle]');
        if (!navBtn) return;

        var menu = document.getElementById(navBtn.getAttribute('aria-controls'));
        if (!menu) return;

        if (isOpen) {
            menu.classList.add('is-open');
        } else {
            menu.classList.remove('is-open');
        }
        navBtn.setAttribute('aria-expanded', String(isOpen));
        navBtn.setAttribute('aria-label', isOpen ? t('navClose') : t('navOpen'));
    }

    function setModal(modal, open) {
        if (!modal) return;

        if (open) {
            modal.classList.add('is-open');
        } else {
            modal.classList.remove('is-open');
        }
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.classList.toggle('modal-open', open);
    }

    function openModal(id) {
        var m = document.getElementById(id || 'donateModal');
        if (!m) return;

        if (!m.classList.contains('is-open')) {
            modalReturnFocus = document.activeElement;
        }
        setModal(m, true);
        var dialog = m.querySelector('.modal-dialog');
        if (dialog) dialog.focus();
    }

    function closeModal(el) {
        var m = el && el.matches && el.matches('.donate-modal') ? el : el && el.closest ? el.closest('.donate-modal') : null;
        if (!m) return;

        setModal(m, false);
        if (modalReturnFocus && typeof modalReturnFocus.focus === 'function' && document.contains(modalReturnFocus)) {
            modalReturnFocus.focus();
        }
        modalReturnFocus = null;
    }

    function trapModalFocus(modal, event) {
        var focusable = modal.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
        if (!focusable.length) {
            event.preventDefault();
            return;
        }

        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        var dialog = modal.querySelector('.modal-dialog');
        var active = document.activeElement;

        if (!modal.contains(active)) {
            event.preventDefault();
            (event.shiftKey ? last : first).focus();
        } else if (event.shiftKey && (active === first || active === dialog)) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && active === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function activateDownloadTab(name, updateHistory) {
        var tabs = Array.prototype.slice.call(document.querySelectorAll('.dl-tab'));
        if (!tabs.length) return;

        var activeTab = tabs.find(function (tab) {
            return tab.getAttribute('data-tab') === name;
        }) || tabs[0];
        var activeName = activeTab.getAttribute('data-tab');

        tabs.forEach(function (tab) {
            var tabName = tab.getAttribute('data-tab');
            var selected = tab === activeTab;
            var paneId = 'pane-' + tabName;
            tab.classList.toggle('active', selected);
            tab.setAttribute('role', 'tab');
            tab.setAttribute('aria-controls', paneId);
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.tabIndex = selected ? 0 : -1;
            if (!tab.id) tab.id = 'dl-tab-' + tabName;
        });

        document.querySelectorAll('.dl-pane').forEach(function (pane) {
            var selected = pane.id === 'pane-' + activeName;
            pane.classList.toggle('active', selected);
            pane.setAttribute('role', 'tabpanel');
            pane.setAttribute('aria-labelledby', 'dl-tab-' + pane.id.replace(/^pane-/, ''));
            pane.hidden = !selected;
        });

        if (updateHistory !== false && window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '#' + activeName);
        }
    }

    window.dlTab = activateDownloadTab;

    function fallbackCopy(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
        } catch (error) {
            // The visible feedback remains useful when the browser blocks clipboard access.
        }
        document.body.removeChild(textarea);
    }

    function showCopyFeedback(button) {
        var original = button.getAttribute('data-copy-label') || button.textContent;
        button.setAttribute('data-copy-label', original);
        button.textContent = t('copied');
        window.setTimeout(function () {
            button.textContent = original;
        }, 1500);
    }

    function copyFromButton(button) {
        var source = document.querySelector(button.getAttribute('data-copy'));
        if (!source) return;

        var text = source.textContent.trim();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                showCopyFeedback(button);
            }).catch(function () {
                fallbackCopy(text);
                showCopyFeedback(button);
            });
        } else {
            fallbackCopy(text);
            showCopyFeedback(button);
        }
    }

    function initializeDownloadTabs() {
        var tablist = document.querySelector('.dl-tabs');
        if (!tablist) return;

        tablist.setAttribute('role', 'tablist');
        var requested = window.location.hash.replace(/^#/, '');
        var requestedTab = requested && Array.prototype.find.call(document.querySelectorAll('.dl-tab'), function (tab) {
            return tab.getAttribute('data-tab') === requested;
        });
        var activeTab = requestedTab || document.querySelector('.dl-tab.active') || document.querySelector('.dl-tab');
        if (activeTab) activateDownloadTab(activeTab.getAttribute('data-tab'), false);
    }

    function initializeReveal() {
        document.querySelectorAll('.main-layout > .card, .main-layout .stats-bar, .main-layout .play-btn, .main-layout > .btn').forEach(function (element) {
            element.classList.add('reveal');
        });

        var revealElements = document.querySelectorAll('.reveal, .news-item');
        if (REDUCED || !('IntersectionObserver' in window)) {
            revealElements.forEach(function (element) {
                element.classList.add('visible');
            });
            return;
        }

        var groups = new Map();
        revealElements.forEach(function (element) {
            var parent = element.parentElement;
            var index = groups.get(parent) || 0;
            element.style.setProperty('--reveal-i', index);
            groups.set(parent, index + 1);
        });

        var observer = new window.IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.1 });
        revealElements.forEach(function (element) {
            observer.observe(element);
        });
    }

    function counterTarget(element) {
        return parseInt(element.getAttribute('data-target'), 10) || 0;
    }

    function showFinalCounterValues(counters) {
        counters.forEach(function (element) {
            element.textContent = counterTarget(element);
        });
    }

    function animateCounter(element) {
        var target = counterTarget(element);
        var duration = 2000;
        var startTime = null;
        var animate = function (currentTime) {
            if (!startTime) startTime = currentTime;
            var progress = Math.min((currentTime - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            element.textContent = Math.floor(eased * target);
            if (progress < 1) {
                window.requestAnimationFrame(animate);
            } else {
                element.textContent = target;
                element.classList.add('flash');
            }
        };
        window.requestAnimationFrame(animate);
    }

    function initializeCounters() {
        var counters = document.querySelectorAll('.stat-value[data-target]');
        if (!counters.length) return;

        if (REDUCED || !('IntersectionObserver' in window)) {
            showFinalCounterValues(counters);
            return;
        }

        var observer = new window.IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.5 });
        counters.forEach(function (counter) {
            observer.observe(counter);
        });
    }

    function applyContractFilters(root) {
        var searchControl = root.querySelector('[data-contract-filter="search"]');
        var roleControl = root.querySelector('[data-contract-filter="role"]');
        var statusControl = root.querySelector('[data-contract-filter="status"]');
        var query = searchControl && typeof searchControl.value === 'string'
            ? searchControl.value.trim().toLocaleLowerCase()
            : '';
        var role = roleControl ? roleControl.value : 'all';
        var status = statusControl ? statusControl.value : 'all';
        var visible = 0;

        root.querySelectorAll('[data-contract-item]').forEach(function (item) {
            var itemSearch = (item.getAttribute('data-contract-search') || '').toLocaleLowerCase();
            var matches = (!query || itemSearch.indexOf(query) !== -1)
                && (role === 'all' || item.getAttribute('data-contract-role') === role)
                && (status === 'all' || item.getAttribute('data-contract-status') === status);
            item.hidden = !matches;
            if (matches) visible += 1;
        });

        root.querySelectorAll('[data-contract-role-group]').forEach(function (group) {
            var groupItems = group.querySelectorAll('[data-contract-item]');
            var groupVisible = 0;
            groupItems.forEach(function (item) {
                if (!item.hidden) groupVisible += 1;
            });
            group.hidden = groupVisible === 0;
            var groupCount = group.querySelector('[data-contract-group-count]');
            if (groupCount) groupCount.textContent = groupVisible;
        });

        var visibleCount = root.querySelector('[data-contract-visible-count]');
        if (visibleCount) visibleCount.textContent = visible;
        var emptyState = root.querySelector('[data-contract-filter-empty]');
        if (emptyState) emptyState.hidden = visible !== 0;
        var reset = root.querySelector('[data-contract-filter-reset]');
        if (reset) reset.hidden = !query && role === 'all' && status === 'all';
    }

    function initializeContractFilters() {
        var root = document.querySelector('[data-contract-registry]');
        if (root) applyContractFilters(root);
    }

    function handleContractFilterEvent(event) {
        var control = event.target && event.target.closest
            ? event.target.closest('[data-contract-filter]')
            : null;
        var root = control && control.closest ? control.closest('[data-contract-registry]') : null;
        if (root) applyContractFilters(root);
    }

    document.addEventListener('click', function (e) {
        var navBtn = e.target.closest('[data-nav-toggle]');
        if (navBtn) {
            var menu = document.getElementById(navBtn.getAttribute('aria-controls'));
            var isOpen = Boolean(menu && !menu.classList.contains('is-open'));
            setNav(isOpen, navBtn);
            return;
        }

        var tab = e.target.closest('.dl-tab');
        if (tab) {
            e.preventDefault();
            activateDownloadTab(tab.getAttribute('data-tab'));
            return;
        }

        var opener = e.target.closest('[data-modal-open]');
        if (opener) {
            openModal(opener.getAttribute('data-modal-open'));
            return;
        }

        var closer = e.target.closest('[data-modal-close]');
        if (closer) {
            closeModal(closer);
            return;
        }

        var scrollBtn = e.target.closest('[data-scroll-to]');
        if (scrollBtn) {
            var target = document.querySelector(scrollBtn.getAttribute('data-scroll-to'));
            if (target) target.scrollIntoView({ behavior: REDUCED ? 'auto' : 'smooth' });
            return;
        }

        var copyBtn = e.target.closest('[data-copy]');
        if (copyBtn) {
            copyFromButton(copyBtn);
            return;
        }

        var resetFilters = e.target.closest('[data-contract-filter-reset]');
        if (resetFilters) {
            var filterRoot = resetFilters.closest('[data-contract-registry]');
            if (!filterRoot) return;
            filterRoot.querySelectorAll('[data-contract-filter]').forEach(function (control) {
                control.value = control.tagName === 'SELECT' ? 'all' : '';
            });
            applyContractFilters(filterRoot);
            return;
        }

        var navLink = e.target.closest('.site-nav-link, .mobile-account-link');
        if (navLink) setNav(false);
    });

    document.addEventListener('input', handleContractFilterEvent);
    document.addEventListener('change', handleContractFilterEvent);

    document.addEventListener('keydown', function (e) {
        var open = document.querySelector('.donate-modal.is-open');
        if (open) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeModal(open);
            } else if (e.key === 'Tab') {
                trapModalFocus(open, e);
            }
            return;
        }

        if (e.key === 'Escape') {
            var navBtn = document.querySelector('[data-nav-toggle][aria-expanded="true"]');
            if (navBtn) {
                e.preventDefault();
                setNav(false, navBtn);
                navBtn.focus();
            }
            return;
        }

        var tab = e.target.closest('.dl-tab');
        if (!tab || ['ArrowLeft', 'ArrowRight', 'Home', 'End'].indexOf(e.key) === -1) return;

        var tabs = Array.prototype.slice.call(document.querySelectorAll('.dl-tab'));
        var index = tabs.indexOf(tab);
        if (e.key === 'Home') index = 0;
        if (e.key === 'End') index = tabs.length - 1;
        if (e.key === 'ArrowLeft') index = (index - 1 + tabs.length) % tabs.length;
        if (e.key === 'ArrowRight') index = (index + 1) % tabs.length;
        e.preventDefault();
        tabs[index].focus();
        activateDownloadTab(tabs[index].getAttribute('data-tab'));
    });

    document.addEventListener('DOMContentLoaded', function () {
        setNav(false);
        initializeDownloadTabs();

        if (document.body.getAttribute('data-show-popup') === '1') {
            openModal('donateModal');
        }

        initializeReveal();
        initializeCounters();
        initializeContractFilters();
    });
})();
