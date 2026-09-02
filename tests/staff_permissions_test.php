<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/staff.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function access_fixture(int $id, string $role): array
{
    $info = staff_role_info($role);
    $permissions = array_fill_keys(array_keys(staff_permission_catalog()), false);
    foreach ($info['permissions'] as $permission) {
        $permissions[$permission] = true;
    }
    return [
        'id' => $id,
        'role' => $role,
        'role_info' => $info,
        'rank' => $info['rank'],
        'permissions' => $permissions,
        'overrides' => [],
    ];
}

$admin = access_fixture(1, 'admin');
$councilHead = access_fixture(2, 'orion_council_head');
$developer = access_fixture(3, 'developer');
$senior = access_fixture(4, 'senior_moderator');
$moderator = access_fixture(5, 'moderator');
$contentMaker = access_fixture(6, 'content_maker');
$player = access_fixture(7, 'player');

check(staff_access_has($admin, 'staff.manage'), 'administrator must manage the team');
check(staff_access_has($admin, 'updates.manage'), 'administrator must manage update history');
check(staff_access_has($admin, 'server.manage'), 'administrator must manage public server status');
check(staff_access_has($admin, 'council.implement'), 'administrator must receive accepted council decisions');
check(staff_access_has($councilHead, 'staff.manage'), 'Orion council head must manage lower staff roles');
check(staff_access_has($councilHead, 'council.participate'), 'Orion council head must vote in the council');
check(staff_access_has($councilHead, 'council.review'), 'Orion council head must review passed decisions');
check(!staff_access_has($councilHead, 'vehicles.manage'), 'Orion council head must not manage technical systems by default');
check(staff_access_has($senior, 'reports.delete'), 'senior moderator must manage and delete reports');
check(staff_access_has($senior, 'council.participate'), 'senior moderator must participate in council voting');
check(!staff_access_has($senior, 'users.view'), 'senior moderator must not access player accounts');
check(!staff_access_has($senior, 'users.edit'), 'senior moderator must not edit player accounts');
check(!staff_access_has($senior, 'bans.manage'), 'senior moderator must not issue bans');
check(!staff_access_has($senior, 'bans.unban'), 'senior moderator must not remove bans');
check(!empty(staff_role_info('senior_moderator')['permissions_fixed']), 'senior moderator permissions must ignore personal overrides');
check(staff_access_has($moderator, 'reports.manage'), 'moderator must process reports');
check(staff_access_has($moderator, 'council.participate'), 'moderator must submit proposals and vote');
check(!staff_access_has($moderator, 'council.review'), 'moderator must not replace the Orion council head');
check(!empty(staff_role_info('moderator')['permissions_fixed']), 'moderator permissions must ignore personal overrides');
check(!staff_access_has($moderator, 'users.view'), 'moderator must not access player accounts');
check(!staff_access_has($moderator, 'users.edit'), 'moderator must not edit player accounts');
check(!staff_access_has($moderator, 'bans.manage'), 'moderator must not issue bans');
check(!staff_access_has($moderator, 'bans.unban'), 'moderator must not remove bans');
check(!staff_access_has($moderator, 'audit.view'), 'moderator must only have report-processing access');
check(!staff_access_has($moderator, 'users.credentials'), 'moderator must not reset passwords by default');
check(!staff_access_has($player, 'dashboard.view'), 'player must not enter the staff panel');
check(!staff_access_has($player, 'council.participate'), 'ordinary player must not vote in the council');
check(staff_access_has($developer, 'vehicles.manage'), 'developer must manage technical systems');
check(staff_access_has($developer, 'council.participate'), 'developer must vote in the council');
foreach ($councilHead['permissions'] as $permission => $enabled) {
    if ($enabled) {
        check(staff_access_has($developer, $permission), 'developer must inherit council-head admin access: ' . $permission);
    }
}
check(staff_access_has($developer, 'staff.manage'), 'developer must manage lower staff access');
check(staff_access_has($contentMaker, 'news.manage'), 'content maker must manage news');
check(staff_access_has($contentMaker, 'council.participate'), 'content maker must vote in the council');
check(!staff_access_has($contentMaker, 'bans.manage'), 'content maker must not issue bans');

check(staff_can_manage_access($admin, $councilHead), 'project lead must manage the Orion council head');
check(staff_can_manage_access($developer, $councilHead), 'developer must rank above and manage the Orion council head');
check(!staff_can_manage_access($councilHead, $developer), 'Orion council head must not manage a higher-ranked developer');
check(!staff_can_manage_access($admin, $admin), 'project lead must not manage the current account');
check(!staff_can_manage_access($senior, $moderator), 'senior moderator has no team management permission');
check(staff_can_act_on_account($developer, $senior), 'developer must outrank a senior moderator');
check(staff_can_act_on_account($senior, $moderator), 'senior moderator must outrank a moderator');
check(staff_can_act_on_account($moderator, $contentMaker), 'moderator must outrank a content maker');
check(staff_can_act_on_account($moderator, $player), 'moderator must act on a player');
check(!staff_can_act_on_account($moderator, $senior), 'moderator must not act on a senior moderator');
check(!staff_can_act_on_account($moderator, $moderator), 'moderator must not act on the current account');
check(normalize_staff_role('player', true) === 'admin', 'legacy is_admin flag must preserve project-lead access');

fwrite(STDOUT, "Staff permission hierarchy checks passed.\n");
