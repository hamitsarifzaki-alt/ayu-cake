<?php require APP_PATH . '/views/superadmin/layouts/header.php';
$listUrl = BASE_URL . '/index.php?page=superadmin-akun-pelanggan';
$f = $data['filterPelanggan'];
$tautanHalaman = static fn(int $halaman): string => $listUrl . '&' . http_build_query(['cari' => $f['cari'], 'halaman' => $halaman]);
$pageUrl = $tautanHalaman($data['nomorHalaman']);
$detail = $data['detailPelanggan'];
?>
<main class="ad-container ad-main sa-main sa-customer-main" id="sa-main" data-module-screen="list" data-module-list-url="<?= $aman($pageUrl) ?>">
    <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / KELOLA AKUN / AKUN PELANGGAN</p><h1>Akun Pelanggan</h1><p>Lihat data dan kelola akses akun pelanggan.</p></div>
    <?php if ($data['pesanPelanggan'] !== null): ?><p class="sa-customer-message" role="status" data-dismissible-notice><?= $aman($data['pesanPelanggan']) ?></p><?php endif; ?>
    <section class="ad-panel sa-customer-panel" aria-labelledby="sa-customer-list-title">
        <div class="sa-section-heading"><div><h2 id="sa-customer-list-title">Daftar Pelanggan</h2><p class="ad-muted"><?= (int) $f['jumlah'] ?> pelanggan ditemukan dari <?= (int) $f['total'] ?> pelanggan.</p></div></div>
        <?php if ($data['errorPelanggan'] !== null): ?><div class="sa-customer-error" role="alert" tabindex="-1" data-dismissible-notice><p><?= $aman($data['errorPelanggan']) ?></p><?php if ($data['muatUlangPelanggan']): ?><a class="ad-button" href="<?= $aman($pageUrl) ?>">Muat Ulang Daftar</a><?php endif; ?></div><?php endif; ?>
        <form class="sa-customer-filters" action="<?= BASE_URL ?>/index.php" method="get" aria-label="Filter pelanggan">
            <input type="hidden" name="page" value="superadmin-akun-pelanggan">
            <label for="sa-customer-search">Cari pelanggan<input type="search" id="sa-customer-search" name="cari" value="<?= $aman($f['cari']) ?>" maxlength="120" placeholder="Nama, email, telepon, atau ID pelanggan"></label>
            <div class="sa-customer-filter-actions"><button class="ad-button" type="submit">Tampilkan</button><?php if ($f['cari'] !== ''): ?><a class="ad-button" href="<?= $aman($listUrl) ?>">Reset</a><?php endif; ?></div>
        </form>
        <div class="ad-table-scroll sa-customer-table-wrap" role="region" aria-label="Daftar akun pelanggan" tabindex="0">
            <table class="ad-table sa-customer-table">
                <caption class="ad-sr-only">Data pelanggan dan tindakan akun</caption>
                <thead><tr><th scope="col">Pelanggan</th><th scope="col">Email</th><th scope="col">Telepon terdaftar</th><th scope="col">Tindakan</th></tr></thead>
                <tbody><?php foreach ($data['daftarPelanggan'] as $a): ?>
                    <tr data-sa-customer-row><th scope="row"><strong><?= $aman($a['nama']) ?></strong><small><?= $aman(sprintf('PLG-%03d', $a['id'])) ?></small></th>
                        <td><?= $aman($a['email']) ?></td><td><?= $aman($a['telepon'] !== '' ? $a['telepon'] : '—') ?></td>
                        <td><div class="sa-customer-row-actions">
                            <a class="ad-button" href="<?= $aman($pageUrl) ?>&amp;detail=<?= (int) $a['id'] ?>"<?= $detail !== null && $detail['id'] === $a['id'] ? ' aria-current="true"' : '' ?>>Detail<span class="ad-sr-only"> <?= $aman($a['nama']) ?></span></a>
                            <?php if (!$a['contoh']): ?>
                                <a class="ad-button sa-admin-delete" href="<?= $aman($pageUrl) ?>&amp;hapus=<?= (int) $a['id'] ?>">Hapus<span class="ad-sr-only"> <?= $aman($a['nama']) ?></span></a>
                            <?php endif; ?>
                        </div></td>
                    </tr>
                <?php endforeach; ?><?php if (!$data['daftarPelanggan']): ?><tr><td colspan="4" class="sa-customer-empty">Tidak ada pelanggan yang sesuai dengan pencarian.</td></tr><?php endif; ?></tbody>
            </table>
        </div>
            <div class="sa-customer-pagination"><p class="ad-muted">Menampilkan <?= $f['jumlah'] ? ($f['halaman'] - 1) * $f['perHalaman'] + 1 : 0 ?>–<?= min($f['halaman'] * $f['perHalaman'], $f['jumlah']) ?> dari <?= (int) $data['jumlahPelanggan'] ?> pelanggan</p>
                <?php if ($data['jumlahHalaman'] > 1): ?><nav aria-label="Halaman daftar pelanggan">
                    <?php if ($data['nomorHalaman'] > 1): ?><a class="ad-button" href="<?= $aman($tautanHalaman($data['nomorHalaman'] - 1)) ?>">‹ Sebelumnya</a><?php endif; ?>
                    <span>Halaman <?= (int) $data['nomorHalaman'] ?> / <?= (int) $data['jumlahHalaman'] ?></span>
                    <?php if ($data['nomorHalaman'] < $data['jumlahHalaman']): ?><a class="ad-button" href="<?= $aman($tautanHalaman($data['nomorHalaman'] + 1)) ?>">Berikutnya ›</a><?php endif; ?>
                </nav><?php endif; ?>
            </div>

        
    </section>
</main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
