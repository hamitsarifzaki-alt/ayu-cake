<?php $daftar = $data['pembayaran']; ?>
<main class="ad-container ad-main" id="ad-main">
    <div class="ad-verifikasi-intro">
        <p class="ad-eyebrow">ADMIN / VERIFIKASI PEMBAYARAN</p>
        <h1>Verifikasi Pembayaran</h1>
        <p>Periksa bukti dan pastikan pembayaran masuk sebelum memproses pesanan.</p>
    </div>
    <?php if ($data['pesan'] !== null): ?><p class="ad-payment-message" role="status" data-dismissible-notice><?= $aman($data['pesan']) ?></p><?php endif; ?>
    <?php
    $notifikasi = $data['notifikasiPembayaran'];
    $peranNotifikasi = 'admin-pembayaran';
    $judulNotifikasi = 'Pembaruan pembayaran';
    $deskripsiNotifikasi = 'Buka bukti untuk memeriksa pembayaran baru atau yang dikirim ulang.';
    require APP_PATH . '/views/shared/notifikasi.php';
    ?>
    <section class="ad-panel ad-payment-panel" aria-labelledby="ad-pay-title">
        <div class="ad-panel-heading">
            <div><h2 id="ad-pay-title">Menunggu Pemeriksaan</h2><p class="ad-muted">Daftar pesanan yang sudah mengunggah bukti pembayaran.</p></div>
            <span class="ad-badge"><?= (int) $daftar['total'] ?> bukti pembayaran</span>
        </div>
        <form class="ad-payment-search" action="<?= BASE_URL ?>/index.php" method="get" role="search" aria-label="Cari pembayaran">
            <input type="hidden" name="page" value="admin-verifikasi">
            <label>Cari pembayaran<input type="search" name="cari" value="<?= $aman($daftar['cari']) ?>" maxlength="120" placeholder="Nomor pesanan atau nama pelanggan"></label>
            <button class="ad-button" type="submit">Cari</button>
        </form>
        <?php if ($daftar['cari'] !== ''): ?><p class="ad-payment-reset"><a class="ad-text-link" href="<?= $aman($urlVerifikasi) ?>">Reset pencarian</a></p><?php endif; ?>
        <?php if ($daftar['jumlah'] > 0): ?>
            <div class="ad-table-scroll" role="region" aria-label="Bukti pembayaran menunggu pemeriksaan" tabindex="0">
                <table class="ad-table ad-payment-table">
                    <caption class="ad-sr-only">Daftar bukti pembayaran menunggu pemeriksaan admin</caption>
                    <thead><tr><th scope="col">Pesanan / pelanggan</th><th scope="col">Metode pembayaran</th><th scope="col">Total tagihan</th><th scope="col">Waktu unggah</th><th scope="col">Bukti pembayaran</th><th scope="col">Tindakan</th></tr></thead>
                    <tbody>
                    <?php foreach ($daftar['items'] as $pesanan): ?>
                        <tr data-ad-payment-row>
                            <th scope="row"><strong><?= $aman($pesanan['nomor']) ?></strong><small><?= $aman($pesanan['nama']) ?></small></th>
                            <td><?= $aman($pesanan['pembayaran']) ?></td>
                            <td><strong><?= $rupiah($pesanan['total']) ?></strong></td>
                            <td><time datetime="<?= $aman(str_replace(' ', 'T', $pesanan['bukti']['waktu']) . '+07:00') ?>"><?= $aman($tanggal($pesanan['bukti']['waktu'])) ?><small><?= $aman(substr($pesanan['bukti']['waktu'], 11, 5)) ?> WIB</small></time></td>
                            <td><strong>Bukti terunggah</strong><small><?= $aman($pesanan['bukti']['berkas']) ?></small></td>
                            <td><a class="ad-button ad-row-action" href="<?= BASE_URL ?>/index.php?page=admin-periksa-pembayaran&amp;nomor=<?= $aman($pesanan['nomor']) ?>">Periksa Bukti<span class="ad-sr-only"> <?= $aman($pesanan['nomor']) ?></span></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="ad-empty"><h3><?= $daftar['total'] === 0 ? 'Semua bukti sudah diperiksa' : 'Pembayaran tidak ditemukan' ?></h3><p><?= $daftar['total'] === 0 ? 'Tidak ada bukti pembayaran yang menunggu pemeriksaan saat ini.' : 'Coba nomor pesanan atau nama pelanggan lain.' ?></p><a class="ad-button" href="<?= $aman($daftar['total'] === 0 ? $urlPesanan : $urlVerifikasi) ?>"><?= $daftar['total'] === 0 ? 'Lihat Semua Pesanan' : 'Tampilkan semua bukti' ?></a></div>
        <?php endif; ?>
        <div class="ad-payment-bottom"><p class="ad-muted" role="status">Menampilkan <?= (int) $daftar['jumlah'] ?> dari <?= (int) $daftar['total'] ?> bukti pembayaran</p></div>
        <p class="ad-payment-tip">Buka Periksa Bukti untuk mencocokkan nominal dan transaksi yang masuk ke akun pembayaran toko.</p>
    </section>
</main>
