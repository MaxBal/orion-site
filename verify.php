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
$locale_url = static function ($path) use ($ui_lang) {
    $path = (string)$path;
    if ($ui_lang === 'ru' || preg_match('/(?:^|[?&])lang=/', $path)) {
        return $path;
    }
    return $path . (strpos($path, '?') === false ? '?' : '&') . 'lang=' . rawurlencode($ui_lang);
};
$copy = $ui_lang === 'en' ? [
    'session' => 'The session has expired. Refresh the page.',
    'invalid_email' => 'Enter a valid email address.',
    'already_verified' => 'This email is already verified. You can sign in.',
    'resent_if_exists' => 'If an account with this email exists, the code was sent again.',
    'code_wait' => 'The code was already sent. Wait a minute before requesting another one.',
    'internal' => 'An internal error occurred. Please try again later.',
    'code_empty' => 'Enter the code from the email.',
    'invalid_code' => 'The code is invalid or has expired. Request a new one.',
    'resent_to' => 'A new code was sent to',
    'page_title' => 'Email verification: Project Orion server 0.8.2',
    'page_description' => 'Verify the email address for your Project Orion game server 0.8.2 account.',
    'banner_subtext' => 'Email verification · 0.8.2',
    'title' => 'Email verification',
    'lead' => 'Enter the code from the email',
    'guidance' => 'We sent a 6-digit code to your email. Enter it below to activate your account.',
    'email' => 'Email',
    'code' => 'Code from the email',
    'already_login' => 'Already verified? Sign in',
    'confirm' => 'Verify',
    'resend_note' => 'Didn’t receive the code? Check your spam folder.',
    'resend' => 'Send code again',
    'email_subject' => 'Verification code: Project Orion',
    'email_title' => 'Registration verification',
    'email_body_prefix' => 'Enter this code on the website to activate the account “',
    'email_body_suffix' => '”.',
] : [
    'session' => 'Сессия устарела. Обновите страницу.',
    'invalid_email' => 'Укажите корректный email.',
    'already_verified' => 'Этот email уже подтверждён. Можете войти.',
    'resent_if_exists' => 'Если такой аккаунт существует, код отправлен повторно.',
    'code_wait' => 'Код уже отправлен. Подождите минуту перед повторной отправкой.',
    'internal' => 'Произошла внутренняя ошибка. Попробуйте позже.',
    'code_empty' => 'Введите код из письма.',
    'invalid_code' => 'Неверный или просроченный код. Запросите новый.',
    'resent_to' => 'Новый код отправлен на',
    'page_title' => 'Подтверждение email — сервер Project Orion 0.8.2',
    'page_description' => 'Подтверждение email аккаунта сервера Project Orion 0.8.2.',
    'banner_subtext' => 'Подтверждение email · 0.8.2',
    'title' => 'Подтверждение email',
    'lead' => 'Введите код из письма',
    'guidance' => 'Мы отправили 6-значный код на вашу почту. Введите его ниже, чтобы активировать аккаунт.',
    'email' => 'Email',
    'code' => 'Код из письма',
    'already_login' => 'Уже подтвердили? Войти',
    'confirm' => 'Подтвердить',
    'resend_note' => 'Не пришёл код? Проверьте папку «Спам».',
    'resend' => 'Отправить код повторно',
    'email_subject' => 'Код подтверждения — Project Orion',
    'email_title' => 'Подтверждение регистрации',
    'email_body_prefix' => 'Введите этот код на сайте, чтобы активировать аккаунт «',
    'email_body_suffix' => '».',
];

if (isset($_SESSION['user_id'])) {
    header('Location: ' . $locale_url('profile.php'));
    exit;
}

$error = '';
$success = '';
$info = '';
$info_email = '';

$email = trim($_GET['email'] ?? ($_SESSION['pending_verify_email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = $copy['session'];
    } else {
        $email = trim($_POST['email'] ?? $email);
        $action = $_POST['action'] ?? 'verify';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $copy['invalid_email'];
        } elseif ($action === 'resend') {
            try {
                $stmt = $pdo->prepare("SELECT id, username, is_verified FROM accounts WHERE email = ?");
                $stmt->execute([$email]);
                $acc = $stmt->fetch();
                if ($acc && intval($acc['is_verified']) === 1) {
                    $success = $copy['already_verified'];
                } elseif (!$acc) {
                    $info = $copy['resent_if_exists'];
                } elseif (!can_request_email_code($pdo, $email, 'register')) {
                    $error = $copy['code_wait'];
                } else {
                    $code = create_email_code($pdo, $acc['id'], $email, 'register');
                    send_email(
                        $email,
                        $copy['email_subject'],
                        render_code_email(
                            $copy['email_title'],
                            $copy['email_body_prefix'] . $acc['username'] . $copy['email_body_suffix'],
                            $code,
                            $ui_lang
                        )
                    );
                    $info = $copy['resent_to'];
                    $info_email = $email;
                }
            } catch (Exception $e) {
                error_log('Verify resend error: ' . $e->getMessage());
                $error = $copy['internal'];
            }
        } else {
            $code = trim($_POST['code'] ?? '');
            if ($code === '') {
                $error = $copy['code_empty'];
            } else {
                try {
                    $result = check_email_code($pdo, $email, 'register', $code);
                    if ($result === false) {
                        $error = $copy['invalid_code'];
                    } else {
                        $stmt = $pdo->prepare("UPDATE accounts SET is_verified = 1 WHERE email = ?");
                        $stmt->execute([$email]);
                        unset($_SESSION['pending_verify_email']);
                        header('Location: ' . $locale_url('login.php?verified=1'));
                        exit;
                    }
                } catch (Exception $e) {
                    error_log('Verify check error: ' . $e->getMessage());
                    $error = $copy['internal'];
                }
            }
        }
    }
}
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'verify.php';
$seo_index = false;
$active_page = '';
$banner_subtext = $copy['banner_subtext'];
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell auth-page">
    <section class="auth-card card">
        <div class="auth-card-header">
            <p class="eyebrow">PROJECT ORION</p>
            <h1><?php echo htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p><?php echo htmlspecialchars($copy['lead'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="auth-card-body">
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <?php if ($info): ?><div class="alert alert-success"><?php echo htmlspecialchars($info, ENT_QUOTES, 'UTF-8'); ?><?php if ($info_email !== ''): ?> <span data-i18n-ignore translate="no"><?php echo htmlspecialchars($info_email, ENT_QUOTES, 'UTF-8'); ?></span>.<?php endif; ?></div><?php endif; ?>

            <p class="auth-guidance"><?php echo htmlspecialchars($copy['guidance'], ENT_QUOTES, 'UTF-8'); ?></p>

            <form action="<?php echo htmlspecialchars($locale_url('verify.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="action" value="verify">
                <div class="form-group">
                    <label for="email"><?php echo htmlspecialchars($copy['email'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input type="email" name="email" id="email" class="form-control notranslate" translate="no" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="code"><?php echo htmlspecialchars($copy['code'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input type="text" name="code" id="code" class="form-control code-input" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="______" required autocomplete="one-time-code">
                </div>
                <div class="form-actions auth-form-actions">
                    <a href="<?php echo htmlspecialchars($locale_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['already_login'], ENT_QUOTES, 'UTF-8'); ?></a>
                    <button type="submit" class="btn btn-primary auth-submit"><?php echo htmlspecialchars($copy['confirm'], ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
            </form>

            <form action="<?php echo htmlspecialchars($locale_url('verify.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST" class="auth-resend">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="action" value="resend">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="auth-resend-row">
                    <span class="auth-resend-note"><?php echo htmlspecialchars($copy['resend_note'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <button type="submit" class="btn btn-secondary"><?php echo htmlspecialchars($copy['resend'], ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
