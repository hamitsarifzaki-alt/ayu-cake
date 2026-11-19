<?php

require __DIR__ . '/partials/tampilan.php';

$daftar = $data['daftarPesanan'] ?? [];
?>

<main class="po-page">
    <div class="container po-container">

        <div class="po-breadcrumb">
            BERANDA / AKUN SAYA / STATUS PESANAN
        </div>

        <h1>Status Pesanan</h1>
        <?php $notifikasi = $data['notifikasiPelanggan']; $peranNotifikasi = 'pelanggan'; require APP_PATH . '/views/shared/notifikasi.php'; ?>

        <p class="po-lead">
            Lihat perkembangan pesanan dan lanjutkan langkah yang diperlukan.
        </p>

        <a class="po-back" href="<?= $aman(BASE_URL) ?>/">
            ‹ Kembali ke beranda
        </a>

        <section class="po-panel">
            <div class="po-heading-row">
                <div>
                    <h2>Pesanan yang sedang berjalan</h2>
                    <p class="po-muted">
                        Informasi terbaru untuk setiap pesananmu.
                    </p>
                </div>

                <span class="po-badge">
                    <?= count($daftar) ?> pesanan
                </span>
            </div>

            <?php if (empty($daftar)): ?>
                <div class="po-empty">
                    <h3>Belum ada pesanan aktif</h3>

                    <p>
                        Pilih produk dari katalog untuk membuat
                        Booking atau Pesan Sekarang.
                    </p>

                    <a
                        class="po-button po-primary"
                        href="<?= $aman(BASE_URL) ?>/index.php?page=katalog"
                    >
                        Lihat Katalog
                    </a>
                </div>
            <?php else: ?>

                <div class="po-list">
                    <?php foreach ($daftar as $pesanan): ?>
                        <?php
                        $kirim = $pesanan['penerimaan'] === 'kirim';
                        $totalFinal = $pesanan['total_pembayaran'] !== null;

                        $layanan = Pesanan::namaLayanan($pesanan['penerimaan'], $pesanan['pengiriman']);

                        $gambar = $pesanan['items'][0]['gambar'] ?? '';

                        $urlDetail = BASE_URL
                            . '/index.php?page=detail-pesanan&nomor='
                            . rawurlencode($pesanan['nomor']);
                        ?>

                        <article class="po-card">

                            <div class="po-card-head">
                                <div>
                                    <h3>
                                        <?= $aman($pesanan['nomor']) ?>
                                    </h3>

                                    <p class="po-muted">
                                        <?= $aman($jenisPesanan($pesanan)) ?>
                                        ·
                                        <?= $aman($layanan) ?>
                                    </p>
                                </div>

                                <span class="po-badge <?= $aman(
                                    $kelasStatus($pesanan['status'])
                                ) ?>">
                                    <?= $aman($pesanan['status']) ?>
                                </span>
                            </div>

                            <div class="po-product-row">

                                <?php if ($gambar !== ''): ?>
                                    <img
                                        class="po-product-photo"
                                        src="<?= $aman(
                                            BASE_URL
                                            . '/public/assets/images/'
                                            . $gambar
                                        ) ?>"
                                        alt="<?= $aman($pesanan['items'][0]['nama']) ?>"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <div class="po-photo-empty">
                                        Foto belum tersedia
                                    </div>
                                <?php endif; ?>

                                <div class="po-product-copy">
                                    <strong>
                                        <?= $aman($pesanan['items'][0]['nama']) ?>
                                    </strong>

                                    <p class="po-muted">
                                        <?= $pesanan['jumlah_produk'] > 1
                                            ? '+ ' . ($pesanan['jumlah_produk'] - 1) . ' produk lainnya'
                                            : '1 produk' ?> ·
                                        <?= (int) $pesanan['jumlah_loyang'] ?>
                                        loyang
                                        <?php if ($pesanan['jumlah_produk'] > 1): ?>
                                            <span class="sr-only">: <?= $aman(implode(', ', array_column(array_slice($pesanan['items'], 1), 'nama'))) ?></span>
                                        <?php endif; ?>
                                    </p>

                                    <p class="po-muted">
                                        Tanggal
                                        <?= $kirim ? 'pengiriman' : 'pengambilan' ?>:
                                        <?= $aman($tanggal(
                                            $pesanan['tanggal_pesanan']
                                        )) ?>
                                    </p>
                                </div>

                                <div class="po-price">
                                    <span class="po-muted">
                                        <?= $totalFinal
                                            ? 'Total pembayaran'
                                            : 'Subtotal produk' ?>
                                    </span>

                                    <strong>
                                        <?= $rupiah(
                                            $totalFinal
                                                ? $pesanan['total_pembayaran']
                                                : $pesanan['subtotal']
                                        ) ?>
                                    </strong>

                                    <small>
                                        <?php if (!$totalFinal): ?>
                                            Total akhir menunggu konfirmasi ongkir
                                        <?php elseif ($kirim): ?>
                                            Termasuk ongkir <?= $aman($layanan) ?>
                                            <?= $rupiah($pesanan['ongkir_final']) ?>
                                        <?php else: ?>
                                            Tanpa ongkos kirim
                                        <?php endif; ?>
                                    </small>
                                </div>

                            </div>

                            <p class="po-notice">
                                <?= $aman($petunjukStatus($pesanan)) ?>
                            </p>

                            <div class="po-actions">

                                <?php if ($pesanan['boleh_batal']): ?>
                                    <form
                                        method="post"
                                        action="<?= $aman(BASE_URL) ?>/index.php?page=batalkan-pesanan"
                                        data-po-cancel
                                    >
                                        <input
                                            type="hidden"
                                            name="token_pesanan"
                                            value="<?= $aman($data['tokenPesanan']) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="nomor"
                                            value="<?= $aman($pesanan['nomor']) ?>"
                                        >

                                        <button
                                            class="po-button po-danger"
                                            type="submit"
                                        >
                                            Batalkan Pesanan
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <a
                                    class="po-button po-secondary po-right"
                                    href="<?= $aman($urlDetail) ?>"
                                >
                                    Lihat Detail Pesanan
                                </a>
                                <?php if ($pesanan['status'] === 'Menunggu Pembayaran' && $totalFinal): ?>
                                    <a class="po-button po-primary" href="<?= $aman(
                                        BASE_URL . '/index.php?page=pembayaran&nomor='
                                        . rawurlencode($pesanan['nomor'])
                                    ) ?>">Bayar Sekarang</a>
                                <?php endif; ?>

                            </div>

                        </article>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </section>

    </div>
</main>

<script src="<?= $aman(BASE_URL) ?>/public/assets/js/pesanan.js?v=3" defer></script>
