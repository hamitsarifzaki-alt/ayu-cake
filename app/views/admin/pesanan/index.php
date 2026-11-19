<?php
$daftar = $data['daftar'];
$tautanHalaman = static fn(int $halaman): string => BASE_URL . '/index.php?' . http_build_query([
    'page' => 'admin-pesanan', 'cari' => $daftar['cari'], 'status' => $daftar['status'],
    'jenis' => $daftar['jenis'], 'halaman' => $halaman,
]);
?>
<main class="ad-container ad-main ad-pesanan-main" id="ad-main">
    <div class="ad-pesanan-intro">
        <p class="ad-eyebrow">ADMIN / PESANAN</p>
        <h1>Pesanan</h1>
        <p>Kelola pesanan pelanggan melalui detail pesanan.</p>
    </div>
    <?php if (!empty($data['pesanPesanan'])): ?><p class="ad-order-success" role="status" data-dismissible-notice><?= $aman($data['pesanPesanan']) ?></p><?php endif; ?>
    <?php $notifikasi = $data['notifikasiAdmin']; $peranNotifikasi = 'admin'; require APP_PATH . '/views/shared/notifikasi.php'; ?>
    <section class="ad-panel ad-pesanan-panel" aria-labelledby="ad-list-title">
        <div class="ad-panel-heading">
            <h2 id="ad-list-title">Daftar Pesanan</h2>
            <p class="ad-muted"><?= (int) $daftar['jumlah'] ?> pesanan<?= $daftar['jumlah'] !== $daftar['total'] ? ' ditemukan' : '' ?></p>
        </div>
        <form class="ad-list-filters" action="<?= BASE_URL ?>/index.php" method="get" role="search" aria-label="Filter daftar pesanan">
            <input type="hidden" name="page" value="admin-pesanan">
            <label>Cari pesanan<input type="search" name="cari" value="<?= $aman($daftar['cari']) ?>" maxlength="120" placeholder="Nomor pesanan atau nama pelanggan"></label>
            <label>Status pesanan<select name="status"><?php foreach ($daftar['opsiStatus'] as $nilai => $label): ?><option value="<?= $aman($nilai) ?>"<?= $daftar['status'] === $nilai ? ' selected' : '' ?>><?= $aman($label) ?></option><?php endforeach; ?></select></label>
            <label>Jenis pesanan<select name="jenis"><?php foreach ($daftar['opsiJenis'] as $nilai => $label): ?><option value="<?= $aman($nilai) ?>"<?= $daftar['jenis'] === $nilai ? ' selected' : '' ?>><?= $aman($label) ?></option><?php endforeach; ?></select></label>
            <button class="ad-button" type="submit">Tampilkan</button>
        </form>
        <div class="ad-list-note"><p class="ad-muted">Urutan berdasarkan pembaruan terbaru. Lihat informasi akun, penerima, dan pesanan melalui Detail Pesanan.</p>
            <?php if ($daftar['cari'] !== '' || $daftar['status'] !== 'semua' || $daftar['jenis'] !== 'semua'): ?><a class="ad-text-link" href="<?= $aman($urlPesanan) ?>">Reset filter</a><?php endif; ?>
        </div>
        <?php if ($daftar['jumlah'] > 0): ?>
            <div class="ad-table-scroll" role="region" aria-label="Daftar pesanan admin" tabindex="0">
                <table class="ad-table ad-list-table">
                    <caption class="ad-sr-only">Daftar pesanan pelanggan, halaman <?= (int) $daftar['halaman'] ?> dari <?= (int) $daftar['halamanTotal'] ?></caption>
                    <thead><tr><th scope="col">Pesanan / pelanggan</th><th scope="col">Jenis / penerimaan</th><th scope="col">Tanggal ambil / kirim</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead>
                    <tbody>
                    <?php foreach ($daftar['items'] as $pesanan): ?>
                        <tr data-ad-list-row>
                            <th scope="row"><strong><?= $aman($pesanan['nomor']) ?></strong><small><?= $aman($pesanan['nama']) ?></small></th>
                            <td><strong><?= $aman($pesanan['jenis']) ?></strong><small><?= $aman($pesanan['layanan']) ?></small></td>
                            <td><?= $aman($tanggal($pesanan['tanggal'])) ?></td>
                            <td><strong><?= $pesanan['total'] === null ? 'Belum final' : $rupiah($pesanan['total']) ?></strong><small><?= $pesanan['total'] === null ? 'Subtotal ' . $rupiah($pesanan['subtotal']) : ($pesanan['status'] === 'Dibatalkan' ? 'Belum dibayar' : ($pesanan['ongkir'] === 0 ? 'Ongkir Rp0' : 'Termasuk ongkir')) ?></small></td>
                            <td><span class="ad-badge <?= $pesanan['status'] === 'Selesai' ? 'ad-badge-green' : ($pesanan['status'] === 'Dibatalkan' ? 'ad-badge-red' : '') ?>"><?= $aman($pesanan['status']) ?></span></td>
                            <td><a class="ad-button ad-row-action" href="<?= $aman($urlDetailPesanan($pesanan['nomor'])) ?>">Detail Pesanan<span class="ad-sr-only"> <?= $aman($pesanan['nomor']) ?></span></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="ad-empty"><h3>Pesanan tidak ditemukan</h3><p>Coba kata kunci lain atau ubah status dan jenis pesanan.</p><a class="ad-button" href="<?= $aman($urlPesanan) ?>">Tampilkan semua pesanan</a></div>
        <?php endif; ?>
        <div class="ad-list-bottom">
            <p class="ad-muted" role="status">Menampilkan <?= $daftar['jumlah'] > 0 ? (($daftar['halaman'] - 1) * 7 + 1) . '–' . (($daftar['halaman'] - 1) * 7 + count($daftar['items'])) : '0' ?> dari <?= (int) $daftar['jumlah'] ?> pesanan</p>
            <?php if ($daftar['jumlah'] > 0): ?>
                <nav class="ad-pagination" aria-label="Halaman daftar pesanan">
                    <?php if ($daftar['halaman'] > 1): ?><a class="ad-button" href="<?= $aman($tautanHalaman($daftar['halaman'] - 1)) ?>" aria-label="Halaman sebelumnya">‹</a><?php endif; ?>
                    <?php for ($i = 1; $i <= $daftar['halamanTotal']; $i++): ?>
                        <?php if ($i === $daftar['halaman']): ?><span aria-current="page" aria-label="Halaman <?= $i ?>"><?= $i ?></span>
                        <?php else: ?><a class="ad-button" href="<?= $aman($tautanHalaman($i)) ?>" aria-label="Halaman <?= $i ?>"><?= $i ?></a><?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($daftar['halaman'] < $daftar['halamanTotal']): ?><a class="ad-button" href="<?= $aman($tautanHalaman($daftar['halaman'] + 1)) ?>" aria-label="Halaman berikutnya">›</a><?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </section>
</main>
