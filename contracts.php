<?php
require_once __DIR__ . '/db.php';

function contracts_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function contracts_redirect() {
    header('Location: ' . i18n_locale_path('contracts.php'), true, 303);
    exit;
}

function contracts_set_flash($type, $message) {
    $_SESSION['contracts_flash'] = [
        'type' => $type === 'danger' ? 'danger' : 'success',
        'message' => $message,
    ];
}

$ui_lang = function_exists('current_lang') ? current_lang() : 'ru';
$ui_lang = in_array($ui_lang, ['ru', 'uk', 'en'], true) ? $ui_lang : 'ru';
$copy = [
    'ru' => [
        'title' => 'Контракты команды',
        'lead' => 'Выберите тип контракта и отправьте его главе проекта. Роль включается после принятия на семь дней. С пятого календарного дня участник может подать заявку на продление.',
        'apply' => 'Подать контракт',
        'renew' => 'Продлить контракт',
        'renew_submit' => 'Отправить заявку на продление',
        'renew_comment' => 'Комментарий к продлению',
        'type' => 'Тип контракта',
        'comment' => 'Комментарий',
        'comment_hint' => 'Необязательно. До 2000 символов.',
        'submit' => 'Отправить контракт',
        'agree' => 'Я принимаю условия контракта и право главы расторгнуть его досрочно.',
        'renew_agree' => 'Я повторно принимаю условия контракта и правила продления.',
        'public' => 'Я согласен на публикацию аккаунта и контракта в открытом реестре.',
        'registry' => 'Публичный реестр',
        'registry_lead' => 'Принятые контракты, роли, точные семидневные сроки и сведения о расторжении.',
        'filter_search' => 'Поиск по имени или номеру',
        'filter_search_placeholder' => 'Например, OrionLead или ORI-2026-000042',
        'filter_role' => 'Роль',
        'filter_status' => 'Статус',
        'filter_any_role' => 'Все роли',
        'filter_any_status' => 'Все статусы',
        'filter_results' => 'контрактов в списке',
        'filter_reset' => 'Сбросить фильтры',
        'filter_empty_title' => 'Ничего не найдено',
        'filter_empty_note' => 'Измените запрос или сбросьте фильтры.',
        'table_contract' => 'Контракт',
        'table_member' => 'Участник',
        'table_status' => 'Статус',
        'table_period' => 'Период',
        'table_approved' => 'Принят',
        'table_files' => 'Документы',
        'table_pdf_ua' => 'Українська PDF',
        'table_pdf_ru' => 'Русский PDF',
        'table_pdf_en' => 'English PDF',
        'empty' => 'Принятых контрактов пока нет.',
        'login' => 'Войдите, чтобы выбрать тип и подать контракт.',
        'login_button' => 'Войти в аккаунт',
        'status' => 'Ваш контракт',
        'pending' => 'Ожидает решения главы',
        'rejected' => 'Контракт отклонён',
        'active' => 'Контракт действует',
        'finished' => 'Предыдущий контракт завершён',
        'period' => 'Период',
        'approved' => 'Принят',
        'termination' => 'Причина расторжения',
        'terminated_at' => 'Дата расторжения',
        'renewal_at' => 'Продление с',
        'renewal_wait' => 'Заявку на продление можно подать с пятого календарного дня.',
        'renewal_ready' => 'Пятый календарный день наступил. Можно подать заявку на продление этой же роли.',
        'renewal_pending' => 'Заявка на продление ожидает решения главы проекта.',
        'renewal_scheduled' => 'Продление принято. Новый семидневный срок начнётся после завершения текущего.',
        'cooldown_title' => 'Пауза перед новым контрактом',
        'cooldown_note' => 'Новый контракт можно подать после %s. Пауза длится 7 дней с момента завершения контракта, отказа или расторжения.',
        'role_contracts' => 'Контракты роли',
        'all_times' => 'Все даты и время на этой странице указаны по киевскому времени.',
        'process' => ['01 Выбор типа', '02 Отправка', '03 Решение главы', '04 Работа 7 дней', '05 Продление'],
        'timeline' => 'Контракт действует семь дней. С пятого календарного дня доступна заявка на продление; принятой заявке присваивается новый срок.',
        'csrf_error' => 'Сессия устарела. Обновите страницу.',
        'apply_success' => 'Контракт отправлен главе проекта.',
        'renew_success' => 'Заявка на продление отправлена главе проекта.',
        'unknown_action' => 'Неизвестное действие.',
    ],
    'uk' => [
        'title' => 'Контракти команди',
        'lead' => 'Оберіть тип контракту й надішліть його главі проєкту. Роль вмикається після прийняття на сім днів. Із п’ятого календарного дня учасник може подати заявку на поновлення.',
        'apply' => 'Подати контракт',
        'renew' => 'Поновити контракт',
        'renew_submit' => 'Надіслати заявку на поновлення',
        'renew_comment' => 'Коментар до поновлення',
        'type' => 'Тип контракту',
        'comment' => 'Коментар',
        'comment_hint' => 'Необов’язково. До 2000 символів.',
        'submit' => 'Надіслати контракт',
        'agree' => 'Я приймаю умови контракту та право глави розірвати його достроково.',
        'renew_agree' => 'Я повторно приймаю умови контракту та правила поновлення.',
        'public' => 'Я погоджуюся на публікацію акаунта й контракту у відкритому реєстрі.',
        'registry' => 'Публічний реєстр',
        'registry_lead' => 'Прийняті контракти, ролі, точні семиденні строки та відомості про розірвання.',
        'filter_search' => 'Пошук за ім’ям або номером',
        'filter_search_placeholder' => 'Наприклад, OrionLead або ORI-2026-000042',
        'filter_role' => 'Роль',
        'filter_status' => 'Статус',
        'filter_any_role' => 'Усі ролі',
        'filter_any_status' => 'Усі статуси',
        'filter_results' => 'контрактів у списку',
        'filter_reset' => 'Скинути фільтри',
        'filter_empty_title' => 'Нічого не знайдено',
        'filter_empty_note' => 'Змініть запит або скиньте фільтри.',
        'table_contract' => 'Контракт',
        'table_member' => 'Учасник',
        'table_status' => 'Статус',
        'table_period' => 'Період',
        'table_approved' => 'Прийнято',
        'table_files' => 'Документи',
        'table_pdf_ua' => 'Українська PDF',
        'table_pdf_ru' => 'Русский PDF',
        'table_pdf_en' => 'English PDF',
        'empty' => 'Прийнятих контрактів поки немає.',
        'login' => 'Увійдіть, щоб обрати тип і подати контракт.',
        'login_button' => 'Увійти в акаунт',
        'status' => 'Ваш контракт',
        'pending' => 'Очікує рішення глави',
        'rejected' => 'Контракт відхилено',
        'active' => 'Контракт діє',
        'finished' => 'Попередній контракт завершено',
        'period' => 'Період',
        'approved' => 'Прийнято',
        'termination' => 'Причина розірвання',
        'terminated_at' => 'Дата розірвання',
        'renewal_at' => 'Поновлення з',
        'renewal_wait' => 'Заявку на поновлення можна подати з п’ятого календарного дня.',
        'renewal_ready' => 'П’ятий календарний день настав. Можна подати заявку на поновлення цієї самої ролі.',
        'renewal_pending' => 'Заявка на поновлення очікує рішення глави проєкту.',
        'renewal_scheduled' => 'Поновлення прийнято. Новий семиденний строк почнеться після завершення поточного.',
        'cooldown_title' => 'Пауза перед новим контрактом',
        'cooldown_note' => 'Новий контракт можна подати після %s. Пауза триває 7 днів від моменту завершення контракту, відхилення або розірвання.',
        'role_contracts' => 'Контракти ролі',
        'all_times' => 'Усі дати й час на цій сторінці вказано за київським часом.',
        'process' => ['01 Вибір типу', '02 Надсилання', '03 Рішення глави', '04 Робота 7 днів', '05 Поновлення'],
        'timeline' => 'Контракт діє сім днів. Із п’ятого календарного дня доступна заявка на поновлення; прийнята заявка отримує новий строк.',
        'csrf_error' => 'Сесія застаріла. Оновіть сторінку.',
        'apply_success' => 'Контракт надіслано главі проєкту.',
        'renew_success' => 'Заявку на поновлення надіслано главі проєкту.',
        'unknown_action' => 'Невідома дія.',
    ],
    'en' => [
        'title' => 'Team contracts',
        'lead' => 'Choose a contract type and send it to the project lead. The role activates for seven days after approval. From the fifth calendar day, a member can submit a renewal request.',
        'apply' => 'Submit contract',
        'renew' => 'Renew contract',
        'renew_submit' => 'Submit renewal request',
        'renew_comment' => 'Renewal comment',
        'type' => 'Contract type',
        'comment' => 'Comment',
        'comment_hint' => 'Optional. Up to 2,000 characters.',
        'submit' => 'Submit contract',
        'agree' => 'I accept the contract terms and the lead’s right to terminate it early.',
        'renew_agree' => 'I accept the contract terms and renewal rules again.',
        'public' => 'I agree to publish the account and contract in the public registry.',
        'registry' => 'Public registry',
        'registry_lead' => 'Approved contracts, roles, exact seven-day terms, and termination details.',
        'filter_search' => 'Search by name or number',
        'filter_search_placeholder' => 'For example, OrionLead or ORI-2026-000042',
        'filter_role' => 'Role',
        'filter_status' => 'Status',
        'filter_any_role' => 'All roles',
        'filter_any_status' => 'All statuses',
        'filter_results' => 'contracts in the list',
        'filter_reset' => 'Reset filters',
        'filter_empty_title' => 'Nothing found',
        'filter_empty_note' => 'Change the query or reset the filters.',
        'table_contract' => 'Contract',
        'table_member' => 'Member',
        'table_status' => 'Status',
        'table_period' => 'Term',
        'table_approved' => 'Approved',
        'table_files' => 'Documents',
        'table_pdf_ua' => 'Ukrainian PDF',
        'table_pdf_ru' => 'Russian PDF',
        'table_pdf_en' => 'English PDF',
        'empty' => 'There are no approved contracts yet.',
        'login' => 'Sign in to choose a type and submit a contract.',
        'login_button' => 'Sign in to your account',
        'status' => 'Your contract',
        'pending' => 'Awaiting the lead’s decision',
        'rejected' => 'Contract rejected',
        'active' => 'Contract active',
        'finished' => 'Previous contract ended',
        'period' => 'Term',
        'approved' => 'Approved',
        'termination' => 'Termination reason',
        'terminated_at' => 'Termination date',
        'renewal_at' => 'Renewal starts',
        'renewal_wait' => 'A renewal request can be submitted from the fifth calendar day.',
        'renewal_ready' => 'The fifth calendar day has arrived. You can submit a renewal request for this role.',
        'renewal_pending' => 'The renewal request is awaiting the project lead’s decision.',
        'renewal_scheduled' => 'Renewal approved. The new seven-day term starts after the current one ends.',
        'cooldown_title' => 'Cooldown before a new contract',
        'cooldown_note' => 'A new contract can be submitted after %s. The cooldown lasts 7 days from contract expiry, rejection, or termination.',
        'role_contracts' => 'Role contracts',
        'all_times' => 'All dates and times on this page use Kyiv time.',
        'process' => ['01 Choose type', '02 Submit', '03 Lead decision', '04 Work for 7 days', '05 Renewal'],
        'timeline' => 'The contract lasts seven days. A renewal request becomes available on the fifth calendar day; an approved request receives a new term.',
        'csrf_error' => 'Your session has expired. Refresh the page.',
        'apply_success' => 'The contract was sent to the project lead.',
        'renew_success' => 'The renewal request was sent to the project lead.',
        'unknown_action' => 'Unknown action.',
    ],
][$ui_lang];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . i18n_locale_path('login.php'), true, 303);
        exit;
    }
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        contracts_set_flash('danger', $copy['csrf_error']);
        contracts_redirect();
    }

    try {
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'apply') {
            $application_id = contract_create_application(
                $pdo,
                intval($_SESSION['user_id']),
                (string)($_POST['preferred_role'] ?? ''),
                (string)($_POST['motivation'] ?? ''),
                !empty($_POST['agree_terms']),
                !empty($_POST['public_consent']),
                get_client_ip()
            );
            log_staff_action($pdo, 'contract.application.create', 'contract_application', $application_id, contract_localize_message('Пользователь подал контракт', $ui_lang));
            contracts_set_flash('success', $copy['apply_success']);
        } elseif ($action === 'renew') {
            $application_id = contract_create_renewal_application(
                $pdo,
                intval($_SESSION['user_id']),
                (string)($_POST['motivation'] ?? ''),
                !empty($_POST['agree_terms']),
                !empty($_POST['public_consent']),
                get_client_ip()
            );
            log_staff_action($pdo, 'contract.application.renew', 'contract_application', $application_id, contract_localize_message('Пользователь подал заявку на продление контракта', $ui_lang));
            contracts_set_flash('success', $copy['renew_success']);
        } else {
            throw new RuntimeException($copy['unknown_action']);
        }
        contracts_redirect();
    } catch (Exception $e) {
        contracts_set_flash('danger', contract_localize_message($e->getMessage(), $ui_lang));
        contracts_redirect();
    }
}

$flash = $_SESSION['contracts_flash'] ?? null;
unset($_SESSION['contracts_flash']);
$user_state = ['application' => null, 'active' => null, 'scheduled' => null, 'history' => [], 'can_renew' => false, 'renewal_available_at' => null, 'cooldown_until' => null, 'cooldown_active' => false];
if (!empty($_SESSION['user_id'])) {
    try {
        $user_state = contract_user_dashboard($pdo, intval($_SESSION['user_id']));
    } catch (Exception $e) {
        error_log('Contract user dashboard error: ' . $e->getMessage());
    }
}
try {
    $public_contracts = contract_public_list($pdo, 200);
} catch (Exception $e) {
    error_log('Public contract registry error: ' . $e->getMessage());
    $public_contracts = [];
}
$active_count = count(array_filter($public_contracts, static function ($contract) {
    return ($contract['status'] ?? '') === 'active';
}));
$public_contracts_by_role = array_fill_keys(array_keys(contract_role_definitions()), []);
foreach ($public_contracts as $contract) {
    $role = (string)($contract['staff_role'] ?? '');
    if (!isset($public_contracts_by_role[$role])) {
        $public_contracts_by_role[$role] = [];
    }
    $public_contracts_by_role[$role][] = $contract;
}
$contract_filter_statuses = ['active', 'scheduled', 'expired', 'terminated'];
$contract_filter_statuses = array_values(array_unique(array_merge(
    $contract_filter_statuses,
    array_map(static function ($contract) {
        return (string)($contract['status'] ?? '');
    }, $public_contracts)
)));
$contract_filter_statuses = array_values(array_filter($contract_filter_statuses, static function ($status) {
    return $status !== '';
}));

$page_title = $copy['title'] . ' — Project Orion';
$page_description = $copy['lead'];
$page_path = 'contracts.php';
$active_page = 'contracts';
$banner_subtext = null;
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell contracts-page">
    <section class="contracts-hero">
        <div class="contracts-hero-copy">
            <p class="eyebrow">ORION TEAM / CONTRACTS</p>
            <h1><?php echo contracts_h($copy['title']); ?></h1>
            <p><?php echo contracts_h($copy['lead']); ?></p>
            <div class="contracts-process" aria-label="Contract workflow">
                <?php foreach ($copy['process'] as $step): ?><span><?php echo contracts_h($step); ?></span><?php endforeach; ?>
            </div>
        </div>
        <div class="contracts-hero-mark" aria-hidden="true"><span>TERM</span><strong>7</strong><small>DAYS</small></div>
    </section>

    <?php if ($flash): ?>
        <div class="alert <?php echo $flash['type'] === 'danger' ? 'alert-danger' : 'alert-success'; ?> contracts-alert" role="status"><?php echo contracts_h($flash['message']); ?></div>
    <?php endif; ?>

    <section class="contracts-member-area" aria-labelledby="memberContractTitle">
        <div class="section-heading contracts-section-heading">
            <div><p class="eyebrow">MEMBER WORKFLOW</p><h2 id="memberContractTitle"><?php echo contracts_h(!empty($_SESSION['user_id']) ? $copy['status'] : $copy['apply']); ?></h2></div>
        </div>

        <?php if (empty($_SESSION['user_id'])): ?>
            <div class="contracts-login-card card"><p><?php echo contracts_h($copy['login']); ?></p><a href="<?php echo contracts_h(i18n_locale_path('login.php')); ?>" class="btn btn-primary"><?php echo contracts_h($copy['login_button']); ?></a></div>
        <?php else: ?>
            <div class="contracts-member-grid">
                <div class="contract-member-status card">
                    <?php if (!empty($user_state['active'])): ?>
                        <?php $active_contract = $user_state['active']; ?>
                        <span class="contract-state contract-state--active"><?php echo contracts_h($copy['active']); ?></span>
                        <h3><?php echo contracts_h(contract_role_label($active_contract['staff_role'], $ui_lang)); ?></h3>
                        <dl class="contract-status-facts">
                            <div><dt><?php echo contracts_h($copy['period']); ?></dt><dd><?php echo contracts_h(contract_format_kyiv_datetime($active_contract['starts_at'])); ?> — <?php echo contracts_h(contract_format_kyiv_datetime($active_contract['expires_at'])); ?></dd></div>
                            <div><dt><?php echo contracts_h($copy['renewal_at']); ?></dt><dd><?php echo contracts_h(contract_format_kyiv_datetime($user_state['renewal_available_at'])); ?></dd></div>
                            <div><dt>№</dt><dd><?php echo contracts_h($active_contract['contract_number']); ?></dd></div>
                        </dl>
                        <?php
                        $active_renewal_pending = !empty($user_state['application'])
                            && (string)$user_state['application']['status'] === 'pending'
                            && (string)($user_state['application']['application_type'] ?? '') === 'renewal'
                            && intval($user_state['application']['renewal_contract_id'] ?? 0) === intval($active_contract['id']);
                        ?>
                        <?php if ($active_renewal_pending): ?>
                            <p class="contract-renewal-note"><?php echo contracts_h($copy['renewal_pending']); ?></p>
                        <?php elseif (!empty($user_state['scheduled'])): ?>
                            <p class="contract-renewal-note contract-renewal-note--ready"><?php echo contracts_h($copy['renewal_scheduled']); ?> <?php echo contracts_h(contract_format_kyiv_datetime($user_state['scheduled']['starts_at'])); ?></p>
                        <?php elseif (!empty($user_state['can_renew'])): ?>
                            <p class="contract-renewal-note contract-renewal-note--ready"><?php echo contracts_h($copy['renewal_ready']); ?></p>
                        <?php else: ?>
                            <p class="contract-renewal-note"><?php echo contracts_h($copy['renewal_wait']); ?> <?php echo contracts_h(contract_format_kyiv_datetime($user_state['renewal_available_at'])); ?></p>
                        <?php endif; ?>
                        <div class="contract-pdf-links"><a href="contract_pdf.php?id=<?php echo contracts_h($active_contract['public_id']); ?>&amp;lang=uk&amp;v=6" target="_blank" rel="noopener">PDF · UA</a><a href="contract_pdf.php?id=<?php echo contracts_h($active_contract['public_id']); ?>&amp;lang=ru&amp;v=6" target="_blank" rel="noopener">PDF · RU</a><a href="contract_pdf.php?id=<?php echo contracts_h($active_contract['public_id']); ?>&amp;lang=en&amp;v=6" target="_blank" rel="noopener">PDF · EN</a></div>
                    <?php elseif (!empty($user_state['application']) && $user_state['application']['status'] === 'pending'): ?>
                        <span class="contract-state contract-state--pending"><?php echo contracts_h($copy['pending']); ?></span>
                        <h3><?php echo contracts_h(contract_role_label($user_state['application']['preferred_role'], $ui_lang)); ?></h3>
                        <?php if (!empty($user_state['application']['motivation'])): ?><p class="notranslate" translate="no"><?php echo nl2br(contracts_h($user_state['application']['motivation'])); ?></p><?php endif; ?>
                    <?php elseif (!empty($user_state['application']) && $user_state['application']['status'] === 'rejected'): ?>
                        <span class="contract-state contract-state--rejected"><?php echo contracts_h($copy['rejected']); ?></span>
                        <h3><?php echo contracts_h(contract_role_label($user_state['application']['preferred_role'], $ui_lang)); ?></h3>
                        <?php if (!empty($user_state['application']['decision_note'])): ?><p class="notranslate" translate="no"><?php echo contracts_h($user_state['application']['decision_note']); ?></p><?php endif; ?>
                    <?php elseif (!empty($user_state['history'])): ?>
                        <?php $last_contract = $user_state['history'][0]; ?>
                        <span class="contract-state contract-state--expired"><?php echo contracts_h($copy['finished']); ?></span>
                        <h3><?php echo contracts_h(contract_role_label($last_contract['staff_role'], $ui_lang)); ?></h3>
                        <p><?php echo contracts_h($copy['timeline']); ?></p>
                    <?php else: ?>
                        <span class="contract-state"><?php echo contracts_h($copy['apply']); ?></span>
                        <h3>Project Orion Team</h3>
                        <p><?php echo contracts_h($copy['lead']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($user_state['cooldown_active'])): ?>
                        <div class="contract-cooldown-notice">
                            <strong><?php echo contracts_h($copy['cooldown_title']); ?></strong>
                            <p><?php echo contracts_h(sprintf($copy['cooldown_note'], contract_format_kyiv_datetime($user_state['cooldown_until']))); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                $application_pending = !empty($user_state['application']) && $user_state['application']['status'] === 'pending';
                $show_renewal_form = !empty($user_state['active'])
                    && !empty($user_state['can_renew'])
                    && empty($user_state['scheduled'])
                    && empty($user_state['cooldown_active'])
                    && !$application_pending;
                $show_application_form = empty($user_state['active'])
                    && empty($user_state['scheduled'])
                    && empty($user_state['cooldown_active'])
                    && !$application_pending;
                ?>
                <?php if ($show_renewal_form): ?>
                    <form method="POST" action="<?php echo contracts_h(i18n_locale_path('contracts.php')); ?>" class="contract-application-card card contract-renewal-form">
                        <input type="hidden" name="csrf_token" value="<?php echo contracts_h($_SESSION['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="action" value="renew">
                        <div><p class="eyebrow">RENEW CONTRACT</p><h3><?php echo contracts_h($copy['renew']); ?></h3><p><?php echo contracts_h(contract_role_label($user_state['active']['staff_role'], $ui_lang)); ?></p></div>
                        <div class="form-group">
                            <label for="renewalMotivation"><?php echo contracts_h($copy['renew_comment']); ?></label>
                            <textarea class="form-control" id="renewalMotivation" name="motivation" maxlength="2000" rows="5"></textarea>
                            <small><?php echo contracts_h($copy['comment_hint']); ?></small>
                        </div>
                        <label class="contract-consent"><input type="checkbox" name="agree_terms" value="1" required><span><?php echo contracts_h($copy['renew_agree']); ?></span></label>
                        <label class="contract-consent"><input type="checkbox" name="public_consent" value="1" required><span><?php echo contracts_h($copy['public']); ?></span></label>
                        <button type="submit" class="btn btn-primary"><?php echo contracts_h($copy['renew_submit']); ?></button>
                    </form>
                <?php elseif ($show_application_form): ?>
                    <form method="POST" action="<?php echo contracts_h(i18n_locale_path('contracts.php')); ?>" class="contract-application-card card">
                        <input type="hidden" name="csrf_token" value="<?php echo contracts_h($_SESSION['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="action" value="apply">
                        <div><p class="eyebrow">SUBMIT CONTRACT</p><h3><?php echo contracts_h($copy['apply']); ?></h3></div>
                        <div class="form-group">
                            <label for="preferredContractRole"><?php echo contracts_h($copy['type']); ?></label>
                            <select class="form-control" id="preferredContractRole" name="preferred_role" required>
                                <?php foreach (contract_role_definitions() as $role => $labels): ?><option value="<?php echo contracts_h($role); ?>"><?php echo contracts_h($labels[$ui_lang]); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="contractMotivation"><?php echo contracts_h($copy['comment']); ?></label>
                            <textarea class="form-control" id="contractMotivation" name="motivation" maxlength="2000" rows="5"></textarea>
                            <small><?php echo contracts_h($copy['comment_hint']); ?></small>
                        </div>
                        <label class="contract-consent"><input type="checkbox" name="agree_terms" value="1" required><span><?php echo contracts_h($copy['agree']); ?></span></label>
                        <label class="contract-consent"><input type="checkbox" name="public_consent" value="1" required><span><?php echo contracts_h($copy['public']); ?></span></label>
                        <button type="submit" class="btn btn-primary"><?php echo contracts_h($copy['submit']); ?></button>
                    </form>
                <?php else: ?>
                    <aside class="contract-timeline-card card"><span>SUBMIT</span><i></i><span>ACCEPT</span><i></i><span>7 DAYS</span><small><?php echo contracts_h($copy['timeline']); ?></small></aside>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="contracts-registry" aria-labelledby="contractsRegistryTitle" data-contract-registry>
        <div class="section-heading contracts-section-heading"><div><p class="eyebrow">PUBLIC / VERIFIED</p><h2 id="contractsRegistryTitle"><?php echo contracts_h($copy['registry']); ?></h2><p><?php echo contracts_h($copy['registry_lead']); ?></p></div><div class="contracts-registry-count"><strong><?php echo count($public_contracts); ?></strong><span>PDF · <?php echo intval($active_count); ?> active</span></div></div>
        <p class="contracts-time-note"><?php echo contracts_h($copy['all_times']); ?></p>
        <?php if (!empty($public_contracts)): ?>
            <div class="contracts-registry-tools" data-contract-filter-panel>
                <div class="contracts-filter-grid">
                    <label class="contracts-filter-field contracts-filter-field--search">
                        <span><?php echo contracts_h($copy['filter_search']); ?></span>
                        <input class="form-control" type="search" data-contract-filter="search" placeholder="<?php echo contracts_h($copy['filter_search_placeholder']); ?>" autocomplete="off">
                    </label>
                    <label class="contracts-filter-field">
                        <span><?php echo contracts_h($copy['filter_role']); ?></span>
                        <select class="form-control" data-contract-filter="role">
                            <option value="all"><?php echo contracts_h($copy['filter_any_role']); ?></option>
                            <?php foreach (contract_role_definitions() as $role => $labels): ?>
                                <option value="<?php echo contracts_h($role); ?>"><?php echo contracts_h($labels[$ui_lang]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="contracts-filter-field">
                        <span><?php echo contracts_h($copy['filter_status']); ?></span>
                        <select class="form-control" data-contract-filter="status">
                            <option value="all"><?php echo contracts_h($copy['filter_any_status']); ?></option>
                            <?php foreach ($contract_filter_statuses as $status): ?>
                                <?php if ($status === '') continue; ?>
                                <option value="<?php echo contracts_h($status); ?>"><?php echo contracts_h(contract_status_label($status, $ui_lang)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="contracts-filter-footer">
                    <p class="contracts-filter-results" aria-live="polite"><strong data-contract-visible-count><?php echo count($public_contracts); ?></strong> <?php echo contracts_h($copy['filter_results']); ?></p>
                    <button type="button" class="btn btn-secondary contracts-filter-reset" data-contract-filter-reset hidden><?php echo contracts_h($copy['filter_reset']); ?></button>
                </div>
            </div>
            <div class="contracts-role-lists" data-contract-list>
                <?php foreach ($public_contracts_by_role as $role => $role_contracts): ?>
                    <?php if (empty($role_contracts)) continue; ?>
                    <section class="contracts-role-group" data-contract-role-group data-contract-role="<?php echo contracts_h($role); ?>" aria-labelledby="contractRole-<?php echo contracts_h($role); ?>">
                        <header class="contracts-role-heading">
                            <div><p><?php echo contracts_h($copy['role_contracts']); ?></p><h3 id="contractRole-<?php echo contracts_h($role); ?>"><?php echo contracts_h(contract_role_label($role, $ui_lang)); ?></h3></div>
                            <span data-contract-group-count><?php echo count($role_contracts); ?></span>
                        </header>
                        <div class="contracts-registry-grid">
                            <table class="contracts-table">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php echo contracts_h($copy['table_contract']); ?></th>
                                        <th scope="col"><?php echo contracts_h($copy['table_member']); ?></th>
                                        <th scope="col"><?php echo contracts_h($copy['table_status']); ?></th>
                                        <th scope="col"><?php echo contracts_h($copy['table_period']); ?></th>
                                        <th scope="col"><?php echo contracts_h($copy['table_approved']); ?></th>
                                        <th scope="col"><?php echo contracts_h($copy['table_files']); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($role_contracts as $contract): ?>
                                        <?php
                                        $contract_status = (string)($contract['status'] ?? '');
                                        $contract_search = trim(implode(' ', [
                                            (string)($contract['username'] ?? ''),
                                            (string)($contract['contract_number'] ?? ''),
                                            contract_role_label($role, $ui_lang),
                                        ]));
                                        $contract_name = $contract['username'] ?: ('Account #' . intval($contract['account_id']));
                                        ?>
                                        <tr class="contracts-table-row" data-contract-item data-contract-role="<?php echo contracts_h($role); ?>" data-contract-status="<?php echo contracts_h($contract_status); ?>" data-contract-search="<?php echo contracts_h($contract_search); ?>">
                                            <td data-label="<?php echo contracts_h($copy['table_contract']); ?>">
                                                <strong class="contracts-table-number"><?php echo contracts_h($contract['contract_number']); ?></strong>
                                            </td>
                                            <td data-label="<?php echo contracts_h($copy['table_member']); ?>">
                                                 <strong class="contracts-table-name notranslate" translate="no"><?php echo contracts_h($contract_name); ?></strong>
                                                <span class="contracts-table-role"><?php echo contracts_h(contract_role_label($contract['staff_role'], $ui_lang)); ?></span>
                                            </td>
                                            <td data-label="<?php echo contracts_h($copy['table_status']); ?>">
                                                <span class="contract-state contract-state--<?php echo contracts_h($contract_status); ?>"><?php echo contracts_h(contract_status_label($contract_status, $ui_lang)); ?></span>
                                                <?php if ($contract_status === 'terminated'): ?>
                                                    <small class="contracts-table-subline contract-registry-terminated-at"><?php echo contracts_h(contract_format_kyiv_datetime($contract['terminated_at'] ?? '')); ?></small>
                                                <?php endif; ?>
                                                <?php if ($contract_status === 'terminated' && !empty($contract['termination_reason'])): ?>
                                                    <details class="contract-public-termination" data-contract-table-reason>
                                                        <summary><?php echo contracts_h($copy['termination']); ?></summary>
                                                        <p class="notranslate" translate="no"><?php echo contracts_h($contract['termination_reason']); ?></p>
                                                    </details>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="<?php echo contracts_h($copy['table_period']); ?>" class="contracts-table-period">
                                                <span><?php echo contracts_h(contract_format_kyiv_datetime($contract['starts_at'])); ?></span>
                                                <span>— <?php echo contracts_h(contract_format_kyiv_datetime($contract['expires_at'])); ?></span>
                                            </td>
                                            <td data-label="<?php echo contracts_h($copy['table_approved']); ?>" class="contracts-table-date"><?php echo contracts_h(contract_format_kyiv_datetime($contract['signed_at'])); ?></td>
                                            <td data-label="<?php echo contracts_h($copy['table_files']); ?>">
                                                <div class="contracts-table-pdf-links">
                                                    <a href="contract_pdf.php?id=<?php echo contracts_h($contract['public_id']); ?>&amp;lang=uk&amp;v=6" target="_blank" rel="noopener" aria-label="<?php echo contracts_h($copy['table_pdf_ua']); ?>">UA</a>
                                                    <a href="contract_pdf.php?id=<?php echo contracts_h($contract['public_id']); ?>&amp;lang=ru&amp;v=6" target="_blank" rel="noopener" aria-label="<?php echo contracts_h($copy['table_pdf_ru']); ?>">RU</a>
                                                    <a href="contract_pdf.php?id=<?php echo contracts_h($contract['public_id']); ?>&amp;lang=en&amp;v=6" target="_blank" rel="noopener" aria-label="<?php echo contracts_h($copy['table_pdf_en']); ?>">EN</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
            <div class="contracts-filter-empty card" data-contract-filter-empty hidden>
                <span>0</span>
                <h3><?php echo contracts_h($copy['filter_empty_title']); ?></h3>
                <p><?php echo contracts_h($copy['filter_empty_note']); ?></p>
            </div>
        <?php else: ?>
            <div class="contracts-empty card"><span>7</span><h3><?php echo contracts_h($copy['empty']); ?></h3></div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
