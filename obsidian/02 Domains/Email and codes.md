---
tags:
  - domain
  - security
---

# Email and codes

Files: `mailer.php` (delivery), `mail_config.php` (settings). The code functions live in `db.php`.

## Delivery

`mail_config.php` holds constants, each read **from the environment first**:

```php
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp-relay.brevo.com');
```

So in production these should be set as environment variables rather than edited in the file.

| Constant | Purpose |
|---|---|
| `EMAIL_VERIFICATION_ENABLED` | master switch for email confirmation |
| `MAIL_FROM`, `MAIL_FROM_NAME` | sender (must be verified with the provider) |
| `SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USER`, `SMTP_PASS` | SMTP |
| `MAIL_CODE_TTL_MIN` | code lifetime, default 15 min |
| `MAIL_CODE_RESEND_SEC` | pause between code requests, default 60 s |

**An empty `SMTP_HOST` falls back to PHP's `mail()`.** The default provider is Brevo
(300 messages/day free); the file's comments include setup steps and an alternative (Mailjet).

> [!warning] `EMAIL_VERIFICATION_ENABLED`
> Defined as `getenv(...) !== '0'` — so it's **on by default**. Until SMTP is configured this
> blocks login for every new account. Disable it with `EMAIL_VERIFICATION_ENABLED=0`.

## One-time codes

The `email_codes` table:

```
id, account_id, email, purpose ENUM('register','reset'),
code_hash CHAR(64), expires_at, attempts, created_at
```

> [!note] Only the hash is stored
> The database holds `sha256` of the code, not the code. Leaking the table doesn't let anyone
> confirm someone else's email.

### Functions in `db.php`

| Function | Behavior |
|---|---|
| `generate_email_code()` | 6 digits from `random_int`, zero-padded |
| `can_request_email_code($pdo, $email, $purpose)` | anti-spam: has `MAIL_CODE_RESEND_SEC` elapsed |
| `create_email_code($pdo, $account_id, $email, $purpose)` | **deletes older codes** for the same email+purpose, then creates one |
| `check_email_code($pdo, $email, $purpose, $code)` | verification, see below |

### Verification logic

```
take the newest code for (email, purpose)
none                             → false
expired OR attempts >= 6         → delete, false
hash_equals mismatch             → attempts + 1, false
match                            → delete the code, return ['account_id' => ...]
```

Three details that matter:

- a maximum of **6 attempts** — brute-force protection for a six-digit code;
- comparison via `hash_equals()` — constant time, no timing leak;
- **a successful code is deleted immediately** — it can't be reused.

## Where it's used

- **Registration** (`purpose = 'register'`) → `verify.php`
- **Password reset** (`purpose = 'reset'`) → `reset_password.php`
- **Login with an unconfirmed email** → `login.php` sends a fresh code itself
  (if anti-spam allows) and redirects to `verify.php`

The message template is `render_code_email($title, $text, $code)` in `mailer.php`.

## Disposable address defense

`is_disposable_email()` in `db.php` checks the domain against the list in
`disposable_email_domains.php`. The list is flipped once with `array_flip` for O(1) lookup,
and subdomains are checked by walking up:
`a.b.trashmail.com` → `b.trashmail.com` → `trashmail.com`.

Related: [[Authentication]], [[Configuration and secrets]], [[Security]]
