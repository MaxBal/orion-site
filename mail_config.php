<?php
/**
 * Настройки отправки почты (коды подтверждения email и сброса пароля).
 *
 * Если SMTP_HOST пустой — письма отправляются через стандартную функцию PHP
 * mail() (подходит, если на сервере настроен sendmail/postfix).
 *
 * Для надёжной доставки (Gmail, Mail.ru, свой SMTP) заполни SMTP_*:
 *   SMTP_SECURE = 'tls'  → порт обычно 587 (STARTTLS)
 *   SMTP_SECURE = 'ssl'  → порт обычно 465
 *   SMTP_SECURE = ''     → без шифрования (порт 25, не рекомендуется)
 *
 * Лучше задавать значения через переменные окружения, а не в коде.
 */

// Включает/выключает подтверждение email при регистрации и блокировку входа
// неподтверждённых аккаунтов (на сайте и в игре). Пока почта не настроена —
// держим false: регистрация сразу активна. Поставь true, когда заработает SMTP.
define('EMAIL_VERIFICATION_ENABLED', getenv('EMAIL_VERIFICATION_ENABLED') !== '0');

// MAIL_FROM — адрес, ОТ которого уходят письма. В Brevo он должен быть
// подтверждён в разделе Senders & IP → Senders (туда придёт письмо со ссылкой
// подтверждения). Можно использовать любой свой email (Gmail, Proton, etc.) —
// домен иметь НЕ обязательно, достаточно подтвердить одну адресу-отправителя.
define('MAIL_FROM',      getenv('MAIL_FROM')      ?: 'noreply@projectorion.fun');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Project Orion');

// ── Brevo (бывш. Sendinblue) — бесплатно 300 писем/день ──────────────────────
//   Регистрация: https://www.brevo.com  (карта не нужна)
//   Где взять данные: панель Brevo → SMTP & API → SMTP.
//     SMTP_USER = «Login» оттуда (это email вида xxxxx@smtp-brevo.com,
//                 НЕ адрес, которым ты регистрировался).
//     SMTP_PASS = «SMTP key» / «Master Password» (нажми «Generate a new SMTP key»).
//   Сервер и порт ниже менять не нужно.
//
//   Альтернатива (если не зайдёт Brevo) — Mailjet (200/день):
//     SMTP_HOST=in-v3.mailjet.com, PORT=587, SECURE=tls,
//     SMTP_USER = API Key, SMTP_PASS = Secret Key.
define('SMTP_HOST',   getenv('SMTP_HOST')   ?: '');
define('SMTP_PORT',   intval(getenv('SMTP_PORT') ?: 587));
define('SMTP_USER',   getenv('SMTP_USER')   ?: '');  // ← Login из панели Brevo
define('SMTP_PASS',   getenv('SMTP_PASS')   ?: '');          // ← SMTP key из панели Brevo
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');        // tls | ssl | ''

// Сколько минут действует код и через сколько секунд можно запросить новый.
define('MAIL_CODE_TTL_MIN', 15);
define('MAIL_CODE_RESEND_SEC', 60);
// Для локальной разработки без почты: если true — код пишется в site/_mail.log
// и его можно прочитать вместо реальной отправки.
define('MAIL_DEBUG_LOG', getenv('MAIL_DEBUG_LOG') === '1');
