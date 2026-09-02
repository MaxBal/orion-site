---
tags:
  - architecture
  - admin
---

# Admin panel

`admin.php` is a single ~180 KB file holding both the backend handlers and the entire panel
markup. It's the largest file in the project; navigate it by structure, not by reading top to bottom.

## Entry

```php
require_once 'db.php';
// no session          → login.php?error=admin
// no dashboard.view   → login.php?error=admin
```

`$current_staff_access` is populated once at the top and used through the `admin_can($permission)`
helper. See [[Roles and permissions]].

## Two request modes

### 1. AJAX

```
POST admin.php?ajax=1   with an `action` field
```

Responds via `json_out()` — JSON then `exit`. Guards:

```php
require_csrf();                        // token from $_POST['csrf_token']
require_ajax_permission('users.edit'); // 403 + JSON error
```

The client half is `postAdmin()` in `js/admin.js`.

Actions in this mode (vehicles, accounts, bans): `set_global_vehicle`, `bulk_global_vehicles`,
`set_account_vehicle`, `bulk_account_vehicles`, `reset_account_overrides`, `enable_all_global`,
`save_account`, `set_password`, `set_username`, `ban_account`, `bulk_ban_accounts`, `ban_ip`,
`ban_mac`, `unban`.

### 2. Form POST plus redirect

A regular POST ending in `header('Location: admin.php?tab=...')` with a flash message. Guards:

```php
require_form_csrf($tab);
require_form_permission('news.manage', $tab);
```

Actions: `save_staff_access`, `update_bug_report`, `delete_bug_report`, `save_downloads`,
`save_news`, `delete_news_media`, `delete_news`, `save_update`, `delete_update`,
`set_server_status`, `update_gso_implementation`.

> [!tip] Which mode to pick
> A targeted change with no reload (a toggle, a button in a table) → AJAX.
> A complex form, especially with file uploads → form plus redirect.

## Tabs

`$tab` comes from `?tab=`. The `$tab_permissions` map (around line 1034) defines the permission
each tab needs. **If the permission is missing, `$tab` silently falls back to `dashboard`**
rather than returning 403.

`bans` is the exception: either `bans.manage` **or** `bans.unban` grants access.

| Tab | Permission | Contents |
|---|---|---|
| `dashboard` | `dashboard.view` | stats, activity chart |
| `reports` | `reports.manage` | [[Bug tracker]] |
| `users` | `users.view` | account search and editing |
| `bans` | `bans.manage` / `bans.unban` | [[Bans]] |
| `news` | `news.manage` | [[News]] |
| `updates` | `updates.manage` | database-backed patch history |
| `server` | project lead only | public online/offline status |
| `vehicles` | `vehicles.manage` | [[Vehicle access]] |
| `downloads` | `downloads.manage` | [[Downloads and mirrors]] |
| `implement` | project lead only | accepted [[GSO]] decisions |
| `staff` | `staff.view` | [[Roles and permissions]] |
| `audit` | `audit.view` | action log |

The page body is a long `if ($tab === '...') elseif (...)` chain. Menu items are wrapped in
`admin_can()` too, so users never see tabs they can't open.

## Auditing is mandatory

Every mutating action must call:

```php
log_staff_action($pdo, 'news.save', 'news', $id, 'Short summary', ['meta' => 'data']);
```

Otherwise the action won't appear in the audit tab. Human-readable names live in
`staff_action_label()` in `includes/staff.php`; when you add a key, add its label too.

## Presentation

The admin panel hides the public header and renders its own sidebar and topbar:

```php
$page_styles  = ['admin.css?v=...'];
$page_scripts = ['js/admin.js?v=...'];
$body_class   = 'is-admin';
```

The `is-admin` class also disables the public language switcher in the header — the admin panel
has its own in the topbar (`i18n_switcher_html('admin')`).

## Working in a file this large

- Find a handler: `grep -n "action === 'name'" admin.php`
- Find a tab's markup: `grep -n "\$tab === '" admin.php`
- After editing, **always**: `C:/xampp/php/php.exe -l admin.php`
- Tab tests: `--group=admin-shell` and `--group=admin-workspaces` — see [[Tests]]

Related: [[Roles and permissions]], [[Security]], [[Design system]]
