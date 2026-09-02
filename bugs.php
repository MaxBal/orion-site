<?php
if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en' && !defined('ORION_SKIP_LANGUAGE_REDIRECT')) {
    define('ORION_SKIP_LANGUAGE_REDIRECT', true);
}
require_once 'db.php';
require_once 'recaptcha.php';

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
$locale_url = static function ($path) use ($ui_lang) {
    $path = (string)$path;
    if ($ui_lang === 'ru' || preg_match('/(?:^|[?&])lang=/', $path)) {
        return $path;
    }
    return $path . (strpos($path, '?') === false ? '?' : '&') . 'lang=' . rawurlencode($ui_lang);
};
$copy = $ui_lang === 'en' ? [
    'csrf' => 'CSRF error. Please try again.',
    'login_required' => 'You must sign in to create a bug report.',
    'banned_reports' => 'Your account has been blocked from creating bug reports by the administration.',
    'anti_spam' => 'Anti-spam: you can create bug reports no more than once per hour. Please wait.',
    'recaptcha' => 'Please confirm that you are not a robot (reCAPTCHA).',
    'title_length' => 'The title must be 5 to 100 characters long.',
    'description_length' => 'The description must contain at least 10 characters.',
    'create_success' => 'Bug report submitted successfully. It will appear on the site after administrator review.',
    'create_error' => 'An error occurred while creating the bug report.',
    'approve_success' => 'Report approved successfully!',
    'delete_success' => 'Report deleted!',
    'status_open' => 'Open',
    'status_in_progress' => 'In progress',
    'status_resolved' => 'Resolved',
    'status_closed' => 'Closed',
    'page_title' => 'Bug reports: Project Orion server 0.8.2',
    'page_description' => 'Project Orion server 0.8.2 bug reports: report an issue and track its resolution.',
    'banner_subtext' => 'Game server · 0.8.2',
    'feedback' => 'ALPHA TEST FEEDBACK',
    'bug_tracker' => 'Bug tracker',
    'lead' => 'Report problems you find and track their progress toward a fix.',
    'create_report' => 'Create report',
    'alpha_heading' => '⚠ Project is in ALPHA TEST',
    'alpha_body' => 'Project Orion server 0.8.2 is an early alpha test. Crashes, bugs, desynchronization, imbalance, and unfinished features are normal and expected at this stage. Many problems are already known and are being worked on. Before creating a report, please check whether a similar issue is already listed below. Describe the bug in detail: what you did, what you expected to see, and what actually happened. This helps us fix it much faster. Thank you for helping test the server!',
    'all_requests' => 'ALL REQUESTS',
    'forum' => 'Bug report forum',
    'sort' => 'Sort',
    'newest' => 'Newest first',
    'oldest' => 'Oldest first',
    'by_status' => 'By status',
    'by_title' => 'By title (A-Z)',
    'apply' => 'Apply',
    'empty' => 'No bug reports yet.',
    'report_prefix' => 'Report',
    'closed_unpublished' => 'Closed without publication',
    'awaiting_review' => 'Awaiting review',
    'author' => 'Author',
    'unknown' => 'Unknown',
    'created' => 'Created',
    'approve_confirm' => 'Approve this report? It will become visible to everyone.',
    'approve' => 'Approve',
    'delete_confirm' => 'Delete this report permanently?',
    'delete' => 'Delete',
    'sending' => 'Sending…',
    'title_label' => 'Title (briefly describe the problem)',
    'description_label' => 'Detailed description',
    'description_placeholder' => 'Describe how to reproduce the bug…',
    'send' => 'Submit',
    'auth_required' => 'Only signed-in users can create bug reports.',
    'login' => 'Sign in',
] : [
    'csrf' => 'Ошибка CSRF. Попробуйте еще раз.',
    'login_required' => 'Вы должны войти в систему, чтобы создать баг-репорт.',
    'banned_reports' => 'Ваш аккаунт заблокирован для создания баг-репортов администрацией.',
    'anti_spam' => 'Анти-спам: вы можете создавать баг-репорты не чаще, чем раз в час. Пожалуйста, подождите.',
    'recaptcha' => 'Пожалуйста, подтвердите, что вы не робот (reCAPTCHA).',
    'title_length' => 'Заголовок должен содержать от 5 до 100 символов.',
    'description_length' => 'Описание должно содержать минимум 10 символов.',
    'create_success' => 'Баг-репорт успешно отправлен. Он появится на сайте после проверки администратором.',
    'create_error' => 'Произошла ошибка при создании баг-репорта.',
    'approve_success' => 'Репорт успешно одобрен!',
    'delete_success' => 'Репорт был удален!',
    'status_open' => 'Открыт',
    'status_in_progress' => 'В работе',
    'status_resolved' => 'Исправлено',
    'status_closed' => 'Закрыт',
    'page_title' => 'Баг-репорты — сервер Project Orion 0.8.2',
    'page_description' => 'Баг-репорты сервера Project Orion 0.8.2: сообщи об ошибке и следи за статусом исправления.',
    'banner_subtext' => 'Игровой сервер · 0.8.2',
    'feedback' => 'Обратная связь альфа-теста',
    'bug_tracker' => 'Баг-трекер',
    'lead' => 'Сообщайте о найденных проблемах и следите за ходом их исправления.',
    'create_report' => 'Создать репорт',
    'alpha_heading' => '⚠ Проект на стадии АЛЬФА-ТЕСТА',
    'alpha_body' => 'Сервер Project Orion 0.8.2 — это ранний альфа-тест. Вылеты, баги, рассинхрон, дисбаланс и недоработки на этом этапе — это нормально и ожидаемо. Многие проблемы нам уже известны и находятся в работе. Прежде чем создавать репорт, пожалуйста, проверьте, нет ли уже похожего в списке ниже. Описывайте баг подробно: что вы делали, что ожидали увидеть и что произошло на самом деле — так мы исправим его гораздо быстрее. Спасибо, что помогаете тестировать сервер!',
    'all_requests' => 'Все обращения',
    'forum' => 'Форум баг-репортов',
    'sort' => 'Сортировка',
    'newest' => 'Сначала новые',
    'oldest' => 'Сначала старые',
    'by_status' => 'По статусу',
    'by_title' => 'По названию (А-Я)',
    'apply' => 'Применить',
    'empty' => 'Баг-репортов пока нет.',
    'report_prefix' => 'Репорт',
    'closed_unpublished' => 'Закрыт без публикации',
    'awaiting_review' => 'Ожидает проверки',
    'author' => 'Автор',
    'unknown' => 'Неизвестно',
    'created' => 'Создано',
    'approve_confirm' => 'Одобрить этот репорт? Он станет виден всем.',
    'approve' => 'Одобрить',
    'delete_confirm' => 'Точно удалить этот репорт навсегда?',
    'delete' => 'Удалить',
    'sending' => 'Отправка…',
    'title_label' => 'Заголовок (кратко о проблеме)',
    'description_label' => 'Подробное описание',
    'description_placeholder' => 'Опишите, как воспроизвести баг…',
    'send' => 'Отправить',
    'auth_required' => 'Только авторизованные пользователи могут создавать баг-репорты.',
    'login' => 'Войти',
];

$error = '';
$success = '';
$can_moderate_reports = session_has_staff_permission('reports.manage');
$can_delete_reports = session_has_staff_permission('reports.delete');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = $copy['csrf'];
    } else {
        $action = $_POST['action'];
        
        if ($action === 'create_bug') {
            if (!isset($_SESSION['user_id'])) {
                $error = $copy['login_required'];
            } else {
                $stmt = $pdo->prepare("SELECT is_banned_reports FROM accounts WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $is_banned = intval($stmt->fetchColumn()) === 1;

                $stmt = $pdo->prepare("SELECT TIMESTAMPDIFF(SECOND, MAX(created_at), NOW()) FROM bug_reports WHERE account_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $seconds_since_last = $stmt->fetchColumn();
                $recent_bugs = ($seconds_since_last !== null && $seconds_since_last !== false && intval($seconds_since_last) < 3600) ? 1 : 0;

                if ($is_banned && !$can_moderate_reports) {
                    $error = $copy['banned_reports'];
                } elseif ($recent_bugs > 0 && !$can_moderate_reports) {
                    $error = $copy['anti_spam'];
                } elseif (!verify_recaptcha($_POST['g-recaptcha-response'] ?? '')) {
                    $error = $copy['recaptcha'];
                } else {
                    $title = trim($_POST['title'] ?? '');
                    $description = trim($_POST['description'] ?? '');

                    if (mb_strlen($title) < 5 || mb_strlen($title) > 100) {
                        $error = $copy['title_length'];
                    } elseif (mb_strlen($description) < 10) {
                        $error = $copy['description_length'];
                    } else {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO bug_reports (account_id, title, description, status, is_approved) VALUES (?, ?, ?, 'open', 0)");
                            $stmt->execute([$_SESSION['user_id'], $title, $description]);
                            $success = $copy['create_success'];
                        } catch (Exception $e) {
                            error_log("Create bug error: " . $e->getMessage());
                            $error = $copy['create_error'];
                        }
                    }
                }
            }
        } elseif ($action === 'approve_bug' && $can_moderate_reports) {
            $bug_id = intval($_POST['bug_id'] ?? 0);
            $pdo->prepare("UPDATE bug_reports SET is_approved = 1 WHERE id = ?")->execute([$bug_id]);
            log_staff_action($pdo, 'report.update', 'bug_report', $bug_id, 'Репорт одобрен на публичной странице', ['approved' => 1]);
            $success = $copy['approve_success'];
        } elseif ($action === 'delete_bug' && $can_delete_reports) {
            $bug_id = intval($_POST['bug_id'] ?? 0);
            $pdo->prepare("DELETE FROM bug_comments WHERE bug_id = ?")->execute([$bug_id]);
            $pdo->prepare("DELETE FROM bug_reports WHERE id = ?")->execute([$bug_id]);
            log_staff_action($pdo, 'report.delete', 'bug_report', $bug_id, 'Репорт удалён на публичной странице');
            $success = $copy['delete_success'];
        }
    }
}

$sort_options = [
    'newest'   => 'b.created_at DESC',
    'oldest'   => 'b.created_at ASC',
    'status'   => "CASE b.status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'resolved' THEN 3 WHEN 'closed' THEN 4 ELSE 5 END, b.created_at DESC",
    'title'    => 'b.title ASC',
];
$sort = $_GET['sort'] ?? 'newest';
if (!isset($sort_options[$sort])) {
    $sort = 'newest';
}
$order_by = $sort_options[$sort];

// Fetch bugs
$bugs = [];
try {
    $stmt = $pdo->query("
        SELECT b.*, a.username
        FROM bug_reports b
        LEFT JOIN accounts a ON b.account_id = a.id
        ORDER BY " . $order_by . "
    ");
    $bugs = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Fetch bugs error: " . $e->getMessage());
}

$status_groups = [
    'open'        => $copy['status_open'],
    'in_progress' => $copy['status_in_progress'],
    'resolved'    => $copy['status_resolved'],
    'closed'      => $copy['status_closed'],
];
$grouped_bugs = ['open' => [], 'in_progress' => [], 'resolved' => [], 'closed' => []];
foreach ($bugs as $b) {
    $st = $b['status'];
    if (!isset($grouped_bugs[$st])) {
        $grouped_bugs[$st] = [];
    }
    $grouped_bugs[$st][] = $b;
}

$active_page = 'bugs';

function get_status_label($status) {
    global $copy;
    switch ($status) {
        case 'open': return '<span class="status-badge status-open">' . htmlspecialchars($copy['status_open'], ENT_QUOTES, 'UTF-8') . '</span>';
        case 'in_progress': return '<span class="status-badge status-in-progress">' . htmlspecialchars($copy['status_in_progress'], ENT_QUOTES, 'UTF-8') . '</span>';
        case 'resolved': return '<span class="status-badge status-resolved">' . htmlspecialchars($copy['status_resolved'], ENT_QUOTES, 'UTF-8') . '</span>';
        case 'closed': return '<span class="status-badge status-closed">' . htmlspecialchars($copy['status_closed'], ENT_QUOTES, 'UTF-8') . '</span>';
        default: return $status;
    }
}
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'bugs.php';
$active_page = 'bugs';
$banner_subtext = $copy['banner_subtext'];
$head_extra = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell bugs-page">
    <header class="page-header-row">
        <div class="page-header">
            <p class="eyebrow"><?php echo htmlspecialchars($copy['feedback'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h1><?php echo htmlspecialchars($copy['bug_tracker'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p><?php echo htmlspecialchars($copy['lead'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <a href="#new-bug-report" class="btn btn-primary"><?php echo htmlspecialchars($copy['create_report'], ENT_QUOTES, 'UTF-8'); ?></a>
    </header>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <aside class="alpha-banner">
        <h2><?php echo htmlspecialchars($copy['alpha_heading'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p>
            <?php echo htmlspecialchars($copy['alpha_body'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
    </aside>

    <div class="bugs-grid">
        <section class="bug-board" aria-labelledby="bug-list-title">
            <div class="section-heading">
                <div>
                    <p class="eyebrow"><?php echo htmlspecialchars($copy['all_requests'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <h2 id="bug-list-title"><?php echo htmlspecialchars($copy['forum'], ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>
            </div>

            <form method="GET" action="<?php echo htmlspecialchars($locale_url('bugs.php'), ENT_QUOTES, 'UTF-8'); ?>" class="bug-toolbar">
                <div class="form-group">
                    <label for="sort"><?php echo htmlspecialchars($copy['sort'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <select name="sort" id="sort" class="form-control" onchange="this.form.submit()">
                        <option value="newest"<?php echo $sort === 'newest' ? ' selected' : ''; ?>><?php echo htmlspecialchars($copy['newest'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <option value="oldest"<?php echo $sort === 'oldest' ? ' selected' : ''; ?>><?php echo htmlspecialchars($copy['oldest'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <option value="status"<?php echo $sort === 'status' ? ' selected' : ''; ?>><?php echo htmlspecialchars($copy['by_status'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <option value="title"<?php echo $sort === 'title' ? ' selected' : ''; ?>><?php echo htmlspecialchars($copy['by_title'], ENT_QUOTES, 'UTF-8'); ?></option>
                    </select>
                </div>
                <noscript><button type="submit" class="btn btn-secondary bug-filter-submit"><?php echo htmlspecialchars($copy['apply'], ENT_QUOTES, 'UTF-8'); ?></button></noscript>
            </form>

            <div class="bug-list">
                <?php if (empty($bugs)): ?>
                    <div class="empty-state"><?php echo htmlspecialchars($copy['empty'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php else: ?>
                    <?php $is_admin = $can_moderate_reports; ?>
                    <?php foreach ($status_groups as $st_key => $st_name): ?>
                        <?php $group = $grouped_bugs[$st_key] ?? []; ?>
                        <?php if (empty($group)) { continue; } ?>
                        <details class="bug-group"<?php echo $st_key === 'open' ? ' open' : ''; ?>>
                            <summary>
                                <span class="chevron" aria-hidden="true">▶</span>
                                <?php echo get_status_label($st_key); ?>
                                <span class="group-count"><?php echo count($group); ?></span>
                            </summary>
                            <div class="bug-group-list">
                                <?php foreach ($group as $bug): ?>
                                    <?php $is_approved = intval($bug['is_approved']) === 1; ?>
                                    <?php $is_closed = (string)$bug['status'] === 'closed'; ?>
                                    <article class="bug-card">
                                        <?php if ($is_approved || $is_admin): ?>
                                            <a href="<?php echo htmlspecialchars($locale_url('bug_view.php?id=' . $bug['id']), ENT_QUOTES, 'UTF-8'); ?>" class="bug-title" data-i18n-ignore translate="no"><?php echo htmlspecialchars($bug['title'], ENT_QUOTES, 'UTF-8'); ?></a>
                                        <?php else: ?>
                                            <span class="bug-title bug-title--pending"><?php echo htmlspecialchars($copy['report_prefix'], ENT_QUOTES, 'UTF-8'); ?> #<?php echo $bug['id']; ?> - <?php echo htmlspecialchars($is_closed ? $copy['closed_unpublished'] : $copy['awaiting_review'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>

                                        <div class="bug-meta">
                                            <?php echo get_status_label($bug['status']); ?>
                                            <span><?php echo htmlspecialchars($copy['author'], ENT_QUOTES, 'UTF-8'); ?>: <span data-i18n-ignore translate="no"><?php echo htmlspecialchars($bug['username'] ?? $copy['unknown'], ENT_QUOTES, 'UTF-8'); ?></span></span>
                                            <span><?php echo htmlspecialchars($copy['created'], ENT_QUOTES, 'UTF-8'); ?>: <span data-i18n-ignore translate="no"><?php echo htmlspecialchars($bug['created_at'], ENT_QUOTES, 'UTF-8'); ?></span></span>

                                            <?php if ($is_admin): ?>
                                                <div class="bug-card-actions">
                                                    <?php if (!$is_approved && !$is_closed): ?>
                                                        <form method="POST" class="inline-form" onsubmit="return confirm('<?php echo htmlspecialchars($copy['approve_confirm'], ENT_QUOTES, 'UTF-8'); ?>');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                            <input type="hidden" name="action" value="approve_bug">
                                                            <input type="hidden" name="bug_id" value="<?php echo $bug['id']; ?>">
                                                            <button type="submit" class="status-badge admin-action admin-action--approve"><?php echo htmlspecialchars($copy['approve'], ENT_QUOTES, 'UTF-8'); ?></button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <?php if ($can_delete_reports): ?><form method="POST" class="inline-form" onsubmit="return confirm('<?php echo htmlspecialchars($copy['delete_confirm'], ENT_QUOTES, 'UTF-8'); ?>');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                        <input type="hidden" name="action" value="delete_bug">
                                                        <input type="hidden" name="bug_id" value="<?php echo $bug['id']; ?>">
                                                        <button type="submit" class="status-badge admin-action admin-action--delete"><?php echo htmlspecialchars($copy['delete'], ENT_QUOTES, 'UTF-8'); ?></button>
                                                    </form><?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <aside id="new-bug-report" class="card bug-report-card">
            <div class="card-header">
                <h2 class="card-title"><?php echo htmlspecialchars($copy['create_report'], ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="<?php echo htmlspecialchars($locale_url('bugs.php'), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerHTML='<?php echo htmlspecialchars($copy['sending'], ENT_QUOTES, 'UTF-8'); ?>';">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="action" value="create_bug">

                        <div class="form-group">
                            <label for="bug-title"><?php echo htmlspecialchars($copy['title_label'], ENT_QUOTES, 'UTF-8'); ?></label>
                            <input type="text" name="title" id="bug-title" class="form-control" required minlength="5" maxlength="100">
                        </div>

                        <div class="form-group">
                            <label for="bug-description"><?php echo htmlspecialchars($copy['description_label'], ENT_QUOTES, 'UTF-8'); ?></label>
                            <textarea name="description" id="bug-description" class="form-control" rows="5" required minlength="10" placeholder="<?php echo htmlspecialchars($copy['description_placeholder'], ENT_QUOTES, 'UTF-8'); ?>"></textarea>
                        </div>

                        <div class="form-group recaptcha-field">
                            <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY, ENT_QUOTES, 'UTF-8'); ?>"></div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block"><?php echo htmlspecialchars($copy['send'], ENT_QUOTES, 'UTF-8'); ?></button>
                    </form>
                <?php else: ?>
                    <p class="muted-copy"><?php echo htmlspecialchars($copy['auth_required'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <a href="<?php echo htmlspecialchars($locale_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary btn-block"><?php echo htmlspecialchars($copy['login'], ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
