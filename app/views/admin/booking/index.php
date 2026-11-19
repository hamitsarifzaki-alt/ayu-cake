<?php
$kalender = $data['kalender'];
$pilihan = $kalender['pilihan'];
$form = $data['bookingForm'];
$bulanNama = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$judulBulan = $bulanNama[(int) $kalender['awal']->format('n')] . ' ' . $kalender['awal']->format('Y');
$tautanBooking = static fn(array $parameter): string => BASE_URL . '/index.php?' . http_build_query(['page' => 'admin-booking'] + $parameter) . '#kalender-booking';
?>
<main class="ad-container ad-main ad-booking-main" id="ad-main">
    <div class="ad-booking-intro"><p class="ad-eyebrow">ADMIN / PENGATURAN BOOKING</p><h1>Pengaturan Booking</h1><p>Atur kapasitas loyang dan tanggal penerimaan pesanan booking.</p></div>
    <section class="ad-panel ad-booking-panel" id="kalender-booking" data-booking-month="<?= $aman($kalender['bulan']) ?>" aria-labelledby="ad-booking-title">
        <h2 id="ad-booking-title">Kalender Booking</h2><p class="ad-muted ad-booking-subtitle">Pilih tanggal pengambilan atau pengiriman yang ingin diatur. Jumlah loyang menunjukkan sisa kuota.</p>
        <div class="ad-booking-grid">
            <section class="ad-booking-calendar" aria-labelledby="ad-booking-month">
                <div class="ad-booking-calendar-heading"><h3 id="ad-booking-month"><?= $aman($judulBulan) ?></h3><nav class="ad-booking-month-nav" aria-label="Navigasi bulan kalender"><?php foreach (['sebelum' => ['Bulan sebelumnya', '‹'], 'sesudah' => ['Bulan berikutnya', '›']] as $key => [$label, $ikon]): ?><?php if ($kalender[$key] !== null): ?><a class="ad-button" href="<?= $aman($tautanBooking($kalender[$key])) ?>" aria-label="<?= $aman($label) ?>"><?= $ikon ?></a><?php else: ?><span class="ad-button ad-booking-disabled" aria-disabled="true" aria-label="<?= $aman($label) ?>"><?= $ikon ?></span><?php endif; ?><?php endforeach; ?></nav></div>
                <div class="ad-booking-weekdays" aria-hidden="true"><?php foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $pekan): ?><span><?= $pekan ?></span><?php endforeach; ?></div>
                <div class="ad-booking-days" role="group" aria-label="Pilih tanggal Booking <?= $aman($judulBulan) ?>">
                    <?php for ($kosong = 0; $kosong < $kalender['offset']; $kosong++): ?><span aria-hidden="true"></span><?php endfor; ?>
                    <?php foreach ($kalender['hari'] as $hari): ?>
                        <?php
                        $dipilih = $hari['tanggal'] === ($pilihan['tanggal'] ?? null);
                        $hariIni = $hari['tanggal'] === $kalender['hariIni'];
                        $kelas = 'ad-booking-day' . ($hari['lampau'] ? ' ad-booking-past' : '')
                            . ($hari['status'] === 'Penuh' ? ' ad-booking-full' : ($hari['status'] === 'Ditutup' ? ' ad-booking-closed' : ''))
                            . ($hariIni ? ' ad-booking-today' : '') . ($dipilih ? ' ad-booking-selected' : '');
                        $keterangan = $hari['lampau'] ? '' : ($hari['status'] === 'Tersedia'
                            ? (int) $hari['sisa'] . ' loyang' : $hari['status']);
                        $label = $tanggal($hari['tanggal'], true) . ($hariIni ? ', Hari ini' : '')
                            . ', ' . ($hari['lampau'] ? 'Sudah lewat' : $hari['status'])
                            . (!$hari['lampau'] && $hari['status'] === 'Tersedia' ? ', sisa ' . (int) $hari['sisa'] . ' loyang' : '')
                            . ($dipilih ? ', Dipilih' : '');
                        ?>
                        <?php if ($hari['lampau']): ?><span class="<?= $kelas ?>" aria-label="<?= $aman($label) ?>"><strong><?= (int) substr($hari['tanggal'], 8) ?></strong><?php if ($keterangan !== ''): ?><small><?= $aman($keterangan) ?></small><?php endif; ?></span>
                        <?php else: ?><a class="<?= $kelas ?>" href="<?= $aman($tautanBooking(['tanggal' => $hari['tanggal']])) ?>" aria-label="<?= $aman('Pilih ' . $label) ?>"<?= $dipilih ? ' aria-current="true"' : '' ?> data-booking-date="<?= $aman($hari['tanggal']) ?>"><strong><?= (int) substr($hari['tanggal'], 8) ?></strong><?php if ($keterangan !== ''): ?><small><?= $aman($keterangan) ?></small><?php endif; ?></a><?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="ad-booking-legend" aria-label="Keterangan kalender"><span>Tersedia</span><span class="ad-booking-legend-selected">Dipilih</span><span class="ad-booking-legend-full">Penuh</span><span class="ad-booking-legend-closed">Ditutup</span></div>
            </section>
            <section class="ad-booking-settings" aria-labelledby="ad-booking-selected-title">
                <?php if ($data['pesanBooking'] !== null): ?><p class="ad-booking-message" role="status" data-dismissible-notice>Pengaturan Booking untuk <?= $aman($tanggal($data['pesanBooking'])) ?> berhasil disimpan.</p><?php endif; ?>
                <?php if ($pilihan === null): ?>
                <h2 id="ad-booking-selected-title">Pilih tanggal di kalender</h2>
                <?php if ($data['errorBooking'] !== null): ?><p class="ad-form-error ad-booking-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($data['errorBooking']) ?></p><?php endif; ?>
                <p class="ad-muted ad-booking-existing">Pilih tanggal untuk melihat dan mengubah kapasitas Booking.</p>
                <?php else: ?>
                <p class="ad-muted">Tanggal Dipilih</p><h2 id="ad-booking-selected-title"><?= $aman($tanggal($pilihan['tanggal'], true)) ?></h2>
                <?php if ($data['errorBooking'] !== null): ?><p class="ad-form-error ad-booking-error" role="alert" tabindex="-1" data-dismissible-notice><?= $aman($data['errorBooking']) ?></p><?php endif; ?>
                <?php if ($pilihan['lampau']): ?><p class="ad-booking-used">Tanggal ini sudah lewat. Pilih tanggal hari ini atau setelahnya untuk mengubah pengaturan.</p><p class="ad-muted"><?= (int) $pilihan['terpakai'] ?> loyang terpakai • Kapasitas <?= (int) $pilihan['maksimal'] ?> loyang</p>
                <?php else: ?>
                    <form action="<?= $aman($tautanBooking(['tanggal' => $pilihan['tanggal']])) ?>" method="post" class="ad-booking-form" data-track-changes data-form-baseline="<?= htmlspecialchars(json_encode(['ketersediaan' => $pilihan['dibuka'] ? 'dibuka' : 'ditutup', 'kapasitas' => $pilihan['maksimal']], JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="token_admin" value="<?= $aman($data['token']) ?>"><input type="hidden" name="tanggal" value="<?= $aman($pilihan['tanggal']) ?>"><input type="hidden" name="versi" value="<?= $aman($form['versi']) ?>">
                        <label for="ad-booking-availability">Ketersediaan tanggal</label><select id="ad-booking-availability" name="ketersediaan" required><option value="dibuka"<?= $form['ketersediaan'] === 'dibuka' ? ' selected' : '' ?>>Dibuka</option><option value="ditutup"<?= $form['ketersediaan'] === 'ditutup' ? ' selected' : '' ?>>Ditutup</option></select>
                        <label for="ad-booking-capacity">Kapasitas maksimal pada tanggal ini</label><div class="ad-booking-capacity-input"><input id="ad-booking-capacity" type="number" name="kapasitas" value="<?= $aman($form['kapasitas']) ?>" min="<?= (int) $pilihan['terpakai'] ?>" max="<?= $data['kapasitasMaksimal'] ?>" step="1" required inputmode="numeric" aria-describedby="ad-booking-used"><span aria-hidden="true">loyang</span></div>
                        <p class="ad-booking-used" id="ad-booking-used"><?= (int) $pilihan['terpakai'] ?> loyang terpakai • <?= !$pilihan['dibuka'] ? 'Tanggal ditutup' : 'Sisa ' . (int) $pilihan['sisa'] . ' loyang' ?> • Maksimal <?= $data['kapasitasMaksimal'] ?> loyang per hari</p>
                        <div class="ad-booking-actions"><a class="ad-button" data-cancel-url="<?= $aman($tautanBooking(['bulan' => $kalender['bulan']])) ?>" data-cancel-changes role="link" aria-disabled="true" tabindex="-1">Batal</a><button class="ad-button" type="submit" data-save-changes disabled>Simpan Pengaturan</button></div>
                        <p class="ad-muted ad-booking-existing">Pesanan yang sudah ada tetap tercatat.</p>
                    </form>
                <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </section>
</main>
