<?php require APP_PATH . '/views/superadmin/layouts/header.php';
$listUrl = BASE_URL . '/index.php?page=superadmin-akun-pelanggan';
$f = $data['filterPelanggan'];
$tautanHalaman = static fn(int $halaman): string => $listUrl . '&' . http_build_query(['cari' => $f['cari'], 'halaman' => $halaman]);
$pageUrl = $tautanHalaman($data['nomorHalaman']);
$detail = $data['detailPelanggan'];
$actionUrl = $pageUrl . ($detail !== null ? '&detail=' . $detail['id'] : '');
?>
<main class="ad-container ad-main sa-main sa-standalone sa-detail-screen" id="sa-main" data-module-screen="detail" data-module-list-url="<?= $aman($pageUrl) ?>">
    <a class="sa-back-link" href="<?= $aman($pageUrl) ?>">← Kembali ke Daftar Pelanggan</a>
    <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / KELOLA AKUN / AKUN PELANGGAN</p><h1 tabindex="-1" data-sa-form-focus>Detail Pelanggan</h1><p>Informasi akun pelanggan yang dipilih.</p></div>
    <?php if ($data['pesanPelanggan'] !== null): ?><p class="sa-customer-message" role="status" data-dismissible-notice><?= $aman($data['pesanPelanggan']) ?></p><?php endif; ?>
    <?php if ($data['errorPelanggan'] !== null): ?><div class="sa-customer-error" role="alert" tabindex="-1" data-dismissible-notice><p><?= $aman($data['errorPelanggan']) ?></p><?php if ($data['muatUlangPelanggan']): ?><a class="ad-button" href="<?= $aman($actionUrl) ?>">Muat Ulang Detail</a><?php endif; ?></div><?php endif; ?>
    <section class="sa-customer-detail" id="sa-customer-detail" aria-labelledby="sa-customer-detail-title">
        <header><h2 id="sa-customer-detail-title"><?= $aman($detail['nama']) ?></h2></header>
        <dl>
            <div><dt>ID pelanggan</dt><dd><strong><?= $aman(sprintf('PLG-%03d', $detail['id'])) ?></strong></dd></div>
            <div><dt>Email</dt><dd><?= $aman($detail['email']) ?></dd></div>
            <?php if ($detail['telepon'] !== ''): ?>
                <div><dt>Telepon saat pendaftaran</dt><dd><?= $aman($detail['telepon']) ?></dd></div>
            <?php endif; ?>
        </dl>
        <footer><a class="ad-button" href="<?= $aman($pageUrl) ?>">Kembali ke Daftar Pelanggan</a></footer>
    </section>
    <?php if ($data['dapatUbahSandi']): ?>
        <section class="sa-admin-form-card" aria-labelledby="sa-customer-password-title">
            <h2 id="sa-customer-password-title">Ubah Kata Sandi Pelanggan</h2>
            <p class="ad-muted sa-admin-password-help">Pastikan permintaan berasal dari pemilik akun. Penggantian kata sandi mengakhiri sesi masuk pelanggan.</p>
            <form action="<?= $aman($actionUrl) ?>" method="post" data-track-changes data-form-baseline='{"password":"","konfirmasi_password":""}'>
                <input type="hidden" name="token_superadmin" value="<?= $aman($data['token']) ?>">
                <input type="hidden" name="id" value="<?= (int) $detail['id'] ?>">
                <input type="hidden" name="versi" value="<?= (int) $detail['versi'] ?>">
                <input type="hidden" name="aksi" value="ubah-sandi">
                <div class="sa-admin-fields">
                    <label for="sa-customer-password">Kata sandi baru
                        <input id="sa-customer-password" name="password" type="password" required minlength="8" maxlength="72" autocomplete="new-password">
                    </label>
                    <label for="sa-customer-confirmation">Konfirmasi kata sandi baru
                        <input id="sa-customer-confirmation" name="konfirmasi_password" type="password" required minlength="8" maxlength="72" autocomplete="new-password">
                    </label>
                </div>
                <div class="sa-admin-form-actions">
                    <button class="ad-button sa-primary" type="submit" data-save-changes data-save-locked="<?= $data['muatUlangPelanggan'] ? 'true' : 'false' ?>" disabled>Simpan Kata Sandi</button>
                </div>
            </form>
        </section>
    <?php endif; ?>
</main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
