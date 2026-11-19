<?php

class Faq
{
    public function semua(): array
    {
        if (!isset(DataModel::$data['app_faq'])) return $this->bawaan();
        return array_values(array_map(
            static fn ($f) => ['pertanyaan' => $f['pertanyaan'], 'jawaban' => $f['jawaban'], 'terbuka' => $f['terbuka']],
            array_filter(DataModel::$data['app_faq']['items'], static fn ($f) => !$f['dihapus'])
        ));
    }

    public function bawaan(): array
    {
        return [
            [
                'pertanyaan' => 'Apa perbedaan Pesan Sekarang dan Booking?',
                'jawaban' => 'Pesan Sekarang menggunakan stok yang tersedia untuk pemesanan hari ini. Booking digunakan untuk memilih tanggal pengambilan atau pengiriman minimal 5 hari setelah tanggal pemesanan (H+5). Contohnya, jika memesan tanggal 4, tanggal paling awal yang dapat dipilih adalah tanggal 9. Kapasitas Booking maksimal 50 loyang per hari, dihitung dari total loyang seluruh pesanan Booking pada tanggal yang dipilih.',
                'terbuka' => true,
            ],
            [
                'pertanyaan' => 'Bagaimana jika tanggal Booking atau kuotanya tidak tersedia?',
                'jawaban' => 'Pilih tanggal lain yang masih tersedia. Tanggal yang dinonaktifkan atau kapasitasnya sudah penuh tidak dapat digunakan untuk membuat pesanan Booking.',
                'terbuka' => false,
            ],
            [
                'pertanyaan' => 'Apakah pesanan bisa diambil di toko atau diantar?',
                'jawaban' => 'Ya. Pilih Ambil di Toko atau Kirim ke Alamat. Pengiriman menggunakan Antar Ayu Cake untuk area Bukit Terap menuju Toboali dan sekitar Kecamatan Tukak Sadai. Admin memeriksa alamat sebelum mengonfirmasi ongkir.',
                'terbuka' => false,
            ],
            [
                'pertanyaan' => 'Bagaimana biaya pengiriman ditentukan?',
                'jawaban' => 'Admin menghitung ongkir Antar Ayu Cake setelah memeriksa alamat penerima. Jika alamat jelas dan berada dalam cakupan layanan, admin mengonfirmasi ongkir dan total tagihan tersedia di Detail Pesanan. Jika alamat kurang jelas atau di luar jangkauan, admin menolak pesanan dengan alasan yang dapat dibaca pelanggan.',
                'terbuka' => true,
            ],
            [
                'pertanyaan' => 'Bagaimana cara membayar dan mengunggah bukti pembayaran?',
                'jawaban' => 'Buka detail pesanan saat statusnya Menunggu Pembayaran, kemudian ikuti informasi metode pembayaran yang ditampilkan. Bayar sesuai total akhir melalui transfer bank ke rekening BRI atau SeaBank yang tersedia, lalu unggah gambar bukti pembayaran. Setelah bukti berhasil dikirim, status berubah menjadi Menunggu Verifikasi. Admin akan memeriksa bukti sebelum pesanan diproses.',
                'terbuka' => false,
            ],
            [
                'pertanyaan' => 'Bagaimana cara melihat status pesanan?',
                'jawaban' => 'Masuk ke akun pelanggan, buka menu Akun Saya, lalu pilih Status Pesanan. Pilih Detail Pesanan untuk melihat informasi pesanan, ongkos kirim, pembayaran, dan perkembangan statusnya.',
                'terbuka' => false,
            ],
            [
                'pertanyaan' => 'Apakah pesanan dapat dibatalkan?',
                'jawaban' => 'Buka detail pesanan untuk membatalkan pesanan selama masih menunggu konfirmasi ongkir atau pembayaran dan bukti pembayaran belum diunggah.',
                'terbuka' => false,
            ],
        ];
    }
}
