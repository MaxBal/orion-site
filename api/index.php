<?php
// Vercel serverless entry point — routes all requests to the appropriate PHP file.
// This avoids hitting the 12-function Hobby plan limit.

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?');
$uri = rtrim($uri, '/');

$root = dirname(__DIR__);

// Map clean URLs to .php files
$routes = [
    '' => 'index.php',
    '/' => 'index.php',
    '/index' => 'index.php',
    '/index.php' => 'index.php',
    '/download' => 'download.php',
    '/download.php' => 'download.php',
    '/changelog' => 'changelog.php',
    '/changelog.php' => 'changelog.php',
    '/roadmap' => 'roadmap.php',
    '/roadmap.php' => 'roadmap.php',
    '/bugs' => 'bugs.php',
    '/bugs.php' => 'bugs.php',
    '/donate' => 'donate.php',
    '/donate.php' => 'donate.php',
    '/login' => 'login.php',
    '/login.php' => 'login.php',
    '/logout' => 'logout.php',
    '/logout.php' => 'logout.php',
    '/register' => 'register.php',
    '/register.php' => 'register.php',
    '/profile' => 'profile.php',
    '/profile.php' => 'profile.php',
    '/players' => 'players.php',
    '/players.php' => 'players.php',
    '/markets' => 'markets.php',
    '/markets.php' => 'markets.php',
    '/gso' => 'gso.php',
    '/gso.php' => 'gso.php',
    '/petitions' => 'petitions.php',
    '/petitions.php' => 'petitions.php',
    '/contracts' => 'contracts.php',
    '/contracts.php' => 'contracts.php',
    '/subscriptions' => 'subscriptions.php',
    '/subscriptions.php' => 'subscriptions.php',
    '/legal' => 'legal.php',
    '/legal.php' => 'legal.php',
    '/verify' => 'verify.php',
    '/verify.php' => 'verify.php',
    '/reset_password' => 'reset_password.php',
    '/reset_password.php' => 'reset_password.php',
    '/admin' => 'admin.php',
    '/admin.php' => 'admin.php',
    '/notifications' => 'notifications.php',
    '/notifications.php' => 'notifications.php',
    '/discord' => 'discord.php',
    '/discord.php' => 'discord.php',
    '/bug_view' => 'bug_view.php',
    '/bug_view.php' => 'bug_view.php',
    '/contract_pdf' => 'contract_pdf.php',
    '/contract_pdf.php' => 'contract_pdf.php',
];

if (isset($routes[$uri])) {
    $file = $root . '/' . $routes[$uri];
} else {
    // Try direct .php file
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

// Set SCRIPT_NAME so PHP pages see the correct filename
$_SERVER['SCRIPT_NAME'] = '/' . basename($file);
$_SERVER['SCRIPT_FILENAME'] = $file;

// Edge caching для гостевых страниц (без сессии)
$guest_pages = ['index', 'download', 'changelog', 'roadmap', 'legal', 'players', 'markets', 'subscriptions', 'donate'];
$page_name = pathinfo(basename($file), PATHINFO_FILENAME);
$is_guest = in_array($page_name, $guest_pages, true);

if ($is_guest) {
    ob_start();
    require $file;
    $html = ob_get_clean();

    // Убираем PHP session/cache заголовки, ставим CDN-кеш
    header_remove('Cache-Control');
    header_remove('Pragma');
    header_remove('Expires');
    header_remove('Set-Cookie');
    header('Cache-Control: public, s-maxage=60, stale-while-revalidate=300');
    header('CDN-Cache-Control: public, max-age=60');
    header('Vercel-CDN-Cache-Control: public, s-maxage=60');

    echo $html;
} else {
    require $file;
}
