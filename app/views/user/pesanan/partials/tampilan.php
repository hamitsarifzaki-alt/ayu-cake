<?php

$aman = static function ($nilai): string {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};

$rupiah = static function ($nilai): string {
    return 'Rp' . number_format((float) $nilai, 0, ',', '.');
};

$tanggal = static function (
    string $nilai,
    bool $denganJam = false
): string {
    $waktu = new DateTimeImmutable(
        $nilai,
        new DateTimeZone('Asia/Jakarta')
    );

    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember',
    ];

    $teks = $waktu->format('d')
        . ' ' . $bulan[(int) $waktu->format('n')]
        . ' ' . $waktu->format('Y');

    return $denganJam
        ? $teks . ', pukul ' . $waktu->format('H.i') . ' WIB'
        : $teks;
};

$jenisPesanan = static function (array $pesanan): string {
    return $pesanan['jenis'] === 'booking'
        ? 'Booking / Pre-Order'
        : 'Pesan Sekarang';
};

$kelasStatus = static function (string $status): string {
    if ($status === 'Menunggu Verifikasi') {
        return 'po-badge-blue';
    }

    if (in_array($status, ['Diproses', 'Selesai'], true)) {
        return 'po-badge-green';
    }

    if ($status === 'Dibatalkan') {
        return 'po-badge-red';
    }

    return '';
};

$petunjukStatus = static function (array $pesanan) use ($tanggal): string {
    switch ($pesanan['status']) {
        case 'Menunggu Biaya Ongkir':
        case 'Menunggu Konfirmasi Ongkir':
            if ($pesanan['tanggal_pesanan'] < date('Y-m-d')) return Pesanan::ALASAN_JADWAL_PENGIRIMAN;
            return 'Admin sedang memeriksa alamat dan menghitung ongkir. Pembayaran tersedia setelah ongkir dikonfirmasi.';

        case 'Menunggu Pembayaran':
            if (!empty($pesanan['penolakan_bukti'])) {
                return 'Admin menolak bukti pembayaran. Alasan: '
                    . $pesanan['penolakan_bukti']['catatan']
                    . ' Silakan perbaiki dan unggah ulang bukti pembayaran.';
            }
            $batas = !empty($pesanan['batas_pembayaran'])
                ? ' Batas pembayaran: '
                    . $tanggal($pesanan['batas_pembayaran'], true) . '.'
                : '';

            return 'Silakan lakukan pembayaran dan unggah bukti pembayaran.'
                . $batas;

        case 'Menunggu Verifikasi':
            return 'Bukti pembayaran sedang diperiksa admin. Pesanan tidak dapat dibatalkan pada tahap ini.';

        case 'Diproses':
            return 'Pesanan sedang diproses oleh Ayu Cake. Pantau perkembangannya melalui halaman ini.';

        case 'Selesai':
            return Pesanan::pesanPenyelesaian($pesanan);

        case 'Dibatalkan':
            return Pesanan::pesanPembatalan($pesanan);

        default:
            return 'Lihat informasi terbaru pada detail pesanan.';
    }
};
