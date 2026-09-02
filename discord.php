<?php
require_once 'db.php';
require_once __DIR__ . '/discord_config.php';
require_once __DIR__ . '/includes/discord_oauth.php';

$requested_action = (string)($_GET['action'] ?? '');
$is_auth_action = in_array($requested_action, ['login', 'register'], true);
if (empty($_SESSION['user_id']) && !$is_auth_action) {
    header('Location: login.php');
    exit;
}
if (!empty($_SESSION['user_id']) && $is_auth_action) {
    header('Location: profile.php');
    exit;
}

function discord_redirect_to($path, $params = []) {
    $location = (string)$path;
    if (!empty($params)) {
        $location .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
    header('Location: ' . $location, true, 303);
    exit;
}

function discord_redirect_for_mode($mode, $status) {
    $path = $mode === 'connect'
        ? 'profile.php'
        : ($mode === 'register' ? 'register.php' : 'login.php');
    discord_redirect_to($path, ['discord' => (string)$status]);
}

function discord_authenticate_account($pdo, $account_id) {
    $stmt = $pdo->prepare("SELECT id, username, email, is_admin, staff_role, is_verified FROM accounts WHERE id = ? LIMIT 1");
    $stmt->execute([intval($account_id)]);
    $account = $stmt->fetch();
    if (!$account) {
        return ['status' => 'error'];
    }
    if (find_active_ban($pdo, intval($account['id']), get_client_ip()) !== null) {
        return ['status' => 'banned'];
    }
    if (defined('EMAIL_VERIFICATION_ENABLED') && EMAIL_VERIFICATION_ENABLED && intval($account['is_verified'] ?? 0) !== 1) {
        return [
            'status' => 'unverified',
            'email' => (string)($account['email'] ?? ''),
        ];
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = intval($account['id']);
    $_SESSION['username'] = (string)$account['username'];
    $_SESSION['is_admin'] = intval($account['is_admin'] ?? 0) === 1;
    discord_clear_pending_registration();
    refresh_session_staff_access($pdo);
    orion_issue_remember_token($pdo, intval($account['id']));

    $now = date('Y-m-d H:i:s');
    $pdo->prepare("UPDATE accounts SET last_login = ?, reg_ip = ? WHERE id = ?")
        ->execute([$now, get_client_ip(), intval($account['id'])]);
    return ['status' => 'ok'];
}

function discord_redirect_login_result($result) {
    $status = (string)($result['status'] ?? 'error');
    if ($status === 'banned') {
        discord_redirect_to('login.php', ['error' => 'banned']);
    }
    if ($status === 'unverified' && !empty($result['email'])) {
        $_SESSION['pending_verify_email'] = (string)$result['email'];
        discord_redirect_to('verify.php', ['email' => (string)$result['email']]);
    }
    discord_redirect_to('login.php', ['discord' => 'error']);
}

if (array_key_exists('code', $_GET) || array_key_exists('error', $_GET) || array_key_exists('state', $_GET)) {
    $oauth_state = $_SESSION['discord_oauth_state'] ?? null;
    unset($_SESSION['discord_oauth_state']);

    $mode = is_array($oauth_state) ? (string)($oauth_state['mode'] ?? '') : '';
    $mode_for_redirect = in_array($mode, ['connect', 'login', 'register'], true) ? $mode : 'login';
    $incoming_state = trim((string)($_GET['state'] ?? ''));
    $state_valid = is_array($oauth_state)
        && in_array($mode, ['connect', 'login', 'register'], true)
        && !empty($oauth_state['value'])
        && hash_equals((string)$oauth_state['value'], $incoming_state)
        && intval($oauth_state['expires_at'] ?? 0) >= time()
        && ($mode !== 'connect'
            ? empty($_SESSION['user_id'])
            : intval($oauth_state['account_id'] ?? 0) === intval($_SESSION['user_id']));
    if (!$state_valid) {
        discord_redirect_for_mode($mode_for_redirect, 'state');
    }

    if ((string)($_GET['error'] ?? '') !== '') {
        discord_redirect_for_mode($mode, (string)$_GET['error'] === 'access_denied' ? 'denied' : 'error');
    }

    $code = trim((string)($_GET['code'] ?? ''));
    if ($code === '' || !DISCORD_OAUTH_ENABLED) {
        discord_redirect_for_mode($mode, 'error');
    }

    $token = discord_http_json(
        DISCORD_API_BASE . '/oauth2/token',
        'POST',
        [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => DISCORD_REDIRECT_URI,
        ],
        '',
        DISCORD_CLIENT_ID . ':' . DISCORD_CLIENT_SECRET
    );
    $access_token = trim((string)($token['access_token'] ?? ''));
    if ($access_token === '') {
        error_log('Discord OAuth token exchange failed.');
        discord_redirect_for_mode($mode, 'error');
    }

    $discord_user = discord_http_json(
        DISCORD_API_BASE . '/users/@me',
        'GET',
        null,
        'Bearer ' . $access_token
    );
    $discord_id = trim((string)($discord_user['id'] ?? ''));
    if (!preg_match('/^\d{1,32}$/D', $discord_id)) {
        error_log('Discord OAuth user lookup failed.');
        discord_redirect_for_mode($mode, 'error');
    }

    // Храним именно username Discord, а не отображаемое имя global_name.
    $discord_username = trim((string)($discord_user['username'] ?? ''));
    if ($discord_username === '') {
        $discord_username = 'Discord';
    }
    $discord_username = function_exists('mb_substr')
        ? mb_substr($discord_username, 0, 100, 'UTF-8')
        : substr($discord_username, 0, 100);

    if ($mode !== 'connect') {
        try {
            $stmt = $pdo->prepare("SELECT account_id FROM account_discord_links WHERE discord_id = ? LIMIT 1");
            $stmt->execute([$discord_id]);
            $linked_account_id = $stmt->fetchColumn();
            if ($linked_account_id !== false) {
                $pdo->prepare("UPDATE account_discord_links SET discord_username = ?, linked_at = NOW() WHERE account_id = ?")
                    ->execute([$discord_username, intval($linked_account_id)]);
                if (!discord_store_oauth_tokens($pdo, intval($linked_account_id), $discord_id, $token)) {
                    throw new RuntimeException('Discord token storage failed');
                }
                $login_result = discord_authenticate_account($pdo, intval($linked_account_id));
                if (($login_result['status'] ?? '') === 'ok') {
                    discord_redirect_to('profile.php');
                }
                discord_redirect_login_result($login_result);
            }

            discord_store_pending_registration($discord_id, $discord_username, $token);
            discord_redirect_to('register.php', ['discord' => '1']);
        } catch (Throwable $e) {
            error_log('Discord OAuth login error: ' . $e->getMessage());
            discord_redirect_for_mode($mode, 'error');
        }
    }

    $account_id = intval($_SESSION['user_id']);
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT account_id FROM account_discord_links WHERE discord_id = ? LIMIT 1 FOR UPDATE");
        $stmt->execute([$discord_id]);
        $discord_owner = $stmt->fetchColumn();
        if ($discord_owner !== false && intval($discord_owner) !== $account_id) {
            $pdo->rollBack();
            discord_redirect_for_mode('connect', 'already');
        }

        $stmt = $pdo->prepare("SELECT discord_id FROM account_discord_links WHERE account_id = ? LIMIT 1 FOR UPDATE");
        $stmt->execute([$account_id]);
        $current_discord_id = $stmt->fetchColumn();
        if ($current_discord_id !== false && (string)$current_discord_id !== $discord_id) {
            $pdo->rollBack();
            discord_redirect_for_mode('connect', 'existing');
        }

        if ($current_discord_id !== false) {
            $stmt = $pdo->prepare("UPDATE account_discord_links SET discord_username = ?, linked_at = NOW() WHERE account_id = ?");
            $stmt->execute([$discord_username, $account_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO account_discord_links (account_id, discord_id, discord_username, linked_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$account_id, $discord_id, $discord_username]);
        }
        if (!discord_store_oauth_tokens($pdo, $account_id, $discord_id, $token)) {
            throw new RuntimeException('Discord token storage failed');
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Discord account link error: ' . $e->getMessage());
        discord_redirect_for_mode('connect', 'error');
    }

    discord_redirect_for_mode('connect', 'linked');
}

$mode = in_array($requested_action, ['connect', 'login', 'register'], true)
    ? $requested_action
    : (!empty($_SESSION['user_id']) ? 'connect' : 'login');
if (!DISCORD_OAUTH_ENABLED) {
    discord_redirect_for_mode($mode, 'config');
}

$state = bin2hex(random_bytes(32));
$silent_reauth = $mode === 'connect' && (string)($_GET['silent'] ?? '') === '1';
$_SESSION['discord_oauth_state'] = [
    'value' => $state,
    'mode' => $mode,
    'account_id' => intval($_SESSION['user_id'] ?? 0),
    'expires_at' => time() + 600,
];

$authorization_url = 'https://discord.com/oauth2/authorize?' . http_build_query([
    'response_type' => 'code',
    'client_id' => DISCORD_CLIENT_ID,
    'scope' => 'identify',
    'state' => $state,
    'redirect_uri' => DISCORD_REDIRECT_URI,
], '', '&', PHP_QUERY_RFC3986);
if ($silent_reauth) {
    $authorization_url .= '&prompt=none';
}
header('Location: ' . $authorization_url, true, 302);
exit;
