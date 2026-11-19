<?php
$aman = static fn($nilai): string => htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
$rupiah = static fn($nilai): string => 'Rp' . number_format((float) $nilai, 0, ',', '.');
$tanggal = static function (string $nilai, bool $hari = false): string {
    $waktu = new DateTimeImmutable($nilai, new DateTimeZone('Asia/Jakarta'));
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $pekan = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return ($hari ? $pekan[(int) $waktu->format('w')] . ', ' : '')
        . $waktu->format('j') . ' ' . $bulan[(int) $waktu->format('n')] . ' ' . $waktu->format('Y');
};
$urlAdmin = BASE_URL . '/index.php?page=admin-dashboard';
$urlPesanan = BASE_URL . '/index.php?page=admin-pesanan';
$diPesanan = in_array($data['halaman'] ?? '', ['pesanan', 'detail-pesanan'], true);
$diDetailPesanan = ($data['halaman'] ?? '') === 'detail-pesanan';
$urlDetailPesanan = static fn(string $nomor): string => BASE_URL . '/index.php?page=admin-detail-pesanan&nomor=' . rawurlencode($nomor);
$diVerifikasi = in_array($data['halaman'] ?? '', ['verifikasi', 'periksa-pembayaran', 'bukti-penuh'], true);
$diBuktiPenuh = ($data['halaman'] ?? '') === 'bukti-penuh';
$diDashboard = ($data['halaman'] ?? '') === 'dashboard';
$urlVerifikasi = BASE_URL . '/index.php?page=admin-verifikasi';
$diProduk = ($data['halaman'] ?? '') === 'produk';
$diKategori = ($data['halaman'] ?? '') === 'kategori';
$urlProduk = BASE_URL . '/index.php?page=admin-produk';
$urlKategori = BASE_URL . '/index.php?page=admin-kategori';
$diBooking = ($data['halaman'] ?? '') === 'booking';
$urlBooking = BASE_URL . '/index.php?page=admin-booking';
$diLaporan = ($data['halaman'] ?? '') === 'laporan';
$urlLaporan = BASE_URL . '/index.php?page=admin-laporan';
$fotoProduk = static fn(array $p): ?string => isset($p['foto'])
    ? BASE_URL . '/index.php?page=admin-produk-foto&id=' . (int) $p['id'] . '&v=' . (int) $p['versi']
    : (!empty($p['gambar']) ? BASE_URL . '/public/assets/images/' . rawurlencode($p['gambar']) : null);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/ui-bersama.css?v=9">
    <script src="<?= BASE_URL ?>/public/assets/js/ui-bersama.js?v=7" data-notification-url="<?= BASE_URL ?>/index.php?page=notifikasi-ringkasan" defer></script>
    <script src="<?= BASE_URL ?>/public/assets/js/form-perubahan.js?v=1" defer></script>
    <script src="<?= BASE_URL ?>/public/assets/js/module-navigation.js?v=7" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $aman($data['title']) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css?v=5">
    <?php if ($diPesanan || $diProduk || $diKategori): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin-pesanan.css?v=2"><?php endif; ?>
    <?php if ($diVerifikasi): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin-verifikasi.css?v=5"><?php endif; ?>
    <?php if ($diBuktiPenuh): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin-bukti.css?v=3"><?php endif; ?>
    <?php if ($diProduk): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin-produk.css?v=4"><script src="<?= BASE_URL ?>/public/assets/js/admin-produk.js?v=4" defer></script><?php endif; ?>
    <?php if ($diKategori): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin-produk.css?v=4"><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin-kategori.css?v=3"><?php endif; ?>
    <?php if ($diBooking): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin-booking.css?v=3"><script src="<?= BASE_URL ?>/public/assets/js/admin-booking.js?v=4" defer></script><?php endif; ?>
    <?php if ($diLaporan): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin-laporan.css?v=2"><?php endif; ?>
    <?php if ($diDetailPesanan): ?><link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin-detail-pesanan.css?v=7"><script src="<?= BASE_URL ?>/public/assets/js/admin-detail-pesanan.js?v=9" defer></script><?php endif; ?>
    <script src="<?= BASE_URL ?>/public/assets/js/admin-dashboard.js?v=5" defer></script>
</head>
<body class="ad-body">
    <a class="ad-skip" href="#ad-main">Lewati ke isi halaman</a>
    <div class="ad-announcement">
        <div class="ad-container ad-announcement-inner">
            <span>Dibuat dengan sepenuh hati untuk momen spesialmu</span>
            <span>Ayu Cake • Pengelolaan Toko</span>
        </div>
    </div>
    <header class="ad-header">
        <div class="ad-container ad-header-inner">
            <a class="ad-brand" href="<?= $aman($urlAdmin) ?>" aria-label="Dashboard Ayu Cake">
                <span class="ad-logo" aria-hidden="true"></span>
                <span><strong>Ayu Cake</strong><small>Kue untuk setiap cerita</small></span>
            </a>
            <div class="ad-header-actions">
                <div class="ad-admin-name"><strong><?= $aman($data['admin']['nama']) ?></strong><span><?= $aman($data['admin']['peran']) ?></span></div>
                <form action="<?= BASE_URL ?>/index.php?page=admin-keluar" method="post">
                    <input type="hidden" name="token_admin" value="<?= $aman($data['token']) ?>">
                    <button class="ad-button ad-logout" type="submit">Keluar</button>
                </form>
            </div>
        </div>
    </header>
    <nav class="ad-nav" aria-label="Navigasi admin">
        <div class="ad-container ad-nav-inner">
            <a class="ad-nav-link<?= $diDashboard ? ' ad-current' : '' ?>" href="<?= $aman($urlAdmin) ?>"<?= $diDashboard ? ' aria-current="page"' : '' ?>>Dashboard</a>
            <details class="ad-menu<?= $diPesanan || $diVerifikasi ? ' ad-menu-current' : '' ?>">
                <summary>Pesanan <span class="notification-count" data-notification-role="admin" aria-live="polite" <?= $data['notifikasiAdmin']['count'] === 0 ? 'hidden' : '' ?>><?= $data['notifikasiAdmin']['count'] > 99 ? '99+' : (int) $data['notifikasiAdmin']['count'] ?></span> <span class="ad-menu__chevron" aria-hidden="true">▾</span></summary>
                <div class="ad-menu-panel">
                    <a class="ad-menu-notifications" href="<?= $aman($urlPesanan) ?>"<?= $diPesanan ? ' aria-current="page"' : '' ?>>Semua Pesanan <span class="notification-count" data-notification-role="admin" aria-live="polite" <?= $data['notifikasiAdmin']['count'] === 0 ? 'hidden' : '' ?>><?= $data['notifikasiAdmin']['count'] > 99 ? '99+' : (int) $data['notifikasiAdmin']['count'] ?></span></a>
                    <a class="ad-menu-notifications" href="<?= $aman($urlVerifikasi) ?>"<?= $diVerifikasi ? ' aria-current="page"' : '' ?>>Verifikasi Pembayaran <span class="notification-count" data-notification-role="admin-pembayaran" aria-live="polite" <?= $data['notifikasiPembayaran']['count'] === 0 ? 'hidden' : '' ?>><?= $data['notifikasiPembayaran']['count'] > 99 ? '99+' : (int) $data['notifikasiPembayaran']['count'] ?></span></a>
                </div>
            </details>
            <details class="ad-menu<?= $diProduk || $diKategori ? ' ad-menu-current' : '' ?>">
                <summary>Produk <span class="ad-menu__chevron" aria-hidden="true">▾</span></summary>
                <div class="ad-menu-panel">
                    <a href="<?= $aman($urlProduk) ?>"<?= $diProduk ? ' aria-current="page"' : '' ?>>Kelola Produk</a>
                    <a href="<?= $aman($urlKategori) ?>"<?= $diKategori ? ' aria-current="page"' : '' ?>>Kelola Kategori</a>
                </div>
            </details>
            <a class="ad-nav-link<?= $diBooking ? ' ad-booking-current' : '' ?>" href="<?= $aman($urlBooking) ?>"<?= $diBooking ? ' aria-current="page"' : '' ?>>Pengaturan Booking</a>
            <a class="ad-nav-link<?= $diLaporan ? ' ad-sales-current' : '' ?>" href="<?= $aman($urlLaporan) ?>"<?= $diLaporan ? ' aria-current="page"' : '' ?>>Laporan Penjualan</a>
        </div>
    </nav>
