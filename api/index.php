<?php
// Router for both API and site pages
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?');
$uri = rtrim($uri, '/');

// API routes
if (str_starts_with($uri, '/api')) {
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../db.php';
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // JWT functions
    function api_create_token($user_id, $username, $email) {
        $secret = getenv('JWT_SECRET') ?: 'orion-reborn-secret-key-change-me';
        $expires_at = time() + 86400;
        $payload = ['sub' => $user_id, 'username' => $username, 'email' => $email, 'exp' => $expires_at];
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$body", $secret, true);
        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return ['token' => "$header.$body.$signature", 'expires_at' => date('c', $expires_at)];
    }
    
    function api_decode_token($token) {
        $secret = getenv('JWT_SECRET') ?: 'orion-reborn-secret-key-change-me';
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $body, $signature] = $parts;
        $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$body", $secret, true)), '+/', '-_'), '=');
        if (!hash_equals($expected, $signature)) return null;
        $payload = json_decode(base64_decode($body), true);
        if (!$payload || ($payload['exp'] ?? 0) < time()) return null;
        return $payload;
    }
    
    function api_get_user() {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($auth, 'Bearer ')) api_error(401, 'Missing or invalid token');
        $payload = api_decode_token(substr($auth, 7));
        if (!$payload) api_error(401, 'Token expired or invalid');
        return $payload;
    }
    
    function api_error($code, $message) { http_response_code($code); echo json_encode(['success' => false, 'error' => $message]); exit; }
    function api_success($data) { echo json_encode(array_merge(['success' => true], $data)); exit; }
    
    // API info
    if ($uri === '/api') {
        api_success(['name' => 'Project Orion Reborn API', 'version' => '1.0.0', 'endpoints' => ['POST /api/login', 'POST /api/refresh', 'GET /api/servers', 'GET /api/servers/{ip}:{port}/status', 'GET /api/updates/manifest', 'GET /api/updates/{version}/{path}', 'GET /api/stats/{account_id}', 'GET /api/stats/{account_id}/history']]);
    }
    
    // Login
    if ($method === 'POST' && $uri === '/api/login') {
        $input = json_decode(file_get_contents('php://input'), true);
        $login = trim((string)($input['login'] ?? ''));
        $password = (string)($input['password'] ?? '');
        if ($login === '' || $password === '') api_error(400, 'Login and password required');
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM accounts WHERE username = ? OR email = ? OR normalized_name = ?");
        $stmt->execute([$login, $login, strtolower($login)]);
        $user = $stmt->fetch();
        if (!$user || md5($password) !== $user['password_hash']) api_error(401, 'Invalid login or password');
        $token_data = api_create_token($user['id'], $user['username'], $user['email']);
        api_success(['token' => $token_data['token'], 'expires_at' => $token_data['expires_at'], 'user' => ['id' => intval($user['id']), 'username' => $user['username'], 'email' => $user['email']]]);
    }
    
    // Refresh
    if ($method === 'POST' && $uri === '/api/refresh') {
        $user = api_get_user();
        $token_data = api_create_token($user['sub'], $user['username'], $user['email']);
        api_success(['token' => $token_data['token'], 'expires_at' => $token_data['expires_at']]);
    }
    
    // Servers
    if ($method === 'GET' && $uri === '/api/servers') {
        api_get_user();
        $servers_file = __DIR__ . '/../servers.json';
        if (!file_exists($servers_file)) api_success(['servers' => []]);
        $data = json_decode(file_get_contents($servers_file), true);
        $servers = [];
        foreach (($data['servers'] ?? []) as $s) {
            $status = ['status' => 'offline', 'players' => 0, 'max_players' => 300, 'ping' => 0];
            $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($sock) {
                socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 3, 'usec' => 0]);
                $start = microtime(true);
                @socket_sendto($sock, "\xff\xff\xff\xff\x54Source Engine Query\x00", 25, 0, $s['ip'], $s['port']);
                $buf = ''; $from = ''; $port = 0;
                if (@socket_recvfrom($sock, $buf, 4096, 0, $from, $port)) {
                    $status['status'] = 'online';
                    $status['ping'] = intval((microtime(true) - $start) * 1000);
                    if (strlen($buf) > 5) { $status['players'] = ord($buf[4]); $status['max_players'] = ord($buf[5]); }
                }
                socket_close($sock);
            }
            $servers[] = array_merge(['name' => $s['name'], 'ip' => $s['ip'], 'port' => $s['port'], 'description' => $s['description'] ?? ''], $status);
        }
        api_success(['servers' => $servers]);
    }
    
    // Server status
    if ($method === 'GET' && preg_match('#^/api/servers/([^:]+):(\d+)/status$#', $uri, $m)) {
        api_get_user();
        $ip = $m[1]; $port = intval($m[2]);
        $status = ['status' => 'offline', 'players' => 0, 'max_players' => 300, 'ping' => 0];
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock) {
            socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 3, 'usec' => 0]);
            $start = microtime(true);
            @socket_sendto($sock, "\xff\xff\xff\xff\x54Source Engine Query\x00", 25, 0, $ip, $port);
            $buf = ''; $from = ''; $rport = 0;
            if (@socket_recvfrom($sock, $buf, 4096, 0, $from, $rport)) {
                $status['status'] = 'online'; $status['ping'] = intval((microtime(true) - $start) * 1000);
                if (strlen($buf) > 5) { $status['players'] = ord($buf[4]); $status['max_players'] = ord($buf[5]); }
            }
            socket_close($sock);
        }
        if ($status['status'] === 'offline') api_error(503, 'Server not responding');
        api_success($status);
    }
    
    // Updates manifest
    if ($method === 'GET' && $uri === '/api/updates/manifest') {
        api_get_user();
        $manifest_path = __DIR__ . '/../updates/manifest.json';
        if (!file_exists($manifest_path)) api_success(['current_version' => '1.0.0', 'latest_version' => '1.0.0', 'updates' => []]);
        api_success(json_decode(file_get_contents($manifest_path), true));
    }
    
    // Update file
    if ($method === 'GET' && preg_match('#^/api/updates/([^/]+)/(.+)$#', $uri, $m)) {
        api_get_user();
        $file_path = __DIR__ . '/../updates/' . $m[1] . '/' . $m[2];
        if (!file_exists($file_path)) api_error(404, 'File not found');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($m[2]) . '"');
        readfile($file_path); exit;
    }
    
    // Stats
    if ($method === 'GET' && preg_match('#^/api/stats/(\d+)$#', $uri, $m)) {
        api_get_user();
        $stmt = $pdo->prepare("SELECT * FROM dossier WHERE account_id = ?");
        $stmt->execute([intval($m[1])]);
        $stats = $stmt->fetch();
        if (!$stats) api_success(['stats' => null]);
        $battles = intval($stats['total_battles'] ?? 0); $wins = intval($stats['wins'] ?? 0); $shots = intval($stats['shots'] ?? 0);
        api_success(['stats' => ['battles' => $battles, 'wins' => $wins, 'losses' => intval($stats['losses'] ?? 0), 'draws' => intval($stats['draws'] ?? 0), 'frags' => intval($stats['frags'] ?? 0), 'damage_dealt' => intval($stats['damage_dealt'] ?? 0), 'damage_received' => intval($stats['damage_received'] ?? 0), 'xp_earned' => intval($stats['total_xp'] ?? 0), 'shots' => $shots, 'hits' => intval($stats['hits'] ?? 0), 'win_rate' => $battles > 0 ? round($wins / $battles * 100, 1) : 0, 'accuracy' => $shots > 0 ? round(intval($stats['hits'] ?? 0) / $shots * 100, 1) : 0, 'avg_damage' => $battles > 0 ? round(intval($stats['damage_dealt'] ?? 0) / $battles, 1) : 0, 'avg_xp' => $battles > 0 ? round(intval($stats['total_xp'] ?? 0) / $battles, 1) : 0]]);
    }
    
    // Battle history
    if ($method === 'GET' && preg_match('#^/api/stats/(\d+)/history$#', $uri, $m)) {
        api_get_user();
        api_success(['battles' => []]);
    }
    
    api_error(404, 'Endpoint not found');
}

// Site routes - map URLs to PHP files
$root = dirname(__DIR__);
$routes = [
    '' => 'index.php', '/' => 'index.php', '/index' => 'index.php', '/index.php' => 'index.php',
    '/download' => 'download.php', '/download.php' => 'download.php',
    '/changelog' => 'changelog.php', '/changelog.php' => 'changelog.php',
    '/roadmap' => 'roadmap.php', '/roadmap.php' => 'roadmap.php',
    '/bugs' => 'bugs.php', '/bugs.php' => 'bugs.php',
    '/donate' => 'donate.php', '/donate.php' => 'donate.php',
    '/login' => 'login.php', '/login.php' => 'login.php',
    '/logout' => 'logout.php', '/logout.php' => 'logout.php',
    '/register' => 'register.php', '/register.php' => 'register.php',
    '/profile' => 'profile.php', '/profile.php' => 'profile.php',
    '/players' => 'players.php', '/players.php' => 'players.php',
    '/markets' => 'markets.php', '/markets.php' => 'markets.php',
    '/gso' => 'gso.php', '/gso.php' => 'gso.php',
    '/petitions' => 'petitions.php', '/petitions.php' => 'petitions.php',
    '/contracts' => 'contracts.php', '/contracts.php' => 'contracts.php',
    '/subscriptions' => 'subscriptions.php', '/subscriptions.php' => 'subscriptions.php',
    '/legal' => 'legal.php', '/legal.php' => 'legal.php',
    '/verify' => 'verify.php', '/verify.php' => 'verify.php',
    '/reset_password' => 'reset_password.php', '/reset_password.php' => 'reset_password.php',
    '/admin' => 'admin.php', '/admin.php' => 'admin.php',
    '/notifications' => 'notifications.php', '/notifications.php' => 'notifications.php',
    '/discord' => 'discord.php', '/discord.php' => 'discord.php',
    '/bug_view' => 'bug_view.php', '/bug_view.php' => 'bug_view.php',
    '/contract_pdf' => 'contract_pdf.php', '/contract_pdf.php' => 'contract_pdf.php',
];

if (isset($routes[$uri])) {
    $file = $root . '/' . $routes[$uri];
} else {
    $candidate = $root . $uri;
    if (is_file($candidate)) {
        $file = $candidate;
    } else {
        $candidate = $root . $uri . '.php';
        if (is_file($candidate)) {
            $file = $candidate;
        } else {
            $file = $root . '/index.php';
        }
    }
}

$_SERVER['SCRIPT_NAME'] = '/' . basename($file);
$_SERVER['SCRIPT_FILENAME'] = $file;

$guest_pages = ['index', 'download', 'changelog', 'roadmap', 'legal', 'players', 'markets', 'subscriptions', 'donate'];
$page_name = pathinfo(basename($file), PATHINFO_FILENAME);
$is_guest = in_array($page_name, $guest_pages, true);

if ($is_guest) {
    ob_start();
    require $file;
    $html = ob_get_clean();
    header_remove('Cache-Control');
    header_remove('Pragma');
    header_remove('Expires');
    header_remove('Set-Cookie');
    header('Cache-Control: public, s-maxage=60, stale-while-revalidate=300');
    echo $html;
} else {
    require $file;
}
