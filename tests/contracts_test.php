<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/contracts.php';
require_once dirname(__DIR__) . '/includes/contract_pdf.php';

function contractCheck(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$roles = contract_role_definitions();
contractCheck(array_keys($roles) === ['orion_council_head', 'developer', 'senior_moderator', 'moderator', 'content_maker'], 'contract types changed');
contractCheck(contract_role_label('orion_council_head', 'uk') === 'Голова ради Оріона', 'Ukrainian Orion council head label is missing');
contractCheck(contract_role_label('developer', 'uk') === 'Розробник', 'Ukrainian developer label is missing');
contractCheck(contract_role_label('content_maker', 'en') === 'Content maker', 'English content maker label is missing');
contractCheck(contract_datetime_add_days('2026-07-22 12:00:00', 7) === '2026-07-29 12:00:00', 'seven-day term calculation failed');
contractCheck(contract_datetime_add_days('2026-07-22 12:00:00', ORION_CONTRACT_RENEWAL_DAY - 1) === '2026-07-26 12:00:00', 'fifth-calendar-day renewal calculation failed');
contractCheck(ORION_CONTRACT_COOLDOWN_DAYS === 7, 'post-decision cooldown must last seven days');
contractCheck(
    contract_cooldown_available_at('2026-07-22 12:00:00', null) === '2026-07-29 12:00:00',
    'rejected application cooldown calculation failed'
);
contractCheck(
    contract_cooldown_available_at(null, null, '2026-07-22 12:00:00') === '2026-07-29 12:00:00',
    'expired contract cooldown calculation failed'
);
contractCheck(
    contract_cooldown_available_at('2026-07-22 12:00:00', '2026-07-24 09:30:00', '2026-07-23 09:30:00') === '2026-07-31 09:30:00',
    'cooldown must use the latest contract event'
);
contractCheck(contract_cooldown_is_active('2026-07-31 09:30:00', '2026-07-31 09:29:59'), 'cooldown ended too early');
contractCheck(!contract_cooldown_is_active('2026-07-31 09:30:00', '2026-07-31 09:30:00'), 'cooldown did not end after seven days');
$calendar_transition_end = contract_datetime_add_days('2026-10-24 12:00:00', 7);
contractCheck(
    contract_format_kyiv_datetime('2026-10-24 12:00:00', 'H:i') === contract_format_kyiv_datetime($calendar_transition_end, 'H:i'),
    'Kyiv calendar-day calculation changed the local contract hour'
);
contractCheck(contract_format_kyiv_datetime('2026-07-22 12:00:00') === '22.07.2026 15:00', 'Kyiv summer time conversion failed');
contractCheck(ORION_CONTRACT_TERMS_VERSION === 'staff-v6-2026-07', 'current contract version is not active');

$sample = [
    'id' => 42,
    'contract_number' => 'ORI-2026-000042',
    'account_id' => 42,
    'username' => 'OrionTester',
    'staff_role' => 'developer',
    'signer_name' => 'Олександр Марченко',
    'offered_by_name' => 'OrionLead',
    'signed_at' => '2026-07-22 12:30:00',
    'starts_at' => '2026-07-22 12:30:00',
    'expires_at' => '2026-07-29 12:30:00',
    'renewal_available_at' => '2026-07-26 12:30:00',
    'status' => 'active',
    'signature_hash' => hash('sha256', 'contract-test'),
];
contractCheck(!contract_can_request_renewal($sample, '2026-07-26 12:29:59'), 'renewal opened before the fifth calendar day');
contractCheck(contract_can_request_renewal($sample, '2026-07-26 12:30:00'), 'renewal did not open on the fifth calendar day');

foreach (['uk', 'ru', 'en'] as $language) {
    $document = contract_pdf_document($sample, $language);
    contractCheck(count($document['sections']) === 6, "{$language} contract clauses are incomplete");
    $term_clause = $document['sections'][3][1];
    $renewal_clause = $document['sections'][4][1];
    $activation_clause = $document['sections'][5][1];
    contractCheck(
        str_contains($term_clause, $language === 'en' ? 'calendar day five' : ($language === 'uk' ? 'п’ятого календарного дня' : 'пятого календарного дня')),
        "{$language} contract does not define fifth-calendar-day renewal"
    );
    contractCheck(
        str_contains($renewal_clause, $language === 'en' ? 'starts exactly when' : ($language === 'uk' ? 'починається точно після' : 'начинается точно после')),
        "{$language} contract does not define linked renewal timing"
    );
    contractCheck(
        str_contains($activation_clause, $language === 'en' ? 'immediately activates' : ($language === 'uk' ? 'одразу активує' : 'сразу активирует')),
        "{$language} approval does not activate the selected role immediately"
    );
    contractCheck(
        str_contains($activation_clause, $language === 'en' ? 'without a justified and recorded reason' : ($language === 'uk' ? 'без обґрунтованої та зафіксованої причини' : 'без обоснованной и зафиксированной причины')),
        "{$language} contract does not prohibit arbitrary termination by the administration"
    );
    $pdf = contract_pdf_render($sample, $language);
    contractCheck(str_starts_with($pdf, '%PDF-1.7'), "{$language} PDF header is invalid");
    contractCheck(str_contains($pdf, '/Count 2'), "{$language} PDF must have two pages");
    contractCheck(strlen($pdf) > 50000, "{$language} PDF does not contain embedded fonts");
}

$terminated = $sample;
$terminated['status'] = 'terminated';
$terminated['terminated_at'] = '2026-07-25 17:15:00';
$terminated['termination_reason'] = 'Контракт завершён решением главы проекта.';
$terminated_document = contract_pdf_document($terminated, 'ru');
contractCheck(
    str_contains($terminated_document['sections'][5][1], $terminated['termination_reason']),
    'termination reason is missing from the PDF contract'
);

fwrite(STDOUT, "Contract roles, terms, translations, and PDF generation checks passed.\n");
