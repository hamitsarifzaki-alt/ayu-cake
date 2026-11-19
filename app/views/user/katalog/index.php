<main class="catalog-page">
  
    <section class="catalog-banner">
        <div class="container catalog-banner-inner">
            <div class="catalog-banner-copy">
                <nav class="catalog-breadcrumb" aria-label="Lokasi halaman">
                    <a href="<?= BASE_URL ?>/">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">Katalog</span>
                </nav>

                <p class="catalog-slogan">
                    Hangatnya kue,<br>
                    indahnya setiap cerita.
                </p>

                <h1>Katalog Kue Ayu Cake</h1>

                <p class="catalog-description">
                    Pilih kue untuk dinikmati hari ini atau pesan lebih awal
                    untuk momen spesialmu.
                </p>
            </div>
        </div>
    </section>
    
    <section class="order-steps" aria-label="Langkah pemesanan">
        <div class="container steps-grid">

            <article class="step">
                <span class="step-number">01</span>
                <div>
                    <h3>Pilih kue favorit</h3>
                    <p>Lihat katalog dan detail produk.</p>
                </div>
            </article>

            <article class="step">
                <span class="step-number">02</span>
                <div>
                    <h3>Atur waktu pesanan</h3>
                    <p>Booking minimal H+5 untuk pre-order.</p>
                </div>
            </article>

            <article class="step">
                <span class="step-number">03</span>
                <div>
                    <h3>Bayar dengan tenang</h3>
                    <p>Unggah bukti, lalu admin memverifikasi.</p>
                </div>
            </article>

        </div>
    </section>

    <!-- Daftar produk -->
    <section class="catalog-content">
        <div class="container">

            <h2 class="catalog-section-title">Pilih kue sesuai seleramu</h2>

            <form
                class="catalog-filter"
                method="get"
                action="<?= BASE_URL ?>/index.php"
            >
                <input type="hidden" name="page" value="katalog">

                <input
                    type="hidden"
                    name="q"
                    value="<?= htmlspecialchars(
                        $data['kataKunci'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <h3>Filter Produk</h3>

                <fieldset class="catalog-categories">
                    <legend>Kategori:</legend>

                    <?php foreach (['semua' => 'Semua produk'] + $data['kategoriProduk'] as $idKategori => $namaKategori): ?>
                    <label>
                        <input type="radio" name="kategori" value="<?= htmlspecialchars($idKategori, ENT_QUOTES, 'UTF-8') ?>" <?= $data['kategori'] === $idKategori ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($namaKategori, ENT_QUOTES, 'UTF-8') ?></span>
                    </label>
                    <?php endforeach; ?>
                </fieldset>

                <fieldset class="catalog-categories catalog-order-types">
                    <legend>Jenis pesanan:</legend>
                    <?php foreach (['semua' => 'Semua jenis', 'booking' => 'Booking (Pre-Order)', 'ready-stock' => 'Pesan Sekarang (Ready Stock)'] as $nilai => $label): ?>
                        <label>
                            <input type="radio" name="jenis" value="<?= $nilai ?>" <?= $data['jenis'] === $nilai ? 'checked' : '' ?>>
                            <span><?= $label ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>

                <noscript>
                    <button class="button button-primary" type="submit">
                        Terapkan Filter
                    </button>
                </noscript>
            </form>

            <?php if ($data['kataKunci'] !== ''): ?>
                <div class="catalog-search-summary">
                    <p>
                        Hasil pencarian:
                        <strong>
                            “<?= htmlspecialchars(
                                $data['kataKunci'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>”
                        </strong>
                    </p>

                    <a href="<?= htmlspecialchars(BASE_URL . '/index.php?' . http_build_query(['page' => 'katalog', 'kategori' => $data['kategori'], 'jenis' => $data['jenis']]), ENT_QUOTES, 'UTF-8') ?>">
                        Hapus pencarian
                    </a>
                </div>
            <?php endif; ?>

            <div class="catalog-products-panel">
                <div class="catalog-category-intro" data-category-intro aria-live="polite" aria-atomic="true" <?= $data['kategori'] === 'semua' ? 'hidden' : '' ?>>
                    <h2 data-category-title><?= htmlspecialchars($data['kategoriProduk'][$data['kategori']] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="category-description" data-category-description><?= htmlspecialchars($data['deskripsiKategori'][$data['kategori']] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <script type="application/json" id="category-descriptions"><?= json_encode($data['deskripsiKategori'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
                <div class="catalog-empty catalog-filter-empty" <?= $data['jumlahHasil'] > 0 ? 'hidden' : '' ?> role="status">
                <h3>Produk tidak ditemukan</h3>
                <p>Coba kategori lain atau tampilkan semua produk.</p>
            <a
                class="button button-primary"
                href="<?= BASE_URL ?>/index.php?page=katalog"
            >
       
            Lihat Semua Produk
    </a>
</div>
                <?php if (empty($data['produk'])): ?>

                    <div class="catalog-empty">
                        <h3>Produk tidak ditemukan</h3>
                        <p>
                            Coba kata kunci lain atau tampilkan semua produk.
                        </p>

                        <a
                            class="button button-primary"
                            href="<?= BASE_URL ?>/index.php?page=katalog"
                        >
                            Lihat Semua Produk
                        </a>
                    </div>

                <?php else: ?>

                    <div class="catalog-grid">

                        <?php foreach ($data['produk'] as $produk): ?>
                       
                            <article
                                class="catalog-card"
                                data-jenis="<?= $produk['jenis'] === 'Pre-Order' ? 'booking' : 'ready-stock' ?>"
                                <?= !$produk['tampil'] ? 'hidden' : '' ?>
                                data-kategori="<?= htmlspecialchars(
                                $produk['kategori'],
                                ENT_QUOTES,
                                'UTF-8'
                                 ) ?>"
                            >

                                <img
                                    class="catalog-card-image"
                                    src="<?= BASE_URL ?>/public/assets/images/<?= htmlspecialchars(
                                        $produk['gambar'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    alt="<?= htmlspecialchars(
                                        $produk['nama'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    width="400"
                                    height="300"
                                    loading="lazy"
                                >

                                <div class="catalog-card-body">
                                    <p class="catalog-card-type">
                                        <?= htmlspecialchars(
                                            $produk['jenis'] === 'Pre-Order' ? 'Booking / Pre-Order' : 'Ready Stock',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>

                                    <h3>
                                        <?= htmlspecialchars(
                                            $produk['nama'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </h3>

                                    <p class="catalog-card-price">
                                        Rp <?= number_format(
                                            $produk['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                        <span>/ Loyang</span>
                                    </p>

                                    <p class="catalog-card-note">
                                        <?= $produk['jenis'] === 'Pre-Order'
                                            ? 'Kuota sesuai tanggal'
                                            : ($produk['stok'] > 0 ? 'Stok: ' . (int) $produk['stok'] . ' loyang' : 'Stok habis') ?>
                                    </p>

                                    <a
                                        class="catalog-detail-link"
                                        href="<?= BASE_URL ?>/index.php?page=detail-produk&amp;id=<?= (int) $produk['id'] ?>"
                                        aria-label="Lihat detail <?= htmlspecialchars(
                                            $produk['nama'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >
                                        Lihat Detail
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </div>

                            </article>
                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

            <?php if (!empty($data['produk'])): ?>
                <nav class="catalog-pagination" aria-label="Halaman katalog" <?= $data['jumlahHasil'] === 0 ? 'hidden' : '' ?>>
                    <span class="catalog-page-current" aria-current="page">
                        1
                    </span>

                    <button
                        type="button"
                        disabled
                        aria-label="Halaman berikutnya belum tersedia"
                    >
                        <span aria-hidden="true">→</span>
                    </button>
                </nav>
            <?php endif; ?>

        </div>
    </section>

    <!-- Informasi booking -->
    <section class="catalog-booking">
        <div class="container catalog-booking-inner">
            <div>
                <p class="catalog-booking-label">
                    Rencanakan hari spesial
                </p>

                <h2>Booking kue tanpa terburu-buru.</h2>

                <p class="catalog-booking-description">
                    Pesan minimal 5 hari sebelumnya.<br>
                    Pilih tanggal yang tersedia dan metode pengambilan.<br>
                    Untuk pengiriman, pembayaran dilakukan setelah<br>
                    alamat dan ongkir dikonfirmasi admin.
                </p>
            </div>

            <a
                class="button button-light"
                href="<?= BASE_URL ?>/index.php?page=cara-pesan"
            >
                Lihat Cara Pesan
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>

</main>

<script
    src="<?= BASE_URL ?>/public/assets/js/katalog.js?v=4"
    defer
></script>
