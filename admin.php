<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=admin');
    exit;
}

$current_staff_access = staff_access_for_account($pdo, intval($_SESSION['user_id']));
if (!staff_access_has($current_staff_access, 'dashboard.view')) {
    header('Location: login.php?error=admin');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function admin_current_locale() {
    $locale = function_exists('current_lang')
        ? current_lang()
        : ($_GET['lang'] ?? ($_SESSION['lang'] ?? 'ru'));
    return in_array($locale, ['ru', 'uk', 'en'], true) ? $locale : 'ru';
}

function admin_i18n_fallback($text) {
    static $map = [
        'У вашей роли нет права на это действие.' => 'Your role does not have permission to perform this action.',
        'Нельзя изменять свой аккаунт или сотрудника равного/высшего уровня.' => 'You cannot modify your own account or an employee of equal or higher rank.',
        'Этот IP связан с сотрудником равного/высшего уровня.' => 'This IP is associated with an employee of equal or higher rank.',
        'Сессия устарела. Обнови страницу.' => 'The session expired. Refresh the page.',
        'Сессия устарела. Обнови страницу и попробуй еще раз.' => 'The session expired. Refresh the page and try again.',
        'Не удалось создать папку uploads/news.' => 'Could not create the uploads/news folder.',
        'Файл больше лимита PHP upload_max_filesize.' => 'The file exceeds the PHP upload_max_filesize limit.',
        'Файл больше лимита формы.' => 'The file exceeds the form limit.',
        'Файл загрузился не полностью.' => 'The file was not uploaded completely.',
        'На сервере нет временной папки для загрузок.' => 'The server has no temporary upload folder.',
        'Сервер не смог записать файл.' => 'The server could not write the file.',
        'PHP-расширение остановило загрузку.' => 'A PHP extension stopped the upload.',
        'Не удалось загрузить файл.' => 'Could not upload the file.',
        'Загрузка отклонена: временный файл не найден.' => 'Upload rejected: temporary file not found.',
        'Разрешены только JPG, PNG, WEBP, GIF, MP4, WEBM и OGG.' => 'Only JPG, PNG, WEBP, GIF, MP4, WEBM, and OGG are allowed.',
        'Неизвестный танк.' => 'Unknown tank.',
        'Нет танков для этого действия.' => 'There are no tanks for this action.',
        'Неверные параметры.' => 'Invalid parameters.',
        'Неизвестный режим.' => 'Unknown mode.',
        'Сначала выбери игрока.' => 'Select a player first.',
        'Игрок не выбран.' => 'No player selected.',
        'Игрок не найден.' => 'Player not found.',
        'Пароль должен быть от 6 до 128 символов.' => 'Password must be 6 to 128 characters.',
        'Никнейм должен быть от 3 до 24 символов.' => 'Username must be 3 to 24 characters.',
        'Никнейм содержит недопустимые символы.' => 'Username contains invalid characters.',
        'Этот никнейм уже занят.' => 'This username is already taken.',
        'Нельзя забанить самого себя.' => 'You cannot ban yourself.',
        'Аккаунт защищён действующим семидневным контрактом. Сначала зафиксируйте деструктивные действия и расторгните контракт в соответствующей вкладке.' => 'This account is protected by an active seven-day contract. Record destructive actions and terminate the contract in the appropriate tab first.',
        'Никто не выбран.' => 'Nobody selected.',
        'Среди выбранных некого банить (только админы/вы сами).' => 'There is nobody to ban among the selected accounts (only administrators/yourself).',
        'Массовый бан отменён: среди выбранных есть участники с действующим контрактом. Их можно снять только за зафиксированные деструктивные действия.' => 'Bulk ban cancelled: some selected participants have an active contract. They can only be removed for documented destructive actions.',
        'Некорректный IP-адрес.' => 'Invalid IP address.',
        'Этот IP связан с участником, защищённым действующим контрактом.' => 'This IP is associated with a participant protected by an active contract.',
        'Некорректный MAC (формат AA:BB:CC:DD:EE:FF).' => 'Invalid MAC (format AA:BB:CC:DD:EE:FF).',
        'Не указан бан.' => 'No ban specified.',
        'Неизвестное действие.' => 'Unknown action.',
        'Произошла внутренняя ошибка.' => 'An internal error occurred.',
        'Укажите версию обновления длиной до 32 символов.' => 'Enter an update version up to 32 characters long.',
        'Название обновления должно содержать от 3 до 180 символов.' => 'The update name must be 3 to 180 characters long.',
        'Укажите корректную дату выпуска.' => 'Enter a valid release date.',
        'Тег или вступление превышают допустимую длину.' => 'The tag or introduction exceeds the allowed length.',
        'Запись обновления не найдена.' => 'Update entry not found.',
        'Статус сервера может менять только глава проекта.' => 'Only the project lead can change the server status.',
        'Очередью реализации может управлять только глава проекта.' => 'Only the project lead can manage the implementation queue.',
        'Статус реализации решения обновлён.' => 'Decision implementation status updated.',
        'Менять статус принятого решения может только глава проекта.' => 'Only the project lead can change the status of an accepted decision.',
        'Контракты может обрабатывать только глава проекта.' => 'Only the project lead can process contracts.',
        'Контракт отклонён.' => 'Contract rejected.',
        'Роль и разрешения защищены действующим семидневным контрактом. До конца срока их нельзя изменить вручную.' => 'The role and permissions are protected by an active seven-day contract. They cannot be changed manually until it ends.',
        'Нельзя изменять себя или сотрудника равного/высшего уровня.' => 'You cannot modify yourself or an employee of equal or higher rank.',
        'Эту роль нельзя назначить с вашего уровня доступа.' => 'This role cannot be assigned from your access level.',
        'Роль и персональные разрешения сохранены.' => 'Role and personal permissions saved.',
        'Закрыть все репорты может только глава проекта.' => 'Only the project lead can close all reports.',
        'Некорректные данные репорта.' => 'Invalid report data.',
        'Недостаточно прав для ограничения автора репорта.' => 'Insufficient permission to restrict the report author.',
        'Нельзя ограничить сотрудника равного/высшего уровня.' => 'You cannot restrict an employee of equal or higher rank.',
        'Баг-репорт не выбран.' => 'No bug report selected.',
        'Баг-репорт обновлён.' => 'Bug report updated.',
        'Баг-репорт удалён.' => 'Bug report deleted.',
        'Настройки загрузок сохранены.' => 'Download settings saved.',
        'У новости должен быть заголовок.' => 'The news item must have a title.',
        'Текст новости не может быть пустым.' => 'The news text cannot be empty.',
        'Медиа не найдено.' => 'Media not found.',
        'Медиа удалено.' => 'Media deleted.',
        'Новость удалена.' => 'News deleted.',
        'Выберите корректный тип контракта.' => 'Select a valid contract type.',
        'Комментарий не должен превышать 2000 символов.' => 'The comment must not exceed 2,000 characters.',
        'Подтвердите условия контракта и публикацию в реестре.' => 'Confirm the contract terms and publication in the registry.',
        'Аккаунт не найден.' => 'Account not found.',
        'Глава проекта не подаёт контракт самому себе.' => 'The project lead cannot submit a contract to themself.',
        'У вас уже есть контракт на рассмотрении.' => 'You already have a contract under review.',
        'У вас уже действует семидневный контракт.' => 'You already have an active seven-day contract.',
        'Подтвердите условия продления и публикацию в реестре.' => 'Confirm the renewal terms and publication in the registry.',
        'Действующий контракт для продления не найден.' => 'The active contract for renewal was not found.',
        'Продление этого контракта уже принято.' => 'Renewal of this contract has already been accepted.',
        'Некорректное решение по контракту.' => 'Invalid contract decision.',
        'Принять или отклонить контракт может только глава проекта.' => 'Only the project lead can accept or reject a contract.',
        'Контракт уже обработан или не найден.' => 'The contract has already been processed or was not found.',
        'Выбранный тип контракта больше недоступен.' => 'The selected contract type is no longer available.',
        'Кандидат должен повторно подать контракт и подтвердить актуальные условия.' => 'The candidate must submit the contract again and confirm the current terms.',
        'Исходный контракт для продления не найден.' => 'The original contract for renewal was not found.',
        'Расторгнутый контракт нельзя продлить.' => 'A terminated contract cannot be renewed.',
        'Продление должно сохранять роль исходного контракта.' => 'The renewal must keep the original contract role.',
        'У исходного контракта не указан срок завершения.' => 'The original contract has no expiration time.',
        'У пользователя уже действует другой контракт.' => 'The user already has another active contract.',
        'У пользователя уже действует семидневный контракт.' => 'The user already has an active seven-day contract.',
        'Некорректный контракт для расторжения.' => 'Invalid contract for termination.',
        'Укажите причину расторжения: минимум 5 символов.' => 'Enter a termination reason of at least 5 characters.',
        'Расторгнуть контракт может только глава проекта.' => 'Only the project lead can terminate a contract.',
        'Действующий контракт не найден или уже завершён.' => 'The active contract was not found or has already ended.',
        'Только члены команды могут подавать предложения.' => 'Only team members can submit proposals.',
        'Название предложения должно содержать от 8 до 180 символов.' => 'The proposal title must be 8 to 180 characters long.',
        'Описание предложения должно содержать от 30 до 10 000 символов.' => 'The proposal description must be 30 to 10,000 characters long.',
        'Опишите ожидаемый результат минимум в 10 символах.' => 'Describe the expected result in at least 10 characters.',
        'Досрочно завершить голосование может только глава проекта или глава совета.' => 'Only the project lead or council head can end voting early.',
        'Нельзя завершить голосование до достижения кворума.' => 'Voting cannot end before quorum is reached.',
        'Обычные игроки не могут голосовать.' => 'Regular players cannot vote.',
        'Выберите допустимый вариант голосования.' => 'Select a valid voting option.',
        'Голосование уже завершено.' => 'Voting has already ended.',
        'Выберите решение главы совета.' => 'Select the council head decision.',
        'Это решение может принять только глава проекта или глава совета.' => 'This decision can only be made by the project lead or council head.',
        'Добавьте краткое обоснование решения.' => 'Add a brief justification for the decision.',
        'Предложение ещё не передано главе совета или уже обработано.' => 'The proposal has not been passed to the council head or has already been processed.',
        'Реализацией решений может управлять только глава проекта.' => 'Only the project lead can manage decision implementation.',
        'Неизвестное действие реализации.' => 'Unknown implementation action.',
        'Опишите, как решение было реализовано.' => 'Describe how the decision was implemented.',
        'Это решение не находится в очереди реализации.' => 'This decision is not in the implementation queue.',
        'Неизвестный статус решения.' => 'Unknown decision status.',
        'Опишите причину смены статуса решения минимум в 5 символах.' => 'Describe why the decision status is changing in at least 5 characters.',
        'Решение ГСО не найдено.' => 'GSO decision not found.',
        'Решение уже находится в этом статусе.' => 'The decision is already in this status.',
    ];
    if (isset($map[$text])) {
        return $map[$text];
    }
    foreach ([
        'Новость сохранена, медиа загружено: ' => 'News saved, media uploaded: ',
        'Новость сохранена, но медиа не загрузилось: ' => 'News saved, but media failed to upload: ',
    ] as $prefix => $translation) {
        if (strncmp($text, $prefix, strlen($prefix)) === 0) {
            return $translation . substr($text, strlen($prefix));
        }
    }
    if (preg_match('/^Файл "(.+)" больше лимита (8 МБ|128 МБ)\.$/u', $text, $matches)) {
        $limit = $matches[2] === '8 МБ' ? '8 MB' : '128 MB';
        return 'File "' . $matches[1] . '" exceeds the ' . $limit . ' limit.';
    }
    if (preg_match('/^Не удалось сохранить файл "(.+)"\.$/u', $text, $matches)) {
        return 'Could not save file "' . $matches[1] . '".';
    }
    return null;
}

function admin_i18n_uk_fallback($text) {
    static $map = [
        'У вашей роли нет права на это действие.' => 'У вашої ролі немає права на цю дію.',
        'Нельзя изменять свой аккаунт или сотрудника равного/высшего уровня.' => 'Не можна змінювати власний обліковий запис або працівника рівного/вищого рівня.',
        'Этот IP связан с сотрудником равного/высшего уровня.' => 'Ця IP-адреса пов’язана з працівником рівного/вищого рівня.',
        'Сессия устарела. Обнови страницу.' => 'Сесія застаріла. Онови сторінку.',
        'Неизвестный танк.' => 'Невідомий танк.',
        'Нет танков для этого действия.' => 'Немає танків для цієї дії.',
        'Неверные параметры.' => 'Неправильні параметри.',
        'Неизвестный режим.' => 'Невідомий режим.',
        'Сначала выбери игрока.' => 'Спочатку вибери гравця.',
        'Игрок не выбран.' => 'Гравця не обрано.',
        'Игрок не найден.' => 'Гравця не знайдено.',
        'Пароль должен быть от 6 до 128 символов.' => 'Пароль має містити від 6 до 128 символів.',
        'Никнейм должен быть от 3 до 24 символов.' => 'Нікнейм має містити від 3 до 24 символів.',
        'Никнейм содержит недопустимые символы.' => 'Нікнейм містить недопустимі символи.',
        'Этот никнейм уже занят.' => 'Цей нікнейм уже зайнятий.',
        'Нельзя забанить самого себя.' => 'Не можна заблокувати самого себе.',
        'Аккаунт защищён действующим семидневным контрактом. Сначала зафиксируйте деструктивные действия и расторгните контракт в соответствующей вкладке.' => 'Обліковий запис захищено чинним семиденним контрактом. Спочатку зафіксуйте деструктивні дії та розірвіть контракт у відповідній вкладці.',
        'Никто не выбран.' => 'Нікого не вибрано.',
        'Среди выбранных некого банить (только админы/вы сами).' => 'Серед обраних немає кого блокувати (лише адміністратори/ви самі).',
        'Массовый бан отменён: среди выбранных есть участники с действующим контрактом. Их можно снять только за зафиксированные деструктивные действия.' => 'Масове блокування скасовано: серед обраних є учасники з чинним контрактом. Їх можна усунути лише за зафіксовані деструктивні дії.',
        'Некорректный IP-адрес.' => 'Некоректна IP-адреса.',
        'Этот IP связан с участником, защищённым действующим контрактом.' => 'Ця IP-адреса пов’язана з учасником, захищеним чинним контрактом.',
        'Некорректный MAC (формат AA:BB:CC:DD:EE:FF).' => 'Некоректна MAC-адреса (формат AA:BB:CC:DD:EE:FF).',
        'Не указан бан.' => 'Бан не вказано.',
        'Неизвестное действие.' => 'Невідома дія.',
        'Произошла внутренняя ошибка.' => 'Сталася внутрішня помилка.',
    ];
    return $map[$text] ?? null;
}

function admin_i18n_text($text, $english = null) {
    $text = (string)$text;
    $locale = admin_current_locale();
    if ($locale === 'ru') {
        return $text;
    }
    if ($locale === 'uk') {
        $ukrainian = admin_i18n_uk_fallback($text);
        if ($ukrainian !== null) {
            return $ukrainian;
        }
        if (function_exists('i18n_translate_text')) {
            $translated = (string)i18n_translate_text($text, 'uk');
            if ($translated !== $text) {
                return $translated;
            }
        }
        return $text;
    }
    $english = $english === null ? admin_i18n_fallback($text) : (string)$english;
    if (function_exists('i18n_translate_text')) {
        $translated = (string)i18n_translate_text($text, 'en');
        if ($translated !== $text) {
            return $translated;
        }
    }
    return $english === null ? $text : (string)$english;
}

function admin_js_text($text, $english = null) {
    return json_encode(
        admin_i18n_text($text, $english),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
}

function json_out($payload) {
    if (is_array($payload)) {
        foreach (['error', 'message'] as $message_key) {
            if (isset($payload[$message_key]) && is_string($payload[$message_key])) {
                $payload[$message_key] = admin_i18n_text($payload[$message_key]);
            }
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function admin_can($permission) {
    global $current_staff_access;
    return staff_access_has($current_staff_access, $permission);
}

function require_ajax_permission($permission) {
    if (!admin_can($permission)) {
        http_response_code(403);
        json_out(['success' => false, 'error' => 'У вашей роли нет права на это действие.']);
    }
}

function require_form_permission($permission, $tab) {
    if (admin_can($permission)) {
        return;
    }
    set_admin_flash('danger', 'У вашей роли нет права на это действие.');
    header('Location: admin.php?tab=' . urlencode($tab));
    exit;
}

function require_account_below_actor($pdo, $account_id) {
    global $current_staff_access;
    $target_access = staff_access_for_account($pdo, intval($account_id));
    if (!$target_access || !staff_can_act_on_account($current_staff_access, $target_access)) {
        http_response_code(403);
        json_out(['success' => false, 'error' => 'Нельзя изменять свой аккаунт или сотрудника равного/высшего уровня.']);
    }
    return $target_access;
}

function require_ip_below_actor($pdo, $ip) {
    global $current_staff_access;
    $stmt = $pdo->prepare("SELECT id FROM accounts WHERE last_ip = ? OR reg_ip = ?");
    $stmt->execute([(string)$ip, (string)$ip]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $account_id) {
        $target_access = staff_access_for_account($pdo, intval($account_id));
        if ($target_access && intval($target_access['rank']) >= intval($current_staff_access['rank'])) {
            http_response_code(403);
            json_out(['success' => false, 'error' => 'Этот IP связан с сотрудником равного/высшего уровня.']);
        }
    }
}

function require_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        json_out(['success' => false, 'error' => 'Сессия устарела. Обнови страницу.']);
    }
}

function vehicle_catalog_path() {
    $paths = [
        __DIR__ . DIRECTORY_SEPARATOR . '_vehicles.json',
    ];
    foreach ($paths as $path) {
        if (is_file($path)) {
            return $path;
        }
    }
    return $paths[0];
}

function vehicle_level($vehicle) {
    if (isset($vehicle['level'])) {
        return max(1, min(10, intval($vehicle['level'])));
    }
    foreach (($vehicle['tags'] ?? []) as $tag) {
        if (preg_match('/^tier(\d+)$/i', (string)$tag, $matches)) {
            return max(1, min(10, intval($matches[1])));
        }
    }
    return 1;
}

function load_vehicle_catalog() {
    $path = vehicle_catalog_path();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data) || !isset($data['vehicles']) || !is_array($data['vehicles'])) {
        return [];
    }
    $vehicles = [];
    foreach ($data['vehicles'] as $index => $vehicle) {
        if (!is_array($vehicle) || empty($vehicle['name'])) {
            continue;
        }
        $vehicle['inv_id'] = $index + 1;
        $vehicle['level_calculated'] = vehicle_level($vehicle);
        $vehicles[] = $vehicle;
    }
    usort($vehicles, function($a, $b) {
        return [$a['nation'] ?? '', $a['level_calculated'] ?? 1, $a['name'] ?? ''] <=> [$b['nation'] ?? '', $b['level_calculated'] ?? 1, $b['name'] ?? ''];
    });
    return $vehicles;
}

function normalize_vehicle_names($vehicle_name_set) {
    $raw = $_POST['vehicle_names'] ?? [];
    if (!is_array($raw)) {
        $raw = [$raw];
    }
    $names = [];
    foreach ($raw as $name) {
        $name = trim((string)$name);
        if ($name !== '' && isset($vehicle_name_set[$name])) {
            $names[$name] = true;
        }
    }
    return array_keys($names);
}

function insert_access_event($pdo, $scope, $account_id, $vehicle_name, $is_enabled) {
    $stmt = $pdo->prepare("INSERT INTO vehicle_access_events (scope, account_id, vehicle_name, is_enabled, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$scope, $account_id ?: null, $vehicle_name, $is_enabled ? 1 : 0, date('Y-m-d H:i:s')]);
}

function apply_global_vehicle($pdo, $tank_name, $status) {
    $now = date('Y-m-d H:i:s');
    if ($status) {
        $stmt = $pdo->prepare("DELETE FROM disabled_vehicles WHERE vehicle_name = ?");
        $stmt->execute([$tank_name]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO disabled_vehicles (vehicle_name, updated_at) VALUES (?, ?) ON CONFLICT (vehicle_name) DO UPDATE SET updated_at = EXCLUDED.updated_at");
        $stmt->execute([$tank_name, $now]);
    }
}

function apply_account_vehicle($pdo, $account_id, $tank_name, $mode) {
    $now = date('Y-m-d H:i:s');
    if ($mode === 'inherit') {
        $stmt = $pdo->prepare("DELETE FROM account_vehicle_overrides WHERE account_id = ? AND vehicle_name = ?");
        $stmt->execute([$account_id, $tank_name]);
        return null;
    }
    $enabled = $mode === 'enabled';
    $stmt = $pdo->prepare("INSERT INTO account_vehicle_overrides (account_id, vehicle_name, is_enabled, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT (account_id, vehicle_name) DO UPDATE SET is_enabled = EXCLUDED.is_enabled, updated_at = EXCLUDED.updated_at");
    $stmt->execute([$account_id, $tank_name, $enabled ? 1 : 0, $now]);
    return $enabled;
}

function global_vehicle_enabled($pdo, $vehicle_name) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM disabled_vehicles WHERE vehicle_name = ?");
    $stmt->execute([$vehicle_name]);
    return intval($stmt->fetchColumn()) === 0;
}

function account_exists($pdo, $account_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM accounts WHERE id = ?");
    $stmt->execute([$account_id]);
    return intval($stmt->fetchColumn()) > 0;
}

function ban_admin_id() {
    $id = intval($_SESSION['user_id'] ?? 0);
    return $id > 0 ? $id : null;
}

// Добавить/обновить правило бана. value — account_id, IP или MAC в зависимости
// от типа. Та же таблица читается игровым сервером.
function add_ban($pdo, $ban_type, $value, $reason) {
    $now = date('Y-m-d H:i:s');
    $admin = ban_admin_id();
    $reason = limit_text($reason, 255);
    if ($ban_type === 'account') {
        $stmt = $pdo->prepare("INSERT INTO bans (ban_type, account_id, reason, created_by, created_at) VALUES ('account', ?, ?, ?, ?) ON CONFLICT ON CONSTRAINT uniq_ban_account DO UPDATE SET reason = EXCLUDED.reason, created_by = EXCLUDED.created_by, created_at = EXCLUDED.created_at");
        $stmt->execute([intval($value), $reason, $admin, $now]);
    } elseif ($ban_type === 'ip') {
        $stmt = $pdo->prepare("INSERT INTO bans (ban_type, ip, reason, created_by, created_at) VALUES ('ip', ?, ?, ?, ?) ON CONFLICT ON CONSTRAINT uniq_ban_ip DO UPDATE SET reason = EXCLUDED.reason, created_by = EXCLUDED.created_by, created_at = EXCLUDED.created_at");
        $stmt->execute([(string)$value, $reason, $admin, $now]);
    } elseif ($ban_type === 'mac') {
        $stmt = $pdo->prepare("INSERT INTO bans (ban_type, mac, reason, created_by, created_at) VALUES ('mac', ?, ?, ?, ?) ON CONFLICT ON CONSTRAINT uniq_ban_mac DO UPDATE SET reason = EXCLUDED.reason, created_by = EXCLUDED.created_by, created_at = EXCLUDED.created_at");
        $stmt->execute([(string)$value, $reason, $admin, $now]);
    }
}

function account_known_ip($pdo, $account_id) {
    $stmt = $pdo->prepare("SELECT last_ip, reg_ip FROM accounts WHERE id = ?");
    $stmt->execute([intval($account_id)]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $ip = trim((string)($row['last_ip'] ?? ''));
    if ($ip === '') {
        $ip = trim((string)($row['reg_ip'] ?? ''));
    }
    return $ip !== '' ? $ip : null;
}

function nation_label($nation) {
    $labels = [
        'ussr' => 'СССР',
        'germany' => 'Германия',
        'usa' => 'США',
        'china' => 'Китай',
        'france' => 'Франция',
        'uk' => 'Британия',
        'japan' => 'Япония',
        'czech' => 'Чехия',
        'sweden' => 'Швеция',
        'poland' => 'Польша',
        'italy' => 'Италия',
    ];
    if (admin_current_locale() === 'en') {
        $english = [
            'ussr' => 'USSR',
            'germany' => 'Germany',
            'usa' => 'USA',
            'china' => 'China',
            'france' => 'France',
            'uk' => 'Britain',
            'japan' => 'Japan',
            'czech' => 'Czechia',
            'sweden' => 'Sweden',
            'poland' => 'Poland',
            'italy' => 'Italy',
        ];
        return $english[$nation] ?? $nation;
    }
    return $labels[$nation] ?? $nation;
}

function class_label($class) {
    $labels = [
        'lightTank' => 'Легкий',
        'mediumTank' => 'Средний',
        'heavyTank' => 'Тяжелый',
        'AT-SPG' => 'ПТ-САУ',
        'SPG' => 'САУ',
    ];
    if (admin_current_locale() === 'en') {
        $english = [
            'lightTank' => 'Light tank',
            'mediumTank' => 'Medium tank',
            'heavyTank' => 'Heavy tank',
            'AT-SPG' => 'Tank destroyer',
            'SPG' => 'SPG',
        ];
        return $english[$class] ?? $class;
    }
    return $labels[$class] ?? $class;
}

function admin_gso_status_label($status) {
    $english = [
        'voting' => 'Voting in progress',
        'council_review' => 'Council head decision',
        'implementation' => 'Awaiting implementation',
        'implemented' => 'Implemented',
        'rejected_vote' => 'Rejected by vote',
        'rejected_head' => 'Rejected by council head',
    ];
    return admin_i18n_text(gso_status_label($status, 'ru'), $english[$status] ?? null);
}

function admin_report_status_label($status) {
    $labels = [
        'open' => ['ru' => 'Открыт', 'en' => 'Open'],
        'in_progress' => ['ru' => 'В работе', 'en' => 'In progress'],
        'resolved' => ['ru' => 'Исправлен', 'en' => 'Resolved'],
        'closed' => ['ru' => 'Закрыт', 'en' => 'Closed'],
    ];
    $status = (string)$status;
    if (!isset($labels[$status])) {
        return $status;
    }
    return admin_i18n_text($labels[$status]['ru'], $labels[$status]['en']);
}

function admin_implementation_status_label($status) {
    $labels = [
        'pending' => ['ru' => 'Ожидает решения', 'en' => 'Awaiting decision'],
        'in_progress' => ['ru' => 'В работе', 'en' => 'In progress'],
        'deferred' => ['ru' => 'Отложено', 'en' => 'Deferred'],
    ];
    $status = (string)$status;
    if (!isset($labels[$status])) {
        return $status;
    }
    return admin_i18n_text($labels[$status]['ru'], $labels[$status]['en']);
}

function set_admin_flash($type, $message, $english = null) {
    $_SESSION['admin_flash'] = [
        'type' => $type === 'danger' ? 'danger' : 'success',
        'message' => admin_i18n_text($message, $english),
    ];
}

function take_admin_flash() {
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return is_array($flash) ? $flash : null;
}

function redirect_admin_news($extra = []) {
    $params = array_merge(['tab' => 'news'], $extra);
    header('Location: admin.php?' . http_build_query($params));
    exit;
}

function require_form_csrf($tab = 'news') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        set_admin_flash('danger', 'Сессия устарела. Обнови страницу и попробуй еще раз.');
        header('Location: admin.php?tab=' . urlencode($tab));
        exit;
    }
}

function limit_text($value, $limit) {
    $value = trim((string)$value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $limit, 'UTF-8');
    }
    return substr($value, 0, $limit);
}

function news_upload_dir() {
    return __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news';
}

function ensure_news_upload_dir() {
    $dir = news_upload_dir();
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        throw new RuntimeException('Не удалось создать папку uploads/news.');
    }
    @chmod($dir, 0775);
    return $dir;
}

function normalize_uploaded_files($field) {
    if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
        return [];
    }
    $files = [];
    foreach ($_FILES[$field]['name'] as $idx => $name) {
        $files[] = [
            'name' => $name,
            'type' => $_FILES[$field]['type'][$idx] ?? '',
            'tmp_name' => $_FILES[$field]['tmp_name'][$idx] ?? '',
            'error' => intval($_FILES[$field]['error'][$idx] ?? UPLOAD_ERR_NO_FILE),
            'size' => intval($_FILES[$field]['size'][$idx] ?? 0),
        ];
    }
    return $files;
}

function upload_error_message($code) {
    $messages = [
        UPLOAD_ERR_INI_SIZE => 'Файл больше лимита PHP upload_max_filesize.',
        UPLOAD_ERR_FORM_SIZE => 'Файл больше лимита формы.',
        UPLOAD_ERR_PARTIAL => 'Файл загрузился не полностью.',
        UPLOAD_ERR_NO_TMP_DIR => 'На сервере нет временной папки для загрузок.',
        UPLOAD_ERR_CANT_WRITE => 'Сервер не смог записать файл.',
        UPLOAD_ERR_EXTENSION => 'PHP-расширение остановило загрузку.',
    ];
    return $messages[$code] ?? 'Не удалось загрузить файл.';
}

function allowed_news_media() {
    return [
        'image/jpeg' => ['type' => 'image', 'ext' => 'jpg', 'max' => 8 * 1024 * 1024],
        'image/png' => ['type' => 'image', 'ext' => 'png', 'max' => 8 * 1024 * 1024],
        'image/webp' => ['type' => 'image', 'ext' => 'webp', 'max' => 8 * 1024 * 1024],
        'image/gif' => ['type' => 'image', 'ext' => 'gif', 'max' => 8 * 1024 * 1024],
        'video/mp4' => ['type' => 'video', 'ext' => 'mp4', 'max' => 128 * 1024 * 1024],
        'video/webm' => ['type' => 'video', 'ext' => 'webm', 'max' => 128 * 1024 * 1024],
        'video/ogg' => ['type' => 'video', 'ext' => 'ogv', 'max' => 128 * 1024 * 1024],
    ];
}

function detect_file_mime($tmp_name) {
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            if ($mime) {
                return $mime;
            }
        }
    }
    return function_exists('mime_content_type') ? mime_content_type($tmp_name) : '';
}

function attach_news_uploads($pdo, $news_id) {
    $files = normalize_uploaded_files('media_files');
    if (empty($files)) {
        return 0;
    }
    $dir = ensure_news_upload_dir();
    $allowed = allowed_news_media();
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM site_news_media WHERE news_id = ?");
    $stmt->execute([$news_id]);
    $sort = intval($stmt->fetchColumn()) + 1;
    $insert = $pdo->prepare("INSERT INTO site_news_media (news_id, media_type, file_path, original_name, mime_type, size_bytes, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $saved = 0;

    foreach ($files as $file) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(upload_error_message($file['error']));
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Загрузка отклонена: временный файл не найден.');
        }
        $mime = detect_file_mime($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Разрешены только JPG, PNG, WEBP, GIF, MP4, WEBM и OGG.');
        }
        $meta = $allowed[$mime];
        if ($file['size'] > $meta['max']) {
            $limit = $meta['type'] === 'image' ? '8 МБ' : '128 МБ';
            throw new RuntimeException('Файл "' . $file['name'] . '" больше лимита ' . $limit . '.');
        }
        $filename = date('Ymd_His') . '_' . intval($news_id) . '_' . bin2hex(random_bytes(8)) . '.' . $meta['ext'];
        $target = $dir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException('Не удалось сохранить файл "' . $file['name'] . '".');
        }
        $relative = 'uploads/news/' . $filename;
        $insert->execute([
            $news_id,
            $meta['type'],
            $relative,
            limit_text($file['name'], 255),
            $mime,
            $file['size'],
            $sort++,
            date('Y-m-d H:i:s'),
        ]);
        $saved++;
    }
    return $saved;
}

function news_media_absolute_path($file_path) {
    $file_path = str_replace('\\', '/', (string)$file_path);
    if (strpos($file_path, '..') !== false || substr($file_path, 0, 13) !== 'uploads/news/') {
        return null;
    }
    return __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file_path);
}

function delete_news_media_file($file_path) {
    $path = news_media_absolute_path($file_path);
    if ($path && is_file($path)) {
        @unlink($path);
    }
}

function parse_news_datetime($value) {
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $time = strtotime(str_replace('T', ' ', $value));
    return $time ? date('Y-m-d H:i:s', $time) : null;
}

function news_datetime_input($value) {
    if (!$value) {
        return '';
    }
    $time = strtotime((string)$value);
    return $time ? date('Y-m-d\TH:i', $time) : '';
}

function format_bytes($bytes) {
    $bytes = intval($bytes);
    if ($bytes >= 1024 * 1024) {
        return round($bytes / 1024 / 1024, 1) . (admin_current_locale() === 'en' ? ' MB' : ' МБ');
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 1) . (admin_current_locale() === 'en' ? ' KB' : ' КБ');
    }
    return $bytes . (admin_current_locale() === 'en' ? ' B' : ' Б');
}

$vehicles = load_vehicle_catalog();
$vehicle_name_set = [];
$nations = [];
$classes = [];
foreach ($vehicles as $vehicle) {
    $vehicle_name_set[$vehicle['name']] = true;
    if (!empty($vehicle['nation'])) {
        $nations[$vehicle['nation']] = true;
    }
    if (!empty($vehicle['vehicleClass'])) {
        $classes[$vehicle['vehicleClass']] = true;
    }
}
ksort($nations);
ksort($classes);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['ajax'])) {
    require_csrf();
    $action = $_POST['action'] ?? '';
    $ajax_permission_map = [
        'set_global_vehicle' => 'vehicles.manage',
        'bulk_global_vehicles' => 'vehicles.manage',
        'set_account_vehicle' => 'vehicles.manage',
        'bulk_account_vehicles' => 'vehicles.manage',
        'reset_account_overrides' => 'vehicles.manage',
        'enable_all_global' => 'vehicles.manage',
        'save_account' => 'users.edit',
        'set_password' => 'users.credentials',
        'set_username' => 'users.edit',
        'ban_account' => 'bans.manage',
        'bulk_ban_accounts' => 'bans.manage',
        'ban_ip' => 'bans.manage',
        'ban_mac' => 'bans.manage',
        'unban' => 'bans.unban',
    ];
    if (isset($ajax_permission_map[$action])) {
        require_ajax_permission($ajax_permission_map[$action]);
    }
    try {
        if ($action === 'set_global_vehicle') {
            $tank_name = trim($_POST['tank_name'] ?? '');
            $status = intval($_POST['status'] ?? 0) === 1;
            if ($tank_name === '' || !isset($vehicle_name_set[$tank_name])) {
                json_out(['success' => false, 'error' => 'Неизвестный танк.']);
            }
            apply_global_vehicle($pdo, $tank_name, $status);
            insert_access_event($pdo, 'global', null, $tank_name, $status);
            log_staff_action($pdo, 'vehicle.global', 'vehicle', $tank_name, ($status ? 'Включена' : 'Отключена') . ' техника ' . $tank_name);
            json_out(['success' => true, 'global_enabled' => $status]);
        }

        if ($action === 'bulk_global_vehicles') {
            $status = intval($_POST['status'] ?? 0) === 1;
            $names = normalize_vehicle_names($vehicle_name_set);
            if (empty($names)) {
                json_out(['success' => false, 'error' => 'Нет танков для этого действия.']);
            }
            $pdo->beginTransaction();
            foreach ($names as $name) {
                apply_global_vehicle($pdo, $name, $status);
            }
            insert_access_event($pdo, 'global', null, '*', $status);
            $pdo->commit();
            log_staff_action($pdo, 'vehicle.global', 'vehicle', '*', ($status ? 'Включено' : 'Отключено') . ' единиц техники: ' . count($names), ['count' => count($names)]);
            json_out(['success' => true, 'count' => count($names), 'global_enabled' => $status]);
        }

        if ($action === 'set_account_vehicle') {
            $account_id = intval($_POST['account_id'] ?? 0);
            $tank_name = trim($_POST['tank_name'] ?? '');
            $mode = $_POST['mode'] ?? 'inherit';
            if ($account_id <= 0 || !account_exists($pdo, $account_id) || $tank_name === '' || !isset($vehicle_name_set[$tank_name])) {
                json_out(['success' => false, 'error' => 'Неверные параметры.']);
            }
            require_account_below_actor($pdo, $account_id);
            if (!in_array($mode, ['inherit', 'enabled', 'disabled'], true)) {
                json_out(['success' => false, 'error' => 'Неизвестный режим.']);
            }
            $override = apply_account_vehicle($pdo, $account_id, $tank_name, $mode);
            $effective = $override === null ? global_vehicle_enabled($pdo, $tank_name) : $override;
            insert_access_event($pdo, 'account', $account_id, $tank_name, $effective);
            log_staff_action($pdo, 'vehicle.account', 'account', $account_id, 'Изменён доступ к ' . $tank_name, ['mode' => $mode]);
            json_out(['success' => true, 'mode' => $mode, 'effective_enabled' => $effective]);
        }

        if ($action === 'bulk_account_vehicles') {
            $account_id = intval($_POST['account_id'] ?? 0);
            $mode = $_POST['mode'] ?? 'inherit';
            $names = normalize_vehicle_names($vehicle_name_set);
            if ($account_id <= 0 || !account_exists($pdo, $account_id)) {
                json_out(['success' => false, 'error' => 'Сначала выбери игрока.']);
            }
            require_account_below_actor($pdo, $account_id);
            if (!in_array($mode, ['inherit', 'enabled', 'disabled'], true)) {
                json_out(['success' => false, 'error' => 'Неизвестный режим.']);
            }
            if (empty($names)) {
                json_out(['success' => false, 'error' => 'Нет танков для этого действия.']);
            }
            $pdo->beginTransaction();
            foreach ($names as $name) {
                apply_account_vehicle($pdo, $account_id, $name, $mode);
            }
            insert_access_event($pdo, 'account', $account_id, '*', $mode !== 'disabled');
            $pdo->commit();
            log_staff_action($pdo, 'vehicle.account', 'account', $account_id, 'Массовое правило техники: ' . $mode, ['count' => count($names)]);
            json_out(['success' => true, 'count' => count($names), 'mode' => $mode]);
        }

        if ($action === 'reset_account_overrides') {
            $account_id = intval($_POST['account_id'] ?? 0);
            if ($account_id <= 0 || !account_exists($pdo, $account_id)) {
                json_out(['success' => false, 'error' => 'Игрок не выбран.']);
            }
            require_account_below_actor($pdo, $account_id);
            $stmt = $pdo->prepare("DELETE FROM account_vehicle_overrides WHERE account_id = ?");
            $stmt->execute([$account_id]);
            insert_access_event($pdo, 'account', $account_id, '*', 1);
            log_staff_action($pdo, 'vehicle.account', 'account', $account_id, 'Сброшены персональные правила техники');
            json_out(['success' => true]);
        }

        if ($action === 'enable_all_global') {
            $pdo->exec("DELETE FROM disabled_vehicles");
            insert_access_event($pdo, 'global', null, '*', 1);
            log_staff_action($pdo, 'vehicle.global', 'vehicle', '*', 'Вся техника включена глобально');
            json_out(['success' => true]);
        }

        if ($action === 'save_account') {
            $account_id = intval($_POST['account_id'] ?? 0);
            if ($account_id <= 0 || !account_exists($pdo, $account_id)) {
                json_out(['success' => false, 'error' => 'Игрок не выбран.']);
            }
            require_account_below_actor($pdo, $account_id);
            $credits = max(0, intval($_POST['credits'] ?? 0));
            $gold = max(0, intval($_POST['gold'] ?? 0));
            $free_xp = max(0, intval($_POST['free_xp'] ?? 0));
            $slots = max(1, intval($_POST['slots'] ?? 1));
            $berths = max(0, intval($_POST['berths'] ?? 0));
            $stmt = $pdo->prepare("UPDATE accounts SET credits = ?, gold = ?, free_xp = ?, slots = ?, berths = ? WHERE id = ?");
            $stmt->execute([$credits, $gold, $free_xp, $slots, $berths, $account_id]);
            log_staff_action($pdo, 'account.update', 'account', $account_id, 'Обновлены игровые параметры аккаунта');
            json_out(['success' => true]);
        }

        if ($action === 'set_password') {
            $account_id = intval($_POST['account_id'] ?? 0);
            $password = (string)($_POST['password'] ?? '');
            if ($account_id <= 0 || !account_exists($pdo, $account_id)) {
                json_out(['success' => false, 'error' => 'Игрок не найден.']);
            }
            require_account_below_actor($pdo, $account_id);
            if (strlen($password) < 6 || strlen($password) > 128) {
                json_out(['success' => false, 'error' => 'Пароль должен быть от 6 до 128 символов.']);
            }
            $stmt = $pdo->prepare("UPDATE accounts SET password_hash = ? WHERE id = ?");
            $stmt->execute([md5($password), $account_id]);
            orion_revoke_account_remember_tokens($pdo, $account_id);
            log_staff_action($pdo, 'account.password', 'account', $account_id, 'Принудительно изменён пароль');
            json_out(['success' => true]);
        }

        if ($action === 'set_username') {
            $account_id = intval($_POST['account_id'] ?? 0);
            $new_username = trim($_POST['username'] ?? '');
            if ($account_id <= 0 || !account_exists($pdo, $account_id)) {
                json_out(['success' => false, 'error' => 'Игрок не найден.']);
            }
            require_account_below_actor($pdo, $account_id);
            if (strlen($new_username) < 3 || strlen($new_username) > 24) {
                json_out(['success' => false, 'error' => 'Никнейм должен быть от 3 до 24 символов.']);
            }
            // Нормализация как при регистрации (normalize_login_name): alnum + _ - .
            $normalized = '';
            for ($i = 0, $len = strlen($new_username); $i < $len; $i++) {
                $ch = $new_username[$i];
                if (ctype_alnum($ch) || $ch === '_' || $ch === '-' || $ch === '.') {
                    $normalized .= $ch;
                }
            }
            $normalized = substr($normalized, 0, 24);
            if ($normalized === '') {
                json_out(['success' => false, 'error' => 'Никнейм содержит недопустимые символы.']);
            }
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM accounts WHERE (username = ? OR normalized_name = ?) AND id <> ?");
            $stmt->execute([$new_username, $normalized, $account_id]);
            if ($stmt->fetchColumn() > 0) {
                json_out(['success' => false, 'error' => 'Этот никнейм уже занят.']);
            }
            $stmt = $pdo->prepare("UPDATE accounts SET username = ?, normalized_name = ? WHERE id = ?");
            $stmt->execute([$new_username, $normalized, $account_id]);
            log_staff_action($pdo, 'account.username', 'account', $account_id, 'Никнейм изменён на ' . $new_username);
            json_out(['success' => true, 'username' => $new_username]);
        }

        if ($action === 'ban_account') {
            $account_id = intval($_POST['account_id'] ?? 0);
            $reason = $_POST['reason'] ?? '';
            $also_ip = intval($_POST['also_ip'] ?? 0) === 1;
            if ($account_id <= 0 || !account_exists($pdo, $account_id)) {
                json_out(['success' => false, 'error' => 'Игрок не найден.']);
            }
            if ($account_id === intval($_SESSION['user_id'])) {
                json_out(['success' => false, 'error' => 'Нельзя забанить самого себя.']);
            }
            require_account_below_actor($pdo, $account_id);
            if (contract_account_is_protected($pdo, $account_id)) {
                json_out(['success' => false, 'error' => 'Аккаунт защищён действующим семидневным контрактом. Сначала зафиксируйте деструктивные действия и расторгните контракт в соответствующей вкладке.']);
            }
            $pdo->beginTransaction();
            add_ban($pdo, 'account', $account_id, $reason);
            $banned_ip = null;
            if ($also_ip) {
                $banned_ip = account_known_ip($pdo, $account_id);
                if ($banned_ip !== null) {
                    add_ban($pdo, 'ip', $banned_ip, $reason);
                }
            }
            $pdo->commit();
            log_staff_action($pdo, 'ban.create', 'account', $account_id, 'Заблокирован аккаунт' . ($banned_ip ? ' и IP' : ''), ['reason' => limit_text($reason, 255)]);
            json_out(['success' => true, 'banned_ip' => $banned_ip]);
        }

        if ($action === 'bulk_ban_accounts') {
            $raw_ids = $_POST['account_ids'] ?? [];
            if (!is_array($raw_ids)) {
                $raw_ids = [$raw_ids];
            }
            $reason = $_POST['reason'] ?? '';
            $also_ip = intval($_POST['also_ip'] ?? 1) === 1;
            $self_id = intval($_SESSION['user_id']);
            $ids = [];
            foreach ($raw_ids as $rid) {
                $rid = intval($rid);
                if ($rid > 0) {
                    $ids[$rid] = true;
                }
            }
            $ids = array_keys($ids);
            if (empty($ids)) {
                json_out(['success' => false, 'error' => 'Никто не выбран.']);
            }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT id, is_admin, staff_role FROM accounts WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $valid = [];
            foreach ($stmt->fetchAll() as $row) {
                $aid = intval($row['id']);
                $target_role = normalize_staff_role($row['staff_role'] ?? '', intval($row['is_admin']) === 1);
                if ($aid === $self_id || intval(staff_role_info($target_role)['rank']) >= intval($current_staff_access['rank'])) {
                    continue;
                }
                $valid[] = $aid;
            }
            if (empty($valid)) {
                json_out(['success' => false, 'error' => 'Среди выбранных некого банить (только админы/вы сами).']);
            }
            $protected = [];
            foreach ($valid as $aid) {
                if (contract_account_is_protected($pdo, $aid)) {
                    $protected[] = $aid;
                }
            }
            if (!empty($protected)) {
                json_out(['success' => false, 'error' => 'Массовый бан отменён: среди выбранных есть участники с действующим контрактом. Их можно снять только за зафиксированные деструктивные действия.']);
            }
            $pdo->beginTransaction();
            $banned = 0;
            $banned_ips = 0;
            foreach ($valid as $aid) {
                add_ban($pdo, 'account', $aid, $reason);
                $banned++;
                if ($also_ip) {
                    $ip = account_known_ip($pdo, $aid);
                    if ($ip !== null) {
                        add_ban($pdo, 'ip', $ip, $reason);
                        $banned_ips++;
                    }
                }
            }
            $pdo->commit();
            log_staff_action($pdo, 'ban.bulk', 'account', implode(',', $valid), 'Массово заблокировано аккаунтов: ' . $banned, ['banned_ips' => $banned_ips, 'reason' => limit_text($reason, 255)]);
            json_out(['success' => true, 'banned' => $banned, 'banned_ips' => $banned_ips, 'skipped' => count($ids) - $banned]);
        }

        if ($action === 'ban_ip') {
            $ip = trim($_POST['ip'] ?? '');
            $reason = $_POST['reason'] ?? '';
            if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
                json_out(['success' => false, 'error' => 'Некорректный IP-адрес.']);
            }
            require_ip_below_actor($pdo, $ip);
            $contract_now = contract_now();
            $stmt = $pdo->prepare("SELECT c.contract_number FROM staff_contracts c INNER JOIN accounts a ON a.id = c.account_id WHERE c.status = 'active' AND c.starts_at <= ? AND c.expires_at > ? AND (a.last_ip = ? OR a.reg_ip = ?) LIMIT 1");
            $stmt->execute([$contract_now, $contract_now, $ip, $ip]);
            if ($stmt->fetchColumn() !== false) {
                json_out(['success' => false, 'error' => 'Этот IP связан с участником, защищённым действующим контрактом.']);
            }
            add_ban($pdo, 'ip', $ip, $reason);
            log_staff_action($pdo, 'ban.create', 'ip', $ip, 'Заблокирован IP ' . $ip, ['reason' => limit_text($reason, 255)]);
            json_out(['success' => true]);
        }

        if ($action === 'ban_mac') {
            $mac = strtoupper(trim($_POST['mac'] ?? ''));
            $reason = $_POST['reason'] ?? '';
            if (!preg_match('/^[0-9A-F]{2}([:-][0-9A-F]{2}){5}$/', $mac)) {
                json_out(['success' => false, 'error' => 'Некорректный MAC (формат AA:BB:CC:DD:EE:FF).']);
            }
            $mac = str_replace('-', ':', $mac);
            add_ban($pdo, 'mac', $mac, $reason);
            log_staff_action($pdo, 'ban.create', 'mac', $mac, 'Заблокирован MAC ' . $mac, ['reason' => limit_text($reason, 255)]);
            json_out(['success' => true]);
        }

        if ($action === 'unban') {
            $ban_id = intval($_POST['ban_id'] ?? 0);
            if ($ban_id <= 0) {
                json_out(['success' => false, 'error' => 'Не указан бан.']);
            }
            $stmt = $pdo->prepare("SELECT ban_type, account_id, ip, mac FROM bans WHERE id = ?");
            $stmt->execute([$ban_id]);
            $removed_ban = $stmt->fetch();
            $stmt = $pdo->prepare("DELETE FROM bans WHERE id = ?");
            $stmt->execute([$ban_id]);
            log_staff_action($pdo, 'ban.remove', 'ban', $ban_id, 'Снята блокировка #' . $ban_id, $removed_ban ?: []);
            json_out(['success' => true]);
        }

        json_out(['success' => false, 'error' => 'Неизвестное действие.']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Admin AJAX error: " . $e->getMessage());
        json_out(['success' => false, 'error' => 'Произошла внутренняя ошибка.']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['ajax'])) {
    $action = $_POST['action'] ?? '';
    $form_action_config = [
        'save_news' => ['permission' => 'news.manage', 'tab' => 'news'],
        'delete_news_media' => ['permission' => 'news.manage', 'tab' => 'news'],
        'delete_news' => ['permission' => 'news.delete', 'tab' => 'news'],
        'save_update' => ['permission' => 'updates.manage', 'tab' => 'updates'],
        'delete_update' => ['permission' => 'updates.manage', 'tab' => 'updates'],
        'set_server_status' => ['permission' => 'server.manage', 'tab' => 'server'],
        'update_gso_implementation' => ['permission' => 'council.implement', 'tab' => 'implement'],
        'override_gso_status' => ['permission' => 'council.implement', 'tab' => 'implement'],
        'save_downloads' => ['permission' => 'downloads.manage', 'tab' => 'downloads'],
        'save_staff_access' => ['permission' => 'staff.manage', 'tab' => 'staff'],
        'approve_contract_application' => ['permission' => 'dashboard.view', 'tab' => 'contracts'],
        'reject_contract_application' => ['permission' => 'dashboard.view', 'tab' => 'contracts'],
        'terminate_contract' => ['permission' => 'dashboard.view', 'tab' => 'contracts'],
        'close_all_bug_reports' => ['permission' => 'reports.manage', 'tab' => 'reports'],
        'update_bug_report' => ['permission' => 'reports.manage', 'tab' => 'reports'],
        'delete_bug_report' => ['permission' => 'reports.delete', 'tab' => 'reports'],
    ];
    if (isset($form_action_config[$action])) {
        $form_redirect_tab = $form_action_config[$action]['tab'];
        require_form_csrf($form_redirect_tab);
        require_form_permission($form_action_config[$action]['permission'], $form_redirect_tab);
        try {
            if ($action === 'save_update') {
                $update_id = intval($_POST['update_id'] ?? 0);
                $version = trim((string)($_POST['version'] ?? ''));
                $name = trim((string)($_POST['name'] ?? ''));
                $release_date = trim((string)($_POST['release_date'] ?? ''));
                $tag = trim((string)($_POST['tag'] ?? ''));
                $intro = trim((string)($_POST['intro'] ?? ''));
                $status = (string)($_POST['status'] ?? 'draft');
                if ($version === '' || strlen($version) > 32) {
                    throw new RuntimeException('Укажите версию обновления длиной до 32 символов.');
                }
                if (gso_text_length($name) < 3 || gso_text_length($name) > 180) {
                    throw new RuntimeException('Название обновления должно содержать от 3 до 180 символов.');
                }
                $date_object = DateTime::createFromFormat('Y-m-d', $release_date);
                if (!$date_object || $date_object->format('Y-m-d') !== $release_date) {
                    throw new RuntimeException('Укажите корректную дату выпуска.');
                }
                if (gso_text_length($tag) > 80 || gso_text_length($intro) > 20000) {
                    throw new RuntimeException('Тег или вступление превышают допустимую длину.');
                }
                if (!in_array($status, ['draft', 'published'], true)) {
                    $status = 'draft';
                }
                $categories = orion_update_categories_from_text($_POST['categories_text'] ?? '');
                $categories_json = json_encode($categories, JSON_UNESCAPED_UNICODE);
                if ($update_id > 0) {
                    $stmt = $pdo->prepare("UPDATE site_updates SET version = ?, name = ?, release_date = ?, tag = ?, intro = ?, categories_json = ?, status = ?, author_account_id = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$version, $name, $release_date, $tag, $intro, $categories_json, $status, intval($_SESSION['user_id']), $update_id]);
                    if ($stmt->rowCount() === 0) {
                        $exists = $pdo->prepare("SELECT COUNT(*) FROM site_updates WHERE id = ?");
                        $exists->execute([$update_id]);
                        if (intval($exists->fetchColumn()) === 0) {
                            throw new RuntimeException('Запись обновления не найдена.');
                        }
                    }
                } else {
                    $stmt = $pdo->prepare("INSERT INTO site_updates (version, name, release_date, tag, intro, categories_json, status, author_account_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$version, $name, $release_date, $tag, $intro, $categories_json, $status, intval($_SESSION['user_id'])]);
                    $update_id = intval($pdo->lastInsertId());
                }
                log_staff_action($pdo, 'updates.save', 'site_update', $update_id, 'Сохранено обновление «' . $version . ' · ' . $name . '»', ['status' => $status]);
                set_admin_flash('success', 'Запись истории обновлений сохранена.');
                header('Location: admin.php?tab=updates&update_id=' . $update_id);
                exit;
            }

            if ($action === 'delete_update') {
                $update_id = intval($_POST['update_id'] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM site_updates WHERE id = ?");
                $stmt->execute([$update_id]);
                if ($stmt->rowCount() === 0) {
                    throw new RuntimeException('Запись обновления не найдена.');
                }
                log_staff_action($pdo, 'updates.delete', 'site_update', $update_id, 'Удалена запись истории обновлений');
                set_admin_flash('success', 'Запись обновления удалена.');
                header('Location: admin.php?tab=updates');
                exit;
            }

            if ($action === 'set_server_status') {
                if (!contract_is_owner_admin($current_staff_access)) {
                    throw new RuntimeException('Статус сервера может менять только глава проекта.');
                }
                $server_status = (string)($_POST['server_status'] ?? 'online');
                $server_message = (string)($_POST['server_status_message'] ?? '');
                orion_set_server_state($pdo, $server_status, $server_message);
                log_staff_action($pdo, 'server.status', 'site_setting', 'server_status', 'Статус сервера: ' . $server_status, ['message' => $server_message]);
                set_admin_flash(
                    'success',
                    $server_status === 'online' ? 'Сервер отмечен как онлайн.' : 'Сервер отмечен как офлайн.',
                    $server_status === 'online' ? 'Server marked as online.' : 'Server marked as offline.'
                );
                header('Location: admin.php?tab=server');
                exit;
            }

            if ($action === 'update_gso_implementation') {
                if (!contract_is_owner_admin($current_staff_access)) {
                    throw new RuntimeException('Очередью реализации может управлять только глава проекта.');
                }
                $proposal_id = intval($_POST['proposal_id'] ?? 0);
                $implementation_action = (string)($_POST['implementation_action'] ?? '');
                $implementation_note = (string)($_POST['implementation_note'] ?? '');
                gso_update_implementation($pdo, $proposal_id, intval($_SESSION['user_id']), $implementation_action, $implementation_note);
                log_staff_action($pdo, 'gso.implementation', 'gso_proposal', $proposal_id, 'Обновлён статус реализации решения ГСО', ['action' => $implementation_action]);
                set_admin_flash('success', 'Статус реализации решения обновлён.');
                header('Location: admin.php?tab=implement#proposal-' . $proposal_id);
                exit;
            }

            if ($action === 'override_gso_status') {
                if (!contract_is_owner_admin($current_staff_access)) {
                    throw new RuntimeException('Менять статус принятого решения может только глава проекта.');
                }
                $proposal_id = intval($_POST['proposal_id'] ?? 0);
                $new_status = (string)($_POST['new_status'] ?? '');
                $status_note = (string)($_POST['status_note'] ?? '');
                $previous_status = gso_override_status($pdo, $proposal_id, intval($_SESSION['user_id']), $new_status, $status_note);
                log_staff_action($pdo, 'gso.status', 'gso_proposal', $proposal_id, 'Изменён статус решения ГСО: ' . gso_status_label($previous_status, 'ru') . ' → ' . gso_status_label($new_status, 'ru'), ['previous_status' => $previous_status, 'new_status' => $new_status, 'note' => trim($status_note), 'public' => false]);
                set_admin_flash(
                    'success',
                    'Статус решения изменён на «' . gso_status_label($new_status, 'ru') . '». Публично изменение не показывается.',
                    'Decision status changed to "' . admin_gso_status_label($new_status) . '". This change is not public.'
                );
                header('Location: admin.php?tab=implement#proposal-' . $proposal_id);
                exit;
            }

            if (in_array($action, ['approve_contract_application', 'reject_contract_application', 'terminate_contract'], true)) {
                if (!contract_is_owner_admin($current_staff_access)) {
                    throw new RuntimeException('Контракты может обрабатывать только глава проекта.');
                }
                if ($action === 'approve_contract_application') {
                    $application_id = intval($_POST['application_id'] ?? 0);
                    $result = contract_review_application(
                        $pdo,
                        $application_id,
                        intval($_SESSION['user_id']),
                        'approve',
                        $_POST['decision_note'] ?? ''
                    );
                    $approval_log = $result['status'] === 'scheduled'
                        ? 'Принято и запланировано продление ' . $result['contract_number']
                        : 'Принят и активирован контракт ' . $result['contract_number'];
                    log_staff_action($pdo, 'contract.application.approve', 'contract_application', $application_id, $approval_log, ['role' => $result['staff_role'], 'status' => $result['status']]);
                    refresh_session_staff_access($pdo);
                    set_admin_flash(
                        'success',
                        $result['status'] === 'scheduled'
                            ? 'Продление принято. Новый срок начнётся после завершения текущего контракта.'
                            : 'Контракт принят. Роль включена на семь дней.',
                        $result['status'] === 'scheduled'
                            ? 'Renewal accepted. The new term starts after the current contract ends.'
                            : 'Contract accepted. The role is active for seven days.'
                    );
                } elseif ($action === 'reject_contract_application') {
                    $application_id = intval($_POST['application_id'] ?? 0);
                    contract_review_application($pdo, $application_id, intval($_SESSION['user_id']), 'reject', $_POST['decision_note'] ?? '');
                    log_staff_action($pdo, 'contract.application.reject', 'contract_application', $application_id, 'Отклонён контракт пользователя');
                    set_admin_flash('success', 'Контракт отклонён.');
                } else {
                    $contract_id = intval($_POST['contract_id'] ?? 0);
                    $terminated = contract_terminate(
                        $pdo,
                        $contract_id,
                        intval($_SESSION['user_id']),
                        $_POST['termination_reason'] ?? ''
                    );
                    $was_scheduled = (string)($terminated['previous_status'] ?? '') === 'scheduled';
                    log_staff_action($pdo, 'contract.terminate', 'contract', $contract_id, ($was_scheduled ? 'Отменён запланированный контракт ' : 'Расторгнут контракт ') . $terminated['contract_number'], [
                        'account_id' => intval($terminated['account_id']),
                        'reason' => $terminated['termination_reason'],
                    ]);
                    set_admin_flash(
                        'success',
                        $was_scheduled ? 'Запланированное продление отменено.' : 'Контракт расторгнут. Роль пользователя снята.',
                        $was_scheduled ? 'Scheduled renewal cancelled.' : 'Contract terminated. The user role was removed.'
                    );
                }
                header('Location: admin.php?tab=contracts');
                exit;
            }

            if ($action === 'save_staff_access') {
                $staff_account_id = intval($_POST['staff_account_id'] ?? 0);
                if (contract_account_is_protected($pdo, $staff_account_id)) {
                    throw new RuntimeException('Роль и разрешения защищены действующим семидневным контрактом. До конца срока их нельзя изменить вручную.');
                }
                $target_access = staff_access_for_account($pdo, $staff_account_id);
                if (!staff_can_manage_access($current_staff_access, $target_access)) {
                    throw new RuntimeException('Нельзя изменять себя или сотрудника равного/высшего уровня.');
                }
                $new_role = (string)($_POST['staff_role'] ?? 'player');
                $assignable_roles = staff_assignable_roles($current_staff_access);
                if (!isset($assignable_roles[$new_role])) {
                    throw new RuntimeException('Эту роль нельзя назначить с вашего уровня доступа.');
                }

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE accounts SET staff_role = ?, is_admin = 0 WHERE id = ?");
                $stmt->execute([$new_role, $staff_account_id]);
                $pdo->prepare("DELETE FROM staff_permission_overrides WHERE account_id = ?")->execute([$staff_account_id]);

                $new_role_info = staff_role_info($new_role);
                if ($new_role !== 'player' && empty($new_role_info['permissions_fixed'])) {
                    $permission_states = $_POST['permission_state'] ?? [];
                    $catalog = staff_permission_catalog();
                    $insert_override = $pdo->prepare("INSERT INTO staff_permission_overrides (account_id, permission_key, allowed, granted_by, updated_at) VALUES (?, ?, ?, ?, NOW())");
                    foreach ($catalog as $permission_key => $permission_meta) {
                        $field_key = str_replace('.', '__', $permission_key);
                        $state = is_array($permission_states) ? ($permission_states[$field_key] ?? 'inherit') : 'inherit';
                        if ($state !== 'allow' && $state !== 'deny') {
                            continue;
                        }
                        // Выдать можно только то право, которым владеешь сам:
                        // иначе роль с staff.manage раздаёт подчинённому доступ,
                        // которого у неё нет, и получает его через чужой аккаунт.
                        // Забрать право (deny) разрешено всегда.
                        if ($state === 'allow' && !admin_can($permission_key)) {
                            continue;
                        }
                        $insert_override->execute([
                            $staff_account_id,
                            $permission_key,
                            $state === 'allow' ? 1 : 0,
                            intval($_SESSION['user_id']),
                        ]);
                    }
                }
                $pdo->commit();
                log_staff_action($pdo, 'staff.access.update', 'account', $staff_account_id, 'Назначена роль «' . staff_role_info($new_role)['label'] . '»', ['previous_role' => $target_access['role'], 'new_role' => $new_role]);
                set_admin_flash('success', 'Роль и персональные разрешения сохранены.');
                header('Location: admin.php?tab=staff&staff_id=' . $staff_account_id);
                exit;
            }

            if ($action === 'close_all_bug_reports') {
                if (!contract_is_owner_admin($current_staff_access)) {
                    throw new RuntimeException('Закрыть все репорты может только глава проекта.');
                }
                $stmt = $pdo->prepare("UPDATE bug_reports SET status = 'closed' WHERE status <> 'closed'");
                $stmt->execute();
                $closed_count = $stmt->rowCount();
                log_staff_action(
                    $pdo,
                    'report.close_all',
                    'bug_report',
                    'all',
                    'Закрыты все незакрытые баг-репорты: ' . $closed_count,
                    ['closed_count' => $closed_count]
                );
                set_admin_flash(
                    'success',
                    $closed_count > 0
                        ? 'Все репорты закрыты. Изменено: ' . $closed_count . '.'
                        : 'Все репорты уже были закрыты.',
                    $closed_count > 0
                        ? 'All reports closed. Changed: ' . $closed_count . '.'
                        : 'All reports were already closed.'
                );
                header('Location: admin.php?tab=reports&report_status=closed');
                exit;
            }

            if ($action === 'update_bug_report') {
                $report_id = intval($_POST['report_id'] ?? 0);
                $report_status = (string)($_POST['report_status'] ?? 'open');
                if ($report_id <= 0 || !in_array($report_status, ['open', 'in_progress', 'resolved', 'closed'], true)) {
                    throw new RuntimeException('Некорректные данные репорта.');
                }
                $stmt = $pdo->prepare("SELECT account_id, is_approved FROM bug_reports WHERE id = ?");
                $stmt->execute([$report_id]);
                $report_row = $stmt->fetch();
                if (!$report_row) {
                    throw new RuntimeException('Баг-репорт не найден.');
                }
                $is_approved = isset($_POST['is_approved']) ? 1 : 0;
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE bug_reports SET status = ?, is_approved = ? WHERE id = ?")->execute([$report_status, $is_approved, $report_id]);

                if (isset($_POST['report_author_restricted'])) {
                    if (!admin_can('users.edit')) {
                        throw new RuntimeException('Недостаточно прав для ограничения автора репорта.');
                    }
                    $author_id = intval($report_row['account_id']);
                    $author_access = staff_access_for_account($pdo, $author_id);
                    if (!staff_can_act_on_account($current_staff_access, $author_access)) {
                        throw new RuntimeException('Нельзя ограничить сотрудника равного/высшего уровня.');
                    }
                    $restricted = intval($_POST['report_author_restricted']) === 1 ? 1 : 0;
                    $pdo->prepare("UPDATE accounts SET is_banned_reports = ? WHERE id = ?")->execute([$restricted, $author_id]);
                    log_staff_action($pdo, 'report.author.restrict', 'account', $author_id, $restricted ? 'Запрещена отправка репортов' : 'Разрешена отправка репортов');
                }

                $pdo->commit();
                log_staff_action($pdo, 'report.update', 'bug_report', $report_id, 'Статус репорта: ' . $report_status, ['approved' => $is_approved]);
                set_admin_flash('success', 'Баг-репорт обновлён.');
                header('Location: admin.php?tab=reports');
                exit;
            }

            if ($action === 'delete_bug_report') {
                $report_id = intval($_POST['report_id'] ?? 0);
                if ($report_id <= 0) {
                    throw new RuntimeException('Баг-репорт не выбран.');
                }
                $pdo->beginTransaction();
                $pdo->prepare("DELETE FROM bug_comments WHERE bug_id = ?")->execute([$report_id]);
                $pdo->prepare("DELETE FROM bug_reports WHERE id = ?")->execute([$report_id]);
                $pdo->commit();
                log_staff_action($pdo, 'report.delete', 'bug_report', $report_id, 'Удалён баг-репорт #' . $report_id);
                set_admin_flash('success', 'Баг-репорт удалён.');
                header('Location: admin.php?tab=reports');
                exit;
            }

            if ($action === 'save_downloads') {
                $client_mirrors = [];
                $client_names = $_POST['client_name'] ?? [];
                $client_urls = $_POST['client_url'] ?? [];
                $client_enabled = $_POST['client_enabled'] ?? [];
                if (is_array($client_names)) {
                    for ($i = 0; $i < min(5, count($client_names)); $i++) {
                        $name = trim((string)($client_names[$i] ?? ''));
                        $url = trim((string)($client_urls[$i] ?? ''));
                        if ($name !== '' && $url !== '') {
                            $client_mirrors[] = ['name' => $name, 'url' => $url, 'enabled' => !empty($client_enabled[$i])];
                        }
                    }
                }
                
                $patch_mirrors = [];
                $patch_names = $_POST['patch_name'] ?? [];
                $patch_urls = $_POST['patch_url'] ?? [];
                $patch_enabled = $_POST['patch_enabled'] ?? [];
                if (is_array($patch_names)) {
                    for ($i = 0; $i < min(5, count($patch_names)); $i++) {
                        $name = trim((string)($patch_names[$i] ?? ''));
                        $url = trim((string)($patch_urls[$i] ?? ''));
                        if ($name !== '' && $url !== '') {
                            $patch_mirrors[] = ['name' => $name, 'url' => $url, 'enabled' => !empty($patch_enabled[$i])];
                        }
                    }
                }
                
                set_setting($pdo, 'download_client_mirrors', json_encode($client_mirrors, JSON_UNESCAPED_UNICODE));
                set_setting($pdo, 'download_patch_mirrors', json_encode($patch_mirrors, JSON_UNESCAPED_UNICODE));
                set_setting($pdo, 'download_video_url', trim($_POST['video_url'] ?? ''));
                set_setting($pdo, 'download_instructions', trim($_POST['instructions'] ?? ''));

                log_staff_action($pdo, 'downloads.save', 'settings', 'downloads', 'Обновлены зеркала и инструкции загрузки');
                set_admin_flash('success', 'Настройки загрузок сохранены.');
                header('Location: admin.php?tab=downloads');
                exit;
            }

            if ($action === 'save_news') {
                $news_id = intval($_POST['news_id'] ?? 0);
                $title = limit_text($_POST['title'] ?? '', 180);
                $summary = limit_text($_POST['summary'] ?? '', 512);
                $body = trim((string)($_POST['body'] ?? ''));
                $status = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
                $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
                $published_at = parse_news_datetime($_POST['published_at'] ?? '');
                if ($status === 'published' && !$published_at) {
                    $published_at = date('Y-m-d H:i:s');
                }
                if ($status === 'draft' && !$published_at) {
                    $published_at = null;
                }
                if ($title === '') {
                    throw new RuntimeException('У новости должен быть заголовок.');
                }
                if ($body === '') {
                    throw new RuntimeException('Текст новости не может быть пустым.');
                }

                if ($news_id > 0) {
                    $stmt = $pdo->prepare("UPDATE site_news SET author_account_id = ?, title = ?, summary = ?, body = ?, status = ?, is_pinned = ?, published_at = ? WHERE id = ?");
                    $stmt->execute([
                        intval($_SESSION['user_id'] ?? 0) ?: null,
                        $title,
                        $summary,
                        $body,
                        $status,
                        $is_pinned,
                        $published_at,
                        $news_id,
                    ]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO site_news (author_account_id, title, summary, body, status, is_pinned, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $now = date('Y-m-d H:i:s');
                    $stmt->execute([
                        intval($_SESSION['user_id'] ?? 0) ?: null,
                        $title,
                        $summary,
                        $body,
                        $status,
                        $is_pinned,
                        $published_at,
                        $now,
                        $now,
                    ]);
                    $news_id = intval($pdo->lastInsertId());
                }

                try {
                    $uploaded = attach_news_uploads($pdo, $news_id);
                    log_staff_action($pdo, 'news.save', 'news', $news_id, 'Сохранена новость «' . $title . '»', ['status' => $status, 'uploaded' => $uploaded]);
                    set_admin_flash(
                        'success',
                        $uploaded > 0 ? 'Новость сохранена, медиа загружено: ' . $uploaded . '.' : 'Новость сохранена.',
                        $uploaded > 0 ? 'News saved; media uploaded: ' . $uploaded . '.' : 'News saved.'
                    );
                } catch (Exception $media_error) {
                    error_log("Admin news upload error: " . $media_error->getMessage());
                    set_admin_flash('danger', 'Новость сохранена, но медиа не загрузилось: ' . $media_error->getMessage());
                }
                redirect_admin_news(['edit_id' => $news_id]);
            }

            if ($action === 'delete_news_media') {
                $media_id = intval($_POST['media_id'] ?? 0);
                $stmt = $pdo->prepare("SELECT news_id, file_path FROM site_news_media WHERE id = ?");
                $stmt->execute([$media_id]);
                $media = $stmt->fetch();
                if (!$media) {
                    throw new RuntimeException('Медиа не найдено.');
                }
                delete_news_media_file($media['file_path']);
                $stmt = $pdo->prepare("DELETE FROM site_news_media WHERE id = ?");
                $stmt->execute([$media_id]);
                log_staff_action($pdo, 'news.media.delete', 'news_media', $media_id, 'Удалено медиа новости #' . intval($media['news_id']));
                set_admin_flash('success', 'Медиа удалено.');
                redirect_admin_news(['edit_id' => intval($media['news_id'])]);
            }

            if ($action === 'delete_news') {
                $news_id = intval($_POST['news_id'] ?? 0);
                $stmt = $pdo->prepare("SELECT file_path FROM site_news_media WHERE news_id = ?");
                $stmt->execute([$news_id]);
                foreach ($stmt->fetchAll() as $media) {
                    delete_news_media_file($media['file_path']);
                }
                $stmt = $pdo->prepare("DELETE FROM site_news WHERE id = ?");
                $stmt->execute([$news_id]);
                log_staff_action($pdo, 'news.delete', 'news', $news_id, 'Удалена новость #' . $news_id);
                set_admin_flash('success', 'Новость удалена.');
                redirect_admin_news();
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Admin form action error: " . $e->getMessage());
            set_admin_flash('danger', $e->getMessage());
            $edit_id = intval($_POST['news_id'] ?? 0);
            if ($form_redirect_tab === 'news') {
                redirect_admin_news($edit_id > 0 ? ['edit_id' => $edit_id] : []);
            }
            if ($form_redirect_tab === 'updates') {
                $update_id = intval($_POST['update_id'] ?? 0);
                header('Location: admin.php?tab=updates' . ($update_id > 0 ? '&update_id=' . $update_id : ''));
                exit;
            }
            if ($form_redirect_tab === 'implement') {
                $proposal_id = intval($_POST['proposal_id'] ?? 0);
                header('Location: admin.php?tab=implement' . ($proposal_id > 0 ? '#proposal-' . $proposal_id : ''));
                exit;
            }
            header('Location: admin.php?tab=' . urlencode($form_redirect_tab));
            exit;
        }
    }
}

$disabled_tanks = [];
$account_overrides = [];
$accounts = [];
$selected_account = null;
$search = trim($_GET['search'] ?? '');
$account_search = trim($_GET['account_search'] ?? '');
$filter_nation = $_GET['nation'] ?? '';
$filter_class = $_GET['class'] ?? '';
$filter_tier = $_GET['tier'] ?? '';
$filter_status = $_GET['status'] ?? '';
$selected_account_id = intval($_GET['account_id'] ?? 0);
$tab_permissions = [
    'dashboard' => 'dashboard.view',
    'reports' => 'reports.manage',
    'users' => 'users.view',
    'bans' => 'bans.manage',
    'news' => 'news.manage',
    'updates' => 'updates.manage',
    'server' => 'server.manage',
    'vehicles' => 'vehicles.manage',
    'downloads' => 'downloads.manage',
    'contracts' => 'dashboard.view',
    'implement' => 'council.implement',
    'staff' => 'staff.view',
    'audit' => 'audit.view',
];
$tab = $_GET['tab'] ?? 'dashboard';
$tab_allowed = isset($tab_permissions[$tab]) && admin_can($tab_permissions[$tab]);
if ($tab === 'bans') {
    $tab_allowed = admin_can('bans.manage') || admin_can('bans.unban');
}
if ($tab === 'contracts') {
    $tab_allowed = contract_is_owner_admin($current_staff_access);
}
if (in_array($tab, ['server', 'implement'], true)) {
    $tab_allowed = contract_is_owner_admin($current_staff_access);
}
if (!$tab_allowed) {
    $tab = 'dashboard';
}
$user_search = trim($_GET['user_search'] ?? '');
$user_page = max(1, intval($_GET['user_page'] ?? 1));
$news_search = trim($_GET['news_search'] ?? '');
$news_status = $_GET['news_status'] ?? '';
if (!in_array($news_status, ['', 'draft', 'published'], true)) {
    $news_status = '';
}
$news_page = max(1, intval($_GET['news_page'] ?? 1));
$news_edit_id = intval($_GET['edit_id'] ?? 0);
$admin_flash = take_admin_flash();
$admin_server_state = orion_server_state($pdo);
$updates_items = [];
$editing_update = null;
$update_edit_id = intval($_GET['update_id'] ?? 0);
if (admin_can('updates.manage')) {
    try {
        $updates_items = orion_update_history($pdo, true);
        if ($update_edit_id > 0) {
            foreach ($updates_items as $update_item) {
                if (intval($update_item['id']) === $update_edit_id) {
                    $editing_update = $update_item;
                    break;
                }
            }
        }
    } catch (Exception $e) {
        error_log('Admin update history query: ' . $e->getMessage());
    }
}

$gso_implementation_items = [];
$gso_status_archive = [];
$gso_internal_events = [];
if (contract_is_owner_admin($current_staff_access)) {
    try {
        gso_sync_expired_votes($pdo);
        $gso_admin_proposals = gso_load_proposals($pdo, intval($_SESSION['user_id']), 120);
        $gso_implementation_items = array_values(array_filter($gso_admin_proposals, function ($proposal) {
            return $proposal['status'] === 'implementation';
        }));
        // Все решения, которым глава проекта может назначить другой статус
        $gso_status_archive = array_slice(array_values(array_filter($gso_admin_proposals, function ($proposal) {
            return $proposal['status'] !== 'implementation';
        })), 0, 60);
        // Служебные события скрыты от публичной хронологии ГСО и видны только здесь
        $gso_event_ids = array_merge(array_column($gso_implementation_items, 'id'), array_column($gso_status_archive, 'id'));
        foreach (gso_load_events($pdo, $gso_event_ids, true) as $event_proposal_id => $events) {
            $internal = array_values(array_filter($events, function ($event) {
                return ($event['visibility'] ?? 'public') === 'internal';
            }));
            if ($internal) {
                $gso_internal_events[$event_proposal_id] = $internal;
            }
        }
    } catch (Exception $e) {
        error_log('Admin GSO implementation query: ' . $e->getMessage());
    }
}
$render_gso_internal_log = function ($proposal_id) use ($gso_internal_events) {
    $events = array_reverse($gso_internal_events[intval($proposal_id)] ?? []);
    if (!$events) {
        return;
    }
    echo '<div class="admin-status-log"><strong>Служебная хронология (публично не видна)</strong><ul>';
    foreach (array_slice($events, 0, 5) as $event) {
        $actor_name = trim((string)($event['actor_name'] ?? ''));
        echo '<li><span>' . h(date('d.m.Y H:i', strtotime($event['created_at']))) . ' · ' . ($actor_name !== '' ? '<span class="notranslate" translate="no">' . h($actor_name) . '</span>' : h('Система')) . '</span><span class="notranslate" translate="no">' . h($event['detail']) . '</span></li>';
    }
    echo '</ul></div>';
};
$contract_admin_data = ['pending' => [], 'current' => [], 'stats' => ['pending' => 0, 'active' => 0, 'accepted' => 0]];
if (contract_is_owner_admin($current_staff_access)) {
    try {
        $contract_admin_data = contract_admin_data($pdo);
    } catch (Exception $e) {
        error_log('Admin contracts query: ' . $e->getMessage());
    }
}

try {
    $disabled_tanks = $pdo->query("SELECT vehicle_name FROM disabled_vehicles")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    error_log("Admin disabled_tanks query: " . $e->getMessage());
    $disabled_tanks = [];
}
$disabled_set = array_flip($disabled_tanks);

try {
    if ($account_search !== '') {
        $stmt = $pdo->prepare("SELECT id, username, credits, gold, free_xp, slots, berths, is_admin, staff_role, last_login FROM accounts WHERE username LIKE ? OR normalized_name LIKE ? OR id = ? ORDER BY last_login DESC LIMIT 80");
        $like = '%' . $account_search . '%';
        $stmt->execute([$like, $like, intval($account_search)]);
    } else {
        $stmt = $pdo->query("SELECT id, username, credits, gold, free_xp, slots, berths, is_admin, staff_role, last_login FROM accounts ORDER BY last_login DESC LIMIT 80");
    }
    $accounts = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Admin accounts query: " . $e->getMessage());
    $accounts = [];
}

if ($selected_account_id <= 0 && !empty($accounts)) {
    $selected_account_id = intval($accounts[0]['id']);
}

if ($selected_account_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, credits, gold, free_xp, slots, berths, is_admin, staff_role, last_login FROM accounts WHERE id = ?");
        $stmt->execute([$selected_account_id]);
        $selected_account = $stmt->fetch() ?: null;
        if ($selected_account) {
            $found = false;
            foreach ($accounts as $account) {
                if (intval($account['id']) === $selected_account_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                array_unshift($accounts, $selected_account);
            }
            $stmt = $pdo->prepare("SELECT vehicle_name, is_enabled FROM account_vehicle_overrides WHERE account_id = ?");
            $stmt->execute([$selected_account_id]);
            foreach ($stmt->fetchAll() as $row) {
                $account_overrides[$row['vehicle_name']] = intval($row['is_enabled']) === 1;
            }
        }
    } catch (Exception $e) {
        error_log("Admin selected_account query: " . $e->getMessage());
        $selected_account = null;
    }
}

$total_accounts = 0;
$override_count = 0;
$event_count = 0;
try {
    $total_accounts = intval($pdo->query("SELECT COUNT(*) FROM accounts")->fetchColumn());
    $override_count = intval($pdo->query("SELECT COUNT(*) FROM account_vehicle_overrides")->fetchColumn());
    $event_count = intval($pdo->query("SELECT COUNT(*) FROM vehicle_access_events")->fetchColumn());
} catch (Exception $e) {
    error_log("Admin stats query: " . $e->getMessage());
}

$filtered_vehicles = [];
foreach ($vehicles as $vehicle) {
    $name = $vehicle['name'] ?? '';
    $nation = $vehicle['nation'] ?? '';
    $class = $vehicle['vehicleClass'] ?? '';
    $level = intval($vehicle['level_calculated'] ?? 1);
    $global_enabled = !isset($disabled_set[$name]);
    $has_override = array_key_exists($name, $account_overrides);
    $effective_enabled = $has_override ? $account_overrides[$name] : $global_enabled;

    if ($search !== '' && stripos($name, $search) === false) {
        continue;
    }
    if ($filter_nation !== '' && $nation !== $filter_nation) {
        continue;
    }
    if ($filter_class !== '' && $class !== $filter_class) {
        continue;
    }
    if ($filter_tier !== '' && $level !== intval($filter_tier)) {
        continue;
    }
    if ($filter_status === 'global_enabled' && !$global_enabled) {
        continue;
    }
    if ($filter_status === 'global_disabled' && $global_enabled) {
        continue;
    }
    if ($filter_status === 'effective_enabled' && !$effective_enabled) {
        continue;
    }
    if ($filter_status === 'effective_disabled' && $effective_enabled) {
        continue;
    }
    if ($filter_status === 'overridden' && !$has_override) {
        continue;
    }

    $vehicle['global_enabled'] = $global_enabled;
    $vehicle['has_override'] = $has_override;
    $vehicle['effective_enabled'] = $effective_enabled;
    $vehicle['override_mode'] = $has_override ? ($account_overrides[$name] ? 'enabled' : 'disabled') : 'inherit';
    $filtered_vehicles[] = $vehicle;
}

$filtered_vehicle_names = [];
foreach ($filtered_vehicles as $vehicle) {
    $filtered_vehicle_names[] = $vehicle['name'];
}

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 30;
$total_items = count($filtered_vehicles);
$total_pages = max(1, intval(ceil($total_items / $limit)));
$page = min($page, $total_pages);
$offset = ($page - 1) * $limit;
$paginated_vehicles = array_slice($filtered_vehicles, $offset, $limit);

$news_limit = 20;
$news_where = [];
$news_bind = [];
if ($news_search !== '') {
    $news_where[] = "(n.title LIKE ? OR n.summary LIKE ? OR n.body LIKE ?)";
    $news_like = '%' . $news_search . '%';
    $news_bind[] = $news_like;
    $news_bind[] = $news_like;
    $news_bind[] = $news_like;
}
if ($news_status !== '') {
    $news_where[] = "n.status = ?";
    $news_bind[] = $news_status;
}
$news_where_sql = $news_where ? ('WHERE ' . implode(' AND ', $news_where)) : '';
$news_total = 0;
$news_items = [];
$editing_news = null;
$editing_media = [];
$news_stats = ['published' => 0, 'draft' => 0, 'media' => 0];
try {
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM site_news n $news_where_sql");
    $count_stmt->execute($news_bind);
    $news_total = intval($count_stmt->fetchColumn());
    $news_total_pages = max(1, intval(ceil($news_total / $news_limit)));
    $news_page = min($news_page, $news_total_pages);
    $news_offset = ($news_page - 1) * $news_limit;
    $sql = "SELECT n.*, a.username AS author_name, COALESCE(mc.media_count, 0) AS media_count
            FROM site_news n
            LEFT JOIN accounts a ON a.id = n.author_account_id
            LEFT JOIN (
                SELECT news_id, COUNT(*) AS media_count
                FROM site_news_media
                GROUP BY news_id
            ) mc ON mc.news_id = n.id
            $news_where_sql
            ORDER BY n.is_pinned DESC, COALESCE(n.published_at, n.created_at) DESC, n.id DESC
            LIMIT $news_limit OFFSET $news_offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($news_bind);
    $news_items = $stmt->fetchAll();

    $news_stats['published'] = intval($pdo->query("SELECT COUNT(*) FROM site_news WHERE status = 'published'")->fetchColumn());
    $news_stats['draft'] = intval($pdo->query("SELECT COUNT(*) FROM site_news WHERE status = 'draft'")->fetchColumn());
    $news_stats['media'] = intval($pdo->query("SELECT COUNT(*) FROM site_news_media")->fetchColumn());

    if ($news_edit_id > 0) {
        $stmt = $pdo->prepare("SELECT n.*, a.username AS author_name FROM site_news n LEFT JOIN accounts a ON a.id = n.author_account_id WHERE n.id = ?");
        $stmt->execute([$news_edit_id]);
        $editing_news = $stmt->fetch() ?: null;
        if ($editing_news) {
            $stmt = $pdo->prepare("SELECT * FROM site_news_media WHERE news_id = ? ORDER BY sort_order ASC, id ASC");
            $stmt->execute([$news_edit_id]);
            $editing_media = $stmt->fetchAll();
        }
    }
} catch (Exception $e) {
    error_log("Admin news query: " . $e->getMessage());
    $news_total_pages = 1;
}

$dashboard_stats = [
    'accounts_total' => $total_accounts,
    'accounts_today' => 0,
    'accounts_week' => 0,
    'active_today' => 0,
    'bans_total' => 0,
    'bans_week' => 0,
    'reports_pending' => 0,
    'reports_open' => 0,
    'reports_resolved_week' => 0,
    'staff_total' => 0,
    'actions_today' => 0,
    'actions_week' => 0,
    'news_published' => $news_stats['published'],
];
$dashboard_daily = [];
for ($day_offset = 13; $day_offset >= 0; $day_offset--) {
    $day_key = date('Y-m-d', strtotime('-' . $day_offset . ' days'));
    $dashboard_daily[$day_key] = [
        'label' => date('d.m', strtotime($day_key)),
        'accounts' => 0,
        'actions' => 0,
    ];
}
$dashboard_recent_actions = [];
$dashboard_recent_reports = [];
$current_staff_stats = ['today' => 0, 'week' => 0, 'bans' => 0, 'reports' => 0];
$role_counts = array_fill_keys(array_keys(staff_role_definitions()), 0);

try {
    $dashboard_stats['accounts_today'] = intval($pdo->query("SELECT COUNT(*) FROM accounts WHERE created_at >= CURRENT_DATE")->fetchColumn());
    $dashboard_stats['accounts_week'] = intval($pdo->query("SELECT COUNT(*) FROM accounts WHERE created_at >= NOW() - INTERVAL '7 days'")->fetchColumn());
    $dashboard_stats['active_today'] = intval($pdo->query("SELECT COUNT(*) FROM accounts WHERE last_login >= CURRENT_DATE")->fetchColumn());
    $dashboard_stats['bans_total'] = intval($pdo->query("SELECT COUNT(*) FROM bans")->fetchColumn());
    $dashboard_stats['bans_week'] = intval($pdo->query("SELECT COUNT(*) FROM bans WHERE created_at >= NOW() - INTERVAL '7 days'")->fetchColumn());
    $dashboard_stats['reports_pending'] = intval($pdo->query("SELECT COUNT(*) FROM bug_reports WHERE is_approved = 0 AND status <> 'closed'")->fetchColumn());
    $dashboard_stats['reports_open'] = intval($pdo->query("SELECT COUNT(*) FROM bug_reports WHERE status IN ('open', 'in_progress')")->fetchColumn());
    $dashboard_stats['reports_resolved_week'] = intval($pdo->query("SELECT COUNT(*) FROM bug_reports WHERE status IN ('resolved', 'closed') AND updated_at >= NOW() - INTERVAL '7 days'")->fetchColumn());
    $dashboard_stats['staff_total'] = intval($pdo->query("SELECT COUNT(*) FROM accounts WHERE is_admin = 1 OR staff_role <> 'player'")->fetchColumn());
    $dashboard_stats['actions_today'] = intval($pdo->query("SELECT COUNT(*) FROM staff_action_log WHERE created_at >= CURRENT_DATE")->fetchColumn());
    $dashboard_stats['actions_week'] = intval($pdo->query("SELECT COUNT(*) FROM staff_action_log WHERE created_at >= NOW() - INTERVAL '7 days'")->fetchColumn());

    foreach ($pdo->query("SELECT created_at::date AS activity_day, COUNT(*) AS amount FROM accounts WHERE created_at >= CURRENT_DATE - INTERVAL '13 days' GROUP BY created_at::date")->fetchAll() as $row) {
        if (isset($dashboard_daily[$row['activity_day']])) {
            $dashboard_daily[$row['activity_day']]['accounts'] = intval($row['amount']);
        }
    }
    foreach ($pdo->query("SELECT created_at::date AS activity_day, COUNT(*) AS amount FROM staff_action_log WHERE created_at >= CURRENT_DATE - INTERVAL '13 days' GROUP BY created_at::date")->fetchAll() as $row) {
        if (isset($dashboard_daily[$row['activity_day']])) {
            $dashboard_daily[$row['activity_day']]['actions'] = intval($row['amount']);
        }
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff_action_log WHERE actor_account_id = ? AND created_at >= CURRENT_DATE");
    $stmt->execute([intval($_SESSION['user_id'])]);
    $current_staff_stats['today'] = intval($stmt->fetchColumn());
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff_action_log WHERE actor_account_id = ? AND created_at >= NOW() - INTERVAL '7 days'");
    $stmt->execute([intval($_SESSION['user_id'])]);
    $current_staff_stats['week'] = intval($stmt->fetchColumn());
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bans WHERE created_by = ?");
    $stmt->execute([intval($_SESSION['user_id'])]);
    $current_staff_stats['bans'] = intval($stmt->fetchColumn());
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff_action_log WHERE actor_account_id = ? AND action_key LIKE 'report.%'");
    $stmt->execute([intval($_SESSION['user_id'])]);
    $current_staff_stats['reports'] = intval($stmt->fetchColumn());

    $dashboard_recent_actions = $pdo->query("SELECT l.*, a.username AS actor_name FROM staff_action_log l LEFT JOIN accounts a ON a.id = l.actor_account_id ORDER BY l.created_at DESC, l.id DESC LIMIT 8")->fetchAll();
    $dashboard_recent_reports = $pdo->query("SELECT b.id, b.title, b.status, b.is_approved, b.created_at, a.username AS author_name FROM bug_reports b LEFT JOIN accounts a ON a.id = b.account_id WHERE b.status <> 'closed' AND (b.is_approved = 0 OR b.status IN ('open', 'in_progress')) ORDER BY b.is_approved ASC, b.created_at DESC LIMIT 6")->fetchAll();

    foreach ($pdo->query("SELECT is_admin, staff_role, COUNT(*) AS amount FROM accounts WHERE is_admin = 1 OR staff_role <> 'player' GROUP BY is_admin, staff_role")->fetchAll() as $row) {
        $role_key = normalize_staff_role($row['staff_role'] ?? '', intval($row['is_admin']) === 1);
        $role_counts[$role_key] = ($role_counts[$role_key] ?? 0) + intval($row['amount']);
    }
} catch (Exception $e) {
    error_log('Admin dashboard query: ' . $e->getMessage());
}

$report_search = trim($_GET['report_search'] ?? '');
$report_status = (string)($_GET['report_status'] ?? 'queue');
$report_page = max(1, intval($_GET['report_page'] ?? 1));
$report_limit = 30;
$report_total = 0;
$report_total_pages = 1;
if (!in_array($report_status, ['queue', 'open', 'in_progress', 'resolved', 'closed', 'all'], true)) {
    $report_status = 'queue';
}
$moderation_reports = [];
if (admin_can('reports.manage')) {
    try {
        $report_where = [];
        $report_bind = [];
        if ($report_status === 'queue') {
            $report_where[] = "b.status <> 'closed' AND (b.is_approved = 0 OR b.status IN ('open', 'in_progress'))";
        } elseif ($report_status !== 'all') {
            $report_where[] = 'b.status = ?';
            $report_bind[] = $report_status;
        }
        if ($report_search !== '') {
            $report_where[] = '(b.title LIKE ? OR b.description LIKE ? OR a.username LIKE ? OR b.id = ?)';
            $report_like = '%' . $report_search . '%';
            array_push($report_bind, $report_like, $report_like, $report_like, intval($report_search));
        }
        $report_where_sql = $report_where ? ('WHERE ' . implode(' AND ', $report_where)) : '';
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM bug_reports b LEFT JOIN accounts a ON a.id = b.account_id $report_where_sql");
        $count_stmt->execute($report_bind);
        $report_total = intval($count_stmt->fetchColumn());
        $report_total_pages = max(1, intval(ceil($report_total / $report_limit)));
        $report_page = min($report_page, $report_total_pages);
        $report_offset = ($report_page - 1) * $report_limit;
        $stmt = $pdo->prepare("SELECT b.*, a.username AS author_name, a.is_admin AS author_is_admin, a.staff_role AS author_staff_role, a.is_banned_reports, COALESCE(c.comment_count, 0) AS comment_count FROM bug_reports b LEFT JOIN accounts a ON a.id = b.account_id LEFT JOIN (SELECT bug_id, COUNT(*) AS comment_count FROM bug_comments GROUP BY bug_id) c ON c.bug_id = b.id $report_where_sql ORDER BY b.is_approved ASC, CASE b.status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'resolved' THEN 3 WHEN 'closed' THEN 4 ELSE 5 END, b.created_at DESC LIMIT $report_limit OFFSET $report_offset");
        $stmt->execute($report_bind);
        $moderation_reports = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log('Admin report queue query: ' . $e->getMessage());
    }
}

$staff_search = trim($_GET['staff_search'] ?? '');
$selected_staff_id = intval($_GET['staff_id'] ?? 0);
$staff_accounts = [];
$selected_staff_account = null;
$selected_staff_access = null;
if (admin_can('staff.view')) {
    try {
        $staff_where = "(is_admin = 1 OR staff_role <> 'player')";
        $staff_bind = [];
        if ($staff_search !== '') {
            $staff_where = "($staff_where OR id = ? OR username LIKE ? OR email LIKE ?)";
            $staff_like = '%' . $staff_search . '%';
            $staff_bind = [intval($staff_search), $staff_like, $staff_like];
        }
        $stmt = $pdo->prepare("SELECT a.id, a.username, a.email, a.is_admin, a.staff_role, a.last_login, a.created_at, dl.discord_username FROM accounts AS a LEFT JOIN account_discord_links AS dl ON dl.account_id = a.id WHERE $staff_where ORDER BY a.is_admin DESC, CASE a.staff_role WHEN 'admin' THEN 1 WHEN 'developer' THEN 2 WHEN 'orion_council_head' THEN 3 WHEN 'senior_moderator' THEN 4 WHEN 'moderator' THEN 5 WHEN 'content_maker' THEN 6 WHEN 'player' THEN 7 ELSE 8 END, a.username ASC LIMIT 80");
        $stmt->execute($staff_bind);
        $staff_accounts = $stmt->fetchAll();
        if ($selected_staff_id <= 0 && !empty($staff_accounts)) {
            $selected_staff_id = intval($staff_accounts[0]['id']);
        }
        if ($selected_staff_id > 0) {
            $stmt = $pdo->prepare("SELECT a.id, a.username, a.email, a.is_admin, a.staff_role, a.last_login, a.created_at, dl.discord_username FROM accounts AS a LEFT JOIN account_discord_links AS dl ON dl.account_id = a.id WHERE a.id = ?");
            $stmt->execute([$selected_staff_id]);
            $selected_staff_account = $stmt->fetch() ?: null;
            $selected_staff_access = $selected_staff_account ? staff_access_for_account($pdo, $selected_staff_id) : null;
        }
    } catch (Exception $e) {
        error_log('Admin staff query: ' . $e->getMessage());
    }
}

$audit_search = trim($_GET['audit_search'] ?? '');
$audit_items = [];
if (admin_can('audit.view')) {
    try {
        $audit_where = '';
        $audit_bind = [];
        if ($audit_search !== '') {
            $audit_where = 'WHERE l.summary LIKE ? OR l.action_key LIKE ? OR a.username LIKE ? OR l.target_id LIKE ?';
            $audit_like = '%' . $audit_search . '%';
            $audit_bind = [$audit_like, $audit_like, $audit_like, $audit_like];
        }
        $stmt = $pdo->prepare("SELECT l.*, a.username AS actor_name FROM staff_action_log l LEFT JOIN accounts a ON a.id = l.actor_account_id $audit_where ORDER BY l.created_at DESC, l.id DESC LIMIT 200");
        $stmt->execute($audit_bind);
        $audit_items = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log('Admin audit query: ' . $e->getMessage());
    }
}

$csrf_token = $_SESSION['csrf_token'];
$page_title = 'Центр управления — сервер Project Orion 0.8.2';
$page_description = 'Центр управления и модерации сервера Project Orion 0.8.2.';
$page_path = 'admin.php';
$seo_index = false;
$active_page = 'admin';
$body_class = 'is-admin';
$page_styles = ['admin.css?v=20'];
$page_scripts = ['js/admin.js?v=5'];
$admin_tab_titles = [
    'dashboard' => 'Оперативный дашборд',
    'reports' => 'Очередь репортов',
    'vehicles' => 'Контроль техники',
    'news' => 'Новости сайта',
    'updates' => 'История обновлений',
    'server' => 'Статус сервера',
    'users' => 'Пользователи',
    'bans' => 'Баны',
    'downloads' => 'Загрузки',
    'contracts' => 'Заявки и контракты',
    'implement' => 'РЕАЛИЗОВАТЬ',
    'staff' => 'Команда и доступы',
    'audit' => 'Журнал действий',
];
$admin_tab_title = $admin_tab_titles[$tab] ?? $admin_tab_titles['dashboard'];
require __DIR__ . '/includes/header.php';
?>

<script>
window.OrionAdminConfig = <?php echo json_encode([
    'csrfToken' => $csrf_token,
    'selectedAccountId' => intval($selected_account_id),
    'filteredVehicleNames' => array_values($filtered_vehicle_names),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>


<main class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-heading">
            <span class="site-brand-mark">O</span>
            <div><strong>Orion Control</strong><small><?php echo h(admin_i18n_text($current_staff_access['role_info']['short_label'])); ?></small></div>
            <button class="admin-sidebar-dismiss" type="button" data-admin-sidebar-dismiss aria-label="Закрыть меню">&times;</button>
        </div>
        <nav class="admin-nav" aria-label="Разделы админ-панели">
            <span class="admin-nav-label">Центр</span>
            <a href="admin.php?tab=dashboard" class="admin-nav-link<?php echo $tab === 'dashboard' ? ' active' : ''; ?>">Дашборд</a>
            <?php if (admin_can('reports.manage') || admin_can('users.view') || admin_can('bans.manage') || admin_can('bans.unban')): ?>
                <span class="admin-nav-label">Модерация</span>
            <?php endif; ?>
            <?php if (admin_can('reports.manage')): ?><a href="admin.php?tab=reports" class="admin-nav-link<?php echo $tab === 'reports' ? ' active' : ''; ?>">Репорты<?php if ($dashboard_stats['reports_pending'] > 0): ?><span class="admin-nav-count"><?php echo $dashboard_stats['reports_pending']; ?></span><?php endif; ?></a><?php endif; ?>
            <?php if (admin_can('users.view')): ?><a href="admin.php?tab=users&amp;account_id=<?php echo intval($selected_account_id); ?>&amp;account_search=<?php echo h($account_search); ?>" class="admin-nav-link<?php echo $tab === 'users' ? ' active' : ''; ?>">Пользователи</a><?php endif; ?>
            <?php if (admin_can('bans.manage') || admin_can('bans.unban')): ?><a href="admin.php?tab=bans" class="admin-nav-link<?php echo $tab === 'bans' ? ' active' : ''; ?>">Блокировки</a><?php endif; ?>
            <?php if (admin_can('news.manage') || admin_can('updates.manage') || admin_can('server.manage') || admin_can('vehicles.manage') || admin_can('downloads.manage')): ?>
                <span class="admin-nav-label">Проект</span>
            <?php endif; ?>
            <?php if (admin_can('news.manage')): ?><a href="admin.php?tab=news" class="admin-nav-link<?php echo $tab === 'news' ? ' active' : ''; ?>">Новости сайта</a><?php endif; ?>
            <?php if (admin_can('updates.manage')): ?><a href="admin.php?tab=updates" class="admin-nav-link<?php echo $tab === 'updates' ? ' active' : ''; ?>">История обновлений</a><?php endif; ?>
            <?php if (contract_is_owner_admin($current_staff_access)): ?><a href="admin.php?tab=server" class="admin-nav-link<?php echo $tab === 'server' ? ' active' : ''; ?>">Статус сервера</a><?php endif; ?>
            <?php if (admin_can('vehicles.manage')): ?>
            <a href="admin.php?tab=vehicles&amp;account_id=<?php echo intval($selected_account_id); ?>&amp;account_search=<?php echo h($account_search); ?>" class="admin-nav-link<?php echo $tab === 'vehicles' ? ' active' : ''; ?>">Контроль техники</a>
            <?php endif; ?>
            <?php if (admin_can('downloads.manage')): ?><a href="admin.php?tab=downloads" class="admin-nav-link<?php echo $tab === 'downloads' ? ' active' : ''; ?>">Загрузки</a><?php endif; ?>
            <?php if (contract_is_owner_admin($current_staff_access) || admin_can('staff.view') || admin_can('audit.view') || admin_can('council.participate')): ?>
                <span class="admin-nav-label">Команда</span>
            <?php endif; ?>
            <?php if (admin_can('council.participate')): ?><a href="gso.php" class="admin-nav-link">ГСО <span aria-hidden="true">↗</span></a><?php endif; ?>
            <?php if (contract_is_owner_admin($current_staff_access)): ?><a href="admin.php?tab=implement" class="admin-nav-link admin-nav-link--implement<?php echo $tab === 'implement' ? ' active' : ''; ?>">РЕАЛИЗОВАТЬ<?php if (count($gso_implementation_items) > 0): ?><span class="admin-nav-count"><?php echo count($gso_implementation_items); ?></span><?php endif; ?></a><?php endif; ?>
            <?php if (contract_is_owner_admin($current_staff_access)): ?><a href="admin.php?tab=contracts" class="admin-nav-link<?php echo $tab === 'contracts' ? ' active' : ''; ?>">Заявки и контракты<?php if ($contract_admin_data['stats']['pending'] > 0): ?><span class="admin-nav-count"><?php echo intval($contract_admin_data['stats']['pending']); ?></span><?php endif; ?></a><?php endif; ?>
            <?php if (admin_can('staff.view')): ?><a href="admin.php?tab=staff" class="admin-nav-link<?php echo $tab === 'staff' ? ' active' : ''; ?>">Роли и доступы</a><?php endif; ?>
            <?php if (admin_can('audit.view')): ?><a href="admin.php?tab=audit" class="admin-nav-link<?php echo $tab === 'audit' ? ' active' : ''; ?>">Журнал действий</a><?php endif; ?>
        </nav>
        <a class="admin-back-link" href="index.php">← Вернуться на сайт</a>
    </aside>
    <button class="admin-sidebar-backdrop" type="button" data-admin-sidebar-close aria-label="Закрыть меню" aria-hidden="true" tabindex="-1"></button>
    <div class="admin-main">
        <header class="admin-topbar">
            <button type="button" class="admin-sidebar-toggle" data-admin-sidebar-toggle aria-label="Открыть меню" aria-expanded="false" aria-controls="adminSidebar">Меню</button>
            <div class="admin-topbar-heading"><p class="eyebrow"><?php echo h(admin_i18n_text($current_staff_access['role_info']['label'])); ?></p><h1><?php echo h($admin_tab_title); ?></h1></div>
            <div class="admin-topbar-actions">
                <?php echo staff_notifications_html($pdo, 8); ?>
                <?php echo i18n_switcher_html('admin'); ?>
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="Включить светлую тему" aria-pressed="true" title="Включить светлую тему">
                    <svg class="theme-icon theme-icon--sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3.5"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"></path></svg>
                    <svg class="theme-icon theme-icon--moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.4A8.5 8.5 0 0 1 8.6 3.8 8.5 8.5 0 1 0 20.2 15.4Z"></path></svg>
                </button>
                <span class="staff-role-badge staff-role-badge--<?php echo h($current_staff_access['role_info']['tone']); ?>"><?php echo h(admin_i18n_text($current_staff_access['role_info']['short_label'])); ?></span>
            </div>
        </header>
        <div id="adminNotice" class="notice-line alert" role="status" aria-live="polite"></div>
        <div class="admin-content">
            <?php if ($admin_flash): ?>
                <div class="alert <?php echo $admin_flash['type'] === 'danger' ? 'alert-danger' : 'alert-success'; ?>">
                    <?php echo h($admin_flash['message'] ?? ''); ?>
                </div>
            <?php endif; ?>

            <?php if ($tab === 'dashboard'): ?>
            <?php
            $dashboard_chart_max = 1;
            foreach ($dashboard_daily as $day_data) {
                $dashboard_chart_max = max($dashboard_chart_max, intval($day_data['accounts']), intval($day_data['actions']));
            }
            $current_permission_count = count(array_filter($current_staff_access['permissions']));
            ?>
            <section class="admin-section admin-section--dashboard">
                <div class="admin-hero-strip admin-command-hero">
                    <div>
                        <p class="eyebrow">ORION COMMAND CENTER</p>
                        <div class="admin-hero-title">Добро пожаловать, <span class="notranslate" translate="no"><?php echo h($current_staff_access['username']); ?></span></div>
                        <div class="admin-hero-sub"><?php echo h(admin_i18n_text($current_staff_access['role_info']['description'])); ?> Доступно разрешений: <?php echo $current_permission_count; ?>.</div>
                    </div>
                    <div class="admin-live"><span></span> система работает</div>
                </div>

                <div class="dashboard-kpi-grid">
                    <article class="dashboard-kpi dashboard-kpi--accent">
                        <span class="dashboard-kpi-label">Аккаунты</span>
                        <strong><?php echo number_format($dashboard_stats['accounts_total'], 0, '.', ' '); ?></strong>
                        <small>+<?php echo $dashboard_stats['accounts_week']; ?> за 7 дней</small>
                    </article>
                    <article class="dashboard-kpi">
                        <span class="dashboard-kpi-label">Онлайн-активность</span>
                        <strong><?php echo $dashboard_stats['active_today']; ?></strong>
                        <small>входили сегодня</small>
                    </article>
                    <article class="dashboard-kpi dashboard-kpi--warning">
                        <span class="dashboard-kpi-label">Очередь репортов</span>
                        <strong><?php echo $dashboard_stats['reports_pending']; ?></strong>
                        <small><?php echo $dashboard_stats['reports_open']; ?> открыто / в работе</small>
                    </article>
                    <article class="dashboard-kpi">
                        <span class="dashboard-kpi-label">Действия команды</span>
                        <strong><?php echo $dashboard_stats['actions_today']; ?></strong>
                        <small><?php echo $dashboard_stats['actions_week']; ?> за 7 дней</small>
                    </article>
                    <article class="dashboard-kpi dashboard-kpi--danger">
                        <span class="dashboard-kpi-label">Активные блокировки</span>
                        <strong><?php echo $dashboard_stats['bans_total']; ?></strong>
                        <small>+<?php echo $dashboard_stats['bans_week']; ?> за неделю</small>
                    </article>
                    <article class="dashboard-kpi dashboard-kpi--success">
                        <span class="dashboard-kpi-label">Закрыто репортов</span>
                        <strong><?php echo $dashboard_stats['reports_resolved_week']; ?></strong>
                        <small>за последние 7 дней</small>
                    </article>
                </div>

                <div class="dashboard-grid dashboard-grid--overview">
                    <article class="card admin-card dashboard-activity-card">
                        <div class="card-header">
                            <div><p class="eyebrow">14 ДНЕЙ</p><h2 class="card-title">Пульс проекта</h2></div>
                            <div class="chart-legend"><span class="chart-legend-item chart-legend-item--accounts">Регистрации</span><span class="chart-legend-item chart-legend-item--actions">Действия</span></div>
                        </div>
                        <div class="card-body">
                            <div class="activity-chart" data-activity-chart aria-label="Регистрации и действия команды за 14 дней">
                                <?php foreach ($dashboard_daily as $day_data): ?>
                                    <?php
                                    $accounts_height = max(4, round((intval($day_data['accounts']) / $dashboard_chart_max) * 100));
                                    $actions_height = max(4, round((intval($day_data['actions']) / $dashboard_chart_max) * 100));
                                    $chart_day_aria = $day_data['label'] . ': регистраций ' . intval($day_data['accounts']) . ', действий ' . intval($day_data['actions']);
                                    $chart_day_aria_en = $day_data['label'] . ': ' . intval($day_data['accounts']) . ' registrations, ' . intval($day_data['actions']) . ' actions';
                                    ?>
                                    <div
                                        class="activity-chart-day"
                                        tabindex="0"
                                        data-chart-date="<?php echo h($day_data['label']); ?>"
                                        data-chart-accounts="<?php echo intval($day_data['accounts']); ?>"
                                        data-chart-actions="<?php echo intval($day_data['actions']); ?>"
                                        aria-label="<?php echo h(admin_i18n_text($chart_day_aria, $chart_day_aria_en)); ?>"
                                    >
                                        <div class="activity-chart-bars">
                                            <span class="activity-bar activity-bar--accounts" style="height: <?php echo $accounts_height; ?>%"></span>
                                            <span class="activity-bar activity-bar--actions" style="height: <?php echo $actions_height; ?>%"></span>
                                        </div>
                                        <small><?php echo h($day_data['label']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                                <div class="activity-chart-tooltip" data-chart-tooltip role="tooltip" aria-hidden="true">
                                    <div class="activity-chart-tooltip-head">
                                        <span>Данные за день</span>
                                        <strong data-chart-tooltip-date>—</strong>
                                    </div>
                                    <div class="activity-chart-tooltip-row activity-chart-tooltip-row--accounts">
                                        <span><i></i>Регистрации</span>
                                        <strong data-chart-tooltip-accounts>0</strong>
                                    </div>
                                    <div class="activity-chart-tooltip-row activity-chart-tooltip-row--actions">
                                        <span><i></i>Действия команды</span>
                                        <strong data-chart-tooltip-actions>0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="card admin-card personal-performance-card">
                        <div class="card-header"><div><p class="eyebrow">ЛИЧНЫЙ ВКЛАД</p><h2 class="card-title">Ваша смена</h2></div></div>
                        <div class="card-body">
                            <div class="personal-stat-list">
                                <div><span>Действий сегодня</span><strong><?php echo $current_staff_stats['today']; ?></strong></div>
                                <div><span>Действий за неделю</span><strong><?php echo $current_staff_stats['week']; ?></strong></div>
                                <div><span>Выдано банов</span><strong><?php echo $current_staff_stats['bans']; ?></strong></div>
                                <div><span>Операций с репортами</span><strong><?php echo $current_staff_stats['reports']; ?></strong></div>
                            </div>
                            <div class="performance-footer">
                                <span class="staff-role-badge staff-role-badge--<?php echo h($current_staff_access['role_info']['tone']); ?>"><?php echo h(admin_i18n_text($current_staff_access['role_info']['label'])); ?></span>
                                <small><?php echo $current_permission_count; ?> активных разрешений</small>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="dashboard-grid">
                    <?php if (admin_can('reports.manage')): ?>
                    <article class="card admin-card dashboard-feed-card">
                        <div class="card-header">
                            <div><p class="eyebrow">ОЧЕРЕДЬ</p><h2 class="card-title">Требуют внимания</h2></div>
                            <a class="btn btn-secondary" href="admin.php?tab=reports">Все репорты</a>
                        </div>
                        <div class="card-body dashboard-feed">
                            <?php if (empty($dashboard_recent_reports)): ?>
                                <div class="admin-empty">Очередь пуста — всё обработано.</div>
                            <?php else: ?>
                                <?php foreach ($dashboard_recent_reports as $report): ?>
                                    <a class="dashboard-feed-item" href="bug_view.php?id=<?php echo intval($report['id']); ?>">
                                        <span class="dashboard-feed-icon">#<?php echo intval($report['id']); ?></span>
                                        <span><strong class="notranslate" translate="no"><?php echo h($report['title']); ?></strong><small><?php if (!empty($report['author_name'])): ?><span class="notranslate" translate="no"><?php echo h($report['author_name']); ?></span><?php else: ?><?php echo h(admin_i18n_text('Неизвестно')); ?><?php endif; ?> · <?php echo h($report['created_at']); ?></small></span>
                                        <span class="pill <?php echo intval($report['is_approved']) === 1 ? 'pill-neutral' : 'pill-off'; ?>"><?php echo intval($report['is_approved']) === 1 ? h(admin_report_status_label($report['status'])) : 'проверка'; ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endif; ?>

                    <article class="card admin-card dashboard-feed-card">
                        <div class="card-header">
                            <div><p class="eyebrow">КОМАНДА</p><h2 class="card-title"><?php echo admin_can('audit.view') ? 'Последние действия' : 'Ваши возможности'; ?></h2></div>
                            <?php if (admin_can('audit.view')): ?><a class="btn btn-secondary" href="admin.php?tab=audit">Открыть аудит</a><?php endif; ?>
                        </div>
                        <div class="card-body dashboard-feed">
                            <?php if (admin_can('audit.view')): ?>
                                <?php if (empty($dashboard_recent_actions)): ?><div class="admin-empty">Журнал пока пуст.</div><?php endif; ?>
                                <?php foreach ($dashboard_recent_actions as $action_item): ?>
                                    <div class="dashboard-feed-item">
                                        <span class="dashboard-feed-icon dashboard-feed-icon--action">•</span>
                                        <span><strong><?php echo h(admin_i18n_text(staff_action_label($action_item['action_key']))); ?></strong><small><?php if (!empty($action_item['actor_name'])): ?><span class="notranslate" translate="no"><?php echo h($action_item['actor_name']); ?></span><?php else: ?><?php echo h(admin_i18n_text('Система')); ?><?php endif; ?> · <?php echo h($action_item['created_at']); ?></small></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($current_staff_access['permissions'] as $permission_key => $allowed): ?>
                                    <?php if (!$allowed): continue; endif; ?>
                                    <div class="permission-summary-row"><span><?php echo h(admin_i18n_text(staff_permission_catalog()[$permission_key]['label'])); ?></span><span class="pill pill-on">разрешено</span></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            </section>

            <?php elseif ($tab === 'vehicles'): ?>
            <section class="admin-section admin-section--vehicles">
            <div class="admin-hero-strip">
                <div>
                    <div class="admin-hero-title text-base md:text-xl">Контроль техники</div>
                    <div class="admin-hero-sub">Глобальное отключение работает для всех, а персональное правило может разрешить или заблокировать танк отдельному аккаунту.</div>
                </div>
                <div class="admin-live">live db</div>
            </div>

            <div class="admin-metrics">
                <div class="metric">
                    <div class="metric-value"><?php echo $total_accounts; ?></div>
                    <div class="metric-label">аккаунтов</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?php echo count($vehicles); ?></div>
                    <div class="metric-label">танков в JSON</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?php echo count($disabled_tanks); ?></div>
                    <div class="metric-label">глобально выключено</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?php echo $override_count; ?></div>
                    <div class="metric-label">персональных правил</div>
                </div>
            </div>

            <div class="admin-workspace">
                <aside class="admin-accounts-panel">
                    <div class="admin-stack">
            <div class="card admin-card">
                <div class="card-header">
                    <div class="card-title text-sm md:text-lg">Игроки</div>
                </div>
                <div class="card-body">
                    <form action="admin.php" method="GET" class="search-box">
                        <input type="hidden" name="search" value="<?php echo h($search); ?>">
                        <input type="hidden" name="nation" value="<?php echo h($filter_nation); ?>">
                        <input type="hidden" name="class" value="<?php echo h($filter_class); ?>">
                        <input type="hidden" name="tier" value="<?php echo h($filter_tier); ?>">
                        <input type="hidden" name="status" value="<?php echo h($filter_status); ?>">
                         <input type="text" name="account_search" class="form-control search-input notranslate" translate="no" placeholder="<?php echo h(admin_i18n_text('ID или ник', 'ID or nickname')); ?>" value="<?php echo h($account_search); ?>">
                        <button type="submit" class="btn btn-secondary">Найти</button>
                    </form>
                    <div class="account-list">
                        <?php foreach ($accounts as $account): ?>
                            <?php
                            $query = $_GET;
                            $query['account_id'] = intval($account['id']);
                            $query['page'] = 1;
                            $url = 'admin.php?' . http_build_query($query);
                            $account_role = normalize_staff_role($account['staff_role'] ?? '', intval($account['is_admin']) === 1);
                            ?>
                            <a class="account-link <?php echo intval($account['id']) === $selected_account_id ? 'active' : ''; ?>" href="<?php echo h($url); ?>">
                                #<?php echo intval($account['id']); ?> <span class="notranslate" translate="no"><?php echo h($account['username']); ?></span>
                                <span class="account-meta"><?php echo h(admin_i18n_text(staff_role_info($account_role)['short_label'])); ?> · <span class="notranslate" translate="no"><?php echo h($account['last_login'] ?? ''); ?></span></span>
                            </a>
                        <?php endforeach; ?>
                        <?php if (empty($accounts)): ?>
                            <span class="muted">Игроки не найдены.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card admin-card">
                <div class="card-header">
                    <div class="card-title text-sm md:text-lg">Аккаунт</div>
                </div>
                <div class="card-body">
                    <?php if ($selected_account): ?>
                        <?php
                        $selected_account_access = staff_access_for_account($pdo, intval($selected_account['id']));
                        $can_act_on_selected_account = staff_can_act_on_account($current_staff_access, $selected_account_access);
                        $can_edit_selected_account = $can_act_on_selected_account && admin_can('users.edit');
                        ?>
                        <form onsubmit="return saveAccount(this);" class="admin-form-grid">
                            <input type="hidden" name="account_id" value="<?php echo intval($selected_account['id']); ?>">
                            <div class="form-group full">
                                <label>Никнейм</label>
                                <div class="admin-inline-field">
                                    <span class="admin-inline-value notranslate" translate="no"><?php echo h($selected_account['username']); ?></span>
                                    <?php if ($can_edit_selected_account): ?><button type="button" class="btn btn-secondary" onclick="setUsername(<?php echo intval($selected_account['id']); ?>, <?php echo h(json_encode($selected_account['username'])); ?>)">Сменить ник</button><?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Кредиты</label>
                                <input type="number" name="credits" class="form-control" min="0" value="<?php echo intval($selected_account['credits']); ?>" <?php echo $can_edit_selected_account ? '' : 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label>Золото</label>
                                <input type="number" name="gold" class="form-control" min="0" value="<?php echo intval($selected_account['gold']); ?>" <?php echo $can_edit_selected_account ? '' : 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label>Свободный опыт</label>
                                <input type="number" name="free_xp" class="form-control" min="0" value="<?php echo intval($selected_account['free_xp']); ?>" <?php echo $can_edit_selected_account ? '' : 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label>Слоты</label>
                                <input type="number" name="slots" class="form-control" min="1" value="<?php echo intval($selected_account['slots']); ?>" <?php echo $can_edit_selected_account ? '' : 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label>Казарма</label>
                                <input type="number" name="berths" class="form-control" min="0" value="<?php echo intval($selected_account['berths']); ?>" <?php echo $can_edit_selected_account ? '' : 'disabled'; ?>>
                            </div>
                            <div class="form-group">
                                <label>Роль</label>
                                <div class="admin-inline-value"><?php echo h(admin_i18n_text($selected_account_access['role_info']['label'])); ?></div>
                            </div>
                            <div class="full table-actions">
                                <?php if ($can_edit_selected_account): ?><button type="submit" class="btn btn-primary">Сохранить</button><?php endif; ?>
                                <?php if ($can_act_on_selected_account): ?><button type="button" class="btn btn-secondary" onclick="resetOverrides()">Сбросить персональные правила</button><?php endif; ?>
                                <?php if (!$can_act_on_selected_account): ?><span class="admin-protected-note">Аккаунт защищён иерархией.</span><?php endif; ?>
                            </div>
                        </form>
                    <?php else: ?>
                        <span class="muted">Выбери игрока для персонального управления.</span>
                    <?php endif; ?>
                </div>
            </div>
                    </div>
                </aside>
                <div class="admin-workspace-main admin-stack">
            <div class="card admin-card">
                <div class="card-header">
                    <div class="card-title text-sm md:text-lg">Доступ к технике</div>
                    <div class="muted">Событий: <?php echo $event_count; ?></div>
                </div>
                <div class="card-body">
                    <form action="admin.php" method="GET" class="admin-toolbar">
                        <input type="hidden" name="tab" value="vehicles">
                        <input type="hidden" name="account_id" value="<?php echo intval($selected_account_id); ?>">
                        <input type="hidden" name="account_search" value="<?php echo h($account_search); ?>">
                         <input type="text" name="search" class="form-control search-input notranslate" translate="no" placeholder="<?php echo h(admin_i18n_text('Поиск танка', 'Search tanks')); ?>" value="<?php echo h($search); ?>">
                        <select name="nation" class="form-control">
                            <option value="">Все нации</option>
                            <?php foreach (array_keys($nations) as $nation): ?>
                                <option value="<?php echo h($nation); ?>" <?php echo $filter_nation === $nation ? 'selected' : ''; ?>><?php echo h(nation_label($nation)); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="class" class="form-control">
                            <option value="">Все классы</option>
                            <?php foreach (array_keys($classes) as $class): ?>
                                <option value="<?php echo h($class); ?>" <?php echo $filter_class === $class ? 'selected' : ''; ?>><?php echo h(class_label($class)); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="tier" class="form-control">
                            <option value="">Все уровни</option>
                            <?php for ($tier = 1; $tier <= 10; $tier++): ?>
                                <option value="<?php echo $tier; ?>" <?php echo $filter_tier === (string)$tier ? 'selected' : ''; ?>><?php echo $tier; ?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="status" class="form-control">
                            <option value="">Все статусы</option>
                            <option value="global_enabled" <?php echo $filter_status === 'global_enabled' ? 'selected' : ''; ?>>Глобально включены</option>
                            <option value="global_disabled" <?php echo $filter_status === 'global_disabled' ? 'selected' : ''; ?>>Глобально выключены</option>
                            <option value="effective_enabled" <?php echo $filter_status === 'effective_enabled' ? 'selected' : ''; ?>>Доступны игроку</option>
                            <option value="effective_disabled" <?php echo $filter_status === 'effective_disabled' ? 'selected' : ''; ?>>Закрыты игроку</option>
                            <option value="overridden" <?php echo $filter_status === 'overridden' ? 'selected' : ''; ?>>Есть персональное правило</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Фильтр</button>
                        <a href="admin.php?tab=vehicles&amp;account_id=<?php echo intval($selected_account_id); ?>" class="btn btn-secondary">Сбросить</a>
                    </form>

                    <div class="bulk-panel">
                        <div>
                            <div class="bulk-title">Массовые действия по фильтру</div>
                            <div class="bulk-sub">Сейчас в фильтре: <?php echo $total_items; ?> танков. Действия применяются ко всем найденным, не только к этой странице.</div>
                        </div>
                        <div class="bulk-action-groups">
                            <div class="bulk-action-group">
                                <span class="bulk-action-label">Для всех</span>
                                <div class="bulk-actions">
                                    <button type="button" class="btn btn-success" onclick="bulkGlobal(true)">Включить</button>
                                    <button type="button" class="btn btn-danger" onclick="bulkGlobal(false)">Выключить</button>
                                </div>
                            </div>
                            <?php if ($selected_account && $can_act_on_selected_account): ?><div class="bulk-action-group">
                                <span class="bulk-action-label">Для игрока</span>
                                <div class="bulk-actions">
                                    <button type="button" class="btn btn-secondary" onclick="bulkPlayer('inherit')">Как глобально</button>
                                    <button type="button" class="btn btn-success" onclick="bulkPlayer('enabled')">Включить</button>
                                    <button type="button" class="btn btn-danger" onclick="bulkPlayer('disabled')">Выключить</button>
                                </div>
                            </div><?php elseif ($selected_account): ?><span class="admin-protected-note">Персональные действия недоступны для защищённого аккаунта.</span><?php endif; ?>
                        </div>
                    </div>

                    <div class="bulk-panel">
                        <div>
                            <div class="bulk-title">Быстрое восстановление</div>
                            <div class="bulk-sub">Включает все танки глобально, но не удаляет персональные правила аккаунтов.</div>
                        </div>
                        <div class="bulk-actions">
                            <button type="button" class="btn btn-secondary" onclick="enableAllGlobal()">Включить все глобально</button>
                        </div>
                    </div>

                    <div class="admin-table-wrap">
                        <table class="tanks-table admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Танк</th>
                                    <th>Нация</th>
                                    <th>Класс</th>
                                    <th>Уровень</th>
                                    <th>Для всех</th>
                                    <th>Для игрока</th>
                                    <th>Фактически</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paginated_vehicles as $vehicle): ?>
                                    <?php
                                    $name = $vehicle['name'] ?? '';
                                    $global_enabled = $vehicle['global_enabled'];
                                    $effective_enabled = $vehicle['effective_enabled'];
                                    $override_mode = $vehicle['override_mode'];
                                    ?>
                                    <tr data-vehicle-name="<?php echo h($name); ?>" data-global-enabled="<?php echo $global_enabled ? '1' : '0'; ?>" data-override-mode="<?php echo h($override_mode); ?>">
                                        <td><?php echo intval($vehicle['inv_id']); ?></td>
                                        <td class="tank-name notranslate" translate="no"><?php echo h($name); ?></td>
                                        <td><span class="badge badge-<?php echo h($vehicle['nation'] ?? 'ussr'); ?>"><?php echo h(nation_label($vehicle['nation'] ?? '')); ?></span></td>
                                        <td><?php echo h(class_label($vehicle['vehicleClass'] ?? '')); ?></td>
                                        <td class="admin-table-number"><?php echo intval($vehicle['level_calculated']); ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <span class="js-global-status"><?php echo $global_enabled ? '<span class="pill pill-on">Включено</span>' : '<span class="pill pill-off">Выключено</span>'; ?></span>
                                                <label class="switch">
                                                    <input type="checkbox" <?php echo $global_enabled ? 'checked' : ''; ?> onchange="toggleGlobal(this)">
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($selected_account && $can_act_on_selected_account): ?>
                                                <select class="form-control mini-select js-player-mode" onchange="setPlayerMode(this)">
                                                    <option value="inherit" <?php echo $override_mode === 'inherit' ? 'selected' : ''; ?>>Как глобально</option>
                                                    <option value="enabled" <?php echo $override_mode === 'enabled' ? 'selected' : ''; ?>>Включить</option>
                                                    <option value="disabled" <?php echo $override_mode === 'disabled' ? 'selected' : ''; ?>>Выключить</option>
                                                </select>
                                            <?php elseif ($selected_account): ?>
                                                <span class="pill pill-neutral">защищён</span>
                                            <?php else: ?>
                                                <span class="pill pill-neutral">без игрока</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="js-effective-status"><?php echo $effective_enabled ? '<span class="pill pill-on">Включено</span>' : '<span class="pill pill-off">Выключено</span>'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($paginated_vehicles)): ?>
                                    <tr>
                                        <td colspan="8" class="admin-empty">Танков по этим фильтрам нет.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php
                            $start = max(1, $page - 4);
                            $end = min($total_pages, $page + 4);
                            for ($i = $start; $i <= $end; $i++):
                                $query_params = $_GET;
                                $query_params['tab'] = 'vehicles';
                                $query_params['page'] = $i;
                                $link = 'admin.php?' . http_build_query($query_params);
                            ?>
                                <a href="<?php echo h($link); ?>" class="pagination-item <?php echo $page === $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
                </div>
            </div>
            </section>
            <?php elseif ($tab === 'reports'): ?>
            <section class="admin-section admin-section--reports">
                <div class="admin-hero-strip">
                    <div>
                        <div class="admin-hero-title">Очередь модерации</div>
                        <div class="admin-hero-sub">Проверяйте новые обращения, меняйте этап работы и ограничивайте спамеров. Все изменения попадают в аудит.</div>
                    </div>
                    <div class="report-hero-actions">
                        <div class="admin-live"><?php echo $dashboard_stats['reports_pending']; ?> ждут проверки</div>
                        <?php if (contract_is_owner_admin($current_staff_access)): ?>
                            <form method="POST" action="admin.php?tab=reports" onsubmit="return confirm(<?php echo h(admin_js_text('Закрыть абсолютно все репорты? Статус каждого незакрытого репорта изменится на «closed».', 'Close all reports? The status of every open report will change to "closed".')); ?>);">
                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                <input type="hidden" name="action" value="close_all_bug_reports">
                                <button type="submit" class="btn btn-danger">Закрыть все репорты</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-metrics">
                    <div class="metric"><div class="metric-value"><?php echo $dashboard_stats['reports_pending']; ?></div><div class="metric-label">Не проверено</div></div>
                    <div class="metric"><div class="metric-value"><?php echo $dashboard_stats['reports_open']; ?></div><div class="metric-label">Открыто / в работе</div></div>
                    <div class="metric"><div class="metric-value"><?php echo $dashboard_stats['reports_resolved_week']; ?></div><div class="metric-label">Закрыто за неделю</div></div>
                    <div class="metric"><div class="metric-value"><?php echo $current_staff_stats['reports']; ?></div><div class="metric-label">Ваших операций</div></div>
                </div>

                <div class="card admin-card">
                    <div class="card-header"><div><p class="eyebrow">ФИЛЬТРЫ</p><h2 class="card-title">Входящие обращения</h2></div><span class="pill pill-neutral"><?php echo count($moderation_reports); ?> из <?php echo $report_total; ?></span></div>
                    <div class="card-body">
                        <form action="admin.php" method="GET" class="admin-toolbar">
                            <input type="hidden" name="tab" value="reports">
                            <input type="hidden" name="report_page" value="1">
                             <input class="form-control admin-control-wide notranslate" translate="no" type="search" name="report_search" value="<?php echo h($report_search); ?>" placeholder="<?php echo h(admin_i18n_text('ID, заголовок, текст или автор', 'ID, title, text, or author')); ?>">
                            <select class="form-control" name="report_status">
                                <option value="queue" <?php echo $report_status === 'queue' ? 'selected' : ''; ?>>Требуют внимания</option>
                                <option value="open" <?php echo $report_status === 'open' ? 'selected' : ''; ?>>Открытые</option>
                                <option value="in_progress" <?php echo $report_status === 'in_progress' ? 'selected' : ''; ?>>В работе</option>
                                <option value="resolved" <?php echo $report_status === 'resolved' ? 'selected' : ''; ?>>Исправленные</option>
                                <option value="closed" <?php echo $report_status === 'closed' ? 'selected' : ''; ?>>Закрытые</option>
                                <option value="all" <?php echo $report_status === 'all' ? 'selected' : ''; ?>>Все</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Показать</button>
                            <a href="admin.php?tab=reports" class="btn btn-secondary">Сбросить</a>
                        </form>
                    </div>
                </div>

                <div class="report-moderation-list">
                    <?php if (empty($moderation_reports)): ?>
                        <div class="card admin-card"><div class="card-body admin-empty">По выбранным фильтрам репортов нет.</div></div>
                    <?php endif; ?>
                    <?php foreach ($moderation_reports as $report): ?>
                        <?php
                        $report_author_role = normalize_staff_role($report['author_staff_role'] ?? '', intval($report['author_is_admin']) === 1);
                        $report_author_rank = intval(staff_role_info($report_author_role)['rank']);
                        $can_restrict_report_author = admin_can('users.edit')
                            && intval($report['account_id']) !== intval($current_staff_access['id'])
                            && intval($current_staff_access['rank']) > $report_author_rank;
                        ?>
                        <article class="card admin-card report-moderation-card">
                            <div class="report-moderation-main">
                                <div class="report-moderation-topline">
                                    <span class="report-number">#<?php echo intval($report['id']); ?></span>
                                    <span class="pill <?php echo intval($report['is_approved']) === 1 ? 'pill-on' : 'pill-off'; ?>"><?php echo intval($report['is_approved']) === 1 ? 'публичный' : ($report['status'] === 'closed' ? 'не публичный' : 'на проверке'); ?></span>
                                    <span class="pill pill-neutral"><?php echo h(admin_report_status_label($report['status'])); ?></span>
                                </div>
                                <h2 class="notranslate" translate="no"><?php echo h($report['title']); ?></h2>
                                <p class="notranslate" translate="no"><?php echo h(limit_text($report['description'], 360)); ?></p>
                                <div class="report-moderation-meta">
                                    <?php if (!empty($report['author_name'])): ?><span class="notranslate" translate="no"><?php echo h($report['author_name']); ?></span><?php else: ?><?php echo h(admin_i18n_text('Неизвестно')); ?><?php endif; ?>
                                    <?php if ($report_author_role !== 'player'): ?><span class="staff-role-badge staff-role-badge--<?php echo h(staff_role_info($report_author_role)['tone']); ?>"><?php echo h(admin_i18n_text(staff_role_info($report_author_role)['short_label'])); ?></span><?php endif; ?>
                                    <span><?php echo h($report['created_at']); ?></span>
                                    <span><?php echo intval($report['comment_count']); ?> комментариев</span>
                                </div>
                                <a class="btn btn-secondary" href="bug_view.php?id=<?php echo intval($report['id']); ?>">Открыть обсуждение</a>
                            </div>
                            <form method="POST" action="admin.php?tab=reports" class="report-moderation-controls">
                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                <input type="hidden" name="action" value="update_bug_report">
                                <input type="hidden" name="report_id" value="<?php echo intval($report['id']); ?>">
                                <label>Этап
                                    <select class="form-control" name="report_status">
                                        <option value="open" <?php echo $report['status'] === 'open' ? 'selected' : ''; ?>>Открыт</option>
                                        <option value="in_progress" <?php echo $report['status'] === 'in_progress' ? 'selected' : ''; ?>>В работе</option>
                                        <option value="resolved" <?php echo $report['status'] === 'resolved' ? 'selected' : ''; ?>>Исправлен</option>
                                        <option value="closed" <?php echo $report['status'] === 'closed' ? 'selected' : ''; ?>>Закрыт</option>
                                    </select>
                                </label>
                                <label class="admin-check-row"><input type="checkbox" name="is_approved" value="1" <?php echo intval($report['is_approved']) === 1 ? 'checked' : ''; ?>><span>Показывать публично</span></label>
                                <?php if ($can_restrict_report_author): ?>
                                    <label>Доступ автора к репортам
                                        <select class="form-control" name="report_author_restricted">
                                            <option value="0" <?php echo intval($report['is_banned_reports']) !== 1 ? 'selected' : ''; ?>>Разрешён</option>
                                            <option value="1" <?php echo intval($report['is_banned_reports']) === 1 ? 'selected' : ''; ?>>Запрещён</option>
                                        </select>
                                    </label>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary">Сохранить</button>
                            </form>
                            <?php if (admin_can('reports.delete')): ?>
                                <form method="POST" action="admin.php?tab=reports" class="report-delete-form" onsubmit="return confirm(<?php echo h(admin_js_text('Удалить репорт и все комментарии без возможности восстановления?', 'Delete this report and all comments permanently?')); ?>);">
                                    <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                    <input type="hidden" name="action" value="delete_bug_report">
                                    <input type="hidden" name="report_id" value="<?php echo intval($report['id']); ?>">
                                    <button type="submit" class="btn btn-danger">Удалить</button>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php if ($report_total_pages > 1): ?>
                    <nav class="pagination" aria-label="Страницы очереди репортов">
                        <?php
                        $report_page_start = max(1, $report_page - 4);
                        $report_page_end = min($report_total_pages, $report_page + 4);
                        for ($report_page_link = $report_page_start; $report_page_link <= $report_page_end; $report_page_link++):
                            $report_query = [
                                'tab' => 'reports',
                                'report_page' => $report_page_link,
                                'report_status' => $report_status,
                                'report_search' => $report_search,
                            ];
                        ?>
                            <a class="pagination-item <?php echo $report_page === $report_page_link ? 'active' : ''; ?>" href="admin.php?<?php echo h(http_build_query($report_query)); ?>"><?php echo $report_page_link; ?></a>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            </section>

            <?php elseif ($tab === 'updates'): ?>
            <?php
            $form_update = $editing_update ?: [
                'id' => 0,
                'version' => '',
                'name' => '',
                'release_date' => date('Y-m-d'),
                'tag' => '',
                'intro' => '',
                'categories' => [],
                'status' => 'draft',
            ];
            $published_updates = count(array_filter($updates_items, function ($item) {
                return $item['status'] === 'published';
            }));
            ?>
            <section class="admin-section admin-section--updates">
                <div class="admin-hero-strip">
                    <div>
                        <p class="eyebrow">RELEASE CONTROL</p>
                        <div class="admin-hero-title">История обновлений</div>
                        <div class="admin-hero-sub">Создавайте патчноуты, сохраняйте черновики и управляйте тем, что опубликовано на странице обновлений.</div>
                    </div>
                    <div class="admin-live"><?php echo $published_updates; ?> опубликовано</div>
                </div>

                <div class="admin-metrics">
                    <div class="metric"><div class="metric-label">Всего записей</div><div class="metric-value"><?php echo count($updates_items); ?></div><div class="metric-sub">в базе истории</div></div>
                    <div class="metric"><div class="metric-label">Опубликовано</div><div class="metric-value"><?php echo $published_updates; ?></div><div class="metric-sub">видно игрокам</div></div>
                    <div class="metric"><div class="metric-label">Черновики</div><div class="metric-value"><?php echo count($updates_items) - $published_updates; ?></div><div class="metric-sub">скрыто от сайта</div></div>
                </div>

                <div class="admin-editor-grid">
                    <div class="card admin-card">
                        <div class="card-header">
                            <div>
                                <p class="eyebrow">РЕДАКТОР</p>
                                <h2 class="card-title"><?php echo intval($form_update['id']) > 0 ? 'Редактирование обновления' : 'Новое обновление'; ?></h2>
                            </div>
                            <?php if (intval($form_update['id']) > 0): ?><a class="btn btn-secondary" href="admin.php?tab=updates">Новая запись</a><?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="admin.php?tab=updates" class="admin-form-grid update-editor-form">
                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                <input type="hidden" name="action" value="save_update">
                                <input type="hidden" name="update_id" value="<?php echo intval($form_update['id']); ?>">
                                 <label class="form-group"><span>Версия</span><input class="form-control notranslate" translate="no" type="text" name="version" maxlength="32" required value="<?php echo h($form_update['version']); ?>" placeholder="<?php echo h(admin_i18n_text('Например: 1.1', 'For example: 1.1')); ?>"></label>
                                <label class="form-group"><span>Дата выпуска</span><input class="form-control" type="date" name="release_date" required value="<?php echo h($form_update['release_date']); ?>"></label>
                                 <label class="form-group admin-form-full"><span>Название обновления</span><input class="form-control notranslate" translate="no" type="text" name="name" minlength="3" maxlength="180" required value="<?php echo h($form_update['name']); ?>" placeholder="<?php echo h(admin_i18n_text('Название релиза', 'Release name')); ?>"></label>
                                 <label class="form-group"><span>Метка</span><input class="form-control notranslate" translate="no" type="text" name="tag" maxlength="80" value="<?php echo h($form_update['tag']); ?>" placeholder="<?php echo h(admin_i18n_text('Крупное обновление', 'Major update')); ?>"></label>
                                <label class="form-group"><span>Публикация</span><select class="form-control" name="status"><option value="draft" <?php echo $form_update['status'] === 'draft' ? 'selected' : ''; ?>>Черновик</option><option value="published" <?php echo $form_update['status'] === 'published' ? 'selected' : ''; ?>>Опубликовано</option></select></label>
                                 <label class="form-group admin-form-full"><span>Вступление</span><textarea class="form-control update-intro-textarea notranslate" translate="no" name="intro" maxlength="20000" placeholder="<?php echo h(admin_i18n_text('Короткое резюме релиза', 'Short release summary')); ?>"><?php echo h($form_update['intro']); ?></textarea></label>
                                <label class="form-group admin-form-full">
                                    <span>Категории и изменения</span>
                                     <textarea class="form-control update-categories-textarea notranslate" translate="no" name="categories_text" required placeholder="## 💬 <?php echo h(admin_i18n_text('Социальные функции', 'Social features')); ?>&#10;<?php echo h(admin_i18n_text('Взводы | Теперь доступны всем игрокам.', 'Platoons | Now available to all players.')); ?>&#10;<?php echo h(admin_i18n_text('Друзья | Добавлено управление списком друзей.', 'Friends | Friend-list management added.')); ?>"><?php echo h(orion_update_categories_to_text($form_update['categories'])); ?></textarea>
                                    <small class="form-help">Категория начинается с <b>## эмодзи Название</b>. Каждый пункт — <b>Заголовок | Описание</b>.</small>
                                </label>
                                <div class="admin-form-full admin-form-actions">
                                    <button class="btn btn-primary" type="submit">Сохранить обновление</button>
                                    <a class="btn btn-secondary" href="changelog.php" target="_blank" rel="noopener noreferrer">Открыть историю ↗</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card admin-card admin-update-list-card">
                        <div class="card-header"><div><p class="eyebrow">АРХИВ</p><h2 class="card-title">Все версии</h2></div><span class="pill pill-neutral"><?php echo count($updates_items); ?></span></div>
                        <div class="card-body">
                            <div class="admin-update-list">
                                <?php foreach ($updates_items as $item): ?>
                                    <article class="admin-update-row<?php echo intval($item['id']) === intval($form_update['id']) ? ' is-selected' : ''; ?>">
                                        <div class="admin-update-version notranslate" translate="no">v<?php echo h($item['version']); ?></div>
                                        <div>
                                             <strong class="notranslate" translate="no"><?php echo h($item['name']); ?></strong>
                                            <span><?php echo h($item['release_date']); ?> · <?php echo $item['status'] === 'published' ? 'опубликовано' : 'черновик'; ?></span>
                                        </div>
                                        <div class="admin-update-actions">
                                            <a class="btn btn-secondary btn-small" href="admin.php?tab=updates&amp;update_id=<?php echo intval($item['id']); ?>">Изменить</a>
                                            <form method="POST" action="admin.php?tab=updates" onsubmit="return confirm(<?php echo h(admin_js_text('Удалить эту запись истории обновлений?', 'Delete this update history entry?')); ?>);">
                                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                                <input type="hidden" name="action" value="delete_update">
                                                <input type="hidden" name="update_id" value="<?php echo intval($item['id']); ?>">
                                                <button class="btn btn-danger btn-small" type="submit">Удалить</button>
                                            </form>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                                <?php if (empty($updates_items)): ?><div class="admin-empty">История обновлений пуста.</div><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <?php elseif ($tab === 'server'): ?>
            <section class="admin-section admin-section--server">
                <div class="admin-hero-strip admin-server-hero admin-server-hero--<?php echo $admin_server_state['is_online'] ? 'online' : 'offline'; ?>">
                    <div>
                        <p class="eyebrow">SERVER PRESENCE</p>
                        <div class="admin-hero-title">Сервер <?php echo $admin_server_state['is_online'] ? 'онлайн' : 'офлайн'; ?></div>
                        <div class="admin-hero-sub">Этот статус сразу отображается в шапке и на главной странице сайта.</div>
                    </div>
                    <div class="admin-server-orb"><span></span><?php echo $admin_server_state['is_online'] ? 'ONLINE' : 'OFFLINE'; ?></div>
                </div>

                <div class="admin-server-layout">
                    <form method="POST" action="admin.php?tab=server" class="card admin-card admin-server-form">
                        <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                        <input type="hidden" name="action" value="set_server_status">
                        <div class="card-header"><div><p class="eyebrow">ПЕРЕКЛЮЧАТЕЛЬ</p><h2 class="card-title">Публичный статус</h2></div></div>
                        <div class="card-body">
                            <div class="server-state-picker">
                                <label class="server-state-option server-state-option--online">
                                    <input type="radio" name="server_status" value="online" <?php echo $admin_server_state['is_online'] ? 'checked' : ''; ?>>
                                    <span><i></i><strong>Онлайн</strong><small>Игроки могут подключаться</small></span>
                                </label>
                                <label class="server-state-option server-state-option--offline">
                                    <input type="radio" name="server_status" value="offline" <?php echo !$admin_server_state['is_online'] ? 'checked' : ''; ?>>
                                    <span><i></i><strong>Офлайн</strong><small>Сервер выключен или на работах</small></span>
                                </label>
                            </div>
                            <label class="form-group">
                                <span>Комментарий для игроков</span>
                                 <textarea class="form-control notranslate" translate="no" name="server_status_message" maxlength="255" placeholder="<?php echo h(admin_i18n_text('Например: Технические работы до 20:00', 'For example: Maintenance until 20:00')); ?>"><?php echo h($admin_server_state['message']); ?></textarea>
                                <small class="form-help">Если оставить пустым, на сайте будет показан только статус.</small>
                            </label>
                            <button class="btn btn-primary" type="submit">Применить статус</button>
                        </div>
                    </form>

                    <div class="card admin-card admin-server-preview">
                        <div class="card-header"><div><p class="eyebrow">ПРЕДПРОСМОТР</p><h2 class="card-title">Как видят игроки</h2></div></div>
                        <div class="card-body">
                            <div class="server-preview-chip server-preview-chip--<?php echo $admin_server_state['is_online'] ? 'online' : 'offline'; ?>"><i></i> Сервер <?php echo $admin_server_state['is_online'] ? 'онлайн' : 'офлайн'; ?> · версия 0.8.2</div>
                             <?php if ($admin_server_state['message'] !== ''): ?><p class="notranslate" translate="no"><?php echo h($admin_server_state['message']); ?></p><?php endif; ?>
                            <dl>
                                <div><dt>Последнее изменение</dt><dd><?php echo h($admin_server_state['updated_at'] ?: 'ещё не менялся'); ?></dd></div>
                                <div><dt>Источник</dt><dd>Панель главы проекта</dd></div>
                            </dl>
                            <a class="btn btn-secondary" href="index.php" target="_blank" rel="noopener noreferrer">Открыть главную ↗</a>
                        </div>
                    </div>
                </div>
            </section>

            <?php elseif ($tab === 'implement'): ?>
            <section class="admin-section admin-section--implement">
                <div class="admin-hero-strip admin-implement-hero">
                    <div>
                        <p class="eyebrow">FINAL EXECUTION QUEUE</p>
                        <div class="admin-hero-title">РЕАЛИЗОВАТЬ</div>
                        <div class="admin-hero-sub">Сюда попадают только решения, которые набрали большинство голосов и были отдельно одобрены главой совета Ориона.</div>
                    </div>
                    <div class="admin-live"><?php echo count($gso_implementation_items); ?> ожидают</div>
                </div>

                <div class="admin-implementation-list">
                    <?php foreach ($gso_implementation_items as $proposal): ?>
                        <?php
                        $proposal_id = intval($proposal['id']);
                        $implementation_label = admin_implementation_status_label($proposal['implementation_status']);
                        ?>
                        <article class="card admin-card admin-implementation-card" id="proposal-<?php echo $proposal_id; ?>">
                            <header>
                                <div>
                                    <span class="pill pill-on"><?php echo h($implementation_label); ?></span>
                                    <h2>GSO-<?php echo str_pad((string)$proposal_id, 4, '0', STR_PAD_LEFT); ?> · <span class="notranslate" translate="no"><?php echo h($proposal['title']); ?></span></h2>
                                </div>
                                <a class="btn btn-secondary btn-small" href="<?php echo h(i18n_locale_path('gso.php#proposal-' . $proposal_id)); ?>" target="_blank" rel="noopener noreferrer">Открыть в ГСО ↗</a>
                            </header>
                            <div class="admin-implementation-meta">
                                <span>Автор: <strong class="notranslate" translate="no"><?php echo h($proposal['author_name'] ?: '—'); ?></strong></span>
                                <span>Голоса: <strong><?php echo intval($proposal['yes_votes']); ?> за · <?php echo intval($proposal['no_votes']); ?> против · <?php echo intval($proposal['abstain_votes']); ?> воздержались</strong></span>
                                <span>Глава совета: <strong class="notranslate" translate="no"><?php echo h($proposal['head_name'] ?: '—'); ?></strong></span>
                            </div>
                            <div class="admin-implementation-content">
                                <div><strong>Что предлагается</strong><p class="notranslate" translate="no"><?php echo nl2br(h($proposal['description'])); ?></p></div>
                                <div><strong>Ожидаемый результат</strong><p class="notranslate" translate="no"><?php echo nl2br(h($proposal['expected_result'])); ?></p></div>
                                <div class="admin-head-approval"><strong>Решение главы совета</strong><p><?php if (!empty($proposal['head_note'])): ?><span class="notranslate" translate="no"><?php echo nl2br(h($proposal['head_note'])); ?></span><?php else: ?><?php echo h(admin_i18n_text('Без комментария.')); ?><?php endif; ?></p></div>
                            </div>
                            <form method="POST" action="admin.php?tab=implement#proposal-<?php echo $proposal_id; ?>" class="admin-implementation-form">
                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                <input type="hidden" name="action" value="update_gso_implementation">
                                <input type="hidden" name="proposal_id" value="<?php echo $proposal_id; ?>">
                                <label><span>Комментарий или отчёт</span><textarea class="form-control notranslate" translate="no" name="implementation_note" maxlength="10000" placeholder="<?php echo h(admin_i18n_text('Что сделано, почему отложено или что нужно выполнить', 'What was done, why it was postponed, or what needs to be done')); ?>"><?php echo h($proposal['implementation_note']); ?></textarea></label>
                                <div>
                                    <button class="btn btn-secondary" type="submit" name="implementation_action" value="start">Взять в работу</button>
                                    <button class="btn btn-secondary" type="submit" name="implementation_action" value="defer">Отложить</button>
                                    <button class="btn btn-primary" type="submit" name="implementation_action" value="complete">Отметить реализованным</button>
                                </div>
                            </form>
                            <form method="POST" action="admin.php?tab=implement#proposal-<?php echo $proposal_id; ?>" class="admin-status-form">
                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                <input type="hidden" name="action" value="override_gso_status">
                                <input type="hidden" name="proposal_id" value="<?php echo $proposal_id; ?>">
                                <label><span>Новый статус решения</span>
                                    <select class="form-control" name="new_status">
                                        <?php foreach (gso_status_override_options() as $status_option): ?>
                                            <?php if ($status_option === $proposal['status']) { continue; } ?>
                                            <option value="<?php echo h($status_option); ?>"><?php echo h(admin_gso_status_label($status_option)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label><span>Причина смены статуса</span><input class="form-control" type="text" name="status_note" minlength="5" maxlength="255" required placeholder="Почему статус решения меняется"></label>
                                <button class="btn btn-secondary" type="submit">Изменить статус</button>
                            </form>
                            <?php $render_gso_internal_log($proposal_id); ?>
                        </article>
                    <?php endforeach; ?>
                    <?php if (empty($gso_implementation_items)): ?><div class="admin-empty">Нет решений, ожидающих реализации. Новые появятся после голосования и одобрения главой совета.</div><?php endif; ?>
                </div>

                <div class="card admin-card">
                    <div class="card-header"><div><p class="eyebrow">АРХИВ РЕШЕНИЙ</p><h2 class="card-title">Реализованные и завершённые решения</h2></div><span class="pill pill-neutral"><?php echo count($gso_status_archive); ?></span></div>
                    <div class="card-body">
                        <p class="admin-hint">Статус любого решения можно перевести обратно: вернуть в реализацию, отправить на повторное голосование или на решение главы совета. Смена статуса главой проекта не публикуется в хронологии ГСО — причина остаётся здесь и в журнале аудита. Решения главы совета логируются публично как раньше.</p>
                        <div class="admin-status-list">
                            <?php foreach ($gso_status_archive as $proposal): ?>
                                <?php $archive_id = intval($proposal['id']); ?>
                                <article class="admin-status-row" id="proposal-<?php echo $archive_id; ?>">
                                    <div class="admin-status-row-head">
                                        <div class="admin-update-version">G<?php echo $archive_id; ?></div>
                                         <div><strong class="notranslate" translate="no"><?php echo h($proposal['title']); ?></strong><span><?php echo h(admin_gso_status_label($proposal['status'])); ?> · <?php echo h($proposal['implemented_at'] ?: $proposal['updated_at']); ?><?php if (!empty($proposal['implementer_name'])): ?> · <span class="notranslate" translate="no"><?php echo h($proposal['implementer_name']); ?></span><?php endif; ?></span></div>
                                         <a class="btn btn-secondary btn-small" href="<?php echo h(i18n_locale_path('gso.php#proposal-' . $archive_id)); ?>">История</a>
                                    </div>
                                    <form method="POST" action="admin.php?tab=implement#proposal-<?php echo $archive_id; ?>" class="admin-status-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                        <input type="hidden" name="action" value="override_gso_status">
                                        <input type="hidden" name="proposal_id" value="<?php echo $archive_id; ?>">
                                        <label><span>Новый статус решения</span>
                                            <select class="form-control" name="new_status">
                                                <?php foreach (gso_status_override_options() as $status_option): ?>
                                                    <?php if ($status_option === $proposal['status']) { continue; } ?>
                                                    <option value="<?php echo h($status_option); ?>"><?php echo h(admin_gso_status_label($status_option)); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label><span>Причина смены статуса</span><input class="form-control" type="text" name="status_note" minlength="5" maxlength="255" required placeholder="Почему статус решения меняется"></label>
                                        <button class="btn btn-secondary" type="submit">Изменить статус</button>
                                    </form>
                                    <?php $render_gso_internal_log($archive_id); ?>
                                </article>
                            <?php endforeach; ?>
                            <?php if (empty($gso_status_archive)): ?><div class="admin-empty">Завершённых решений пока нет.</div><?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <?php elseif ($tab === 'news'): ?>
            <?php
            $form_news = $editing_news ?: [
                'id' => 0,
                'title' => '',
                'summary' => '',
                'body' => '',
                'status' => 'published',
                'is_pinned' => 0,
                'published_at' => '',
            ];
            ?>
            <section class="admin-section admin-section--news">
            <div class="admin-hero-strip">
                <div>
                    <div class="admin-hero-title text-base md:text-xl">Новости сайта</div>
                    <div class="admin-hero-sub">Публикации появляются на главной странице. К новости можно прикрепить несколько изображений или видео.</div>
                </div>
                <div class="admin-live"><?php echo $news_stats['published']; ?> опубликовано</div>
            </div>

            <div class="admin-metrics">
                <div class="metric">
                    <div class="metric-value"><?php echo $news_stats['published']; ?></div>
                    <div class="metric-label">опубликовано</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?php echo $news_stats['draft']; ?></div>
                    <div class="metric-label">черновиков</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?php echo $news_stats['media']; ?></div>
                    <div class="metric-label">медиафайлов</div>
                </div>
                <div class="metric">
                    <div class="metric-value"><?php echo $news_total; ?></div>
                    <div class="metric-label">в текущем списке</div>
                </div>
            </div>

            <div class="admin-editor-grid">
                <div class="card admin-card">
                    <div class="card-header">
                        <div class="card-title text-sm md:text-lg"><?php echo intval($form_news['id']) > 0 ? 'Редактирование новости' : 'Новая публикация'; ?></div>
                        <?php if (intval($form_news['id']) > 0): ?>
                            <a href="admin.php?tab=news" class="btn btn-secondary">Новая</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form action="admin.php?tab=news" method="POST" enctype="multipart/form-data" class="admin-form-grid">
                            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                            <input type="hidden" name="action" value="save_news">
                            <input type="hidden" name="news_id" value="<?php echo intval($form_news['id']); ?>">

                            <div class="form-group full">
                                <label>Заголовок</label>
                                <input type="text" name="title" class="form-control notranslate" translate="no" maxlength="180" required value="<?php echo h($form_news['title']); ?>" placeholder="<?php echo h(admin_i18n_text('Например: Открыт общий тест', 'For example: Public test opened')); ?>">
                            </div>

                            <div class="form-group">
                                <label>Статус</label>
                                <select name="status" class="form-control">
                                    <option value="draft" <?php echo $form_news['status'] === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                                    <option value="published" <?php echo $form_news['status'] === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Дата публикации</label>
                                <input type="datetime-local" name="published_at" class="form-control" value="<?php echo h(news_datetime_input($form_news['published_at'] ?? '')); ?>">
                            </div>

                            <div class="form-group full">
                                <label>Короткое описание</label>
                                <input type="text" name="summary" class="form-control notranslate" translate="no" maxlength="512" value="<?php echo h($form_news['summary']); ?>" placeholder="<?php echo h(admin_i18n_text('Показывается под заголовком на главной', 'Shown below the title on the home page')); ?>">
                            </div>

                            <div class="form-group full">
                                <label>Текст новости</label>
                                <textarea name="body" class="form-control news-textarea notranslate" translate="no" required placeholder="<?php echo h(admin_i18n_text('Основной текст новости', 'Main news text')); ?>"><?php echo h($form_news['body']); ?></textarea>
                            </div>

                            <div class="form-group full">
                                <label class="switch-label admin-check-row">
                                    <input type="checkbox" name="is_pinned" value="1" <?php echo intval($form_news['is_pinned'] ?? 0) === 1 ? 'checked' : ''; ?>>
                                    Закрепить выше остальных новостей
                                </label>
                            </div>

                            <div class="form-group full media-drop">
                                <div class="media-drop-title">Медиа</div>
                                <div class="media-drop-hint">Можно выбрать несколько файлов. Изображения до 8 МБ, видео до 128 МБ.</div>
                                <input type="file" name="media_files[]" multiple accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/ogg">
                            </div>

                            <div class="full table-actions">
                                <button type="submit" class="btn btn-primary">Сохранить</button>
                                <?php if (intval($form_news['id']) > 0): ?>
                                    <a href="index.php" class="btn btn-secondary" target="_blank">Открыть главную</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card admin-card admin-media-panel">
                    <div class="card-header">
                        <div class="card-title text-sm md:text-lg">Прикрепленные медиа</div>
                        <div class="muted"><?php echo count($editing_media); ?> файлов</div>
                    </div>
                    <div class="card-body">
                        <?php if ($editing_news && !empty($editing_media)): ?>
                            <div class="news-media-grid">
                                <?php foreach ($editing_media as $media): ?>
                                    <div class="news-media-card">
                                        <?php if ($media['media_type'] === 'image'): ?>
                                            <img src="<?php echo h($media['file_path']); ?>" alt="<?php echo h($media['original_name']); ?>" class="news-media-preview" data-i18n-ignore translate="no">
                                        <?php else: ?>
                                            <video src="<?php echo h($media['file_path']); ?>" class="news-media-preview" controls preload="metadata" data-i18n-ignore translate="no"></video>
                                        <?php endif; ?>
                                        <div class="news-media-name notranslate" translate="no"><?php echo h($media['original_name']); ?></div>
                                        <div class="news-media-meta"><?php echo h($media['media_type']); ?> · <?php echo h(format_bytes($media['size_bytes'])); ?></div>
                                        <form action="admin.php?tab=news&amp;edit_id=<?php echo intval($editing_news['id']); ?>" method="POST" class="inline-form" onsubmit="return confirm(<?php echo h(admin_js_text('Удалить этот файл?', 'Delete this file?')); ?>);">
                                            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                            <input type="hidden" name="action" value="delete_news_media">
                                            <input type="hidden" name="media_id" value="<?php echo intval($media['id']); ?>">
                                            <button type="submit" class="danger-link media-delete">Удалить</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="admin-empty">Сначала сохрани новость, затем добавь картинки или видео.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card admin-card">
                <div class="card-header">
                    <div class="card-title text-sm md:text-lg">Список новостей</div>
                    <div class="muted"><?php echo $news_total; ?> записей</div>
                </div>
                <div class="card-body">
                    <form action="admin.php" method="GET" class="admin-toolbar">
                        <input type="hidden" name="tab" value="news">
                                <input type="text" name="news_search" class="form-control search-input notranslate" translate="no" placeholder="<?php echo h(admin_i18n_text('Поиск по новостям', 'Search news')); ?>" value="<?php echo h($news_search); ?>">
                        <select name="news_status" class="form-control">
                            <option value="">Все статусы</option>
                            <option value="published" <?php echo $news_status === 'published' ? 'selected' : ''; ?>>Опубликованные</option>
                            <option value="draft" <?php echo $news_status === 'draft' ? 'selected' : ''; ?>>Черновики</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Фильтр</button>
                        <a href="admin.php?tab=news" class="btn btn-secondary">Сбросить</a>
                    </form>

                    <div class="news-admin-list">
                        <?php foreach ($news_items as $item): ?>
                            <div class="news-admin-row">
                                <div>
                                    <div class="news-admin-title notranslate" translate="no"><?php echo h($item['title']); ?></div>
                                    <div class="news-admin-meta">
                                        <span><?php echo $item['status'] === 'published' ? '<span class="pill pill-on">опубликовано</span>' : '<span class="pill pill-neutral">черновик</span>'; ?></span>
                                        <?php if (intval($item['is_pinned']) === 1): ?><span class="pill pill-on">закреплено</span><?php endif; ?>
                                        <span><?php echo h($item['published_at'] ?: $item['created_at']); ?></span>
                                        <span class="notranslate" translate="no"><?php echo h($item['author_name'] ?: 'admin'); ?></span>
                                        <span>медиа: <?php echo intval($item['media_count']); ?></span>
                                    </div>
                                </div>
                                <div class="news-admin-actions">
                                    <a class="btn btn-secondary" href="admin.php?tab=news&amp;edit_id=<?php echo intval($item['id']); ?>">Редактировать</a>
                                    <?php if (admin_can('news.delete')): ?><form action="admin.php?tab=news" method="POST" class="inline-form" onsubmit="return confirm(<?php echo h(admin_js_text('Удалить новость полностью?', 'Delete this news post permanently?')); ?>);">
                                        <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                        <input type="hidden" name="action" value="delete_news">
                                        <input type="hidden" name="news_id" value="<?php echo intval($item['id']); ?>">
                                        <button type="submit" class="btn btn-danger">Удалить</button>
                                    </form><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($news_items)): ?>
                            <div class="admin-empty">Новостей по этим фильтрам нет.</div>
                        <?php endif; ?>
                    </div>

                    <?php if ($news_total_pages > 1): ?>
                        <div class="pagination">
                            <?php
                            $n_start = max(1, $news_page - 4);
                            $n_end = min($news_total_pages, $news_page + 4);
                            for ($i = $n_start; $i <= $n_end; $i++):
                                $query_params = $_GET;
                                $query_params['tab'] = 'news';
                                $query_params['news_page'] = $i;
                                $link = 'admin.php?' . http_build_query($query_params);
                            ?>
                                <a href="<?php echo h($link); ?>" class="pagination-item <?php echo $news_page === $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </section>
            <?php elseif ($tab === 'users'): ?>
            <?php
            $user_limit = 50;
            $user_where = '';
            $user_bind = [];
            if ($user_search !== '') {
                $user_where = "WHERE id = ? OR username LIKE ? OR email LIKE ? OR reg_ip LIKE ?";
                $like = '%' . $user_search . '%';
                $user_bind = [intval($user_search), $like, $like, $like];
            }
            try {
                $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM accounts $user_where");
                $count_stmt->execute($user_bind);
                $user_total = intval($count_stmt->fetchColumn());
            } catch (Exception $e) {
                $user_total = 0;
            }
            $user_total_pages = max(1, intval(ceil($user_total / $user_limit)));
            $user_page = min($user_page, $user_total_pages);
            $user_offset = ($user_page - 1) * $user_limit;
            try {
                $stmt = $pdo->prepare("SELECT id, username, email, reg_ip, last_ip, is_admin, staff_role, created_at, last_login FROM accounts $user_where ORDER BY id ASC LIMIT $user_limit OFFSET $user_offset");
                $stmt->execute($user_bind);
                $all_users = $stmt->fetchAll();
            } catch (Exception $e) {
                $all_users = [];
            }
            $banned_account_ids = [];
            $banned_ip_set = [];
            try {
                foreach ($pdo->query("SELECT account_id FROM bans WHERE ban_type = 'account' AND account_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN) as $bid) {
                    $banned_account_ids[intval($bid)] = true;
                }
                foreach ($pdo->query("SELECT ip FROM bans WHERE ban_type = 'ip' AND ip IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN) as $bip) {
                    $banned_ip_set[$bip] = true;
                }
            } catch (Exception $e) {}
            ?>
            <section class="admin-section admin-section--users">
            <div class="admin-hero-strip">
                <div>
                    <div class="admin-hero-title text-base md:text-xl">Пользователи</div>
                    <div class="admin-hero-sub">Страница <?php echo $user_page; ?> из <?php echo $user_total_pages; ?> · всего <?php echo $user_total; ?> аккаунтов</div>
                </div>
                <div class="admin-live"><?php echo $total_accounts; ?> аккаунтов</div>
            </div>

            <div class="card admin-card">
                <div class="card-header">
                    <div class="card-title text-sm md:text-lg">Все пользователи</div>
                </div>
                <div class="card-body">
                    <form action="admin.php" method="GET" class="admin-toolbar">
                        <input type="hidden" name="tab" value="users">
                        <input type="hidden" name="user_page" value="1">
                        <input type="text" name="user_search" class="form-control search-input notranslate" translate="no" placeholder="<?php echo h(admin_i18n_text('ID, логин, email или IP', 'ID, login, email, or IP')); ?>" value="<?php echo h($user_search); ?>">
                        <button type="submit" class="btn btn-primary">Найти</button>
                        <a href="admin.php?tab=users" class="btn btn-secondary">Сбросить</a>
                    </form>

                    <?php if (admin_can('bans.manage')): ?>
                    <div class="bulk-panel danger-panel">
                        <div>
                            <div class="bulk-title">Массовый бан по галочкам</div>
                            <div class="bulk-sub">Отметь нужных игроков и нажми «Забанить выделенных» — каждого забанит вместе с его игровым IP. Выбрано: <span id="banSelCount">0</span>.</div>
                        </div>
                        <div class="bulk-action-groups">
                            <div class="bulk-action-group">
                                <span class="bulk-action-label">Выбор</span>
                                <div class="bulk-actions">
                                    <button type="button" class="btn btn-secondary" onclick="toggleAllUsers(true)">Выделить всех</button>
                                    <button type="button" class="btn btn-secondary" onclick="toggleAllUsers(false)">Снять выделение</button>
                                </div>
                            </div>
                            <div class="bulk-action-group">
                                <span class="bulk-action-label">Действие</span>
                                <div class="bulk-actions">
                                    <button type="button" class="btn btn-danger" onclick="banSelected()">Забанить выделенных</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="admin-table-wrap">
                        <table class="tanks-table admin-table admin-table--users">
                            <thead>
                                <tr>
                                    <th class="admin-select-cell"><?php if (admin_can('bans.manage')): ?><input type="checkbox" id="banSelectAll" onclick="toggleAllUsers(this.checked)" title="Выделить всех"><?php endif; ?></th>
                                    <th>ID</th>
                                    <th>Логин</th>
                                    <th>Email</th>
                                    <th>IP сайта</th>
                                    <th>IP игры</th>
                                    <th>Статус</th>
                                    <th>Последний вход</th>
                                    <th>Действие</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_users as $u): ?>
                                    <?php
                                    $uid = intval($u['id']);
                                    $game_ip = trim((string)($u['last_ip'] ?? ''));
                                    $acc_banned = isset($banned_account_ids[$uid]);
                                    $ip_banned = $game_ip !== '' && isset($banned_ip_set[$game_ip]);
                                    $user_staff_role = normalize_staff_role($u['staff_role'] ?? '', intval($u['is_admin']) === 1);
                                    $user_role_info = staff_role_info($user_staff_role);
                                    $user_is_protected = $uid === intval($current_staff_access['id']) || intval($user_role_info['rank']) >= intval($current_staff_access['rank']);
                                    $can_edit_user = !$user_is_protected && admin_can('users.edit');
                                    $can_reset_user_password = !$user_is_protected && admin_can('users.credentials');
                                    $can_ban = !$user_is_protected && admin_can('bans.manage') && !$acc_banned;
                                    ?>
                                    <tr>
                                        <td class="admin-select-cell">
                                            <?php if ($can_ban): ?>
                                                <input type="checkbox" class="js-ban-check" value="<?php echo $uid; ?>" onchange="updateBanSelCount()">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $uid; ?></td>
                                        <td class="tank-name notranslate" translate="no"><?php echo h($u['username']); ?></td>
                                        <td class="admin-table-secondary notranslate" translate="no"><?php echo h($u['email'] ?? '-'); ?></td>
                                        <td class="admin-table-mono">
                                            <?php if ($user_is_protected): ?>защищено<?php else: ?><span class="notranslate" translate="no"><?php echo h($u['reg_ip'] ?? '-'); ?></span><?php endif; ?>
                                        </td>
                                        <td class="admin-table-mono">
                                            <?php if ($user_is_protected): ?>защищено<?php else: ?><span class="notranslate" translate="no"><?php echo h($game_ip !== '' ? $game_ip : '-'); ?></span><?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user_staff_role !== 'player'): ?>
                                                 <span class="staff-role-badge staff-role-badge--<?php echo h($user_role_info['tone']); ?>"><?php echo h(admin_i18n_text($user_role_info['short_label'])); ?></span>
                                            <?php elseif ($acc_banned || $ip_banned): ?>
                                                <span class="pill pill-off">бан<?php echo $ip_banned ? ' + IP' : ''; ?></span>
                                            <?php else: ?>
                                                <span class="pill pill-neutral">активен</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="admin-table-secondary notranslate" translate="no"><?php echo h($u['last_login'] ?? '-'); ?></td>
                                        <td>
                                            <div class="table-actions admin-row-actions">
                                                <?php if ($can_edit_user): ?><button type="button" class="btn btn-secondary admin-row-action" onclick="setUsername(<?php echo $uid; ?>, <?php echo h(json_encode($u['username'])); ?>)">Ник</button><?php endif; ?>
                                                <?php if ($can_reset_user_password): ?><button type="button" class="btn btn-secondary admin-row-action" onclick="setUserPassword(<?php echo $uid; ?>, <?php echo h(json_encode($u['username'])); ?>)">Пароль</button><?php endif; ?>
                                                <?php if ($can_ban): ?>
                                                    <button type="button" class="btn btn-danger admin-row-action" onclick="banUser(<?php echo $uid; ?>, <?php echo h(json_encode($u['username'])); ?>)">Забанить</button>
                                                <?php elseif (!$user_is_protected && ($acc_banned || $ip_banned)): ?>
                                                    <span class="muted admin-row-note">см. вкладку «Баны»</span>
                                                <?php elseif ($user_is_protected): ?>
                                                    <span class="muted admin-row-note">защищён иерархией</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($all_users)): ?>
                                    <tr>
                                        <td colspan="9" class="admin-empty">Пользователи не найдены.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($user_total_pages > 1): ?>
                        <div class="pagination">
                            <?php
                            $u_start = max(1, $user_page - 4);
                            $u_end = min($user_total_pages, $user_page + 4);
                            for ($i = $u_start; $i <= $u_end; $i++):
                            ?>
                                <a href="admin.php?tab=users&amp;user_page=<?php echo $i; ?>&amp;user_search=<?php echo h($user_search); ?>" class="pagination-item <?php echo $user_page === $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </section>
            <?php elseif ($tab === 'bans'): ?>
            <section class="admin-section admin-section--bans">
            <?php
            $ban_list = [];
            $ban_counts = ['account' => 0, 'ip' => 0, 'mac' => 0];
            $ip_owner_map = [];
            $ban_search = trim($_GET['ban_search'] ?? '');
            try {
                // Глобальные счётчики (не зависят от поиска).
                foreach ($pdo->query("SELECT ban_type, COUNT(*) AS c FROM bans GROUP BY ban_type")->fetchAll() as $row) {
                    if (isset($ban_counts[$row['ban_type']])) {
                        $ban_counts[$row['ban_type']] = intval($row['c']);
                    }
                }

                $ban_where = '';
                $ban_bind = [];
                if ($ban_search !== '') {
                    $like = '%' . $ban_search . '%';
                    // IP аккаунтов, чей ник/почта/ID подходят под поиск — чтобы
                    // по нику находились и IP-баны этого игрока.
                    $owner_ips = [];
                    $ip_stmt = $pdo->prepare("SELECT last_ip, reg_ip FROM accounts WHERE username LIKE ? OR email LIKE ? OR id = ?");
                    $ip_stmt->execute([$like, $like, intval($ban_search)]);
                    foreach ($ip_stmt->fetchAll() as $r) {
                        foreach ([$r['last_ip'], $r['reg_ip']] as $oip) {
                            $oip = trim((string)$oip);
                            if ($oip !== '') {
                                $owner_ips[$oip] = true;
                            }
                        }
                    }
                    $owner_ips = array_keys($owner_ips);
                    $clauses = ["a.username LIKE ?", "a.email LIKE ?", "b.account_id = ?", "b.ip LIKE ?", "b.mac LIKE ?"];
                    $ban_bind = [$like, $like, intval($ban_search), $like, $like];
                    if (!empty($owner_ips)) {
                        $ph = implode(',', array_fill(0, count($owner_ips), '?'));
                        $clauses[] = "b.ip IN ($ph)";
                        $ban_bind = array_merge($ban_bind, $owner_ips);
                    }
                    $ban_where = 'WHERE ' . implode(' OR ', $clauses);
                }

                $sql = "SELECT b.id, b.ban_type, b.account_id, b.ip, b.mac, b.reason, b.created_at, a.username AS account_name, a.email AS account_email, a.last_ip AS account_last_ip, a.reg_ip AS account_reg_ip FROM bans b LEFT JOIN accounts a ON a.id = b.account_id $ban_where ORDER BY b.created_at DESC, b.id DESC LIMIT 500";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($ban_bind);
                $ban_list = $stmt->fetchAll();
                $banned_ips_for_lookup = [];
                foreach ($ban_list as $b) {
                    if ($b['ban_type'] === 'ip' && !empty($b['ip'])) {
                        $banned_ips_for_lookup[$b['ip']] = true;
                    }
                }
                // Для IP-банов ищем владельца аккаунта по игровому/регистрационному IP,
                // чтобы в строке показать ник и почту рядом с адресом.
                $banned_ips_for_lookup = array_keys($banned_ips_for_lookup);
                if (!empty($banned_ips_for_lookup)) {
                    $ph = implode(',', array_fill(0, count($banned_ips_for_lookup), '?'));
                    $owner_stmt = $pdo->prepare("SELECT id, username, email, last_ip, reg_ip FROM accounts WHERE last_ip IN ($ph) OR reg_ip IN ($ph)");
                    $owner_stmt->execute(array_merge($banned_ips_for_lookup, $banned_ips_for_lookup));
                    foreach ($owner_stmt->fetchAll() as $owner) {
                        foreach ([$owner['last_ip'], $owner['reg_ip']] as $oip) {
                            $oip = trim((string)$oip);
                            if ($oip !== '' && !isset($ip_owner_map[$oip])) {
                                $ip_owner_map[$oip] = $owner;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $ban_list = [];
            }
            ?>
            <div class="admin-hero-strip">
                <div>
                    <div class="admin-hero-title text-base md:text-xl">Баны</div>
                    <div class="admin-hero-sub">Перманентные баны по аккаунту и IP действуют сразу и в игре, и на сайте. Игровой сервер читает эту же таблицу (онлайн-игроков выкидывает в течение ~10–15 сек).</div>
                </div>
                <div class="admin-live">live db</div>
            </div>

            <div class="admin-metrics">
                <div class="metric"><div class="metric-value"><?php echo count($ban_list); ?></div><div class="metric-label">всего банов</div></div>
                <div class="metric"><div class="metric-value"><?php echo $ban_counts['account']; ?></div><div class="metric-label">по аккаунту</div></div>
                <div class="metric"><div class="metric-value"><?php echo $ban_counts['ip']; ?></div><div class="metric-label">по IP</div></div>
                <div class="metric"><div class="metric-value"><?php echo $ban_counts['mac']; ?></div><div class="metric-label">по MAC</div></div>
            </div>

            <?php if (admin_can('bans.manage')): ?>
            <div class="card admin-card">
                <div class="card-header"><div class="card-title text-sm md:text-lg">Добавить бан вручную</div></div>
                <div class="card-body">
                    <div class="bulk-panel danger-panel">
                        <div class="admin-panel-span">
                            <div class="bulk-title">Бан по IP</div>
                            <div class="bulk-sub">Заблокирует вход в игру и на сайт с этого IP-адреса.</div>
                            <div class="admin-toolbar admin-toolbar--nested">
                                <input type="text" id="banIpAddr" class="form-control admin-control-wide" placeholder="IPv4 / IPv6, напр. 203.0.113.5">
                                <input type="text" id="banIpReason" class="form-control search-input" placeholder="Причина (необязательно)">
                                <button type="button" class="btn btn-danger" onclick="banIpManual()">Забанить IP</button>
                            </div>
                        </div>
                    </div>
                    <div class="bulk-panel danger-panel">
                        <div class="admin-panel-span">
                            <div class="bulk-title">Бан по MAC</div>
                            <div class="bulk-sub">Внимание: текущий стоковый клиент не передаёт MAC, поэтому такой бан в игре не сработает до добавления передачи аппаратного ID. Поле оставлено на будущее и для ручного учёта.</div>
                            <div class="admin-toolbar admin-toolbar--nested">
                                <input type="text" id="banMacAddr" class="form-control admin-control-wide" placeholder="AA:BB:CC:DD:EE:FF">
                                <input type="text" id="banMacReason" class="form-control search-input" placeholder="Причина (необязательно)">
                                <button type="button" class="btn btn-danger" onclick="banMacManual()">Забанить MAC</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card admin-card">
                <div class="card-header"><div class="card-title text-sm md:text-lg">Активные баны</div></div>
                <div class="card-body">
                    <form action="admin.php" method="GET" class="admin-toolbar">
                        <input type="hidden" name="tab" value="bans">
                        <input type="text" name="ban_search" class="form-control search-input notranslate" translate="no" placeholder="<?php echo h(admin_i18n_text('Ник, IP, почта или ID', 'Nickname, IP, email, or ID')); ?>" value="<?php echo h($ban_search); ?>">
                        <button type="submit" class="btn btn-primary">Найти</button>
                        <a href="admin.php?tab=bans" class="btn btn-secondary">Сбросить</a>
                    </form>

                    <div class="admin-table-wrap">
                        <table class="tanks-table admin-table admin-table--bans">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Тип</th>
                                    <th>Цель</th>
                                    <th>Причина</th>
                                    <th>Дата</th>
                                    <th>Действие</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ban_list as $b): ?>
                                    <?php
                                    // Собираем ник / IP / почту для строки независимо от типа бана.
                                    $t_nick = '';
                                    $t_id = 0;
                                    $t_ip = '';
                                    $t_email = '';
                                    if ($b['ban_type'] === 'account') {
                                        $type_label = 'аккаунт';
                                        $t_id = intval($b['account_id']);
                                        $t_nick = $b['account_name'] ?? '(удалён)';
                                        $t_email = trim((string)($b['account_email'] ?? ''));
                                        $t_ip = trim((string)($b['account_last_ip'] ?? ''));
                                        if ($t_ip === '') {
                                            $t_ip = trim((string)($b['account_reg_ip'] ?? ''));
                                        }
                                    } elseif ($b['ban_type'] === 'ip') {
                                        $type_label = 'IP';
                                        $t_ip = trim((string)$b['ip']);
                                        $owner = $ip_owner_map[$t_ip] ?? null;
                                        if ($owner) {
                                            $t_id = intval($owner['id']);
                                            $t_nick = $owner['username'];
                                            $t_email = trim((string)($owner['email'] ?? ''));
                                        }
                                    } else {
                                        $type_label = 'MAC';
                                        $t_ip = trim((string)$b['mac']);
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo intval($b['id']); ?></td>
                                        <td><span class="pill pill-off"><?php echo h($type_label); ?></span></td>
                                        <td>
                                            <div class="ban-target">
                                                <span class="ban-target-name">
                                                    <?php if ($t_nick !== ''): ?>
                                                        <?php echo ($t_id > 0 ? '#' . $t_id . ' ' : '') . '<span class="notranslate" translate="no">' . h($t_nick) . '</span>'; ?>
                                                    <?php else: ?>
                                                        <span class="muted">ник неизвестен</span>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="ban-target-address"><?php if ($t_ip !== ''): ?><span class="notranslate" translate="no"><?php echo h($t_ip); ?></span><?php else: ?><span class="muted">IP неизвестен</span><?php endif; ?></span>
                                                <span class="ban-target-email"><?php if ($t_email !== ''): ?><span class="notranslate" translate="no"><?php echo h($t_email); ?></span><?php else: ?>почта неизвестна<?php endif; ?></span>
                                            </div>
                                        </td>
                                        <td class="admin-table-secondary notranslate" translate="no"><?php echo h($b['reason'] !== '' ? $b['reason'] : '—'); ?></td>
                                        <td class="admin-table-secondary"><?php echo h($b['created_at']); ?></td>
                                        <td><?php if (admin_can('bans.unban')): ?><button type="button" class="btn btn-success admin-row-action" onclick="unban(<?php echo intval($b['id']); ?>)">Разбанить</button><?php else: ?><span class="muted">только просмотр</span><?php endif; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($ban_list)): ?>
                                    <tr><td colspan="6" class="admin-empty"><?php if ($ban_search !== ''): ?>По запросу «<span class="notranslate" translate="no"><?php echo h($ban_search); ?></span>» ничего не найдено.<?php else: ?>Активных банов нет.<?php endif; ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            </section>
            <?php elseif ($tab === 'downloads'): ?>
            <?php
            $client_mirrors = get_setting_json($pdo, 'download_client_mirrors', [['name' => 'Скачать на другом сайте', 'url' => '', 'enabled' => false]]);
            $patch_mirrors = get_setting_json($pdo, 'download_patch_mirrors', [['name' => 'Скачать scripts_config.xml', 'url' => '', 'enabled' => false]]);
            $video_url = get_setting($pdo, 'download_video_url') ?: '';
            $instructions = get_setting($pdo, 'download_instructions');
            ?>
            <section class="admin-section admin-section--downloads">
            <div class="admin-hero-strip">
                <div>
                    <div class="admin-hero-title text-base md:text-xl">Настройки страницы загрузок</div>
                    <div class="admin-hero-sub">Зеркала для скачивания клиента и патча, видео-инструкция и текст.</div>
                </div>
            </div>

            <form method="POST" action="admin.php?tab=downloads" class="admin-form-grid admin-card admin-downloads-form">
                <input type="hidden" name="action" value="save_downloads">
                <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

                <div class="full download-settings-section">
                    <div class="download-settings-title">Зеркала клиента</div>
                    <div id="client-mirrors" class="mirror-list">
                        <?php foreach ($client_mirrors as $i => $m): ?>
                        <div class="mirror-row">
                            <input type="text" name="client_name[]" value="<?php echo h($m['name']); ?>" placeholder="<?php echo h(admin_i18n_text('Название', 'Name')); ?>" class="form-control mirror-input mirror-input--name notranslate" translate="no">
                            <input type="url" name="client_url[]" value="<?php echo h($m['url']); ?>" placeholder="https://..." class="form-control mirror-input mirror-input--url notranslate" translate="no">
                            <label class="mirror-enabled">
                                <input type="checkbox" name="client_enabled[]" <?php echo !isset($m['enabled']) || $m['enabled'] ? 'checked' : ''; ?>>
                                Активно
                            </label>
                            <button type="button" class="btn btn-secondary mirror-remove" onclick="removeMirrorRow(this)">Удалить</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary mirror-add" onclick="addMirrorRow('client-mirrors','client')" id="btn-add-client">+ Добавить зеркало</button>
                </div>

                <div class="full download-settings-section">
                    <div class="download-settings-title">Зеркала патча</div>
                    <div id="patch-mirrors" class="mirror-list">
                        <?php foreach ($patch_mirrors as $i => $m): ?>
                        <div class="mirror-row">
                            <input type="text" name="patch_name[]" value="<?php echo h($m['name']); ?>" placeholder="<?php echo h(admin_i18n_text('Название', 'Name')); ?>" class="form-control mirror-input mirror-input--name notranslate" translate="no">
                            <input type="url" name="patch_url[]" value="<?php echo h($m['url']); ?>" placeholder="https://..." class="form-control mirror-input mirror-input--url notranslate" translate="no">
                            <label class="mirror-enabled">
                                <input type="checkbox" name="patch_enabled[]" <?php echo !isset($m['enabled']) || $m['enabled'] ? 'checked' : ''; ?>>
                                Активно
                            </label>
                            <button type="button" class="btn btn-secondary mirror-remove" onclick="removeMirrorRow(this)">Удалить</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary mirror-add" onclick="addMirrorRow('patch-mirrors','patch')" id="btn-add-patch">+ Добавить зеркало</button>
                </div>

                <div class="full download-settings-section">
                    <div class="download-settings-title">Видео-инструкция</div>
                    <input type="text" name="video_url" value="<?php echo h($video_url); ?>" placeholder="<?php echo h(admin_i18n_text('video/установка.mp4 или URL', 'video/installation.mp4 or URL')); ?>" class="form-control download-setting-input notranslate" translate="no">
                </div>

                <div class="full download-settings-section">
                    <div class="download-settings-title">Текст инструкции (HTML)</div>
                    <textarea name="instructions" rows="8" class="form-control download-instructions notranslate" translate="no"><?php echo h($instructions ?? ''); ?></textarea>
                    <div class="download-settings-hint">Если оставить пустым — используется стандартная инструкция с сайта.</div>
                </div>

                <div class="full table-actions download-submit">
                    <button type="submit" class="btn btn-primary">Сохранить настройки</button>
                </div>
            </form>
            </section>

            <?php elseif ($tab === 'contracts'): ?>
            <section class="admin-section admin-section--contracts">
                <div class="admin-hero-strip admin-contracts-hero">
                    <div>
                        <p class="eyebrow">OWNER ADMIN ONLY</p>
                        <div class="admin-hero-title">Контракты команды</div>
                        <div class="admin-hero-sub">Пользователь сам выбирает тип и подаёт контракт. С пятого календарного дня он может запросить продление той же роли. Все даты и время указаны по Киеву.</div>
                    </div>
                    <div class="admin-live"><span></span><?php echo intval($contract_admin_data['stats']['pending']); ?> новых</div>
                </div>

                <div class="admin-metrics contract-admin-metrics">
                    <div class="metric"><div class="metric-value"><?php echo intval($contract_admin_data['stats']['pending']); ?></div><div class="metric-label">контрактов ждут решения</div></div>
                    <div class="metric"><div class="metric-value"><?php echo intval($contract_admin_data['stats']['active']); ?></div><div class="metric-label">действуют сейчас</div></div>
                    <div class="metric"><div class="metric-value"><?php echo intval($contract_admin_data['stats']['scheduled']); ?></div><div class="metric-label">продлений запланировано</div></div>
                    <div class="metric"><div class="metric-value"><?php echo intval($contract_admin_data['stats']['accepted']); ?></div><div class="metric-label">принято всего</div></div>
                </div>

                <div class="contract-admin-layout">
                    <div class="contract-admin-main">
                        <div class="card admin-card contract-review-card">
                            <div class="card-header"><div><p class="eyebrow">REVIEW QUEUE</p><h2 class="card-title">Контракты на рассмотрении</h2></div><span class="admin-nav-count"><?php echo count($contract_admin_data['pending']); ?></span></div>
                            <div class="card-body contract-review-list">
                                <?php foreach ($contract_admin_data['pending'] as $application): ?>
                                    <?php $application_role_info = staff_role_info($application['preferred_role']); ?>
                                    <?php $is_renewal_application = (string)($application['application_type'] ?? 'initial') === 'renewal'; ?>
                                    <article class="contract-review-item">
                                        <header>
                                            <div><span>#<?php echo intval($application['id']); ?> · <?php echo $is_renewal_application ? 'ПРОДЛЕНИЕ' : 'КОНТРАКТ'; ?></span><h3 class="notranslate" translate="no"><?php echo h($application['username'] ?: ('Account #' . intval($application['account_id']))); ?></h3><small><?php if (!empty($application['email'])): ?><span class="notranslate" translate="no"><?php echo h($application['email']); ?></span><?php else: ?>email не указан<?php endif; ?> · <?php echo h(contract_format_kyiv_datetime($application['created_at'])); ?><?php if ($is_renewal_application && !empty($application['renewal_contract_number'])): ?> · <span class="notranslate" translate="no"><?php echo h($application['renewal_contract_number']); ?></span><?php endif; ?></small></div>
                                            <span class="staff-role-badge staff-role-badge--<?php echo h($application_role_info['tone']); ?>"><?php echo h(contract_role_label($application['preferred_role'], admin_current_locale())); ?></span>
                                        </header>
                                        <?php if (!empty($application['motivation'])): ?><p class="contract-review-motivation notranslate" translate="no"><?php echo nl2br(h($application['motivation'])); ?></p><?php endif; ?>
                                        <form method="POST" action="admin.php?tab=contracts" class="contract-approval-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                            <input type="hidden" name="action" value="approve_contract_application">
                                            <input type="hidden" name="application_id" value="<?php echo intval($application['id']); ?>">
                                            <div class="contract-review-actions"><input class="form-control" name="decision_note" maxlength="500" placeholder="Комментарий (необязательно)"><button type="submit" class="btn btn-primary"><?php echo $is_renewal_application ? 'Принять продление' : 'Принять на 7 дней'; ?></button></div>
                                        </form>
                                        <form method="POST" action="admin.php?tab=contracts" class="contract-reject-form" onsubmit="return confirm(<?php echo h(admin_js_text('Отклонить этот контракт?', 'Reject this contract?')); ?>);">
                                            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                            <input type="hidden" name="action" value="reject_contract_application">
                                            <input type="hidden" name="application_id" value="<?php echo intval($application['id']); ?>">
                                            <input class="form-control" name="decision_note" maxlength="500" placeholder="Причина отказа для кандидата">
                                            <button type="submit" class="btn btn-danger">Отклонить</button>
                                        </form>
                                    </article>
                                <?php endforeach; ?>
                                <?php if (empty($contract_admin_data['pending'])): ?><div class="admin-empty">Новых контрактов нет.</div><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <aside class="contract-admin-side admin-stack">
                        <div class="card admin-card contract-policy-card">
                            <div class="card-header"><div><p class="eyebrow">LIFECYCLE</p><h2 class="card-title">Простой поток</h2></div></div>
                            <div class="card-body"><div class="contract-policy-line"><span>01</span><p><strong>Пользователь</strong>Выбирает тип и подаёт контракт</p></div><div class="contract-policy-line"><span>02</span><p><strong>Глава</strong>Принимает или отклоняет</p></div><div class="contract-policy-line"><span>05</span><p><strong>Продление</strong>С пятого дня участник может подать заявку</p></div><div class="contract-policy-line"><span>07</span><p><strong>Срок</strong>Принятая роль работает семь дней</p></div></div>
                        </div>
                    </aside>
                </div>

                <div class="card admin-card contract-current-card">
                    <div class="card-header"><div><p class="eyebrow">ACTIVE / SCHEDULED</p><h2 class="card-title">Действующие и запланированные контракты</h2></div><a href="<?php echo h(i18n_locale_path('contracts.php')); ?>" class="btn btn-secondary" target="_blank" rel="noopener">Открыть публичный реестр</a></div>
                    <div class="card-body table-scroll">
                        <table class="admin-table contract-admin-table">
                            <thead><tr><th>Контракт</th><th>Участник</th><th>Роль</th><th>Статус</th><th>Период</th><th>PDF</th><th>Действие</th></tr></thead>
                            <tbody>
                                <?php foreach ($contract_admin_data['current'] as $contract): ?>
                                    <tr>
                                        <td><strong class="notranslate" translate="no"><?php echo h($contract['contract_number']); ?></strong></td>
                                        <td class="notranslate" translate="no"><?php echo h($contract['username'] ?: ('Account #' . intval($contract['account_id']))); ?></td>
                                        <td><?php echo h(contract_role_label($contract['staff_role'], admin_current_locale())); ?></td>
                                        <td><span class="pill <?php echo $contract['status'] === 'active' ? 'pill-on' : 'pill-neutral'; ?>"><?php echo h(contract_status_label($contract['status'], admin_current_locale())); ?></span></td>
                                        <td class="admin-table-secondary"><?php echo h(contract_format_kyiv_datetime($contract['starts_at'])); ?><br><?php echo h(contract_format_kyiv_datetime($contract['expires_at'])); ?></td>
                                        <td><div class="contract-table-pdfs"><a href="contract_pdf.php?id=<?php echo h($contract['public_id']); ?>&amp;lang=uk&amp;v=6" target="_blank" rel="noopener">UA</a><a href="contract_pdf.php?id=<?php echo h($contract['public_id']); ?>&amp;lang=ru&amp;v=6" target="_blank" rel="noopener">RU</a><a href="contract_pdf.php?id=<?php echo h($contract['public_id']); ?>&amp;lang=en&amp;v=6" target="_blank" rel="noopener">EN</a></div></td>
                                        <td class="contract-admin-actions">
                                            <form method="POST" action="admin.php?tab=contracts" class="contract-terminate-form contract-terminate-form--inline" onsubmit="return confirm(<?php echo h($contract['status'] === 'scheduled' ? admin_js_text('Отменить запланированное продление?', 'Cancel the scheduled renewal?') : admin_js_text('Расторгнуть этот контракт? Роль пользователя будет снята сразу.', 'Terminate this contract? The user role will be removed immediately.')); ?>);">
                                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                                <input type="hidden" name="action" value="terminate_contract">
                                                <input type="hidden" name="contract_id" value="<?php echo intval($contract['id']); ?>">
                                                <input class="form-control" name="termination_reason" minlength="5" maxlength="500" placeholder="Причина расторжения" required>
                                                <button type="submit" class="btn btn-danger"><?php echo $contract['status'] === 'scheduled' ? 'Отменить' : 'Расторгнуть'; ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($contract_admin_data['current'])): ?><tr><td colspan="7" class="admin-empty">Действующих или запланированных контрактов нет.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <?php elseif ($tab === 'staff'): ?>
            <?php
            $can_edit_selected_staff = $selected_staff_access && staff_can_manage_access($current_staff_access, $selected_staff_access);
            $assignable_roles = staff_assignable_roles($current_staff_access);
            $permission_groups = [];
            foreach (staff_permission_catalog() as $permission_key => $permission_meta) {
                $permission_groups[$permission_meta['group']][$permission_key] = $permission_meta;
            }
            ?>
            <section class="admin-section admin-section--staff">
                <div class="admin-hero-strip">
                    <div>
                        <div class="admin-hero-title">Команда и иерархия</div>
                        <div class="admin-hero-sub">Роль задаёт базовый набор возможностей, а персональные исключения точечно разрешают или запрещают отдельные действия.</div>
                    </div>
                    <div class="admin-live"><?php echo $dashboard_stats['staff_total']; ?> сотрудников</div>
                </div>

                <div class="role-summary-grid">
                    <?php foreach (['admin', 'developer', 'orion_council_head', 'senior_moderator', 'moderator', 'content_maker'] as $summary_role): ?>
                        <?php $summary_info = staff_role_info($summary_role); ?>
                        <article class="role-summary-card role-summary-card--<?php echo h($summary_info['tone']); ?>">
                            <span><?php echo h(admin_i18n_text($summary_info['label'])); ?></span>
                            <strong><?php echo intval($role_counts[$summary_role] ?? 0); ?></strong>
                            <small>уровень <?php echo intval($summary_info['rank']); ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="admin-workspace admin-workspace--staff">
                    <aside class="admin-accounts-panel staff-roster-panel">
                        <div class="card admin-card">
                            <div class="card-header"><div><p class="eyebrow">СОСТАВ</p><h2 class="card-title">Команда</h2></div></div>
                            <div class="card-body">
                                    <form action="admin.php" method="GET" class="account-search-form">
                                    <input type="hidden" name="tab" value="staff">
                                    <input type="search" name="staff_search" class="form-control notranslate" translate="no" value="<?php echo h($staff_search); ?>" placeholder="<?php echo h(admin_i18n_text('Ник, email или ID', 'Nickname, email, or ID')); ?>">
                                    <button type="submit" class="btn btn-secondary">Найти</button>
                                </form>
                                <p class="staff-search-hint">Чтобы назначить нового сотрудника, найдите обычного игрока.</p>
                                <div class="account-list staff-roster-list">
                                    <?php foreach ($staff_accounts as $staff_account): ?>
                                        <?php
                                        $staff_row_role = normalize_staff_role($staff_account['staff_role'] ?? '', intval($staff_account['is_admin']) === 1);
                                        $staff_row_info = staff_role_info($staff_row_role);
                                        ?>
                                        <a class="account-link <?php echo intval($staff_account['id']) === $selected_staff_id ? 'active' : ''; ?>" href="admin.php?tab=staff&amp;staff_id=<?php echo intval($staff_account['id']); ?>&amp;staff_search=<?php echo urlencode($staff_search); ?>">
                                            <span class="account-name">#<?php echo intval($staff_account['id']); ?> <span class="notranslate" translate="no"><?php echo h($staff_account['username']); ?></span></span>
                                            <span class="account-meta"><span class="staff-role-dot staff-role-dot--<?php echo h($staff_row_info['tone']); ?>"></span><?php echo h(admin_i18n_text($staff_row_info['short_label'])); ?> · <?php if (!empty($staff_account['last_login'])): ?><span class="notranslate" translate="no"><?php echo h($staff_account['last_login']); ?></span><?php else: ?><?php echo h(admin_i18n_text('не входил')); ?><?php endif; ?><?php if (!empty($staff_account['discord_username'])): ?> · Discord: @<span class="notranslate" translate="no"><?php echo h($staff_account['discord_username']); ?></span><?php endif; ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if (empty($staff_accounts)): ?><div class="admin-empty">Никого не найдено.</div><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <div class="admin-workspace-main">
                        <?php if (!$selected_staff_account || !$selected_staff_access): ?>
                            <div class="card admin-card"><div class="card-body admin-empty">Выберите сотрудника или найдите игрока для назначения роли.</div></div>
                        <?php else: ?>
                            <div class="card admin-card staff-access-card">
                                <div class="card-header staff-member-header">
                                    <div>
                                        <p class="eyebrow">АККАУНТ #<?php echo intval($selected_staff_account['id']); ?></p>
                                        <h2 class="card-title notranslate" translate="no"><?php echo h($selected_staff_account['username']); ?></h2>
                                        <?php if (!empty($selected_staff_account['email'])): ?><span class="staff-member-email notranslate" translate="no"><?php echo h($selected_staff_account['email']); ?></span><?php else: ?><span class="staff-member-email">email не указан</span><?php endif; ?>
                                        <?php if (!empty($selected_staff_account['discord_username'])): ?><span class="staff-member-email">Discord: @<span class="notranslate" translate="no"><?php echo h($selected_staff_account['discord_username']); ?></span></span><?php endif; ?>
                                    </div>
                                    <span class="staff-role-badge staff-role-badge--<?php echo h($selected_staff_access['role_info']['tone']); ?>"><?php echo h(admin_i18n_text($selected_staff_access['role_info']['label'])); ?></span>
                                </div>
                                <div class="card-body">
                                    <div class="staff-hierarchy-note">
                                        <strong>Иерархическая защита включена.</strong>
                                        <span>Ваш уровень: <?php echo intval($current_staff_access['rank']); ?> · уровень аккаунта: <?php echo intval($selected_staff_access['rank']); ?>. Изменять можно только нижестоящие аккаунты.</span>
                                    </div>

                                    <?php if ($can_edit_selected_staff): ?>
                                        <form method="POST" action="admin.php?tab=staff&amp;staff_id=<?php echo intval($selected_staff_id); ?>" class="staff-access-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                                            <input type="hidden" name="action" value="save_staff_access">
                                            <input type="hidden" name="staff_account_id" value="<?php echo intval($selected_staff_id); ?>">

                                            <div class="staff-role-picker">
                                                <label for="staff-role-select">Роль в команде</label>
                                                <select id="staff-role-select" class="form-control" name="staff_role">
                                                    <?php foreach ($assignable_roles as $role_key => $role_info): ?>
                                                        <option value="<?php echo h($role_key); ?>" <?php echo $selected_staff_access['role'] === $role_key ? 'selected' : ''; ?>><?php echo h(admin_i18n_text($role_info['label'])); ?> — уровень <?php echo intval($role_info['rank']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small>При смене роли персональные исключения пересчитываются из формы ниже.</small>
                                            </div>

                                            <div class="permission-groups">
                                                <?php if (!empty($selected_staff_access['role_info']['permissions_fixed'])): ?>
                                                    <div class="staff-hierarchy-note">
                                                        <strong>Права модерации зафиксированы.</strong>
                                                        <span>Доступны только предусмотренные ролью операции с репортами; персональные разрешения не применяются.</span>
                                                    </div>
                                                <?php else: ?>
                                                <?php foreach ($permission_groups as $group_label => $group_permissions): ?>
                                                    <fieldset class="permission-group-card">
                                                        <legend><?php echo h(admin_i18n_text($group_label)); ?></legend>
                                                        <?php foreach ($group_permissions as $permission_key => $permission_meta): ?>
                                                            <?php
                                                            $override_state = array_key_exists($permission_key, $selected_staff_access['overrides'])
                                                                ? ($selected_staff_access['overrides'][$permission_key] ? 'allow' : 'deny')
                                                                : 'inherit';
                                                            ?>
                                                            <div class="permission-control-row">
                                                                <div><strong><?php echo h(admin_i18n_text($permission_meta['label'])); ?></strong><small><?php echo h(admin_i18n_text($permission_meta['description'])); ?></small></div>
                                                                <select class="form-control permission-state permission-state--<?php echo h($override_state); ?>" name="permission_state[<?php echo h(str_replace('.', '__', $permission_key)); ?>]">
                                                                    <option value="inherit" <?php echo $override_state === 'inherit' ? 'selected' : ''; ?>>По роли</option>
                                                                    <option value="allow" <?php echo $override_state === 'allow' ? 'selected' : ''; ?>>Разрешить</option>
                                                                    <option value="deny" <?php echo $override_state === 'deny' ? 'selected' : ''; ?>>Запретить</option>
                                                                </select>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </fieldset>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="staff-access-actions">
                                                <button type="submit" class="btn btn-primary">Сохранить роль и доступы</button>
                                                <span>Изменение будет применено на следующем запросе сотрудника.</span>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="staff-readonly-permissions">
                                            <p><?php echo intval($selected_staff_id) === intval($current_staff_access['id']) ? 'Собственные права нельзя изменять из текущей сессии.' : 'Этот аккаунт защищён иерархией.'; ?></p>
                                            <?php foreach (staff_permission_catalog() as $permission_key => $permission_meta): ?>
                                                <div class="permission-summary-row">
                                                    <span><?php echo h(admin_i18n_text($permission_meta['label'])); ?></span>
                                                    <span class="pill <?php echo !empty($selected_staff_access['permissions'][$permission_key]) ? 'pill-on' : 'pill-off'; ?>"><?php echo !empty($selected_staff_access['permissions'][$permission_key]) ? 'разрешено' : 'запрещено'; ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <?php elseif ($tab === 'audit'): ?>
            <section class="admin-section admin-section--audit">
                <div class="admin-hero-strip">
                    <div>
                        <div class="admin-hero-title">Журнал действий</div>
                        <div class="admin-hero-sub">Неизменяемая история критичных операций: кто, когда и над каким объектом выполнил действие.</div>
                    </div>
                    <div class="admin-live"><?php echo $dashboard_stats['actions_week']; ?> за 7 дней</div>
                </div>

                <div class="card admin-card">
                    <div class="card-header"><div><p class="eyebrow">АУДИТ</p><h2 class="card-title">Последние 200 событий</h2></div></div>
                    <div class="card-body">
                        <form action="admin.php" method="GET" class="admin-toolbar">
                            <input type="hidden" name="tab" value="audit">
                            <input type="search" name="audit_search" class="form-control admin-control-wide notranslate" translate="no" value="<?php echo h($audit_search); ?>" placeholder="<?php echo h(admin_i18n_text('Сотрудник, действие, объект или описание', 'Employee, action, object, or description')); ?>">
                            <button type="submit" class="btn btn-primary">Найти</button>
                            <a href="admin.php?tab=audit" class="btn btn-secondary">Сбросить</a>
                        </form>
                        <div class="admin-table-wrap">
                            <table class="admin-table audit-table">
                                <thead><tr><th>Время</th><th>Сотрудник</th><th>Действие</th><th>Объект</th><th>Описание</th><th>IP</th></tr></thead>
                                <tbody>
                                    <?php foreach ($audit_items as $audit_item): ?>
                                        <tr>
                                            <td class="admin-table-secondary"><?php echo h($audit_item['created_at']); ?></td>
                                            <td><strong><?php if (!empty($audit_item['actor_name'])): ?><span class="notranslate" translate="no"><?php echo h($audit_item['actor_name']); ?></span><?php else: ?><?php echo h(admin_i18n_text('Система')); ?><?php endif; ?></strong></td>
                                            <td><span class="audit-action-key"><?php echo h(admin_i18n_text(staff_action_label($audit_item['action_key']))); ?></span></td>
                                            <td class="admin-table-mono notranslate" translate="no"><?php echo h(($audit_item['target_type'] ?: '—') . ($audit_item['target_id'] !== '' ? ' #' . $audit_item['target_id'] : '')); ?></td>
                                            <td class="notranslate" translate="no"><?php echo h($audit_item['summary'] ?: '—'); ?></td>
                                            <td class="admin-table-mono admin-table-secondary notranslate" translate="no"><?php echo h($audit_item['ip_address'] ?: '—'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($audit_items)): ?><tr><td colspan="6" class="admin-empty">События не найдены.</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <?php endif; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
