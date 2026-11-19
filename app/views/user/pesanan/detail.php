<?php

require __DIR__ . '/partials/tampilan.php';

$pesanan = $data['pesanan'] ?? null;
$dariRiwayat = $pesanan !== null && in_array($pesanan['status'], ['Selesai', 'Dibatalkan'], true);
$halamanKembali = $dariRiwayat ? 'riwayat-pesanan' : 'status-pesanan';
$labelKembali = $dariRiwayat ? 'Riwayat Pesanan' : 'Status Pesanan';

if ($pesanan !== null) {
    $kirim = $pesanan['penerimaan'] === 'kirim';
    $totalFinal = $pesanan['total_pembayaran'] !== null;

    $layanan = Pesanan::namaLayanan($pesanan['penerimaan'], $pesanan['pengiriman']);

    $metodeBayar = ProfilToko::namaPembayaran($pesanan['pembayaran']);

    $bolehBayar = $pesanan['status'] === 'Menunggu Pembayaran'
        && $totalFinal;

}
?>

<main class="po-page">
    <div class="container po-container">
        <div class="po-breadcrumb">
            BERANDA / <?= strtoupper($labelKembali) ?> / DETAIL PESANAN
        </div>

        <h1>Detail Pesanan</h1>
        <p class="po-lead">Informasi lengkap pesanan dan pembayaranmu.</p>

        <a
            class="po-back"
            href="<?= $aman(BASE_URL) ?>/index.php?page=<?= $halamanKembali ?>"
        >
            ‹ Kembali ke <?= $labelKembali ?>
        </a>

        <?php if ($pesanan === null): ?>
            <section class="po-panel po-empty">
                <h2>Pesanan tidak ditemukan</h2>
                <p>
                    Buka pesanan melalui daftar Status Pesanan
                    melalui akun yang digunakan saat memesan.
                </p>
            </section>
        <?php else: ?>

            <article class="po-panel">
                <div class="po-heading-row">
                    <div>
                        <h2><?= $aman($pesanan['nomor']) ?></h2>
                        <p class="po-muted">
                            Dibuat pada
                            <?= $aman($tanggal($pesanan['dibuat_pada'], true)) ?>
                        </p>
                    </div>

                    <span class="po-badge <?= $aman(
                        $kelasStatus($pesanan['status'])
                    ) ?>">
                        <?= $aman($pesanan['status']) ?>
                    </span>
                </div>

                <?php if ($pesanan['jenis'] === 'booking'): ?>
                    <section class="po-white-box" aria-labelledby="po-booking-date-label">
                        <span class="po-muted" id="po-booking-date-label">Tanggal booking yang dipilih</span>
                        <strong class="po-deadline">
                            <time datetime="<?= $aman($pesanan['tanggal_pesanan']) ?>">
                                <?= $aman($tanggal($pesanan['tanggal_pesanan'])) ?>
                            </time>
                        </strong>
                        <p>
                            Tanggal yang Anda pilih untuk
                            <?= $kirim ? 'pengiriman' : 'pengambilan' ?> kue.
                        </p>
                    </section>
                <?php endif; ?>

                <?php if (!empty($data['pesanBukti'])): ?>
                    <div class="po-notice po-notice-gold" role="status" data-dismissible-notice>
                        <?= $aman($data['pesanBukti']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($pesanan['penolakan_bukti'] !== null): ?>
                    <?php require __DIR__ . '/partials/penolakan-bukti.php'; ?>
                <?php else: ?>
                <div class="po-notice po-notice-gold">
                    <?php if ($bolehBayar && $pesanan['batas_pembayaran']): ?>
                        <span>Selesaikan pembayaran sebelum</span>
                        <strong class="po-deadline">
                            <?= $aman($tanggal(
                                $pesanan['batas_pembayaran'],
                                true
                            )) ?>
                        </strong>
                        <p>
                            Setelah membayar, unggah bukti agar admin
                            dapat memverifikasi pesananmu.
                        </p>
                    <?php else: ?>
                        <?= $aman($petunjukStatus($pesanan)) ?>
                    <?php endif; ?>
                </div>

                <?php endif; ?>

                <section class="po-section">
                    <h3>
                        <?= $kirim
                            ? 'Informasi pengiriman'
                            : 'Informasi pengambilan' ?>
                    </h3>

                    <dl class="po-info-grid">
                        <div>
                            <dt>Nama penerima</dt>
                            <dd><?= $aman($pesanan['nama_penerima']) ?></dd>
                        </div>
                        <div>
                            <dt>Nomor telepon</dt>
                            <dd><?= $aman($pesanan['telepon']) ?></dd>
                        </div>
                        <div>
                            <dt>Jenis pesanan</dt>
                            <dd><?= $aman($jenisPesanan($pesanan)) ?></dd>
                        </div>
                        <div>
                            <dt>Metode penerimaan</dt>
                            <dd>
                                <?= $kirim ? 'Dikirim ke Alamat' : 'Ambil di Toko' ?>
                            </dd>
                        </div>
                        <div>
                            <dt>Tanggal dibuat</dt>
                            <dd>
                                <?= $aman($tanggal(
                                    $pesanan['dibuat_pada'],
                                    true
                                )) ?>
                            </dd>
                        </div>
                        <div>
                            <dt>
                                Tanggal <?= $kirim ? 'pengiriman' : 'pengambilan' ?>
                            </dt>
                            <dd>
                                <?= $aman($tanggal(
                                    $pesanan['tanggal_pesanan']
                                )) ?>
                            </dd>
                        </div>

                        <?php if ($kirim): ?>
                            <div>
                                <dt>Layanan pengiriman</dt>
                                <dd><?= $aman($layanan) ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>

                    <div class="po-white-box">
                        <span class="po-muted">
                            <?= $kirim ? 'Alamat penerima' : 'Lokasi pengambilan' ?>
                        </span>

                        <?php if ($kirim): ?>
                            <p><?= nl2br($aman($pesanan['alamat'])) ?></p>
                        <?php else: ?>
                            <p>
                                <strong>Ayu Cake</strong><br>
                                Desa Bukit Terap, Kecamatan Tukak Sadai,
                                Bangka Selatan.
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($pesanan['catatan_ongkir']) && $totalFinal): ?>
                        <p class="po-white-box po-muted">Catatan admin: <?= $aman($pesanan['catatan_ongkir']) ?></p>
                    <?php endif; ?>
                </section>

                <section class="po-section">
                    <h3>Produk yang dipesan</h3>
                    <p class="po-muted">
                        <?= (int) $pesanan['jumlah_produk'] ?> jenis produk ·
                        <?= (int) $pesanan['jumlah_loyang'] ?> loyang
                    </p>

                    <div class="po-table-wrap">
                        <table class="po-table">
                            <thead>
                                <tr>
                                    <th scope="col">Produk</th>
                                    <th scope="col">Harga per loyang</th>
                                    <th scope="col">Jumlah loyang</th>
                                    <th scope="col">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody><?php foreach ($pesanan['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?= $aman($item['nama']) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?= $rupiah($item['harga']) ?>
                                    </td>
                                    <td>
                                        <?= (int) $item['jumlah'] ?>
                                    </td>
                                    <td>
                                        <strong>
                                            <?= $rupiah($item['subtotal']) ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?></tbody>
                        </table>
                    </div>

                    <div class="po-white-box">
                        <h4>Catatan pesanan</h4>
                        <p>
                            <?= $pesanan['catatan'] !== ''
                                ? nl2br($aman($pesanan['catatan']))
                                : 'Tidak ada catatan tambahan.' ?>
                        </p>
                    </div>
                </section>

                <section class="po-section">
                    <h3>Rincian pembayaran</h3>

                    <dl class="po-payment">
                        <div>
                            <dt>Metode pembayaran</dt>
                            <dd><?= $aman($metodeBayar) ?></dd>
                        </div>
                        <?php if ($totalFinal && $pesanan['status'] !== 'Dibatalkan' && isset($pesanan['tujuan_bank'])): ?>
                        <div><dt>Nomor rekening tujuan</dt><dd><?= $aman($pesanan['tujuan_bank']['nomor']) ?></dd></div>
                        <div><dt>Atas nama rekening</dt><dd><?= $aman($pesanan['tujuan_bank']['atas_nama']) ?></dd></div>
                        <?php endif; ?>

                        <div>
                            <dt>Subtotal produk</dt>
                            <dd><?= $rupiah($pesanan['subtotal']) ?></dd>
                        </div>
                        <div>
                            <dt>Ongkos kirim</dt>
                            <dd>
                                <?php if (!$kirim): ?>
                                    Rp0 — Ambil di Toko
                                <?php elseif ($totalFinal): ?>
                                    <?= $rupiah($pesanan['ongkir_final']) ?>
                                    — <?= $aman($layanan) ?>
                                <?php elseif ($pesanan['status'] === 'Dibatalkan'): ?>
                                    —
                                <?php else: ?>
                                    Menunggu perhitungan admin
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div class="po-payment-total">
                            <dt>Total pembayaran</dt>
                            <dd>
                                <?= $totalFinal
                                    ? $rupiah($pesanan['total_pembayaran'])
                                    : ($pesanan['status'] === 'Dibatalkan' ? '—' : 'Menunggu konfirmasi ongkir') ?>
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="po-section">
                    <h3>Riwayat status</h3>

                    <ol class="po-timeline">
                        <?php foreach (
                            array_reverse($pesanan['riwayat_status']) as $riwayat
                        ): ?>
                            <li>
                                <div class="po-heading-row">
                                    <strong><?= $aman($riwayat['status']) ?></strong>
                                    <span class="po-muted">
                                        <?= $aman($tanggal($riwayat['waktu'], true)) ?>
                                    </span>
                                </div>
                                <p class="po-muted">
                                    <?= $aman($riwayat['keterangan']) ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </section>

                <div class="po-actions po-detail-actions">
                    <?php if ($data['bolehSelesai']): ?>
                        <form method="post" action="<?= $aman(BASE_URL . '/index.php?page=selesai-pesanan') ?>" class="customer-complete-form">
                            <input type="hidden" name="token_pesanan" value="<?= $aman($data['tokenPesanan']) ?>">
                            <input type="hidden" name="nomor" value="<?= $aman($pesanan['nomor']) ?>">
                            <input type="hidden" name="versi" value="<?= (int) ($pesanan['versi'] ?? 0) ?>">
                            <label><input type="checkbox" name="diterima" value="1" required> Saya sudah menerima pesanan dalam kondisi sesuai.</label>
                            <button type="submit" class="po-button po-primary">Pesanan Selesai</button>
                        </form>
                    <?php elseif ($pesanan['status'] === 'Diproses'): ?>
                        <div class="customer-complete-form">
                            <p>Konfirmasi penerimaan tersedia mulai <?= $aman($tanggal($pesanan['tanggal_pesanan'])) ?>, setelah pesanan diterima.</p>
                            <button type="button" class="po-button po-primary" disabled>Pesanan Selesai</button>
                        </div>
                    <?php endif; ?>
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
                            <button type="submit" class="po-button po-danger">
                                Batalkan Pesanan
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($bolehBayar): ?>
                        <a
    class="po-button po-primary po-right"
    href="<?= $aman(
        BASE_URL
        . '/index.php?page=pembayaran&nomor='
        . rawurlencode($pesanan['nomor'])
    ) ?>"
>
    Bayar Sekarang
</a>
                    <?php endif; ?>
                </div>

                <?php if ($pesanan['boleh_batal']): ?>
                    <p class="po-muted po-footnote">
                        Pesanan dapat dibatalkan sebelum bukti pembayaran diunggah.
                    </p>
                <?php endif; ?>
            </article>

        <?php endif; ?>
    </div>
</main>

<script src="<?= $aman(BASE_URL) ?>/public/assets/js/pesanan.js?v=3" defer></script>
