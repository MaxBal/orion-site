# AGENTS.md

## What this is

Server-rendered PHP 8 site + admin panel for Project Orion (game server emulator v0.8.2).
No framework, no build step, no composer.json, no package.json. Every `.php` file in root is a route.
Edits are live on reload — no compilation.

## Critical constraints

- **Passwords are unsalted `sha256`** — game emulator requires this. Never "fix" to `password_hash()`.
- **`bans` table shape is a cross-project contract** — the Python game server reads it.
- **Shared interface text is Russian** — UK/EN come from locale maps in `lang.php`, not `t()` keys.
- **Do not translate authored content** (news, bug reports, usernames, GSO text) — protected with `data-i18n-ignore`.
- **`_template/` is a design prototype** — reference only, not loaded by the site.

## Commands

PHP is not on PATH. Use the absolute binary:
```bash
C:/laragon/bin/php/php-8.3.33-Win32-vs16-x64/php.exe -l admin.php   # syntax check
C:/laragon/bin/php/php-8.3.33-Win32-vs16-x64/php.exe tests/ui_contract_test.php          # 11 groups
C:/laragon/bin/php/php-8.3.33-Win32-vs16-x64/php.exe tests/ui_contract_test.php --group=admin-shell
C:/laragon/bin/php/php-8.3.33-Win32-vs16-x64/php.exe tests/staff_permissions_test.php
```

Node tests (`site_interactions_test.js`, `admin_mirror_lifecycle_test.js`) require Node.js which may not be installed.

Test groups: `harness`, `shared`, `home`, `content`, `theme`, `interactions`, `account`, `bugs`, `admin-shell`, `admin-workspaces`, `staff-rbac`.

## Architecture

### Bootstrap chain

Every page: `require_once 'db.php'` → session → lang → schema auto-migration → CSRF → security headers → PDO connection → staff permissions → ban check → output buffer with i18n filter.

**Adding a table/column** = edit `ensure_site_schema()` in `db.php` or `ensure_staff_schema()` in `includes/staff.php`, guarded by `db_column_exists()`.

### Database

PostgreSQL on Vercel (Neon). DB credentials from environment variables:
- `DB_HOST`, `DB_PORT` (default 5432), `DB_NAME`, `DB_USER`, `DB_PASS`

Schema auto-migrates on every request. No migration files.

### Localization system

Three locales: `ru`, `uk`, `en`. Default is `ru`.

- `ob_start('i18n_output_filter')` at end of `db.php` translates completed HTML.
- `ru` = unchanged, `uk` = `i18n_uk_map()`, `en` = `i18n_en_map()`.
- Filter skips non-HTML responses (admin JSON, downloads, PDF).
- JS files carry their own `ru`/`uk`/`en` dictionaries — browser strings never reach the filter.
- `seo_head()` in `db.php` hardcodes base URL — update when domain changes.

Three traps:
1. Length sorting: earlier key consumes text first, longer key starting later never fires.
2. Phrases split by inline tags don't match — put markup in the key.
3. JS-rendered strings never reach the PHP filter — use JS locale dicts.

### Shared shell

`includes/header.php` + `includes/footer.php`. Pages set variables before requiring header:
`$page_title`, `$page_description`, `$page_path`, `$seo_index`, `$active_page`, `$head_extra`,
`$body_class`, `$show_popup`, `$page_styles[]`, `$page_scripts[]`, `$footer_extra`.

### Admin panel

`admin.php` — one large file, two modes:
1. AJAX: `POST admin.php?ajax=1` with `action` field → `json_out()`
2. Form POST + redirect: `header('Location: admin.php?tab=...')`

`$tab` from `?tab=`, reset to `dashboard` if role lacks permission. Every mutating action calls `log_staff_action()`.

### Permissions

15 permission keys, 7 ranked roles (admin 100 → player 0). Two gates:
- `staff_access_has($access, $perm)` — does actor hold permission?
- `staff_can_act_on_account($actor, $target)` — is target strictly lower rank?

## Frontend

- Dark angular military-industrial skin. Square corners (`--radius-*` = 0).
- Design tokens in `style.css` `:root` / `:root[data-theme="dark"]`. Dark is default.
- `js/theme.js` loaded synchronously in `<head>`. Syncs across tabs.
- `js/site.js` — delegated events, `data-*` hooks. Adding UI = adding a hook.
- `js/front.js` — skin chrome (marquees, hero canvas, countdown). Separate file on purpose.
- Fonts: e-Ukraine (self-hosted), Russo One (Google Fonts), Roboto Mono.
- All animations respect `prefers-reduced-motion`.
- Cache busting: `?v=N` on CSS/JS URLs. Bump when editing.

## Conventions

- Escape dynamic values: `h()` or `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- All queries are prepared statements via PDO (`ATTR_EMULATE_PREPARES => false`).
- Every POST validates CSRF.
- Code comments in Russian.
- `obsidian/` is an Obsidian vault — project knowledge base. Start at `Home.md`.

## Environment

- `orion_is_https()` — drives session cookie secure flag, HSTS, CSP.
- `orion_is_local()` — drives display_errors (on locally, off elsewhere).
- `get_client_ip()` — honors CF-Connecting-IP/X-Forwarded-For only for trusted proxies.

## Vercel deployment

- `vercel.json` routes all requests to PHP serverless functions.
- Environment variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, plus optional `SMTP_*`, `RECAPTCHA_*`, `DISCORD_*`.
- Domain: `test.projectorion.top` — configure in Vercel dashboard.
