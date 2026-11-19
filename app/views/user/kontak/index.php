<?php
$aman = static function ($nilai): string {
    return htmlspecialchars(
        (string) $nilai,
        ENT_QUOTES,
        'UTF-8'
    );
};

$profil = $data['profil'];
$urlWhatsapp = $data['urlWhatsapp'];

$jamOperasional = trim(
    (string) $profil['jamOperasional']
);
?>

<main class="tk-page">

    <!-- Banner halaman kontak -->
    <section class="cp-hero" aria-labelledby="tk-judul">
        <div class="container cp-hero-inner">

            <nav class="cp-breadcrumb" aria-label="Breadcrumb">
                <a href="<?= BASE_URL ?>/">Beranda</a>
                <span aria-hidden="true">/</span>

                <span aria-current="page">
                    Tentang Kami &amp; Kontak
                </span>
            </nav>

            <div class="cp-hero-copy">
                <p class="cp-slogan">
                    Hangatnya kue,<br>
                    indahnya setiap cerita.
                </p>

                <h1 id="tk-judul">
                    Tentang <?= $aman($profil['nama']) ?>
                </h1>

                <p class="cp-hero-description">
                    Kenali Ayu Cake serta informasi
                    kontak dan lokasi toko.
                </p>
            </div>

        </div>
    </section>

    <div class="container tk-content">

        <section aria-labelledby="tk-profil-judul">

            <div class="tk-section-heading">
                <h2 id="tk-profil-judul">
                    Profil <?= $aman($profil['nama']) ?>
                </h2>

                <p>Baca informasi tentang Ayu Cake.</p>
            </div>

            <div class="tk-profile-card">
                <p class="tk-eyebrow">Tentang Ayu Cake</p>

                <h3><?= $aman($profil['slogan']) ?></h3>

                <div class="tk-profile-description">
                    <?php foreach ($profil['deskripsi'] as $paragraf): ?>
                        <p><?= $aman($paragraf) ?></p>
                    <?php endforeach; ?>
                </div>

                <p class="tk-profile-tags">
                    Kue basah
                    <span aria-hidden="true">•</span>
                    Bolu
                    <span aria-hidden="true">•</span>
                    Booking &amp; Ready Stock
                </p>
            </div>

        </section>

        <section
            class="tk-contact-section"
            id="hubungi"
            aria-labelledby="tk-kontak-judul"
        >
            <div class="tk-section-heading">
                <h2 id="tk-kontak-judul">
                    Ada yang ingin ditanyakan?
                </h2>

                <p>
                    Hubungi Ayu Cake untuk informasi produk,
                    pengambilan, atau pengiriman pesanan.
                </p>
            </div>

            <div class="tk-whatsapp-card">
                <div class="tk-whatsapp-copy">
                    <h3 class="tk-eyebrow">
                        WhatsApp Ayu Cake
                    </h3>

                    <p>Kirim pertanyaanmu melalui WhatsApp.</p>

                    <?php if ($urlWhatsapp !== null): ?>
                        <p class="tk-phone">
                            +<?= $aman($profil['whatsapp']) ?>
                        </p>
                    <?php else: ?>
                        <p class="tk-contact-status" id="tk-wa-status">
                            Nomor WhatsApp belum tersedia.
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($urlWhatsapp !== null): ?>
                    <a
                        class="tk-button tk-button-primary"
                        href="<?= $aman($urlWhatsapp) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Chat Ayu Cake melalui WhatsApp, membuka tab baru"
                    >
                        Chat melalui WhatsApp
                        <span aria-hidden="true">→</span>
                    </a>
                <?php else: ?>
                    <button
                        class="tk-button tk-button-primary"
                        type="button"
                        disabled
                        aria-describedby="tk-wa-status"
                    >
                        Chat melalui WhatsApp
                        <span aria-hidden="true">→</span>
                    </button>
                <?php endif; ?>
            </div>

            <div class="tk-info-grid">

                <article class="tk-info-card">
                    <h3>Alamat toko</h3>

                    <address>
                        <?= $aman($profil['alamat']) ?>
                    </address>

                    <p class="tk-info-note">
                        Untuk titik pengambilan yang lebih jelas,
                        silakan hubungi Ayu Cake melalui WhatsApp.
                    </p>
                </article>

                <article class="tk-info-card">
                    <h3>Jam operasional</h3>
                    <?php if ($profil['hariOperasional'] !== ''): ?><p><?= $aman($profil['hariOperasional']) ?></p><?php endif; ?>

                    <p class="tk-hours">
                        <?= $aman(
                            $jamOperasional !== ''
                                ? $jamOperasional
                                : 'Informasi jam operasional belum tersedia.'
                        ) ?>
                    </p>

                    <p class="tk-info-note">
                        Pastikan waktu pengambilan pesanan
                        sesuai dengan jam operasional toko.
                    </p>
                </article>

            </div>

            <aside class="tk-delivery-note">
                <h3>Area layanan Antar Ayu Cake</h3>

                <p>
                    Pengantaran mencakup Bukit Terap menuju Toboali dan sekitar Kecamatan Tukak Sadai.
                    Untuk alamat di luar jangkauan, pilih Ambil di Toko atau hubungi Ayu Cake untuk memastikan cakupan alamat.
                </p>
            </aside>

        </section>

        <section class="tk-cta" aria-labelledby="tk-cta-judul">
            <div>
                <h2 id="tk-cta-judul">
                    Ingin tahu cara memesan?
                </h2>

                <p>
                    Baca panduan pemesanan dan
                    pertanyaan yang sering ditanyakan.
                </p>
            </div>

            <a
                class="tk-button tk-button-light"
                href="<?= BASE_URL ?>/index.php?page=cara-pesan"
            >
                FAQ &amp; Cara Pesan
                <span aria-hidden="true">→</span>
            </a>
        </section>

    </div>
</main>
