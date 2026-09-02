# Players Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete `players.php` with five server-rendered player rankings and accessible prefix-based nickname autocomplete while preserving the existing public profile search.

**Architecture:** Keep the page server-rendered. A focused `includes/player_rankings.php` loader will query the five fixed rankings from `accounts` and `dossier`; `players.php` will render all ranking panels and expose a small JSON suggestion mode before the HTML shell. A page-specific vanilla `js/players.js` will only switch tabs and enhance autocomplete, with the existing form remaining functional without JavaScript.

**Tech Stack:** PHP 8, PDO/MySQL, existing `players.php` shared shell, vanilla JavaScript, CSS custom properties in `style.css`, existing PHP output-filter localization, Node fake-DOM tests, and PHP structural contract tests.

## Global Constraints

- The five rankings are `Win Rate`, `Победы`, `Фраги`, `Средний урон`, and `Средний опыт`.
- Every ranking has at most 10 rows; `Win Rate` requires at least 10 battles and the other rankings require at least one battle.
- Rankings include all qualifying accounts, including staff accounts.
- Ranking ties sort by total battles descending and then username ascending.
- Autocomplete accepts valid prefixes from two to 24 characters and returns at most eight suggestions.
- All SQL values are prepared; the ranking SQL expression is selected only from a fixed server-side map.
- Public player responses must not select email, resources, passwords, IP addresses, or other private account fields.
- Russian is the source language; PHP-rendered phrases belong in `lang.php`, while JavaScript-rendered phrases need RU and UK entries in `js/players.js`.
- New colors use existing `--color-*` tokens and new motion must respect reduced-motion behavior.
- Bump the shared `style.css` cache version when editing it and register the page script with a numeric cache version.
- Preserve unrelated dirty worktree changes. Do not commit unless the user explicitly requests a commit.

## File Map

- Create `includes/player_rankings.php`: fixed ranking definitions, PDO loader, and ranking-value formatter.
- Modify `players.php`: JSON suggestion mode, ranking loading/fallback state, ranking tabs/panels, autocomplete markup, and page-script registration.
- Create `js/players.js`: accessible ranking tabs and debounced autocomplete behavior.
- Modify `style.css`: ranking cards, rows, suggestion list, and responsive rules using existing tokens.
- Modify `includes/header.php`: bump the global `style.css` cache version by one.
- Modify `lang.php`: add exact Russian-to-Ukrainian mappings for new server-rendered labels and states.
- Create `tests/player_rankings_test.php`: pure ranking-definition and formatter checks without a database connection.
- Create `tests/player_interactions_test.js`: fake-DOM checks for tabs, keyboard navigation, stale autocomplete responses, and both languages.
- Modify `tests/ui_contract_test.php`: structural contracts for the page, endpoint, accessibility attributes, privacy boundary, and asset registration.

---

### Task 1: Add the Ranking Data Unit

**Files:**
- Create: `includes/player_rankings.php`
- Test: `tests/player_rankings_test.php`

**Interfaces:**
- Produces `player_ranking_definitions(): array`, `load_player_rankings($pdo, $limit = 10): array`, and `format_player_ranking_value($key, $value): string` for `players.php`.
- `load_player_rankings()` returns an associative array keyed by `win_rate`, `wins`, `frags`, `avg_damage`, and `avg_xp`; each value contains `label` and a `rows` array.
- Each row contains only `id`, `username`, `is_admin`, `staff_role`, `total_battles`, and `metric_value`.

- [ ] **Step 1: Write the failing pure PHP test.**

Create `tests/player_rankings_test.php` with this contract before creating the implementation:

```php
<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/player_rankings.php';

function playerRankingCheck(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$definitions = player_ranking_definitions();
playerRankingCheck(array_keys($definitions) === ['win_rate', 'wins', 'frags', 'avg_damage', 'avg_xp'], 'ranking keys are incomplete or reordered');
playerRankingCheck($definitions['win_rate']['minimum_battles'] === 10, 'win rate must require ten battles');
playerRankingCheck($definitions['wins']['minimum_battles'] === 1, 'wins must require one battle');
playerRankingCheck($definitions['frags']['minimum_battles'] === 1, 'frags must require one battle');
playerRankingCheck($definitions['avg_damage']['minimum_battles'] === 1, 'average damage must require one battle');
playerRankingCheck($definitions['avg_xp']['minimum_battles'] === 1, 'average XP must require one battle');
playerRankingCheck(str_contains($definitions['win_rate']['metric_sql'], 'd.wins'), 'win rate SQL must use wins');
playerRankingCheck(str_contains($definitions['avg_damage']['metric_sql'], 'd.damage_dealt'), 'average damage SQL must use damage dealt');
playerRankingCheck(str_contains($definitions['avg_xp']['metric_sql'], 'd.total_xp'), 'average XP SQL must use total XP');
playerRankingCheck(format_player_ranking_value('win_rate', 62.5) === '62.5%', 'percentage formatting should trim trailing zeroes');
playerRankingCheck(format_player_ranking_value('wins', 1200) === '1,200', 'integer formatting should use number separators');
playerRankingCheck(format_player_ranking_value('avg_damage', 1001) === '1,001', 'average damage formatting is incorrect');

fwrite(STDOUT, "Player ranking definitions passed.\n");
```

- [ ] **Step 2: Run the new test and verify it fails for the missing implementation.**

Run:

```text
C:/xampp/php/php.exe tests/player_rankings_test.php
```

Expected: FAIL because `includes/player_rankings.php` does not exist yet.

- [ ] **Step 3: Implement the fixed definitions, loader, and formatter.**

Create `includes/player_rankings.php` with these rules:

```php
<?php

function player_ranking_definitions(): array
{
    return [
        'win_rate' => [
            'label' => 'Win Rate',
            'metric_sql' => 'ROUND((d.wins / NULLIF(d.total_battles, 0)) * 100, 2)',
            'minimum_battles' => 10,
            'suffix' => '%',
            'decimals' => 2,
        ],
        'wins' => [
            'label' => 'Победы',
            'metric_sql' => 'd.wins',
            'minimum_battles' => 1,
            'suffix' => '',
            'decimals' => 0,
        ],
        'frags' => [
            'label' => 'Фраги',
            'metric_sql' => 'd.frags',
            'minimum_battles' => 1,
            'suffix' => '',
            'decimals' => 0,
        ],
        'avg_damage' => [
            'label' => 'Средний урон',
            'metric_sql' => 'ROUND(d.damage_dealt / NULLIF(d.total_battles, 0))',
            'minimum_battles' => 1,
            'suffix' => '',
            'decimals' => 0,
        ],
        'avg_xp' => [
            'label' => 'Средний опыт',
            'metric_sql' => 'ROUND(d.total_xp / NULLIF(d.total_battles, 0))',
            'minimum_battles' => 1,
            'suffix' => '',
            'decimals' => 0,
        ],
    ];
}

function load_player_rankings($pdo, $limit = 10): array
{
    $limit = max(1, min(10, intval($limit)));
    $rankings = [];

    foreach (player_ranking_definitions() as $key => $definition) {
        $sql = "SELECT a.id, a.username, a.is_admin, a.staff_role,
                        d.total_battles,
                        {$definition['metric_sql']} AS metric_value
                 FROM accounts AS a
                 INNER JOIN dossier AS d ON d.account_id = a.id
                 WHERE d.total_battles >= ?
                 ORDER BY metric_value DESC, d.total_battles DESC, a.username ASC
                 LIMIT {$limit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$definition['minimum_battles']]);
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[] = [
                'id' => intval($row['id']),
                'username' => (string)$row['username'],
                'is_admin' => intval($row['is_admin'] ?? 0),
                'staff_role' => (string)($row['staff_role'] ?? ''),
                'total_battles' => intval($row['total_battles']),
                'metric_value' => (float)$row['metric_value'],
            ];
        }
        $rankings[$key] = ['label' => $definition['label'], 'rows' => $rows];
    }

    return $rankings;
}

function format_player_ranking_value($key, $value): string
{
    $definitions = player_ranking_definitions();
    $definition = $definitions[$key] ?? ['suffix' => '', 'decimals' => 0];
    if ($definition['decimals'] === 0) {
        $formatted = number_format((int)$value);
    } else {
        $formatted = number_format((float)$value, $definition['decimals'], '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');
    }
    return $formatted . $definition['suffix'];
}
```

The SQL expression strings above are constants returned by the fixed
definition map; do not interpolate a request parameter into an expression,
column name, table name, or sort direction.

- [ ] **Step 4: Run the pure test and PHP lint.**

Run:

```text
C:/xampp/php/php.exe tests/player_rankings_test.php
C:/xampp/php/php.exe -l includes/player_rankings.php
```

Expected: `Player ranking definitions passed.` and `No syntax errors detected`.

### Task 2: Add the Server-Rendered Page and Suggestion Endpoint

**Files:**
- Modify: `players.php`
- Modify: `tests/ui_contract_test.php`

**Interfaces:**
- `players.php?ajax=player-suggestions&q=<prefix>` returns JSON shaped as `{ "suggestions": [{ "username": "...", "total_battles": 12 }] }`.
- The HTML page consumes the `load_player_rankings($pdo)` result from Task 1 and exposes the `data-player-*` hooks used by Task 3.
- The current `?username=...` profile query and all existing public-profile fields remain unchanged.

- [ ] **Step 1: Add failing structural contracts before the page change.**

In the existing `account` group in `tests/ui_contract_test.php`, add these
expectations after the current `players.php` checks:

```php
        expectContains('players.php', "($_GET['ajax'] ?? '') === 'player-suggestions'", 'Player autocomplete endpoint is missing');
        expectContains('players.php', 'load_player_rankings($pdo', 'Player rankings are not loaded server-side');
        expectContains('players.php', 'class="player-rankings"', 'Player ranking block is missing');
        expectContains('players.php', 'role="tablist"', 'Player ranking tablist is missing');
        expectContains('players.php', 'data-player-ranking-tab', 'Player ranking tab hook is missing');
        expectContains('players.php', 'role="tabpanel"', 'Player ranking panel is missing');
        expectContains('players.php', 'data-player-input', 'Player autocomplete input hook is missing');
        expectContains('players.php', 'role="listbox"', 'Player autocomplete listbox is missing');
        expectContains('players.php', 'LIMIT 8', 'Player autocomplete result limit is missing');
        expectContains('players.php', "'suggestions'", 'Player autocomplete JSON envelope is missing');
        expectContains('players.php', "'js/players.js?v=1'", 'Player interaction script is not registered');
        expectNotContains('players.php', 'SELECT email', 'Public player endpoint exposes email');
        expectNotContains('players.php', 'SELECT credits', 'Public player endpoint exposes account resources');
```

- [ ] **Step 2: Run the focused contract group and verify the new expectations fail.**

Run:

```text
C:/xampp/php/php.exe tests/ui_contract_test.php --group=account
```

Expected: the existing account checks pass, while the newly added ranking and
autocomplete expectations report missing strings.

- [ ] **Step 3: Add the JSON mode before normal page queries.**

Immediately after the two existing `require_once` calls in `players.php`, add
the ranking helper include and this endpoint branch. It must exit before
loading rankings or including the shared HTML header:

```php
require_once __DIR__ . '/includes/player_rankings.php';

if (($_GET['ajax'] ?? '') === 'player-suggestions') {
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, max-age=0');
    $query = trim((string)($_GET['q'] ?? ''));
    $suggestions = [];

    if ($query !== ''
        && strlen($query) >= 2
        && strlen($query) <= 24
        && preg_match('/^[A-Za-z0-9_.-]+$/', $query) === 1
    ) {
        try {
            $normalized_query = function_exists('mb_strtolower')
                ? mb_strtolower($query, 'UTF-8')
                : strtolower($query);
            $stmt = $pdo->prepare(
                "SELECT a.username, COALESCE(d.total_battles, 0) AS total_battles
                 FROM accounts AS a
                 LEFT JOIN dossier AS d ON d.account_id = a.id
                 WHERE a.normalized_name LIKE ?
                 ORDER BY a.normalized_name ASC, a.username ASC
                 LIMIT 8"
            );
            $stmt->execute([$normalized_query . '%']);
            foreach ($stmt->fetchAll() as $row) {
                $suggestions[] = [
                    'username' => (string)$row['username'],
                    'total_battles' => intval($row['total_battles']),
                ];
            }
        } catch (Exception $e) {
            error_log('Public player autocomplete load error: ' . $e->getMessage());
            http_response_code(500);
        }
    }

    echo json_encode(
        ['suggestions' => $suggestions],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}
```

Short or invalid input must return `{"suggestions":[]}` without preparing a
query. The public JSON must contain only username and battle-count data.

- [ ] **Step 4: Load rankings without breaking profile search.**

After the existing `$stats` initialization and before the `$username` profile
search branch, initialize ranking state and load it independently:

```php
$rankings = [];
$ranking_error = '';
foreach (player_ranking_definitions() as $ranking_key => $ranking_definition) {
    $rankings[$ranking_key] = ['label' => $ranking_definition['label'], 'rows' => []];
}

try {
    $rankings = load_player_rankings($pdo);
} catch (Exception $e) {
    error_log('Public player rankings load error: ' . $e->getMessage());
    $ranking_error = 'Рейтинг временно недоступен.';
}
```

Keep the existing profile-search `try/catch` separate so a ranking query
failure does not prevent an exact profile lookup from rendering.

- [ ] **Step 5: Register the page script and render accessible ranking panels.**

Set `$page_scripts = ['js/players.js?v=1'];` with the other page variables before
requiring `includes/header.php`. Insert the following ranking block after the
hero header and before `.player-search-result`:

```php
    <section class="player-rankings" data-player-rankings aria-labelledby="playerRankingsTitle">
        <header class="player-rankings-header">
            <div>
                <p class="eyebrow">ЛИДЕРЫ СЕРВЕРА</p>
                <h2 id="playerRankingsTitle">Топ игроков</h2>
            </div>
            <p>Лучшие результаты игроков по основным показателям.</p>
        </header>

        <?php if ($ranking_error !== ''): ?>
            <div class="player-ranking-unavailable" role="status"><?php echo htmlspecialchars($ranking_error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php else: ?>
            <div class="player-ranking-tabs" role="tablist" aria-label="Категории рейтинга">
                <?php foreach ($rankings as $ranking_key => $ranking): ?>
                    <?php $is_active = $ranking_key === 'win_rate'; ?>
                    <button
                        class="player-ranking-tab<?php echo $is_active ? ' is-active' : ''; ?>"
                        type="button"
                        id="player-ranking-tab-<?php echo htmlspecialchars($ranking_key, ENT_QUOTES, 'UTF-8'); ?>"
                        role="tab"
                        aria-controls="player-ranking-panel-<?php echo htmlspecialchars($ranking_key, ENT_QUOTES, 'UTF-8'); ?>"
                        aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                        tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
                        data-player-ranking-tab="<?php echo htmlspecialchars($ranking_key, ENT_QUOTES, 'UTF-8'); ?>"
                    ><?php echo htmlspecialchars($ranking['label'], ENT_QUOTES, 'UTF-8'); ?></button>
                <?php endforeach; ?>
            </div>

            <div class="player-ranking-panels">
                <?php foreach ($rankings as $ranking_key => $ranking): ?>
                    <?php $is_active = $ranking_key === 'win_rate'; ?>
                    <section
                        class="player-ranking-panel"
                        id="player-ranking-panel-<?php echo htmlspecialchars($ranking_key, ENT_QUOTES, 'UTF-8'); ?>"
                        role="tabpanel"
                        aria-labelledby="player-ranking-tab-<?php echo htmlspecialchars($ranking_key, ENT_QUOTES, 'UTF-8'); ?>"
                        data-player-ranking-panel="<?php echo htmlspecialchars($ranking_key, ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo $is_active ? '' : 'hidden'; ?>
                    >
                        <?php if (empty($ranking['rows'])): ?>
                            <p class="player-ranking-empty">В рейтинге пока нет игроков.</p>
                        <?php else: ?>
                            <ol class="player-ranking-list">
                                <?php foreach ($ranking['rows'] as $rank => $row): ?>
                                    <?php
                                    $profile_role = normalize_staff_role($row['staff_role'], $row['is_admin'] === 1);
                                    $profile_url = 'players.php?username=' . rawurlencode($row['username']);
                                    ?>
                                    <li class="player-ranking-row">
                                        <span class="player-ranking-place"><?php echo $rank + 1; ?></span>
                                        <a class="player-ranking-player" href="<?php echo htmlspecialchars($profile_url, ENT_QUOTES, 'UTF-8'); ?>">
                                            <strong><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <?php if ($profile_role !== 'player'): ?>
                                                <span class="player-ranking-role"><?php echo htmlspecialchars(staff_role_info($profile_role)['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php endif; ?>
                                        </a>
                                        <span class="player-ranking-metric">
                                            <strong><?php echo htmlspecialchars(format_player_ranking_value($ranking_key, $row['metric_value']), ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <small><?php echo number_format($row['total_battles']); ?> боёв</small>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
```

The fixed ranking keys come from `player_ranking_definitions()`, so the IDs,
labels, and SQL selection remain server-controlled. Use the existing escaping
style for every dynamic attribute and text node.

- [ ] **Step 6: Add the autocomplete markup without removing form fallback.**

Wrap the existing search input in a positioned field container and add the
listbox hook. Keep the same `name="username"`, GET action, submit button,
length limits, and server-rendered value:

```php
            <div class="player-search-field">
                <input
                    class="form-control"
                    type="search"
                    id="playerUsername"
                    name="username"
                    value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Введите никнейм игрока..."
                    minlength="3"
                    maxlength="24"
                    autocomplete="off"
                    aria-autocomplete="list"
                    aria-controls="playerSuggestions"
                    aria-expanded="false"
                    data-player-input
                    required
                >
                <div class="player-suggestions" id="playerSuggestions" role="listbox" data-player-suggestions hidden></div>
            </div>
```

- [ ] **Step 7: Run the focused contract group, PHP lint, and the ranking unit test.**

Run:

```text
C:/xampp/php/php.exe tests/ui_contract_test.php --group=account
C:/xampp/php/php.exe -l players.php
C:/xampp/php/php.exe tests/player_rankings_test.php
```

Expected: all account contracts pass, the page has no syntax errors, and the
ranking definitions test still passes.

### Task 3: Implement Tabs and Autocomplete as Progressive Enhancement

**Files:**
- Create: `js/players.js`
- Test: `tests/player_interactions_test.js`

**Interfaces:**
- `window.OrionPlayers.initialize()` initializes the page when the page script is loaded.
- `data-player-ranking-tab` and `data-player-ranking-panel` control the five panels.
- `data-player-input` and `data-player-suggestions` control the autocomplete listbox.
- The endpoint response is `{ suggestions: Array<{ username: string, total_battles: number }> }`.

- [ ] **Step 1: Create a fake-DOM test harness and failing interaction assertions.**

Create `tests/player_interactions_test.js` using `assert`, `fs`, `path`, and
`vm`, following the existing dependency-free style. The harness must provide
elements with `classList`, `hidden`, `value`, `textContent`, attributes,
`addEventListener`, `querySelectorAll`, `appendChild`, `focus`, and `contains`,
plus a fake `window.fetch` that returns deferred promises.

The test must load `js/players.js` in a VM, call
`window.OrionPlayers.initialize()`, then assert these behaviors. The
`createHarness()` helper must return `activeTab`, `nextTab`, `input`,
`suggestions`, `tabEvents`, `inputEvents`, `fetchCalls`, `resolveFetch(index,
payload)`, `rejectFetch(index)`, `clock.runNext()`, `documentElement`, and
`flushMicrotasks()`. Its fake `appendChild()` must make rendered suggestion
text available through `suggestions.textContent`, and every fake suggestion
link must be returned by `suggestions.querySelectorAll('[data-player-suggestion]')`.
The assertions run inside an async function so promise callbacks are flushed
with `await flushMicrotasks()` after each deferred response.

```javascript
assert.strictEqual(activeTab.getAttribute('aria-selected'), 'true', 'Win Rate tab should start selected');
tabEvents.keydown({ key: 'ArrowRight', preventDefault() {} });
assert.strictEqual(activeTab.getAttribute('aria-selected'), 'false', 'ArrowRight should move to the next ranking');
assert.strictEqual(nextTab.getAttribute('aria-selected'), 'true', 'next ranking should become selected');

input.value = 'ab';
inputEvents.input({ target: input });
clock.runNext();
assert.strictEqual(fetchCalls[0].url, 'players.php?ajax=player-suggestions&q=ab', 'autocomplete URL must encode the prefix');

resolveFetch(0, { suggestions: [
    { username: 'Alpha', total_battles: 12 },
    { username: 'Abel', total_battles: 4 },
] });
await flushMicrotasks();
assert.strictEqual(suggestions.hidden, false, 'matching suggestions should open the listbox');
assert.strictEqual(input.getAttribute('aria-expanded'), 'true', 'open listbox must be exposed to accessibility APIs');

input.value = 'ac';
inputEvents.input({ target: input });
clock.runNext();
resolveFetch(1, { suggestions: [{ username: 'Active', total_battles: 20 }] });
await flushMicrotasks();
resolveFetch(0, { suggestions: [{ username: 'Stale', total_battles: 99 }] });
await flushMicrotasks();
assert.ok(suggestions.textContent.includes('Active'), 'newer autocomplete results should render');
assert.ok(!suggestions.textContent.includes('Stale'), 'stale responses must not replace newer results');

inputEvents.keydown({ key: 'ArrowDown', preventDefault() {} });
inputEvents.keydown({ key: 'Enter', preventDefault() { this.prevented = true; } });
assert.strictEqual(window.location.href, 'players.php?username=Active', 'Enter should open the selected profile');

documentElement.setAttribute('lang', 'uk');
input.value = 'ук';
inputEvents.input({ target: input });
clock.runNext();
resolveFetch(2, { suggestions: [{ username: 'Гравець', total_battles: 3 }] });
await flushMicrotasks();
assert.ok(suggestions.textContent.includes('боїв'), 'Ukrainian suggestion metadata should be localized');
```

The test should also assert that a one-character input hides the list without
calling `fetch`, Escape closes the list, and a rejected fetch hides the list
without exposing an exception message in the DOM.

- [ ] **Step 2: Run the new JavaScript test and verify it fails before the script exists.**

Run:

```text
node tests/player_interactions_test.js
```

Expected: FAIL because `js/players.js` and `window.OrionPlayers.initialize()`
do not exist yet.

- [ ] **Step 3: Implement `js/players.js` with tab and listbox behavior.**

Use one page-local IIFE with these exact implementation rules:

```javascript
(function () {
    'use strict';

    var DEBOUNCE_MS = 180;
    var STRINGS = {
        ru: { battles: 'боёв' },
        uk: { battles: 'боїв' }
    };

    function text(key) {
        var lang = document.documentElement.getAttribute('lang') === 'uk' ? 'uk' : 'ru';
        return STRINGS[lang][key];
    }

    function initializeRankingTabs() {
        var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-player-ranking-tab]'));
        var panels = Array.prototype.slice.call(document.querySelectorAll('[data-player-ranking-panel]'));
        if (!tabs.length || !panels.length) return;

        function activate(tab, moveFocus) {
            var key = tab.getAttribute('data-player-ranking-tab');
            tabs.forEach(function (candidate) {
                var selected = candidate === tab;
                candidate.classList.toggle('is-active', selected);
                candidate.setAttribute('aria-selected', selected ? 'true' : 'false');
                candidate.tabIndex = selected ? 0 : -1;
            });
            panels.forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-player-ranking-panel') !== key;
            });
            if (moveFocus) tab.focus();
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () { activate(tab, false); });
            tab.addEventListener('keydown', function (event) {
                if (['ArrowLeft', 'ArrowRight', 'Home', 'End'].indexOf(event.key) === -1) return;
                event.preventDefault();
                var next = index;
                if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
                if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
                if (event.key === 'Home') next = 0;
                if (event.key === 'End') next = tabs.length - 1;
                activate(tabs[next], true);
            });
        });
    }

    function initializeAutocomplete() {
        var input = document.querySelector('[data-player-input]');
        var suggestions = document.querySelector('[data-player-suggestions]');
        if (!input || !suggestions) return;

        var timer = null;
        var requestSerial = 0;
        var activeIndex = -1;

        function links() {
            return Array.prototype.slice.call(suggestions.querySelectorAll('[data-player-suggestion]'));
        }

        function closeList() {
            activeIndex = -1;
            suggestions.hidden = true;
            suggestions.textContent = '';
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        }

        function setActive(index) {
            var items = links();
            activeIndex = items.length ? (index + items.length) % items.length : -1;
            items.forEach(function (item, itemIndex) {
                item.setAttribute('aria-selected', itemIndex === activeIndex ? 'true' : 'false');
            });
            if (activeIndex >= 0) input.setAttribute('aria-activedescendant', items[activeIndex].id);
            else input.removeAttribute('aria-activedescendant');
        }

        function renderList(items) {
            suggestions.textContent = '';
            activeIndex = -1;
            if (!items.length) {
                closeList();
                return;
            }
            items.forEach(function (item, index) {
                var link = document.createElement('a');
                var battles = Number(item.total_battles) || 0;
                link.id = 'player-suggestion-' + index;
                link.href = 'players.php?username=' + encodeURIComponent(String(item.username || ''));
                link.setAttribute('role', 'option');
                link.setAttribute('aria-selected', 'false');
                link.setAttribute('data-player-suggestion', '');
                link.textContent = String(item.username || '') + (battles ? ' · ' + battles + ' ' + text('battles') : '');
                suggestions.appendChild(link);
            });
            suggestions.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function request(query) {
            var serial = ++requestSerial;
            window.fetch('players.php?ajax=player-suggestions&q=' + encodeURIComponent(query), {
                headers: { Accept: 'application/json' }
            }).then(function (response) {
                if (!response.ok) throw new Error('autocomplete request failed');
                return response.json();
            }).then(function (payload) {
                if (serial !== requestSerial) return;
                renderList(Array.isArray(payload.suggestions) ? payload.suggestions : []);
            }).catch(function () {
                if (serial === requestSerial) closeList();
            });
        }

        input.addEventListener('input', function () {
            var query = String(input.value || '').trim();
            requestSerial += 1;
            if (timer) window.clearTimeout(timer);
            if (query.length < 2) {
                closeList();
                return;
            }
            timer = window.setTimeout(function () { request(query); }, DEBOUNCE_MS);
        });

        input.addEventListener('keydown', function (event) {
            var items = links();
            if (event.key === 'ArrowDown' && items.length) {
                event.preventDefault();
                setActive(activeIndex + 1);
            } else if (event.key === 'ArrowUp' && items.length) {
                event.preventDefault();
                setActive(activeIndex - 1);
            } else if (event.key === 'Enter' && activeIndex >= 0 && items[activeIndex]) {
                event.preventDefault();
                window.location.href = items[activeIndex].href;
            } else if (event.key === 'Escape') {
                closeList();
            }
        });

        document.addEventListener('click', function (event) {
            if (!input.parentElement.contains(event.target)) closeList();
        });
    }

    function initializePlayers() {
        initializeRankingTabs();
        initializeAutocomplete();
    }

    window.OrionPlayers = { initialize: initializePlayers };
    document.addEventListener('DOMContentLoaded', initializePlayers);
})();
```

When adapting the snippet to the fake DOM, preserve the same event names,
endpoint URL, 180 ms debounce, serial check, `encodeURIComponent`, and ARIA
state changes. Do not add a second shared document-level controller to
`js/site.js`.

- [ ] **Step 4: Run the interaction test in both language branches.**

Run:

```text
node tests/player_interactions_test.js
```

Expected: `Player interaction DOM checks passed.`

### Task 4: Add Styling, Localization, and Asset Cache Busting

**Files:**
- Modify: `style.css`
- Modify: `includes/header.php`
- Modify: `lang.php`
- Modify: `tests/ui_contract_test.php`

**Interfaces:**
- The markup from Task 2 is styled by `.player-rankings`, `.player-ranking-*`, `.player-search-field`, and `.player-suggestions` classes.
- `style.css` is loaded as `style.css?v=28` after this task.
- New server-rendered labels have exact entries in `i18n_uk_map()`.

- [ ] **Step 1: Add failing CSS/localization contracts.**

In the `account` group of `tests/ui_contract_test.php`, add:

```php
        expectContains('style.css', '.player-rankings', 'Player ranking styles are missing');
        expectContains('style.css', '.player-suggestions', 'Player suggestion styles are missing');
        expectContains('style.css', '@media (max-width: 760px)', 'Player ranking mobile styles are missing');
        expectContains('includes/header.php', 'href="style.css?v=28"', 'Global style cache version was not bumped');
        expectContains('lang.php', "'Топ игроков' => 'Топ гравців'", 'Player ranking title is not localized');
        expectContains('lang.php', "'Средний опыт' => 'Середній досвід'", 'Average XP ranking is not localized');
        expectContains('lang.php', "'Рейтинг временно недоступен.' => 'Рейтинг тимчасово недоступний.'", 'Ranking error is not localized');
```

- [ ] **Step 2: Run the focused account contract and verify the new style/localization checks fail.**

Run:

```text
C:/xampp/php/php.exe tests/ui_contract_test.php --group=account
```

Expected: the new style, cache-version, and localization expectations fail.

- [ ] **Step 3: Add token-based ranking and autocomplete styles.**

Add the ranking styles next to the existing `.players-hero` and `.player-search`
rules. Use these declarations as the baseline and keep selectors scoped to
the players page:

```css
.player-search-field { position: relative; flex: 1 1 auto; min-width: 0; }
.player-search-field .form-control { width: 100%; }
.player-suggestions { position: absolute; z-index: 20; top: calc(100% + 8px); right: 0; left: 0; overflow: hidden; background: var(--color-elevated); border: 1px solid var(--color-line-strong); border-radius: var(--radius-md); box-shadow: var(--shadow-float); }
.player-suggestions[hidden] { display: none; }
.player-suggestions a { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 16px; color: var(--color-ink); border-bottom: 1px solid var(--color-line); font-size: 14px; font-weight: 700; }
.player-suggestions a:last-child { border-bottom: 0; }
.player-suggestions a:hover,
.player-suggestions a[aria-selected="true"] { color: var(--color-accent); background: var(--color-accent-soft); }
.player-rankings { width: min(100%, 960px); margin: 0 auto 34px; padding: 26px; background: var(--color-surface-soft); border: 1px solid var(--color-line); border-radius: var(--radius-lg); }
.player-rankings-header { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 20px; }
.player-rankings-header h2 { margin: 0; font-size: 24px; }
.player-rankings-header p:last-child { max-width: 360px; margin: 0; color: var(--color-muted); font-size: 13px; text-align: right; }
.player-ranking-tabs { display: flex; gap: 8px; overflow-x: auto; margin-bottom: 14px; padding-bottom: 4px; }
.player-ranking-tab { flex: 0 0 auto; padding: 10px 14px; color: var(--color-muted); background: transparent; border: 1px solid var(--color-line); border-radius: 999px; font: 700 12px/1.2 var(--font-sans); cursor: pointer; }
.player-ranking-tab:hover,
.player-ranking-tab.is-active { color: var(--color-on-action); background: var(--color-action); border-color: var(--color-action); }
.player-ranking-panel[hidden] { display: none; }
.player-ranking-list { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
.player-ranking-row { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; align-items: center; gap: 12px; padding: 13px 14px; background: var(--color-elevated); border: 1px solid var(--color-line); border-radius: var(--radius-sm); }
.player-ranking-place { color: var(--color-accent); font: 700 15px/1 var(--font-mono); text-align: center; }
.player-ranking-player { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; min-width: 0; color: var(--color-ink); }
.player-ranking-player strong { overflow-wrap: anywhere; }
.player-ranking-role { padding: 3px 7px; color: var(--color-accent); background: var(--color-accent-soft); border-radius: 999px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
.player-ranking-metric { display: grid; gap: 3px; min-width: 90px; text-align: right; }
.player-ranking-metric strong { color: var(--color-accent); font: 700 16px/1.1 var(--font-display); }
.player-ranking-metric small { color: var(--color-muted); font-size: 11px; }
.player-ranking-empty,
.player-ranking-unavailable { margin: 0; padding: 24px; color: var(--color-muted); background: var(--color-elevated); border: 1px dashed var(--color-line-strong); border-radius: var(--radius-sm); text-align: center; }
```

Add the responsive rules in the existing mobile section:

```css
@media (max-width: 760px) {
    .player-rankings { padding: 20px; }
    .player-rankings-header { display: grid; align-items: start; gap: 8px; }
    .player-rankings-header p:last-child { text-align: left; }
    .player-ranking-row { grid-template-columns: 30px minmax(0, 1fr); }
    .player-ranking-metric { grid-column: 2; justify-self: start; display: flex; align-items: baseline; gap: 8px; text-align: left; }
}
```

Do not use undefined shadow/background tokens and do not add a motion rule
without a reduced-motion-safe behavior.

- [ ] **Step 4: Bump the global stylesheet version without changing other header behavior.**

Change only the asset query in `includes/header.php`:

```php
    <link rel="stylesheet" href="style.css?v=28">
```

- [ ] **Step 5: Add the exact localization mappings.**

In the existing player/profile section of `lang.php`, add these mappings:

```php
        'ЛИДЕРЫ СЕРВЕРА' => 'ЛІДЕРИ СЕРВЕРА',
        'Топ игроков' => 'Топ гравців',
        'Лучшие результаты игроков по основным показателям.' => 'Найкращі результати гравців за основними показниками.',
        'Категории рейтинга' => 'Категорії рейтингу',
        'Фраги' => 'Фраги',
        'Средний опыт' => 'Середній досвід',
        'В рейтинге пока нет игроков.' => 'У рейтингу поки немає гравців.',
        'Рейтинг временно недоступен.' => 'Рейтинг тимчасово недоступний.',
```

Existing mappings for `Средний урон`, `Игроки`, and staff labels should be
reused rather than duplicated. JavaScript's `боёв`/`боїв` values stay in
`js/players.js` and must not be added as a PHP-only translation assumption.

- [ ] **Step 6: Run the CSS/localization contract and lint changed PHP files.**

Run:

```text
C:/xampp/php/php.exe tests/ui_contract_test.php --group=account
C:/xampp/php/php.exe -l players.php
C:/xampp/php/php.exe -l includes/player_rankings.php
C:/xampp/php/php.exe -l lang.php
```

Expected: all account contracts pass and every lint command reports no syntax
errors.

### Task 5: Run the Complete Verification Set

**Files:**
- Verify only; no source changes expected unless a test identifies a concrete defect.

- [ ] **Step 1: Run all feature-specific tests.**

```text
C:/xampp/php/php.exe tests/player_rankings_test.php
node tests/player_interactions_test.js
```

Expected: both tests print their `passed` messages and exit with status 0.

- [ ] **Step 2: Run every existing repository check required by `CLAUDE.md`.**

```text
C:/xampp/php/php.exe tests/ui_contract_test.php
C:/xampp/php/php.exe tests/staff_permissions_test.php
node tests/site_interactions_test.js
node tests/admin_mirror_lifecycle_test.js
C:/xampp/php/php.exe tests/player_stats_test.php
```

Expected: all commands exit with status 0. The full UI contract must report
all groups passed, including the updated account group.

- [ ] **Step 3: Check syntax and whitespace for every changed PHP/JS/CSS file.**

```text
C:/xampp/php/php.exe -l players.php
C:/xampp/php/php.exe -l includes/player_rankings.php
C:/xampp/php/php.exe -l lang.php
git diff --check
```

Expected: no PHP syntax errors and no whitespace errors.

- [ ] **Step 4: Inspect the final diff and worktree boundary.**

Run:

```text
git status --short
git diff -- players.php includes/player_rankings.php js/players.js style.css includes/header.php lang.php tests/player_rankings_test.php tests/player_interactions_test.js tests/ui_contract_test.php
```

Confirm that the diff contains only the players-page feature and its tests,
while the pre-existing unrelated modifications remain untouched. Do not
commit or revert anything without an explicit user instruction.
