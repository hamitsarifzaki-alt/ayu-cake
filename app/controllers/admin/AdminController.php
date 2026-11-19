<?php

require_once APP_PATH . '/models/dashboard/DashboardAdmin.php';
require_once APP_PATH . '/models/akun/AkunAdmin.php';
require_once APP_PATH . '/models/katalog/AdminProduk.php';
require_once APP_PATH . '/models/laporan/AdminLaporan.php';
require_once APP_PATH . '/models/pesanan/Pesanan.php';

/** Dashboard lokal dengan pemeriksaan sesi admin dari halaman Masuk bersama. */
class AdminController extends Controller
{

    public function dashboard(): void
    {
        $this->tampilkan('dashboard');
    }

    public function pesanan(): void
    {
        $this->tampilkan('pesanan');
    }

    public function detailPesanan(): void
    {
        $this->tampilkan('detail-pesanan');
    }

    public function verifikasi(): void
    {
        $this->tampilkan('verifikasi');
    }

    public function periksaPembayaran(): void
    {
        $this->tampilkan('periksa-pembayaran');
    }

    public function booking(): void
    {
        $this->tampilkan('booking');
    }

    public function laporan(): void
    {
        $this->tampilkan('laporan');
    }

    public function unduhLaporan(): void
    {
        if (AkunAdmin::aktif() === null) {
            header('Location: ' . BASE_URL . '/index.php?page=login', true, 303);
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }
        $format = $_GET['format'] ?? null;
        $laporan = (new AdminLaporan())->buat((new DashboardAdmin())->ringkasan()['pesanan'], $_GET);
        if ($laporan['error'] !== null || !in_array($format, ['pdf', 'xlsx'], true)) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo $laporan['error'] ?? 'Format unduhan tidak valid.';
            return;
        }
        require_once APP_PATH . '/services/AdminLaporanEkspor.php';
        $ekspor = new AdminLaporanEkspor();
        try {
            $isi = $format === 'pdf' ? $ekspor->pdf($laporan) : $ekspor->xlsx($laporan);
        } catch (Throwable $error) {
            error_log('Ekspor laporan admin: ' . $error->getMessage());
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Laporan belum dapat diunduh. Silakan coba kembali.';
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . ($format === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));
        header('Content-Disposition: attachment; filename="ayu-cake-penjualan-' . $laporan['mulai'] . '-' . $laporan['akhir'] . '.' . $format . '"');
        header('Content-Length: ' . strlen($isi));
        echo $isi;
    }

    public function bukti(): void
    {
        if (AkunAdmin::aktif() === null) {
            http_response_code(403);
            echo 'Masuk sebagai admin untuk melihat bukti pembayaran.';
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }
        $nomor = is_string($_GET['nomor'] ?? null) ? $_GET['nomor'] : '';
        $pesanan = array_column((new DashboardAdmin())->ringkasan()['pesanan'], null, 'nomor')[$nomor] ?? null;
        if ($pesanan === null || $pesanan['bukti'] === null) {
            http_response_code(404);
            echo 'Bukti pembayaran tidak ditemukan.';
            return;
        }
        if (isset($_GET['versi']) && $_GET['versi'] !== (string) $pesanan['versi']) {
            http_response_code(409);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Bukti atau pesanan sudah berubah. Buka kembali halaman pemeriksaan.';
            return;
        }
        $bukti = (new Pesanan())->buktiAdmin($nomor);
        if (!$bukti) {
            http_response_code(404);
            return;
        }
        header('Content-Type: '.$bukti['mime']);
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: inline');
        echo $bukti['isi'];
    }

    public function buktiPenuh(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }
        $this->tampilkan('bukti-penuh');
    }

    private function tampilkan(string $halaman): void
    {
        $admin = AkunAdmin::aktif();
        if ($admin === null) {
            header('Location: ' . BASE_URL . '/index.php?page=login', true, 303);
            return;
        }
        $_SESSION['app_admin_token'] ??= bin2hex(random_bytes(32));
        $model = new DashboardAdmin();
        $data = $model->ringkasan();
        $data['produk'] = (new AdminProduk())->semua();
        $data['admin'] = $admin;
        $data['token'] = $_SESSION['app_admin_token'];
        $data['halaman'] = $halaman;
        $judul = ['dashboard' => 'Dashboard Admin', 'pesanan' => 'Pesanan Admin',
            'verifikasi' => 'Verifikasi Pembayaran Admin', 'periksa-pembayaran' => 'Periksa Bukti Pembayaran',
            'booking' => 'Pengaturan Booking Admin', 'laporan' => 'Laporan Penjualan Admin', 'detail-pesanan' => 'Detail Pesanan Admin', 'bukti-penuh' => 'Bukti Pembayaran Ukuran Penuh'];
        $data['title'] = $judul[$halaman] . ' | Ayu Cake';
        if ($halaman === 'detail-pesanan') {
            $nomor = is_string($_GET['nomor'] ?? null) ? $_GET['nomor'] : '';
            $p = array_column($data['pesanan'], null, 'nomor')[$nomor] ?? null;
            $data['detailPesanan'] = $p;
            if ($p !== null && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
                require_once APP_PATH . '/models/notifikasi/Notifikasi.php';
                Notifikasi::baca('admin', $nomor);
            }
            $data['errorDetail'] = null;
            $data['detailMuatUlang'] = false;
            $data['detailAksi'] = '';
            $data['detailForm'] = ['ongkir' => '', 'catatan_status' => '', 'konfirmasi' => false];
            $data['pesanDetail'] = $_SESSION['app_admin_detail_pesan'][$nomor] ?? null;
            unset($_SESSION['app_admin_detail_pesan'][$nomor]);
            if ($p === null) {
                http_response_code(404);
            } elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
                $data['detailAksi'] = is_string($_POST['aksi'] ?? null) ? $_POST['aksi'] : '';
                $bidang = match ($data['detailAksi']) {
                    'konfirmasi-ongkir' => 'ongkir',
                    'tolak-pengiriman' => 'catatan_status',
                    default => null,
                };
                if ($bidang !== null && is_string($_POST[$bidang] ?? null)) {
                    $data['detailForm'][$bidang] = mb_substr($_POST[$bidang], 0, 500);
                }
                $data['detailForm']['konfirmasi'] = ($_POST['konfirmasi'] ?? null) === '1';
                $token = $_POST['token_admin'] ?? null;
                if (!is_string($token) || !hash_equals($data['token'], $token)) {
                    http_response_code(403);
                    $data['errorDetail'] = 'Formulir tidak valid. Muat ulang detail pesanan sebelum menyimpan.';
                    $data['detailMuatUlang'] = true;
                } else {
                    $hasil = (new AdminPesanan())->simpan($p, $_POST);
                    if ($hasil['error'] === null) {
                        if ($data['detailAksi'] === 'konfirmasi-ongkir') {
                            $_SESSION['app_admin_pesanan_pesan'] = $nomor . ': ' . $hasil['pesan'];
                            header('Location: ' . BASE_URL . '/index.php?page=admin-pesanan', true, 303);
                            return;
                        }
                        $_SESSION['app_admin_detail_pesan'][$nomor] = $hasil['pesan'];
                        header('Location: ' . BASE_URL . '/index.php?page=admin-detail-pesanan&nomor=' . rawurlencode($nomor), true, 303);
                        return;
                    }
                    http_response_code($hasil['kode']);
                    $data['errorDetail'] = $hasil['error'];
                    $data['detailMuatUlang'] = $hasil['kode'] === 409;
                }
            }
        }
        if ($halaman === 'laporan') {
            $data['laporan'] = (new AdminLaporan())->buat($data['pesanan'], $_GET);
            if ($data['laporan']['error'] !== null) http_response_code(400);
        }
        if ($halaman === 'booking') {
            $bookingModel = new AdminBooking();
            $data['kalender'] = $bookingModel->kalender($_GET, $data['pesanan']);
            $pilihan = $data['kalender']['pilihan'];
            $data['bookingForm'] = $pilihan !== null ? ['ketersediaan' => $pilihan['dibuka'] ? 'dibuka' : 'ditutup',
                'kapasitas' => (string) $pilihan['maksimal'], 'versi' => (string) $pilihan['versi']] : null;
            $data['errorBooking'] = null;
            if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
                $token = $_POST['token_admin'] ?? null;
                if (!is_string($token) || !hash_equals($data['token'], $token)) {
                    http_response_code(403);
                    $data['errorBooking'] = 'Formulir tidak valid. Muat ulang halaman sebelum menyimpan pengaturan.';
                } elseif ($pilihan === null || ($_POST['tanggal'] ?? null) !== $pilihan['tanggal']) {
                    http_response_code(400);
                    $data['errorBooking'] = 'Tanggal tidak sesuai. Pilih ulang tanggal dari kalender.';
                } else {
                    foreach ($data['bookingForm'] as $key => $nilai) $data['bookingForm'][$key] = is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
                    $hasil = $bookingModel->simpan($_POST, $data['pesanan']);
                    if ($hasil['error'] === null) {
                        $_SESSION['app_admin_booking_pesan'] = $pilihan['tanggal'];
                        header('Location: ' . BASE_URL . '/index.php?page=admin-booking&bulan=' . substr($pilihan['tanggal'], 0, 7) . '#kalender-booking', true, 303);
                        return;
                    }
                    $data['errorBooking'] = $hasil['error'];
                    http_response_code($hasil['kode']);
                }
            }
            $data['pesanBooking'] = $_SESSION['app_admin_booking_pesan'] ?? null;
            unset($_SESSION['app_admin_booking_pesan']);
        }
        if ($halaman === 'pesanan') {
            $data['daftar'] = $model->daftar($data['pesanan'], $_GET);
            $data['pesanPesanan'] = $_SESSION['app_admin_pesanan_pesan'] ?? null;
            unset($_SESSION['app_admin_pesanan_pesan']);
        }
        if ($halaman === 'verifikasi') {
            $data['pembayaran'] = $model->pembayaran($data['pesanan'], $_GET);
            $data['pesan'] = $_SESSION['app_admin_verifikasi_pesan'] ?? null;
            unset($_SESSION['app_admin_verifikasi_pesan']);
        }
        if (in_array($halaman, ['periksa-pembayaran', 'bukti-penuh'], true)) {
            $nomor = is_string($_GET['nomor'] ?? null) ? $_GET['nomor'] : '';
            $p = array_column($data['pesanan'], null, 'nomor')[$nomor] ?? null;
            $data['pesananPeriksa'] = $p;
            $data['error'] = null;
            $data['buktiBerubah'] = false;
            $data['catatan'] = is_string($_POST['catatan'] ?? null) ? trim($_POST['catatan']) : '';
            $data['cocok'] = ($_POST['cocok'] ?? null) === '1';
            if ($p === null || $p['bukti'] === null) {
                $data['pesananPeriksa'] = null;
                http_response_code(404);
            } elseif ($halaman === 'bukti-penuh' && isset($_GET['versi']) && $_GET['versi'] !== (string) $p['versi']) {
                $data['buktiBerubah'] = true;
                http_response_code(409);
            } elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
                require_once APP_PATH . '/models/notifikasi/Notifikasi.php';
                Notifikasi::baca('admin', $nomor);
            } elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
                $token = $_POST['token_admin'] ?? null;
                if (!is_string($token) || !hash_equals($data['token'], $token)) {
                    http_response_code(403);
                    $data['error'] = 'Formulir tidak valid. Muat ulang halaman sebelum memeriksa bukti.';
                } else {
                    $aksi = is_string($_POST['keputusan'] ?? null) ? $_POST['keputusan'] : '';
                    $versi = is_string($_POST['versi'] ?? null) ? $_POST['versi'] : null;
                    $hasil = $model->putuskan($p, $versi, $aksi, $data['catatan'], $data['cocok']);
                    if ($hasil['error'] === null) {
                        $_SESSION['app_admin_verifikasi_pesan'] = $nomor . ': ' . $hasil['pesan'];
                        header('Location: ' . BASE_URL . '/index.php?page=admin-verifikasi', true, 303);
                        return;
                    }
                    $data['error'] = $hasil['error'];
                    http_response_code($hasil['kode']);
                    if ($hasil['kode'] === 409) {
                        $data['catatan'] = '';
                        $data['cocok'] = false;
                    }
                }
            }
        }
        $data['kapasitasMaksimal'] = AdminBooking::KAPASITAS_MAKSIMAL;
        $detail = $data['detailPesanan'] ?? null;
        $data['dapatUbahOngkir'] = $detail !== null && (new AdminPesanan())->ongkirDapatDiubah($detail);
        $data['dapatSelesai'] = $detail !== null && in_array('Selesai', (new AdminPesanan())->pilihanStatus($detail), true);
        $this->adminView($halaman, $data);
    }

    public function masuk(): void
    {
        // Kompatibilitas tautan lama; tidak ada lagi form atau endpoint login terpisah.
        header('Location: ' . BASE_URL . '/index.php?page=login', true, 303);
    }

    public function keluar(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo 'Gunakan tombol Keluar pada Dashboard Admin.';
            return;
        }
        $token = $_POST['token_admin'] ?? null;
        $sesi = $_SESSION['app_admin_token'] ?? '';
        if (AkunAdmin::aktif() === null || !is_string($token) || $sesi === '' || !hash_equals($sesi, $token)) {
            http_response_code(403);
            echo 'Formulir keluar tidak valid. Muat ulang Dashboard Admin.';
            return;
        }
        AkunAdmin::keluar();
        $_SESSION['app_auth_pesan'] = 'Kamu telah keluar dari akun admin.';
        header('Location: ' . BASE_URL . '/index.php?page=login', true, 303);
    }
}
