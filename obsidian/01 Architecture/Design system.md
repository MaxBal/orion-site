---
tags:
  - architecture
  - frontend
---

# Design system

The current look is the **"Steel Front" redesign**: a dark, angular military-industrial skin
derived from the `template.html` prototype the project lead supplied. Everything is square
(no rounded corners), display type is uppercase Russo One, and the accent is the amber /
gold / orange ramp.

The earlier "Orion Glass" pass (spec and plan in `docs/superpowers/`, prototype in `_template/`,
which the site does not use) is what Steel Front replaced. The class names survived the
change — the contract tests pin most of them — only the paint and the layout moved.

## What makes it look like Steel Front

Five moves carry the whole skin. If you add a section, reuse them instead of inventing new ones:

1. **Square everything.** `--radius-*` are all `0`. A cut corner comes from
   `clip-path: var(--clip-cut)` (12px) or `var(--clip-cut-sm)` (8px), not from a radius.
   Circles stay circles only for dots, avatars, orbits, and the logo.
2. **Corner brackets.** `<span class="front-corners"><i></i>×4</span>` inside a
   `position: relative` panel draws the four L-shaped amber brackets.
3. **Uppercase stencil type.** `--font-stencil` plus wide `letter-spacing` for anything that
   labels rather than reads: nav, buttons, tabs, badges, table headers, section eyebrows.
4. **Hairline panels on `--color-surface`** with a 44px grid overlay
   (`--color-grid`) and a hover lift.
5. **Film grain over the whole document** — `body::after`, opacity `--color-noise`.

> [!warning] Always-dark surfaces
> The hero, its stats strip, and `.support-cta` sit on dark artwork in **both** themes, so their
> text colors are hardcoded hex on purpose. Do not "fix" them into tokens — the light theme
> would wash them out.

## Tokens

The whole palette is CSS custom properties at the top of `style.css`:

- `:root` — the **light** theme (base token set)
- `:root[data-theme="dark"]` — **dark**, overriding the same variables

> [!warning] Dark is the default
> `includes/header.php` emits `<html lang="ru" data-theme="dark">`, and `theme.js` applies the
> stored theme or `dark`. Light is the base token set, but not the default.

Variable groups:

| Prefix | Purpose |
|---|---|
| `--color-ink`, `--color-text`, `--color-muted`, `--color-faint` | text, decreasing contrast |
| `--color-canvas`, `--color-surface`, `--color-elevated`, `--color-media` | surfaces |
| `--color-line`, `--color-line-strong` | borders |
| `--color-accent` | accent (amber `#ffb02e` dark / `#b5701a` light) |
| `--color-gold`, `--color-orange`, `--color-teal` | the rest of the Steel Front ramp |
| `--color-success` / `-danger` / `-info` / `-warning` | status |
| `--color-action`, `--color-on-action` | primary action (buttons) |
| `--color-grid`, `--color-scan`, `--color-noise` | panel grid, hero scanlines, film grain |
| `--radius-sm/md/lg/xl` | all `0` — the skin is square |
| `--cut`, `--cut-sm`, `--clip-cut`, `--clip-cut-sm` | the 12/8px corner chamfer |
| `--header-height` | hero pull-up offset; `js/front.js` measures the real header into it |
| `--shadow-button`, `--shadow-float` | shadows |
| `--content-wide`, `--content-reading` | 1240 / 820 px |

> [!tip] The rule
> Every new color **must** go through a `--color-*` variable. A hardcoded hex will look fine
> in one theme and break in the other.

## Theme switching

`js/theme.js` is loaded **synchronously in `<head>`** — that's why there's no white flash on
load: the theme is applied before the first frame.

- `localStorage` key: `orion-theme`
- public API: `window.OrionTheme.get() / .set(theme) / .toggle()`
- cross-tab sync via the `storage` event
- the trigger is any element with `[data-theme-toggle]`, handled by a delegated listener

Its button labels are localized in JS, because the output filter can't see them — see
[[Localization]].

## Fonts

| Variable | Font | Source |
|---|---|---|
| `--font-sans` | e-Ukraine | local, `/fonts` |
| `--font-display` | e-Ukraine Head | local, `/fonts` |
| `--font-stencil` | Russo One → falls back to e-Ukraine Head | Google Fonts |
| `--font-mono` | Roboto Mono | Google Fonts |

`--font-stencil` is the Steel Front display face and drives `h1`–`h4`, nav, buttons, tabs,
badges and table headers. `--font-sans` still sets body copy, so the Ukrainian brand face
carries every paragraph. `--font-display` stays defined (the contract test pins the token) and
serves as the stencil fallback.

Monospace is for technical metadata: dates, versions, IPs, codes, statuses.

> [!warning] Uppercase stops at `h2`
> `h1`/`h2` are `text-transform: uppercase`; `h3`/`h4` are not, because they carry
> **authored** text — news titles, bug report titles — and forcing case would mangle it.

## JS hooks

`js/site.js` is built on **delegated handlers** (one listener on `document` per concern) rather
than binding to specific nodes. Markup drives behavior through `data-*`:

| Hook | Behavior |
|---|---|
| `[data-nav-toggle]` | mobile menu (manages `aria-expanded`) |
| `[data-modal-open="id"]` / `[data-modal-close]` | modals with a focus trap |
| `[data-copy]` | copy to clipboard plus button feedback |
| `[data-scroll-to]` | smooth scroll |
| `.stat-value[data-target]` | animated counters |
| `.reveal` | scroll-triggered reveal |

`js/front.js` carries the Steel Front chrome and is loaded on every page next to `site.js`:

| Hook | Behavior |
|---|---|
| `[data-front-marquee]` | clones its first child so a `-50%` scroll loops seamlessly |
| `.app-header` | `.is-scrolled` past 10px, and publishes `--header-height` |
| `.metric-card` | `.is-in` fills the stat bars when the strip enters the viewport |
| `[data-front-embers]` | the hero ember canvas (skipped under reduced motion) |
| `[data-front-countdown="<ISO>"]` | countdown, writing into `[data-countdown-days\|hours\|minutes\|seconds]` |
| `[data-front-hall-tab]` / `[data-front-hall-panel]` | hall-of-fame category switch |

> [!warning] Why front.js is a separate file
> `js/site.js` is verified against a **fake DOM** in `tests/site_interactions_test.js` that has
> no `canvas`, no `window.addEventListener` and no `requestAnimationFrame`. Anything needing a
> real browser goes in `front.js`, or that test starts throwing.

> [!tip] New interactivity
> Usually that's a **new hook in the markup**, not a new JS file. Check whether an existing
> hook already covers the case.

## Accessibility isn't optional

It's enforced by tests (`--group=interactions`, `--group=theme`), so it isn't an extra step:

- `prefers-reduced-motion` — global override at the top of `style.css`
- modals keep focus inside (focus trap) and close on `Esc`
- the mobile menu keeps `aria-expanded` in sync
- the theme button updates `aria-pressed`, `aria-label` and `title`
- minimum touch-target sizes are checked by a test

## File split

| File | Scope |
|---|---|
| `style.css` | tokens, shared shell, components, public pages |
| `admin.css` | admin shell, sidebar, tables, forms |
| `js/site.js` | public behavior (fake-DOM tested — see the warning above) |
| `js/front.js` | Steel Front chrome: marquees, embers, countdown, header state |
| `js/admin.js` | admin AJAX and admin-only UI |

The admin panel loads its own files via `$page_styles` / `$page_scripts` — see [[Admin panel]].
It shares the public header, so the ticker is hidden there by `body.is-admin .front-ticker`.

## Homepage layout

`index.php` is the one page whose structure follows the prototype rather than the generic
`.page-shell`:

1. `.front-ticker` (rendered by `includes/header.php`, above the sticky header)
2. `.home-hero` — full-bleed art, scanlines, ember canvas, copy + bracketed logo, pulled up
   under the transparent header by `--header-height`
3. `.home-hero-stats` / `.stats-grid` — four cells with fill bars, welded to the hero's bottom edge
4. `.home-subscriptions-banner` — tier list on the left, instrument panel on the right
5. `.roadmap-preview` — brackets, live countdown to the 2026-08-30 launch, progress
6. `.front-hall` — player rating with category tabs, from `load_player_rankings()`
7. `.news-feed` — first item is `.news-card--lead` (large, image on top), then side-image cards
8. `.front-strip` — full-bleed image marquee
9. `.support-cta` — dark amber box with dashed inner frame

## Cache busting

Asset URLs carry `?v=N`. **Bump the number whenever you edit the corresponding CSS or JS**,
otherwise users get the cached old file. Most contract assertions match `\d+`, but one pins
the exact `style.css?v=N` in `includes/header.php` — that assertion exists to force the bump,
so update it in the same commit — see [[Tests]].

## Shared shell

`includes/header.php` renders the whole `<head>` and the site header. A page sets variables
**before** requiring the header; all have defaults, so omitting any of them is safe:

`$page_title`, `$page_description`, `$page_path`, `$seo_index`, `$active_page`,
`$head_extra`, `$body_class`, `$show_popup`, `$page_styles[]`, `$page_scripts[]`, `$footer_extra`

`$active_page` highlights the nav item via `nav_active()` and becomes a `page-*` class on `<body>`.

Related: [[Localization]], [[Admin panel]], [[Tests]]
