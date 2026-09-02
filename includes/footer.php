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
// Заголовки и подписи — структурная разметка подвала,
// поэтому локали заданы явными ветками, а не картой фраз.
$footer_copy = [
    'ru' => ['sections' => 'Разделы', 'help' => 'Помощь', 'online' => 'Серверы работают', 'offline' => 'Серверы на паузе', 'copyright' => '© 2026 Project Orion 0.8.2. Все права защищены.', 'desc' => 'Независимый некоммерческий проект, созданный сообществом энтузиастов.'],
    'uk' => ['sections' => 'Розділи', 'help' => 'Допомога', 'online' => 'Сервери працюють', 'offline' => 'Сервери на паузі', 'copyright' => '© 2026 Project Orion 0.8.2. Всі права захищені.', 'desc' => 'Незалежний некомерційний проект, створений спільнотою ентузіастів.'],
    'en' => ['sections' => 'Sections', 'help' => 'Help', 'online' => 'Servers are up', 'offline' => 'Servers are paused', 'copyright' => '© 2026 Project Orion 0.8.2. All rights reserved.', 'desc' => 'Independent non-profit project created by a community of enthusiasts.'],
][$footer_lang] ?? [];
?>
<footer class="b-footer">
    <div class="b-footer-inner">
        <div class="b-footer-top">
            <nav class="b-footer-nav" aria-label="<?php echo htmlspecialchars($footer_copy['sections'], ENT_QUOTES, 'UTF-8'); ?>">
                <span class="b-footer-heading"><?php echo htmlspecialchars($footer_copy['sections'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="<?php echo $footer_url('download.php'); ?>">Скачать</a>
                <a href="<?php echo $footer_url('players.php'); ?>">Игроки</a>
                <a href="<?php echo $footer_url('subscriptions.php'); ?>">Подписки</a>
                <a href="<?php echo $footer_url('changelog.php'); ?>">Обновления</a>
                <a href="<?php echo $footer_url('roadmap.php'); ?>">Роадмап</a>
            </nav>
            <nav class="b-footer-nav" aria-label="<?php echo htmlspecialchars($footer_copy['help'], ENT_QUOTES, 'UTF-8'); ?>">
                <span class="b-footer-heading"><?php echo htmlspecialchars($footer_copy['help'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="<?php echo $footer_url('petitions.php'); ?>"><?php echo $footer_lang === 'uk' ? 'Пропозиції' : ($footer_lang === 'en' ? 'Proposals' : 'Предложения'); ?></a>
                <a href="<?php echo $footer_url('bugs.php'); ?>">Баг-репорты</a>
                <a href="<?php echo $footer_url('donate.php'); ?>">Поддержать</a>
                <a href="<?php echo $footer_url('legal.php'); ?>">Правовая информация</a>
            </nav>
            <div class="b-footer-status-block">
                <div class="b-footer-status<?php echo $footer_state['is_online'] ? '' : ' b-footer-status--offline'; ?>">
                    <i aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($footer_state['is_online'] ? $footer_copy['online'] : $footer_copy['offline'], ENT_QUOTES, 'UTF-8'); ?> · 0.8.2</span>
                </div>
                <p class="b-footer-desc"><?php echo htmlspecialchars($footer_copy['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
        <div class="b-footer-bottom">
            <small><?php echo htmlspecialchars($footer_copy['copyright'], ENT_QUOTES, 'UTF-8'); ?></small>
        </div>
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
