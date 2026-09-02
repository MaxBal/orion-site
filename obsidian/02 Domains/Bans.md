---
tags:
  - domain
  - security
---

# Bans

> [!warning] Cross-project contract
> The `bans` table is read by the **game server** (`server_core/emulator_impl.py`) to keep banned
> players out of the game. Its structure is a shared agreement between two projects and cannot be
> changed unilaterally from the website side.

## Model

One row is one rule.

```
bans(id, ban_type ENUM('account','ip','mac'), account_id, ip, mac,
     reason, created_by, created_at)
```

Unique keys on `account_id`, `ip` and `mac` prevent duplicate rules. `reason` may be an empty
string; `created_by` records who issued it.

## Three types

| Type | Field | Note |
|---|---|---|
| `account` | `account_id` | targets one account |
| `ip` | `ip` | affects everyone at that address |
| `mac` | `mac` | enforced by the **game server only**; the site never sees MACs |

## Two IPs per account

A distinction worth remembering:

- **`accounts.reg_ip`** — IP of the last **website** login (updated in `login.php`)
- **`accounts.last_ip`** — IP of the last **game** login, written by the game server

An IP ban in the admin panel is usually issued together with the game IP, so the player can't
route around it through the client.

## Checking

`find_active_ban($pdo, $account_id, $ip)` in `db.php` checks both conditions in one query
with `OR`:

```sql
SELECT reason FROM bans
WHERE (ban_type = 'account' AND account_id = ?)
   OR (ban_type = 'ip' AND ip = ?)
LIMIT 1
```

It returns the **reason** (possibly an empty string) or `null`. An empty string is still a ban,
so test with `!== null`, not for truthiness.

`get_client_ip()` resolves the address in this order: `CF-Connecting-IP` → first entry in
`X-Forwarded-For` → `REMOTE_ADDR`. So behind Cloudflare the address is correct.

## Two enforcement points

**1. Login** — `login.php` checks the ban **before** verifying the password, so a banned user
gets no signal about whether the password was right.

**2. Every page** — `enforce_session_ban($pdo)` in `db.php`:

```
no user_id               → return
session_is_staff()       → return (staff are never auto-banned out)
find_active_ban() = null → return
otherwise → clear $_SESSION, kill the cookie, session_destroy(),
            redirect to login.php?error=banned
```

`login.php` and `logout.php` are excluded to avoid a redirect loop.

> [!note] Why both
> The login check alone wouldn't help against an already-logged-in player. The global check
> handles exactly that case: the ban takes effect on their very next click.

## Admin panel

The `bans` tab. `bans.manage` (issue) and `bans.unban` (lift) are **separate** permissions,
and either one opens the tab.

AJAX actions: `ban_account`, `bulk_ban_accounts`, `ban_ip`, `ban_mac`, `unban`.

Before banning an account, `require_account_below_actor()` runs — you can't ban yourself or a
staff member of equal/higher rank. `require_ip_below_actor()` does the same for IPs.
See [[Roles and permissions]].

All actions are audited: `ban.create`, `ban.bulk`, `ban.remove`.

## Separate: report bans

`accounts.is_banned_reports` is **not** an account ban — it only forbids creating bug reports.
It lives entirely outside the `bans` table. See [[Bug tracker]].

Related: [[Authentication]], [[Database schema]], [[Admin panel]]
