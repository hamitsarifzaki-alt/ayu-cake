<?php

class BookingPelanggan
{
    public function kalender(DateTimeImmutable $minimal): array
    {
        $hasil = [];
        foreach (DataModel::$data['app_admin_booking'] as $tanggal => $aturan) {
            if ($tanggal >= $minimal->format('Y-m-d')) $hasil[$tanggal] = ['kapasitas' => $aturan['maksimal'], 'terisi' => 0, 'ditutup' => !$aturan['dibuka']];
        }
        foreach (DataModel::allOrders() as $p) {
            if ($p['jenis'] !== 'booking' || $p['status'] === 'Dibatalkan') continue;
            $tanggal = $p['tanggal_pesanan'];
            $hasil[$tanggal] ??= ['kapasitas' => 50, 'terisi' => 0, 'ditutup' => false];
            $hasil[$tanggal]['terisi'] += array_sum(array_column($p['items'], 'jumlah'));
        }
        return $hasil;
    }
}
