<?php

/** Akses pesanan selalu dibatasi oleh identitas pelanggan yang aktif. */
class Pesanan
{
    public const KODE_ANTAR = 'ayu';
    public const NAMA_ANTAR = 'Antar Ayu Cake';
    public const MAKSIMAL_TAGIHAN = 99_999_999_999_999;
    public const PENGIRIMAN_DITOLAK = 'Pengiriman ditolak admin';
    public const BUKTI_DITOLAK_JADWAL_LEWAT = 'Bukti ditolak setelah tanggal booking';
    public const AREA_ANTAR = 'Bukit Terap menuju Toboali dan sekitar Kecamatan Tukak Sadai';
    public const ALASAN_TENGGAT = 'Pesanan dibatalkan otomatis karena batas waktu pembayaran telah berakhir. Silakan buat pesanan baru jika masih ingin memesan.';
    public const ALASAN_JADWAL_PENGIRIMAN = 'Tanggal pengiriman sudah lewat. Pembayaran tidak dapat dibuka. Hubungi Ayu Cake jika masih ingin memesan.';

    public static function namaLayanan(string $penerimaan, ?string $pengiriman): string
    {
        if ($penerimaan === 'ambil') return 'Ambil di Toko';
        return $pengiriman === self::KODE_ANTAR ? self::NAMA_ANTAR : 'Kirim ke Alamat';
    }

    public static function akhirTanggalBooking(string $tanggal): DateTimeImmutable
    {
        return (new DateTimeImmutable($tanggal, new DateTimeZone('Asia/Jakarta')))->modify('+1 day');
    }

    /** Waktu aktivitas pesanan, bukan tanggal booking/penerimaan yang masih akan datang. */
    public static function waktuPembaruanTerakhir(array $pesanan): string
    {
        $waktu = [$pesanan['dibuat_pada']];
        foreach (['waktu_upload_bukti', 'dibatalkan_pada', 'tanggal_selesai'] as $key) {
            if (!empty($pesanan[$key])) $waktu[] = $pesanan[$key];
        }
        foreach (['riwayat_status', 'riwayat_admin'] as $key) foreach ($pesanan[$key] ?? [] as $aktivitas) {
            if (!empty($aktivitas['waktu'])) $waktu[] = $aktivitas['waktu'];
        }
        return max($waktu);
    }

    /** Dipanggil dalam transaksi dan kunci tulis aplikasi, sebelum membaca pesanan. */
    public static function batalkanLewatTenggat(): int
    {
        $db = Database::connection();
        if (!$db->inTransaction()) throw new LogicException('Pembatalan tenggat harus berada dalam transaksi.');
        $kedaluwarsa = $db->query("SELECT nomor,pengguna_id,jenis,versi FROM pesanan
            WHERE status='Menunggu Pembayaran' AND batas_pembayaran IS NOT NULL
                AND (batas_pembayaran<=NOW() OR (jenis='booking' AND tanggal_pesanan<CURDATE()))
                AND total_pembayaran IS NOT NULL
                AND ongkir_final IS NOT NULL FOR UPDATE")->fetchAll();
        $waktu = $db->query('SELECT NOW()')->fetchColumn();
        foreach ($kedaluwarsa as $pesanan) {
            $nomor = $pesanan['nomor'];
            $versi = (int) $pesanan['versi'] + 1;
            $db->prepare("UPDATE pesanan SET status='Dibatalkan',dibatalkan_pada=?,versi=? WHERE nomor=?")
                ->execute([$waktu, $versi, $nomor]);
            if ($pesanan['jenis'] === 'pesan-sekarang') {
                $db->prepare('UPDATE produk p JOIN detail_pesanan d ON d.produk_id=p.id
                    SET p.stok=p.stok+d.jumlah,p.versi=p.versi+1 WHERE d.nomor=?')->execute([$nomor]);
            }
            // Kapasitas booking dihitung dari pesanan yang belum dibatalkan.
            $urutan = $db->prepare('SELECT COALESCE(MAX(urutan),-1)+1 FROM riwayat_pesanan WHERE nomor=?');
            $urutan->execute([$nomor]);
            $db->prepare('INSERT INTO riwayat_pesanan(nomor,urutan,status,waktu,keterangan) VALUES(?,?,?,?,?)')
                ->execute([$nomor, $urutan->fetchColumn(), 'Dibatalkan', $waktu, self::ALASAN_TENGGAT]);
            $db->prepare('INSERT INTO aktivitas(id,nomor,waktu,admin,admin_key,peran,aktivitas,target,keterangan,status,perubahan) VALUES(?,?,?,?,?,?,?,?,?,?,?)')
                ->execute(['tenggat-'.bin2hex(random_bytes(12)), $nomor, $waktu, 'Sistem', 'sistem', 'Sistem',
                    'Pesanan dibatalkan otomatis', 'Pesanan '.$nomor, self::ALASAN_TENGGAT, 'Dibatalkan', '[]']);
            $notifikasi = $db->prepare('INSERT IGNORE INTO notifikasi(kunci,nomor,peran,penerima_id,pesan,jenis) VALUES(?,?,?,?,?,?)');
            $notifikasi->execute([$nomor.':'.$versi.':pelanggan', $nomor, 'pelanggan', $pesanan['pengguna_id'], self::ALASAN_TENGGAT, 'pembayaran-kedaluwarsa']);
            $notifikasi->execute([$nomor.':'.$versi.':admin', $nomor, 'admin', null, self::ALASAN_TENGGAT, 'pembayaran-kedaluwarsa']);
        }
        return count($kedaluwarsa);
    }

    public static function pesanPembatalan(array $pesanan): string
    {
        foreach (array_reverse($pesanan['riwayat_admin'] ?? []) as $aktivitas) {
            if (($aktivitas['peran'] ?? '') === 'Sistem'
                && ($aktivitas['judul'] ?? '') === 'Pesanan dibatalkan otomatis') return self::ALASAN_TENGGAT;
            if (($aktivitas['peran'] ?? '') === 'Admin'
                && !empty($aktivitas['catatan'])) {
                if (($aktivitas['judul'] ?? '') === self::BUKTI_DITOLAK_JADWAL_LEWAT) {
                    return 'Admin menolak bukti pembayaran dan pesanan dibatalkan karena tanggal booking sudah lewat. '
                        . 'Jika sudah transfer, hubungi Ayu Cake. Alasan penolakan: ' . $aktivitas['catatan'];
                }
                if (($aktivitas['judul'] ?? '') === self::PENGIRIMAN_DITOLAK) {
                    return 'Admin menolak pesanan. Alasan: ' . $aktivitas['catatan'];
                }
                if (($aktivitas['judul'] ?? '') === 'Pesanan dibatalkan') {
                    return 'Admin membatalkan pesanan. Alasan: ' . $aktivitas['catatan'];
                }
            }
        }
        return 'Pesanan telah dibatalkan. Tidak perlu melakukan pembayaran untuk pesanan ini.';
    }

    public function semua(): array
    {
        return DataModel::$data['app_pesanan'];
    }
    public function cari(string $nomor): ?array
    {
        return $this->semua()[$nomor] ?? null;
    }
    public function pemeriksaanPembayaran(string $nomor): ?array
    {
        if ($this->cari($nomor) === null) return null;
        return DataModel::$data['app_admin_verifikasi'][$nomor] ?? null;
    }
    public function simpan(array $pesanan): void
    {
        if (!isset($_SESSION['app_pelanggan'])) throw new RuntimeException('Masuk sebagai pelanggan terlebih dahulu.');
        $old = $this->cari($pesanan['nomor']);
        if ($old !== null && $old['status'] !== 'Menunggu Verifikasi' && $pesanan['status'] === 'Menunggu Verifikasi') {
            unset(DataModel::$data['app_admin_verifikasi'][$pesanan['nomor']]);
        }
        if ($old !== null && $old !== $pesanan) $pesanan['versi'] = ($old['versi'] ?? 0) + 1;
        DataModel::$data['app_pesanan'][$pesanan['nomor']] = $pesanan;
    }
    public function buktiAdmin(string $nomor): ?array
    {
        if (AkunAdmin::aktif() === null) return null;
        return DataModel::allOrders()[$nomor]['bukti_pembayaran'] ?? null;
    }

    public function bolehDiselesaikan(array $pesanan): bool
    {
        return $pesanan['status'] === 'Diproses' && $pesanan['tanggal_pesanan'] <= date('Y-m-d');
    }

    public static function pesanPenyelesaian(array $pesanan): string
    {
        foreach (array_reverse($pesanan['riwayat_admin'] ?? []) as $aktivitas) {
            if (($aktivitas['status'] ?? '') !== 'Selesai') continue;
            if (($aktivitas['peran'] ?? '') === 'Pelanggan') {
                return 'Pesanan selesai setelah Anda mengonfirmasi bahwa pesanan sudah diterima.';
            }
            if (in_array($aktivitas['peran'] ?? '', ['Admin', 'Super Admin'], true)) {
                return 'Pesanan telah dinyatakan selesai oleh admin.';
            }
        }
        return 'Pesanan telah selesai.';
    }

    public function selesai(string $nomor, string $versi, bool $diterima): ?string
    {
        $p = $this->cari($nomor);
        if ($p === null) return 'Pesanan tidak ditemukan pada akun ini.';
        if ((string) ($p['versi'] ?? 0) !== $versi) return 'Pesanan sudah berubah. Muat ulang detail sebelum mengonfirmasi.';
        if (!$this->bolehDiselesaikan($p)) return 'Pesanan hanya dapat diselesaikan saat Diproses dan tanggal ambil/kirim sudah tiba.';
        if (!$diterima) return 'Konfirmasikan bahwa pesanan sudah diterima.';
        $p['status'] = 'Selesai';
        $p['tanggal_selesai'] = date('Y-m-d');
        $p['riwayat_status'][] = ['status' => 'Selesai', 'waktu' => date('Y-m-d H:i:s'), 'keterangan' => 'Pelanggan mengonfirmasi pesanan sudah diterima.'];
        $p['riwayat_admin'][] = ['log_id' => 'pesanan-'.bin2hex(random_bytes(12)), 'judul' => 'Pesanan diselesaikan pelanggan',
            'catatan' => 'Pelanggan mengonfirmasi sudah menerima pesanan.', 'status' => 'Selesai', 'waktu' => date('Y-m-d H:i:s'),
            'admin' => $_SESSION['app_pelanggan']['nama'], 'admin_key' => 'pelanggan-'.DataModel::newUserId($_SESSION['app_pelanggan']['email']), 'peran' => 'Pelanggan'];
        $this->simpan($p);
        return null;
    }

    public function bolehDibatalkan(array $pesanan): bool
    {
        return in_array($pesanan['status'], ['Menunggu Biaya Ongkir', 'Menunggu Konfirmasi Ongkir', 'Menunggu Pembayaran'], true)
            && empty($pesanan['pernah_unggah_bukti'])
            && empty($pesanan['bukti_pembayaran'])
            && empty($pesanan['waktu_upload_bukti']);
    }

    public function untukTampilan(array $pesanan): array
    {
        $pesanan = $this->siapkanData($pesanan);
        $pesanan['boleh_batal'] = $this->bolehDibatalkan($pesanan);
        $pemeriksaan = $this->pemeriksaanPembayaran($pesanan['nomor']);
        $pesanan['penolakan_bukti'] = $pesanan['status'] === 'Menunggu Pembayaran'
            && ($pemeriksaan['aksi'] ?? '') === 'tolak' ? $pemeriksaan : null;
        return $pesanan;
    }

    private function siapkanData(array $pesanan): array
    {
        require_once dirname(__DIR__) . '/katalog/Produk.php';
        if (empty($pesanan['riwayat_status'])) {
            $pesanan['riwayat_status'] = [[
                'status' => 'Pesanan berhasil dibuat',
                'waktu' => $pesanan['dibuat_pada'],
                'keterangan' => $pesanan['penerimaan'] === 'ambil'
                    ? 'Pesanan dibuat untuk diambil di toko.'
                    : 'Pesanan dibuat untuk pemeriksaan alamat dan ongkir.',
            ]];
        }

        $modelProduk = new Produk();
        $items = $pesanan['items'] ?? [$pesanan['produk']];
        foreach ($items as &$item) {
            $item['gambar'] ??= $modelProduk->cari((int) $item['id'])['gambar'] ?? '';
            $item['subtotal'] ??= $item['harga'] * $item['jumlah'];
        }
        unset($item);
        $pesanan['items'] = $items;
        $pesanan['jumlah_produk'] = count($items);
        $pesanan['jumlah_loyang'] = array_sum(array_column($items, 'jumlah'));
        return $pesanan;
    }

    public function batalkan(string $nomor): ?string
    {
        $pesanan = $this->cari($nomor);
        if ($pesanan === null) return 'Pesanan tidak ditemukan.';
        if (!$this->bolehDibatalkan($pesanan)) return 'Pesanan ini sudah tidak dapat dibatalkan.';

        $pesanan = $this->siapkanData($pesanan);
        unset($pesanan['boleh_batal'], $pesanan['penolakan_bukti']);
        $sekarang = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
        $pesanan['status'] = 'Dibatalkan';
        $pesanan['dibatalkan_pada'] = $sekarang->format('Y-m-d H:i:s');
        $pesanan['riwayat_status'][] = [
            'status' => 'Dibatalkan',
            'waktu' => $pesanan['dibatalkan_pada'],
            'keterangan' => 'Pesanan dibatalkan oleh pelanggan.',
        ];
        $this->simpan($pesanan);
        return null;
    }

    public function simpanBukti(array $pesanan, array $bukti): bool
    {
        $dikirimUlang = !empty($pesanan['pernah_unggah_bukti']);
        $pesanan = $this->siapkanData($pesanan);
        unset($pesanan['boleh_batal'], $pesanan['penolakan_bukti']);

        $sekarang = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
        $waktu = $sekarang->format('Y-m-d H:i:s');
        $pesanan['bukti_pembayaran'] = $bukti;
        $pesanan['pernah_unggah_bukti'] = true;
        $pesanan['waktu_upload_bukti'] = $waktu;
        $pesanan['status'] = 'Menunggu Verifikasi';
        $pesanan['riwayat_status'][] = [
            'status' => 'Menunggu Verifikasi',
            'waktu' => $waktu,
            'keterangan' => $dikirimUlang ? 'Bukti pembayaran dikirim ulang oleh pelanggan.' : 'Bukti pembayaran dikirim oleh pelanggan.',
        ];
        $this->simpan($pesanan);
        return $dikirimUlang;
    }

    public static function waktuAkhir(array $pesanan): ?string
    {
        $key = $pesanan['status'] === 'Selesai' ? 'selesai_pada' : 'dibatalkan_pada';
        if (isset($pesanan[$key])) return $pesanan[$key];
        foreach (array_reverse($pesanan['riwayat_status']) as $riwayat) {
            if ($riwayat['status'] === $pesanan['status']) return $riwayat['waktu'];
        }
        return null;
    }
}
