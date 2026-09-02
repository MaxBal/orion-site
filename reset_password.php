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
    'request_info' => 'If an account with this email exists, we sent a password reset code.',
    'internal' => 'An internal error occurred. Please try again later.',
    'code_empty' => 'Enter the code from the email.',
    'password_short' => 'The password must be at least 6 characters long.',
    'password_long' => 'The password is too long (maximum 128 characters).',
    'password_match' => 'The passwords do not match.',
    'invalid_code' => 'The code is invalid or has expired. Request a new one.',
    'page_title' => 'Password recovery: Project Orion server 0.8.2',
    'page_description' => 'Recover the password for your Project Orion game server 0.8.2 account.',
    'banner_subtext' => 'Password recovery · 0.8.2',
    'title' => 'Password recovery',
    'lead' => 'Get a code and set a new password',
    'request_guidance' => 'Enter the email address used during registration. We will send a password reset code.',
    'reset_guidance' => 'Enter the code from the email and a new password.',
    'email' => 'Email',
    'code' => 'Code from the email',
    'new_password' => 'New password',
    'password_placeholder' => 'Enter new password…',
    'confirm_password' => 'Repeat password',
    'confirm_placeholder' => 'Repeat new password…',
    'remembered_login' => 'Remembered your password? Sign in',
    'send_code' => 'Send code',
    'request_again' => 'Request a new code',
    'change_password' => 'Change password',
    'email_subject' => 'Password recovery: Project Orion',
    'email_title' => 'Password recovery',
    'email_body_prefix' => 'Enter this code on the website to set a new password for the account “',
    'email_body_suffix' => '”.',
] : [
    'session' => 'Сессия устарела. Обновите страницу.',
    'invalid_email' => 'Укажите корректный email.',
    'request_info' => 'Если аккаунт с таким email есть, мы отправили код для сброса пароля.',
    'internal' => 'Произошла внутренняя ошибка. Попробуйте позже.',
    'code_empty' => 'Введите код из письма.',
    'password_short' => 'Пароль должен быть не менее 6 символов.',
    'password_long' => 'Пароль слишком длинный (максимум 128 символов).',
    'password_match' => 'Пароли не совпадают.',
    'invalid_code' => 'Неверный или просроченный код. Запросите новый.',
    'page_title' => 'Восстановление пароля — сервер Project Orion 0.8.2',
    'page_description' => 'Восстановление пароля аккаунта сервера Project Orion 0.8.2.',
    'banner_subtext' => 'Восстановление пароля · 0.8.2',
    'title' => 'Восстановление пароля',
    'lead' => 'Получите код и задайте новый пароль',
    'request_guidance' => 'Введите email, указанный при регистрации. Мы отправим код для сброса пароля.',
    'reset_guidance' => 'Введите код из письма и новый пароль.',
    'email' => 'Email',
    'code' => 'Код из письма',
    'new_password' => 'Новый пароль',
    'password_placeholder' => 'Введите новый пароль…',
    'confirm_password' => 'Повтор пароля',
    'confirm_placeholder' => 'Повторите новый пароль…',
    'remembered_login' => 'Вспомнили пароль? Войти',
    'send_code' => 'Отправить код',
    'request_again' => 'Запросить код заново',
    'change_password' => 'Сменить пароль',
    'email_subject' => 'Восстановление пароля — Project Orion',
    'email_title' => 'Восстановление пароля',
    'email_body_prefix' => 'Введите этот код на сайте, чтобы задать новый пароль для аккаунта «',
    'email_body_suffix' => '».',
];

if (isset($_SESSION['user_id'])) {
    header('Location: ' . $locale_url('profile.php'));
    exit;
}

$error = '';
$success = '';
$info = '';
$stage = 'request'; // request → ввод email; reset → ввод кода и нового пароля
$email = trim($_GET['email'] ?? '');
if ($email !== '') {
    $stage = 'reset';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = $copy['session'];
    } else {
        $action = $_POST['action'] ?? 'request';
        $email = trim($_POST['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $copy['invalid_email'];
        } elseif ($action === 'request') {
            $stage = 'reset';
            try {
                $stmt = $pdo->prepare("SELECT id, username FROM accounts WHERE email = ?");
                $stmt->execute([$email]);
                $acc = $stmt->fetch();
                if ($acc) {
                    if (can_request_email_code($pdo, $email, 'reset')) {
                        $code = create_email_code($pdo, $acc['id'], $email, 'reset');
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
                    }
                }
                // Не раскрываем, существует ли аккаунт.
                $info = $copy['request_info'];
            } catch (Exception $e) {
                error_log('Reset request error: ' . $e->getMessage());
                $error = $copy['internal'];
            }
        } elseif ($action === 'reset') {
            $stage = 'reset';
            $code = trim($_POST['code'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';
            if ($code === '') {
                $error = $copy['code_empty'];
            } elseif (strlen($password) < 6) {
                $error = $copy['password_short'];
            } elseif (strlen($password) > 128) {
                $error = $copy['password_long'];
            } elseif ($password !== $password_confirm) {
                $error = $copy['password_match'];
            } else {
                try {
                    $result = check_email_code($pdo, $email, 'reset', $code);
                    if ($result === false) {
                        $error = $copy['invalid_code'];
                    } else {
                        // Сброс пароля подтверждает владение почтой → ставим is_verified.
                        $hash = hash('sha256', $password);
                        $stmt = $pdo->prepare("UPDATE accounts SET password_hash = ?, is_verified = 1 WHERE email = ?");
                        $stmt->execute([$hash, $email]);
                        $reset_account_id = intval($result['account_id'] ?? 0);
                        if ($reset_account_id <= 0) {
                            $stmt = $pdo->prepare("SELECT id FROM accounts WHERE email = ? LIMIT 1");
                            $stmt->execute([$email]);
                            $reset_account_id = intval($stmt->fetchColumn());
                        }
                        orion_revoke_account_remember_tokens($pdo, $reset_account_id);
                        header('Location: ' . $locale_url('login.php?reset=1'));
                        exit;
                    }
                } catch (Exception $e) {
                    error_log('Reset apply error: ' . $e->getMessage());
                    $error = $copy['internal'];
                }
            }
        }
    }
}
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'reset_password.php';
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
            <?php if ($info): ?><div class="alert alert-success"><?php echo htmlspecialchars($info); ?></div><?php endif; ?>

            <?php if ($stage === 'request'): ?>
                <p class="auth-guidance"><?php echo htmlspecialchars($copy['request_guidance'], ENT_QUOTES, 'UTF-8'); ?></p>
                <form action="<?php echo htmlspecialchars($locale_url('reset_password.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <input type="hidden" name="action" value="request">
                    <div class="form-group">
                        <label for="email"><?php echo htmlspecialchars($copy['email'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="email" name="email" id="email" class="form-control notranslate" translate="no" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-actions auth-form-actions">
                        <a href="<?php echo htmlspecialchars($locale_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['remembered_login'], ENT_QUOTES, 'UTF-8'); ?></a>
                        <button type="submit" class="btn btn-primary auth-submit"><?php echo htmlspecialchars($copy['send_code'], ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                </form>
            <?php else: ?>
                <p class="auth-guidance"><?php echo htmlspecialchars($copy['reset_guidance'], ENT_QUOTES, 'UTF-8'); ?></p>
                <form action="<?php echo htmlspecialchars($locale_url('reset_password.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <input type="hidden" name="action" value="reset">
                    <div class="form-group">
                        <label for="email"><?php echo htmlspecialchars($copy['email'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="email" name="email" id="email" class="form-control notranslate" translate="no" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="code"><?php echo htmlspecialchars($copy['code'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="text" name="code" id="code" class="form-control code-input" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="______" required autocomplete="one-time-code">
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo htmlspecialchars($copy['new_password'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="<?php echo htmlspecialchars($copy['password_placeholder'], ENT_QUOTES, 'UTF-8'); ?>" minlength="6" required autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm"><?php echo htmlspecialchars($copy['confirm_password'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="<?php echo htmlspecialchars($copy['confirm_placeholder'], ENT_QUOTES, 'UTF-8'); ?>" minlength="6" required autocomplete="new-password">
                    </div>
                    <div class="form-actions auth-form-actions">
                        <a href="<?php echo htmlspecialchars($locale_url('reset_password.php'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['request_again'], ENT_QUOTES, 'UTF-8'); ?></a>
                        <button type="submit" class="btn btn-primary auth-submit"><?php echo htmlspecialchars($copy['change_password'], ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
