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
    'login_required' => 'You must sign in to perform this action.',
    'csrf' => 'CSRF error. Please try again.',
    'closed_comments' => 'This report is closed. Comments cannot be added.',
    'author_or_admin' => 'Only the report author and administrators can leave comments.',
    'comment_spam' => 'Anti-spam: you can leave comments no more than once per minute. Please wait.',
    'recaptcha' => 'Please confirm that you are not a robot (reCAPTCHA).',
    'comment_short' => 'The comment is too short.',
    'comment_added' => 'Comment added.',
    'comment_error' => 'Error adding the comment.',
    'status_updated' => 'Status updated.',
    'status_error' => 'Error updating the status.',
    'approve_success' => 'Report approved successfully!',
    'comment_deleted' => 'Comment deleted.',
    'restrict_level' => 'You cannot restrict an employee of equal or higher rank.',
    'reports_banned' => 'The user has been blocked from submitting reports.',
    'reports_unbanned' => 'The user can submit reports again.',
    'not_found' => 'Bug report not found.',
    'private_closed' => 'This bug report was closed without publication.',
    'private_pending' => 'This bug report is awaiting administrator review.',
    'status_open' => 'Open',
    'status_in_progress' => 'In progress',
    'status_resolved' => 'Resolved',
    'status_closed' => 'Closed',
    'page_title' => 'Bug report #',
    'page_title_suffix' => ' — Project Orion server 0.8.2',
    'page_description' => 'Bug report #',
    'page_description_suffix' => ' on Project Orion server 0.8.2.',
    'banner_subtext' => 'Game server · 0.8.2',
    'back' => 'Back to bug tracker',
    'report_label' => 'Bug report #',
    'status' => 'Status',
    'report_number' => 'Report number:',
    'discussion' => 'Discussion',
    'comments' => 'Comments',
    'empty_comments' => 'No comments.',
    'delete_comment_confirm' => 'Delete this comment?',
    'delete' => 'Delete',
    'closed_note' => 'Topic closed. Discussion has ended.',
    'add_comment' => 'Add a comment',
    'comment_placeholder' => 'Your reply…',
    'send' => 'Submit',
    'author_comment_note' => 'Only the report author and administrators can leave comments.',
    'login' => 'Sign in',
    'details' => 'DETAILS',
    'manage' => 'Manage report',
    'author' => 'Author',
    'unknown' => 'Unknown',
    'created' => 'Created',
    'save_status' => 'Save status',
    'approve_confirm' => 'Approve this report?',
    'approve_public' => 'Approve (make public)',
    'danger' => 'Danger zone',
    'danger_description' => 'These actions affect the author’s report access or permanently delete the request.',
    'confirm' => 'Are you sure?',
    'unban_author' => 'Unblock author',
    'ban_author' => 'Block author (reports)',
    'delete_report_confirm' => 'Delete this report permanently?',
    'delete_report' => 'Delete report',
    'access_title' => 'Access denied',
    'access_heading' => 'Access denied',
    'access_back' => 'Go back',
] : [
    'login_required' => 'Вы должны войти в систему для этого действия.',
    'csrf' => 'Ошибка CSRF. Попробуйте еще раз.',
    'closed_comments' => 'Этот репорт закрыт. Добавление комментариев невозможно.',
    'author_or_admin' => 'Только автор репорта и администрация могут оставлять комментарии.',
    'comment_spam' => 'Анти-спам: вы можете оставлять комментарии не чаще, чем раз в минуту. Пожалуйста, подождите.',
    'recaptcha' => 'Пожалуйста, подтвердите, что вы не робот (reCAPTCHA).',
    'comment_short' => 'Комментарий слишком короткий.',
    'comment_added' => 'Комментарий добавлен.',
    'comment_error' => 'Ошибка при добавлении комментария.',
    'status_updated' => 'Статус обновлен.',
    'status_error' => 'Ошибка при обновлении статуса.',
    'approve_success' => 'Репорт успешно одобрен!',
    'comment_deleted' => 'Комментарий удален.',
    'restrict_level' => 'Нельзя ограничить сотрудника равного или более высокого уровня.',
    'reports_banned' => 'Пользователь забанен для репортов.',
    'reports_unbanned' => 'Пользователь разбанен.',
    'not_found' => 'Баг-репорт не найден.',
    'private_closed' => 'Этот баг-репорт закрыт без публикации.',
    'private_pending' => 'Этот баг-репорт ожидает проверки администратором.',
    'status_open' => 'Открыт',
    'status_in_progress' => 'В работе',
    'status_resolved' => 'Исправлено',
    'status_closed' => 'Закрыт',
    'page_title' => 'Баг-репорт #',
    'page_title_suffix' => ' — сервер Project Orion 0.8.2',
    'page_description' => 'Баг-репорт #',
    'page_description_suffix' => ' на сервере Project Orion 0.8.2.',
    'banner_subtext' => 'Игровой сервер · 0.8.2',
    'back' => '← Вернуться к баг-трекеру',
    'report_label' => 'Баг-репорт #',
    'status' => 'Статус',
    'report_number' => 'Номер репорта:',
    'discussion' => 'Обсуждение',
    'comments' => 'Комментарии',
    'empty_comments' => 'Нет комментариев.',
    'delete_comment_confirm' => 'Удалить комментарий?',
    'delete' => 'Удалить',
    'closed_note' => 'Тема закрыта. Обсуждение завершено.',
    'add_comment' => 'Добавить комментарий',
    'comment_placeholder' => 'Ваш ответ…',
    'send' => 'Отправить',
    'author_comment_note' => 'Только автор репорта и администраторы могут оставлять комментарии.',
    'login' => 'Войдите',
    'details' => 'Сведения',
    'manage' => 'Управление репортом',
    'author' => 'Автор',
    'unknown' => 'Неизвестно',
    'created' => 'Создан',
    'save_status' => 'Сохранить статус',
    'approve_confirm' => 'Одобрить этот репорт?',
    'approve_public' => 'Одобрить (сделать публичным)',
    'danger' => 'Опасная зона',
    'danger_description' => 'Эти действия влияют на доступ автора к репортам или безвозвратно удаляют обращение.',
    'confirm' => 'Вы уверены?',
    'unban_author' => 'Разбанить автора',
    'ban_author' => 'Забанить автора (репорты)',
    'delete_report_confirm' => 'Точно удалить этот репорт?',
    'delete_report' => 'Удалить репорт',
    'access_title' => 'Доступ закрыт',
    'access_heading' => 'Доступ закрыт',
    'access_back' => 'Вернуться назад',
];

$error = '';
$success = '';
$can_moderate_reports = session_has_staff_permission('reports.manage');
$can_delete_reports = session_has_staff_permission('reports.delete');
$can_restrict_report_authors = session_has_staff_permission('users.edit');

$bug_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$bug_id) {
    header('Location: ' . $locale_url('bugs.php'));
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION['user_id'])) {
        $error = $copy['login_required'];
    } elseif (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = $copy['csrf'];
    } else {
        if ($_POST['action'] === 'add_comment') {
            $check_stmt = $pdo->prepare("SELECT account_id, status FROM bug_reports WHERE id = ?");
            $check_stmt->execute([$bug_id]);
            $bug_info = $check_stmt->fetch();
            $bug_author_id = $bug_info['account_id'];
            
            $is_user_admin = $can_moderate_reports;
            
            if ($bug_info['status'] === 'closed') {
                $error = $copy['closed_comments'];
            } elseif (!$is_user_admin && $_SESSION['user_id'] != $bug_author_id) {
                $error = $copy['author_or_admin'];
            } else {
                $stmt = $pdo->prepare("SELECT created_at FROM bug_comments WHERE account_id = ? ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$_SESSION['user_id']]);
                $last_comment = $stmt->fetchColumn();

                if ($last_comment && time() - strtotime($last_comment) < 60 && !$can_moderate_reports) {
                    $error = $copy['comment_spam'];
                } elseif (!$can_moderate_reports && !verify_recaptcha($_POST['g-recaptcha-response'] ?? '')) {
                    $error = $copy['recaptcha'];
                } else {
                    $comment = trim($_POST['comment'] ?? '');
                    if (mb_strlen($comment) < 2) {
                        $error = $copy['comment_short'];
                    } else {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO bug_comments (bug_id, account_id, comment) VALUES (?, ?, ?)");
                            $stmt->execute([$bug_id, $_SESSION['user_id'], $comment]);
                            $success = $copy['comment_added'];
                        } catch (Exception $e) {
                            error_log("Add comment error: " . $e->getMessage());
                            $error = $copy['comment_error'];
                        }
                    }
                }
            }
        } elseif ($_POST['action'] === 'change_status' && $can_moderate_reports) {
            $status = $_POST['status'] ?? 'open';
            $valid_statuses = ['open', 'in_progress', 'resolved', 'closed'];
            if (in_array($status, $valid_statuses)) {
                try {
                    $stmt = $pdo->prepare("UPDATE bug_reports SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $bug_id]);
                    log_staff_action($pdo, 'report.update', 'bug_report', $bug_id, 'Статус репорта изменён на ' . $status);
                    $success = $copy['status_updated'];
                } catch (Exception $e) {
                    error_log("Change status error: " . $e->getMessage());
                    $error = $copy['status_error'];
                }
            }
        } elseif ($_POST['action'] === 'approve_bug' && $can_moderate_reports) {
            $pdo->prepare("UPDATE bug_reports SET is_approved = 1 WHERE id = ?")->execute([$bug_id]);
            log_staff_action($pdo, 'report.update', 'bug_report', $bug_id, 'Репорт одобрен', ['approved' => 1]);
            $success = $copy['approve_success'];
        } elseif ($_POST['action'] === 'delete_bug' && $can_delete_reports) {
            $pdo->prepare("DELETE FROM bug_comments WHERE bug_id = ?")->execute([$bug_id]);
            $pdo->prepare("DELETE FROM bug_reports WHERE id = ?")->execute([$bug_id]);
            log_staff_action($pdo, 'report.delete', 'bug_report', $bug_id, 'Репорт удалён');
            header('Location: ' . $locale_url('bugs.php'));
            exit;
        } elseif ($_POST['action'] === 'delete_comment' && $can_delete_reports) {
            $comment_id = intval($_POST['comment_id'] ?? 0);
            $pdo->prepare("DELETE FROM bug_comments WHERE id = ?")->execute([$comment_id]);
            log_staff_action($pdo, 'report.comment.delete', 'bug_comment', $comment_id, 'Удалён комментарий в репорте #' . $bug_id);
            $success = $copy['comment_deleted'];
        } elseif ($_POST['action'] === 'toggle_ban' && $can_restrict_report_authors) {
            $author_id = intval($_POST['author_id'] ?? 0);
            $new_ban_status = intval($_POST['ban_status'] ?? 0);
            $actor_access = staff_access_for_account($pdo, intval($_SESSION['user_id']));
            $author_access = staff_access_for_account($pdo, $author_id);
            if (!staff_can_act_on_account($actor_access, $author_access)) {
                $error = $copy['restrict_level'];
            } else {
                $pdo->prepare("UPDATE accounts SET is_banned_reports = ? WHERE id = ?")->execute([$new_ban_status, $author_id]);
                log_staff_action($pdo, 'report.author.restrict', 'account', $author_id, $new_ban_status ? 'Запрещена отправка репортов' : 'Разрешена отправка репортов');
                $success = $new_ban_status ? $copy['reports_banned'] : $copy['reports_unbanned'];
            }
        }
    }
}

// Fetch bug details
$bug = null;
try {
    $stmt = $pdo->prepare("
        SELECT b.*, a.username, a.is_admin, a.staff_role, a.is_banned_reports
        FROM bug_reports b 
        LEFT JOIN accounts a ON b.account_id = a.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$bug_id]);
    $bug = $stmt->fetch();
} catch (Exception $e) {
    error_log("Fetch bug error: " . $e->getMessage());
}

if (!$bug) {
    die($copy['not_found']);
}

$is_admin = $can_moderate_reports;
$is_approved = intval($bug['is_approved'] ?? 0) === 1;
$is_closed = (string)($bug['status'] ?? '') === 'closed';
$bug_author_role = normalize_staff_role($bug['staff_role'] ?? '', intval($bug['is_admin']) === 1);
$viewer_access = !empty($_SESSION['user_id']) ? staff_access_for_account($pdo, intval($_SESSION['user_id'])) : null;
$bug_author_access = staff_access_for_account($pdo, intval($bug['account_id']));
$can_restrict_bug_author = $can_restrict_report_authors && staff_can_act_on_account($viewer_access, $bug_author_access);

if (!$is_approved && !$is_admin) {
    $private_report_message = $is_closed
        ? $copy['private_closed']
        : $copy['private_pending'];
    $access_lang = htmlspecialchars($ui_lang, ENT_QUOTES, 'UTF-8');
    $access_title = htmlspecialchars($copy['access_title'], ENT_QUOTES, 'UTF-8');
    $access_heading = htmlspecialchars($copy['access_heading'], ENT_QUOTES, 'UTF-8');
    $access_message = htmlspecialchars($private_report_message, ENT_QUOTES, 'UTF-8');
    $access_back = htmlspecialchars($copy['access_back'], ENT_QUOTES, 'UTF-8');
    $access_url = htmlspecialchars($locale_url('bugs.php'), ENT_QUOTES, 'UTF-8');
    die("<!doctype html><html lang=\"{$access_lang}\"><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><link rel='stylesheet' href='style.css'><title>{$access_title}</title></head><body><main class='access-denied-page'><section class='access-denied-card'><h1>{$access_heading}</h1><p>{$access_message}</p><a href='{$access_url}' class='btn btn-primary'>{$access_back}</a></section></main></body></html>");
}

// Fetch comments
$comments = [];
try {
    $stmt = $pdo->prepare("
        SELECT c.*, a.username, a.is_admin, a.staff_role
        FROM bug_comments c 
        LEFT JOIN accounts a ON c.account_id = a.id 
        WHERE c.bug_id = ? 
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$bug_id]);
    $comments = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Fetch comments error: " . $e->getMessage());
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
$page_title = $copy['page_title'] . $bug_id . $copy['page_title_suffix'];
$page_description = $copy['page_description'] . $bug_id . $copy['page_description_suffix'];
$page_path = 'bug_view.php?id=' . $bug_id;
$seo_index = false;
$active_page = 'bugs';
$banner_subtext = $copy['banner_subtext'];
$head_extra = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell bug-detail-page">
    <header class="page-header bug-detail-header">
        <a href="<?php echo htmlspecialchars($locale_url('bugs.php'), ENT_QUOTES, 'UTF-8'); ?>" class="back-link"><?php echo htmlspecialchars($copy['back'], ENT_QUOTES, 'UTF-8'); ?></a>
        <p class="eyebrow"><?php echo htmlspecialchars($copy['report_label'], ENT_QUOTES, 'UTF-8'); ?><?php echo $bug['id']; ?></p>
        <h1 data-i18n-ignore translate="no"><?php echo htmlspecialchars($bug['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
    </header>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="bug-detail-grid">
        <div class="bug-detail-main">
            <article class="card bug-issue-card">
                <div class="bug-status-banner">
                    <div><?php echo htmlspecialchars($copy['status'], ENT_QUOTES, 'UTF-8'); ?>: <?php echo get_status_label($bug['status']); ?></div>
                    <div class="bug-id"><?php echo htmlspecialchars($copy['report_number'], ENT_QUOTES, 'UTF-8'); ?> #<?php echo $bug['id']; ?></div>
                </div>
                <div class="bug-issue-body">
                    <div class="comment-header">
                        <div>
                            <span class="comment-author" data-i18n-ignore translate="no"><?php echo htmlspecialchars($bug['username'] ?? $copy['unknown'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php $bug_author_role = normalize_staff_role($bug['staff_role'] ?? '', intval($bug['is_admin']) === 1); ?>
                            <?php if ($bug_author_role !== 'player'): ?><span class="admin-badge"><?php echo htmlspecialchars(staff_role_info($bug_author_role)['short_label'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                        </div>
                        <time data-i18n-ignore translate="no"><?php echo htmlspecialchars($bug['created_at'], ENT_QUOTES, 'UTF-8'); ?></time>
                    </div>
                    <div class="comment-body bug-description" data-i18n-ignore translate="no"><?php echo htmlspecialchars($bug['description'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </article>

            <section class="bug-comments" aria-labelledby="comments-title">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow"><?php echo htmlspecialchars($copy['discussion'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <h2 id="comments-title"><?php echo htmlspecialchars($copy['comments'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo count($comments); ?>)</h2>
                    </div>
                </div>

                <div class="comment-list">
                    <?php if (empty($comments)): ?>
                        <div class="empty-state"><?php echo htmlspecialchars($copy['empty_comments'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                            <article class="comment-box">
                                <div class="comment-header">
                                    <div>
                                        <span class="comment-author" data-i18n-ignore translate="no"><?php echo htmlspecialchars($c['username'] ?? $copy['unknown'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php $comment_role = normalize_staff_role($c['staff_role'] ?? '', intval($c['is_admin']) === 1); ?>
                                        <?php if ($comment_role !== 'player'): ?><span class="admin-badge"><?php echo htmlspecialchars(staff_role_info($comment_role)['short_label'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                                    </div>
                                    <div class="comment-meta-actions">
                                        <time data-i18n-ignore translate="no"><?php echo htmlspecialchars($c['created_at'], ENT_QUOTES, 'UTF-8'); ?></time>
                                        <?php if ($can_delete_reports): ?>
                                            <form method="POST" onsubmit="return confirm('<?php echo htmlspecialchars($copy['delete_comment_confirm'], ENT_QUOTES, 'UTF-8'); ?>');" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <input type="hidden" name="action" value="delete_comment">
                                                <input type="hidden" name="comment_id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" class="danger-link"><?php echo htmlspecialchars($copy['delete'], ENT_QUOTES, 'UTF-8'); ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="comment-body" data-i18n-ignore translate="no"><?php echo htmlspecialchars($c['comment'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($bug['status'] === 'closed'): ?>
                <div class="discussion-note">
                    <?php echo htmlspecialchars($copy['closed_note'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php elseif (isset($_SESSION['user_id']) && ($is_admin || $_SESSION['user_id'] == $bug['account_id'])): ?>
                <section class="card comment-form-card">
                    <div class="card-header">
                        <h2 class="card-title"><?php echo htmlspecialchars($copy['add_comment'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($locale_url('bug_view.php?id=' . $bug_id), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="add_comment">
                            <div class="form-group">
                                <textarea name="comment" class="form-control" rows="3" required placeholder="<?php echo htmlspecialchars($copy['comment_placeholder'], ENT_QUOTES, 'UTF-8'); ?>"></textarea>
                            </div>
                            <?php if (!$can_moderate_reports): ?><div class="form-group recaptcha-field">
                                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY, ENT_QUOTES, 'UTF-8'); ?>"></div>
                            </div><?php endif; ?>
                            <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($copy['send'], ENT_QUOTES, 'UTF-8'); ?></button>
                        </form>
                    </div>
                </section>
            <?php elseif (isset($_SESSION['user_id'])): ?>
                <div class="discussion-note">
                    <?php echo htmlspecialchars($copy['author_comment_note'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php else: ?>
                <div class="discussion-note">
                    <a href="<?php echo htmlspecialchars($locale_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['login'], ENT_QUOTES, 'UTF-8'); ?></a><?php echo $ui_lang === 'en' ? ' to leave a comment.' : ', чтобы оставить комментарий.'; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="bug-actions-card">
            <p class="eyebrow"><?php echo htmlspecialchars($copy['details'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h2><?php echo htmlspecialchars($copy['manage'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <dl class="bug-facts">
                <div>
                    <dt><?php echo htmlspecialchars($copy['status'], ENT_QUOTES, 'UTF-8'); ?></dt>
                    <dd><?php echo get_status_label($bug['status']); ?></dd>
                </div>
                <div>
                    <dt><?php echo htmlspecialchars($copy['author'], ENT_QUOTES, 'UTF-8'); ?></dt>
                    <dd data-i18n-ignore translate="no"><?php echo htmlspecialchars($bug['username'] ?? $copy['unknown'], ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div>
                    <dt><?php echo htmlspecialchars($copy['created'], ENT_QUOTES, 'UTF-8'); ?></dt>
                    <dd data-i18n-ignore translate="no"><?php echo htmlspecialchars($bug['created_at'], ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
            </dl>

            <?php if ($can_moderate_reports): ?>
                <div class="admin-controls">
                        <form method="POST" action="<?php echo htmlspecialchars($locale_url('bug_view.php?id=' . $bug_id), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="action" value="change_status">
                        <div class="form-group">
                            <label for="bug-status"><?php echo htmlspecialchars($copy['status'], ENT_QUOTES, 'UTF-8'); ?></label>
                            <select name="status" id="bug-status" class="form-control">
                                <option value="open" <?php echo $bug['status'] === 'open' ? 'selected' : ''; ?>><?php echo htmlspecialchars($copy['status_open'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <option value="in_progress" <?php echo $bug['status'] === 'in_progress' ? 'selected' : ''; ?>><?php echo htmlspecialchars($copy['status_in_progress'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <option value="resolved" <?php echo $bug['status'] === 'resolved' ? 'selected' : ''; ?>><?php echo htmlspecialchars($copy['status_resolved'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <option value="closed" <?php echo $bug['status'] === 'closed' ? 'selected' : ''; ?>><?php echo htmlspecialchars($copy['status_closed'], ENT_QUOTES, 'UTF-8'); ?></option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-block"><?php echo htmlspecialchars($copy['save_status'], ENT_QUOTES, 'UTF-8'); ?></button>
                    </form>

                    <?php if (!$is_approved && !$is_closed): ?>
                        <form method="POST" onsubmit="return confirm('<?php echo htmlspecialchars($copy['approve_confirm'], ENT_QUOTES, 'UTF-8'); ?>');" class="admin-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="approve_bug">
                            <button type="submit" class="btn btn-approve btn-block"><?php echo htmlspecialchars($copy['approve_public'], ENT_QUOTES, 'UTF-8'); ?></button>
                        </form>
                    <?php endif; ?>

                    <section class="danger-zone">
                        <h3><?php echo htmlspecialchars($copy['danger'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars($copy['danger_description'], ENT_QUOTES, 'UTF-8'); ?></p>

                        <?php if ($can_restrict_bug_author): ?>
                            <form method="POST" onsubmit="return confirm('<?php echo htmlspecialchars($copy['confirm'], ENT_QUOTES, 'UTF-8'); ?>');" class="danger-form">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="toggle_ban">
                                <input type="hidden" name="author_id" value="<?php echo $bug['account_id']; ?>">
                                <?php if ($bug['is_banned_reports']): ?>
                                    <input type="hidden" name="ban_status" value="0">
                                    <button type="submit" class="btn btn-unban btn-block"><?php echo htmlspecialchars($copy['unban_author'], ENT_QUOTES, 'UTF-8'); ?></button>
                                <?php else: ?>
                                    <input type="hidden" name="ban_status" value="1">
                                    <button type="submit" class="btn btn-danger btn-block"><?php echo htmlspecialchars($copy['ban_author'], ENT_QUOTES, 'UTF-8'); ?></button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>

                        <?php if ($can_delete_reports): ?><form method="POST" onsubmit="return confirm('<?php echo htmlspecialchars($copy['delete_report_confirm'], ENT_QUOTES, 'UTF-8'); ?>');" class="danger-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="delete_bug">
                            <button type="submit" class="btn btn-danger btn-block"><?php echo htmlspecialchars($copy['delete_report'], ENT_QUOTES, 'UTF-8'); ?></button>
                        </form><?php endif; ?>
                    </section>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
