<?php

require_once APP_PATH . '/models/katalog/Produk.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $data = [
            'title' => 'Beranda | Ayu Cake',
            'produk' => (new Produk())->semua(),
        ];

        $this->view('home/index', $data);
    }
}
