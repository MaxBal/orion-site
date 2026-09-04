<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

function source(string $path): string
{
    global $root;
    $full = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    $content = file_get_contents($full);
    if ($content === false) {
        throw new RuntimeException("Cannot read {$path}");
    }
    return $content;
}

function fail(string $message): void
{
    global $failures;
    $failures[] = $message;
}

function expectContains(string $path, string $needle, string $message): void
{
    if (!str_contains(source($path), $needle)) {
        fail("{$message} ({$path})");
    }
}

function expectNotContains(string $path, string $needle, string $message): void
{
    if (str_contains(source($path), $needle)) {
        fail("{$message} ({$path})");
    }
}

function expectBefore(string $path, string $first, string $second, string $message): void
{
    $content = source($path);
    $firstPosition = strpos($content, $first);
    $secondPosition = strpos($content, $second);
    if ($firstPosition === false || $secondPosition === false || $firstPosition >= $secondPosition) {
        fail("{$message} ({$path})");
    }
}

function expectFileNotExists(string $path, string $message): void
{
    global $root;
    $full = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (file_exists($full)) {
        fail("{$message} ({$path})");
    }
}

function expectRegex(string $path, string $pattern, string $message): void
{
    if (preg_match($pattern, source($path)) !== 1) {
        fail("{$message} ({$path})");
    }
}

function cssSelectorMinHeightAtLeast(string $css, string $selectorNeedle, float $minimum): bool
{
    $heights = [];
    preg_match_all('~([^{}]+)\{([^{}]*)\}~s', $css, $rules, PREG_SET_ORDER);
    foreach ($rules as $rule) {
        if (!str_contains($rule[1], $selectorNeedle)) {
            continue;
        }
        preg_match_all('~(?<![-\w])min-height\s*:\s*([+-]?(?:\d+(?:\.\d+)?|\.\d+))px\b~i', $rule[2], $matches);
        foreach ($matches[1] as $height) {
            $heights[] = floatval($height);
        }
    }
    if ($heights === []) {
        return false;
    }
    foreach ($heights as $height) {
        if ($height < $minimum) {
            return false;
        }
    }
    return max($heights) >= $minimum;
}

function expectCssSelectorMinHeightAtLeast(string $path, string $selectorNeedle, float $minimum, string $message): void
{
    if (!cssSelectorMinHeightAtLeast(source($path), $selectorNeedle, $minimum)) {
        fail("{$message} ({$path})");
    }
}

function expectCssFontMinimum(string $path, float $minimum): void
{
    preg_match_all('~(?<![-\w])font(?:-size)?\s*:\s*([^;{}]+)~i', source($path), $declarations, PREG_SET_ORDER);
    foreach ($declarations as $declaration) {
        preg_match_all('~([0-9]*\.?[0-9]+)px\b~i', $declaration[1], $sizes);
        foreach ($sizes[1] as $size) {
            if (floatval($size) < $minimum) {
                fail("Font size {$size}px is below {$minimum}px ({$path})");
            }
        }
    }
}

$groups = [
    'harness' => static function (): void {
        expectContains('README.md', '# orion-site', 'Repository marker is missing');
    },
    'shared' => static function (): void {
        require_once dirname(__DIR__) . '/lang.php';
        $translated_markets = i18n_translate_html('<h1>Рынки прогнозов</h1><p>Все рынки</p>');
        if (!str_contains($translated_markets, '<h1>Ринки прогнозів</h1>')
            || !str_contains($translated_markets, '<p>Усі ринки</p>')) {
            fail('Chunked localization does not translate the Markets page');
        }
        expectContains('includes/header.php', 'class="b-portalmenu"', 'Portal bar is missing');
        expectContains('includes/header.php', 'class="b-logo"', 'Logo is missing');
        expectContains('includes/header.php', "header_url('roadmap.php')", 'Roadmap navigation link is missing');
        expectContains('includes/header.php', "header_url('subscriptions.php')", 'Subscriptions navigation link is missing');
        expectContains('includes/header.php', 'aria-controls="siteNav"', 'Mobile navigation control is not wired');
        expectContains('includes/header.php', 'b-menu-mobile-only', 'Mobile account navigation is missing');
        expectContains('includes/header.php', '$page_styles', 'Page stylesheet contract is missing');
        expectContains('includes/header.php', "i18n_switcher_html('header')", 'Public language switcher is not in the top header');
        expectContains('admin.php', "i18n_switcher_html('admin')", 'Admin language switcher is not in the top bar');
        expectContains('lang.php', "function i18n_switcher_html(\$placement = 'header')", 'Language switcher placement contract is missing');
        expectNotContains('lang.php', '$switcher = i18n_switcher_html();', 'Language switcher is still injected at the bottom of every page');
        expectContains('includes/footer.php', 'class="b-footer"', 'Shared footer is missing');
        expectContains('includes/footer.php', 'Независимый некоммерческий проект, созданный сообществом энтузиастов.', 'Footer community notice changed');
        expectContains('includes/footer.php', '$page_scripts', 'Page script contract is missing');
        expectContains('js/site.js', "document.getElementById(navBtn.getAttribute('aria-controls'))", 'Mobile navigation handler ignores aria-controls');
        expectContains('js/site.js', "navBtn.setAttribute('aria-expanded', String(isOpen))", 'Mobile navigation expanded state is not updated');
        expectNotContains('js/site.js', "getElementById('navMenu')", 'Legacy mobile navigation target remains');
        expectContains('style.css', '--color-accent: #b5701a', 'Amber accent token is missing');
        expectContains('style.css', "--font-sans: 'e-Ukraine'", 'e-Ukraine body font token is missing');
        expectContains('style.css', "--font-display: 'e-Ukraine Head'", 'e-Ukraine Head display font token is missing');
        expectContains('style.css', "url('fonts/e-Ukraine-Regular.otf')", 'Local e-Ukraine font asset is not registered');
        expectContains('style.css', "url('fonts/e-UkraineHead-Bold.otf')", 'Local e-Ukraine Head font asset is not registered');
        expectContains('style.css', '.b-portalmenu', 'Portal bar styles are missing');
        expectContains('style.css', '.b-menu-mobile-only {', 'Mobile account links are not desktop-hidden');
        expectContains('style.css', '.card', 'Card component is missing');
        expectContains('style.css', '.btn-primary', 'Primary button component is missing');
        expectContains('style.css', '.form-control', 'Form control component is missing');
        expectContains('style.css', '@media (max-width: 760px)', 'Mobile breakpoint is missing');
        expectCssFontMinimum('style.css', 12);
        expectCssFontMinimum('admin.css', 12);
    },
    'home' => static function (): void {
        expectContains('index.php', 'class="home-hero"', 'Split hero is missing');
        expectContains('index.php', '<h1><?php echo h($copy[\'game_server\']); ?><br><span><?php echo h($copy[\'version\']); ?></span></h1>', 'Homepage headline must use locale-aware copy');
        expectNotContains('index.php', 'Твой ангар', 'Old homepage slogan remains');
        expectContains('index.php', 'class="server-chip server-chip--', 'Dynamic server status chip is missing');
        expectContains('index.php', 'orion_server_state($pdo)', 'Homepage server status is still hardcoded');
        expectContains('includes/header.php', 'b-portalmenu-profile', 'Shared header does not expose user profile');
        // Пути к картинкам живут в константах (db.php), поэтому проверяем
        // использование константы, а не имя файла: ассет могут заменить,
        // не трогая разметку.
        expectContains('index.php', 'class="home-hero-media home-hero-media--logo"', 'Hero logo variant is missing');
        expectContains('index.php', 'ORION_HERO_IMAGE', 'Homepage hero image constant is not used');
        expectContains('index.php', 'image_size_attrs(ORION_HERO_IMAGE)', 'Hero image has no intrinsic size attributes');
        expectContains('index.php', "\$lead_media['file_path'] ?? ORION_NEWS_COVER", 'News cards do not fall back to the shared cover');
        expectRegex('db.php', "~define\('ORION_HERO_IMAGE'~", 'Hero image constant is not defined');
        expectRegex('db.php', "~define\('ORION_NEWS_COVER'~", 'News cover constant is not defined');
        expectContains('db.php', 'function image_size_attrs', 'Intrinsic image size helper is missing');
        // Углы логотипа непрозрачно чёрные, поэтому бокс обязан быть квадратным
        // и скруглённым в круг — иначе они снова появятся на странице.
        expectContains('style.css', 'aspect-ratio: 1;', 'Logo hero box is not square');
        expectContains('style.css', 'border-radius: 50%;', 'Logo corners are not clipped away');
        expectNotContains('style.css', '.home-hero-media--logo { background: #000; }', 'Black backing behind the logo is back');
        expectRegex('style.css', '~\.home-hero-media--logo\s*\{[^}]*background:\s*none~', 'Logo hero must not paint a background');
        expectContains('index.php', 'class="stats-grid"', 'Stats grid is missing');
        expectContains('index.php', 'class="home-subscriptions-banner reveal"', 'Homepage subscriptions banner is missing');
        expectContains('index.php', '<a class="btn btn-primary" href="<?php echo h($locale_url(\'subscriptions.php\')); ?>"><?php echo h($copy[\'explore_plans\']); ?></a>', 'Homepage subscriptions call-to-action must use locale-aware URL and copy');
        expectContains('index.php', 'class="home-subscriptions-orbit"', 'Homepage subscriptions orbit artwork is missing');
        expectRegex('index.php', '~home-subscription-tier--max.*?href="<\\?php echo h\\(\\$locale_url\\(\'subscriptions\\.php#max\'\\)\\); \\?>"~s', 'Homepage Max tier must use its locale-aware subscription link');
        expectContains('index.php', 'home-subscription-tier--max', 'Homepage subscriptions banner does not show every tier');
        expectContains('style.css', '.home-subscriptions-tiers', 'Homepage subscriptions banner styling is missing');
        expectContains('lang.php', "'Исследовать тарифы' => 'Дослідити тарифи'", 'Homepage subscriptions call-to-action translation is missing');
        expectContains('index.php', 'class="news-feed"', 'News feed is missing');
        expectContains('index.php', 'class="roadmap-preview reveal"', 'Homepage roadmap preview is missing');
        expectContains('index.php', "orion_roadmap_phase(\$roadmap, \$roadmap['current_id'])", 'Homepage roadmap current phase is not data-driven');
        expectContains('index.php', 'data-target="<?php echo $total_accounts; ?>"', 'Account counter contract changed');
        expectContains('index.php', "require __DIR__ . '/includes/donate_modal.php'", 'Donation modal was removed');
        expectContains('index.php', 'foreach ($media as $idx => $item)', 'Additional news media rendering was removed');
        expectNotContains('index.php', 'hero-title--letters', 'Old letter animation remains');
    },
    'content' => static function (): void {
        expectContains('download.php', 'class="page-shell download-page"', 'Download page shell is missing');
        expectContains('download.php', 'class="download-options"', 'Download cards are missing');
        expectContains('download.php', 'class="install-steps"', 'Install steps are missing');
        expectContains('donate.php', 'class="donate-grid"', 'Donation grid is missing');
        expectContains('donate.php', 'class="wallet-card"', 'Wallet cards are missing');
        expectContains('changelog.php', 'class="release-feed"', 'Release feed is missing');
        expectContains('changelog.php', 'orion_update_history($pdo, false)', 'Public update history is not database-driven');
        expectContains('changelog.php', "echo h(\$cat['icon'])", 'Admin-authored update category icons are not escaped');
        expectContains('admin.php', "name=\"action\" value=\"save_update\"", 'Admin update history editor is missing');
        expectContains('admin.php', "name=\"action\" value=\"set_server_status\"", 'Admin server status control is missing');
        expectContains('admin.php', "name=\"action\" value=\"update_gso_implementation\"", 'Admin GSO implementation queue is missing');
        expectContains('admin.php', "\$tab === 'implement'", 'GSO implementation admin tab is missing');
        expectContains('admin.php', "name=\"action\" value=\"override_gso_status\"", 'GSO decision status override is missing');
        expectContains('includes/gso.php', 'function gso_override_status', 'Project head cannot change a decided GSO status');
        expectContains('includes/gso.php', "e.visibility = 'public'", 'Public GSO timeline does not filter internal events');
        expectContains('includes/gso.php', "ADD COLUMN visibility VARCHAR(16) NOT NULL DEFAULT 'public'", 'GSO event visibility column is not migrated');
        expectContains('admin.php', '$render_gso_internal_log(', 'Internal GSO status history is not shown in the admin panel');
        expectContains('admin.css', '.admin-status-form', 'GSO status override styling is missing');
        expectContains('gso.php', 'class="gso-chamber card"', 'GSO parliamentary chamber is missing');
        expectContains('gso.php', 'src="images/gso.png"', 'GSO image logo is missing from the council console');
        expectContains('gso.php', 'class="gso-role-groups"', 'GSO seats are not separated into role groups');
        expectContains('gso.php', 'class="gso-role-seat', 'GSO role seats are missing');
        expectContains('gso.php', 'gso-member-discord', 'GSO member Discord usernames are missing');
        expectContains('gso.php', 'class="gso-proposal-dashboard"', 'Compact GSO proposal dashboard is missing');
        expectContains('gso.php', '<details class="gso-timeline">', 'GSO proposal timeline must be collapsed by default');
        expectContains('gso.php', "\$delegate_role_groups = [", 'GSO role groups are not derived from current staff members');
        expectNotContains('gso.php', 'gso_seat_limit', 'GSO still uses an artificial configured seat limit');
        expectBefore('gso.php', 'gso-leadership-tier--project', 'gso-leadership-tier--developer', 'Project founder must be displayed before the developer');
        expectBefore('gso.php', 'gso-leadership-tier--developer', 'gso-leadership-tier--council', 'Developer must be displayed before the council head');
        expectContains('gso.php', "name=\"action\" value=\"create_proposal\"", 'GSO proposal form is missing');
        expectContains('gso.php', "name=\"action\" value=\"vote\"", 'GSO voting form is missing');
        expectContains('gso.php', "name=\"action\" value=\"head_decision\"", 'Orion council head decision form is missing');
        expectContains('gso.php', '<details class="card gso-proposal', 'GSO proposals are not collapsed list items');
        expectContains('gso.php', 'class="gso-proposal-summary"', 'Collapsed GSO proposal title is missing');
        expectContains('gso.php', "'rejection_rule'", 'Final council-head rejection rule is not explained');
        expectContains('gso.php', "'rejected_head'", 'Council-head rejection result is not handled');
        expectContains('gso.php', 'gso_can_decide_review($viewer_access)', 'Project head cannot act on passed GSO votes');
        expectContains('includes/gso.php', 'function gso_can_decide_review', 'GSO review authority check is missing');
        expectContains('includes/gso.php', 'function gso_head_decision_transition', 'GSO head rejection limit is missing');
        expectContains('includes/gso.php', "status = 'rejected_head'", 'Council-head rejection is not final');
        expectNotContains('includes/gso.php', "return 'revote'", 'Council-head rejection still starts a second vote');
        expectContains('includes/gso.php', 'PRIMARY KEY (proposal_id, vote_round, account_id)', 'GSO votes are not separated by voting round');
        expectContains('includes/gso.php', 'function gso_finalize_vote', 'GSO vote finalization workflow is missing');
        expectContains('includes/gso.php', "'council_review'", 'Passed GSO decisions do not reach the council head');
        expectContains('includes/gso.php', "'implementation'", 'Approved GSO decisions do not reach implementation');
        expectContains('db.php', 'ensure_update_history_schema($pdo)', 'Update history schema is not bootstrapped');
        expectContains('db.php', 'ensure_gso_schema($pdo)', 'GSO schema is not bootstrapped');
        expectContains('db.php', 'ensure_player_petition_schema($pdo)', 'Player petition schema is not bootstrapped');
        expectContains('includes/petitions.php', 'ORION_PLAYER_PETITION_SIGNATURE_THRESHOLD = 50', 'Player petition threshold is not fixed at fifty signatures');
        expectContains('includes/petitions.php', 'ORION_PLAYER_PETITION_COOLDOWN_DAYS = 3', 'Player petition cooldown is not fixed at three days');
        expectContains('includes/petitions.php', 'PRIMARY KEY (petition_id, account_id)', 'Player petition signatures are not unique per account');
        expectContains('includes/petitions.php', 'player_petition_promote_locked', 'Player petitions do not promote into the management workflow');
        expectContains('petitions.php', 'class="page-shell petitions-page"', 'Player petition page shell is missing');
        expectContains('petitions.php', 'name="action" value="create_petition"', 'Player petition form is missing');
        expectContains('petitions.php', 'name="action" value="sign_petition"', 'Player petition signature form is missing');
        expectContains('petitions.php', 'verify_recaptcha($_POST[\'g-recaptcha-response\'] ?? \'\')', 'Player petition creation is not protected by reCAPTCHA');
        expectContains('petitions.php', 'class="g-recaptcha"', 'Player petition reCAPTCHA widget is missing');
        expectContains('petitions.php', 'gso.php#proposal-', 'Promoted petitions do not link to the management vote');
        expectContains('petitions.css', '.petitions-layout', 'Player petition layout styling is missing');
        expectContains('sitemap.xml', 'https://projectorion.fun/gso.php', 'GSO route is missing from the sitemap');
        expectContains('gso.css', '.gso-role-group', 'GSO role-group styling is missing');
        expectContains('gso.css', '.gso-leadership-ladder', 'GSO leadership hierarchy styling is missing');
        expectContains('admin.css', '.admin-implementation-card', 'GSO implementation admin styling is missing');
        expectContains('roadmap.php', 'class="roadmap-timeline"', 'Full roadmap timeline is missing');
        expectContains('roadmap.php', 'roadmap-phase--battle', 'Battle milestone emphasis is missing');
        expectContains('subscriptions.php', 'class="page-shell subscriptions-page"', 'Subscriptions page shell is missing');
        expectContains('contracts.php', 'class="page-shell contracts-page"', 'Public contracts page shell is missing');
        expectContains('contracts.php', 'class="contracts-registry-grid"', 'Public contract registry is missing');
        expectContains('contracts.php', 'class="contracts-table"', 'Contract registry table is missing');
        expectContains('contracts.php', 'name="preferred_role"', 'Contract type selection is missing');
        expectContains('contracts.php', 'name="public_consent"', 'Public-registry consent is missing from contract submission');
        expectContains('contracts.php', 'name="action" value="renew"', 'Fifth-day renewal application is missing');
        expectContains('contracts.php', 'contract-registry-terminated-at', 'Public termination date is missing');
        expectContains('contracts.php', 'contracts-time-note', 'Kyiv-time registry note is missing');
        expectContains('contracts.php', 'class="contracts-role-lists"', 'Public contracts are not grouped into role lists');
        expectContains('contracts.php', 'data-contract-registry', 'Contract registry filter root is missing');
        expectContains('contracts.php', 'data-contract-filter="search"', 'Contract registry search filter is missing');
        expectContains('contracts.php', 'data-contract-filter="role"', 'Contract registry role filter is missing');
        expectContains('contracts.php', 'data-contract-filter="status"', 'Contract registry status filter is missing');
        expectContains('js/site.js', 'function initializeContractFilters', 'Contract registry filters are not initialized');
        expectContains('contracts.php', 'contract-cooldown-notice', 'Contract cooldown is not shown to the user');
        expectNotContains('contracts.php', 'name="action" value="sign"', 'Legacy post-approval signing is still exposed');
        expectContains('contracts.php', 'contract_pdf.php?id=', 'Contract PDF links are missing');
        expectContains('admin.php', "\$tab === 'contracts'", 'Contract review tab is missing from the admin panel');
        expectContains('admin.php', 'approve_contract_application', 'Admin contract approval action is missing');
        expectNotContains('admin.php', 'offer_contract_renewal', 'Legacy renewal flow remains in the admin panel');
        expectNotContains('admin.php', 'decline_contract_renewal', 'Legacy non-renewal flow remains in the admin panel');
        expectContains('admin.php', 'name="action" value="terminate_contract"', 'Project lead cannot terminate an active contract');
        expectContains('admin.php', 'name="termination_reason"', 'Contract termination reason is missing');
        expectContains('admin.php', 'contract_account_is_protected($pdo, $staff_account_id)', 'Manual staff-role changes can bypass an active contract');
        expectContains('admin.php', 'contract_account_is_protected($pdo, $account_id)', 'Account bans can bypass an active contract');
        expectContains('admin.php', 'contract_is_owner_admin($current_staff_access)', 'Contract decisions are not restricted to the project lead');
        expectContains('db.php', 'ensure_contract_schema($pdo)', 'Contract tables are not bootstrapped');
        expectContains('db.php', 'synchronize_contract_lifecycle($pdo)', 'Contract role lifecycle is not synchronized globally');
        expectContains('includes/contracts.php', 'ORION_CONTRACT_TERM_DAYS = 7', 'Seven-day contract term is missing');
        expectContains('includes/contracts.php', 'ORION_CONTRACT_RENEWAL_DAY = 5', 'Fifth-calendar-day renewal rule is missing');
        expectContains('includes/contracts.php', 'ORION_CONTRACT_COOLDOWN_DAYS = 7', 'Seven-day post-decision cooldown is missing');
        expectContains('includes/contracts.php', 'function contract_account_cooldown_until', 'Contract cooldown lookup is missing');
        expectContains('includes/contracts.php', 'contract_cooldown_is_active($cooldown_until, $now)', 'Contract submission does not enforce cooldown');
        expectContains('includes/contracts.php', 'function contract_create_renewal_application', 'User renewal application domain action is missing');
        expectContains('includes/contracts.php', 'contract_datetime_add_days($starts_at, ORION_CONTRACT_TERM_DAYS)', 'Approval does not calculate the seven-day expiry');
        expectContains('includes/contracts.php', "status, parent_contract_id, offered_by", 'Accepted contract is not created directly');
        expectContains('includes/contracts.php', 'SELECT is_admin, staff_role FROM accounts WHERE id = ? FOR UPDATE', 'Domain approval does not verify the project lead');
        expectContains('includes/contracts.php', 'function contract_terminate', 'Owner-only contract termination domain action is missing');
        expectContains('includes/contracts.php', "termination_cause = 'owner_decision'", 'Contract termination decision is not persisted');
        expectContains('contracts.php', 'class="contract-public-termination"', 'Public termination reason is missing');
        expectNotContains('includes/contracts.php', 'function contract_sign_offer', 'Legacy member-signing domain action remains');
        expectNotContains('includes/contracts.php', 'function contract_offer_renewal', 'Legacy renewal domain action remains');
        expectContains('includes/contract_pdf.php', "'uk' => [", 'Ukrainian PDF contract variant is missing');
        expectContains('includes/contract_pdf.php', "'ru' => [", 'Russian PDF contract variant is missing');
        expectContains('includes/contract_pdf.php', "'en' => [", 'English PDF contract variant is missing');
        expectContains('contract_pdf.php', 'ob_end_clean()', 'Binary PDFs are not protected from the HTML translation buffer');
        expectRegex('contract_pdf.php', "~define\('ORION_SKIP_LANGUAGE_REDIRECT', true\);\s*require_once __DIR__ \. '/db\.php';~", 'PDF language parameter is still intercepted by the site language redirect');
        expectContains('db.php', "!defined('ORION_SKIP_LANGUAGE_REDIRECT')", 'Site language redirect cannot be bypassed by the PDF endpoint');
        expectContains('style.css', '.contracts-registry-grid', 'Public contract registry styling is missing');
        expectContains('style.css', '.contracts-table', 'Contract registry table styling is missing');
        expectContains('style.css', '.contracts-registry-tools', 'Contract registry filter styling is missing');
        expectContains('sitemap.xml', 'https://projectorion.fun/contracts.php', 'Contracts route is missing from the sitemap');
        expectContains('markets.php', 'class="page-shell markets-page"', 'Markets page shell is missing');
        expectContains('markets.php', 'data-market-username="toffexcrf"', 'Markets page is not fixed to the requested Manifold user');
        expectContains('markets.php', 'data-bet-list', 'Full Manifold bet history container is missing');
        expectContains('js/markets.js', "API_ROOT + '/markets?limit=1000&userId='", 'Markets are not loaded through the paginated creator endpoint');
        expectContains('js/markets.js', "API_ROOT + '/bets?limit=1000&username='", 'User bets are not loaded at the maximum API page size');
        expectContains('js/markets.js', "url += '&before='", 'Manifold history pagination cursor is missing');
        expectContains('js/markets.js', 'function fetchMissingMarkets()', 'Bet history does not resolve referenced market titles');
        expectContains('markets.php', 'data-market-voting', 'Selected market has no dynamic voting container');
        expectContains('js/markets.js', 'function ensureFullMarket(marketId)', 'Selected markets do not load FullMarket answer data');
        expectContains('js/markets.js', "type === 'MULTIPLE_CHOICE' || type === 'FREE_RESPONSE'", 'Multiple-choice market voting is not supported');
        expectContains('js/markets.js', "type === 'POLL'", 'Poll market voting is not supported');
        expectContains('js/markets.js', "type === 'NUMERIC' || type === 'PSEUDO_NUMERIC'", 'Numeric market voting is not supported');
        expectContains('js/markets.js', "type === 'BOUNTIED_QUESTION'", 'Bounty question actions are not supported');
        expectContains('js/markets.js', "market.shouldAnswersSumToOne !== false", 'Dependent and independent answer markets are not distinguished');
        expectContains('style.css', '.market-answer-row', 'Multiple-choice answer row styling is missing');
        expectContains('style.css', '.market-poll-row', 'Poll option styling is missing');
        expectContains('style.css', '.market-numeric-scale', 'Numeric market styling is missing');
        expectContains('db.php', "connect-src 'self' https://api.manifold.markets", 'Content Security Policy blocks the Manifold API');
        expectContains('style.css', '.markets-workspace', 'Markets workspace styling is missing');
        expectContains('style.css', '.market-bet-row', 'Markets bet history styling is missing');
        expectContains('lang.php', "'Рынки' => 'Ринки'", 'Markets navigation translation is missing');
        expectContains('sitemap.xml', 'https://projectorion.fun/markets.php', 'Markets route is missing from the sitemap');
        expectContains('subscriptions.php', 'class="subscriptions-grid"', 'Subscriptions pricing grid is missing');
        expectContains('subscriptions.php', '<strong>$1</strong>', 'Orion Lite weekly price is missing');
        expectContains('subscriptions.php', '<strong>$5</strong>', 'Orion Plus weekly price is missing');
        expectContains('subscriptions.php', '<strong>$10</strong>', 'Orion Pro weekly price is missing');
        expectContains('subscriptions.php', '<strong>$20</strong>', 'Orion Max weekly price is missing');
        expectContains('subscriptions.php', '<button class="btn btn-secondary btn-block subscription-button" type="button" disabled><?php echo htmlspecialchars($copy[\'available_soon\'], ENT_QUOTES, \'UTF-8\'); ?></button>', 'Frontend-only subscription actions must remain disabled with locale-aware copy');
        expectContains('style.css', '.subscription-card--max', 'Subscription tier styling is missing');
        expectContains('style.css', '.subscriptions-grid { grid-template-columns: 1fr; }', 'Subscriptions mobile layout is missing');
        expectContains('lang.php', "'Подписки' => 'Підписки'", 'Subscriptions navigation translation is missing');
        expectContains('sitemap.xml', 'https://projectorion.fun/subscriptions.php', 'Subscriptions route is missing from the sitemap');
        expectNotContains('roadmap.php', 'WoT', 'Third-party abbreviation remains in the roadmap page');
        expectNotContains('includes/roadmap_data.php', 'WoT', 'Third-party abbreviation remains in roadmap content');
        expectNotContains('legal.php', 'ВОТ', 'Legacy abbreviation remains in legal content');
        expectNotContains('db.php', 'world of tanks', 'Third-party product name remains in SEO keywords');
        expectNotContains('includes/header.php', '0.6.5', 'Old server version remains in the shared header');
        expectNotContains('includes/footer.php', '0.6.5', 'Old server version remains in the shared footer');
        expectContains('includes/roadmap_data.php', "'id' => 'battle'", 'Battle roadmap milestone is missing');
        expectContains('includes/roadmap_data.php', "'id' => 'launch'", 'Launch roadmap milestone is missing');
        expectContains('includes/roadmap_data.php', "'progress_value' => 88", 'Roadmap progress contract is missing');
        expectContains('includes/donate_modal.php', 'role="dialog"', 'Donation modal lacks dialog semantics');
        expectContains('includes/donate_modal.php', 'aria-modal="true"', 'Donation modal lacks modal semantics');
        expectContains('includes/donate_modal.php', 'data-copy="#btcAddr"', 'BTC copy action changed');
        expectContains('includes/donate_modal.php', 'data-copy="#ltcAddr"', 'LTC copy action changed');
        expectContains('download.php', '<b>Внимание:</b> по ссылке скачивается архив, размещённый на <b>стороннем файлообменнике</b>. Этот архив <b>никак не связан</b> с нашим сайтом и проектом: мы его <b>не создавали, не размещаем и не храним</b> на своих серверах, а лишь приводим внешнюю ссылку для удобства. Все права на содержимое архива принадлежат их законным правообладателям. Скачивая файл, вы делаете это <b>самостоятельно и на свой страх и риск</b>.', 'Download archive legal notice was abridged');
        expectContains('includes/donate_modal.php', 'Все пожертвования являются добровольной поддержкой автора. Пожертвование не является покупкой товара или услуги и не предоставляет никаких игровых преимуществ.', 'Donation legal terms were abridged');
        expectContains('includes/donate_modal.php', 'Размер пожертвования никак не влияет на игровой процесс', 'Donation non-influence term is missing');
        expectContains('includes/donate_modal.php', 'Донат не даёт донатеру преимуществ перед другими игроками', 'Donation no-advantages term is missing');
        expectContains('includes/donate_modal.php', 'Поддержка идёт на оплату хостинга и развитие проекта', 'Donation use-of-funds term is missing');
        expectRegex('js/site.js', "~function openModal\(id\).*?setModal\(m, true\).*?dialog\.focus\(\);~s", 'Donation modal open state and focus are not scoped to openModal');
        expectRegex('js/site.js', "~function closeModal\(el\).*?setModal\(m, false\).*?modalReturnFocus\.focus\(\);~s", 'Donation modal close state and focus restoration are not scoped to closeModal');
        expectRegex('js/site.js', "~function trapModalFocus\(modal, event\).*?event\.shiftKey.*?last\.focus\(\).*?!event\.shiftKey.*?first\.focus\(\);~s", 'Donation modal focus trap is incomplete');
        expectRegex('js/site.js', "~else if \(e\.key === 'Tab'\) \{\s*trapModalFocus\(open, e\);~s", 'Donation modal key handler does not invoke the focus trap');
        expectContains('style.css', '.video-card video { display: block; width: 100%; height: auto; aspect-ratio: 16 / 9; object-fit: contain; }', 'Download video can crop at desktop widths');
        expectNotContains('style.css', '.video-card video { min-height: 0; }', 'Obsolete mobile-only video fix remains');
        expectRegex('style.css', '~@media \(prefers-reduced-motion: reduce\) \{\s*body \{ animation: none; \}\s*\.dl-pane\.active \{ animation: none; \}\s*\.donate-modal \{ transition: none; \}~s', 'Reduced-motion overrides do not cover download panels and the donation modal');
        expectContains('download.php', 'launcher/RebornLauncher-v1.1-R2.zip', 'Launcher archive download is missing');
        expectNotContains('download.php', 'кнопкой слева', 'Desktop-only patch direction remains');
    },
    'theme' => static function (): void {
        expectContains('includes/header.php', '<html lang="<?php echo htmlspecialchars($header_lang, ENT_QUOTES, \'UTF-8\'); ?>" data-theme="dark">', 'Document language must be dynamic while dark remains the no-script default');
        // Номер в ?v= — кеш-бастер, он меняется при каждой правке файла.
        // Контракт здесь в том, что контроллер темы грузится из <head>, а не в
        // конкретной версии, поэтому версию не фиксируем.
        expectRegex('includes/header.php', '~<script src="js/theme\.js\?v=\d+"></script>~', 'Early theme controller is not loaded from the document head');
        expectContains('includes/header.php', 'data-theme-toggle', 'Public theme toggle is missing');
        expectContains('admin.php', 'class="admin-topbar-actions"', 'Admin theme controls are not grouped');
        expectContains('admin.php', 'data-theme-toggle', 'Admin theme toggle is missing');
        expectContains('style.css', ':root[data-theme="dark"]', 'Dark palette selector is missing');
        expectContains('style.css', '--color-elevated: #1c1e25;', 'Dark elevated-surface token is missing');
        expectContains('style.css', '[data-theme="dark"] .theme-icon--moon { display: block; }', 'Theme icon state is not styled');
        expectContains('js/theme.js', "var STORAGE_KEY = 'orion-theme';", 'Theme preference key is missing');
        expectContains('js/theme.js', "applyTheme(readStoredTheme() || 'dark', false);", 'Dark default and saved preference are not initialized');
        expectContains('js/theme.js', "button.setAttribute('aria-pressed', isDark ? 'true' : 'false');", 'Theme toggle accessibility state is not synchronized');
        expectContains('js/theme.js', "window.addEventListener('storage'", 'Theme changes are not synchronized between tabs');
    },
    'interactions' => static function (): void {
        expectContains('js/site.js', 'function setNav(isOpen, navBtn)', 'Navigation state controller is missing');
        expectContains('js/site.js', "document.getElementById(navBtn.getAttribute('aria-controls'))", 'Navigation does not honor aria-controls');
        expectContains('js/site.js', "classList.add('is-open')", 'Open-state convention is missing');
        expectContains('js/site.js', "classList.remove('is-open')", 'Closed-state convention is missing');
        expectContains('js/site.js', "navBtn.setAttribute('aria-expanded', String(isOpen))", 'Navigation accessibility state is missing');
        expectContains('js/site.js', 'function setModal(modal, open)', 'Modal state controller is missing');
        expectContains('js/site.js', "modal.setAttribute('aria-hidden', open ? 'false' : 'true')", 'Modal accessibility state is missing');
        expectContains('js/site.js', "document.body.classList.toggle('modal-open', open)", 'Modal body state is missing');
        expectContains('js/site.js', 'modalReturnFocus.focus();', 'Modal focus return was removed');
        expectContains('js/site.js', 'trapModalFocus(open, e);', 'Modal focus trap was removed');
        expectContains('js/site.js', "e.target.closest('.dl-tab')", 'Download tabs are not delegated');
        expectContains('js/site.js', "querySelectorAll('.dl-tab')", 'Download tabs are not initialized');
        expectContains('js/site.js', "setAttribute('aria-selected', selected ? 'true' : 'false')", 'Download tab selection is not exposed');
        expectContains('js/site.js', 'pane.hidden = !selected;', 'Download pane visibility is not synchronized');
        expectContains('js/site.js', 'window.dlTab = activateDownloadTab;', 'Existing download tab callers are not preserved');
        expectContains('js/site.js', "return tab.getAttribute('data-tab') === requested;", 'Download hash selection is not matched safely');
        expectNotContains('js/site.js', "'.dl-tab[data-tab=\"' + requested + '\"]'", 'Download hash is interpolated into a selector');
        expectContains('download.php', 'class="dl-tabs" role="tablist"', 'Download tablist semantics are missing');
        expectContains('download.php', 'aria-controls="pane-client" aria-selected="true"', 'Initial client tab semantics are missing');
        expectNotContains('download.php', 'function dlTab(name)', 'Download behavior remains duplicated inline');
        expectContains('js/site.js', "document.execCommand('copy')", 'Clipboard fallback was removed');
        expectContains('js/site.js', "document.body.getAttribute('data-show-popup') === '1'", 'Donation auto-popup hook was removed');
        expectRegex('js/site.js', "~function initializeCounters\(\).*?if \(REDUCED \|\| !\('IntersectionObserver' in window\)\).*?showFinalCounterValues\(counters\);~s", 'Counters do not handle reduced motion or a missing IntersectionObserver');
        expectContains('style.css', '.b-portal-menu.is-open { display: flex; }', 'Navigation CSS does not use the shared open state');
        expectContains('style.css', '.donate-modal.is-open { visibility: visible; opacity: 1; }', 'Modal CSS does not use the shared open state');
        expectContains('style.css', 'body.modal-open { overflow: hidden; }', 'Modal scroll locking is missing');
        expectNotContains('style.css', '.site-nav.open {', 'Legacy navigation open state remains');
        expectNotContains('style.css', '.donate-modal.show {', 'Legacy modal open state remains');
        expectNotContains('includes/footer.php', 'orion-sky.js', 'Legacy sky script is still loaded');
        expectFileNotExists('js/orion-sky.js', 'Legacy sky script still exists');
        expectNotContains('style.css', '--sky-deep', 'Legacy starfield tokens remain');
        expectNotContains('style.css', '.rivet-strip', 'Legacy rivet styles remain');
    },
    'account' => static function (): void {
        foreach (['login.php', 'register.php', 'reset_password.php', 'verify.php'] as $path) {
            expectContains($path, 'class="page-shell auth-page"', "Auth shell is missing in {$path}");
            expectNotContains($path, 'style="max-width:', "Inline auth width remains in {$path}");
        }
        expectContains('login.php', 'name="username"', 'Login username field changed');
        expectContains('login.php', 'name="password"', 'Login password field changed');
        expectContains('login.php', 'discord.php?action=login', 'Discord login action is missing');
        expectContains('register.php', 'discord.php?action=register', 'Discord registration action is missing');
        expectContains('register.php', 'account_discord_links', 'Discord registration does not save the link');
        expectContains('discord.php', "'mode' => \$mode", 'Discord OAuth flow mode is not stored');
        expectContains('db.php', "const ORION_REMEMBER_LIFETIME = 2592000", 'Persistent login lifetime is not 30 days');
        expectContains('db.php', "const ORION_REMEMBER_REFRESH_WINDOW = 604800", 'Persistent login is not refreshed during its final week');
        expectContains('db.php', 'CREATE TABLE IF NOT EXISTS account_remember_tokens', 'Persistent login token table is missing');
        expectContains('db.php', "hash('sha256', \$validator)", 'Persistent login stores an unhashed validator');
        expectContains('db.php', 'function orion_restore_remembered_login($pdo)', 'Persistent login restoration is missing');
        expectContains('db.php', "!empty(\$_SESSION['user_id']) && !orion_remember_cookie_parts()", 'Existing authenticated sessions do not receive a persistent login');
        expectContains('db.php', "'httponly' => true", 'Persistent login cookie is readable by JavaScript');
        expectContains('db.php', "'samesite' => 'Lax'", 'Persistent login cookie has no SameSite policy');
        expectContains('login.php', 'orion_issue_remember_token($pdo', 'Successful login does not create a persistent session');
        expectContains('logout.php', 'orion_forget_remember_token($pdo);', 'Logout does not revoke the persistent session');
        expectContains('profile.php', 'orion_revoke_account_remember_tokens($pdo, $user_id);', 'Password change leaves old persistent sessions active');
        expectContains('reset_password.php', 'orion_revoke_account_remember_tokens($pdo', 'Password reset leaves old persistent sessions active');
        expectContains('admin.php', 'orion_revoke_account_remember_tokens($pdo, $account_id);', 'Administrative password reset leaves persistent sessions active');
        expectContains('register.php', 'class="g-recaptcha"', 'Registration Recaptcha was removed');
        expectContains('profile.php', 'class="page-shell profile-page"', 'Profile shell is missing');
        expectContains('profile.php', 'class="profile-metrics"', 'Profile metrics are missing');
        expectContains('profile.php', 'class="player-stat-overview player-stat-overview--compact"', 'Profile battle overview is missing');
        expectContains('profile.php', 'discord.php?action=connect', 'Discord link action is missing');
        expectContains('profile.php', 'name="unlink_discord"', 'Discord unlink action is missing');
        expectContains('discord.php', "hash_equals((string)\$oauth_state['value']", 'Discord OAuth state validation is missing');
        expectContains('discord.php', "'scope' => 'identify'", 'Discord OAuth requests excessive permissions');
        expectContains('discord.php', "\$discord_user['username']", 'Discord link does not store the Discord username');
        expectNotContains('discord.php', "\$discord_user['global_name']", 'Discord link stores the display name instead of username');
        expectContains('discord.php', 'discord_store_oauth_tokens', 'Discord OAuth tokens are not persisted for automatic sync');
        expectContains('discord.php', "'silent'", 'Discord silent reauthorization mode is missing');
        expectContains('profile.php', 'discord_sync_account_link', 'Profile does not refresh the Discord username automatically');
        expectContains('profile.php', 'discord_auto_sync_attempted_at', 'Legacy Discord links do not auto-start synchronization');
        expectNotContains('profile.php', 'Включить синхронизацию username', 'Discord sync still requires a manual enable button');
        expectContains('includes/discord_oauth.php', 'function discord_sync_account_link', 'Discord automatic sync helper is missing');
        expectContains('db.php', 'CREATE TABLE IF NOT EXISTS account_discord_links', 'Discord account link table is missing');
        expectContains('db.php', 'access_token_encrypted', 'Discord access token storage columns are missing');
        expectContains('includes/header.php', "header_url('players.php')", 'Player profiles navigation tab is missing');
        expectContains('players.php', 'class="page-shell players-page"', 'Player search page shell is missing');
        expectContains('players.php', 'name="username"', 'Player username search field is missing');
        expectContains('players.php', 'class="profile-card player-profile-card"', 'Public player profile card is missing');
        expectContains('players.php', 'profile-discord-handle', 'Public player profile does not show Discord username');
        expectContains('players.php', 'class="player-stat-overview"', 'Public player statistics overview is missing');
        expectNotContains('players.php', "SELECT email", 'Public profile query exposes player email');
        expectNotContains('players.php', "SELECT credits", 'Public profile query exposes account resources');
        expectContains('players.php', "(\$_GET['ajax'] ?? '') === 'player-suggestions'", 'Player autocomplete endpoint is missing');
        expectContains('players.php', 'load_player_rankings($pdo', 'Player rankings are not loaded server-side');
        expectContains('players.php', 'class="player-rankings"', 'Player ranking block is missing');
        expectContains('players.php', 'role="tablist"', 'Player ranking tablist is missing');
        expectContains('players.php', 'data-player-ranking-tab', 'Player ranking tab hook is missing');
        expectContains('players.php', 'role="tabpanel"', 'Player ranking panel is missing');
        expectContains('players.php', 'data-player-input', 'Player autocomplete input hook is missing');
        expectContains('players.php', 'role="listbox"', 'Player autocomplete listbox is missing');
        expectContains('players.php', 'LIMIT 8', 'Player autocomplete result limit is missing');
        expectContains('players.php', "'suggestions'", 'Player autocomplete JSON envelope is missing');
        expectRegex('players.php', "~'js/players\\.js\\?v=\\d+'~", 'Player interaction script is not registered');
        expectNotContains('players.php', 'SELECT email', 'Public player endpoint exposes email');
        expectNotContains('players.php', 'SELECT credits', 'Public player endpoint exposes account resources');
        expectContains('style.css', '.player-rankings', 'Player ranking styles are missing');
        expectContains('style.css', '.player-suggestions', 'Player suggestion styles are missing');
        expectContains('style.css', '@media (max-width: 760px)', 'Player ranking mobile styles are missing');
        expectContains('includes/header.php', 'href="style.css?v=37"', 'Global style cache version was not bumped');
        expectContains('lang.php', "'Топ игроков' => 'Топ гравців'", 'Player ranking title is not localized');
        expectContains('lang.php', "'Средний опыт' => 'Середній досвід'", 'Average XP ranking is not localized');
        expectContains('lang.php', "'Победы' => 'Перемоги'", 'Wins ranking is not localized');
        expectContains('lang.php', "'Рейтинг временно недоступен.' => 'Рейтинг тимчасово недоступний.'", 'Ranking error is not localized');
        expectContains('lang.php', "'Глава совета Ориона' => 'Голова ради Оріона'", 'Council head title with project name is not localized');
        expectContains('legal.php', 'class="page-shell legal-page"', 'Legal shell is missing');
        expectContains('legal.php', 'class="legal-copy"', 'Legal reading column is missing');
    },
    'bugs' => static function (): void {
        expectContains('bugs.php', 'class="page-shell bugs-page"', 'Bug list shell is missing');
        expectContains('bugs.php', 'class="bug-toolbar"', 'Bug filters are missing');
        expectContains('bugs.php', 'class="bug-list"', 'Bug list is missing');
        expectContains('bug_view.php', 'class="page-shell bug-detail-page"', 'Bug detail shell is missing');
        expectContains('bug_view.php', 'class="bug-detail-grid"', 'Bug detail grid is missing');
        expectContains('bug_view.php', 'class="comment-list"', 'Comment list is missing');
        expectContains('bug_view.php', 'class="danger-zone"', 'Danger zone is missing');
        expectContains('bug_view.php', 'name="csrf_token"', 'Bug CSRF fields were removed');
        expectContains('style.css', '.bugs-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px;', 'Bug report column is too narrow for Recaptcha');
        expectContains('style.css', '.bug-report-card .g-recaptcha { width: 304px; max-width: none; transform-origin: top left; }', 'Bug report Recaptcha desktop sizing is missing');
        expectRegex('style.css', '~@media \(max-width: 380px\) \{.*?\.bug-report-card \.recaptcha-field \{ height: 63px; \}.*?\.bug-report-card \.g-recaptcha \{ transform: scale\(\.8\); \}~s', 'Bug report Recaptcha narrow-screen scaling is missing');
        expectNotContains('style.css', '.bug-card:hover { border-color: rgba(181, 112, 26, .35); box-shadow: var(--shadow-soft);', 'Bug card uses an undefined shadow token');
        expectNotContains('style.css', '.access-denied-page { display: grid; min-height: 100vh; padding: 28px; place-items: center; background: var(--color-bg);', 'Denied-access page uses an undefined canvas token');
        expectNotContains('style.css', 'box-shadow: var(--shadow-soft); text-align: center;', 'Denied-access card uses an undefined shadow token');
    },
    'admin-shell' => static function (): void {
        // Версия в ?v= — кеш-бастер и меняется при каждой правке ассета,
        // поэтому проверяем факт подключения, а не конкретный номер.
        expectRegex('admin.php', '~\$page_styles = \[\'admin\.css\?v=\d+\'\];~', 'Admin stylesheet is not registered');
        expectRegex('admin.php', '~\$page_scripts = \[\'js/admin\.js\?v=\d+\'\];~', 'Admin script is not registered');
        expectContains('admin.php', 'class="admin-layout"', 'Admin layout is missing');
        expectContains('admin.php', 'class="admin-sidebar"', 'Admin sidebar is missing');
        expectContains('admin.php', 'data-admin-sidebar-toggle', 'Admin mobile toggle is missing');
        expectContains('admin.php', 'data-admin-sidebar-close', 'Admin mobile backdrop is missing');
        expectContains('admin.php', 'window.OrionAdminConfig', 'Admin configuration bridge is missing');
        expectContains('admin.php', 'JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT', 'Admin configuration is not script-context encoded');
        expectNotContains('admin.php', 'JSON_UNESCAPED_SLASHES', 'Admin configuration permits a literal closing script tag');
        expectContains('admin.php', "'csrfToken' => \$csrf_token", 'Admin CSRF configuration key changed');
        expectContains('admin.php', "'selectedAccountId' => intval(\$selected_account_id)", 'Admin account configuration key changed');
        expectContains('admin.php', "'filteredVehicleNames' => array_values(\$filtered_vehicle_names)", 'Admin vehicle configuration key changed');
        $script_payload = '</script><script>alert("orion")</script>';
        $script_json = json_encode(['value' => $script_payload], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if ($script_json === false || str_contains($script_json, '</script>') || !str_contains($script_json, '\u003C\/script\u003E')) {
            fail('Admin configuration flags do not neutralize a closing script payload');
        }
        expectContains('admin.css', '.admin-layout', 'Admin layout CSS is missing');
        expectContains('js/admin.js', 'OrionAdminConfig', 'Admin JS does not consume its configuration');
        expectNotContains('admin.php', 'color:#fff', 'Ban target retains unreadable white inline text');
        expectNotContains('admin.php', 'background:#131b26', 'Download settings retain dark inline inputs');
        expectNotContains('js/admin.js', 'background:#131b26', 'Dynamic mirror rows retain dark inline inputs');
        expectContains('admin.php', 'class="ban-target-name"', 'Ban target does not use its light-theme class');
        expectContains('admin.php', 'class="form-control mirror-input', 'Saved mirror controls do not use light-theme classes');
        expectContains('js/admin.js', 'class="form-control mirror-input', 'Dynamic mirror controls do not use light-theme classes');
        expectContains('admin.css', '.ban-target-name', 'Ban target light-theme CSS is missing');
        expectContains('admin.css', '.mirror-input', 'Mirror input light-theme CSS is missing');
        expectContains('admin.css', 'overflow-y: auto;', 'Admin sidebar cannot scroll in a short viewport');
        expectRegex('admin.css', '~@media \(max-width: 820px\) \{.*?\.admin-sidebar \{[^}]*visibility: hidden;[^}]*pointer-events: none;.*?\.admin-sidebar\.is-open \{ transform: translateX\(0\); visibility: visible; pointer-events: auto; \}~s', 'Mobile sidebar hidden/open interaction states are incomplete');
        expectContains('admin.css', '.admin-sidebar.is-open { transform: translateX(0); visibility: visible; pointer-events: auto; }', 'Open mobile sidebar state is incomplete');
        expectContains('js/admin.js', "sidebar.setAttribute('aria-hidden', String(!open));", 'Mobile sidebar aria-hidden state is not synchronized');
        expectContains('js/admin.js', "sidebar.toggleAttribute('inert', !open);", 'Closed mobile sidebar is not removed from focus order');
        expectContains('js/admin.js', "sidebar.removeAttribute('aria-hidden');", 'Desktop sidebar aria-hidden state is not restored');
        expectContains('js/admin.js', "sidebar.removeAttribute('inert');", 'Desktop sidebar focusability is not restored');
        expectContains('admin.php', 'data-admin-sidebar-dismiss', 'Admin drawer close button is missing');
        expectContains('js/admin.js', "closers.forEach(function (close)", 'Admin sidebar close controls are not initialized');
        expectContains('admin.css', '.admin-sidebar.is-open + .admin-sidebar-backdrop', 'Admin mobile backdrop open state is missing');
        expectContains('includes/staff.php', 'function staff_notification_feed', 'Staff notification feed is missing');
        expectContains('includes/staff.php', 'function staff_notifications_mark_read', 'Staff notification read action is missing');
        expectContains('includes/staff.php', 'CREATE TABLE IF NOT EXISTS staff_notification_reads', 'Staff notification read state table is missing');
        expectContains('includes/staff.php', 'staff-notifications-read-form', 'Staff notification read button is missing');
        expectContains('includes/header.php', 'staff_notifications_html($pdo, 8)', 'Public pages do not expose staff notifications');
        expectContains('admin.php', 'staff_notifications_html($pdo, 8)', 'Admin does not expose staff notifications');
        expectContains('notifications.php', 'staff_notifications_mark_read', 'Notification read endpoint is missing');
        expectContains('notifications.php', 'verify_csrf', 'Notification read endpoint does not validate CSRF');
        expectContains('style.css', '.staff-notifications-panel', 'Staff notification panel styling is missing');
        expectNotContains('admin.php', '<canvas class="admin-fx"', 'Old admin particle canvas remains');
        expectNotContains('admin.php', 'function startAdminFx()', 'Old particle code remains');
    },
    'admin-workspaces' => static function (): void {
        expectContains('admin.php', 'class="admin-section admin-section--vehicles"', 'Vehicle workspace marker is missing');
        expectContains('admin.php', 'class="admin-section admin-section--news"', 'News workspace marker is missing');
        expectContains('admin.php', 'class="admin-section admin-section--users"', 'Users workspace marker is missing');
        expectContains('admin.php', 'class="admin-section admin-section--bans"', 'Bans workspace marker is missing');
        expectContains('admin.php', 'class="admin-section admin-section--downloads"', 'Downloads workspace marker is missing');
        expectContains('admin.php', 'class="admin-table-wrap"', 'Responsive table wrapper is missing');
        expectContains('admin.php', 'class="admin-editor-grid"', 'News editor grid is missing');
        expectContains('admin.php', 'class="mirror-list"', 'Download mirror list is missing');
        expectRegex('admin.php', '~admin-section--vehicles.*?admin-hero-strip.*?admin-metrics.*?admin-workspace~s', 'Vehicle heading and metrics are not aligned above the workspace');
        expectContains('admin.php', 'class="bulk-action-groups"', 'Bulk actions are not grouped consistently');
        expectContains('admin.css', '.bulk-action-group', 'Bulk action group styling is missing');
        expectContains('js/admin.js', 'class="btn btn-secondary mirror-remove admin-row-action"', 'Dynamic mirror remove action does not use the shared row-action class');
        expectRegex('admin.php', '~id="client-mirrors".*?onclick="removeMirrorRow\(this\)".*?id="patch-mirrors".*?onclick="removeMirrorRow\(this\)"~s', 'Saved mirror rows do not use the shared removal lifecycle');
        expectContains('js/admin.js', 'function syncMirrorControls(containerId, prefix)', 'Mirror controls do not derive state from the DOM');
        expectContains('js/admin.js', 'function removeMirrorRow(button)', 'Shared mirror removal function is missing');
        expectContains('js/admin.js', 'button.hidden = count >= 5;', 'Mirror add-button visibility is not synchronized');
        expectContains('js/admin.js', "syncMirrorControls('client-mirrors', 'client');", 'Initial client mirror state is not synchronized');
        expectContains('js/admin.js', "syncMirrorControls('patch-mirrors', 'patch');", 'Initial patch mirror state is not synchronized');
        expectContains('admin.php', 'data-activity-chart', 'Dashboard activity chart hook is missing');
        expectContains('admin.php', 'data-chart-tooltip', 'Dashboard activity tooltip is missing');
        expectContains('admin.php', 'data-chart-accounts=', 'Dashboard chart values are not exposed to its tooltip');
        expectContains('js/admin.js', 'function initActivityChart()', 'Dashboard activity hover is not initialized');
        expectContains('admin.css', '.activity-chart-tooltip', 'Dashboard activity tooltip styling is missing');
        expectNotContains('js/admin.js', 'mirrorCount[prefix]--;', 'Dynamic mirror removal still mutates a stale counter');
        expectCssSelectorMinHeightAtLeast('admin.css', '.admin-row-action', 40, 'Admin row actions remain below the touch-target minimum');
        expectCssSelectorMinHeightAtLeast('admin.css', '.mirror-remove', 40, 'Mirror remove controls override the touch-target minimum');
        $safe_touch_css = ".admin-downloads-form\n  .mirror-remove {\n    min-height : 40px;\n}";
        if (!cssSelectorMinHeightAtLeast($safe_touch_css, '.mirror-remove', 40)) {
            fail('CSS touch-target parser does not accept multiline, more-specific selectors');
        }
        $unsafe_touch_css = ".mirror-remove { min-height: 40px; }\n@media (max-width: 620px) {\n  .admin-downloads-form .mirror-remove {\n    min-height: 36px;\n  }\n}";
        if (cssSelectorMinHeightAtLeast($unsafe_touch_css, '.mirror-remove', 40)) {
            fail('CSS touch-target parser misses a later, more-specific 36px override');
        }
        expectNotContains('admin.php', '<style>', 'Admin inline stylesheet remains');
        expectNotContains('admin.php', 'background:#131b26', 'Legacy dark inline controls remain');
        expectNotContains('js/admin.js', 'style.cssText', 'Dynamic mirror rows retain inline presentation');
    },
    'staff-rbac' => static function (): void {
        require_once dirname(__DIR__) . '/includes/staff.php';
        $roles = staff_role_definitions();
        $permissions = staff_permission_catalog();
        foreach (['admin', 'orion_council_head', 'developer', 'senior_moderator', 'moderator', 'content_maker', 'player'] as $role) {
            if (!isset($roles[$role])) {
                fail("Staff role {$role} is missing");
            }
        }
        if (!($roles['admin']['rank'] > $roles['developer']['rank']
            && $roles['developer']['rank'] > $roles['orion_council_head']['rank']
            && $roles['orion_council_head']['rank'] > $roles['senior_moderator']['rank']
            && $roles['senior_moderator']['rank'] > $roles['moderator']['rank']
            && $roles['moderator']['rank'] > $roles['content_maker']['rank']
            && $roles['content_maker']['rank'] > $roles['player']['rank'])) {
            fail('Staff role hierarchy is not strictly ordered');
        }
        foreach (['dashboard.view', 'reports.manage', 'users.view', 'bans.manage', 'vehicles.manage', 'staff.manage', 'audit.view'] as $permission) {
            if (!isset($permissions[$permission])) {
                fail("Staff permission {$permission} is missing");
            }
        }
        expectContains('db.php', 'ensure_staff_schema($pdo);', 'Staff schema is not installed');
        expectContains('db.php', 'refresh_session_staff_access($pdo);', 'Staff session access is not refreshed');
        expectContains('includes/staff.php', 'CREATE TABLE IF NOT EXISTS staff_permission_overrides', 'Permission override table is missing');
        expectContains('includes/staff.php', 'CREATE TABLE IF NOT EXISTS staff_action_log', 'Staff audit table is missing');
        expectContains('admin.php', 'function require_ajax_permission($permission)', 'AJAX permission guard is missing');
        expectContains('admin.php', 'function require_account_below_actor($pdo, $account_id)', 'Hierarchy account guard is missing');
        expectContains('admin.php', "'save_staff_access' => ['permission' => 'staff.manage'", 'Staff access action is not protected');
        expectContains('admin.php', "'delete_bug_report' => ['permission' => 'reports.delete'", 'Report deletion permission is missing');
        expectContains('admin.php', "'close_all_bug_reports' => ['permission' => 'reports.manage'", 'Close-all-reports action is not protected');
        expectRegex('admin.php', '~if \(\$action === \'close_all_bug_reports\'\) \{\s*if \(!contract_is_owner_admin\(\$current_staff_access\)\)~', 'Close-all-reports action is not restricted to the project lead');
        expectContains('admin.php', "UPDATE bug_reports SET status = 'closed' WHERE status <> 'closed'", 'Close-all-reports action does not close every open report');
        expectContains('admin.php', "SELECT COUNT(*) FROM bug_reports WHERE is_approved = 0 AND status <> 'closed'", 'Closed reports remain in the pending-review counter');
        expectContains('admin.php', "b.status <> 'closed' AND (b.is_approved = 0 OR b.status IN ('open', 'in_progress'))", 'Closed reports remain in the moderation queue');
        expectContains('admin.php', "\$report['status'] === 'closed' ? 'не публичный' : 'на проверке'", 'Closed private reports are still labelled as awaiting review');
        expectContains('admin.php', 'name="action" value="close_all_bug_reports"', 'Project lead close-all-reports button is missing');
        expectContains('bugs.php', 'if (!$is_approved && !$is_closed)', 'Closed reports still offer a public-list approval action');
        expectContains('bug_view.php', 'if (!$is_approved && !$is_closed)', 'Closed reports still offer a detail-view approval action');
        expectContains('admin.php', "if (!admin_can('users.edit'))", 'Report-author restriction is not separated from report processing');
        expectContains('bug_view.php', "\$can_restrict_report_authors = session_has_staff_permission('users.edit');", 'Detailed report view lets report-only moderators restrict players');
        expectContains('includes/staff.php', "\$role !== 'admin' && empty(\$role_info['permissions_fixed'])", 'Fixed moderator permissions can be bypassed by personal overrides');
        expectNotContains('admin.php', 'name="is_admin"', 'Legacy administrator checkbox bypasses role management');
        expectContains('admin.php', 'class="admin-section admin-section--dashboard"', 'Staff dashboard is missing');
        expectContains('admin.php', 'class="admin-section admin-section--reports"', 'Moderation queue is missing');
        expectContains('admin.php', 'class="admin-section admin-section--staff"', 'Staff management workspace is missing');
        expectContains('admin.php', 'class="admin-section admin-section--audit"', 'Audit workspace is missing');
        expectContains('admin.php', '$report_limit = 30;', 'Moderation queue pagination limit is missing');
        expectContains('admin.php', 'aria-label="Страницы очереди репортов"', 'Moderation queue pagination is missing');
        expectContains('admin.css', '.dashboard-kpi-grid', 'Dashboard KPI styling is missing');
        expectContains('admin.css', '.permission-control-row', 'Permission editor styling is missing');
        expectContains('includes/header.php', 'session_is_staff()', 'Staff management navigation is missing');
        expectContains('bugs.php', "session_has_staff_permission('reports.manage')", 'Public report list ignores moderator permission');
        expectContains('bug_view.php', "session_has_staff_permission('reports.delete')", 'Report detail deletion permission is missing');
    },
];

$selected = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--group=')) {
        $selected = substr($arg, 8);
    }
}

if ($selected !== null && !isset($groups[$selected])) {
    fwrite(STDERR, "Unknown group: {$selected}\n");
    exit(2);
}

foreach ($groups as $name => $test) {
    if ($selected !== null && $selected !== $name) {
        continue;
    }
    $test();
    fwrite(STDOUT, "Checked {$name}\n");
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: {$failure}\n");
    }
    exit(1);
}

fwrite(STDOUT, "All UI contract checks passed.\n");
