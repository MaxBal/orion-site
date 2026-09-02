<?php
if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en' && !defined('ORION_SKIP_LANGUAGE_REDIRECT')) {
    define('ORION_SKIP_LANGUAGE_REDIRECT', true);
}
require_once 'db.php';

if (isset($_GET['lang']) && is_string($_GET['lang']) && $_GET['lang'] === 'en') {
    $_SESSION['lang'] = 'en';
}
$ui_lang = function_exists('current_lang') ? current_lang() : 'ru';
if (($_SESSION['lang'] ?? '') === 'en') {
    $ui_lang = 'en';
}
if (!in_array($ui_lang, ['ru', 'uk', 'en'], true)) {
    $ui_lang = 'ru';
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Реквизиты владельца проекта. Пустая строка — блок не выводится.
$donation_url = '';
$btc_address = '';
$ltc_address = '';
$bmc_username = 'orion_reborn';
$copy = [
    'ru' => [
        'page_title' => 'Поддержать сервер Project Orion 0.8.2',
        'page_description' => 'Поддержи сервер Project Orion 0.8.2 добровольным пожертвованием. Донат не даёт игровых преимуществ.',
        'banner_subtext' => 'Поддержка сервера · 0.8.2',
        'support' => 'Поддержка',
        'title' => 'Поддержать Project Orion',
        'lead' => 'Выберите удобный способ добровольной поддержки проекта.',
        'disclaimer' => 'Пожертвование — это',
        'voluntary' => 'добровольная',
        'author_support' => 'поддержка автора.',
        'not_purchase' => 'Оно не является',
        'purchase_tail' => 'покупкой товара или услуги, не даёт игровых преимуществ и не влияет на игровой процесс. Средства идут на оплату хостинга и развитие проекта.',
        'wallets' => 'Банковская карта и кошельки',
        'fast_transfer' => 'Быстрый перевод картой, через электронный кошелёк или другим доступным способом.',
        'support_alerts' => 'Поддержать через DonationAlerts',
        'btc_alt' => 'QR-код Bitcoin',
        'ltc_alt' => 'QR-код Litecoin',
        'btc_note' => 'Отправляйте только BTC в сети Bitcoin.',
        'ltc_note' => 'Отправляйте только LTC в сети Litecoin.',
        'copy' => 'Скопировать адрес',
    ],
    'uk' => [
        'page_title' => 'Підтримати сервер Project Orion 0.8.2',
        'page_description' => 'Підтримай сервер Project Orion 0.8.2 добровільною пожертвою. Донат не дає ігрових переваг.',
        'banner_subtext' => 'Підтримка сервера · 0.8.2',
        'support' => 'Підтримка',
        'title' => 'Підтримати Project Orion',
        'lead' => 'Оберіть зручний спосіб добровільної підтримки проєкту.',
        'disclaimer' => 'Пожертва — це',
        'voluntary' => 'добровільна',
        'author_support' => 'підтримка автора.',
        'not_purchase' => 'Вона не є',
        'purchase_tail' => 'покупкою товару чи послуги, не дає ігрових переваг і не впливає на ігровий процес. Кошти йдуть на оплату хостингу та розвиток проєкту.',
        'wallets' => 'Банківська картка та гаманці',
        'fast_transfer' => 'Швидкий переказ карткою, через електронний гаманець або іншим доступним способом.',
        'support_alerts' => 'Підтримати через DonationAlerts',
        'btc_alt' => 'QR-код Bitcoin',
        'ltc_alt' => 'QR-код Litecoin',
        'btc_note' => 'Надсилайте лише BTC у мережі Bitcoin.',
        'ltc_note' => 'Надсилайте лише LTC у мережі Litecoin.',
        'copy' => 'Скопіювати адресу',
    ],
    'en' => [
        'page_title' => 'Support Project Orion server 0.8.2',
        'page_description' => 'Support Project Orion server 0.8.2 with a voluntary donation. Donations provide no in-game advantages.',
        'banner_subtext' => 'Server support · 0.8.2',
        'support' => 'Support',
        'title' => 'Support Project Orion',
        'lead' => 'Choose a convenient way to support the project voluntarily.',
        'disclaimer' => 'A donation is',
        'voluntary' => 'voluntary',
        'author_support' => 'support for the author.',
        'not_purchase' => 'It is not',
        'purchase_tail' => 'a purchase of a product or service, provides no in-game advantages, and does not affect gameplay. Funds pay for hosting and project development.',
        'wallets' => 'Bank card and wallets',
        'fast_transfer' => 'Make a quick transfer by card, electronic wallet, or another available method.',
        'support_alerts' => 'Support via DonationAlerts',
        'btc_alt' => 'Bitcoin QR code',
        'ltc_alt' => 'Litecoin QR code',
        'btc_note' => 'Send BTC only over the Bitcoin network.',
        'ltc_note' => 'Send LTC only over the Litecoin network.',
        'copy' => 'Copy address',
    ],
][$ui_lang];
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'donate.php';
$active_page = 'donate';
$banner_subtext = $copy['banner_subtext'];
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell donate-page">
    <header class="page-header">
        <p class="eyebrow"><?php echo h($copy['support']); ?></p>
        <h1><?php echo h($copy['title']); ?></h1>
        <p><?php echo h($copy['lead']); ?></p>
    </header>

    <p class="donate-disclaimer">
        <?php echo h($copy['disclaimer']); ?> <strong><?php echo h($copy['voluntary']); ?></strong> <?php echo h($copy['author_support']); ?> <?php echo h($copy['not_purchase']); ?> <strong><?php echo h($copy['purchase_tail']); ?></strong>
    </p>

    <section class="donate-grid">
        <?php if ($bmc_username !== ''): ?>
        <article class="wallet-card">
            <div>
                <p class="eyebrow">Buy Me a Coffee</p>
                <h2>Coffee</h2>
                <p><?php echo h($copy['fast_transfer']); ?></p>
                <a href="https://buymeacoffee.com/<?php echo h($bmc_username); ?>" target="_blank" rel="noopener" class="btn btn-primary">Buy Me a Coffee</a>
            </div>
        </article>
        <?php endif; ?>

        <article class="wallet-card">
            <div>
                <p class="eyebrow"><?php echo h($copy['wallets']); ?></p>
                <h2>DonationAlerts</h2>
                <p><?php echo h($copy['fast_transfer']); ?></p>
                <?php if ($donation_url !== ''): ?>
                <a href="<?php echo h($donation_url); ?>" target="_blank" rel="noopener" class="btn btn-primary"><?php echo h($copy['support_alerts']); ?></a>
                <?php endif; ?>
            </div>
        </article>

        <?php if ($btc_address !== ''): ?>
        <article class="wallet-card">
            <div>
                <p class="eyebrow">Bitcoin</p>
                <h2>BTC</h2>
                <p><?php echo h($copy['btc_note']); ?></p>
                <div class="wallet-address" id="btcAddr"><?php echo h($btc_address); ?></div>
                <button type="button" class="btn btn-secondary" data-copy="#btcAddr"><?php echo h($copy['copy']); ?></button>
            </div>
        </article>
        <?php endif; ?>

        <?php if ($ltc_address !== ''): ?>
        <article class="wallet-card">
            <div>
                <p class="eyebrow">Litecoin</p>
                <h2>LTC</h2>
                <p><?php echo h($copy['ltc_note']); ?></p>
                <div class="wallet-address" id="ltcAddr"><?php echo h($ltc_address); ?></div>
                <button type="button" class="btn btn-secondary" data-copy="#ltcAddr"><?php echo h($copy['copy']); ?></button>
            </div>
        </article>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php if ($bmc_username !== ''): ?>
<script data-name="BMC-Widget" data-cfasync="false" src="https://cdnjs.buymeacoffee.com/1.0.0/widget.prod.min.js" data-id="<?php echo h($bmc_username); ?>" data-description="Support me on Buy me a coffee!" data-message="" data-color="#FF813F" data-position="Right" data-x_margin="18" data-y_margin="18"></script>
<?php endif; ?>
