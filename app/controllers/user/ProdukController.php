<?php

require_once APP_PATH . '/models/katalog/Produk.php';

class ProdukController extends Controller
{
    public function detail()
    {
        $nilaiId = $_GET['id'] ?? '';

        $id = is_string($nilaiId)
            ? filter_var(
                $nilaiId,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            )
            : false;

        $modelProduk = new Produk();

        $produk = $id !== false
            ? $modelProduk->cari($id)
            : null;

        if ($produk === null) {
            http_response_code(404);

            $this->view('produk/detail', [
                'title' => 'Produk Tidak Ditemukan | Ayu Cake',
                'produk' => null,
            ]);

            return;
        }

        $this->view('produk/detail', [
            'title' => $produk['nama'] . ' | Ayu Cake',
            'produk' => $produk,
        ]);
    }
}
