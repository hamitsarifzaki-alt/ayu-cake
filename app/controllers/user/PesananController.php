<?php

require_once APP_PATH . '/models/pesanan/Pesanan.php';
require_once APP_PATH . '/models/pesanan/PembuatanPesanan.php';
require_once APP_PATH . '/models/belanja/Keranjang.php';
require_once APP_PATH . '/models/toko/ProfilToko.php';

class PesananController extends Controller
{
    private function periksaPelanggan(): void
    {
        if (!is_array($_SESSION['app_pelanggan'] ?? null)) {
            header('Location: ' . BASE_URL . '/index.php?page=login');
            exit;
        }
    }

    private function teksPost(string $nama): string
    {
        $nilai = $_POST[$nama] ?? '';
        return is_string($nilai) ? trim($nilai) : '';
    }

    private function angkaPost(string $nama): ?int
    {
        $hasil = filter_var($this->teksPost($nama), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $hasil === false ? null : $hasil;
    }

    private function nomorGet(): string
    {
        return is_string($_GET['nomor'] ?? null) ? $_GET['nomor'] : '';
    }

    private function tokenPesanan(): string
    {
        if (empty($_SESSION['app_token_pesanan'])) {
            $_SESSION['app_token_pesanan'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['app_token_pesanan'];
    }

    private function tokenPesananValid(): bool
    {
        return hash_equals($this->tokenPesanan(), $this->teksPost('token_pesanan'));
    }

    private function gagal(string $pesan, int $kode = 400): void
    {
        http_response_code($kode);
        $this->view('pesanan/berhasil', [
            'title' => 'Periksa Pesanan | Ayu Cake',
            'pesanan' => null,
            'pesanError' => $pesan,
        ]);
    }

    public function buat(): void
    {
        $this->periksaPelanggan();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=katalog');
            exit;
        }
        $tokenCheckout = $_SESSION['app_token_checkout'] ?? null;
        if (!is_string($tokenCheckout) || $tokenCheckout === '' || !hash_equals($tokenCheckout, $this->teksPost('token_checkout'))) {
            $this->gagal('Formulir checkout sudah tidak valid atau pesanan telah dikirim. Buka kembali halaman checkout sebelum memesan.', 403);
            return;
        }

        $jenis = $this->teksPost('jenis');
        $sumber = $this->teksPost('sumber') ?: 'langsung';
        if (!in_array($jenis, ['booking', 'pesan-sekarang'], true)) {
            $this->gagal('Jenis pesanan tidak valid.');
            return;
        }
        if (!in_array($sumber, ['langsung', 'keranjang'], true)) {
            $this->gagal('Sumber pesanan tidak valid.');
            return;
        }

        $keranjang = new Keranjang();
        $pilihan = $sumber === 'keranjang' ? $keranjang->data() : [($this->angkaPost('id_produk') ?? 0) => $this->angkaPost('jumlah')];
        $isian = [];
        foreach (['tanggal', 'nama_penerima', 'telepon', 'alamat', 'catatan', 'penerimaan', 'pengiriman', 'pembayaran'] as $kolom) {
            $isian[$kolom] = $this->teksPost($kolom);
        }
        try {
            $pesanan = (new PembuatanPesanan())->siapkan($jenis, $pilihan, $isian);
        } catch (DomainException $error) {
            $this->gagal($error->getMessage());
            return;
        }

        unset($_SESSION['app_token_checkout']);
        (new Pesanan())->simpan($pesanan);
        if ($sumber === 'keranjang') $keranjang->kosongkan();
        header('Location: ' . BASE_URL . '/index.php?page=pesanan-berhasil&nomor=' . rawurlencode($pesanan['nomor']), true, 303);
        exit;
    }

    public function berhasil(): void
    {
        $this->periksaPelanggan();
        $model = new Pesanan();
        $pesanan = $model->cari($this->nomorGet());
        if ($pesanan === null) {
            http_response_code(404);
            $this->view('pesanan/berhasil', [
                'title' => 'Pesanan Tidak Ditemukan | Ayu Cake',
                'pesanan' => null,
                'pesanError' => 'Pesanan tidak ditemukan. Silakan buat pesanan melalui halaman checkout.',
            ]);
            return;
        }
        $this->view('pesanan/berhasil', [
            'title' => 'Pesanan Berhasil | Ayu Cake',
            'pesanan' => $model->untukTampilan($pesanan),
            'pesanError' => null,
        ]);
    }

    private function urutkanPembaruanTerbaru(array $daftar): array
    {
        foreach ($daftar as &$pesanan) {
            $pesanan['diperbarui_pada'] = Pesanan::waktuPembaruanTerakhir($pesanan);
        }
        unset($pesanan);
        usort($daftar, static function (array $a, array $b): int {
            return strcmp($b['diperbarui_pada'], $a['diperbarui_pada'])
                ?: strcmp($b['dibuat_pada'], $a['dibuat_pada']);
        });
        return $daftar;
    }

    public function status(): void
    {
        $this->periksaPelanggan();
        $model = new Pesanan();
        $aktif = array_filter($model->semua(), static fn(array $p): bool => !in_array($p['status'], ['Selesai', 'Dibatalkan'], true));
        $daftar = array_map([$model, 'untukTampilan'], array_values($aktif));
        $this->view('pesanan/status', [
            'title' => 'Status Pesanan | Ayu Cake',
            'daftarPesanan' => $this->urutkanPembaruanTerbaru($daftar),
            'tokenPesanan' => $this->tokenPesanan(),
        ]);
    }

    public function riwayat(): void
    {
        $this->periksaPelanggan();
        $model = new Pesanan();
        $daftar = [];
        foreach ($model->semua() as $pesanan) {
            if (!in_array($pesanan['status'], ['Selesai', 'Dibatalkan'], true)) continue;
            $pesanan = $model->untukTampilan($pesanan);
            $pesanan['waktu_akhir'] = Pesanan::waktuAkhir($pesanan);
            $daftar[] = $pesanan;
        }
        $this->view('pesanan/riwayat', [
            'title' => 'Riwayat Pesanan | Ayu Cake',
            'daftarPesanan' => $this->urutkanPembaruanTerbaru($daftar),
        ]);
    }

    public function detail(): void
    {
        $this->periksaPelanggan();
        $model = new Pesanan();
        $nomor = $this->nomorGet();
        $pesanan = $model->cari($nomor);
        if ($pesanan === null) {
            http_response_code(404);
        } elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
            require_once APP_PATH . '/models/notifikasi/Notifikasi.php';
            Notifikasi::baca('pelanggan', $nomor);
        }
        $pesanBukti = ($pesanan['status'] ?? null) === 'Menunggu Verifikasi'
            ? ($_SESSION['app_pesan_bukti'][$nomor] ?? null) : null;
        unset($_SESSION['app_pesan_bukti'][$nomor]);
        $this->view('pesanan/detail', [
            'title' => 'Detail Pesanan | Ayu Cake',
            'pesanan' => $pesanan === null ? null : $model->untukTampilan($pesanan),
            'tokenPesanan' => $this->tokenPesanan(),
            'bolehSelesai' => $pesanan !== null && $model->bolehDiselesaikan($pesanan),
            'pesanBukti' => $pesanBukti,
        ]);
    }

    public function selesai(): void
    {
        $this->periksaPelanggan();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }
        if (!$this->tokenPesananValid()) {
            $this->gagal('Formulir tidak valid. Muat ulang detail pesanan.', 403);
            return;
        }
        $nomor = $this->teksPost('nomor');
        $error = (new Pesanan())->selesai($nomor, $this->teksPost('versi'), $this->teksPost('diterima') === '1');
        if ($error !== null) {
            $this->gagal($error, 409);
            return;
        }
        header('Location: ' . BASE_URL . '/index.php?page=detail-pesanan&nomor=' . rawurlencode($nomor), true, 303);
    }

    public function batalkan(): void
    {
        $this->periksaPelanggan();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=status-pesanan');
            exit;
        }
        if (!$this->tokenPesananValid()) {
            $this->gagal('Formulir sudah tidak valid. Buka kembali halaman Status Pesanan.');
            return;
        }
        $nomor = $this->teksPost('nomor');
        $error = (new Pesanan())->batalkan($nomor);
        if ($error !== null) {
            $this->gagal($error);
            return;
        }
        header('Location: ' . BASE_URL . '/index.php?page=detail-pesanan&nomor=' . rawurlencode($nomor), true, 303);
        exit;
    }

    private function masalahPembayaran(array $pesanan): ?string
    {
        if ($pesanan['status'] === 'Dibatalkan') return Pesanan::pesanPembatalan($pesanan);
        if (in_array($pesanan['status'], ['Menunggu Biaya Ongkir', 'Menunggu Konfirmasi Ongkir'], true)) {
            if ($pesanan['tanggal_pesanan'] < date('Y-m-d')) return Pesanan::ALASAN_JADWAL_PENGIRIMAN;
            return 'Pembayaran tersedia setelah admin memeriksa alamat dan mengonfirmasi ongkir.';
        }
        if ($pesanan['status'] !== 'Menunggu Pembayaran') {
            return 'Pembayaran dan unggah bukti hanya tersedia ketika status pesanan Menunggu Pembayaran.';
        }

        if (
            $pesanan['total_pembayaran'] === null
            || $pesanan['ongkir_final'] === null
        ) {
            return 'Total pembayaran belum ditetapkan. Tunggu konfirmasi ongkir dari admin.';
        }

        if (empty($pesanan['batas_pembayaran'])) {
            return 'Batas pembayaran belum tersedia.';
        }

        $zona = new DateTimeZone('Asia/Jakarta');
        $sekarang = new DateTimeImmutable('now', $zona);

        $batas = new DateTimeImmutable(
            $pesanan['batas_pembayaran'],
            $zona
        );

        if ($sekarang >= $batas) {
            return 'Batas waktu pembayaran sudah berakhir. Bukti pembayaran tidak dapat dikirim.';
        }

        if (!ProfilToko::rekeningPesananTersedia($pesanan)) {
            return 'Metode pembayaran pesanan ini tidak tersedia. Hubungi Ayu Cake jika sudah membayar atau memerlukan bantuan.';
        }

        return null;
    }

    public function pembayaran(): void
    {
        $this->periksaPelanggan();
        $model = new Pesanan();
        $nomor = $this->nomorGet();
        $pesanan = $model->cari($nomor);
        $pesanError = null;
        $penghalang = null;
        if ($pesanan === null) {
            http_response_code(404);
        } else {
            $pesanan = $model->untukTampilan($pesanan);
            $penghalang = $this->masalahPembayaran($pesanan);
            $pesanError = $_SESSION['app_error_bukti'][$nomor] ?? null;
            unset($_SESSION['app_error_bukti'][$nomor]);
        }
        $this->view('pesanan/pembayaran', [
            'title' => 'Pembayaran & Bukti Bayar | Ayu Cake',
            'pesanan' => $pesanan,
            'penghalang' => $penghalang,
            'pesanError' => $pesanError,
            'tokenPesanan' => $this->tokenPesanan(),
        ]);
    }

    private function gagalUnggah(string $nomor, string $pesan): void
    {
        $_SESSION['app_error_bukti'][$nomor] = $pesan;
        header('Location: ' . BASE_URL . '/index.php?page=pembayaran&nomor=' . rawurlencode($nomor), true, 303);
        exit;
    }

    public function unggahBukti(): void
    {
        $this->periksaPelanggan();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=status-pesanan');
            exit;
        }
        if (!$this->tokenPesananValid()) {
            $this->gagal('Formulir tidak valid. Buka kembali halaman pembayaran.');
            return;
        }
        $model = new Pesanan();
        $nomor = $this->teksPost('nomor');
        $pesanan = $model->cari($nomor);
        if ($pesanan === null) {
            $this->gagal('Pesanan tidak ditemukan.');
            return;
        }
        $masalah = $this->masalahPembayaran($pesanan);
        if ($masalah !== null) $this->gagalUnggah($nomor, $masalah);

        $berkas = $_FILES['bukti'] ?? null;
        if (!is_array($berkas) || !is_int($berkas['error'] ?? null)) {
            $this->gagalUnggah($nomor, 'Pilih gambar bukti pembayaran.');
        }
        if ($berkas['error'] !== UPLOAD_ERR_OK) {
            $this->gagalUnggah($nomor, 'Berkas gagal diterima. Pilih gambar JPG atau PNG maksimal 2 MB.');
        }
        $lokasi = $berkas['tmp_name'] ?? null;
        if (!is_string($lokasi) || !is_uploaded_file($lokasi)) {
            $this->gagalUnggah($nomor, 'Berkas unggahan tidak valid.');
        }
        $ukuran = filesize($lokasi);
        if ($ukuran === false || $ukuran < 1 || $ukuran > 2 * 1024 * 1024) {
            $this->gagalUnggah($nomor, 'Ukuran gambar harus lebih dari 0 dan maksimal 2 MB.');
        }
        $infoGambar = @getimagesize($lokasi);
        $mime = $infoGambar['mime'] ?? '';
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            $this->gagalUnggah($nomor, 'Bukti pembayaran harus berupa gambar JPG atau PNG yang valid.');
        }

        $namaBerkas = is_string($berkas['name'] ?? null)
            ? basename(str_replace('\\', '/', $berkas['name'])) : 'bukti-pembayaran';
        $dikirimUlang = $model->simpanBukti($pesanan, [
            'nama' => $namaBerkas,
            'ukuran' => $ukuran,
            'mime' => $mime,
            'isi' => file_get_contents($lokasi),
        ]);
        $_SESSION['app_pesan_bukti'][$nomor] = $dikirimUlang
            ? 'Bukti pembayaran berhasil dikirim ulang. Menunggu pemeriksaan admin.'
            : 'Bukti pembayaran berhasil dikirim. Menunggu pemeriksaan admin.';
        header('Location: ' . BASE_URL . '/index.php?page=detail-pesanan&nomor=' . rawurlencode($nomor), true, 303);
        exit;
    }
}
