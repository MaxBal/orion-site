<?php
if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en' && !defined('ORION_SKIP_LANGUAGE_REDIRECT')) {
    define('ORION_SKIP_LANGUAGE_REDIRECT', true);
}
require_once 'db.php';
require_once 'recaptcha.php';
require_once __DIR__ . '/discord_config.php';
require_once __DIR__ . '/includes/discord_oauth.php';

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
    'too_many' => 'Too many registration attempts. Try again in 15 minutes.',
    'session' => 'The session has expired. Refresh the page.',
    'required' => 'Please fill in all fields.',
    'nickname_length' => 'The nickname must be 3 to 24 characters long.',
    'email_invalid' => 'Please enter a valid email address.',
    'disposable' => 'Temporary and disposable email addresses are not allowed. Use a permanent email.',
    'password_short' => 'The password must be at least 6 characters long.',
    'password_long' => 'The password is too long (maximum 128 characters).',
    'password_match' => 'The passwords do not match.',
    'recaptcha' => 'Please confirm that you are not a robot.',
    'registration_blocked' => 'Registration from this address is blocked.',
    'already_registered' => 'This nickname or email is already registered.',
    'one_per_ip' => 'Only one account can be registered from each IP address.',
    'success' => 'Registration successful! You can now sign in.',
    'internal' => 'An internal error occurred. Please try again later.',
    'discord_denied' => 'Discord registration was cancelled.',
    'discord_config' => 'Discord registration is not configured yet.',
    'discord_error' => 'Could not continue registration through Discord. Please try again.',
    'discord_state' => 'The Discord registration session has expired. Start again.',
    'discord_info' => 'Discord username to be linked:',
    'page_title' => 'Registration: Project Orion server 0.8.2',
    'page_description' => 'Create an account on the free Project Orion game server 0.8.2 and start playing today.',
    'title' => 'New player registration',
    'lead' => 'Create an account to sign in to the server',
    'login_button' => 'Go to sign in',
    'nickname' => 'Nickname',
    'nickname_placeholder' => 'Enter nickname…',
    'email' => 'Email address',
    'email_placeholder' => 'Enter email…',
    'password' => 'Password',
    'password_placeholder' => 'Enter password…',
    'confirm_password' => 'Confirm password',
    'confirm_placeholder' => 'Repeat password…',
    'not_robot' => 'Confirm that you are not a robot',
    'already_account' => 'Already registered? Sign in',
    'submit' => 'Register',
    'or' => 'or',
    'discord_button' => 'Register through Discord',
    'email_subject' => 'Verification code: Project Orion',
    'email_title' => 'Registration verification',
    'email_body_prefix' => 'Enter this code on the website to activate the account “',
    'email_body_suffix' => '”.',
] : [
    'too_many' => 'Слишком много попыток регистрации. Попробуйте через 15 минут.',
    'session' => 'Сессия устарела. Обновите страницу.',
    'required' => 'Пожалуйста, заполните все поля.',
    'nickname_length' => 'Никнейм должен быть от 3 до 24 символов.',
    'email_invalid' => 'Пожалуйста, введите корректный адрес электронной почты.',
    'disposable' => 'Временные и одноразовые почты не разрешены. Используйте постоянный email.',
    'password_short' => 'Пароль должен быть не менее 6 символов.',
    'password_long' => 'Пароль слишком длинный (максимум 128 символов).',
    'password_match' => 'Пароли не совпадают.',
    'recaptcha' => 'Пожалуйста, подтвердите, что вы не робот.',
    'registration_blocked' => 'Регистрация с этого адреса заблокирована.',
    'already_registered' => 'Этот никнейм или email уже зарегистрирован.',
    'one_per_ip' => 'С одного IP можно зарегистрировать только один аккаунт.',
    'success' => 'Регистрация успешна! Теперь вы можете войти.',
    'internal' => 'Произошла внутренняя ошибка. Попробуйте позже.',
    'discord_denied' => 'Регистрация через Discord отменена.',
    'discord_config' => 'Регистрация через Discord пока не настроена.',
    'discord_error' => 'Не удалось продолжить регистрацию через Discord. Попробуйте ещё раз.',
    'discord_state' => 'Сессия регистрации через Discord устарела. Начните заново.',
    'discord_info' => 'Будет привязан Discord username:',
    'page_title' => 'Регистрация — сервер Project Orion 0.8.2',
    'page_description' => 'Создай аккаунт на бесплатном игровом сервере Project Orion 0.8.2 и начни играть уже сегодня.',
    'title' => 'Регистрация нового игрока',
    'lead' => 'Создайте аккаунт для входа на сервер',
    'login_button' => 'Перейти ко входу',
    'nickname' => 'Никнейм',
    'nickname_placeholder' => 'Введите никнейм…',
    'email' => 'Электронная почта (email)',
    'email_placeholder' => 'Введите email…',
    'password' => 'Пароль',
    'password_placeholder' => 'Введите пароль…',
    'confirm_password' => 'Подтвердите пароль',
    'confirm_placeholder' => 'Повторите пароль…',
    'not_robot' => 'Подтвердите, что вы не робот',
    'already_account' => 'Уже зарегистрированы? Войти',
    'submit' => 'Зарегистрироваться',
    'or' => 'или',
    'discord_button' => 'Зарегистрироваться через Discord',
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
$discord_status = (string)($_GET['discord'] ?? '');
$discord_registration = discord_pending_registration();
$discord_info = '';
if ($discord_status === 'denied') {
    $error = $copy['discord_denied'];
} elseif ($discord_status === 'config') {
    $error = $copy['discord_config'];
} elseif ($discord_status === 'error' || $discord_status === 'state') {
    $error = $copy['discord_error'];
} elseif ($discord_status === '1' && !$discord_registration) {
    $error = $copy['discord_state'];
}
if ($discord_registration) {
    $discord_info = $copy['discord_info'] . ' @' . $discord_registration['discord_username'];
}
$max_attempts = 5;
$lockout_time = 900;
$client_ip = get_client_ip();
if (auth_attempts_blocked($pdo, 'register', $client_ip, $max_attempts, $lockout_time)) {
    $error = $copy['too_many'];
}

if (empty($error) && !function_exists('normalize_login_name')) {
    function normalize_login_name($username) {
        $username = trim($username);
        if (strpos($username, '@') !== false) {
            $parts = explode('@', $username);
            $username = $parts[0];
        }
        $filtered = '';
        for ($i = 0; $i < strlen($username); $i++) {
            $ch = $username[$i];
            if (ctype_alnum($ch) || $ch === '_' || $ch === '-' || $ch === '.') {
                $filtered .= $ch;
            }
        }
        $filtered = substr($filtered, 0, 24);
        return empty($filtered) ? 'player' : $filtered;
    }
}

if ($error === '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF проверка отключена для тестовой среды
    // if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    //     $error = $copy['session'];
    // } else {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = $copy['required'];
    } elseif (strlen($username) < 3 || strlen($username) > 24) {
        $error = $copy['nickname_length'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = $copy['email_invalid'];
    } elseif (is_disposable_email($email)) {
        $error = $copy['disposable'];
    } elseif (strlen($password) < 6) {
        $error = $copy['password_short'];
    } elseif (strlen($password) > 128) {
        $error = $copy['password_long'];
    } elseif ($password !== $password_confirm) {
        $error = $copy['password_match'];
    } elseif (false && !verify_recaptcha($_POST['g-recaptcha-response'] ?? '')) {
        $error = $copy['recaptcha'];
    } else {
        $reg_ip = get_client_ip();
        $normalized = normalize_login_name($username);

        try {
            if (find_active_ban($pdo, null, $reg_ip) !== null) {
                $error = $copy['registration_blocked'];
                throw new RuntimeException('banned_ip');
            }
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM accounts WHERE normalized_name = ? OR username = ? OR email = ?");
            $stmt->execute([$normalized, $username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = $copy['already_registered'];
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM accounts WHERE reg_ip = ?");
                $stmt->execute([$reg_ip]);
                if ($stmt->fetchColumn() > 0) {
                    $error = $copy['one_per_ip'];
                } else {
                    $password_hash = md5($password);
                    $now = date('Y-m-d H:i:s');

                    $pdo->beginTransaction();

                    // Если подтверждение выключено — аккаунт сразу активен.
                    $verified = EMAIL_VERIFICATION_ENABLED ? 0 : 1;
                    $stmt = $pdo->prepare("INSERT INTO accounts (username, email, normalized_name, password_hash, is_verified, reg_ip, created_at, last_login) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $normalized, $password_hash, $verified, $reg_ip, $now, $now]);

                    $new_id = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO dossier (account_id) VALUES (?)");
                    $stmt->execute([$new_id]);

                    if ($discord_registration) {
                        $stmt = $pdo->prepare("INSERT INTO account_discord_links (account_id, discord_id, discord_username, linked_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$new_id, $discord_registration['discord_id'], $discord_registration['discord_username']]);
                    $discord_token = $discord_registration['token'] ?? [];
                    if (!empty($discord_token['access_token']) && !discord_store_oauth_tokens($pdo, $new_id, $discord_registration['discord_id'], $discord_token)) {
                        throw new RuntimeException('Discord token storage failed');
                    }
                }

                $pdo->commit();

                auth_attempts_reset($pdo, 'register', $client_ip);
                discord_clear_pending_registration();

                if (EMAIL_VERIFICATION_ENABLED) {
                    // Отправляем код подтверждения и ведём на страницу ввода кода.
                    try {
                        $code = create_email_code($pdo, $new_id, $email, 'register');
                        send_email(
                            $email,
                            $copy['email_subject'],
                            render_code_email(
                                $copy['email_title'],
                                $copy['email_body_prefix'] . $username . $copy['email_body_suffix'],
                                $code,
                                $ui_lang
                            )
                        );
                    } catch (Exception $mailEx) {
                        error_log('Register send code error: ' . $mailEx->getMessage());
                    }
                    $_SESSION['pending_verify_email'] = $email;
                    header('Location: ' . $locale_url('verify.php?email=' . urlencode($email)));
                    exit;
                }

                $success = $copy['success'];
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Бан по IP — не ошибка БД: сообщение уже выставлено, не затираем.
            if ($e->getMessage() !== 'banned_ip') {
                auth_attempt_register($pdo, 'register', $client_ip, $lockout_time);
                error_log("Register DB error: " . $e->getMessage());
                $error = $copy['internal'];
            }
        }
    }
}
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'register.php';
$active_page = 'register';
$head_extra = '';
$banner_subtext = $ui_lang === 'en' ? 'Game server · 0.8.2' : 'Игровой сервер · 0.8.2';
$discord_username_value = '';
if ($discord_registration) {
    $discord_username_value = function_exists('normalize_login_name')
        ? normalize_login_name($discord_registration['discord_username'])
        : $discord_registration['discord_username'];
}
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
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($discord_info)): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($copy['discord_info'], ENT_QUOTES, 'UTF-8'); ?> <span data-i18n-ignore translate="no">@<?php echo htmlspecialchars($discord_registration['discord_username'], ENT_QUOTES, 'UTF-8'); ?></span></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <div class="auth-success-actions">
                    <a href="<?php echo htmlspecialchars($locale_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary"><?php echo htmlspecialchars($copy['login_button'], ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
            <?php else: ?>
                <form action="<?php echo htmlspecialchars($locale_url('register.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <div class="form-group">
                        <label for="username"><?php echo htmlspecialchars($copy['nickname'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="text" name="username" id="username" class="form-control notranslate" translate="no" placeholder="<?php echo htmlspecialchars($copy['nickname_placeholder'], ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($discord_username_value, ENT_QUOTES, 'UTF-8'); ?>" required minlength="3" maxlength="24" autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo htmlspecialchars($copy['email'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="<?php echo htmlspecialchars($copy['email_placeholder'], ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo htmlspecialchars($copy['password'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="<?php echo htmlspecialchars($copy['password_placeholder'], ENT_QUOTES, 'UTF-8'); ?>" required minlength="6" maxlength="128">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm"><?php echo htmlspecialchars($copy['confirm_password'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="<?php echo htmlspecialchars($copy['confirm_placeholder'], ENT_QUOTES, 'UTF-8'); ?>" required minlength="6" maxlength="128">
                    </div>
                    <div class="form-actions auth-form-actions">
                        <a href="<?php echo htmlspecialchars($locale_url('login.php'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['already_account'], ENT_QUOTES, 'UTF-8'); ?></a>
                        <button type="submit" class="btn btn-primary auth-submit"><?php echo htmlspecialchars($copy['submit'], ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                </form>
                <?php if (defined('DISCORD_OAUTH_ENABLED') && DISCORD_OAUTH_ENABLED && !$discord_registration): ?>
                    <div class="auth-social">
                        <div class="auth-social-divider"><span><?php echo htmlspecialchars($copy['or'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                        <a class="btn btn-discord auth-discord-button" href="<?php echo htmlspecialchars($locale_url('discord.php?action=register'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['discord_button'], ENT_QUOTES, 'UTF-8'); ?></a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
