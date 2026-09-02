---
tags:
  - operations
  - security
---

# Security

An overview: what's already in place, and which trade-offs were made deliberately.

## What works

### Headers

`security_headers()` in `db.php` sets these on **every** page:

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()
Content-Security-Policy: default-src 'self'; ...
```

The CSP allows scripts from `self` plus Google (reCAPTCHA), styles from `self` plus Google Fonts,
images from `self` and `data:`, and frames only from `google.com`. It also sets
`frame-ancestors 'none'`, `form-action 'self'` and `base-uri 'self'`.

> [!note] `'unsafe-inline'` in `script-src`
> Present on purpose: inline `<script>` blocks exist in the templates (JSON-LD on the homepage,
> reCAPTCHA init). Removing it requires moving everything to nonces or hashes.

### HTTPS

`orion_is_https()` detects TLS four ways: `$_SERVER['HTTPS']`, `X-Forwarded-Proto`,
`CF-Visitor` (Cloudflare), and port 443. On HTTPS the site additionally sends:

- `Strict-Transport-Security: max-age=31536000; includeSubDomains`
- the CSP directive `upgrade-insecure-requests`
- the session cookie with `secure`

None of these are sent over plain HTTP — otherwise the browser would remember a redirect to
`https://localhost` and local development would stop working.

### Session

Cookie: `httponly`, `samesite=Lax`, `lifetime=0`, and `secure` matching the protocol.
After a successful login, `session_regenerate_id(true)` (session-fixation protection).

### CSRF

A 32-byte token in the session, compared with `hash_equals()`. Three checkpoints —
`verify_csrf()`, `require_csrf()`, `require_form_csrf()`. See [[Code conventions]].

### SQL

Prepared statements only, with `EMULATE_PREPARES => false` — so parameterization is real,
at the MySQL protocol level, not string interpolation.

### XSS

Every dynamic value passes through `h()` / `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
News bodies render as `nl2br(h(...))`, so author-supplied HTML never executes.

### Access control

A two-level model: permission plus rank, with self-targeting always forbidden. Permissions are
re-read from the database on every request. See [[Roles and permissions]].

`save_staff_access` additionally refuses to **grant** a permission the actor does not hold —
otherwise a role with `staff.manage` could hand a subordinate access it lacks itself and then
use that account. Revoking (`deny`) stays unrestricted.

### Redirects

`orion_safe_redirect_path()` normalises the path used by the invalid-`?lang=` redirect: leading
`//` or `/\` (which browsers read as an absolute address on another host) collapse to a single
slash, and CR/LF/NUL reset the path to `/`. `notifications.php` validates its own `return_to`
the same way.

### The client's IP is not taken on trust

`get_client_ip()` reads `CF-Connecting-IP` / `X-Forwarded-For` **only** when `REMOTE_ADDR` is a
known proxy: loopback, private ranges, or a published Cloudflare range (`ORION_TRUSTED_PROXIES`
overrides the list with comma-separated CIDRs). Anything else falls back to `REMOTE_ADDR`, and
every value is validated with `filter_var(..., FILTER_VALIDATE_IP)`.

Without that check, a single request header would forge the address — bypassing IP bans, the
login throttle and the one-account-per-IP rule, and writing someone else's address into the
moderation log.

> [!warning] A reverse proxy that is neither Cloudflare nor private must be listed
> Otherwise every visitor arrives as the proxy's own address, and one-account-per-IP blocks
> all registrations. Set `ORION_TRUSTED_PROXIES`.

### Anti-spam and anti-brute-force

- reCAPTCHA v2 on login, registration and report creation
- login and registration attempt counters in the `auth_attempts` table, keyed by scope + IP
  (10 login / 5 registration failures, 15-minute window). They used to live in `$_SESSION`,
  where dropping the cookie reset the counter
- one account per IP at registration
- disposable-email blocklist with subdomain support
- confirmation codes: TTL, resend cooldown, 6-attempt cap, `hash_equals`
- bug report creation throttle
- bans enforced on every page, not just at login

### Error output

In production `display_errors` is off — stack traces would expose paths, SQL and database
credentials. `log_errors` is always on. Locally errors are visible.
See [[Configuration and secrets]].

### File delivery

`patch/get.php` works from an allowlist of two keys. It never builds a path from the parameter,
so path traversal isn't possible.

## Accepted trade-offs

> [!danger] Passwords are unsalted SHA-256
> `hash('sha256', $password)` — no salt, no stretching. This is a **compatibility requirement**
> with the game server, which verifies the same column during BigWorld login.
>
> It can only change in lockstep with the emulator. Changing it unilaterally breaks game login
> for everyone. Details: [[Authentication]].

> [!danger] Three secrets live in tracked config files
> The reCAPTCHA secret (`recaptcha_config.php`), the Brevo SMTP key (`mail_config.php`) and the
> Discord client secret (`discord_config.php`) are committed as literals, and they are in commit
> history as well. This is accepted **only because the repository is private**: the moment it is
> opened, mirrored, or shared with anyone outside the project, all three are compromised and must
> be rotated — see [[Configuration and secrets]].
>
> Two consequences worth remembering: the Brevo key sends mail as the project (phishing), and
> `discord_token_key()` derives the encryption key for stored OAuth tokens from
> `DISCORD_CLIENT_SECRET`, so anyone with the repo plus a database dump can decrypt them.

## Open items

- deleted news leave their files behind in `uploads/news/` ([[News]])
- roles, permissions and ranks are covered by tests, but the admin panel's SQL logic is not
  ([[Tests]])
- `reg_ip` doubles as "last site IP": login overwrites it, so an account that signs in from a
  new address frees its old one for another registration, and the original address is lost for
  moderation. Splitting registration IP from last-seen IP needs a new column plus admin-view
  changes ([[Bans]])
- password comparison is `hash('sha256', …) === $stored` — not constant-time. Exploiting the
  timing difference over the network is impractical, and the hash itself is fixed by the game
  server ([[Authentication]])

Related: [[Roles and permissions]], [[Authentication]], [[Configuration and secrets]]
