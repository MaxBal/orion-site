<?php

function orion_server_status_locale(): string
{
    $lang = function_exists('current_lang') ? current_lang() : 'ru';
    return in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'ru';
}

function orion_server_status_error($key): string
{
    $messages = [
        'invalid_status' => [
            'ru' => 'Выберите статус сервера: онлайн или офлайн.',
            'uk' => 'Оберіть статус сервера: онлайн або офлайн.',
            'en' => 'Choose a server status: online or offline.',
        ],
        'message_too_long' => [
            'ru' => 'Комментарий к статусу не должен превышать 255 символов.',
            'uk' => 'Коментар до статусу не має перевищувати 255 символів.',
            'en' => 'The status comment must not exceed 255 characters.',
        ],
    ];
    $lang = orion_server_status_locale();
    return $messages[$key][$lang] ?? $messages[$key]['ru'] ?? (string)$key;
}

function orion_server_state($pdo) {
    // Кешируем статус на 30 секунд в сессии
    $cache_key = 'server_status_cache';
    $cache_ttl = 30;
    
    if (isset($_SESSION[$cache_key]) && ($_SESSION[$cache_key]['time'] ?? 0) > time() - $cache_ttl) {
        return $_SESSION[$cache_key]['data'];
    }
    
    // Реальная проверка сервера по UDP
    $ip = '62.84.175.101';
    $ports = [20016, 20017];
    $is_online = false;
    $players = 0;
    $max_players = 300;
    
    foreach ($ports as $port) {
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock) {
            socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);
            @socket_sendto($sock, "\xff\xff\xff\xff\x54Source Engine Query\x00", 25, 0, $ip, $port);
            $buf = ''; $from = ''; $rport = 0;
            if (@socket_recvfrom($sock, $buf, 4096, 0, $from, $rport)) {
                $is_online = true;
                if (strlen($buf) > 5) {
                    $players = ord($buf[4]);
                    $max_players = ord($buf[5]);
                }
            }
            socket_close($sock);
        }
        if ($is_online) break;
    }
    
    $result = [
        'status' => $is_online ? 'online' : 'offline',
        'is_online' => $is_online,
        'message' => $is_online ? "Players: $players/$max_players" : '',
        'players' => $players,
        'max_players' => $max_players,
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    
    $_SESSION[$cache_key] = ['time' => time(), 'data' => $result];
    return $result;
}

function orion_set_server_state($pdo, $status, $message = '') {
    $status = strtolower(trim((string)$status));
    if (!in_array($status, ['online', 'offline'], true)) {
        throw new RuntimeException(orion_server_status_error('invalid_status'));
    }
    $message = trim((string)$message);
    $message_length = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);
    if ($message_length > 255) {
        throw new RuntimeException(orion_server_status_error('message_too_long'));
    }
    $pdo->beginTransaction();
    try {
        set_setting($pdo, 'server_status', $status);
        set_setting($pdo, 'server_status_message', $message);
        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
