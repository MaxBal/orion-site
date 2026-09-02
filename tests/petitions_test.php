<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/staff.php';
require_once dirname(__DIR__) . '/includes/gso.php';
require_once dirname(__DIR__) . '/includes/petitions.php';

function petition_check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

petition_check(ORION_PLAYER_PETITION_SIGNATURE_THRESHOLD === 50, 'player petition threshold must be 50 signatures');
petition_check(ORION_PLAYER_PETITION_VOTING_DAYS === 3, 'promoted petitions must receive a three-day vote');
petition_check(ORION_PLAYER_PETITION_COOLDOWN_DAYS === 3, 'players must wait three days before creating another petition');
petition_check(player_petition_signature_progress(0, 50) === 0, 'empty petition progress must be zero');
petition_check(player_petition_signature_progress(25, 50) === 50, 'petition progress must reflect half the threshold');
petition_check(player_petition_signature_progress(49, 50) === 98, 'petition progress must reflect signatures before the threshold');
petition_check(player_petition_signature_progress(50, 50) === 100, 'petition progress must reach one hundred at the threshold');
petition_check(player_petition_signature_progress(90, 50) === 100, 'petition progress must be capped at one hundred');
petition_check(player_petition_has_reached_threshold(50, 50), 'threshold helper must accept exactly fifty signatures');
petition_check(!player_petition_has_reached_threshold(49, 50), 'threshold helper must reject forty-nine signatures');
petition_check(player_petition_status_from_gso_status('voting') === 'voting', 'GSO voting must remain visible as voting');
petition_check(player_petition_status_from_gso_status('rejected_head') === 'rejected', 'head rejection must close the petition');
petition_check(player_petition_status_from_gso_status('unknown') === null, 'unknown GSO status must not overwrite petition status');
petition_check(player_petition_status_label('collecting', 'uk') === 'Збір підписів', 'Ukrainian petition status must be available');

fwrite(STDOUT, "Player petition checks passed.\n");
