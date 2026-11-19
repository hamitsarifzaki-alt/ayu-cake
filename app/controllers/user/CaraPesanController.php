<?php

require_once APP_PATH . '/models/faq/Faq.php';

class CaraPesanController extends Controller
{
    public function index()
    {
        $modelFaq = new Faq();

        $this->view('cara-pesan/index', [
            'title' => 'FAQ & Cara Pesan | Ayu Cake',
            'faq' => $modelFaq->semua(),
        ]);
    }
}
