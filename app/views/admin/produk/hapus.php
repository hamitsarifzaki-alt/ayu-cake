<?php $produk = $data['produkEdit']; ?>
<main class="ad-container ad-main" id="ad-main">
    <div class="ad-pesanan-intro"><p class="ad-eyebrow">ADMIN / PRODUK / HAPUS PRODUK</p><h1>Hapus Produk</h1><p>Periksa produk yang akan dihapus dari daftar.</p></div>
    <section class="ad-panel ad-product-confirm" aria-labelledby="ad-delete-title">
        <h2 id="ad-delete-title">Hapus produk ini?</h2>
        <?php if ($data['error'] !== null): ?><p class="ad-form-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($data['error']) ?></p><?php endif; ?>
        <div class="ad-product-cell"><?php if ($fotoProduk($produk) !== null): ?><img src="<?= $aman($fotoProduk($produk)) ?>" alt="<?= $aman($produk['nama']) ?>" width="64" height="64"><?php else: ?><span class="ad-product-placeholder">Foto</span><?php endif; ?><div><strong><?= $aman($produk['nama']) ?></strong><p class="ad-muted"><?= $aman(sprintf('PRD-%03d', $produk['id'])) ?> • <?= $rupiah($produk['harga']) ?> / loyang</p></div></div>
        <p>Produk akan dikeluarkan dari daftar Kelola Produk. Catatan pesanan yang sudah ada tetap tersimpan.</p>
        <form class="ad-product-form-actions" action="<?= BASE_URL ?>/index.php?page=admin-produk-hapus&amp;id=<?= (int) $produk['id'] ?>" method="post"><input type="hidden" name="token_admin" value="<?= $aman($data['token']) ?>"><input type="hidden" name="versi" value="<?= (int) $produk['versi'] ?>"><a class="ad-button" href="<?= $aman($urlProduk) ?>">Batal</a><button class="ad-button ad-product-delete" type="submit">Ya, Hapus Produk</button></form>
    </section>
</main>
