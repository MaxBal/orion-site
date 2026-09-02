<?php
// This endpoint owns the `lang` query parameter. Prevent db.php from treating it
// as the site's HTML language switch and redirecting to a URL without it.
define('ORION_SKIP_LANGUAGE_REDIRECT', true);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/contract_pdf.php';

$public_id = strtolower(trim((string)($_GET['id'] ?? '')));
$lang = (string)($_GET['lang'] ?? 'uk');
if (!in_array($lang, ['uk', 'ru', 'en'], true)) {
    $lang = 'uk';
}

$contract = contract_find_public($pdo, $public_id);
if (!$contract) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Contract not found.';
    exit;
}

try {
    $pdf = contract_pdf_render($contract, $lang);
} catch (Exception $e) {
    error_log('Contract PDF generation error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Unable to generate the contract PDF.';
    exit;
}

$filename = preg_replace('/[^A-Za-z0-9_.-]/', '-', (string)$contract['contract_number']) . '-' . $lang . '.pdf';
// db.php starts the HTML translation buffer. Binary PDF bytes must bypass it.
while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdf));
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: public, max-age=300, must-revalidate');
header('X-Content-Type-Options: nosniff');
echo $pdf;
