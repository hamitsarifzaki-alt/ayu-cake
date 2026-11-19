<main>

    <section class="hero">
        <div class="container hero-inner">

            <div class="hero-copy">
                <span class="welcome-label">
                    Selamat datang di Ayu Cake
                </span>

                <h1>
                    Hangatnya kue,<br>
                    indahnya setiap cerita.
                </h1>

                <p class="hero-description">
                    Jelajahi pilihan kue basah dan bolu untuk dinikmati
                    hari ini atau dipesan lebih awal untuk hari istimewa.
                </p>

                <div class="hero-actions">
                    <a
                        class="button button-primary"
                        href="<?= BASE_URL ?>/index.php?page=katalog"
                    >
                        Lihat Katalog
                    </a>

                    <a
                        class="button button-light"
                        href="<?= BASE_URL ?>/index.php?page=cara-pesan"
                    >
                        Cara Pemesanan
                    </a>
                </div>

                <p class="hero-note">
                    Pilihan ambil di toko atau kirim sesuai ketersediaan.
                </p>
            </div>

            <!--
                Logo besar pada banner.
                Gambar diatur melalui CSS pada .hero-art.
            -->
            <div class="hero-art" aria-hidden="true"></div>

        </div>
    </section>

    <section class="order-steps" aria-label="Langkah pemesanan">
        <div class="container steps-grid">

            <div class="step">
                <span class="step-number">01</span>

                <div>
                    <h2>Pilih kue favorit</h2>
                    <p>Lihat katalog dan detail produk.</p>
                </div>
            </div>

            <div class="step">
                <span class="step-number">02</span>

                <div>
                    <h2>Atur waktu pesanan</h2>
                    <p>Booking minimal H+5 untuk pre-order.</p>
                </div>
            </div>

            <div class="step">
                <span class="step-number">03</span>

                <div>
                    <h2>Bayar dengan tenang</h2>
                    <p>Unggah bukti, lalu admin memverifikasi.</p>
                </div>
            </div>

        </div>
    </section>

    <section class="featured-products">
        <div class="container">

            <div class="section-heading">
                <h2>Pilihan untuk setiap momen</h2>

                <a href="<?= BASE_URL ?>/index.php?page=katalog">
                    Lihat semua produk <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            <div class="product-panel">
                <div class="product-grid">

                    <?php foreach ($data['produk'] as $produk): ?>
                        <article class="product-card">
                            <div
                                class="product-photo"
                                role="img"
                                aria-label="<?= htmlspecialchars($produk['nama'], ENT_QUOTES, 'UTF-8') ?>"
                                style="background-image: url('<?= htmlspecialchars(BASE_URL . '/public/assets/images/' . $produk['gambar'], ENT_QUOTES, 'UTF-8') ?>')"
                            ></div>
                            <div class="product-body">
                                <p class="product-type">
                                    <?= $produk['jenis'] === 'Pre-Order' ? 'Booking / Pre-Order' : 'Ready Stock' ?>
                                </p>
                                <h3><?= htmlspecialchars($produk['nama'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="product-price">
                                    Rp <?= number_format($produk['harga'], 0, ',', '.') ?> / Loyang
                                </p>
                                <p class="product-stock"><?= $produk['jenis'] === 'Pre-Order'
                                    ? 'Kuota sesuai tanggal'
                                    : ($produk['stok'] > 0 ? 'Stok: ' . (int) $produk['stok'] . ' loyang' : 'Stok habis') ?></p>
                                <a class="product-link" href="<?= BASE_URL ?>/index.php?page=detail-produk&amp;id=<?= (int) $produk['id'] ?>">
                                    Lihat Detail <span aria-hidden="true">&rarr;</span>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>

                </div>
            </div>

        </div>
    </section>

    <section class="booking-promo">
        <div class="container booking-inner">

            <div class="booking-copy">
                <p class="booking-label">
                    Rencanakan hari spesial
                </p>

                <h2>Booking kue tanpa terburu-buru.</h2>

                <p class="booking-description">
                    Pesan minimal 5 hari sebelumnya.<br>
                    Pilih tanggal yang tersedia dan metode pengambilan.<br>
                    Untuk pengiriman, pembayaran dilakukan setelah<br class="desktop-break">
                    alamat dan ongkir dikonfirmasi admin.
                </p>
            </div>

            <a
                class="button button-light"
                href="<?= BASE_URL ?>/index.php?page=cara-pesan"
            >
                Lihat Cara Pesan <span aria-hidden="true">&rarr;</span>
            </a>

        </div>
    </section>

</main>
