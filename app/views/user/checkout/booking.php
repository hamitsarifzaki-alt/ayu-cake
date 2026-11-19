<?php

$aman = static function ($nilai): string {
    return htmlspecialchars(
        (string) $nilai,
        ENT_QUOTES,
        'UTF-8'
    );
};

$rupiah = static function ($nilai): string {
    return 'Rp' . number_format((float) $nilai, 0, ',', '.');
};

$produk = $data['produk'];

$configBooking = [
    'hariIni' => $data['hariIni'],
    'tanggalMinimal' => $data['tanggalMinimal'],
    'jumlah' => (int) $data['jumlah'],
    'subtotal' => $data['subtotal'],
    'kalender' => $data['kalenderContoh'],
];

?>

<main class="co-page">
    <div class="container co-container">

        <nav class="co-breadcrumb" aria-label="Jejak halaman">
            <a href="<?= BASE_URL ?>/">Beranda</a>
            <span>/</span>
            <a href="<?= BASE_URL ?>/index.php?page=<?= $data['dariKeranjang'] ? 'keranjang' : 'katalog' ?>"><?= $data['dariKeranjang'] ? 'Keranjang' : 'Katalog' ?></a>
            <span>/</span>
            <span aria-current="page">Booking</span>
        </nav>

        <h1>Checkout Booking</h1>

        <p class="co-lead">
            Rencanakan kue untuk hari istimewamu, minimal 5 hari sebelumnya.
        </p>

        <ol class="co-steps" id="co-langkah">
            <li class="is-current" aria-current="step">
                <span>1</span> Isi pesanan
            </li>
            <li><span>2</span> Pembayaran</li>
            <li><span>3</span> Verifikasi pembayaran</li>
        </ol>

        <a
    class="co-back"
    href="<?= $aman($data['urlKembali']) ?>"
>
    ← <?= $aman($data['teksKembali']) ?>
</a>

        <?php if ($data['pesanError'] !== null): ?>

            <section class="co-panel">
                <h2>Pesanan belum dapat dilanjutkan</h2>
                <p role="alert"><?= $aman($data['pesanError']) ?></p>
            </section>

        <?php else: ?>

            <form
    class="co-panel"
    id="booking-form"
    action="<?= htmlspecialchars(
        BASE_URL . '/index.php?page=buat-pesanan',
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    method="post"
>
    <input type="hidden" name="jenis" value="booking">
    <input type="hidden" name="token_checkout" value="<?= $aman($data['tokenCheckout']) ?>">

    <input
    type="hidden"
    name="sumber"
    value="<?= !empty($data['dariKeranjang']) ? 'keranjang' : 'langsung' ?>"
>

<?php if (empty($data['dariKeranjang'])): ?>
    <input
        type="hidden"
        name="id_produk"
        value="<?= (int) ($data['produk']['id'] ?? 0) ?>"
    >

    <input
        type="hidden"
        name="jumlah"
        value="<?= (int) ($data['jumlah'] ?? 0) ?>"
    >
<?php endif; ?>

                <section class="co-section">
                    <h2><span class="co-number">1</span> Data penerima</h2>

                    <div class="co-grid">
                        <label class="co-field">
                            <span>Nama penerima *</span>
                            <input
                                type="text"
                                name="nama_penerima"
                                placeholder="Nama lengkap penerima"
                                autocomplete="name"
                                minlength="3"
                                maxlength="100"
                                pattern="\p{L}+(?: +\p{L}+)*"
                                title="Nama penerima minimal 3 karakter dan hanya boleh berisi huruf serta spasi."
                                required
                            >
                        </label>

                        <label class="co-field">
                            <span>Nomor WhatsApp *</span>
                            <input
                                type="tel"
                                name="telepon"
                                placeholder="Contoh: 081234567890"
                                autocomplete="tel"
                                inputmode="numeric"
                                minlength="10"
                                maxlength="15"
                                pattern="08[0-9]{8,13}"
                                title="Nomor telepon harus berisi 10–15 angka dan diawali 08."
                                required
                            >
                        </label>
                    </div>

                    <p class="co-help">
                        Pastikan data penerima benar sebelum membuat pesanan.
                    </p>
                </section>

                <section class="co-section">
                    <h2>
                        <span class="co-number">2</span>
                        Tanggal pengambilan atau pengiriman
                    </h2>

                    <p class="co-help">
                        Pilih tanggal minimal H+5.
                        Kapasitas Booking maksimal 50 loyang per hari.
                    </p>

                    <p class="co-date-note" id="co-info-tanggal"></p>

                    <div class="co-calendar">
                        <div class="co-calendar-heading">
                            <h3 id="co-bulan" aria-live="polite"></h3>

                            <div class="co-calendar-navigation">
                                <button
                                    type="button"
                                    id="co-bulan-sebelumnya"
                                    aria-label="Bulan sebelumnya"
                                >‹</button>

                                <button
                                    type="button"
                                    id="co-bulan-berikutnya"
                                    aria-label="Bulan berikutnya"
                                >›</button>
                            </div>
                        </div>

                        <div class="co-weekdays" aria-hidden="true">
                            <span>Sen</span><span>Sel</span>
                            <span>Rab</span><span>Kam</span>
                            <span>Jum</span><span>Sab</span>
                            <span>Min</span>
                        </div>

                        <div
                            class="co-days"
                            id="co-hari"
                            role="group"
                            aria-label="Pilihan tanggal Booking"
                        ></div>

                        <div class="co-calendar-legend">
                            <span><i class="co-dot-selected"></i> Dipilih</span>
                            <span><i class="co-dot-unavailable"></i> Tidak tersedia</span>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="tanggal"
                        id="co-tanggal"
                    >

                    <div class="co-quota" aria-live="polite">
                        <div class="co-row">
                            <strong id="co-tanggal-terpilih">
                                Belum ada tanggal dipilih
                            </strong>

                            <strong id="co-sisa-kuota"></strong>
                        </div>

                        <progress
                            id="co-progress"
                            max="50"
                            value="0"
                            aria-label="Kapasitas Booking terisi"
                        ></progress>

                        <div class="co-row co-help">
                            <span id="co-kuota-terisi">
                                Pilih tanggal yang tersedia pada kalender.
                            </span>
                            <span>
                                Pesananmu: <?= (int) $data['jumlah'] ?> loyang
                            </span>
                        </div>
                    </div>

                    <p
                        id="co-error-tanggal"
                        class="co-error"
                        role="alert"
                        hidden
                    ></p>
                </section>

                <section class="co-section">
                    <h2>
                        <span class="co-number">3</span>
                        Cara menerima pesanan
                    </h2>

                    <div class="co-grid">
                        <label class="co-choice">
                            <input
                                type="radio"
                                name="penerimaan"
                                value="ambil"
                                checked
                            >
                            <span>
                                <strong>Ambil di Toko</strong>
                                <small>Tanpa ongkos kirim</small>
                            </span>
                        </label>

                        <label class="co-choice">
                            <input
                                type="radio"
                                name="penerimaan"
                                value="kirim"
                            >
                            <span>
                                <strong>Dikirim ke Alamat</strong>
                                <small>Alamat dan ongkir diperiksa admin</small>
                            </span>
                        </label>
                    </div>

                    <div class="co-store" id="co-info-toko">
                        <strong>Pengambilan di Ayu Cake</strong>
                        <p>
                            Desa Bukit Terap, Kecamatan Tukak Sadai,
                            Kabupaten Bangka Selatan.
                        </p>
                    </div>

                    <fieldset id="co-pengiriman" hidden disabled>
                        <legend class="sr-only">Informasi pengiriman</legend>

                        <label class="co-field co-address">
                            <span>Alamat lengkap *</span>
                            <textarea
                                name="alamat"
                                rows="2"
                                minlength="10"
                                maxlength="500"
                                autocomplete="street-address"
                                placeholder="Jalan, nomor rumah, desa/kelurahan, kecamatan, dan kota/kabupaten"
                                required
                            ></textarea>
                        </label>

                        <p class="co-label co-delivery-service"><?= $aman(Pesanan::NAMA_ANTAR) ?></p>
                        <p class="co-help">Area layanan mencakup <?= $aman(Pesanan::AREA_ANTAR) ?>.</p>

                        <div class="co-info-box">
                            <strong id="co-info-ongkir-judul"></strong>
                            <p id="co-info-ongkir-isi"></p>
                        </div>
                    </fieldset>
                </section>

                <section class="co-section">
                    <h2><span class="co-number">4</span> Catatan pesanan</h2>

                    <label class="sr-only" for="co-catatan">
                        Catatan pesanan opsional
                    </label>

                    <textarea
                        id="co-catatan"
                        name="catatan"
                        rows="2"
                        maxlength="500"
                        placeholder="Tulis patokan alamat atau catatan pesanan (opsional)."
                    ></textarea>
                </section>

                <section class="co-section">
                    <h2><span class="co-number">5</span> Metode pembayaran</h2>

                    <?php require APP_PATH . '/views/user/checkout/partials/pembayaran.php'; ?>

                    <p class="co-help" id="co-petunjuk-bayar"></p>
                </section>

                <section class="co-summary">
                    <h2>Ringkasan Pesanan</h2>

                    <p class="co-date-note" id="co-ringkasan-tanggal">
                        BOOKING • Tanggal belum dipilih
                    </p>

                    <?php require APP_PATH . '/views/user/checkout/partials/produk.php'; ?>

                    <div class="co-totals" aria-live="polite">
                        <div class="co-row">
                            <span>Subtotal produk</span>
                            <strong><?= $rupiah($data['subtotal']) ?></strong>
                        </div>

                        <div class="co-row">
                            <span id="co-label-ongkir">Ongkir • Ambil di Toko</span>
                            <strong id="co-nilai-ongkir">Rp0</strong>
                        </div>

                        <div class="co-row co-total">
                            <strong id="co-label-total">Total pembayaran</strong>
                            <strong id="co-nilai-total">
                                <?= $rupiah($data['subtotal']) ?>
                            </strong>
                        </div>
                    </div>


                    <button class="co-submit" type="submit"<?= !$data['bankTersedia'] ? ' disabled' : '' ?>>
        Buat Pesanan Booking <span aria-hidden="true">→</span>
    </button>

                    <p class="co-help" id="co-status-info"></p>

                    <p
                        class="co-info-box"
                        id="co-hasil-validasi"
                        role="status"
                        hidden
                    ></p>
                </section>

            </form>

            

            <script type="application/json" id="booking-config">
                <?= json_encode(
                    $configBooking,
                    JSON_HEX_TAG | JSON_HEX_AMP |
                    JSON_HEX_APOS | JSON_HEX_QUOT
                ) ?>
            </script>

            <script
                src="<?= BASE_URL ?>/public/assets/js/booking.js?v=3"
                defer
            ></script>

        <?php endif; ?>

    </div>
</main>
