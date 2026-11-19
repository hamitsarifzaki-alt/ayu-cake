<?php

/** Satu perhitungan untuk tampilan dan kedua unduhan laporan admin. */
class AdminLaporan
{
    private DateTimeImmutable $hari;

    public function __construct(?DateTimeImmutable $hari = null)
    {
        $this->hari = $hari ?? new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta'));
    }

    public static function tanggalValid($nilai): bool
    {
        if (!is_string($nilai) || !preg_match('/^(20\d{2}|2100)-\d{2}-\d{2}$/D', $nilai)) return false;
        $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d', $nilai);
        return $tanggal !== false && $tanggal->format('Y-m-d') === $nilai;
    }

    public function buat(array $pesanan, array $input): array
    {
        $mulai = array_key_exists('mulai', $input) ? $input['mulai'] : $this->hari->format('Y-m-01');
        $akhir = array_key_exists('akhir', $input) ? $input['akhir'] : $this->hari->format('Y-m-d');
        $error = null;
        if (!self::tanggalValid($mulai) || !self::tanggalValid($akhir)) {
            $error = 'Isi tanggal mulai dan akhir yang valid (tahun 2000–2100).';
        } elseif ($mulai > $akhir) {
            $error = 'Tanggal akhir tidak boleh sebelum tanggal mulai.';
        }
        $items = [];
        $total = ['pesanan' => 0, 'loyang' => 0, 'produk' => 0, 'ongkir' => 0, 'total' => 0];
        if ($error === null) {
            foreach ($pesanan as $p) {
                $selesai = $p['tanggal_selesai'] ?? null;
                // Tanggal penerimaan bukan pengganti tanggal pesanan selesai.
                if ($p['status'] !== 'Selesai' || !self::tanggalValid($selesai) || $selesai < $mulai || $selesai > $akhir) continue;
                $p['loyang'] = (int) array_sum(array_column($p['items'], 'jumlah'));
                $p['subtotal'] = (int) $p['subtotal'];
                $p['ongkir'] = (int) $p['ongkir'];
                $p['total'] = $p['subtotal'] + $p['ongkir'];
                $items[] = $p;
                $total['pesanan']++;
                $total['loyang'] += $p['loyang'];
                $total['produk'] += $p['subtotal'];
                $total['ongkir'] += $p['ongkir'];
                $total['total'] += $p['total'];
            }
            usort($items, static fn ($a, $b) => [$a['tanggal_selesai'], $a['nomor']] <=> [$b['tanggal_selesai'], $b['nomor']]);
        }
        return ['mulai' => is_string($mulai) ? mb_substr($mulai, 0, 10) : '',
            'akhir' => is_string($akhir) ? mb_substr($akhir, 0, 10) : '',
            'error' => $error, 'items' => $items, 'total' => $total];
    }
}
