<?php

require_once APP_PATH . '/models/belanja/Keranjang.php';

class KeranjangController extends Controller
{
    public function ringkasan(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $ringkasan = $this->sudahMasuk()
            ? (new Keranjang())->ringkasan()
            : ['jumlahLoyang' => 0, 'jumlahProduk' => 0];
        $this->json([
            'berhasil' => true,
            'jumlahLoyang' => $ringkasan['jumlahLoyang'],
            'jumlahProduk' => $ringkasan['jumlahProduk'],
        ]);
    }

    public function tambah(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->sudahMasuk()) {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Silakan masuk terlebih dahulu untuk menambahkan produk.',
            ], 401);

            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Metode permintaan tidak sesuai.',
            ], 405);

            return;
        }

        $token = $_POST['token'] ?? '';
        $tokenSesi =
            $_SESSION['app_keranjang_token'] ?? '';

        if (
            !is_string($token)
            || !is_string($tokenSesi)
            || $tokenSesi === ''
            || !hash_equals($tokenSesi, $token)
        ) {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Silakan muat ulang halaman dan coba kembali.',
            ], 403);

            return;
        }

        $id = $this->angkaPositif($_POST['id'] ?? null);
        $jumlah = $this->angkaPositif($_POST['jumlah'] ?? null);

        if ($id === null || $jumlah === null) {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Produk atau jumlah loyang tidak valid.',
            ], 400);

            return;
        }

        $model = new Keranjang();

        if (!$model->tambah($id, $jumlah)) {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Produk tidak ditemukan atau jumlah tidak dapat ditambahkan.',
            ], 400);

            return;
        }

        $ringkasan = $model->ringkasan();

        $this->json([
            'berhasil' => true,
            'pesan' => $jumlah . ' loyang berhasil ditambahkan ke keranjang.',
            'jumlahLoyang' => $ringkasan['jumlahLoyang'],
            'jumlahProduk' => $ringkasan['jumlahProduk'],
        ]);
    }
    private function sudahMasuk(): bool
    {
        return true && is_array(
            $_SESSION['app_pelanggan'] ?? null
        );
    }

    public function index(): void
    {
        $sudahMasuk = $this->sudahMasuk();

        if (empty($_SESSION['app_keranjang_token'])) {
            $_SESSION['app_keranjang_token'] =
                bin2hex(random_bytes(32));
        }

        $model = new Keranjang();

        $ringkasan = $sudahMasuk
            ? $model->ringkasan()
            : [
                'items' => [],
                'jumlahProduk' => 0,
                'jumlahLoyang' => 0,
                'subtotal' => 0,
            ];

        $this->view('keranjang/pelanggan', [
            'title' => 'Keranjang Belanja | Ayu Cake',
            'sudahMasuk' => $sudahMasuk,
            'ringkasan' => $ringkasan,
            'tokenKeranjang' =>
                $_SESSION['app_keranjang_token'],
        ]);
    }

    public function ubah(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->sudahMasuk()) {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Silakan masuk terlebih dahulu.',
            ], 401);

            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Metode permintaan tidak sesuai.',
            ], 405);

            return;
        }

        $token = $_POST['token'] ?? '';
        $tokenSesi =
            $_SESSION['app_keranjang_token'] ?? '';

        if (
            !is_string($token)
            || !is_string($tokenSesi)
            || $tokenSesi === ''
            || !hash_equals($tokenSesi, $token)
        ) {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Muat ulang halaman dan coba kembali.',
            ], 403);

            return;
        }

        $id = $this->angkaPositif($_POST['id'] ?? null);
        $aksi = $_POST['aksi'] ?? '';

        if ($id === null) {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Produk tidak valid.',
            ], 400);

            return;
        }

        $model = new Keranjang();

        if ($aksi === 'hapus') {
            $model->hapus($id);
        } elseif ($aksi === 'jumlah') {
            $jumlah = $this->angkaPositif(
                $_POST['jumlah'] ?? null
            );

            if ($jumlah === null) {
                $this->json([
                    'berhasil' => false,
                    'pesan' => 'Masukkan jumlah loyang berupa bilangan bulat yang valid, minimal 1.',
                ], 400);

                return;
            }

            if (!$model->ubahJumlah($id, $jumlah)) {
                $this->json([
                    'berhasil' => false,
                    'pesan' => 'Produk sudah tidak ada di keranjang.',
                ], 404);

                return;
            }
        } else {
            $this->json([
                'berhasil' => false,
                'pesan' => 'Aksi tidak valid.',
            ], 400);

            return;
        }

        $this->json([
            'berhasil' => true,
            'ringkasan' => $model->ringkasan(),
        ]);
    }

    private function angkaPositif($nilai): ?int
    {
        if (!is_string($nilai)) {
            return null;
        }

        $hasil = filter_var(
            $nilai,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => Keranjang::JUMLAH_MAKSIMAL]]
        );

        return $hasil === false ? null : $hasil;
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
