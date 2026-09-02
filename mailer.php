<?php
require_once __DIR__ . '/mail_config.php';

/**
 * Отправляет HTML-письмо. Возвращает true при успехе.
 * Если SMTP_HOST не задан — использует PHP mail(). Иначе — встроенный SMTP.
 */
function send_email($to, $subject, $html) {
    // Лог с кодами подтверждения лежит в корне сайта, то есть раздаётся по HTTP.
    // Поэтому он разрешён только на локальной машине, даже если флаг включили
    // на боевом хосте по ошибке.
    if (MAIL_DEBUG_LOG && (!function_exists('orion_is_local') || orion_is_local())) {
        @file_put_contents(
            __DIR__ . '/_mail.log',
            '[' . date('Y-m-d H:i:s') . "] TO: $to\nSUBJECT: $subject\n" . strip_tags($html) . "\n\n",
            FILE_APPEND
        );
        // В debug-режиме считаем письмо доставленным (код виден в логе).
        return true;
    }
    if (SMTP_HOST === '') {
        return send_email_phpmail($to, $subject, $html);
    }
    try {
        return send_email_smtp($to, $subject, $html);
    } catch (Exception $e) {
        error_log('SMTP send error: ' . $e->getMessage());
        return false;
    }
}

function send_email_phpmail($to, $subject, $html) {
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . mail_encode_name(MAIL_FROM_NAME) . ' <' . MAIL_FROM . '>';
    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    return @mail($to, $encoded_subject, $html, implode("\r\n", $headers));
}

function mail_encode_name($name) {
    return '=?UTF-8?B?' . base64_encode($name) . '?=';
}

function send_email_smtp($to, $subject, $html) {
    $secure = strtolower(SMTP_SECURE);
    $host = SMTP_HOST;
    $transport = ($secure === 'ssl') ? 'ssl://' . $host : $host;

    $fp = @stream_socket_client(
        $transport . ':' . SMTP_PORT,
        $errno, $errstr, 15,
        STREAM_CLIENT_CONNECT
    );
    if (!$fp) {
        throw new RuntimeException("connect failed: $errstr ($errno)");
    }
    stream_set_timeout($fp, 15);

    $read = function() use ($fp) {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            // Многострочный ответ: "250-..." продолжается, "250 ..." конец.
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    $cmd = function($command, $expect) use ($fp, $read) {
        if ($command !== null) {
            fwrite($fp, $command . "\r\n");
        }
        $resp = $read();
        $code = intval(substr($resp, 0, 3));
        if (is_array($expect)) {
            if (!in_array($code, $expect, true)) {
                throw new RuntimeException("unexpected reply: $resp");
            }
        } elseif ($code !== $expect) {
            throw new RuntimeException("unexpected reply: $resp");
        }
        return $resp;
    };

    $ehlo_host = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $cmd(null, 220);                 // приветствие сервера
    $cmd('EHLO ' . $ehlo_host, 250);

    if ($secure === 'tls') {
        $cmd('STARTTLS', 220);
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
            throw new RuntimeException('STARTTLS negotiation failed');
        }
        $cmd('EHLO ' . $ehlo_host, 250);
    }

    if (SMTP_USER !== '') {
        $cmd('AUTH LOGIN', 334);
        $cmd(base64_encode(SMTP_USER), 334);
        $cmd(base64_encode(SMTP_PASS), 235);
    }

    $cmd('MAIL FROM:<' . MAIL_FROM . '>', 250);
    $cmd('RCPT TO:<' . $to . '>', [250, 251]);
    $cmd('DATA', 354);

    $headers = "From: " . mail_encode_name(MAIL_FROM_NAME) . " <" . MAIL_FROM . ">\r\n";
    $headers .= "To: <" . $to . ">\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: base64\r\n";
    $body = chunk_split(base64_encode($html));
    // Точки в начале строки экранируем по правилу SMTP.
    $message = $headers . "\r\n" . $body;
    $message = preg_replace('/^\./m', '..', $message);
    fwrite($fp, $message . "\r\n.\r\n");
    $cmd(null, 250);

    fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return true;
}

/**
 * Шаблон письма с кодом.
 */
function render_code_email($title, $intro, $code, $locale = null) {
    if ($locale === null && function_exists('current_lang')) {
        $locale = current_lang();
    }
    $is_english = strtolower(trim((string)$locale)) === 'en';
    $code_ttl = intval(MAIL_CODE_TTL_MIN);
    $validity_copy = $is_english
        ? 'This code is valid for ' . $code_ttl . ' minutes. If you did not request this email, simply ignore it.'
        : 'Код действует ' . $code_ttl . ' минут. Если вы не запрашивали это письмо — просто проигнорируйте его.';
    $server_copy = $is_english ? 'Project Orion server 0.8.2' : 'Сервер Project Orion 0.8.2';
    $code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $intro = htmlspecialchars($intro, ENT_QUOTES, 'UTF-8');
    return '<div style="font-family:Arial,sans-serif;background:#151d2b;padding:24px;color:#e8e8e8;">'
        . '<div style="max-width:480px;margin:0 auto;background:#0f1622;border:1px solid #2a2a2c;border-radius:8px;padding:28px;">'
        . '<h2 style="color:#b8863f;margin:0 0 12px;">' . $title . '</h2>'
        . '<p style="margin:0 0 18px;line-height:1.5;">' . $intro . '</p>'
        . '<div style="font-size:34px;letter-spacing:8px;font-weight:bold;color:#fff;background:#000;border:1px solid #b8863f;border-radius:6px;text-align:center;padding:16px;">' . $code . '</div>'
        . '<p style="margin:18px 0 0;color:#8c8c8c;font-size:13px;">' . $validity_copy . '</p>'
        . '<p style="margin:16px 0 0;color:#8c8c8c;font-size:12px;">' . $server_copy . '</p>'
        . '</div></div>';
}
