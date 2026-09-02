<?php
// Shared document head and public application header.
// Existing page contracts remain optional so every current route can include this file unchanged.
$page_title = $page_title ?? 'Project Orion — сервер 0.8.2';
$page_description = $page_description ?? '';
$page_path = $page_path ?? '';
$seo_index = $seo_index ?? true;
$active_page = $active_page ?? '';
$banner_variant = $banner_variant ?? 'compact';
if (!array_key_exists('banner_subtext', get_defined_vars())) {
    $banner_subtext = 'Игровой сервер · 0.8.2';
}
$head_extra = $head_extra ?? '';
$body_class = $body_class ?? '';
$show_popup = $show_popup ?? false;
$page_styles = $page_styles ?? [];
$page_scripts = $page_scripts ?? [];
$header_lang = function_exists('current_lang') ? current_lang() : 'ru';
$header_server_label = $header_lang === 'en' ? 'server 0.8.2' : 'сервер 0.8.2';
$header_server_state = isset($pdo) && function_exists('orion_server_state')
    ? orion_server_state($pdo)
    : ['status' => 'online', 'is_online' => true, 'message' => ''];
$header_url = static function ($path) use ($header_lang) {
    $url = function_exists('i18n_locale_path') ? i18n_locale_path($path, $header_lang) : $path;
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
};

if (!function_exists('nav_active')) {
    function nav_active($key, $active) {
        return $key === $active ? ' active' : '';
    }
}

$body_classes = array_filter([
    'page-' . ($active_page !== '' ? $active_page : 'default'),
    $body_class,
]);

// Бегущая строка «Стального фронта». Строки структурные, поэтому переводятся
// явными ветками локали, а не фильтром фраз (см. Localization.md).
$header_ticker_items = [
    'ru' => ['PROJECT ORION 0.8.2', 'Запуск — 30 августа 2026', 'Живая разработка каждую неделю', 'Общая экономика сервера', 'Discord открыт для всех', 'Баг-репорты и предложения принимаются'],
    'uk' => ['PROJECT ORION 0.8.2', 'Запуск — 30 серпня 2026', 'Жива розробка щотижня', 'Спільна економіка сервера', 'Discord відкритий для всіх', 'Баг-репорти та пропозиції приймаються'],
    'en' => ['PROJECT ORION 0.8.2', 'Launch — August 30, 2026', 'Live development every week', 'One shared server economy', 'Discord is open to everyone', 'Bug reports and proposals welcome'],
][$header_lang] ?? [];
$header_ticker_state = [
    'ru' => $header_server_state['is_online'] ? 'Сервер онлайн' : 'Сервер офлайн',
    'uk' => $header_server_state['is_online'] ? 'Сервер онлайн' : 'Сервер офлайн',
    'en' => $header_server_state['is_online'] ? 'Server online' : 'Server offline',
][$header_lang] ?? '';
array_splice($header_ticker_items, 1, 0, [$header_ticker_state]);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($header_lang, ENT_QUOTES, 'UTF-8'); ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php seo_head($page_title, $page_description, $page_path, $seo_index); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500;600&family=Russo+One&display=swap" rel="stylesheet">
    <script src="js/theme.js?v=3"></script>
    <link rel="stylesheet" href="style.css?v=37">
    <link rel="stylesheet" href="css/animations.css?v=6">
    <?php foreach ($page_styles as $stylesheet): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($stylesheet, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>
    <link rel="icon" type="image/png" href="favicon.png">
    <?php echo $head_extra; ?>
</head>
<body class="<?php echo htmlspecialchars(implode(' ', $body_classes), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $show_popup ? ' data-show-popup="1"' : ''; ?>>
<div class="front-ticker" aria-hidden="true">
    <div class="front-ticker-track" data-front-marquee>
        <div class="front-ticker-group">
            <?php foreach ($header_ticker_items as $ticker_item): ?>
                <span><?php echo htmlspecialchars($ticker_item, ENT_QUOTES, 'UTF-8'); ?></span><span class="front-ticker-dot">◆</span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<header class="app-header">
    <a class="site-brand" href="<?php echo $header_url('index.php'); ?>" aria-label="Project Orion — главная">
        <span class="site-brand-mark"><img src="images/logo.png" alt=""></span>
        <span class="site-brand-copy">
            <strong>PROJECT ORION</strong>
            <small><?php echo $header_server_label; ?> <span class="header-server-status header-server-status--<?php echo $header_server_state['is_online'] ? 'online' : 'offline'; ?>"><i aria-hidden="true"></i><?php echo $header_server_state['is_online'] ? 'онлайн' : 'офлайн'; ?></span></small>
        </span>
    </a>
    <button class="nav-toggle" type="button" data-nav-toggle aria-label="Открыть меню" aria-expanded="false" aria-controls="siteNav"><span></span><span></span><span></span></button>
    <nav class="site-nav" id="siteNav" aria-label="Основная навигация">
        <a href="<?php echo $header_url('index.php'); ?>" class="site-nav-link<?php echo nav_active('index', $active_page); ?>">Главная</a>
        <a href="<?php echo $header_url('download.php'); ?>" class="site-nav-link<?php echo nav_active('download', $active_page); ?>">Играть</a>
        <a href="<?php echo $header_url('players.php'); ?>" class="site-nav-link<?php echo nav_active('players', $active_page); ?>">Игроки</a>
        <a href="<?php echo $header_url('subscriptions.php'); ?>" class="site-nav-link<?php echo nav_active('subscriptions', $active_page); ?>">Подписки</a>
        <a href="<?php echo $header_url('petitions.php'); ?>" class="site-nav-link<?php echo nav_active('petitions', $active_page); ?>"><?php echo $header_lang === 'uk' ? 'Пропозиції' : ($header_lang === 'en' ? 'Proposals' : 'Предложения'); ?></a>
        <a href="<?php echo $header_url('changelog.php'); ?>" class="site-nav-link<?php echo nav_active('changelog', $active_page); ?>">Обновления</a>
        <a href="<?php echo $header_url('roadmap.php'); ?>" class="site-nav-link<?php echo nav_active('roadmap', $active_page); ?>">Роадмап</a>
        <a href="<?php echo $header_url('bugs.php'); ?>" class="site-nav-link<?php echo nav_active('bugs', $active_page); ?>">Баг-репорты</a>
        <a href="<?php echo $header_url('donate.php'); ?>" class="site-nav-link<?php echo nav_active('donate', $active_page); ?>">Поддержать</a>
        <?php if (function_exists('session_is_staff') && session_is_staff()): ?><a href="<?php echo $header_url('admin.php'); ?>" class="site-nav-link<?php echo nav_active('admin', $active_page); ?>">Управление</a><?php endif; ?>
        <div class="mobile-account-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="mobile-account-link mobile-account-profile<?php echo nav_active('profile', $active_page); ?>" href="<?php echo $header_url('profile.php'); ?>">Личный кабинет</a>
                <a class="mobile-account-link mobile-account-logout" href="<?php echo $header_url('logout.php'); ?>">Выйти</a>
            <?php else: ?>
                <a class="mobile-account-link mobile-account-login" href="<?php echo $header_url('login.php'); ?>">Войти</a>
                <a class="mobile-account-link mobile-account-register<?php echo nav_active('register', $active_page); ?>" href="<?php echo $header_url('register.php'); ?>">Регистрация</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="header-actions">
        <?php if (isset($pdo) && function_exists('staff_notifications_html')): ?>
            <?php echo staff_notifications_html($pdo, 8); ?>
        <?php endif; ?>
        <?php if (!in_array('is-admin', $body_classes, true) && function_exists('i18n_switcher_html')): ?>
            <?php echo i18n_switcher_html('header'); ?>
        <?php endif; ?>
        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Включить светлую тему" aria-pressed="true" title="Включить светлую тему">
            <svg class="theme-icon theme-icon--sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3.5"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"></path></svg>
            <svg class="theme-icon theme-icon--moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.4A8.5 8.5 0 0 1 8.6 3.8 8.5 8.5 0 1 0 20.2 15.4Z"></path></svg>
        </button>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a class="profile-chip<?php echo nav_active('profile', $active_page); ?>" href="<?php echo $header_url('profile.php'); ?>"><span><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span><small>Личный кабинет</small></a>
            <a class="header-login" href="<?php echo $header_url('logout.php'); ?>">Выйти</a>
        <?php else: ?>
            <a class="header-login" href="<?php echo $header_url('login.php'); ?>">Войти</a>
            <a class="header-register<?php echo nav_active('register', $active_page); ?>" href="<?php echo $header_url('register.php'); ?>">Регистрация</a>
        <?php endif; ?>
        <a class="btn btn-primary header-cta" href="<?php echo $header_url('download.php'); ?>">Играть</a>
    </div>
</header>
