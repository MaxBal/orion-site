<?php
require_once __DIR__ . '/db.php';

$return_to = (string)($_POST['return_to'] ?? 'index.php');
if ($return_to === ''
    || str_starts_with($return_to, '//')
    || preg_match('/[\\\r\n]/', $return_to) === 1
    || preg_match('/^[a-z][a-z0-9+.-]*:/i', $return_to) === 1
) {
    $return_to = 'index.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && !empty($_SESSION['user_id'])
    && session_is_staff()
    && verify_csrf($_POST['csrf_token'] ?? '')
) {
    staff_notifications_mark_read($pdo, intval($_SESSION['user_id']));
}

header('Location: ' . $return_to, true, 303);
exit;
