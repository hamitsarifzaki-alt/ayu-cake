<?php $kategori = $data['kategoriEdit']; $terhubung = $data['produkTerhubung']; ?>
<main class="ad-container ad-main" id="ad-main">
    <div class="ad-pesanan-intro"><p class="ad-eyebrow">ADMIN / PRODUK / HAPUS KATEGORI</p><h1>Hapus Kategori</h1><p>Periksa kategori dan produk yang masih terhubung sebelum menghapus.</p></div>
    <section class="ad-panel ad-product-confirm ad-category-delete-panel" aria-labelledby="ad-category-delete-title">
        <h2 id="ad-category-delete-title"><?= $terhubung === [] ? 'Hapus kategori ini?' : 'Kategori masih digunakan' ?></h2>
        <?php if ($data['error'] !== null): ?><p class="ad-form-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($data['error']) ?></p><?php endif; ?>
        <div class="ad-category-delete-summary"><strong><?= $aman($kategori['nama']) ?></strong><p class="ad-muted"><?= $aman($kategori['deskripsi']) ?></p><span class="ad-badge"><?= count($terhubung) ?> produk</span></div>
        <?php if ($terhubung !== []): ?>
            <p>Pindahkan produk berikut ke kategori lain sebelum menghapus kategori ini.</p>
            <ul class="ad-category-linked-products"><?php foreach ($terhubung as $produk): ?><li><strong><?= $aman($produk['nama']) ?></strong><a class="ad-button" href="<?= BASE_URL ?>/index.php?page=admin-produk-form&amp;id=<?= (int) $produk['id'] ?>">Edit Produk<span class="ad-sr-only"> <?= $aman($produk['nama']) ?></span></a></li><?php endforeach; ?></ul>
            <div class="ad-product-form-actions"><a class="ad-button" href="<?= $aman($urlKategori) ?>">Kembali ke Kategori</a><a class="ad-button" href="<?= $aman($urlProduk) ?>&amp;kategori=<?= $aman(rawurlencode($kategori['id'])) ?>">Lihat Produk</a></div>
        <?php else: ?>
            <p>Kategori akan dikeluarkan dari daftar. Tidak ada produk yang dihapus.</p>
            <form class="ad-product-form-actions" action="<?= BASE_URL ?>/index.php?page=admin-kategori-hapus&amp;id=<?= $aman(rawurlencode($kategori['id'])) ?>" method="post"><input type="hidden" name="token_admin" value="<?= $aman($data['token']) ?>"><input type="hidden" name="versi" value="<?= (int) $kategori['versi'] ?>"><a class="ad-button" href="<?= $aman($urlKategori) ?>">Batal</a><button class="ad-button ad-product-delete" type="submit">Ya, Hapus Kategori</button></form>
        <?php endif; ?>
    </section>
</main>
