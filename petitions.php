<?php
require_once 'db.php';
require_once 'recaptcha.php';

function petitions_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function petitions_set_flash($type, $message) {
    $_SESSION['petitions_flash'] = [
        'type' => $type === 'danger' ? 'danger' : 'success',
        'message' => (string)$message,
    ];
}

function petitions_redirect($petition_id = 0) {
    $location = 'petitions.php';
    if (intval($petition_id) > 0) {
        $location .= '#petition-' . intval($petition_id);
    }
    header('Location: ' . i18n_locale_path($location), true, 303);
    exit;
}

function petitions_localize_error($message, $lang) {
    return function_exists('player_petition_localize_message')
        ? player_petition_localize_message($message, $lang)
        : $message;
}

$lang = function_exists('current_lang') ? current_lang() : 'ru';
$lang = in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'ru';
$is_uk = $lang === 'uk';
$is_en = $lang === 'en';
$copy = $is_uk ? [
    'eyebrow' => 'ГОЛОС ГРАВЦІВ',
    'title' => 'Пропозиції, які доходять до керівництва',
    'lead' => 'Будь-який гравець може запропонувати зміну для Project Orion. Спочатку ініціатива збирає 50 підписів, а потім автоматично передається до ГСО на голосування команди.',
    'threshold' => 'підписів до передачі керівництву',
    'step_1' => 'Створіть',
    'step_1_note' => 'Опишіть ідею конкретно.',
    'step_2' => 'Зберіть 50',
    'step_2_note' => 'Один акаунт — один підпис.',
    'step_3' => 'Передача в ГСО',
    'step_3_note' => 'Керівництво голосує відкрито.',
    'submit_title' => 'Створити пропозицію',
    'submit_note' => 'Після створення ви одразу отримуєте перший підпис. Не створюйте дублікати однієї ідеї.',
    'title_label' => 'Назва пропозиції',
    'title_placeholder' => 'Наприклад: додати новий режим бою',
    'description_label' => 'Що саме пропонується',
    'description_placeholder' => 'Опишіть проблему, рішення та важливі деталі.',
    'expected_label' => 'Очікуваний результат',
    'expected_placeholder' => 'Як має змінитися гра або сайт після реалізації?',
    'submit' => 'Опублікувати пропозицію',
    'login_to_submit' => 'Щоб створити пропозицію, потрібно увійти в акаунт.',
    'login_required' => 'Щоб підписати пропозицію, потрібно увійти в акаунт.',
    'recaptcha_error' => 'Будь ласка, підтвердьте, що ви не робот (reCAPTCHA).',
    'login' => 'Увійти в акаунт',
    'board_eyebrow' => 'ВІДКРИТІ ІНІЦІАТИВИ',
    'board_title' => 'Пропозиції гравців',
    'board_note' => 'Підтримайте ідеї інших гравців або відкрийте їхній шлях до рішення керівництва.',
    'petition_id' => 'ІНІЦІАТИВА',
    'author' => 'Автор',
    'created' => 'Створено',
    'signatures' => 'підписів',
    'of' => 'з',
    'sign' => 'Підписати',
    'signed' => 'Ви вже підписали',
    'login_to_sign' => 'Увійдіть, щоб підписати',
    'open_gso' => 'Відкрити рішення в ГСО',
    'empty' => 'Пропозицій поки немає. Створіть першу ініціативу.',
    'created_success' => 'Пропозицію створено. Тепер вона збирає підписи спільноти.',
    'signed_success' => 'Ваш підпис враховано.',
    'promoted_success' => 'Поріг у 50 підписів досягнуто. Пропозицію передано керівництву на голосування.',
    'csrf_error' => 'Сесія застаріла. Оновіть сторінку.',
    'unknown_action' => 'Невідома дія.',
] : ($is_en ? [
    'eyebrow' => 'PLAYER VOICE',
    'title' => 'Proposals that reach the council',
    'lead' => 'Any player can propose a change for Project Orion. The initiative first gathers 50 signatures, then is automatically sent to the GSO for a team vote.',
    'threshold' => 'signatures before submission to the council',
    'step_1' => 'Create',
    'step_1_note' => 'Describe the idea clearly.',
    'step_2' => 'Gather 50',
    'step_2_note' => 'One account, one signature.',
    'step_3' => 'Submit to GSO',
    'step_3_note' => 'The council votes openly.',
    'submit_title' => 'Create a proposal',
    'submit_note' => 'You receive the first signature immediately after creating it. Do not create duplicates of the same idea.',
    'title_label' => 'Proposal title',
    'title_placeholder' => 'For example: add a new battle mode',
    'description_label' => 'What is being proposed',
    'description_placeholder' => 'Describe the problem, solution, and important details.',
    'expected_label' => 'Expected result',
    'expected_placeholder' => 'How should the game or site change after implementation?',
    'submit' => 'Publish proposal',
    'login_to_submit' => 'Sign in to create a proposal.',
    'login_required' => 'Sign in to sign a proposal.',
    'recaptcha_error' => 'Please confirm that you are not a robot (reCAPTCHA).',
    'login' => 'Sign in to your account',
    'board_eyebrow' => 'OPEN INITIATIVES',
    'board_title' => 'Player proposals',
    'board_note' => 'Support other players’ ideas or open their path to a council decision.',
    'petition_id' => 'INITIATIVE',
    'author' => 'Author',
    'created' => 'Created',
    'signatures' => 'signatures',
    'of' => 'of',
    'sign' => 'Sign',
    'signed' => 'You have already signed',
    'login_to_sign' => 'Sign in to sign',
    'open_gso' => 'Open decision in GSO',
    'empty' => 'There are no proposals yet. Create the first initiative.',
    'created_success' => 'Proposal created. It is now collecting community signatures.',
    'signed_success' => 'Your signature has been recorded.',
    'promoted_success' => 'The threshold of 50 signatures has been reached. The proposal was sent to the council for a vote.',
    'csrf_error' => 'Your session has expired. Refresh the page.',
    'unknown_action' => 'Unknown action.',
] : [
    'eyebrow' => 'ГОЛОС ИГРОКОВ',
    'title' => 'Предложения, которые доходят до руководства',
    'lead' => 'Любой игрок может предложить изменение для Project Orion. Сначала инициатива собирает 50 подписей, а затем автоматически передаётся в ГСО на голосование команды.',
    'threshold' => 'подписей до передачи руководству',
    'step_1' => 'Создайте',
    'step_1_note' => 'Опишите идею конкретно.',
    'step_2' => 'Соберите 50',
    'step_2_note' => 'Один аккаунт — одна подпись.',
    'step_3' => 'Передача в ГСО',
    'step_3_note' => 'Руководство голосует открыто.',
    'submit_title' => 'Создать предложение',
    'submit_note' => 'После создания вы сразу получаете первую подпись. Не создавайте дубликаты одной идеи.',
    'title_label' => 'Название предложения',
    'title_placeholder' => 'Например: добавить новый режим боя',
    'description_label' => 'Что именно предлагается',
    'description_placeholder' => 'Опишите проблему, решение и важные детали.',
    'expected_label' => 'Ожидаемый результат',
    'expected_placeholder' => 'Как должна измениться игра или сайт после реализации?',
    'submit' => 'Опубликовать предложение',
    'login_to_submit' => 'Чтобы создать предложение, нужно войти в аккаунт.',
    'login_required' => 'Чтобы подписать предложение, нужно войти в аккаунт.',
    'recaptcha_error' => 'Пожалуйста, подтвердите, что вы не робот (reCAPTCHA).',
    'login' => 'Войти в аккаунт',
    'board_eyebrow' => 'ОТКРЫТЫЕ ИНИЦИАТИВЫ',
    'board_title' => 'Предложения игроков',
    'board_note' => 'Поддержите идеи других игроков или откройте их путь к решению руководства.',
    'petition_id' => 'ИНИЦИАТИВА',
    'author' => 'Автор',
    'created' => 'Создано',
    'signatures' => 'подписей',
    'of' => 'из',
    'sign' => 'Подписать',
    'signed' => 'Вы уже подписали',
    'login_to_sign' => 'Войти, чтобы подписать',
    'open_gso' => 'Открыть решение в ГСО',
    'empty' => 'Предложений пока нет. Создайте первую инициативу.',
    'created_success' => 'Предложение создано. Теперь оно собирает подписи сообщества.',
    'signed_success' => 'Ваша подпись учтена.',
    'promoted_success' => 'Порог в 50 подписей достигнут. Предложение передано руководству на голосование.',
    'csrf_error' => 'Сессия устарела. Обновите страницу.',
    'unknown_action' => 'Неизвестное действие.',
]);

$viewer_id = intval($_SESSION['user_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $petition_id = intval($_POST['petition_id'] ?? 0);
    try {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            throw new RuntimeException($copy['csrf_error']);
        }
        if ($viewer_id <= 0) {
            throw new RuntimeException($copy['login_required']);
        }

        $action = (string)($_POST['action'] ?? '');
        if ($action === 'create_petition') {
            if (!verify_recaptcha($_POST['g-recaptcha-response'] ?? '')) {
                throw new RuntimeException($copy['recaptcha_error']);
            }
            $petition_id = player_petition_create(
                $pdo,
                $viewer_id,
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['expected_result'] ?? '',
                get_client_ip()
            );
            petitions_set_flash('success', $copy['created_success']);
        } elseif ($action === 'sign_petition') {
            $result = player_petition_sign($pdo, $petition_id, $viewer_id, get_client_ip());
            petitions_set_flash('success', $result['promoted'] ? $copy['promoted_success'] : $copy['signed_success']);
        } else {
            throw new RuntimeException($copy['unknown_action']);
        }
    } catch (Exception $e) {
        petitions_set_flash('danger', petitions_localize_error($e->getMessage(), $lang));
    }
    petitions_redirect($petition_id);
}

player_petition_sync_statuses($pdo);
$petitions = player_petition_load($pdo, $viewer_id, 120);
$petitions_flash = $_SESSION['petitions_flash'] ?? null;
unset($_SESSION['petitions_flash']);

$page_title = [
    'ru' => 'Предложения игроков — Project Orion',
    'uk' => 'Пропозиції гравців — Project Orion',
    'en' => 'Player proposals — Project Orion',
][$lang];
$page_description = $copy['lead'];
$page_path = 'petitions.php';
$active_page = 'petitions';
$page_styles = ['petitions.css?v=3'];
$head_extra = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
$banner_subtext = [
    'ru' => 'Открытые инициативы сообщества',
    'uk' => 'Відкриті ініціативи спільноти',
    'en' => 'Open community initiatives',
][$lang];
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell petitions-page">
    <section class="petition-hero" aria-labelledby="petitions-title">
        <div class="page-header">
            <p class="eyebrow"><?php echo petitions_h($copy['eyebrow']); ?></p>
            <h1 id="petitions-title"><?php echo petitions_h($copy['title']); ?></h1>
            <p><?php echo petitions_h($copy['lead']); ?></p>
        </div>
        <div class="petition-threshold" aria-label="<?php echo petitions_h($copy['threshold']); ?>">
            <strong>50</strong>
            <span><?php echo petitions_h($copy['threshold']); ?></span>
        </div>
    </section>

    <section class="petition-flow" aria-label="<?php echo petitions_h(['ru' => 'Как это работает', 'uk' => 'Як це працює', 'en' => 'How it works'][$lang]); ?>">
        <article class="petition-flow-step"><span>01</span><div><strong><?php echo petitions_h($copy['step_1']); ?></strong><p><?php echo petitions_h($copy['step_1_note']); ?></p></div></article>
        <article class="petition-flow-step"><span>02</span><div><strong><?php echo petitions_h($copy['step_2']); ?></strong><p><?php echo petitions_h($copy['step_2_note']); ?></p></div></article>
        <article class="petition-flow-step"><span>03</span><div><strong><?php echo petitions_h($copy['step_3']); ?></strong><p><?php echo petitions_h($copy['step_3_note']); ?></p></div></article>
    </section>

    <?php if ($petitions_flash): ?>
        <div class="alert <?php echo $petitions_flash['type'] === 'danger' ? 'alert-danger' : 'alert-success'; ?> petition-alert"><?php echo petitions_h($petitions_flash['message']); ?></div>
    <?php endif; ?>

    <div class="petitions-layout">
        <section class="card petition-submit-card" id="petition-form" aria-labelledby="petition-submit-title">
            <header class="card-header">
                <p class="eyebrow"><?php echo petitions_h(['ru' => 'НОВАЯ ИНИЦИАТИВА', 'uk' => 'НОВА ІНІЦІАТИВА', 'en' => 'NEW INITIATIVE'][$lang]); ?></p>
                <h2 id="petition-submit-title" class="card-title"><?php echo petitions_h($copy['submit_title']); ?></h2>
                <p><?php echo petitions_h($copy['submit_note']); ?></p>
            </header>
            <div class="card-body">
                <?php if ($viewer_id > 0): ?>
                    <form method="POST" action="<?php echo petitions_h(i18n_locale_path('petitions.php#petition-form')); ?>" class="petition-submit-form">
                        <input type="hidden" name="csrf_token" value="<?php echo petitions_h($_SESSION['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="action" value="create_petition">
                        <div class="form-group">
                            <label for="petition-title"><?php echo petitions_h($copy['title_label']); ?></label>
                            <input class="form-control" type="text" id="petition-title" name="title" minlength="8" maxlength="180" placeholder="<?php echo petitions_h($copy['title_placeholder']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="petition-description"><?php echo petitions_h($copy['description_label']); ?></label>
                            <textarea class="form-control" id="petition-description" name="description" rows="6" minlength="30" maxlength="10000" placeholder="<?php echo petitions_h($copy['description_placeholder']); ?>" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="petition-expected"><?php echo petitions_h($copy['expected_label']); ?></label>
                            <textarea class="form-control" id="petition-expected" name="expected_result" rows="3" minlength="10" maxlength="4000" placeholder="<?php echo petitions_h($copy['expected_placeholder']); ?>" required></textarea>
                        </div>
                        <div class="form-group recaptcha-field">
                            <div class="g-recaptcha" data-sitekey="<?php echo petitions_h(RECAPTCHA_SITE_KEY); ?>"></div>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit"><?php echo petitions_h($copy['submit']); ?></button>
                    </form>
                <?php else: ?>
                    <p class="muted-copy"><?php echo petitions_h($copy['login_to_submit']); ?></p>
                    <a class="btn btn-secondary btn-block" href="<?php echo petitions_h(i18n_locale_path('login.php')); ?>"><?php echo petitions_h($copy['login']); ?></a>
                <?php endif; ?>
            </div>
        </section>

        <section class="petition-board" aria-labelledby="petition-board-title">
            <header class="section-heading petition-board-heading">
                <div>
                    <p class="eyebrow"><?php echo petitions_h($copy['board_eyebrow']); ?></p>
                    <h2 id="petition-board-title"><?php echo petitions_h($copy['board_title']); ?></h2>
                    <p><?php echo petitions_h($copy['board_note']); ?></p>
                </div>
                <span class="petition-board-count"><?php echo count($petitions); ?></span>
            </header>

            <div class="petition-list">
                <?php if (empty($petitions)): ?>
                    <div class="card petition-empty"><strong><?php echo petitions_h($copy['empty']); ?></strong></div>
                <?php else: ?>
                    <?php foreach ($petitions as $petition): ?>
                        <?php
                        $petition_id = intval($petition['id']);
                        $signature_count = intval($petition['signature_count']);
                        $signature_threshold = max(1, intval($petition['signature_threshold']));
                        $progress = player_petition_signature_progress($signature_count, $signature_threshold);
                        $status = (string)$petition['status'];
                        $is_collecting = $status === 'collecting';
                        $viewer_signed = intval($petition['viewer_signed'] ?? 0) === 1;
                        ?>
                        <article class="card petition-card petition-card--<?php echo petitions_h($status); ?>" id="petition-<?php echo $petition_id; ?>">
                            <header class="petition-card-header">
                                <div>
                                    <span class="petition-id"><?php echo petitions_h($copy['petition_id']); ?>-<?php echo str_pad((string)$petition_id, 4, '0', STR_PAD_LEFT); ?></span>
                                    <h3 class="notranslate" translate="no"><?php echo petitions_h($petition['title']); ?></h3>
                                </div>
                                <span class="petition-status petition-status--<?php echo petitions_h($status); ?>"><?php echo petitions_h(player_petition_status_label($status, $lang)); ?></span>
                            </header>
                            <div class="petition-card-body">
                                <div class="petition-meta">
                                    <span><?php echo petitions_h($copy['author']); ?>: <strong class="notranslate" translate="no"><?php echo petitions_h($petition['author_name'] ?: '—'); ?></strong></span>
                                    <span><?php echo petitions_h($copy['created']); ?>: <strong><?php echo petitions_h(date('d.m.Y H:i', strtotime($petition['created_at']))); ?></strong></span>
                                </div>
                                <p class="petition-description notranslate" translate="no"><?php echo nl2br(petitions_h($petition['description'])); ?></p>
                                <div class="petition-expected"><strong><?php echo petitions_h($copy['expected_label']); ?></strong><p class="notranslate" translate="no"><?php echo nl2br(petitions_h($petition['expected_result'])); ?></p></div>
                                <div class="petition-progress-heading"><span><?php echo $signature_count; ?> <?php echo petitions_h($copy['signatures']); ?> <?php echo petitions_h($copy['of']); ?> <?php echo $signature_threshold; ?></span><strong><?php echo $progress; ?>%</strong></div>
                                <div class="petition-progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo $signature_threshold; ?>" aria-valuenow="<?php echo min($signature_count, $signature_threshold); ?>" aria-label="<?php echo petitions_h($copy['signatures']); ?>"><span style="width: <?php echo $progress; ?>%"></span></div>
                            </div>
                            <footer class="petition-card-footer">
                                <?php if ($is_collecting): ?>
                                    <?php if ($viewer_id <= 0): ?>
                                        <a class="btn btn-secondary btn-small" href="<?php echo petitions_h(i18n_locale_path('login.php')); ?>"><?php echo petitions_h($copy['login_to_sign']); ?></a>
                                    <?php elseif ($viewer_signed): ?>
                                        <span class="petition-signed"><?php echo petitions_h($copy['signed']); ?></span>
                                    <?php else: ?>
                                        <form method="POST" action="<?php echo petitions_h(i18n_locale_path('petitions.php#petition-' . $petition_id)); ?>" class="inline-form petition-sign-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo petitions_h($_SESSION['csrf_token'] ?? ''); ?>">
                                            <input type="hidden" name="action" value="sign_petition">
                                            <input type="hidden" name="petition_id" value="<?php echo $petition_id; ?>">
                                            <button class="btn btn-primary btn-small" type="submit"><?php echo petitions_h($copy['sign']); ?></button>
                                        </form>
                                    <?php endif; ?>
                                <?php elseif (intval($petition['gso_proposal_id']) > 0): ?>
                                    <a class="btn btn-secondary btn-small" href="<?php echo petitions_h(i18n_locale_path('gso.php#proposal-' . intval($petition['gso_proposal_id']))); ?>"><?php echo petitions_h($copy['open_gso']); ?></a>
                                <?php endif; ?>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
