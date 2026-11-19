<?php

require_once dirname(__DIR__) . '/katalog/Produk.php';
require_once dirname(__DIR__) . '/booking/BookingPelanggan.php';
require_once dirname(__DIR__) . '/toko/ProfilToko.php';
require_once __DIR__ . '/Pesanan.php';

class PembuatanPesanan
{
    public function siapkan(string $jenis, array $pilihan, array $isian): array
    {
        [$items, $jumlah, $subtotal] = $this->rincianProduk($jenis, $pilihan);
        $sekarang = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
        $tanggal = $this->tanggalPesanan($jenis, $isian['tanggal'], $jumlah, $sekarang);
        $rekening = $this->validasiPenerima($isian);

        $kirim = $isian['penerimaan'] === 'kirim';
        $pengiriman = $kirim ? Pesanan::KODE_ANTAR : null;
        $status = $kirim ? 'Menunggu Konfirmasi Ongkir' : 'Menunggu Pembayaran';

        return [
            'nomor' => 'AYU-' . $sekarang->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(6))),
            'jenis' => $jenis,
            'dibuat_pada' => $sekarang->format('Y-m-d H:i:s'),
            'tanggal_pesanan' => $tanggal,
            'nama_penerima' => $isian['nama_penerima'],
            'telepon' => $isian['telepon'],
            'alamat' => $kirim ? $isian['alamat'] : '',
            'penerimaan' => $isian['penerimaan'],
            'pengiriman' => $pengiriman,
            'pembayaran' => $isian['pembayaran'],
            'tujuan_bank' => $rekening,
            'catatan' => $isian['catatan'],
            'status' => $status,
            'versi' => 0,
            'items' => $items,
            'jumlah_produk' => count($items),
            'jumlah_loyang' => $jumlah,
            'subtotal' => $subtotal,
            'estimasi_ongkir' => $kirim ? null : 0,
            'ongkir_final' => $kirim ? null : 0,
            'total_pembayaran' => $kirim ? null : $subtotal,
            'batas_pembayaran' => $kirim ? null : $sekarang->modify('+1 day')->format('Y-m-d H:i:s'),
        ];
    }

    private function rincianProduk(string $jenis, array $pilihan): array
    {
        if ($pilihan === []) {
            throw new DomainException('Keranjang masih kosong. Tambahkan produk terlebih dahulu.');
        }
        $model = new Produk();
        $opsi = ['options' => ['min_range' => 1]];
        $items = [];
        $jumlah = 0;
        $subtotal = 0;
        foreach ($pilihan as $id => $qty) {
            $id = filter_var($id, FILTER_VALIDATE_INT, $opsi);
            $qty = is_int($qty) || is_string($qty) ? filter_var($qty, FILTER_VALIDATE_INT, $opsi) : false;
            $produk = $id !== false ? $model->cari($id) : null;
            if ($produk === null || $qty === false || $qty > PHP_INT_MAX - $jumlah) {
                throw new DomainException('Produk atau jumlah pesanan tidak valid. Periksa kembali pilihanmu.');
            }
            if ($jenis === 'pesan-sekarang' && $qty > (int) $produk['stok']) {
                throw new DomainException('Jumlah ' . $produk['nama'] . ' melebihi stok yang tersedia.');
            }
            $harga = (float) $produk['harga'];
            $items[] = [
                'id' => $id,
                'nama' => $produk['nama'],
                'gambar' => $produk['gambar'] ?? '',
                'harga' => $harga,
                'jumlah' => $qty,
                'subtotal' => $harga * $qty,
            ];
            $jumlah += $qty;
            $subtotal += $harga * $qty;
        }

        if ($jenis === 'booking' && $jumlah > 50) {
            throw new DomainException('Jumlah Booking melebihi kapasitas 50 loyang.');
        }
        return [$items, $jumlah, $subtotal];
    }

    private function tanggalPesanan(string $jenis, string $isian, int $jumlah, DateTimeImmutable $sekarang): string
    {
        if ($jenis !== 'booking') {
            return $sekarang->format('Y-m-d');
        }
        $minimal = $sekarang->setTime(0, 0)->modify('+5 days');
        $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d', $isian, $sekarang->getTimezone());
        if ($tanggal === false || $tanggal->format('Y-m-d') !== $isian || $tanggal < $minimal) {
            throw new DomainException('Tanggal Booking harus minimal lima hari setelah tanggal pemesanan.');
        }
        $kalender = (new BookingPelanggan())->kalender($minimal);
        $kapasitas = $kalender[$isian] ?? ['kapasitas' => 50, 'terisi' => 0, 'ditutup' => false];
        if ($kapasitas['ditutup'] || $jumlah > max(0, $kapasitas['kapasitas'] - $kapasitas['terisi'])) {
            throw new DomainException('Tanggal Booking ditutup atau sisa kuotanya tidak mencukupi.');
        }
        return $isian;
    }

    private function validasiPenerima(array $isian): array
    {
        $nama = $isian['nama_penerima'];
        $panjangNama = mb_strlen($nama, 'UTF-8');
        if ($panjangNama < 3 || $panjangNama > 100 || !preg_match('/\A\p{L}+(?: +\p{L}+)*\z/u', $nama)) {
            throw new DomainException('Nama penerima minimal 3 karakter dan hanya boleh berisi huruf serta spasi.');
        }
        if (!preg_match('/\A08[0-9]{8,13}\z/', $isian['telepon'])) {
            throw new DomainException('Nomor telepon harus berisi 10–15 angka dan diawali 08.');
        }
        if (!in_array($isian['penerimaan'], ['ambil', 'kirim'], true)) {
            throw new DomainException('Pilih cara menerima pesanan.');
        }
        $metode = $isian['pembayaran'];
        $bank = (new ProfilToko())->rekeningBank();
        $rekening = $bank[ProfilToko::kodeBank($metode) ?? ''] ?? null;
        if ($rekening === null) {
            throw new DomainException('Pilih metode pembayaran yang tersedia.');
        }

        $panjangAlamat = mb_strlen($isian['alamat'], 'UTF-8');
        if ($panjangAlamat > 500 || mb_strlen($isian['catatan'], 'UTF-8') > 500) {
            throw new DomainException('Alamat dan catatan masing-masing maksimal 500 karakter.');
        }
        if ($isian['penerimaan'] === 'kirim') {
            if ($panjangAlamat < 10) {
                throw new DomainException('Alamat pengiriman minimal 10 karakter.');
            }
            if (!in_array($isian['pengiriman'] ?? '', ['', Pesanan::KODE_ANTAR], true)) {
                throw new DomainException('Pengiriman hanya tersedia melalui Antar Ayu Cake.');
            }
        }
        return $rekening;
    }
}
