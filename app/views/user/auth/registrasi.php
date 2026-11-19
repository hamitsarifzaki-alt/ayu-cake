<?php
$aman = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<main class="auth-page auth-register-page">
    <div class="container auth-container">

        <nav class="auth-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/">Beranda</a>
            <span aria-hidden="true">/</span>

            <a href="<?= BASE_URL ?>/index.php?page=login">
                Masuk
            </a>

            <span aria-hidden="true">/</span>
            <span aria-current="page">Daftar</span>
        </nav>

        <a
            class="auth-back"
            href="<?= BASE_URL ?>/index.php?page=katalog"
        >
            <span aria-hidden="true">←</span>
            Kembali melihat katalog
        </a>

        <div class="auth-card">

            <aside
                class="auth-brand-panel"
                aria-label="Bergabung dengan Ayu Cake"
            >
                <p class="auth-brand-label">AYU CAKE</p>

                <img
                    class="auth-logo"
                    src="<?= BASE_URL ?>/public/assets/images/LogoAyuCake.png"
                    alt="Logo Ayu Cake"
                    width="146"
                    height="146"
                >

                <div class="auth-brand-message">
                    <h2>
                        Awali cerita manismu<br>
                        bersama Ayu Cake.
                    </h2>

                    <p>
                        Buat akun untuk memesan kue favorit
                        dan mengikuti perkembangan pesananmu.
                    </p>
                </div>

                <p class="auth-brand-tagline">
                    <span aria-hidden="true"></span>
                    Kue untuk setiap cerita.
                </p>
            </aside>

            <section
                class="auth-form-panel"
                aria-labelledby="register-title"
            >
                <div class="auth-heading">
                    <h1 id="register-title">Buat akun Ayu Cake</h1>

                    <p>Lengkapi data berikut untuk mendaftar.</p>

                    <p class="auth-required-note" id="register-note">
                        Semua kolom wajib diisi.
                    </p>
                </div>

                <form
                    class="auth-form"
                    id="register-form"
                    action="<?= BASE_URL ?>/index.php?page=registrasi"
                    method="post"
                    aria-describedby="register-note"
                >
                    <input type="hidden" name="token_auth" value="<?= $aman($data['token']) ?>">
                    <?php if ($data['error'] !== null): ?>
                        <p class="auth-recovery-message auth-recovery-error" role="alert" data-dismissible-notice><?= $aman($data['error']) ?></p>
                    <?php endif; ?>
                    <div class="auth-field">
                        <label for="register-name">Nama lengkap</label>

                        <input
                            id="register-name"
                            name="nama"
                            value="<?= $aman($data['isian']['nama']) ?>"
                            maxlength="120"
                            type="text"
                            placeholder="Masukkan nama lengkap"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="register-email">Email</label>

                        <input
                            id="register-email"
                            name="email"
                            value="<?= $aman($data['isian']['email']) ?>"
                            maxlength="254"
                            type="email"
                            placeholder="Masukkan alamat email"
                            autocomplete="email"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="register-phone">Nomor telepon</label>

                        <input
                            id="register-phone"
                            name="telepon"
                            value="<?= $aman($data['isian']['telepon']) ?>"
                            maxlength="25"
                            type="tel"
                            placeholder="Masukkan nomor telepon"
                            autocomplete="tel"
                            inputmode="tel"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="register-password">Kata sandi</label>

                        <div class="auth-password-wrap">
                            <input
                                id="register-password"
                                name="password"
                                type="password"
                                placeholder="Buat kata sandi"
                                autocomplete="new-password"
                                minlength="8"
                                maxlength="72"
                                aria-describedby="register-password-hint"
                                required
                            >

                            <button
                                class="auth-password-toggle"
                                type="button"
                                data-password-toggle
                                aria-controls="register-password"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                                hidden
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    ></path>

                                    <circle cx="12" cy="12" r="3"></circle>

                                    <path
                                        class="auth-eye-slash"
                                        d="M3 3 21 21"
                                        hidden
                                    ></path>
                                </svg>
                            </button>
                        </div>

                        <p
                            class="auth-field-hint"
                            id="register-password-hint"
                        >
                            Gunakan 8–72 karakter.
                        </p>
                    </div>

                    <div class="auth-field">
                        <label for="register-confirm">
                            Konfirmasi kata sandi
                        </label>

                        <div class="auth-password-wrap">
                            <input
                                id="register-confirm"
                                name="password_confirmation"
                                maxlength="72"
                                minlength="8"
                                type="password"
                                placeholder="Konfirmasi kata sandi"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                class="auth-password-toggle"
                                type="button"
                                data-password-toggle
                                aria-controls="register-confirm"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                                hidden
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    ></path>

                                    <circle cx="12" cy="12" r="3"></circle>

                                    <path
                                        class="auth-eye-slash"
                                        d="M3 3 21 21"
                                        hidden
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button
                        class="auth-submit"
                        id="register-submit"
                        type="submit"
                    >
                        Daftar
                    </button>

                    <p
                        class="auth-feedback"
                        id="register-feedback"
                        role="status"
                        aria-live="polite"
                        hidden
                    ></p>

                    <p class="auth-switch">
                        <span>Sudah punya akun?</span>

                        <a href="<?= BASE_URL ?>/index.php?page=login">
                            Masuk di sini
                        </a>
                    </p>
                </form>
            </section>

        </div>
        
    </div>
</main>

<script
    src="<?= BASE_URL ?>/public/assets/js/auth.js?v=2"
    defer
></script>

<script
    src="<?= BASE_URL ?>/public/assets/js/registrasi.js?v=2"
    defer
></script>
