<?php

function player_dossier_defaults(): array
{
    return [
        'total_battles' => 0,
        'wins' => 0,
        'losses' => 0,
        'draws' => 0,
        'frags' => 0,
        'damage_dealt' => 0,
        'damage_received' => 0,
        'shots' => 0,
        'hits' => 0,
        'max_xp' => 0,
        'max_damage' => 0,
        'max_frags' => 0,
        'total_xp' => 0,
    ];
}

function normalize_player_dossier($dossier): array
{
    $normalized = player_dossier_defaults();
    if (!is_array($dossier)) {
        return $normalized;
    }

    foreach ($normalized as $field => $default) {
        $normalized[$field] = intval($dossier[$field] ?? $default);
    }

    return $normalized;
}

function calculate_player_stats($dossier): array
{
    $dossier = normalize_player_dossier($dossier);
    $total_battles = $dossier['total_battles'];
    $shots = $dossier['shots'];

    return array_merge($dossier, [
        'win_rate' => $total_battles > 0 ? round(($dossier['wins'] / $total_battles) * 100, 2) : 0,
        'loss_rate' => $total_battles > 0 ? round(($dossier['losses'] / $total_battles) * 100, 2) : 0,
        'draw_rate' => $total_battles > 0 ? round(($dossier['draws'] / $total_battles) * 100, 2) : 0,
        'hit_ratio' => $shots > 0 ? round(($dossier['hits'] / $shots) * 100, 2) : 0,
        'avg_damage' => $total_battles > 0 ? round($dossier['damage_dealt'] / $total_battles) : 0,
        'avg_xp' => $total_battles > 0 ? round($dossier['total_xp'] / $total_battles) : 0,
    ]);
}
