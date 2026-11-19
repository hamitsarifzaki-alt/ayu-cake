<?php

require_once dirname(__DIR__, 2) . '/core/Database.php';

/**
 * Data mapper dan Unit of Work untuk model domain.
 * Array hanya hidup selama satu request. Sumber data permanen adalah tabel relasional,
 * bukan sesi browser. Perubahan disimpan bersama dalam satu transaksi InnoDB.
 */
final class DataModel
{
    public static array $data = [];
    private static array $before = [];
    private static array $recordsBefore = [];
    private static bool $started = false;
    private static bool $finished = false;
    private static array $userIds = [];
    private static int $nextUser = 1;

    public static function begin(): void
    {
        $db = Database::connection();
        // Kunci lintas browser melindungi snapshot stok, kuota, dan versi perubahan.
        // Semua akses tulis aplikasi harus melalui Unit of Work ini.
        if ((int) $db->query("SELECT GET_LOCK('ayu_cake_write', 15)")->fetchColumn() !== 1) {
            throw new RuntimeException('Sistem sedang sibuk. Silakan coba kembali.', 503);
        }
        $db->beginTransaction();
        self::$started = true;
        self::$finished = false;
        require_once dirname(__DIR__) . '/pesanan/Pesanan.php';
        Pesanan::batalkanLewatTenggat();
        self::load();
        self::$before = unserialize(serialize(self::$data));
        self::$recordsBefore = self::records();
    }

    private static function rows(string $table): array
    {
        return Database::connection()->query('SELECT * FROM `' . $table . '`')->fetchAll();
    }

    private static function load(): void
    {
        self::$data = [
            'app_akun' => [], 'app_kelola_admin' => ['items' => [], 'berikutnya' => 1],
            'app_kelola_pelanggan' => ['items' => [], 'berikutnya' => 1],
            'app_admin_produk' => ['items' => [], 'berikutnya' => 1, 'kategori_versi' => 1],
            'app_admin_kategori' => ['items' => [], 'berikutnya' => 1],
            'app_admin_booking' => [], 'app_admin_pesanan' => [], 'app_admin_verifikasi' => [],
            'app_faq' => ['items' => [], 'berikutnya' => 1], 'app_profil_toko' => [], 'app_bank_toko' => [],
            'app_pesanan' => [], 'app_keranjang' => [],
        ];
        self::$userIds = [];
        foreach (self::rows('pengguna') as $u) {
            $id = (int) $u['id'];
            self::$nextUser = max(self::$nextUser, $id + 1);
            self::$userIds[$u['email']] = $id;
            if ($u['peran'] === 'pelanggan') {
                $profile = array_intersect_key($u, array_flip(['nama', 'email', 'telepon']));
                self::$data['app_akun'][$u['email']] = ['profil' => $profile, 'hash' => $u['password_hash'],
                    'auth_versi' => (int) $u['auth_versi'], 'data' => ['app_keranjang' => [], 'app_pesanan' => []]];
                self::$data['app_kelola_pelanggan']['items'][$u['email']] = [
                    'id' => $id, 'email' => $u['email'], 'profil' => $profile, 'aktif' => (bool) $u['aktif'], 'versi' => (int) $u['versi'],
                    'email_pelanggan_dihapus' => $u['email_pelanggan_dihapus']];
            } elseif ($u['peran'] === 'admin') {
                self::$data['app_kelola_admin']['items'][$id] = ['id' => $id, 'nama' => $u['nama'], 'email' => $u['email'],
                    'hash' => $u['password_hash'], 'aktif' => (bool) $u['aktif'], 'versi' => (int) $u['versi'], 'auth_versi' => (int) $u['auth_versi'],
                    'email_admin_dihapus' => $u['email_admin_dihapus']];
            } else {
                self::$data['superadmin'] = $u;
            }
        }
        self::$data['app_kelola_admin']['berikutnya'] = self::$nextUser;
        self::$data['app_kelola_pelanggan']['berikutnya'] = self::$nextUser;
        foreach (self::rows('kategori') as $k) {
            $k['versi'] = (int) $k['versi'];
            $k['dihapus'] = (bool) $k['dihapus'];
            self::$data['app_admin_kategori']['items'][$k['id']] = $k;
            if (preg_match('/^kategori-(\d+)$/', $k['id'], $m)) self::$data['app_admin_kategori']['berikutnya'] = max(self::$data['app_admin_kategori']['berikutnya'], (int) $m[1] + 1);
        }
        foreach (self::rows('produk') as $p) {
            foreach (['id', 'harga', 'stok', 'versi'] as $key) $p[$key] = (int) $p[$key];
            $p['aktif'] = (bool) $p['aktif'];
            $p['dihapus'] = (bool) $p['dihapus'];
            $p['kategori'] = $p['kategori_id'];
            unset($p['kategori_id']);
            $p['foto'] = $p['foto'] === null ? null : ['mime' => $p['foto_mime'], 'data' => $p['foto']];
            unset($p['foto_mime']);
            self::$data['app_admin_produk']['items'][$p['id']] = $p;
            self::$data['app_admin_produk']['berikutnya'] = max(self::$data['app_admin_produk']['berikutnya'], $p['id'] + 1);
        }
        $emails = array_flip(self::$userIds);
        foreach (self::rows('keranjang') as $r) {
            $email = $emails[$r['pengguna_id']];
            self::$data['app_akun'][$email]['data']['app_keranjang'][(int) $r['produk_id']] = (int) $r['jumlah'];
        }
        foreach (self::rows('pesanan') as $p) {
            $p['tujuan_bank'] = isset($p['tujuan_bank']) ? json_decode($p['tujuan_bank'], true, 512, JSON_THROW_ON_ERROR) : null;
            $email = $emails[$p['pengguna_id']];
            unset($p['pengguna_id']);
            foreach (['subtotal', 'estimasi_ongkir', 'ongkir_final', 'total_pembayaran', 'versi'] as $key) if ($p[$key] !== null) $p[$key] = (int) $p[$key];
            $p += ['items' => [], 'riwayat_status' => [], 'riwayat_admin' => []];
            self::$data['app_akun'][$email]['data']['app_pesanan'][$p['nomor']] = $p;
        }
        $orders = [];
        foreach (self::$data['app_akun'] as &$a) foreach ($a['data']['app_pesanan'] as $no => &$p) $orders[$no] = &$p;
        unset($p, $a);
        foreach (self::rows('detail_pesanan') as $r) {
            $p = &$orders[$r['nomor']];
            unset($r['nomor']);
            $r['id'] = (int) $r['produk_id'];
            unset($r['produk_id']);
            foreach (['harga', 'jumlah', 'subtotal'] as $key) $r[$key] = (int) $r[$key];
            $p['items'][] = $r;
        }
        foreach (self::rows('pembayaran') as $r) {
            $p = &$orders[$r['nomor']];
            $p['bukti_pembayaran'] = ['nama' => $r['nama_berkas'], 'ukuran' => (int) $r['ukuran'], 'mime' => $r['mime'], 'isi' => $r['isi']];
            $p['waktu_upload_bukti'] = $r['waktu'];
            $p['pernah_unggah_bukti'] = true;
            if ($r['keputusan']) self::$data['app_admin_verifikasi'][$r['nomor']] = ['aksi' => $r['keputusan'], 'catatan' => $r['catatan'], 'waktu' => $r['diperiksa_pada']];
        }
        foreach (self::rows('riwayat_pesanan') as $r) {
            $orders[$r['nomor']]['riwayat_status'][(int) $r['urutan']] = array_intersect_key($r, array_flip(['status', 'waktu', 'keterangan']));
        }
        // Tabel yang sama masih menyimpan riwayat status untuk tiap pesanan.
        $riwayat = Database::connection()->query('SELECT * FROM aktivitas WHERE nomor IS NOT NULL');
        foreach ($riwayat as $r) {
            if (!isset($orders[$r['nomor']])) continue;
            $orders[$r['nomor']]['riwayat_admin'][] = ['log_id' => $r['id'], 'judul' => $r['aktivitas'], 'catatan' => $r['keterangan']]
                + array_intersect_key($r, array_flip(['status', 'waktu', 'admin', 'admin_key', 'peran', 'perubahan']));
        }
        unset($p);
        foreach (self::rows('booking_harian') as $b) self::$data['app_admin_booking'][$b['tanggal']] = ['maksimal' => (int) $b['maksimal'], 'dibuka' => (bool) $b['dibuka'], 'versi' => (int) $b['versi']];
        foreach (self::rows('faq') as $f) {
            $f['id'] = (int) $f['id'];
            $f['versi'] = (int) $f['versi'];
            $f['terbuka'] = (bool) $f['terbuka'];
            $f['dihapus'] = (bool) $f['dihapus'];
            self::$data['app_faq']['items'][$f['id']] = $f;
            self::$data['app_faq']['berikutnya'] = max(self::$data['app_faq']['berikutnya'], $f['id'] + 1);
        }
        $shop = self::rows('profil_toko')[0] ?? [];
        $shop = array_intersect_key($shop, array_flip(['nama', 'slogan', 'deskripsi', 'alamat', 'whatsapp', 'instagram',
            'hari_operasional', 'jam_operasional', 'logo', 'versi']));
        if ($shop) $shop['versi'] = (int) $shop['versi'];
        self::$data['app_profil_toko'] = $shop;
        foreach (self::rows('bank_toko') as $bank) self::$data['app_bank_toko'][$bank['kode']] = $bank;
    }

    public static function bindCustomer(?string $email): void
    {
        // Lepaskan dahulu referensi akun sebelumnya ketika login/logout.
        unset(self::$data['app_keranjang'], self::$data['app_pesanan']);
        foreach (['app_keranjang', 'app_pesanan'] as $key) {
            if ($email !== null && isset(self::$data['app_akun'][$email])) {
                self::$data['app_akun'][$email]['data'][$key] ??= [];
                self::$data[$key] = &self::$data['app_akun'][$email]['data'][$key];
            } else self::$data[$key] = [];
        }
    }

    public static function newUserId(string $email): int
    {
        if (!isset(self::$userIds[$email])) self::$userIds[$email] = self::$nextUser++;
        return self::$userIds[$email];
    }

    public static function allOrders(): array
    {
        $result = [];
        foreach (self::$data['app_akun'] as $email => $a) foreach ($a['data']['app_pesanan'] ?? [] as $no => $p) {
            $p['email'] = $email;
            $result[$no] = $p;
        }
        return $result;
    }

    private static function synchronize(): void
    {
        $old = [];
        foreach (self::$before['app_akun'] ?? [] as $a) $old += $a['data']['app_pesanan'] ?? [];
        foreach (self::$data['app_akun'] as &$a) foreach ($a['data']['app_pesanan'] ?? [] as $no => $unused) {
            $p = &$a['data']['app_pesanan'][$no];
            $change = self::$data['app_admin_pesanan'][$no] ?? [];
            foreach (['status' => 'status', 'ongkir' => 'ongkir_final', 'total' => 'total_pembayaran', 'batas_pembayaran' => 'batas_pembayaran',
                'versi' => 'versi', 'catatan_ongkir' => 'catatan_ongkir', 'tanggal_selesai' => 'tanggal_selesai', 'dibatalkan_pada' => 'dibatalkan_pada', 'riwayat' => 'riwayat_admin'] as $from => $to) {
                if (array_key_exists($from, $change)) $p[$to] = $change[$from];
            }
            if (($change['layanan'] ?? null) === Pesanan::NAMA_ANTAR) $p['pengiriman'] = Pesanan::KODE_ANTAR;
            $oldStatus = $old[$no]['status'] ?? null;
            if ($change && $oldStatus !== $p['status']) {
                $pemeriksaan = self::$data['app_admin_verifikasi'][$no] ?? null;
                $keterangan = $oldStatus === 'Menunggu Verifikasi' && $p['status'] === 'Menunggu Pembayaran'
                    && ($pemeriksaan['aksi'] ?? '') === 'tolak'
                    ? 'Admin menolak bukti pembayaran. Alasan: ' . $pemeriksaan['catatan']
                    : 'Status pesanan diperbarui oleh admin.';
                if ($p['status'] === 'Dibatalkan') {
                    $keterangan = Pesanan::pesanPembatalan($p);
                }
                $p['riwayat_status'][] = ['status' => $p['status'], 'waktu' => date('Y-m-d H:i:s'), 'keterangan' => $keterangan];
            }
        }
        unset($a,$p);
        // Reservasi stok baru dan pengembalian stok hanya pada transisi batal.
        foreach (self::allOrders() as $no => $p) {
            if ($p['jenis'] !== 'pesan-sekarang') continue;
            $was = isset($old[$no]) && $old[$no]['status'] !== 'Dibatalkan';
            $now = $p['status'] !== 'Dibatalkan';
            if ($was === $now) continue;
            foreach ($p['items'] as $item) {
                $product = &self::$data['app_admin_produk']['items'][$item['id']];
                $product['stok'] += ($now ? -1 : 1) * $item['jumlah'];
                $product['versi']++;
                if ($product['stok'] < 0) throw new RuntimeException('Stok produk tidak mencukupi. Periksa kembali pesanan.', 409);
                unset($product);
            }
        }
    }

    /** Ubah objek domain menjadi baris tabel eksplisit, tanpa menyimpan seluruh sesi/objek sebagai JSON. */
    private static function records(): array
    {
        $out = array_fill_keys(['pengguna', 'kategori', 'produk', 'keranjang', 'booking_harian', 'pesanan', 'detail_pesanan', 'pembayaran', 'riwayat_pesanan', 'aktivitas', 'faq', 'profil_toko', 'bank_toko'], []);
        $add = static function (string $table, array $row) use (&$out): void {
            $keys = self::keys()[$table];
            $id = implode(':', array_map(static fn ($k) => (string) $row[$k], $keys));
            foreach ($row as &$v) {
                if (is_bool($v)) $v = (int) $v;
            } unset($v);
            $out[$table][$id] = $row;
        };
        foreach (self::$data['app_akun'] as $email => $a) {
            $profile = $a['profil'];
            $meta = self::$data['app_kelola_pelanggan']['items'][$email] ?? [];
            $id = $meta['id'] ?? self::newUserId($email);
            self::$userIds[$email] = $id;
            $add('pengguna', ['id' => $id, 'nama' => $profile['nama'], 'email' => $email, 'password_hash' => $a['hash'], 'peran' => 'pelanggan',
                'telepon' => $profile['telepon'] ?? '', 'email_pelanggan_dihapus' => $meta['email_pelanggan_dihapus'] ?? null,
                'aktif' => $meta['aktif'] ?? true, 'versi' => $meta['versi'] ?? 1, 'auth_versi' => $a['auth_versi'] ?? 1]);
            foreach ($a['data']['app_keranjang'] ?? [] as $pid => $qty) $add('keranjang', ['pengguna_id' => $id, 'produk_id' => $pid, 'jumlah' => $qty]);
            foreach ($a['data']['app_pesanan'] ?? [] as $no => $p) {
                $row = array_intersect_key($p, array_flip(['nomor', 'jenis', 'dibuat_pada', 'tanggal_pesanan', 'nama_penerima', 'telepon', 'alamat', 'penerimaan', 'pengiriman', 'pembayaran', 'catatan', 'status', 'subtotal', 'estimasi_ongkir', 'ongkir_final', 'total_pembayaran', 'batas_pembayaran', 'tanggal_selesai', 'dibatalkan_pada', 'versi', 'catatan_ongkir']));
                $row += ['pengguna_id' => $id, 'tanggal_selesai' => null, 'dibatalkan_pada' => null, 'versi' => 0, 'catatan_ongkir' => ''];
                $row['tujuan_bank'] = isset($p['tujuan_bank']) ? json_encode($p['tujuan_bank'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : null;
                $add('pesanan', $row);
                foreach ($p['items'] as $item) $add('detail_pesanan', ['nomor' => $no, 'produk_id' => $item['id'], 'nama' => $item['nama'], 'gambar' => $item['gambar'] ?? '', 'kategori' => $item['kategori'] ?? self::$data['app_admin_produk']['items'][$item['id']]['kategori'],
                    'harga' => $item['harga'], 'jumlah' => $item['jumlah'], 'subtotal' => $item['subtotal']]);
                if (isset($p['bukti_pembayaran'])) {
                    $b = $p['bukti_pembayaran'];
                    $v = self::$data['app_admin_verifikasi'][$no] ?? [];
                    $add('pembayaran', ['nomor' => $no, 'nama_berkas' => $b['nama'], 'ukuran' => $b['ukuran'], 'mime' => $b['mime'], 'isi' => $b['isi'],
                        'waktu' => $p['waktu_upload_bukti'], 'keputusan' => $v['aksi'] ?? null, 'catatan' => $v['catatan'] ?? null, 'diperiksa_pada' => $v['waktu'] ?? null]);
                }
                foreach ($p['riwayat_status'] ?? [] as $i => $r) $add('riwayat_pesanan', ['nomor' => $no, 'urutan' => $i, 'status' => $r['status'], 'waktu' => $r['waktu'], 'keterangan' => $r['keterangan']]);
                foreach ($p['riwayat_admin'] ?? [] as $r) $add('aktivitas', ['id' => $r['log_id'], 'nomor' => $no, 'waktu' => $r['waktu'], 'admin' => $r['admin'],
                    'admin_key' => $r['admin_key'], 'peran' => $r['peran'], 'aktivitas' => $r['judul'], 'target' => 'Pesanan '.$no, 'keterangan' => $r['catatan'],
                    'status' => $r['status'], 'perubahan' => $r['perubahan'] ?? '[]']);
            }
        }
        foreach (self::$data['app_kelola_admin']['items'] as $a) {
            self::$userIds[$a['email']] = $a['id'];
            $add('pengguna', ['id' => $a['id'], 'nama' => $a['nama'], 'email' => $a['email'], 'password_hash' => $a['hash'], 'peran' => 'admin',
                'telepon' => '', 'aktif' => $a['aktif'], 'versi' => $a['versi'], 'auth_versi' => $a['auth_versi'],
                'email_admin_dihapus' => $a['email_admin_dihapus']]);
        }
        if (isset(self::$data['superadmin'])) {
            $r = self::$data['superadmin'];
            unset($r['dibuat_pada'], $r['alamat'], $r['foto']);
            $add('pengguna', $r);
        }
        foreach (self::$data['app_admin_kategori']['items'] as $r) $add('kategori', $r);
        foreach (self::$data['app_admin_produk']['items'] as $r) {
            $r['kategori_id'] = $r['kategori'];
            unset($r['kategori']);
            $r['foto_mime'] = $r['foto']['mime'] ?? null;
            $r['foto'] = $r['foto']['data'] ?? null;
            $add('produk', $r);
        }
        foreach (self::$data['app_admin_booking'] as $date => $r) $add('booking_harian', ['tanggal' => $date] + $r);
        foreach (self::$data['app_faq']['items'] as $r) $add('faq', $r);
        if (self::$data['app_profil_toko']) $add('profil_toko', ['id' => 1] + self::$data['app_profil_toko']);
        foreach (self::$data['app_bank_toko'] as $bank) $add('bank_toko', $bank);
        return $out;
    }

    private static function keys(): array
    {
        return ['pengguna' => ['id'], 'kategori' => ['id'], 'produk' => ['id'], 'keranjang' => ['pengguna_id', 'produk_id'],
            'booking_harian' => ['tanggal'], 'pesanan' => ['nomor'], 'detail_pesanan' => ['nomor', 'produk_id'],
            'pembayaran' => ['nomor'], 'riwayat_pesanan' => ['nomor', 'urutan'], 'aktivitas' => ['id'], 'faq' => ['id'], 'profil_toko' => ['id'], 'bank_toko' => ['kode']];
    }

    public static function finish(bool $commit = true): void
    {
        if (!self::$started || self::$finished) return;
        self::$finished = true;
        $db = Database::connection();
        try {
            if (!$commit) {
                $db->rollBack();
                return;
            }
            self::synchronize();
            $records = self::records();
            // Hapus relasi anak yang dilepas (misalnya item keranjang), sebelum upsert induknya.
            foreach (array_reverse(array_keys($records)) as $table) foreach (self::$recordsBefore[$table] as $id => $row) {
                if (isset($records[$table][$id])) continue;
                $keys = self::keys()[$table];
                $where = implode(' AND ', array_map(static fn ($k) => '`'.$k.'` = ?', $keys));
                $db->prepare('DELETE FROM `'.$table.'` WHERE '.$where)->execute(array_map(static fn ($k) => $row[$k], $keys));
            }
            foreach ($records as $table => $items) foreach ($items as $id => $row) {
                if (isset(self::$recordsBefore[$table][$id]) && self::$recordsBefore[$table][$id] == $row) continue;
                $cols = array_keys($row);
                $sql = 'INSERT INTO `'.$table.'` (`'.implode('`,`', $cols).'`) VALUES ('.implode(',', array_fill(0, count($cols), '?')).') ON DUPLICATE KEY UPDATE '
                    .implode(',', array_map(static fn ($k) => '`'.$k.'`=VALUES(`'.$k.'`)', $cols));
                $db->prepare($sql)->execute(array_values($row));
            }
            require_once dirname(__DIR__) . '/notifikasi/Notifikasi.php';
            $oldOrders = [];
            foreach (self::$before['app_akun'] ?? [] as $email => $a) foreach ($a['data']['app_pesanan'] ?? [] as $no => $p) {
                $p['email'] = $email;
                $oldOrders[$no] = $p;
            }
            Notifikasi::catatPerubahan($oldOrders, self::allOrders());
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        } finally {
            $db->query("SELECT RELEASE_LOCK('ayu_cake_write')");
        }
    }
}
