<?php
$aman = static function ($nilai): string {
    return htmlspecialchars(
        (string) $nilai,
        ENT_QUOTES,
        'UTF-8'
    );
};

$daftarFaq = $data['faq'] ?? [];
?>

<main class="cp-page">

    <section class="cp-hero" aria-labelledby="cp-judul">
        <div class="container cp-hero-inner">

            <nav class="cp-breadcrumb" aria-label="Breadcrumb">
                <a href="<?= BASE_URL ?>/">Beranda</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">FAQ &amp; Cara Pesan</span>
            </nav>

            <div class="cp-hero-copy">
                <p class="cp-slogan">
                    Hangatnya kue,<br>
                    indahnya setiap cerita.
                </p>

                <h1 id="cp-judul">Cara Pesan Ayu Cake</h1>

                <p class="cp-hero-description">
                    Panduan pemesanan dan jawaban untuk pertanyaanmu.
                </p>
            </div>

        </div>
    </section>

    <div class="container cp-content">

        <section
            class="cp-section"
            aria-labelledby="cp-pilihan-judul"
        >
            <div class="cp-section-heading">
                <h2 id="cp-pilihan-judul">
                    Mau pesan untuk kapan?
                </h2>

                <p>
                    Pilih Pesan Sekarang untuk stok tersedia,
                    atau Booking untuk tanggal pilihanmu.
                </p>
            </div>

            <div class="cp-order-options">

                <article class="cp-option-card">
                    <div class="cp-option-heading">
                        <span class="cp-number" aria-hidden="true">
                            01
                        </span>

                        <div>
                            <h3>Pesan Sekarang</h3>
                            <p class="cp-option-label">Ready Stock</p>
                        </div>
                    </div>

                    <p class="cp-option-description">
                        Pilih kue dari stok yang tersedia.
                        Ketersediaan diperiksa sebelum pesanan dibuat.
                    </p>
                </article>

                <article class="cp-option-card">
                    <div class="cp-option-heading">
                        <span class="cp-number" aria-hidden="true">
                            02
                        </span>

                        <div>
                            <h3>Booking</h3>
                            <p class="cp-option-label">Pre-Order</p>
                        </div>
                    </div>

                    <p class="cp-option-description">
                        Pilih tanggal minimal 5 hari setelah pemesanan.
                        Kapasitas Booking maksimal 50 loyang per hari
                        untuk seluruh jenis kue, sesuai ketersediaan tanggal.
                    </p>
                </article>

            </div>
        </section>

        <section
            class="cp-section"
            aria-labelledby="cp-langkah-judul"
        >
            <div class="cp-section-heading">
                <h2 id="cp-langkah-judul">
                    Cara memesan di Ayu Cake
                </h2>
            </div>

            <ol class="cp-steps">

                <li class="cp-step">
                    <span class="cp-number" aria-hidden="true">1</span>
                    <h3>Pilih kue</h3>

                    <p>
                        Buka katalog, pilih produk,
                        lalu tentukan jumlah pesanan.
                    </p>
                </li>

                <li class="cp-step">
                    <span class="cp-number" aria-hidden="true">2</span>
                    <h3>Pilih cara pesan</h3>

                    <p>
                        Pilih Pesan Sekarang atau Booking.
                        Tentukan tanggal untuk Booking.
                    </p>
                </li>

                <li class="cp-step">
                    <span class="cp-number" aria-hidden="true">3</span>
                    <h3>Buat pesanan</h3>

                    <p>
                        Pilih ambil di toko atau pengiriman,
                        lengkapi informasi, lalu buat pesanan.
                    </p>
                </li>

                <li class="cp-step">
                    <span class="cp-number" aria-hidden="true">4</span>
                    <h3>Bayar &amp; unggah bukti</h3>

                    <p>
                        Saat status Menunggu Pembayaran,
                        bayar total akhir dan unggah bukti.
                    </p>
                </li>

            </ol>

            <p class="cp-note cp-cart-note">
                Ingin membeli beberapa produk?
                Tambahkan ke Keranjang, lalu lanjutkan ke checkout.
            </p>
        </section>

        <section
            class="cp-payment-card"
            aria-labelledby="cp-pembayaran-judul"
        >
            <h2 id="cp-pembayaran-judul">
                Kapan saya bisa membayar?
            </h2>

            <dl class="cp-payment-list">
                <div class="cp-payment-row">
                    <dt>Ambil di toko</dt>
                    <dd>
                        Pembayaran tersedia setelah pesanan dibuat.
                    </dd>
                </div>

                <div class="cp-payment-row">
                    <dt>Antar Ayu Cake</dt>
                    <dd>
                        Admin memeriksa alamat, menghitung ongkir, lalu mengonfirmasi total sebelum pelanggan bisa membayar.
                    </dd>
                </div>

            </dl>

            <p class="cp-payment-note">
                Bayar saat status
                <strong>Menunggu Pembayaran</strong>.
                Ikuti batas waktu pembayaran yang tertera
                pada detail pesanan.
                Unggah bukti pembayaran, lalu admin memverifikasinya
                sebelum pesanan diproses.
            </p>
        </section>

        <p class="cp-note cp-delivery-note">
            Antar Ayu Cake melayani area Bukit Terap menuju Toboali dan sekitar Kecamatan Tukak Sadai.
            Untuk alamat di luar jangkauan, pilih Ambil di Toko.
        </p>

        <section
            class="cp-section cp-faq-section"
            id="faq"
            aria-labelledby="cp-faq-judul"
        >
            <div class="cp-section-heading">
                <h2 id="cp-faq-judul">
                    Pertanyaan yang sering ditanyakan
                </h2>

                <p>
                    Temukan informasi pemesanan, pengiriman,
                    dan pembayaran di sini.
                </p>
            </div>

            <div class="cp-faq-list">
                <?php foreach ($daftarFaq as $faq): ?>
                    <details
                        class="cp-faq-item"
                        <?= !empty($faq['terbuka']) ? 'open' : '' ?>
                    >
                        <summary class="cp-faq-question">
                            <span>
                                <?= $aman($faq['pertanyaan']) ?>
                            </span>

                            <span
                                class="cp-faq-icon"
                                aria-hidden="true"
                            ></span>
                        </summary>

                        <div class="cp-faq-answer">
                            <p><?= nl2br($aman($faq['jawaban'])) ?></p>
                        </div>
                    </details>
                <?php endforeach; ?>
                <?php if (!$daftarFaq): ?><p>Belum ada pertanyaan umum yang ditampilkan. Silakan hubungi toko melalui halaman Kontak.</p><?php endif; ?>
            </div>
        </section>

        <section class="cp-cta" aria-labelledby="cp-cta-judul">
            <div>
                <h2 id="cp-cta-judul">
                    Sudah siap memilih kue?
                </h2>

                <p>Temukan pilihan kue untuk momenmu.</p>
            </div>

            <a
                class="cp-catalog-button"
                href="<?= BASE_URL ?>/index.php?page=katalog"
            >
                Lihat Katalog
                <span aria-hidden="true">→</span>
            </a>
        </section>

    </div>
</main>
