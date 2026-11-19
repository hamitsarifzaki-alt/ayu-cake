<?php
require __DIR__ . '/partials/tampilan.php';
$daftar = $data['daftarPesanan'];
?>
<main class="po-page rh-page">
    <div class="container po-container">
        <nav class="po-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/">BERANDA</a> / AKUN SAYA / <span aria-current="page">RIWAYAT PESANAN</span>
        </nav>
        <h1>Riwayat Pesanan</h1>
        <p class="po-lead">Lihat kembali pesanan yang telah selesai atau dibatalkan.</p>
        <a class="po-back" href="<?= BASE_URL ?>/">← Kembali ke Beranda</a>

        <section class="po-panel">
            <div class="po-heading-row">
                <div>
                    <h2>Pesanan sebelumnya</h2>
                    <p class="po-muted">Pesanan yang sudah selesai atau dibatalkan ditampilkan di sini.</p>
                </div>
                <span class="po-badge"><?= count($daftar) ?> pesanan</span>
            </div>
            <?php if ($daftar === []): ?>
                <div class="po-empty">
                    <h3>Belum ada riwayat pesanan</h3>
                    <p>Pesananmu akan muncul di sini setelah selesai atau dibatalkan.</p>
                    <div class="rh-empty-actions">
                        <a class="po-button po-secondary" href="<?= BASE_URL ?>/index.php?page=status-pesanan">Lihat Status Pesanan</a>
                        <a class="po-button po-primary" href="<?= BASE_URL ?>/index.php?page=katalog">Lihat Katalog</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="po-list">
                    <?php foreach ($daftar as $pesanan): ?>
                        <?php
                        $selesai = $pesanan['status'] === 'Selesai';
                        $pertama = $pesanan['items'][0];
                        $layanan = Pesanan::namaLayanan($pesanan['penerimaan'], $pesanan['pengiriman']);
                        $urlDetail = BASE_URL . '/index.php?page=detail-pesanan&nomor=' . rawurlencode($pesanan['nomor']);
                        ?>
                        <article class="po-card">
                            <div class="po-card-head">
                                <div>
                                    <h3><?= $aman($pesanan['nomor']) ?></h3>
                                    <p class="po-muted">
                                        <?= $pesanan['jenis'] === 'booking' ? 'Booking / Pre-Order' : 'Pesan Sekarang / Ready Stock' ?>
                                        • <?= $aman($layanan) ?>
                                    </p>
                                </div>
                                <span class="po-badge <?= $aman($kelasStatus($pesanan['status'])) ?>"><?= $aman($pesanan['status']) ?></span>
                            </div>
                            <div class="po-product-row">
                                <?php if ($pertama['gambar'] !== ''): ?>
                                    <img class="po-product-photo" src="<?= $aman(BASE_URL . '/public/assets/images/' . $pertama['gambar']) ?>" alt="<?= $aman($pertama['nama']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="po-photo-empty">Foto belum tersedia</div>
                                <?php endif; ?>
                                <div class="po-product-copy">
                                    <strong><?= $aman($pertama['nama']) ?></strong>
                                    <p class="po-muted">
                                        <?= $pesanan['jumlah_produk'] > 1 ? '+ ' . ($pesanan['jumlah_produk'] - 1) . ' produk lainnya' : '1 produk' ?>
                                        • <?= (int) $pesanan['jumlah_loyang'] ?> Loyang
                                    </p>
                                    <p class="po-muted">
                                        <?= $selesai ? 'Selesai' : 'Dibatalkan' ?>
                                        <?= $pesanan['waktu_akhir'] !== null ? 'pada ' . $aman($tanggal($pesanan['waktu_akhir'])) : '— tanggal belum tersedia' ?>
                                    </p>
                                </div>
                                <div class="po-price">
                                    <span class="po-muted"><?= $pesanan['total_pembayaran'] !== null ? 'Total pesanan' : 'Subtotal produk' ?></span>
                                    <strong><?= $rupiah($pesanan['total_pembayaran'] ?? $pesanan['subtotal']) ?></strong>
                                    <small>
                                        <?php if ($pesanan['total_pembayaran'] === null): ?>
                                            Ongkir belum ditetapkan
                                        <?php elseif (($pesanan['ongkir_final'] ?? 0) > 0): ?>
                                            Termasuk ongkir <?= $rupiah($pesanan['ongkir_final']) ?>
                                        <?php elseif (!empty($pesanan['pembayaran_terverifikasi']) || !empty($pesanan['pembayaran_diverifikasi_pada'])): ?>
                                            Pembayaran terverifikasi
                                        <?php elseif (!$selesai && empty($pesanan['pernah_unggah_bukti']) && empty($pesanan['bukti_pembayaran'])): ?>
                                            Belum dibayar
                                        <?php else: ?>
                                            <?= $selesai ? 'Pesanan selesai' : 'Lihat rincian pembayaran' ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <p class="po-notice <?= !$selesai ? 'rh-cancelled' : '' ?>">
                                <?= $aman($petunjukStatus($pesanan)) ?>
                            </p>
                            <div class="rh-actions">
                                <a class="po-button po-secondary rh-detail" href="<?= $aman($urlDetail) ?>">Lihat Detail</a>
                            </div>

                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
    </div>
</main>

