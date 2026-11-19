<?php
$kartu = [
    ['ongkir', 'Periksa Ongkir', 'Pesanan menunggu pemeriksaan'],
    ['verifikasi', 'Verifikasi Pembayaran', 'Bukti pembayaran belum diperiksa'],
    ['diproses', 'Sedang Diproses', 'Pesanan dalam pengerjaan'],
    ['selesai', 'Selesai Hari Ini', 'Pesanan telah diterima'],
];
$booking = $data['booking'];
$sisa = max(0, $booking['maksimal'] - $booking['terpakai']);
?>
<main class="ad-container ad-main" id="ad-main">
    <section class="ad-welcome" aria-labelledby="ad-welcome-title">
        <div>
            <p class="ad-eyebrow">DASHBOARD</p>
            <h1 id="ad-welcome-title">Selamat datang, <?= $aman($data['admin']['nama']) ?>.</h1>
            <p>Ringkasan pesanan dan pekerjaan toko hari ini.</p>
        </div>
        <time class="ad-date" datetime="<?= $aman($data['hari']) ?>"><?= $aman($tanggal($data['hari'], true)) ?></time>
    </section>

    <section class="ad-stats" aria-label="Ringkasan pesanan hari ini">
        <?php foreach ($kartu as [$key, $judul, $keterangan]): ?>
            <a class="ad-stat" href="<?= $aman($key === 'verifikasi' ? $urlVerifikasi : $urlPesanan . '&status=' . $key) ?>" aria-label="<?= $aman($judul . ': ' . $data['angka'][$key] . ' pesanan') ?>">
                <span class="ad-stat-title"><?= $aman($judul) ?></span>
                <strong class="ad-stat-number"><?= (int) $data['angka'][$key] ?></strong>
                <span class="ad-stat-description"><?= $aman($keterangan) ?></span>
            </a>
        <?php endforeach; ?>
    </section>

    <section class="ad-panel ad-followup" aria-labelledby="ad-followup-title">
        <div class="ad-panel-heading">
            <div>
                <h2 id="ad-followup-title">Pesanan perlu ditindaklanjuti</h2>
                <p class="ad-muted">Pilih pesanan untuk melanjutkan pemeriksaan.</p>
            </div>
            <a class="ad-button ad-all-orders" href="<?= $aman($urlPesanan) ?>">Semua Pesanan</a>
        </div>
        <div class="ad-table-scroll" role="region" aria-label="Pesanan prioritas" tabindex="0">
            <table class="ad-table ad-priority-table">
                <caption class="ad-sr-only">Tiga pesanan prioritas yang memerlukan tindak lanjut</caption>
                <thead><tr><th scope="col">Pesanan / pelanggan</th><th scope="col">Jenis pesanan</th><th scope="col">Tanggal ambil / kirim</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead>
                <tbody>
                    <?php foreach ($data['prioritas'] as $pesanan): ?>
                        <tr>
                            <th scope="row"><strong><?= $aman($pesanan['nomor']) ?></strong><small><?= $aman($pesanan['nama']) ?></small></th>
                            <td><?= $aman($pesanan['jenis']) ?></td>
                            <td><?= $aman($tanggal($pesanan['tanggal'])) ?></td>
                            <td><span class="ad-badge"><?= $aman($pesanan['status']) ?></span></td>
                            <td><?php if ($pesanan['status'] === 'Menunggu Verifikasi'): ?><a class="ad-button ad-row-action" href="<?= BASE_URL ?>/index.php?page=admin-periksa-pembayaran&amp;nomor=<?= $aman($pesanan['nomor']) ?>">Periksa Pembayaran<span class="ad-sr-only"> <?= $aman($pesanan['nomor']) ?></span></a><?php else: ?><a class="ad-button ad-row-action" href="<?= $aman($urlDetailPesanan($pesanan['nomor'])) ?>">Detail Pesanan<span class="ad-sr-only"> <?= $aman($pesanan['nomor']) ?></span></a><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <section class="ad-capacity" aria-labelledby="ad-capacity-title">
            <div class="ad-capacity-heading">
                <div>
                    <h2 id="ad-capacity-title">Kapasitas Booking</h2>
                    <p class="ad-muted">Pengambilan / pengiriman <?= $aman($tanggal($booking['tanggal'])) ?></p>
                </div>
                <a class="ad-button ad-booking-button" href="<?= $aman($urlBooking) ?>&amp;tanggal=<?= $aman($booking['tanggal']) ?>">Pengaturan Booking</a>
            </div>
            <div class="ad-capacity-count"><strong><?= (int) $booking['terpakai'] ?> dari <?= (int) $booking['maksimal'] ?> loyang terpakai</strong><span class="ad-badge<?= $booking['dibuka'] && $sisa > 0 ? ' ad-badge-green' : '' ?>"><?= !$booking['dibuka'] ? 'Tanggal ditutup' : ($sisa === 0 ? 'Penuh' : 'Sisa ' . (int) $sisa . ' loyang') ?></span></div>
            <progress class="ad-progress" value="<?= (int) $booking['terpakai'] ?>" max="<?= (int) $booking['maksimal'] ?>" aria-label="Kapasitas Booking terpakai"><?= (int) round(100 * $booking['terpakai'] / $booking['maksimal']) ?>%</progress>
        </section>
    </section>
</main>

