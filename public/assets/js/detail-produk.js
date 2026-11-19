(function () {
    'use strict';

    const produk = document.querySelector('.dp-product');

    // Halaman produk tidak ditemukan tidak memiliki elemen ini.
    if (!produk) return;

    const inputJumlah = document.getElementById('dp-jumlah');
    const tombolKurangi = document.getElementById('dp-kurangi');
    const tombolTambah = document.getElementById('dp-tambah');
    const pesanJumlah = document.getElementById('dp-pesan-jumlah');
    const pemberitahuan = document.getElementById('dp-pemberitahuan');

    const idProduk = produk.dataset.id;
    const stok = Number(produk.dataset.stok);
    const urlDasar = produk.dataset.url;

    function tampilkanPesan(teks) {
        if (window.AyuNotice) window.AyuNotice.show(pemberitahuan, teks);
        else { pemberitahuan.textContent = teks; pemberitahuan.hidden = false; }
    }

    function bersihkanPesan() {
        pesanJumlah.textContent = '';
        inputJumlah.removeAttribute('aria-invalid');
        pemberitahuan.hidden = true;
        pemberitahuan.classList.remove('dp-notice-error');
    }

    function bacaJumlah() {
        const jumlah = Number(inputJumlah.value);

        if (!Number.isSafeInteger(jumlah) || jumlah < 1) {
            pesanJumlah.textContent =
                'Masukkan jumlah loyang berupa bilangan bulat minimal 1.';

            inputJumlah.setAttribute('aria-invalid', 'true');
            inputJumlah.focus();

            return null;
        }

        return jumlah;
    }

    function perbaruiTombolKurangi() {
        const jumlah = Number(inputJumlah.value);

        tombolKurangi.disabled =
            !Number.isSafeInteger(jumlah) || jumlah <= 1;
    }

    tombolKurangi.addEventListener('click', function () {
        const jumlah = bacaJumlah();

        if (jumlah === null) return;

        inputJumlah.value = Math.max(1, jumlah - 1);

        bersihkanPesan();
        perbaruiTombolKurangi();
    });

    tombolTambah.addEventListener('click', function () {
        const jumlah = bacaJumlah();

        if (jumlah === null) return;

        if (!Number.isSafeInteger(jumlah + 1)) return;

        inputJumlah.value = jumlah + 1;

        bersihkanPesan();
        perbaruiTombolKurangi();
    });

    inputJumlah.addEventListener('input', function () {
        bersihkanPesan();
        perbaruiTombolKurangi();
    });

    produk.querySelectorAll('[data-dp-action]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            bersihkanPesan();

            const aksi = tombol.dataset.dpAction;

            if (aksi !== 'booking' && aksi !== 'pesan') return;

            if (produk.dataset.sudahMasuk !== 'true') {
                tampilkanPesan(
                    aksi === 'booking'
                        ? 'Silakan masuk terlebih dahulu untuk melakukan booking.'
                        : 'Silakan masuk terlebih dahulu untuk memesan produk.'
                );
                pemberitahuan.classList.add('dp-notice-error');
                return;
            }

            const jumlah = bacaJumlah();

            if (jumlah === null) return;

            if (aksi === 'pesan' && jumlah > stok) {
                pesanJumlah.textContent =
                    'Stok siap saat ini hanya ' + stok + ' loyang.';

                inputJumlah.setAttribute('aria-invalid', 'true');
                inputJumlah.focus();

                return;
            }

            if (aksi === 'booking' && jumlah > 50) {
                pesanJumlah.textContent =
                    'Jumlah Booking maksimal 50 loyang. ' +
                    'Ketersediaan kuota diperiksa berdasarkan tanggal pilihan.';

                inputJumlah.setAttribute('aria-invalid', 'true');
                inputJumlah.focus();

                return;
            }

            const tujuan = new URL(urlDasar, window.location.href);

            tujuan.searchParams.set(
                'page',
                aksi === 'booking' ? 'booking' : 'pesan-sekarang'
            );

            tujuan.searchParams.set('id', idProduk);
            tujuan.searchParams.set('jumlah', String(jumlah));

            window.location.assign(tujuan.href);
        });
    });

    perbaruiTombolKurangi();
})();
