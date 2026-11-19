<?php

require_once dirname(__DIR__) . '/laporan/AdminLaporan.php';

/** Rincian produk memakai harga pada pesanan, bukan harga katalog terbaru. */
class SuperAdminLaporan
{
    private AdminLaporan $laporan;

    public function __construct(?DateTimeImmutable $hari = null)
    {
        $this->laporan = new AdminLaporan($hari);
    }

    public function buat(array $pesanan, array $input): array
    {
        $hasil = $this->laporan->buat($pesanan, $input);
        $produk = [];
        $kategori = ['bolu' => 'Bolu', 'kue-basah' => 'Kue Basah', 'brownies' => 'Brownies'];
        foreach ($hasil['items'] as $p) {
            foreach ($p['items'] as $item) {
                // Harga berbeda ditampilkan pada baris berbeda agar nilai historis tetap jelas.
                $key = json_encode([$item['id'] ?? null, $item['nama'], $item['kategori'], (int) $item['harga']]);
                $produk[$key] ??= ['nama' => $item['nama'], 'kategori' => $kategori[$item['kategori']] ?? ucwords(str_replace('-', ' ', $item['kategori'])),
                    'harga' => (int) $item['harga'], 'jumlah' => 0, 'subtotal' => 0];
                $produk[$key]['jumlah'] += (int) $item['jumlah'];
                $produk[$key]['subtotal'] += (int) ($item['subtotal'] ?? $item['harga'] * $item['jumlah']);
            }
        }
        $hasil['produk_terjual'] = array_values($produk);
        usort($hasil['produk_terjual'], static fn ($a, $b) => [$a['nama'], $a['harga'], $a['kategori']] <=> [$b['nama'], $b['harga'], $b['kategori']]);
        return $hasil;
    }
}
