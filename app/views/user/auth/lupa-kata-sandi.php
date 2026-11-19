<?php
$nomor = preg_replace('/\D/', '', $data['profilToko']['whatsapp']);
if (str_starts_with($nomor, '08')) {
    $nomor = '62' . substr($nomor, 1);
} elseif (str_starts_with($nomor, '8')) {
    $nomor = '62' . $nomor;
}
$whatsapp = preg_match('/^628[0-9]{8,11}$/D', $nomor)
    ? 'https://wa.me/' . $nomor . '?text=' . rawurlencode('Halo Ayu Cake, saya perlu bantuan Super Admin untuk mengganti kata sandi akun pelanggan saya.')
    : null;
?>
<main class="auth-page">
    <div class="container auth-container">
        <nav class="auth-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/">Beranda</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Lupa Kata Sandi</span>
        </nav>
        <a class="auth-back" href="<?= BASE_URL ?>/index.php?page=login">← Kembali ke Masuk</a>
        <div class="auth-card">
            <aside class="auth-brand-panel" aria-label="Ayu Cake">
                <p class="auth-brand-label">AYU CAKE</p>
                <img class="auth-logo" src="<?= BASE_URL ?>/public/assets/images/LogoAyuCake.png" alt="Logo Ayu Cake" width="126" height="126">
            </aside>
            <section class="auth-form-panel" aria-labelledby="auth-help-title">
                <div class="auth-heading">
                    <h1 id="auth-help-title">Lupa Kata Sandi?</h1>
                    <p>Hubungi Super Admin melalui WhatsApp Ayu Cake. Siapkan alamat email yang terdaftar agar Super Admin dapat memeriksa akunmu dan membantu mengganti kata sandi.</p>
                </div>
                <?php if ($whatsapp !== null): ?>
                    <a class="auth-submit auth-help-action" href="<?= htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Hubungi Super Admin</a>
                <?php else: ?>
                    <a class="auth-submit auth-help-action" href="<?= BASE_URL ?>/index.php?page=kontak">Lihat Kontak Ayu Cake</a>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
