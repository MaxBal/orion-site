<?php
// ── Определение окружения ────────────────────────────────────────────────────
// Прод отличается от локальной разработки по хосту. Локально (localhost, 127.*,
// *.local) держим мягкие настройки: HTTP без TLS и видимые ошибки.
function orion_is_https() {
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    // За Cloudflare/nginx исходная схема приходит в заголовке.
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    if (!empty($_SERVER['HTTP_CF_VISITOR']) && str_contains((string)$_SERVER['HTTP_CF_VISITOR'], 'https')) {
        return true;
    }
    return intval($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function orion_is_local() {
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $host = explode(':', $host)[0];
    return $host === 'localhost'
        || $host === '127.0.0.1'
        || $host === '::1'
        || str_ends_with($host, '.local')
        || str_ends_with($host, '.test');
}

// Собственный путь для заголовка Location. Браузер понимает «//host» и «/\host»
// как АБСОЛЮТНЫЙ адрес чужого сайта, поэтому лишние ведущие слэши срезаются, а
// перевод строки (инъекция заголовка) обнуляет путь.
function orion_safe_redirect_path($path) {
    $path = (string)$path;
    if ($path === '' || preg_match('/[\r\n\0]/', $path) === 1) {
        return '/';
    }
    return '/' . ltrim(str_replace('\\', '/', $path), '/');
}

const ORION_REMEMBER_COOKIE = 'orion_remember';
const ORION_REMEMBER_LIFETIME = 2592000; // 30 days
const ORION_REMEMBER_REFRESH_WINDOW = 604800; // rotate during the final 7 days

// Ошибки: на проде пишем в лог и не показываем пользователю (в трейсе видны
// пути, SQL и параметры подключения). Локально — показываем.
if (orion_is_local()) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}
ini_set('log_errors', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    // Флаг ставится только на HTTPS: иначе браузер отбросит куку и локальная
    // разработка по HTTP перестанет держать сессию.
    'secure' => orion_is_https(),
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/lang.php';

// ── Язык сайта (сохраняется в сессии) ────────────────────────────────────────
// Валидный ?lang=ru|uk|en остаётся в URL: это отдельный crawlable locale URL,
// а выбранная локаль дополнительно сохраняется в сессии. Старые ссылки ru/uk
// продолжают работать. Неизвестный lang очищается 303-редиректом.
if (!defined('ORION_SKIP_LANGUAGE_REDIRECT') && array_key_exists('lang', $_GET)) {
    $requested_lang = is_scalar($_GET['lang'])
        ? i18n_locale_code($_GET['lang'], null)
        : null;
    if ($requested_lang === null) {
        $_SESSION['lang'] = 'ru';
        $params = $_GET;
        unset($params['lang']);
        $path = orion_safe_redirect_path(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $location = $path . ($query === '' ? '' : ('?' . $query));
        header('Location: ' . $location, true, 303);
        exit;
    }
    $_SESSION['lang'] = $requested_lang;
}
if (!isset($_SESSION['lang']) || i18n_locale_code($_SESSION['lang'], null) === null) {
    $_SESSION['lang'] = 'ru';
}

require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/includes/staff.php';
require_once __DIR__ . '/includes/contracts.php';
require_once __DIR__ . '/includes/update_history.php';
require_once __DIR__ . '/includes/gso.php';
require_once __DIR__ . '/includes/petitions.php';
require_once __DIR__ . '/includes/site_status.php';

function load_server_database_config() {
    $env_host = trim((string)(getenv('DB_HOST') ?: ''));
    $env_port = trim((string)(getenv('DB_PORT') ?: ''));
    $env_name = trim((string)(getenv('DB_NAME') ?: ''));
    $env_user = trim((string)(getenv('DB_USER') ?: ''));
    $env_pass = getenv('DB_PASS');
    if ($env_host !== '') {
        return [
            'host' => $env_host,
            'port' => $env_port !== '' ? intval($env_port) : 5432,
            'name' => $env_name !== '' ? $env_name : 'wot_emulator',
            'user' => $env_user !== '' ? $env_user : 'postgres',
            'password' => $env_pass !== false ? $env_pass : '',
        ];
    }
    $defaults = [
        'host' => '127.0.0.1',
        'port' => 5432,
        'name' => 'wot_emulator',
        'user' => 'postgres',
        'password' => '',
    ];
    $paths = [];
    $paths[] = __DIR__ . DIRECTORY_SEPARATOR . 'server.json';
    foreach ($paths as $path) {
        if (!is_string($path) || $path === '' || !is_file($path)) {
            continue;
        }
        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data) || !isset($data['database']) || !is_array($data['database'])) {
            continue;
        }
        return array_merge($defaults, $data['database']);
    }
    return $defaults;
}

function db_column_exists($pdo, $table, $column) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ? AND column_name = ?"
    );
    $stmt->execute([$table, $column]);
    return intval($stmt->fetchColumn()) > 0;
}

function ensure_site_schema($pdo) {
    static $ready = false;
    if ($ready) {
        return;
    }

    // Триггер-функция для автообновления updated_at (аналог MySQL ON UPDATE CURRENT_TIMESTAMP)
    $pdo->exec("CREATE OR REPLACE FUNCTION update_updated_at_column()
        RETURNS TRIGGER AS $$
        BEGIN
            NEW.updated_at = NOW();
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql");

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_news (id BIGINT GENERATED BY DEFAULT AS IDENTITY, author_account_id BIGINT NULL, title VARCHAR(180) NOT NULL, summary VARCHAR(512) NOT NULL DEFAULT '', body TEXT NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published')), is_pinned SMALLINT NOT NULL DEFAULT 0, published_at TIMESTAMP NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id))");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_site_news_status ON site_news (status, published_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_site_news_pinned ON site_news (is_pinned, published_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_site_news_author ON site_news (author_account_id)");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trg_site_news_updated_at') THEN CREATE TRIGGER trg_site_news_updated_at BEFORE UPDATE ON site_news FOR EACH ROW EXECUTE FUNCTION update_updated_at_column(); END IF; END $$");

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_news_media (id BIGINT GENERATED BY DEFAULT AS IDENTITY, news_id BIGINT NOT NULL, media_type VARCHAR(10) NOT NULL CHECK (media_type IN ('image', 'video')), file_path VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(120) NOT NULL, size_bytes BIGINT NOT NULL DEFAULT 0, sort_order INTEGER NOT NULL DEFAULT 0, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), CONSTRAINT fk_site_news_media_news FOREIGN KEY (news_id) REFERENCES site_news(id) ON DELETE CASCADE)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_site_news_media_news ON site_news_media (news_id, sort_order)");

    $pdo->exec("CREATE TABLE IF NOT EXISTS disabled_vehicles (vehicle_name VARCHAR(128) NOT NULL, updated_at TIMESTAMP NOT NULL, PRIMARY KEY (vehicle_name))");

    $pdo->exec("CREATE TABLE IF NOT EXISTS account_vehicle_overrides (account_id BIGINT NOT NULL, vehicle_name VARCHAR(128) NOT NULL, is_enabled SMALLINT NOT NULL, updated_at TIMESTAMP NOT NULL, PRIMARY KEY (account_id, vehicle_name))");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_account_vehicle_overrides_vehicle ON account_vehicle_overrides (vehicle_name)");

    $pdo->exec("CREATE TABLE IF NOT EXISTS vehicle_access_events (id BIGINT GENERATED BY DEFAULT AS IDENTITY, scope VARCHAR(16) NOT NULL, account_id BIGINT NULL, vehicle_name VARCHAR(128) NOT NULL, is_enabled SMALLINT NOT NULL, created_at TIMESTAMP NOT NULL, PRIMARY KEY (id))");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_vehicle_access_events_account ON vehicle_access_events (account_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_vehicle_access_events_scope ON vehicle_access_events (scope)");

    if (db_column_exists($pdo, 'accounts', 'id') && !db_column_exists($pdo, 'accounts', 'is_admin')) {
        $pdo->exec("ALTER TABLE accounts ADD COLUMN is_admin SMALLINT NOT NULL DEFAULT 0");
    }
    if (db_column_exists($pdo, 'accounts', 'id') && !db_column_exists($pdo, 'accounts', 'email')) {
        $pdo->exec("ALTER TABLE accounts ADD COLUMN email VARCHAR(255) NULL DEFAULT NULL");
        $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uniq_email') THEN ALTER TABLE accounts ADD CONSTRAINT uniq_email UNIQUE (email); END IF; END $$");
    }
    if (!db_column_exists($pdo, 'disabled_vehicles', 'updated_at')) {
        $pdo->exec("ALTER TABLE disabled_vehicles ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
    if (db_column_exists($pdo, 'accounts', 'id') && !db_column_exists($pdo, 'accounts', 'reg_ip')) {
        $pdo->exec("ALTER TABLE accounts ADD COLUMN reg_ip VARCHAR(45) NULL DEFAULT NULL");
    }
    // last_ip — IP последнего входа В ИГРУ (пишет игровой сервер). Используется
    // для бана аккаунта вместе с его игровым IP.
    if (db_column_exists($pdo, 'accounts', 'id') && !db_column_exists($pdo, 'accounts', 'last_ip')) {
        $pdo->exec("ALTER TABLE accounts ADD COLUMN last_ip VARCHAR(45) NULL DEFAULT NULL");
    }
    // Перманентные баны. Одна строка = одно правило (account/ip/mac).
    // Та же таблица читается игровым сервером (server_core/emulator_impl.py).
    $pdo->exec("CREATE TABLE IF NOT EXISTS bans (id BIGINT GENERATED BY DEFAULT AS IDENTITY, ban_type VARCHAR(10) NOT NULL CHECK (ban_type IN ('account','ip','mac')), account_id BIGINT NULL, ip VARCHAR(45) NULL, mac VARCHAR(48) NULL, reason VARCHAR(255) NOT NULL DEFAULT '', created_by BIGINT NULL, created_at TIMESTAMP NOT NULL, PRIMARY KEY (id))");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uniq_ban_account') THEN ALTER TABLE bans ADD CONSTRAINT uniq_ban_account UNIQUE (account_id); END IF; END $$");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uniq_ban_ip') THEN ALTER TABLE bans ADD CONSTRAINT uniq_ban_ip UNIQUE (ip); END IF; END $$");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uniq_ban_mac') THEN ALTER TABLE bans ADD CONSTRAINT uniq_ban_mac UNIQUE (mac); END IF; END $$");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ban_type ON bans (ban_type)");
    // Подтверждение email. Существующие аккаунты помечаем подтверждёнными, чтобы
    // не заблокировать текущих игроков.
    if (db_column_exists($pdo, 'accounts', 'id') && !db_column_exists($pdo, 'accounts', 'is_verified')) {
        $pdo->exec("ALTER TABLE accounts ADD COLUMN is_verified SMALLINT NOT NULL DEFAULT 0");
        $pdo->exec("UPDATE accounts SET is_verified = 1");
    }
    // Коды подтверждения email и сброса пароля (хранится только sha256 кода).
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_codes (id BIGINT GENERATED BY DEFAULT AS IDENTITY, account_id BIGINT NULL, email VARCHAR(255) NOT NULL, purpose VARCHAR(10) NOT NULL CHECK (purpose IN ('register','reset')), code_hash CHAR(64) NOT NULL, expires_at TIMESTAMP NOT NULL, attempts INTEGER NOT NULL DEFAULT 0, created_at TIMESTAMP NOT NULL, PRIMARY KEY (id))");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email_purpose ON email_codes (email, purpose)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_account ON email_codes (account_id)");

    // Счётчик неудачных попыток входа и регистрации. Лежит в БД, а не в сессии:
    // сессию перебирающий просто не отправляет, и лимит в $_SESSION не работает.
    $pdo->exec("CREATE TABLE IF NOT EXISTS auth_attempts (scope VARCHAR(16) NOT NULL, ip VARCHAR(45) NOT NULL, attempts INTEGER NOT NULL DEFAULT 0, first_attempt_at TIMESTAMP NOT NULL, last_attempt_at TIMESTAMP NOT NULL, PRIMARY KEY (scope, ip))");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_auth_attempts_last ON auth_attempts (last_attempt_at)");
    if (db_column_exists($pdo, 'accounts', 'id') && !db_column_exists($pdo, 'accounts', 'is_banned_reports')) {
        $pdo->exec("ALTER TABLE accounts ADD COLUMN is_banned_reports SMALLINT NOT NULL DEFAULT 0");
    }
    
    // Bug reports tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS bug_reports (id BIGINT GENERATED BY DEFAULT AS IDENTITY, account_id BIGINT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'open' CHECK (status IN ('open', 'in_progress', 'resolved', 'closed')), is_approved SMALLINT NOT NULL DEFAULT 0, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id))");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_bug_reports_account ON bug_reports (account_id)");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trg_bug_reports_updated_at') THEN CREATE TRIGGER trg_bug_reports_updated_at BEFORE UPDATE ON bug_reports FOR EACH ROW EXECUTE FUNCTION update_updated_at_column(); END IF; END $$");

    $pdo->exec("CREATE TABLE IF NOT EXISTS bug_comments (id BIGINT GENERATED BY DEFAULT AS IDENTITY, bug_id BIGINT NOT NULL, account_id BIGINT NOT NULL, comment TEXT NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id))");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_bug_comments_bug ON bug_comments (bug_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_bug_comments_account ON bug_comments (account_id)");

    if (db_column_exists($pdo, 'bug_reports', 'id') && !db_column_exists($pdo, 'bug_reports', 'is_approved')) {
        $pdo->exec("ALTER TABLE bug_reports ADD COLUMN is_approved SMALLINT NOT NULL DEFAULT 0");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(64) NOT NULL, setting_value TEXT NOT NULL, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (setting_key))");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trg_site_settings_updated_at') THEN CREATE TRIGGER trg_site_settings_updated_at BEFORE UPDATE ON site_settings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column(); END IF; END $$");
    ensure_update_history_schema($pdo);
    ensure_gso_schema($pdo);
    ensure_player_petition_schema($pdo);
    $pdo->exec("CREATE TABLE IF NOT EXISTS account_remember_tokens (
        selector CHAR(32) NOT NULL,
        account_id BIGINT NOT NULL,
        token_hash CHAR(64) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (selector)
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_remember_account ON account_remember_tokens (account_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_remember_expires ON account_remember_tokens (expires_at)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS account_discord_links (
        account_id BIGINT NOT NULL,
        discord_id VARCHAR(32) NOT NULL,
        discord_username VARCHAR(100) NOT NULL DEFAULT '',
        access_token_encrypted TEXT NULL,
        refresh_token_encrypted TEXT NULL,
        token_expires_at TIMESTAMP NULL,
        username_checked_at TIMESTAMP NULL,
        linked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (account_id)
    )");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uniq_account_discord_id') THEN ALTER TABLE account_discord_links ADD CONSTRAINT uniq_account_discord_id UNIQUE (discord_id); END IF; END $$");
    if (db_column_exists($pdo, 'account_discord_links', 'account_id') && !db_column_exists($pdo, 'account_discord_links', 'access_token_encrypted')) {
        $pdo->exec("ALTER TABLE account_discord_links ADD COLUMN access_token_encrypted TEXT NULL");
    }
    if (db_column_exists($pdo, 'account_discord_links', 'account_id') && !db_column_exists($pdo, 'account_discord_links', 'refresh_token_encrypted')) {
        $pdo->exec("ALTER TABLE account_discord_links ADD COLUMN refresh_token_encrypted TEXT NULL");
    }
    if (db_column_exists($pdo, 'account_discord_links', 'account_id') && !db_column_exists($pdo, 'account_discord_links', 'token_expires_at')) {
        $pdo->exec("ALTER TABLE account_discord_links ADD COLUMN token_expires_at TIMESTAMP NULL");
    }
    if (db_column_exists($pdo, 'account_discord_links', 'account_id') && !db_column_exists($pdo, 'account_discord_links', 'username_checked_at')) {
        $pdo->exec("ALTER TABLE account_discord_links ADD COLUMN username_checked_at TIMESTAMP NULL");
    }

    ensure_staff_schema($pdo);
    ensure_contract_schema($pdo);

    $ready = true;
}

function get_setting($pdo, $key, $default = null) {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function set_setting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value, updated_at = NOW()");
    $stmt->execute([$key, $value]);
}

function get_setting_json($pdo, $key, $default = []) {
    $val = get_setting($pdo, $key);
    if ($val === null || $val === '') {
        return $default;
    }
    $decoded = json_decode($val, true);
    return is_array($decoded) ? $decoded : $default;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf($token) {
    return !empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}

// Показывать ли всплывающее окно в этом запросе. Возвращает true один раз в
// начале каждой новой сессии, а затем не чаще, чем раз в 30 минут. Вызов имеет
// побочный эффект: при true запоминает время показа.
function should_show_session_popup($interval_seconds = 1800) {
    $now = time();
    $last = $_SESSION['popup_last_shown'] ?? null;
    if ($last === null || ($now - intval($last)) >= $interval_seconds) {
        $_SESSION['popup_last_shown'] = $now;
        return true;
    }
    return false;
}

// ── Реальный IP клиента ──────────────────────────────────────────────────────
// CF-Connecting-IP и X-Forwarded-For клиент подделывает одной строкой в запросе.
// Верить им можно ТОЛЬКО когда соединение пришло от известного прокси: иначе
// подделка обходит бан по IP, счётчик попыток входа и правило «один аккаунт на
// IP», а в журнале модерации остаётся чужой адрес. Список доверенных прокси —
// петля, приватные сети и опубликованные диапазоны Cloudflare; переопределяется
// переменной окружения ORION_TRUSTED_PROXIES (CIDR через запятую).
function orion_default_trusted_proxies() {
    return [
        '127.0.0.0/8', '::1/128',
        '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '169.254.0.0/16', 'fc00::/7', 'fe80::/10',
        // Cloudflare IPv4 (https://www.cloudflare.com/ips/) — обновлять вручную.
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
        '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
        '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        // Cloudflare IPv6.
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
        '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ];
}

function orion_trusted_proxies() {
    static $list = null;
    if ($list !== null) {
        return $list;
    }
    $configured = trim((string)(getenv('ORION_TRUSTED_PROXIES') ?: ''));
    if ($configured !== '') {
        $list = array_values(array_filter(array_map('trim', explode(',', $configured))));
        return $list;
    }
    $list = orion_default_trusted_proxies();
    return $list;
}

// Проверка «адрес входит в CIDR» для IPv4 и IPv6. Без маски сравниваются
// сами адреса.
function orion_ip_in_cidr($ip, $cidr) {
    $ip_bin = @inet_pton((string)$ip);
    if ($ip_bin === false) {
        return false;
    }
    $parts = explode('/', (string)$cidr, 2);
    $net_bin = @inet_pton($parts[0]);
    if ($net_bin === false || strlen($net_bin) !== strlen($ip_bin)) {
        return false;
    }
    $bits = isset($parts[1]) ? intval($parts[1]) : strlen($ip_bin) * 8;
    $bits = max(0, min(strlen($ip_bin) * 8, $bits));
    $whole_bytes = intdiv($bits, 8);
    $rest_bits = $bits % 8;
    if ($whole_bytes > 0 && strncmp($ip_bin, $net_bin, $whole_bytes) !== 0) {
        return false;
    }
    if ($rest_bits === 0) {
        return true;
    }
    $mask = chr((0xFF << (8 - $rest_bits)) & 0xFF);
    return (($ip_bin[$whole_bytes] & $mask) === ($net_bin[$whole_bytes] & $mask));
}

function orion_ip_is_trusted_proxy($ip) {
    $ip = trim((string)$ip);
    if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return false;
    }
    foreach (orion_trusted_proxies() as $cidr) {
        if (orion_ip_in_cidr($ip, $cidr)) {
            return true;
        }
    }
    return false;
}

function get_client_ip() {
    $remote = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
    if (!orion_ip_is_trusted_proxy($remote)) {
        return filter_var($remote, FILTER_VALIDATE_IP) !== false ? $remote : '';
    }
    $cf = trim((string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP) !== false) {
        return $cf;
    }
    // Цепочку X-Forwarded-For читаем СПРАВА НАЛЕВО: правые записи добавлены
    // нашими прокси, левые мог написать сам клиент.
    $forwarded = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
    if ($forwarded !== '') {
        $chain = array_reverse(array_map('trim', explode(',', $forwarded)));
        foreach ($chain as $candidate) {
            if ($candidate === '' || filter_var($candidate, FILTER_VALIDATE_IP) === false) {
                continue;
            }
            if (!orion_ip_is_trusted_proxy($candidate)) {
                return $candidate;
            }
        }
    }
    return filter_var($remote, FILTER_VALIDATE_IP) !== false ? $remote : '';
}

// Возвращает причину бана (строку, возможно пустую) если аккаунт ИЛИ IP забанен,
// иначе null. Используется в login.php чтобы не пускать забаненных и на сайт.
function find_active_ban($pdo, $account_id = null, $ip = null) {
    $conds = [];
    $params = [];
    if ($account_id) {
        $conds[] = "(ban_type = 'account' AND account_id = ?)";
        $params[] = intval($account_id);
    }
    if ($ip) {
        $conds[] = "(ban_type = 'ip' AND ip = ?)";
        $params[] = $ip;
    }
    if (empty($conds)) {
        return null;
    }
    try {
        $stmt = $pdo->prepare("SELECT reason FROM bans WHERE " . implode(' OR ', $conds) . " LIMIT 1");
        $stmt->execute($params);
        $reason = $stmt->fetchColumn();
    } catch (Exception $e) {
        return null;
    }
    return $reason === false ? null : (string)$reason;
}

function orion_remember_cookie_options($expires) {
    return [
        'expires' => intval($expires),
        'path' => '/',
        'domain' => '',
        'secure' => orion_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function orion_set_remember_cookie($value, $expires) {
    if (!headers_sent()) {
        setcookie(ORION_REMEMBER_COOKIE, (string)$value, orion_remember_cookie_options($expires));
    }
    if ($value === '') {
        unset($_COOKIE[ORION_REMEMBER_COOKIE]);
    } else {
        $_COOKIE[ORION_REMEMBER_COOKIE] = (string)$value;
    }
}

function orion_clear_remember_cookie() {
    orion_set_remember_cookie('', time() - 42000);
}

function orion_remember_cookie_parts() {
    $cookie = strtolower(trim((string)($_COOKIE[ORION_REMEMBER_COOKIE] ?? '')));
    if (!preg_match('/^([a-f0-9]{32})\.([a-f0-9]{64})$/D', $cookie, $matches)) {
        return null;
    }
    return ['selector' => $matches[1], 'validator' => $matches[2]];
}

function orion_issue_remember_token($pdo, $account_id) {
    $account_id = intval($account_id);
    if ($account_id <= 0) {
        return false;
    }
    try {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $expires = time() + ORION_REMEMBER_LIFETIME;
        $expires_at = date('Y-m-d H:i:s', $expires);

        $pdo->prepare("DELETE FROM account_remember_tokens WHERE expires_at <= ?")->execute([date('Y-m-d H:i:s')]);
        $stmt = $pdo->prepare("INSERT INTO account_remember_tokens (selector, account_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$selector, $account_id, hash('sha256', $validator), $expires_at]);

        orion_set_remember_cookie($selector . '.' . $validator, $expires);
        $_SESSION['remember_token_selector'] = $selector;
        $_SESSION['remember_token_expires_at'] = $expires;
        return true;
    } catch (Throwable $e) {
        error_log('Remember token issue error: ' . $e->getMessage());
        return false;
    }
}

function orion_forget_remember_token($pdo) {
    $parts = orion_remember_cookie_parts();
    $selector = (string)($_SESSION['remember_token_selector'] ?? ($parts['selector'] ?? ''));
    if ($selector !== '') {
        try {
            $pdo->prepare("DELETE FROM account_remember_tokens WHERE selector = ?")->execute([$selector]);
        } catch (Throwable $e) {
            error_log('Remember token revoke error: ' . $e->getMessage());
        }
    }
    unset($_SESSION['remember_token_selector'], $_SESSION['remember_token_expires_at']);
    orion_clear_remember_cookie();
}

function orion_revoke_account_remember_tokens($pdo, $account_id) {
    $account_id = intval($account_id);
    if ($account_id <= 0) {
        return;
    }
    try {
        $pdo->prepare("DELETE FROM account_remember_tokens WHERE account_id = ?")->execute([$account_id]);
    } catch (Throwable $e) {
        error_log('Remember account revoke error: ' . $e->getMessage());
    }
    if (intval($_SESSION['user_id'] ?? 0) === $account_id) {
        unset($_SESSION['remember_token_selector'], $_SESSION['remember_token_expires_at']);
        orion_clear_remember_cookie();
    }
}

function orion_restore_remembered_login($pdo) {
    if (!empty($_SESSION['user_id'])) {
        return false;
    }
    $parts = orion_remember_cookie_parts();
    if (!$parts) {
        if (!empty($_COOKIE[ORION_REMEMBER_COOKIE])) {
            orion_clear_remember_cookie();
        }
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            "SELECT t.account_id, t.token_hash, t.expires_at, a.username, a.is_admin, a.staff_role, a.is_verified
             FROM account_remember_tokens t
             INNER JOIN accounts a ON a.id = t.account_id
             WHERE t.selector = ?
             LIMIT 1"
        );
        $stmt->execute([$parts['selector']]);
        $remembered = $stmt->fetch();
        $validator_hash = hash('sha256', $parts['validator']);
        $is_expired = !$remembered || strtotime((string)$remembered['expires_at']) <= time();
        $is_verified = !defined('EMAIL_VERIFICATION_ENABLED')
            || !EMAIL_VERIFICATION_ENABLED
            || intval($remembered['is_verified'] ?? 0) === 1;
        $is_valid = $remembered
            && !$is_expired
            && $is_verified
            && hash_equals((string)$remembered['token_hash'], $validator_hash);

        if (!$is_valid) {
            $pdo->prepare("DELETE FROM account_remember_tokens WHERE selector = ?")->execute([$parts['selector']]);
            orion_clear_remember_cookie();
            return false;
        }

        $account_id = intval($remembered['account_id']);
        if (find_active_ban($pdo, $account_id, get_client_ip()) !== null) {
            orion_revoke_account_remember_tokens($pdo, $account_id);
            return false;
        }

        $consume = $pdo->prepare("DELETE FROM account_remember_tokens WHERE selector = ? AND token_hash = ?");
        $consume->execute([$parts['selector'], $validator_hash]);
        if ($consume->rowCount() !== 1) {
            orion_clear_remember_cookie();
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $account_id;
        $_SESSION['username'] = (string)$remembered['username'];
        $_SESSION['is_admin'] = intval($remembered['is_admin']) === 1;
        orion_issue_remember_token($pdo, $account_id);

        $now = date('Y-m-d H:i:s');
        $pdo->prepare("UPDATE accounts SET last_login = ?, reg_ip = ? WHERE id = ?")
            ->execute([$now, get_client_ip(), $account_id]);
        return true;
    } catch (Throwable $e) {
        error_log('Remember token restore error: ' . $e->getMessage());
        orion_clear_remember_cookie();
        return false;
    }
}

function orion_destroy_current_session() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        if (!headers_sent()) {
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
    }
    @session_destroy();
}

// Принудительно выкидывает забаненного пользователя из сессии на ЛЮБОЙ странице
// сайта (бан по аккаунту ИЛИ по текущему IP). Админов не трогаем. Вызывается
// после подключения к БД, поэтому действует глобально (db.php подключают все
// страницы). Так забаненный игрок не сможет пользоваться сайтом, даже если был
// залогинен до бана.
function enforce_session_ban($pdo) {
    if (empty($_SESSION['user_id'])) {
        return;
    }
    if (session_is_staff()) {
        return;
    }
    $reason = find_active_ban($pdo, intval($_SESSION['user_id']), get_client_ip());
    if ($reason === null) {
        return;
    }
    orion_revoke_account_remember_tokens($pdo, intval($_SESSION['user_id']));
    orion_destroy_current_session();
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($script !== 'login.php' && $script !== 'logout.php') {
        header('Location: login.php?error=banned');
        exit;
    }
}

// ── Ограничение попыток входа и регистрации ──────────────────────────────────
// Счётчик хранится в БД по IP: сессионный обходится удалением cookie.
// $window — окно блокировки в секундах, счётчик старше окна начинается заново.

function auth_attempts_state($pdo, $scope, $ip, $window) {
    $ip = trim((string)$ip);
    if ($ip === '') {
        return ['count' => 0, 'blocked_until' => 0];
    }
    try {
        $stmt = $pdo->prepare("SELECT attempts, last_attempt_at FROM auth_attempts WHERE scope = ? AND ip = ?");
        $stmt->execute([(string)$scope, $ip]);
        $row = $stmt->fetch();
    } catch (Exception $e) {
        error_log('Auth attempts read error: ' . $e->getMessage());
        return ['count' => 0, 'blocked_until' => 0];
    }
    if (!$row) {
        return ['count' => 0, 'blocked_until' => 0];
    }
    $last = strtotime((string)$row['last_attempt_at']);
    if ($last === false || (time() - $last) >= intval($window)) {
        return ['count' => 0, 'blocked_until' => 0];
    }
    return ['count' => intval($row['attempts']), 'blocked_until' => $last + intval($window)];
}

function auth_attempts_blocked($pdo, $scope, $ip, $max_attempts, $window) {
    $state = auth_attempts_state($pdo, $scope, $ip, $window);
    return $state['count'] >= intval($max_attempts);
}

function auth_attempt_register($pdo, $scope, $ip, $window) {
    $ip = trim((string)$ip);
    if ($ip === '') {
        return;
    }
    $now = date('Y-m-d H:i:s');
    $state = auth_attempts_state($pdo, $scope, $ip, $window);
    try {
        if ($state['count'] === 0) {
            $stmt = $pdo->prepare("INSERT INTO auth_attempts (scope, ip, attempts, first_attempt_at, last_attempt_at) VALUES (?, ?, 1, ?, ?) ON CONFLICT (scope, ip) DO UPDATE SET attempts = 1, first_attempt_at = EXCLUDED.first_attempt_at, last_attempt_at = EXCLUDED.last_attempt_at");
            $stmt->execute([(string)$scope, $ip, $now, $now]);
        } else {
            $stmt = $pdo->prepare("UPDATE auth_attempts SET attempts = attempts + 1, last_attempt_at = ? WHERE scope = ? AND ip = ?");
            $stmt->execute([$now, (string)$scope, $ip]);
        }
        // Заодно чистим протухшие строки, чтобы таблица не росла бесконечно.
        $pdo->prepare("DELETE FROM auth_attempts WHERE last_attempt_at < ?")
            ->execute([date('Y-m-d H:i:s', time() - 86400)]);
    } catch (Exception $e) {
        error_log('Auth attempts write error: ' . $e->getMessage());
    }
}

function auth_attempts_reset($pdo, $scope, $ip) {
    $ip = trim((string)$ip);
    if ($ip === '') {
        return;
    }
    try {
        $pdo->prepare("DELETE FROM auth_attempts WHERE scope = ? AND ip = ?")->execute([(string)$scope, $ip]);
    } catch (Exception $e) {
        error_log('Auth attempts reset error: ' . $e->getMessage());
    }
}

// ── Защита от одноразовых / временных почт ───────────────────────────────────
// Возвращает true, если домен email из чёрного списка (disposable_email_domains.php).
// Учитывает поддомены: foo.mailinator.com → блокируется по mailinator.com.
function is_disposable_email($email) {
    $at = strrpos((string)$email, '@');
    if ($at === false) {
        return false;
    }
    $domain = strtolower(trim(substr($email, $at + 1)));
    if ($domain === '') {
        return false;
    }
    static $list = null;
    if ($list === null) {
        $file = __DIR__ . '/disposable_email_domains.php';
        $arr = is_file($file) ? require $file : [];
        $list = array_flip(array_map('strtolower', $arr));  // O(1) поиск
    }
    if (isset($list[$domain])) {
        return true;
    }
    // Проверяем родительские домены поддомена: a.b.trashmail.com → trashmail.com.
    $parts = explode('.', $domain);
    while (count($parts) > 2) {
        array_shift($parts);
        if (isset($list[implode('.', $parts)])) {
            return true;
        }
    }
    return false;
}

// ── Коды подтверждения email / сброса пароля ─────────────────────────────────

function generate_email_code() {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function email_code_hash($code) {
    return hash('sha256', trim((string)$code));
}

// Можно ли запросить новый код (антиспам). Возвращает true, если последний код
// для этого email+purpose старше MAIL_CODE_RESEND_SEC (или его нет).
function can_request_email_code($pdo, $email, $purpose) {
    $stmt = $pdo->prepare("SELECT created_at FROM email_codes WHERE email = ? AND purpose = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$email, $purpose]);
    $last = $stmt->fetchColumn();
    if (!$last) {
        return true;
    }
    $resend = defined('MAIL_CODE_RESEND_SEC') ? MAIL_CODE_RESEND_SEC : 60;
    return (time() - strtotime($last)) >= $resend;
}

// Создаёт новый код (удаляя старые того же типа для email) и возвращает его.
function create_email_code($pdo, $account_id, $email, $purpose) {
    $code = generate_email_code();
    $ttl = defined('MAIL_CODE_TTL_MIN') ? MAIL_CODE_TTL_MIN : 15;
    $del = $pdo->prepare("DELETE FROM email_codes WHERE email = ? AND purpose = ?");
    $del->execute([$email, $purpose]);
    $stmt = $pdo->prepare("INSERT INTO email_codes (account_id, email, purpose, code_hash, expires_at, attempts, created_at) VALUES (?, ?, ?, ?, ?, 0, ?)");
    $stmt->execute([
        $account_id ?: null,
        $email,
        $purpose,
        email_code_hash($code),
        date('Y-m-d H:i:s', time() + $ttl * 60),
        date('Y-m-d H:i:s'),
    ]);
    return $code;
}

// Проверяет код. Возвращает account_id (может быть null) при успехе или false.
// При успехе код удаляется. Защита от перебора: максимум 6 попыток.
function check_email_code($pdo, $email, $purpose, $code) {
    $stmt = $pdo->prepare("SELECT id, account_id, code_hash, expires_at, attempts FROM email_codes WHERE email = ? AND purpose = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$email, $purpose]);
    $row = $stmt->fetch();
    if (!$row) {
        return false;
    }
    if (strtotime($row['expires_at']) < time() || intval($row['attempts']) >= 6) {
        $pdo->prepare("DELETE FROM email_codes WHERE id = ?")->execute([$row['id']]);
        return false;
    }
    if (!hash_equals($row['code_hash'], email_code_hash($code))) {
        $pdo->prepare("UPDATE email_codes SET attempts = attempts + 1 WHERE id = ?")->execute([$row['id']]);
        return false;
    }
    $account_id = $row['account_id'];
    $pdo->prepare("DELETE FROM email_codes WHERE id = ?")->execute([$row['id']]);
    return ['account_id' => $account_id];
}

// ── Картинки проекта ─────────────────────────────────────────────────────────
// Возвращает первый существующий файл из списка. Нужен, чтобы при отсутствии
// нового ассета страница показывала прежний, а не значок битой картинки.
function orion_first_existing_image(array $candidates) {
    foreach ($candidates as $path) {
        if (is_file(__DIR__ . '/' . ltrim($path, '/'))) {
            return $path;
        }
    }
    return end($candidates);
}

// Главный экран — круглый логотип проекта. Углы файла непрозрачно чёрные,
// поэтому изображение обрезается в круг средствами CSS.
if (!defined('ORION_HERO_IMAGE')) {
    define('ORION_HERO_IMAGE', orion_first_existing_image([
        'images/logo-hero.jpg',
        'images/logo.png',
    ]));
}

// Обложка новостей без собственного медиа — ключевой арт Apollo.
if (!defined('ORION_NEWS_COVER')) {
    define('ORION_NEWS_COVER', orion_first_existing_image([
        'images/orion-apollo-keyart.jpg',
        'images/news-default-cover.png',
    ]));
}

// Атрибуты width/height для <img> берём из самого файла. Без них браузер не
// знает пропорций до загрузки картинки и верстка прыгает (CLS). Так размер
// остаётся верным, даже если картинку заменили на другую.
function image_size_attrs($relative_path) {
    static $cache = [];
    $relative_path = ltrim((string)$relative_path, '/');
    if (array_key_exists($relative_path, $cache)) {
        return $cache[$relative_path];
    }
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);
    $attrs = '';
    if (is_file($file)) {
        $size = @getimagesize($file);
        if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
            $attrs = ' width="' . intval($size[0]) . '" height="' . intval($size[1]) . '"';
        }
    }
    $cache[$relative_path] = $attrs;
    return $attrs;
}

function security_headers() {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // Ограничиваем доступ к аппаратным API — сайту они не нужны.
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');

    $csp = "default-src 'self'; "
        . "script-src 'self' https://www.google.com https://www.gstatic.com 'unsafe-inline'; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "frame-src https://www.google.com; "
        . "img-src 'self' data: https://storage.googleapis.com https://lh3.googleusercontent.com https://firebasestorage.googleapis.com; "
        . "media-src 'self'; connect-src 'self' https://api.manifold.markets; "
        . "font-src 'self' https://fonts.gstatic.com; "
        // Запрещаем встраивание в чужие страницы на уровне CSP (современный
        // аналог X-Frame-Options) и отправку форм на сторонние адреса.
        . "frame-ancestors 'none'; form-action 'self'; base-uri 'self'";

    if (orion_is_https()) {
        // На HTTPS доводим смешанный контент до TLS и включаем HSTS на год.
        // Локально по HTTP не отправляем: иначе браузер запомнит редирект
        // на https://localhost и разработка встанет.
        $csp .= '; upgrade-insecure-requests';
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    header('Content-Security-Policy: ' . $csp);
}

security_headers();

$db_config = load_server_database_config();
$db_host = $db_config['host'] ?? '127.0.0.1';
$db_port = intval($db_config['port'] ?? 5432);
$db_name = $db_config['name'] ?? 'wot_emulator';
$db_user = $db_config['user'] ?? 'postgres';
$db_pass = $db_config['password'] ?? '';

try {
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
    // Neon и другие облачные PostgreSQL требуют SSL + SNI
    if (str_contains($db_host, 'neon.tech')) {
        $dsn .= ';sslmode=require';
        // Neon требует endpoint ID для SNI. Извлекаем из имени хоста
        // (первая часть до первого дефиса после 'ep-')
        if (preg_match('/^(ep-[a-z0-9]+)-/', $db_host, $m)) {
            $dsn .= ';options=endpoint%3D' . $m[1];
        }
    } elseif (str_contains($db_host, 'amazonaws.com')) {
        $dsn .= ';sslmode=require';
    }
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    ensure_site_schema($pdo);
} catch (PDOException $e) {
    error_log("DB connection error: " . $e->getMessage());
    die("Connection failed. Please check server configuration.");
}

if (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'logout.php') {
    orion_restore_remembered_login($pdo);
    if (!empty($_SESSION['user_id']) && !orion_remember_cookie_parts()) {
        orion_forget_remember_token($pdo);
        orion_issue_remember_token($pdo, intval($_SESSION['user_id']));
    } elseif (
        !empty($_SESSION['user_id'])
        && intval($_SESSION['remember_token_expires_at'] ?? 0) <= time() + ORION_REMEMBER_REFRESH_WINDOW
    ) {
        orion_forget_remember_token($pdo);
        orion_issue_remember_token($pdo, intval($_SESSION['user_id']));
    }
}
synchronize_contract_lifecycle($pdo);
refresh_session_staff_access($pdo);

// Глобальная проверка бана: забаненного (по аккаунту или IP) выкидываем из
// сессии на любой странице сайта.
enforce_session_ban($pdo);

// SEO: единый блок мета-тегов для <head> каждой страницы.
// $path — относительный путь страницы ('' для главной),
// $index = false ставит noindex для служебных страниц.
function seo_head($title, $description, $path = '', $index = true, $options = []) {
    $base = 'https://projectorion.fun/';
    $lang = function_exists('current_lang') ? current_lang() : 'ru';
    $locale = function_exists('i18n_locale_meta') ? i18n_locale_meta($lang) : [
        'og_locale' => 'ru_RU',
        'keywords' => 'project orion, projectorion, projectorion.fun',
    ];
    $localized_title = function_exists('i18n_translate_text')
        ? i18n_translate_text($title, $lang)
        : $title;
    $localized_description = function_exists('i18n_translate_text')
        ? i18n_translate_text($description, $lang)
        : $description;
    $locale_urls = function_exists('i18n_locale_urls')
        ? i18n_locale_urls($path)
        : [$lang => ltrim((string)$path, '/') . '?lang=' . rawurlencode($lang)];
    $absolute_urls = [];
    foreach ($locale_urls as $locale_code => $relative_url) {
        $absolute_urls[$locale_code] = $base . ltrim($relative_url, '/');
    }
    $canonical_raw = $absolute_urls[$lang] ?? ($base . ltrim((string)$path, '/'));
    $t = htmlspecialchars($localized_title, ENT_QUOTES, 'UTF-8');
    $d = htmlspecialchars($localized_description, ENT_QUOTES, 'UTF-8');
    $u = htmlspecialchars($canonical_raw, ENT_QUOTES, 'UTF-8');
    $img = htmlspecialchars($base . 'images/banner.png', ENT_QUOTES, 'UTF-8');
    $keywords = htmlspecialchars((string)($locale['keywords'] ?? ''), ENT_QUOTES, 'UTF-8');
    echo "<title>$t</title>\n";
    echo "    <meta name=\"description\" content=\"$d\">\n";
    echo "    <meta name=\"keywords\" content=\"$keywords\">\n";
    echo "    <meta name=\"language\" content=\"" . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . "\">\n";
    echo '    <meta name="robots" content="' . ($index ? 'index, follow' : 'noindex, follow') . "\">\n";
    echo "    <link rel=\"canonical\" href=\"$u\">\n";
    if (function_exists('i18n_locale_catalog')) {
        foreach (i18n_locale_catalog() as $locale_code => $locale_meta) {
            $alternate = htmlspecialchars($absolute_urls[$locale_code] ?? '', ENT_QUOTES, 'UTF-8');
            echo '    <link rel="alternate" hreflang="' . htmlspecialchars($locale_meta['hreflang'], ENT_QUOTES, 'UTF-8') . '" href="' . $alternate . '">' . "\n";
        }
        $x_default = htmlspecialchars($absolute_urls['ru'] ?? $canonical_raw, ENT_QUOTES, 'UTF-8');
        echo "    <link rel=\"alternate\" hreflang=\"x-default\" href=\"$x_default\">\n";
    }
    echo "    <meta property=\"og:type\" content=\"website\">\n";
    echo "    <meta property=\"og:site_name\" content=\"Project Orion\">\n";
    echo "    <meta property=\"og:title\" content=\"$t\">\n";
    echo "    <meta property=\"og:description\" content=\"$d\">\n";
    echo "    <meta property=\"og:url\" content=\"$u\">\n";
    echo "    <meta property=\"og:image\" content=\"$img\">\n";
    echo "    <meta property=\"og:locale\" content=\"" . htmlspecialchars($locale['og_locale'] ?? 'ru_RU', ENT_QUOTES, 'UTF-8') . "\">\n";
    if (function_exists('i18n_locale_catalog')) {
        foreach (i18n_locale_catalog() as $locale_code => $locale_meta) {
            if ($locale_code === $lang) {
                continue;
            }
            echo "    <meta property=\"og:locale:alternate\" content=\"" . htmlspecialchars($locale_meta['og_locale'], ENT_QUOTES, 'UTF-8') . "\">\n";
        }
    }
    echo "    <meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    echo "    <meta name=\"twitter:title\" content=\"$t\">\n";
    echo "    <meta name=\"twitter:description\" content=\"$d\">\n";
    echo "    <meta name=\"twitter:image\" content=\"$img\">\n";
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $localized_title,
        'description' => $localized_description,
        'url' => $canonical_raw,
        'inLanguage' => $lang,
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'Project Orion',
            'url' => $base,
        ],
    ];
    if (is_array($options)) {
        $schema = array_replace($schema, $options);
    }
    $json = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    echo '    <script type="application/ld+json">' . ($json ?: '{}') . '</script>';
}

// Локализация: оборачиваем вывод страницы в буфер, чтобы перевести готовый HTML
// на украинский (если выбран) и добавить переключатель языка. Не-HTML ответы
// (JSON из admin.php и т.п.) фильтр пропускает без изменений.
ob_start('i18n_output_filter');
?>
