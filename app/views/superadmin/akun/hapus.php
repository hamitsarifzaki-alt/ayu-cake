<?php
require APP_PATH . '/views/superadmin/layouts/header.php';
$pelanggan = $data['halaman'] === 'akun-pelanggan';
$peran = $pelanggan ? 'Pelanggan' : 'Admin';
$listUrl = BASE_URL . '/index.php?page=superadmin-' . $data['halaman'];
if ($pelanggan) {
    $filter = $data['filterPelanggan'];
    $listUrl .= '&' . http_build_query(['cari' => $filter['cari'], 'halaman' => $filter['halaman']]);
}
$target = $pelanggan ? $data['hapusPelanggan'] : $data['hapusAdmin'];
$error = $pelanggan ? $data['errorPelanggan'] : $data['errorAdmin'];
$muatUlang = $pelanggan ? $data['muatUlangPelanggan'] : $data['muatUlangAdmin'];
$pesananAktif = $data['pesananAktif'] ?? [];
$hapusUrl = $listUrl . '&hapus=' . (int) $target['id'];
?>
<main class="ad-container ad-main sa-main sa-standalone sa-form-screen" id="sa-main" data-module-screen="form" data-module-list-url="<?= $aman($listUrl) ?>">
    <a class="sa-back-link" href="<?= $aman($listUrl) ?>">← Kembali ke Daftar <?= $peran ?></a>
    <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / KELOLA AKUN / AKUN <?= strtoupper($peran) ?></p><h1 tabindex="-1" data-sa-form-focus>Hapus Akun <?= $peran ?></h1></div>
    <section class="sa-admin-form-card" aria-labelledby="sa-admin-delete-title">
        <h2 id="sa-admin-delete-title"><?= $pesananAktif === [] ? 'Hapus akun ' . $aman($target['nama']) . '?' : 'Penghapusan belum tersedia' ?></h2>
        <?php if ($error !== null): ?>
            <p class="sa-admin-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($error) ?></p>
        <?php endif; ?>
        <p class="ad-muted sa-admin-password-help"><?= $aman($target['email']) ?> · <?= $aman(sprintf($pelanggan ? 'PLG-%03d' : 'ADM-%03d', $target['id'])) ?></p>
        <?php if ($pesananAktif !== []): ?>
            <p>Tunggu sampai seluruh pesanan berstatus Selesai atau Dibatalkan sebelum menghapus akun.</p>
            <ul>
                <?php foreach ($pesananAktif as $pesanan): ?>
                    <li><?= $aman($pesanan['nomor']) ?> · <?= $aman($pesanan['status']) ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="sa-admin-form-actions"><a class="ad-button" href="<?= $aman($listUrl) ?>">Kembali ke Daftar Pelanggan</a></div>
        <?php else: ?>
        <p>Penghapusan mencabut akses masuk dan mengakhiri sesi akun ini.</p>
        <?php if ($pelanggan): ?><p>Riwayat pesanan tersimpan di panel Admin.</p><?php endif; ?>
        <form class="sa-admin-form-actions" action="<?= $aman($hapusUrl) ?>" method="post">
            <input type="hidden" name="token_superadmin" value="<?= $aman($data['token']) ?>">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="versi" value="<?= (int) $target['versi'] ?>">
            <a class="ad-button" href="<?= $aman($listUrl) ?>">Batal</a>
            <?php if ($muatUlang): ?>
                <a class="ad-button" href="<?= $aman($hapusUrl) ?>">Muat Ulang Konfirmasi</a>
            <?php else: ?>
                <button class="ad-button sa-admin-delete" type="submit">Ya, Hapus <?= $peran ?></button>
            <?php endif; ?>
        </form>
        <?php endif; ?>
    </section>
</main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
