/* Публичная интеграция Manifold Markets для @toffexcrf. */
(function () {
    'use strict';

    var root = document.querySelector('[data-markets-root]');
    if (!root || !window.fetch || !window.Promise) return;

    var API_ROOT = 'https://api.manifold.markets/v0';
    var username = root.getAttribute('data-market-username') || 'toffexcrf';
    var language = (document.documentElement.getAttribute('lang') || '').toLowerCase().split('-')[0];
    if (language !== 'uk' && language !== 'en') language = 'ru';
    var locale = language === 'en' ? 'en-US' : (language === 'uk' ? 'uk-UA' : 'ru-RU');
    var strings = {
        ru: {
            balance: 'Баланс: Ṁ{value}',
            marketsError: 'Manifold временно недоступен. Проверьте подключение и повторите попытку.',
            userError: 'Пользователь @{username} не найден.',
            marketLoadError: 'Не удалось загрузить выбранный рынок.',
            openOrder: 'Лимитный ордер',
            cancelled: 'Отменено',
            redemption: 'Погашение',
            sale: 'Продажа',
            trade: 'Ставка',
            closes: 'Закрытие: {date}',
            resolved: 'Завершён',
            open: 'Открыт',
            closed: 'Закрыт',
            result: 'Результат: {value}',
            questionFallback: 'Рынок {id}',
            probabilityMove: '{before}% → {after}%',
            probabilitySingle: '{value}%',
            probabilityNone: 'без изменения вероятности',
            amount: 'Ṁ{value}',
            orderAmount: 'ордер Ṁ{value}',
            traders: '{value} трейдеров',
            yes: 'ДА',
            no: 'НЕТ',
            probabilityLabel: 'вероятность',
            leadingAnswer: 'лидирующий вариант',
            currentValue: 'текущее значение',
            totalVotes: 'всего голосов',
            bounty: 'награда',
            binaryMarket: 'Рынок ДА / НЕТ',
            binaryHint: 'Выберите сторону — ставка откроется на Manifold.',
            singleChoice: 'Один победитель',
            singleChoiceHint: 'Варианты связаны между собой и в сумме дают 100%.',
            independentChoice: 'Несколько победителей',
            independentChoiceHint: 'Каждый вариант оценивается отдельно и может победить независимо.',
            freeResponse: 'Свободные ответы',
            freeResponseHint: 'Ответы добавляют участники, после чего на них можно делать ставки.',
            poll: 'Опрос',
            pollHint: 'Выберите вариант и подтвердите голос на Manifold.',
            numericMarket: 'Числовой прогноз',
            numericHint: 'Укажите своё числовое значение на странице рынка.',
            bountyQuestion: 'Вопрос с наградой',
            bountyHint: 'Добавьте полезный ответ и претендуйте на награду.',
            votingLoading: 'Загружаем варианты голосования…',
            votingUnavailable: 'Варианты временно не загрузились.',
            noAnswers: 'Варианты пока не добавлены.',
            votes: '{value} голосов',
            placeBet: 'Сделать ставку ↗',
            viewResult: 'Посмотреть результат ↗',
            vote: 'Голосовать ↗',
            enterForecast: 'Указать прогноз ↗',
            addAnswer: 'Добавить ответ ↗',
            addOption: 'Добавить вариант ↗',
            openVoting: 'Открыть голосование ↗',
            minValue: 'минимум',
            maxValue: 'максимум',
            choicesShort: 'ВАРИАНТЫ',
            pollShort: 'ОПРОС',
            bountyShort: 'ОТВЕТЫ',
            typeBinary: 'БИНАРНЫЙ',
            typeFreeResponse: 'СВОБОДНЫЕ ОТВЕТЫ',
            typeMultipleChoice: 'МНОЖЕСТВЕННЫЙ ВЫБОР',
            typePoll: 'ОПРОС',
            typeNumeric: 'ЧИСЛОВОЙ',
            typePseudoNumeric: 'ПСЕВДО-ЧИСЛОВОЙ',
            typeBounty: 'НАГРАДА',
            typeMarket: 'РЫНОК',
            typeRaw: 'ТИП: {value}',
            activityToday: 'сегодня',
            activityYesterday: 'вчера'
        },
        uk: {
            balance: 'Баланс: Ṁ{value}',
            marketsError: 'Manifold тимчасово недоступний. Перевірте підключення та повторіть спробу.',
            userError: 'Користувача @{username} не знайдено.',
            marketLoadError: 'Не вдалося завантажити вибраний ринок.',
            openOrder: 'Лімітний ордер',
            cancelled: 'Скасовано',
            redemption: 'Погашення',
            sale: 'Продаж',
            trade: 'Ставка',
            closes: 'Закриття: {date}',
            resolved: 'Завершено',
            open: 'Відкрито',
            closed: 'Закрито',
            result: 'Результат: {value}',
            questionFallback: 'Ринок {id}',
            probabilityMove: '{before}% → {after}%',
            probabilitySingle: '{value}%',
            probabilityNone: 'без зміни ймовірності',
            amount: 'Ṁ{value}',
            orderAmount: 'ордер Ṁ{value}',
            traders: '{value} трейдерів',
            yes: 'ТАК',
            no: 'НІ',
            probabilityLabel: 'ймовірність',
            leadingAnswer: 'варіант-лідер',
            currentValue: 'поточне значення',
            totalVotes: 'усього голосів',
            bounty: 'винагорода',
            binaryMarket: 'Ринок ТАК / НІ',
            binaryHint: 'Оберіть сторону — ставка відкриється на Manifold.',
            singleChoice: 'Один переможець',
            singleChoiceHint: 'Варіанти пов’язані між собою та в сумі дають 100%.',
            independentChoice: 'Кілька переможців',
            independentChoiceHint: 'Кожен варіант оцінюється окремо й може перемогти незалежно.',
            freeResponse: 'Вільні відповіді',
            freeResponseHint: 'Відповіді додають учасники, після чого на них можна робити ставки.',
            poll: 'Опитування',
            pollHint: 'Оберіть варіант і підтвердьте голос на Manifold.',
            numericMarket: 'Числовий прогноз',
            numericHint: 'Укажіть своє числове значення на сторінці ринку.',
            bountyQuestion: 'Запитання з винагородою',
            bountyHint: 'Додайте корисну відповідь і претендуйте на винагороду.',
            votingLoading: 'Завантажуємо варіанти голосування…',
            votingUnavailable: 'Варіанти тимчасово не завантажилися.',
            noAnswers: 'Варіанти ще не додані.',
            votes: '{value} голосів',
            placeBet: 'Зробити ставку ↗',
            viewResult: 'Переглянути результат ↗',
            vote: 'Голосувати ↗',
            enterForecast: 'Указати прогноз ↗',
            addAnswer: 'Додати відповідь ↗',
            addOption: 'Додати варіант ↗',
            openVoting: 'Відкрити голосування ↗',
            minValue: 'мінімум',
            maxValue: 'максимум',
            choicesShort: 'ВАРІАНТИ',
            pollShort: 'ОПИТ.',
            bountyShort: 'ВІДПОВІДІ',
            typeBinary: 'БІНАРНИЙ',
            typeFreeResponse: 'ВІЛЬНІ ВІДПОВІДІ',
            typeMultipleChoice: 'МНОЖИННИЙ ВИБІР',
            typePoll: 'ОПИТУВАННЯ',
            typeNumeric: 'ЧИСЛОВИЙ',
            typePseudoNumeric: 'ПСЕВДОЧИСЛОВИЙ',
            typeBounty: 'ВИНАГОРОДА',
            typeMarket: 'РИНОК',
            typeRaw: 'ТИП: {value}',
            activityToday: 'сьогодні',
            activityYesterday: 'вчора'
        },
        en: {
            balance: 'Balance: Ṁ{value}',
            marketsError: 'Manifold is temporarily unavailable. Check your connection and try again.',
            userError: 'User @{username} not found.',
            marketLoadError: 'Failed to load the selected market.',
            openOrder: 'Limit order',
            cancelled: 'Cancelled',
            redemption: 'Redemption',
            sale: 'Sale',
            trade: 'Trade',
            closes: 'Closes: {date}',
            resolved: 'Resolved',
            open: 'Open',
            closed: 'Closed',
            result: 'Result: {value}',
            questionFallback: 'Market {id}',
            probabilityMove: '{before}% → {after}%',
            probabilitySingle: '{value}%',
            probabilityNone: 'probability unchanged',
            amount: 'Ṁ{value}',
            orderAmount: 'order Ṁ{value}',
            traders: '{value} traders',
            yes: 'YES',
            no: 'NO',
            probabilityLabel: 'probability',
            leadingAnswer: 'leading answer',
            currentValue: 'current value',
            totalVotes: 'total votes',
            bounty: 'bounty',
            binaryMarket: 'YES / NO market',
            binaryHint: 'Choose a side — the bet will open on Manifold.',
            singleChoice: 'Single winner',
            singleChoiceHint: 'The options are linked and add up to 100% in total.',
            independentChoice: 'Multiple winners',
            independentChoiceHint: 'Each option is evaluated separately and can win independently.',
            freeResponse: 'Free response',
            freeResponseHint: 'Participants add answers, after which you can bet on them.',
            poll: 'Poll',
            pollHint: 'Choose an option and confirm your vote on Manifold.',
            numericMarket: 'Numeric forecast',
            numericHint: 'Enter your numeric value on the market page.',
            bountyQuestion: 'Bounty question',
            bountyHint: 'Add a useful answer and claim the bounty.',
            votingLoading: 'Loading voting options…',
            votingUnavailable: 'Voting options are temporarily unavailable.',
            noAnswers: 'No options have been added yet.',
            votes: '{value} votes',
            placeBet: 'Place bet ↗',
            viewResult: 'View result ↗',
            vote: 'Vote ↗',
            enterForecast: 'Enter forecast ↗',
            addAnswer: 'Add answer ↗',
            addOption: 'Add option ↗',
            openVoting: 'Open voting ↗',
            minValue: 'minimum',
            maxValue: 'maximum',
            choicesShort: 'OPTIONS',
            pollShort: 'POLL',
            bountyShort: 'ANSWERS',
            typeBinary: 'BINARY',
            typeFreeResponse: 'FREE RESPONSE',
            typeMultipleChoice: 'MULTIPLE CHOICE',
            typePoll: 'POLL',
            typeNumeric: 'NUMERIC',
            typePseudoNumeric: 'PSEUDO NUMERIC',
            typeBounty: 'BOUNTY',
            typeMarket: 'MARKET',
            typeRaw: 'TYPE: {value}',
            activityToday: 'today',
            activityYesterday: 'yesterday'
        }
    };
    var state = {
        user: null,
        markets: [],
        bets: [],
        marketMap: {},
        fullMarketIds: {},
        fullMarketRequests: {},
        selectedId: '',
        selectedMarketBets: [],
        chartRequest: 0,
        marketRequest: 0,
        filter: 'all'
    };

    function t(key, values) {
        var text = strings[language][key] || strings.ru[key] || key;
        Object.keys(values || {}).forEach(function (name) {
            text = text.replace('{' + name + '}', values[name]);
        });
        return text;
    }

    function marketTypeLabel(type) {
        var normalized = String(type || 'MARKET').trim().toUpperCase();
        var keys = {
            BINARY: 'typeBinary',
            FREE_RESPONSE: 'typeFreeResponse',
            'FREE RESPONSE': 'typeFreeResponse',
            MULTIPLE_CHOICE: 'typeMultipleChoice',
            'MULTIPLE CHOICE': 'typeMultipleChoice',
            POLL: 'typePoll',
            NUMERIC: 'typeNumeric',
            PSEUDO_NUMERIC: 'typePseudoNumeric',
            'PSEUDO NUMERIC': 'typePseudoNumeric',
            BOUNTY: 'typeBounty',
            BOUNTIED_QUESTION: 'typeBounty',
            MARKET: 'typeMarket'
        };
        return keys[normalized]
            ? t(keys[normalized])
            : t('typeRaw', { value: normalized.replace(/_/g, ' ') });
    }

    function one(selector) {
        return root.querySelector(selector);
    }

    function all(selector) {
        return Array.prototype.slice.call(root.querySelectorAll(selector));
    }

    function setText(selector, value) {
        var node = one(selector);
        if (node) node.textContent = value;
    }

    function formatNumber(value, maximumFractionDigits) {
        var number = Number(value);
        if (!isFinite(number)) return '0';
        return number.toLocaleString(locale, {
            maximumFractionDigits: maximumFractionDigits == null ? 0 : maximumFractionDigits
        });
    }

    function formatProbability(value) {
        var number = Number(value);
        if (!isFinite(number)) return '—';
        return Math.round(number * 100) + '%';
    }

    function formatDate(timestamp, withTime) {
        var date = new Date(Number(timestamp));
        if (isNaN(date.getTime())) return '—';
        return new Intl.DateTimeFormat(locale, {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: withTime ? '2-digit' : undefined,
            minute: withTime ? '2-digit' : undefined,
            timeZone: 'Europe/Kyiv'
        }).format(date);
    }

    function safeManifoldUrl(value) {
        return typeof value === 'string' && value.indexOf('https://manifold.markets/') === 0
            ? value
            : 'https://manifold.markets/' + encodeURIComponent(username);
    }

    function safeImageUrl(value) {
        return typeof value === 'string' && value.indexOf('https://') === 0 ? value : 'images/logo.png';
    }

    function requestJson(url) {
        return fetch(url, { headers: { Accept: 'application/json' } }).then(function (response) {
            if (!response.ok) {
                var error = new Error('HTTP ' + response.status);
                error.status = response.status;
                throw error;
            }
            return response.json();
        });
    }

    function mergeMarket(market) {
        if (!market || !market.id) return market;
        var current = state.marketMap[market.id] || {};
        Object.keys(market).forEach(function (key) {
            current[key] = market[key];
        });
        state.marketMap[market.id] = current;
        state.markets = state.markets.map(function (item) {
            return item.id === market.id ? current : item;
        });
        return current;
    }

    function ensureFullMarket(marketId) {
        if (state.fullMarketIds[marketId]) {
            return Promise.resolve(state.marketMap[marketId]);
        }
        if (state.fullMarketRequests[marketId]) {
            return state.fullMarketRequests[marketId];
        }

        state.fullMarketRequests[marketId] = requestJson(API_ROOT + '/market/' + encodeURIComponent(marketId))
            .then(function (market) {
                state.fullMarketIds[marketId] = true;
                delete state.fullMarketRequests[marketId];
                return mergeMarket(market);
            })
            .catch(function (error) {
                delete state.fullMarketRequests[marketId];
                throw error;
            });
        return state.fullMarketRequests[marketId];
    }

    function fetchAllMarkets(userId) {
        var items = [];
        var ids = {};
        var cursors = {};

        function next(before) {
            var url = API_ROOT + '/markets?limit=1000&userId=' + encodeURIComponent(userId);
            if (before) url += '&before=' + encodeURIComponent(before);

            return requestJson(url).then(function (page) {
                if (!Array.isArray(page)) throw new Error('Invalid markets response');
                page.forEach(function (market) {
                    if (market && market.id && !ids[market.id]) {
                        ids[market.id] = true;
                        items.push(market);
                    }
                });

                if (page.length < 1000) return items;
                var cursor = page[page.length - 1] && page[page.length - 1].id;
                if (!cursor || cursors[cursor]) return items;
                cursors[cursor] = true;
                return next(cursor);
            });
        }

        return next('');
    }

    function fetchAllBets() {
        var items = [];
        var ids = {};
        var cursors = {};

        function next(before) {
            var url = API_ROOT + '/bets?limit=1000&username=' + encodeURIComponent(username);
            if (before) url += '&before=' + encodeURIComponent(before);

            return requestJson(url).then(function (page) {
                if (!Array.isArray(page)) throw new Error('Invalid bets response');
                page.forEach(function (bet) {
                    var id = bet && (bet.id || bet.betId);
                    if (id && !ids[id]) {
                        ids[id] = true;
                        items.push(bet);
                    }
                });

                if (page.length < 1000) return items;
                var last = page[page.length - 1];
                var cursor = last && (last.id || last.betId);
                if (!cursor || cursors[cursor]) return items;
                cursors[cursor] = true;
                return next(cursor);
            });
        }

        return next('');
    }

    function fetchMissingMarkets() {
        var pending = [];
        var scheduled = {};

        state.bets.forEach(function (bet) {
            var id = bet && bet.contractId;
            if (id && !state.marketMap[id] && !scheduled[id]) {
                scheduled[id] = true;
                pending.push(id);
            }
        });

        var index = 0;
        function worker() {
            if (index >= pending.length) return Promise.resolve();
            var id = pending[index++];
            return requestJson(API_ROOT + '/market/' + encodeURIComponent(id))
                .then(function (market) {
                    if (market && market.id) {
                        mergeMarket(market);
                        state.fullMarketIds[market.id] = true;
                    }
                })
                .catch(function () {
                    state.marketMap[id] = { id: id, question: t('questionFallback', { id: id }) };
                })
                .then(worker);
        }

        var workers = [];
        var workerCount = Math.min(6, pending.length);
        for (var i = 0; i < workerCount; i++) workers.push(worker());
        return Promise.all(workers);
    }

    function loadSelectedMarketBets(marketId) {
        var requestId = ++state.chartRequest;
        return requestJson(API_ROOT + '/bets?limit=160&contractId=' + encodeURIComponent(marketId))
            .then(function (bets) {
                if (requestId !== state.chartRequest || !Array.isArray(bets)) return;
                state.selectedMarketBets = bets;
                drawChart();
            })
            .catch(function () {
                if (requestId !== state.chartRequest) return;
                state.selectedMarketBets = state.bets.filter(function (bet) {
                    return bet.contractId === marketId;
                });
                drawChart();
            });
    }

    function renderProfile() {
        if (!state.user) return;
        var avatar = one('[data-market-avatar]');
        var profile = one('[data-market-profile]');
        if (avatar) avatar.src = safeImageUrl(state.user.avatarUrl);
        if (profile) profile.href = safeManifoldUrl(state.user.url);
        setText('[data-market-display-name]', '@' + (state.user.username || username));
        setText('[data-market-balance]', t('balance', { value: formatNumber(state.user.balance, 0) }));
    }

    function renderSummary() {
        var marketIds = {};
        state.bets.forEach(function (bet) {
            if (bet && bet.contractId) marketIds[bet.contractId] = true;
        });

        setText('[data-market-count]', formatNumber(state.markets.length));
        setText('[data-bet-count]', formatNumber(state.bets.length));
        setText('[data-market-contract-count]', formatNumber(Object.keys(marketIds).length));
        setText('[data-market-updated]', state.bets.length ? formatDate(state.bets[0].createdTime, false) : '—');
    }

    function marketStatus(market) {
        if (market.isResolved) {
            return t('result', { value: market.resolution || '—' });
        }
        if (market.closeTime && Number(market.closeTime) < Date.now()) return t('closed');
        return t('open');
    }

    function marketListMetric(market) {
        var type = String(market.outcomeType || '').toUpperCase();
        if (type === 'MULTIPLE_CHOICE' || type === 'FREE_RESPONSE') {
            return Array.isArray(market.answers) && market.answers.length
                ? formatNumber(market.answers.length)
                : t('choicesShort');
        }
        if (type === 'POLL') return t('pollShort');
        if (type === 'BOUNTIED_QUESTION') return t('bountyShort');
        if (type === 'NUMERIC' || type === 'PSEUDO_NUMERIC') {
            return isFinite(Number(market.value)) ? formatNumber(market.value, 2) : '—';
        }
        return formatProbability(market.probability);
    }

    function selectedMarketMetric(market) {
        var type = String(market.outcomeType || '').toUpperCase();
        var answers;
        var probabilities;
        var options;
        var total;

        if (type === 'MULTIPLE_CHOICE' || type === 'FREE_RESPONSE') {
            answers = Array.isArray(market.answers) ? market.answers : [];
            probabilities = answers
                .map(function (answer) { return Number(answer.probability); })
                .filter(function (probability) { return isFinite(probability); });
            if (probabilities.length) {
                return {
                    value: formatProbability(Math.max.apply(Math, probabilities)),
                    label: t('leadingAnswer')
                };
            }
            return { value: formatNumber(answers.length), label: t('choicesShort').toLowerCase() };
        }

        if (type === 'POLL') {
            options = Array.isArray(market.options) ? market.options : [];
            total = options.reduce(function (sum, option) {
                return sum + Math.max(0, Number(option.votes) || 0);
            }, 0);
            return { value: formatNumber(total), label: t('totalVotes') };
        }

        if (type === 'NUMERIC' || type === 'PSEUDO_NUMERIC') {
            return {
                value: isFinite(Number(market.value)) ? formatNumber(market.value, 2) : '—',
                label: t('currentValue')
            };
        }

        if (type === 'BOUNTIED_QUESTION') {
            return {
                value: 'Ṁ' + formatNumber(market.bountyLeft == null ? market.totalBounty : market.bountyLeft),
                label: t('bounty')
            };
        }

        return { value: formatProbability(market.probability), label: t('probabilityLabel') };
    }

    function renderMarkets() {
        var list = one('[data-market-list]');
        if (!list) return;
        list.textContent = '';
        setText('[data-market-list-count]', state.markets.length);
        one('[data-market-list-empty]').hidden = state.markets.length !== 0;

        state.markets.forEach(function (market) {
            var button = document.createElement('button');
            var top = document.createElement('span');
            var question = document.createElement('strong');
            var probability = document.createElement('b');
            var meta = document.createElement('span');
            var traders = document.createElement('small');
            var volume = document.createElement('small');

            button.type = 'button';
            button.className = 'market-list-item';
            button.setAttribute('role', 'option');
            button.setAttribute('data-market-id', market.id);
            button.setAttribute('aria-selected', market.id === state.selectedId ? 'true' : 'false');
            if (market.id === state.selectedId) button.classList.add('is-selected');

            top.className = 'market-list-item-top';
            question.textContent = market.question || t('questionFallback', { id: market.id });
            probability.className = 'market-list-probability';
            probability.textContent = marketListMetric(market);
            if (market.probability != null && isFinite(Number(market.probability))) {
                if (Number(market.probability) >= 0.6) probability.classList.add('is-high');
                if (Number(market.probability) < 0.4) probability.classList.add('is-low');
            }
            top.appendChild(question);
            top.appendChild(probability);

            meta.className = 'market-list-meta';
            traders.textContent = t('traders', { value: formatNumber(market.uniqueBettorCount) });
            volume.textContent = 'Ṁ' + formatNumber(market.volume);
            meta.appendChild(traders);
            meta.appendChild(volume);

            button.appendChild(top);
            button.appendChild(meta);
            list.appendChild(button);
        });
    }

    function appendTextElement(parent, tagName, className, value) {
        var node = document.createElement(tagName);
        if (className) node.className = className;
        node.textContent = value;
        parent.appendChild(node);
        return node;
    }

    function createVoteLink(marketUrl, label, variant, ariaLabel) {
        var link = document.createElement('a');
        link.className = 'market-vote-action market-vote-action--' + variant;
        link.href = marketUrl;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.textContent = label;
        if (ariaLabel) link.setAttribute('aria-label', ariaLabel);
        return link;
    }

    function appendVotingHeader(panel, typeLabel, title, hint) {
        var header = document.createElement('header');
        var copy = document.createElement('div');
        header.className = 'market-voting-header';
        appendTextElement(header, 'span', 'market-voting-type', typeLabel);
        appendTextElement(copy, 'h3', '', title);
        appendTextElement(copy, 'p', '', hint);
        header.appendChild(copy);
        panel.appendChild(header);
    }

    function renderBinaryVoting(panel, market, marketUrl) {
        var choices = document.createElement('div');
        var activeLabel = market.isResolved ? t('viewResult') : t('placeBet');
        choices.className = 'market-binary-choices';

        [
            { label: t('yes'), probability: market.probability, variant: 'yes' },
            { label: t('no'), probability: 1 - Number(market.probability), variant: 'no' }
        ].forEach(function (choice) {
            var link = document.createElement('a');
            link.className = 'market-binary-choice market-binary-choice--' + choice.variant;
            link.href = marketUrl;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            appendTextElement(link, 'span', '', choice.label);
            appendTextElement(link, 'strong', '', formatProbability(choice.probability));
            appendTextElement(link, 'small', '', activeLabel);
            choices.appendChild(link);
        });

        appendVotingHeader(panel, marketTypeLabel('BINARY'), t('binaryMarket'), t('binaryHint'));
        panel.appendChild(choices);
    }

    function renderAnswerVoting(panel, market, marketUrl) {
        var answers = Array.isArray(market.answers) ? market.answers : [];
        var isFreeResponse = market.outcomeType === 'FREE_RESPONSE';
        var dependent = market.shouldAnswersSumToOne !== false;
        var list = document.createElement('div');
        list.className = 'market-answer-list';

        appendVotingHeader(
            panel,
            isFreeResponse ? marketTypeLabel('FREE_RESPONSE') : marketTypeLabel('MULTIPLE_CHOICE'),
            isFreeResponse ? t('freeResponse') : t(dependent ? 'singleChoice' : 'independentChoice'),
            isFreeResponse ? t('freeResponseHint') : t(dependent ? 'singleChoiceHint' : 'independentChoiceHint')
        );

        if (!answers.length) {
            appendTextElement(list, 'p', 'market-voting-empty', t('noAnswers'));
        }

        answers.forEach(function (answer, index) {
            var row = document.createElement('article');
            var copy = document.createElement('div');
            var actions = document.createElement('div');
            var probability = Number(answer.probability);
            var progress = isFinite(probability) ? Math.max(0, Math.min(100, probability * 100)) : 0;
            var answerText = answer.text || t('questionFallback', { id: answer.id || index + 1 });

            row.className = 'market-answer-row';
            row.setAttribute('data-market-answer-id', answer.id || String(index));
            row.style.setProperty('--answer-progress', progress + '%');

            copy.className = 'market-answer-copy';
            appendTextElement(copy, 'strong', 'market-answer-probability', formatProbability(probability));
            appendTextElement(copy, 'span', 'market-answer-text', answerText);

            actions.className = 'market-answer-actions';
            actions.appendChild(createVoteLink(
                marketUrl,
                t('yes'),
                'yes',
                t('yes') + ': ' + answerText
            ));
            actions.appendChild(createVoteLink(
                marketUrl,
                t('no'),
                'no',
                t('no') + ': ' + answerText
            ));

            row.appendChild(copy);
            row.appendChild(actions);
            list.appendChild(row);
        });
        panel.appendChild(list);

        if (market.addAnswersMode === 'ANYONE') {
            var footer = document.createElement('div');
            footer.className = 'market-voting-footer';
            footer.appendChild(createVoteLink(
                marketUrl,
                isFreeResponse ? t('addAnswer') : t('addOption'),
                'primary'
            ));
            panel.appendChild(footer);
        }
    }

    function renderPollVoting(panel, market, marketUrl) {
        var options = Array.isArray(market.options) ? market.options : [];
        var total = options.reduce(function (sum, option) {
            return sum + Math.max(0, Number(option.votes) || 0);
        }, 0);
        var list = document.createElement('div');
        list.className = 'market-poll-list';

        appendVotingHeader(panel, marketTypeLabel('POLL'), t('poll'), t('pollHint'));
        if (!options.length) appendTextElement(list, 'p', 'market-voting-empty', t('noAnswers'));

        options.forEach(function (option, index) {
            var row = document.createElement('article');
            var copy = document.createElement('div');
            var count = Math.max(0, Number(option.votes) || 0);
            var percent = total ? Math.round(count / total * 100) : 0;
            row.className = 'market-poll-row';
            copy.className = 'market-poll-copy';
            appendTextElement(copy, 'strong', '', option.text || String(index + 1));
            appendTextElement(copy, 'span', '', percent + '% · ' + t('votes', { value: formatNumber(count) }));
            row.appendChild(copy);
            row.appendChild(createVoteLink(marketUrl, t('vote'), 'primary'));
            list.appendChild(row);
        });
        panel.appendChild(list);
    }

    function renderNumericVoting(panel, market, marketUrl) {
        var scale = document.createElement('div');
        var range = document.createElement('div');
        var current = document.createElement('strong');
        var min = isFinite(Number(market.min)) ? formatNumber(market.min, 2) : '—';
        var max = isFinite(Number(market.max)) ? formatNumber(market.max, 2) : '—';
        var value = isFinite(Number(market.value)) ? formatNumber(market.value, 2) : '—';

        appendVotingHeader(panel, marketTypeLabel(market.outcomeType || 'NUMERIC'), t('numericMarket'), t('numericHint'));
        scale.className = 'market-numeric-scale';
        range.className = 'market-numeric-range';
        appendTextElement(range, 'span', '', t('minValue') + ' ' + min);
        appendTextElement(range, 'span', '', t('maxValue') + ' ' + max);
        current.className = 'market-numeric-current';
        current.textContent = value;
        scale.appendChild(current);
        scale.appendChild(range);
        scale.appendChild(createVoteLink(marketUrl, t('enterForecast'), 'primary'));
        panel.appendChild(scale);
    }

    function renderBountyVoting(panel, market, marketUrl) {
        var body = document.createElement('div');
        var amount = market.bountyLeft == null ? market.totalBounty : market.bountyLeft;
        appendVotingHeader(panel, marketTypeLabel('BOUNTY'), t('bountyQuestion'), t('bountyHint'));
        body.className = 'market-bounty-action';
        appendTextElement(body, 'strong', '', 'Ṁ' + formatNumber(amount));
        body.appendChild(createVoteLink(marketUrl, t('addAnswer'), 'primary'));
        panel.appendChild(body);
    }

    function renderGenericVoting(panel, market, marketUrl) {
        appendVotingHeader(
            panel,
            marketTypeLabel(market.outcomeType || 'MARKET'),
            t('openVoting').replace(' ↗', ''),
            t('binaryHint')
        );
        panel.appendChild(createVoteLink(marketUrl, t('openVoting'), 'primary'));
    }

    function renderVoting(market) {
        var panel = one('[data-market-voting]');
        var type = String(market.outcomeType || 'BINARY').toUpperCase();
        var marketUrl = safeManifoldUrl(market.url);
        if (!panel) return;
        panel.textContent = '';
        panel.setAttribute('data-market-vote-kind', type);

        if (
            !state.fullMarketIds[market.id]
            && (type === 'MULTIPLE_CHOICE' || type === 'FREE_RESPONSE' || type === 'POLL')
        ) {
            appendTextElement(panel, 'p', 'market-voting-loading', t('votingLoading'));
            return;
        }

        if (type === 'BINARY') {
            renderBinaryVoting(panel, market, marketUrl);
        } else if (type === 'MULTIPLE_CHOICE' || type === 'FREE_RESPONSE') {
            renderAnswerVoting(panel, market, marketUrl);
        } else if (type === 'POLL') {
            renderPollVoting(panel, market, marketUrl);
        } else if (type === 'NUMERIC' || type === 'PSEUDO_NUMERIC') {
            renderNumericVoting(panel, market, marketUrl);
        } else if (type === 'BOUNTIED_QUESTION') {
            renderBountyVoting(panel, market, marketUrl);
        } else {
            renderGenericVoting(panel, market, marketUrl);
        }
    }

    function renderVotingError(market) {
        var panel = one('[data-market-voting]');
        if (!panel || !market) return;
        panel.textContent = '';
        appendTextElement(panel, 'p', 'market-voting-empty', t('votingUnavailable'));
        panel.appendChild(createVoteLink(safeManifoldUrl(market.url), t('openVoting'), 'primary'));
    }

    function renderSelectedMarket() {
        var market = state.marketMap[state.selectedId];
        var empty = one('[data-market-empty]');
        var detail = one('[data-market-detail]');
        if (!market) {
            if (empty) empty.hidden = false;
            if (detail) detail.hidden = true;
            return;
        }

        if (empty) empty.hidden = true;
        if (detail) detail.hidden = false;

        var avatar = one('[data-selected-avatar]');
        var marketUrl = safeManifoldUrl(market.url);
        var metric = selectedMarketMetric(market);
        if (avatar) avatar.src = safeImageUrl(market.creatorAvatarUrl || (state.user && state.user.avatarUrl));
        setText('[data-selected-creator]', '@' + (market.creatorUsername || username));
        setText('[data-selected-close]', market.closeTime ? t('closes', { date: formatDate(market.closeTime, false) }) : '—');
        setText('[data-selected-state]', marketStatus(market));
        setText('[data-selected-question]', market.question || t('questionFallback', { id: market.id }));
        setText('[data-selected-probability]', metric.value);
        setText('[data-selected-probability-label]', metric.label);
        setText('[data-selected-traders]', formatNumber(market.uniqueBettorCount));
        setText('[data-selected-volume]', 'Ṁ' + formatNumber(market.volume));
        setText('[data-selected-liquidity]', 'Ṁ' + formatNumber(market.totalLiquidity));
        setText('[data-market-address]', marketUrl.replace('https://', ''));

        var links = [one('[data-market-open]'), one('[data-selected-link]')];
        links.forEach(function (link) {
            if (link) link.href = marketUrl;
        });

        renderVoting(market);
    }

    function selectMarket(marketId) {
        var requestId = ++state.marketRequest;
        state.selectedId = marketId || '';
        renderMarkets();
        renderSelectedMarket();
        if (!state.selectedId) return;

        loadSelectedMarketBets(state.selectedId);
        ensureFullMarket(state.selectedId)
            .then(function () {
                if (requestId !== state.marketRequest || state.selectedId !== marketId) return;
                renderMarkets();
                renderSelectedMarket();
                drawChart();
            })
            .catch(function () {
                if (requestId !== state.marketRequest || state.selectedId !== marketId) return;
                renderVotingError(state.marketMap[marketId]);
            });
    }

    function probabilityText(bet) {
        var before = Number(bet.probBefore);
        var after = Number(bet.probAfter);
        if (isFinite(before) && isFinite(after)) {
            return t('probabilityMove', {
                before: Math.round(before * 100),
                after: Math.round(after * 100)
            });
        }
        if (isFinite(after)) return t('probabilitySingle', { value: Math.round(after * 100) });
        if (isFinite(before)) return t('probabilitySingle', { value: Math.round(before * 100) });
        return t('probabilityNone');
    }

    function betType(bet) {
        if (bet.isRedemption) return t('redemption');
        if (bet.isCancelled) return t('cancelled');
        if (bet.isFilled === false) return t('openOrder');
        if (Number(bet.amount) < 0) return t('sale');
        return t('trade');
    }

    function betAmount(bet) {
        var amount = Math.abs(Number(bet.amount));
        var orderAmount = Math.abs(Number(bet.orderAmount));
        if (amount > 0) return t('amount', { value: formatNumber(amount, 2) });
        if (orderAmount > 0) return t('orderAmount', { value: formatNumber(orderAmount, 2) });
        return 'Ṁ0';
    }

    function renderBets() {
        var list = one('[data-bet-list]');
        if (!list) return;
        list.textContent = '';
        one('[data-bet-list-empty]').hidden = state.bets.length !== 0;

        var counts = { all: state.bets.length, YES: 0, NO: 0 };
        state.bets.forEach(function (bet) {
            if (counts[bet.outcome] != null) counts[bet.outcome]++;

            var market = state.marketMap[bet.contractId] || {};
            var row = document.createElement('article');
            var side = document.createElement('span');
            var main = document.createElement('div');
            var title = document.createElement('a');
            var meta = document.createElement('span');
            var values = document.createElement('div');
            var amount = document.createElement('strong');
            var probability = document.createElement('span');
            var time = document.createElement('time');

            row.className = 'market-bet-row';
            row.setAttribute('data-bet-outcome', bet.outcome || '');

            side.className = 'market-bet-side market-bet-side--' + (bet.outcome === 'YES' ? 'yes' : 'no');
            side.textContent = bet.outcome === 'YES' ? t('yes') : t('no');

            main.className = 'market-bet-main';
            title.className = 'market-bet-question';
            title.href = safeManifoldUrl(market.url);
            title.target = '_blank';
            title.rel = 'noopener noreferrer';
            title.textContent = market.question || t('questionFallback', { id: bet.contractId });
            meta.className = 'market-bet-meta';
            meta.textContent = betType(bet);
            main.appendChild(title);
            main.appendChild(meta);

            values.className = 'market-bet-values';
            amount.textContent = betAmount(bet);
            probability.textContent = probabilityText(bet);
            values.appendChild(amount);
            values.appendChild(probability);

            var betDate = new Date(Number(bet.createdTime));
            if (!isNaN(betDate.getTime())) time.dateTime = betDate.toISOString();
            time.textContent = formatDate(bet.createdTime, true);

            row.appendChild(side);
            row.appendChild(main);
            row.appendChild(values);
            row.appendChild(time);
            list.appendChild(row);
        });

        Object.keys(counts).forEach(function (key) {
            setText('[data-bet-filter-count="' + key + '"]', counts[key]);
        });
        applyBetFilter();
    }

    function applyBetFilter() {
        all('[data-bet-outcome]').forEach(function (row) {
            row.hidden = state.filter !== 'all' && row.getAttribute('data-bet-outcome') !== state.filter;
        });
        all('[data-bet-filter]').forEach(function (button) {
            var active = button.getAttribute('data-bet-filter') === state.filter;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function drawChart() {
        var canvas = one('[data-market-chart]');
        if (!canvas || !canvas.getContext) return;
        var context = canvas.getContext('2d');
        var width = Math.max(280, Math.round(canvas.getBoundingClientRect().width || 500));
        var height = 116;
        var ratio = Math.min(window.devicePixelRatio || 1, 2);
        var values = state.selectedMarketBets
            .slice()
            .reverse()
            .map(function (bet) { return Number(bet.probAfter); })
            .filter(function (value) { return isFinite(value); });
        var market = state.marketMap[state.selectedId] || {};
        if (values.length < 2 && isFinite(Number(market.probability))) {
            values = [Number(market.probability), Number(market.probability)];
        }
        if (values.length < 2) values = [0.5, 0.5];

        canvas.width = Math.round(width * ratio);
        canvas.height = Math.round(height * ratio);
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, width, height);

        var style = getComputedStyle(document.documentElement);
        var lineColor = style.getPropertyValue('--color-accent').trim();
        var fillColor = style.getPropertyValue('--color-accent-soft').trim();
        var min = Math.min.apply(Math, values);
        var max = Math.max.apply(Math, values);
        var spread = Math.max(max - min, 0.02);
        var pad = 8;

        function point(value, index) {
            return {
                x: pad + (index / (values.length - 1)) * (width - pad * 2),
                y: pad + (1 - (value - min) / spread) * (height - pad * 2)
            };
        }

        context.beginPath();
        values.forEach(function (value, index) {
            var current = point(value, index);
            if (index === 0) context.moveTo(current.x, current.y);
            else context.lineTo(current.x, current.y);
        });
        var last = point(values[values.length - 1], values.length - 1);
        var first = point(values[0], 0);
        context.lineTo(last.x, height);
        context.lineTo(first.x, height);
        context.closePath();
        context.fillStyle = fillColor;
        context.fill();

        context.beginPath();
        values.forEach(function (value, index) {
            var current = point(value, index);
            if (index === 0) context.moveTo(current.x, current.y);
            else context.lineTo(current.x, current.y);
        });
        context.strokeStyle = lineColor;
        context.lineWidth = 2;
        context.lineJoin = 'round';
        context.lineCap = 'round';
        context.stroke();
    }

    function showLoaded() {
        one('[data-markets-loading]').hidden = true;
        one('[data-markets-error]').hidden = true;
        all('[data-markets-content]').forEach(function (node) { node.hidden = false; });
        root.setAttribute('aria-busy', 'false');
    }

    function showError(error) {
        one('[data-markets-loading]').hidden = true;
        all('[data-markets-content]').forEach(function (node) { node.hidden = true; });
        one('[data-markets-error]').hidden = false;
        setText(
            '[data-markets-error-message]',
            error && error.status === 404
                ? t('userError', { username: username })
                : t('marketsError')
        );
        root.setAttribute('aria-busy', 'false');
    }

    function load() {
        root.setAttribute('aria-busy', 'true');
        one('[data-markets-loading]').hidden = false;
        one('[data-markets-error]').hidden = true;
        all('[data-markets-content]').forEach(function (node) { node.hidden = true; });

        requestJson(API_ROOT + '/user/' + encodeURIComponent(username))
            .then(function (user) {
                state.user = user;
                return Promise.all([fetchAllMarkets(user.id), fetchAllBets()]);
            })
            .then(function (results) {
                state.markets = results[0].sort(function (a, b) {
                    return Number(b.createdTime) - Number(a.createdTime);
                });
                state.bets = results[1].sort(function (a, b) {
                    return Number(b.createdTime) - Number(a.createdTime);
                });
                state.marketMap = {};
                state.fullMarketIds = {};
                state.fullMarketRequests = {};
                state.markets.forEach(function (market) {
                    state.marketMap[market.id] = market;
                });
                state.selectedId = state.markets[0] ? state.markets[0].id : '';
                return fetchMissingMarkets();
            })
            .then(function () {
                renderProfile();
                renderSummary();
                renderBets();
                showLoaded();
                selectMarket(state.selectedId);
            })
            .catch(showError);
    }

    document.addEventListener('click', function (event) {
        var marketButton = event.target.closest && event.target.closest('[data-market-id]');
        if (marketButton && root.contains(marketButton)) {
            selectMarket(marketButton.getAttribute('data-market-id') || '');
            return;
        }

        var filterButton = event.target.closest && event.target.closest('[data-bet-filter]');
        if (filterButton && root.contains(filterButton)) {
            state.filter = filterButton.getAttribute('data-bet-filter') || 'all';
            applyBetFilter();
            return;
        }

        var retryButton = event.target.closest && event.target.closest('[data-markets-retry]');
        if (retryButton && root.contains(retryButton)) {
            load();
            return;
        }

        var themeButton = event.target.closest && event.target.closest('[data-theme-toggle]');
        if (themeButton) window.requestAnimationFrame(drawChart);
    });

    window.addEventListener('resize', function () {
        if (state.selectedId) drawChart();
    });
    load();
}());
