<?php
$p = $data['detailPesanan'];

$form = $data['detailForm'];
$dapatEdit = $p !== null && !$data['detailMuatUlang'];
$dapatTolak = $dapatEdit && $data['dapatUbahOngkir'];
$jadwalLewat = $dapatTolak && $p['tanggal'] < $data['hari'];
$rekeningTidakTersedia = $dapatTolak && empty($p['rekening_tersedia']);
$dapatKirim = $dapatTolak && !$jadwalLewat && !$rekeningTidakTersedia;
$bukaPenolakan = $data['detailAksi'] === 'tolak-pengiriman';
$dapatSelesai = $dapatEdit && $data['dapatSelesai'];
$urlDetail = $p !== null ? $urlDetailPesanan($p['nomor']) : $urlPesanan;
$dibuat = $p['tanggal_dibuat'] ?? ($data['hari'] . ' 10:00:00');
$waktuTampil = static fn(string $waktu): string => $tanggal($waktu) . ', ' . str_replace(':', '.', substr($waktu, 11, 5)) . ' WIB';
$tokenForm = static function () use ($aman, $data, $p): void { ?>
    <input type="hidden" name="token_admin" value="<?= $aman($data['token']) ?>">
    <input type="hidden" name="versi" value="<?= (int) $p['versi'] ?>">
<?php };
?>
<main class="ad-container ad-main ad-order-main" id="ad-main">
    <div class="ad-order-intro">
        <p class="ad-eyebrow">ADMIN / PESANAN / DETAIL PESANAN</p>
        <h1>Detail Pesanan</h1>
        <p>Periksa informasi pelanggan, produk, pengiriman, dan perkembangan pesanan.</p>
    </div>
    <a class="ad-order-back" href="<?= $aman($urlPesanan) ?>">‹ Kembali ke daftar pesanan</a>
    <?php if ($data['pesanDetail'] !== null): ?><p class="ad-order-success" role="status" data-dismissible-notice><?= $aman($data['pesanDetail']) ?></p><?php endif; ?>
    <?php if ($data['errorDetail'] !== null): ?>
        <div class="ad-form-error ad-order-error" role="alert" tabindex="-1" data-dismissible-notice>
            <p><?= $aman($data['errorDetail']) ?></p>
            <?php if ($data['detailMuatUlang']): ?><a class="ad-button" href="<?= $aman($urlDetail) ?>">Muat Ulang Detail</a><?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($p === null): ?>
        <section class="ad-panel ad-order-panel"><div class="ad-empty"><h2>Pesanan tidak ditemukan</h2><p>Pilih pesanan yang tersedia dari daftar Pesanan.</p><a class="ad-button" href="<?= $aman($urlPesanan) ?>">Lihat Semua Pesanan</a></div></section>
    <?php else: ?>
        <section class="ad-panel ad-order-panel ad-order-information" aria-labelledby="ad-order-title">
            <div class="ad-order-heading"><h2 id="ad-order-title">Informasi Pesanan</h2><span class="ad-badge<?= $p['status'] === 'Selesai' ? ' ad-badge-green' : ($p['status'] === 'Dibatalkan' ? ' ad-badge-red' : '') ?>" data-order-status><?= $aman($p['status']) ?></span></div>
            <dl class="ad-order-meta">
                <div><dt>Nomor pesanan</dt><dd><?= $aman($p['nomor']) ?></dd></div>
                <div><dt>Jenis pesanan</dt><dd><?= $aman($p['jenis']) ?></dd></div>
                <div><dt>Tanggal dibuat</dt><dd><?= $aman($waktuTampil($dibuat)) ?></dd></div>
                <div><dt><?= $p['layanan'] === 'Ambil di Toko' ? 'Tanggal pengambilan' : 'Tanggal pengiriman' ?></dt><dd><?= $aman($tanggal($p['tanggal'])) ?></dd></div>
            </dl>
            <dl class="ad-order-meta ad-order-meta-recipient">
                <div><dt>Nama penerima</dt><dd><?= $aman($p['nama']) ?></dd></div>
                <div><dt>Nomor telepon</dt><dd><?= $aman($p['telepon']) ?></dd></div>
                <div><dt>Cara menerima pesanan</dt><dd><?= $p['layanan'] === 'Ambil di Toko' ? 'Ambil di toko' : 'Dikirim ke alamat' ?></dd></div>
                <div><dt>Metode pembayaran</dt><dd><?= $aman($p['pembayaran']) ?></dd></div>
                <?php if (($p['tujuan_bank'] ?? null) !== null): ?><div><dt>Nomor rekening tujuan</dt><dd><?= $aman($p['tujuan_bank']['nomor']) ?></dd></div><div><dt>Atas nama rekening</dt><dd><?= $aman($p['tujuan_bank']['atas_nama']) ?></dd></div><?php endif; ?>
            </dl>
        </section>

        <section class="ad-panel ad-order-panel ad-order-account" aria-labelledby="ad-order-account-title" data-order-account>
            <h2 id="ad-order-account-title">Pemilik Akun</h2>
            <p class="ad-muted ad-order-subtitle">Akun yang digunakan untuk membuat pesanan ini.<?= $p['pemilik_akun']['profil_admin'] ? ' Pesanan melalui profil belanja Admin.' : '' ?></p>
            <dl class="ad-order-meta">
                <div><dt>ID pelanggan</dt><dd><?= $p['pemilik_akun']['id'] !== null ? $aman(sprintf('PLG-%03d', $p['pemilik_akun']['id'])) : 'Belum tersedia' ?></dd></div>
                <div><dt>Nama akun</dt><dd><?= $aman($p['pemilik_akun']['nama'] !== '' ? $p['pemilik_akun']['nama'] : 'Belum diisi') ?></dd></div>
                <div><dt>Email akun</dt><dd><?= $aman($p['pemilik_akun']['email']) ?></dd></div>
                <div><dt>Telepon terdaftar</dt><dd><?= $aman($p['pemilik_akun']['telepon'] !== '' ? $p['pemilik_akun']['telepon'] : 'Belum diisi') ?></dd></div>
            </dl>
        </section>

        <section class="ad-panel ad-order-panel" aria-labelledby="ad-order-products">
            <h2 id="ad-order-products">Produk yang Dipesan</h2>
            <p class="ad-muted ad-order-subtitle"><?= count($p['items']) ?> jenis produk • Total <?= (int) array_sum(array_column($p['items'], 'jumlah')) ?> loyang</p>
            <div class="ad-table-scroll ad-order-products-scroll" role="region" aria-labelledby="ad-order-products" tabindex="0">
                <table class="ad-table ad-order-products-table">
                    <caption class="ad-sr-only">Rincian produk pesanan <?= $aman($p['nomor']) ?></caption>
                    <thead><tr><th scope="col">Produk</th><th scope="col">Harga / loyang</th><th scope="col">Jumlah</th><th scope="col">Subtotal</th></tr></thead>
                    <tbody><?php foreach ($p['items'] as $item): ?>
                        <tr><th scope="row"><div class="ad-order-product"><img src="<?= $aman(BASE_URL . '/public/assets/images/' . $item['gambar']) ?>" alt="<?= $aman($item['nama']) ?>" width="58" height="58"><div><strong><?= $aman($item['nama']) ?></strong><small>Satuan pemesanan: loyang</small></div></div></th><td><?= $rupiah($item['harga']) ?></td><td><?= (int) $item['jumlah'] ?> loyang</td><td><strong><?= $rupiah($item['subtotal']) ?></strong></td></tr>
                    <?php endforeach; ?></tbody>
                </table>
            </div>
        </section>

        <section class="ad-panel ad-order-panel" aria-labelledby="ad-order-shipping">
            <h2 id="ad-order-shipping">Pemeriksaan Alamat &amp; Ongkos Kirim</h2>
            <p class="ad-muted ad-order-subtitle">Periksa alamat penerima. Jika alamat jelas dan terjangkau, hitung ongkir Antar Ayu Cake dan kirim tagihannya.</p>
            <div class="ad-order-address"><span><?= $p['layanan'] === 'Ambil di Toko' ? 'Lokasi pengambilan' : 'Alamat penerima' ?></span><p><?= $p['layanan'] === 'Ambil di Toko' ? 'Toko Ayu Cake, Bukit Terap, Tukak Sadai, Bangka Selatan.' : $aman($p['alamat']) ?></p></div>
            <dl class="ad-order-delivery-details"><div><dt>Cara penerimaan pada pesanan</dt><dd><?= $aman($p['layanan_pelanggan'] ?? $p['layanan']) ?></dd></div><div><dt>Catatan pelanggan</dt><dd><?= $p['catatan_pelanggan'] !== '' ? $aman($p['catatan_pelanggan']) : 'Tidak ada catatan.' ?></dd></div></dl>
            <?php if ($dapatKirim): ?>
                <form class="ad-order-shipping-form" action="<?= $aman($urlDetail) ?>" method="post" data-order-shipping-form data-subtotal="<?= $aman($p['subtotal']) ?>">
                    <?php $tokenForm(); ?>
                    <input type="hidden" name="aksi" value="konfirmasi-ongkir">
                    <div class="ad-order-shipping-cost">
                        <label for="ad-order-shipping-cost">Ongkos kirim (Rp)</label>
                        <input id="ad-order-shipping-cost" name="ongkir" type="text" inputmode="numeric" pattern="[0-9]{1,14}" maxlength="14"
                               value="<?= $aman($form['ongkir']) ?>" aria-describedby="ad-order-shipping-help" required>
                        <p class="ad-muted ad-order-field-help" id="ad-order-shipping-help">Isi dalam rupiah bulat. Isi 0 jika pengiriman gratis.</p>
                    </div>
                    <label class="ad-order-confirm"><input type="checkbox" name="konfirmasi" value="1"<?= $data['detailAksi'] === 'konfirmasi-ongkir' && $form['konfirmasi'] ? ' checked' : '' ?> required> Saya sudah memeriksa bahwa alamat jelas dan berada dalam area layanan.</label>
                    <div class="ad-order-shipping-bottom">
                        <div><span>Subtotal produk</span><strong><?= $rupiah($p['subtotal']) ?></strong></div>
                        <div><span>Total setelah konfirmasi</span><strong class="ad-order-preview" data-order-total-preview aria-live="polite">Isi ongkir</strong></div>
                        <div class="ad-order-shipping-actions">
                            <button class="ad-button ad-order-reject" type="button" data-order-reject-toggle aria-controls="ad-order-rejection" aria-expanded="<?= $bukaPenolakan ? 'true' : 'false' ?>">Tolak Pesanan</button>
                            <button class="ad-button ad-order-primary" type="submit" data-order-shipping-submit>Konfirmasi &amp; Kirim Ongkir</button>
                        </div>
                    </div>
                </form>
            <?php elseif ($jadwalLewat): ?>
                <p class="ad-order-note">Tanggal pengiriman sudah lewat. Tolak pesanan dengan menyertakan alasan untuk pelanggan.</p>
            <?php elseif ($rekeningTidakTersedia): ?>
                <p class="ad-order-note">Transfer bank tidak tersedia untuk pesanan ini. Tolak pesanan dengan menyertakan alasan.</p>
            <?php else: ?>
                <dl class="ad-order-delivery-details ad-order-delivery-saved"><div><dt>Layanan pengiriman yang ditetapkan</dt><dd><?= $aman($p['layanan']) ?></dd></div><div><dt>Biaya ongkir</dt><dd><?= $p['ongkir'] === null ? 'Belum ditetapkan' : $rupiah($p['ongkir']) ?></dd></div><div><dt>Catatan untuk pelanggan</dt><dd><?= $p['catatan_ongkir'] !== '' ? nl2br($aman($p['catatan_ongkir'])) : 'Tidak ada catatan.' ?></dd></div></dl>
                <p class="ad-order-note"><?= $p['layanan'] === 'Ambil di Toko' ? 'Ambil di toko tidak dikenakan ongkir (Rp0).' : ($p['status'] === 'Menunggu Pembayaran' ? 'Ongkir sudah dikonfirmasi dan dikirim kepada pelanggan. Konfirmasi tidak dapat dikirim ulang.' : ($data['detailMuatUlang'] ? 'Muat ulang detail untuk melanjutkan pengaturan pengiriman.' : 'Pengiriman sudah terkunci pada status pesanan ini.')) ?></p>
            <?php endif; ?>
            <?php if ($dapatTolak): ?>
                <div class="ad-order-rejection" id="ad-order-rejection"<?= $dapatKirim && !$bukaPenolakan ? ' hidden' : '' ?>>
                    <form class="ad-order-shipping-form" action="<?= $aman($urlDetail) ?>" method="post">
                        <?php $tokenForm(); ?>
                        <input type="hidden" name="aksi" value="tolak-pengiriman">
                        <label for="ad-order-cancel-reason">Alasan penolakan yang akan dibaca pelanggan</label>
                        <input id="ad-order-cancel-reason" name="catatan_status" type="text" maxlength="500" value="<?= $aman($form['catatan_status']) ?>" placeholder="Contoh: Alamat kurang jelas atau berada di luar area layanan." required>
                        <label class="ad-order-confirm"><input type="checkbox" name="konfirmasi" value="1"<?= $data['detailAksi'] === 'tolak-pengiriman' && $form['konfirmasi'] ? ' checked' : '' ?> required> Saya mengonfirmasi penolakan pesanan ini.</label>
                        <button class="ad-button" type="submit">Kirim Penolakan</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>

        <section class="ad-panel ad-order-panel" aria-labelledby="ad-order-payment">
            <h2 id="ad-order-payment">Pembayaran</h2>
            <p class="ad-muted ad-order-subtitle">Pembayaran tersedia setelah pemeriksaan alamat dan konfirmasi ongkir selesai.</p>
            <dl class="ad-order-payment-details"><div><dt>Metode pembayaran</dt><dd><?= $aman($p['pembayaran']) ?></dd></div><div><dt>Total pembayaran saat ini</dt><dd data-order-total><?= $p['total'] === null ? 'Belum ditetapkan' : $rupiah($p['total']) ?></dd></div><div><dt>Bukti pembayaran</dt><dd><?= $p['bukti'] === null ? (in_array($p['status'], ['Diproses', 'Selesai'], true) ? 'Pembayaran terverifikasi' : 'Belum diunggah') : (isset($p['pemeriksaan']) ? ($p['pemeriksaan']['aksi'] === 'terima' ? 'Sudah diverifikasi' : 'Bukti ditolak') : 'Sudah diunggah') ?></dd></div></dl>
            <div class="ad-order-action-row"><p class="ad-order-note"><?php if (!empty($p['batas_pembayaran']) && $p['status'] === 'Menunggu Pembayaran'): ?>Batas pembayaran: <?= $aman($waktuTampil($p['batas_pembayaran'])) ?>.<?php elseif ($jadwalLewat): ?>Pembayaran sudah tidak dapat dibuka karena tanggal pengiriman lewat.<?php elseif ($dapatKirim): ?>Batas pembayaran dimulai setelah ongkir dikonfirmasi.<?php endif; ?></p><?php if ($p['bukti'] !== null): ?><a class="ad-button" href="<?= BASE_URL ?>/index.php?page=admin-periksa-pembayaran&amp;nomor=<?= $aman($p['nomor']) ?>">Verifikasi Pembayaran</a><?php else: ?><button class="ad-button" type="button" disabled>Verifikasi Pembayaran</button><?php endif; ?></div>
        </section>

        <section class="ad-panel ad-order-panel" aria-labelledby="ad-order-status-title">
            <h2 id="ad-order-status-title">Pembaruan Status Pesanan</h2>
            <p class="ad-muted ad-order-subtitle">Status proses dapat diperbarui setelah bukti pembayaran dinyatakan valid.</p>
            <?php if ($dapatSelesai): ?>
                <form class="ad-order-status-form" action="<?= $aman($urlDetail) ?>" method="post">
                    <?php $tokenForm(); ?><input type="hidden" name="aksi" value="status"><input type="hidden" name="catatan_status" value="Pesanan sudah diterima pelanggan."><input type="hidden" name="konfirmasi" value="1">
                    <div><label for="ad-order-status">Status saat ini</label><select id="ad-order-status" name="status" required><option value="" selected><?= $aman($p['status']) ?></option><option value="Selesai">Selesai</option></select></div><button class="ad-button" type="submit" data-order-status-submit>Perbarui Status</button>
                </form>
            <?php else: ?>
                <div class="ad-order-status-form"><div><label for="ad-order-status-readonly">Status saat ini</label><input id="ad-order-status-readonly" type="text" value="<?= $aman($p['status']) ?>" readonly></div><button class="ad-button" type="button" disabled>Perbarui Status</button></div>
            <?php endif; ?>
            <p class="ad-muted ad-order-field-help"><?php if ($p['status'] === 'Selesai'): ?>Pesanan selesai pada <?= $aman($tanggal($p['tanggal_selesai'])) ?>.<?php elseif ($p['status'] === 'Dibatalkan'): ?>Pesanan sudah dibatalkan.<?php elseif ($p['status'] === 'Diproses' && !$dapatSelesai): ?>Pesanan dapat ditandai selesai mulai tanggal ambil / kirim setelah diterima pelanggan.<?php else: ?>Setelah pembayaran valid: Diproses → Selesai. Admin memastikan pesanan telah diterima.<?php endif; ?></p>
        </section>

        <section class="ad-panel ad-order-panel" aria-labelledby="ad-order-history">
            <h2 id="ad-order-history">Riwayat Status</h2>
            <ol class="ad-order-history">
                <li><div><strong>Pesanan berhasil dibuat</strong><p>Pesanan tercatat dan menunggu tindak lanjut.</p></div><time datetime="<?= $aman(str_replace(' ', 'T', $dibuat) . '+07:00') ?>"><?= $aman($waktuTampil($dibuat)) ?></time></li>
                <?php foreach ($p['riwayat'] as $riwayat): ?><li><div><strong><?= $aman($riwayat['judul']) ?></strong><p><?= $aman($riwayat['status']) ?></p><?php if ($riwayat['catatan'] !== ''): ?><p><?= nl2br($aman($riwayat['catatan'])) ?></p><?php endif; ?></div><time datetime="<?= $aman(str_replace(' ', 'T', $riwayat['waktu']) . '+07:00') ?>"><?= $aman($waktuTampil($riwayat['waktu'])) ?></time></li><?php endforeach; ?>
            </ol>
        </section>
    <?php endif; ?>
</main>
