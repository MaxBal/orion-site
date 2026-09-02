/* Нативный контроллер анимаций Project Orion.
   Запускается в конце body и улучшает серверную разметку без внешних библиотек. */
(function () {
    'use strict';

    var REDUCED = Boolean(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

    if (!REDUCED) document.documentElement.classList.add('motion-ready');

    function revealElement(element) {
        element.classList.add('motion-visible', 'visible');
    }

    function revealDelay(element, index) {
        var explicit = parseInt(element.getAttribute('data-aos-delay') || '', 10);
        var delay = Number.isFinite(explicit) ? explicit : (index * 55);
        element.style.setProperty('--motion-delay', delay + 'ms');
    }

    function initializeScrollReveals() {
        var elements = Array.prototype.slice.call(document.querySelectorAll('[data-aos]'));
        if (!elements.length) return;

        elements.forEach(revealDelay);

        if (REDUCED || !('IntersectionObserver' in window)) {
            elements.forEach(revealElement);
            return;
        }

        var observer = new window.IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                revealElement(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

        elements.forEach(function (element) {
            observer.observe(element);
        });
    }

    function initializeScrollProgress() {
        var root = document.documentElement;
        var queued = false;

        function update() {
            var scrollable = root.scrollHeight - window.innerHeight;
            var progress = scrollable > 0 ? window.scrollY / scrollable : 0;
            root.style.setProperty('--scroll-progress', Math.max(0, Math.min(progress, 1)).toFixed(3));
            queued = false;
        }

        function queueUpdate() {
            if (queued) return;
            queued = true;
            window.requestAnimationFrame(update);
        }

        update();
        window.addEventListener('scroll', queueUpdate, { passive: true });
        window.addEventListener('resize', queueUpdate);
    }

    function initializeHeroParallax() {
        var hero = document.querySelector('.home-hero');
        var mediaQuery = window.matchMedia && window.matchMedia('(pointer: fine)');
        if (!hero || REDUCED || !mediaQuery || !mediaQuery.matches) return;

        var currentX = 0;
        var currentY = 0;
        var targetX = 0;
        var targetY = 0;
        var frame = null;

        function update() {
            currentX += (targetX - currentX) * .08;
            currentY += (targetY - currentY) * .08;
            hero.style.setProperty('--hero-shift-x', (currentX * 12).toFixed(2) + 'px');
            hero.style.setProperty('--hero-shift-y', (currentY * 12).toFixed(2) + 'px');
            hero.style.setProperty('--hero-rotate-x', (currentY * -2.4).toFixed(2) + 'deg');
            hero.style.setProperty('--hero-rotate-y', (currentX * 2.4).toFixed(2) + 'deg');

            if (Math.abs(targetX - currentX) > .001 || Math.abs(targetY - currentY) > .001) {
                frame = window.requestAnimationFrame(update);
            } else {
                frame = null;
            }
        }

        function animateTo(x, y) {
            targetX = x;
            targetY = y;
            if (!frame) frame = window.requestAnimationFrame(update);
        }

        hero.addEventListener('pointermove', function (event) {
            var bounds = hero.getBoundingClientRect();
            animateTo(((event.clientX - bounds.left) / bounds.width - .5) * 2, ((event.clientY - bounds.top) / bounds.height - .5) * 2);
        });
        hero.addEventListener('pointerleave', function () { animateTo(0, 0); });
    }

    function initializeRipples() {
        if (REDUCED) return;

        document.addEventListener('pointerdown', function (event) {
            var target = event.target.closest('.btn, .theme-toggle, .nav-toggle');
            if (!target || target.disabled) return;

            var bounds = target.getBoundingClientRect();
            var ripple = document.createElement('span');
            ripple.className = 'motion-ripple';
            ripple.style.left = (event.clientX - bounds.left) + 'px';
            ripple.style.top = (event.clientY - bounds.top) + 'px';
            ripple.addEventListener('animationend', function () { ripple.remove(); });
            target.appendChild(ripple);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initializeScrollReveals();
        initializeScrollProgress();
        initializeHeroParallax();
        initializeRipples();
    });
})();
