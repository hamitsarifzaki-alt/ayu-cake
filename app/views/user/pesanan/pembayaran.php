<?php

require __DIR__ . '/partials/tampilan.php';

$pesanan = $data['pesanan'] ?? null;
$penghalang = $data['penghalang'] ?? null;
$pesanError = $data['pesanError'] ?? null;

if ($pesanan !== null) {
    $kirim = $pesanan['penerimaan'] === 'kirim';
    $metode = ProfilToko::namaPembayaran($pesanan['pembayaran']);
    $rekening = $pesanan['tujuan_bank'];

    $layanan = Pesanan::namaLayanan($pesanan['penerimaan'], $pesanan['pengiriman']);

    $urlDetail = BASE_URL
        . '/index.php?page=detail-pesanan&nomor='
        . rawurlencode($pesanan['nomor']);

    $totalFinal = $pesanan['total_pembayaran'] !== null;
}
?>

<main class="po-page">
    <div class="container po-container">
        <div class="po-breadcrumb">
            BERANDA / DETAIL PESANAN / PEMBAYARAN
        </div>

        <h1>Pembayaran &amp; Bukti Bayar</h1>

        <p class="po-lead">
            <?= $penghalang === null ? 'Periksa pesananmu, lakukan pembayaran, lalu unggah buktinya.' : 'Lihat rincian dan status pembayaran pesananmu.' ?>
        </p>

        <a
            class="po-back"
            href="<?= $aman(
                $pesanan !== null
                    ? $urlDetail
                    : BASE_URL . '/index.php?page=status-pesanan'
            ) ?>"
        >
            ‹ Kembali ke <?= $pesanan !== null
                ? 'Detail Pesanan'
                : 'Status Pesanan' ?>
        </a>

        <?php if ($pesanan === null): ?>
            <section class="po-panel po-empty">
                <h2>Pesanan tidak ditemukan</h2>
                <p>Buka pembayaran melalui Detail Pesanan.</p>
            </section>
        <?php else: ?>

            <article class="po-panel pay-panel">
                <div class="po-heading-row">
                    <div>
                        <h2>Pesanan <?= $aman($pesanan['nomor']) ?></h2>
                        <p class="po-muted">
                            Dibuat <?= $aman($tanggal(
                                $pesanan['dibuat_pada'],
                                true
                            )) ?>
                        </p>
                    </div>

                    <span class="po-badge <?= $aman(
                        $kelasStatus($pesanan['status'])
                    ) ?>">
                        <?= $aman($pesanan['status']) ?>
                    </span>
                </div>

                <?php if ($pesanan['penolakan_bukti'] !== null): ?>
                    <?php require __DIR__ . '/partials/penolakan-bukti.php'; ?>
                <?php endif; ?>

                <?php if ($penghalang === null && $pesanan['penolakan_bukti'] === null): ?>
                    <div class="po-notice po-notice-gold">
                        <span>Batas pembayaran dan unggah bukti</span>
                        <strong class="po-deadline">
                            <?= $aman($tanggal(
                                $pesanan['batas_pembayaran'],
                                true
                            )) ?>
                        </strong>
                        <p>Selesaikan sebelum batas waktu berakhir.</p>
                    </div>
                <?php elseif ($penghalang !== null): ?>
                    <div class="po-notice po-notice-gold" role="status">
                        <?= $aman($penghalang) ?>
                    </div>
                <?php endif; ?>

                <section class="po-section">
                    <h3 class="pay-heading">
                        <span>1</span> Informasi pesanan
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
                                <?= $kirim ? 'Kirim ke Alamat' : 'Ambil di Toko' ?>
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
                    </dl>

                    <div class="po-white-box">
                        <span class="po-muted">
                            <?= $kirim ? 'Alamat penerima' : 'Lokasi pengambilan' ?>
                        </span>

                        <p>
                            <?php if ($kirim): ?>
                                <?= nl2br($aman($pesanan['alamat'])) ?>
                            <?php else: ?>
                                Ayu Cake — Desa Bukit Terap,
                                Kecamatan Tukak Sadai, Bangka Selatan.
                            <?php endif; ?>
                        </p>
                    </div>

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
                </section>

                <section class="po-section">
                    <h3 class="pay-heading">
                        <span>2</span> Rincian pembayaran
                    </h3>

                    <div class="po-white-box">
                        <span class="po-muted">
                            Metode pembayaran yang dipilih saat checkout
                        </span>
                        <p class="pay-method"><strong><?= $aman($metode) ?></strong></p>
                    </div>

                    <dl class="po-payment">
                        <div>
                            <dt>Subtotal produk</dt>
                            <dd><?= $rupiah($pesanan['subtotal']) ?></dd>
                        </div>
                        <div>
                            <dt>Ongkos kirim</dt>
                            <dd>
                                <?php if (!$kirim): ?>
                                    Rp0 — Ambil di Toko
                                <?php elseif ($pesanan['ongkir_final'] !== null): ?>
                                    <?= $rupiah($pesanan['ongkir_final']) ?>
                                    — <?= $aman($layanan) ?>
                                <?php else: ?>
                                    <?= $pesanan['status'] === 'Dibatalkan' ? '—' : 'Menunggu konfirmasi ongkir' ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div class="po-payment-total">
                            <dt>Total yang harus dibayar</dt>
                            <dd>
                                <?= $totalFinal
                                    ? $rupiah($pesanan['total_pembayaran'])
                                    : ($pesanan['status'] === 'Dibatalkan' ? '—' : 'Menunggu konfirmasi ongkir') ?>
                            </dd>
                        </div>
                    </dl>
                </section>

                <?php if ($penghalang === null): ?>
                    <section class="po-section">
                        <h3 class="pay-heading">
                            <span>3</span>
                            Pembayaran melalui <?= $aman($metode) ?>
                        </h3>

                        <div class="po-white-box">
                            <dl class="po-info-grid">
                                <div><dt>Bank tujuan</dt><dd><?= $aman($rekening['nama']) ?></dd></div>
                                <div><dt>Nomor rekening</dt><dd><?= $aman($rekening['nomor']) ?></dd></div>
                                <div><dt>Atas nama</dt><dd><?= $aman($rekening['atas_nama']) ?></dd></div>
                            </dl>
                        </div>

                        <ol class="pay-steps">
                            <li>Buka layanan perbankan, lalu pilih transfer ke bank tujuan yang tercantum.</li>
                            <li>Masukkan nomor rekening dan periksa nama penerima sebelum melanjutkan transfer.</li>
                            <li>Bayar sesuai total <strong><?= $rupiah($pesanan['total_pembayaran']) ?></strong>.</li>
                            <li>Simpan bukti transaksi yang berhasil.</li>
                        </ol>
                    </section>

                    <section class="po-section">
                        <h3 class="pay-heading">
                            <span>4</span> Unggah bukti pembayaran
                        </h3>

                        <p class="po-muted">
                            Pastikan nominal, tanggal, dan status transaksi terlihat jelas.
                            Format JPG atau PNG, maksimal 2 MB.
                        </p>

                        <?php if ($pesanError !== null): ?>
                            <p class="pay-error" role="alert" data-dismissible-notice>
                                <?= $aman($pesanError) ?>
                            </p>
                        <?php endif; ?>

                        <form
                            id="pay-upload-form"
                            action="<?= $aman(BASE_URL) ?>/index.php?page=unggah-bukti"
                            method="post"
                            enctype="multipart/form-data"
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

                            <div class="pay-upload-box">
                                <strong>Pilih gambar bukti pembayaran</strong>

                                <p
                                    id="pay-file-name"
                                    class="po-muted"
                                    aria-live="polite"
                                >
                                    Belum ada berkas dipilih
                                </p>

                                <label class="po-button po-secondary pay-file-label">
                                    Pilih Berkas
                                    <input
                                        id="pay-file"
                                        type="file"
                                        name="bukti"
                                        accept="image/jpeg,image/png"
                                        required
                                        aria-label="Pilih gambar bukti pembayaran"
                                    >
                                </label>

                                <div id="pay-preview-wrap" hidden>
                                    <img
                                        id="pay-preview"
                                        alt="Pratinjau bukti pembayaran yang dipilih"
                                    >
                                </div>
                            </div>

                            <p
                                id="pay-file-error"
                                class="pay-error"
                                hidden
                                role="alert"
                            ></p>

                            <div class="po-notice po-notice-gold">
                                Setelah bukti berhasil dikirim, status menjadi
                                <strong>Menunggu Verifikasi</strong>.
                                Pesanan tidak dapat dibatalkan setelah bukti diunggah.
                            </div>

                            <button
                                id="pay-submit"
                                class="po-button po-primary pay-submit"
                                type="submit"
                                disabled
                            >
                                Kirim Bukti Pembayaran
                            </button>

                            <p class="po-muted pay-footnote">
                                Unggah bukti pembayaran sesuai tagihan pesanan.
                                Tidak perlu melakukan pembayaran sungguhan untuk pengujian ini.
                            </p>
                        </form>
                    </section>
                <?php endif; ?>
            </article>
        <?php endif; ?>
    </div>
</main>

<script src="<?= $aman(BASE_URL) ?>/public/assets/js/pembayaran.js?v=1"></script>
