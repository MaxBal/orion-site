<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

function localization_source(string $path): string
{
    global $root;
    $full = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    $content = file_get_contents($full);
    if ($content === false) {
        throw new RuntimeException("Cannot read {$path}");
    }
    return $content;
}

function localization_check(bool $condition, string $message): void
{
    global $failures;
    if (!$condition) {
        $failures[] = $message;
    }
}

require_once $root . '/lang.php';

$lang_source = localization_source('lang.php');
$catalog = i18n_locale_catalog();
localization_check(function_exists('current_lang'), 'current_lang API is missing');
localization_check(function_exists('i18n_locale_code'), 'Locale whitelist API is missing');
localization_check(array_keys($catalog) === ['ru', 'uk', 'en'], 'Locale catalog must contain exactly ru, uk and en');
localization_check(i18n_locale_code(' EN ') === 'en', 'Locale codes must be normalized and whitelisted');
localization_check(i18n_locale_code('invalid', null) === null, 'Invalid locale must be rejectable without a fallback');

$_SESSION = ['lang' => 'uk'];
localization_check(current_lang() === 'uk', 'current_lang must return the selected Ukrainian locale');
$_SESSION = ['lang' => 'en'];
localization_check(current_lang() === 'en', 'current_lang must return the selected English locale');

$en_map = i18n_en_map();
$english_values = array_filter($en_map, static function ($value): bool {
    return is_string($value) && preg_match('/[A-Za-z]/', $value) === 1;
});
localization_check(str_contains($lang_source, 'function i18n_en_map()'), 'English translation map API is missing');
localization_check(is_array($en_map) && $english_values !== [], 'English translation map must contain English values');
localization_check(str_contains($lang_source, "function i18n_switcher_html(\$placement = 'header')"), 'Locale switcher API is missing');

$_GET = ['section' => 'plans'];
$_SERVER['REQUEST_URI'] = '/subscriptions.php?section=plans';
$switcher = i18n_switcher_html('header');
localization_check(substr_count($switcher, 'lang-switch-option') === 3, 'Locale switcher must expose all three locales');
foreach (['lang=ru', 'lang=uk', 'lang=en'] as $locale_query) {
    localization_check(str_contains($switcher, $locale_query), "Locale switcher is missing {$locale_query}");
}
localization_check(str_contains($switcher, 'aria-current="true"'), 'Locale switcher must mark the active locale');

$localized_path = i18n_locale_path('subscriptions.php?section=plans&lang=ru#max', 'en');
localization_check(
    str_contains($localized_path, 'section=plans')
        && str_contains($localized_path, 'lang=en')
        && substr_count($localized_path, 'lang=') === 1
        && str_ends_with($localized_path, '#max'),
    'Locale URL helper must preserve other query parameters and fragments'
);

foreach (['js/site.js', 'js/theme.js', 'js/admin.js', 'js/players.js', 'js/markets.js'] as $path) {
    localization_check(
        preg_match('~\ben\s*:\s*\{~', localization_source($path)) === 1,
        "English JS dictionary is missing in {$path}"
    );
}

localization_check(str_contains($lang_source, 'function i18n_protect_ignored_html'), 'Authored HTML protection helper is missing');
localization_check(str_contains($lang_source, 'data-i18n-ignore'), 'data-i18n-ignore marker is not recognized');
localization_check(str_contains($lang_source, 'translate\\s*=\\s*'), 'translate=no marker is not recognized');
localization_check(str_contains($lang_source, 'notranslate'), 'notranslate class marker is not recognized');
foreach (['index.php', 'bugs.php', 'bug_view.php'] as $path) {
    $page_source = localization_source($path);
    localization_check(
        str_contains($page_source, 'data-i18n-ignore') || str_contains($page_source, 'translate="no"'),
        "Authored content markers are missing in {$path}"
    );
}

$phrase = null;
foreach ($en_map as $source_phrase => $translated_phrase) {
    if (is_string($source_phrase) && trim($source_phrase) !== '' && !str_contains($source_phrase, '<')) {
        $phrase = $source_phrase;
        break;
    }
}
if ($phrase !== null) {
    $protected_html = '<p data-i18n-ignore>' . $phrase . '</p>'
        . '<p translate="no">' . $phrase . '</p>'
        . '<p class="notranslate">' . $phrase . '</p>'
        . '<p>' . $phrase . '</p>';
    $translated_html = i18n_translate_html($protected_html, 'en');
    localization_check(str_contains($translated_html, '<p data-i18n-ignore>' . $phrase . '</p>'), 'data-i18n-ignore content must remain authored');
    localization_check(str_contains($translated_html, '<p translate="no">' . $phrase . '</p>'), 'translate=no content must remain authored');
    localization_check(str_contains($translated_html, '<p class="notranslate">' . $phrase . '</p>'), 'notranslate content must remain authored');
    localization_check(!str_contains($translated_html, '<p>' . $phrase . '</p>'), 'Unprotected content must remain translatable');
} else {
    localization_check(false, 'English map has no simple phrase for protection runtime coverage');
}

$admin_labels = i18n_translate_html(
    '<nav><span>Модерация</span><span>Журнал действий</span><span>КОМАНДА</span></nav>'
        . '<small>Категория начинается с <b>## эмодзи Название</b>. Каждый пункт — <b>Заголовок | Описание</b>.</small>'
        . '<span>Сервер онлайн · версия 0.8.2</span>',
    'en'
);
localization_check(
    str_contains($admin_labels, 'Moderation')
        && str_contains($admin_labels, 'Audit log')
        && str_contains($admin_labels, 'TEAM')
        && str_contains($admin_labels, 'Category starts with <b>## emoji Name</b>. Each item — <b>Title | Description</b>.')
        && str_contains($admin_labels, 'Server online · version 0.8.2'),
    'English admin labels are incomplete'
);

$notification_labels = i18n_translate_html(
    '<span>Уведомления</span><span>Уведомления для команды</span><span>Новое голосование</span><span>Новое предложение игрока</span><button>Прочитать уведомления</button><p>Все уведомления прочитаны.</p>',
    'en'
);
localization_check(
    str_contains($notification_labels, 'Notifications')
        && str_contains($notification_labels, 'Staff notifications')
        && str_contains($notification_labels, 'New vote')
        && str_contains($notification_labels, 'New player proposal')
        && str_contains($notification_labels, 'Mark notifications as read')
        && str_contains($notification_labels, 'All notifications are read.'),
    'English staff notification labels are missing'
);

$db_source = localization_source('db.php');
localization_check(str_contains($db_source, 'i18n_locale_meta($lang)'), 'SEO must resolve locale metadata');
localization_check(str_contains($db_source, 'i18n_locale_urls($path)'), 'SEO must emit locale-aware URLs');
localization_check(str_contains($db_source, 'hreflang'), 'SEO alternate locale links are missing');
localization_check(str_contains($db_source, "'inLanguage' => \$lang"), 'SEO schema language hook is missing');
localization_check(
    str_contains($lang_source, "stripos(\$h, 'content-type:')")
        && str_contains($lang_source, "stripos(\$h, 'text/html') === false")
        && !str_contains($lang_source, 'application/pdf')
        && !str_contains($lang_source, 'application/json'),
    'Output filter must use a generic non-HTML guard, not a PDF/JSON-specific claim'
);
localization_check(i18n_output_filter('{"label":"payload"}') === '{"label":"payload"}', 'Output filter must not translate a non-document payload');

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: {$failure}\n");
    }
    exit(1);
}

fwrite(STDOUT, "Localization checks passed.\n");
