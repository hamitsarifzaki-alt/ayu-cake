(function () {
    'use strict';

    const form = document.getElementById('pesan-sekarang-form');
    const configElement = document.getElementById('pesan-sekarang-config');

    if (!form || !configElement) return;

    const config = JSON.parse(configElement.textContent);

    const el = (id) => document.getElementById(id);

    const uang = (nilai) => {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(nilai);
    };

    function tampilkanLangkah(kirim) {
        const daftar = kirim
            ? [
                'Isi pesanan',
                'Konfirmasi ongkir',
                'Pembayaran',
                'Verifikasi pembayaran'
            ]
            : [
                'Isi pesanan',
                'Pembayaran',
                'Verifikasi pembayaran'
            ];

        const wadah = el('co-langkah');
        wadah.replaceChildren();

        daftar.forEach(function (teks, index) {
            const item = document.createElement('li');
            const nomor = document.createElement('span');

            nomor.textContent = index + 1;

            item.append(
                nomor,
                document.createTextNode(teks)
            );

            if (index === 0) {
                item.className = 'is-current';
                item.setAttribute('aria-current', 'step');
            }

            wadah.appendChild(item);
        });
    }

    function perbaruiPengiriman() {
        const kirim = form.querySelector(
            '[name="penerimaan"]:checked'
        ).value === 'kirim';

        // Field pengiriman hanya aktif jika memilih kirim ke alamat.
        el('co-info-toko').hidden = kirim;
        el('co-pengiriman').hidden = !kirim;
        el('co-pengiriman').disabled = !kirim;

        el('co-hasil-validasi').hidden = true;

        tampilkanLangkah(kirim);

        if (!kirim) {
            el('co-label-ongkir').textContent =
                'Ongkir • Ambil di Toko';

            el('co-nilai-ongkir').textContent = uang(0);

            el('co-label-total').textContent =
                'Total pembayaran';

            el('co-nilai-total').textContent =
                uang(config.subtotal);

            el('co-petunjuk-bayar').textContent =
                'Petunjuk pembayaran ditampilkan setelah pesanan dibuat.';

            el('co-status-info').textContent =
                'Status setelah dibuat: Menunggu Pembayaran. '
                + 'Selesaikan pembayaran dalam 1 hari setelah pesanan '
                + 'dibuat, lalu unggah bukti pembayaran.';

            return;
        }

        el('co-petunjuk-bayar').textContent =
            'Pembayaran dilakukan setelah admin mengonfirmasi ongkir '
            + 'dan total akhir. Batas pembayaran 1 hari sejak konfirmasi.';

        el('co-label-ongkir').textContent = 'Ongkir • Antar Ayu Cake';
        el('co-nilai-ongkir').textContent = 'Dihitung admin';
        el('co-label-total').textContent = 'Total pembayaran';
        el('co-nilai-total').textContent = 'Menunggu konfirmasi ongkir';
        el('co-info-ongkir-judul').textContent = 'Admin memeriksa alamat penerima';
        el('co-info-ongkir-isi').textContent =
            'Admin menghitung ongkir setelah memeriksa alamat. Jika alamat berada di luar area, '
            + 'pilih Ambil di Toko.';
        el('co-status-info').textContent =
            'Status setelah dibuat: Menunggu Konfirmasi Ongkir. Kamu belum perlu membayar.';
    }

    form.querySelectorAll(
        '[name="penerimaan"]'
    ).forEach(function (input) {
        input.addEventListener('change', perbaruiPengiriman);
    });

    const namaInput = form.elements.namedItem('nama_penerima');
    const teleponInput = form.elements.namedItem('telepon');
    const alamatInput = form.elements.namedItem('alamat');

    function validasiNama() {
        namaInput.setCustomValidity(
            [...namaInput.value.trim()].length >= 3
                && /^\p{L}+(?: +\p{L}+)*$/u.test(namaInput.value.trim())
                ? ''
                : 'Nama penerima minimal 3 karakter dan hanya boleh berisi huruf serta spasi.'
        );
    }

    function validasiTelepon() {
        const nilai = teleponInput.value.trim();
        const valid = /^08[0-9]{8,13}$/.test(nilai);

        teleponInput.setCustomValidity(
            valid
                ? ''
                : 'Nomor telepon harus berisi 10–15 angka dan diawali 08.'
        );
    }

    function validasiAlamat() {
        const kirim = form.querySelector(
            '[name="penerimaan"]:checked'
        ).value === 'kirim';

        alamatInput.setCustomValidity(
            !kirim || [...alamatInput.value.trim()].length >= 10
                ? ''
                : 'Alamat pengiriman minimal 10 karakter.'
        );
    }

    namaInput.addEventListener('input', validasiNama);
    teleponInput.addEventListener('input', validasiTelepon);
    alamatInput.addEventListener('input', validasiAlamat);

    form.addEventListener('input', function () {
        el('co-hasil-validasi').hidden = true;
    });

    form.addEventListener('change', function () {
        el('co-hasil-validasi').hidden = true;
    });

    form.addEventListener('submit', function(event) {
    event.preventDefault();

    validasiNama();
    validasiTelepon();
    validasiAlamat();

    if (!form.reportValidity()) {
        return;
    }

    const tombol = form.querySelector('button[type="submit"]');

    if (tombol) {
        tombol.disabled = true;
        tombol.textContent = 'Membuat pesanan...';
    }

    HTMLFormElement.prototype.submit.call(form);
});

    perbaruiPengiriman();
})();
