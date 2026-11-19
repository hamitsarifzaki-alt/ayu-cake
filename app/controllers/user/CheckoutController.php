<?php

require_once APP_PATH . '/models/katalog/Produk.php';
require_once APP_PATH . '/models/belanja/Keranjang.php';
require_once APP_PATH . '/models/booking/BookingPelanggan.php';
require_once APP_PATH . '/models/toko/ProfilToko.php';

require_once APP_PATH . '/models/pesanan/Pesanan.php';

class CheckoutController extends Controller
{
    public function booking(): void
    {
        $this->tampilkan('booking');
    }

    public function pesanSekarang(): void
    {
        $this->tampilkan('pesan-sekarang');
    }

    private function tampilkan(string $jenis): void
    {

        $pelangganAktif =
            $_SESSION['app_pelanggan'] ?? null;

        if (!is_array($pelangganAktif)) {
            header(
                'Location: ' . BASE_URL . '/index.php?page=login'
            );
            exit;
        }

        $dariKeranjang =
            ($_GET['sumber'] ?? '') === 'keranjang';

        $modelProduk = new Produk();

        $produk = null;
        $items = [];
        $jumlah = 0;
        $subtotal = 0;
        $pesanError = null;

        if ($dariKeranjang) {
            $modelKeranjang = new Keranjang();
            $ringkasan = $modelKeranjang->ringkasan();

            $items = $ringkasan['items'];
            $jumlah = (int) $ringkasan['jumlahLoyang'];
            $subtotal = (float) $ringkasan['subtotal'];

            if ($items === []) {
                http_response_code(400);

                $pesanError =
                    'Keranjang masih kosong. Tambahkan produk terlebih dahulu.';
            }
        } else {
            $id = $this->bilanganPositif(
                $_GET['id'] ?? null
            );

            $jumlahPilihan = $this->bilanganPositif(
                $_GET['jumlah'] ?? null
            );

            $produk = $id !== null
                ? $modelProduk->cari($id)
                : null;

            if ($produk === null) {
                http_response_code(404);

                $pesanError =
                    'Produk tidak ditemukan. Silakan pilih dari katalog.';
            } elseif ($jumlahPilihan === null) {
                http_response_code(400);

                $pesanError =
                    'Jumlah loyang harus berupa bilangan bulat minimal 1.';
            } else {
                $jumlah = $jumlahPilihan;

                $subtotal =
                    (float) $produk['harga'] * $jumlah;

                $items[] = [
                    'id' => (int) $produk['id'],
                    'nama' => $produk['nama'],
                    'kategori' => $produk['kategori'] ?? '',
                    'gambar' => $produk['gambar'] ?? '',
                    'harga' => (float) $produk['harga'],
                    'stok' => (int) ($produk['stok'] ?? 0),
                    'jumlah' => $jumlah,
                    'subtotal' => $subtotal,
                ];
            }
        }

        // Pesan Sekarang: periksa stok setiap produk.
        if ($pesanError === null && $jenis === 'pesan-sekarang') {
            foreach ($items as $item) {
                if ((int) $item['jumlah'] > (int) $item['stok']) {
                    http_response_code(400);

                    $pesanError =
                        'Stok siap ' . $item['nama']
                        . ' hanya ' . (int) $item['stok']
                        . ' loyang, sedangkan jumlah pilihanmu '
                        . (int) $item['jumlah']
                        . ' loyang. Kurangi jumlah atau pilih Booking.';

                    break;
                }
            }
        }

        // Booking: kapasitas dihitung dari seluruh loyang.
        if (
            $pesanError === null
            && $jenis === 'booking'
            && $jumlah > 50
        ) {
            http_response_code(400);

            $pesanError =
                'Total Booking maksimal 50 loyang per hari. '
                . 'Jumlah pilihanmu saat ini '
                . $jumlah . ' loyang.';
        }

        $hariIni = new DateTimeImmutable(
            'today',
            new DateTimeZone('Asia/Jakarta')
        );

        $tanggalMinimal = $hariIni->modify('+5 days');

        $kalenderContoh = (new BookingPelanggan())->kalender($tanggalMinimal);

        if ($dariKeranjang) {
            $urlKembali =
                BASE_URL . '/index.php?page=keranjang';

            $teksKembali = 'Kembali ke keranjang';
        } elseif ($produk !== null) {
            $urlKembali =
                BASE_URL
                . '/index.php?page=detail-produk&id='
                . (int) $produk['id'];

            $teksKembali = 'Kembali ke detail produk';
        } else {
            $urlKembali =
                BASE_URL . '/index.php?page=katalog';

            $teksKembali = 'Kembali ke katalog';
        }

        $judul = $jenis === 'booking'
            ? 'Booking'
            : 'Pesan Sekarang';

        $namaView = $jenis === 'booking'
            ? 'checkout/booking'
            : 'checkout/index';

        $_SESSION['app_token_checkout'] ??= bin2hex(random_bytes(32));

        $this->view($namaView, [
            'title' => $judul . ' | Ayu Cake',
            'judul' => $judul,
            'tokenCheckout' => $_SESSION['app_token_checkout'],
            'jenis' => $jenis,
            'produk' => $produk,
            'items' => $items,
            'jumlahProduk' => count($items),
            'jumlah' => $jumlah,
            'subtotal' => $subtotal,
            'dariKeranjang' => $dariKeranjang,
            'urlKembali' => $urlKembali,
            'teksKembali' => $teksKembali,
            'pesanError' => $pesanError,
            'hariIni' => $hariIni->format('Y-m-d'),
            'tanggalMinimal' => $tanggalMinimal->format('Y-m-d'),
            'kalenderContoh' => $kalenderContoh,
            'bankTersedia' => (new ProfilToko())->rekeningBank(),
        ]);
    }

    private function bilanganPositif($nilai): ?int
    {
        if (!is_string($nilai)) {
            return null;
        }

        $hasil = filter_var(
            $nilai,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        return $hasil === false ? null : $hasil;
    }
}
