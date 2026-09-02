<?php
require_once 'db.php';
require_once __DIR__ . '/includes/roadmap_data.php';

function roadmap_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$roadmap = orion_roadmap_data();
$lang = function_exists('current_lang') ? current_lang() : 'ru';
$lang = in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'ru';
$is_uk = $lang === 'uk';
$is_en = $lang === 'en';
$page_title = [
    'ru' => 'Роадмап сервера 0.8.2 — Project Orion',
    'uk' => 'Роадмап сервера 0.8.2 — Project Orion',
    'en' => 'Orion server roadmap 0.8.2 — Project Orion',
][$lang];
$page_description = $roadmap['description'];
$page_path = 'roadmap.php';
$active_page = 'roadmap';
$banner_subtext = null;
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell roadmap-page">
    <section class="roadmap-hero" aria-labelledby="roadmapTitle">
        <div class="roadmap-hero-orbit" aria-hidden="true"><span></span><span></span><span></span></div>
        <div class="roadmap-hero-copy">
            <div class="roadmap-live-chip"><span aria-hidden="true"></span><?php echo roadmap_h($roadmap['updated']); ?></div>
            <p class="eyebrow"><?php echo roadmap_h($roadmap['eyebrow']); ?></p>
            <h1 id="roadmapTitle"><?php echo roadmap_h($roadmap['title']); ?></h1>
            <p class="roadmap-hero-lead"><?php echo roadmap_h($roadmap['description']); ?></p>
            <div class="roadmap-hero-actions">
                <a class="btn btn-primary" href="<?php echo roadmap_h(i18n_locale_path('roadmap.php#roadmapTimeline')); ?>"><?php echo roadmap_h($roadmap['all_stages_label']); ?></a>
                <a class="btn btn-secondary" href="<?php echo roadmap_h(i18n_locale_path('download.php')); ?>"><?php echo roadmap_h(['ru' => 'Начать играть', 'uk' => 'Почати грати', 'en' => 'Start playing'][$lang]); ?></a>
            </div>
        </div>

        <aside class="roadmap-progress-panel" aria-label="<?php echo roadmap_h($roadmap['progress_label']); ?>">
            <div class="roadmap-progress-head">
                <div>
                    <span><?php echo roadmap_h($roadmap['progress_label']); ?></span>
                    <strong><?php echo intval($roadmap['progress_value']); ?>%</strong>
                </div>
                <span class="roadmap-progress-code">ORION://0.8.2</span>
            </div>
            <div class="roadmap-progress-track" aria-hidden="true">
                <span style="--roadmap-progress: <?php echo intval($roadmap['progress_value']); ?>%"></span>
            </div>
            <p><?php echo roadmap_h($roadmap['progress_note']); ?></p>
            <?php $current_phase = orion_roadmap_phase($roadmap, $roadmap['current_id']); ?>
            <a class="roadmap-current-focus" href="<?php echo roadmap_h(i18n_locale_path('roadmap.php#' . $roadmap['current_id'])); ?>">
                <span class="roadmap-current-icon" aria-hidden="true"></span>
                <span><small><?php echo roadmap_h($roadmap['current_label']); ?></small><strong><?php echo roadmap_h($current_phase['title']); ?></strong></span>
                <span aria-hidden="true">→</span>
            </a>
        </aside>
    </section>

    <section class="roadmap-summary-grid" aria-label="<?php echo roadmap_h(['ru' => 'Коротко о плане', 'uk' => 'Коротко про план', 'en' => 'Plan at a glance'][$lang]); ?>">
        <?php foreach ($roadmap['summary'] as $item): ?>
            <article class="roadmap-summary-card reveal">
                <strong><?php echo roadmap_h($item['value']); ?></strong>
                <span><?php echo roadmap_h($item['label']); ?></span>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="roadmap-timeline-section" id="roadmapTimeline">
        <header class="roadmap-section-heading">
            <div>
                <p class="eyebrow"><?php echo roadmap_h($roadmap['timeline_label']); ?></p>
                <h2><?php echo roadmap_h($roadmap['timeline_title']); ?></h2>
            </div>
            <p><?php echo roadmap_h($roadmap['timeline_description']); ?></p>
        </header>

        <div class="roadmap-legend" aria-label="<?php echo roadmap_h(['ru' => 'Обозначения статусов', 'uk' => 'Позначення статусів', 'en' => 'Status legend'][$lang]); ?>">
            <?php foreach (['done', 'current', 'planned', 'goal'] as $status): ?>
                <span class="roadmap-status roadmap-status--<?php echo $status; ?>"><i aria-hidden="true"></i><?php echo roadmap_h($roadmap['statuses'][$status]); ?></span>
            <?php endforeach; ?>
        </div>

        <div class="roadmap-timeline">
            <?php foreach ($roadmap['phases'] as $phase): ?>
                <article class="roadmap-phase roadmap-phase--<?php echo roadmap_h($phase['status']); ?><?php echo in_array($phase['id'], ['battle', 'launch'], true) ? ' roadmap-phase--battle' : ''; ?> reveal" id="<?php echo roadmap_h($phase['id']); ?>">
                    <div class="roadmap-phase-meta">
                        <strong><?php echo roadmap_h($phase['phase']); ?></strong>
                        <span><?php echo roadmap_h($phase['period']); ?></span>
                    </div>
                    <div class="roadmap-phase-rail" aria-hidden="true">
                        <span><?php echo $phase['status'] === 'done' ? '✓' : roadmap_h($phase['number']); ?></span>
                    </div>
                    <div class="roadmap-phase-card">
                        <header class="roadmap-phase-header">
                            <div>
                                <span class="roadmap-status roadmap-status--<?php echo roadmap_h($phase['status']); ?>"><i aria-hidden="true"></i><?php echo roadmap_h($roadmap['statuses'][$phase['status']]); ?></span>
                                <h3><?php echo roadmap_h($phase['title']); ?></h3>
                            </div>
                            <?php if (!empty($phase['meta'])): ?>
                                <div class="roadmap-phase-chips">
                                    <?php foreach ($phase['meta'] as $meta): ?><span><?php echo roadmap_h($meta); ?></span><?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </header>
                        <p class="roadmap-phase-lead"><?php echo roadmap_h($phase['lead']); ?></p>
                        <div class="roadmap-phase-divider"><span><?php echo roadmap_h($roadmap['details_label']); ?></span></div>
                        <ul class="roadmap-task-list">
                            <?php foreach ($phase['tasks'] as $task): ?>
                                <li><span aria-hidden="true"></span><p><?php echo roadmap_h($task); ?></p></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="roadmap-final-cta reveal">
        <div>
            <p class="eyebrow">PROJECT ORION</p>
            <h2><?php echo roadmap_h(['ru' => 'Следи за прогрессом. Заходи в игру.', 'uk' => 'Слідкуй за прогресом. Заходь у гру.', 'en' => 'Track the progress. Join the game.'][$lang]); ?></h2>
            <p><?php echo roadmap_h(['ru' => 'Роадмап будет обновляться вместе с разработкой сервера.', 'uk' => 'Роадмап оновлюватиметься разом із розробкою сервера.', 'en' => 'The roadmap will be updated as the server develops.'][$lang]); ?></p>
        </div>
        <a class="btn btn-primary" href="<?php echo roadmap_h(i18n_locale_path('download.php')); ?>"><?php echo roadmap_h(['ru' => 'Скачать клиент', 'uk' => 'Завантажити клієнт', 'en' => 'Download client'][$lang]); ?></a>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
