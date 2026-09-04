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

$video_url = get_setting($pdo, 'download_video_url') ?: '';
$instructions = get_setting($pdo, 'download_instructions');
$copy = [
    'ru' => [
        'page_title' => 'Скачать клиент — сервер Project Orion 0.8.2',
        'page_description' => 'Скачать клиент для сервера Project Orion 0.8.2. Установи клиент, зарегистрируйся и подключайся к серверу.',
        'banner_subtext' => 'Игровой сервер · 0.8.2',
        'server' => 'Сервер 0.8.2',
        'title' => 'Скачать и установить',
        'lead' => 'Скачайте лаунчер или полный клиент и следуйте инструкции.',
        'download_option' => 'Вариант загрузки',
        'client_tab' => 'Скачать клиент',
        'client' => 'Клиент',
        'client_title' => 'Клиент для сервера 0.8.2',
        'client_description' => 'Полная версия клиента для Windows. Размер архива — около 2,4 ГБ.',
        'launcher' => 'Лаунчер',
        'launcher_title' => 'Лаунчер Reborn',
        'launcher_description' => 'Установщик клиента 0.8.2: скачивает файлы игры, ставит свежий патч и запускает игру. ZIP-архив, около 17 МБ.',
        'launcher_button' => 'Скачать лаунчер',
        'unavailable' => 'недоступно',
        'no_links' => 'Ссылок пока нет',
        'preparation' => 'Подготовка',
        'install_client' => 'Установка клиента',
        'video_note' => 'Подробная инструкция по установке показана в видео ниже:',
        'video_fallback' => 'Ваш браузер не поддерживает воспроизведение видео.',
    ],
    'uk' => [
        'page_title' => 'Завантажити клієнт — сервер Project Orion 0.8.2',
        'page_description' => 'Завантажити клієнт для сервера Project Orion 0.8.2. Встанови клієнт, зареєструйся та підключайся до сервера.',
        'banner_subtext' => 'Ігровий сервер · 0.8.2',
        'server' => 'Сервер 0.8.2',
        'title' => 'Завантажити та встановити',
        'lead' => 'Завантажте лаунчер або повний клієнт та дотримуйтесь інструкції.',
        'download_option' => 'Варіант завантаження',
        'client_tab' => 'Завантажити клієнт',
        'client' => 'Клієнт',
        'client_title' => 'Клієнт для сервера 0.8.2',
        'client_description' => 'Повна версія клієнта для Windows. Розмір архіву — близько 2,4 ГБ.',
        'launcher' => 'Лаунчер',
        'launcher_title' => 'Лаунчер Reborn',
        'launcher_description' => 'Інсталятор клієнта 0.8.2: завантажує файли гри, ставить свіжий патч і запускає гру. ZIP-архів, близько 17 МБ.',
        'launcher_button' => 'Завантажити лаунчер',
        'unavailable' => 'недоступно',
        'no_links' => 'Посилань поки немає',
        'preparation' => 'Підготовка',
        'install_client' => 'Встановлення клієнта',
        'video_note' => 'Детальна інструкція зі встановлення показана у відео нижче:',
        'video_fallback' => 'Ваш браузер не підтримує відтворення відео.',
    ],
    'en' => [
        'page_title' => 'Download client: Project Orion server 0.8.2',
        'page_description' => 'Download the Project Orion 0.8.2 server client. Install the client, register, and connect to the server.',
        'banner_subtext' => 'Game server · 0.8.2',
        'server' => 'Server 0.8.2',
        'title' => 'Download and install',
        'lead' => 'Download the launcher or the full client and follow the instructions.',
        'download_option' => 'Download option',
        'client_tab' => 'Download client',
        'client' => 'Client',
        'client_title' => 'Client for server 0.8.2',
        'client_description' => 'The full Windows client. The archive is approximately 2.4 GB.',
        'launcher' => 'Launcher',
        'launcher_title' => 'Reborn launcher',
        'launcher_description' => 'Client 0.8.2 installer: downloads game files, applies the latest patch, and launches the game. ZIP archive, about 17 MB.',
        'launcher_button' => 'Download launcher',
        'unavailable' => 'unavailable',
        'no_links' => 'No links yet',
        'preparation' => 'Preparation',
        'install_client' => 'Client installation',
        'video_note' => 'A detailed installation guide is shown in the video below:',
        'video_fallback' => 'Your browser does not support video playback.',
    ],
][$ui_lang];
$client_mirrors = get_setting_json($pdo, 'download_client_mirrors', [['name' => $ui_lang === 'en' ? 'Download from another site' : 'Скачать на другом сайте', 'url' => '', 'enabled' => false]]);
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'download.php';
$active_page = 'download';
$banner_subtext = $copy['banner_subtext'];
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell download-page">
    <header class="page-header">
        <p class="eyebrow"><?php echo htmlspecialchars($copy['server'], ENT_QUOTES, 'UTF-8'); ?></p>
        <h1><?php echo htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars($copy['lead'], ENT_QUOTES, 'UTF-8'); ?></p>
    </header>

    <div class="dl-tabs" role="tablist" aria-label="<?php echo htmlspecialchars($copy['download_option'], ENT_QUOTES, 'UTF-8'); ?>">
        <button type="button" id="dl-tab-client" class="dl-tab active" role="tab" data-tab="client" aria-controls="pane-client" aria-selected="true"><?php echo htmlspecialchars($copy['client_tab'], ENT_QUOTES, 'UTF-8'); ?></button>
    </div>

    <div class="dl-pane active" id="pane-client" role="tabpanel" aria-labelledby="dl-tab-client">
        <section class="download-options">
            <article class="download-card download-card--accent">
                <p class="eyebrow"><?php echo htmlspecialchars($copy['client'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2><?php echo htmlspecialchars($copy['client_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p><?php echo htmlspecialchars($copy['client_description'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="download-links">
                    <?php foreach ($client_mirrors as $mirror): ?>
                    <?php if (!empty($mirror['enabled'])): ?>
                    <a href="<?php echo htmlspecialchars($mirror['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-primary" data-i18n-ignore><?php echo htmlspecialchars($mirror['name'], ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php else: ?>
                    <button type="button" class="btn btn-disabled" disabled data-i18n-ignore><?php echo htmlspecialchars($mirror['name'], ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($copy['unavailable'], ENT_QUOTES, 'UTF-8'); ?></button>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($client_mirrors)): ?>
                    <button type="button" class="btn btn-disabled" disabled><?php echo htmlspecialchars($copy['no_links'], ENT_QUOTES, 'UTF-8'); ?></button>
                    <?php endif; ?>
                </div>
                <?php $active_count = count(array_filter($client_mirrors, function($m) { return !empty($m['enabled']); })); ?>
                <?php if ($active_count < count($client_mirrors)): ?>
                <?php if ($ui_lang === 'en'): ?>
                    <p class="download-mirror-status">Currently <b><?php echo $active_count; ?></b> mirror<?php echo $active_count === 1 ? '' : 's'; ?> available for download.</p>
                <?php else: ?>
                    <p class="download-mirror-status">Сейчас доступно <b><?php echo $active_count; ?></b> зеркал<?php echo $active_count === 1 ? 'о' : (($active_count >= 2 && $active_count <= 4) ? 'а' : ''); ?> для загрузки.</p>
                <?php endif; ?>
                <?php endif; ?>
            </article>

            <article class="download-card download-card--accent">
                <p class="eyebrow"><?php echo htmlspecialchars($copy['launcher'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2><?php echo htmlspecialchars($copy['launcher_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p><?php echo htmlspecialchars($copy['launcher_description'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="download-links">
                    <a href="launcher/RebornLauncher-v1.1-R2.zip" class="btn btn-primary" data-i18n-ignore><?php echo htmlspecialchars($copy['launcher_button'], ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
            </article>
        </section>

        <section class="install-layout">
            <div>
                <p class="eyebrow"><?php echo htmlspecialchars($copy['preparation'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2><?php echo htmlspecialchars($copy['install_client'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="install-steps">
                    <?php if ($instructions): ?>
                    <p class="instruction-copy" data-i18n-ignore><?php echo htmlspecialchars($instructions, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php else: ?>
                    <p><?php echo htmlspecialchars($copy['video_note'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($video_url !== ''): ?>
            <aside class="video-card">
                <video controls preload="metadata">
                    <source src="<?php echo htmlspecialchars($video_url, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                    <?php echo htmlspecialchars($copy['video_fallback'], ENT_QUOTES, 'UTF-8'); ?>
                </video>
            </aside>
            <?php endif; ?>
        </section>
    </div>

    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
