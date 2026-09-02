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

class FakePlayerRankingStatement
{
    public string $sql;
    public array $rows;
    public array $executions = [];

    public function __construct(string $sql, array $rows)
    {
        $this->sql = $sql;
        $this->rows = $rows;
    }

    public function execute(array $params): bool
    {
        $this->executions[] = $params;
        return true;
    }

    public function fetchAll(): array
    {
        return $this->rows;
    }
}

class FakePlayerRankingPdo
{
    public array $statements = [];
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function prepare(string $sql): FakePlayerRankingStatement
    {
        $statement = new FakePlayerRankingStatement($sql, $this->rows);
        $this->statements[] = $statement;
        return $statement;
    }
}

$fakePdo = new FakePlayerRankingPdo([[
    'id' => '7',
    'username' => 'Alice',
    'is_admin' => '1',
    'staff_role' => 'moderator',
    'total_battles' => '12',
    'metric_value' => '55.50',
    'email' => 'private@example.test',
]]);
$loadedRankings = load_player_rankings($fakePdo, 999);
$statements = $fakePdo->statements;
$expectedMetricSql = [
    'ROUND((d.wins / NULLIF(d.total_battles, 0)) * 100, 2)',
    'd.wins',
    'd.frags',
    'ROUND(d.damage_dealt / NULLIF(d.total_battles, 0))',
    'ROUND(d.total_xp / NULLIF(d.total_battles, 0))',
];

playerRankingCheck(count($statements) === 5, 'ranking loader must execute five fixed queries');
foreach ($statements as $index => $statement) {
    playerRankingCheck(str_contains($statement->sql, $expectedMetricSql[$index]), 'ranking query metric expression is not fixed');
    playerRankingCheck(str_contains($statement->sql, 'LIMIT 10'), 'ranking limit must clamp to ten');
    playerRankingCheck(count($statement->executions) === 1, 'each ranking query must execute once');
}

$thresholds = array_map(static function (FakePlayerRankingStatement $statement): int {
    return intval($statement->executions[0][0] ?? 0);
}, $statements);
playerRankingCheck($thresholds === [10, 1, 1, 1, 1], 'ranking queries must bind the expected battle thresholds');

$expectedPublicRow = [
    'id' => 7,
    'username' => 'Alice',
    'is_admin' => 1,
    'staff_role' => 'moderator',
    'total_battles' => 12,
    'metric_value' => 55.5,
];
foreach ($loadedRankings as $ranking) {
    playerRankingCheck($ranking['rows'] === [$expectedPublicRow], 'ranking rows must normalize to the public row fields');
}

fwrite(STDOUT, "Player ranking definitions passed.\n");
