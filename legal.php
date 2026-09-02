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
$copy = [
    'ru' => [
        'page_title' => 'Правовая информация — сервер Project Orion 0.8.2',
        'page_description' => 'Правовая информация независимого некоммерческого сервера Project Orion 0.8.2.',
        'banner_subtext' => 'Игровой сервер · 0.8.2',
        'updated' => 'Последнее обновление:',
    ],
    'uk' => [
        'page_title' => 'Правова інформація — сервер Project Orion 0.8.2',
        'page_description' => 'Правова інформація незалежного некомерційного сервера Project Orion 0.8.2.',
        'banner_subtext' => 'Ігровий сервер · 0.8.2',
        'updated' => 'Останнє оновлення:',
    ],
    'en' => [
        'page_title' => 'Legal information: Project Orion server 0.8.2',
        'page_description' => 'Legal information for the independent, non-commercial Project Orion server 0.8.2.',
        'banner_subtext' => 'Game server · 0.8.2',
        'updated' => 'Last updated:',
    ],
][$ui_lang];
$page_title = $copy['page_title'];
$page_description = $copy['page_description'];
$page_path = 'legal.php';
$active_page = 'legal';
$banner_subtext = $copy['banner_subtext'];
require __DIR__ . '/includes/header.php';
?>

<main class="page-shell legal-page">
    <article class="legal-copy">
        <p class="eyebrow">PROJECT ORION</p>
        <h1><?php echo $ui_lang === 'en' ? 'Legal information and terms of use' : 'Правовая информация и условия использования'; ?></h1>

        <?php if ($ui_lang === 'en'): ?>
            <div class="legal-callout">
                <p><strong>Project Orion server 0.8.2</strong> is a community-made, non-commercial hobby project. Server access is provided free of charge.</p>
            </div>

            <div class="legal-section">
                <h2>About the project name</h2>
                <p>The project uses its own name, <strong>Project Orion</strong>, referring to the <strong>Orion constellation</strong> shown on the project emblem. The name is used as the independent name of the server and its community.</p>
                <p>The project name <strong>has no connection</strong> with any commercial computer game, its rights holders, trademarks, logos, or brands. Any similarity in abbreviations is coincidental and does not imply a relationship, affiliation, or endorsement by any rights holder.</p>
            </div>

            <div class="legal-section">
                <h2>1. Independent project</h2>
                <p>This is an <strong>independent</strong> community project. It is not affiliated with, sponsored by, endorsed by, or otherwise connected to any third-party company, developer, or game publisher.</p>
            </div>

            <div class="legal-section">
                <h2>2. Third-party intellectual property</h2>
                <ul>
                    <li>All trademarks, logos, names, images, and other materials mentioned or used anywhere remain the property of their lawful rights holders.</li>
                    <li>We <strong>do not claim</strong> ownership of third-party intellectual property or take credit for another party&apos;s work.</li>
                    <li>Any third-party names that appear are used solely for descriptive and nominative purposes.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>3. Our files and source code</h2>
                <p>All software hosted <strong>on our servers</strong> and <strong>on this website</strong> is <strong>our own work</strong>. The server, networking code, website, database, and related tools were written from scratch by project contributors and do not contain files belonging to third parties.</p>
                <ul>
                    <li>We <strong>do not host, distribute, or store</strong> third-party game clients, resources, executables, or other copyrighted materials on our servers or website.</li>
                    <li>All code we publish and use is written by us and is <strong>original</strong>.</li>
                    <li>To connect to the server, users acquire and install the client <strong>independently</strong>; we do not provide or distribute it.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>4. Non-commercial nature</h2>
                <p>The project is <strong>fully non-commercial</strong>. We do <strong>not sell</strong> the game, in-game currency, vehicles, accounts, or other in-game goods.</p>
                <p>Any voluntary donations, if accepted, are used <strong>solely</strong> to cover technical expenses such as hosting, the domain, and equipment. They are not payment for the game or its content.</p>
            </div>

            <div class="legal-section">
                <h2>5. Project purpose</h2>
                <p>The project exists to <strong>study and preserve</strong> classic online gameplay for educational, research, and nostalgic purposes. It is a non-commercial community effort and is not intended to cause commercial harm to anyone.</p>
            </div>

            <div class="legal-section">
                <h2>6. No warranties</h2>
                <p>The project is provided <strong>as is</strong>, without warranties of any kind. The administration is not responsible for possible damage connected with use of the project. By using the project, you agree that you do so at your own risk.</p>
            </div>

            <div class="legal-section">
                <h2>7. Rights-holder requests</h2>
                <p>If you are a rights holder and believe that material has been published in violation of your copyright or other rights, contact me. I will promptly review the request and remove the disputed material if necessary.</p>
                <div class="legal-contact">Contact email: <a href="mailto:contact@projectorion.fun">contact@projectorion.fun</a></div>
            </div>
        <?php else: ?>

                <div class="legal-callout">
                    <p>
                        <strong>Сервер Project Orion 0.8.2</strong> &mdash; это любительский,
                        некоммерческий проект, созданный сообществом энтузиастов. Доступ к серверу
                        предоставляется бесплатно.
                    </p>
                </div>

                <div class="legal-section">
                    <h2>О названии проекта</h2>
                    <p>
                        Проект носит собственное название <strong>Project Orion</strong>. Оно
                        отсылает к <strong>созвездию Орион</strong>, изображённому на эмблеме проекта.
                        Название используется как самостоятельное обозначение сервера и сообщества.
                    </p>
                    <p>
                        Название проекта <strong>не имеет ничего общего</strong> ни с какой
                        коммерческой компьютерной игрой, её правообладателями, товарными знаками,
                        логотипами или брендами. Любое сходство в сокращениях является случайным и
                        не подразумевает какой-либо связи, аффилированности или одобрения со стороны
                        какого-либо правообладателя.
                    </p>
                    <p class="legal-en">
                        <strong>About the name:</strong> the project is named &laquo;Project Orion&raquo;
                        in reference to the <strong>Orion constellation</strong> shown on the project
                        emblem. It is used as the independent name of the server and its community.
                        The project is not affiliated with or endorsed by any third-party company.
                    </p>
                </div>

                <div class="legal-section">
                    <h2>1. Независимый проект</h2>
                    <p>
                        Это <strong>независимый</strong> проект сообщества. Он не аффилирован,
                        не спонсируется, не поддерживается и никак иначе не связан с какими-либо
                        сторонними компаниями, разработчиками или издателями игр.
                    </p>
                </div>

                <div class="legal-section">
                    <h2>2. Интеллектуальная собственность третьих лиц</h2>
                    <ul>
                        <li>Все товарные знаки, логотипы, названия, изображения и иные материалы, упомянутые или используемые где-либо, являются собственностью их законных правообладателей.</li>
                        <li>Мы <strong>не заявляем</strong> прав собственности на чужую интеллектуальную собственность и не присваиваем себе чужого авторства.</li>
                        <li>Любые упоминания сторонних названий, если они встречаются, используются исключительно в описательных (номинативных) целях.</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>3. Собственные файлы и исходный код</h2>
                    <p>
                        Всё программное обеспечение, размещённое <strong>на наших серверах</strong> и
                        <strong>на этом сайте</strong>, является <strong>нашей собственной разработкой</strong>.
                        Серверная часть, сетевой код, веб-сайт, база данных и сопутствующие инструменты
                        написаны участниками проекта <strong>с нуля</strong> и не содержат файлов,
                        принадлежащих третьим лицам.
                    </p>
                    <ul>
                        <li>Мы <strong>не размещаем, не распространяем и не храним</strong> на своих серверах или на сайте чужие игровые клиенты, ресурсы, исполняемые файлы или иные защищённые авторским правом материалы сторонних правообладателей.</li>
                        <li>Весь код, который мы публикуем и используем, написан нами самостоятельно и является <strong>оригинальным</strong>.</li>
                        <li>Для подключения к серверу используется клиент, который пользователь приобретает и устанавливает <strong>самостоятельно</strong>; мы его не предоставляем и не распространяем.</li>
                    </ul>
                    <p class="legal-en">
                        <strong>Our own files only:</strong> all software hosted <strong>on our servers</strong>
                        and <strong>on this website</strong> is <strong>our own original work</strong>, written
                        from scratch by the project's contributors. We do <strong>not</strong> host, store, or
                        distribute any third-party game clients, assets, executables, or other copyrighted
                        materials belonging to anyone else. Nothing on our servers or site belongs to a third party.
                    </p>
                </div>

                <div class="legal-section">
                    <h2>4. Некоммерческий характер</h2>
                    <p>
                        Проект является <strong>полностью некоммерческим</strong>. Мы
                        <strong>не продаём</strong> игру, игровую валюту, технику, аккаунты или иные
                        внутриигровые ценности.
                    </p>
                    <p>
                        Любые добровольные пожертвования, если таковые принимаются, направляются
                        <strong>исключительно</strong> на покрытие технических расходов (оплата хостинга,
                        домена и оборудования) и не являются платой за игру или её контент.
                    </p>
                </div>

                <div class="legal-section">
                    <h2>5. Назначение проекта</h2>
                    <p>
                        Цель проекта &mdash; <strong>изучение и сохранение</strong> классического
                        сетевого геймплея в образовательных, исследовательских и ностальгических
                        целях. Это некоммерческая работа сообщества, не направленная на причинение
                        кому-либо коммерческого ущерба.
                    </p>
                </div>

                <div class="legal-section">
                    <h2>6. Отсутствие гарантий</h2>
                    <p>
                        Проект предоставляется &laquo;как есть&raquo;, без каких-либо гарантий. Администрация
                        не несёт ответственности за возможный ущерб, связанный с использованием проекта.
                        Используя проект, вы соглашаетесь с тем, что делаете это на свой страх и риск.
                    </p>
                </div>

                <div class="legal-section">
                    <h2>7. Обращение правообладателей</h2>
                    <p>
                        Если вы являетесь правообладателем и считаете, что какой-либо материал
                        размещён с нарушением ваших авторских или иных прав, свяжитесь со мной &mdash; я
                        оперативно рассмотрю обращение и при необходимости удалю спорный материал.
                    </p>
                    <div class="legal-contact">
                        Электронная почта для связи:
                        <a href="mailto:contact@projectorion.fun">contact@projectorion.fun</a>
                    </div>
                </div>

                <div class="legal-section">
                    <h2>Legal notice (English)</h2>
                    <p class="legal-en">
                        <strong>Project Orion 0.8.2</strong> is a non-commercial, community-made hobby
                        project. It is <strong>independent</strong> and is <strong>not affiliated with,
                        endorsed, sponsored, or supported by</strong> any third-party company, developer,
                        or publisher.
                    </p>
                    <p class="legal-en">
                        We <strong>do not claim any ownership</strong> of any third-party trademarks,
                        logos, artwork, or intellectual property, all of which remain the sole property
                        of their respective rights holders. The project is provided free of charge and is
                        strictly non-commercial. If you are a rights holder and believe any material
                        infringes your rights, please contact the administration and it will be reviewed
                        and removed if necessary.
                    </p>
                </div>

        <?php endif; ?>

                <p class="legal-updated"><?php echo htmlspecialchars($copy['updated'], ENT_QUOTES, 'UTF-8'); ?> <?php echo date($ui_lang === 'en' ? 'Y-m-d' : 'd.m.Y'); ?></p>

    </article>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
