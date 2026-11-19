<?php

class Controller
{
    protected function adminView(string $view, array $data): void
    {
        require_once APP_PATH . '/models/notifikasi/Notifikasi.php';
        $data['notifikasiAdmin'] = Notifikasi::ringkasan('admin');
        $data['notifikasiPembayaran'] = Notifikasi::ringkasan('admin', true);
        $templates = require APP_PATH . '/../config/views.php';
        require APP_PATH . '/views/admin/layouts/header.php';
        require APP_PATH . '/views/admin/' . $templates['admin'][$view];
        require APP_PATH . '/views/admin/layouts/footer.php';
    }
    protected function view(string $view, array $data = []): void
    {
        $viewFile = APP_PATH . '/views/user/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException('File tampilan tidak ditemukan.');
        }

        require_once APP_PATH . '/models/toko/ProfilToko.php';
        require_once APP_PATH . '/models/belanja/Keranjang.php';
        require_once APP_PATH . '/models/katalog/AdminKategori.php';
        $data['kategoriProduk'] = (new AdminKategori())->peta();
        $data['profilToko'] = (new ProfilToko())->detail();
        $data['adminAktif'] = AkunAdmin::aktif();
        $data['superadminAktif'] = AkunSuperAdmin::aktif();
        require_once APP_PATH . '/models/notifikasi/Notifikasi.php';
        $data['notifikasiPelanggan'] = Notifikasi::ringkasan('pelanggan');
        $data['jumlahKeranjang'] = isset($_SESSION['app_pelanggan']) ? (new Keranjang())->ringkasan()['jumlahLoyang'] : 0;
        $_SESSION['app_token_auth'] ??= bin2hex(random_bytes(32));
        $_SESSION['app_keranjang_token'] ??= bin2hex(random_bytes(32));
        require APP_PATH . '/views/user/layouts/header.php';
        require $viewFile;
        require APP_PATH . '/views/user/layouts/footer.php';
    }
}
