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

// Текст ссылок портала — структурная разметка, переводится явными ветками.
$portal_labels = [
    'ru' => ['portal' => 'Портал', 'discord' => 'Discord', 'bugs' => 'Баг-репорты', 'support' => 'Поддержка', 'login' => 'Войти', 'register' => 'создать аккаунт', 'profile' => 'Личный кабинет', 'logout' => 'Выйти', 'admin' => 'Управление'],
    'uk' => ['portal' => 'Портал', 'discord' => 'Discord', 'bugs' => 'Баг-репорти', 'support' => 'Підтримка', 'login' => 'Увійти', 'register' => 'створити акаунт', 'profile' => 'Особистий кабінет', 'logout' => 'Вийти', 'admin' => 'Управління'],
    'en' => ['portal' => 'Portal', 'discord' => 'Discord', 'bugs' => 'Bug reports', 'support' => 'Support', 'login' => 'Sign in', 'register' => 'create account', 'profile' => 'My account', 'logout' => 'Sign out', 'admin' => 'Admin'],
][$header_lang] ?? [];

// Пункты главного меню — структурная разметка, переводится явными ветками.
$menu_items = [
    'ru' => [
        ['key' => 'index',         'label' => 'ГЛАВНАЯ',        'url' => 'index.php'],
        ['key' => 'download',      'label' => 'ИГРА',           'url' => 'download.php'],
        ['key' => 'players',       'label' => 'СООБЩЕСТВО',     'url' => 'players.php'],
        ['key' => 'subscriptions', 'label' => 'ПОДПИСКИ',       'url' => 'subscriptions.php'],
        ['key' => 'changelog',     'label' => 'ОБНОВЛЕНИЯ',     'url' => 'changelog.php'],
        ['key' => 'roadmap',       'label' => 'ДОРОЖНАЯ КАРТА', 'url' => 'roadmap.php'],
    ],
    'uk' => [
        ['key' => 'index',         'label' => 'ГОЛОВНА',        'url' => 'index.php'],
        ['key' => 'download',      'label' => 'ГРА',            'url' => 'download.php'],
        ['key' => 'players',       'label' => 'СПІЛЬНОТА',      'url' => 'players.php'],
        ['key' => 'subscriptions', 'label' => 'ПІДПИСКИ',       'url' => 'subscriptions.php'],
        ['key' => 'changelog',     'label' => 'ОНОВЛЕННЯ',      'url' => 'changelog.php'],
        ['key' => 'roadmap',       'label' => 'ДОРОЖНЯ КАРТА',  'url' => 'roadmap.php'],
    ],
    'en' => [
        ['key' => 'index',         'label' => 'HOME',           'url' => 'index.php'],
        ['key' => 'download',      'label' => 'GAME',           'url' => 'download.php'],
        ['key' => 'players',       'label' => 'COMMUNITY',      'url' => 'players.php'],
        ['key' => 'subscriptions', 'label' => 'SUBSCRIPTIONS',  'url' => 'subscriptions.php'],
        ['key' => 'changelog',     'label' => 'UPDATES',        'url' => 'changelog.php'],
        ['key' => 'roadmap',       'label' => 'ROADMAP',        'url' => 'roadmap.php'],
    ],
][$header_lang] ?? [];
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

<!-- Верхняя полоса портала (2012 WoT style) -->
<div class="b-portalmenu">
    <div class="b-portalmenu-inner">
        <div class="b-portalmenu-links">
            <ul>
                <li class="active"><span><?php echo htmlspecialchars($portal_labels['portal'], ENT_QUOTES, 'UTF-8'); ?></span></li>
                <li><a href="https://discord.gg/fM4Dqess6r" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($portal_labels['discord'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                <li><a href="<?php echo $header_url('bugs.php'); ?>"><?php echo htmlspecialchars($portal_labels['bugs'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                <li><a href="<?php echo $header_url('donate.php'); ?>"><?php echo htmlspecialchars($portal_labels['support'], ENT_QUOTES, 'UTF-8'); ?></a></li>
            </ul>
        </div>
        <div class="b-portalmenu-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="b-portalmenu-profile" href="<?php echo $header_url('profile.php'); ?>"><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></a>
                <span class="b-portalmenu-sep">|</span>
                <a href="<?php echo $header_url('profile.php'); ?>"><?php echo htmlspecialchars($portal_labels['profile'], ENT_QUOTES, 'UTF-8'); ?></a>
                <span class="b-portalmenu-sep">|</span>
                <?php if (function_exists('session_is_staff') && session_is_staff()): ?>
                    <a href="<?php echo $header_url('admin.php'); ?>"><?php echo htmlspecialchars($portal_labels['admin'], ENT_QUOTES, 'UTF-8'); ?></a>
                    <span class="b-portalmenu-sep">|</span>
                <?php endif; ?>
                <a href="<?php echo $header_url('logout.php'); ?>"><?php echo htmlspecialchars($portal_labels['logout'], ENT_QUOTES, 'UTF-8'); ?></a>
            <?php else: ?>
                <a href="<?php echo $header_url('login.php'); ?>"><?php echo htmlspecialchars($portal_labels['login'], ENT_QUOTES, 'UTF-8'); ?></a>
                <span class="b-portalmenu-sep">/</span>
                <a href="<?php echo $header_url('register.php'); ?>"><?php echo htmlspecialchars($portal_labels['register'], ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Главное меню (2012 WoT style) -->
<div class="b-menu">
    <button class="nav-toggle" type="button" data-nav-toggle aria-label="Открыть меню" aria-expanded="false" aria-controls="siteNav"><span></span><span></span><span></span></button>
    <ul class="b-portal-menu" id="siteNav">
        <?php foreach ($menu_items as $item): ?>
            <li class="b-portal-menu_point<?php echo nav_active($item['key'], $active_page); ?>">
                <a class="b-portal-menu_point_linck" href="<?php echo $header_url($item['url']); ?>"><span class="b-portal-menu_point_linck_txt"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span></a>
            </li>
        <?php endforeach; ?>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="b-portal-menu_point b-menu-mobile-only"><a class="b-portal-menu_point_linck" href="<?php echo $header_url('profile.php'); ?>"><span class="b-portal-menu_point_linck_txt"><?php echo htmlspecialchars($portal_labels['profile'], ENT_QUOTES, 'UTF-8'); ?></span></a></li>
            <li class="b-portal-menu_point b-menu-mobile-only"><a class="b-portal-menu_point_linck" href="<?php echo $header_url('logout.php'); ?>"><span class="b-portal-menu_point_linck_txt"><?php echo htmlspecialchars($portal_labels['logout'], ENT_QUOTES, 'UTF-8'); ?></span></a></li>
        <?php else: ?>
            <li class="b-portal-menu_point b-menu-mobile-only"><a class="b-portal-menu_point_linck" href="<?php echo $header_url('login.php'); ?>"><span class="b-portal-menu_point_linck_txt"><?php echo htmlspecialchars($portal_labels['login'], ENT_QUOTES, 'UTF-8'); ?></span></a></li>
            <li class="b-portal-menu_point b-menu-mobile-only"><a class="b-portal-menu_point_linck" href="<?php echo $header_url('register.php'); ?>"><span class="b-portal-menu_point_linck_txt"><?php echo htmlspecialchars($portal_labels['register'], ENT_QUOTES, 'UTF-8'); ?></span></a></li>
        <?php endif; ?>
    </ul>
</div>
