<?php
/**
 * Настройки OAuth2-привязки Discord.
 *
 * Зарегистрируй приложение на https://discord.com/developers/applications.
 * Redirect URI должен в точности совпадать с адресом, зарегистрированным
 * в настройках приложения Discord.
 */

define('DISCORD_CLIENT_ID', getenv('DISCORD_CLIENT_ID') ?: '');
define('DISCORD_CLIENT_SECRET', getenv('DISCORD_CLIENT_SECRET') ?: '');
define('DISCORD_REDIRECT_URI', 'https://projectorion.fun/discord.php');
define('DISCORD_API_BASE', 'https://discord.com/api/v10');
define('DISCORD_OAUTH_ENABLED', DISCORD_CLIENT_ID !== '' && DISCORD_CLIENT_SECRET !== '' && DISCORD_REDIRECT_URI !== '');

function discord_store_pending_registration($discord_id, $discord_username, $token = []) {
    $_SESSION['discord_registration'] = [
        'discord_id' => trim((string)$discord_id),
        'discord_username' => trim((string)$discord_username),
        'token' => [
            'access_token' => trim((string)($token['access_token'] ?? '')),
            'refresh_token' => trim((string)($token['refresh_token'] ?? '')),
            'expires_in' => max(60, intval($token['expires_in'] ?? 0)),
        ],
        'expires_at' => time() + 900,
    ];
}

function discord_pending_registration() {
    $pending = $_SESSION['discord_registration'] ?? null;
    $discord_id = is_array($pending) ? trim((string)($pending['discord_id'] ?? '')) : '';
    $discord_username = is_array($pending) ? trim((string)($pending['discord_username'] ?? '')) : '';
    $expires_at = is_array($pending) ? intval($pending['expires_at'] ?? 0) : 0;
    $token = is_array($pending) && is_array($pending['token'] ?? null) ? $pending['token'] : [];
    if ($discord_id === '' || !preg_match('/^\d{1,32}$/D', $discord_id) || $discord_username === '' || $expires_at < time()) {
        unset($_SESSION['discord_registration']);
        return null;
    }
    return [
        'discord_id' => $discord_id,
        'discord_username' => $discord_username,
        'token' => [
            'access_token' => trim((string)($token['access_token'] ?? '')),
            'refresh_token' => trim((string)($token['refresh_token'] ?? '')),
            'expires_in' => max(60, intval($token['expires_in'] ?? 0)),
        ],
    ];
}

function discord_clear_pending_registration() {
    unset($_SESSION['discord_registration']);
}
