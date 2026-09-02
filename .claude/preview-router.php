<?php
// Роутер для встроенного сервера PHP (`php -S`), используется только локальным
// превью из .claude/launch.json. На боевом сервере файл недостижим: под любым
// другим SAPI он сразу отвечает 404, чтобы прямой запрос не подключил сам себя.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

// Роутер НЕ стартует сессию и НЕ печатает ничего своего.
// db.php на каждом запросе вызывает ini_set('session.*') и
// session_set_cookie_params(). На уже активной сессии это warning'и, они
// печатаются до <!DOCTYPE html>, и с этого момента заголовки отправлены:
// login.php теряет и session_regenerate_id(), и свой Location, а вход
// заканчивается сообщением «Сессия устарела. Обновите страницу.».
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = rtrim($_SERVER['DOCUMENT_ROOT'], '\\/');
$target = $root . str_replace('/', DIRECTORY_SEPARATOR, $path);

// Служебная папка наружу не отдаётся. Без этого запрос самого роутера
// подключил бы его же — рекурсия до предела стека.
if (strncmp($path, '/.claude/', 9) === 0) {
    http_response_code(404);
    echo 'Not found';
    return true;
}

if ($path !== '/' && is_file($target) && substr($path, -4) !== '.php') {
    return false;
}

if ($path === '/' || $path === '') {
    $path = '/index.php';
    $target = $root . DIRECTORY_SEPARATOR . 'index.php';
}

if (is_file($target)) {
    $_SERVER['SCRIPT_FILENAME'] = $target;
    $_SERVER['SCRIPT_NAME'] = $path;
    chdir(dirname($target));
    require $target;
    return true;
}

http_response_code(404);
echo 'Not found';
