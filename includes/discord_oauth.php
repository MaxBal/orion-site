<?php
require_once __DIR__ . '/../discord_config.php';

function discord_http_json($url, $method = 'GET', $fields = null, $authorization = '', $basic_auth = '', $timeout = 15) {
    $headers = [
        'Accept: application/json',
        'User-Agent: Project Orion account linking',
    ];
    if ($authorization !== '') {
        $headers[] = 'Authorization: ' . $authorization;
    }
    if ($basic_auth !== '') {
        $headers[] = 'Authorization: Basic ' . base64_encode($basic_auth);
    }

    $body = $fields === null
        ? null
        : http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
    if ($body !== null) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }

    $timeout = max(3, intval($timeout));
    $result = false;
    if (function_exists('curl_init')) {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(8, $timeout),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => $headers,
        ];
        if (strtoupper((string)$method) === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $body ?? '';
        }
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => strtoupper((string)$method),
                'header' => implode("\r\n", $headers) . "\r\n",
                'content' => $body ?? '',
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
        ]);
        $result = @file_get_contents($url, false, $context);
    }

    if ($result === false) {
        return null;
    }
    $data = json_decode($result, true);
    return is_array($data) ? $data : null;
}

function discord_token_key() {
    return hash('sha256', DISCORD_CLIENT_SECRET . '|Project Orion Discord OAuth', true);
}

function discord_encrypt_token($token) {
    if (!function_exists('openssl_encrypt')) {
        return null;
    }
    $iv = random_bytes(12);
    $tag = '';
    $encrypted = openssl_encrypt(
        (string)$token,
        'aes-256-gcm',
        discord_token_key(),
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    if ($encrypted === false || $tag === '') {
        return null;
    }
    return base64_encode($iv . $tag . $encrypted);
}

function discord_decrypt_token($encoded) {
    if (!function_exists('openssl_decrypt') || trim((string)$encoded) === '') {
        return null;
    }
    $payload = base64_decode((string)$encoded, true);
    if ($payload === false || strlen($payload) <= 28) {
        return null;
    }
    $iv = substr($payload, 0, 12);
    $tag = substr($payload, 12, 16);
    $encrypted = substr($payload, 28);
    $decrypted = openssl_decrypt(
        $encrypted,
        'aes-256-gcm',
        discord_token_key(),
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    return $decrypted === false ? null : (string)$decrypted;
}

function discord_store_oauth_tokens($pdo, $account_id, $discord_id, $token, $fallback_refresh_token = '') {
    $access_token = trim((string)($token['access_token'] ?? ''));
    $refresh_token = trim((string)($token['refresh_token'] ?? $fallback_refresh_token));
    if ($access_token === '' || $refresh_token === '') {
        return false;
    }

    $access_encrypted = discord_encrypt_token($access_token);
    $refresh_encrypted = discord_encrypt_token($refresh_token);
    if ($access_encrypted === null || $refresh_encrypted === null) {
        return false;
    }

    $expires_in = max(60, intval($token['expires_in'] ?? 0));
    $token_expires_at = date('Y-m-d H:i:s', time() + $expires_in);
    $stmt = $pdo->prepare(
        "UPDATE account_discord_links
         SET access_token_encrypted = ?, refresh_token_encrypted = ?, token_expires_at = ?
         WHERE account_id = ? AND discord_id = ?"
    );
    $stmt->execute([$access_encrypted, $refresh_encrypted, $token_expires_at, intval($account_id), (string)$discord_id]);
    return $stmt->rowCount() >= 0;
}

function discord_clear_oauth_tokens($pdo, $account_id, $discord_id) {
    $stmt = $pdo->prepare(
        "UPDATE account_discord_links
         SET access_token_encrypted = NULL, refresh_token_encrypted = NULL, token_expires_at = NULL
         WHERE account_id = ? AND discord_id = ?"
    );
    $stmt->execute([intval($account_id), (string)$discord_id]);
}

function discord_refresh_access_token($refresh_token) {
    return discord_http_json(
        DISCORD_API_BASE . '/oauth2/token',
        'POST',
        [
            'grant_type' => 'refresh_token',
            'refresh_token' => (string)$refresh_token,
        ],
        '',
        DISCORD_CLIENT_ID . ':' . DISCORD_CLIENT_SECRET,
        10
    );
}

function discord_sync_account_link($pdo, $account_id, $link) {
    if (!is_array($link) || trim((string)($link['refresh_token_encrypted'] ?? '')) === '') {
        return $link;
    }

    try {
        $checked_at = strtotime((string)($link['username_checked_at'] ?? ''));
        if ($checked_at !== false && $checked_at > time() - 60) {
            return $link;
        }

        $refresh_token = discord_decrypt_token($link['refresh_token_encrypted']);
        if ($refresh_token === null) {
            return $link;
        }
        $access_token = discord_decrypt_token($link['access_token_encrypted'] ?? '');
        $expires_at = strtotime((string)($link['token_expires_at'] ?? ''));
        if ($access_token === null || $expires_at === false || $expires_at <= time() + 60) {
            $token = discord_refresh_access_token($refresh_token);
            $access_token = trim((string)($token['access_token'] ?? ''));
            if ($access_token === '') {
                discord_clear_oauth_tokens($pdo, $account_id, $link['discord_id']);
                return $link;
            }
            discord_store_oauth_tokens($pdo, $account_id, $link['discord_id'], $token, $refresh_token);
        }

        $discord_user = discord_http_json(
            DISCORD_API_BASE . '/users/@me',
            'GET',
            null,
            'Bearer ' . $access_token,
            '',
            5
        );
        $discord_id = trim((string)($discord_user['id'] ?? ''));
        $discord_username = trim((string)($discord_user['username'] ?? ''));
        if ($discord_id === '' || $discord_id !== (string)$link['discord_id'] || $discord_username === '') {
            return $link;
        }
        $discord_username = function_exists('mb_substr')
            ? mb_substr($discord_username, 0, 100, 'UTF-8')
            : substr($discord_username, 0, 100);

        $stmt = $pdo->prepare(
            "UPDATE account_discord_links
             SET discord_username = ?, username_checked_at = NOW()
             WHERE account_id = ? AND discord_id = ?"
        );
        $stmt->execute([$discord_username, intval($account_id), (string)$link['discord_id']]);
        $link['discord_username'] = $discord_username;
        $link['username_checked_at'] = date('Y-m-d H:i:s');
        return $link;
    } catch (Throwable $e) {
        error_log('Discord username sync error: ' . $e->getMessage());
        return $link;
    }
}
