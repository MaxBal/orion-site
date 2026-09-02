# Players Page: Rankings and Nickname Autocomplete

Date: 2026-08-01
Status: Approved design

## Goal

Complete `players.php` as a public player directory. The page will keep its
server-rendered exact-name profile search and add:

- Five player ranking tabs.
- Prefix-based nickname autocomplete.
- Keyboard-accessible search suggestions.
- Responsive light and dark theme styling.

The feature will use the existing PHP, PDO, vanilla JavaScript, localization,
and shared page-shell patterns. It will not require a build step or a game
server change.

## Decisions

- Rankings include every account with battle statistics, including staff
  accounts.
- Every ranking displays at most 10 players.
- The `Win Rate` ranking requires at least 10 battles.
- The other rankings require at least one battle.
- Ranking ties sort by total battles descending and then username ascending.
- The five rankings are `Win Rate`, `Победы`, `Фраги`, `Средний урон`, and
  `Средний опыт`.
- Autocomplete starts at two input characters and displays at most eight
  prefix matches.
- Selecting a suggestion opens the existing public profile URL.
- The default ranking tab is `Win Rate`.

## Architecture

### Ranking data

Add `includes/player_rankings.php` with a focused loader for the five ranking
lists. It will join `accounts` to `dossier` and return only the public fields
needed by the view:

- account ID
- username
- staff flags used to render an optional role label
- total battles
- the calculated value for the selected ranking

The loader will use prepared SQL statements. Ranking expressions will be
calculated from dossier columns:

- `Win Rate`: wins divided by total battles, ordered by the percentage.
- `Победы`: wins.
- `Фраги`: frags.
- `Средний урон`: damage dealt divided by total battles.
- `Средний опыт`: total XP divided by total battles.

Rows without a dossier are treated as zero-stat accounts and do not qualify
for a ranking. The page will render all five lists in the initial HTML so tab
switching does not require another network request.

### Autocomplete endpoint

`players.php` will recognize `?ajax=player-suggestions&q=...` before rendering
the shared header. It will:

1. Trim and validate the query.
2. Return an empty JSON list for fewer than two characters or invalid input.
3. Search `accounts.normalized_name` with a prepared prefix `LIKE` value.
4. Return no more than eight usernames, ordered by the normalized name.
5. Include each username and total battles when available. The client will
   construct the profile URL with `encodeURIComponent`, rather than trusting a
   URL returned by the endpoint.

The endpoint will set a JSON content type and a no-cache response. Database
details will be logged server-side but never returned to the browser. The
existing exact profile GET remains the no-JavaScript fallback.

## Interface

The ranking block will appear below the hero search and above the selected
profile result. Its tab buttons will use `role="tab"`, `aria-selected`, and
`aria-controls`. Each ranking panel will use `role="tabpanel"` and remain
available in the server-rendered document; JavaScript will only toggle
visibility and active state.

Each ranking row will show:

- rank number
- linked username
- staff role label when the account has a staff role
- ranking value
- total battles

The search field will gain a listbox-style suggestion surface. JavaScript will
debounce requests by roughly 180 milliseconds and ignore stale responses. It
will support:

- Arrow Up and Arrow Down to change the active suggestion.
- Enter to open the active suggestion.
- Escape to close the list.
- Click selection and click-outside dismissal.

The suggestion surface will be hidden when there are no matches, while the
regular form submit remains available at all times. On mobile, ranking tabs
will scroll horizontally and ranking rows will preserve readable values
without forcing the page wider than the viewport.

New styles will use existing `--color-*`, typography, radius, and shadow
tokens so both themes remain consistent. Motion will follow the existing
reduced-motion rules.

## Localization

Page labels and server-rendered ranking text will be written in Russian, in
line with the repository convention. New exact Russian phrases will be added
to `i18n_uk_map()` in `lang.php`, including markup when a phrase crosses an
inline element. JavaScript-rendered labels and error text will have RU and UK
entries in the page script because they do not pass through the PHP output
filter.

## Error Handling and Security

- Invalid or short autocomplete queries will not reach the database.
- All dynamic SQL values will use prepared statements; the ranking selector
  will be chosen from a fixed server-side map rather than user SQL input.
- Usernames, labels, and profile URL parameters will be escaped at their
  output boundaries.
- The public response will not select or expose email, credits, gold,
  passwords, IP addresses, or other private account fields.
- If ranking loading fails, the page will still render the search/profile
  functionality and show a neutral temporary-unavailable state for the
  ranking area.
- If the JSON lookup fails, the suggestion list will close without exposing
  exception details and the normal search form will continue to work.

## Verification

Add or extend tests for:

- The five ranking tabs and ranking data hooks in `players.php`.
- The autocomplete endpoint contract and `js/players.js` cache-busted
  registration.
- Accessible tab and listbox attributes.
- The public-field boundary that prevents private account fields from being
  selected.
- Keyboard selection, stale-response handling, and empty/error states in the
  JavaScript fake-DOM test style already used by the repository.

Before implementation is considered complete, run PHP lint on every changed
PHP file and all existing project checks from `CLAUDE.md`, including the UI
contract, staff permissions, site interactions, admin mirror lifecycle, and
player statistics tests.

## Scope Boundaries

This feature does not add pagination, online presence, historical ranking
snapshots, profile privacy settings, database migrations, or changes to the
game emulator. It also does not replace the existing profile page or alter
password and account-storage behavior.
