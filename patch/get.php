<?php
$files = [
    'config'     => 'scripts_config.xml',
    'play_linux' => 'play.sh',
];

$key = $_GET['f'] ?? '';
if (!isset($files[$key])) {
    http_response_code(404);
    exit('Not found');
}

$name = $files[$key];
$path = __DIR__ . '/' . $name;
if (!is_file($path)) {
    http_response_code(404);
    exit('Not found');
}

$data = file_get_contents($path);
$data = preg_replace('/^\xEF\xBB\xBF/', '', $data);
$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
if ($ext === 'sh') {
    $data = str_replace("\r\n", "\n", $data);
} else {
    $data = str_replace("\r\n", "\n", $data);
    $data = str_replace("\n", "\r\n", $data);
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . strlen($data));
header('Cache-Control: no-store');
echo $data;
exit;
