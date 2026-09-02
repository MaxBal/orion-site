---
tags:
  - domain
---

# Bug tracker

Pages: `bugs.php` (list and form), `bug_view.php` (detail and comments).
The `reports` tab in the admin panel.

## Tables

```
bug_reports(id, account_id, title, description,
            status ENUM('open','in_progress','resolved','closed'),
            is_approved, created_at, updated_at)

bug_comments(id, bug_id, account_id, comment, created_at)
```

## Moderation before publication

`is_approved` defaults to `0`. A fresh report is **not publicly visible** until a moderator
approves it. This is the main defense against spam and noise in the list.

So a report has two independent dimensions:

- **`is_approved`** — whether it's visible at all
- **`status`** — how far along the work is (`open` → `in_progress` → `resolved` / `closed`)

## Four barriers on creation

`bugs.php` checks these before inserting:

1. **CSRF** — `verify_csrf()`
2. **Personal restriction** — `accounts.is_banned_reports`. A separate flag, unrelated to the
   `bans` table. The player can use the site but can't file reports. See [[Bans]]
3. **Throttle** — interval since the same author's last report:
   ```sql
   SELECT TIMESTAMPDIFF(SECOND, MAX(created_at), NOW())
   FROM bug_reports WHERE account_id = ?
   ```
4. **reCAPTCHA v2** — `verify_recaptcha()`

Only after all of that: `INSERT ... status='open', is_approved=0`.

## Admin panel

Permissions: `reports.manage` (approve, change status, comment, restrict authors) and
`reports.delete` (permanent deletion).

Actions (form plus redirect): `update_bug_report`, `delete_bug_report`. The project lead
also has an owner-only, CSRF-protected `close_all_bug_reports` action that changes every
non-closed report to `closed` without deleting reports, comments, or publication state.

Audit: `report.update`, `report.close_all`, `report.delete`, `report.comment.delete`,
`report.author.restrict`.

The count of unprocessed reports appears as a badge next to the menu item
(`$dashboard_stats['reports_pending']`), so nobody has to open the tab to check.
Closed reports are excluded from this count and from the default review queue even when
they were never approved for publication. Closing a report does not publish or delete it;
in the closed list such a report is labelled as non-public instead of awaiting review.

> [!note] Report titles stay in the author's language
> Report titles and bodies are player-written and are never translated. Some are in Ukrainian
> even on the Russian version of the page — that's correct, not a localization bug.
> See [[Localization]].

Moderators and senior moderators process this queue according to their role permissions.
See [[Roles and permissions]].

Related: [[Admin panel]], [[Database schema]], [[Security]]
