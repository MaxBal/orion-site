<?php

const ORION_CONTRACT_TERM_DAYS = 7;
const ORION_CONTRACT_RENEWAL_DAY = 5;
const ORION_CONTRACT_COOLDOWN_DAYS = 7;
const ORION_CONTRACT_TIMEZONE = 'Europe/Kyiv';
const ORION_CONTRACT_TERMS_VERSION = 'staff-v6-2026-07';

function contract_locale(): string
{
    $lang = function_exists('current_lang') ? current_lang() : 'ru';
    return in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'ru';
}

function contract_localize_message($message, $lang = null): string
{
    $message = (string)$message;
    $lang = $lang === null ? contract_locale() : $lang;
    $lang = in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'ru';
    if ($lang === 'ru') {
        return $message;
    }

    $translations = [
        'uk' => [
            'Выберите корректный тип контракта.' => 'Оберіть коректний тип контракту.',
            'Комментарий не должен превышать 2000 символов.' => 'Коментар не має перевищувати 2000 символів.',
            'Подтвердите условия контракта и публикацию в реестре.' => 'Підтвердьте умови контракту та публікацію в реєстрі.',
            'Аккаунт не найден.' => 'Акаунт не знайдено.',
            'Глава проекта не подаёт контракт самому себе.' => 'Глава проєкту не подає контракт сам собі.',
            'У вас уже есть контракт на рассмотрении.' => 'У вас уже є контракт на розгляді.',
            'У вас уже действует семидневный контракт.' => 'У вас уже діє семиденний контракт.',
            'Аккаунт не найден.' => 'Акаунт не знайдено.',
            'Подтвердите условия продления и публикацию в реестре.' => 'Підтвердьте умови поновлення та публікацію в реєстрі.',
            'Действующий контракт для продления не найден.' => 'Чинний контракт для поновлення не знайдено.',
            'Продление этого контракта уже принято.' => 'Поновлення цього контракту вже прийнято.',
            'Некорректное решение по контракту.' => 'Некоректне рішення щодо контракту.',
            'Принять или отклонить контракт может только глава проекта.' => 'Прийняти або відхилити контракт може лише глава проєкту.',
            'Контракт уже обработан или не найден.' => 'Контракт уже опрацьовано або його не знайдено.',
            'Выбранный тип контракта больше недоступен.' => 'Обраний тип контракту більше недоступний.',
            'Кандидат должен повторно подать контракт и подтвердить актуальные условия.' => 'Кандидат має повторно подати контракт і підтвердити актуальні умови.',
            'Исходный контракт для продления не найден.' => 'Початковий контракт для поновлення не знайдено.',
            'Расторгнутый контракт нельзя продлить.' => 'Розірваний контракт не можна поновити.',
            'Продление должно сохранять роль исходного контракта.' => 'Поновлення має зберігати роль початкового контракту.',
            'У исходного контракта не указан срок завершения.' => 'У початкового контракту не вказано строк завершення.',
            'У пользователя уже действует другой контракт.' => 'У користувача вже діє інший контракт.',
            'Некорректный контракт для расторжения.' => 'Некоректний контракт для розірвання.',
            'Укажите причину расторжения: минимум 5 символов.' => 'Вкажіть причину розірвання: щонайменше 5 символів.',
            'Расторгнуть контракт может только глава проекта.' => 'Розірвати контракт може лише глава проєкту.',
            'Действующий контракт не найден или уже завершён.' => 'Чинний контракт не знайдено або його вже завершено.',
            'Пользователь подал контракт' => 'Користувач подав контракт',
            'Пользователь подал заявку на продление контракта' => 'Користувач подав заявку на поновлення контракту',
        ],
        'en' => [
            'Выберите корректный тип контракта.' => 'Choose a valid contract type.',
            'Комментарий не должен превышать 2000 символов.' => 'The comment must not exceed 2,000 characters.',
            'Подтвердите условия контракта и публикацию в реестре.' => 'Confirm the contract terms and publication in the registry.',
            'Аккаунт не найден.' => 'Account not found.',
            'Глава проекта не подаёт контракт самому себе.' => 'The project lead cannot submit a contract for themselves.',
            'У вас уже есть контракт на рассмотрении.' => 'You already have a contract under review.',
            'У вас уже действует семидневный контракт.' => 'You already have an active seven-day contract.',
            'Подтвердите условия продления и публикацию в реестре.' => 'Confirm the renewal terms and publication in the registry.',
            'Действующий контракт для продления не найден.' => 'No active contract was found for renewal.',
            'Продление этого контракта уже принято.' => 'This contract renewal has already been approved.',
            'Некорректное решение по контракту.' => 'Invalid contract decision.',
            'Принять или отклонить контракт может только глава проекта.' => 'Only the project lead can approve or reject a contract.',
            'Контракт уже обработан или не найден.' => 'The contract was already processed or was not found.',
            'Выбранный тип контракта больше недоступен.' => 'The selected contract type is no longer available.',
            'Кандидат должен повторно подать контракт и подтвердить актуальные условия.' => 'The candidate must resubmit the contract and confirm the current terms.',
            'Исходный контракт для продления не найден.' => 'The original contract for renewal was not found.',
            'Расторгнутый контракт нельзя продлить.' => 'A terminated contract cannot be renewed.',
            'Продление должно сохранять роль исходного контракта.' => 'The renewal must keep the original contract role.',
            'У исходного контракта не указан срок завершения.' => 'The original contract has no expiry date.',
            'У пользователя уже действует другой контракт.' => 'The user already has another active contract.',
            'Некорректный контракт для расторжения.' => 'Invalid contract for termination.',
            'Укажите причину расторжения: минимум 5 символов.' => 'Provide a termination reason of at least 5 characters.',
            'Расторгнуть контракт может только глава проекта.' => 'Only the project lead can terminate a contract.',
            'Действующий контракт не найден или уже завершён.' => 'The active contract was not found or has already ended.',
            'Пользователь подал контракт' => 'User submitted a contract',
            'Пользователь подал заявку на продление контракта' => 'User submitted a contract renewal',
        ],
    ];
    if (isset($translations[$lang][$message])) {
        return $translations[$lang][$message];
    }

    $cooldown_prefix = 'После отказа, расторжения или завершения контракта действует пауза 7 дней. Повторная подача доступна с ';
    if (strncmp($message, $cooldown_prefix, strlen($cooldown_prefix)) === 0) {
        $date = substr($message, strlen($cooldown_prefix));
        $date = preg_replace('/ по киевскому времени\.$/u', '', $date);
        return $lang === 'uk'
            ? 'Після відмови, розірвання або завершення контракту діє пауза 7 днів. Повторне подання доступне з ' . $date . ' за київським часом.'
            : 'After a rejection, termination, or contract expiry, a 7-day cooldown applies. You can apply again from ' . $date . ' Kyiv time.';
    }
    $renewal_prefix = 'Подать заявку на продление можно с пятого календарного дня: ';
    if (strncmp($message, $renewal_prefix, strlen($renewal_prefix)) === 0) {
        $date = substr($message, strlen($renewal_prefix));
        $date = preg_replace('/ по киевскому времени\.$/u', '', $date);
        return $lang === 'uk'
            ? 'Подати заявку на поновлення можна з п’ятого календарного дня: ' . $date . ' за київським часом.'
            : 'You can apply for renewal from calendar day five: ' . $date . ' Kyiv time.';
    }
    return $message;
}

function contract_role_definitions() {
    return [
        'orion_council_head' => ['ru' => 'Глава совета Ориона', 'uk' => 'Голова ради Оріона', 'en' => 'Head of the Orion Council'],
        'developer' => ['ru' => 'Разработчик', 'uk' => 'Розробник', 'en' => 'Developer'],
        'senior_moderator' => ['ru' => 'Старший модератор', 'uk' => 'Старший модератор', 'en' => 'Senior moderator'],
        'moderator' => ['ru' => 'Модератор', 'uk' => 'Модератор', 'en' => 'Moderator'],
        'content_maker' => ['ru' => 'Контент-мейкер', 'uk' => 'Контент-мейкер', 'en' => 'Content maker'],
    ];
}

function contract_role_label($role, $lang = 'ru') {
    $roles = contract_role_definitions();
    $lang = in_array($lang, ['ru', 'uk', 'en'], true) ? $lang : 'ru';
    return $roles[$role][$lang] ?? (string)$role;
}

function contract_allowed_roles($json) {
    $decoded = json_decode((string)$json, true);
    if (!is_array($decoded)) {
        return [];
    }
    $known = contract_role_definitions();
    $roles = [];
    foreach ($decoded as $role) {
        $role = (string)$role;
        if (isset($known[$role])) {
            $roles[$role] = true;
        }
    }
    return array_keys($roles);
}

function contract_is_owner_admin($access = null) {
    if ($access === null) {
        return !empty($_SESSION['user_id']) && (string)($_SESSION['staff_role'] ?? '') === 'admin';
    }
    return is_array($access)
        && (string)($access['role'] ?? '') === 'admin'
        && intval($access['rank'] ?? 0) >= 100;
}

function contract_limit_text($value, $limit) {
    $value = trim((string)$value);
    return function_exists('mb_substr')
        ? mb_substr($value, 0, intval($limit), 'UTF-8')
        : substr($value, 0, intval($limit));
}

function ensure_contract_schema($pdo) {
    static $ready = false;
    if ($ready) {
        return;
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS staff_contract_applications (
        id BIGINT GENERATED BY DEFAULT AS IDENTITY,
        account_id BIGINT NOT NULL,
        application_type VARCHAR(10) NOT NULL DEFAULT 'initial' CHECK (application_type IN ('initial','renewal')),
        renewal_contract_id BIGINT NULL,
        preferred_role VARCHAR(32) NOT NULL,
        motivation TEXT NOT NULL,
        terms_accepted_at TIMESTAMP NULL,
        public_consent_at TIMESTAMP NULL,
        submitted_ip VARCHAR(45) NULL,
        status VARCHAR(10) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
        reviewed_by BIGINT NULL,
        decision_note VARCHAR(500) NOT NULL DEFAULT '',
        reviewed_at TIMESTAMP NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_contract_applications_account ON staff_contract_applications (account_id, created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_contract_applications_status ON staff_contract_applications (status, created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_contract_applications_renewal ON staff_contract_applications (renewal_contract_id)");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trg_staff_contract_applications_updated_at') THEN CREATE TRIGGER trg_staff_contract_applications_updated_at BEFORE UPDATE ON staff_contract_applications FOR EACH ROW EXECUTE FUNCTION update_updated_at_column(); END IF; END $$");

    $application_columns = [
        'application_type' => "ALTER TABLE staff_contract_applications ADD COLUMN application_type VARCHAR(10) NOT NULL DEFAULT 'initial' CHECK (application_type IN ('initial','renewal'))",
        'renewal_contract_id' => "ALTER TABLE staff_contract_applications ADD COLUMN renewal_contract_id BIGINT NULL",
        'terms_accepted_at' => "ALTER TABLE staff_contract_applications ADD COLUMN terms_accepted_at TIMESTAMP NULL",
        'public_consent_at' => "ALTER TABLE staff_contract_applications ADD COLUMN public_consent_at TIMESTAMP NULL",
        'submitted_ip' => "ALTER TABLE staff_contract_applications ADD COLUMN submitted_ip VARCHAR(45) NULL",
    ];
    foreach ($application_columns as $column => $alter_sql) {
        if (!db_column_exists($pdo, 'staff_contract_applications', $column)) {
            $pdo->exec($alter_sql);
        }
    }

    // Legacy columns stay readable so already-issued PDFs remain valid.
    $pdo->exec("CREATE TABLE IF NOT EXISTS staff_contracts (
        id BIGINT GENERATED BY DEFAULT AS IDENTITY,
        public_id CHAR(24) NOT NULL,
        contract_number VARCHAR(40) NOT NULL,
        application_id BIGINT NOT NULL,
        account_id BIGINT NOT NULL,
        staff_role VARCHAR(32) NULL,
        offered_roles_json TEXT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('awaiting_signature','scheduled','active','expired','terminated')),
        parent_contract_id BIGINT NULL,
        offered_by BIGINT NOT NULL,
        offered_at TIMESTAMP NOT NULL,
        signer_name VARCHAR(160) NULL,
        signed_at TIMESTAMP NULL,
        starts_at TIMESTAMP NULL,
        expires_at TIMESTAMP NULL,
        renewal_available_at TIMESTAMP NULL,
        renewal_decision VARCHAR(16) NULL,
        renewal_decided_by BIGINT NULL,
        renewal_decided_at TIMESTAMP NULL,
        renewal_note VARCHAR(500) NOT NULL DEFAULT '',
        signature_hash CHAR(64) NULL,
        signature_nonce CHAR(32) NULL,
        signed_ip VARCHAR(45) NULL,
        terms_version VARCHAR(40) NOT NULL,
        terminated_by BIGINT NULL,
        terminated_at TIMESTAMP NULL,
        termination_cause VARCHAR(32) NULL,
        termination_reason VARCHAR(500) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    )");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uniq_contract_public_id') THEN ALTER TABLE staff_contracts ADD CONSTRAINT uniq_contract_public_id UNIQUE (public_id); END IF; END $$");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uniq_contract_number') THEN ALTER TABLE staff_contracts ADD CONSTRAINT uniq_contract_number UNIQUE (contract_number); END IF; END $$");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'uniq_contract_application') THEN ALTER TABLE staff_contracts ADD CONSTRAINT uniq_contract_application UNIQUE (application_id); END IF; END $$");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_contract_account_status ON staff_contracts (account_id, status, expires_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_contract_public_status ON staff_contracts (status, signed_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_contract_parent ON staff_contracts (parent_contract_id)");
    $pdo->exec("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trg_staff_contracts_updated_at') THEN CREATE TRIGGER trg_staff_contracts_updated_at BEFORE UPDATE ON staff_contracts FOR EACH ROW EXECUTE FUNCTION update_updated_at_column(); END IF; END $$");

    $legacy_columns = [
        'parent_contract_id' => "ALTER TABLE staff_contracts ADD COLUMN parent_contract_id BIGINT NULL",
        'renewal_available_at' => "ALTER TABLE staff_contracts ADD COLUMN renewal_available_at TIMESTAMP NULL",
        'renewal_decision' => "ALTER TABLE staff_contracts ADD COLUMN renewal_decision VARCHAR(16) NULL",
        'renewal_decided_by' => "ALTER TABLE staff_contracts ADD COLUMN renewal_decided_by BIGINT NULL",
        'renewal_decided_at' => "ALTER TABLE staff_contracts ADD COLUMN renewal_decided_at TIMESTAMP NULL",
        'renewal_note' => "ALTER TABLE staff_contracts ADD COLUMN renewal_note VARCHAR(500) NOT NULL DEFAULT ''",
        'terminated_by' => "ALTER TABLE staff_contracts ADD COLUMN terminated_by BIGINT NULL",
        'terminated_at' => "ALTER TABLE staff_contracts ADD COLUMN terminated_at TIMESTAMP NULL",
        'termination_cause' => "ALTER TABLE staff_contracts ADD COLUMN termination_cause VARCHAR(32) NULL",
        'termination_reason' => "ALTER TABLE staff_contracts ADD COLUMN termination_reason VARCHAR(500) NOT NULL DEFAULT ''",
    ];
    foreach ($legacy_columns as $column => $alter_sql) {
        if (!db_column_exists($pdo, 'staff_contracts', $column)) {
            $pdo->exec($alter_sql);
        }
    }

    $ready = true;
}

function contract_datetime_add_days($value, $days) {
    try {
        $utc = new DateTimeZone('UTC');
        $kyiv = new DateTimeZone(ORION_CONTRACT_TIMEZONE);
        $time = new DateTimeImmutable(trim((string)$value) ?: 'now', $utc);
        return $time
            ->setTimezone($kyiv)
            ->modify((intval($days) >= 0 ? '+' : '') . intval($days) . ' days')
            ->setTimezone($utc)
            ->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        return contract_now();
    }
}

function contract_now() {
    return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
}

function contract_format_kyiv_datetime($value, $format = 'd.m.Y H:i') {
    $value = trim((string)$value);
    if ($value === '') {
        return '-';
    }
    try {
        $time = new DateTimeImmutable($value, new DateTimeZone('UTC'));
        return $time->setTimezone(new DateTimeZone(ORION_CONTRACT_TIMEZONE))->format((string)$format);
    } catch (Exception $e) {
        return $value;
    }
}

function contract_renewal_available_at($contract) {
    if (!empty($contract['renewal_available_at'])) {
        return (string)$contract['renewal_available_at'];
    }
    if (empty($contract['starts_at'])) {
        return null;
    }
    return contract_datetime_add_days(
        (string)$contract['starts_at'],
        ORION_CONTRACT_RENEWAL_DAY - 1
    );
}

function contract_can_request_renewal($contract, $now = null) {
    $now = $now ?: contract_now();
    $available_at = contract_renewal_available_at($contract);
    return intval($contract['id'] ?? 0) > 0
        && (string)($contract['status'] ?? '') === 'active'
        && contract_validate_role($contract['staff_role'] ?? '')
        && $available_at !== null
        && $available_at <= $now
        && !empty($contract['expires_at'])
        && (string)$contract['expires_at'] > $now;
}

function contract_cooldown_available_at($rejected_at = null, $terminated_at = null, $expired_at = null) {
    $action_times = array_values(array_filter([
        trim((string)$rejected_at),
        trim((string)$terminated_at),
        trim((string)$expired_at),
    ]));
    if (empty($action_times)) {
        return null;
    }
    rsort($action_times, SORT_STRING);
    return contract_datetime_add_days($action_times[0], ORION_CONTRACT_COOLDOWN_DAYS);
}

function contract_account_cooldown_until($pdo, $account_id) {
    $account_id = intval($account_id);
    if ($account_id <= 0) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT MAX(reviewed_at) FROM staff_contract_applications WHERE account_id = ? AND status = 'rejected' AND reviewed_by IS NOT NULL");
    $stmt->execute([$account_id]);
    $rejected_at = $stmt->fetchColumn() ?: null;

    $stmt = $pdo->prepare("SELECT MAX(terminated_at) FROM staff_contracts WHERE account_id = ? AND status = 'terminated' AND terminated_by IS NOT NULL AND termination_cause IN ('owner_decision','parent_terminated')");
    $stmt->execute([$account_id]);
    $terminated_at = $stmt->fetchColumn() ?: null;

    // Естественное завершение срока тоже закрывает повторную подачу на 7 дней.
    // Принятое продление исключает родительский контракт из этого правила.
    $stmt = $pdo->prepare("SELECT MAX(c.expires_at) FROM staff_contracts c WHERE c.account_id = ? AND c.status = 'expired' AND c.expires_at IS NOT NULL AND NOT EXISTS (SELECT 1 FROM staff_contracts child WHERE child.parent_contract_id = c.id AND child.status IN ('scheduled','active'))");
    $stmt->execute([$account_id]);
    $expired_at = $stmt->fetchColumn() ?: null;

    return contract_cooldown_available_at($rejected_at, $terminated_at, $expired_at);
}

function contract_cooldown_is_active($cooldown_until, $now = null) {
    $cooldown_until = trim((string)$cooldown_until);
    return $cooldown_until !== '' && $cooldown_until > ($now ?: contract_now());
}

function contract_validate_role($role) {
    return isset(contract_role_definitions()[(string)$role]);
}

function synchronize_contract_lifecycle($pdo) {
    try {
        $now = contract_now();
        $affected = [];

        $stmt = $pdo->prepare("SELECT DISTINCT account_id FROM staff_contracts WHERE status IN ('active','scheduled') AND expires_at IS NOT NULL AND expires_at <= ?");
        $stmt->execute([$now]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $account_id) {
            $affected[intval($account_id)] = true;
        }
        $pdo->prepare("UPDATE staff_contracts SET status = 'expired', updated_at = ? WHERE status IN ('active','scheduled') AND expires_at IS NOT NULL AND expires_at <= ?")->execute([$now, $now]);

        // Preserve activation of legacy scheduled records; the new workflow creates no scheduled state.
        $stmt = $pdo->prepare("SELECT DISTINCT account_id FROM staff_contracts WHERE status = 'scheduled' AND starts_at IS NOT NULL AND starts_at <= ? AND expires_at > ?");
        $stmt->execute([$now, $now]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $account_id) {
            $affected[intval($account_id)] = true;
        }
        $pdo->prepare("UPDATE staff_contracts SET status = 'active', updated_at = ? WHERE status = 'scheduled' AND starts_at IS NOT NULL AND starts_at <= ? AND expires_at > ?")->execute([$now, $now, $now]);

        $stmt = $pdo->prepare("SELECT DISTINCT account_id FROM staff_contracts WHERE status = 'active' AND starts_at <= ? AND expires_at > ?");
        $stmt->execute([$now, $now]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $account_id) {
            $affected[intval($account_id)] = true;
        }

        if (empty($affected)) {
            return;
        }

        $active = $pdo->prepare("SELECT staff_role FROM staff_contracts WHERE account_id = ? AND status = 'active' AND starts_at <= ? AND expires_at > ? ORDER BY starts_at DESC, id DESC LIMIT 1");
        $update_role = $pdo->prepare("UPDATE accounts SET staff_role = ?, is_admin = 0 WHERE id = ? AND is_admin = 0");
        $clear_overrides = $pdo->prepare("DELETE FROM staff_permission_overrides WHERE account_id = ?");
        foreach (array_keys($affected) as $account_id) {
            $active->execute([$account_id, $now, $now]);
            $role = $active->fetchColumn();
            $effective_role = $role !== false && contract_validate_role($role) ? $role : 'player';
            $update_role->execute([$effective_role, $account_id]);
            if ($effective_role !== 'player') {
                $clear_overrides->execute([$account_id]);
            }
        }
    } catch (Exception $e) {
        error_log('Contract lifecycle synchronization error: ' . $e->getMessage());
    }
}

function contract_has_open_application($pdo, $account_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff_contract_applications WHERE account_id = ? AND status = 'pending'");
    $stmt->execute([intval($account_id)]);
    return intval($stmt->fetchColumn()) > 0;
}

function contract_active_for_account($pdo, $account_id) {
    $now = contract_now();
    $stmt = $pdo->prepare("SELECT c.*, a.username FROM staff_contracts c LEFT JOIN accounts a ON a.id = c.account_id WHERE c.account_id = ? AND c.status = 'active' AND c.starts_at <= ? AND c.expires_at > ? ORDER BY c.starts_at DESC, c.id DESC LIMIT 1");
    $stmt->execute([intval($account_id), $now, $now]);
    return $stmt->fetch() ?: null;
}

function contract_account_is_protected($pdo, $account_id) {
    return contract_active_for_account($pdo, $account_id) !== null;
}

function contract_create_application($pdo, $account_id, $preferred_role, $motivation = '', $terms_accepted = false, $public_consent = false, $submitted_ip = '') {
    $account_id = intval($account_id);
    $preferred_role = (string)$preferred_role;
    $motivation = trim((string)$motivation);
    $motivation_length = function_exists('mb_strlen') ? mb_strlen($motivation, 'UTF-8') : strlen($motivation);

    if ($account_id <= 0 || !contract_validate_role($preferred_role)) {
        throw new RuntimeException('Выберите корректный тип контракта.');
    }
    if ($motivation_length > 2000) {
        throw new RuntimeException('Комментарий не должен превышать 2000 символов.');
    }
    if (!$terms_accepted || !$public_consent) {
        throw new RuntimeException('Подтвердите условия контракта и публикацию в реестре.');
    }

    $pdo->beginTransaction();
    try {
        $lock = $pdo->prepare("SELECT id, is_admin, staff_role FROM accounts WHERE id = ? FOR UPDATE");
        $lock->execute([$account_id]);
        $account = $lock->fetch();
        if (!$account) {
            throw new RuntimeException('Аккаунт не найден.');
        }
        if (intval($account['is_admin'] ?? 0) === 1 || (string)($account['staff_role'] ?? '') === 'admin') {
            throw new RuntimeException('Глава проекта не подаёт контракт самому себе.');
        }
        if (contract_has_open_application($pdo, $account_id)) {
            throw new RuntimeException('У вас уже есть контракт на рассмотрении.');
        }
        $now = contract_now();
        $cooldown_until = contract_account_cooldown_until($pdo, $account_id);
        if (contract_cooldown_is_active($cooldown_until, $now)) {
            throw new RuntimeException('После отказа, расторжения или завершения контракта действует пауза 7 дней. Повторная подача доступна с ' . contract_format_kyiv_datetime($cooldown_until) . ' по киевскому времени.');
        }
        if (contract_active_for_account($pdo, $account_id)) {
            throw new RuntimeException('У вас уже действует семидневный контракт.');
        }

        $stmt = $pdo->prepare("INSERT INTO staff_contract_applications (account_id, application_type, renewal_contract_id, preferred_role, motivation, terms_accepted_at, public_consent_at, submitted_ip, status, created_at, updated_at) VALUES (?, 'initial', NULL, ?, ?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->execute([
            $account_id,
            $preferred_role,
            $motivation,
            $now,
            $now,
            substr((string)$submitted_ip, 0, 45),
            $now,
            $now,
        ]);
        $application_id = intval($pdo->lastInsertId());
        $pdo->commit();
        return $application_id;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function contract_create_renewal_application($pdo, $account_id, $motivation = '', $terms_accepted = false, $public_consent = false, $submitted_ip = '') {
    $account_id = intval($account_id);
    $motivation = trim((string)$motivation);
    $motivation_length = function_exists('mb_strlen') ? mb_strlen($motivation, 'UTF-8') : strlen($motivation);
    if ($account_id <= 0) {
        throw new RuntimeException('Аккаунт не найден.');
    }
    if ($motivation_length > 2000) {
        throw new RuntimeException('Комментарий не должен превышать 2000 символов.');
    }
    if (!$terms_accepted || !$public_consent) {
        throw new RuntimeException('Подтвердите условия продления и публикацию в реестре.');
    }

    $pdo->beginTransaction();
    try {
        $lock = $pdo->prepare("SELECT id, is_admin, staff_role FROM accounts WHERE id = ? FOR UPDATE");
        $lock->execute([$account_id]);
        $account = $lock->fetch();
        if (!$account) {
            throw new RuntimeException('Аккаунт не найден.');
        }
        if (intval($account['is_admin'] ?? 0) === 1 || (string)($account['staff_role'] ?? '') === 'admin') {
            throw new RuntimeException('Глава проекта не подаёт контракт самому себе.');
        }
        if (contract_has_open_application($pdo, $account_id)) {
            throw new RuntimeException('У вас уже есть контракт на рассмотрении.');
        }

        $now = contract_now();
        $cooldown_until = contract_account_cooldown_until($pdo, $account_id);
        if (contract_cooldown_is_active($cooldown_until, $now)) {
            throw new RuntimeException('После отказа, расторжения или завершения контракта действует пауза 7 дней. Повторная подача доступна с ' . contract_format_kyiv_datetime($cooldown_until) . ' по киевскому времени.');
        }
        $stmt = $pdo->prepare("SELECT * FROM staff_contracts WHERE account_id = ? AND status = 'active' AND starts_at <= ? AND expires_at > ? ORDER BY starts_at DESC, id DESC LIMIT 1 FOR UPDATE");
        $stmt->execute([$account_id, $now, $now]);
        $contract = $stmt->fetch();
        if (!$contract) {
            throw new RuntimeException('Действующий контракт для продления не найден.');
        }

        $renewal_available_at = contract_renewal_available_at($contract);
        if (!contract_can_request_renewal($contract, $now)) {
            $available_label = $renewal_available_at
                ? contract_format_kyiv_datetime($renewal_available_at)
                : '-';
            throw new RuntimeException('Подать заявку на продление можно с пятого календарного дня: ' . $available_label . ' по киевскому времени.');
        }

        $child_stmt = $pdo->prepare("SELECT COUNT(*) FROM staff_contracts WHERE parent_contract_id = ? AND status IN ('scheduled','active')");
        $child_stmt->execute([intval($contract['id'])]);
        if (intval($child_stmt->fetchColumn()) > 0) {
            throw new RuntimeException('Продление этого контракта уже принято.');
        }

        $stmt = $pdo->prepare("INSERT INTO staff_contract_applications (account_id, application_type, renewal_contract_id, preferred_role, motivation, terms_accepted_at, public_consent_at, submitted_ip, status, created_at, updated_at) VALUES (?, 'renewal', ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->execute([
            $account_id,
            intval($contract['id']),
            (string)$contract['staff_role'],
            $motivation,
            $now,
            $now,
            substr((string)$submitted_ip, 0, 45),
            $now,
            $now,
        ]);
        $application_id = intval($pdo->lastInsertId());
        $pdo->prepare("UPDATE staff_contracts SET renewal_available_at = ?, renewal_decision = 'requested', renewal_decided_by = NULL, renewal_decided_at = NULL, renewal_note = '', updated_at = ? WHERE id = ?")->execute([
            $renewal_available_at,
            $now,
            intval($contract['id']),
        ]);

        $pdo->commit();
        return $application_id;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function contract_review_application($pdo, $application_id, $admin_id, $decision, $note = '') {
    $application_id = intval($application_id);
    $admin_id = intval($admin_id);
    $decision = (string)$decision;
    $decision_note = contract_limit_text($note, 500);
    if ($application_id <= 0 || $admin_id <= 0 || !in_array($decision, ['approve', 'reject'], true)) {
        throw new RuntimeException('Некорректное решение по контракту.');
    }

    $pdo->beginTransaction();
    try {
        $owner_stmt = $pdo->prepare("SELECT is_admin, staff_role FROM accounts WHERE id = ? FOR UPDATE");
        $owner_stmt->execute([$admin_id]);
        $owner = $owner_stmt->fetch();
        if (!$owner || (intval($owner['is_admin'] ?? 0) !== 1 && (string)($owner['staff_role'] ?? '') !== 'admin')) {
            throw new RuntimeException('Принять или отклонить контракт может только глава проекта.');
        }

        $stmt = $pdo->prepare("SELECT ap.*, a.username FROM staff_contract_applications ap LEFT JOIN accounts a ON a.id = ap.account_id WHERE ap.id = ? FOR UPDATE");
        $stmt->execute([$application_id]);
        $application = $stmt->fetch();
        if (!$application || $application['status'] !== 'pending') {
            throw new RuntimeException('Контракт уже обработан или не найден.');
        }

        $now = contract_now();
        $is_renewal = (string)($application['application_type'] ?? 'initial') === 'renewal'
            && intval($application['renewal_contract_id'] ?? 0) > 0;

        if ($decision === 'reject') {
            $stmt = $pdo->prepare("UPDATE staff_contract_applications SET status = 'rejected', reviewed_by = ?, decision_note = ?, reviewed_at = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$admin_id, $decision_note, $now, $now, $application_id]);
            if ($is_renewal) {
                $pdo->prepare("UPDATE staff_contracts SET renewal_decision = 'rejected', renewal_decided_by = ?, renewal_decided_at = ?, renewal_note = ?, updated_at = ? WHERE id = ?")->execute([
                    $admin_id,
                    $now,
                    $decision_note,
                    $now,
                    intval($application['renewal_contract_id']),
                ]);
            }
            $pdo->commit();
            return ['application' => $application, 'contract_id' => null];
        }

        $role = (string)$application['preferred_role'];
        if (!contract_validate_role($role)) {
            throw new RuntimeException('Выбранный тип контракта больше недоступен.');
        }
        if (empty($application['terms_accepted_at']) || empty($application['public_consent_at'])) {
            throw new RuntimeException('Кандидат должен повторно подать контракт и подтвердить актуальные условия.');
        }
        $parent_contract = null;
        $parent_contract_id = null;
        $starts_at = $now;
        $contract_status = 'active';
        if ($is_renewal) {
            $parent_stmt = $pdo->prepare("SELECT * FROM staff_contracts WHERE id = ? FOR UPDATE");
            $parent_stmt->execute([intval($application['renewal_contract_id'])]);
            $parent_contract = $parent_stmt->fetch();
            if (!$parent_contract || intval($parent_contract['account_id']) !== intval($application['account_id'])) {
                throw new RuntimeException('Исходный контракт для продления не найден.');
            }
            if ((string)$parent_contract['status'] === 'terminated') {
                throw new RuntimeException('Расторгнутый контракт нельзя продлить.');
            }
            if ((string)$parent_contract['staff_role'] !== $role) {
                throw new RuntimeException('Продление должно сохранять роль исходного контракта.');
            }
            if (empty($parent_contract['expires_at'])) {
                throw new RuntimeException('У исходного контракта не указан срок завершения.');
            }

            $existing_child = $pdo->prepare("SELECT COUNT(*) FROM staff_contracts WHERE parent_contract_id = ? AND status IN ('scheduled','active')");
            $existing_child->execute([intval($parent_contract['id'])]);
            if (intval($existing_child->fetchColumn()) > 0) {
                throw new RuntimeException('Продление этого контракта уже принято.');
            }

            $active_contract = contract_active_for_account($pdo, intval($application['account_id']));
            if ($active_contract && intval($active_contract['id']) !== intval($parent_contract['id'])) {
                throw new RuntimeException('У пользователя уже действует другой контракт.');
            }

            $parent_contract_id = intval($parent_contract['id']);
            $starts_at = (string)$parent_contract['expires_at'] > $now
                ? (string)$parent_contract['expires_at']
                : $now;
            $contract_status = $starts_at > $now ? 'scheduled' : 'active';
        } elseif (contract_active_for_account($pdo, intval($application['account_id']))) {
            throw new RuntimeException('У пользователя уже действует семидневный контракт.');
        }

        $expires_at = contract_datetime_add_days($starts_at, ORION_CONTRACT_TERM_DAYS);
        $renewal_available_at = contract_datetime_add_days($starts_at, ORION_CONTRACT_RENEWAL_DAY - 1);
        $public_id = bin2hex(random_bytes(12));
        $nonce = bin2hex(random_bytes(16));
        $username = trim((string)($application['username'] ?? '')) ?: ('Account #' . intval($application['account_id']));
        $signature_hash = hash('sha256', implode('|', [
            $public_id,
            $application_id,
            intval($application['account_id']),
            $role,
            $admin_id,
            $now,
            $starts_at,
            $expires_at,
            $parent_contract_id ?: 0,
            ORION_CONTRACT_TERMS_VERSION,
            $nonce,
        ]));
        $temporary_number = 'PENDING-' . $public_id;

        $stmt = $pdo->prepare("INSERT INTO staff_contracts (public_id, contract_number, application_id, account_id, staff_role, offered_roles_json, status, parent_contract_id, offered_by, offered_at, signer_name, signed_at, starts_at, expires_at, renewal_available_at, signature_hash, signature_nonce, signed_ip, terms_version, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $public_id,
            $temporary_number,
            $application_id,
            intval($application['account_id']),
            $role,
            json_encode([$role], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $contract_status,
            $parent_contract_id,
            $admin_id,
            $now,
            $username,
            $now,
            $starts_at,
            $expires_at,
            $renewal_available_at,
            $signature_hash,
            $nonce,
            substr((string)($application['submitted_ip'] ?? ''), 0, 45),
            ORION_CONTRACT_TERMS_VERSION,
            $now,
            $now,
        ]);
        $contract_id = intval($pdo->lastInsertId());
        $contract_number = 'ORI-' . contract_format_kyiv_datetime($now, 'Y') . '-' . str_pad((string)$contract_id, 6, '0', STR_PAD_LEFT);

        $pdo->prepare("UPDATE staff_contracts SET contract_number = ? WHERE id = ?")->execute([$contract_number, $contract_id]);
        $pdo->prepare("UPDATE staff_contract_applications SET status = 'approved', reviewed_by = ?, decision_note = ?, reviewed_at = ?, updated_at = ? WHERE id = ?")->execute([$admin_id, $decision_note, $now, $now, $application_id]);
        if ($is_renewal) {
            $pdo->prepare("UPDATE staff_contracts SET renewal_decision = 'approved', renewal_decided_by = ?, renewal_decided_at = ?, renewal_note = ?, updated_at = ? WHERE id = ?")->execute([
                $admin_id,
                $now,
                $decision_note,
                $now,
                $parent_contract_id,
            ]);
        }
        if ($contract_status === 'active') {
            $pdo->prepare("UPDATE accounts SET staff_role = ?, is_admin = 0 WHERE id = ? AND is_admin = 0")->execute([$role, intval($application['account_id'])]);
            $pdo->prepare("DELETE FROM staff_permission_overrides WHERE account_id = ?")->execute([intval($application['account_id'])]);
        }

        $pdo->commit();
        return [
            'application' => $application,
            'contract_id' => $contract_id,
            'contract_number' => $contract_number,
            'staff_role' => $role,
            'status' => $contract_status,
            'application_type' => $is_renewal ? 'renewal' : 'initial',
            'starts_at' => $starts_at,
            'expires_at' => $expires_at,
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function contract_terminate($pdo, $contract_id, $admin_id, $reason) {
    $contract_id = intval($contract_id);
    $admin_id = intval($admin_id);
    $termination_reason = contract_limit_text($reason, 500);
    $reason_length = function_exists('mb_strlen')
        ? mb_strlen($termination_reason, 'UTF-8')
        : strlen($termination_reason);
    if ($contract_id <= 0 || $admin_id <= 0) {
        throw new RuntimeException('Некорректный контракт для расторжения.');
    }
    if ($reason_length < 5) {
        throw new RuntimeException('Укажите причину расторжения: минимум 5 символов.');
    }

    $pdo->beginTransaction();
    try {
        $owner_stmt = $pdo->prepare("SELECT is_admin, staff_role FROM accounts WHERE id = ? FOR UPDATE");
        $owner_stmt->execute([$admin_id]);
        $owner = $owner_stmt->fetch();
        if (!$owner || (intval($owner['is_admin'] ?? 0) !== 1 && (string)($owner['staff_role'] ?? '') !== 'admin')) {
            throw new RuntimeException('Расторгнуть контракт может только глава проекта.');
        }

        $stmt = $pdo->prepare("SELECT c.*, a.username FROM staff_contracts c LEFT JOIN accounts a ON a.id = c.account_id WHERE c.id = ? FOR UPDATE");
        $stmt->execute([$contract_id]);
        $contract = $stmt->fetch();
        if (!$contract || !in_array((string)$contract['status'], ['active', 'scheduled'], true)) {
            throw new RuntimeException('Действующий контракт не найден или уже завершён.');
        }

        $now = contract_now();
        $pdo->prepare("UPDATE staff_contracts SET status = 'terminated', terminated_by = ?, terminated_at = ?, termination_cause = 'owner_decision', termination_reason = ?, updated_at = ? WHERE id = ?")->execute([
            $admin_id,
            $now,
            $termination_reason,
            $now,
            $contract_id,
        ]);
        $pdo->prepare("UPDATE staff_contracts SET status = 'terminated', terminated_by = ?, terminated_at = ?, termination_cause = 'parent_terminated', termination_reason = ?, updated_at = ? WHERE parent_contract_id = ? AND status = 'scheduled'")->execute([
            $admin_id,
            $now,
            $termination_reason,
            $now,
            $contract_id,
        ]);
        $pdo->prepare("UPDATE staff_contract_applications SET status = 'rejected', reviewed_by = ?, decision_note = ?, reviewed_at = ?, updated_at = ? WHERE application_type = 'renewal' AND renewal_contract_id = ? AND status = 'pending'")->execute([
            $admin_id,
            contract_limit_text('Исходный контракт расторгнут: ' . $termination_reason, 500),
            $now,
            $now,
            $contract_id,
        ]);

        $active_stmt = $pdo->prepare("SELECT staff_role FROM staff_contracts WHERE account_id = ? AND status = 'active' AND starts_at <= ? AND expires_at > ? ORDER BY starts_at DESC, id DESC LIMIT 1");
        $active_stmt->execute([intval($contract['account_id']), $now, $now]);
        $fallback_role = $active_stmt->fetchColumn();
        $effective_role = $fallback_role !== false && contract_validate_role($fallback_role)
            ? $fallback_role
            : 'player';
        $pdo->prepare("UPDATE accounts SET staff_role = ?, is_admin = 0 WHERE id = ? AND is_admin = 0")->execute([
            $effective_role,
            intval($contract['account_id']),
        ]);
        if ($effective_role === 'player') {
            $pdo->prepare("DELETE FROM staff_permission_overrides WHERE account_id = ?")->execute([intval($contract['account_id'])]);
        }

        $pdo->commit();
        return array_merge($contract, [
            'previous_status' => (string)$contract['status'],
            'status' => 'terminated',
            'terminated_by' => $admin_id,
            'terminated_at' => $now,
            'termination_cause' => 'owner_decision',
            'termination_reason' => $termination_reason,
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function contract_user_dashboard($pdo, $account_id) {
    $account_id = intval($account_id);
    $state = [
        'application' => null,
        'active' => null,
        'scheduled' => null,
        'history' => [],
        'can_renew' => false,
        'renewal_available_at' => null,
        'cooldown_until' => null,
        'cooldown_active' => false,
    ];
    $stmt = $pdo->prepare("SELECT * FROM staff_contract_applications WHERE account_id = ? ORDER BY created_at DESC, id DESC LIMIT 1");
    $stmt->execute([$account_id]);
    $state['application'] = $stmt->fetch() ?: null;

    $stmt = $pdo->prepare("SELECT c.*, a.username FROM staff_contracts c LEFT JOIN accounts a ON a.id = c.account_id WHERE c.account_id = ? ORDER BY c.created_at DESC, c.id DESC");
    $stmt->execute([$account_id]);
    foreach ($stmt->fetchAll() as $contract) {
        if ($contract['status'] === 'active' && $state['active'] === null) {
            $state['active'] = $contract;
        }
        if ($contract['status'] === 'scheduled' && $state['scheduled'] === null) {
            $state['scheduled'] = $contract;
        }
        if (!empty($contract['signed_at'])) {
            $state['history'][] = $contract;
        }
    }
    if ($state['active']) {
        $state['renewal_available_at'] = contract_renewal_available_at($state['active']);
        $state['can_renew'] = contract_can_request_renewal($state['active']);
    }
    $state['cooldown_until'] = contract_account_cooldown_until($pdo, $account_id);
    $state['cooldown_active'] = contract_cooldown_is_active($state['cooldown_until']);
    return $state;
}

function contract_public_list($pdo, $limit = 100) {
    $limit = max(1, min(500, intval($limit)));
    $stmt = $pdo->query("SELECT c.*, a.username FROM staff_contracts c LEFT JOIN accounts a ON a.id = c.account_id WHERE c.signed_at IS NOT NULL ORDER BY c.signed_at DESC, c.id DESC LIMIT $limit");
    return $stmt->fetchAll();
}

function contract_find_public($pdo, $public_id) {
    $public_id = strtolower(trim((string)$public_id));
    if (!preg_match('/^[a-f0-9]{24}$/', $public_id)) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT c.*, a.username, reviewer.username AS offered_by_name FROM staff_contracts c LEFT JOIN accounts a ON a.id = c.account_id LEFT JOIN accounts reviewer ON reviewer.id = c.offered_by WHERE c.public_id = ? AND c.signed_at IS NOT NULL LIMIT 1");
    $stmt->execute([$public_id]);
    return $stmt->fetch() ?: null;
}

function contract_admin_data($pdo) {
    $data = ['pending' => [], 'current' => [], 'stats' => ['pending' => 0, 'active' => 0, 'scheduled' => 0, 'accepted' => 0]];
    $data['pending'] = $pdo->query("SELECT ap.*, a.username, a.email, parent.contract_number AS renewal_contract_number FROM staff_contract_applications ap LEFT JOIN accounts a ON a.id = ap.account_id LEFT JOIN staff_contracts parent ON parent.id = ap.renewal_contract_id WHERE ap.status = 'pending' ORDER BY ap.created_at ASC, ap.id ASC LIMIT 100")->fetchAll();
    $data['current'] = $pdo->query("SELECT c.*, a.username FROM staff_contracts c LEFT JOIN accounts a ON a.id = c.account_id WHERE c.status IN ('active','scheduled') ORDER BY c.starts_at ASC, c.id ASC LIMIT 200")->fetchAll();
    $data['stats']['pending'] = count($data['pending']);
    $data['stats']['active'] = count(array_filter($data['current'], static function ($contract) {
        return (string)($contract['status'] ?? '') === 'active';
    }));
    $data['stats']['scheduled'] = count(array_filter($data['current'], static function ($contract) {
        return (string)($contract['status'] ?? '') === 'scheduled';
    }));
    $data['stats']['accepted'] = intval($pdo->query("SELECT COUNT(*) FROM staff_contracts WHERE signed_at IS NOT NULL")->fetchColumn());
    return $data;
}

function contract_status_label($status, $lang = 'ru') {
    $labels = [
        'ru' => ['awaiting_signature' => 'Архивное предложение', 'scheduled' => 'Запланирован', 'active' => 'Действует', 'expired' => 'Завершён', 'terminated' => 'Прекращён'],
        'uk' => ['awaiting_signature' => 'Архівна пропозиція', 'scheduled' => 'Запланований', 'active' => 'Діє', 'expired' => 'Завершений', 'terminated' => 'Припинений'],
        'en' => ['awaiting_signature' => 'Legacy offer', 'scheduled' => 'Scheduled', 'active' => 'Active', 'expired' => 'Expired', 'terminated' => 'Terminated'],
    ];
    $lang = isset($labels[$lang]) ? $lang : 'ru';
    return $labels[$lang][$status] ?? (string)$status;
}
