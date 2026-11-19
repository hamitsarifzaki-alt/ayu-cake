<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function perbaruiFaqPengiriman(PDO $db, array $faq): int
{
    $jawabanLama = [
        [2, 'Ya. Saat membuat pesanan, pilih Ambil di Toko atau Kirim ke Alamat. Untuk pengiriman, lengkapi alamat penerima agar admin dapat memeriksa kesesuaian layanan dan ongkos kirim.'],
        [3, 'Antar Ayu Cake dikenakan biaya Rp5.000 untuk wilayah layanan yang ditentukan. Jalur bus dikenakan biaya Rp10.000 untuk alamat yang sesuai rute atau titik pengambilan yang disepakati. Untuk ekspedisi, admin menentukan ongkir setelah memeriksa alamat. Admin dapat menyesuaikan metode pengiriman jika pilihan awal tidak sesuai dengan alamat penerima. Periksa metode dan total akhir pada detail pesanan sebelum membayar.'],
        [6, 'Pesanan dapat dibatalkan selama masih berstatus Menunggu Konfirmasi Ongkir, Menunggu Biaya Ongkir, atau Menunggu Pembayaran dan belum mengunggah bukti pembayaran. Jika tidak menyetujui perubahan metode atau biaya pengiriman, Anda juga dapat membatalkan pesanan sebelum mengunggah bukti pembayaran. Pembatalan dilakukan melalui tombol Batalkan Pesanan pada detail pesanan.'],
        [3, 'Ongkir Antar Ayu Cake adalah Rp5.000 per pesanan. Admin memeriksa apakah alamat berada dalam area layanan. Setelah ongkir dikonfirmasi, pelanggan dapat melihat total akhir pada detail pesanan dan melakukan pembayaran.'],
    ];
    $ubah = $db->prepare('UPDATE faq SET jawaban = ?, versi = versi + 1 WHERE pertanyaan = ? AND BINARY jawaban = ?');
    $jumlah = 0;
    foreach ($jawabanLama as [$i, $lama]) {
        $ubah->execute([$faq[$i]['jawaban'], $faq[$i]['pertanyaan'], $lama]);
        $jumlah += $ubah->rowCount();
    }
    return $jumlah;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require_once dirname(__DIR__) . '/app/core/Database.php';
    $seed = json_decode(file_get_contents(__DIR__ . '/seed-data.json'), true, 512, JSON_THROW_ON_ERROR);
    $db = Database::connection();
    $db->beginTransaction();
    try {
        $jumlah = perbaruiFaqPengiriman($db, $seed['faq']);
        $db->commit();
        echo "FAQ pengiriman diperbarui: {$jumlah}.\n";
    } catch (Throwable $error) {
        $db->rollBack();
        throw $error;
    }
}
