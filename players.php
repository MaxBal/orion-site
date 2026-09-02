<?php
require_once 'db.php';
require_once __DIR__ . '/includes/player_stats.php';
require_once __DIR__ . '/includes/player_rankings.php';

if (($_GET['ajax'] ?? '') === 'player-suggestions') {
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, max-age=0');
    $raw_query = $_GET['q'] ?? '';
    $query = is_string($raw_query) ? trim($raw_query) : '';
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
            $like_query = str_replace(
                ['\\', '%', '_'],
                ['\\\\', '\\%', '\\_'],
                $normalized_query
            );
            $stmt = $pdo->prepare(
                "SELECT a.username, COALESCE(d.total_battles, 0) AS total_battles
                 FROM accounts AS a
                 LEFT JOIN dossier AS d ON d.account_id = a.id
                 WHERE a.normalized_name LIKE ? ESCAPE '\\\\'
                 ORDER BY a.normalized_name ASC, a.username ASC
                 LIMIT 8"
            );
            $stmt->execute([$like_query . '%']);
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

$username = trim((string)($_GET['username'] ?? ''));
$has_search = array_key_exists('username', $_GET);
$search_error = '';
$player = null;
$stats = calculate_player_stats(null);
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

if ($has_search) {
    if ($username === '') {
        $search_error = 'Введите никнейм игрока.';
    } elseif (strlen($username) < 3 || strlen($username) > 24) {
        $search_error = 'Никнейм должен быть от 3 до 24 символов.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT a.id, a.username, a.is_admin, a.staff_role, dl.discord_username
                 FROM accounts AS a
                 LEFT JOIN account_discord_links AS dl ON dl.account_id = a.id
                 WHERE a.username = ? OR a.normalized_name = ?
                 LIMIT 1"
            );
            $stmt->execute([$username, $username]);
            $player = $stmt->fetch();

            if ($player) {
                $stmt = $pdo->prepare(
                    "SELECT total_battles, wins, losses, draws, frags, damage_dealt,
                            damage_received, shots, hits, max_xp, max_damage, max_frags, total_xp
                     FROM dossier
                     WHERE account_id = ?"
                );
                $stmt->execute([$player['id']]);
                $stats = calculate_player_stats($stmt->fetch());
            } else {
                $search_error = 'Игрок не найден. Проверьте никнейм и попробуйте снова.';
            }
        } catch (Exception $e) {
            error_log('Public player profile load error: ' . $e->getMessage());
            $player = null;
            $search_error = 'Не удалось загрузить профиль. Попробуйте позже.';
        }
    }
}

$page_title = 'Профили игроков — Project Orion 0.8.2';
$page_description = 'Поиск игроков Project Orion по никнейму и публичная статистика боев.';
$page_path = 'players.php';
$active_page = 'players';
$banner_subtext = 'Игровой сервер · 0.8.2';
$page_scripts = ['js/players.js?v=2'];
$player_search_placeholder = function_exists('i18n_translate_text')
    ? i18n_translate_text('Введите никнейм игрока...', current_lang())
    : 'Введите никнейм игрока...';
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell players-page">
    <header class="players-hero">
        <p class="eyebrow">PROJECT ORION</p>
        <h1>Профили игроков</h1>
        <p>Найдите игрока по точному никнейму и посмотрите его боевую статистику.</p>

        <form class="player-search" action="players.php" method="GET" role="search" aria-label="Поиск профиля игрока">
            <label class="visually-hidden" for="playerUsername">Никнейм игрока</label>
            <div class="player-search-field">
                <input
                    class="form-control notranslate"
                    translate="no"
                    type="search"
                    id="playerUsername"
                    name="username"
                    value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="<?php echo htmlspecialchars($player_search_placeholder, ENT_QUOTES, 'UTF-8'); ?>"
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
            <button class="btn btn-primary" type="submit">Найти профиль</button>
        </form>

        <?php if (!$has_search && isset($_SESSION['username'])): ?>
            <a class="player-own-profile" href="players.php?username=<?php echo rawurlencode((string)$_SESSION['username']); ?>">Показать мой профиль</a>
        <?php endif; ?>
    </header>

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
                                            <strong class="notranslate" translate="no"><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
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

    <div class="player-search-result" aria-live="polite">
        <?php if ($search_error !== ''): ?>
            <div class="alert alert-danger player-search-alert"><?php echo htmlspecialchars($search_error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php elseif ($player): ?>
            <?php $profile_role = normalize_staff_role($player['staff_role'] ?? '', intval($player['is_admin']) === 1); ?>
            <article class="profile-card player-profile-card">
                <header class="profile-card-header">
                    <p class="eyebrow">ПУБЛИЧНЫЙ ПРОФИЛЬ</p>
                    <div class="profile-identity">
                         <h2 class="profile-username notranslate" translate="no"><?php echo htmlspecialchars($player['username'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <?php if ($profile_role !== 'player'): ?>
                            <span class="profile-role"><?php echo htmlspecialchars(staff_role_info($profile_role)['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php else: ?>
                        <span class="profile-role profile-role--player">Игрок</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($player['discord_username'])): ?>
                    <p class="profile-discord-handle"><span>Discord:</span> <strong class="notranslate" translate="no">@<?php echo htmlspecialchars($player['discord_username'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                <?php endif; ?>
            </header>

                <div class="player-stat-overview">
                    <div class="player-stat-card">
                        <strong><?php echo number_format($stats['total_battles']); ?></strong>
                        <span>Бои</span>
                    </div>
                    <div class="player-stat-card player-stat-card--accent">
                        <strong><?php echo $stats['win_rate']; ?>%</strong>
                        <span>Процент побед</span>
                    </div>
                    <div class="player-stat-card">
                        <strong><?php echo number_format($stats['frags']); ?></strong>
                        <span>Уничтожено</span>
                    </div>
                    <div class="player-stat-card">
                        <strong><?php echo number_format($stats['avg_damage']); ?></strong>
                        <span>Средний урон</span>
                    </div>
                </div>

                <h3 class="profile-section-title">Статистика боев</h3>
                <div class="stat-grid">
                    <div>
                        <div class="stat-row">
                            <span class="stat-label">Сыграно боев:</span>
                            <span class="stat-value"><?php echo number_format($stats['total_battles']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Победы (Win Rate):</span>
                            <span class="stat-value stat-value--win"><?php echo number_format($stats['wins']); ?> (<?php echo $stats['win_rate']; ?>%)</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Поражения:</span>
                            <span class="stat-value stat-value--loss"><?php echo number_format($stats['losses']); ?> (<?php echo $stats['loss_rate']; ?>%)</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Ничьи:</span>
                            <span class="stat-value"><?php echo number_format($stats['draws']); ?> (<?php echo $stats['draw_rate']; ?>%)</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Уничтожено врагов (Frags):</span>
                            <span class="stat-value"><?php echo number_format($stats['frags']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Максимум уничтожено за бой:</span>
                            <span class="stat-value"><?php echo number_format($stats['max_frags']); ?></span>
                        </div>
                    </div>
                    <div>
                        <div class="stat-row">
                            <span class="stat-label">Средний урон за бой:</span>
                            <span class="stat-value"><?php echo number_format($stats['avg_damage']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Максимальный урон за бой:</span>
                            <span class="stat-value"><?php echo number_format($stats['max_damage']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Точность (Hits/Shots):</span>
                            <span class="stat-value"><?php echo $stats['hit_ratio']; ?>% (<?php echo number_format($stats['hits']); ?>/<?php echo number_format($stats['shots']); ?>)</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Средний опыт за бой:</span>
                            <span class="stat-value"><?php echo number_format($stats['avg_xp']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Максимальный опыт за бой:</span>
                            <span class="stat-value"><?php echo number_format($stats['max_xp']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Нанесено урона:</span>
                            <span class="stat-value"><?php echo number_format($stats['damage_dealt']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Получено урона:</span>
                            <span class="stat-value"><?php echo number_format($stats['damage_received']); ?></span>
                        </div>
                    </div>
                </div>
            </article>
        <?php elseif (!$has_search): ?>
            <div class="player-empty-state">
                <strong>Найдите профиль игрока</strong>
                <p>Начните с поиска — здесь появятся основные показатели и подробная статистика игрока.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
