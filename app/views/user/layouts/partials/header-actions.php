<?php
$halamanHeader = $_GET['page'] ?? 'home';

if (!is_string($halamanHeader)) {
    $halamanHeader = 'home';
}

$keranjangAktif = $halamanHeader === 'keranjang';
$akunAktif = in_array($halamanHeader, [
    'status-pesanan', 'riwayat-pesanan',
    'detail-pesanan', 'pembayaran', 'pesanan-berhasil',
], true);
$masukAktif = in_array($halamanHeader, [
    'login', 'registrasi', 'lupa-kata-sandi',
], true);

$adminHeader = $data['adminAktif'];
$superadminHeader = $data['superadminAktif'];

$dataPelangganHeader =
    $_SESSION['app_pelanggan'] ?? null;

$pelangganAktif = is_array($dataPelangganHeader)
    ? $dataPelangganHeader
    : null;

$namaPelanggan = htmlspecialchars(
    (string) ($pelangganAktif['nama'] ?? 'Pelanggan'),
    ENT_QUOTES,
    'UTF-8'
);

$jumlahKeranjangHeader = 0;
if ($pelangganAktif !== null) {

    $jumlahKeranjangHeader = $data['jumlahKeranjang'];
}
?>

<div class="header-actions">

    <a
        class="header-button <?= $keranjangAktif ? 'header-current' : '' ?>"
        href="<?= BASE_URL ?>/index.php?page=keranjang"
        data-header-cart
        data-cart-summary-url="<?= BASE_URL ?>/index.php?page=keranjang-ringkasan"
        aria-label="Keranjang, <?= (int) $jumlahKeranjangHeader ?> loyang"
        <?= $keranjangAktif ? 'aria-current="page"' : '' ?>
    >
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M2 3h3l3 12h11l3-9H6"></path>
            <circle cx="9" cy="20" r="1"></circle>
            <circle cx="19" cy="20" r="1"></circle>
        </svg>

        <span>Keranjang</span>
        <span class="header-cart-count" data-cart-badge aria-hidden="true" <?= $jumlahKeranjangHeader === 0 ? 'hidden' : '' ?>><?= $jumlahKeranjangHeader > 99 ? '99+' : (int) $jumlahKeranjangHeader ?></span>
    </a>

    <?php if ($pelangganAktif !== null): ?>

        <details class="ac-account<?= $akunAktif ? ' ac-account-current' : '' ?>">

            <summary class="ac-account__toggle">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M5 21v-2a7 7 0 0 1 14 0v2"></path>
                </svg>

                <span class="ac-account__label">Akun Saya <span class="notification-count" data-notification-role="pelanggan" aria-live="polite" <?= $data['notifikasiPelanggan']['count'] === 0 ? 'hidden' : '' ?>><?= $data['notifikasiPelanggan']['count'] > 99 ? '99+' : (int) $data['notifikasiPelanggan']['count'] ?></span></span>

                <svg
                    class="ac-account__chevron"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </summary>

            <div class="ac-account__panel">

                <div class="ac-account__intro">
                    <strong>Halo, <?= $namaPelanggan ?></strong>
                    <span>Pantau pesananmu</span>
                </div>

                <a
                    class="ac-account__link"
                    href="<?= BASE_URL ?>/index.php?page=status-pesanan"
                    <?= $halamanHeader === 'status-pesanan' ? 'aria-current="page"' : '' ?>
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m12 3 9 5v8l-9 5-9-5V8Z"></path>
                        <path d="m3 8 9 5 9-5M12 13v8M7.5 5.5l9 5"></path>
                    </svg>

                    <span class="ac-account__label">Status Pesanan <span class="notification-count" data-notification-role="pelanggan" aria-live="polite" <?= $data['notifikasiPelanggan']['count'] === 0 ? 'hidden' : '' ?>><?= $data['notifikasiPelanggan']['count'] > 99 ? '99+' : (int) $data['notifikasiPelanggan']['count'] ?></span></span>
                </a>

                <a
                    class="ac-account__link"
                    href="<?= BASE_URL ?>/index.php?page=riwayat-pesanan"
                    <?= $halamanHeader === 'riwayat-pesanan' ? 'aria-current="page"' : '' ?>
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 11a9 9 0 1 1 2.6 7"></path>
                        <path d="M3 4v7h7M12 7v5l3 2"></path>
                    </svg>

                    <span>Riwayat Pesanan</span>
                </a>

                <form
                    class="ac-account__logout"
                    action="<?= BASE_URL ?>/index.php"
                    method="post"
                >
                    <input type="hidden" name="token_auth" value="<?= htmlspecialchars($_SESSION['app_token_auth'], ENT_QUOTES, 'UTF-8') ?>">
                    <button
                        class="ac-account__link ac-account__exit"
                        type="submit"
                        name="aksi_pelanggan"
                        value="keluar"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 4H4v16h5M10 12h11m-4-4 4 4-4 4"></path>
                        </svg>

                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </details>

    <?php else: ?>

        <a
            class="header-button <?= $masukAktif ? 'header-current' : '' ?>"
            href="<?= BASE_URL ?>/index.php?page=<?= $superadminHeader !== null ? 'superadmin-dashboard' : ($adminHeader !== null ? 'admin-dashboard' : 'login') ?>"
            <?= $masukAktif ? 'aria-current="page"' : '' ?>
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9"></circle>
                <circle cx="12" cy="9" r="3"></circle>
                <path d="M6.5 19c.5-3 2.5-5 5.5-5s5 2 5.5 5"></path>
            </svg>

            <span><?= $superadminHeader !== null ? 'Panel Super Admin' : ($adminHeader !== null ? 'Panel Admin' : 'Masuk') ?></span>
        </a>

    <?php endif; ?>

</div>
