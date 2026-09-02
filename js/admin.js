(function () {
    'use strict';

    var config = window.OrionAdminConfig || {};
    var csrfToken = config.csrfToken || '';
    var selectedAccountId = Number(config.selectedAccountId || 0);
    var filteredVehicleNames = Array.isArray(config.filteredVehicleNames) ? config.filteredVehicleNames : [];
    var mirrorCount = { client: 0, patch: 0 };
    var STRINGS = {
        ru: {
            enabled: 'Включено',
            disabled: 'Выключено',
            unknownError: 'Неизвестная ошибка',
            globalUpdated: 'Глобальный доступ обновлен.',
            personalUpdated: 'Персональный доступ обновлен.',
            noVehicles: 'Нет танков в текущем фильтре.',
            globalEnable: 'включить',
            globalDisable: 'выключить',
            globalConfirm: 'Глобально {action} {count} танков в текущем фильтре?',
            vehiclesUpdated: 'Обновлено танков: {count}.',
            selectPlayer: 'Сначала выбери игрока.',
            inheritFor: 'вернуть наследование для',
            enableFor: 'включить для игрока',
            disableFor: 'выключить для игрока',
            playerConfirm: '{action} {count} танков в текущем фильтре?',
            rulesUpdated: 'Обновлено персональных правил: {count}.',
            resetOverrides: 'Сбросить все персональные правила этого аккаунта?',
            enableAll: 'Включить все танки глобально для сервера?',
            accountUpdated: 'Аккаунт обновлен.',
            banConfirm: 'Забанить игрока {username} (#{accountId})?\n\nОК — бан аккаунта + его последнего игрового IP.\nОтмена — без бана.',
            banReason: 'Причина бана (необязательно):',
            userBanned: 'Игрок забанен.',
            bannedIp: ' IP {ip} тоже заблокирован.',
            unknownGameIp: ' Игровой IP неизвестен (игрок ещё не заходил в игру) — забанен только аккаунт.',
            newPassword: 'Новый пароль для {username} (#{accountId}):\n6–128 символов.',
            passwordLength: 'Пароль должен быть от 6 до 128 символов.',
            passwordChanged: 'Пароль игрока {username} изменён.',
            newUsername: 'Новый никнейм для {username} (#{accountId}):\n3–24 символа (буквы, цифры, _ - .).',
            usernameLength: 'Никнейм должен быть от 3 до 24 символов.',
            usernameChanged: 'Никнейм игрока {username} изменён на {next}.',
            noneSelected: 'Никто не выбран. Отметь игроков галочками.',
            bulkBanConfirm: 'Забанить выбранных игроков ({count}) вместе с их игровыми IP?',
            bulkBanReason: 'Причина бана (необязательно, применится ко всем):',
            bulkBanned: 'Забанено игроков: {banned} (IP: {ips}).',
            bulkSkipped: ' Пропущено: {skipped} (админы/вы сами).',
            ipRequired: 'Введите IP-адрес.',
            ipBanned: 'IP забанен.',
            macRequired: 'Введите MAC-адрес.',
            macBanned: 'MAC забанен.',
            unbanConfirm: 'Снять этот бан?',
            unbanned: 'Бан снят.',
            mirrorName: 'Название',
            mirrorActive: 'Активно',
            mirrorRemove: 'Удалить',
            navOpen: 'Открыть меню',
            navClose: 'Закрыть меню'
        },
        uk: {
            enabled: 'Увімкнено',
            disabled: 'Вимкнено',
            unknownError: 'Невідома помилка',
            globalUpdated: 'Глобальний доступ оновлено.',
            personalUpdated: 'Персональний доступ оновлено.',
            noVehicles: 'У поточному фільтрі немає танків.',
            globalEnable: 'увімкнути',
            globalDisable: 'вимкнути',
            globalConfirm: 'Глобально {action} {count} танків у поточному фільтрі?',
            vehiclesUpdated: 'Оновлено танків: {count}.',
            selectPlayer: 'Спочатку вибери гравця.',
            inheritFor: 'повернути успадкування для',
            enableFor: 'увімкнути для гравця',
            disableFor: 'вимкнути для гравця',
            playerConfirm: '{action} {count} танків у поточному фільтрі?',
            rulesUpdated: 'Оновлено персональних правил: {count}.',
            resetOverrides: 'Скинути всі персональні правила цього облікового запису?',
            enableAll: 'Увімкнути всі танки глобально для сервера?',
            accountUpdated: 'Обліковий запис оновлено.',
            banConfirm: 'Заблокувати гравця {username} (#{accountId})?\n\nОК — заблокувати обліковий запис і його останню ігрову IP-адресу.\nСкасувати — не блокувати.',
            banReason: 'Причина блокування (необов’язково):',
            userBanned: 'Гравця заблоковано.',
            bannedIp: ' IP {ip} також заблоковано.',
            unknownGameIp: ' Ігрова IP-адреса невідома (гравець ще не заходив у гру) — заблоковано лише обліковий запис.',
            newPassword: 'Новий пароль для {username} (#{accountId}):\n6–128 символів.',
            passwordLength: 'Пароль має містити від 6 до 128 символів.',
            passwordChanged: 'Пароль гравця {username} змінено.',
            newUsername: 'Новий нікнейм для {username} (#{accountId}):\n3–24 символи (літери, цифри, _ - .).',
            usernameLength: 'Нікнейм має містити від 3 до 24 символів.',
            usernameChanged: 'Нікнейм гравця {username} змінено на {next}.',
            noneSelected: 'Нікого не вибрано. Познач гравців галочками.',
            bulkBanConfirm: 'Заблокувати вибраних гравців ({count}) разом з їхніми ігровими IP-адресами?',
            bulkBanReason: 'Причина блокування (необов’язково, буде застосована до всіх):',
            bulkBanned: 'Заблоковано гравців: {banned} (IP: {ips}).',
            bulkSkipped: ' Пропущено: {skipped} (адміністратори/ви самі).',
            ipRequired: 'Введіть IP-адресу.',
            ipBanned: 'IP-адресу заблоковано.',
            macRequired: 'Введіть MAC-адресу.',
            macBanned: 'MAC-адресу заблоковано.',
            unbanConfirm: 'Зняти це блокування?',
            unbanned: 'Блокування знято.',
            mirrorName: 'Назва',
            mirrorActive: 'Активно',
            mirrorRemove: 'Видалити',
            navOpen: 'Відкрити меню',
            navClose: 'Закрити меню'
        },
        en: {
            enabled: 'Enabled',
            disabled: 'Disabled',
            unknownError: 'Unknown error',
            globalUpdated: 'Global access updated.',
            personalUpdated: 'Personal access updated.',
            noVehicles: 'No tanks in the current filter.',
            globalEnable: 'enable',
            globalDisable: 'disable',
            globalConfirm: 'Globally {action} {count} tanks in the current filter?',
            vehiclesUpdated: 'Tanks updated: {count}.',
            selectPlayer: 'Select a player first.',
            inheritFor: 'restore inheritance for',
            enableFor: 'enable for player',
            disableFor: 'disable for player',
            playerConfirm: '{action} {count} tanks in the current filter?',
            rulesUpdated: 'Personal rules updated: {count}.',
            resetOverrides: 'Reset all personal rules for this account?',
            enableAll: 'Enable all tanks globally for the server?',
            accountUpdated: 'Account updated.',
            banConfirm: 'Ban player {username} (#{accountId})?\n\nOK - ban the account and its last game IP.\nCancel - do not ban.',
            banReason: 'Ban reason (optional):',
            userBanned: 'Player banned.',
            bannedIp: ' IP {ip} was blocked too.',
            unknownGameIp: ' The game IP is unknown (the player has not joined the game yet), so only the account was banned.',
            newPassword: 'New password for {username} (#{accountId}):\n6-128 characters.',
            passwordLength: 'Password must be 6 to 128 characters.',
            passwordChanged: 'Password for player {username} changed.',
            newUsername: 'New username for {username} (#{accountId}):\n3-24 characters (letters, numbers, _ - .).',
            usernameLength: 'Username must be 3 to 24 characters.',
            usernameChanged: 'Username for player {username} changed to {next}.',
            noneSelected: 'No one selected. Check the players you want to ban.',
            bulkBanConfirm: 'Ban the selected players ({count}) together with their game IPs?',
            bulkBanReason: 'Ban reason (optional, applied to all):',
            bulkBanned: 'Players banned: {banned} (IPs: {ips}).',
            bulkSkipped: ' Skipped: {skipped} (admins/yourself).',
            ipRequired: 'Enter an IP address.',
            ipBanned: 'IP banned.',
            macRequired: 'Enter a MAC address.',
            macBanned: 'MAC banned.',
            unbanConfirm: 'Remove this ban?',
            unbanned: 'Ban removed.',
            mirrorName: 'Name',
            mirrorActive: 'Active',
            mirrorRemove: 'Remove',
            navOpen: 'Open menu',
            navClose: 'Close menu'
        }
    };

    function t(key, values) {
        var root = document.documentElement;
        var lang = root && root.getAttribute ? (root.getAttribute('lang') || '').toLowerCase().split('-')[0] : '';
        var text = (STRINGS[lang] || STRINGS.ru)[key] || STRINGS.ru[key] || key;
        Object.keys(values || {}).forEach(function (name) {
            text = text.replace('{' + name + '}', values[name]);
        });
        return text;
    }

    function statusPill(enabled) {
        return enabled
            ? '<span class="pill pill-on">' + t('enabled') + '</span>'
            : '<span class="pill pill-off">' + t('disabled') + '</span>';
    }

    function postAdmin(payload) {
        payload.append('csrf_token', csrfToken);
        return fetch('admin.php?ajax=1', { method: 'POST', body: payload })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.error || t('unknownError'));
                }
                return data;
            });
    }

    function flash(message, danger) {
        var box = document.getElementById('adminNotice');
        if (!box) {
            return;
        }
        box.className = 'notice-line show alert ' + (danger ? 'alert-danger' : 'alert-success');
        box.textContent = message;
        window.clearTimeout(window.adminNoticeTimer);
        window.adminNoticeTimer = window.setTimeout(function () {
            box.classList.remove('show');
        }, 2800);
    }

    function pulseRow(row) {
        if (!row) {
            return;
        }
        row.classList.remove('row-flash');
        void row.offsetWidth;
        row.classList.add('row-flash');
    }

    function updateEffective(row) {
        var override = row.dataset.overrideMode;
        var globalEnabled = row.dataset.globalEnabled === '1';
        var effective = override === 'inherit' ? globalEnabled : override === 'enabled';
        var effectiveCell = row.querySelector('.js-effective-status');
        if (effectiveCell) {
            effectiveCell.innerHTML = statusPill(effective);
        }
    }

    function toggleGlobal(input) {
        var row = input.closest('tr');
        var formData = new FormData();
        formData.append('action', 'set_global_vehicle');
        formData.append('tank_name', row.dataset.vehicleName);
        formData.append('status', input.checked ? '1' : '0');
        input.disabled = true;
        postAdmin(formData)
            .then(function (data) {
                row.dataset.globalEnabled = data.global_enabled ? '1' : '0';
                row.querySelector('.js-global-status').innerHTML = statusPill(data.global_enabled);
                updateEffective(row);
                pulseRow(row);
                flash(t('globalUpdated'), false);
            })
            .catch(function (error) {
                input.checked = !input.checked;
                flash(error.message, true);
            })
            .finally(function () { input.disabled = false; });
    }

    function setPlayerMode(select) {
        var row = select.closest('tr');
        var oldValue = select.dataset.lastValue || 'inherit';
        var formData = new FormData();
        formData.append('action', 'set_account_vehicle');
        formData.append('account_id', selectedAccountId);
        formData.append('tank_name', row.dataset.vehicleName);
        formData.append('mode', select.value);
        select.disabled = true;
        postAdmin(formData)
            .then(function (data) {
                row.dataset.overrideMode = data.mode;
                select.dataset.lastValue = data.mode;
                updateEffective(row);
                pulseRow(row);
                flash(t('personalUpdated'), false);
            })
            .catch(function (error) {
                select.value = oldValue;
                flash(error.message, true);
            })
            .finally(function () { select.disabled = false; });
    }

    function appendVehicleNames(formData) {
        filteredVehicleNames.forEach(function (name) {
            formData.append('vehicle_names[]', name);
        });
        return filteredVehicleNames.length;
    }

    function bulkGlobal(status) {
        var formData = new FormData();
        var count = appendVehicleNames(formData);
        if (!count) {
            flash(t('noVehicles'), true);
            return;
        }
        var label = t(status ? 'globalEnable' : 'globalDisable');
        if (!window.confirm(t('globalConfirm', { action: label, count: count }))) {
            return;
        }
        formData.append('action', 'bulk_global_vehicles');
        formData.append('status', status ? '1' : '0');
        postAdmin(formData)
            .then(function (data) {
                flash(t('vehiclesUpdated', { count: data.count }), false);
                window.setTimeout(function () { window.location.reload(); }, 450);
            })
            .catch(function (error) { flash(error.message, true); });
    }

    function bulkPlayer(mode) {
        if (!selectedAccountId) {
            flash(t('selectPlayer'), true);
            return;
        }
        var formData = new FormData();
        var count = appendVehicleNames(formData);
        if (!count) {
            flash(t('noVehicles'), true);
            return;
        }
        var labels = { inherit: t('inheritFor'), enabled: t('enableFor'), disabled: t('disableFor') };
        if (!window.confirm(t('playerConfirm', { action: labels[mode], count: count }))) {
            return;
        }
        formData.append('action', 'bulk_account_vehicles');
        formData.append('account_id', selectedAccountId);
        formData.append('mode', mode);
        postAdmin(formData)
            .then(function (data) {
                flash(t('rulesUpdated', { count: data.count }), false);
                window.setTimeout(function () { window.location.reload(); }, 450);
            })
            .catch(function (error) { flash(error.message, true); });
    }

    function resetOverrides() {
        if (!selectedAccountId) {
            flash(t('selectPlayer'), true);
            return;
        }
        if (!window.confirm(t('resetOverrides'))) {
            return;
        }
        var formData = new FormData();
        formData.append('action', 'reset_account_overrides');
        formData.append('account_id', selectedAccountId);
        postAdmin(formData)
            .then(function () { window.location.reload(); })
            .catch(function (error) { flash(error.message, true); });
    }

    function enableAllGlobal() {
        if (!window.confirm(t('enableAll'))) {
            return;
        }
        var formData = new FormData();
        formData.append('action', 'enable_all_global');
        postAdmin(formData)
            .then(function () { window.location.reload(); })
            .catch(function (error) { flash(error.message, true); });
    }

    function saveAccount(form) {
        var formData = new FormData(form);
        formData.append('action', 'save_account');
        postAdmin(formData)
            .then(function () { flash(t('accountUpdated'), false); })
            .catch(function (error) { flash(error.message, true); });
        return false;
    }

    function banUser(accountId, username) {
        if (!window.confirm(t('banConfirm', { username: username, accountId: accountId }))) {
            return;
        }
        var reason = window.prompt(t('banReason'), '') || '';
        var formData = new FormData();
        formData.append('action', 'ban_account');
        formData.append('account_id', accountId);
        formData.append('also_ip', '1');
        formData.append('reason', reason);
        postAdmin(formData)
            .then(function (data) {
                var msg = t('userBanned');
                if (data.banned_ip) {
                    msg += t('bannedIp', { ip: data.banned_ip });
                } else {
                    msg += t('unknownGameIp');
                }
                flash(msg, false);
                window.setTimeout(function () { window.location.reload(); }, 700);
            })
            .catch(function (error) { flash(error.message, true); });
    }

    function setUserPassword(accountId, username) {
        var password = window.prompt(t('newPassword', { username: username, accountId: accountId }), '');
        if (password === null) {
            return;
        }
        if (password.length < 6 || password.length > 128) {
            flash(t('passwordLength'), true);
            return;
        }
        var formData = new FormData();
        formData.append('action', 'set_password');
        formData.append('account_id', accountId);
        formData.append('password', password);
        postAdmin(formData)
            .then(function () { flash(t('passwordChanged', { username: username }), false); })
            .catch(function (error) { flash(error.message, true); });
    }

    function setUsername(accountId, username) {
        var next = window.prompt(t('newUsername', { username: username, accountId: accountId }), username);
        if (next === null) {
            return;
        }
        var trimmed = next.trim();
        if (trimmed.length < 3 || trimmed.length > 24) {
            flash(t('usernameLength'), true);
            return;
        }
        var formData = new FormData();
        formData.append('action', 'set_username');
        formData.append('account_id', accountId);
        formData.append('username', trimmed);
        postAdmin(formData)
            .then(function (data) {
                flash(t('usernameChanged', { username: username, next: data.username || trimmed }), false);
                window.setTimeout(function () { window.location.reload(); }, 700);
            })
            .catch(function (error) { flash(error.message, true); });
    }

    function getBanChecks() {
        return Array.prototype.slice.call(document.querySelectorAll('.js-ban-check'));
    }

    function updateBanSelCount() {
        var counter = document.getElementById('banSelCount');
        if (counter) {
            counter.textContent = getBanChecks().filter(function (checkbox) { return checkbox.checked; }).length;
        }
    }

    function toggleAllUsers(checked) {
        getBanChecks().forEach(function (checkbox) { checkbox.checked = checked; });
        var master = document.getElementById('banSelectAll');
        if (master) {
            master.checked = checked;
        }
        updateBanSelCount();
    }

    function banSelected() {
        var ids = getBanChecks()
            .filter(function (checkbox) { return checkbox.checked; })
            .map(function (checkbox) { return checkbox.value; });
        if (!ids.length) {
            flash(t('noneSelected'), true);
            return;
        }
        if (!window.confirm(t('bulkBanConfirm', { count: ids.length }))) {
            return;
        }
        var reason = window.prompt(t('bulkBanReason'), '') || '';
        var formData = new FormData();
        formData.append('action', 'bulk_ban_accounts');
        formData.append('also_ip', '1');
        formData.append('reason', reason);
        ids.forEach(function (id) { formData.append('account_ids[]', id); });
        postAdmin(formData)
            .then(function (data) {
                var msg = t('bulkBanned', { banned: data.banned, ips: data.banned_ips });
                if (data.skipped > 0) {
                    msg += t('bulkSkipped', { skipped: data.skipped });
                }
                flash(msg, false);
                window.setTimeout(function () { window.location.reload(); }, 800);
            })
            .catch(function (error) { flash(error.message, true); });
    }

    function banIpManual() {
        var ip = (document.getElementById('banIpAddr').value || '').trim();
        var reason = (document.getElementById('banIpReason').value || '').trim();
        if (!ip) {
            flash(t('ipRequired'), true);
            return;
        }
        var formData = new FormData();
        formData.append('action', 'ban_ip');
        formData.append('ip', ip);
        formData.append('reason', reason);
        postAdmin(formData)
            .then(function () {
                flash(t('ipBanned'), false);
                window.setTimeout(function () { window.location.reload(); }, 600);
            })
            .catch(function (error) { flash(error.message, true); });
    }

    function banMacManual() {
        var mac = (document.getElementById('banMacAddr').value || '').trim();
        var reason = (document.getElementById('banMacReason').value || '').trim();
        if (!mac) {
            flash(t('macRequired'), true);
            return;
        }
        var formData = new FormData();
        formData.append('action', 'ban_mac');
        formData.append('mac', mac);
        formData.append('reason', reason);
        postAdmin(formData)
            .then(function () {
                flash(t('macBanned'), false);
                window.setTimeout(function () { window.location.reload(); }, 600);
            })
            .catch(function (error) { flash(error.message, true); });
    }

    function unban(banId) {
        if (!window.confirm(t('unbanConfirm'))) {
            return;
        }
        var formData = new FormData();
        formData.append('action', 'unban');
        formData.append('ban_id', banId);
        postAdmin(formData)
            .then(function () {
                flash(t('unbanned'), false);
                window.setTimeout(function () { window.location.reload(); }, 600);
            })
            .catch(function (error) { flash(error.message, true); });
    }

    function syncMirrorControls(containerId, prefix) {
        var container = document.getElementById(containerId);
        var button = document.getElementById('btn-add-' + prefix);
        var count = container ? container.querySelectorAll('.mirror-row').length : 0;
        mirrorCount[prefix] = count;
        if (button) {
            button.hidden = count >= 5;
        }
        return count;
    }

    function removeMirrorRow(button) {
        var row = button.closest('.mirror-row');
        var container = row ? row.parentElement : null;
        if (!container || (container.id !== 'client-mirrors' && container.id !== 'patch-mirrors')) {
            return;
        }
        var prefix = container.id === 'client-mirrors' ? 'client' : 'patch';
        row.remove();
        syncMirrorControls(container.id, prefix);
    }

    function addMirrorRow(containerId, prefix) {
        if (syncMirrorControls(containerId, prefix) >= 5) return;
        var div = document.createElement('div');
        div.className = 'mirror-row';
        div.innerHTML = '<input type="text" name="' + prefix + '_name[]" placeholder="' + t('mirrorName') + '" class="form-control mirror-input mirror-input--name">' +
            '<input type="url" name="' + prefix + '_url[]" placeholder="https://..." class="form-control mirror-input mirror-input--url">' +
            '<label class="mirror-enabled"><input type="checkbox" name="' + prefix + '_enabled[]" checked> ' + t('mirrorActive') + '</label>' +
            '<button type="button" class="btn btn-secondary mirror-remove admin-row-action" onclick="removeMirrorRow(this)">' + t('mirrorRemove') + '</button>';
        document.getElementById(containerId).appendChild(div);
        syncMirrorControls(containerId, prefix);
    }

    function initActivityChart() {
        var chart = document.querySelector('[data-activity-chart]');
        if (!chart) {
            return;
        }

        var tooltip = chart.querySelector('[data-chart-tooltip]');
        var tooltipDate = chart.querySelector('[data-chart-tooltip-date]');
        var tooltipAccounts = chart.querySelector('[data-chart-tooltip-accounts]');
        var tooltipActions = chart.querySelector('[data-chart-tooltip-actions]');
        var activeDay = null;
        if (!tooltip || !tooltipDate || !tooltipAccounts || !tooltipActions) {
            return;
        }

        function dayFromEvent(event) {
            var target = event.target;
            if (!target || typeof target.closest !== 'function') {
                return null;
            }
            var day = target.closest('.activity-chart-day');
            return day && chart.contains(day) ? day : null;
        }

        function positionTooltip(day, event) {
            var chartRect = chart.getBoundingClientRect();
            var dayRect = day.getBoundingClientRect();
            var hasPointer = event && Number.isFinite(event.clientX) && Number.isFinite(event.clientY);
            var x = hasPointer ? event.clientX - chartRect.left : dayRect.left - chartRect.left + (dayRect.width / 2);
            var y = hasPointer ? event.clientY - chartRect.top - 16 : Math.min(chartRect.height * .52, 150);
            var halfWidth = tooltip.offsetWidth / 2;
            var tooltipHeight = tooltip.offsetHeight;
            var sideSafe = halfWidth + 12;

            x = Math.max(sideSafe, Math.min(chartRect.width - sideSafe, x));
            y = Math.max(tooltipHeight + 14, Math.min(chartRect.height - 12, y));
            tooltip.style.setProperty('--chart-tooltip-x', x + 'px');
            tooltip.style.setProperty('--chart-tooltip-y', y + 'px');
        }

        function showTooltip(day, event) {
            if (activeDay && activeDay !== day) {
                activeDay.classList.remove('is-active');
            }
            activeDay = day;
            activeDay.classList.add('is-active');
            tooltipDate.textContent = day.dataset.chartDate || '—';
            tooltipAccounts.textContent = day.dataset.chartAccounts || '0';
            tooltipActions.textContent = day.dataset.chartActions || '0';
            tooltip.setAttribute('aria-hidden', 'false');
            chart.classList.add('is-tooltip-visible');
            positionTooltip(day, event);
        }

        function hideTooltip(day) {
            if (day && activeDay && day !== activeDay) {
                return;
            }
            if (activeDay) {
                activeDay.classList.remove('is-active');
            }
            activeDay = null;
            chart.classList.remove('is-tooltip-visible');
            tooltip.setAttribute('aria-hidden', 'true');
        }

        chart.addEventListener('pointerover', function (event) {
            var day = dayFromEvent(event);
            if (day) {
                showTooltip(day, event);
            }
        });
        chart.addEventListener('pointermove', function (event) {
            var day = dayFromEvent(event);
            if (day && day === activeDay) {
                positionTooltip(day, event);
            }
        });
        chart.addEventListener('pointerout', function (event) {
            var day = dayFromEvent(event);
            if (!day || (event.relatedTarget && day.contains(event.relatedTarget))) {
                return;
            }
            if (document.activeElement !== day) {
                hideTooltip(day);
            }
        });
        chart.addEventListener('focusin', function (event) {
            var day = dayFromEvent(event);
            if (day) {
                showTooltip(day, null);
            }
        });
        chart.addEventListener('focusout', function (event) {
            var day = dayFromEvent(event);
            if (day && !day.contains(event.relatedTarget)) {
                hideTooltip(day);
            }
        });
        chart.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && activeDay) {
                activeDay.blur();
                hideTooltip(activeDay);
            }
        });
        window.addEventListener('resize', function () { hideTooltip(); });
    }

    var mobileSidebarQuery = window.matchMedia('(max-width: 820px)');

    function syncSidebarState(sidebar, toggle) {
        if (!mobileSidebarQuery.matches) {
            sidebar.classList.remove('is-open');
            sidebar.removeAttribute('aria-hidden');
            sidebar.removeAttribute('inert');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', t('navOpen'));
            return;
        }
        var open = sidebar.classList.contains('is-open');
        sidebar.setAttribute('aria-hidden', String(!open));
        sidebar.toggleAttribute('inert', !open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? t('navClose') : t('navOpen'));
    }

    function setSidebarOpen(sidebar, toggle, open) {
        sidebar.classList.toggle('is-open', open);
        syncSidebarState(sidebar, toggle);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initActivityChart();

        document.querySelectorAll('.js-player-mode').forEach(function (select) {
            select.dataset.lastValue = select.value;
        });

        syncMirrorControls('client-mirrors', 'client');
        syncMirrorControls('patch-mirrors', 'patch');

        var sidebar = document.getElementById('adminSidebar');
        var toggle = document.querySelector('[data-admin-sidebar-toggle]');
        var closers = document.querySelectorAll('[data-admin-sidebar-close], [data-admin-sidebar-dismiss]');
        if (sidebar && toggle) {
            syncSidebarState(sidebar, toggle);
            toggle.addEventListener('click', function () {
                setSidebarOpen(sidebar, toggle, !sidebar.classList.contains('is-open'));
            });
            closers.forEach(function (close) {
                close.addEventListener('click', function () {
                    setSidebarOpen(sidebar, toggle, false);
                    toggle.focus();
                });
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
                    setSidebarOpen(sidebar, toggle, false);
                    toggle.focus();
                }
            });
            sidebar.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    setSidebarOpen(sidebar, toggle, false);
                });
            });
            if (typeof mobileSidebarQuery.addEventListener === 'function') {
                mobileSidebarQuery.addEventListener('change', function () {
                    syncSidebarState(sidebar, toggle);
                });
            } else {
                mobileSidebarQuery.addListener(function () {
                    syncSidebarState(sidebar, toggle);
                });
            }
        }
    });

    window.toggleGlobal = toggleGlobal;
    window.setPlayerMode = setPlayerMode;
    window.bulkGlobal = bulkGlobal;
    window.bulkPlayer = bulkPlayer;
    window.resetOverrides = resetOverrides;
    window.enableAllGlobal = enableAllGlobal;
    window.saveAccount = saveAccount;
    window.banUser = banUser;
    window.setUserPassword = setUserPassword;
    window.setUsername = setUsername;
    window.updateBanSelCount = updateBanSelCount;
    window.toggleAllUsers = toggleAllUsers;
    window.banSelected = banSelected;
    window.banIpManual = banIpManual;
    window.banMacManual = banMacManual;
    window.unban = unban;
    window.removeMirrorRow = removeMirrorRow;
    window.addMirrorRow = addMirrorRow;
    window.mirrorCount = mirrorCount;
})();
