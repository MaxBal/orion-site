<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/player_stats.php';

function playerStatsCheck(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$empty = calculate_player_stats(null);
playerStatsCheck($empty['total_battles'] === 0, 'empty dossier must have zero battles');
playerStatsCheck($empty['win_rate'] === 0, 'empty dossier must have zero win rate');
playerStatsCheck($empty['hit_ratio'] === 0, 'empty dossier must have zero accuracy');

$stats = calculate_player_stats([
    'total_battles' => '8',
    'wins' => '5',
    'losses' => '2',
    'draws' => '1',
    'frags' => '11',
    'damage_dealt' => '8005',
    'shots' => '20',
    'hits' => '7',
    'total_xp' => '267',
]);

playerStatsCheck($stats['win_rate'] === 62.5, 'win rate calculation is incorrect');
playerStatsCheck($stats['loss_rate'] === 25.0, 'loss rate calculation is incorrect');
playerStatsCheck($stats['draw_rate'] === 12.5, 'draw rate calculation is incorrect');
playerStatsCheck($stats['hit_ratio'] === 35.0, 'accuracy calculation is incorrect');
playerStatsCheck($stats['avg_damage'] === 1001.0, 'average damage calculation is incorrect');
playerStatsCheck($stats['avg_xp'] === 33.0, 'average XP calculation is incorrect');
playerStatsCheck($stats['max_damage'] === 0, 'missing dossier fields must fall back to zero');

fwrite(STDOUT, "Player statistics calculations passed.\n");
