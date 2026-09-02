<?php
$footer_extra = $footer_extra ?? '';
$page_scripts = $page_scripts ?? [];
$footer_lang = function_exists('current_lang') ? current_lang() : 'ru';
$footer_url = static function ($path) use ($footer_lang) {
    $url = function_exists('i18n_locale_path') ? i18n_locale_path($path, $footer_lang) : $path;
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
};
$footer_state = isset($pdo) && function_exists('orion_server_state')
    ? orion_server_state($pdo)
    : ['is_online' => true];
// Заголовки колонок и подпись статуса — структурная разметка подвала,
// поэтому локали заданы явными ветками, а не картой фраз.
$footer_copy = [
    'ru' => ['sections' => 'Разделы', 'help' => 'Помощь', 'community' => 'Сообщество', 'online' => 'Серверы работают', 'offline' => 'Серверы на паузе'],
    'uk' => ['sections' => 'Розділи', 'help' => 'Допомога', 'community' => 'Спільнота', 'online' => 'Сервери працюють', 'offline' => 'Сервери на паузі'],
    'en' => ['sections' => 'Sections', 'help' => 'Help', 'community' => 'Community', 'online' => 'Servers are up', 'offline' => 'Servers are paused'],
][$footer_lang] ?? [];
?>
<footer class="site-footer">
    <div class="site-footer-inner">
        <div class="site-footer-brand">
            <strong>PROJECT ORION</strong>
            <p>Сайт создан для демонстрации и тестирования сервера Project Orion 0.8.2.</p>
            <p>Независимый некоммерческий проект, созданный сообществом энтузиастов.</p>
            <div class="site-footer-status<?php echo $footer_state['is_online'] ? '' : ' site-footer-status--offline'; ?>"><i aria-hidden="true"></i><?php echo htmlspecialchars($footer_state['is_online'] ? $footer_copy['online'] : $footer_copy['offline'], ENT_QUOTES, 'UTF-8'); ?> · 0.8.2</div>
        </div>
        <nav aria-label="Ссылки в подвале">
            <span class="site-footer-heading"><?php echo htmlspecialchars($footer_copy['sections'], ENT_QUOTES, 'UTF-8'); ?></span>
            <a href="<?php echo $footer_url('download.php'); ?>">Скачать</a>
            <a href="<?php echo $footer_url('players.php'); ?>"<?php echo nav_active('players', $active_page) ? ' class="active"' : ''; ?>>Игроки</a>
            <a href="<?php echo $footer_url('subscriptions.php'); ?>"<?php echo nav_active('subscriptions', $active_page) ? ' class="active"' : ''; ?>>Подписки</a>
            <a href="<?php echo $footer_url('contracts.php'); ?>"<?php echo nav_active('contracts', $active_page) ? ' class="active"' : ''; ?>>Контракты</a>
            <a href="<?php echo $footer_url('gso.php'); ?>"<?php echo nav_active('gso', $active_page) ? ' class="active"' : ''; ?>>ГСО</a>
            <a href="<?php echo $footer_url('markets.php'); ?>"<?php echo nav_active('markets', $active_page) ? ' class="active"' : ''; ?>>Рынки</a>
        </nav>
        <nav aria-label="Помощь и сообщество">
            <span class="site-footer-heading"><?php echo htmlspecialchars($footer_copy['help'], ENT_QUOTES, 'UTF-8'); ?></span>
            <a href="<?php echo $footer_url('petitions.php'); ?>"<?php echo nav_active('petitions', $active_page) ? ' class="active"' : ''; ?>><?php echo $footer_lang === 'uk' ? 'Пропозиції' : ($footer_lang === 'en' ? 'Proposals' : 'Предложения'); ?></a>
            <a href="<?php echo $footer_url('roadmap.php'); ?>"<?php echo nav_active('roadmap', $active_page) ? ' class="active"' : ''; ?>>Роадмап</a>
            <a href="<?php echo $footer_url('bugs.php'); ?>">Баг-репорты</a>
            <a href="<?php echo $footer_url('changelog.php'); ?>">Обновления</a>
            <a href="<?php echo $footer_url('donate.php'); ?>">Поддержать</a>
            <a href="<?php echo $footer_url('legal.php'); ?>" class="footer-legal<?php echo nav_active('legal', $active_page); ?>">Правовая информация</a>
        </nav>
        <div class="site-footer-community">
            <span class="site-footer-heading"><?php echo htmlspecialchars($footer_copy['community'], ENT_QUOTES, 'UTF-8'); ?></span>
            <div class="site-footer-socials">
                <a href="https://discord.gg/fM4Dqess6r" target="_blank" rel="noopener noreferrer" aria-label="Discord"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 5.3A16 16 0 0 0 16 4l-.5 1a15 15 0 0 0-7 0L8 4a16 16 0 0 0-4 1.3C1.6 9 1 12.6 1.3 16.2A16 16 0 0 0 6.2 19l1-1.6a10 10 0 0 1-1.6-.8l.4-.3a11.4 11.4 0 0 0 12 0l.4.3c-.5.3-1 .6-1.6.8l1 1.6a16 16 0 0 0 4.9-2.8c.4-4.2-.7-7.8-2.7-10.9zM8.7 14.2c-1 0-1.7-.9-1.7-2s.7-2 1.7-2 1.8.9 1.7 2c0 1.1-.7 2-1.7 2zm6.6 0c-1 0-1.7-.9-1.7-2s.7-2 1.7-2 1.8.9 1.7 2c0 1.1-.7 2-1.7 2z"/></svg></a>
                <a href="<?php echo $footer_url('download.php'); ?>" aria-label="Скачать клиент"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3v10.6l4-4 1.4 1.4L11 18 4.6 11l1.4-1.4 4 4V3zM4 19h16v2H4z"/></svg></a>
                <a href="<?php echo $footer_url('bugs.php'); ?>" aria-label="Баг-репорты"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a5 5 0 0 1 5 5v1h2v2h-2v3h3v2h-3v1a5 5 0 0 1-10 0v-1H4v-2h3v-3H5V8h2V7a5 5 0 0 1 5-5zm-1 7v8h2V9z"/></svg></a>
            </div>
        </div>
        <small>&copy; 2026 Project Orion 0.8.2.</small>
    </div>
</footer>
<?php echo $footer_extra; ?>
<script src="js/site.js?v=6"></script>
<script src="js/animations.js?v=2"></script>
<script src="js/front.js?v=2"></script>
<?php foreach ($page_scripts as $script): ?>
    <script src="<?php echo htmlspecialchars($script, ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php endforeach; ?>
</body>
</html>
