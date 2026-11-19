<?php $p = $data['pesananPeriksa']; ?>
<main class="ad-container ad-main ad-proof-full-main" id="ad-main">
    <div class="ad-verifikasi-intro">
        <p class="ad-eyebrow">ADMIN / VERIFIKASI PEMBAYARAN / BUKTI UKURAN PENUH</p>
        <h1>Bukti Pembayaran Ukuran Penuh</h1>
        <p>Bukti ditampilkan utuh sesuai lebar halaman.</p>
    </div>
    <a class="ad-proof-back" href="<?= $aman($p !== null ? BASE_URL . '/index.php?page=admin-periksa-pembayaran&nomor=' . rawurlencode($p['nomor']) : $urlVerifikasi) ?>">← Kembali ke <?= $p !== null ? 'Pemeriksaan Pembayaran' : 'Verifikasi Pembayaran' ?></a>
    <?php if ($data['buktiBerubah']): ?>
        <section class="ad-panel"><div class="ad-empty"><h2>Bukti sudah berubah</h2><p>Buka kembali Pemeriksaan Pembayaran untuk melihat bukti terbaru.</p></div></section>
    <?php elseif ($p === null): ?>
        <section class="ad-panel"><div class="ad-empty"><h2>Bukti pembayaran tidak ditemukan</h2><p>Pilih pesanan yang memiliki bukti pembayaran dari daftar Verifikasi Pembayaran.</p><a class="ad-button" href="<?= $aman($urlVerifikasi) ?>">Kembali ke daftar</a></div></section>
    <?php else: ?>
        <section class="ad-panel ad-proof-full-panel" aria-labelledby="ad-proof-full-title">
            <h2 id="ad-proof-full-title"><?= $aman($p['nomor']) ?></h2>
            <p class="ad-proof-full-file"><?= $aman($p['bukti']['berkas']) ?></p>
            <div class="ad-proof-full-photo">
                <img data-proof-image src="<?= $aman(BASE_URL . '/index.php?page=admin-bukti&nomor=' . rawurlencode($p['nomor']) . '&versi=' . $p['versi']) ?>" alt="Bukti pembayaran pesanan <?= $aman($p['nomor']) ?>">
            </div>
        </section>
    <?php endif; ?>
</main>
