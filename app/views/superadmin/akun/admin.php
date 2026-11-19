<?php require APP_PATH . '/views/superadmin/layouts/header.php';
$listUrl = BASE_URL . '/index.php?page=superadmin-akun-admin';
?>
<main class="ad-container ad-main sa-main sa-admin-main" id="sa-main" data-module-screen="list" data-module-list-url="<?= $aman($listUrl) ?>">
    <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / KELOLA AKUN / AKUN ADMIN</p><h1>Akun Admin</h1><p>Kelola data dan akses admin yang bertugas mengelola toko.</p></div>
    <?php if ($data['pesanAdmin'] !== null): ?><p class="sa-admin-message" role="status" data-dismissible-notice><?= $aman($data['pesanAdmin']) ?></p><?php endif; ?>
    <section class="ad-panel sa-admin-panel" aria-labelledby="sa-admin-list-title">
        <div class="sa-section-heading"><div><h2 id="sa-admin-list-title">Daftar Admin</h2><p class="ad-muted"><?= (int) $data['aktifAdmin'] ?> admin aktif • <?= count($data['daftarAdmin']) - $data['aktifAdmin'] ?> admin nonaktif</p></div><a class="ad-button sa-primary sa-add-admin" href="<?= $aman($listUrl) ?>&amp;form=tambah">+ Tambah Admin</a></div>
        <?php if ($data['errorAdmin'] !== null): ?><div class="sa-admin-error" role="alert" tabindex="-1" data-dismissible-notice><p><?= $aman($data['errorAdmin']) ?></p><?php if ($data['muatUlangAdmin']): ?><a class="ad-button" href="<?= $aman($listUrl) ?>">Muat Ulang Form</a><?php endif; ?></div><?php endif; ?>
        <div class="ad-table-scroll sa-admin-table-wrap" role="region" aria-label="Daftar akun admin" tabindex="0"><table class="ad-table sa-admin-table"><caption class="ad-sr-only">Akun admin aktif dan nonaktif</caption><thead><tr><th scope="col">Nama Admin</th><th scope="col">Email</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
            <?php foreach ($data['daftarAdmin'] as $a): ?>
                <tr data-sa-admin-row>
                    <th scope="row"><strong><?= $aman($a['nama']) ?></strong><small><?= $aman(sprintf('ADM-%03d', $a['id'])) ?></small></th>
                    <td><?= $aman($a['email']) ?></td>
                    <td><span class="ad-badge <?= $a['aktif'] ? 'ad-badge-green' : 'sa-badge-inactive' ?>"><?= $a['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td><div class="sa-admin-row-actions">
                        <a class="ad-button" href="<?= $aman($listUrl) ?>&amp;edit=<?= (int) $a['id'] ?>">Edit<span class="ad-sr-only"> <?= $aman($a['nama']) ?></span></a>
                        <a class="ad-button sa-admin-delete" href="<?= $aman($listUrl) ?>&amp;hapus=<?= (int) $a['id'] ?>">Hapus<span class="ad-sr-only"> <?= $aman($a['nama']) ?></span></a>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($data['daftarAdmin'] === []): ?><tr><td colspan="4">Belum ada akun admin.</td></tr><?php endif; ?>
        </tbody></table></div>
    </section>
</main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
