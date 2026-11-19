<?php require APP_PATH . '/views/superadmin/layouts/header.php';
$listUrl = BASE_URL . '/index.php?page=superadmin-faq';
?>
<main class="ad-container ad-main sa-main" id="sa-main" data-module-screen="list" data-module-list-url="<?= $aman($listUrl) ?>">
    <div class="sa-intro"><p class="ad-eyebrow">SUPER ADMIN / KELOLA FAQ</p><h1>Kelola FAQ</h1><p>Kelola pertanyaan umum dan jawaban yang ditampilkan kepada pelanggan.</p></div>
    <?php if ($data['pesanFaq'] !== null): ?><div class="sa-faq-message" role="status" data-dismissible-notice><p><?= $aman($data['pesanFaq']['teks']) ?></p><?php if ($data['pesanFaq']['pulihkan'] !== null): ?><form action="<?= $aman($listUrl) ?>" method="post"><input type="hidden" name="token_superadmin" value="<?= $aman($data['token']) ?>"><input type="hidden" name="aksi" value="pulihkan"><input type="hidden" name="id" value="<?= (int) $data['pesanFaq']['pulihkan']['id'] ?>"><input type="hidden" name="versi" value="<?= (int) $data['pesanFaq']['pulihkan']['versi'] ?>"><button class="ad-button" type="submit">Batalkan Hapus</button></form><?php endif; ?></div><?php endif; ?>
    <section class="ad-panel sa-faq-panel" aria-labelledby="sa-faq-list-title">
        <div class="sa-section-heading"><div><h2 id="sa-faq-list-title">Daftar Pertanyaan</h2><p class="ad-muted"><?= count($data['daftarFaq']) ?> pertanyaan umum</p></div><a class="ad-button sa-faq-add" href="<?= $aman($listUrl) ?>&amp;form=tambah">+ Tambah FAQ</a></div>
        <?php if ($data['errorFaq'] !== null): ?><div class="sa-faq-error" role="alert" tabindex="-1" data-dismissible-notice><p><?= $aman($data['errorFaq']) ?></p><?php if ($data['muatUlangFaq']): ?><a class="ad-button" href="<?= $aman($listUrl) ?>">Muat Ulang FAQ</a><?php else: ?><a href="<?= $aman($listUrl) ?>">Kembali ke daftar</a><?php endif; ?></div><?php endif; ?>
        <div class="ad-table-scroll sa-faq-table-wrap" role="region" aria-label="Daftar pertanyaan dan jawaban FAQ" tabindex="0">
            <table class="ad-table sa-faq-table"><caption class="ad-sr-only">FAQ yang ditampilkan pada halaman pelanggan</caption>
                <thead><tr><th scope="col">No.</th><th scope="col">Pertanyaan dan Jawaban</th><th scope="col">Tindakan</th></tr></thead>
                <tbody><?php foreach ($data['daftarFaq'] as $index => $faq): ?><tr data-sa-faq-row><td><?= $index + 1 ?></td><th scope="row"><strong><?= $aman($faq['pertanyaan']) ?></strong><p><?= $aman($faq['jawaban']) ?></p></th><td><div class="sa-faq-row-actions"><a class="ad-button" href="<?= $aman($listUrl) ?>&amp;edit=<?= (int) $faq['id'] ?>">Edit<span class="ad-sr-only"> FAQ <?= $index + 1 ?></span></a><form action="<?= $aman($listUrl) ?>" method="post"><input type="hidden" name="token_superadmin" value="<?= $aman($data['token']) ?>"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int) $faq['id'] ?>"><input type="hidden" name="versi" value="<?= (int) $faq['versi'] ?>"><button class="ad-button sa-faq-delete" type="submit">Hapus<span class="ad-sr-only"> FAQ <?= $index + 1 ?></span></button></form></div></td></tr><?php endforeach; ?>
                    <?php if (!$data['daftarFaq']): ?><tr><td colspan="3" class="sa-faq-empty">Belum ada FAQ. Pilih Tambah FAQ untuk membuat pertanyaan pertama.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        
    </section>
</main>
<?php require APP_PATH . '/views/superadmin/layouts/footer.php'; ?>
