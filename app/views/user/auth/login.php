<?php
$aman = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<main class="auth-page">
    <div class="container auth-container">

        <nav class="auth-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/">Beranda</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Masuk</span>
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
                aria-label="Selamat datang di Ayu Cake"
            >
                <p class="auth-brand-label">AYU CAKE</p>

                <img
                    class="auth-logo"
                    src="<?= BASE_URL ?>/public/assets/images/LogoAyuCake.png"
                    alt="Logo Ayu Cake"
                    width="126"
                    height="126"
                >

                <div class="auth-brand-message">
                    <h2>
                        Hangatnya kue,<br>
                        indahnya setiap cerita.
                    </h2>

                    <p>
                        Masuk untuk memesan kue favorit<br>
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
                aria-labelledby="auth-login-title"
            >
                <div class="auth-heading">
                    <h1 id="auth-login-title">
                        Selamat datang kembali
                    </h1>

                    <p>
                        Masuk ke akun Ayu Cake untuk melanjutkan.
                    </p>
                </div>

                <form
                    class="auth-form"
                    id="login-form"
                    action="<?= BASE_URL ?>/index.php?page=login"
                    method="post"
                >
                    <input type="hidden" name="token_auth" value="<?= $aman($data['token']) ?>">
                    <?php if ($data['error'] !== null): ?>
                        <p class="auth-recovery-message auth-recovery-error" role="alert" data-dismissible-notice><?= $aman($data['error']) ?></p>
                    <?php elseif ($data['pesan'] !== null): ?>
                        <p class="auth-recovery-message auth-recovery-success" role="status" data-dismissible-notice><?= $aman($data['pesan']) ?></p>
                    <?php endif; ?>
                    <div class="auth-field">
                        <label for="login-email">Email</label>

                        <input
                            id="login-email"
                            name="email"
                            type="email"
                            value="<?= $aman($data['email']) ?>"
                            maxlength="254"
                            placeholder="Masukkan alamat email"
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="login-password">Kata sandi</label>

                        <div class="auth-password-wrap">
                            <input
                                id="login-password"
                                name="password"
                                type="password"
                                maxlength="72"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                class="auth-password-toggle"
                                type="button"
                                data-password-toggle
                                aria-controls="login-password"
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

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    ></circle>

                                    <path
                                        class="auth-eye-slash"
                                        d="M3 3 21 21"
                                        hidden
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="auth-forgot">
                        <a
                            href="<?= BASE_URL ?>/index.php?page=lupa-kata-sandi"
                        >
                            Lupa kata sandi?
                        </a>
                    </div>

                    <button
                        class="auth-submit"
                        id="login-submit"
                        type="submit"
                    >
                        Masuk
                    </button>

                    <p
                        class="auth-feedback"
                        id="login-feedback"
                        role="status"
                        aria-live="polite"
                        hidden
                    ></p>

                    <p class="auth-switch">
                        <span>Belum punya akun?</span>

                        <a
                            href="<?= BASE_URL ?>/index.php?page=registrasi"
                        >
                            Daftar sekarang
                        </a>
                    </p>
                </form>

            </section>

        </div>
    </div>
</main>

<script
    src="<?= BASE_URL ?>/public/assets/js/auth.js?v=3"
    defer
></script>
