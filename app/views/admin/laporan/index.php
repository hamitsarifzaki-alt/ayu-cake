<?php
$laporan = $data['laporan'];
$total = $laporan['total'];
$valid = $laporan['error'] === null;
$periode = $valid ? $tanggal($laporan['mulai']) . ' – ' . $tanggal($laporan['akhir']) : 'Pilih periode yang valid';
$unduh = static fn(string $format): string => BASE_URL . '/index.php?' . http_build_query([
    'page' => 'admin-laporan-unduh', 'mulai' => $laporan['mulai'], 'akhir' => $laporan['akhir'], 'format' => $format]);
?>
<main class="ad-container ad-main ad-sales-main" id="ad-main">
    <div class="ad-sales-intro"><p class="ad-eyebrow">ADMIN / LAPORAN PENJUALAN</p><h1>Laporan Penjualan</h1><p>Lihat ringkasan dan rincian penjualan berdasarkan periode.</p></div>
    <section class="ad-panel ad-sales-panel" aria-labelledby="ad-sales-title">
        <h2 id="ad-sales-title">Periode Penjualan</h2><p class="ad-muted ad-sales-subtitle">Pilih tanggal mulai dan akhir, lalu klik Tampilkan.</p>
        <?php if (!$valid): ?><p class="ad-form-error ad-sales-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($laporan['error']) ?></p><?php endif; ?>
        <form class="ad-sales-filter" action="<?= BASE_URL ?>/index.php" method="get">
            <input type="hidden" name="page" value="admin-laporan">
            <div><label for="ad-sales-start">Tanggal mulai</label><input type="date" id="ad-sales-start" name="mulai" value="<?= $aman($laporan['mulai']) ?>" min="2000-01-01" max="2100-12-31" required></div>
            <div><label for="ad-sales-end">Tanggal akhir</label><input type="date" id="ad-sales-end" name="akhir" value="<?= $aman($laporan['akhir']) ?>" min="2000-01-01" max="2100-12-31" required></div>
            <button class="ad-button" type="submit">Tampilkan</button><p class="ad-muted">Berdasarkan tanggal <br>pesanan selesai</p>
        </form>
        <?php if ($valid): ?>
        <div class="ad-sales-summary" aria-label="Ringkasan penjualan periode terpilih">
            <div><p>Penjualan produk</p><strong data-sales-products><?= $rupiah($total['produk']) ?></strong><small>Tidak termasuk ongkir</small></div>
            <div><p>Pesanan selesai</p><strong data-sales-orders><?= (int) $total['pesanan'] ?> pesanan</strong><small>Periode <?= $aman($periode) ?></small></div>
            <div><p>Produk terjual</p><strong data-sales-trays><?= (int) $total['loyang'] ?> loyang</strong><small>Total jumlah loyang terjual</small></div>
        </div>
        <div class="ad-sales-detail-heading"><div><h2 id="ad-sales-details">Rincian Penjualan</h2><p class="ad-muted">Menampilkan pesanan yang berstatus selesai.</p></div><div class="ad-sales-downloads"><a class="ad-button" href="<?= $aman($unduh('pdf')) ?>">Unduh PDF</a><a class="ad-button" href="<?= $aman($unduh('xlsx')) ?>">Unduh Excel</a></div></div>
        <div class="ad-table-scroll" role="region" aria-labelledby="ad-sales-details" tabindex="0">
            <table class="ad-table ad-sales-table">
                <caption class="ad-sr-only">Penjualan selesai periode <?= $aman($periode) ?>. Nilai produk tidak termasuk ongkir.</caption>
                <colgroup><col class="ad-sales-col-order"><col><col><col class="ad-sales-col-trays"><col><col><col></colgroup>
                <thead><tr><th scope="col">Pesanan / Pelanggan</th><th scope="col">Tanggal selesai</th><th scope="col">Jenis pesanan</th><th scope="col">Loyang</th><th scope="col">Nilai produk</th><th scope="col">Ongkir</th><th scope="col">Total</th></tr></thead>
                <tbody>
                    <?php foreach ($laporan['items'] as $p): ?><tr data-sales-row><th scope="row"><a href="<?= $aman($urlDetailPesanan($p['nomor'])) ?>" aria-label="<?= $aman('Detail Pesanan ' . $p['nomor']) ?>"><strong><?= $aman($p['nomor']) ?></strong></a><small><?= $aman($p['nama']) ?></small></th><td><?= $aman($tanggal($p['tanggal_selesai'])) ?></td><td><?= $aman($p['jenis']) ?></td><td><?= (int) $p['loyang'] ?></td><td><?= $rupiah($p['subtotal']) ?></td><td class="ad-sales-shipping"><?= $rupiah($p['ongkir']) ?></td><td><strong><?= $rupiah($p['total']) ?></strong></td></tr><?php endforeach; ?>
                    <?php if (!$laporan['items']): ?><tr><td class="ad-sales-empty" colspan="7"><strong>Belum ada pesanan selesai pada periode ini.</strong><p>Coba pilih rentang tanggal lain, lalu klik Tampilkan.</p></td></tr><?php endif; ?>
                </tbody>
                <tfoot><tr><th scope="row" colspan="3">Total periode</th><td><?= (int) $total['loyang'] ?></td><td><?= $rupiah($total['produk']) ?></td><td class="ad-sales-shipping"><?= $rupiah($total['ongkir']) ?></td><td><?= $rupiah($total['total']) ?></td></tr></tfoot>
            </table>
        </div>
        <?php endif; ?>
    </section>
</main>
