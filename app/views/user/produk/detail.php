<?php

$produk = $data['produk'];

$aman = static function ($nilai): string {
    return htmlspecialchars(
        (string) $nilai,
        ENT_QUOTES,
        'UTF-8'
    );
};

?>

<main class="dp-page">
    <div class="container dp-container">

        <nav class="dp-breadcrumb" aria-label="Jejak halaman">
            <a href="<?= BASE_URL ?>/index.php?page=home">
                Beranda
            </a>

            <span aria-hidden="true">/</span>

            <a href="<?= BASE_URL ?>/index.php?page=katalog">
                Katalog
            </a>

            <span aria-hidden="true">/</span>

            <span aria-current="page">Detail Produk</span>
        </nav>

        <a
            class="dp-back"
            href="<?= BASE_URL ?>/index.php?page=katalog"
        >
            ← Kembali ke katalog
        </a>

        <?php if ($produk === null): ?>

            <section class="dp-empty">
                <h1>Produk tidak ditemukan</h1>

                <p>
                    Silakan kembali ke katalog untuk memilih produk.
                </p>
            </section>

        <?php else: ?>

            <?php

            $namaKategori = $data['kategoriProduk'][$produk['kategori']] ?? $produk['kategori'];

            ?>

            <section
    class="dp-product"
    data-sudah-masuk="<?= is_array($_SESSION['app_pelanggan'] ?? null) ? 'true' : 'false' ?>"
    data-id="<?= (int) $produk['id'] ?>"
    data-stok="<?= (int) $produk['stok'] ?>"
    data-url="<?= $aman(BASE_URL . '/index.php') ?>"
    aria-labelledby="dp-nama-produk"
>
                <div class="dp-photo">
                    <img
                        src="<?= BASE_URL ?>/public/assets/images/<?= $aman($produk['gambar']) ?>"
                        alt="<?= $aman($produk['nama']) ?>"
                    >
                </div>

                <div class="dp-info">
                    <p class="dp-category">
                        <?= $aman($namaKategori) ?>
                    </p>

                    <h1 id="dp-nama-produk">
                        <?= $aman($produk['nama']) ?>
                    </h1>

                    <p class="dp-price">
                        Rp <?= number_format($produk['harga'], 0, ',', '.') ?>
                        <span>/ loyang</span>
                    </p>

                    <p class="dp-stock">
                        <?php if ($produk['stok'] > 0): ?>

                            Stok siap:
                            <?= (int) $produk['stok'] ?> loyang

                        <?php else: ?>

                            Stok siap saat ini habis

                        <?php endif; ?>
                    </p>

                    <div class="dp-quantity">
                        <label for="dp-jumlah">
                            Jumlah loyang
                        </label>

                        <div class="dp-quantity-control">
                            <button
                                type="button"
                                id="dp-kurangi"
                                aria-label="Kurangi jumlah loyang"
                            >
                                −
                            </button>

                            <input
                                type="number"
                                id="dp-jumlah"
                                name="jumlah"
                                value="1"
                                min="1"
                                step="1"
                                inputmode="numeric"
                                aria-describedby="dp-pesan-jumlah"
                                required
                            >

                            <button
                                type="button"
                                id="dp-tambah"
                                aria-label="Tambah jumlah loyang"
                            >
                                +
                            </button>
                        </div>

                        <p
                            id="dp-pesan-jumlah"
                            class="dp-quantity-message"
                            aria-live="polite"
                        ></p>
                    </div>

                    <div class="dp-main-actions">
                        <button
                            type="button"
                            class="dp-action dp-action-primary"
                            id="dp-pesan-sekarang"
                            data-dp-action="pesan"
                            <?= $produk['stok'] < 1 ? 'disabled' : '' ?>
                        >
                            <strong>Pesan Sekarang</strong>
                            <span>Ready Stock</span>
                        </button>

                        <button
                            type="button"
                            class="dp-action dp-action-booking"
                            id="dp-booking"
                            data-dp-action="booking"
                        >
                            <strong>Booking</strong>
                            <span>Pre-Order Minimal H+5</span>
                        </button>
                    </div>

                    <div class="dp-secondary-actions">
                        <?php

?>

<button
    type="button"
    id="dp-tambah-keranjang"
    data-cart-add
    data-id="<?= (int) $produk['id'] ?>"
    data-url="<?= $aman(
        BASE_URL . '/index.php?page=keranjang-tambah'
    ) ?>"
    data-token="<?= $aman(
        $_SESSION['app_keranjang_token']
    ) ?>"
>
    + Tambah ke Keranjang
</button>

                    </div>

                    <p
                        class="dp-notice"
                        id="dp-pemberitahuan"
                        role="status"
                        hidden
                    ></p>
                </div>
            </section>

            <section
                class="dp-description"
                aria-labelledby="dp-judul-deskripsi"
            >
                <h2 id="dp-judul-deskripsi">
                    Deskripsi Produk
                </h2>

                <p>
                    <?= $aman($produk['deskripsi']) ?>
                </p>
            </section>

        <?php endif; ?>

    </div>
</main>

<script
    src="<?= BASE_URL ?>/public/assets/js/detail-produk.js?v=5"
    defer
></script>

<script
    src="<?= BASE_URL ?>/public/assets/js/tambah-keranjang.js?v=3"
    defer
></script>
