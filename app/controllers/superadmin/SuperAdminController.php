<?php

require_once APP_PATH . '/models/akun/AkunSuperAdmin.php';
require_once APP_PATH . '/models/dashboard/DashboardSuperAdmin.php';

class SuperAdminController
{
    public function jalankan(string $page): void
    {
        header('Cache-Control: no-store, private, max-age=0');
        header('Pragma: no-cache');

        $akun = AkunSuperAdmin::aktif();
        if ($akun === null) {
            header('Location: ' . BASE_URL . '/index.php?page=login', true, 303);
            return;
        }
        $_SESSION['app_superadmin_token'] ??= bin2hex(random_bytes(32));
        if ($page === 'superadmin-keluar') {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                http_response_code(405);
                header('Allow: POST');
                return;
            }
            $token = $_POST['token_superadmin'] ?? null;
            if (!is_string($token) || !hash_equals($_SESSION['app_superadmin_token'], $token)) {
                http_response_code(403);
                echo 'Formulir keluar tidak valid. Muat ulang Dashboard Super Admin.';
                return;
            }
            AkunSuperAdmin::keluar();
            header('Location: ' . BASE_URL . '/index.php?page=login', true, 303);
            return;
        }
        if ($page === 'superadmin-akun-admin') {
            $this->akunAdmin($akun);
            return;
        }
        if ($page === 'superadmin-akun-pelanggan') {
            $this->akunPelanggan($akun);
            return;
        }
        if ($page === 'superadmin-profil-toko') {
            $this->profilToko($akun);
            return;
        }
        if ($page === 'superadmin-faq') {
            $this->faq($akun);
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }
        $data = (new DashboardSuperAdmin())->ringkasan();
        if (in_array($page, ['superadmin-laporan', 'superadmin-laporan-unduh'], true)) {
            require_once APP_PATH . '/models/laporan/SuperAdminLaporan.php';
            $data['laporan'] = (new SuperAdminLaporan())->buat((new DashboardAdmin())->ringkasan()['pesanan'], $_GET);
        }
        if ($page === 'superadmin-laporan-unduh') {
            $format = $_GET['format'] ?? null;
            if (!in_array($format, ['pdf', 'xlsx'], true)) {
                http_response_code(400);
                echo 'Format unduhan tidak valid.';
                return;
            }
            if ($data['laporan']['error'] !== null) {
                http_response_code(400);
                echo $data['laporan']['error'];
                return;
            }
            require_once APP_PATH . '/services/AdminLaporanEkspor.php';
            try {
                $ekspor = new AdminLaporanEkspor();
                $isi = $format === 'pdf' ? $ekspor->pdf($data['laporan']) : $ekspor->xlsx($data['laporan']);
            } catch (Throwable $error) {
                error_log('Ekspor Super Admin: ' . $error->getMessage());
                http_response_code(500);
                echo 'Laporan belum dapat diunduh. Silakan coba kembali.';
                return;
            }
            header('X-Content-Type-Options: nosniff');
            header('Content-Type: ' . ($format === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));
            header('Content-Disposition: attachment; filename="ayu-cake-penjualan-' . $data['laporan']['mulai'] . '-' . $data['laporan']['akhir'] . '.' . $format . '"');
            header('Content-Length: ' . strlen($isi));
            echo $isi;
            return;
        }
        $data['akun'] = $akun;
        $data['token'] = $_SESSION['app_superadmin_token'];
        $data['halaman'] = 'dashboard';
        if ($page === 'superadmin-laporan') {
            $data['title'] = 'Laporan Penjualan';
            $data['halaman'] = 'laporan';
            if ($data['laporan']['error'] !== null) http_response_code(400);
            require APP_PATH . '/views/superadmin/laporan/index.php';
            return;
        }
        require APP_PATH . '/views/superadmin/dashboard/index.php';
    }

    private function faq(array $akun): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['GET', 'POST'], true)) {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }
        require_once APP_PATH . '/models/faq/SuperAdminFaq.php';
        $model = new SuperAdminFaq();
        $editDiminta = array_key_exists('edit', $_GET);
        $id = $editDiminta && is_string($_GET['edit']) ? filter_var($_GET['edit'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        $edit = $id !== false ? $model->cari($id) : null;
        $data = ['title' => 'Kelola FAQ', 'halaman' => 'faq', 'akun' => $akun, 'token' => $_SESSION['app_superadmin_token'],
            'faqEdit' => $edit, 'tampilFormFaq' => $edit !== null || (!$editDiminta && ($_GET['form'] ?? null) === 'tambah'),
            'formFaq' => ['pertanyaan' => $edit['pertanyaan'] ?? '', 'jawaban' => $edit['jawaban'] ?? '', 'versi' => (string) ($edit['versi'] ?? 0)],
            'errorFaq' => null, 'muatUlangFaq' => false];
        if ($editDiminta && $edit === null) {
            http_response_code(404);
            $data['errorFaq'] = 'FAQ tidak ditemukan. Pilih kembali dari daftar.';
        } elseif ($method === 'POST') {
            $token = $_POST['token_superadmin'] ?? null;
            if (!is_string($token) || !hash_equals($data['token'], $token)) {
                http_response_code(403);
                $data['errorFaq'] = 'Formulir tidak valid. Muat ulang halaman sebelum melanjutkan.';
                $data['muatUlangFaq'] = true;
            } else {
                $aksi = $_POST['aksi'] ?? null;
                $hasil = ['error' => 'Pilih tindakan FAQ yang tersedia.', 'kode' => 400];
                if ($aksi === 'simpan' && $data['tampilFormFaq']) {
                    foreach ($data['formFaq'] as $key => $value) $data['formFaq'][$key] = is_string($_POST[$key] ?? null) ? mb_substr($_POST[$key], 0, 4100) : '';
                    $hasil = $model->simpan($edit['id'] ?? null, $_POST);
                } elseif (in_array($aksi, ['hapus', 'pulihkan'], true) && !$editDiminta && !$data['tampilFormFaq']) {
                    $target = is_string($_POST['id'] ?? null) ? filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
                    $hasil = $model->status($target === false ? 0 : $target, $_POST);
                }
                if ($hasil['error'] === null) {
                    $_SESSION['app_superadmin_faq_pesan'] = ['teks' => $aksi === 'hapus' ? 'FAQ berhasil dihapus.' : ($aksi === 'pulihkan' ? 'Penghapusan FAQ dibatalkan.' : 'FAQ berhasil disimpan.'),
                        'pulihkan' => $aksi === 'hapus' ? ['id' => $hasil['id'], 'versi' => $hasil['versi']] : null];
                    header('Location: ' . BASE_URL . '/index.php?page=superadmin-faq', true, 303);
                    return;
                }
                http_response_code($hasil['kode']);
                $data['errorFaq'] = $hasil['error'];
                $data['muatUlangFaq'] = $hasil['kode'] === 409;
            }
        }
        $data['daftarFaq'] = $model->semua();
        $data['pesanFaq'] = $_SESSION['app_superadmin_faq_pesan'] ?? null;
        unset($_SESSION['app_superadmin_faq_pesan']);
        $data += (new DashboardSuperAdmin())->ringkasan();
        if ($data['tampilFormFaq']) $data['title'] = $edit === null ? 'Tambah FAQ' : 'Edit FAQ';
        require APP_PATH . '/views/superadmin/faq/' . ($data['tampilFormFaq'] ? 'form.php' : 'index.php');
    }

    private function profilToko(array $akun): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['GET', 'POST'], true)) {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }
        require_once APP_PATH . '/models/toko/SuperAdminProfilToko.php';
        $model = new SuperAdminProfilToko();
        $data = ['title' => 'Profil Toko', 'halaman' => 'profil-toko', 'akun' => $akun,
            'token' => $_SESSION['app_superadmin_token'], 'formToko' => $model->detail(), 'errorToko' => null,
            'muatUlangToko' => false, 'gambarPilihUlang' => false];
        $data['tokoAwal'] = array_diff_key($data['formToko'], ['logo' => true]);
        if ($method === 'POST') {
            $token = $_POST['token_superadmin'] ?? null;
            if (!is_string($token) || !hash_equals($data['token'], $token)) {
                http_response_code(403);
                $data['errorToko'] = 'Formulir tidak valid. Muat ulang halaman sebelum menyimpan.';
                $data['muatUlangToko'] = true;
            } else {
                foreach ($data['formToko'] as $key => $value) {
                    if (in_array($key, ['logo', 'bank'], true)) continue;
                    $data['formToko'][$key] = is_string($_POST[$key] ?? null) ? mb_substr($_POST[$key], 0, 2100) : '';
                }
                $postedWallets = is_array($_POST['bank'] ?? null) ? $_POST['bank'] : [];
                foreach (SuperAdminProfilToko::bank() as $kode => $nama) foreach (['nomor', 'atas_nama'] as $field) {
                    $value = is_array($postedWallets[$kode] ?? null) ? ($postedWallets[$kode][$field] ?? null) : null;
                    $data['formToko']['bank'][$kode][$field] = is_string($value) ? mb_substr($value, 0, 150) : '';
                }
                $hasil = $model->simpan($_POST, $_FILES);
                if ($hasil['error'] === null) {
                    $_SESSION['app_superadmin_toko_pesan'] = !empty($hasil['berubah'])
                        ? 'Perubahan profil toko berhasil disimpan.' : 'Tidak ada perubahan untuk disimpan.';
                    header('Location: ' . BASE_URL . '/index.php?page=superadmin-profil-toko', true, 303);
                    return;
                }
                http_response_code($hasil['kode']);
                $data['errorToko'] = $hasil['error'];
                $data['muatUlangToko'] = $hasil['kode'] === 409;
            }
            $data['gambarPilihUlang'] = ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        }
        $data['pesanToko'] = $_SESSION['app_superadmin_toko_pesan'] ?? null;
        unset($_SESSION['app_superadmin_toko_pesan']);
        $data += (new DashboardSuperAdmin())->ringkasan();
        $data['bankPilihan'] = SuperAdminProfilToko::bank();
        require APP_PATH . '/views/superadmin/toko/profil.php';
    }

    private function akunAdmin(array $akun): void
    {
        $model = new KelolaAkunAdmin();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['GET', 'POST'], true)) {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }
        $editDiminta = array_key_exists('edit', $_GET);
        $id = $editDiminta && is_string($_GET['edit']) ? filter_var($_GET['edit'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        $edit = $id !== false ? $model->cari($id) : null;
        $hapusDiminta = array_key_exists('hapus', $_GET);
        $idHapus = $hapusDiminta && is_string($_GET['hapus']) ? filter_var($_GET['hapus'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        $hapus = !$editDiminta && $idHapus !== false ? $model->cari($idHapus) : null;
        $data = ['title' => 'Akun Admin', 'halaman' => 'akun-admin', 'akun' => $akun,
            'token' => $_SESSION['app_superadmin_token'], 'editAdmin' => $edit,
            'hapusAdmin' => $hapus,
            'tampilForm' => !$hapusDiminta && ($edit !== null || (!$editDiminta && ($_GET['form'] ?? null) === 'tambah')),
            'errorAdmin' => null, 'muatUlangAdmin' => false,
            'formAdmin' => ['nama' => $edit['nama'] ?? '', 'email' => $edit['email'] ?? '', 'versi' => (string) ($edit['versi'] ?? 0)]];
        if (($editDiminta && $edit === null) || ($hapusDiminta && $hapus === null)) {
            http_response_code(404);
            $data['errorAdmin'] = 'Akun admin tidak ditemukan. Pilih kembali akun dari daftar.';
        } elseif ($method === 'POST') {
            $token = $_POST['token_superadmin'] ?? null;
            if (!is_string($token) || !hash_equals($data['token'], $token)) {
                http_response_code(403);
                $data['errorAdmin'] = 'Formulir tidak valid. Muat ulang halaman sebelum menyimpan.';
                $data['muatUlangAdmin'] = true;
            } elseif (($_POST['aksi'] ?? null) === 'simpan' && $data['tampilForm']) {
                foreach (['nama', 'email', 'versi'] as $key) $data['formAdmin'][$key] = is_string($_POST[$key] ?? null) ? mb_substr($_POST[$key], 0, 255) : '';
                $hasil = $model->simpan($edit !== null ? $edit['id'] : null, $_POST);
                if ($hasil['error'] === null) {
                    $this->akunSelesai($edit === null ? 'Admin berhasil ditambahkan dan dapat masuk menggunakan akun barunya.' : 'Perubahan akun admin berhasil disimpan.');
                    return;
                }
                http_response_code($hasil['kode']);
                $data['errorAdmin'] = $hasil['error'];
                $data['muatUlangAdmin'] = $hasil['kode'] === 409;
            } elseif (($_POST['aksi'] ?? null) === 'hapus' && $hapus !== null) {
                $versi = is_string($_POST['versi'] ?? null) ? $_POST['versi'] : null;
                $hasil = $model->hapus($idHapus, $versi);
                if ($hasil['error'] === null) {
                    $this->akunSelesai('Akun admin “' . $hapus['nama'] . '” (' . $hapus['email'] . ') berhasil dihapus.');
                    return;
                }
                http_response_code($hasil['kode']);
                $data['errorAdmin'] = $hasil['error'];
                $data['muatUlangAdmin'] = $hasil['kode'] === 409;
            } else {
                http_response_code(400);
                $data['errorAdmin'] = 'Pilih Tambah Admin, Edit, atau Hapus dari daftar.';
            }
        }
        $data['daftarAdmin'] = $model->semua();
        $data['aktifAdmin'] = count(array_filter($data['daftarAdmin'], static fn ($a) => $a['aktif']));
        $data['pesanAdmin'] = $_SESSION['app_superadmin_akun_pesan'] ?? null;
        unset($_SESSION['app_superadmin_akun_pesan']);
        // Ringkasan menu lain tetap tersedia melalui header Super Admin.
        $data += (new DashboardSuperAdmin())->ringkasan();
        if ($data['tampilForm']) $data['title'] = $edit === null ? 'Tambah Admin' : 'Edit Admin';
        if ($hapus !== null) {
            $data['title'] = 'Hapus Akun Admin';
            require APP_PATH . '/views/superadmin/akun/hapus.php';
            return;
        }
        require APP_PATH . '/views/superadmin/akun/' . ($data['tampilForm'] ? 'admin-form.php' : 'admin.php');
    }

    private function akunPelanggan(array $akun): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['GET', 'POST'], true)) {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }
        $model = new KelolaAkunPelanggan();
        $daftar = $model->daftar($_GET);
        $nomorHalaman = $daftar['halaman'];
        $detailDiminta = array_key_exists('detail', $_GET);
        $id = $detailDiminta && is_string($_GET['detail']) ? filter_var($_GET['detail'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        $detail = $id !== false ? $model->cari($id) : null;
        $hapusDiminta = array_key_exists('hapus', $_GET);
        $idHapus = $hapusDiminta && is_string($_GET['hapus']) ? filter_var($_GET['hapus'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        $hapus = !$detailDiminta && $idHapus !== false ? $model->cari($idHapus) : null;
        $data = ['title' => 'Akun Pelanggan', 'halaman' => 'akun-pelanggan', 'akun' => $akun,
            'token' => $_SESSION['app_superadmin_token'], 'detailPelanggan' => $detail, 'hapusPelanggan' => $hapus,
            'errorPelanggan' => null, 'muatUlangPelanggan' => false, 'nomorHalaman' => $nomorHalaman,
            'jumlahHalaman' => $daftar['jumlahHalaman'], 'jumlahPelanggan' => $daftar['jumlah'],
            'filterPelanggan' => $daftar];
        if (!$daftar['halamanValid'] || ($detailDiminta && $detail === null) || ($hapusDiminta && $hapus === null)) {
            http_response_code(404);
            $data['errorPelanggan'] = 'Pelanggan atau halaman tidak ditemukan. Pilih kembali dari daftar.';
        } elseif ($method === 'POST') {
            $token = $_POST['token_superadmin'] ?? null;
            if (!is_string($token) || !hash_equals($data['token'], $token)) {
                http_response_code(403);
                $data['errorPelanggan'] = 'Formulir tidak valid. Muat ulang halaman sebelum melanjutkan.';
                $data['muatUlangPelanggan'] = true;
            } else {
                $target = is_string($_POST['id'] ?? null) ? filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
                $aksi = $_POST['aksi'] ?? null;
                if ($aksi === 'ubah-sandi' && $detail !== null && $target === $detail['id']) {
                    $hasil = $model->ubahSandi($target, $_POST);
                } elseif ($aksi === 'hapus' && $hapus !== null && !$hapus['contoh']) {
                    $versi = is_string($_POST['versi'] ?? null) ? $_POST['versi'] : null;
                    $hasil = $model->hapus($idHapus, $versi);
                } else {
                    $hasil = ['error' => 'Pilih tindakan yang tersedia pada akun pelanggan.', 'kode' => 400];
                }
                if ($hasil['error'] === null) {
                    $pelangganTarget = $hapus ?? $detail;
                    $identitas = 'Akun pelanggan “' . $pelangganTarget['nama'] . '” (' . $pelangganTarget['email'] . ')';
                    $_SESSION['app_superadmin_pelanggan_pesan'] = $aksi === 'hapus'
                        ? $identitas . ' berhasil dihapus.'
                        : 'Kata sandi untuk ' . $identitas . ' berhasil diganti.';
                    $terbaru = $model->daftar($_GET);
                    $parameter = ['page' => 'superadmin-akun-pelanggan', 'cari' => $terbaru['cari'], 'halaman' => $terbaru['halaman']];
                    if ($detail !== null) $parameter['detail'] = $detail['id'];
                    header('Location: ' . BASE_URL . '/index.php?' . http_build_query($parameter), true, 303);
                    return;
                }
                http_response_code($hasil['kode']);
                $data['errorPelanggan'] = $hasil['error'];
                $data['muatUlangPelanggan'] = $hasil['kode'] === 409;
            }
        }
        $data['daftarPelanggan'] = $daftar['items'];
        $data['pesanPelanggan'] = $_SESSION['app_superadmin_pelanggan_pesan'] ?? null;
        unset($_SESSION['app_superadmin_pelanggan_pesan']);
        $data += (new DashboardSuperAdmin())->ringkasan();
        if ($hapus !== null && !$hapus['contoh'] && $daftar['halamanValid']) {
            $data['title'] = 'Hapus Akun Pelanggan';
            $data['pesananAktif'] = $model->pesananAktif($hapus['email']);
            require APP_PATH . '/views/superadmin/akun/hapus.php';
            return;
        }
        if ($detail !== null) {
            $data['title'] = 'Detail Pelanggan';
            $data['dapatUbahSandi'] = $model->dapatUbahSandi($detail);
        }
        require APP_PATH . '/views/superadmin/akun/' . ($detail !== null ? 'pelanggan-detail.php' : 'pelanggan.php');
    }

    private function akunSelesai(string $pesan): void
    {
        $_SESSION['app_superadmin_akun_pesan'] = $pesan;
        header('Location: ' . BASE_URL . '/index.php?page=superadmin-akun-admin', true, 303);
    }
}
