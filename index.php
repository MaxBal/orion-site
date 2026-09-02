<?php
// English is handled locally until the shared language bootstrap exposes it.
// Keeping the query parameter on this request also makes direct /?lang=en work.
if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en' && !defined('ORION_SKIP_LANGUAGE_REDIRECT')) {
    define('ORION_SKIP_LANGUAGE_REDIRECT', true);
}
require_once 'db.php';
require_once __DIR__ . '/includes/roadmap_data.php';

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
$locale_url = static function ($path) use ($ui_lang) {
    $path = (string)$path;
    if ($ui_lang === 'ru' || preg_match('/(?:^|[?&])lang=/', $path)) {
        return $path;
    }
    $fragment = '';
    $fragment_pos = strpos($path, '#');
    if ($fragment_pos !== false) {
        $fragment = substr($path, $fragment_pos);
        $path = substr($path, 0, $fragment_pos);
    }
    return $path . (strpos($path, '?') === false ? '?' : '&') . 'lang=' . rawurlencode($ui_lang) . $fragment;
};

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function format_news_date($value) {
    $time = strtotime((string)$value);
    return $time ? date('d.m.Y H:i', $time) : '';
}

$total_accounts = 0;
$total_battles = 0;
$total_wins = 0;
$news_items = [];

$copy = [
    'ru' => [
        'page_title' => 'Project Orion — игровой сервер 0.8.2',
        'page_description' => 'Project Orion — бесплатный игровой сервер версии 0.8.2 с живой разработкой, общей экономикой и сообществом игроков.',
        'banner_subtext' => 'Игровой сервер · 0.8.2',
        'server' => 'Сервер',
        'online' => 'онлайн',
        'offline' => 'офлайн',
        'game_server' => 'Игровой сервер',
        'version' => 'версии 0.8.2',
        'hero_lead' => 'Стабильный сервер с живой разработкой, общей экономикой и сообществом игроков. Создай аккаунт и подключайся.',
        'download_client' => 'Скачать клиент',
        'create_account' => 'Создать аккаунт',
        'hero_alt' => 'Логотип Project Orion',
        'stats_aria' => 'Статистика сервера',
        'tankers' => 'танкистов',
        'battles' => 'боёв',
        'wins' => 'побед',
        'server_status' => 'статус сервера',
        'online_label' => 'Онлайн',
        'offline_label' => 'Офлайн',
        'subscription_title' => 'Выбери свой уровень Orion',
        'subscription_lead' => 'От лёгкого Lite до максимального Max — четыре подписки с разным масштабом.',
        'explore_plans' => 'Исследовать тарифы',
        'all_plans' => 'Все тарифы на отдельной странице',
        'per_week' => 'в неделю',
        'roadmap_transition' => 'От ангара — к бою',
        'community' => 'СООБЩЕСТВО',
        'news' => 'Новости сервера',
        'all_updates' => 'Все обновления →',
        'administration' => 'Администрация',
        'pinned' => 'Закреплено',
        'no_news' => 'Новостей пока нет',
        'no_news_note' => 'Администрация ещё не опубликовала новости.',
        'support' => 'ПОДДЕРЖКА',
        'support_title' => 'Помоги Orion развиваться',
        'support_note' => 'Добровольная поддержка идёт на инфраструктуру проекта.',
        'support_button' => 'Поддержать проект',
        'schema_description' => 'Бесплатный игровой сервер версии 0.8.2',
        'subscription_note' => 'Четыре уровня — от Lite до Max',
        'membership_lead' => 'Подписка не даёт превосходства в бою: она ускоряет прогресс и поддерживает сервер.',
        'hall_eyebrow' => 'ЗАЛ СЛАВЫ',
        'hall_title' => 'Рейтинг игроков',
        'hall_note' => 'Топ-8 по каждой метрике. Данные берутся из общего досье сервера.',
        'all_players' => 'Все игроки →',
        'col_rank' => 'Ранг',
        'col_player' => 'Игрок',
        'col_battles' => 'Боёв',
        'col_value' => 'Результат',
        'no_players' => 'Боёв пока нет — рейтинг заполнится после первых сражений.',
        'launch_label' => 'До запуска 0.8.2',
        'cd_days' => 'дней',
        'cd_hours' => 'часов',
        'cd_minutes' => 'минут',
        'cd_seconds' => 'секунд',
        'news_note' => 'Патчи, тесты и решения совета — всё, что происходит на сервере.',
        'perk_free' => 'Вход бесплатный',
        'perk_dev' => 'Живая разработка',
        'perk_economy' => 'Общая экономика',
        'perk_community' => 'Решает сообщество',
    ],
    'uk' => [
        'page_title' => 'Project Orion — ігровий сервер 0.8.2',
        'page_description' => 'Project Orion — безкоштовний ігровий сервер версії 0.8.2 із живою розробкою, спільною економікою та спільнотою гравців.',
        'banner_subtext' => 'Ігровий сервер · 0.8.2',
        'server' => 'Сервер',
        'online' => 'онлайн',
        'offline' => 'офлайн',
        'game_server' => 'Ігровий сервер',
        'version' => 'версії 0.8.2',
        'hero_lead' => 'Стабільний сервер із живою розробкою, спільною економікою та спільнотою гравців. Створи акаунт і підключайся.',
        'download_client' => 'Завантажити клієнт',
        'create_account' => 'Створити акаунт',
        'hero_alt' => 'Логотип Project Orion',
        'stats_aria' => 'Статистика сервера',
        'tankers' => 'танкістів',
        'battles' => 'боїв',
        'wins' => 'перемог',
        'server_status' => 'статус сервера',
        'online_label' => 'Онлайн',
        'offline_label' => 'Офлайн',
        'subscription_title' => 'Обери свій рівень Orion',
        'subscription_lead' => 'Від легкого Lite до максимального Max — чотири підписки з різним масштабом.',
        'explore_plans' => 'Дослідити тарифи',
        'all_plans' => 'Усі тарифи на окремій сторінці',
        'per_week' => 'на тиждень',
        'roadmap_transition' => 'Від ангара — до бою',
        'community' => 'СПІЛЬНОТА',
        'news' => 'Новини сервера',
        'all_updates' => 'Усі оновлення →',
        'administration' => 'Адміністрація',
        'pinned' => 'Закріплено',
        'no_news' => 'Новин поки немає',
        'no_news_note' => 'Адміністрація ще не опублікувала новини.',
        'support' => 'ПІДТРИМКА',
        'support_title' => 'Допоможи Orion розвиватися',
        'support_note' => 'Добровільна підтримка йде на інфраструктуру проєкту.',
        'support_button' => 'Підтримати проєкт',
        'schema_description' => 'Безкоштовний ігровий сервер версії 0.8.2',
        'subscription_note' => 'Чотири рівні — від Lite до Max',
        'membership_lead' => 'Підписка не дає переваги в бою: вона пришвидшує прогрес і підтримує сервер.',
        'hall_eyebrow' => 'ЗАЛА СЛАВИ',
        'hall_title' => 'Рейтинг гравців',
        'hall_note' => 'Топ-8 за кожною метрикою. Дані беруться зі спільного досьє сервера.',
        'all_players' => 'Усі гравці →',
        'col_rank' => 'Ранг',
        'col_player' => 'Гравець',
        'col_battles' => 'Боїв',
        'col_value' => 'Результат',
        'no_players' => 'Боїв поки немає — рейтинг заповниться після перших боїв.',
        'launch_label' => 'До запуску 0.8.2',
        'cd_days' => 'днів',
        'cd_hours' => 'годин',
        'cd_minutes' => 'хвилин',
        'cd_seconds' => 'секунд',
        'news_note' => 'Патчі, тести та рішення ради — усе, що відбувається на сервері.',
        'perk_free' => 'Вхід безкоштовний',
        'perk_dev' => 'Жива розробка',
        'perk_economy' => 'Спільна економіка',
        'perk_community' => 'Вирішує спільнота',
    ],
    'en' => [
        'page_title' => 'Project Orion: game server 0.8.2',
        'page_description' => 'Project Orion is a free 0.8.2 game server with active development, a shared economy, and a player community.',
        'banner_subtext' => 'Game server · 0.8.2',
        'server' => 'Server',
        'online' => 'online',
        'offline' => 'offline',
        'game_server' => 'Game server',
        'version' => 'version 0.8.2',
        'hero_lead' => 'A stable server with active development, a shared economy, and a player community. Create an account and connect.',
        'download_client' => 'Download client',
        'create_account' => 'Create account',
        'hero_alt' => 'Project Orion logo',
        'stats_aria' => 'Server statistics',
        'tankers' => 'tankers',
        'battles' => 'battles',
        'wins' => 'wins',
        'server_status' => 'server status',
        'online_label' => 'Online',
        'offline_label' => 'Offline',
        'subscription_title' => 'Choose your Orion tier',
        'subscription_lead' => 'From the light Lite tier to the ultimate Max tier: four memberships with different scales.',
        'explore_plans' => 'Explore plans',
        'all_plans' => 'All plans on a separate page',
        'per_week' => 'per week',
        'roadmap_transition' => 'From garage to battle',
        'community' => 'COMMUNITY',
        'news' => 'Server news',
        'all_updates' => 'All updates →',
        'administration' => 'Administration',
        'pinned' => 'Pinned',
        'no_news' => 'No news yet',
        'no_news_note' => 'The administration has not published any news yet.',
        'support' => 'SUPPORT',
        'support_title' => 'Help Orion grow',
        'support_note' => 'Voluntary support goes toward project infrastructure.',
        'support_button' => 'Support the project',
        'schema_description' => 'Free game server version 0.8.2',
        'subscription_note' => 'Four tiers — from Lite to Max',
        'membership_lead' => 'A membership gives no battle advantage: it speeds up progress and keeps the server running.',
        'hall_eyebrow' => 'HALL OF FAME',
        'hall_title' => 'Player rating',
        'hall_note' => 'Top 8 per metric, taken straight from the shared server dossier.',
        'all_players' => 'All players →',
        'col_rank' => 'Rank',
        'col_player' => 'Player',
        'col_battles' => 'Battles',
        'col_value' => 'Result',
        'no_players' => 'No battles yet — the rating fills in after the first fights.',
        'launch_label' => 'Until 0.8.2 launch',
        'cd_days' => 'days',
        'cd_hours' => 'hours',
        'cd_minutes' => 'minutes',
        'cd_seconds' => 'seconds',
        'news_note' => 'Patches, tests and council decisions — everything happening on the server.',
        'perk_free' => 'Free to join',
        'perk_dev' => 'Live development',
        'perk_economy' => 'Shared economy',
        'perk_community' => 'Community decides',
    ],
][$ui_lang];

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM accounts");
    $total_accounts = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM battles");
    $total_battles = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT SUM(wins) FROM dossier");
    $total_wins = intval($stmt->fetchColumn());
} catch (Exception $e) {
    error_log("Index stats query: " . $e->getMessage());
}

try {
    $stmt = $pdo->query("SELECT n.*, a.username AS author_name
        FROM site_news n
        LEFT JOIN accounts a ON a.id = n.author_account_id
        WHERE n.status = 'published'
        ORDER BY n.is_pinned DESC, COALESCE(n.published_at, n.created_at) DESC, n.id DESC
        LIMIT 8");
    $news_items = $stmt->fetchAll();
    if (!empty($news_items)) {
        $ids = array_map(function($item) {
            return intval($item['id']);
        }, $news_items);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM site_news_media WHERE news_id IN ($placeholders) ORDER BY news_id ASC, sort_order ASC, id ASC");
        $stmt->execute($ids);
        $media_by_news = [];
        foreach ($stmt->fetchAll() as $media) {
            $media_by_news[intval($media['news_id'])][] = $media;
        }
        foreach ($news_items as &$news_item) {
            $news_item['media'] = $media_by_news[intval($news_item['id'])] ?? [];
        }
        unset($news_item);
    }
} catch (Exception $e) {
    error_log("Index news query: " . $e->getMessage());
    $news_items = [];
}

// Зал славы на главной берёт те же метрики, что и страница игроков,
// поэтому логика рейтинга живёт в одном месте.
require_once __DIR__ . '/includes/player_rankings.php';
$home_rankings = [];
try {
    $home_rankings = array_filter(load_player_rankings($pdo, 8), static function ($ranking) {
        return !empty($ranking['rows']);
    });
} catch (Exception $e) {
    error_log("Index rankings query: " . $e->getMessage());
    $home_rankings = [];
}

// Заполнение шкал в полосе статистики. Значения осмысленные, а не
// декоративные: доля от контрольной отметки и реальный винрейт.
$metric_fill = static function ($value, $reference) {
    $reference = max(1, (float)$reference);
    return number_format(min(1, max(.04, (float)$value / $reference)), 3, '.', '');
};
$win_rate_fill = $total_battles > 0
    ? number_format(min(1, max(.04, $total_wins / max(1, $total_battles))), 3, '.', '')
    : '0.04';

$active_page = 'index';
// Английский роадмап целиком лежит в orion_roadmap_data('en'); отдельная
// подмена копий на главной жила здесь с тех пор, как английского блока ещё не
// было, и после обновления плана начала возвращать устаревшие заголовки.
$roadmap = orion_roadmap_data($ui_lang);
$roadmap_current = orion_roadmap_phase($roadmap, $roadmap['current_id']);
$roadmap_next = orion_roadmap_phase($roadmap, $roadmap['next_id']);
$roadmap_goal = orion_roadmap_phase($roadmap, $roadmap['goal_id'] ?? 'battle');

// Превью этапов: старт, текущий, следующий и финал плана. Когда «следующий»
// совпадает с финалом, добавляем последний завершённый этап, чтобы не дублировать карточку.
$roadmap_preview_ids = array_values(array_unique([
    $roadmap['phases'][0]['id'],
    $roadmap['current_id'],
    $roadmap['next_id'],
    $roadmap_goal['id'],
]));
foreach (array_reverse($roadmap['phases']) as $roadmap_phase) {
    if (count($roadmap_preview_ids) >= 4) {
        break;
    }
    if ($roadmap_phase['status'] === 'done' && !in_array($roadmap_phase['id'], $roadmap_preview_ids, true)) {
        $roadmap_preview_ids[] = $roadmap_phase['id'];
    }
}
$roadmap_preview = array_values(array_filter($roadmap['phases'], static function ($phase) use ($roadmap_preview_ids) {
    return in_array($phase['id'], $roadmap_preview_ids, true);
}));
$server_state = orion_server_state($pdo);

// Всплывающее окно (донат-модалка) показывается один раз в начале новой сессии,
// затем не чаще, чем раз в 30 минут.
$show_popup = should_show_session_popup();

$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = '';
$banner_variant = 'full';
$banner_subtext = null;
$head_extra = '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebSite',
            'name' => 'Project Orion',
            'alternateName' => ['Проект Орион', 'Проєкт Оріон'],
            'url' => 'https://projectorion.fun/',
        ],
        [
            '@type' => 'VideoGame',
            'name' => 'Project Orion 0.8.2',
            'alternateName' => ['Проект Орион', 'Проєкт Оріон'],
            'url' => 'https://projectorion.fun/',
            'description' => $copy['schema_description'],
            'applicationCategory' => 'Game',
            'operatingSystem' => 'Windows',
            'playMode' => 'MultiPlayer',
            'genre' => ['MMO', 'Online multiplayer'],
            'inLanguage' => ['ru', 'uk', 'en'],
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
$banner_subtext = $copy['banner_subtext'];
require __DIR__ . '/includes/header.php';
?>

<main class="home-page">
    <section class="home-hero" id="top">
        <div class="home-hero-bg" aria-hidden="true"></div>
        <div class="home-hero-scan" aria-hidden="true"></div>
        <canvas class="home-hero-embers" data-front-embers aria-hidden="true"></canvas>
        <div id="hero-particles" class="hero-particles" aria-hidden="true"></div>
        <div class="home-hero-inner">
        <div class="home-hero-copy" data-aos="fade-up">
            <div class="server-chip server-chip--<?php echo $server_state['is_online'] ? 'online' : 'offline'; ?>"><span></span> <?php echo h($copy['server']); ?> <?php echo h($server_state['is_online'] ? $copy['online'] : $copy['offline']); ?> · 0.8.2</div>
            <p class="eyebrow">PROJECT ORION</p>
            <h1><?php echo h($copy['game_server']); ?><br><span><?php echo h($copy['version']); ?></span></h1>
            <p class="hero-lead"><?php echo h($copy['hero_lead']); ?></p>
            <?php if ($server_state['message'] !== ''): ?><p class="server-status-message" data-i18n-ignore><?php echo h($server_state['message']); ?></p><?php endif; ?>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?php echo h($locale_url('download.php')); ?>"><?php echo h($copy['download_client']); ?></a>
                <a class="btn btn-secondary" href="<?php echo h($locale_url('register.php')); ?>"><?php echo h($copy['create_account']); ?></a>
                <a class="btn btn-discord" href="https://discord.gg/fM4Dqess6r" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M20.317 4.37a19.79 19.79 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.865-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.058a.082.082 0 0 0 .031.056 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.291.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.3 12.3 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.84 19.84 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.06.06 0 0 0-.031-.03zM8.02 15.331c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                    Discord
                </a>
            </div>
        </div>
        <div class="home-hero-media home-hero-media--logo" data-aos="fade-up" data-aos-delay="180">
            <span class="home-hero-frame" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
            <img src="<?php echo h(ORION_HERO_IMAGE); ?>"<?php echo image_size_attrs(ORION_HERO_IMAGE); ?> alt="<?php echo h($copy['hero_alt']); ?>" fetchpriority="high" decoding="async">
        </div>
        </div>

        <div class="home-hero-stats">
            <section class="stats-grid" aria-label="<?php echo h($copy['stats_aria']); ?>">
                <article class="metric-card" style="--metric-fill: <?php echo h($metric_fill($total_accounts, 500)); ?>"><strong class="stat-value" data-target="<?php echo $total_accounts; ?>">0</strong><span><?php echo h($copy['tankers']); ?></span><span class="metric-bar" aria-hidden="true"><i></i></span></article>
                <article class="metric-card" style="--metric-fill: <?php echo h($metric_fill($total_battles, 20000)); ?>"><strong class="stat-value" data-target="<?php echo $total_battles; ?>">0</strong><span><?php echo h($copy['battles']); ?></span><span class="metric-bar" aria-hidden="true"><i></i></span></article>
                <article class="metric-card" style="--metric-fill: <?php echo h($win_rate_fill); ?>"><strong class="stat-value" data-target="<?php echo $total_wins; ?>">0</strong><span><?php echo h($copy['wins']); ?></span><span class="metric-bar" aria-hidden="true"><i></i></span></article>
                <article class="metric-card" style="--metric-fill: <?php echo $server_state['is_online'] ? '1' : '0.12'; ?>"><strong class="stat-value metric-status metric-status--<?php echo $server_state['is_online'] ? 'online' : 'offline'; ?>"><span class="metric-status-dot" aria-hidden="true"></span><?php echo h($server_state['is_online'] ? $copy['online_label'] : $copy['offline_label']); ?></strong><span><?php echo h($copy['server_status']); ?></span><span class="metric-bar" aria-hidden="true"><i></i></span></article>
            </section>
        </div>
    </section>

    <div class="page-shell home-page-body">
    <section class="home-subscriptions-banner reveal" aria-labelledby="homeSubscriptionsTitle">
        <div class="section-heading">
            <div>
                <div class="home-subscriptions-kicker"><span aria-hidden="true"></span>ORION MEMBERSHIP</div>
                <h2 id="homeSubscriptionsTitle"><?php echo h($copy['subscription_title']); ?></h2>
            </div>
            <p class="section-note"><?php echo h($copy['subscription_lead']); ?></p>
        </div>
        <div class="home-subscriptions-grid">
            <ol class="home-subscriptions-tiers" aria-label="<?php echo h($copy['all_plans']); ?>">
                <li class="home-subscription-tier home-subscription-tier--lite"><a href="<?php echo h($locale_url('subscriptions.php#lite')); ?>"><em>01</em><span>Lite</span><strong>$1</strong><small><?php echo h($copy['per_week']); ?></small></a></li>
                <li class="home-subscription-tier home-subscription-tier--plus"><a href="<?php echo h($locale_url('subscriptions.php#plus')); ?>"><em>02</em><span>Plus</span><strong>$5</strong><small><?php echo h($copy['per_week']); ?></small></a></li>
                <li class="home-subscription-tier home-subscription-tier--pro"><a href="<?php echo h($locale_url('subscriptions.php#pro')); ?>"><em>03</em><span>Pro</span><strong>$10</strong><small><?php echo h($copy['per_week']); ?></small></a></li>
                <li class="home-subscription-tier home-subscription-tier--max"><a href="<?php echo h($locale_url('subscriptions.php#max')); ?>"><em>04</em><span>Max</span><strong>$20</strong><small><?php echo h($copy['per_week']); ?></small></a></li>
            </ol>
            <div class="home-subscriptions-stage">
                <span class="front-corners" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                <div class="home-subscriptions-orbit" aria-hidden="true"><span>ORION</span><i></i><i></i><i></i></div>
                <div class="home-subscriptions-copy">
                    <p class="home-subscriptions-note"><?php echo h($copy['subscription_note']); ?></p>
                    <p><?php echo h($copy['membership_lead']); ?></p>
                    <div class="home-subscriptions-actions">
                        <a class="btn btn-primary" href="<?php echo h($locale_url('subscriptions.php')); ?>"><?php echo h($copy['explore_plans']); ?></a>
                        <span><?php echo h($copy['all_plans']); ?></span>
                    </div>
                </div>
                <span class="home-subscriptions-watermark" aria-hidden="true">0.8.2</span>
            </div>
        </div>
    </section>

    <section class="section-block home-roadmap-section">
        <div class="section-heading" data-aos="fade-up">
            <div>
                <p class="eyebrow"><?php echo h($roadmap['eyebrow']); ?></p>
                <h2><?php echo h($copy['roadmap_transition']); ?></h2>
            </div>
            <a href="<?php echo h($locale_url('roadmap.php')); ?>"><?php echo h($roadmap['open_label']); ?> →</a>
        </div>

        <div class="roadmap-preview reveal">
            <div class="roadmap-preview-main">
                <span class="front-corners" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                <div class="roadmap-preview-topline">
                    <span class="roadmap-live-chip"><span aria-hidden="true"></span><?php echo h($roadmap['updated']); ?></span>
                    <span class="roadmap-progress-code">ORION://0.8.2</span>
                </div>
                <h3><?php echo h($roadmap['title']); ?></h3>
                <p><?php echo h($roadmap['description']); ?></p>
                <div class="front-countdown" data-front-countdown="2026-08-30T00:00:00">
                    <span class="front-countdown-label"><?php echo h($copy['launch_label']); ?></span>
                    <div class="front-countdown-boxes">
                        <div class="front-countdown-box"><b data-countdown-days>00</b><span><?php echo h($copy['cd_days']); ?></span></div>
                        <div class="front-countdown-box"><b data-countdown-hours>00</b><span><?php echo h($copy['cd_hours']); ?></span></div>
                        <div class="front-countdown-box"><b data-countdown-minutes>00</b><span><?php echo h($copy['cd_minutes']); ?></span></div>
                        <div class="front-countdown-box"><b data-countdown-seconds>00</b><span><?php echo h($copy['cd_seconds']); ?></span></div>
                    </div>
                </div>
                <div class="roadmap-preview-progress">
                    <div><span><?php echo h($roadmap['progress_label']); ?></span><strong><?php echo intval($roadmap['progress_value']); ?>%</strong></div>
                    <div class="roadmap-progress-track" aria-hidden="true"><span style="--roadmap-progress: <?php echo intval($roadmap['progress_value']); ?>%"></span></div>
                    <small><?php echo h($roadmap['progress_note']); ?></small>
                </div>
                <a class="btn btn-primary" href="<?php echo h($locale_url('roadmap.php')); ?>"><?php echo h($roadmap['open_label']); ?></a>
            </div>

            <div class="roadmap-preview-stages" aria-label="<?php echo h($roadmap['timeline_label']); ?>">
                <?php foreach ($roadmap_preview as $preview_phase): ?>
                    <a class="roadmap-preview-stage roadmap-preview-stage--<?php echo h($preview_phase['status']); ?>" href="<?php echo h($locale_url('roadmap.php#' . $preview_phase['id'])); ?>">
                        <span class="roadmap-preview-marker" aria-hidden="true"><?php echo $preview_phase['status'] === 'done' ? '✓' : h($preview_phase['number']); ?></span>
                        <span class="roadmap-preview-stage-copy">
                            <small><?php echo h($preview_phase['period']); ?></small>
                            <strong><?php echo h($preview_phase['title']); ?></strong>
                        </span>
                        <span class="roadmap-status roadmap-status--<?php echo h($preview_phase['status']); ?>"><i aria-hidden="true"></i><?php echo h($roadmap['statuses'][$preview_phase['status']]); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if (!empty($home_rankings)): ?>
    <section class="section-block front-hall">
        <div class="section-heading" data-aos="fade-up">
            <div>
                <p class="eyebrow"><?php echo h($copy['hall_eyebrow']); ?></p>
                <h2><?php echo h($copy['hall_title']); ?></h2>
            </div>
            <a href="<?php echo h($locale_url('players.php')); ?>"><?php echo h($copy['all_players']); ?></a>
        </div>
        <div class="front-hall-tabs" role="tablist" aria-label="<?php echo h($copy['hall_title']); ?>">
            <?php $hall_index = 0; foreach ($home_rankings as $ranking_key => $ranking): ?>
                <button class="front-hall-tab<?php echo $hall_index === 0 ? ' is-active' : ''; ?>" type="button" role="tab" id="hall-tab-<?php echo h($ranking_key); ?>" aria-controls="hall-panel-<?php echo h($ranking_key); ?>" aria-selected="<?php echo $hall_index === 0 ? 'true' : 'false'; ?>" data-front-hall-tab="<?php echo h($ranking_key); ?>"><?php echo h($ranking['label']); ?></button>
            <?php $hall_index++; endforeach; ?>
        </div>
        <?php $hall_index = 0; foreach ($home_rankings as $ranking_key => $ranking): ?>
            <div class="front-hall-table" id="hall-panel-<?php echo h($ranking_key); ?>" role="tabpanel" aria-labelledby="hall-tab-<?php echo h($ranking_key); ?>" data-front-hall-panel="<?php echo h($ranking_key); ?>"<?php echo $hall_index === 0 ? '' : ' hidden'; ?>>
                <div class="front-hall-row front-hall-row--head">
                    <span><?php echo h($copy['col_rank']); ?></span>
                    <span><?php echo h($copy['col_player']); ?></span>
                    <span class="front-hall-hide"><?php echo h($copy['col_battles']); ?></span>
                    <span><?php echo h($ranking['label']); ?></span>
                </div>
                <?php foreach ($ranking['rows'] as $row_index => $ranking_row): ?>
                    <a class="front-hall-row<?php echo $row_index < 3 ? ' front-hall-row--top' . ($row_index + 1) : ''; ?>" href="<?php echo h($locale_url('players.php?id=' . intval($ranking_row['id']))); ?>">
                        <span class="front-hall-rank"><?php echo str_pad((string)($row_index + 1), 2, '0', STR_PAD_LEFT); ?></span>
                        <span class="front-hall-name"><span class="front-hall-avatar" aria-hidden="true" data-i18n-ignore><?php echo h(mb_strtoupper(mb_substr($ranking_row['username'], 0, 1, 'UTF-8'), 'UTF-8')); ?></span><span data-i18n-ignore translate="no" class="notranslate"><?php echo h($ranking_row['username']); ?></span></span>
                        <span class="front-hall-hide front-hall-battles"><?php echo number_format($ranking_row['total_battles'], 0, '.', ' '); ?></span>
                        <span class="front-hall-value"><?php echo h(format_player_ranking_value($ranking_key, $ranking_row['metric_value'])); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php $hall_index++; endforeach; ?>
    </section>
    <?php endif; ?>

    <section class="section-block">
        <div class="section-heading" data-aos="fade-up">
            <div>
                <p class="eyebrow"><?php echo h($copy['community']); ?></p>
                <h2><?php echo h($copy['news']); ?></h2>
            </div>
            <p class="section-note"><?php echo h($copy['news_note']); ?></p>
            <a href="<?php echo h($locale_url('changelog.php')); ?>"><?php echo h($copy['all_updates']); ?></a>
        </div>
        <div class="news-feed">
            <?php foreach ($news_items as $news_idx => $news): ?>
                <?php
                $media = $news['media'] ?? [];
                $lead_media = $media[0] ?? null;
                ?>
                <article class="news-card<?php echo $news_idx === 0 ? ' news-card--lead' : ''; ?>" data-aos="fade-up" data-aos-delay="<?php echo min($news_idx * 70, 350); ?>">
                    <div class="news-card-media">
                        <?php if ($lead_media && $lead_media['media_type'] === 'video'): ?>
                            <video src="<?php echo h($lead_media['file_path']); ?>" controls preload="metadata"></video>
                        <?php else: ?>
                            <img src="<?php echo h($lead_media['file_path'] ?? ORION_NEWS_COVER); ?>" alt="<?php echo h($news['title']); ?>" data-i18n-ignore loading="lazy" decoding="async">
                        <?php endif; ?>
                    </div>
                    <div class="news-card-copy">
                        <div class="news-meta">
                            <span><?php echo h(format_news_date($news['published_at'] ?: $news['created_at'])); ?></span>
                            <?php if (!empty($news['author_name'])): ?><span data-i18n-ignore><?php echo h($news['author_name']); ?></span><?php else: ?><span><?php echo h($copy['administration']); ?></span><?php endif; ?>
                            <?php if (intval($news['is_pinned']) === 1): ?><span><?php echo h($copy['pinned']); ?></span><?php endif; ?>
                        </div>
                        <h3 data-i18n-ignore><?php echo h($news['title']); ?></h3>
                        <?php if (trim((string)$news['summary']) !== ''): ?>
                            <p data-i18n-ignore><?php echo h($news['summary']); ?></p>
                        <?php endif; ?>
                        <div class="news-body" data-i18n-ignore><?php echo nl2br(h($news['body'])); ?></div>
                        <?php if (count($media) > 1): ?>
                            <div class="news-media-strip">
                                <?php foreach ($media as $idx => $item): ?>
                                    <?php if ($idx === 0) { continue; } ?>
                                    <?php if ($item['media_type'] === 'image'): ?>
                                        <img src="<?php echo h($item['file_path']); ?>" alt="<?php echo h($item['original_name']); ?>" data-i18n-ignore class="news-media-tile">
                                    <?php else: ?>
                                        <video src="<?php echo h($item['file_path']); ?>" class="news-media-tile" controls preload="metadata"></video>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (empty($news_items)): ?>
                <div class="empty-state">
                    <h3><?php echo h($copy['no_news']); ?></h3>
                    <p><?php echo h($copy['no_news_note']); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    </div>

    <div class="front-strip" aria-hidden="true">
        <div class="front-strip-track" data-front-marquee>
            <div class="front-strip-group">
                <figure><img src="images/orion-apollo-keyart.jpg" alt="" loading="lazy" decoding="async"></figure>
                <figure><img src="images/project-orion-apollo.jpg" alt="" loading="lazy" decoding="async"></figure>
                <figure><img src="images/banner.png" alt="" loading="lazy" decoding="async"></figure>
                <figure><img src="images/gso.png" alt="" loading="lazy" decoding="async"></figure>
                <figure><img src="images/logo-hero.jpg" alt="" loading="lazy" decoding="async"></figure>
                <figure><img src="images/news-default-cover.png" alt="" loading="lazy" decoding="async"></figure>
            </div>
        </div>
    </div>

    <div class="page-shell home-page-cta">
    <section class="support-cta" data-aos="zoom-in">
        <div>
            <p class="eyebrow"><?php echo h($copy['support']); ?></p>
            <h2><?php echo h($copy['support_title']); ?></h2>
            <p><?php echo h($copy['support_note']); ?></p>
            <div class="support-perks">
                <span><i aria-hidden="true"></i><?php echo h($copy['perk_free']); ?></span>
                <span><i aria-hidden="true"></i><?php echo h($copy['perk_dev']); ?></span>
                <span><i aria-hidden="true"></i><?php echo h($copy['perk_economy']); ?></span>
                <span><i aria-hidden="true"></i><?php echo h($copy['perk_community']); ?></span>
            </div>
        </div>
        <button class="btn btn-primary" type="button" data-modal-open="donateModal"><?php echo h($copy['support_button']); ?></button>
    </section>
    </div>
</main>
<?php require __DIR__ . '/includes/donate_modal.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
