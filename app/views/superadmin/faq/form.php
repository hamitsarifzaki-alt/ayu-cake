<?php
require APP_PATH . '/views/superadmin/layouts/header.php';
$listUrl = BASE_URL . '/index.php?page=superadmin-faq';
$edit = $data['faqEdit'];
$f = $data['formFaq'];
$formUrl = $edit !== null ? $listUrl . '&edit=' . $edit['id'] : $listUrl . '&form=tambah';
?>
<main class="ad-container ad-main sa-main sa-standalone sa-form-screen" id="sa-main" data-module-screen="form" data-module-list-url="<?= $aman($listUrl) ?>">
    <a class="sa-back-link" href="<?= $aman($listUrl) ?>">← Kembali ke Daftar FAQ</a>
    <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / KELOLA FAQ</p><h1 tabindex="-1" data-sa-form-focus><?= $edit === null ? 'Tambah FAQ' : 'Edit FAQ' ?></h1><p>Susun pertanyaan dan jawaban yang akan ditampilkan kepada pelanggan.</p></div>
    <section class="sa-faq-form-card" id="sa-faq-form" aria-labelledby="sa-faq-form-title">
        <h2 id="sa-faq-form-title">Pertanyaan dan Jawaban</h2>
        <?php if ($data['errorFaq'] !== null): ?><div class="sa-faq-error" role="alert" tabindex="-1" data-dismissible-notice><p><?= $aman($data['errorFaq']) ?></p><?php if ($data['muatUlangFaq']): ?><a class="ad-button" href="<?= $aman($data['tampilFormFaq'] ? $formUrl : $listUrl) ?>">Muat Ulang FAQ</a><?php else: ?><a href="<?= $aman($listUrl) ?>">Kembali ke daftar</a><?php endif; ?></div><?php endif; ?>
        <form action="<?= $aman($formUrl) ?>" method="post" data-track-changes data-form-baseline="<?= htmlspecialchars(json_encode($edit ?? ['pertanyaan' => '', 'jawaban' => ''], JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token_superadmin" value="<?= $aman($data['token']) ?>"><input type="hidden" name="aksi" value="simpan"><input type="hidden" name="versi" value="<?= $aman($f['versi']) ?>">
            <label for="sa-faq-question">Pertanyaan<input type="text" id="sa-faq-question" name="pertanyaan" value="<?= $aman($f['pertanyaan']) ?>" placeholder="Masukkan pertanyaan umum" maxlength="300" required></label>
            <label for="sa-faq-answer">Jawaban<textarea id="sa-faq-answer" name="jawaban" placeholder="Tuliskan jawaban yang jelas dan mudah dipahami pelanggan." maxlength="4000" required><?= $aman($f['jawaban']) ?></textarea></label>
            <div class="sa-faq-form-actions"><a class="ad-button" data-cancel-url="<?= $aman($listUrl) ?>" data-cancel-changes role="link" aria-disabled="true" tabindex="-1">Batal</a><button class="ad-button" type="submit" data-save-changes data-save-locked="<?= $data['muatUlangFaq'] ? 'true' : 'false' ?>" disabled>Simpan FAQ</button></div>
        </form>
    </section>
</main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
