(function () {
    'use strict';

    const form = document.getElementById('booking-form');
    const configElement = document.getElementById('booking-config');

    if (!form || !configElement) return;

    const config = JSON.parse(configElement.textContent);
    const el = (id) => document.getElementById(id);

    const uang = (nilai) => 'Rp' + new Intl.NumberFormat('id-ID')
        .format(nilai);

    // Gunakan tanggal dari PHP dengan zona waktu Asia/Jakarta.
    // UTC di sini hanya untuk perhitungan tanggal kalender.
    function bacaTanggal(teks) {
        const [tahun, bulan, hari] = teks.split('-').map(Number);
        return new Date(Date.UTC(tahun, bulan - 1, hari));
    }

    function kodeTanggal(tanggal) {
        return [
            tanggal.getUTCFullYear(),
            String(tanggal.getUTCMonth() + 1).padStart(2, '0'),
            String(tanggal.getUTCDate()).padStart(2, '0')
        ].join('-');
    }

    function formatTanggal(teks, lengkap = false) {
        const opsi = {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            timeZone: 'UTC'
        };

        if (lengkap) opsi.weekday = 'long';

        return new Intl.DateTimeFormat('id-ID', opsi)
            .format(bacaTanggal(teks));
    }

    const tanggalMinimal = bacaTanggal(config.tanggalMinimal);

    let bulanTampil = new Date(Date.UTC(
        tanggalMinimal.getUTCFullYear(),
        tanggalMinimal.getUTCMonth(),
        1
    ));

    let tanggalDipilih = '';

    function dataTanggal(kode) {
        const data = config.kalender[kode] || {
            kapasitas: 50,
            terisi: 0,
            ditutup: false
        };

        return {
            ...data,
            sisa: Math.max(0, data.kapasitas - data.terisi)
        };
    }

    function alasanTidakTersedia(kode) {
        const data = dataTanggal(kode);

        if (kode < config.tanggalMinimal) return 'Belum H+5';
        if (data.ditutup) return 'Ditutup';
        if (data.sisa === 0) return 'Penuh';
        if (data.sisa < config.jumlah) return 'Kuota kurang';

        return '';
    }

    function pilihTanggal(kode) {
        if (alasanTidakTersedia(kode)) return;

        tanggalDipilih = kode;
        el('co-tanggal').value = kode;

        const data = dataTanggal(kode);

        el('co-tanggal-terpilih').textContent =
            formatTanggal(kode, true);

        el('co-sisa-kuota').textContent =
            'Sisa ' + data.sisa + ' loyang';

        el('co-progress').max = data.kapasitas;
        el('co-progress').value = data.terisi;

        el('co-kuota-terisi').textContent =
            data.terisi + ' dari ' + data.kapasitas + ' loyang terisi';

        el('co-ringkasan-tanggal').textContent =
            'BOOKING • ' + formatTanggal(kode);

        el('co-error-tanggal').hidden = true;
        el('co-hasil-validasi').hidden = true;

        tampilkanKalender();

        const tombolTerpilih = el('co-hari').querySelector(
            '[data-tanggal="' + kode + '"]'
        );

        if (tombolTerpilih) {
            tombolTerpilih.focus({ preventScroll: true });
        }
    }

    function tampilkanKalender() {
        const tahun = bulanTampil.getUTCFullYear();
        const bulan = bulanTampil.getUTCMonth();

        el('co-bulan').textContent = new Intl.DateTimeFormat('id-ID', {
            month: 'long',
            year: 'numeric',
            timeZone: 'UTC'
        }).format(bulanTampil);

        const wadah = el('co-hari');
        wadah.replaceChildren();

        // Kalender dimulai dari Senin.
        const posisiAwal = (bulanTampil.getUTCDay() + 6) % 7;

        for (let i = 0; i < posisiAwal; i++) {
            const ruang = document.createElement('span');
            ruang.setAttribute('aria-hidden', 'true');
            wadah.appendChild(ruang);
        }

        const jumlahHari = new Date(
            Date.UTC(tahun, bulan + 1, 0)
        ).getUTCDate();

        for (let hari = 1; hari <= jumlahHari; hari++) {
            const tanggal = new Date(Date.UTC(tahun, bulan, hari));
            const kode = kodeTanggal(tanggal);
            const data = dataTanggal(kode);
            const alasan = alasanTidakTersedia(kode);

            const tombol = document.createElement('button');
            tombol.type = 'button';
            tombol.className = 'co-day';
            tombol.dataset.tanggal = kode;
            tombol.disabled = Boolean(alasan);

            const dipilih = tanggalDipilih === kode;
            tombol.classList.toggle('is-selected', dipilih);
            tombol.setAttribute('aria-pressed', String(dipilih));

            const angka = document.createElement('span');
            angka.textContent = hari;

            const keterangan = document.createElement('small');
            keterangan.textContent = alasan || data.sisa + ' loyang';

            tombol.append(angka, keterangan);

            const detail = alasan
                ? alasan + (
                    alasan === 'Kuota kurang'
                        ? ', sisa ' + data.sisa + ' loyang'
                        : ''
                )
                : 'sisa ' + data.sisa + ' loyang';

            tombol.setAttribute(
                'aria-label',
                formatTanggal(kode, true) + ', ' + detail
            );

            tombol.title = detail;
            tombol.addEventListener('click', () => pilihTanggal(kode));
            wadah.appendChild(tombol);
        }

        const indeksBulan = tahun * 12 + bulan;
        const indeksMinimal =
            tanggalMinimal.getUTCFullYear() * 12
            + tanggalMinimal.getUTCMonth();

        el('co-bulan-sebelumnya').disabled =
            indeksBulan <= indeksMinimal;
    }

    el('co-info-tanggal').textContent =
        'Tanggal pemesanan: ' + formatTanggal(config.hariIni)
        + ' • Paling awal: ' + formatTanggal(config.tanggalMinimal);

    el('co-bulan-sebelumnya').addEventListener('click', function () {
        bulanTampil.setUTCMonth(bulanTampil.getUTCMonth() - 1);
        tampilkanKalender();
    });

    el('co-bulan-berikutnya').addEventListener('click', function () {
        bulanTampil.setUTCMonth(bulanTampil.getUTCMonth() + 1);
        tampilkanKalender();
    });

    function tampilkanLangkah(kirim) {
        const daftar = kirim
            ? ['Isi pesanan', 'Konfirmasi ongkir', 'Pembayaran',
                'Verifikasi pembayaran']
            : ['Isi pesanan', 'Pembayaran', 'Verifikasi pembayaran'];

        const wadah = el('co-langkah');
        wadah.replaceChildren();

        daftar.forEach(function (teks, index) {
            const item = document.createElement('li');
            const nomor = document.createElement('span');

            nomor.textContent = index + 1;
            item.append(nomor, document.createTextNode(teks));

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

        el('co-info-toko').hidden = kirim;
        el('co-pengiriman').hidden = !kirim;
        el('co-pengiriman').disabled = !kirim;
        el('co-hasil-validasi').hidden = true;

        tampilkanLangkah(kirim);

        if (!kirim) {
            el('co-label-ongkir').textContent = 'Ongkir • Ambil di Toko';
            el('co-nilai-ongkir').textContent = uang(0);
            el('co-label-total').textContent = 'Total pembayaran';
            el('co-nilai-total').textContent = uang(config.subtotal);

            el('co-petunjuk-bayar').textContent =
                'Petunjuk pembayaran ditampilkan setelah pesanan dibuat.';

            el('co-status-info').textContent =
                'Status setelah dibuat: Menunggu Pembayaran. '
                + 'Selesaikan pembayaran maksimal 1 hari setelah pesanan dibuat.';

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

        form.addEventListener('submit', function(event) {
        event.preventDefault();

        const alasan = tanggalDipilih
            ? alasanTidakTersedia(tanggalDipilih)
            : 'Belum memilih tanggal';

        if (alasan) {
            el('co-error-tanggal').textContent =
                'Pilih tanggal yang memenuhi H+5 dan memiliki kuota cukup untuk '
                + config.jumlah
                + ' loyang.';

            el('co-error-tanggal').hidden = false;

            el('co-hari').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            return;
        }

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

    tampilkanKalender();
    perbaruiPengiriman();
})();