<?php
// API Router for Reborn Launcher
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?');
$uri = rtrim($uri, '/');

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
    $payload = [
        'sub' => $user_id,
        'username' => $username,
        'email' => $email,
        'exp' => $expires_at
    ];
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $body = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$header.$body", $secret, true);
    $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    return [
        'token' => "$header.$body.$signature",
        'expires_at' => date('c', $expires_at)
    ];
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
    if (!str_starts_with($auth, 'Bearer ')) {
        api_error(401, 'Missing or invalid token');
    }
    $token = substr($auth, 7);
    $payload = api_decode_token($token);
    if (!$payload) {
        api_error(401, 'Token expired or invalid');
    }
    return $payload;
}

function api_error($code, $message) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function api_success($data) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

// Route: POST /api/login
if ($method === 'POST' && $uri === '/api/login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $login = trim((string)($input['login'] ?? ''));
    $password = (string)($input['password'] ?? '');
    
    if ($login === '' || $password === '') {
        api_error(400, 'Login and password required');
    }
    
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM accounts WHERE username = ? OR email = ? OR normalized_name = ?");
    $stmt->execute([$login, $login, strtolower($login)]);
    $user = $stmt->fetch();
    
    if (!$user || md5($password) !== $user['password_hash']) {
        api_error(401, 'Invalid login or password');
    }
    
    $token_data = api_create_token($user['id'], $user['username'], $user['email']);
    api_success([
        'token' => $token_data['token'],
        'expires_at' => $token_data['expires_at'],
        'user' => [
            'id' => intval($user['id']),
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ]);
}

// Route: POST /api/refresh
if ($method === 'POST' && $uri === '/api/refresh') {
    $user = api_get_user();
    $token_data = api_create_token($user['sub'], $user['username'], $user['email']);
    api_success([
        'token' => $token_data['token'],
        'expires_at' => $token_data['expires_at']
    ]);
}

// Route: GET /api/servers
if ($method === 'GET' && $uri === '/api/servers') {
    api_get_user();
    
    $servers_file = __DIR__ . '/servers.json';
    if (!file_exists($servers_file)) {
        api_success(['servers' => []]);
    }
    
    $data = json_decode(file_get_contents($servers_file), true);
    $servers = [];
    
    foreach (($data['servers'] ?? []) as $s) {
        $servers[] = [
            'name' => $s['name'],
            'ip' => $s['ip'],
            'port' => $s['port'],
            'status' => 'offline',
            'players' => 0,
            'max_players' => 300,
            'description' => $s['description'] ?? ''
        ];
    }
    
    api_success(['servers' => $servers]);
}

// Route: GET /api/servers/{ip}:{port}/status
if ($method === 'GET' && preg_match('#^/api/servers/([^:]+):(\d+)/status$#', $uri, $m)) {
    api_get_user();
    api_error(503, 'Server not responding');
}

// Route: GET /api/updates/manifest
if ($method === 'GET' && $uri === '/api/updates/manifest') {
    api_get_user();
    
    $manifest_path = __DIR__ . '/updates/manifest.json';
    if (!file_exists($manifest_path)) {
        api_success(['current_version' => '1.0.0', 'latest_version' => '1.0.0', 'updates' => []]);
    }
    
    $data = json_decode(file_get_contents($manifest_path), true);
    api_success($data);
}

// Route: GET /api/updates/{version}/{path}
if ($method === 'GET' && preg_match('#^/api/updates/([^/]+)/(.+)$#', $uri, $m)) {
    api_get_user();
    
    $version = $m[1];
    $path = $m[2];
    $file_path = __DIR__ . '/updates/' . $version . '/' . $path;
    
    if (!file_exists($file_path)) {
        api_error(404, 'File not found');
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    readfile($file_path);
    exit;
}

// Route: GET /api/stats/{account_id}
if ($method === 'GET' && preg_match('#^/api/stats/(\d+)$#', $uri, $m)) {
    api_get_user();
    
    $account_id = intval($m[1]);
    $stmt = $pdo->prepare("SELECT * FROM dossier WHERE account_id = ?");
    $stmt->execute([$account_id]);
    $stats = $stmt->fetch();
    
    if (!$stats) {
        api_success(['stats' => null]);
    }
    
    $battles = intval($stats['total_battles'] ?? 0);
    $wins = intval($stats['wins'] ?? 0);
    $shots = intval($stats['shots'] ?? 0);
    
    api_success([
        'stats' => [
            'battles' => $battles,
            'wins' => $wins,
            'losses' => intval($stats['losses'] ?? 0),
            'draws' => intval($stats['draws'] ?? 0),
            'frags' => intval($stats['frags'] ?? 0),
            'damage_dealt' => intval($stats['damage_dealt'] ?? 0),
            'damage_received' => intval($stats['damage_received'] ?? 0),
            'xp_earned' => intval($stats['total_xp'] ?? 0),
            'shots' => $shots,
            'hits' => intval($stats['hits'] ?? 0),
            'win_rate' => $battles > 0 ? round($wins / $battles * 100, 1) : 0,
            'accuracy' => $shots > 0 ? round(intval($stats['hits'] ?? 0) / $shots * 100, 1) : 0,
            'avg_damage' => $battles > 0 ? round(intval($stats['damage_dealt'] ?? 0) / $battles, 1) : 0,
            'avg_xp' => $battles > 0 ? round(intval($stats['total_xp'] ?? 0) / $battles, 1) : 0
        ]
    ]);
}

// Route: GET /api/stats/{account_id}/history
if ($method === 'GET' && preg_match('#^/api/stats/(\d+)/history$#', $uri, $m)) {
    api_get_user();
    api_success(['battles' => []]);
}

// 404
api_error(404, 'Endpoint not found');
