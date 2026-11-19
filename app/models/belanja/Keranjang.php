<?php

require_once APP_PATH . '/models/katalog/Produk.php';

class Keranjang
{
    // Batas kolom jumlah di database, bukan batas stok atau kapasitas booking.
    public const JUMLAH_MAKSIMAL = 4294967295;

    public function data(): array
    {
        return DataModel::$data['app_keranjang'];
    }
    public function simpan(array $items): void
    {
        DataModel::$data['app_keranjang'] = $items;
    }
    public function kosongkan(): void
    {
        $this->simpan([]);
    }
    public function tambah(int $id, int $jumlah): bool
    {
        $modelProduk = new Produk();

        if ($modelProduk->cari($id) === null || $jumlah < 1 || $jumlah > self::JUMLAH_MAKSIMAL) {
            return false;
        }

        $items = DataModel::$data['app_keranjang'] ?? [];

        if (!is_array($items)) {
            $items = [];
        }

        $jumlahLama = max(0, (int) ($items[$id] ?? 0));

        // Jumlah gabungan harus tetap dapat disimpan tanpa pemotongan nilai.
        if ($jumlah > self::JUMLAH_MAKSIMAL - $jumlahLama) {
            return false;
        }

        $items[$id] = $jumlahLama + $jumlah;

        DataModel::$data['app_keranjang'] = $items;

        return true;
    }
    public function ringkasan(): array
    {
        $tersimpan = DataModel::$data['app_keranjang'] ?? [];

        if (!is_array($tersimpan)) {
            $tersimpan = [];
        }

        $modelProduk = new Produk();
        $items = [];
        $jumlahLoyang = 0;
        $subtotal = 0;

        foreach ($tersimpan as $id => $jumlah) {
            $id = (int) $id;
            $jumlah = (int) $jumlah;

            if ($id < 1 || $jumlah < 1) {
                continue;
            }

            $produk = $modelProduk->cari($id);

            if ($produk === null) {
                continue;
            }

            $totalItem = (float) $produk['harga'] * $jumlah;

            $items[] = [
                'id' => $id,
                'nama' => $produk['nama'],
                'kategori' => $produk['kategori'] ?? '',
                'gambar' => $produk['gambar'] ?? '',
                'harga' => (float) $produk['harga'],
                'stok' => (int) ($produk['stok'] ?? 0),
                'jumlah' => $jumlah,
                'subtotal' => $totalItem,
            ];

            $jumlahLoyang += $jumlah;
            $subtotal += $totalItem;
        }

        return [
            'items' => $items,
            'jumlahProduk' => count($items),
            'jumlahLoyang' => $jumlahLoyang,
            'subtotal' => $subtotal,
        ];
    }

    public function ubahJumlah(int $id, int $jumlah): bool
    {
        $items = DataModel::$data['app_keranjang'] ?? [];

        if (!is_array($items) || !isset($items[$id]) || $jumlah < 1 || $jumlah > self::JUMLAH_MAKSIMAL) {
            return false;
        }

        $items[$id] = $jumlah;
        DataModel::$data['app_keranjang'] = $items;

        return true;
    }

    public function hapus(int $id): void
    {
        $items = DataModel::$data['app_keranjang'] ?? [];

        if (!is_array($items)) {
            $items = [];
        }

        unset($items[$id]);

        DataModel::$data['app_keranjang'] = $items;
    }
}
