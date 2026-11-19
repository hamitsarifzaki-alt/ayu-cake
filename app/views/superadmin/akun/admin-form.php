<?php
require APP_PATH . '/views/superadmin/layouts/header.php';
$listUrl = BASE_URL . '/index.php?page=superadmin-akun-admin';
$edit = $data['editAdmin'];
$form = $data['formAdmin'];
$formUrl = $edit !== null ? $listUrl . '&edit=' . $edit['id'] : $listUrl . '&form=tambah';
?>
<main class="ad-container ad-main sa-main sa-standalone sa-form-screen" id="sa-main" data-module-screen="form" data-module-list-url="<?= $aman($listUrl) ?>">
    <a class="sa-back-link" href="<?= $aman($listUrl) ?>">← Kembali ke Daftar Admin</a>
    <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / KELOLA AKUN / AKUN ADMIN</p><h1 tabindex="-1" data-sa-form-focus><?= $edit === null ? 'Tambah Admin' : 'Edit Admin' ?></h1><p>Isi identitas dan akses akun admin yang mengelola toko.</p></div>
    <section class="sa-admin-form-card" id="sa-admin-form" aria-labelledby="sa-admin-form-title">
        <h2 id="sa-admin-form-title">Data Admin</h2>
        <?php if ($data['errorAdmin'] !== null): ?><div class="sa-admin-error" role="alert" tabindex="-1" data-dismissible-notice><p><?= $aman($data['errorAdmin']) ?></p><?php if ($data['muatUlangAdmin']): ?><a class="ad-button" href="<?= $aman($data['tampilForm'] ? $formUrl : $listUrl) ?>">Muat Ulang Form</a><?php endif; ?></div><?php endif; ?>
        <form action="<?= $aman($formUrl) ?>" method="post" data-track-changes data-form-baseline="<?= htmlspecialchars(json_encode(['nama' => $edit['nama'] ?? '', 'email' => $edit['email'] ?? '', 'password' => '', 'konfirmasi' => ''], JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token_superadmin" value="<?= $aman($data['token']) ?>"><input type="hidden" name="aksi" value="simpan"><input type="hidden" name="versi" value="<?= $aman($form['versi']) ?>">
            <div class="sa-admin-fields">
                <label for="sa-admin-name">Nama admin<input type="text" id="sa-admin-name" name="nama" value="<?= $aman($form['nama']) ?>" maxlength="120" placeholder="Masukkan nama admin" required autocomplete="name"></label>
                <label for="sa-admin-email">Email<input type="email" id="sa-admin-email" name="email" value="<?= $aman($form['email']) ?>" maxlength="254" placeholder="Masukkan email admin" required autocomplete="off" autocapitalize="none" spellcheck="false"></label>
                <?php foreach (['password' => ['sa-admin-password', 'Kata sandi', 'Kata sandi baru'], 'konfirmasi' => ['sa-admin-confirmation', 'Konfirmasi kata sandi', 'Konfirmasi kata sandi baru']] as $field => [$id, $labelTambah, $labelEdit]): $label = $edit === null ? $labelTambah : $labelEdit; ?>
                    <div class="sa-admin-password-field">
                        <label for="<?= $id ?>"><?= $aman($label) ?></label>
                        <div class="sa-admin-password-wrap">
                            <input type="password" id="<?= $id ?>" name="<?= $field ?>" minlength="8" maxlength="72" placeholder="<?= $edit === null ? ($field === 'password' ? 'Masukkan kata sandi' : 'Ulangi kata sandi') : 'Kosongkan jika tidak diubah' ?>" autocomplete="new-password"<?= $edit === null ? ' required' : '' ?> aria-describedby="sa-admin-password-help">
                            <button class="sa-admin-password-toggle" type="button" data-sa-password-toggle data-password-name="<?= $aman(strtolower($label)) ?>" aria-controls="<?= $id ?>" aria-label="Tampilkan <?= $aman(strtolower($label)) ?>" aria-pressed="false" hidden>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle><path data-sa-eye-slash d="M3 3 21 21" hidden></path></svg>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="sa-admin-form-actions"><a class="ad-button" data-cancel-url="<?= $aman($listUrl) ?>" data-cancel-changes role="link" aria-disabled="true" tabindex="-1">Batal</a><button class="ad-button sa-primary" type="submit" data-save-changes data-save-locked="<?= $data['muatUlangAdmin'] ? 'true' : 'false' ?>" disabled>Simpan Admin</button></div>
        </form>
        <p class="ad-muted sa-admin-password-help" id="sa-admin-password-help"><?= $edit === null ? 'Gunakan kata sandi 8–72 karakter dan ulangi pada kolom konfirmasi.' : 'Kata sandi saat ini tetap digunakan jika kedua kolom dikosongkan. Untuk menggantinya, isi kata sandi baru 8–72 karakter dan ulangi pada kolom konfirmasi.' ?></p>
    </section>
</main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
