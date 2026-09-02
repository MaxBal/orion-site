<?php
if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en' && !defined('ORION_SKIP_LANGUAGE_REDIRECT')) {
    define('ORION_SKIP_LANGUAGE_REDIRECT', true);
}
require_once 'db.php';
require_once __DIR__ . '/includes/player_stats.php';
require_once __DIR__ . '/discord_config.php';
require_once __DIR__ . '/includes/discord_oauth.php';

if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en') {
    $_SESSION['lang'] = 'en';
}
$ui_lang = function_exists('current_lang') ? current_lang() : 'ru';
if (($_SESSION['lang'] ?? '') === 'en') {
    $ui_lang = 'en';
}
if (!in_array($ui_lang, ['ru', 'uk', 'en'], true)) {
    $ui_lang = 'ru';
}
$locale_url = static function ($path) use ($ui_lang) {
    $path = (string)$path;
    if ($ui_lang === 'ru' || preg_match('/(?:^|[?&])lang=/', $path)) {
        return $path;
    }
    return $path . (strpos($path, '?') === false ? '?' : '&') . 'lang=' . rawurlencode($ui_lang);
};
$copy = $ui_lang === 'en' ? [
    'discord_linked' => 'Discord linked successfully.',
    'discord_unlinked' => 'Discord unlinked successfully.',
    'discord_denied' => 'Discord linking was cancelled.',
    'discord_already' => 'This Discord account is already linked to another account.',
    'discord_existing' => 'Unlink your current Discord account first.',
    'discord_config' => 'Discord linking is not configured yet.',
    'discord_error' => 'Could not link Discord. Please try again.',
    'session' => 'The session has expired. Refresh the page.',
    'required' => 'Please fill in all fields.',
    'new_password_short' => 'The new password must be at least 6 characters long.',
    'new_password_long' => 'The new password is too long (maximum 128 characters).',
    'new_password_match' => 'The new passwords do not match.',
    'password_changed' => 'Password changed successfully!',
    'wrong_password' => 'The current password is incorrect.',
    'internal' => 'An internal error occurred. Please try again later.',
    'page_title' => 'My account: Project Orion server 0.8.2',
    'page_description' => 'Player account for the Project Orion game server 0.8.2.',
    'banner_subtext' => 'Game server · 0.8.2',
    'player_role' => 'Player',
    'public_profile' => 'View public profile',
    'credits' => 'Credits',
    'gold' => 'Gold',
    'free_xp' => 'Free experience',
    'battle_stats' => 'Battle statistics',
    'battles' => 'Battles',
    'win_rate' => 'Win rate',
    'destroyed' => 'Destroyed',
    'average_damage' => 'Average damage',
    'battles_played' => 'Battles played:',
    'wins' => 'Victories (Win Rate):',
    'losses' => 'Defeats:',
    'draws' => 'Draws:',
    'enemies_destroyed' => 'Enemies destroyed (Frags):',
    'max_frags' => 'Maximum destroyed in one battle:',
    'damage_dealt' => 'Damage dealt:',
    'damage_received' => 'Damage received:',
    'average_damage_battle' => 'Average damage per battle:',
    'accuracy' => 'Accuracy (Hits/Shots):',
    'average_xp_battle' => 'Average experience per battle:',
    'max_xp' => 'Maximum experience per battle:',
    'garage_slots' => 'Garage slots:',
    'barracks' => 'Barracks spaces:',
    'security' => 'SECURITY',
    'change_password' => 'Change password',
    'current_password' => 'Current password',
    'new_password' => 'New password',
    'confirm_password' => 'Confirm password',
    'update_password' => 'Update password',
    'social_links' => 'SOCIAL LINKS',
    'linked_account' => 'Linked account',
    'unlink_discord' => 'Unlink Discord',
    'discord_prompt' => 'Link Discord to confirm its connection to your Project Orion account.',
    'link_discord' => 'Link Discord',
    'discord_unavailable' => 'Discord linking is currently unavailable.',
] : [
    'discord_linked' => 'Discord успешно привязан.',
    'discord_unlinked' => 'Discord успешно отвязан.',
    'discord_denied' => 'Привязка Discord отменена.',
    'discord_already' => 'Этот Discord уже привязан к другому аккаунту.',
    'discord_existing' => 'Сначала отвяжите текущий Discord.',
    'discord_config' => 'Привязка Discord пока не настроена.',
    'discord_error' => 'Не удалось привязать Discord. Попробуйте ещё раз.',
    'session' => 'Сессия устарела. Обновите страницу.',
    'required' => 'Пожалуйста, заполните все поля.',
    'new_password_short' => 'Новый пароль должен быть не менее 6 символов.',
    'new_password_long' => 'Новый пароль слишком длинный (максимум 128 символов).',
    'new_password_match' => 'Новые пароли не совпадают.',
    'password_changed' => 'Пароль успешно изменен!',
    'wrong_password' => 'Неверный текущий пароль.',
    'internal' => 'Произошла внутренняя ошибка. Попробуйте позже.',
    'page_title' => 'Личный кабинет — сервер Project Orion 0.8.2',
    'page_description' => 'Личный кабинет игрока сервера Project Orion 0.8.2.',
    'banner_subtext' => 'Игровой сервер · 0.8.2',
    'player_role' => 'Игрок',
    'public_profile' => 'Посмотреть публичный профиль',
    'credits' => 'Кредиты',
    'gold' => 'Золото',
    'free_xp' => 'Свободный опыт',
    'battle_stats' => 'Статистика боев',
    'battles' => 'Бои',
    'win_rate' => 'Процент побед',
    'destroyed' => 'Уничтожено',
    'average_damage' => 'Средний урон',
    'battles_played' => 'Сыграно боев:',
    'wins' => 'Победы (Win Rate):',
    'losses' => 'Поражения:',
    'draws' => 'Ничьи:',
    'enemies_destroyed' => 'Уничтожено врагов (Frags):',
    'max_frags' => 'Максимум уничтожено за бой:',
    'damage_dealt' => 'Нанесено урона:',
    'damage_received' => 'Получено урона:',
    'average_damage_battle' => 'Средний урон за бой:',
    'accuracy' => 'Точность (Hits/Shots):',
    'average_xp_battle' => 'Средний опыт за бой:',
    'max_xp' => 'Максимальный опыт за бой:',
    'garage_slots' => 'Слоты в ангаре:',
    'barracks' => 'Места в казарме:',
    'security' => 'БЕЗОПАСНОСТЬ',
    'change_password' => 'Смена пароля',
    'current_password' => 'Текущий пароль',
    'new_password' => 'Новый пароль',
    'confirm_password' => 'Подтвердите пароль',
    'update_password' => 'Обновить пароль',
    'social_links' => 'СОЦИАЛЬНЫЕ СВЯЗИ',
    'linked_account' => 'Привязанный аккаунт',
    'unlink_discord' => 'Отвязать Discord',
    'discord_prompt' => 'Привяжите Discord, чтобы подтвердить связь с аккаунтом Project Orion.',
    'link_discord' => 'Привязать Discord',
    'discord_unavailable' => 'Привязка Discord пока недоступна.',
];

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $locale_url('login.php'));
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$discord_status = (string)($_GET['discord'] ?? '');
if ($discord_status === 'linked') {
    $success = $copy['discord_linked'];
} elseif ($discord_status === 'unlinked') {
    $success = $copy['discord_unlinked'];
} elseif ($discord_status === 'denied') {
    $error = $copy['discord_denied'];
} elseif ($discord_status === 'already') {
    $error = $copy['discord_already'];
} elseif ($discord_status === 'existing') {
    $error = $copy['discord_existing'];
} elseif ($discord_status === 'config') {
    $error = $copy['discord_config'];
} elseif ($discord_status === 'state' || $discord_status === 'error') {
    $error = $copy['discord_error'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = $copy['session'];
    } else {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $new_password_confirm = $_POST['new_password_confirm'] ?? '';

    if (empty($current_password) || empty($new_password)) {
        $error = $copy['required'];
    } elseif (strlen($new_password) < 6) {
        $error = $copy['new_password_short'];
    } elseif (strlen($new_password) > 128) {
        $error = $copy['new_password_long'];
    } elseif ($new_password !== $new_password_confirm) {
        $error = $copy['new_password_match'];

    } else {
        try {
            $stmt = $pdo->prepare("SELECT password_hash FROM accounts WHERE id = ?");
            $stmt->execute([$user_id]);
            $current_hash = $stmt->fetchColumn();

            if (hash('sha256', $current_password) === $current_hash) {
                $new_hash = hash('sha256', $new_password);
                $update_stmt = $pdo->prepare("UPDATE accounts SET password_hash = ? WHERE id = ?");
                $update_stmt->execute([$new_hash, $user_id]);
                orion_revoke_account_remember_tokens($pdo, $user_id);
                orion_issue_remember_token($pdo, $user_id);
                $success = $copy['password_changed'];
            } else {
                $error = $copy['wrong_password'];
            }
        } catch (Exception $e) {
            error_log("Profile password change error: " . $e->getMessage());
            $error = $copy['internal'];
        }
    }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlink_discord'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = $copy['session'];
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM account_discord_links WHERE account_id = ?");
            $stmt->execute([$user_id]);
            $success = $copy['discord_unlinked'];
        } catch (Exception $e) {
            error_log("Discord unlink error: " . $e->getMessage());
            $error = $copy['internal'];
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT username, credits, gold, free_xp, slots, berths, is_admin, staff_role FROM accounts WHERE id = ?");
    $stmt->execute([$user_id]);
    $account = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT discord_id, discord_username, access_token_encrypted, refresh_token_encrypted, token_expires_at, username_checked_at FROM account_discord_links WHERE account_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $discord_link = $stmt->fetch() ?: null;
    $discord_link = discord_sync_account_link($pdo, $user_id, $discord_link);

    $stmt = $pdo->prepare("SELECT total_battles, wins, losses, draws, frags, damage_dealt, damage_received, shots, hits, max_xp, max_damage, max_frags, total_xp FROM dossier WHERE account_id = ?");
    $stmt->execute([$user_id]);
    $dossier = $stmt->fetch();
} catch (Exception $e) {
    error_log("Profile load error: " . $e->getMessage());
    $account = null;
    $dossier = null;
}

if (!$account) {
    orion_forget_remember_token($pdo);
    orion_destroy_current_session();
    header('Location: ' . $locale_url('login.php'));
    exit;
}

$auto_sync_attempted_at = intval($_SESSION['discord_auto_sync_attempted_at'] ?? 0);
if ($discord_link && empty($discord_link['refresh_token_encrypted']) && DISCORD_OAUTH_ENABLED && $auto_sync_attempted_at < time() - 2592000) {
    $_SESSION['discord_auto_sync_attempted_at'] = time();
    header('Location: ' . $locale_url('discord.php?action=connect&silent=1'));
    exit;
}
if ($discord_link && !empty($discord_link['refresh_token_encrypted'])) {
    unset($_SESSION['discord_auto_sync_attempted_at']);
}

$dossier = calculate_player_stats($dossier);
$total_battles = $dossier['total_battles'];
$wins = $dossier['wins'];
$losses = $dossier['losses'];
$draws = $dossier['draws'];
$win_rate = $dossier['win_rate'];
$loss_rate = $dossier['loss_rate'];
$draw_rate = $dossier['draw_rate'];
$shots = $dossier['shots'];
$hits = $dossier['hits'];
$hit_ratio = $dossier['hit_ratio'];
$avg_dmg = $dossier['avg_damage'];
$avg_xp = $dossier['avg_xp'];
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'profile.php';
$seo_index = false;
$active_page = 'profile';
$banner_subtext = $copy['banner_subtext'];
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell profile-page">
    <div class="profile-grid">
        <section class="profile-card">
            <header class="profile-card-header">
                <p class="eyebrow">PROJECT ORION</p>
                <div class="profile-identity">
                    <h1 class="profile-username" data-i18n-ignore translate="no"><?php echo htmlspecialchars($account['username']); ?></h1>
                    <?php $profile_role = normalize_staff_role($account['staff_role'] ?? '', intval($account['is_admin']) === 1); ?>
                    <?php if ($profile_role !== 'player'): ?>
                        <span class="profile-role"><?php echo htmlspecialchars(staff_role_info($profile_role)['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php else: ?>
                        <span class="profile-role profile-role--player"><?php echo htmlspecialchars($copy['player_role'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
                <a class="profile-public-link" href="<?php echo htmlspecialchars($locale_url('players.php?username=' . rawurlencode((string)$account['username'])), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['public_profile'], ENT_QUOTES, 'UTF-8'); ?></a>
            </header>

            <div class="profile-metrics">
                <div class="resource-card">
                    <div class="resource-val resource-credits"><?php echo number_format($account['credits']); ?></div>
                    <div class="resource-label"><?php echo htmlspecialchars($copy['credits'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="resource-card">
                    <div class="resource-val resource-gold"><?php echo number_format($account['gold']); ?></div>
                    <div class="resource-label"><?php echo htmlspecialchars($copy['gold'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="resource-card">
                    <div class="resource-val resource-xp"><?php echo number_format($account['free_xp']); ?></div>
                    <div class="resource-label"><?php echo htmlspecialchars($copy['free_xp'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>

            <h2 class="profile-section-title"><?php echo htmlspecialchars($copy['battle_stats'], ENT_QUOTES, 'UTF-8'); ?></h2>

            <div class="player-stat-overview player-stat-overview--compact">
                <div class="player-stat-card">
                    <strong><?php echo number_format($total_battles); ?></strong>
                    <span><?php echo htmlspecialchars($copy['battles'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="player-stat-card player-stat-card--accent">
                    <strong><?php echo $win_rate; ?>%</strong>
                    <span><?php echo htmlspecialchars($copy['win_rate'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="player-stat-card">
                    <strong><?php echo number_format($dossier['frags']); ?></strong>
                    <span><?php echo htmlspecialchars($copy['destroyed'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="player-stat-card">
                    <strong><?php echo number_format($avg_dmg); ?></strong>
                    <span><?php echo htmlspecialchars($copy['average_damage'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>

            <div class="stat-grid">
                <div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['battles_played'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo $total_battles; ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['wins'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value stat-value--win"><?php echo $wins; ?> (<?php echo $win_rate; ?>%)</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['losses'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value stat-value--loss"><?php echo $losses; ?> (<?php echo $loss_rate; ?>%)</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['draws'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo $draws; ?> (<?php echo $draw_rate; ?>%)</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['enemies_destroyed'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo number_format($dossier['frags']); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['max_frags'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo $dossier['max_frags']; ?></span>
                    </div>
                </div>
                <div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['damage_dealt'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo number_format($dossier['damage_dealt']); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['damage_received'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo number_format($dossier['damage_received']); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['average_damage_battle'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo number_format($avg_dmg); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['accuracy'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo $hit_ratio; ?>% (<?php echo $hits; ?>/<?php echo $shots; ?>)</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['average_xp_battle'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo number_format($avg_xp); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><?php echo htmlspecialchars($copy['max_xp'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo number_format($dossier['max_xp']); ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-extras">
                <div class="resource-card">
                    <span class="stat-label"><?php echo htmlspecialchars($copy['garage_slots'], ENT_QUOTES, 'UTF-8'); ?></span> <span class="stat-value"><?php echo $account['slots']; ?></span>
                </div>
                <div class="resource-card">
                    <span class="stat-label"><?php echo htmlspecialchars($copy['barracks'], ENT_QUOTES, 'UTF-8'); ?></span> <span class="stat-value"><?php echo $account['berths']; ?></span>
                </div>
            </div>
        </section>

        <aside class="security-card">
            <p class="eyebrow"><?php echo htmlspecialchars($copy['security'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h2><?php echo htmlspecialchars($copy['change_password'], ENT_QUOTES, 'UTF-8'); ?></h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger security-alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success security-alert"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($locale_url('profile.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="change_password" value="1">
                <div class="form-group">
                    <label for="current_password"><?php echo htmlspecialchars($copy['current_password'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label for="new_password"><?php echo htmlspecialchars($copy['new_password'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6" maxlength="128" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="new_password_confirm"><?php echo htmlspecialchars($copy['confirm_password'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input type="password" name="new_password_confirm" id="new_password_confirm" class="form-control" required minlength="6" maxlength="128" autocomplete="new-password">
                </div>
                <button type="submit" name="change_password" class="btn btn-primary security-submit"><?php echo htmlspecialchars($copy['update_password'], ENT_QUOTES, 'UTF-8'); ?></button>
            </form>

            <div class="discord-link-block">
                <p class="eyebrow"><?php echo htmlspecialchars($copy['social_links'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2 id="discord-link-heading">Discord</h2>
                <?php if ($discord_link): ?>
                    <div class="discord-link-status">
                        <span><?php echo htmlspecialchars($copy['linked_account'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <strong data-i18n-ignore translate="no"><?php echo htmlspecialchars($discord_link['discord_username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    <form action="<?php echo htmlspecialchars($locale_url('profile.php'), ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="unlink_discord" value="1">
                        <button type="submit" name="unlink_discord" class="btn btn-secondary security-submit"><?php echo htmlspecialchars($copy['unlink_discord'], ENT_QUOTES, 'UTF-8'); ?></button>
                    </form>
                <?php elseif (defined('DISCORD_OAUTH_ENABLED') && DISCORD_OAUTH_ENABLED): ?>
                    <p><?php echo htmlspecialchars($copy['discord_prompt'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <a class="btn btn-discord security-submit" href="<?php echo htmlspecialchars($locale_url('discord.php?action=connect'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($copy['link_discord'], ENT_QUOTES, 'UTF-8'); ?></a>
                <?php else: ?>
                    <p class="muted"><?php echo htmlspecialchars($copy['discord_unavailable'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
