<?php

require_once APP_PATH . '/models/toko/ProfilToko.php';

class KontakController extends Controller
{
    public function index()
    {
        $modelProfil = new ProfilToko();
        $profil = $modelProfil->detail();

        // Menghapus spasi, tanda +, dan tanda hubung.
        $nomorWhatsapp = preg_replace(
            '/\D/',
            '',
            (string) ($profil['whatsapp'] ?? '')
        );

        // Mengubah awalan 08 menjadi 628.
        if (substr($nomorWhatsapp, 0, 2) === '08') {
            $nomorWhatsapp = '62' . substr($nomorWhatsapp, 1);
        }

        // Mengubah awalan 8 menjadi 628.
        elseif (substr($nomorWhatsapp, 0, 1) === '8') {
            $nomorWhatsapp = '62' . $nomorWhatsapp;
        }

        $urlWhatsapp = null;

        // Memeriksa format nomor seluler Indonesia.
        if (preg_match('/^628[0-9]{8,11}$/', $nomorWhatsapp)) {
            $pesan = $profil['pesanWhatsapp']
                ?? 'Halo Ayu Cake, saya ingin bertanya mengenai pemesanan kue.';

            $urlWhatsapp =
                'https://wa.me/' .
                $nomorWhatsapp .
                '?text=' .
                rawurlencode($pesan);
        }

        // Nomor yang ditampilkan menggunakan format yang sudah dirapikan.
        $profil['whatsapp'] = $nomorWhatsapp;

        $this->view('kontak/index', [
            'title' => 'Tentang Kami & Kontak | Ayu Cake',
            'profil' => $profil,
            'urlWhatsapp' => $urlWhatsapp,
        ]);
    }
}
