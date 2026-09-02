---
tags:
  - development
---

# Local setup

## Requirements

- **XAMPP** with PHP 8.2 — `C:\xampp\php\php.exe`
- **MySQL** on port 3306 with a `wot_emulator` database
- **Node.js** — only for the two JS test suites

> [!warning] PHP is not on PATH
> Every command uses the absolute binary path:
> ```bash
> C:/xampp/php/php.exe -l admin.php
> ```
> Not `php`. This applies to tests and syntax checks alike.

## Running the site

The config lives in `.claude/launch.json` under the name **`orion-site`**: PHP's built-in server
on port **8123** with a preview router.

> [!tip] Use the preview tooling, not Bash
> The dev server is started with `preview_start` and the name `orion-site`, not `php -S`
> in a terminal.

The router lives **outside the repository** (in a scratchpad) and changes nothing about the site.
It exists for design review: it suppresses the auto-opening donation modal so screenshots show
the actual page, and it allows signing in as a test user via `?__preview_as=`. It is a review
harness only and never becomes part of the site.

If the router is missing (a fresh scratchpad), the site still comes up — the modal will just
open on the homepage.

## Database config

`load_server_database_config()` in `db.php` looks for **`server.json`** in the repository root.
The file is not in git — it's the game server's config. Shape:

```json
{ "database": { "host": "127.0.0.1", "port": 3306,
                "name": "wot_emulator", "user": "root", "password": "" } }
```

Without the file those same values are used as defaults, which is usually enough for local XAMPP.

See [[Configuration and secrets]].

## Schema

Nothing to apply by hand: `ensure_site_schema()` creates the site's tables on the first request.
But the site does **not** create the emulator's tables (`accounts`, `dossier`, `battles`) —
the game server must. On an empty database some pages show zeros and write to `error_log`,
but nothing crashes: the stats queries are wrapped in `try/catch`.

## Checking it's alive

```bash
# all routes
for p in index download changelog roadmap bugs donate login register legal; do
  curl -s -o /dev/null -w "$p %{http_code}\n" "http://localhost:8123/$p.php"
done
```

`profile.php`, `bug_view.php` and `admin.php` return **302** without a session — that's the
expected redirect to login.

## Common problems

| Symptom | Cause |
|---|---|
| "Connection failed. Please check server configuration." | MySQL isn't running, or the config is wrong |
| Blank page with an error in the log | check `preview_logs` output from the built-in server |
| Zeros in the homepage counters | the emulator's `battles` / `dossier` tables are missing |
| Donation modal opens by itself | normal without the preview router; throttled to every 30 min |
| CSS or JS changes don't show up | bump the `?v=N` suffix in `header.php` / `footer.php` |

Related: [[Tests]], [[Configuration and secrets]], [[System overview]]
