<?php $kategori = $data['kategori']; ?>
<main class="ad-container ad-main" id="ad-main">
    <div class="ad-pesanan-intro"><p class="ad-eyebrow">ADMIN / PRODUK / KELOLA KATEGORI</p><h1>Kelola Kategori</h1><p>Kelompokkan produk agar mudah ditemukan pelanggan.</p></div>
    <?php if ($data['error'] !== null): ?><p class="ad-form-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($data['error']) ?></p><?php endif; ?>
    <?php if ($data['pesanKategori'] !== null): ?>
        <div class="ad-product-message ad-category-message" role="status" data-dismissible-notice>
            <p><?= $aman($data['pesanKategori']['teks']) ?></p>
            <?php if ($data['pesanKategori']['pulihkan'] !== null): ?>
                <form action="<?= BASE_URL ?>/index.php?page=admin-kategori-pulihkan&amp;id=<?= $aman(rawurlencode($data['pesanKategori']['pulihkan'])) ?>" method="post">
                    <input type="hidden" name="token_admin" value="<?= $aman($data['token']) ?>">
                    <button class="ad-button" type="submit">Batalkan Hapus</button>
                </form>
            <?php endif; ?>
            <button class="ad-category-message-close" type="button" data-dismiss-notice aria-label="Tutup pemberitahuan">×</button>
        </div>
    <?php endif; ?>
    <section class="ad-panel ad-category-panel" aria-labelledby="ad-category-title">
        <div class="ad-panel-heading"><div><h2 id="ad-category-title">Daftar Kategori</h2><p class="ad-muted"><?= count($kategori) ?> kategori</p></div><a class="ad-button ad-category-add" href="<?= BASE_URL ?>/index.php?page=admin-kategori-form">+ Tambah Kategori</a></div>
        <?php if ($kategori !== []): ?>
            <div class="ad-table-scroll" role="region" aria-label="Daftar kategori admin" tabindex="0">
                <table class="ad-table ad-category-admin-table">
                    <caption class="ad-sr-only">Kategori produk Ayu Cake</caption>
                    <thead><tr><th scope="col">Nama kategori</th><th scope="col">Deskripsi</th><th scope="col">Jumlah produk</th><th scope="col">Tindakan</th></tr></thead>
                    <tbody><?php foreach ($kategori as $item): ?><tr data-ad-category-row>
                        <th scope="row"><strong><?= $aman($item['nama']) ?></strong></th>
                        <td class="ad-category-description"><?= $aman($item['deskripsi']) ?></td>
                        <td><a class="ad-badge ad-category-count" href="<?= $aman($urlProduk) ?>&amp;kategori=<?= $aman(rawurlencode($item['id'])) ?>" aria-label="<?= $aman('Lihat ' . $item['jumlah'] . ' produk kategori ' . $item['nama']) ?>"><?= (int) $item['jumlah'] ?> produk</a></td>
                        <td><div class="ad-product-actions"><a class="ad-button" href="<?= BASE_URL ?>/index.php?page=admin-kategori-form&amp;id=<?= $aman(rawurlencode($item['id'])) ?>">Edit<span class="ad-sr-only"> <?= $aman($item['nama']) ?></span></a><a class="ad-button ad-product-delete" href="<?= BASE_URL ?>/index.php?page=admin-kategori-hapus&amp;id=<?= $aman(rawurlencode($item['id'])) ?>">Hapus<span class="ad-sr-only"> <?= $aman($item['nama']) ?></span></a></div></td>
                    </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        <?php else: ?><div class="ad-empty"><h3>Belum ada kategori</h3><p>Tambahkan kategori untuk mengelompokkan produk.</p><a class="ad-button" href="<?= BASE_URL ?>/index.php?page=admin-kategori-form">Tambah Kategori</a></div><?php endif; ?>
        <div class="ad-category-bottom"><p class="ad-muted" role="status">Menampilkan <?= count($kategori) ?> dari <?= count($kategori) ?> kategori</p></div>
        <p class="ad-category-tip">Jumlah produk mengikuti produk yang terhubung dengan masing-masing kategori.</p>
    </section>
</main>
