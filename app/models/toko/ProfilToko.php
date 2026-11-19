<?php

class ProfilToko
{
    public static function layananBank(): array
    {
        return ['bri' => 'BRI', 'seabank' => 'SeaBank'];
    }

    public static function kodeBank(string $metode): ?string
    {
        $kode = substr($metode, 5);
        return str_starts_with($metode, 'bank-') && isset(self::layananBank()[$kode]) ? $kode : null;
    }

    public static function namaPembayaran(string $kode): string
    {
        $bank = self::kodeBank($kode);
        return $bank === null ? 'Metode tidak tersedia' : 'Transfer Bank • ' . self::layananBank()[$bank];
    }

    public static function rekeningPesananTersedia(array $pesanan): bool
    {
        return self::kodeBank($pesanan['pembayaran']) !== null
            && !empty($pesanan['tujuan_bank']);
    }

    public function detail(): array
    {
        $profil = [
            'nama' => 'Ayu Cake',

            'slogan' => 'Kue untuk setiap cerita',

            'deskripsi' => [
                'Ayu Cake menyediakan kue basah dan bolu dari Desa Bukit Terap, Kecamatan Tukak Sadai, Bangka Selatan.',

                'Pilih kue dari stok yang tersedia atau pesan lebih awal melalui Booking untuk tanggal pilihanmu. Pesanan dapat diambil di toko atau dikirim sesuai layanan yang tersedia.',
            ],

            'alamat' => 'Desa Bukit Terap, Kecamatan Tukak Sadai, Kabupaten Bangka Selatan, Bangka Belitung.',

            // Isi nomor WhatsApp asli dengan format 62.
            // Gunakan angka saja, tanpa tanda +, spasi, atau tanda hubung.
            'whatsapp' => '',

            // Isi setelah mendapatkan jadwal operasional dari Ayu.
            'jamOperasional' => '08:00 - 22:00',

            'pesanWhatsapp' => 'Halo Ayu Cake, saya ingin bertanya mengenai produk dan pemesanan kue.',
            'instagram' => '', 'hariOperasional' => '', 'logo' => '',
        ];
        $simpan = DataModel::$data['app_profil_toko'] ?? [];
        foreach (['nama', 'slogan', 'alamat', 'whatsapp', 'instagram', 'logo'] as $key) if (isset($simpan[$key])) $profil[$key] = $simpan[$key];
        if (isset($simpan['deskripsi'])) $profil['deskripsi'] = preg_split('/\R\s*\R/u', $simpan['deskripsi']) ?: [$simpan['deskripsi']];
        $profil['hariOperasional'] = $simpan['hari_operasional'] ?? '';
        $profil['jamOperasional'] = $simpan['jam_operasional'] ?? $profil['jamOperasional'];
        return $profil;
    }

    public function rekeningBank(): array
    {
        $rekening = [];
        foreach (self::layananBank() as $kode => $nama) {
            $akun = DataModel::$data['app_bank_toko'][$kode] ?? null;
            if ($akun && $akun['nomor'] !== '' && $akun['atas_nama'] !== '') $rekening[$kode] = $akun + ['nama' => $nama];
        }
        return $rekening;
    }
}
