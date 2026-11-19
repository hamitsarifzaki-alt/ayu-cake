<?php

require_once dirname(__DIR__) . '/dashboard/DashboardAdmin.php';
require_once dirname(__DIR__) . '/laporan/AdminLaporan.php';
require_once dirname(__DIR__) . '/toko/ProfilToko.php';
require_once dirname(__DIR__) . '/faq/Faq.php';
require_once dirname(__DIR__) . '/akun/KelolaAkunAdmin.php';
require_once dirname(__DIR__) . '/akun/KelolaAkunPelanggan.php';

class DashboardSuperAdmin
{
    public function ringkasan(): array
    {
        $hari = new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta'));
        $toko = (new DashboardAdmin())->ringkasan();
        $laporan = (new AdminLaporan($hari))->buat($toko['pesanan'], []);
        $pelanggan = (new KelolaAkunPelanggan())->semua();
        $admin = (new KelolaAkunAdmin())->semua();
        return ['hari' => $hari->format('Y-m-d'), 'laporan' => $laporan, 'pelanggan' => $pelanggan,
            'admin' => $admin, 'adminAktif' => count(array_filter($admin, static fn ($a) => $a['aktif'])),
            'profil' => (new ProfilToko())->detail(), 'faq' => (new Faq())->semua()];
    }
}
