<?php

$profilFooter = $data['profilToko'];
$amanFooter = static function ($nilai): string {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};
$nomorFooter = preg_replace('/\D/', '', $profilFooter['whatsapp']);
if (substr($nomorFooter, 0, 1) === '0') {
    $nomorFooter = '62' . substr($nomorFooter, 1);
}
$urlWhatsappFooter = preg_match('/^628[0-9]{8,11}$/D', $nomorFooter)
    ? 'https://wa.me/' . $nomorFooter . '?text=' . rawurlencode($profilFooter['pesanWhatsapp']) : null;
?>
<footer class="site-footer">
    <div class="container">

        <div class="footer-grid">

            <div class="footer-brand">
                <h2><?= $amanFooter($profilFooter['nama']) ?></h2>

                <p>
                    Kue basah dan bolu untuk menemani
                    cerita hangat bersama keluarga.
                </p>

                <p>
                    Pesan secara online, lalu pilih
                    ambil di toko atau pengiriman.
                </p>
            </div>

            <div class="footer-column">
                <h3>Jelajahi</h3>

                <a href="<?= BASE_URL ?>/index.php?page=katalog">
                    Katalog Produk
                </a>

                <a href="<?= BASE_URL ?>/index.php?page=katalog&amp;jenis=booking">
                    Booking (Pre-Order)
                </a>

                <a href="<?= BASE_URL ?>/index.php?page=katalog&amp;jenis=ready-stock">
                    Pesan Sekarang
                </a>
            </div>

            <div class="footer-column">
                <h3>Bantuan</h3>

                <a href="<?= BASE_URL ?>/index.php?page=cara-pesan">
                    FAQ &amp; Cara Pesan
                </a>

                <a href="<?= BASE_URL ?>/index.php?page=status-pesanan">
                    Status Pesanan
                </a>


                <a href="<?= BASE_URL ?>/index.php?page=kontak">
                    Tentang Kami &amp; Kontak
                </a>
            </div>

            <div class="footer-column">
                <h3>Kontak &amp; Lokasi</h3>

                <div class="footer-social">
                    <p class="footer-contact">WhatsApp: <?php if ($urlWhatsappFooter !== null): ?><a href="<?= $amanFooter($urlWhatsappFooter) ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat Ayu Cake melalui WhatsApp, membuka tab baru"><?= $amanFooter($profilFooter['whatsapp']) ?></a><?php else: ?>Belum tersedia<?php endif; ?></p>
                    <?php if ($profilFooter['instagram'] !== ''): ?>
                        <p class="footer-contact">Instagram: <a href="https://www.instagram.com/<?= $amanFooter(rawurlencode($profilFooter['instagram'])) ?>/" target="_blank" rel="noopener noreferrer">@<?= $amanFooter($profilFooter['instagram']) ?></a></p>
                    <?php endif; ?>
                </div>
                <p>Alamat: <?= $amanFooter($profilFooter['alamat']) ?></p>
                <p>Jam buka: <?= $profilFooter['jamOperasional'] !== '' ? $amanFooter($profilFooter['jamOperasional']) . ' WIB' : 'Belum tersedia' ?></p>
                <?php if ($profilFooter['hariOperasional'] !== ''): ?><p>Hari buka: <?= $amanFooter($profilFooter['hariOperasional']) ?></p><?php endif; ?>
            </div>

        </div>

        <div class="footer-payment">
            <strong>Pembayaran melalui transfer BRI / SeaBank.</strong>

            <p>
                Setelah membayar, unggah bukti untuk diverifikasi admin.
            </p>
        </div>

        <div class="footer-bottom">
            <p>
                &copy; <?= date('Y') ?> <?= $amanFooter($profilFooter['nama']) ?>
                &bull; <?= $amanFooter($profilFooter['slogan']) ?>.
            </p>

            <p>Kue basah dan bolu untuk setiap momen.</p>
        </div>

    </div>
</footer>

<script src="<?= BASE_URL ?>/public/assets/js/main.js?v=4"></script>
<script>
(function () {
    const account = document.querySelector('.ac-account');

    if (!account) return;

    const toggle = account.querySelector('summary');

    document.addEventListener('click', function (event) {
        if (!account.contains(event.target)) {
            account.open = false;
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && account.open) {
            account.open = false;
            toggle.focus();
        }
    });
})();
</script>
</body>
</html>
