(function () {
    'use strict';

    const form = document.getElementById('pay-upload-form');

    if (!form) return;

    const input = document.getElementById('pay-file');
    const nama = document.getElementById('pay-file-name');
    const error = document.getElementById('pay-file-error');
    const preview = document.getElementById('pay-preview');
    const wadahPreview = document.getElementById('pay-preview-wrap');
    const tombol = document.getElementById('pay-submit');

    let urlPreview = null;
    let pilihan = 0;
    let gambarValid = false;

    function bersihkanPreview() {
        preview.removeAttribute('src');
        wadahPreview.hidden = true;

        if (urlPreview) {
            URL.revokeObjectURL(urlPreview);
            urlPreview = null;
        }
    }

    function tampilkanError(pesan) {
        error.textContent = pesan;
        error.hidden = false;
        gambarValid = false;
        tombol.disabled = true;
    }

    input.addEventListener('change', function () {
        pilihan += 1;
        const pilihanSaatIni = pilihan;

        bersihkanPreview();
        error.hidden = true;
        gambarValid = false;
        tombol.disabled = true;

        const berkas = input.files[0];

        if (!berkas) {
            nama.textContent = 'Belum ada berkas dipilih';
            return;
        }

        nama.textContent = berkas.name
            + ' • '
            + (berkas.size / 1024).toFixed(0)
            + ' KB';

        if (!['image/jpeg', 'image/png'].includes(berkas.type)) {
            tampilkanError('Pilih gambar berformat JPG atau PNG.');
            return;
        }

        if (berkas.size === 0 || berkas.size > 2 * 1024 * 1024) {
            tampilkanError('Ukuran gambar harus lebih dari 0 dan maksimal 2 MB.');
            return;
        }

        urlPreview = URL.createObjectURL(berkas);
        const pemeriksa = new Image();
        const urlSaatIni = urlPreview;

        pemeriksa.onload = function () {
            if (pilihanSaatIni !== pilihan) return;

            preview.src = urlSaatIni;
            wadahPreview.hidden = false;
            gambarValid = true;
            tombol.disabled = false;
        };

        pemeriksa.onerror = function () {
            if (pilihanSaatIni !== pilihan) return;

            bersihkanPreview();
            tampilkanError('Gambar tidak dapat dibaca. Pilih gambar lain.');
        };

        pemeriksa.src = urlSaatIni;
    });

    form.addEventListener('submit', function (event) {
        if (!gambarValid || !input.files.length) {
            event.preventDefault();
            tampilkanError('Pilih gambar bukti pembayaran yang valid.');
            return;
        }

        tombol.disabled = true;
        tombol.textContent = 'Mengirim bukti pembayaran...';
    });

    window.addEventListener('pageshow', function () {
        tombol.textContent = 'Kirim Bukti Pembayaran';
        tombol.disabled = !gambarValid;
    });
})();