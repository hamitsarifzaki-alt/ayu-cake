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

$ringkasan = $data['ringkasan'];
$sudahMasuk = $data['sudahMasuk'];
$items = $ringkasan['items'];
?>

<main class="kb-page">
    <div class="container kb-container">

        <nav class="kb-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/">BERANDA</a>
            <span>/</span>
            <span>KERANJANG</span>
        </nav>

        <a
            class="kb-back"
            href="<?= BASE_URL ?>/index.php?page=katalog"
        >
            ← Kembali melihat katalog
        </a>

        <section class="kb-panel" aria-labelledby="kb-title">

            <div class="kb-heading">
                <div>
                    <h1 id="kb-title">Keranjang Belanja</h1>

                    <p>
                        Periksa produk dan jumlahnya sebelum
                        melanjutkan pesanan.
                    </p>
                </div>

                <span class="kb-badge" id="kb-badge">
                    <?= $ringkasan['jumlahProduk'] ?> produk
                    • <?= $ringkasan['jumlahLoyang'] ?> loyang
                </span>
            </div>

            <div class="kb-sync" role="status" aria-live="polite">
                <span id="kb-sync-status"></span>
                <button type="button" class="kb-retry" id="kb-retry" hidden>Coba simpan lagi</button>
            </div>

            <p
                class="kb-message"
                id="kb-message"
                role="status"
                hidden
            ></p>

            <div
                class="kb-empty"
                id="kb-empty"
                <?= $items !== [] ? 'hidden' : '' ?>
            >
                <div class="kb-empty-symbol" aria-hidden="true">
                    🛒
                </div>

                <h2>Keranjangmu masih kosong</h2>

                <p>
                    <?= $sudahMasuk
                        ? 'Tambahkan kue favoritmu untuk mulai memesan.'
                        : 'Masuk untuk menyimpan dan mengelola keranjang belanja.' ?>
                </p>

                <a
                    class="kb-primary"
                    href="<?= BASE_URL ?>/index.php?page=<?= $sudahMasuk ? 'katalog' : 'login' ?>"
                >
                    <?= $sudahMasuk ? 'Lihat Katalog' : 'Masuk' ?>
                </a>
            </div>

            <div
                id="kb-filled"
                <?= $items === [] ? 'hidden' : '' ?>
            >

                <div class="kb-list">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $kategori = $item['kategori'] === 'kue-basah'
                            ? 'Kue Basah'
                            : ($item['kategori'] === 'bolu'
                                ? 'Bolu'
                                : $item['kategori']);
                        ?>

                        <article
                            class="kb-item"
                            data-cart-item
                            data-id="<?= (int) $item['id'] ?>"
                        >
                            <a
                                class="kb-photo-link"
                                href="<?= BASE_URL ?>/index.php?page=detail-produk&id=<?= (int) $item['id'] ?>"
                            >
                                <?php if ($item['gambar'] !== ''): ?>
                                    <img
                                        src="<?= $aman(
                                            BASE_URL
                                            . '/public/assets/images/'
                                            . $item['gambar']
                                        ) ?>"
                                        alt="<?= $aman($item['nama']) ?>"
                                    >
                                <?php else: ?>
                                    <span>Foto produk</span>
                                <?php endif; ?>
                            </a>

                            <div class="kb-product-info">
                                <p class="kb-category">
                                    <?= $aman($kategori) ?>
                                </p>

                                <h2>
                                    <a
                                        href="<?= BASE_URL ?>/index.php?page=detail-produk&id=<?= (int) $item['id'] ?>"
                                    >
                                        <?= $aman($item['nama']) ?>
                                    </a>
                                </h2>

                                <p class="kb-unit-price">
                                    <?= $rupiah($item['harga']) ?>
                                    / Loyang
                                </p>

                                <p class="kb-stock" data-cart-stock>
                                    Stok siap: <?= max(0, (int) $item['stok']) ?> loyang
                                </p>

                                <button
                                    class="kb-remove"
                                    type="button"
                                    data-cart-remove
                                    aria-label="<?= $aman(
                                        'Hapus ' . $item['nama']
                                    ) ?>"
                                >
                                    Hapus
                                </button>
                            </div>

                            <div class="kb-quantity">
                                <span>Jumlah</span>

                                <div class="kb-stepper">
                                    <button
                                        type="button"
                                        data-cart-minus
                                        aria-label="Kurangi jumlah"
                                        <?= $item['jumlah'] <= 1 ? 'disabled' : '' ?>
                                    >
                                        −
                                    </button>

                                    <output data-cart-quantity>
                                        <?= (int) $item['jumlah'] ?>
                                    </output>

                                    <button
                                        type="button"
                                        data-cart-plus
                                        aria-label="Tambah jumlah"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <div class="kb-item-total">
                                <span>Subtotal</span>

                                <strong data-cart-subtotal>
                                    <?= $rupiah($item['subtotal']) ?>
                                </strong>
                            </div>

                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="kb-summary">
                    <h2>Ringkasan Belanja</h2>

                    <div class="kb-summary-row">
                        <span>Jumlah produk</span>
                        <strong id="kb-product-count">
                            <?= $ringkasan['jumlahProduk'] ?> produk
                        </strong>
                    </div>

                    <div class="kb-summary-row">
                        <span>Total jumlah</span>
                        <strong id="kb-quantity-count">
                            <?= $ringkasan['jumlahLoyang'] ?> loyang
                        </strong>
                    </div>

                    <div class="kb-summary-total">
                        <strong>Subtotal</strong>

                        <strong id="kb-total">
                            <?= $rupiah($ringkasan['subtotal']) ?>
                        </strong>
                    </div>

                    <p class="kb-help">
                        Jika memilih pengiriman, admin memeriksa alamat
                        dan menghitung ongkir sebelum pembayaran.
                    </p>

                    <div class="kb-checkout-actions">
                        <button
                            class="kb-primary"
                            type="button"
                            data-cart-checkout="pesan-sekarang"
                        >
                            Pesan Sekarang <span>→</span>
                        </button>

                        <button
                            class="kb-secondary"
                            type="button"
                            data-cart-checkout="booking"
                        >
                            Booking <span>→</span>
                        </button>
                    </div>

                    <p class="kb-help">
                        Stok siap berlaku untuk Pesan Sekarang.
                        Booking untuk tanggal minimal 5 hari
                        dari hari pemesanan.
                    </p>
                </div>

            </div>

        </section>

        <p class="kb-footnote">
            Jumlah produk dapat diubah. Ketersediaan akan diperiksa
            sebelum pesanan dibuat.
        </p>

    </div>
</main>

<div class="wl-toast" id="kb-toast" role="status" aria-live="polite" aria-atomic="true" hidden></div>

<script type="application/json" id="kb-config">
<?= json_encode([
    'urlUbah' => (parse_url(BASE_URL, PHP_URL_PATH) ?: '') . '/index.php?page=keranjang-ubah',
    'urlDasar' => (parse_url(BASE_URL, PHP_URL_PATH) ?: '') . '/index.php',
    'token' => $data['tokenKeranjang'],
    'ringkasan' => $ringkasan,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
</script>

<script
    src="<?= BASE_URL ?>/public/assets/js/keranjang.js?v=8"
    defer
></script>
