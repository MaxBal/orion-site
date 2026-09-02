# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Server-rendered PHP 8 site + admin panel for **Project Orion**, a game server emulator (version 0.8.2).
No framework, no build step, no package manager. Files are served directly; edits are live on reload.

The site **shares its MySQL database with the game emulator** (`wot_emulator`). Tables like `accounts`,
`dossier`, `battles`, and `bans` are written and read by the Python game server too. This constrains
several design choices — most notably password hashing (see Constraints).

## Commands

PHP is **not on PATH**. Use the absolute binary of whichever stack is installed — this machine
currently has Laragon (`C:/laragon/bin/php/php-8.3.33-Win32-vs16-x64/php.exe`), not XAMPP.
`.claude/launch.json` points at the same binary:

```bash
# Syntax check a file
C:/xampp/php/php.exe -l admin.php

# Tests (all four must pass before committing)
C:/xampp/php/php.exe tests/ui_contract_test.php          # structural contract test, 11 groups
C:/xampp/php/php.exe tests/ui_contract_test.php --group=admin-shell   # single group
C:/xampp/php/php.exe tests/staff_permissions_test.php    # RBAC hierarchy unit test
node tests/site_interactions_test.js                     # site.js against a fake DOM
node tests/admin_mirror_lifecycle_test.js                # admin.js mirror rows
```

Node is **not installed** on this machine, so the two `node` suites cannot run locally — treat
`js/site.js` and `js/admin.js` as covered only by review until Node is available.

Test group names in `ui_contract_test.php`: `harness`, `shared`, `home`, `content`, `theme`,
`interactions`, `account`, `bugs`, `admin-shell`, `admin-workspaces`, `staff-rbac`.

### Running the site locally

Use the `orion-site` config in [.claude/launch.json](.claude/launch.json) via the preview tools —
never `php -S` through Bash. It starts PHP's built-in server on port 8123 with a preview-only router
(the router lives outside the repo and suppresses the donate popup / allows fake sign-in for
screenshots). MySQL must be running on 3306.

## Architecture

### `db.php` is the bootstrap — every page starts with `require_once 'db.php'`

It is not just a connection file. In order, it: starts the session; loads `lang.php`; handles the
`?lang=ru|uk|en` locale switch; stores valid locale URLs in the session while leaving the query in
the URL; 303-redirects an unknown locale to the same path without `lang`; loads `mailer.php` and
`includes/staff.php`; **auto-migrates the schema** (`ensure_site_schema()` runs `CREATE TABLE IF NOT EXISTS`
/ conditional `ALTER TABLE` on every request — there are no migration files); mints the CSRF token;
emits security headers including CSP; opens the PDO connection; refreshes staff permissions into the
session; force-logs-out banned users globally; and finally opens an output buffer with the i18n filter.

Consequence: **adding a table or column means editing `ensure_site_schema()` in `db.php`** (or
`ensure_staff_schema()` in `includes/staff.php`), guarded by `db_column_exists()`.

DB credentials come from `server.json` in the repo root (**not committed** — the game server's config
file). Falls back to `127.0.0.1:3306 / wot_emulator / root / no password`.

SMTP, Discord and reCAPTCHA credentials are **not in the repository**. `mail_config.php`,
`discord_config.php` and `recaptcha_config.php` ship with empty defaults and read every value from
the environment (`SMTP_*`, `MAIL_FROM`, `DISCORD_CLIENT_ID`, `DISCORD_CLIENT_SECRET`,
`RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`). Empty keys are handled: `RECAPTCHA_ENABLED` and
`DISCORD_OAUTH_ENABLED` become `false` and those features are skipped instead of failing. Do not
put live credentials back into these files.

### Localization is a locale-aware output system, not string keys

The supported locales are `ru`, `uk`, and `en`. `i18n_locale_catalog()` in [lang.php](lang.php) is
the source of truth for their codes, labels, switcher abbreviations, `hreflang`, Open Graph locale,
and SEO keywords. `i18n_locale_code()` is the whitelist/normalizer and `current_lang()` returns the
normalized locale stored in `$_SESSION['lang']` (default `ru`).

Valid scalar `?lang=ru|uk|en` values are normalized, persisted in the session, and intentionally
remain in the URL as separate crawlable locale URLs. Without a query parameter, the session choice
persists across requests. An unknown `lang` resets the session to `ru` and gets a 303 redirect to the
same path with that invalid parameter removed. `i18n_switch_url()` preserves the current query while
replacing `lang`; `i18n_locale_path()` and `i18n_locale_urls()` build locale-aware links for pages and
SEO, while the static sitemap mirrors the same crawlable locale URL shape.

Most shared template text is authored in Russian, but long/structured pages and some dynamic
messages have explicit locale branches. At the end of `db.php`, `ob_start('i18n_output_filter')` runs
over the completed document:

- `ru` returns HTML unchanged.
- `uk` applies `i18n_uk_map()` (RU→UK).
- `en` applies `i18n_en_map()` (RU→EN).

The filter changes the shared `<html lang>` value and calls `i18n_translate_html()` only for
document-like HTML output containing `<html` or `</body>`. It skips responses advertising a non-`text/html` content type, including admin JSON
and downloads; the PDF endpoint also clears the buffer before emitting binary bytes. `i18n_translate_text()`
is the plain-text path used where HTML replacement is not appropriate.

Do not introduce `t()`-style PHP keys for shared static copy. Add the exact Russian source phrase to
both appropriate maps when it needs both translations. `i18n_compiled()` chunks the maps, sorts
long phrases first, matches whitespace as `\s+`, and uses Cyrillic word boundaries.

Structured content is translated by code, not by hoping the phrase filter can translate variable
data. `includes/roadmap_data.php` contains explicit RU/UK/EN arrays; `index.php`, `gso.php`,
`petitions.php`, and `roadmap.php` consume locale-specific copy, while `includes/contracts.php`,
`includes/gso.php`, and `includes/petitions.php` provide explicit locale maps for messages, roles,
and statuses. `contract_pdf.php` owns its separate `?lang=uk|ru|en` parameter and renders the
selected PDF locale directly.

Browser-generated strings never reach the server filter. `js/theme.js`, `js/site.js`, `js/admin.js`,
`js/players.js`, and `js/markets.js` each carry `ru`/`uk`/`en` dictionaries and read `<html lang>`;
new JS-rendered copy must be added in all three branches.

Admin AJAX is a separate path: `POST admin.php?ajax=1` returns `application/json`, so the output
filter does not rewrite it. `json_out()` localizes top-level `error` and `message` values through
`admin_i18n_text()` (explicit UK/EN fallbacks plus `i18n_translate_text()`), while `js/admin.js`
localizes its own browser-side messages before `postAdmin()` displays the response.

Email is also outside the page buffer. Auth pages choose their copy before calling `render_code_email()`
and `send_email()`. The current template has an explicit English branch and a Russian fallback, so
English mail is English while Ukrainian mail currently remains Russian; the output filter never
changes an already-rendered email.

`seo_head()` localizes title and description with `i18n_translate_text()`, reads keywords and locale
metadata from the catalog, makes the current locale URL canonical, emits `hreflang` alternates for
`ru`, `uk`, and `en` plus `x-default`, and sets Open Graph and schema `inLanguage` values. Keep
`?lang=en` URLs crawlable when adding or documenting indexable pages.

Three things that bite:

1. **Length sorting only breaks ties at the same position.** A key that starts earlier consumes the
   text first, so a longer key starting later never fires. Fix by extending the earlier key.
2. **Phrases split by inline tags don't match** — the regex runs on raw HTML. Put the markup in the
   key: `'Мы <strong>не заявляем</strong>' => 'Ми <strong>не заявляємо</strong>'`. This is also more
   precise than translating the bare word, which would leak into authored content.
3. **Strings set from JavaScript never reach the filter.** Use the locale dictionaries in the JS
   files instead of adding browser-rendered text to a PHP map.

**Do not translate authored content**: news fields and media labels, bug-report titles/descriptions/
comments, player proposals and GSO text, usernames, contract motivations/notes, and changelog release
copy. These values come from users/admins or editorial records and are protected with
`data-i18n-ignore`, `translate="no"`, or `.notranslate`; machine substitution would produce mixed
language text.

### Shared shell

[includes/header.php](includes/header.php) and [includes/footer.php](includes/footer.php) render the
whole document. Pages set optional variables **before** requiring the header — all have defaults, so
nothing breaks if omitted:

`$page_title`, `$page_description`, `$page_path`, `$seo_index`, `$active_page`, `$head_extra`,
`$body_class`, `$show_popup`, `$page_styles[]`, `$page_scripts[]`, `$footer_extra`.

`$active_page` drives nav highlighting via `nav_active()` and becomes the `page-*` body class.
`$page_styles` / `$page_scripts` are how admin loads `admin.css` / `js/admin.js`.

### Frontend — the "Steel Front" skin

The look is a dark, angular military-industrial skin derived from the `template.html` prototype:
square corners everywhere, uppercase stencil display type, amber/gold/orange accent ramp.

- **Design tokens** live at the top of [style.css](style.css): `:root` holds the light palette,
  `:root[data-theme="dark"]` overrides it. **Dark is the default** (set in the `<html>` tag and in
  `theme.js`). Any new color must go through a `--color-*` variable or it will break one theme.
- **The skin is square.** `--radius-*` are all `0`; a cut corner comes from
  `clip-path: var(--clip-cut)` / `var(--clip-cut-sm)`, never from a radius. `border-radius: 50%`
  survives only for dots, avatars, orbits and the hero logo. Corner brackets are
  `<span class="front-corners"><i></i>×4</span>` inside a `position: relative` panel.
- **Exception to the token rule**: the hero, its stats strip and `.support-cta` sit on dark
  artwork in *both* themes, so their text colors are deliberately hardcoded hex. Leave them.
- [js/theme.js](js/theme.js) is loaded **in `<head>`, synchronously**, so the stored theme paints
  before first frame. It exposes `window.OrionTheme` and syncs across tabs via `storage` events.
- [js/site.js](js/site.js) is delegated-event based (one document-level listener per concern) and
  driven by `data-*` hooks: `data-nav-toggle`, `data-modal-open`, `data-modal-close`, `data-copy`,
  `data-scroll-to`, `data-target` (counters), `.reveal`. Adding UI usually means adding a hook, not
  new JS.
- [js/front.js](js/front.js) holds the skin's chrome — marquees (`data-front-marquee`), the
  scrolled header + `--header-height`, the hero ember canvas, the launch countdown, the
  hall-of-fame tabs. It is a **separate file on purpose**: `site.js` is verified against a fake
  DOM in `tests/site_interactions_test.js` that has no canvas, no `window.addEventListener` and
  no `requestAnimationFrame`.
- Fonts: **e-Ukraine / e-Ukraine Head** self-hosted from `fonts/` (`--font-sans`, `--font-display`)
  carry body copy; **Russo One** from Google Fonts is the display face (`--font-stencil`) on
  `h1`–`h4`, nav, buttons, tabs and badges; Roboto Mono for technical metadata (`--font-mono`).
  `h1`/`h2` are uppercase — `h3`/`h4` are not, because they hold authored text (news and bug
  report titles).
- All animations must respect `prefers-reduced-motion` — there is a global override at the top of
  `style.css`, and the contract tests check for it.

### `admin.php` — one 180KB file, two request modes

1. **AJAX**: `POST admin.php?ajax=1` with an `action` field, answered by `json_out()`. Guarded by
   `require_csrf()` + `require_ajax_permission()`. Handled in `js/admin.js` via `postAdmin()`.
2. **Form POST + redirect**: guarded by `require_form_csrf($tab)` + `require_form_permission($p, $tab)`,
   ends in `header('Location: admin.php?tab=...')` with a flash message.

The page body is a long `if ($tab === '...') elseif ...` chain. `$tab` comes from `?tab=` and is
**reset to `dashboard` if the role lacks the permission** listed in `$tab_permissions` (~line 1034).

Every mutating action must call `log_staff_action()` so it appears in the audit tab.

### Permissions ([includes/staff.php](includes/staff.php))

A permission catalog of 15 keys (`dashboard.view`, `reports.manage`, `bans.manage`, `staff.manage`, …),
seven ranked account roles (`admin` 100 → `developer` 95 → `orion_council_head` 90 → `senior_moderator` 70 → `moderator` 50
→ `content_maker` 40 → `player` 0),
plus per-account `allow`/`deny` rows in `staff_permission_overrides`. The report-only
moderation roles have fixed permissions and ignore personal overrides.

Two separate gates, don't confuse them:
- `staff_access_has($access, $perm)` — does this actor hold the permission?
- `staff_can_act_on_account($actor, $target)` — is the target **strictly lower rank**? Acting on
  yourself always returns false, at any rank.

Session copies (`$_SESSION['staff_permissions']`) are refreshed on every request by
`refresh_session_staff_access()`, so role changes take effect immediately. Legacy `is_admin = 1`
accounts are normalized to the `admin` role.

## Constraints

- **Passwords are unsalted `hash('sha256', $password)`** in `login.php` / `register.php` / admin
  password reset. This is required for compatibility with the game emulator's BigWorld login — the
  game server verifies the same column. Do not "fix" this to `password_hash()` without changing the
  emulator in lockstep.
- The `bans` table is read by the game server (`server_core/emulator_impl.py`), so its shape is a
  cross-project contract. `enforce_session_ban()` in `db.php` additionally kills web sessions for
  banned accounts or IPs on every page load.
- `tests/ui_contract_test.php` asserts on **literal source strings** (class names, attributes,
  function calls). Renaming a CSS class or an ARIA attribute will fail tests in a way that looks
  unrelated — read the failure message, it names the file and the expectation.
- Shared interface source text stays **in Russian**; UK/EN output comes from the locale maps or
  explicit locale branches. Authored and editorial content is not machine-translated.
- `changelog.php` reads published rows from `site_updates`; the project lead edits release history
  in `admin.php?tab=updates`. The original release is seeded once from
  `includes/update_history.php`.

## Environment-dependent behavior

`db.php` branches on the request, not on a config flag:

- `orion_is_https()` — checks `$_SERVER['HTTPS']`, `X-Forwarded-Proto`, `CF-Visitor`, port 443.
  Drives the session cookie's `secure` flag, the HSTS header, and CSP `upgrade-insecure-requests`.
  All three stay off on plain HTTP so local development keeps working.
- `orion_is_local()` — `localhost`, `127.0.0.1`, `::1`, `*.local`, `*.test`. Drives `display_errors`
  (on locally, off elsewhere; `log_errors` is always on).

Consequence: nothing needs toggling between dev and prod, but if you add behavior that must differ,
hang it off these two functions rather than inventing a new flag.

`get_client_ip()` honors `CF-Connecting-IP` / `X-Forwarded-For` **only** when `REMOTE_ADDR` is a
trusted proxy (loopback, private ranges, Cloudflare; override with the `ORION_TRUSTED_PROXIES`
env var, comma-separated CIDRs). Behind any other reverse proxy, add it there — otherwise every
visitor shares the proxy's address and one-account-per-IP blocks registration.

**Cache busting**: asset URLs in `includes/header.php` / `includes/footer.php` and the `$page_styles`
/ `$page_scripts` in `admin.php` carry `?v=N`. Bump it whenever you edit the corresponding CSS/JS —
users get the stale file otherwise. Most contract assertions match `\d+`, but one pins the exact
`style.css?v=N` string in `includes/header.php`; that assertion exists to force the bump, so update
it in the same commit.

## Conventions

- Escape every dynamic value: `h()` (defined per-page) or `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- All queries are prepared statements via PDO with `ATTR_EMULATE_PREPARES => false`.
- Every POST validates CSRF (`verify_csrf()` on public pages, `require_csrf()` / `require_form_csrf()`
  in admin).
- Code comments are in Russian, matching the existing files.
- `_template/` is the **design prototype** the current look was derived from — reference only,
  not loaded by the site.

## Documentation vault

`obsidian/` is an Obsidian vault holding the long-form project knowledge base — architecture,
per-domain notes, development workflow, and operational detail. Open `obsidian/` as a vault;
start at `Home.md`.

It explains the *why* behind decisions this file only states. Worth reading before non-trivial
work: `Localization.md` (three traps in the phrase-matching filter), `Request lifecycle.md`
(what `db.php` does before your page runs), `Roles and permissions.md` (the two separate
access checks).

When you change a subsystem in a way that invalidates a note there, update the note in the same
commit — `99 Meta/Maintaining this vault.md` lists which notes go stale fastest.
