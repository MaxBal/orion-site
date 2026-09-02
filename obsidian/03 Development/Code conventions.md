---
tags:
  - development
---

# Code conventions

The goal is for new code not to stand out. This project is consistent in its own style,
and consistency here matters more than being "correct in general".

## Language

- **Comments are in Russian.** That's true across every file.
- **The interface is in Russian.** Ukrainian comes from `i18n_uk_map()`, not from the template.
  See [[Localization]].
- Identifiers, permission keys and action names are in English (`news.manage`, `save_account`).

## Mandatory in PHP

**Always escape.** Every dynamic value rendered into HTML:

```php
h($value)  // or htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
```

`h()` is defined per page, often behind `if (!function_exists('h'))`.

**Always use prepared statements.** With `EMULATE_PREPARES => false` these are real prepared
statements at the MySQL protocol level. Concatenating values into SQL is unacceptable.

For `IN (...)`, generate the placeholder set:

```php
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM t WHERE id IN ($placeholders)");
$stmt->execute($ids);
```

**CSRF on every POST.**

| Context | Call |
|---|---|
| Public page | `verify_csrf($_POST['csrf_token'] ?? '')` |
| Admin, AJAX | `require_csrf()` |
| Admin, form | `require_form_csrf($tab)` |

**Permissions on every admin action.** `require_ajax_permission()` or
`require_form_permission()`, plus a rank check with `require_account_below_actor()` wherever the
action targets another user. See [[Roles and permissions]].

**Audit every mutation.** `log_staff_action(...)`, or the action won't appear in the log.

**Wrap stats queries in `try/catch`.** The homepage must not die because an emulator table is
missing; the error goes to `error_log` and the values stay at zero.

## Page structure

Order within a file:

```php
<?php
require_once 'db.php';        // always first
// POST handling (if any) → redirect
// queries, data preparation
$active_page = '...';         // header variables
$page_title = '...';
require __DIR__ . '/includes/header.php';
?>
<main class="page-shell ...">  <!-- markup -->
<?php require __DIR__ . '/includes/footer.php'; ?>
```

Never output anything before `require_once 'db.php'` — headers and redirects come after it.

## CSS

- Color goes through `--color-*` **only**. A hardcoded hex breaks one of the themes.
- Radii use `--radius-*`, widths use `--content-*`.
- Public styles in `style.css`, admin in `admin.css`. Don't mix them.
- Animations must survive `prefers-reduced-motion` (the global rule already exists).
- **Bump `?v=N`** after editing CSS or JS.

See [[Design system]].

## JavaScript

- Vanilla, no build, `'use strict'`, IIFE.
- Handlers are **delegated** on `document` rather than bound to nodes: markup may appear later,
  and one listener covers it.
- Behavior attaches through `data-*` attributes. New interactivity is usually a new hook,
  not a new file.
- ES5-compatible style (`var`, `function`) — that's how the existing code is written, and the
  fake DOM in the tests assumes it.
- **User-facing strings need both languages** in the file's own RU/UK table, since the output
  filter can't reach them. See [[Localization]].

## Before committing

1. `C:/xampp/php/php.exe -l` on every changed `.php`
2. all four test suites — [[Tests]]
3. if you touched markup, check both themes and a mobile width

Related: [[Security]], [[Tests]], [[Design system]]
