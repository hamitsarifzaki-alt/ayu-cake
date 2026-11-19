<?php

require_once APP_PATH . '/models/katalog/Produk.php';
require_once APP_PATH . '/models/booking/AdminBooking.php';
require_once APP_PATH . '/models/pesanan/AdminPesanan.php';
require_once APP_PATH . '/models/pesanan/Pesanan.php';
require_once APP_PATH . '/models/toko/ProfilToko.php';

/** Data dashboard sendiri, tanpa mutasi model produk maupun pesanan pelanggan. */
class DashboardAdmin
{
    public function ringkasan(): array
    {
        $hari = new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta'));
        $produk = (new Produk())->semua();
        $pesanan = [];
        $profilAdmin = [];
        foreach (Database::connection()->query('SELECT pelanggan.email AS email_pelanggan, admin.nama, COALESCE(admin.email_admin_dihapus, admin.email) AS email
            FROM profil_belanja_admin tautan JOIN pengguna pelanggan ON pelanggan.id=tautan.pelanggan_id
            JOIN pengguna admin ON admin.id=tautan.admin_id')->fetchAll() as $profil) {
            $profilAdmin[$profil['email_pelanggan']] = $profil;
        }
        foreach (DataModel::allOrders() as $p) {
            $akun = DataModel::$data['app_kelola_pelanggan']['items'][$p['email']] ?? [];
            $profil = DataModel::$data['app_akun'][$p['email']]['profil'] ?? [];
            $emailPemilik = $akun['email_pelanggan_dihapus'] ?? $p['email'];
            $pemilik = ['id' => $akun['id'] ?? null, 'nama' => $profil['nama'] ?? '', 'email' => $emailPemilik,
                'telepon' => $profil['telepon'] ?? '', 'profil_admin' => isset($profilAdmin[$p['email']])];
            if ($pemilik['profil_admin']) {
                $pemilik['nama'] = $profilAdmin[$p['email']]['nama'];
                $pemilik['email'] = $profilAdmin[$p['email']]['email'];
            }
            $layanan = Pesanan::namaLayanan($p['penerimaan'], $p['pengiriman']);
            $pesanan[] = (new AdminPesanan())->terapkan([
                'nomor' => $p['nomor'], 'nama' => $p['nama_penerima'], 'email' => $emailPemilik, 'telepon' => $p['telepon'], 'alamat' => $p['alamat'],
                'pemilik_akun' => $pemilik, 'diperbarui_pada' => Pesanan::waktuPembaruanTerakhir($p),
                'jenis' => $p['jenis'] === 'booking' ? 'Booking' : 'Pesan Sekarang', 'layanan' => $layanan,
                'catatan_pelanggan' => $p['catatan'], 'tanggal' => $p['tanggal_pesanan'], 'tanggal_dibuat' => $p['dibuat_pada'],
                'tanggal_selesai' => $p['tanggal_selesai'] ?? null, 'status' => $p['status'], 'items' => $p['items'], 'subtotal' => $p['subtotal'],
                'ongkir' => $p['ongkir_final'], 'total' => $p['total_pembayaran'], 'pembayaran' => ProfilToko::namaPembayaran($p['pembayaran']),
                'tujuan_bank' => $p['tujuan_bank'] ?? null, 'rekening_tersedia' => ProfilToko::rekeningPesananTersedia($p),
                'bukti' => isset($p['bukti_pembayaran']) ? ['berkas' => $p['bukti_pembayaran']['nama'], 'waktu' => $p['waktu_upload_bukti']] : null,
                'pemeriksaan' => DataModel::$data['app_admin_verifikasi'][$p['nomor']] ?? null,
                'versi' => $p['versi'] ?? 0, 'catatan_ongkir' => $p['catatan_ongkir'] ?? '', 'riwayat' => $p['riwayat_admin'] ?? [],
                'batas_pembayaran' => $p['batas_pembayaran'],
            ]);
        }
        usort($pesanan, static fn(array $a, array $b): int =>
            strcmp($b['diperbarui_pada'], $a['diperbarui_pada'])
            ?: strcmp($b['tanggal_dibuat'], $a['tanggal_dibuat'])
            ?: strcmp($b['nomor'], $a['nomor']));
        $filter = static function (array $order, string $jenis): bool {
            switch ($jenis) {
                case 'ongkir': return in_array($order['status'], ['Menunggu Biaya Ongkir', 'Menunggu Konfirmasi Ongkir'], true);
                case 'verifikasi': return $order['status'] === 'Menunggu Verifikasi';
                case 'diproses': return $order['status'] === 'Diproses';
                case 'selesai': return $order['status'] === 'Selesai';
                default: return true;
            }
        };
        $angka = [];
        foreach (['ongkir', 'verifikasi', 'diproses', 'selesai'] as $key) {
            $angka[$key] = count(array_filter($pesanan, static fn ($p) => $filter($p, $key)));
        }
        $prioritas = array_values(array_filter($pesanan, static fn ($p) => $filter($p, 'ongkir') || $filter($p, 'verifikasi')));
        $selesai = array_values(array_filter($pesanan, static fn ($p) => $filter($p, 'selesai')));
        return [
            'hari' => $hari->format('Y-m-d'), 'produk' => $produk, 'pesanan' => $pesanan,
            'angka' => $angka, 'prioritas' => array_slice($prioritas, 0, 3),
            'booking' => (new AdminBooking($hari))->ringkasan($pesanan),
            'penjualan' => ['pesanan' => count($selesai), 'loyang' => array_sum(array_map(static fn ($p) => array_sum(array_column($p['items'], 'jumlah')), $selesai)), 'produk' => array_sum(array_column($selesai, 'subtotal')), 'ongkir' => array_sum(array_column($selesai, 'ongkir'))],
        ];
    }

    public function pembayaran(array $pesanan, array $input): array
    {
        $cari = mb_substr(is_string($input['cari'] ?? null) ? trim($input['cari']) : '', 0, 120);
        $menunggu = array_values(array_filter($pesanan, static fn ($p) => $p['status'] === 'Menunggu Verifikasi' && $p['bukti'] !== null));
        $hasil = array_values(array_filter($menunggu, static fn ($p) => $cari === '' || mb_stripos($p['nomor'] . ' ' . $p['nama'], $cari) !== false));
        return ['cari' => $cari, 'items' => $hasil, 'jumlah' => count($hasil), 'total' => count($menunggu)];
    }

    public function putuskan(array $pesanan, ?string $versi, string $aksi, string $catatan, bool $cocok): array
    {
        $gagal = static fn (string $pesan, int $kode = 400): array => ['error' => $pesan, 'kode' => $kode];
        if ($pesanan['status'] !== 'Menunggu Verifikasi') {
            return $gagal('Bukti ini sudah diperiksa atau tidak tersedia. Kembali ke daftar untuk melihat status terbaru.', 409);
        }

        if ($versi !== (string) $pesanan['versi']) {
            return $gagal('Bukti atau pesanan sudah berubah. Periksa bukti terbaru sebelum mengambil keputusan.', 409);
        }
        if (!in_array($aksi, ['terima', 'tolak'], true)) return $gagal('Pilih tindakan pemeriksaan yang valid.');
        if (mb_strlen($catatan) > 500) return $gagal('Catatan maksimal 500 karakter.');
        if ($aksi === 'terima' && !$cocok) return $gagal('Centang konfirmasi setelah mencocokkan bukti dan total tagihan.');
        if ($aksi === 'tolak' && $catatan === '') return $gagal('Isi alasan penolakan agar pelanggan mengetahui yang perlu diperbaiki.');
        $sekarang = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
        DataModel::$data['app_admin_verifikasi'][$pesanan['nomor']] = [
            'aksi' => $aksi, 'catatan' => $catatan, 'waktu' => $sekarang->format('Y-m-d H:i:s'),
        ];
        $adminPesanan = new AdminPesanan();
        if ($aksi === 'terima') {
            $adminPesanan->catat($pesanan, ['status' => 'Diproses'], 'Pembayaran diterima', $catatan);
            return ['error' => null, 'pesan' => 'Pembayaran diterima. Pesanan masuk ke status Diproses.'];
        }

        $batas = $sekarang->modify('+1 day');
        if ($pesanan['jenis'] === 'Booking') {
            $batas = min($batas, Pesanan::akhirTanggalBooking($pesanan['tanggal']));
            if ($batas <= $sekarang) {
                $adminPesanan->catat($pesanan, ['status' => 'Dibatalkan', 'dibatalkan_pada' => $sekarang->format('Y-m-d H:i:s')],
                    Pesanan::BUKTI_DITOLAK_JADWAL_LEWAT, $catatan);
                return ['error' => null, 'pesan' => 'Bukti ditolak dan pesanan dibatalkan karena tanggal booking sudah lewat.'];
            }
        }
        $adminPesanan->catat($pesanan, ['status' => 'Menunggu Pembayaran', 'batas_pembayaran' => $batas->format('Y-m-d H:i:s')],
            'Bukti pembayaran ditolak', $catatan);
        return ['error' => null, 'pesan' => 'Bukti ditolak. Pesanan kembali ke status Menunggu Pembayaran dengan batas unggah ulang baru.'];
    }

    /** Filter GET dan pagination tetap bekerja tanpa JavaScript. */
    public function daftar(array $pesanan, array $input): array
    {
        $status = ['semua' => 'Semua status', 'ongkir' => 'Periksa Ongkir',
            'biaya-ongkir' => 'Menunggu Biaya Ongkir', 'konfirmasi-ongkir' => 'Menunggu Konfirmasi Ongkir',
            'pembayaran' => 'Menunggu Pembayaran', 'verifikasi' => 'Menunggu Verifikasi',
            'diproses' => 'Diproses', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];
        $jenis = ['semua' => 'Semua jenis', 'booking' => 'Booking', 'ready-stock' => 'Pesan Sekarang'];
        $teks = static fn (string $key): string => is_string($input[$key] ?? null) ? trim($input[$key]) : '';
        $cari = mb_substr($teks('cari'), 0, 120);
        $pilihStatus = array_key_exists($teks('status'), $status) ? $teks('status') : 'semua';
        $pilihJenis = array_key_exists($teks('jenis'), $jenis) ? $teks('jenis') : 'semua';
        $hasil = array_values(array_filter($pesanan, static function ($p) use ($cari, $pilihStatus, $pilihJenis, $status, $jenis): bool {
            $sesuaiStatus = $pilihStatus === 'semua'
                || ($pilihStatus === 'ongkir' ? in_array($p['status'], ['Menunggu Biaya Ongkir', 'Menunggu Konfirmasi Ongkir'], true) : $p['status'] === $status[$pilihStatus]);
            return $sesuaiStatus && ($pilihJenis === 'semua' || $p['jenis'] === $jenis[$pilihJenis])
                && ($cari === '' || mb_stripos($p['nomor'] . ' ' . $p['nama'], $cari) !== false);
        }));
        $jumlah = count($hasil);
        $halamanTotal = max(1, (int) ceil($jumlah / 7));
        $diminta = filter_var($teks('halaman'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $halaman = min($halamanTotal, $diminta);
        return ['items' => array_slice($hasil, ($halaman - 1) * 7, 7), 'jumlah' => $jumlah,
            'total' => count($pesanan), 'halaman' => $halaman, 'halamanTotal' => $halamanTotal,
            'cari' => $cari, 'status' => $pilihStatus, 'jenis' => $pilihJenis,
            'opsiStatus' => $status, 'opsiJenis' => $jenis];
    }
}
