<?php

require_once APP_PATH . '/models/katalog/AdminProduk.php';
require_once APP_PATH . '/models/dashboard/DashboardAdmin.php';
require_once APP_PATH . '/models/akun/AkunAdmin.php';

class AdminProdukController extends Controller
{
    public function jalankan(string $route): void
    {
        header('Cache-Control: no-store, private, max-age=0');
        header('Pragma: no-cache');

        $admin = AkunAdmin::aktif();
        if ($admin === null) {
            if ($route === 'admin-produk-foto') {
                http_response_code(403);
                return;
            }
            header('Location: ' . BASE_URL . '/index.php?page=login', true, 303);
            return;
        }
        $_SESSION['app_admin_token'] ??= bin2hex(random_bytes(32));
        $token = $_SESSION['app_admin_token'];
        $model = new AdminProduk();
        $id = filter_var(is_string($_GET['id'] ?? null) ? $_GET['id'] : '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $produk = $id > 0 ? $model->cari($id) : null;
        if ($route === 'admin-produk-foto') {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
                http_response_code(405);
                header('Allow: GET');
                return;
            }
            if ($produk === null || $produk['foto'] === null) {
                http_response_code(404);
                return;
            }
            header('Content-Type: ' . $produk['foto']['mime']);
            header('X-Content-Type-Options: nosniff');
            header("Content-Security-Policy: default-src 'none'; sandbox");
            echo $produk['foto']['data'];
            return;
        }
        $data = (new DashboardAdmin())->ringkasan();
        $data['admin'] = $admin;
        $data['token'] = $token;
        $data['halaman'] = 'produk';
        $data['produk'] = $model->semua();
        $data['kategoriProduk'] = $model->kategori();
        $data['error'] = null;
        $data['fotoPilihUlang'] = false;
        $data['produkEdit'] = $produk;
        $data['form'] = $produk === null ? ['nama' => '', 'kategori' => array_key_first($data['kategoriProduk']) ?? '', 'harga' => '', 'stok' => '0',
            'jenis' => 'Ready Stock', 'aktif' => '1', 'deskripsi' => '', 'versi' => '0'] : array_merge($produk, ['aktif' => $produk['aktif'] ? '1' : '0']);
        $view = $route === 'admin-produk-form' ? 'produk-form' : ($route === 'admin-produk-hapus' ? 'produk-hapus' : 'produk');
        if (($route === 'admin-produk-form' && array_key_exists('id', $_GET) && $produk === null)
            || ($route === 'admin-produk-hapus' && $produk === null)) {
            http_response_code(404);
            $data['error'] = 'Produk tidak ditemukan. Kembali ke daftar produk untuk memilih produk lain.';
            $view = 'produk-tidak-ada';
        } elseif ($route === 'admin-produk-pulihkan' && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            $data['error'] = 'Gunakan tombol Batalkan Hapus dari daftar produk.';
        } elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $csrf = $_POST['token_admin'] ?? null;
            if (!is_string($csrf) || !hash_equals($token, $csrf)) {
                http_response_code(403);
                $data['error'] = 'Formulir tidak valid. Muat ulang halaman sebelum menyimpan perubahan.';
                $data['fotoPilihUlang'] = $route === 'admin-produk-form' && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
            } elseif ($route === 'admin-produk-form') {
                foreach ($data['form'] as $key => $nilai) {
                    if (in_array($key, ['nama', 'kategori', 'harga', 'stok', 'jenis', 'aktif', 'deskripsi', 'versi'], true)) {
                        $data['form'][$key] = is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
                    }
                }
                $hasil = $model->simpan($id > 0 ? $id : null, $_POST, is_array($_FILES['foto'] ?? null) ? $_FILES['foto'] : null);
                if ($hasil['error'] === null) {
                    $this->selesai($id > 0 ? 'Perubahan produk berhasil disimpan.' : 'Produk berhasil ditambahkan.');
                    return;
                }
                $data['error'] = $hasil['error'];
                $data['fotoPilihUlang'] = ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
                http_response_code($hasil['kode']);
            } elseif ($route === 'admin-produk-hapus') {
                $data['error'] = $model->hapus($id, is_string($_POST['versi'] ?? null) ? $_POST['versi'] : '');
                if ($data['error'] === null) {
                    $this->selesai('Produk berhasil dihapus dari daftar.', $id);
                    return;
                }
                http_response_code(409);
            } elseif ($route === 'admin-produk-pulihkan') {
                if ($model->pulihkan($id)) {
                    $this->selesai('Produk berhasil dipulihkan.');
                    return;
                }
                $data['error'] = 'Produk tidak dapat dipulihkan. Periksa apakah produk sudah aktif, namanya sudah digunakan, atau kategorinya sudah dihapus.';
                http_response_code(409);
            } else {
                http_response_code(405);
                header('Allow: GET');
                $data['error'] = 'Gunakan form tambah, edit, atau hapus produk.';
            }
        }
        $data['daftarProduk'] = $model->daftar($_GET);
        $data['pesanProduk'] = $_SESSION['app_admin_produk_pesan'] ?? null;
        unset($_SESSION['app_admin_produk_pesan']);
        $data['title'] = ($view === 'produk-form' ? ($id > 0 ? 'Edit Produk' : 'Tambah Produk') : ($view === 'produk-hapus' ? 'Hapus Produk' : 'Kelola Produk Admin')) . ' | Ayu Cake';
        $this->adminView($view, $data);
    }

    private function selesai(string $pesan, ?int $pulihkan = null): void
    {
        $_SESSION['app_admin_produk_pesan'] = ['teks' => $pesan, 'pulihkan' => $pulihkan];
        header('Location: ' . BASE_URL . '/index.php?page=admin-produk', true, 303);
    }
}
