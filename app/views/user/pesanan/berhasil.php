<?php

$pesanan = $data['pesanan'] ?? null;
$pesanError = $data['pesanError'] ?? null;

$aman = static function ($nilai): string {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};

$rupiah = static function ($nilai): string {
    return 'Rp' . number_format((float) $nilai, 0, ',', '.');
};

$formatTanggal = static function (
    string $nilai,
    bool $denganJam = false
): string {
    $tanggal = new DateTimeImmutable(
        $nilai,
        new DateTimeZone('Asia/Jakarta')
    );

    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $hasil = $tanggal->format('d')
        . ' '
        . $bulan[(int) $tanggal->format('n')]
        . ' '
        . $tanggal->format('Y');

    if ($denganJam) {
        $hasil .= ', pukul ' . $tanggal->format('H.i') . ' WIB';
    }

    return $hasil;
};

if ($pesanan !== null) {
    $kirim = $pesanan['penerimaan'] === 'kirim';
    $dibatalkan = $pesanan['status'] === 'Dibatalkan';
    $menungguOngkir = $kirim && in_array($pesanan['status'], ['Menunggu Biaya Ongkir', 'Menunggu Konfirmasi Ongkir'], true);
    $bolehBayar = $pesanan['status'] === 'Menunggu Pembayaran' && $pesanan['total_pembayaran'] !== null;
    $kodePesanan = rawurlencode($pesanan['nomor']);

    $jenisLabel = $pesanan['jenis'] === 'booking'
        ? 'Booking'
        : 'Pesan Sekarang';

    $layananLabel = Pesanan::namaLayanan($pesanan['penerimaan'], $pesanan['pengiriman']);

    $pembayaranLabel = ProfilToko::namaPembayaran($pesanan['pembayaran']);
}
?>



<main class="pb-page">
    <div class="container pb-container">

        <nav class="pb-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= $aman(BASE_URL) ?>/">Beranda</a>
            <span aria-hidden="true">/</span>
            <span>Pesanan Berhasil</span>
        </nav>

        <h1>Selangkah lebih dekat dengan hari istimewamu</h1>

        <p class="pb-lead">
            <?php if ($pesanan !== null && $bolehBayar): ?>
                Pesananmu tercatat. Lanjutkan pembayaran agar dapat diproses.
            <?php else: ?>
                Pantau kelanjutan pesanan melalui informasi pesananmu.
            <?php endif; ?>
        </p>

        <a
            class="pb-back"
            href="<?= $aman(BASE_URL) ?>/index.php?page=katalog"
        >
            ‹ Kembali ke katalog
        </a>

        <?php if ($pesanan === null): ?>

            <section class="pb-panel">
                <h2>Pesanan belum dapat ditampilkan</h2>
                <p><?= $aman($pesanError) ?></p>

                <a
                    class="pb-button pb-primary"
                    href="<?= $aman(BASE_URL) ?>/index.php?page=katalog"
                >
                    Kembali ke Katalog
                </a>
            </section>

        <?php else: ?>

            <article class="pb-panel">
                <header class="pb-success">
                    <span class="pb-check" aria-hidden="true">
                        <svg viewBox="0 0 48 48">
                            <path d="M11 25 20 34 38 15"></path>
                        </svg>
                    </span>

                    <h2><?= $dibatalkan ? 'Pesanan dibatalkan' : 'Pesanan berhasil dibuat!' ?></h2>

                    <p>
                        <?php if ($dibatalkan): ?>
                            <?= $aman(Pesanan::pesanPembatalan($pesanan)) ?>
                        <?php elseif ($menungguOngkir): ?>
                            Admin akan memeriksa alamat, menghitung ongkir, lalu mengirimkan total pembayaran.
                        <?php elseif ($bolehBayar): ?>
                            Silakan bayar melalui <?= $aman($pembayaranLabel) ?>, lalu unggah bukti pembayaran.
                        <?php else: ?>
                            Lihat perkembangan pesananmu pada halaman detail.
                        <?php endif; ?>
                    </p>

                    <span class="pb-status">
                        <?= $aman($pesanan['status']) ?>
                    </span>
                </header>

                <section class="pb-section">
                    <h3>Informasi pesanan</h3>

                    <dl class="pb-info-grid">
                        <div>
                            <dt>Nomor pesanan</dt>
                            <dd><?= $aman($pesanan['nomor']) ?></dd>
                        </div>

                        <div>
                            <dt>Jenis pesanan</dt>
                            <dd><?= $aman($jenisLabel) ?></dd>
                        </div>

                        <div>
                            <dt>Tanggal pesanan dibuat</dt>
                            <dd>
                                <?= $aman($formatTanggal(
                                    $pesanan['dibuat_pada'],
                                    true
                                )) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Metode penerimaan</dt>
                            <dd>
                                <?= $kirim ? 'Kirim ke Alamat' : 'Ambil di Toko' ?>
                            </dd>
                        </div>

                        <div>
                            <dt>
                                <?= $kirim
                                    ? 'Tanggal pengiriman'
                                    : 'Tanggal pengambilan' ?>
                            </dt>
                            <dd>
                                <?= $aman($formatTanggal(
                                    $pesanan['tanggal_pesanan']
                                )) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Metode pembayaran</dt>
                            <dd><?= $aman($pembayaranLabel) ?></dd>
                        </div>

                        <div>
                            <dt>Produk yang dipesan</dt>
                            <dd>
                                <?php foreach ($pesanan['items'] as $item): ?><div><?= $aman($item['nama']) ?>
                                · <?= (int) $item['jumlah'] ?> loyang</div><?php endforeach; ?>
                            </dd>
                        </div>

                        <?php if ($kirim): ?>
                            <div>
                                <dt>Layanan pengiriman</dt>
                                <dd><?= $aman($layananLabel) ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>

                    <div class="pb-address">
                        <?php if ($kirim): ?>
                            <span>Alamat penerima</span>

                            <strong>
                                <?= $aman($pesanan['nama_penerima']) ?>
                                · <?= $aman($pesanan['telepon']) ?>
                            </strong>

                            <p><?= nl2br($aman($pesanan['alamat'])) ?></p>
                        <?php else: ?>
                            <span>Lokasi pengambilan</span>
                            <strong>Ayu Cake</strong>
                            <p>
                                Desa Bukit Terap, Kecamatan Tukak Sadai,
                                Bangka Selatan.
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if ($pesanan['catatan'] !== ''): ?>
                        <p class="pb-order-note">
                            <strong>Catatan pesanan:</strong>
                            <?= nl2br($aman($pesanan['catatan'])) ?>
                        </p>
                    <?php endif; ?>
                </section>

                <section class="pb-cost" aria-label="Rincian biaya">
                    <div class="pb-cost-row">
                        <span>Subtotal produk</span>
                        <strong>
                            <?= $rupiah($pesanan['subtotal']) ?>
                        </strong>
                    </div>

                    <div class="pb-cost-row">
                        <span>Ongkos kirim</span>
                        <strong>
                            <?php if (!$kirim): ?>
                                Rp0 — Ambil di Toko
                            <?php elseif ($pesanan['ongkir_final'] !== null): ?>
                                <?= $rupiah($pesanan['ongkir_final']) ?>
                            <?php else: ?>
                                <?= $menungguOngkir ? 'Dihitung admin' : '—' ?>
                            <?php endif; ?>
                        </strong>
                    </div>
                    <div class="pb-cost-row pb-cost-total">
                        <strong>Total pembayaran</strong>
                        <strong>
                            <?= $pesanan['total_pembayaran'] !== null
                                ? $rupiah($pesanan['total_pembayaran'])
                                : ($menungguOngkir ? 'Menunggu konfirmasi ongkir' : '—') ?>
                        </strong>
                    </div>
                </section>

                <?php if ($bolehBayar && $pesanan['batas_pembayaran'] !== null): ?>
                    <section class="pb-notice">
                        <span>Selesaikan pembayaran sebelum</span>
                        <strong class="pb-deadline"><?= $aman($formatTanggal($pesanan['batas_pembayaran'], true)) ?></strong>
                    </section>
                <?php endif; ?>

                <div class="pb-actions">
                    <?php if ($bolehBayar): ?>
                        <a class="pb-button pb-primary" href="<?= $aman(BASE_URL . '/index.php?page=pembayaran&nomor=' . $kodePesanan) ?>">Lanjut ke Pembayaran</a>
                    <?php else: ?>
                        <a class="pb-button pb-secondary" href="<?= $aman(BASE_URL) ?>/index.php?page=katalog">Kembali ke Katalog</a>
                    <?php endif; ?>
                    <a class="pb-button <?= $bolehBayar ? 'pb-secondary' : 'pb-primary' ?>" href="<?= $aman(BASE_URL . '/index.php?page=detail-pesanan&nomor=' . $kodePesanan) ?>">Lihat Detail Pesanan</a>
                </div>
            </article>

        <?php endif; ?>
    </div>
</main>

