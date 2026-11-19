<?php

require_once APP_PATH . '/models/katalog/Produk.php';
require_once APP_PATH . '/models/katalog/AdminKategori.php';

class KatalogController extends Controller
{
    public function index()
    {
        $modelProduk = new Produk();
        $kategoriProduk = (new AdminKategori())->peta();
        $deskripsiKategori = array_column((new AdminKategori())->semua(), 'deskripsi', 'id');

        $kategori = $_GET['kategori'] ?? 'semua';
        $kataKunci = $_GET['q'] ?? '';
        $jenis = $_GET['jenis'] ?? 'semua';
        if (!is_string($jenis) || !in_array($jenis, ['semua', 'booking', 'ready-stock'], true)) {
            $jenis = 'semua';
        }

        if (
            !is_string($kategori)
            || ($kategori !== 'semua' && !isset($kategoriProduk[$kategori]))
        ) {
            $kategori = 'semua';
        }

        $kataKunci = is_string($kataKunci)
            ? trim($kataKunci)
            : '';

        $daftar = $modelProduk->semua();
        $jumlahHasil = 0;
        foreach ($daftar as &$produk) {
            $jenisProduk = $produk['jenis'] === 'Pre-Order' ? 'booking' : 'ready-stock';
            $produk['tampil'] = ($kategori === 'semua' || $produk['kategori'] === $kategori)
                && ($jenis === 'semua' || $jenisProduk === $jenis)
                && ($kataKunci === '' || stripos($produk['nama'], $kataKunci) !== false);
            if ($produk['tampil']) $jumlahHasil++;
        }
        unset($produk);

        $this->view('katalog/index', [
            'title' => 'Katalog | Ayu Cake',
            'produk' => $daftar,
            'kategori' => $kategori,
            'kategoriProduk' => $kategoriProduk,
            'deskripsiKategori' => $deskripsiKategori,
            'kataKunci' => $kataKunci,
            'jenis' => $jenis,
            'jumlahHasil' => $jumlahHasil,
        ]);
    }
}
