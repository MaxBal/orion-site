---
tags:
  - architecture
  - i18n
---

# Localization

> [!important] Not the usual approach
> The site supports **Russian (`ru`)**, **Ukrainian (`uk`)**, and **English (`en`)**. There are
> no PHP translation keys or shared `t()` call. Most shared template copy is authored in Russian
> and the completed HTML is rewritten for `uk` or `en`; long structured data and selected dynamic
> messages use explicit locale branches instead.

## Locale selection

`db.php` starts the session, loads `lang.php`, and processes the locale before the page renders.
`i18n_locale_catalog()` is the source of truth for the supported codes and their metadata:

| Code | Label | Switcher | `hreflang` | Open Graph |
|---|---|---|---|---|
| `ru` | Русский | RU | `ru` | `ru_RU` |
| `uk` | Українська | УК | `uk` | `uk_UA` |
| `en` | English | EN | `en` | `en_US` |

Each catalog entry also contains locale-specific SEO keywords. `i18n_locale_code()` normalizes
and whitelists values; `current_lang()` returns the normalized `$_SESSION['lang']` value and
defaults to `ru`.

- A valid scalar `?lang=ru|uk|en` is stored in the session and intentionally remains in the URL.
  These are separate crawlable locale URLs.
- Without `?lang=`, the stored session locale persists across requests.
- An unknown or non-scalar `lang` resets the session to `ru` and receives a 303 redirect to the
  same path with the invalid `lang` parameter removed.
- `i18n_switch_url()` preserves the current query while replacing `lang`.
- `i18n_locale_path()` replaces an existing `lang`, preserves other query parameters and fragments,
  and emits `lang=uk` or `lang=en` for non-Russian paths.
- `i18n_locale_urls()` returns one path for every catalog locale; SEO uses it directly and the
  static sitemap mirrors its crawlable URL shape.

## HTML filter

The request ends with `ob_start('i18n_output_filter')` in `db.php`.

1. The page emits its server-rendered document, mostly using Russian shared source text.
2. The callback ignores buffers that advertise a non-`text/html` content type, such as admin JSON
   and downloads, and ignores non-document fragments without `<html` or `</body>`.
3. `ru` returns unchanged HTML.
4. `uk` changes the document language to `uk` and applies `i18n_uk_map()`.
5. `en` changes the document language to `en` and applies `i18n_en_map()`.

`i18n_translate_html()` protects marked authored blocks before replacing phrases. The PDF endpoint
clears the buffer before emitting `application/pdf`, so binary bytes never pass through the filter.
`i18n_translate_text()` is the plain-text path for messages and metadata where HTML replacement is
not appropriate.

`i18n_compiled()` selects the requested map, sorts keys by descending length, splits them into
bounded regex chunks, normalizes whitespace, and caches the compiled chunks for the request.
Matches use `\s+` for whitespace and Cyrillic word boundaries. Temporary placeholders prevent a
short phrase in a later chunk from translating part of a longer phrase already matched.

## Explicit locale branches

The phrase filter is not a substitute for translating structured or variable data.

- `includes/roadmap_data.php` contains ready-made RU/UK/EN arrays; `index.php` and `roadmap.php`
  consume the selected data.
- `index.php`, `gso.php`, `petitions.php`, and other long pages select locale-specific copy before
  rendering.
- `includes/contracts.php`, `includes/gso.php`, and `includes/petitions.php` contain explicit
  RU/UK/EN maps for messages, role labels, status labels, and other dynamic values.
- `contract_pdf.php` owns its own `?lang=uk|ru|en` parameter, renders the selected PDF locale,
  and does not use the session locale redirect or HTML output filter.

When adding long structured content or a dynamic message, provide the needed `ru`, `uk`, and `en`
branches in the code that owns that data. Do not put variable database values into a phrase map.

## JavaScript

The server filter runs before the browser executes JavaScript, so text inserted into the DOM later
cannot be translated by PHP. These files each have `ru`, `uk`, and `en` dictionaries and select a
branch from `<html lang>`:

| File | Browser-generated copy |
|---|---|
| `js/theme.js` | theme toggle labels |
| `js/site.js` | mobile navigation labels and copy feedback |
| `js/admin.js` | admin controls, confirmations, and notices |
| `js/players.js` | player ranking counters |
| `js/markets.js` | market UI, states, errors, and labels |

New JS-rendered strings must be added to all three dictionaries. Adding them to `lang.php` cannot
work because they do not exist in the server output buffer.

## AJAX and email

Admin AJAX is deliberately outside the HTML filter. `POST admin.php?ajax=1` sets
`Content-Type: application/json`; `json_out()` localizes only top-level `error` and `message` values
with `admin_i18n_text()`. That helper uses explicit Ukrainian/English fallbacks and
`i18n_translate_text()`. `js/admin.js` then uses its own three-locale dictionary while
`postAdmin()` parses and displays the JSON response.

Email is also outside the page buffer. Auth pages choose the subject and body copy before calling
`render_code_email()` and `send_email()`. `render_code_email()` has an explicit English branch and
a Russian fallback; English mail is English, while Ukrainian mail currently remains Russian. The
HTML output filter never rewrites an already-rendered email.

## SEO

`seo_head()` uses the active locale to:

- translate the title and description through `i18n_translate_text()`;
- load keywords and `og_locale` from `i18n_locale_catalog()`;
- make the current locale URL canonical;
- emit `hreflang="ru"`, `hreflang="uk"`, and `hreflang="en"` alternates plus `x-default`;
- emit the matching Open Graph locale values and schema `inLanguage`.

The base URL is the Russian variant. `?lang=en` and `?lang=uk` URLs remain intentional crawlable
alternates, and the sitemap preserves each base URL while listing the English variants.

## Three traps

### 1. Length sorting only breaks ties at the same position

Sorting by length resolves conflicts for matches starting at the **same offset**. If a different
key starts earlier, it consumes the text first and your longer key never fires.

This actually happened with `устанавливает <strong>самостоятельно</strong>`: an earlier key
covering `Для подключения к серверу используется клиент, который пользователь приобретает и
устанавливает` swallowed the text first. The fix was extending **that** key, not adding a new one.

### 2. Phrases split by inline tags don't match

The regex runs on raw HTML, so a phrase with `<strong>` in the middle won't match a key without it.
On `legal.php` this produced mixed sentences like "Мы не заявляємо".

Put the markup **in the key** and add the corresponding UK and EN values:

```php
'Мы <strong>не заявляем</strong>' => 'Ми <strong>не заявляємо</strong>',
// Add the same Russian source phrase to i18n_en_map() with its English value.
```

This is also more precise than translating the bare word — the key is pinned to one place and
won't touch the same words inside database-authored content.

### 3. Strings set from JavaScript never reach the filter

Use the locale dictionaries in the JS files. Do not add browser-rendered text to a PHP map.

## Authored content exclusion

Machine translation must not alter free-form or authored values. Current protected content includes:

- news titles, summaries, bodies, and media labels;
- bug-report titles, descriptions, comments, usernames, and related authored values;
- player proposals and GSO proposal text, names, and notes;
- contract motivations, decision notes, and termination reasons;
- changelog release copy and other editorial records.

These values are stored in the database or maintained as editorial records. Templates protect them
with `data-i18n-ignore`, `translate="no"`, or the `.notranslate` class. `i18n_protect_ignored_html()`
removes the marked element ranges before phrase replacement and restores them afterward. Without
protection, a common Russian phrase inside a user paragraph could produce mixed-language content.

## Adding a translation

For shared static copy, add the exact Russian source phrase to both maps when both translations are
needed:

```php
// lang.php
// inside i18n_uk_map()
'Exact Russian phrase from the page' => 'Exact Ukrainian phrase',
```

```php
// inside i18n_en_map()
'Exact Russian phrase from the page' => 'Exact English phrase',
```

The source phrase must match the raw HTML, including inline tags. For JS-rendered copy, update all
three JS dictionaries. For structured data or dynamic messages, update the explicit locale branch
instead of the phrase maps.

## Relevant functions

| Function | What it does |
|---|---|
| `i18n_locale_catalog()` | supported locale metadata |
| `i18n_locale_code($lang, $fallback)` | locale normalization and whitelist |
| `current_lang()` | normalized session locale (`ru`, `uk`, or `en`) |
| `i18n_uk_map()` | RU->UK shared phrase map |
| `i18n_en_map()` | RU->EN shared phrase map |
| `i18n_translate_html($html, $lang)` | protected HTML translation |
| `i18n_translate_text($text, $lang)` | plain-text translation |
| `i18n_locale_path($path, $lang)` | locale-aware relative path |
| `i18n_locale_urls($path)` | one path per supported locale |
| `i18n_switch_url($lang)` | current request URL with the selected `lang` |
| `i18n_switcher_html($placement)` | three-locale switcher for `header` or `admin` |
| `i18n_output_filter($buffer)` | output-buffer callback |

The switcher is embedded in `includes/header.php` for public pages and in the `admin.php` topbar.
Its styling lives in `style.css` (`.lang-switch`) so it inherits the site theme.

Related: [[Request lifecycle]], [[Design system]]
