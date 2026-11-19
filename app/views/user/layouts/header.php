<?php

$profilHeader = $data['profilToko'];
$amanHeader = static fn($s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/ui-bersama.css?v=9">
        <script src="<?= BASE_URL ?>/public/assets/js/ui-bersama.js?v=7" data-notification-url="<?= BASE_URL ?>/index.php?page=notifikasi-ringkasan" defer></script>
        <script src="<?= BASE_URL ?>/public/assets/js/form-perubahan.js?v=1" defer></script>
    <script src="<?= BASE_URL ?>/public/assets/js/module-navigation.js?v=7" defer></script>

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>
            <?= htmlspecialchars(
                $data['title'] ?? 'Ayu Cake',
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </title>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/public/assets/css/style.css?v=32"
        >

        <?php
$halamanAkun = $_GET['page'] ?? '';

if (
    is_string($halamanAkun) &&
    in_array(
        $halamanAkun,
        ['login', 'registrasi', 'lupa-kata-sandi'],
        true
    )
):
?>
    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/public/assets/css/auth.css?v=5"
    >
<?php endif; ?>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/interaksi.css?v=7">

    </head>

    <body>

    <div class="announcement">
        <div class="container announcement-inner">
            <span>Dibuat dengan sepenuh hati untuk momen spesialmu</span>
            <span>Ayu Cake &bull; Kue basah &amp; bolu</span>
        </div>
    </div>

    <header class="site-header">
        <div class="container header-inner">

            <a
                class="brand"
                href="<?= BASE_URL ?>/"
                aria-label="<?= $amanHeader('Beranda ' . $profilHeader['nama']) ?>"
            >
                <span class="brand-logo" aria-hidden="true"<?= $profilHeader['logo'] !== '' ? ' style="background-image: url(\'' . $amanHeader($profilHeader['logo']) . '\')"' : '' ?>></span>

                <span class="brand-copy">
                    <span class="brand-name"><?= $amanHeader($profilHeader['nama']) ?></span>
                    <span class="brand-tagline"><?= $amanHeader($profilHeader['slogan']) ?></span>
                </span>
            </a>

            <form
                class="search-form"
                action="<?= BASE_URL ?>/index.php"
                method="get"
                role="search"
            >
                <input type="hidden" name="page" value="katalog">

                <label class="sr-only" for="search">
                    Cari produk
                </label>

                <input
                    id="search"
                    type="search"
                    name="q"
                    placeholder="Cari kue favoritmu..."
                >

                <button type="submit" aria-label="Cari produk">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="10.5" cy="10.5" r="6.5"></circle>
                        <path d="m16 16 5 5"></path>
                    </svg>
                </button>
            </form>

            <?php require __DIR__ . '/partials/header-actions.php'; ?> 

        </div>
    </header>

    <?php
    $halamanAktif = $_GET['page'] ?? 'home';
    ?>
    <nav class="main-nav" aria-label="Navigasi utama">
        <div class="container nav-inner">

            <a
                href="<?= BASE_URL ?>/"
                class="<?= $halamanAktif === 'home' ? 'active' : '' ?>"
            >
                BERANDA
            </a>

            <a
                href="<?= BASE_URL ?>/index.php?page=katalog"
                class="<?= in_array($halamanAktif, ['katalog', 'detail-produk'], true) ? 'active' : '' ?>"
            >
                KATALOG
            </a>

            <a
                href="<?= BASE_URL ?>/index.php?page=cara-pesan"
                class="<?= $halamanAktif === 'cara-pesan' ? 'active' : '' ?>"
            >
                FAQ &amp; CARA PESAN
            </a>

            <a
                href="<?= BASE_URL ?>/index.php?page=kontak"
                class="<?= $halamanAktif === 'kontak' ? 'active' : '' ?>"
            >
                TENTANG KAMI &amp; KONTAK
            </a>

        </div>
    </nav>
