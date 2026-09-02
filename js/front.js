/*
 * front.js — поведение оформления «Стального фронта».
 *
 * Живёт отдельно от site.js намеренно: site.js покрыт контрактным тестом на
 * фальшивом DOM (tests/site_interactions_test.js), у которого нет ни canvas,
 * ни window.addEventListener. Здесь же всё завязано на реальный браузер,
 * поэтому каждый блок проверяет наличие своего узла и молча выходит.
 */
(function () {
    'use strict';

    var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function each(list, callback) {
        Array.prototype.forEach.call(list || [], callback);
    }

    function pad(value) {
        return value < 10 ? '0' + value : String(value);
    }

    /* Бегущие ленты: дублируем группу, чтобы прокрутка на -50% была бесшовной. */
    each(document.querySelectorAll('[data-front-marquee]'), function (track) {
        if (track.firstElementChild) {
            track.appendChild(track.firstElementChild.cloneNode(true));
        }
    });

    /* Шапка проявляется только после прокрутки — над героем она прозрачная.
       Заодно отдаём её высоту в --header-height: на неё опирается отрицательный
       отступ героя, а высота зависит от длины навигации и шрифта. */
    var header = document.querySelector('.app-header');
    if (header) {
        var syncHeader = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 10);
        };
        var syncHeaderHeight = function () {
            document.documentElement.style.setProperty('--header-height', header.offsetHeight + 'px');
        };
        syncHeader();
        syncHeaderHeight();
        window.addEventListener('scroll', syncHeader, { passive: true });
        window.addEventListener('resize', syncHeaderHeight);
        window.addEventListener('load', syncHeaderHeight);
    }

    /* Шкалы в полосе статистики заполняются, когда полоса попадает в кадр. */
    var bars = document.querySelectorAll('.metric-card');
    if (bars.length) {
        if (REDUCED || !('IntersectionObserver' in window)) {
            each(bars, function (card) { card.classList.add('is-in'); });
        } else {
            var barObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-in');
                    barObserver.unobserve(entry.target);
                });
            }, { threshold: 0.35 });
            each(bars, function (card) { barObserver.observe(card); });
        }
    }

    /* Искры над стартовым столом. Канвас декоративный, при reduced motion не запускаем. */
    var embers = document.querySelector('[data-front-embers]');
    if (embers && embers.getContext && !REDUCED) {
        var context = embers.getContext('2d');
        var particles = [];
        var width = 0;
        var height = 0;

        var resize = function () {
            width = embers.width = embers.offsetWidth;
            height = embers.height = embers.offsetHeight;
        };
        resize();
        window.addEventListener('resize', resize);

        for (var i = 0; i < 70; i++) {
            particles.push({
                x: Math.random(),
                y: Math.random(),
                size: 0.6 + Math.random() * 2.2,
                speed: 0.0006 + Math.random() * 0.0022,
                drift: Math.random() * 6.28,
                opacity: 0.25 + Math.random() * 0.6
            });
        }

        (function loop(time) {
            context.clearRect(0, 0, width, height);
            for (var index = 0; index < particles.length; index++) {
                var particle = particles[index];
                particle.y -= particle.speed;
                particle.drift += 0.02;
                if (particle.y < -0.05) {
                    particle.y = 1.05;
                    particle.x = Math.random();
                }
                var x = (particle.x + Math.sin(particle.drift) * 0.012) * width;
                var y = particle.y * height;
                var flicker = particle.opacity * (0.7 + 0.3 * Math.sin(time / 120 + particle.drift * 3));
                context.beginPath();
                context.fillStyle = 'rgba(255,' + (140 + Math.floor(60 * Math.sin(particle.drift))) + ',40,' + flicker.toFixed(2) + ')';
                context.arc(x, y, particle.size, 0, 6.28);
                context.fill();
            }
            window.requestAnimationFrame(loop);
        })(0);
    }

    /* Обратный отсчёт до запуска. Дата приходит из разметки в ISO-формате. */
    each(document.querySelectorAll('[data-front-countdown]'), function (widget) {
        var target = Date.parse(widget.getAttribute('data-front-countdown'));
        if (isNaN(target)) return;
        var days = widget.querySelector('[data-countdown-days]');
        var hours = widget.querySelector('[data-countdown-hours]');
        var minutes = widget.querySelector('[data-countdown-minutes]');
        var seconds = widget.querySelector('[data-countdown-seconds]');
        if (!days || !hours || !minutes || !seconds) return;

        var tick = function () {
            var left = Math.max(0, Math.floor((target - Date.now()) / 1000));
            days.textContent = pad(Math.floor(left / 86400));
            hours.textContent = pad(Math.floor((left % 86400) / 3600));
            minutes.textContent = pad(Math.floor((left % 3600) / 60));
            seconds.textContent = pad(left % 60);
        };
        tick();
        window.setInterval(tick, 1000);
    });

    /* Вкладки зала славы: панели уже отрисованы сервером, здесь только показ. */
    var hallTabs = document.querySelectorAll('[data-front-hall-tab]');
    if (hallTabs.length) {
        var panels = document.querySelectorAll('[data-front-hall-panel]');
        each(hallTabs, function (tab) {
            tab.addEventListener('click', function () {
                var requested = tab.getAttribute('data-front-hall-tab');
                each(hallTabs, function (other) {
                    var selected = other === tab;
                    other.classList.toggle('is-active', selected);
                    other.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
                each(panels, function (panel) {
                    panel.hidden = panel.getAttribute('data-front-hall-panel') !== requested;
                });
            });
        });
    }
})();
