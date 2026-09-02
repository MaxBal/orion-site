---
aliases:
  - Project brain
tags:
  - moc
---

# Project Orion — knowledge vault

This is the project's brain: the things you can't see from the code at a glance.
The code tells you **how**; these notes tell you **why** and **where**.

> [!info] What this project is
> The website and control panel for the **Project Orion** game server (version 0.8.2).
> Server-rendered PHP 8, no framework, no build step, no package manager.
> The database is **shared with the game server** — that is the single biggest
> constraint on everything else.

---

## Architecture

- [[System overview]] — what the site is made of and how the parts connect
- [[Request lifecycle]] — everything `db.php` does before a page renders a single byte
- [[Database schema]] — tables, auto-migration, entities shared with the emulator
- [[Vehicle modules and the two id spaces]] — the one shared table whose ids don't mean what they look like
- [[Localization]] — translating finished HTML through an output buffer
- [[Design system]] — tokens, themes, fonts, JS hooks
- [[Admin panel]] — `admin.php`: two request modes, tabs, audit trail

## Domains

- [[Roles and permissions]] — RBAC: permission catalog, ranks, per-account overrides
- [[Authentication]] — registration, login, email confirmation
- [[Bans]] — account, IP and MAC bans; the contract with the game server
- [[News]] — posts and media
- [[Vehicle access]] — global rules and per-player overrides
- [[Downloads and mirrors]] — client, patch, settings driven from the admin panel
- [[Bug tracker]] — player reports and their moderation
- [[GSO]] — council seats, proposals, voting, approval, and implementation
- [[Email and codes]] — SMTP, one-time codes, disposable-address defense

## Development

- [[Local setup]] — getting the site running
- [[Tests]] — four suites and what each one actually guards
- [[Code conventions]] — how to write so your code doesn't stand out

## Operations

- [[Configuration and secrets]] — where config comes from, what isn't in the repo
- [[Security]] — CSRF, CSP, escaping, known trade-offs

## Meta

- [[Maintaining this vault]] — how to keep these notes useful

---

## Quick facts

| | |
|---|---|
| Stack | PHP 8.2 + MySQL + vanilla JS, no build |
| PHP binary | `C:\xampp\php\php.exe` — **not on PATH** |
| Database | `wot_emulator`, shared with the game server |
| Local port | 8123 (see [[Local setup]]) |
| Interface language | Russian in source, Ukrainian via a filter — [[Localization]] |
| Default theme | dark |
| Domain | `projectorion.fun` |

## Where to start

1. [[System overview]] — get the map
2. [[Request lifecycle]] — so you stop looking for "where X is initialized"
3. [[Local setup]] — see it running
4. [[Code conventions]] — before your first commit
