<?php
require_once 'db.php';

orion_forget_remember_token($pdo);
$logout_locale = function_exists('current_lang') ? current_lang() : 'ru';
orion_destroy_current_session();
header('Location: ' . i18n_locale_path('index.php', $logout_locale));
exit;
?>
