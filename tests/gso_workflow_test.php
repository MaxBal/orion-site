<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/staff.php';
require_once dirname(__DIR__) . '/includes/gso.php';
require_once dirname(__DIR__) . '/includes/update_history.php';

function gso_check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

gso_check(gso_quorum_required(1) === 1, 'single-member council quorum must be one');
gso_check(gso_quorum_required(2) === 2, 'two-member council must require both members for quorum');
gso_check(gso_quorum_required(5) === 3, 'five-member council quorum must be a majority of seats');
gso_check(gso_quorum_required(12) === 6, 'twelve-member council quorum must be six');

gso_check(gso_vote_result(3, 1, 0, 3) === 'passed', 'majority with quorum must pass');
gso_check(gso_vote_result(2, 2, 1, 5) === 'rejected', 'tied vote must not pass');
gso_check(gso_vote_result(3, 0, 0, 4) === 'rejected', 'vote without quorum must not pass');
gso_check(gso_vote_result(2, 1, 1, 4) === 'passed', 'abstentions must count toward quorum');

gso_check(gso_can_decide_review(['role' => 'orion_council_head']), 'council head must decide passed votes');
gso_check(gso_can_decide_review(['role' => 'admin']), 'project head must decide passed votes like the council head');
gso_check(!gso_can_decide_review(['role' => 'developer']), 'developer must not replace the council head');
gso_check(!gso_can_decide_review(['role' => 'senior_moderator']), 'moderators must not decide passed votes');
gso_check(gso_decision_authority_label(['role' => 'admin']) === 'Глава проекта', 'project head decisions must be signed by the project head');
gso_check(gso_decision_authority_label(['role' => 'orion_council_head']) === 'Глава совета', 'council head decisions must be signed by the council head');

$override_statuses = gso_status_override_options();
gso_check(in_array('implementation', $override_statuses, true), 'implemented decisions must be returnable to the implementation queue');
gso_check(in_array('voting', $override_statuses, true), 'a decided proposal must be returnable to a new voting round');
gso_check(count(array_diff($override_statuses, array_keys(['voting' => 1, 'council_review' => 1, 'implementation' => 1, 'implemented' => 1, 'rejected_vote' => 1, 'rejected_head' => 1]))) === 0, 'status override must stay inside the known workflow states');

gso_check(gso_head_decision_transition('accept', 0) === 'implementation', 'first review acceptance must reach implementation');
gso_check(gso_head_decision_transition('reject', 0) === 'rejected_head', 'head rejection must immediately end the proposal');
gso_check(gso_head_decision_transition('reject', 1) === 'rejected_head', 'legacy rejection counters must not reopen voting');

$source = "## 💬 Социальные функции\nВзводы | Доступны всем.\nДрузья | Добавлен список.\n\n## 🔧 Сервер\nОптимизация | Снижена нагрузка.";
$categories = orion_update_categories_from_text($source);
gso_check(count($categories) === 2, 'update parser must preserve category groups');
gso_check($categories[0]['icon'] === '💬', 'update parser must preserve category icon');
gso_check($categories[0]['items'][1][0] === 'Друзья', 'update parser must preserve item title');
gso_check(orion_update_categories_from_text(orion_update_categories_to_text($categories)) === $categories, 'update category text must round-trip');

$thrown = false;
try {
    orion_update_categories_from_text("## Ошибки\nСтрока без разделителя");
} catch (RuntimeException $e) {
    $thrown = true;
}
gso_check($thrown, 'malformed update item must be rejected');

fwrite(STDOUT, "GSO workflow and update history checks passed.\n");
