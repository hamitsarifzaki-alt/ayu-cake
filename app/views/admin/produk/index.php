<?php
$daftar = $data['daftarProduk'];
$halamanProduk = static fn(int $halaman): string => BASE_URL . '/index.php?' . http_build_query([
    'page' => 'admin-produk', 'cari' => $daftar['cari'], 'kategori' => $daftar['kategori'], 'status' => $daftar['status'], 'halaman' => $halaman,
]);
?>
<main class="ad-container ad-main" id="ad-main">
    <div class="ad-pesanan-intro"><p class="ad-eyebrow">ADMIN / PRODUK / KELOLA PRODUK</p><h1>Kelola Produk</h1><p>Atur informasi kue, harga, dan stok ready stock.</p></div>
    <?php if ($data['error'] !== null): ?><p class="ad-form-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($data['error']) ?></p><?php endif; ?>
    <?php if ($data['pesanProduk'] !== null): ?>
        <div class="ad-product-message" role="status" data-dismissible-notice><p><?= $aman($data['pesanProduk']['teks']) ?></p>
        <?php if ($data['pesanProduk']['pulihkan'] !== null): ?><form action="<?= BASE_URL ?>/index.php?page=admin-produk-pulihkan&amp;id=<?= (int) $data['pesanProduk']['pulihkan'] ?>" method="post"><input type="hidden" name="token_admin" value="<?= $aman($data['token']) ?>"><button class="ad-button" type="submit">Batalkan Hapus</button></form><?php endif; ?></div>
    <?php endif; ?>
    <section class="ad-panel ad-pesanan-panel ad-products-panel" aria-labelledby="ad-products-title">
        <div class="ad-panel-heading"><div><h2 id="ad-products-title">Daftar Produk</h2><p class="ad-muted"><?= (int) $daftar['total'] ?> produk</p></div><a class="ad-button ad-all-orders" href="<?= BASE_URL ?>/index.php?page=admin-produk-form">+ Tambah Produk</a></div>
        <form class="ad-list-filters ad-products-filters" action="<?= BASE_URL ?>/index.php" method="get" role="search" aria-label="Filter produk">
            <input type="hidden" name="page" value="admin-produk">
            <label>Cari produk<input type="search" name="cari" value="<?= $aman($daftar['cari']) ?>" placeholder="Nama produk" maxlength="120"></label>
            <label>Kategori<select name="kategori"><option value="semua">Semua kategori</option><?php foreach ($data['kategoriProduk'] as $key => $label): ?><option value="<?= $aman($key) ?>"<?= $daftar['kategori'] === $key ? ' selected' : '' ?>><?= $aman($label) ?></option><?php endforeach; ?></select></label>
            <label>Ketersediaan<select name="status"><option value="semua">Semua status</option><option value="tersedia"<?= $daftar['status'] === 'tersedia' ? ' selected' : '' ?>>Tersedia</option><option value="tidak-tersedia"<?= $daftar['status'] === 'tidak-tersedia' ? ' selected' : '' ?>>Tidak Tersedia</option></select></label>
            <button class="ad-button" type="submit">Tampilkan</button>
        </form>
        <?php if ($daftar['cari'] !== '' || $daftar['kategori'] !== 'semua' || $daftar['status'] !== 'semua'): ?><p class="ad-products-reset"><a class="ad-text-link" href="<?= $aman($urlProduk) ?>">Reset filter</a></p><?php endif; ?>
        <?php if ($daftar['jumlah'] > 0): ?>
            <div class="ad-table-scroll" role="region" aria-label="Daftar produk admin" tabindex="0">
                <table class="ad-table ad-product-admin-table">
                    <caption class="ad-sr-only">Produk Ayu Cake, halaman <?= (int) $daftar['halaman'] ?> dari <?= (int) $daftar['totalHalaman'] ?></caption>
                    <thead><tr><th scope="col">Produk</th><th scope="col">Kategori</th><th scope="col">Harga / loyang</th><th scope="col">Stok ready stock</th><th scope="col">Ketersediaan</th><th scope="col">Tindakan</th></tr></thead>
                    <tbody><?php foreach ($daftar['items'] as $produk): ?>
                        <tr data-ad-product-row>
                            <th scope="row"><div class="ad-product-cell"><?php if ($fotoProduk($produk) !== null): ?><img src="<?= $aman($fotoProduk($produk)) ?>" alt="<?= $aman($produk['nama']) ?>" width="64" height="64"><?php else: ?><span class="ad-product-placeholder" aria-label="Belum ada foto">Foto</span><?php endif; ?><div><strong><?= $aman($produk['nama']) ?></strong><small><?= $aman(sprintf('PRD-%03d', $produk['id'])) ?> • <?= $produk['jenis'] === 'Pre-Order' ? 'Booking' : 'Ready Stock' ?></small></div></div></th>
                            <td><?= $aman(($data['kategoriProduk'][$produk['kategori']] ?? 'Kategori tidak tersedia')) ?></td><td><strong><?= $rupiah($produk['harga']) ?></strong></td>
                            <td><?= $produk['jenis'] === 'Pre-Order' ? 'Kuota Booking' : (int) $produk['stok'] . ' loyang' ?></td>
                            <td><span class="ad-badge<?= ($produk['aktif'] && ($produk['jenis'] === 'Pre-Order' || $produk['stok'] > 0)) ? ' ad-badge-green' : '' ?>"><?= ($produk['aktif'] && ($produk['jenis'] === 'Pre-Order' || $produk['stok'] > 0)) ? 'Tersedia' : 'Tidak Tersedia' ?></span></td>
                            <td><div class="ad-product-actions"><a class="ad-button" href="<?= BASE_URL ?>/index.php?page=admin-produk-form&amp;id=<?= (int) $produk['id'] ?>">Edit<span class="ad-sr-only"> <?= $aman($produk['nama']) ?></span></a><a class="ad-button ad-product-delete" href="<?= BASE_URL ?>/index.php?page=admin-produk-hapus&amp;id=<?= (int) $produk['id'] ?>">Hapus<span class="ad-sr-only"> <?= $aman($produk['nama']) ?></span></a></div></td>
                        </tr>
                    <?php endforeach; ?></tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="ad-empty"><h3><?= $daftar['total'] === 0 ? 'Belum ada produk' : 'Produk tidak ditemukan' ?></h3><p><?= $daftar['total'] === 0 ? 'Tambahkan produk pertama untuk mulai mengelola katalog admin.' : 'Coba nama produk lain atau ubah filter yang dipilih.' ?></p><a class="ad-button" href="<?= $aman($daftar['total'] === 0 ? BASE_URL . '/index.php?page=admin-produk-form' : $urlProduk) ?>"><?= $daftar['total'] === 0 ? 'Tambah Produk' : 'Tampilkan semua produk' ?></a></div>
        <?php endif; ?>
        <div class="ad-list-bottom"><p class="ad-muted" role="status">Menampilkan <?= count($daftar['items']) ?> dari <?= (int) $daftar['jumlah'] ?> produk</p>
            <?php if ($daftar['jumlah'] > 0): ?><nav class="ad-pagination" aria-label="Halaman daftar produk"><?php for ($i = 1; $i <= $daftar['totalHalaman']; $i++): ?><?php if ($i === $daftar['halaman']): ?><span aria-current="page" aria-label="Halaman <?= $i ?>"><?= $i ?></span><?php else: ?><a class="ad-button" href="<?= $aman($halamanProduk($i)) ?>" aria-label="Halaman <?= $i ?>"><?= $i ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; ?>
        </div>
    </section>
</main>
