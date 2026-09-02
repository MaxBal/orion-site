---
tags:
  - development
  - tests
---

# Tests

Seven independent suites with **no test framework dependencies** — no PHPUnit, no Jest, no npm packages.
All seven must pass before a commit.

```bash
C:/xampp/php/php.exe tests/ui_contract_test.php
C:/xampp/php/php.exe tests/staff_permissions_test.php
C:/xampp/php/php.exe tests/gso_workflow_test.php
C:/xampp/php/php.exe tests/contracts_test.php
C:/xampp/php/php.exe tests/player_stats_test.php
node tests/site_interactions_test.js
node tests/admin_mirror_lifecycle_test.js
```

## 1. `ui_contract_test.php` — structural contract

The most important and most unusual one. It **does not run the site** — it reads source files as
text and asserts that specific strings are present: class names, attributes, function calls,
CSS values.

```php
expectContains('includes/header.php', 'class="app-header"', 'Shared app header is missing');
expectNotContains(...);   expectRegex(...);   expectFileNotExists(...);
```

There are also specialized helpers — for example `cssSelectorMinHeightAtLeast()`, which parses
CSS and checks minimum touch-target sizes.

### Groups

`harness`, `shared`, `home`, `content`, `theme`, `interactions`, `account`, `bugs`,
`admin-shell`, `admin-workspaces`, `staff-rbac`

Running one:

```bash
C:/xampp/php/php.exe tests/ui_contract_test.php --group=admin-shell
```

An unknown group name exits with code 2 and a list.

> [!warning] The most common cause of "weird" failures
> The test is pinned to **literal strings in the source**. Renaming a CSS class, changing an
> ARIA attribute, or swapping quote styles breaks the test somewhere that looks unrelated to
> your change. Read the failure message — it names the file and the expectation.
>
> That's the price of checking accessibility and markup without a browser. If the rename is
> intentional, update the expectation alongside the code.

> [!note] Cache busters are deliberately not pinned
> Asset includes are checked with `expectRegex` using `\d+` instead of a version number.
> Otherwise every `?v=` bump — required on **any** CSS/JS edit — would fail a test that has
> nothing to do with the change.

## 2. `staff_permissions_test.php` — RBAC

A genuine unit test: it includes `includes/staff.php` and checks the model's invariants against
role fixtures. What it guards:

- each role has its own permission set (content makers can't ban, moderators can't reset passwords);
- `player` can't enter the panel;
- **acting on yourself is forbidden** — at any rank;
- acting on an equal or higher rank is forbidden;
- legacy `is_admin = 1` yields the `admin` role.

A failure here means the access model was touched. See [[Roles and permissions]].

## 3. `gso_workflow_test.php` — council rules

Checks quorum calculation, majority/tie behavior, abstentions, and the reversible text format
used by the admin update-history editor. Database transitions remain covered structurally and
through local integration checks.

## 4. `contracts_test.php` — contract documents

Checks the four user-selectable contract types, seven-day term calculation, immediate
role activation after lead acceptance, fresh submission for a new term, all three
document languages, embedded-font PDF generation, and the two-page A4 structure.

## 5. `player_stats_test.php` — statistics calculations

Checks the pure player-stat aggregation helpers.

## 6–7. JS tests on a fake DOM

`site_interactions_test.js` and `admin_mirror_lifecycle_test.js` don't use jsdom. They implement
a minimal fake DOM (`FakeElement`, `FakeClassList`, `fakeDocument`) and execute the real
`js/site.js` / `js/admin.js` through the `vm` module.

They cover: the mobile menu, modals and the focus trap, clipboard copy, tabs, counters,
**localization of JS-set strings in both languages**, and the mirror row lifecycle in the admin
panel ([[Downloads and mirrors]]).

Language is a harness option:

```js
const ukHarness = createHarness({ lang: 'uk' });
```

It sets `lang` on the fake `document.documentElement`, exactly as the filter does on a live page.
See [[Localization]].

Consequence: if the JS starts using a DOM API the fake doesn't implement, the test fails with
`is not a function` or `Cannot read properties of undefined`. Then add the missing property to
the fake — that's how `documentElement` got there.

## What is NOT covered

There are no tests for PHP logic that touches the database. SQL queries, transactions, and admin
handlers are only verified manually through [[Local setup]]. When planning a change in that area,
budget for manual verification.

## Minimum before a commit

```bash
C:/xampp/php/php.exe -l changed_file.php    # syntax
C:/xampp/php/php.exe tests/ui_contract_test.php
C:/xampp/php/php.exe tests/staff_permissions_test.php
C:/xampp/php/php.exe tests/gso_workflow_test.php
C:/xampp/php/php.exe tests/contracts_test.php
C:/xampp/php/php.exe tests/player_stats_test.php
node tests/site_interactions_test.js
node tests/admin_mirror_lifecycle_test.js
```

Related: [[Code conventions]], [[Local setup]], [[Design system]]
