<?php
$periksa = $data['pesananPeriksa'];
$queryBukti = $periksa === null ? '' : '&nomor=' . rawurlencode($periksa['nomor']) . '&versi=' . $periksa['versi'];
$urlBukti = BASE_URL . '/index.php?page=admin-bukti' . $queryBukti;
$urlBuktiPenuh = BASE_URL . '/index.php?page=admin-bukti-penuh' . $queryBukti;
?>
<main class="ad-container ad-main" id="ad-main">
    <div class="ad-verifikasi-intro">
        <p class="ad-eyebrow">ADMIN / VERIFIKASI PEMBAYARAN / PERIKSA BUKTI</p>
        <h1>Periksa Bukti Pembayaran</h1>
        <p>Cocokkan bukti pembayaran dengan total tagihan pesanan.</p>
    </div>
    <a class="ad-proof-back" href="<?= $aman($urlVerifikasi) ?>">← Kembali ke Verifikasi Pembayaran</a>
    <section class="ad-panel ad-proof-panel" aria-labelledby="ad-proof-title">
        <?php if ($periksa === null): ?>
            <div class="ad-empty"><h2 id="ad-proof-title">Bukti tidak ditemukan</h2><p>Pilih bukti yang tersedia dari daftar Verifikasi Pembayaran.</p><a class="ad-button" href="<?= $aman($urlVerifikasi) ?>">Kembali ke daftar</a></div>
        <?php else: ?>
            <div class="ad-panel-heading"><div><h2 id="ad-proof-title"><?= $aman($periksa['nomor']) ?></h2><p class="ad-muted"><?= $aman($periksa['nama']) ?> • <?= $aman($periksa['jenis']) ?> • <?= $aman($periksa['layanan']) ?></p></div><span class="ad-badge"><?= $aman($periksa['status']) ?></span></div>
            
            <div class="ad-proof-grid">
                <figure class="ad-proof-image">
                    <a class="ad-proof-preview-stage" href="<?= $aman($urlBuktiPenuh) ?>" target="_blank" rel="noopener" aria-label="Buka bukti pembayaran ukuran penuh (tab baru)">
                        <img src="<?= $aman($urlBukti) ?>" alt="Bukti pembayaran <?= $aman($periksa['nama']) ?>, <?= $rupiah($periksa['total']) ?>">
                    </a>
                    <figcaption><span class="ad-proof-file-name"><?= $aman($periksa['bukti']['berkas']) ?></span><a class="ad-button" href="<?= $aman($urlBuktiPenuh) ?>" target="_blank" rel="noopener">Buka tampilan penuh ↗<span class="ad-sr-only"> (tab baru)</span></a></figcaption>
                    <p class="ad-muted ad-proof-preview-help">Klik gambar untuk memperbesar di tab baru. Isian pemeriksaan tetap tersedia di halaman ini.</p>
                </figure>
                <div>
                    <h3>Rincian pembayaran</h3>
                    <dl class="ad-detail-totals">
                        <div><dt>Metode pembayaran</dt><dd><?= $aman($periksa['pembayaran']) ?></dd></div>
                        <?php if (($periksa['tujuan_bank'] ?? null) !== null): ?><div><dt>Nomor rekening tujuan</dt><dd><?= $aman($periksa['tujuan_bank']['nomor']) ?></dd></div><div><dt>Atas nama rekening</dt><dd><?= $aman($periksa['tujuan_bank']['atas_nama']) ?></dd></div><?php endif; ?>
                        <div><dt>Waktu unggah</dt><dd><?= $aman($tanggal($periksa['bukti']['waktu'])) ?><br><?= $aman(substr($periksa['bukti']['waktu'], 11, 5)) ?> WIB</dd></div>
                        <div><dt>Subtotal produk</dt><dd><?= $rupiah($periksa['subtotal']) ?></dd></div>
                        <div><dt>Ongkos kirim</dt><dd><?= $rupiah($periksa['ongkir']) ?></dd></div>
                        <div class="ad-detail-total"><dt>Total tagihan</dt><dd><?= $rupiah($periksa['total']) ?></dd></div>
                    </dl>
                    <?php if ($data['error'] !== null): ?><p class="ad-proof-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($data['error']) ?></p><?php endif; ?>
                    <?php if ($periksa['status'] === 'Menunggu Verifikasi'): ?>
                        <form class="ad-proof-form" action="<?= BASE_URL ?>/index.php?page=admin-periksa-pembayaran&amp;nomor=<?= $aman($periksa['nomor']) ?>" method="post">
                            <input type="hidden" name="token_admin" value="<?= $aman($data['token']) ?>">
                            <input type="hidden" name="versi" value="<?= $aman((string) $periksa['versi']) ?>" autocomplete="off">
                            <label for="ad-proof-note">Catatan pemeriksaan</label>
                            <textarea id="ad-proof-note" name="catatan" rows="4" maxlength="500" aria-describedby="ad-proof-note-help" placeholder="Tuliskan alasan jika bukti pembayaran ditolak."><?= $aman($data['catatan']) ?></textarea>
                            <p class="ad-muted" id="ad-proof-note-help">Alasan wajib diisi untuk menolak bukti. Maksimal 500 karakter.</p>
                            <label class="ad-proof-check"><input type="checkbox" name="cocok" value="1"<?= $data['cocok'] ? ' checked' : '' ?>> <span>Saya sudah mencocokkan bukti dan total tagihan.</span></label>
                            <div class="ad-proof-actions"><button class="ad-button" type="submit" name="keputusan" value="tolak">Tolak Bukti</button><button class="ad-button" type="submit" name="keputusan" value="terima">Terima Pembayaran</button></div>
                            <?php if ($periksa['jenis'] === 'Booking' && $periksa['tanggal'] < $data['hari']): ?>
                                <p class="ad-muted ad-proof-help">Tanggal booking sudah lewat. Jika bukti ditolak, pesanan akan dibatalkan.</p>
                            <?php else: ?>
                                <p class="ad-muted ad-proof-help">Terima → pesanan Diproses. Tolak → Menunggu Pembayaran dengan batas unggah ulang baru.</p>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <p class="ad-info">Bukti ini sudah diperiksa. Status pesanan saat ini: <strong><?= $aman($periksa['status']) ?></strong>.</p>
                        <?php if (isset($periksa['pemeriksaan'])): ?><p class="ad-info"><strong><?= $periksa['pemeriksaan']['aksi'] === 'terima' ? 'Bukti diterima' : 'Bukti ditolak' ?></strong><br><?= $aman($periksa['pemeriksaan']['catatan']) ?></p><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="proof-bottom-actions">
                <a class="ad-button" href="<?= $aman($urlVerifikasi) ?>">Kembali ke daftar</a>
                <a class="ad-button" href="<?= $aman($urlDetailPesanan($periksa['nomor'])) ?>">Detail Pesanan</a>
            </div>
        <?php endif; ?>
    </section>
</main>
