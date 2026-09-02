<?php
// Реквизиты владельца проекта. Пустая строка — блок не выводится.
$modal_donation_url = '';
$modal_btc_address = '';
$modal_ltc_address = '';
$modal_lang = isset($ui_lang) && in_array($ui_lang, ['ru', 'uk', 'en'], true)
    ? $ui_lang
    : (function_exists('current_lang') && in_array(current_lang(), ['ru', 'uk', 'en'], true) ? current_lang() : 'ru');
$modal_copy = [
    'ru' => [
        'close' => 'Закрыть',
        'support' => 'Поддержка',
        'title' => 'Поддержать Project Orion',
        'intro' => 'Все пожертвования являются добровольной поддержкой автора. Пожертвование не является покупкой товара или услуги и не предоставляет никаких игровых преимуществ.',
        'amount' => 'Размер пожертвования никак не влияет на игровой процесс',
        'advantage' => 'Донат не даёт донатеру преимуществ перед другими игроками',
        'hosting' => 'Поддержка идёт на оплату хостинга и развитие проекта',
        'support_alerts' => 'Поддержать через DonationAlerts',
        'btc_alt' => 'QR-код Bitcoin',
        'ltc_alt' => 'QR-код Litecoin',
        'copy' => 'Скопировать адрес',
    ],
    'uk' => [
        'close' => 'Закрити',
        'support' => 'Підтримка',
        'title' => 'Підтримати Project Orion',
        'intro' => 'Усі пожертви є добровільною підтримкою автора. Пожертва не є покупкою товару чи послуги й не надає жодних ігрових переваг.',
        'amount' => 'Розмір пожертви ніяк не впливає на ігровий процес',
        'advantage' => 'Донат не дає донатеру переваг перед іншими гравцями',
        'hosting' => 'Підтримка йде на оплату хостингу та розвиток проєкту',
        'support_alerts' => 'Підтримати через DonationAlerts',
        'btc_alt' => 'QR-код Bitcoin',
        'ltc_alt' => 'QR-код Litecoin',
        'copy' => 'Скопіювати адресу',
    ],
    'en' => [
        'close' => 'Close',
        'support' => 'Support',
        'title' => 'Support Project Orion',
        'intro' => 'All donations are voluntary support for the author. A donation is not a purchase of a product or service and provides no in-game advantages.',
        'amount' => 'The donation amount does not affect gameplay',
        'advantage' => 'Donations do not give donors an advantage over other players',
        'hosting' => 'Support pays for hosting and project development',
        'support_alerts' => 'Support via DonationAlerts',
        'btc_alt' => 'Bitcoin QR code',
        'ltc_alt' => 'Litecoin QR code',
        'copy' => 'Copy address',
    ],
][$modal_lang];
?>
<div id="donateModal" class="donate-modal" role="dialog" aria-modal="true" aria-labelledby="donateModalTitle" aria-hidden="true">
    <div class="donate-modal-bg" data-modal-close></div>
    <div class="modal-dialog" tabindex="-1">
        <button type="button" data-modal-close class="modal-close" aria-label="<?php echo htmlspecialchars($modal_copy['close'], ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
        <div class="modal-header">
            <p class="eyebrow"><?php echo htmlspecialchars($modal_copy['support'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h2 id="donateModalTitle"><?php echo htmlspecialchars($modal_copy['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        </div>
        <div class="modal-body">
            <p><?php echo htmlspecialchars($modal_copy['intro'], ENT_QUOTES, 'UTF-8'); ?></p>
            <ul class="donation-terms">
                <li><?php echo htmlspecialchars($modal_copy['amount'], ENT_QUOTES, 'UTF-8'); ?></li>
                <li><?php echo htmlspecialchars($modal_copy['advantage'], ENT_QUOTES, 'UTF-8'); ?></li>
                <li><?php echo htmlspecialchars($modal_copy['hosting'], ENT_QUOTES, 'UTF-8'); ?></li>
            </ul>
            <?php if ($modal_donation_url !== ''): ?>
            <a href="<?php echo htmlspecialchars($modal_donation_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-primary btn-block"><?php echo htmlspecialchars($modal_copy['support_alerts'], ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endif; ?>

            <?php if ($modal_btc_address !== ''): ?>
            <section class="wallet-card">
                <div>
                    <p class="eyebrow">Bitcoin</p>
                    <div id="btcAddr" class="wallet-address"><?php echo htmlspecialchars($modal_btc_address, ENT_QUOTES, 'UTF-8'); ?></div>
                    <button type="button" data-copy="#btcAddr" class="btn btn-secondary"><?php echo htmlspecialchars($modal_copy['copy'], ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($modal_ltc_address !== ''): ?>
            <section class="wallet-card">
                <div>
                    <p class="eyebrow">Litecoin</p>
                    <div id="ltcAddr" class="wallet-address"><?php echo htmlspecialchars($modal_ltc_address, ENT_QUOTES, 'UTF-8'); ?></div>
                    <button type="button" data-copy="#ltcAddr" class="btn btn-secondary"><?php echo htmlspecialchars($modal_copy['copy'], ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </div>
</div>
