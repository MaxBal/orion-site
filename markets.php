<?php
if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en' && !defined('ORION_SKIP_LANGUAGE_REDIRECT')) {
    define('ORION_SKIP_LANGUAGE_REDIRECT', true);
}
require_once 'db.php';

if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en') {
    $_SESSION['lang'] = 'en';
}
$ui_lang = function_exists('current_lang') ? current_lang() : 'ru';
if (($_SESSION['lang'] ?? '') === 'en') {
    $ui_lang = 'en';
}
if (!in_array($ui_lang, ['ru', 'uk', 'en'], true)) {
    $ui_lang = 'ru';
}

$copy = [
    'ru' => [
        'page_title' => 'Рынки прогнозов @toffexcrf — Project Orion 0.8.2',
        'page_description' => 'Рынки и полная история ставок пользователя @toffexcrf на Manifold Markets.',
        'banner_subtext' => 'Manifold Markets · данные в реальном времени',
        'title' => 'Рынки прогнозов',
        'live' => 'живые данные',
        'lead' => 'Все рынки и ставки пользователя @toffexcrf загружаются напрямую через официальный Manifold API.',
        'author_player' => 'АВТОР И ИГРОК',
        'balance_loading' => 'Баланс загружается…',
        'stats_aria' => 'Статистика Manifold',
        'markets_created' => 'создано рынков',
        'bets_history' => 'ставок в истории',
        'markets_with_bets' => 'рынков со ставками',
        'last_activity' => 'последняя активность',
        'loading' => 'Загружаем рынки и всю историю ставок…',
        'loading_note' => 'Количество страниц определяется автоматически — записи не обрезаются.',
        'load_error' => 'Не удалось загрузить данные Manifold.',
        'connection_error' => 'Проверьте подключение и повторите попытку.',
        'retry' => 'Повторить',
        'workspace_aria' => 'Рынки пользователя toffexcrf',
        'all_markets' => 'Все рынки',
        'select_market' => 'Выберите рынок',
        'open' => 'Открыть ↗',
        'market_empty' => 'Выберите рынок',
        'market_empty_note' => 'Детали, вероятность и последние сделки появятся здесь.',
        'probability' => 'вероятность',
        'chart_aria' => 'График изменения вероятности',
        'earlier' => 'раньше',
        'latest_trades' => 'последние сделки',
        'now' => 'сейчас',
        'voting_aria' => 'Текущая вероятность исходов',
        'traders' => 'Трейдеры',
        'volume' => 'Объём',
        'liquidity' => 'Ликвидность',
        'open_market' => 'Открыть рынок на Manifold',
        'full_history' => 'FULL BET HISTORY',
        'all_bets' => 'Все ставки @toffexcrf',
        'history_note' => 'Полная публичная история: обычные сделки, продажи, лимитные ордера и погашения.',
        'filter' => 'Фильтр ставок',
        'all' => 'Все',
        'yes' => 'ДА',
        'no' => 'НЕТ',
        'no_bets' => 'У @toffexcrf пока нет публичных ставок.',
        'source_note' => 'Данные предоставлены Manifold Markets и могут обновляться с небольшой задержкой. Project Orion показывает историю только для просмотра; торговые действия выполняются на сайте Manifold.',
    ],
    'uk' => [
        'page_title' => 'Ринки прогнозів @toffexcrf — Project Orion 0.8.2',
        'page_description' => 'Ринки та повна історія ставок користувача @toffexcrf на Manifold Markets.',
        'banner_subtext' => 'Manifold Markets · дані в реальному часі',
        'title' => 'Ринки прогнозів',
        'live' => 'живі дані',
        'lead' => 'Усі ринки та ставки користувача @toffexcrf завантажуються безпосередньо через офіційний Manifold API.',
        'author_player' => 'АВТОР І ГРАВЕЦЬ',
        'balance_loading' => 'Баланс завантажується…',
        'stats_aria' => 'Статистика Manifold',
        'markets_created' => 'створено ринків',
        'bets_history' => 'ставок в історії',
        'markets_with_bets' => 'ринків зі ставками',
        'last_activity' => 'остання активність',
        'loading' => 'Завантажуємо ринки та всю історію ставок…',
        'loading_note' => 'Кількість сторінок визначається автоматично — записи не обрізаються.',
        'load_error' => 'Не вдалося завантажити дані Manifold.',
        'connection_error' => 'Перевірте підключення та повторіть спробу.',
        'retry' => 'Повторити',
        'workspace_aria' => 'Ринки користувача toffexcrf',
        'all_markets' => 'Усі ринки',
        'select_market' => 'Оберіть ринок',
        'open' => 'Відкрити ↗',
        'market_empty' => 'Оберіть ринок',
        'market_empty_note' => 'Деталі, ймовірність та останні угоди зʼявляться тут.',
        'probability' => 'ймовірність',
        'chart_aria' => 'Графік зміни ймовірності',
        'earlier' => 'раніше',
        'latest_trades' => 'останні угоди',
        'now' => 'зараз',
        'voting_aria' => 'Поточна ймовірність результатів',
        'traders' => 'Трейдери',
        'volume' => 'Обсяг',
        'liquidity' => 'Ліквідність',
        'open_market' => 'Відкрити ринок на Manifold',
        'full_history' => 'FULL BET HISTORY',
        'all_bets' => 'Усі ставки @toffexcrf',
        'history_note' => 'Повна публічна історія: звичайні угоди, продажі, лімітні ордери та погашення.',
        'filter' => 'Фільтр ставок',
        'all' => 'Усі',
        'yes' => 'ТАК',
        'no' => 'НІ',
        'no_bets' => 'У @toffexcrf поки немає публічних ставок.',
        'source_note' => 'Дані надані Manifold Markets і можуть оновлюватися з невеликою затримкою. Project Orion показує історію лише для перегляду; торгові дії виконуються на сайті Manifold.',
    ],
    'en' => [
        'page_title' => 'Prediction markets @toffexcrf: Project Orion 0.8.2',
        'page_description' => 'Prediction markets and the complete betting history of @toffexcrf on Manifold Markets.',
        'banner_subtext' => 'Manifold Markets · real-time data',
        'title' => 'Prediction markets',
        'live' => 'live data',
        'lead' => 'All markets and bets by @toffexcrf are loaded directly through the official Manifold API.',
        'author_player' => 'AUTHOR AND TRADER',
        'balance_loading' => 'Loading balance…',
        'stats_aria' => 'Manifold statistics',
        'markets_created' => 'markets created',
        'bets_history' => 'bets in history',
        'markets_with_bets' => 'markets with bets',
        'last_activity' => 'latest activity',
        'loading' => 'Loading markets and the complete betting history…',
        'loading_note' => 'The number of pages is determined automatically; records are not truncated.',
        'load_error' => 'Failed to load Manifold data.',
        'connection_error' => 'Check your connection and try again.',
        'retry' => 'Retry',
        'workspace_aria' => 'Markets by toffexcrf',
        'all_markets' => 'All markets',
        'select_market' => 'Select a market',
        'open' => 'Open ↗',
        'market_empty' => 'Select a market',
        'market_empty_note' => 'Details, probability, and recent trades will appear here.',
        'probability' => 'probability',
        'chart_aria' => 'Probability change chart',
        'earlier' => 'earlier',
        'latest_trades' => 'recent trades',
        'now' => 'now',
        'voting_aria' => 'Current outcome probabilities',
        'traders' => 'Traders',
        'volume' => 'Volume',
        'liquidity' => 'Liquidity',
        'open_market' => 'Open market on Manifold',
        'full_history' => 'FULL BET HISTORY',
        'all_bets' => 'All bets by @toffexcrf',
        'history_note' => 'Complete public history: regular trades, sales, limit orders, and redemptions.',
        'filter' => 'Bet filter',
        'all' => 'All',
        'yes' => 'YES',
        'no' => 'NO',
        'no_bets' => '@toffexcrf has no public bets yet.',
        'source_note' => 'Data is provided by Manifold Markets and may update with a short delay. Project Orion displays the history for viewing only; trading actions take place on the Manifold website.',
    ],
][$ui_lang];
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'markets.php';
$active_page = 'markets';
$banner_subtext = $copy['banner_subtext'];
$page_scripts = ['js/markets.js?v=3'];
require __DIR__ . '/includes/header.php';
?>

<main
    class="page-shell markets-page"
    data-markets-root
    data-market-username="toffexcrf"
    aria-busy="true"
>
    <section class="markets-hero" aria-labelledby="markets-title">
        <div class="markets-hero-copy">
            <div class="markets-title-row">
                <div>
                    <p class="eyebrow">MANIFOLD MARKETS</p>
                    <h1 id="markets-title"><?php echo htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                </div>
                <span class="markets-live-badge"><i aria-hidden="true"></i> <?php echo htmlspecialchars($copy['live'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <p><?php echo str_replace('@toffexcrf', '<a href="https://manifold.markets/toffexcrf" target="_blank" rel="noopener noreferrer">@toffexcrf</a>', htmlspecialchars($copy['lead'], ENT_QUOTES, 'UTF-8')); ?></p>
        </div>

        <a class="markets-profile-card" data-market-profile href="https://manifold.markets/toffexcrf" target="_blank" rel="noopener noreferrer">
            <span class="markets-profile-avatar-wrap">
                <img data-market-avatar src="images/logo.png" alt="" width="56" height="56">
            </span>
            <span class="markets-profile-copy">
                    <small><?php echo htmlspecialchars($copy['author_player'], ENT_QUOTES, 'UTF-8'); ?></small>
                    <strong data-market-display-name>@toffexcrf</strong>
                    <span data-market-balance><?php echo htmlspecialchars($copy['balance_loading'], ENT_QUOTES, 'UTF-8'); ?></span>
            </span>
            <span class="markets-profile-arrow" aria-hidden="true">↗</span>
        </a>
    </section>

    <section class="markets-summary" aria-label="<?php echo htmlspecialchars($copy['stats_aria'], ENT_QUOTES, 'UTF-8'); ?>">
        <article>
            <span data-market-count>—</span>
            <small><?php echo htmlspecialchars($copy['markets_created'], ENT_QUOTES, 'UTF-8'); ?></small>
        </article>
        <article>
            <span data-bet-count>—</span>
            <small><?php echo htmlspecialchars($copy['bets_history'], ENT_QUOTES, 'UTF-8'); ?></small>
        </article>
        <article>
            <span data-market-contract-count>—</span>
            <small><?php echo htmlspecialchars($copy['markets_with_bets'], ENT_QUOTES, 'UTF-8'); ?></small>
        </article>
        <article>
            <span data-market-updated>—</span>
            <small><?php echo htmlspecialchars($copy['last_activity'], ENT_QUOTES, 'UTF-8'); ?></small>
        </article>
    </section>

    <div class="markets-loading card" data-markets-loading role="status" aria-live="polite">
        <span class="markets-loading-orbit" aria-hidden="true"></span>
        <div>
            <strong><?php echo htmlspecialchars($copy['loading'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <p><?php echo htmlspecialchars($copy['loading_note'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>

    <div class="alert alert-danger markets-error" data-markets-error role="alert" hidden>
        <strong><?php echo htmlspecialchars($copy['load_error'], ENT_QUOTES, 'UTF-8'); ?></strong>
        <span data-markets-error-message><?php echo htmlspecialchars($copy['connection_error'], ENT_QUOTES, 'UTF-8'); ?></span>
        <button class="btn btn-secondary" type="button" data-markets-retry><?php echo htmlspecialchars($copy['retry'], ENT_QUOTES, 'UTF-8'); ?></button>
    </div>

    <section class="markets-workspace" data-markets-content hidden aria-label="<?php echo htmlspecialchars($copy['workspace_aria'], ENT_QUOTES, 'UTF-8'); ?>">
        <aside class="markets-list-card card">
            <header class="markets-panel-heading">
                <div>
                    <p class="eyebrow">CREATED BY @TOFFEXCRF</p>
                    <h2><?php echo htmlspecialchars($copy['all_markets'], ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>
                <span class="markets-count-pill" data-market-list-count>0</span>
            </header>
            <div class="markets-list" data-market-list role="listbox" aria-label="<?php echo htmlspecialchars($copy['select_market'], ENT_QUOTES, 'UTF-8'); ?>"></div>
            <div class="markets-list-empty" data-market-list-empty hidden>
                У @toffexcrf пока нет созданных рынков.
            </div>
        </aside>

        <article class="market-window card">
            <header class="market-window-bar">
                <span class="market-window-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                <span class="market-window-address" data-market-address>manifold.markets/toffexcrf</span>
                <a data-market-open href="https://manifold.markets/toffexcrf" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($copy['open'], ENT_QUOTES, 'UTF-8'); ?></a>
            </header>

            <div class="market-window-empty" data-market-empty>
                <span aria-hidden="true">←</span>
                    <strong><?php echo htmlspecialchars($copy['market_empty'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    <p><?php echo htmlspecialchars($copy['market_empty_note'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="market-detail" data-market-detail hidden>
                <header class="market-detail-header">
                    <div class="market-creator">
                        <img data-selected-avatar src="images/logo.png" alt="" width="32" height="32">
                        <span data-selected-creator>@toffexcrf</span>
                        <small data-selected-close>—</small>
                    </div>
                    <span class="market-state" data-selected-state>—</span>
                    <h2 data-selected-question>—</h2>
                </header>

                <div class="market-probability-panel">
                    <div class="market-probability">
                        <strong data-selected-probability>—</strong>
                        <span data-selected-probability-label><?php echo htmlspecialchars($copy['probability'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="market-chart">
                        <canvas data-market-chart height="116" aria-label="<?php echo htmlspecialchars($copy['chart_aria'], ENT_QUOTES, 'UTF-8'); ?>"></canvas>
                        <div class="market-chart-axis"><span><?php echo htmlspecialchars($copy['earlier'], ENT_QUOTES, 'UTF-8'); ?></span><span><?php echo htmlspecialchars($copy['latest_trades'], ENT_QUOTES, 'UTF-8'); ?></span><span><?php echo htmlspecialchars($copy['now'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                    </div>
                </div>

                <div class="market-voting" data-market-voting aria-label="<?php echo htmlspecialchars($copy['voting_aria'], ENT_QUOTES, 'UTF-8'); ?>" aria-live="polite"></div>

                <dl class="market-detail-stats">
                    <div><dt><?php echo htmlspecialchars($copy['traders'], ENT_QUOTES, 'UTF-8'); ?></dt><dd data-selected-traders>—</dd></div>
                    <div><dt><?php echo htmlspecialchars($copy['volume'], ENT_QUOTES, 'UTF-8'); ?></dt><dd data-selected-volume>—</dd></div>
                    <div><dt><?php echo htmlspecialchars($copy['liquidity'], ENT_QUOTES, 'UTF-8'); ?></dt><dd data-selected-liquidity>—</dd></div>
                </dl>

                <a class="btn btn-primary market-open-cta" data-selected-link href="https://manifold.markets/toffexcrf" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($copy['open_market'], ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
        </article>
    </section>

    <section class="market-bets-section" data-markets-content hidden aria-labelledby="market-bets-title">
        <header class="section-heading market-bets-heading">
            <div>
                <p class="eyebrow"><?php echo htmlspecialchars($copy['full_history'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2 id="market-bets-title"><?php echo htmlspecialchars($copy['all_bets'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p><?php echo htmlspecialchars($copy['history_note'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="market-bet-filters" role="group" aria-label="<?php echo htmlspecialchars($copy['filter'], ENT_QUOTES, 'UTF-8'); ?>">
                <button type="button" class="is-active" data-bet-filter="all" aria-pressed="true"><?php echo htmlspecialchars($copy['all'], ENT_QUOTES, 'UTF-8'); ?> <span data-bet-filter-count="all">0</span></button>
                <button type="button" data-bet-filter="YES" aria-pressed="false"><?php echo htmlspecialchars($copy['yes'], ENT_QUOTES, 'UTF-8'); ?> <span data-bet-filter-count="YES">0</span></button>
                <button type="button" data-bet-filter="NO" aria-pressed="false"><?php echo htmlspecialchars($copy['no'], ENT_QUOTES, 'UTF-8'); ?> <span data-bet-filter-count="NO">0</span></button>
            </div>
        </header>

        <div class="market-bet-list" data-bet-list></div>
        <div class="markets-list-empty" data-bet-list-empty hidden><?php echo htmlspecialchars($copy['no_bets'], ENT_QUOTES, 'UTF-8'); ?></div>
    </section>

    <aside class="markets-source-note" data-markets-content hidden>
        <span aria-hidden="true">i</span>
        <p><?php echo htmlspecialchars($copy['source_note'], ENT_QUOTES, 'UTF-8'); ?></p>
    </aside>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
