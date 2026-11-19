<?php

require_once dirname(__DIR__) . '/akun/AkunAdmin.php';
require_once __DIR__ . '/Pesanan.php';

/** Perubahan pesanan milik sesi admin, terpisah dari checkout pelanggan. */
class AdminPesanan
{
    public function terapkan(array $pesanan): array
    {
        $tersimpan = DataModel::$data['app_admin_pesanan'][$pesanan['nomor']] ?? [];
        return array_replace($pesanan + ['versi' => 0, 'catatan_ongkir' => '', 'riwayat' => [],
            'layanan_pelanggan' => $pesanan['layanan'], 'batas_pembayaran' => null], $tersimpan);
    }

    public function ongkirDapatDiubah(array $p): bool
    {
        return $p['layanan'] !== 'Ambil di Toko' && ($p['bukti'] ?? null) === null
            && in_array($p['status'], ['Menunggu Biaya Ongkir', 'Menunggu Konfirmasi Ongkir'], true);
    }

    public function pilihanStatus(array $p): array
    {
        $hari = (new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
        return $p['status'] === 'Diproses' && $p['tanggal'] <= $hari ? ['Selesai'] : [];
    }

    public function simpan(array $p, array $input): array
    {
        $gagal = static fn (string $pesan, int $kode = 400): array => ['error' => $pesan, 'kode' => $kode];
        if (!is_string($input['versi'] ?? null) || $input['versi'] !== (string) $p['versi']) {
            return $gagal('Pesanan sudah berubah atau formulir sudah kedaluwarsa. Muat ulang detail sebelum menyimpan.', 409);
        }
        $aksi = $input['aksi'] ?? null;
        if ($aksi === 'konfirmasi-ongkir' || $aksi === 'tolak-pengiriman') {
            return $this->putuskanPengiriman($p, $input);
        }
        if ($aksi !== 'status') return $gagal('Pilih tindakan pesanan yang valid.');
        $status = $input['status'] ?? null;
        if (!is_string($status) || !in_array($status, $this->pilihanStatus($p), true)) return $gagal('Perubahan status tersebut tidak tersedia. Ikuti alur pesanan yang ditampilkan.', 409);
        $catatan = $input['catatan_status'] ?? null;
        if (!is_string($catatan) || mb_strlen(trim($catatan)) > 500) return $gagal('Catatan status maksimal 500 karakter.');
        $catatan = trim($catatan);
        if (($input['konfirmasi'] ?? null) !== '1') return $gagal('Konfirmasikan bahwa pesanan sudah diterima pelanggan.');
        $tanggal = new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta'));
        $this->catat($p, ['status' => 'Selesai', 'tanggal_selesai' => $tanggal->format('Y-m-d')], 'Pesanan selesai', $catatan);
        return ['error' => null, 'pesan' => 'Pesanan ditandai selesai dan masuk ke Laporan Penjualan.'];
    }

    private function putuskanPengiriman(array $p, array $input): array
    {
        if (!$this->ongkirDapatDiubah($p)) {
            return ['error' => 'Pesanan ini sudah melewati tahap pemeriksaan alamat dan ongkir.', 'kode' => 409];
        }
        $sekarang = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
        if ($input['aksi'] === 'tolak-pengiriman') {
            $alasan = is_string($input['catatan_status'] ?? null) ? trim($input['catatan_status']) : '';
            if ($alasan === '' || mb_strlen($alasan) > 500) {
                return ['error' => 'Isi alasan penolakan pesanan, maksimal 500 karakter.', 'kode' => 400];
            }
            if (($input['konfirmasi'] ?? null) !== '1') {
                return ['error' => 'Centang konfirmasi untuk menolak pesanan ini.', 'kode' => 400];
            }
            $this->catat($p, [
                'status' => 'Dibatalkan',
                'dibatalkan_pada' => $sekarang->format('Y-m-d H:i:s'),
                'ongkir' => null,
                'total' => null,
                'batas_pembayaran' => null,
            ], Pesanan::PENGIRIMAN_DITOLAK, $alasan);
            return ['error' => null, 'pesan' => 'Pesanan ditolak. Alasan penolakan telah disampaikan kepada pelanggan.'];
        }

        if ($p['tanggal'] < $sekarang->format('Y-m-d')) {
            return ['error' => 'Tanggal pengiriman sudah lewat. Tolak pesanan dengan menyertakan alasan.', 'kode' => 409];
        }
        if (empty($p['rekening_tersedia'])) {
            return ['error' => 'Transfer bank tidak tersedia untuk pesanan ini. Tolak pesanan dengan menyertakan alasan.', 'kode' => 409];
        }
        if (($input['konfirmasi'] ?? null) !== '1') {
            return ['error' => 'Konfirmasikan bahwa alamat sudah jelas dan berada dalam area layanan Antar Ayu Cake.', 'kode' => 400];
        }
        if (isset($input['layanan']) && $input['layanan'] !== Pesanan::NAMA_ANTAR) {
            return ['error' => 'Pengiriman hanya tersedia melalui Antar Ayu Cake.', 'kode' => 400];
        }
        $nilai = $input['ongkir'] ?? null;
        if (!is_string($nilai) || !preg_match('/\A[0-9]{1,14}\z/', $nilai)) {
            return ['error' => 'Isi ongkir dalam rupiah bulat, tanpa titik atau koma. Isi 0 jika gratis.', 'kode' => 400];
        }
        $ongkir = (int) $nilai;
        if ($ongkir > Pesanan::MAKSIMAL_TAGIHAN - $p['subtotal']) {
            return ['error' => 'Total pembayaran melebihi batas nominal yang dapat disimpan.', 'kode' => 400];
        }
        $batas = $sekarang->modify('+1 day');
        if ($p['jenis'] === 'Booking') $batas = min($batas, Pesanan::akhirTanggalBooking($p['tanggal']));
        $this->catat($p, [
            'layanan' => Pesanan::NAMA_ANTAR,
            'status' => 'Menunggu Pembayaran',
            'ongkir' => $ongkir,
            'total' => $p['subtotal'] + $ongkir,
            'batas_pembayaran' => $batas->format('Y-m-d H:i:s'),
        ], 'Ongkir dikonfirmasi', Pesanan::NAMA_ANTAR . ' • Rp' . number_format($ongkir, 0, ',', '.'));
        return ['error' => null, 'pesan' => 'Ongkir berhasil dikonfirmasi. Pesanan menunggu pembayaran pelanggan.'];
    }

    /** Pembayaran juga menaikkan versi, supaya formulir detail lama tidak menimpa hasil pemeriksaan. */
    public function catat(array $p, array $perubahan, string $judul, string $catatan): void
    {
        $riwayat = $p['riwayat'];
        $admin = AkunAdmin::aktif();
        $riwayat[] = [
            'log_id' => 'pesanan-' . bin2hex(random_bytes(12)),
            'admin' => $admin['nama'] ?? 'Sistem',
            'admin_key' => $admin === null ? 'sistem' : 'admin-' . $admin['id'],
            'peran' => $admin === null ? 'Sistem' : 'Admin',
            'judul' => $judul, 'catatan' => $catatan, 'status' => $perubahan['status'] ?? $p['status'],
            'waktu' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s')];
        $lama = DataModel::$data['app_admin_pesanan'][$p['nomor']] ?? [];
        DataModel::$data['app_admin_pesanan'][$p['nomor']] = array_replace($lama, $perubahan, ['versi' => $p['versi'] + 1, 'riwayat' => $riwayat]);
    }
}
