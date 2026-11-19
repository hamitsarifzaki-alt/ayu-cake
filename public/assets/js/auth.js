(function () {
    "use strict";

    const halaman = document.querySelector(".auth-page");

    if (!halaman) return;

    // Tombol tampilkan/sembunyikan kata sandi.
    halaman.querySelectorAll("[data-password-toggle]").forEach(
        function (tombol) {
            const inputId = tombol.getAttribute("aria-controls");
            const input = document.getElementById(inputId);

            if (!input) return;

            tombol.hidden = false;

            tombol.addEventListener("click", function () {
                const tampilkan = input.type === "password";

                input.type = tampilkan ? "text" : "password";

                tombol.setAttribute(
                    "aria-pressed",
                    String(tampilkan)
                );

                tombol.setAttribute(
                    "aria-label",
                    tampilkan
                        ? "Sembunyikan kata sandi"
                        : "Tampilkan kata sandi"
                );

                const garisMata = tombol.querySelector(
                    ".auth-eye-slash"
                );

                if (garisMata) {
                    garisMata.toggleAttribute("hidden", !tampilkan);
                }
            });
        }
    );

    const form = halaman.querySelector('#login-form');
    if (!form) return;
    form.addEventListener('submit', event => {
        const email = document.getElementById('login-email');
        email.value = email.value.trim();
        if (!form.reportValidity()) { event.preventDefault(); return; }
        const button = document.getElementById('login-submit');
        button.disabled = true;
        button.textContent = 'Memeriksa akun...';
    });
    window.addEventListener('pageshow', event => {
        if (event.persisted) { window.location.reload(); return; }
        const button = document.getElementById('login-submit');
        if (button.textContent.includes('...')) {
            button.disabled = false;
            button.textContent = 'Masuk';
        }
    });
})();
