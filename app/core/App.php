<?php

class App
{
    public function run(): void
    {
        $page = $_GET['page'] ?? 'home';

        switch ($page) {
            case 'notifikasi-ringkasan':
                require_once APP_PATH . '/controllers/shared/NotifikasiController.php';
                (new NotifikasiController())->ringkasan();
                break;
            case 'superadmin':
            case 'superadmin-dashboard':
            case 'superadmin-keluar':
            case 'superadmin-laporan-unduh':
            case 'superadmin-laporan':
            case 'superadmin-profil-toko':
            case 'superadmin-faq':
            case 'superadmin-akun-admin':
            case 'superadmin-akun-pelanggan':
                require_once APP_PATH . '/controllers/superadmin/SuperAdminController.php';
                (new SuperAdminController())->jalankan($page);
                break;

            case 'admin-login':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->masuk();
                break;

            case 'admin':
            case 'admin-dashboard':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->dashboard();
                break;

            case 'admin-pesanan':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->pesanan();
                break;

            case 'admin-detail-pesanan':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->detailPesanan();
                break;

            case 'admin-verifikasi':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->verifikasi();
                break;

            case 'admin-periksa-pembayaran':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->periksaPembayaran();
                break;

            case 'admin-bukti':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->bukti();
                break;

            case 'admin-bukti-penuh':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->buktiPenuh();
                break;

            case 'admin-produk':
            case 'admin-produk-form':
            case 'admin-produk-hapus':
            case 'admin-produk-pulihkan':
            case 'admin-produk-foto':
                require_once APP_PATH . '/controllers/admin/AdminProdukController.php';
                (new AdminProdukController())->jalankan($page);
                break;

            case 'admin-kategori':
            case 'admin-kategori-form':
            case 'admin-kategori-hapus':
            case 'admin-kategori-pulihkan':
                require_once APP_PATH . '/controllers/admin/AdminKategoriController.php';
                (new AdminKategoriController())->jalankan($page);
                break;

            case 'admin-booking':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->booking();
                break;

            case 'admin-laporan':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->laporan();
                break;

            case 'admin-laporan-unduh':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->unduhLaporan();
                break;

            case 'admin-keluar':
                require_once APP_PATH . '/controllers/admin/AdminController.php';
                (new AdminController())->keluar();
                break;

            case 'keranjang-ringkasan':
                require_once APP_PATH . '/controllers/user/KeranjangController.php';
                (new KeranjangController())->ringkasan();
                break;
            case 'riwayat-pesanan':
                require_once APP_PATH . '/controllers/user/PesananController.php';
                (new PesananController())->riwayat();
                break;
            case 'home':
                require_once APP_PATH . '/controllers/user/HomeController.php';
                $controller = new HomeController();
                $controller->index();
                break;

            case 'katalog':
                require_once APP_PATH . '/controllers/user/KatalogController.php';
                $controller = new KatalogController();
                $controller->index();
                break;

            case 'detail-produk':
                require_once APP_PATH . '/controllers/user/ProdukController.php';
                $controller = new ProdukController();
                $controller->detail();
                break;

            case 'booking':
                require_once APP_PATH . '/controllers/user/CheckoutController.php';
                $controller = new CheckoutController();
                $controller->booking();
                break;

            case 'pesan-sekarang':
                require_once APP_PATH . '/controllers/user/CheckoutController.php';
                $controller = new CheckoutController();
                $controller->pesanSekarang();
                break;

            case 'cara-pesan':
                require_once APP_PATH . '/controllers/user/CaraPesanController.php';
                $controller = new CaraPesanController();
                $controller->index();
                break;

            case 'kontak':
                require_once APP_PATH . '/controllers/user/KontakController.php';
                $controller = new KontakController();
                $controller->index();
                break;

            case 'login':
                require_once APP_PATH . '/controllers/shared/AuthController.php';
                $controller = new AuthController();
                $controller->login();
                break;

            case 'lupa-kata-sandi':
                require_once APP_PATH . '/controllers/shared/AuthController.php';
                $controller = new AuthController();
                $controller->lupaKataSandi();
                break;

            case 'registrasi':
                require_once APP_PATH . '/controllers/shared/AuthController.php';
                $controller = new AuthController();
                $controller->registrasi();
                break;

            case 'keranjang':
                require_once APP_PATH . '/controllers/user/KeranjangController.php';
                $controller = new KeranjangController();
                $controller->index();
                break;

            case 'buat-pesanan':
                require_once APP_PATH . '/controllers/user/PesananController.php';
                $controller = new PesananController();
                $controller->buat();
                break;

            case 'pesanan-berhasil':
                require_once APP_PATH . '/controllers/user/PesananController.php';
                $controller = new PesananController();
                $controller->berhasil();
                break;

            case 'status-pesanan':
                require_once APP_PATH . '/controllers/user/PesananController.php';
                $controller = new PesananController();
                $controller->status();
                break;

            case 'detail-pesanan':
                require_once APP_PATH . '/controllers/user/PesananController.php';
                $controller = new PesananController();
                $controller->detail();
                break;

            case 'batalkan-pesanan':
                require_once APP_PATH . '/controllers/user/PesananController.php';
                $controller = new PesananController();
                $controller->batalkan();
                break;

            case 'selesai-pesanan':
                require_once APP_PATH . '/controllers/user/PesananController.php';
                (new PesananController())->selesai();
                break;

            case 'pembayaran':
                require_once APP_PATH . '/controllers/user/PesananController.php';
                $controller = new PesananController();
                $controller->pembayaran();
                break;

            case 'unggah-bukti':
                require_once APP_PATH . '/controllers/user/PesananController.php';
                $controller = new PesananController();
                $controller->unggahBukti();
                break;

            case 'keranjang-ubah':
                require_once APP_PATH . '/controllers/user/KeranjangController.php';
                $controller = new KeranjangController();
                $controller->ubah();
                break;

            case 'keranjang-tambah':
                require_once APP_PATH . '/controllers/user/KeranjangController.php';
                $controller = new KeranjangController();
                $controller->tambah();
                break;

            default:
                http_response_code(404);

                echo '<h1>Halaman tidak ditemukan</h1>';
                echo '<a href="' . BASE_URL . '/">Kembali ke Beranda</a>';
                break;
        }
    }
}
