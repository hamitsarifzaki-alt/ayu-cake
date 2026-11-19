<?php

require_once dirname(__DIR__) . '/pesanan/Pesanan.php';

/** Pembaruan pesanan dengan tanda baca per akun, termasuk antarperangkat. */
class Notifikasi
{
    private static function pesanPelanggan(array $pesanan): string
    {
        if ($pesanan['status'] === 'Selesai') return Pesanan::pesanPenyelesaian($pesanan);
        if ($pesanan['status'] === 'Dibatalkan') {
            $pesan = Pesanan::pesanPembatalan($pesanan);
            return mb_strlen($pesan) <= 300 ? $pesan : mb_substr($pesan, 0, 299) . '…';
        }
        $pemeriksaan = DataModel::$data['app_admin_verifikasi'][$pesanan['nomor']] ?? null;
        if ($pesanan['status'] === 'Menunggu Pembayaran' && ($pemeriksaan['aksi'] ?? '') === 'tolak') {
            // Ringkasan notifikasi dibatasi 300 karakter; alasan lengkap ada di detail.
            $alasan = trim($pemeriksaan['catatan']);
            if (mb_strlen($alasan) > 180) $alasan = mb_substr($alasan, 0, 179) . '…';
            return 'Admin menolak bukti pembayaran. Alasan: ' . $alasan
                . ' Silakan perbaiki dan unggah ulang bukti pembayaran.';
        }
        return $pesanan['status'] === 'Menunggu Pembayaran'
            ? 'Tagihan sudah tersedia. Silakan periksa dan lanjutkan pembayaran.'
            : 'Status pesanan diperbarui: '.$pesanan['status'].'.';
    }

    public static function catatPerubahan(array $lama, array $baru): void
    {
        $db = Database::connection();
        $insert = $db->prepare('INSERT IGNORE INTO notifikasi(kunci,nomor,peran,penerima_id,pesan,jenis) VALUES(?,?,?,?,?,?)');
        $olehAdmin = str_starts_with((string) ($_GET['page'] ?? ''), 'admin-');
        foreach ($baru as $no => $p) {
            $old = $lama[$no] ?? null;
            $versi = $p['versi'] ?? 0;
            $penerima = DataModel::newUserId($p['email']);
            if ($old === null) {
                $insert->execute([$no.':baru', $no, 'admin', null, 'Pesanan baru dari '.$p['nama_penerima'].'.', 'pesanan-baru']);
                continue;
            }
            $berubah = $old['status'] !== $p['status'] || $old['total_pembayaran'] !== $p['total_pembayaran']
                || $old['ongkir_final'] !== $p['ongkir_final'] || $old['pengiriman'] !== $p['pengiriman'];
            if (!$berubah) continue;
            $aksiPemilik = !$olehAdmin && ($_SESSION['app_pelanggan']['email'] ?? null) === $p['email'];
            if (!$aksiPemilik) {
                $pesan = self::pesanPelanggan($p);
                $insert->execute([$no.':'.$versi.':pelanggan', $no, 'pelanggan', $penerima, $pesan, 'status-pesanan']);
            }
            if ($aksiPemilik) {
                $jenis = $p['status'] === 'Menunggu Verifikasi' ? 'bukti-baru' : 'status-pelanggan';
                $dikirimUlang = !empty($old['pernah_unggah_bukti']);
                $pesan = $jenis === 'bukti-baru'
                    ? ($dikirimUlang ? 'Bukti pembayaran dikirim ulang dan menunggu pemeriksaan.' : 'Bukti pembayaran baru menunggu pemeriksaan.')
                    : ($p['status'] === 'Selesai'
                        ? 'Pelanggan mengonfirmasi pesanan sudah diterima. Pesanan berstatus Selesai.'
                        : 'Pelanggan memperbarui pesanan: '.$p['status'].'.');
                $insert->execute([$no.':'.$versi.':admin', $no, 'admin', null, $pesan, $jenis]);
            }
        }
    }

    private static function identitas(string $peran): ?int
    {
        if ($peran === 'admin') return AkunAdmin::aktif()['id'] ?? null;
        $email = $_SESSION['app_pelanggan']['email'] ?? null;
        return is_string($email) && Akun::cari($email) !== null ? DataModel::newUserId($email) : null;
    }

    public static function ringkasan(string $peran, bool $hanyaPembayaran = false): array
    {
        $id = self::identitas($peran);
        if ($id === null) return ['count' => 0, 'items' => []];
        $db = Database::connection();
        $filter = 'n.peran=? AND (n.penerima_id=? OR (n.peran=\'admin\' AND n.penerima_id IS NULL)) AND d.notifikasi_id IS NULL';
        if ($hanyaPembayaran) {
            $filter .= " AND n.jenis='bukti-baru' AND EXISTS (SELECT 1 FROM pesanan p WHERE p.nomor=n.nomor AND p.status='Menunggu Verifikasi')";
        }
        $sql = 'SELECT n.nomor,MAX(n.id) terakhir FROM notifikasi n LEFT JOIN notifikasi_dibaca d ON d.notifikasi_id=n.id AND d.pengguna_id=? WHERE '.$filter.' GROUP BY n.nomor';
        $q = $db->prepare('SELECT COUNT(*) FROM ('.$sql.') jumlah');
        $q->execute([$id, $peran, $id]);
        $count = (int) $q->fetchColumn();
        $q = $db->prepare('SELECT n.* FROM notifikasi n JOIN ('.$sql.' ORDER BY terakhir DESC LIMIT 5) terbaru ON terbaru.terakhir=n.id ORDER BY n.id DESC');
        $q->execute([$id, $peran, $id]);
        $items = [];
        $pesanan = DataModel::allOrders();
        foreach ($q->fetchAll() as $r) {
            $p = $pesanan[$r['nomor']] ?? null;
            // Perjelas juga notifikasi lama yang masih merujuk keputusan terbaru.
            if ($peran === 'pelanggan' && $p !== null
                && $r['kunci'] === $r['nomor'].':'.($p['versi'] ?? 0).':pelanggan') {
                $r['pesan'] = self::pesanPelanggan($p);
            }
            $route = $peran === 'admin' ? ($r['jenis'] === 'bukti-baru' ? 'admin-periksa-pembayaran' : 'admin-detail-pesanan') : 'detail-pesanan';
            $items[] = ['nomor' => $r['nomor'], 'pesan' => $r['pesan'], 'waktu' => $r['waktu'],
                'url' => BASE_URL.'/index.php?page='.$route.'&nomor='.rawurlencode($r['nomor'])];
        }
        return ['count' => $count, 'items' => $items];
    }

    public static function baca(string $peran, string $nomor): void
    {
        $id = self::identitas($peran);
        if ($id === null) return;
        $sql = 'INSERT IGNORE INTO notifikasi_dibaca(notifikasi_id,pengguna_id) SELECT id,? FROM notifikasi WHERE nomor=? AND peran=? AND (penerima_id=? OR (peran=\'admin\' AND penerima_id IS NULL))';
        Database::connection()->prepare($sql)->execute([$id, $nomor, $peran, $id]);
    }
}
