---
tags:
  - meta
---

# Maintaining this vault

## What belongs here

This vault **complements the code, it doesn't restate it**. The code shows *how*;
these notes explain *why* and *where*.

Write down:

- decisions and their **reasons** ("passwords are sha256 because the emulator requires it")
- non-obvious connections between files ("`db.php` is the bootstrap, not a connector")
- contracts with external systems (shared tables, formats)
- rakes already stepped on
- anything that takes reading several files to understand

Don't write down:

- restatements of function signatures — faster to read in the code
- full file listings
- things that change tomorrow (line numbers, exact sizes)
- commit history — git already has it

> [!tip] The test
> If a question can be answered in 10 seconds with `grep`, it doesn't need a note.
> If it takes reading three files and a guess, it does.

## Structure

```
Home.md              entry point, MOC — update it when adding a note
01 Architecture/     how the system is built
02 Domains/          individual subsystems
03 Development/      how to work here
04 Operations/       config, security, production
99 Meta/             about the vault itself
```

New notes go in an existing folder. Only create a folder when you have 3+ notes for it up front.

## Formatting rules

**Frontmatter** — minimal:

```yaml
---
tags:
  - domain
---
```

Tags in use: `architecture`, `domain`, `development`, `operations`, `security`, `frontend`,
`db`, `rbac`, `i18n`, `tests`, `game`, `content`, `moc`, `meta`.

**Link generously.** Double-bracket links resolve by filename regardless of folder. A link to a
note that doesn't exist yet is fine — it marks a topic worth covering.

**Callouts** by purpose:

- `> [!important]` — you can't understand the subject without this
- `> [!warning]` — easy to break here
- `> [!danger]` — a known trade-off or security risk
- `> [!note]` — side context
- `> [!tip]` — practical advice

**Reference code** by file and function name, **without line numbers**. Lines drift after the
first edit; `grep -n "function_name"` doesn't.

## Keeping in sync with the code

> [!warning] The main risk
> Stale documentation is worse than none — people trust it.

The rule: **when you change a subsystem, update its note in the same commit.**

Notes that go stale fastest:

| Note | Update trigger |
|---|---|
| [[Database schema]] | any edit to `ensure_site_schema()` |
| [[Roles and permissions]] | a new permission or role |
| [[Admin panel]] | a new tab or AJAX action |
| [[Tests]] | a new group in the contract test |
| [[Design system]] | a new token or JS hook |
| [[Localization]] | a new trap in the phrase-matching filter |

## Relationship to `CLAUDE.md`

The repository root holds `CLAUDE.md`, an instruction file for Claude Code. Both it and this
vault are in English, but they serve **different audiences**:

| | `CLAUDE.md` | This vault |
|---|---|---|
| Audience | the agent | a person |
| Length | terse, only what's actionable | expanded, with context |
| Contents | commands, constraints, conventions | explanations, reasons, connections |

When something fundamental changes (say, schema auto-migration disappears), update **both**.

## The vault in git

`obsidian/` lives inside the repository, so notes are versioned alongside the code — you can see
how understanding of the system evolved. The `.obsidian/` folder holds editor settings; its
local workspace state is gitignored (see [[Configuration and secrets]]).
