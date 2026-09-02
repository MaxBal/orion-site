<?php

function staff_locale(): string
{
    $lang = function_exists('current_lang') ? current_lang() : 'ru';
    return in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'ru';
}

function staff_localized_value(array $values): string
{
    $lang = staff_locale();
    return (string)($values[$lang] ?? $values['ru'] ?? '');
}

function staff_permission_catalog() {
    $catalog = [
        'dashboard.view' => [
            'group' => ['ru' => 'Обзор', 'uk' => 'Огляд', 'en' => 'Overview'],
            'label' => ['ru' => 'Открывать дашборд', 'uk' => 'Відкривати дашборд', 'en' => 'Open dashboard'],
            'description' => ['ru' => 'Сводная статистика проекта и личная активность сотрудника.', 'uk' => 'Зведена статистика проєкту та особиста активність працівника.', 'en' => 'Project overview statistics and staff member activity.'],
        ],
        'reports.manage' => [
            'group' => ['ru' => 'Модерация', 'uk' => 'Модерація', 'en' => 'Moderation'],
            'label' => ['ru' => 'Обрабатывать баг-репорты', 'uk' => 'Обробляти баг-репорти', 'en' => 'Handle bug reports'],
            'description' => ['ru' => 'Одобрение, смена статуса, комментарии и ограничения для авторов.', 'uk' => 'Схвалення, зміна статусу, коментарі та обмеження для авторів.', 'en' => 'Approve reports, change status, comment, and restrict authors.'],
        ],
        'reports.delete' => [
            'group' => ['ru' => 'Модерация', 'uk' => 'Модерація', 'en' => 'Moderation'],
            'label' => ['ru' => 'Удалять баг-репорты', 'uk' => 'Видаляти баг-репорти', 'en' => 'Delete bug reports'],
            'description' => ['ru' => 'Безвозвратное удаление репортов и комментариев.', 'uk' => 'Безповоротне видалення репортів і коментарів.', 'en' => 'Permanently delete reports and comments.'],
        ],
        'users.view' => [
            'group' => ['ru' => 'Пользователи', 'uk' => 'Користувачі', 'en' => 'Users'],
            'label' => ['ru' => 'Просматривать пользователей', 'uk' => 'Переглядати користувачів', 'en' => 'View users'],
            'description' => ['ru' => 'Поиск аккаунтов, IP, почты и статусов блокировки.', 'uk' => 'Пошук акаунтів, IP, пошти та статусів блокування.', 'en' => 'Search accounts, IPs, email, and ban status.'],
        ],
        'users.edit' => [
            'group' => ['ru' => 'Пользователи', 'uk' => 'Користувачі', 'en' => 'Users'],
            'label' => ['ru' => 'Редактировать аккаунты', 'uk' => 'Редагувати акаунти', 'en' => 'Edit accounts'],
            'description' => ['ru' => 'Никнейм, ресурсы, слоты и другие игровые параметры.', 'uk' => 'Нікнейм, ресурси, слоти та інші ігрові параметри.', 'en' => 'Nicknames, resources, slots, and other game parameters.'],
        ],
        'users.credentials' => [
            'group' => ['ru' => 'Пользователи', 'uk' => 'Користувачі', 'en' => 'Users'],
            'label' => ['ru' => 'Сбрасывать пароли', 'uk' => 'Скидати паролі', 'en' => 'Reset passwords'],
            'description' => ['ru' => 'Принудительная установка нового пароля пользователю.', 'uk' => 'Примусове встановлення нового пароля користувачу.', 'en' => 'Force a new password for a user.'],
        ],
        'bans.manage' => [
            'group' => ['ru' => 'Безопасность', 'uk' => 'Безпека', 'en' => 'Security'],
            'label' => ['ru' => 'Выдавать блокировки', 'uk' => 'Видавати блокування', 'en' => 'Issue bans'],
            'description' => ['ru' => 'Баны аккаунтов, IP и MAC-адресов.', 'uk' => 'Бани акаунтів, IP і MAC-адрес.', 'en' => 'Account, IP, and MAC address bans.'],
        ],
        'bans.unban' => [
            'group' => ['ru' => 'Безопасность', 'uk' => 'Безпека', 'en' => 'Security'],
            'label' => ['ru' => 'Снимать блокировки', 'uk' => 'Знімати блокування', 'en' => 'Lift bans'],
            'description' => ['ru' => 'Удаление действующих правил блокировки.', 'uk' => 'Видалення чинних правил блокування.', 'en' => 'Remove active ban rules.'],
        ],
        'news.manage' => [
            'group' => ['ru' => 'Контент', 'uk' => 'Контент', 'en' => 'Content'],
            'label' => ['ru' => 'Управлять новостями', 'uk' => 'Керувати новинами', 'en' => 'Manage news'],
            'description' => ['ru' => 'Создание, публикация и редактирование новостей и медиа.', 'uk' => 'Створення, публікація та редагування новин і медіа.', 'en' => 'Create, publish, and edit news and media.'],
        ],
        'news.delete' => [
            'group' => ['ru' => 'Контент', 'uk' => 'Контент', 'en' => 'Content'],
            'label' => ['ru' => 'Удалять новости', 'uk' => 'Видаляти новини', 'en' => 'Delete news'],
            'description' => ['ru' => 'Безвозвратное удаление публикаций.', 'uk' => 'Безповоротне видалення публікацій.', 'en' => 'Permanently delete publications.'],
        ],
        'vehicles.manage' => [
            'group' => ['ru' => 'Система', 'uk' => 'Система', 'en' => 'System'],
            'label' => ['ru' => 'Управлять техникой', 'uk' => 'Керувати технікою', 'en' => 'Manage vehicles'],
            'description' => ['ru' => 'Глобальные и персональные правила доступа к технике.', 'uk' => 'Глобальні та персональні правила доступу до техніки.', 'en' => 'Global and personal vehicle access rules.'],
        ],
        'downloads.manage' => [
            'group' => ['ru' => 'Система', 'uk' => 'Система', 'en' => 'System'],
            'label' => ['ru' => 'Управлять загрузками', 'uk' => 'Керувати завантаженнями', 'en' => 'Manage downloads'],
            'description' => ['ru' => 'Ссылки, зеркала, видео и инструкции клиента.', 'uk' => 'Посилання, дзеркала, відео та інструкції клієнта.', 'en' => 'Links, mirrors, videos, and client instructions.'],
        ],
        'staff.view' => [
            'group' => ['ru' => 'Команда', 'uk' => 'Команда', 'en' => 'Team'],
            'label' => ['ru' => 'Просматривать команду', 'uk' => 'Переглядати команду', 'en' => 'View team'],
            'description' => ['ru' => 'Состав команды, роли и эффективные разрешения.', 'uk' => 'Склад команди, ролі та чинні дозволи.', 'en' => 'Team membership, roles, and effective permissions.'],
        ],
        'staff.manage' => [
            'group' => ['ru' => 'Команда', 'uk' => 'Команда', 'en' => 'Team'],
            'label' => ['ru' => 'Управлять командой', 'uk' => 'Керувати командою', 'en' => 'Manage team'],
            'description' => ['ru' => 'Назначение нижестоящих ролей и персональных исключений.', 'uk' => 'Призначення нижчих ролей і персональних винятків.', 'en' => 'Assign lower-ranked roles and personal exceptions.'],
        ],
        'audit.view' => [
            'group' => ['ru' => 'Команда', 'uk' => 'Команда', 'en' => 'Team'],
            'label' => ['ru' => 'Просматривать аудит', 'uk' => 'Переглядати аудит', 'en' => 'View audit'],
            'description' => ['ru' => 'Полный журнал действий администрации и модераторов.', 'uk' => 'Повний журнал дій адміністрації та модераторів.', 'en' => 'Full administration and moderation action log.'],
        ],
        'updates.manage' => [
            'group' => ['ru' => 'Проект', 'uk' => 'Проєкт', 'en' => 'Project'],
            'label' => ['ru' => 'Управлять историей обновлений', 'uk' => 'Керувати історією оновлень', 'en' => 'Manage update history'],
            'description' => ['ru' => 'Создание, публикация, редактирование и удаление патчноутов.', 'uk' => 'Створення, публікація, редагування та видалення патчноутів.', 'en' => 'Create, publish, edit, and delete patch notes.'],
        ],
        'server.manage' => [
            'group' => ['ru' => 'Проект', 'uk' => 'Проєкт', 'en' => 'Project'],
            'label' => ['ru' => 'Менять статус сервера', 'uk' => 'Змінювати статус сервера', 'en' => 'Change server status'],
            'description' => ['ru' => 'Переключение публичного статуса сервера онлайн или офлайн.', 'uk' => 'Перемикання публічного статусу сервера онлайн або офлайн.', 'en' => 'Switch the public server status online or offline.'],
        ],
        'council.participate' => [
            'group' => ['ru' => 'ГСО', 'uk' => 'ГСО', 'en' => 'GSO'],
            'label' => ['ru' => 'Участвовать в ГСО', 'uk' => 'Брати участь у ГСО', 'en' => 'Participate in GSO'],
            'description' => ['ru' => 'Создание предложений и голосование в Генеральном совете Ориона.', 'uk' => 'Створення пропозицій і голосування в Генеральній раді Оріона.', 'en' => 'Create proposals and vote in the General Council of Orion.'],
        ],
        'council.review' => [
            'group' => ['ru' => 'ГСО', 'uk' => 'ГСО', 'en' => 'GSO'],
            'label' => ['ru' => 'Утверждать решения ГСО', 'uk' => 'Затверджувати рішення ГСО', 'en' => 'Approve GSO decisions'],
            'description' => ['ru' => 'Финальное решение главы совета после успешного голосования.', 'uk' => 'Фінальне рішення голови ради після успішного голосування.', 'en' => 'Final council-head decision after a successful vote.'],
        ],
        'council.implement' => [
            'group' => ['ru' => 'ГСО', 'uk' => 'ГСО', 'en' => 'GSO'],
            'label' => ['ru' => 'Реализовывать решения ГСО', 'uk' => 'Реалізовувати рішення ГСО', 'en' => 'Implement GSO decisions'],
            'description' => ['ru' => 'Очередь решений, одобренных главой совета, для главы проекта.', 'uk' => 'Черга рішень, схвалених головою ради, для голови проєкту.', 'en' => 'Queue of decisions approved by the council head for the project head.'],
        ],
    ];
    foreach ($catalog as &$permission) {
        foreach (['group', 'label', 'description'] as $field) {
            $permission[$field] = staff_localized_value($permission[$field]);
        }
    }
    unset($permission);
    return $catalog;
}

function staff_role_definitions() {
    $all_permissions = array_keys(staff_permission_catalog());
    $council_admin_permissions = [
        'dashboard.view', 'reports.manage', 'reports.delete', 'users.view',
        'users.edit', 'users.credentials', 'bans.manage', 'bans.unban',
        'news.manage', 'news.delete', 'staff.view', 'staff.manage', 'audit.view',
        'council.participate', 'council.review',
    ];
    $roles = [
        'admin' => [
            'label' => ['ru' => 'Глава / создатель', 'uk' => 'Глава / засновник', 'en' => 'Lead / creator'],
            'short_label' => ['ru' => 'Глава', 'uk' => 'Глава', 'en' => 'Lead'],
            'rank' => 100,
            'tone' => 'admin',
            'description' => ['ru' => 'Создатель проекта с полным доступом к команде и системным настройкам.', 'uk' => 'Засновник проєкту з повним доступом до команди та системних налаштувань.', 'en' => 'Project creator with full access to the team and system settings.'],
            'permissions' => $all_permissions,
        ],
        'developer' => [
            'label' => ['ru' => 'Разработчик', 'uk' => 'Розробник', 'en' => 'Developer'],
            'short_label' => ['ru' => 'Разработчик', 'uk' => 'Розробник', 'en' => 'Developer'],
            'rank' => 95,
            'tone' => 'developer',
            'description' => ['ru' => 'Развивает проект, руководит техническими системами и имеет полный доступ главы совета к панели управления.', 'uk' => 'Розвиває проєкт, керує технічними системами та має повний доступ голови ради до панелі керування.', 'en' => 'Develops the project, leads technical systems, and has the council head’s full panel access.'],
            'permissions' => array_values(array_unique(array_merge(
                $council_admin_permissions,
                ['vehicles.manage', 'downloads.manage']
            ))),
        ],
        'orion_council_head' => [
            'label' => ['ru' => 'Глава совета Ориона', 'uk' => 'Голова ради Оріона', 'en' => 'Head of the Orion Council'],
            'short_label' => ['ru' => 'Глава совета', 'uk' => 'Голова ради', 'en' => 'Council head'],
            'rank' => 90,
            'tone' => 'council',
            'description' => ['ru' => 'Руководит советом Ориона, командой, модерацией и публичными материалами проекта.', 'uk' => 'Керує радою Оріона, командою, модерацією та публічними матеріалами проєкту.', 'en' => 'Leads the Orion council, team, moderation, and public project materials.'],
            'permissions' => $council_admin_permissions,
        ],
        'senior_moderator' => [
            'label' => ['ru' => 'Старший модератор', 'uk' => 'Старший модератор', 'en' => 'Senior moderator'],
            'short_label' => ['ru' => 'Старший модер', 'uk' => 'Старший модер', 'en' => 'Senior mod'],
            'rank' => 70,
            'tone' => 'senior',
            'description' => ['ru' => 'Руководит обработкой репортов без доступа к аккаунтам и блокировкам игроков.', 'uk' => 'Керує обробкою репортів без доступу до акаунтів і блокувань гравців.', 'en' => 'Leads report handling without access to player accounts or bans.'],
            'permissions' => ['dashboard.view', 'reports.manage', 'reports.delete', 'council.participate'],
            'permissions_fixed' => true,
        ],
        'moderator' => [
            'label' => ['ru' => 'Модератор', 'uk' => 'Модератор', 'en' => 'Moderator'],
            'short_label' => ['ru' => 'Модератор', 'uk' => 'Модератор', 'en' => 'Moderator'],
            'rank' => 50,
            'tone' => 'moderator',
            'description' => ['ru' => 'Обрабатывает репорты без доступа к аккаунтам и блокировкам игроков.', 'uk' => 'Обробляє репорти без доступу до акаунтів і блокувань гравців.', 'en' => 'Handles reports without access to player accounts or bans.'],
            'permissions' => ['dashboard.view', 'reports.manage', 'council.participate'],
            'permissions_fixed' => true,
        ],
        'content_maker' => [
            'label' => ['ru' => 'Контент-мейкер', 'uk' => 'Контент-мейкер', 'en' => 'Content maker'],
            'short_label' => ['ru' => 'Контент', 'uk' => 'Контент', 'en' => 'Content'],
            'rank' => 40,
            'tone' => 'content',
            'description' => ['ru' => 'Готовит и публикует материалы проекта.', 'uk' => 'Готує та публікує матеріали проєкту.', 'en' => 'Prepares and publishes project materials.'],
            'permissions' => ['dashboard.view', 'news.manage', 'council.participate'],
        ],
        'player' => [
            'label' => ['ru' => 'Игрок', 'uk' => 'Гравець', 'en' => 'Player'],
            'short_label' => ['ru' => 'Игрок', 'uk' => 'Гравець', 'en' => 'Player'],
            'rank' => 0,
            'tone' => 'player',
            'description' => ['ru' => 'Обычный аккаунт без доступа к панели управления.', 'uk' => 'Звичайний акаунт без доступу до панелі керування.', 'en' => 'A regular account without access to the control panel.'],
            'permissions' => [],
        ],
    ];
    foreach ($roles as &$role) {
        foreach (['label', 'short_label', 'description'] as $field) {
            $role[$field] = staff_localized_value($role[$field]);
        }
    }
    unset($role);
    return $roles;
}

function normalize_staff_role($role, $is_admin = false) {
    if ($is_admin) {
        return 'admin';
    }
    $role = trim((string)$role);
    $roles = staff_role_definitions();
    return isset($roles[$role]) ? $role : 'player';
}

function staff_role_info($role) {
    $roles = staff_role_definitions();
    return $roles[$role] ?? $roles['player'];
}

function ensure_staff_schema($pdo) {
    if (!db_column_exists($pdo, 'accounts', 'id')) {
        return;
    }
    if (!db_column_exists($pdo, 'accounts', 'staff_role')) {
        $pdo->exec("ALTER TABLE accounts ADD COLUMN staff_role VARCHAR(32) NOT NULL DEFAULT 'player'");
        $pdo->exec("UPDATE accounts SET staff_role = 'admin' WHERE is_admin = 1");
    } else {
        $pdo->exec("UPDATE accounts SET staff_role = 'admin' WHERE is_admin = 1 AND staff_role <> 'admin'");
        $pdo->exec("UPDATE accounts SET staff_role = 'player' WHERE is_admin = 0 AND staff_role NOT IN ('admin','orion_council_head','developer','senior_moderator','moderator','content_maker','player')");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS staff_permission_overrides (
        account_id BIGINT NOT NULL,
        permission_key VARCHAR(64) NOT NULL,
        allowed SMALLINT NOT NULL,
        granted_by BIGINT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (account_id, permission_key)
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_staff_permission_key ON staff_permission_overrides (permission_key)");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trg_staff_permission_overrides_updated_at') THEN CREATE TRIGGER trg_staff_permission_overrides_updated_at BEFORE UPDATE ON staff_permission_overrides FOR EACH ROW EXECUTE FUNCTION update_updated_at_column(); END IF; END $$");

    $pdo->exec("CREATE TABLE IF NOT EXISTS staff_action_log (
        id BIGINT GENERATED BY DEFAULT AS IDENTITY,
        actor_account_id BIGINT NULL,
        action_key VARCHAR(80) NOT NULL,
        target_type VARCHAR(32) NOT NULL DEFAULT '',
        target_id VARCHAR(128) NOT NULL DEFAULT '',
        summary VARCHAR(255) NOT NULL DEFAULT '',
        metadata_json TEXT NULL,
        ip_address VARCHAR(45) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_staff_action_actor ON staff_action_log (actor_account_id, created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_staff_action_key ON staff_action_log (action_key, created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_staff_action_created ON staff_action_log (created_at)");

    $pdo->exec("CREATE TABLE IF NOT EXISTS staff_notification_reads (
        account_id BIGINT NOT NULL,
        seen_at TIMESTAMP NOT NULL,
        PRIMARY KEY (account_id)
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_staff_notification_seen ON staff_notification_reads (seen_at)");
}

function staff_access_for_account($pdo, $account_id) {
    $account_id = intval($account_id);
    if ($account_id <= 0) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT id, username, is_admin, staff_role FROM accounts WHERE id = ? LIMIT 1");
    $stmt->execute([$account_id]);
    $account = $stmt->fetch();
    if (!$account) {
        return null;
    }

    $role = normalize_staff_role($account['staff_role'] ?? '', intval($account['is_admin'] ?? 0) === 1);
    $role_info = staff_role_info($role);
    $catalog = staff_permission_catalog();
    $permissions = array_fill_keys(array_keys($catalog), false);
    foreach ($role_info['permissions'] as $permission) {
        if (isset($catalog[$permission])) {
            $permissions[$permission] = true;
        }
    }

    $overrides = [];
    if ($role !== 'admin' && empty($role_info['permissions_fixed'])) {
        $stmt = $pdo->prepare("SELECT permission_key, allowed FROM staff_permission_overrides WHERE account_id = ?");
        $stmt->execute([$account_id]);
        foreach ($stmt->fetchAll() as $row) {
            $key = (string)$row['permission_key'];
            if (!isset($catalog[$key])) {
                continue;
            }
            $allowed = intval($row['allowed']) === 1;
            $permissions[$key] = $allowed;
            $overrides[$key] = $allowed;
        }
    }

    return [
        'id' => $account_id,
        'username' => (string)$account['username'],
        'role' => $role,
        'role_info' => $role_info,
        'rank' => intval($role_info['rank']),
        'permissions' => $permissions,
        'overrides' => $overrides,
    ];
}

function staff_access_has($access, $permission) {
    return is_array($access) && !empty($access['permissions'][$permission]);
}

function session_has_staff_permission($permission) {
    if (!empty($_SESSION['is_admin'])) {
        return true;
    }
    $permissions = $_SESSION['staff_permissions'] ?? [];
    return is_array($permissions) && in_array($permission, $permissions, true);
}

function session_is_staff() {
    return session_has_staff_permission('dashboard.view');
}

function staff_notification_read_at($pdo, $account_id) {
    $account_id = intval($account_id);
    if ($account_id <= 0) {
        return null;
    }
    try {
        $stmt = $pdo->prepare('SELECT seen_at FROM staff_notification_reads WHERE account_id = ? LIMIT 1');
        $stmt->execute([$account_id]);
        $seen_at = $stmt->fetchColumn();
        return $seen_at === false ? null : (string)$seen_at;
    } catch (Exception $e) {
        error_log('Staff notification read-state query error: ' . $e->getMessage());
        return null;
    }
}

function staff_notifications_mark_read($pdo, $account_id) {
    $account_id = intval($account_id);
    if ($account_id <= 0) {
        return false;
    }
    try {
        $stmt = $pdo->prepare('INSERT INTO staff_notification_reads (account_id, seen_at) VALUES (?, NOW()) ON CONFLICT (account_id) DO UPDATE SET seen_at = EXCLUDED.seen_at');
        return $stmt->execute([$account_id]);
    } catch (Exception $e) {
        error_log('Staff notification read-state update error: ' . $e->getMessage());
        return false;
    }
}

function staff_notification_feed($pdo, $limit = 8, $account_id = 0) {
    $limit = max(1, min(20, intval($limit)));
    $account_id = intval($account_id);
    static $cache = [];
    $cache_key = $account_id . ':' . $limit;
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $items = [];
    $total = 0;
    $unread = 0;
    $seen_at = staff_notification_read_at($pdo, $account_id);
    $count_source = static function ($pdo, $base_query, $seen_at) {
        try {
            $total = intval($pdo->query($base_query)->fetchColumn());
            if ($seen_at === null) {
                return [$total, $total];
            }
            $stmt = $pdo->prepare($base_query . ' AND created_at > ?');
            $stmt->execute([$seen_at]);
            return [$total, intval($stmt->fetchColumn())];
        } catch (Exception $e) {
            error_log('Staff notification count query error: ' . $e->getMessage());
            return [0, 0];
        }
    };

    [$vote_total, $vote_unread] = $count_source(
        $pdo,
        "SELECT COUNT(*) FROM gso_proposals WHERE status = 'voting' AND voting_ends_at > NOW()",
        $seen_at
    );
    $total += $vote_total;
    $unread += $vote_unread;
    try {
        $stmt = $pdo->query("SELECT p.id, p.title, p.created_at, a.username AS author_name
            FROM gso_proposals AS p
            LEFT JOIN accounts AS a ON a.id = p.author_account_id
            WHERE p.status = 'voting' AND p.voting_ends_at > NOW()
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT {$limit}");
        foreach ($stmt->fetchAll() as $row) {
            $items[] = [
                'kind' => 'vote',
                'id' => intval($row['id']),
                'title' => (string)$row['title'],
                'author_name' => (string)($row['author_name'] ?? ''),
                'created_at' => (string)$row['created_at'],
            ];
        }
    } catch (Exception $e) {
        error_log('Staff GSO notification query error: ' . $e->getMessage());
    }

    [$petition_total, $petition_unread] = $count_source(
        $pdo,
        "SELECT COUNT(*) FROM player_petitions WHERE status = 'collecting'",
        $seen_at
    );
    $total += $petition_total;
    $unread += $petition_unread;
    try {
        $stmt = $pdo->query("SELECT p.id, p.title, p.created_at, a.username AS author_name
            FROM player_petitions AS p
            LEFT JOIN accounts AS a ON a.id = p.author_account_id
            WHERE p.status = 'collecting'
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT {$limit}");
        foreach ($stmt->fetchAll() as $row) {
            $items[] = [
                'kind' => 'petition',
                'id' => intval($row['id']),
                'title' => (string)$row['title'],
                'author_name' => (string)($row['author_name'] ?? ''),
                'created_at' => (string)$row['created_at'],
            ];
        }
    } catch (Exception $e) {
        error_log('Staff petition notification query error: ' . $e->getMessage());
    }

    usort($items, static function ($left, $right) {
        $date_order = strcmp((string)$right['created_at'], (string)$left['created_at']);
        return $date_order !== 0 ? $date_order : intval($right['id']) <=> intval($left['id']);
    });

    return $cache[$cache_key] = [
        'count' => $total,
        'unread_count' => $unread,
        'seen_at' => $seen_at,
        'items' => array_slice($items, 0, $limit),
    ];
}

function staff_notification_text($russian, $english) {
    $lang = staff_locale();
    if ($lang === 'en') {
        return (string)$english;
    }
    if ($lang === 'uk' && function_exists('i18n_translate_text')) {
        return (string)i18n_translate_text($russian, 'uk');
    }
    return (string)$russian;
}

function staff_notifications_html($pdo, $limit = 8) {
    if (empty($_SESSION['user_id']) || !session_is_staff()) {
        return '';
    }

    $escape = static function ($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    };
    $feed = staff_notification_feed($pdo, $limit, intval($_SESSION['user_id']));
    $unread_count = intval($feed['unread_count'] ?? 0);
    $count_label = $unread_count > 99 ? '99+' : (string)$unread_count;
    $return_to = (string)($_SERVER['REQUEST_URI'] ?? 'index.php');
    if ($return_to === '') {
        $return_to = 'index.php';
    }
    $aria_label = staff_notification_text('Уведомления для команды', 'Staff notifications');
    $html = '<details class="staff-notifications">'
        . '<summary class="staff-notifications-toggle' . ($unread_count > 0 ? ' has-unread' : '') . '" aria-label="' . $escape($aria_label) . '">'
        . '<span class="staff-notifications-icon" aria-hidden="true">!</span>'
        . '<span class="staff-notifications-label">' . $escape(staff_notification_text('Уведомления', 'Notifications')) . '</span>'
        . '<span class="staff-notifications-count">' . $escape($count_label) . '</span>'
        . '</summary>'
        . '<div class="staff-notifications-panel">'
        . '<div class="staff-notifications-heading"><strong>' . $escape($aria_label) . '</strong><span>'
        . $escape(staff_notification_text('Активные события', 'Active items')) . '</span></div>';

    if (empty($feed['items'])) {
        $html .= '<p class="staff-notifications-empty">' . $escape(staff_notification_text('Новых голосований и предложений нет.', 'No new votes or proposals.')) . '</p>';
    } else {
        $html .= '<ul class="staff-notifications-list">';
        foreach ($feed['items'] as $notification) {
            $is_vote = (string)$notification['kind'] === 'vote';
            $label = $is_vote ? 'Новое голосование' : 'Новое предложение игрока';
            $url = $is_vote
                ? 'gso.php#proposal-' . intval($notification['id'])
                : 'petitions.php#petition-' . intval($notification['id']);
            if (function_exists('i18n_locale_path')) {
                $url = i18n_locale_path($url);
            }
            $html .= '<li><a class="staff-notification-item" href="' . $escape($url) . '">'
                . '<span class="staff-notification-kind staff-notification-kind--' . ($is_vote ? 'vote' : 'petition') . '">'
                . $escape(staff_notification_text($label, $is_vote ? 'New vote' : 'New player proposal')) . '</span>'
                . '<strong class="notranslate" translate="no">' . $escape($notification['title']) . '</strong>'
                . '<small>';
            if ((string)$notification['author_name'] !== '') {
                $html .= '<span class="notranslate" translate="no">' . $escape($notification['author_name']) . '</span> · ';
            }
            $html .= $escape($notification['created_at']) . '</small></a></li>';
        }
        $html .= '</ul>';
    }

    if ($unread_count > 0) {
        $html .= '<form method="POST" action="notifications.php" class="staff-notifications-read-form">'
            . '<input type="hidden" name="csrf_token" value="' . $escape($_SESSION['csrf_token'] ?? '') . '">'
            . '<input type="hidden" name="return_to" value="' . $escape($return_to) . '">'
            . '<button type="submit" class="staff-notifications-read">'
            . $escape(staff_notification_text('Прочитать уведомления', 'Mark notifications as read'))
            . '</button></form>';
    } elseif (!empty($feed['items'])) {
        $html .= '<p class="staff-notifications-read-state">'
            . $escape(staff_notification_text('Все уведомления прочитаны.', 'All notifications are read.'))
            . '</p>';
    }

    return $html . '</div></details>';
}

function refresh_session_staff_access($pdo) {
    if (empty($_SESSION['user_id'])) {
        unset($_SESSION['staff_role'], $_SESSION['staff_role_label'], $_SESSION['staff_permissions']);
        return null;
    }
    try {
        $access = staff_access_for_account($pdo, intval($_SESSION['user_id']));
    } catch (Exception $e) {
        error_log('Staff session refresh error: ' . $e->getMessage());
        return null;
    }
    if (!$access) {
        return null;
    }
    $_SESSION['is_admin'] = $access['role'] === 'admin';
    $_SESSION['staff_role'] = $access['role'];
    $_SESSION['staff_role_label'] = $access['role_info']['label'];
    $_SESSION['staff_permissions'] = array_keys(array_filter($access['permissions']));
    return $access;
}

function staff_can_manage_access($actor_access, $target_access) {
    if (!staff_access_has($actor_access, 'staff.manage') || !$target_access) {
        return false;
    }
    if (intval($actor_access['id']) === intval($target_access['id'])) {
        return false;
    }
    return intval($actor_access['rank']) > intval($target_access['rank']);
}

function staff_can_act_on_account($actor_access, $target_access) {
    if (!$actor_access || !$target_access) {
        return false;
    }
    if (intval($actor_access['id']) === intval($target_access['id'])) {
        return false;
    }
    return intval($actor_access['rank']) > intval($target_access['rank']);
}

function staff_assignable_roles($actor_access) {
    $assignable = [];
    $actor_rank = intval($actor_access['rank'] ?? 0);
    foreach (staff_role_definitions() as $key => $definition) {
        if (intval($definition['rank']) < $actor_rank) {
            $assignable[$key] = $definition;
        }
    }
    return $assignable;
}

function log_staff_action($pdo, $action_key, $target_type = '', $target_id = '', $summary = '', $metadata = []) {
    try {
        $actor_id = intval($_SESSION['user_id'] ?? 0) ?: null;
        $stmt = $pdo->prepare("INSERT INTO staff_action_log (actor_account_id, action_key, target_type, target_id, summary, metadata_json, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $actor_id,
            substr((string)$action_key, 0, 80),
            substr((string)$target_type, 0, 32),
            substr((string)$target_id, 0, 128),
            substr((string)$summary, 0, 255),
            empty($metadata) ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            function_exists('get_client_ip') ? get_client_ip() : null,
            date('Y-m-d H:i:s'),
        ]);
    } catch (Exception $e) {
        error_log('Staff audit log error: ' . $e->getMessage());
    }
}

function staff_action_label($action_key) {
    $labels = [
        'vehicle.global' => ['ru' => 'Глобальное правило техники', 'uk' => 'Глобальне правило техніки', 'en' => 'Global vehicle rule'],
        'vehicle.account' => ['ru' => 'Правило техники игрока', 'uk' => 'Правило техніки гравця', 'en' => 'Player vehicle rule'],
        'account.update' => ['ru' => 'Изменение аккаунта', 'uk' => 'Зміна акаунта', 'en' => 'Account changed'],
        'account.username' => ['ru' => 'Изменение никнейма', 'uk' => 'Зміна нікнейму', 'en' => 'Username changed'],
        'account.password' => ['ru' => 'Сброс пароля', 'uk' => 'Скидання пароля', 'en' => 'Password reset'],
        'ban.create' => ['ru' => 'Выдана блокировка', 'uk' => 'Видано блокування', 'en' => 'Ban issued'],
        'ban.bulk' => ['ru' => 'Массовая блокировка', 'uk' => 'Масове блокування', 'en' => 'Bulk ban'],
        'ban.remove' => ['ru' => 'Снята блокировка', 'uk' => 'Знято блокування', 'en' => 'Ban removed'],
        'news.save' => ['ru' => 'Сохранена новость', 'uk' => 'Збережено новину', 'en' => 'News saved'],
        'news.delete' => ['ru' => 'Удалена новость', 'uk' => 'Видалено новину', 'en' => 'News deleted'],
        'news.media.delete' => ['ru' => 'Удалено медиа новости', 'uk' => 'Видалено медіа новини', 'en' => 'News media deleted'],
        'downloads.save' => ['ru' => 'Обновлены загрузки', 'uk' => 'Оновлено завантаження', 'en' => 'Downloads updated'],
        'report.update' => ['ru' => 'Обновлён баг-репорт', 'uk' => 'Оновлено баг-репорт', 'en' => 'Bug report updated'],
        'report.close_all' => ['ru' => 'Закрыты все баг-репорты', 'uk' => 'Закрито всі баг-репорти', 'en' => 'All bug reports closed'],
        'report.delete' => ['ru' => 'Удалён баг-репорт', 'uk' => 'Видалено баг-репорт', 'en' => 'Bug report deleted'],
        'report.comment.delete' => ['ru' => 'Удалён комментарий', 'uk' => 'Видалено коментар', 'en' => 'Comment deleted'],
        'report.author.restrict' => ['ru' => 'Ограничен автор репортов', 'uk' => 'Обмежено автора репортів', 'en' => 'Report author restricted'],
        'staff.access.update' => ['ru' => 'Изменены права сотрудника', 'uk' => 'Змінено права працівника', 'en' => 'Staff access changed'],
        'contract.application.create' => ['ru' => 'Подана заявка в команду', 'uk' => 'Подано заявку до команди', 'en' => 'Team application submitted'],
        'contract.application.renew' => ['ru' => 'Подана заявка на продление контракта', 'uk' => 'Подано заявку на поновлення контракту', 'en' => 'Contract renewal submitted'],
        'contract.application.reject' => ['ru' => 'Отклонена заявка в команду', 'uk' => 'Відхилено заявку до команди', 'en' => 'Team application rejected'],
        'contract.application.approve' => ['ru' => 'Принят и активирован контракт', 'uk' => 'Прийнято й активовано контракт', 'en' => 'Contract approved and activated'],
        'contract.terminate' => ['ru' => 'Расторгнут контракт', 'uk' => 'Розірвано контракт', 'en' => 'Contract terminated'],
        'updates.save' => ['ru' => 'Сохранена запись обновлений', 'uk' => 'Збережено запис оновлень', 'en' => 'Update entry saved'],
        'updates.delete' => ['ru' => 'Удалена запись обновлений', 'uk' => 'Видалено запис оновлень', 'en' => 'Update entry deleted'],
        'server.status' => ['ru' => 'Изменён статус сервера', 'uk' => 'Змінено статус сервера', 'en' => 'Server status changed'],
        'gso.proposal.create' => ['ru' => 'Создано предложение ГСО', 'uk' => 'Створено пропозицію ГСО', 'en' => 'GSO proposal created'],
        'gso.vote' => ['ru' => 'Учтён голос ГСО', 'uk' => 'Враховано голос ГСО', 'en' => 'GSO vote recorded'],
        'gso.head.decision' => ['ru' => 'Решение главы совета ГСО', 'uk' => 'Рішення голови ради ГСО', 'en' => 'GSO council-head decision'],
        'gso.implementation' => ['ru' => 'Обновлена реализация решения ГСО', 'uk' => 'Оновлено реалізацію рішення ГСО', 'en' => 'GSO implementation updated'],
        'gso.status' => ['ru' => 'Изменён статус решения ГСО', 'uk' => 'Змінено статус рішення ГСО', 'en' => 'GSO decision status changed'],
    ];
    return isset($labels[$action_key])
        ? staff_localized_value($labels[$action_key])
        : str_replace('.', ' · ', (string)$action_key);
}
