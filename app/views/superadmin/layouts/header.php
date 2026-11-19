<?php
$aman = static fn($s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$rupiah = static fn($n): string => 'Rp' . number_format((float) $n, 0, ',', '.');
$bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$hari = new DateTimeImmutable($data['hari']);
$periode = $bulan[(int) $hari->format('n')] . ' ' . $hari->format('Y');
$total = $data['laporan']['total'];
$url = BASE_URL . '/index.php?page=superadmin-dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/ui-bersama.css?v=9">
    <script src="<?= BASE_URL ?>/public/assets/js/ui-bersama.js?v=7" defer></script>
    <script src="<?= BASE_URL ?>/public/assets/js/form-perubahan.js?v=1" defer></script>
    <script src="<?= BASE_URL ?>/public/assets/js/module-navigation.js?v=7" defer></script>
    <title><?= $aman($data['title'] ?? 'Dashboard Super Admin') ?> | Ayu Cake</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css?v=5">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/superadmin.css?v=6">
    <?php if (in_array($data['halaman'] ?? '', ['akun-admin', 'akun-pelanggan'], true)): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/superadmin-akun-admin.css?v=6"><?php endif; ?>
    <?php if (($data['halaman'] ?? '') === 'akun-pelanggan'): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/superadmin-akun-pelanggan.css?v=7"><?php endif; ?>
    <?php if (($data['halaman'] ?? '') === 'laporan'): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/superadmin-laporan.css?v=3"><?php endif; ?>
    <?php if (($data['halaman'] ?? '') === 'profil-toko'): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/superadmin-profil-toko.css?v=6"><script src="<?= BASE_URL ?>/public/assets/js/superadmin-profil-toko.js?v=6" defer></script><?php endif; ?>
    <?php if (($data['halaman'] ?? '') === 'faq'): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/superadmin-faq.css?v=4"><?php endif; ?>
    <script src="<?= BASE_URL ?>/public/assets/js/superadmin.js?v=9" defer></script>
</head>
<body class="sa-body">
    <a class="ad-skip" href="#sa-main">Lewati ke isi halaman</a>
    <div class="ad-announcement"><div class="ad-container ad-announcement-inner"><span>Dibuat dengan sepenuh hati untuk momen spesialmu</span><span>Ayu Cake • Pengelolaan Toko</span></div></div>
    <header class="ad-header"><div class="ad-container ad-header-inner"><a class="ad-brand" href="<?= $aman($url) ?>" aria-label="Dashboard Super Admin <?= $aman($data['profil']['nama']) ?>"><span class="ad-logo" aria-hidden="true"<?= $data['profil']['logo'] !== '' ? ' style="background-image: url(\'' . $aman($data['profil']['logo']) . '\')"' : '' ?>></span><span><strong><?= $aman($data['profil']['nama']) ?></strong><small><?= $aman($data['profil']['slogan']) ?></small></span></a><div class="ad-header-actions"><div class="ad-admin-name"><strong><?= $aman($data['akun']['nama']) ?></strong><span>Super Admin</span></div><form action="<?= BASE_URL ?>/index.php?page=superadmin-keluar" method="post"><input type="hidden" name="token_superadmin" value="<?= $aman($data['token']) ?>"><button class="ad-button ad-logout" type="submit">Keluar</button></form></div></div></header>
    <nav class="ad-nav" aria-label="Navigasi Super Admin"><div class="ad-container sa-nav-inner">
        <a class="ad-nav-link<?= ($data['halaman'] ?? 'dashboard') === 'dashboard' ? ' sa-current' : '' ?>" href="<?= $aman($url) ?>"<?= ($data['halaman'] ?? 'dashboard') === 'dashboard' ? ' aria-current="page"' : '' ?>>Dashboard</a>
        <details class="ad-menu<?= in_array($data['halaman'] ?? '', ['akun-admin', 'akun-pelanggan'], true) ? ' ad-menu-current' : '' ?>"><summary>Kelola Akun <span class="ad-menu__chevron" aria-hidden="true">▾</span></summary><div class="ad-menu-panel"><a href="<?= BASE_URL ?>/index.php?page=superadmin-akun-admin"<?= ($data['halaman'] ?? '') === 'akun-admin' ? ' aria-current="page"' : '' ?>>Akun Admin</a><a href="<?= BASE_URL ?>/index.php?page=superadmin-akun-pelanggan"<?= ($data['halaman'] ?? '') === 'akun-pelanggan' ? ' aria-current="page"' : '' ?>>Akun Pelanggan</a></div></details>
        <a class="ad-nav-link<?= ($data['halaman'] ?? '') === 'laporan' ? ' sa-current' : '' ?>" href="<?= BASE_URL ?>/index.php?page=superadmin-laporan"<?= ($data['halaman'] ?? '') === 'laporan' ? ' aria-current="page"' : '' ?>>Laporan Penjualan</a>
        <a class="ad-nav-link<?= ($data['halaman'] ?? '') === 'profil-toko' ? ' sa-current' : '' ?>" href="<?= BASE_URL ?>/index.php?page=superadmin-profil-toko"<?= ($data['halaman'] ?? '') === 'profil-toko' ? ' aria-current="page"' : '' ?>>Profil Toko</a>
        <a class="ad-nav-link<?= ($data['halaman'] ?? '') === 'faq' ? ' sa-current' : '' ?>" href="<?= BASE_URL ?>/index.php?page=superadmin-faq"<?= ($data['halaman'] ?? '') === 'faq' ? ' aria-current="page"' : '' ?>>Kelola FAQ</a>
    </div></nav>
