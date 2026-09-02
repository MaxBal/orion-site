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
    $status = strtolower((string)get_setting($pdo, 'server_status', 'online'));
    if (!in_array($status, ['online', 'offline'], true)) {
        $status = 'online';
    }
    $message = trim((string)get_setting($pdo, 'server_status_message', ''));
    $updated_at = null;
    try {
        $stmt = $pdo->prepare("SELECT MAX(updated_at) FROM site_settings WHERE setting_key IN ('server_status', 'server_status_message')");
        $stmt->execute();
        $updated_at = $stmt->fetchColumn() ?: null;
    } catch (Exception $e) {
        $updated_at = null;
    }
    return [
        'status' => $status,
        'is_online' => $status === 'online',
        'message' => $message,
        'updated_at' => $updated_at,
    ];
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
