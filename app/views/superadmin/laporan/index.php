<?php require APP_PATH . '/views/superadmin/layouts/header.php';
$laporan = $data['laporan'];
$valid = $laporan['error'] === null;
$tanggal = static function (string $s) use ($bulan): string {
    $d = new DateTimeImmutable($s);
    return $d->format('d') . ' ' . substr($bulan[(int) $d->format('n')], 0, 3) . ' ' . $d->format('Y');
};
$unduh = static fn(string $format): string => BASE_URL . '/index.php?' . http_build_query([
    'page' => 'superadmin-laporan-unduh', 'mulai' => $laporan['mulai'], 'akhir' => $laporan['akhir'], 'format' => $format]);
?>
<main class="ad-container ad-main sa-main sa-sales-main" id="sa-main">
    <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / LAPORAN PENJUALAN</p><h1>Laporan Penjualan</h1><p>Lihat ringkasan penjualan, produk terjual, dan rincian pesanan selesai.</p></div>
    <section class="ad-panel sa-sales-panel" aria-labelledby="sa-sales-title">
        <h2 id="sa-sales-title">Periode Laporan</h2><p class="ad-muted sa-sales-subtitle">Pilih rentang tanggal penyelesaian pesanan untuk menampilkan laporan.</p>
        <?php if (!$valid): ?><p class="sa-sales-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($laporan['error']) ?></p><?php endif; ?>
        <form class="sa-sales-filter" action="<?= BASE_URL ?>/index.php" method="get">
            <input type="hidden" name="page" value="superadmin-laporan">
            <div><label for="sa-sales-start">Tanggal mulai</label><input type="date" id="sa-sales-start" name="mulai" value="<?= $aman($laporan['mulai']) ?>" min="2000-01-01" max="2100-12-31" required></div>
            <div><label for="sa-sales-end">Tanggal akhir</label><input type="date" id="sa-sales-end" name="akhir" value="<?= $aman($laporan['akhir']) ?>" min="2000-01-01" max="2100-12-31" required></div>
            <button class="ad-button" type="submit">Tampilkan</button>
            <?php if ($valid): ?><a class="ad-button" href="<?= $aman($unduh('pdf')) ?>">Unduh PDF</a><a class="ad-button" href="<?= $aman($unduh('xlsx')) ?>">Unduh Excel</a><?php else: ?><button class="ad-button" type="button" disabled>Unduh PDF</button><button class="ad-button" type="button" disabled>Unduh Excel</button><?php endif; ?>
        </form>
        <?php if ($valid): ?>
            <p class="ad-sr-only">Laporan dan unduhan untuk periode <?= $aman($tanggal($laporan['mulai'])) ?> sampai <?= $aman($tanggal($laporan['akhir'])) ?>.</p>
            <div class="sa-sales-stats" aria-label="Ringkasan periode terpilih">
                <div><p>Penjualan produk</p><strong data-sa-sales-products><?= $rupiah($total['produk']) ?></strong><small>Tidak termasuk ongkir</small></div>
                <div><p>Pesanan selesai</p><strong data-sa-sales-orders><?= (int) $total['pesanan'] ?> pesanan</strong><small>Dalam periode yang dipilih</small></div>
                <div><p>Produk terjual</p><strong data-sa-sales-trays><?= (int) $total['loyang'] ?> loyang</strong><small>Total jumlah loyang terjual</small></div>
                <div><p>Ongkos kirim</p><strong data-sa-sales-shipping><?= $rupiah($total['ongkir']) ?></strong><small>Dicatat terpisah dari penjualan</small></div>
            </div>
            <div class="sa-sales-section-heading"><h2 id="sa-sales-products-title">Rincian Produk Terjual</h2><p class="ad-muted">Jumlah loyang dan nilai penjualan setiap produk.</p></div>
            <div class="ad-table-scroll sa-sales-table-wrap" role="region" aria-labelledby="sa-sales-products-title" tabindex="0">
                <table class="ad-table sa-sales-products-table"><caption class="ad-sr-only">Produk terjual berdasarkan harga pada pesanan. Harga berbeda ditampilkan pada baris terpisah.</caption>
                    <thead><tr><th scope="col">Produk</th><th scope="col">Kategori</th><th scope="col">Harga / loyang</th><th scope="col">Terjual</th><th scope="col">Penjualan produk</th></tr></thead>
                    <tbody><?php foreach ($laporan['produk_terjual'] as $produk): ?><tr data-sa-sales-product-row><th scope="row"><?= $aman($produk['nama']) ?></th><td><?= $aman($produk['kategori']) ?></td><td><?= $rupiah($produk['harga']) ?></td><td><?= (int) $produk['jumlah'] ?> loyang</td><td><strong><?= $rupiah($produk['subtotal']) ?></strong></td></tr><?php endforeach; ?>
                        <?php if (!$laporan['produk_terjual']): ?><tr><td class="sa-sales-empty" colspan="5">Belum ada produk terjual pada periode ini.</td></tr><?php endif; ?>
                    </tbody><tfoot><tr><th scope="row" colspan="3">Total</th><td><?= (int) $total['loyang'] ?> loyang</td><td><?= $rupiah($total['produk']) ?></td></tr></tfoot>
                </table>
            </div>
            <div class="sa-sales-section-heading sa-sales-orders-heading"><h2 id="sa-sales-orders-title">Rincian Pesanan Selesai</h2><p class="ad-muted">Tanggal pada tabel adalah tanggal pesanan selesai.</p></div>
            <div class="ad-table-scroll sa-sales-table-wrap" role="region" aria-labelledby="sa-sales-orders-title" tabindex="0">
                <table class="ad-table sa-sales-orders-table"><caption class="ad-sr-only">Pesanan selesai dalam periode terpilih</caption>
                    <thead><tr><th scope="col">Nomor pesanan</th><th scope="col">Pelanggan</th><th scope="col">Tanggal selesai</th><th scope="col">Jenis pesanan</th><th scope="col">Loyang</th><th scope="col">Nilai produk</th><th scope="col">Ongkir</th><th scope="col">Total</th></tr></thead>
                    <tbody><?php foreach ($laporan['items'] as $p): ?><tr data-sa-sales-order-row><th scope="row"><?= $aman($p['nomor']) ?></th><td><?= $aman($p['nama']) ?></td><td><?= $aman($tanggal($p['tanggal_selesai'])) ?></td><td><?= $aman($p['jenis']) ?></td><td><?= (int) $p['loyang'] ?></td><td><?= $rupiah($p['subtotal']) ?></td><td><?= $rupiah($p['ongkir']) ?></td><td><strong><?= $rupiah($p['total']) ?></strong></td></tr><?php endforeach; ?>
                        <?php if (!$laporan['items']): ?><tr><td class="sa-sales-empty" colspan="8"><strong>Belum ada pesanan selesai pada periode ini.</strong><p>Coba pilih rentang tanggal lain, lalu klik Tampilkan.</p></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="sa-sales-grand-total">Total nilai produk dan ongkir: <strong><?= $rupiah($total['total']) ?></strong></p>
        <?php endif; ?>
    </section>
</main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
