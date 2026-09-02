---
tags:
  - domain
  - rbac
  - security
---

# Roles and permissions

The whole model lives in `includes/staff.php`. It's the most carefully written part of the
project, and worth understanding fully before touching the admin panel.

## Three levels

**1. Permission catalog** — `staff_permission_catalog()`, 20 keys. Each has a group, label,
and description (all in Russian, shown in the UI).

| Group | Keys |
|---|---|
| Overview | `dashboard.view` |
| Moderation | `reports.manage`, `reports.delete` |
| Users | `users.view`, `users.edit`, `users.credentials` |
| Security | `bans.manage`, `bans.unban` |
| Content | `news.manage`, `news.delete` |
| System | `vehicles.manage`, `downloads.manage` |
| Team | `staff.view`, `staff.manage`, `audit.view` |
| Project | `updates.manage`, `server.manage` |
| GSO | `council.participate`, `council.review`, `council.implement` |

**2. Roles** — `staff_role_definitions()`. A role is a set of permissions plus a **rank**.

| Role | Rank | Summary |
|---|---:|---|
| `admin` | 100 | project lead/creator; everything |
| `developer` | 95 | council-head panel access plus vehicles and downloads |
| `orion_council_head` | 90 | council, team, moderation, and public content |
| `senior_moderator` | 70 | report status, publication, and deletion only |
| `moderator` | 50 | report status and publication only |
| `content_maker` | 40 | dashboard and news publishing |
| `player` | 0 | no panel access |

**3. Per-account overrides** — the `staff_permission_overrides` table, one row per
(account, permission) pair with `allow` or `deny`. Overrides the role.

> [!note] Admins have no overrides
> For the `admin` role overrides are **not read at all** — an administrator always has everything.

> [!note] Moderators have fixed permissions
> The `moderator` and `senior_moderator` roles ignore personal overrides. A moderator
> remains limited to `dashboard.view`, `reports.manage`, and `council.participate`; a senior
> moderator adds only `reports.delete`. Council participation is part of the fixed role so it
> cannot be removed accidentally.

All non-player staff roles include `council.participate`. The developer inherits every
admin-panel permission held by the Orion council head, plus `vehicles.manage` and
`downloads.manage`, and ranks above that role. The public GSO decision endpoint verifies the
exact role through `gso_can_decide_review()`: `orion_council_head` **or** `admin`, the project
lead. The developer's `council.review` permission alone does not grant it. Final implementation
remains restricted to the project lead.

## Two different checks

This is the most common source of confusion:

```php
staff_access_has($access, 'bans.manage')       // Does the actor hold the permission?
staff_can_act_on_account($actor, $target)      // Does the actor OUTRANK the target?
```

You almost always need both: the permission answers "what can be done", the rank answers "to whom".

**Rank rules**, baked into `staff_can_act_on_account()`:

- the actor's rank must be **strictly greater** than the target's;
- **acting on yourself is always forbidden**, even for an administrator;
- equal rank is forbidden.

`staff_can_manage_access()` is the same plus a `staff.manage` requirement.
`staff_assignable_roles()` returns only roles **below** the actor's rank, so promoting someone
to your own level is impossible.

## Session lifecycle

`refresh_session_staff_access($pdo)` runs from `db.php` on **every request** and rewrites:

```php
$_SESSION['is_admin']          // bool
$_SESSION['staff_role']        // role key
$_SESSION['staff_role_label']  // human label
$_SESSION['staff_permissions'] // array of active keys
```

> [!tip] Consequence
> Role and override changes take effect **immediately** — no re-login needed. The session is a
> cache here, not the source of truth.

For checks outside the admin panel there are `session_has_staff_permission($key)` and
`session_is_staff()` (the latter is just `dashboard.view`; used in the header and in
`enforce_session_ban` so staff aren't kicked out).

## Legacy flag compatibility

`normalize_staff_role($role, $is_admin)` returns `'admin'` when passed `is_admin = 1`, and
`ensure_staff_schema()` runs `UPDATE accounts SET staff_role = 'admin' WHERE is_admin = 1`
on every boot.

So legacy `is_admin = 1` accounts stay administrators even if they never had a `staff_role` column.

## Auditing

`log_staff_action($pdo, $key, $type, $id, $summary, $meta)` writes to `staff_action_log`:
actor, action key, target, summary, JSON metadata, IP, timestamp. Logging failures are swallowed
into `error_log` — the audit must never break the action itself.

Human-readable names live in `staff_action_label()`. A new key without a label shows up as
`key · with · dots`.

## Tests

`tests/staff_permissions_test.php` checks exactly these invariants: who can do what, who
outranks whom, whether the legacy flag still works. Plus the `--group=staff-rbac` group in the
contract test. See [[Tests]].

Related: [[Admin panel]], [[Security]], [[Database schema]]

Contract-backed assignment and automatic expiry are described in [[Team contracts]].
