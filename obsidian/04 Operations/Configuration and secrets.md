---
tags:
  - operations
  - security
---

# Configuration and secrets

## Three config sources

**1. `server.json`** — the database. Sits in the root, **not in git**. It's the game server's
config file; the site reads its `database` section:

```json
{ "database": { "host": "127.0.0.1", "port": 3306,
                "name": "wot_emulator", "user": "root", "password": "" } }
```

Without the file those same values are used as defaults
(`load_server_database_config()` in `db.php`).

**2. Environment variables** — mail and reCAPTCHA. Every constant follows
`getenv(...) ?: 'default'`, so the environment overrides whatever is in the file.

Discord OAuth credentials also come from the environment (`DISCORD_CLIENT_ID`,
`DISCORD_CLIENT_SECRET`); `discord_config.php` holds empty defaults. The client secret must
never be committed or shared; rotate it in the Discord Developer Portal after any exposure.
OAuth refresh tokens are stored encrypted in `account_discord_links` for automatic username sync.

**3. The `site_settings` table** — things editable from the admin panel without a deploy:
download mirrors, video, instructions. See [[Downloads and mirrors]].

## Environment variables

| Variable | File | Default |
|---|---|---|
| `EMAIL_VERIFICATION_ENABLED` | `mail_config.php` | on (disable with `0`) |
| `MAIL_FROM`, `MAIL_FROM_NAME` | `mail_config.php` | `noreply@projectorion.fun`, `Project Orion` |
| `SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USER`, `SMTP_PASS` | `mail_config.php` | empty host (falls back to `mail()`), :587 tls |
| `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY` | `recaptcha_config.php` | empty (widget disabled) |
| `DISCORD_CLIENT_ID`, `DISCORD_CLIENT_SECRET` | `discord_config.php` | empty (OAuth disabled) |

See [[Email and codes]].

> [!danger] The old reCAPTCHA, Brevo and Discord secrets were committed — treat them as burned
> Those keys sat in the tracked config files and are in the commit history of any clone made
> before the 2026-09-02 cleanup. The files now hold empty defaults, but rotating the keys at the
> providers is still required.
>
> Setting up a deployment:
> 1. create a reCAPTCHA pair at https://www.google.com/recaptcha/admin
> 2. set `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY` in the environment
> 3. do the same for `SMTP_*` and the Discord client ID/secret
>
> `RECAPTCHA_ENABLED` becomes `false` if either key is empty; `verify_recaptcha()` then skips
> the check and writes to `error_log`. That fail-open is deliberate — otherwise missing keys
> would block login, registration and bug reports entirely.

## `.gitignore`

Ignored: `server.json` (database secrets), `.env`, `*.local.php`, `uploads/*` (except the empty
`uploads/news/123.txt` placeholder that keeps the directory in git), `video/`, `.superpowers/`,
Obsidian's local workspace state, and OS junk.

> [!note] `docs/superpowers/` is deliberately NOT ignored
> The redesign spec and plan are already under version control and are useful project history.
> Adding them to `.gitignore` would be both pointless (git keeps tracking already-tracked files)
> and misleading.

> [!warning] That same rule bit us here — `.gitignore` alone does nothing to tracked files
> `obsidian/.obsidian/workspace.json` sat in `.gitignore` for a long time while still being
> tracked, so it kept showing up modified on every vault open. Listing a path is only half the
> job; the other half is `git rm --cached <path>`, which unstages it but leaves it on disk.
> Fixed 2026-07-19 for `workspace.json` and `graph.json`.

## Environment mode

`db.php` distinguishes local development from production by the request:

| Function | What it detects |
|---|---|
| `orion_is_https()` | `$_SERVER['HTTPS']`, `X-Forwarded-Proto`, `CF-Visitor`, port 443 |
| `orion_is_local()` | `localhost`, `127.0.0.1`, `::1`, `*.local`, `*.test` |

Three things depend on this: the session cookie's `secure` flag, HSTS, and error display.
Locally `display_errors` is on; in production it's off (stack traces expose paths, SQL and
connection parameters). `log_errors` is always on.

## Not in git but required to run

| Path | Where it comes from |
|---|---|
| `server.json` | the game server's config |
| `uploads/news/` | admin panel uploads ([[News]]) |
| the `wot_emulator` database | created by the game server |

## Domain and SEO

`seo_head()` in `db.php` hardcodes the base `https://projectorion.fun/` — canonical URL,
OG tags, Twitter Card, and the `images/banner.png` banner. Changing the domain means editing
there, plus `sitemap.xml` and `robots.txt`.

Related: [[Security]], [[Local setup]], [[Request lifecycle]]
