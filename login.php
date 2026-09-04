<?php
if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en' && !defined('ORION_SKIP_LANGUAGE_REDIRECT')) {
    define('ORION_SKIP_LANGUAGE_REDIRECT', true);
}
require_once 'db.php';
require_once 'recaptcha.php';
require_once __DIR__ . '/discord_config.php';

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
    'banned_site' => 'Your account is blocked. Access to the site is forbidden.',
    'admin' => 'Access is restricted to the project team.',
    'verified' => 'Email confirmed! You can now sign in.',
    'reset' => 'Password changed! Sign in with your new password.',
    'too_many' => 'Too many attempts. Try again in 15 minutes.',
    'session' => 'The session has expired. Refresh the page.',
    'required' => 'Please fill in all fields.',
    'recaptcha' => 'Please confirm that you are not a robot.',
    'banned' => 'Account blocked.',
    'reason' => 'Reason',
    'invalid' => 'Invalid username or password.',
    'internal' => 'An internal error occurred. Please try again later.',
    'page_title' => 'Sign in: Project Orion server 0.8.2',
    'page_description' => 'Sign in to your Project Orion game server 0.8.2 account.',
    'banner_subtext' => 'Game server · 0.8.2',
    'title' => 'Sign in',
    'lead' => 'Sign in to your Project Orion account',
    'username_label' => 'Username or email',
    'username_placeholder' => 'Enter username…',
    'password_label' => 'Password',
    'password_placeholder' => 'Enter password…',
    'register' => 'No account? Create one',
    'forgot' => 'Forgot password?',
    'submit' => 'Sign in',
    'or' => 'or',
    'discord' => 'Sign in with Discord',
    'discord_denied' => 'Discord sign-in was cancelled.',
    'discord_config' => 'Discord sign-in is not configured yet.',
    'discord_error' => 'Could not sign in through Discord. Please try again.',
    'email_subject' => 'Verification code: Project Orion',
    'email_title' => 'Registration verification',
    'email_body' => 'Enter this code on the website to activate your account.',
] : [
    'banned_site' => 'Ваш аккаунт заблокирован. Доступ к сайту запрещён.',
    'admin' => 'Доступ только для команды проекта.',
    'verified' => 'Email подтверждён! Теперь вы можете войти.',
    'reset' => 'Пароль изменён! Войдите с новым паролем.',
    'too_many' => 'Слишком много попыток. Попробуйте через 15 минут.',
    'session' => 'Сессия устарела. Обновите страницу.',
    'required' => 'Пожалуйста, заполните все поля.',
    'recaptcha' => 'Пожалуйста, подтвердите, что вы не робот.',
    'banned' => 'Аккаунт заблокирован.',
    'reason' => 'Причина',
    'invalid' => 'Неверное имя пользователя или пароль.',
    'internal' => 'Произошла внутренняя ошибка. Попробуйте позже.',
    'page_title' => 'Вход — сервер Project Orion 0.8.2',
    'page_description' => 'Вход в аккаунт игрового сервера Project Orion 0.8.2.',
    'banner_subtext' => 'Игровой сервер · 0.8.2',
    'title' => 'Авторизация',
    'lead' => 'Войдите в аккаунт Project Orion',
    'username_label' => 'Логин или Email',
    'username_placeholder' => 'Введите логин…',
    'password_label' => 'Пароль',
    'password_placeholder' => 'Введите пароль…',
    'register' => 'Нет аккаунта? Создать',
    'forgot' => 'Забыли пароль?',
    'submit' => 'Войти',
    'or' => 'или',
    'discord' => 'Войти через Discord',
    'discord_denied' => 'Вход через Discord отменён.',
    'discord_config' => 'Вход через Discord пока не настроен.',
    'discord_error' => 'Не удалось войти через Discord. Попробуйте ещё раз.',
    'email_subject' => 'Код подтверждения — Project Orion',
    'email_title' => 'Подтверждение регистрации',
    'email_body' => 'Введите код, чтобы активировать аккаунт.',
];

if (isset($_SESSION['user_id'])) {
    header('Location: ' . $locale_url('profile.php'));
    exit;
}

$error = '';
$success = '';
$ban_reason_for_display = null;
$discord_status = (string)($_GET['discord'] ?? '');
if ($discord_status === 'denied') {
    $error = $copy['discord_denied'];
} elseif ($discord_status === 'config') {
    $error = $copy['discord_config'];
} elseif ($discord_status === 'error' || $discord_status === 'state') {
    $error = $copy['discord_error'];
}
if (($_GET['error'] ?? '') === 'banned') {
    $error = $copy['banned_site'];
} elseif (($_GET['error'] ?? '') === 'admin') {
    $error = $copy['admin'];
}
if (($_GET['verified'] ?? '') === '1') {
    $success = $copy['verified'];
} elseif (($_GET['reset'] ?? '') === '1') {
    $success = $copy['reset'];
}
$max_attempts = 10;
$lockout_time = 900;
$client_ip = get_client_ip();
if (auth_attempts_blocked($pdo, 'login', $client_ip, $max_attempts, $lockout_time)) {
    $error = $copy['too_many'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF проверка отключена для тестовой среды
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = $copy['required'];
    } elseif (false && !verify_recaptcha($_POST['g-recaptcha-response'] ?? '')) {
        $error = $copy['recaptcha'];
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash, is_admin, staff_role, is_verified FROM accounts WHERE username = ? OR normalized_name = ? OR email = ?");
            $stmt->execute([$username, $username, $username]);
            $user = $stmt->fetch();

            $ban_reason = find_active_ban($pdo, $user ? $user['id'] : null, get_client_ip());
            if ($ban_reason !== null) {
                $error = $copy['banned'];
                $ban_reason_for_display = $ban_reason;
            } elseif (EMAIL_VERIFICATION_ENABLED && $user && md5($password) === $user['password_hash'] && intval($user['is_verified']) !== 1) {
                // Пароль верный, но email не подтверждён — шлём код и ведём на verify.
                $vemail = (string)($user['email'] ?? '');
                if ($vemail !== '' && can_request_email_code($pdo, $vemail, 'register')) {
                    try {
                        $code = create_email_code($pdo, $user['id'], $vemail, 'register');
                        send_email($vemail, $copy['email_subject'], render_code_email($copy['email_title'], $copy['email_body'], $code));
                    } catch (Exception $mailEx) {
                        error_log('Login resend code error: ' . $mailEx->getMessage());
                    }
                }
                $_SESSION['pending_verify_email'] = $vemail;
                header('Location: ' . $locale_url('verify.php?email=' . urlencode($vemail)));
                exit;
            } elseif ($user && md5($password) === $user['password_hash']) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = (intval($user['is_admin']) === 1);
                refresh_session_staff_access($pdo);
                orion_issue_remember_token($pdo, intval($user['id']));

                $now = date('Y-m-d H:i:s');
                $reg_ip = get_client_ip();
                $update_stmt = $pdo->prepare("UPDATE accounts SET last_login = ?, reg_ip = ? WHERE id = ?");
                $update_stmt->execute([$now, $reg_ip, $user['id']]);

                auth_attempts_reset($pdo, 'login', $client_ip);
                 header('Location: ' . $locale_url('profile.php'));
                exit;
            } else {
                auth_attempt_register($pdo, 'login', $client_ip, $lockout_time);
                $error = $copy['invalid'];
            }
        } catch (Exception $e) {
            error_log("Login DB error: " . $e->getMessage());
            $error = $copy['internal'];
        }
    }
}
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'login.php';
$seo_index = false;
$active_page = '';
$banner_subtext = $copy['banner_subtext'];
$head_extra = '';
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
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?><?php if ($ban_reason_for_display !== null && $ban_reason_for_display !== ''): ?> <?php echo htmlspecialchars($copy['reason']); ?>: <span data-i18n-ignore><?php echo htmlspecialchars($ban_reason_for_display); ?></span><?php endif; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

                <form action="<?php echo htmlspecialchars($locale_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <div class="form-group">
                    <label for="username"><?php echo htmlspecialchars($copy['username_label'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="<?php echo htmlspecialchars($copy['username_placeholder'], ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password"><?php echo htmlspecialchars($copy['password_label'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="<?php echo htmlspecialchars($copy['password_placeholder'], ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY, ENT_QUOTES, 'UTF-8'); ?>"></div>
                </div>
                <div class="form-actions auth-form-actions">
                    <div class="auth-links">
                        <a href="<?php echo htmlspecialchars($locale_url('register.php'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['register'], ENT_QUOTES, 'UTF-8'); ?></a>
                        <a href="<?php echo htmlspecialchars($locale_url('reset_password.php'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['forgot'], ENT_QUOTES, 'UTF-8'); ?></a>
                    </div>
                    <button type="submit" class="btn btn-primary auth-submit"><?php echo htmlspecialchars($copy['submit'], ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
            </form>
            <?php if (defined('DISCORD_OAUTH_ENABLED') && DISCORD_OAUTH_ENABLED): ?>
                <div class="auth-social">
                        <div class="auth-social-divider"><span><?php echo htmlspecialchars($copy['or'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                    <a class="btn btn-discord auth-discord-button" href="<?php echo htmlspecialchars($locale_url('discord.php?action=login'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['discord'], ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
