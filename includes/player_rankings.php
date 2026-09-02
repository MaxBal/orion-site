<?php

function player_ranking_locale(): string
{
    $lang = function_exists('current_lang') ? current_lang() : 'ru';
    return in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'ru';
}

function player_ranking_definitions(): array
{
    $lang = player_ranking_locale();
    $labels = [
        'win_rate' => [
            'ru' => 'Win Rate',
            'uk' => 'Win Rate',
            'en' => 'Win Rate',
        ],
        'wins' => [
            'ru' => 'Победы',
            'uk' => 'Перемоги',
            'en' => 'Wins',
        ],
        'frags' => [
            'ru' => 'Фраги',
            'uk' => 'Фраги',
            'en' => 'Frags',
        ],
        'avg_damage' => [
            'ru' => 'Средний урон',
            'uk' => 'Середня шкода',
            'en' => 'Average damage',
        ],
        'avg_xp' => [
            'ru' => 'Средний опыт',
            'uk' => 'Середній досвід',
            'en' => 'Average experience',
        ],
    ];
    return [
        'win_rate' => [
            'label' => $labels['win_rate'][$lang],
            'metric_sql' => 'ROUND((d.wins / NULLIF(d.total_battles, 0)) * 100, 2)',
            'minimum_battles' => 10,
            'suffix' => '%',
            'decimals' => 2,
        ],
        'wins' => [
            'label' => $labels['wins'][$lang],
            'metric_sql' => 'd.wins',
            'minimum_battles' => 1,
            'suffix' => '',
            'decimals' => 0,
        ],
        'frags' => [
            'label' => $labels['frags'][$lang],
            'metric_sql' => 'd.frags',
            'minimum_battles' => 1,
            'suffix' => '',
            'decimals' => 0,
        ],
        'avg_damage' => [
            'label' => $labels['avg_damage'][$lang],
            'metric_sql' => 'ROUND(d.damage_dealt / NULLIF(d.total_battles, 0))',
            'minimum_battles' => 1,
            'suffix' => '',
            'decimals' => 0,
        ],
        'avg_xp' => [
            'label' => $labels['avg_xp'][$lang],
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
