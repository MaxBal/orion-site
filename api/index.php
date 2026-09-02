<?php
// Vercel serverless entry point — routes all requests to the appropriate PHP file.
// This avoids hitting the 12-function Hobby plan limit.

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?');
$uri = rtrim($uri, '/');

// Static files are handled by Vercel's routing, so we only handle PHP routes.

$root = dirname(__DIR__);

// Map clean URLs to .php files
$routes = [
    '' => 'index.php',
    '/' => 'index.php',
    '/index' => 'index.php',
    '/download' => 'download.php',
    '/changelog' => 'changelog.php',
    '/roadmap' => 'roadmap.php',
    '/bugs' => 'bugs.php',
    '/donate' => 'donate.php',
    '/login' => 'login.php',
    '/logout' => 'logout.php',
    '/register' => 'register.php',
    '/profile' => 'profile.php',
    '/players' => 'players.php',
    '/markets' => 'markets.php',
    '/gso' => 'gso.php',
    '/petitions' => 'petitions.php',
    '/contracts' => 'contracts.php',
    '/subscriptions' => 'subscriptions.php',
    '/legal' => 'legal.php',
    '/verify' => 'verify.php',
    '/reset_password' => 'reset_password.php',
    '/admin' => 'admin.php',
    '/notifications' => 'notifications.php',
    '/discord' => 'discord.php',
    '/bug_view' => 'bug_view.php',
    '/contract_pdf' => 'contract_pdf.php',
];

if (isset($routes[$uri])) {
    $file = $root . '/' . $routes[$uri];
} else {
    // Try direct .php file
    $candidate = $root . $uri . '.php';
    if (is_file($candidate)) {
        $file = $candidate;
    } else {
        // Fallback to index
        $file = $root . '/index.php';
    }
}

// Set SCRIPT_NAME so PHP pages see the correct filename
$_SERVER['SCRIPT_NAME'] = '/' . basename($file);
$_SERVER['SCRIPT_FILENAME'] = $file;

require $file;
