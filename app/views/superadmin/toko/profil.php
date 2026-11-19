<?php require APP_PATH . '/views/superadmin/layouts/header.php';
$f = $data['formToko'];
$storeUrl = BASE_URL . '/index.php?page=superadmin-profil-toko';
$layananAwal = '';
foreach ($f['bank'] as $kode => $akun) if ($akun['nomor'] !== '' || $akun['atas_nama'] !== '') { $layananAwal = $kode; break; }
$input = static function (string $key, string $label, string $placeholder, int $max) use ($f, $aman): void { ?>
    <label for="sa-store-<?= $key ?>"><?= $label ?><input id="sa-store-<?= $key ?>" name="<?= $key ?>" type="text" value="<?= $aman($f[$key]) ?>" placeholder="<?= $aman($placeholder) ?>" maxlength="<?= $max ?>" required></label>
<?php }; ?>
<main class="ad-container ad-main sa-main" id="sa-main">
    <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / PROFIL TOKO</p><h1>Profil Toko</h1><p>Kelola informasi Ayu Cake yang ditampilkan pada website.</p></div>
    <?php if ($data['pesanToko'] !== null): ?><p class="sa-store-message" role="status" data-dismissible-notice><?= $aman($data['pesanToko']) ?></p><?php endif; ?>
    <form id="sa-store-form" class="ad-panel sa-store-panel" data-store-locked="<?= $data['muatUlangToko'] ? 'true' : 'false' ?>" action="<?= $aman($storeUrl) ?>" method="post" enctype="multipart/form-data">
        <p class="ad-muted sa-store-required-note"><span aria-hidden="true">*</span> Kolom wajib diisi.</p>
        <input type="hidden" name="token_superadmin" value="<?= $aman($data['token']) ?>"><input type="hidden" name="versi" value="<?= $aman($f['versi']) ?>">
        <?php if ($data['errorToko'] !== null): ?><div class="sa-store-error" role="alert" tabindex="-1" data-dismissible-notice><p><?= $aman($data['errorToko']) ?></p><?php if ($data['gambarPilihUlang']): ?><p>Pilih kembali gambar logo sebelum menyimpan ulang.</p><?php endif; ?><?php if ($data['muatUlangToko']): ?><a class="ad-button" href="<?= $aman($storeUrl) ?>">Muat Ulang Profil Toko</a><?php endif; ?></div><?php endif; ?>
        <section class="sa-store-card" aria-labelledby="sa-store-info-title">
            <header><h2 id="sa-store-info-title">Informasi Toko</h2><p class="ad-muted">Perbarui logo, nama, slogan, dan deskripsi toko.</p></header>
            <div class="sa-store-brand-fields">
                <div class="sa-store-logo-upload">
                    <div class="sa-store-logo-preview"><img id="sa-store-logo-preview" src="<?= $aman($f['logo'] !== '' ? $f['logo'] : BASE_URL . '/public/assets/images/LogoAyuCake.png') ?>" alt="Logo toko saat ini"></div>
                    <label class="ad-button sa-store-file-button" for="sa-store-logo">Ganti Logo<input class="sa-store-file" type="file" id="sa-store-logo" name="logo" accept="image/png,image/jpeg" data-store-upload="sa-store-logo-preview" aria-describedby="sa-store-logo-help"></label>
                    <p class="ad-muted" id="sa-store-logo-help">PNG atau JPG, maks. 2 MB</p><p class="sa-store-file-status ad-muted" data-store-status="sa-store-logo-preview" role="status"></p>
                </div>
                <div class="sa-store-fields">
                    <?php $input('nama', 'Nama toko', 'Ayu Cake', 120); $input('slogan', 'Slogan', 'Kue untuk setiap cerita', 160); ?>
                    <label class="sa-store-wide" for="sa-store-description">Deskripsi toko<textarea id="sa-store-description" name="deskripsi" maxlength="2000" required><?= $aman($f['deskripsi']) ?></textarea></label>
                </div>
            </div>
            <label class="sa-store-address" for="sa-store-address">Alamat toko / lokasi pengambilan<textarea id="sa-store-address" name="alamat" maxlength="1000" required><?= $aman($f['alamat']) ?></textarea></label>
        </section>
        <section class="sa-store-card" aria-labelledby="sa-store-contact-title">
            <header><h2 id="sa-store-contact-title">Kontak dan Jam Operasional</h2><p class="ad-muted">Informasi ini digunakan pada halaman kontak dan footer website.</p></header>
            <div class="sa-store-fields">
                <?php $input('whatsapp', 'Nomor WhatsApp', 'Isi nomor WhatsApp toko', 25); $input('instagram', 'Instagram', 'Isi nama akun Instagram toko', 31);
                $input('hari_operasional', 'Hari operasional', 'Isi hari operasional toko', 120); $input('jam_operasional', 'Jam operasional', 'Isi jam buka dan tutup toko', 80); ?>
            </div>
        </section>
        <section class="sa-store-card" aria-labelledby="sa-store-payment-title">
            <header><h2 id="sa-store-payment-title">Informasi Pembayaran</h2><p class="ad-muted">Kelola rekening BRI dan SeaBank yang ditampilkan kepada pelanggan.</p></header>
            <p class="ad-muted sa-store-wallet-help">Pilih bank untuk mengatur nomor rekening dan nama pemiliknya. Isian tiap bank tetap tersimpan saat berpindah pilihan. Kosongkan kedua isian jika rekening bank tidak digunakan.</p>
            <label class="sa-store-wallet-picker" for="sa-store-wallet-select">Transfer bank
                <select id="sa-store-wallet-select" data-store-wallet-select>
                    <option value="">Pilih bank</option>
                    <?php foreach ($data['bankPilihan'] as $kode => $nama): ?><option value="<?= $aman($kode) ?>"<?= $layananAwal === $kode ? ' selected' : '' ?>><?= $aman($nama) ?></option><?php endforeach; ?>
                </select>
            </label>
            <div class="sa-store-wallets">
                <?php foreach ($data['bankPilihan'] as $kode => $nama): $akun = $f['bank'][$kode]; ?>
                    <fieldset class="sa-store-wallet" data-store-wallet="<?= $aman($kode) ?>"<?= $kode !== $layananAwal ? ' hidden' : '' ?>>
                        <legend><?= $aman($nama) ?></legend>
                        <div class="sa-store-fields">
                            <label for="sa-store-<?= $kode ?>-nomor">Nomor rekening <?= $aman($nama) ?><input id="sa-store-<?= $kode ?>-nomor" name="bank[<?= $kode ?>][nomor]" type="text" inputmode="numeric" pattern="[0-9]{6,25}" value="<?= $aman($akun['nomor']) ?>" maxlength="25" placeholder="Nomor rekening <?= $aman($nama) ?>" data-wallet-number data-optional></label>
                            <label for="sa-store-<?= $kode ?>-pemilik">Nama pemilik rekening <?= $aman($nama) ?><input id="sa-store-<?= $kode ?>-pemilik" name="bank[<?= $kode ?>][atas_nama]" type="text" value="<?= $aman($akun['atas_nama']) ?>" maxlength="120" placeholder="Nama penerima pembayaran" data-wallet-owner data-optional></label>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
            </div>
            <p class="ad-muted sa-store-payment-note">Pelanggan mengunggah bukti pembayaran untuk diperiksa oleh admin.</p>
        </section>
        <footer class="sa-store-actions"><p class="ad-muted">Simpan setelah selesai memperbarui informasi toko.</p><div><button class="ad-button" type="button" data-store-cancel disabled>Batal</button><button class="ad-button" type="submit" data-store-save disabled>Simpan Perubahan</button></div></footer>
        
    </form>
    <script type="application/json" id="sa-store-initial"><?= json_encode($data['tokoAwal'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
</main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
