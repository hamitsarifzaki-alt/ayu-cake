<?php

require_once APP_PATH . '/models/katalog/AdminKategori.php';
require_once APP_PATH . '/models/katalog/AdminProduk.php';
require_once APP_PATH . '/models/dashboard/DashboardAdmin.php';
require_once APP_PATH . '/models/akun/AkunAdmin.php';

class AdminKategoriController extends Controller
{
    public function jalankan(string $route): void
    {
        header('Cache-Control: no-store, private, max-age=0');
        header('Pragma: no-cache');

        $admin = AkunAdmin::aktif();
        if ($admin === null) {
            header('Location: ' . BASE_URL . '/index.php?page=login', true, 303);
            return;
        }
        $_SESSION['app_admin_token'] ??= bin2hex(random_bytes(32));
        $token = $_SESSION['app_admin_token'];
        $model = new AdminKategori();
        $id = is_string($_GET['id'] ?? null) ? $_GET['id'] : '';
        $kategori = $model->cari($id);
        $view = $route === 'admin-kategori-form' ? 'kategori-form' : ($route === 'admin-kategori-hapus' ? 'kategori-hapus' : 'kategori');
        $data = (new DashboardAdmin())->ringkasan();
        $data['admin'] = $admin;
        $data['token'] = $token;
        $data['halaman'] = 'kategori';
        $data['produk'] = (new AdminProduk())->semua();
        $data['error'] = null;
        $data['kategoriEdit'] = $kategori;
        $data['form'] = $kategori ?? ['nama' => '', 'deskripsi' => '', 'versi' => '0'];
        if (($route === 'admin-kategori-form' && array_key_exists('id', $_GET) && $kategori === null)
            || ($route === 'admin-kategori-hapus' && $kategori === null)) {
            http_response_code(404);
            $data['error'] = 'Kategori tidak ditemukan. Pilih kategori yang tersedia dari daftar.';
            $view = 'kategori-tidak-ada';
        } elseif ($route === 'admin-kategori-pulihkan' && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            $data['error'] = 'Gunakan tombol Batalkan Hapus dari daftar kategori.';
        } elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $csrf = $_POST['token_admin'] ?? null;
            if (!is_string($csrf) || !hash_equals($token, $csrf)) {
                http_response_code(403);
                $data['error'] = 'Formulir tidak valid. Muat ulang halaman sebelum menyimpan perubahan.';
            } elseif ($route === 'admin-kategori-form') {
                foreach (['nama', 'deskripsi', 'versi'] as $key) $data['form'][$key] = is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
                $hasil = $model->simpan($id !== '' ? $id : null, $_POST);
                if ($hasil['error'] === null) {
                    $this->selesai($id !== '' ? 'Perubahan kategori berhasil disimpan.' : 'Kategori berhasil ditambahkan.');
                    return;
                }
                $data['error'] = $hasil['error'];
                http_response_code($hasil['kode']);
            } elseif ($route === 'admin-kategori-hapus') {
                $data['error'] = $model->hapus($id, is_string($_POST['versi'] ?? null) ? $_POST['versi'] : '');
                if ($data['error'] === null) {
                    $this->selesai('Kategori “' . $kategori['nama'] . '” berhasil dihapus dari daftar.', $id);
                    return;
                }
                http_response_code(409);
            } elseif ($route === 'admin-kategori-pulihkan') {
                if ($model->pulihkan($id)) {
                    $this->selesai('Kategori berhasil dipulihkan.');
                    return;
                }
                $data['error'] = 'Kategori tidak dapat dipulihkan. Periksa apakah kategori sudah aktif atau namanya sudah digunakan.';
                http_response_code(409);
            } else {
                http_response_code(405);
                header('Allow: GET');
                $data['error'] = 'Gunakan form tambah, edit, atau hapus kategori.';
            }
        }
        $data['kategori'] = $model->daftar();
        $data['produkTerhubung'] = $kategori !== null ? $model->produkTerhubung($id) : [];
        $data['pesanKategori'] = $_SESSION['app_admin_kategori_pesan'] ?? null;
        unset($_SESSION['app_admin_kategori_pesan']);
        $data['title'] = ($view === 'kategori-form' ? ($kategori !== null ? 'Edit Kategori' : 'Tambah Kategori') : ($view === 'kategori-hapus' ? 'Hapus Kategori' : 'Kelola Kategori Admin')) . ' | Ayu Cake';
        $this->adminView($view, $data);
    }

    private function selesai(string $pesan, ?string $pulihkan = null): void
    {
        $_SESSION['app_admin_kategori_pesan'] = ['teks' => $pesan, 'pulihkan' => $pulihkan];
        header('Location: ' . BASE_URL . '/index.php?page=admin-kategori', true, 303);
    }
}
