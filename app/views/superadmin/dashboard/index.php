<?php require APP_PATH . '/views/superadmin/layouts/header.php'; ?>
    <main class="ad-container ad-main sa-main" id="sa-main">
        <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / DASHBOARD</p><h1>Dashboard Super Admin</h1><p>Pantau akun dan penjualan Ayu Cake.</p></div>
        <section class="ad-panel sa-panel" aria-labelledby="sa-summary-title">
            <div class="sa-section-heading"><div><h2 id="sa-summary-title">Ringkasan Toko</h2><p class="ad-muted">Informasi akun saat ini dan penjualan bulan berjalan.</p></div><span class="ad-muted sa-month"><?= $aman($periode) ?></span></div>
            <div class="sa-stats">
                <div class="sa-stat"><p>Admin aktif</p><strong><?= (int) $data['adminAktif'] ?> akun</strong><small>Admin yang dapat mengelola toko</small></div>
                <div class="sa-stat"><p>Pelanggan terdaftar</p><strong><?= count($data['pelanggan']) ?> akun</strong><small>Total akun pelanggan</small></div>
                <div class="sa-stat"><p>Pesanan selesai</p><strong><?= (int) $total['pesanan'] ?> pesanan</strong><small>Bulan <?= $aman($periode) ?></small></div>
                <div class="sa-stat"><p>Penjualan produk</p><strong><?= $rupiah($total['produk']) ?></strong><small>Bulan ini • tidak termasuk ongkir</small></div>
            </div>
            <div class="sa-account-heading"><h2>Pengelolaan Akun</h2><p class="ad-muted">Buka halaman untuk mengelola akun dan status pengguna.</p></div>
            <div class="sa-account-cards"><div><h3>Akun Admin</h3><p class="ad-muted">Tambah, edit, atau hapus akun admin.</p><a class="ad-button sa-primary" href="<?= BASE_URL ?>/index.php?page=superadmin-akun-admin">Kelola Akun Admin</a></div><div><h3>Akun Pelanggan</h3><p class="ad-muted">Lihat data, ubah kata sandi, atau hapus akun pelanggan.</p><a class="ad-button sa-primary" href="<?= BASE_URL ?>/index.php?page=superadmin-akun-pelanggan">Kelola Akun Pelanggan</a></div></div>
        </section>
    </main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
