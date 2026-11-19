<?php $form = $data['form']; $edit = $data['kategoriEdit']; ?>
<main class="ad-container ad-main" id="ad-main">
    <div class="ad-pesanan-intro"><p class="ad-eyebrow">ADMIN / PRODUK / <?= $edit !== null ? 'EDIT' : 'TAMBAH' ?> KATEGORI</p><h1><?= $edit !== null ? 'Edit Kategori' : 'Tambah Kategori' ?></h1><p>Atur nama dan deskripsi untuk mengelompokkan produk.</p></div>
    <a class="ad-product-back" href="<?= $aman($urlKategori) ?>">← Kembali ke Kelola Kategori</a>
    <section class="ad-panel ad-category-form-panel" aria-labelledby="ad-category-form-title">
        <h2 id="ad-category-form-title">Informasi kategori</h2>
        <?php if ($data['error'] !== null): ?><p class="ad-form-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($data['error']) ?></p><?php endif; ?>
        <form action="<?= BASE_URL ?>/index.php?page=admin-kategori-form<?= $edit !== null ? '&amp;id=' . $aman(rawurlencode($edit['id'])) : '' ?>" method="post" data-track-changes data-form-baseline="<?= htmlspecialchars(json_encode($edit ?? ['nama' => '', 'deskripsi' => ''], JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token_admin" value="<?= $aman($data['token']) ?>"><input type="hidden" name="versi" value="<?= $aman($form['versi']) ?>">
            <div class="ad-product-fields ad-category-fields"><label>Nama kategori<input type="text" name="nama" value="<?= $aman($form['nama']) ?>" maxlength="80" required placeholder="Contoh: Kue Tradisional"></label><label>Deskripsi kategori<textarea name="deskripsi" rows="4" maxlength="500" required placeholder="Jelaskan jenis produk dalam kategori ini."><?= $aman($form['deskripsi']) ?></textarea></label></div>
            <p class="ad-muted">Deskripsi menjelaskan jenis produk dalam kategori ini. Pelanggan melihatnya di halaman Katalog saat memilih kategori tersebut.</p>
            <?php if ($edit !== null): ?><p class="ad-category-note"><?= count($data['produkTerhubung']) ?> produk terhubung. Mengubah nama kategori tetap mempertahankan hubungan produk.</p><?php endif; ?>
            <div class="ad-product-form-actions"><a class="ad-button" data-cancel-url="<?= $aman($urlKategori) ?>" data-cancel-changes role="link" aria-disabled="true" tabindex="-1">Batal</a><button class="ad-button" type="submit" data-save-changes disabled>Simpan Kategori</button></div>
        </form>
    </section>
</main>
