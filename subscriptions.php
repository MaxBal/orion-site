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
        'page_title' => 'Подписки Project Orion — Orion Lite, Plus, Pro и Max',
        'page_description' => 'Выберите еженедельную подписку Project Orion: Orion Lite, Plus, Pro или Max.',
        'banner_subtext' => 'Подписки Project Orion · 0.8.2',
        'preview' => 'Предварительный просмотр',
        'hero_title' => 'Выберите свою орбиту',
        'hero_lead' => 'Четыре уровня подписки для тех, кто хочет быть ближе к Project Orion. Выберите подходящий темп — от лёгкого старта до максимального уровня.',
        'visual_note' => 'Сейчас это визуальный макет.',
        'activation_note' => 'Оплата и активация подписок появятся на следующем этапе.',
        'four_levels' => 'ЧЕТЫРЕ УРОВНЯ',
        'find_plan' => 'Найдите свой тариф',
        'weekly_cost' => 'Стоимость указана за одну неделю.',
        'plans_aria' => 'Тарифы подписки',
        'level_01' => 'Уровень 01',
        'level_02' => 'Уровень 02',
        'level_03' => 'Уровень 03',
        'level_04' => 'Уровень 04',
        'lite_summary' => 'Лёгкий способ начать знакомство с подписками Orion.',
        'plus_summary' => 'Расширенный уровень для тех, кто чаще бывает на орбите Orion.',
        'pro_summary' => 'Продвинутый уровень для активных участников проекта.',
        'max_summary' => 'Максимальный уровень для тех, кто хочет быть на главной орбите Orion.',
        'per_week' => 'в неделю',
        'includes' => 'В тарифе',
        'one_week' => 'Подписка на 1 неделю',
        'status_lite' => 'Статус Orion Lite',
        'status_plus' => 'Статус Orion Plus',
        'status_pro' => 'Статус Orion Pro',
        'status_max' => 'Статус Orion Max',
        'benefits_soon' => 'Состав преимуществ — скоро',
        'available_soon' => 'Скоро доступно',
        'next_stage' => 'СЛЕДУЮЩИЙ ЭТАП',
        'launchpad' => 'Интерфейс уже на стартовой площадке',
        'next_note' => 'Структура тарифов готова. Подключение оплаты, активации и точного списка преимуществ будет добавлено отдельно.',
        'create_account' => 'Создать аккаунт',
    ],
    'uk' => [
        'page_title' => 'Підписки Project Orion — Orion Lite, Plus, Pro та Max',
        'page_description' => 'Оберіть щотижневу підписку Project Orion: Orion Lite, Plus, Pro або Max.',
        'banner_subtext' => 'Підписки Project Orion · 0.8.2',
        'preview' => 'Попередній перегляд',
        'hero_title' => 'Оберіть свою орбіту',
        'hero_lead' => 'Чотири рівні підписки для тих, хто хоче бути ближче до Project Orion. Оберіть відповідний темп — від легкого старту до максимального рівня.',
        'visual_note' => 'Зараз це візуальний макет.',
        'activation_note' => 'Оплата та активація підписок зʼявляться на наступному етапі.',
        'four_levels' => 'ЧОТИРИ РІВНІ',
        'find_plan' => 'Знайдіть свій тариф',
        'weekly_cost' => 'Вартість вказана за один тиждень.',
        'plans_aria' => 'Тарифи підписки',
        'level_01' => 'Рівень 01',
        'level_02' => 'Рівень 02',
        'level_03' => 'Рівень 03',
        'level_04' => 'Рівень 04',
        'lite_summary' => 'Легкий спосіб почати знайомство з підписками Orion.',
        'plus_summary' => 'Розширений рівень для тих, хто частіше буває на орбіті Orion.',
        'pro_summary' => 'Просунутий рівень для активних учасників проєкту.',
        'max_summary' => 'Максимальний рівень для тих, хто хоче бути на головній орбіті Orion.',
        'per_week' => 'на тиждень',
        'includes' => 'У тарифі',
        'one_week' => 'Підписка на 1 тиждень',
        'status_lite' => 'Статус Orion Lite',
        'status_plus' => 'Статус Orion Plus',
        'status_pro' => 'Статус Orion Pro',
        'status_max' => 'Статус Orion Max',
        'benefits_soon' => 'Перелік переваг — незабаром',
        'available_soon' => 'Незабаром буде доступно',
        'next_stage' => 'НАСТУПНИЙ ЕТАП',
        'launchpad' => 'Інтерфейс уже на стартовому майданчику',
        'next_note' => 'Структура тарифів готова. Підключення оплати, активації та точного переліку переваг буде додано окремо.',
        'create_account' => 'Створити акаунт',
    ],
    'en' => [
        'page_title' => 'Project Orion memberships: Lite, Plus, Pro, and Max',
        'page_description' => 'Choose a weekly Project Orion membership: Orion Lite, Plus, Pro, or Max.',
        'banner_subtext' => 'Project Orion memberships · 0.8.2',
        'preview' => 'Preview',
        'hero_title' => 'Choose your orbit',
        'hero_lead' => 'Four membership tiers for those who want to stay closer to Project Orion. Choose your pace, from a light start to the highest tier.',
        'visual_note' => 'This is currently a visual mockup.',
        'activation_note' => 'Membership payments and activation will be added in the next stage.',
        'four_levels' => 'FOUR LEVELS',
        'find_plan' => 'Find your plan',
        'weekly_cost' => 'Price shown per week.',
        'plans_aria' => 'Membership plans',
        'level_01' => 'Level 01',
        'level_02' => 'Level 02',
        'level_03' => 'Level 03',
        'level_04' => 'Level 04',
        'lite_summary' => 'A light way to start exploring Orion memberships.',
        'plus_summary' => 'An expanded tier for those who spend more time in Orion orbit.',
        'pro_summary' => 'An advanced tier for active project participants.',
        'max_summary' => 'The highest tier for those who want to stay in Orion’s main orbit.',
        'per_week' => 'per week',
        'includes' => 'Includes',
        'one_week' => '1-week membership',
        'status_lite' => 'Orion Lite status',
        'status_plus' => 'Orion Plus status',
        'status_pro' => 'Orion Pro status',
        'status_max' => 'Orion Max status',
        'benefits_soon' => 'Benefits list coming soon',
        'available_soon' => 'Coming soon',
        'next_stage' => 'NEXT STAGE',
        'launchpad' => 'The interface is already on the launchpad',
        'next_note' => 'The tier structure is ready. Payment, activation, and the final benefits list will be added separately.',
        'create_account' => 'Create account',
    ],
][$ui_lang];
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'subscriptions.php';
$active_page = 'subscriptions';
$banner_subtext = $copy['banner_subtext'];
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell subscriptions-page">
    <section class="subscriptions-hero" aria-labelledby="subscriptionsTitle">
        <div class="subscriptions-hero-copy">
            <div class="subscriptions-hero-topline">
                <p class="eyebrow">ORION MEMBERSHIP</p>
                <span class="subscriptions-preview-badge"><i aria-hidden="true"></i><?php echo htmlspecialchars($copy['preview'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <h1 id="subscriptionsTitle"><?php echo htmlspecialchars($copy['hero_title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p><?php echo htmlspecialchars($copy['hero_lead'], ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="subscriptions-hero-note">
                <span aria-hidden="true">01</span>
                <p><strong><?php echo htmlspecialchars($copy['visual_note'], ENT_QUOTES, 'UTF-8'); ?></strong> <?php echo htmlspecialchars($copy['activation_note'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
        <div class="subscriptions-orbit" aria-hidden="true">
            <span class="subscriptions-orbit-core">ORION</span>
            <i></i><i></i><i></i><i></i>
        </div>
    </section>

    <section class="subscriptions-catalog" aria-labelledby="plansTitle">
        <header class="subscriptions-section-heading">
            <div>
                <p class="eyebrow"><?php echo htmlspecialchars($copy['four_levels'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2 id="plansTitle"><?php echo htmlspecialchars($copy['find_plan'], ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>
            <p><?php echo htmlspecialchars($copy['weekly_cost'], ENT_QUOTES, 'UTF-8'); ?></p>
        </header>

        <div class="subscriptions-grid" aria-label="<?php echo htmlspecialchars($copy['plans_aria'], ENT_QUOTES, 'UTF-8'); ?>">
            <article class="subscription-card subscription-card--lite reveal" id="lite" style="--reveal-i: 0">
                <div class="subscription-card-head">
                    <span class="subscription-level"><?php echo htmlspecialchars($copy['level_01'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="subscription-mark" aria-hidden="true"><i></i></span>
                </div>
                <div class="subscription-name"><p>ORION</p><h3>Lite</h3></div>
                <p class="subscription-summary"><?php echo htmlspecialchars($copy['lite_summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="subscription-price"><strong>$1</strong><span>/ <?php echo htmlspecialchars($copy['per_week'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                <div class="subscription-divider"></div>
                <p class="subscription-includes"><?php echo htmlspecialchars($copy['includes'], ENT_QUOTES, 'UTF-8'); ?></p>
                <ul class="subscription-features">
                    <li><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['one_week'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['status_lite'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <li class="is-pending"><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['benefits_soon'], ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
                <button class="btn btn-secondary btn-block subscription-button" type="button" disabled><?php echo htmlspecialchars($copy['available_soon'], ENT_QUOTES, 'UTF-8'); ?></button>
            </article>

            <article class="subscription-card subscription-card--plus reveal" id="plus" style="--reveal-i: 1">
                <div class="subscription-card-head">
                    <span class="subscription-level"><?php echo htmlspecialchars($copy['level_02'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="subscription-mark" aria-hidden="true"><i></i></span>
                </div>
                <div class="subscription-name"><p>ORION</p><h3>Plus</h3></div>
                <p class="subscription-summary"><?php echo htmlspecialchars($copy['plus_summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="subscription-price"><strong>$5</strong><span>/ <?php echo htmlspecialchars($copy['per_week'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                <div class="subscription-divider"></div>
                <p class="subscription-includes"><?php echo htmlspecialchars($copy['includes'], ENT_QUOTES, 'UTF-8'); ?></p>
                <ul class="subscription-features">
                    <li><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['one_week'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['status_plus'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <li class="is-pending"><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['benefits_soon'], ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
                <button class="btn btn-secondary btn-block subscription-button" type="button" disabled><?php echo htmlspecialchars($copy['available_soon'], ENT_QUOTES, 'UTF-8'); ?></button>
            </article>

            <article class="subscription-card subscription-card--pro reveal" id="pro" style="--reveal-i: 2">
                <div class="subscription-card-head">
                    <span class="subscription-level"><?php echo htmlspecialchars($copy['level_03'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="subscription-mark" aria-hidden="true"><i></i></span>
                </div>
                <div class="subscription-name"><p>ORION</p><h3>Pro</h3></div>
                <p class="subscription-summary"><?php echo htmlspecialchars($copy['pro_summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="subscription-price"><strong>$10</strong><span>/ <?php echo htmlspecialchars($copy['per_week'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                <div class="subscription-divider"></div>
                <p class="subscription-includes"><?php echo htmlspecialchars($copy['includes'], ENT_QUOTES, 'UTF-8'); ?></p>
                <ul class="subscription-features">
                    <li><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['one_week'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['status_pro'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <li class="is-pending"><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['benefits_soon'], ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
                <button class="btn btn-secondary btn-block subscription-button" type="button" disabled><?php echo htmlspecialchars($copy['available_soon'], ENT_QUOTES, 'UTF-8'); ?></button>
            </article>

            <article class="subscription-card subscription-card--max reveal" id="max" style="--reveal-i: 3">
                <div class="subscription-card-head">
                    <span class="subscription-level"><?php echo htmlspecialchars($copy['level_04'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="subscription-mark" aria-hidden="true"><i></i></span>
                </div>
                <div class="subscription-name"><p>ORION</p><h3>Max</h3></div>
                <p class="subscription-summary"><?php echo htmlspecialchars($copy['max_summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="subscription-price"><strong>$20</strong><span>/ <?php echo htmlspecialchars($copy['per_week'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                <div class="subscription-divider"></div>
                <p class="subscription-includes"><?php echo htmlspecialchars($copy['includes'], ENT_QUOTES, 'UTF-8'); ?></p>
                <ul class="subscription-features">
                    <li><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['one_week'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['status_max'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <li class="is-pending"><span aria-hidden="true"></span><?php echo htmlspecialchars($copy['benefits_soon'], ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
                <button class="btn btn-secondary btn-block subscription-button" type="button" disabled><?php echo htmlspecialchars($copy['available_soon'], ENT_QUOTES, 'UTF-8'); ?></button>
            </article>
        </div>
    </section>

    <section class="subscriptions-next reveal" aria-labelledby="subscriptionsNextTitle">
        <span class="subscriptions-next-index" aria-hidden="true">NEXT</span>
        <div>
            <p class="eyebrow"><?php echo htmlspecialchars($copy['next_stage'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h2 id="subscriptionsNextTitle"><?php echo htmlspecialchars($copy['launchpad'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><?php echo htmlspecialchars($copy['next_note'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <a class="btn btn-primary" href="<?php echo htmlspecialchars($ui_lang === 'ru' ? 'register.php' : 'register.php?lang=' . rawurlencode($ui_lang), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['create_account'], ENT_QUOTES, 'UTF-8'); ?></a>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
