<?php
require_once 'db.php';

function gso_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function gso_set_flash($type, $message) {
    $_SESSION['gso_flash'] = ['type' => $type, 'message' => $message];
}

function gso_redirect($proposal_id = 0) {
    $location = 'gso.php';
    if (intval($proposal_id) > 0) {
        $location .= '#proposal-' . intval($proposal_id);
    }
    header('Location: ' . i18n_locale_path($location), true, 303);
    exit;
}

function gso_initial($username) {
    $username = trim((string)$username);
    if ($username === '') {
        return 'O';
    }
    return function_exists('mb_substr') ? mb_strtoupper(mb_substr($username, 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr($username, 0, 1));
}

function gso_discord_username($member) {
    $username = trim((string)($member['discord_username'] ?? ''));
    return $username !== '' ? '@' . $username : '';
}

function gso_role_public_label($role, $lang) {
    $labels = [
        'ru' => [
            'admin' => 'Глава проекта / создатель',
            'orion_council_head' => 'Глава совета',
            'developer' => 'Разработчик',
            'senior_moderator' => 'Старший модератор',
            'moderator' => 'Модератор',
            'content_maker' => 'Контент-мейкер',
        ],
        'uk' => [
            'admin' => 'Глава проєкту / засновник',
            'orion_council_head' => 'Голова ради',
            'developer' => 'Розробник',
            'senior_moderator' => 'Старший модератор',
            'moderator' => 'Модератор',
            'content_maker' => 'Контент-мейкер',
        ],
        'en' => [
            'admin' => 'Project lead / creator',
            'orion_council_head' => 'Council head',
            'developer' => 'Developer',
            'senior_moderator' => 'Senior moderator',
            'moderator' => 'Moderator',
            'content_maker' => 'Content maker',
        ],
    ];
    return $labels[$lang][$role] ?? (string)$role;
}

$lang = function_exists('current_lang') ? current_lang() : 'ru';
$lang = in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'ru';
$is_uk = $lang === 'uk';
$is_en = $lang === 'en';
$copy = $is_uk ? [
    'eyebrow' => 'ГЕНЕРАЛЬНА РАДА ОРІОНА',
    'title' => 'Рішення приймаються відкрито',
    'lead' => 'Модератори та команда вносять пропозиції, голосують, передають ухвалене голові ради, а після погодження — голові проєкту на реалізацію.',
    'members' => 'членів ради',
    'active' => 'активних рішень',
    'implemented' => 'реалізовано',
    'quorum' => 'голосів для кворуму',
    'session_open' => 'СЕСІЯ',
    'session_state' => 'ВІДКРИТА',
    'dateline_seats' => 'СКЛАД',
    'dateline_quorum' => 'КВОРУМ',
    'dateline_active' => 'НА ГОЛОСУВАННІ',
    'dateline_done' => 'РЕАЛІЗОВАНО',
    'procedure' => 'Порядок проходження',
    'procedure_note' => 'Три станції, які проходить кожна пропозиція.',
    'flow_vote' => 'Голосування',
    'flow_vote_note' => 'Команда обирає ТАК, НІ або утримується.',
    'flow_head' => 'Голова ради',
    'flow_head_note' => 'Погоджує або відхиляє рішення більшості.',
    'flow_admin' => 'Реалізація',
    'flow_admin_note' => 'Голова проєкту бере рішення в роботу.',
    'chamber' => 'Зала засідань',
    'chamber_note' => 'Кожен чинний член команди отримує одне місце й один голос. Склад оновлюється автоматично разом із ролями модераторів.',
    'leadership' => 'Керівництво ради',
    'council_head_office' => 'Голова Ради Оріона',
    'developer_office' => 'Розробник Project Orion',
    'project_head_office' => 'Глава проєкту / засновник',
    'not_assigned' => 'Ще не призначено',
    'leadership_mandate' => 'Керівний мандат',
    'technical_mandate' => 'Технічне керівництво',
    'delegate_floor' => 'Делегати та місця команди',
    'seat_legend' => 'Склад зали',
    'development_group' => 'Розробка',
    'senior_moderation_group' => 'Старші модератори',
    'moderation_group' => 'Модерація',
    'moderator_group' => 'Модератори',
    'content_group' => 'Контент',
    'no_role_members' => 'Місце ролі поки вільне.',
    'members_title' => 'Місця ради',
    'submit_title' => 'Подати пропозицію',
    'submit_note' => 'Опишіть проблему, конкретне рішення й очікуваний результат.',
    'proposal_title' => 'Назва рішення',
    'description' => 'Що пропонується',
    'expected' => 'Очікуваний результат',
    'duration' => 'Тривалість голосування',
    'day_1' => '24 години',
    'day_3' => '3 дні',
    'day_7' => '7 днів',
    'submit' => 'Винести на голосування',
    'view_only' => 'Ви можете переглядати засідання та історію рішень. Право подавати пропозиції й голосувати мають лише члени команди.',
    'player_petitions' => 'Ініціативи гравців',
    'login' => 'Увійти в акаунт',
    'active_title' => 'Порядок денний',
    'active_note' => 'Рішення, які зараз голосуються, перевіряються головою ради або очікують реалізації.',
    'history_title' => 'Стенограма голосувань',
    'history_note' => 'Завершені, реалізовані та відхилені рішення з повною хронологією.',
    'author' => 'Автор',
    'created' => 'Створено',
    'ends' => 'Голосування до',
    'result' => 'Очікуваний результат',
    'yes' => 'ТАК',
    'no' => 'НІ',
    'abstain' => 'УТРИМАЮСЬ',
    'uncast' => 'НЕ ГОЛОСУВАЛИ',
    'vote_snapshot' => 'Поділ голосів',
    'total_votes' => 'подано',
    'quorum_progress' => 'Кворум',
    'quorum_met' => 'кворум набрано',
    'quorum_left' => 'бракує голосів',
    'vote_members' => 'Учасники голосування',
    'no_voters' => 'Ніхто',
    'close_vote' => 'Завершити голосування',
    'close_hint' => 'Дострокове завершення доступне після досягнення кворуму.',
    'head_title' => 'Рішення голови ради',
    'head_title_project' => 'Рішення голови проєкту',
    'head_note' => 'Обґрунтування рішення',
    'accept' => 'Погодити й передати на реалізацію',
    'reject' => 'Відхилити',
    'vote_round' => 'Тур голосування',
    'rejection_rule' => 'Відмова одразу завершує пропозицію. Другого туру голосування не буде.',
    'timeline' => 'Хронологія',
    'empty_active' => 'Зараз немає активних пропозицій.',
    'empty_history' => 'Історія голосувань поки порожня.',
    'implementation_note' => 'Звіт про реалізацію',
    'csrf_error' => 'Сесія застаріла. Оновіть сторінку.',
    'team_only_error' => 'Лише члени команди можуть подавати пропозиції.',
    'player_vote_error' => 'Звичайні гравці не можуть голосувати.',
    'close_vote_error' => 'У вас немає права завершувати голосування.',
    'head_decision_error' => 'Це рішення може прийняти лише голова проєкту або голова ради.',
    'unknown_action' => 'Невідома дія.',
    'proposal_created_audit' => 'Створено пропозицію ГСО',
    'vote_audit' => 'Враховано голос у ГСО',
    'head_decision_audit' => 'Рішення голови ради щодо пропозиції ГСО',
    'system' => 'Система',
] : ($is_en ? [
    'eyebrow' => 'GENERAL COUNCIL OF ORION',
    'title' => 'Decisions are made in the open',
    'lead' => 'Moderators and team members submit proposals, vote, pass approved decisions to the council head, and then send them to the project lead for implementation.',
    'members' => 'council members',
    'active' => 'active decisions',
    'implemented' => 'implemented',
    'quorum' => 'votes for quorum',
    'session_open' => 'SESSION',
    'session_state' => 'OPEN',
    'dateline_seats' => 'SEATS',
    'dateline_quorum' => 'QUORUM',
    'dateline_active' => 'IN VOTE',
    'dateline_done' => 'IMPLEMENTED',
    'procedure' => 'Decision process',
    'procedure_note' => 'Three stations every proposal passes through.',
    'flow_vote' => 'Vote',
    'flow_vote_note' => 'The team chooses YES, NO, or abstains.',
    'flow_head' => 'Council head',
    'flow_head_note' => 'Approves or rejects the majority decision.',
    'flow_admin' => 'Implementation',
    'flow_admin_note' => 'The project lead takes the decision into work.',
    'chamber' => 'Council chamber',
    'chamber_note' => 'Every active team member receives one seat and one vote. The chamber updates automatically as moderator roles change.',
    'leadership' => 'Council leadership',
    'council_head_office' => 'Head of the Orion Council',
    'developer_office' => 'Project Orion developer',
    'project_head_office' => 'Project lead / creator',
    'not_assigned' => 'Not assigned yet',
    'leadership_mandate' => 'Leadership mandate',
    'technical_mandate' => 'Technical leadership',
    'delegate_floor' => 'Delegates and team seats',
    'seat_legend' => 'Chamber roster',
    'development_group' => 'Development',
    'senior_moderation_group' => 'Senior moderators',
    'moderation_group' => 'Moderation',
    'moderator_group' => 'Moderators',
    'content_group' => 'Content',
    'no_role_members' => 'This role seat is currently vacant.',
    'members_title' => 'Council seats',
    'submit_title' => 'Submit a proposal',
    'submit_note' => 'Describe the problem, a specific solution, and the expected result.',
    'proposal_title' => 'Decision title',
    'description' => 'What is being proposed',
    'expected' => 'Expected result',
    'duration' => 'Voting duration',
    'day_1' => '24 hours',
    'day_3' => '3 days',
    'day_7' => '7 days',
    'submit' => 'Put to a vote',
    'view_only' => 'You can view the chamber and decision history. Only team members can submit proposals and vote.',
    'player_petitions' => 'Player initiatives',
    'login' => 'Sign in to your account',
    'active_title' => 'Agenda',
    'active_note' => 'Decisions currently being voted on, reviewed by the council head, or awaiting implementation.',
    'history_title' => 'Voting transcript',
    'history_note' => 'Completed, implemented, and rejected decisions with their full timeline.',
    'author' => 'Author',
    'created' => 'Created',
    'ends' => 'Voting ends',
    'result' => 'Expected result',
    'yes' => 'YES',
    'no' => 'NO',
    'abstain' => 'ABSTAIN',
    'uncast' => 'NOT VOTED',
    'vote_snapshot' => 'Vote split',
    'total_votes' => 'cast',
    'quorum_progress' => 'Quorum',
    'quorum_met' => 'quorum reached',
    'quorum_left' => 'votes needed',
    'vote_members' => 'Voters',
    'no_voters' => 'Nobody',
    'close_vote' => 'Close vote',
    'close_hint' => 'Early closing is available after quorum is reached.',
    'head_title' => 'Council-head decision',
    'head_title_project' => 'Project-lead decision',
    'head_note' => 'Decision justification',
    'accept' => 'Approve and send to implementation',
    'reject' => 'Reject',
    'vote_round' => 'Voting round',
    'rejection_rule' => 'A rejection ends the proposal immediately. There will be no second voting round.',
    'timeline' => 'Timeline',
    'empty_active' => 'There are no active proposals right now.',
    'empty_history' => 'The voting history is empty for now.',
    'implementation_note' => 'Implementation report',
    'csrf_error' => 'Your session has expired. Refresh the page.',
    'team_only_error' => 'Only team members can submit proposals.',
    'player_vote_error' => 'Regular players cannot vote.',
    'close_vote_error' => 'You do not have permission to close the vote.',
    'head_decision_error' => 'Only the project lead or council head can make this decision.',
    'unknown_action' => 'Unknown action.',
    'proposal_created_audit' => 'GSO proposal created',
    'vote_audit' => 'GSO vote recorded',
    'head_decision_audit' => 'GSO council-head decision',
    'system' => 'System',
] : [
    'eyebrow' => 'ГЕНЕРАЛЬНЫЙ СОВЕТ ОРИОНА',
    'title' => 'Решения принимаются открыто',
    'lead' => 'Модераторы и команда вносят предложения, голосуют, передают принятое главе совета, а после согласования — главе проекта на реализацию.',
    'members' => 'членов совета',
    'active' => 'активных решений',
    'implemented' => 'реализовано',
    'quorum' => 'голосов для кворума',
    'session_open' => 'СЕССИЯ',
    'session_state' => 'ОТКРЫТА',
    'dateline_seats' => 'СОСТАВ',
    'dateline_quorum' => 'КВОРУМ',
    'dateline_active' => 'НА ГОЛОСОВАНИИ',
    'dateline_done' => 'РЕАЛИЗОВАНО',
    'procedure' => 'Порядок прохождения',
    'procedure_note' => 'Три станции, которые проходит каждое предложение.',
    'flow_vote' => 'Голосование',
    'flow_vote_note' => 'Команда выбирает ДА, НЕТ или воздерживается.',
    'flow_head' => 'Глава совета',
    'flow_head_note' => 'Согласует или отклоняет решение большинства.',
    'flow_admin' => 'Реализация',
    'flow_admin_note' => 'Глава проекта принимает решение в работу.',
    'chamber' => 'Зал заседаний',
    'chamber_note' => 'Каждый действующий член команды получает одно место и один голос. Состав обновляется автоматически вместе с ролями модераторов.',
    'leadership' => 'Руководство совета',
    'council_head_office' => 'Глава Совета Ориона',
    'developer_office' => 'Разработчик Project Orion',
    'project_head_office' => 'Глава проекта / создатель',
    'not_assigned' => 'Ещё не назначен',
    'leadership_mandate' => 'Руководящий мандат',
    'technical_mandate' => 'Техническое руководство',
    'delegate_floor' => 'Делегаты и места команды',
    'seat_legend' => 'Состав зала',
    'development_group' => 'Разработка',
    'senior_moderation_group' => 'Старшие модераторы',
    'moderation_group' => 'Модерация',
    'moderator_group' => 'Модераторы',
    'content_group' => 'Контент',
    'no_role_members' => 'Место роли пока свободно.',
    'members_title' => 'Места совета',
    'submit_title' => 'Подать предложение',
    'submit_note' => 'Опишите проблему, конкретное решение и ожидаемый результат.',
    'proposal_title' => 'Название решения',
    'description' => 'Что предлагается',
    'expected' => 'Ожидаемый результат',
    'duration' => 'Продолжительность голосования',
    'day_1' => '24 часа',
    'day_3' => '3 дня',
    'day_7' => '7 дней',
    'submit' => 'Вынести на голосование',
    'view_only' => 'Вы можете просматривать заседания и историю решений. Право подавать предложения и голосовать есть только у членов команды.',
    'player_petitions' => 'Предложения игроков',
    'login' => 'Войти в аккаунт',
    'active_title' => 'Повестка',
    'active_note' => 'Решения, которые сейчас голосуются, проверяются главой совета или ожидают реализации.',
    'history_title' => 'Стенограмма голосований',
    'history_note' => 'Завершённые, реализованные и отклонённые решения с полной хронологией.',
    'author' => 'Автор',
    'created' => 'Создано',
    'ends' => 'Голосование до',
    'result' => 'Ожидаемый результат',
    'yes' => 'ДА',
    'no' => 'НЕТ',
    'abstain' => 'ВОЗДЕРЖУСЬ',
    'uncast' => 'НЕ ГОЛОСОВАЛИ',
    'vote_snapshot' => 'Деление голосов',
    'total_votes' => 'подано',
    'quorum_progress' => 'Кворум',
    'quorum_met' => 'кворум набран',
    'quorum_left' => 'не хватает голосов',
    'vote_members' => 'Участники голосования',
    'no_voters' => 'Никто',
    'close_vote' => 'Завершить голосование',
    'close_hint' => 'Досрочное завершение доступно после достижения кворума.',
    'head_title' => 'Решение главы совета',
    'head_title_project' => 'Решение главы проекта',
    'head_note' => 'Обоснование решения',
    'accept' => 'Согласовать и передать на реализацию',
    'reject' => 'Отклонить',
    'vote_round' => 'Тур голосования',
    'rejection_rule' => 'Отказ сразу завершает предложение. Второго тура голосования не будет.',
    'timeline' => 'Хронология',
    'empty_active' => 'Сейчас нет активных предложений.',
    'empty_history' => 'История голосований пока пуста.',
    'implementation_note' => 'Отчёт о реализации',
    'csrf_error' => 'Сессия устарела. Обновите страницу.',
    'team_only_error' => 'Только члены команды могут подавать предложения.',
    'player_vote_error' => 'Обычные игроки не могут голосовать.',
    'close_vote_error' => 'У вас нет права завершать голосование.',
    'head_decision_error' => 'Это решение может принять только глава проекта или глава совета.',
    'unknown_action' => 'Неизвестное действие.',
    'proposal_created_audit' => 'Создано предложение ГСО',
    'vote_audit' => 'Учтён голос в ГСО',
    'head_decision_audit' => 'Решение главы совета по предложению ГСО',
    'system' => 'Система',
]);

$viewer_id = intval($_SESSION['user_id'] ?? 0);
$viewer_access = $viewer_id > 0 ? staff_access_for_account($pdo, $viewer_id) : null;
$can_participate = gso_can_participate($viewer_access);
$can_close_vote = $can_participate && gso_can_decide_review($viewer_access);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proposal_id = intval($_POST['proposal_id'] ?? 0);
    try {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            throw new RuntimeException($copy['csrf_error']);
        }
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'create_proposal') {
            if (!$can_participate) {
                throw new RuntimeException($copy['team_only_error']);
            }
            $proposal_id = gso_create_proposal(
                $pdo,
                $viewer_id,
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['expected_result'] ?? '',
                $_POST['duration_days'] ?? 3
            );
            log_staff_action($pdo, 'gso.proposal.create', 'gso_proposal', $proposal_id, $copy['proposal_created_audit']);
            gso_set_flash('success', $is_uk ? 'Пропозицію винесено на голосування.' : ($is_en ? 'The proposal was put to a vote.' : 'Предложение вынесено на голосование.'));
        } elseif ($action === 'vote') {
            if (!$can_participate) {
                throw new RuntimeException($copy['player_vote_error']);
            }
            gso_cast_vote($pdo, $proposal_id, $viewer_id, $_POST['choice'] ?? '');
            log_staff_action($pdo, 'gso.vote', 'gso_proposal', $proposal_id, $copy['vote_audit']);
            gso_set_flash('success', $is_uk ? 'Ваш голос враховано.' : ($is_en ? 'Your vote has been recorded.' : 'Ваш голос учтён.'));
        } elseif ($action === 'close_vote') {
            if (!$can_close_vote) {
                throw new RuntimeException($copy['close_vote_error']);
            }
            gso_finalize_vote($pdo, $proposal_id, $viewer_id, true);
            gso_set_flash('success', $is_uk ? 'Голосування завершено.' : ($is_en ? 'Voting has ended.' : 'Голосование завершено.'));
        } elseif ($action === 'head_decision') {
            if (!gso_can_decide_review($viewer_access)) {
                throw new RuntimeException($copy['head_decision_error']);
            }
            $head_outcome = gso_head_decide($pdo, $proposal_id, $viewer_id, $_POST['decision'] ?? '', $_POST['head_note'] ?? '');
            log_staff_action($pdo, 'gso.head.decision', 'gso_proposal', $proposal_id, $copy['head_decision_audit']);
            if ($head_outcome === 'rejected_head') {
                gso_set_flash('success', $is_uk ? 'Пропозицію остаточно відхилено головою ради.' : ($is_en ? 'The proposal was finally rejected by the council head.' : 'Предложение окончательно отклонено главой совета.'));
            } else {
                gso_set_flash('success', $is_uk ? 'Рішення прийнято та передано на реалізацію.' : ($is_en ? 'The decision was approved and sent for implementation.' : 'Решение принято и передано на реализацию.'));
            }
        } else {
            throw new RuntimeException($copy['unknown_action']);
        }
    } catch (Exception $e) {
        gso_set_flash('danger', gso_localize_error($e->getMessage(), $lang));
    }
    gso_redirect($proposal_id);
}

gso_sync_expired_votes($pdo);
$members = gso_eligible_members($pdo);
$council_heads = array_values(array_filter($members, function ($member) {
    return $member['access']['role'] === 'orion_council_head';
}));
$project_heads = array_values(array_filter($members, function ($member) {
    return $member['access']['role'] === 'admin';
}));
$developers = array_values(array_filter($members, function ($member) {
    return $member['access']['role'] === 'developer';
}));
$council_members = array_values(array_filter($members, function ($member) {
    return !in_array($member['access']['role'], ['admin', 'developer', 'orion_council_head'], true);
}));
$delegate_role_groups = [
    'senior_moderator' => [
        'tone' => 'senior',
        'members' => array_values(array_filter($council_members, function ($member) {
            return $member['access']['role'] === 'senior_moderator';
        })),
    ],
    'moderator' => [
        'tone' => 'moderator',
        'members' => array_values(array_filter($council_members, function ($member) {
            return $member['access']['role'] === 'moderator';
        })),
    ],
    'content_maker' => [
        'tone' => 'content',
        'members' => array_values(array_filter($council_members, function ($member) {
            return $member['access']['role'] === 'content_maker';
        })),
    ],
];
$role_group_counts = [
    'admin' => count($project_heads),
    'developer' => count($developers),
    'council' => count($council_heads),
    'senior_moderator' => count($delegate_role_groups['senior_moderator']['members']),
    'moderator' => count($delegate_role_groups['moderator']['members']),
    'content' => count($delegate_role_groups['content_maker']['members']),
];
$proposals = gso_load_proposals($pdo, $viewer_id, 120);
$vote_rosters = gso_load_vote_rosters($pdo, $proposals, $members);
$events_by_proposal = gso_load_events($pdo, array_column($proposals, 'id'));
$active_statuses = ['voting', 'council_review', 'implementation'];
$active_proposals = array_values(array_filter($proposals, function ($proposal) use ($active_statuses) {
    return in_array($proposal['status'], $active_statuses, true);
}));
$history_proposals = array_values(array_filter($proposals, function ($proposal) use ($active_statuses) {
    return !in_array($proposal['status'], $active_statuses, true);
}));
$implemented_count = count(array_filter($proposals, function ($proposal) {
    return $proposal['status'] === 'implemented';
}));
$member_count = count($members);
$default_quorum = gso_quorum_required(max(1, $member_count));
$gso_flash = $_SESSION['gso_flash'] ?? null;
unset($_SESSION['gso_flash']);

$page_title = [
    'ru' => 'Генеральный совет Ориона — Project Orion',
    'uk' => 'Генеральна рада Оріона — Project Orion',
    'en' => 'General Council of Orion — Project Orion',
][$lang];
$page_description = $copy['lead'];
$page_path = 'gso.php';
$active_page = 'gso';
$banner_subtext = [
    'ru' => 'Открытая система решений команды',
    'uk' => 'Відкрита система рішень команди',
    'en' => 'Open team decision system',
][$lang];
$page_styles = ['gso.css?v=6'];
$page_scripts = ['js/gso.js?v=2'];
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell gso-page">
    <section class="gso-masthead" aria-labelledby="gso-title">
        <div class="gso-masthead-seal">
            <img
                src="images/gso.png"
                width="1024"
                height="1024"
                alt="<?php echo gso_h(['ru' => 'Логотип Генерального совета Ориона', 'uk' => 'Логотип Генеральної ради Оріона', 'en' => 'General Council of Orion logo'][$lang]); ?>"
            >
        </div>
        <div class="gso-masthead-copy">
            <p class="eyebrow"><?php echo gso_h($copy['eyebrow']); ?></p>
            <h1 id="gso-title"><?php echo gso_h($copy['title']); ?></h1>
            <p class="gso-masthead-lead"><?php echo gso_h($copy['lead']); ?></p>
        </div>
    </section>

    <dl class="gso-dateline" aria-label="<?php echo gso_h(['ru' => 'Состояние сессии', 'uk' => 'Стан сесії', 'en' => 'Session state'][$lang]); ?>">
        <div>
            <dt><?php echo gso_h($copy['session_open']); ?></dt>
            <dd class="is-live"><i aria-hidden="true"></i><?php echo gso_h($copy['session_state']); ?></dd>
        </div>
        <div><dt><?php echo gso_h($copy['dateline_seats']); ?></dt><dd><?php echo $member_count; ?></dd></div>
        <div><dt><?php echo gso_h($copy['dateline_quorum']); ?></dt><dd><?php echo $default_quorum; ?></dd></div>
        <div><dt><?php echo gso_h($copy['dateline_active']); ?></dt><dd><?php echo count($active_proposals); ?></dd></div>
        <div><dt><?php echo gso_h($copy['dateline_done']); ?></dt><dd><?php echo $implemented_count; ?></dd></div>
    </dl>

    <?php if ($gso_flash): ?>
        <div class="alert <?php echo $gso_flash['type'] === 'danger' ? 'alert-danger' : 'alert-success'; ?>" style="margin-top: 22px;"><?php echo gso_h($gso_flash['message']); ?></div>
    <?php endif; ?>

    <section class="gso-procedure" aria-label="<?php echo gso_h($copy['procedure']); ?>">
        <span class="gso-label"><?php echo gso_h($copy['procedure']); ?></span>
        <div class="gso-procedure-rail">
            <article class="gso-station gso-station--now">
                <strong><?php echo gso_h($copy['flow_vote']); ?></strong>
                <p><?php echo gso_h($copy['flow_vote_note']); ?></p>
            </article>
            <article class="gso-station">
                <strong><?php echo gso_h($copy['flow_head']); ?></strong>
                <p><?php echo gso_h($copy['flow_head_note']); ?></p>
            </article>
            <article class="gso-station">
                <strong><?php echo gso_h($copy['flow_admin']); ?></strong>
                <p><?php echo gso_h($copy['flow_admin_note']); ?></p>
            </article>
        </div>
    </section>

    <section class="gso-section gso-chamber-section" aria-labelledby="gso-chamber-title">
        <header class="gso-section-head">
            <div>
                <span class="gso-label">COUNCIL CHAMBER</span>
                <h2 id="gso-chamber-title"><?php echo gso_h($copy['chamber']); ?></h2>
                <p><?php echo gso_h($copy['chamber_note']); ?></p>
            </div>
            <span class="gso-seat-counter"><?php echo $member_count; ?></span>
        </header>

        <div class="gso-chamber card">
            <div class="gso-leadership">
                <span class="gso-label"><?php echo gso_h($copy['leadership']); ?></span>
                <div class="gso-leadership-ladder">
                    <div class="gso-leadership-tier gso-leadership-tier--project">
                        <div class="gso-leadership-office">
                            <span class="gso-leadership-order">01 · PROJECT ORION</span>
                            <strong><?php echo gso_h($copy['project_head_office']); ?></strong>
                        </div>
                        <div class="gso-leadership-people">
                            <?php if ($project_heads): ?>
                                <?php foreach ($project_heads as $member): ?>
                                    <article class="gso-leader gso-seat--admin">
                                        <span class="gso-seat-avatar notranslate" translate="no"><?php echo gso_h(gso_initial($member['username'])); ?></span>
                                        <div>
                                             <strong class="notranslate" translate="no"><?php echo gso_h($member['username']); ?></strong>
                                            <small><?php echo gso_h(gso_role_public_label($member['access']['role'], $lang)); ?></small>
                                            <?php $member_discord = gso_discord_username($member); ?>
                                             <?php if ($member_discord !== ''): ?><small class="gso-member-discord notranslate" translate="no"><?php echo gso_h($member_discord); ?></small><?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <article class="gso-leader gso-seat--vacant">
                                    <span class="gso-seat-avatar">+</span>
                                    <div><strong><?php echo gso_h($copy['not_assigned']); ?></strong></div>
                                </article>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="gso-leadership-tier gso-leadership-tier--developer">
                        <div class="gso-leadership-office">
                            <span class="gso-leadership-order">02 · <?php echo gso_h($copy['technical_mandate']); ?></span>
                            <strong><?php echo gso_h($copy['developer_office']); ?></strong>
                        </div>
                        <div class="gso-leadership-people">
                            <?php if ($developers): ?>
                                <?php foreach ($developers as $member): ?>
                                    <article class="gso-leader gso-seat--developer">
                                        <span class="gso-seat-avatar notranslate" translate="no"><?php echo gso_h(gso_initial($member['username'])); ?></span>
                                        <div>
                                             <strong class="notranslate" translate="no"><?php echo gso_h($member['username']); ?></strong>
                                            <small><?php echo gso_h(gso_role_public_label($member['access']['role'], $lang)); ?></small>
                                            <?php $member_discord = gso_discord_username($member); ?>
                                             <?php if ($member_discord !== ''): ?><small class="gso-member-discord notranslate" translate="no"><?php echo gso_h($member_discord); ?></small><?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <article class="gso-leader gso-seat--vacant">
                                    <span class="gso-seat-avatar">+</span>
                                    <div><strong><?php echo gso_h($copy['not_assigned']); ?></strong></div>
                                </article>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="gso-leadership-tier gso-leadership-tier--council">
                        <div class="gso-leadership-office">
                            <span class="gso-leadership-order">03 · <?php echo gso_h($copy['leadership_mandate']); ?></span>
                            <strong><?php echo gso_h($copy['council_head_office']); ?></strong>
                        </div>
                        <div class="gso-leadership-people">
                            <?php if ($council_heads): ?>
                                <?php foreach ($council_heads as $member): ?>
                                    <article class="gso-leader gso-seat--council">
                                        <span class="gso-seat-avatar notranslate" translate="no"><?php echo gso_h(gso_initial($member['username'])); ?></span>
                                        <div>
                                             <strong class="notranslate" translate="no"><?php echo gso_h($member['username']); ?></strong>
                                            <small><?php echo gso_h(gso_role_public_label($member['access']['role'], $lang)); ?></small>
                                            <?php $member_discord = gso_discord_username($member); ?>
                                             <?php if ($member_discord !== ''): ?><small class="gso-member-discord notranslate" translate="no"><?php echo gso_h($member_discord); ?></small><?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <article class="gso-leader gso-seat--vacant">
                                    <span class="gso-seat-avatar">+</span>
                                    <div><strong><?php echo gso_h($copy['not_assigned']); ?></strong></div>
                                </article>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gso-parliament">
                <div class="gso-parliament-heading">
                    <span class="gso-label"><?php echo gso_h($copy['delegate_floor']); ?></span>
                    <span><?php echo count($council_members); ?> <?php echo gso_h($copy['members']); ?></span>
                </div>
                <div class="gso-role-groups" aria-label="<?php echo gso_h($copy['members_title']); ?>">
                    <?php foreach ($delegate_role_groups as $role => $group): ?>
                        <section class="gso-role-group gso-role-group--<?php echo gso_h($group['tone']); ?>">
                            <header class="gso-role-group-heading">
                                <strong><?php echo gso_h(gso_role_public_label($role, $lang)); ?></strong>
                                <span><?php echo count($group['members']); ?></span>
                            </header>
                            <div class="gso-role-group-seats">
                                <?php foreach ($group['members'] as $member): ?>
                                    <article class="gso-role-seat gso-seat--<?php echo gso_h($group['tone']); ?>">
                                        <span class="gso-seat-avatar notranslate" translate="no"><?php echo gso_h(gso_initial($member['username'])); ?></span>
                                        <div>
                                         <strong class="notranslate" translate="no"><?php echo gso_h($member['username']); ?></strong>
                                            <?php $member_discord = gso_discord_username($member); ?>
                                         <?php if ($member_discord !== ''): ?><small class="gso-member-discord notranslate" translate="no"><?php echo gso_h($member_discord); ?></small><?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                                <?php if (!$group['members']): ?><p class="gso-role-group-empty"><?php echo gso_h($copy['no_role_members']); ?></p><?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="gso-seat-legend" aria-label="<?php echo gso_h($copy['seat_legend']); ?>">
                <strong><?php echo gso_h($copy['seat_legend']); ?></strong>
                <span class="gso-legend-item gso-legend-item--admin"><i></i><?php echo gso_h($copy['project_head_office']); ?> · <?php echo $role_group_counts['admin']; ?></span>
                <span class="gso-legend-item gso-legend-item--council"><i></i><?php echo gso_h($copy['council_head_office']); ?> · <?php echo $role_group_counts['council']; ?></span>
                <span class="gso-legend-item gso-legend-item--developer"><i></i><?php echo gso_h($copy['development_group']); ?> · <?php echo $role_group_counts['developer']; ?></span>
                <span class="gso-legend-item gso-legend-item--senior"><i></i><?php echo gso_h($copy['senior_moderation_group']); ?> · <?php echo $role_group_counts['senior_moderator']; ?></span>
                <span class="gso-legend-item gso-legend-item--moderator"><i></i><?php echo gso_h($copy['moderator_group']); ?> · <?php echo $role_group_counts['moderator']; ?></span>
                <span class="gso-legend-item gso-legend-item--content"><i></i><?php echo gso_h($copy['content_group']); ?> · <?php echo $role_group_counts['content']; ?></span>
            </div>
        </div>
    </section>

    <section class="gso-section" aria-labelledby="gso-submit-title">
        <header class="gso-section-head">
            <div>
                <span class="gso-label">NEW MOTION</span>
                <h2 id="gso-submit-title"><?php echo gso_h($copy['submit_title']); ?></h2>
                <p><?php echo gso_h($copy['submit_note']); ?></p>
            </div>
        </header>
        <?php if ($can_participate): ?>
             <form method="POST" action="<?php echo gso_h(i18n_locale_path('gso.php')); ?>" class="gso-motion-form">
                <input type="hidden" name="csrf_token" value="<?php echo gso_h($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="create_proposal">
                <label><span><?php echo gso_h($copy['proposal_title']); ?></span><input class="form-control" type="text" name="title" minlength="8" maxlength="180" required></label>
                <label><span><?php echo gso_h($copy['description']); ?></span><textarea class="form-control" name="description" minlength="30" maxlength="10000" required></textarea></label>
                <label><span><?php echo gso_h($copy['expected']); ?></span><textarea class="form-control" name="expected_result" minlength="10" maxlength="4000" required></textarea></label>
                <div class="gso-motion-form-foot">
                    <label><span><?php echo gso_h($copy['duration']); ?></span>
                        <select class="form-control" name="duration_days">
                            <option value="1"><?php echo gso_h($copy['day_1']); ?></option>
                            <option value="3" selected><?php echo gso_h($copy['day_3']); ?></option>
                            <option value="7"><?php echo gso_h($copy['day_7']); ?></option>
                        </select>
                    </label>
                    <button class="btn btn-primary" type="submit"><?php echo gso_h($copy['submit']); ?></button>
                </div>
            </form>
        <?php else: ?>
            <div class="gso-view-only">
                <p><?php echo gso_h($copy['view_only']); ?></p>
                <?php if (!$viewer_id): ?><a class="btn btn-secondary" href="<?php echo gso_h(i18n_locale_path('login.php')); ?>"><?php echo gso_h($copy['login']); ?></a><?php endif; ?>
                <a class="btn btn-secondary" href="<?php echo gso_h(i18n_locale_path('petitions.php')); ?>"><?php echo gso_h($copy['player_petitions']); ?></a>
            </div>
        <?php endif; ?>
    </section>

    <?php
    // Поимённое деление: одно деление на место в зале, штриховая линия — порог кворума.
    $render_proposals = function ($items) use ($copy, $lang, $can_participate, $can_close_vote, $viewer_access, $events_by_proposal, $member_count, $vote_rosters) {
        foreach ($items as $proposal):
            $proposal_id = intval($proposal['id']);
            $yes = intval($proposal['yes_votes']);
            $no = intval($proposal['no_votes']);
            $abstain = intval($proposal['abstain_votes']);
            $cast = $yes + $no + $abstain;
            $quorum_required = max(1, intval($proposal['quorum_required']));
            $seats = max(1, $member_count, $cast, $quorum_required);
            $uncast = max(0, $seats - $cast);
            $quorum_position = min(100, $quorum_required / $seats * 100);
            $quorum_met = $cast >= $quorum_required;
            $vote_roster = $vote_rosters[$proposal_id] ?? ['yes' => [], 'abstain' => [], 'no' => [], 'uncast' => []];
            ?>
            <details class="card gso-proposal gso-proposal--<?php echo gso_h($proposal['status']); ?>" id="proposal-<?php echo $proposal_id; ?>">
                <summary class="gso-proposal-summary">
                    <span class="gso-motion-locator">ГСО-<?php echo str_pad((string)$proposal_id, 4, '0', STR_PAD_LEFT); ?></span>
                    <span class="gso-motion-title notranslate" translate="no"><?php echo gso_h($proposal['title']); ?></span>
                    <span class="gso-status gso-status--<?php echo gso_h($proposal['status']); ?>"><?php echo gso_h(gso_status_label($proposal['status'], $lang)); ?></span>
                    <i aria-hidden="true"></i>
                </summary>
                <div class="gso-proposal-details">
                    <div class="gso-proposal-meta">
                        <span><?php echo gso_h($copy['author']); ?>: <strong class="notranslate" translate="no"><?php echo gso_h($proposal['author_name'] ?: '—'); ?></strong></span>
                        <span><?php echo gso_h($copy['created']); ?>: <strong><?php echo gso_h(date('d.m.Y H:i', strtotime($proposal['created_at']))); ?></strong></span>
                        <span class="<?php echo intval($proposal['vote_round']) > 1 ? 'gso-round-badge' : ''; ?>"><?php echo gso_h($copy['vote_round']); ?>: <strong><?php echo max(1, intval($proposal['vote_round'])); ?></strong></span>
                        <?php if ($proposal['status'] === 'voting'): ?><span><?php echo gso_h($copy['ends']); ?>: <strong><?php echo gso_h(date('d.m.Y H:i', strtotime($proposal['voting_ends_at']))); ?></strong></span><?php endif; ?>
                    </div>

                    <div class="gso-proposal-dashboard">
                        <div class="gso-proposal-body">
                            <p class="notranslate" translate="no"><?php echo nl2br(gso_h($proposal['description'])); ?></p>
                            <div class="gso-expected"><strong><?php echo gso_h($copy['result']); ?></strong><p class="notranslate" translate="no"><?php echo nl2br(gso_h($proposal['expected_result'])); ?></p></div>
                        </div>

                        <div class="gso-division">
                            <div class="gso-division-head">
                                <span class="gso-label"><?php echo gso_h($copy['vote_snapshot']); ?></span>
                                <strong><?php echo $cast; ?><small><?php echo gso_h($copy['total_votes']); ?></small></strong>
                            </div>
                            <div class="gso-division-strip" role="img" aria-label="<?php echo gso_h($copy['yes']); ?> <?php echo $yes; ?>, <?php echo gso_h($copy['abstain']); ?> <?php echo $abstain; ?>, <?php echo gso_h($copy['no']); ?> <?php echo $no; ?>, <?php echo gso_h($copy['uncast']); ?> <?php echo $uncast; ?>">
                                <?php for ($i = 0; $i < $yes; $i++): ?><span class="gso-tick gso-tick--yes"></span><?php endfor; ?>
                                <?php for ($i = 0; $i < $abstain; $i++): ?><span class="gso-tick gso-tick--abstain"></span><?php endfor; ?>
                                <?php for ($i = 0; $i < $no; $i++): ?><span class="gso-tick gso-tick--no"></span><?php endfor; ?>
                                <?php for ($i = 0; $i < $uncast; $i++): ?><span class="gso-tick gso-tick--empty"></span><?php endfor; ?>
                                <span class="gso-quorum-mark" style="left: <?php echo round($quorum_position, 2); ?>%" aria-hidden="true"><span><?php echo gso_h($copy['quorum_progress']); ?> <?php echo $quorum_required; ?></span></span>
                            </div>
                            <div class="gso-division-key">
                                <span class="gso-vote-yes"><i></i><?php echo gso_h($copy['yes']); ?><b><?php echo $yes; ?></b></span>
                                <span class="gso-vote-abstain"><i></i><?php echo gso_h($copy['abstain']); ?><b><?php echo $abstain; ?></b></span>
                                <span class="gso-vote-no"><i></i><?php echo gso_h($copy['no']); ?><b><?php echo $no; ?></b></span>
                            </div>
                            <div class="gso-division-quorum<?php echo $quorum_met ? ' is-met' : ''; ?>">
                                <span><?php echo gso_h($copy['quorum_progress']); ?> <b><?php echo $cast; ?> / <?php echo $quorum_required; ?></b></span>
                                <span><?php echo $quorum_met ? gso_h($copy['quorum_met']) : gso_h($copy['quorum_left']) . ': ' . ($quorum_required - $cast); ?></span>
                            </div>
                            <div class="gso-vote-roster" aria-label="<?php echo gso_h($copy['vote_members']); ?>">
                                <span class="gso-label"><?php echo gso_h($copy['vote_members']); ?></span>
                                <div class="gso-vote-roster-groups">
                                    <?php foreach ([
                                        'yes' => ['label' => $copy['yes'], 'tone' => 'yes'],
                                        'abstain' => ['label' => $copy['abstain'], 'tone' => 'abstain'],
                                        'no' => ['label' => $copy['no'], 'tone' => 'no'],
                                        'uncast' => ['label' => $copy['uncast'], 'tone' => 'uncast'],
                                    ] as $choice => $group): ?>
                                        <section class="gso-vote-roster-group gso-vote-roster-group--<?php echo gso_h($group['tone']); ?>">
                                            <header>
                                                <span><i aria-hidden="true"></i><?php echo gso_h($group['label']); ?></span>
                                                <b><?php echo count($vote_roster[$choice]); ?></b>
                                            </header>
                                            <ul class="gso-vote-member-list">
                                                <?php if (!empty($vote_roster[$choice])): ?>
                                                    <?php foreach ($vote_roster[$choice] as $vote_member): ?>
                                                        <li>
                                                             <span class="gso-vote-member-avatar notranslate" translate="no"><?php echo gso_h(gso_initial($vote_member['username'])); ?></span>
                                                             <span class="gso-vote-member-name notranslate" translate="no"><?php echo gso_h($vote_member['username']); ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <li class="is-empty"><?php echo gso_h($copy['no_voters']); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        </section>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($proposal['status'] === 'voting' && $can_participate): ?>
                        <form method="POST" action="<?php echo gso_h(i18n_locale_path('gso.php#proposal-' . $proposal_id)); ?>" class="gso-vote-actions">
                            <input type="hidden" name="csrf_token" value="<?php echo gso_h($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="vote">
                            <input type="hidden" name="proposal_id" value="<?php echo $proposal_id; ?>">
                            <button class="gso-vote-button gso-vote-button--yes<?php echo $proposal['viewer_choice'] === 'yes' ? ' is-selected' : ''; ?>" type="submit" name="choice" value="yes" data-count="<?php echo $yes; ?>"><?php echo gso_h($copy['yes']); ?></button>
                            <button class="gso-vote-button gso-vote-button--abstain<?php echo $proposal['viewer_choice'] === 'abstain' ? ' is-selected' : ''; ?>" type="submit" name="choice" value="abstain" data-count="<?php echo $abstain; ?>"><?php echo gso_h($copy['abstain']); ?></button>
                            <button class="gso-vote-button gso-vote-button--no<?php echo $proposal['viewer_choice'] === 'no' ? ' is-selected' : ''; ?>" type="submit" name="choice" value="no" data-count="<?php echo $no; ?>"><?php echo gso_h($copy['no']); ?></button>
                        </form>
                    <?php endif; ?>

                    <?php if ($proposal['status'] === 'voting' && $can_close_vote): ?>
                        <form method="POST" action="<?php echo gso_h(i18n_locale_path('gso.php#proposal-' . $proposal_id)); ?>" class="gso-close-vote">
                            <input type="hidden" name="csrf_token" value="<?php echo gso_h($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="close_vote">
                            <input type="hidden" name="proposal_id" value="<?php echo $proposal_id; ?>">
                            <small><?php echo gso_h($copy['close_hint']); ?></small>
                            <button class="btn btn-secondary" type="submit"><?php echo gso_h($copy['close_vote']); ?></button>
                        </form>
                    <?php endif; ?>

                    <?php if ($proposal['status'] === 'council_review' && gso_can_decide_review($viewer_access)): ?>
                        <form method="POST" action="<?php echo gso_h(i18n_locale_path('gso.php#proposal-' . $proposal_id)); ?>" class="gso-head-decision">
                            <input type="hidden" name="csrf_token" value="<?php echo gso_h($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="head_decision">
                            <input type="hidden" name="proposal_id" value="<?php echo $proposal_id; ?>">
                            <div class="gso-head-decision-intro">
                                <strong><?php echo gso_h(gso_is_project_head($viewer_access) ? $copy['head_title_project'] : $copy['head_title']); ?></strong>
                                <p><?php echo gso_h($copy['rejection_rule']); ?></p>
                            </div>
                            <div class="gso-head-decision-controls">
                                <textarea class="form-control" name="head_note" minlength="5" required placeholder="<?php echo gso_h($copy['head_note']); ?>"></textarea>
                                <div>
                                    <button class="btn btn-primary" type="submit" name="decision" value="accept"><?php echo gso_h($copy['accept']); ?></button>
                                    <button class="btn btn-danger" type="submit" name="decision" value="reject"><?php echo gso_h($copy['reject']); ?></button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>

                    <?php if (!empty($proposal['head_note'])): ?>
                        <?php $decided_by_project_head = ($proposal['head_role'] ?? '') === 'admin' || intval($proposal['head_is_admin'] ?? 0) === 1; ?>
                        <div class="gso-decision-note"><strong><?php echo gso_h($decided_by_project_head ? $copy['head_title_project'] : $copy['head_title']); ?> · <span class="notranslate" translate="no"><?php echo gso_h($proposal['head_name'] ?: '—'); ?></span></strong><p class="notranslate" translate="no"><?php echo nl2br(gso_h($proposal['head_note'])); ?></p></div>
                    <?php endif; ?>
                    <?php if (!empty($proposal['implementation_note'])): ?>
                        <div class="gso-decision-note gso-decision-note--implementation"><strong><?php echo gso_h($copy['implementation_note']); ?> · <span class="notranslate" translate="no"><?php echo gso_h($proposal['implementer_name'] ?: '—'); ?></span></strong><p class="notranslate" translate="no"><?php echo nl2br(gso_h($proposal['implementation_note'])); ?></p></div>
                    <?php endif; ?>

                    <details class="gso-timeline">
                        <summary><span><?php echo gso_h($copy['timeline']); ?></span><b><?php echo count($events_by_proposal[$proposal_id] ?? []); ?></b></summary>
                        <ol>
                            <?php foreach (($events_by_proposal[$proposal_id] ?? []) as $event): ?>
                                 <li><span aria-hidden="true"></span><div><strong><?php echo gso_h(gso_event_label($event['event_type'], $lang)); ?></strong><p class="notranslate" translate="no"><?php echo gso_h($event['detail']); ?></p><small><span class="notranslate" translate="no"><?php echo gso_h($event['actor_name'] ?: $copy['system']); ?></span> · <?php echo gso_h(date('d.m.Y H:i', strtotime($event['created_at']))); ?></small></div></li>
                            <?php endforeach; ?>
                        </ol>
                    </details>
                </div>
            </details>
        <?php endforeach;
    };
    ?>

    <section class="gso-section gso-proposals-section" aria-labelledby="gso-active-title">
        <header class="gso-section-head">
            <div><span class="gso-label">ACTIVE MOTIONS</span><h2 id="gso-active-title"><?php echo gso_h($copy['active_title']); ?></h2><p><?php echo gso_h($copy['active_note']); ?></p></div>
            <span class="gso-seat-counter"><?php echo count($active_proposals); ?></span>
        </header>
        <div class="gso-proposal-list"><?php $render_proposals($active_proposals); ?></div>
        <?php if (empty($active_proposals)): ?><div class="empty-state"><p><?php echo gso_h($copy['empty_active']); ?></p></div><?php endif; ?>
    </section>

    <section class="gso-section gso-proposals-section gso-history-section" aria-labelledby="gso-history-title">
        <header class="gso-section-head">
            <div><span class="gso-label">VOTING ARCHIVE</span><h2 id="gso-history-title"><?php echo gso_h($copy['history_title']); ?></h2><p><?php echo gso_h($copy['history_note']); ?></p></div>
            <span class="gso-seat-counter"><?php echo count($history_proposals); ?></span>
        </header>
        <div class="gso-proposal-list"><?php $render_proposals($history_proposals); ?></div>
        <?php if (empty($history_proposals)): ?><div class="empty-state"><p><?php echo gso_h($copy['empty_history']); ?></p></div><?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
