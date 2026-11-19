(function () {
    "use strict";

    const form = document.querySelector("#register-form");

    if (!form) return;

    const nama = form.querySelector("#register-name");
    const email = form.querySelector("#register-email");
    const telepon = form.querySelector("#register-phone");
    const password = form.querySelector("#register-password");
    const konfirmasi = form.querySelector("#register-confirm");
    const tombol = form.querySelector("#register-submit");
    const pesan = form.querySelector("#register-feedback");

    function periksaNama() {
        nama.setCustomValidity(
            nama.value.trim() === ""
                ? "Silakan isi nama lengkap."
                : ""
        );
    }

    function periksaTelepon() {
        const nilai = telepon.value.trim();

        // Menerima angka, awalan +, spasi, kurung, dan tanda hubung.
        const karakterValid = /^\+?[\d\s()-]+$/.test(nilai);

        const angka = nilai.replace(/\D/g, "");

        // Nomor seluler Indonesia dengan awalan 08 atau 628.
        const nomorValid = /^(?:08|628)\d{8,11}$/.test(angka);

        telepon.setCustomValidity(
            karakterValid && nomorValid
                ? ""
                : "Masukkan nomor telepon yang valid, diawali 08 atau +628."
        );
    }

    function periksaPassword() {
        password.setCustomValidity(
            password.value.length < 8 || password.value.length > 72 || password.value.trim() === ""
                ? "Kata sandi harus berisi 8–72 karakter."
                : ""
        );

        konfirmasi.setCustomValidity(
            konfirmasi.value !== password.value
                ? "Konfirmasi kata sandi harus sama dengan kata sandi."
                : ""
        );
    }

    // Validasi ditangani melalui JavaScript agar pesan dapat disesuaikan.
    // reportValidity() tetap menampilkan petunjuk bawaan browser.
    form.noValidate = true;

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        nama.value = nama.value.trim();
        email.value = email.value.trim();
        telepon.value = telepon.value.trim();

        periksaNama();
        periksaTelepon();
        periksaPassword();

        if (!form.reportValidity()) return;

        tombol.disabled = true;
        tombol.textContent = 'Membuat akun...';
        HTMLFormElement.prototype.submit.call(form);
    });

    nama.addEventListener("input", periksaNama);
    telepon.addEventListener("input", periksaTelepon);
    password.addEventListener("input", periksaPassword);
    konfirmasi.addEventListener("input", periksaPassword);

    form.addEventListener("input", function () {
        pesan.hidden = true;
    });

    window.addEventListener('pageshow', () => {
        if (tombol.textContent.includes('...')) {
            tombol.disabled = false;
            tombol.textContent = 'Daftar';
        }
    });
})();