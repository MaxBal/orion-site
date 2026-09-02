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
        'page_title' => 'Обновления и патчноуты — сервер Project Orion 0.8.2',
        'page_description' => 'История обновлений сервера Project Orion 0.8.2 — список изменений, новых возможностей и исправлений.',
        'banner_subtext' => 'Игровой сервер · 0.8.2',
        'eyebrow' => 'Разработка',
        'title' => 'История обновлений',
        'lead' => 'Патчноуты Project Orion: новые возможности, изменения и исправления каждой версии сервера. Свежие обновления находятся сверху.',
        'new' => 'Новое',
        'empty_title' => 'История обновлений пока пуста.',
        'empty_note' => 'Опубликованные патчноуты появятся здесь после добавления в панели управления.',
    ],
    'uk' => [
        'page_title' => 'Оновлення та патчноути — сервер Project Orion 0.8.2',
        'page_description' => 'Історія оновлень сервера Project Orion 0.8.2 — список змін, нових можливостей і виправлень.',
        'banner_subtext' => 'Ігровий сервер · 0.8.2',
        'eyebrow' => 'Розробка',
        'title' => 'Історія оновлень',
        'lead' => 'Патчноути Project Orion: нові можливості, зміни та виправлення кожної версії сервера. Свіжі оновлення зверху.',
        'new' => 'Нове',
        'empty_title' => 'Історія оновлень поки порожня.',
        'empty_note' => 'Опубліковані патчноути зʼявляться тут після додавання в панелі керування.',
    ],
    'en' => [
        'page_title' => 'Updates and patch notes: Project Orion server 0.8.2',
        'page_description' => 'Project Orion server 0.8.2 update history with changes, new features, and fixes.',
        'banner_subtext' => 'Game server · 0.8.2',
        'eyebrow' => 'Development',
        'title' => 'Update history',
        'lead' => 'Project Orion patch notes: new features, changes, and fixes for each server version. The latest updates appear first.',
        'new' => 'New',
        'empty_title' => 'The update history is empty.',
        'empty_note' => 'Published patch notes will appear here after they are added in the admin panel.',
    ],
][$ui_lang];

if (!function_exists('h')) {
    function h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

function format_cl_date($value) {
    global $ui_lang;
    $time = strtotime((string)$value);
    return $time ? date($ui_lang === 'en' ? 'Y-m-d' : 'd.m.Y', $time) : h($value);
}

$changelogs = orion_update_history($pdo, false);
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'changelog.php';
$active_page = 'changelog';
$banner_subtext = $copy['banner_subtext'];
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell changelog-page">
    <header class="page-header">
        <p class="eyebrow"><?php echo h($copy['eyebrow']); ?></p>
        <h1><?php echo h($copy['title']); ?></h1>
        <p><?php echo h($copy['lead']); ?></p>
    </header>

    <section class="release-feed">
        <?php foreach ($changelogs as $patch_index => $patch): ?>
        <article class="release-card">
            <header class="release-card-header cl-patch-head">
                <div class="cl-badge">
                    <span class="cl-badge-label">ver</span>
                    <span class="cl-badge-v"><?php echo h($patch['version']); ?></span>
                </div>
                <div class="cl-patch-meta">
                    <div class="cl-patch-titlerow">
                        <h2 class="cl-patch-name" data-i18n-ignore><?php echo h($patch['name']); ?></h2>
                        <?php if ($patch_index === 0): ?><span class="cl-tag-new"><?php echo h($copy['new']); ?></span><?php endif; ?>
                    </div>
                    <div class="cl-patch-sub">
                        <span class="cl-date">&#128197; <?php echo format_cl_date($patch['date']); ?></span>
                        <?php if (!empty($patch['tag'])): ?><span class="cl-chip" data-i18n-ignore><?php echo h($patch['tag']); ?></span><?php endif; ?>
                    </div>
                </div>
            </header>
            <div class="release-card-body cl-body" data-i18n-ignore>
                <?php if (!empty($patch['intro'])): ?>
                    <div class="cl-intro"><?php echo h($patch['intro']); ?></div>
                <?php endif; ?>
                <?php foreach ($patch['categories'] as $cat): ?>
                    <section class="cl-cat">
                        <div class="cl-cat-head">
                            <span class="cl-cat-icon"><?php echo h($cat['icon']); ?></span>
                            <h3 class="cl-cat-title"><?php echo h($cat['title']); ?></h3>
                        </div>
                        <ul class="cl-list">
                            <?php foreach ($cat['items'] as $item): ?>
                                <li class="cl-item">
                                    <span class="cl-marker"></span>
                                    <span class="cl-item-text"><strong><?php echo h($item[0]); ?></strong> &mdash; <?php echo h($item[1]); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
            </div>
        </article>
        <?php endforeach; ?>
        <?php if (empty($changelogs)): ?>
            <div class="card empty-state">
                <strong><?php echo h($copy['empty_title']); ?></strong>
                <p><?php echo h($copy['empty_note']); ?></p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
