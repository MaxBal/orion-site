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
        'lead' => 'Выберите полный клиент или файл подключения и следуйте инструкции.',
        'download_option' => 'Вариант загрузки',
        'client_tab' => 'Скачать клиент',
        'patch_tab' => 'Скачать патч',
        'new' => 'НОВОЕ',
        'client' => 'Клиент',
        'client_title' => 'Клиент для сервера 0.8.2',
        'client_description' => 'Полная версия клиента для Windows. Размер архива — около 2,4 ГБ.',
        'unavailable' => 'недоступно',
        'no_links' => 'Ссылок пока нет',
        'important' => 'Важно',
        'fresh_patch' => 'Установите свежий патч',
        'preparation' => 'Подготовка',
        'install_client' => 'Установка клиента',
        'video_note' => 'Подробная инструкция по установке показана в видео ниже:',
        'video_fallback' => 'Ваш браузер не поддерживает воспроизведение видео.',
        'patch' => 'Патч',
        'patch_title' => 'Файл подключения к серверу',
        'installation' => 'Установка',
        'four_steps' => 'Четыре шага',
        'step_1' => 'Полностью закройте игру и лаунчер, если они открыты.',
        'step_2' => 'Скачайте scripts_config.xml в карточке загрузки.',
        'step_3' => 'Положите его в подпапку res\\, заменив существующий файл: …\\res\\scripts_config.xml.',
        'step_4' => 'Запустите игру — подключение готово.',
        'linux' => 'тот же файл положите в',
        'linux_tail' => 'внутри папки игры и запускайте как обычно через Wine/Proton. Опционально — лаунчер',
        'wine_note' => '(нужен Wine ≥ 8 или Proton).',
        'safe_title' => 'Это безопасно.',
        'safe_note' => 'scripts_config.xml — обычный текстовый файл, его можно открыть «Блокнотом» и увидеть адрес сервера. Системные файлы Windows он не трогает, права администратора не нужны.',
    ],
    'uk' => [
        'page_title' => 'Завантажити клієнт — сервер Project Orion 0.8.2',
        'page_description' => 'Завантажити клієнт для сервера Project Orion 0.8.2. Встанови клієнт, зареєструйся та підключайся до сервера.',
        'banner_subtext' => 'Ігровий сервер · 0.8.2',
        'server' => 'Сервер 0.8.2',
        'title' => 'Завантажити та встановити',
        'lead' => 'Оберіть повний клієнт або файл підключення та дотримуйтесь інструкції.',
        'download_option' => 'Варіант завантаження',
        'client_tab' => 'Завантажити клієнт',
        'patch_tab' => 'Завантажити патч',
        'new' => 'НОВЕ',
        'client' => 'Клієнт',
        'client_title' => 'Клієнт для сервера 0.8.2',
        'client_description' => 'Повна версія клієнта для Windows. Розмір архіву — близько 2,4 ГБ.',
        'unavailable' => 'недоступно',
        'no_links' => 'Посилань поки немає',
        'important' => 'Важливо',
        'fresh_patch' => 'Встановіть свіжий патч',
        'preparation' => 'Підготовка',
        'install_client' => 'Встановлення клієнта',
        'video_note' => 'Детальна інструкція зі встановлення показана у відео нижче:',
        'video_fallback' => 'Ваш браузер не підтримує відтворення відео.',
        'patch' => 'Патч',
        'patch_title' => 'Файл підключення до сервера',
        'installation' => 'Встановлення',
        'four_steps' => 'Чотири кроки',
        'step_1' => 'Повністю закрийте гру та лаунчер, якщо вони відкриті.',
        'step_2' => 'Завантажте scripts_config.xml у картці завантаження.',
        'step_3' => 'Покладіть його в підпапку res\\, замінивши наявний файл: …\\res\\scripts_config.xml.',
        'step_4' => 'Запустіть гру — підключення готове.',
        'linux' => 'той самий файл покладіть у',
        'linux_tail' => 'усередині теки гри та запускайте як зазвичай через Wine/Proton. Опційно — лаунчер',
        'wine_note' => '(потрібен Wine ≥ 8 або Proton).',
        'safe_title' => 'Це безпечно.',
        'safe_note' => 'scripts_config.xml — звичайний текстовий файл, його можна відкрити «Блокнотом» і побачити адресу сервера. Системні файли Windows він не чіпає, права адміністратора не потрібні.',
    ],
    'en' => [
        'page_title' => 'Download client: Project Orion server 0.8.2',
        'page_description' => 'Download the Project Orion 0.8.2 server client. Install the client, register, and connect to the server.',
        'banner_subtext' => 'Game server · 0.8.2',
        'server' => 'Server 0.8.2',
        'title' => 'Download and install',
        'lead' => 'Choose the full client or the connection file and follow the instructions.',
        'download_option' => 'Download option',
        'client_tab' => 'Download client',
        'patch_tab' => 'Download patch',
        'new' => 'NEW',
        'client' => 'Client',
        'client_title' => 'Client for server 0.8.2',
        'client_description' => 'The full Windows client. The archive is approximately 2.4 GB.',
        'unavailable' => 'unavailable',
        'no_links' => 'No links yet',
        'important' => 'Important',
        'fresh_patch' => 'Install the latest patch',
        'preparation' => 'Preparation',
        'install_client' => 'Client installation',
        'video_note' => 'A detailed installation guide is shown in the video below:',
        'video_fallback' => 'Your browser does not support video playback.',
        'patch' => 'Patch',
        'patch_title' => 'Server connection file',
        'installation' => 'Installation',
        'four_steps' => 'Four steps',
        'step_1' => 'Close the game and launcher completely if they are open.',
        'step_2' => 'Download scripts_config.xml from the download card.',
        'step_3' => 'Put it in the res\\ subfolder, replacing the existing file: …\\res\\scripts_config.xml.',
        'step_4' => 'Launch the game: the connection is ready.',
        'linux' => 'Put the same file in',
        'linux_tail' => 'inside the game folder and launch it normally through Wine/Proton. An optional launcher is',
        'wine_note' => '(Wine ≥ 8 or Proton is required).',
        'safe_title' => 'This is safe.',
        'safe_note' => 'scripts_config.xml is a plain text file. You can open it in Notepad and see the server address. It does not touch Windows system files and does not require administrator rights.',
    ],
][$ui_lang];
$client_mirrors = get_setting_json($pdo, 'download_client_mirrors', [['name' => $ui_lang === 'en' ? 'Download from another site' : 'Скачать на другом сайте', 'url' => '', 'enabled' => false]]);
$patch_mirrors = get_setting_json($pdo, 'download_patch_mirrors', [['name' => $ui_lang === 'en' ? 'Download scripts_config.xml' : 'Скачать scripts_config.xml', 'url' => '', 'enabled' => false]]);
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
        <button type="button" id="dl-tab-patch" class="dl-tab dl-tab--patch" role="tab" data-tab="patch" aria-controls="pane-patch" aria-selected="false" tabindex="-1"><?php echo htmlspecialchars($copy['patch_tab'], ENT_QUOTES, 'UTF-8'); ?><span class="dl-tab-badge"><?php echo htmlspecialchars($copy['new'], ENT_QUOTES, 'UTF-8'); ?></span></button>
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

            <article class="download-card download-notice-card">
                <p class="eyebrow"><?php echo htmlspecialchars($copy['important'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2><?php echo htmlspecialchars($copy['fresh_patch'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="note-box warn">
                    <?php if ($ui_lang === 'en'): ?>
                        <b>Important:</b> the patch inside the game archive is <b>outdated</b>. The latest patch is on the <a href="#patch" onclick="dlTab('patch');return false;">“⚡ Download patch”</a> tab. Install it after unpacking the game or you will not be able to connect to the server.
                    <?php else: ?>
                        <b>Важно:</b> патч внутри архива с игрой — <b>устаревший</b>. Актуальный патч находится на вкладке <a href="#patch" onclick="dlTab('patch');return false;">«⚡ Скачать патч»</a> — обязательно установите его после распаковки игры, иначе подключиться к серверу не получится.
                    <?php endif; ?>
                </div>
                <div class="note-box warn">
                    <?php if ($ui_lang === 'en'): ?>
                        <b>Warning:</b> the link downloads an archive hosted on a <b>third-party file host</b>. This archive is <b>not connected</b> with our website or project: we <b>did not create, host, or store it</b> on our servers and only provide the external link for convenience. All rights to the archive contents belong to their lawful owners. You download it <b>independently and at your own risk</b>.
                    <?php else: ?>
                        <b>Внимание:</b> по ссылке скачивается архив, размещённый на <b>стороннем файлообменнике</b>. Этот архив <b>никак не связан</b> с нашим сайтом и проектом: мы его <b>не создавали, не размещаем и не храним</b> на своих серверах, а лишь приводим внешнюю ссылку для удобства. Все права на содержимое архива принадлежат их законным правообладателям. Скачивая файл, вы делаете это <b>самостоятельно и на свой страх и риск</b>.
                    <?php endif; ?>
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

    <div class="dl-pane" id="pane-patch" role="tabpanel" aria-labelledby="dl-tab-patch" hidden>
        <section class="download-options">
            <article class="download-card download-card--accent">
                <p class="eyebrow"><?php echo htmlspecialchars($copy['patch'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2><?php echo htmlspecialchars($copy['patch_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <?php if ($ui_lang === 'en'): ?>
                    <p>If a compatible client is <b>already installed</b>, you do not need to download it again. <b>One file</b>, <code class="kbd">scripts_config.xml</code>, writes the server address directly into the client. No <code class="kbd">hosts</code> file, administrator rights, or <code class="kbd">.bat</code> file is needed: it works on both Windows and Linux.</p>
                <?php else: ?>
                    <p>Если совместимый клиент <b>уже установлен</b> — не нужно скачивать его заново. <b>Один файл</b> <code class="kbd">scripts_config.xml</code> прописывает адрес сервера прямо в клиент. Никаких <code class="kbd">hosts</code>, прав администратора и <code class="kbd">.bat</code> — работает и на Windows, и на Linux.</p>
                <?php endif; ?>
                <div class="download-links">
                    <?php foreach ($patch_mirrors as $mirror): ?>
                    <?php if (!empty($mirror['enabled'])): ?>
                    <a href="<?php echo htmlspecialchars($mirror['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-primary" data-i18n-ignore><?php echo htmlspecialchars($mirror['name'], ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php else: ?>
                    <button type="button" class="btn btn-disabled" disabled data-i18n-ignore><?php echo htmlspecialchars($mirror['name'], ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($copy['unavailable'], ENT_QUOTES, 'UTF-8'); ?></button>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($patch_mirrors)): ?>
                    <button type="button" class="btn btn-disabled" disabled><?php echo htmlspecialchars($copy['no_links'], ENT_QUOTES, 'UTF-8'); ?></button>
                    <?php endif; ?>
                </div>
            </article>

            <article class="download-card">
                <p class="eyebrow"><?php echo htmlspecialchars($copy['installation'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2><?php echo htmlspecialchars($copy['four_steps'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="install-steps">
                    <ol class="steps">
                        <?php if ($ui_lang === 'en'): ?>
                            <li>Close the game and launcher completely if they are open.</li>
                            <li>Download <code class="kbd">scripts_config.xml</code> from the download card.</li>
                            <li>Put it in the <code class="kbd">res\</code> subfolder, replacing the existing file: <code class="kbd">…\res\scripts_config.xml</code>.</li>
                            <li>Launch the game: the connection is ready.</li>
                        <?php else: ?>
                            <li>Полностью закройте игру и лаунчер, если они открыты.</li>
                            <li>Скачайте <code class="kbd">scripts_config.xml</code> в карточке загрузки.</li>
                            <li>Положите его в подпапку <code class="kbd">res\</code>, заменив существующий файл: <code class="kbd">…\res\scripts_config.xml</code>.</li>
                            <li>Запустите игру — подключение готово.</li>
                        <?php endif; ?>
                    </ol>
                </div>
            </article>
        </section>

        <section class="patch-notes">
            <div class="note-box">
                <b>Linux (Wine / Proton):</b> <?php echo htmlspecialchars($copy['linux'], ENT_QUOTES, 'UTF-8'); ?> <code class="kbd">res/</code> <?php echo htmlspecialchars($copy['linux_tail'], ENT_QUOTES, 'UTF-8'); ?> <a href="patch/get.php?f=play_linux">play.sh</a> <?php echo htmlspecialchars($copy['wine_note'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="note-box">
                <b><?php echo htmlspecialchars($copy['safe_title'], ENT_QUOTES, 'UTF-8'); ?></b> <?php echo htmlspecialchars($copy['safe_note'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </section>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
