<?php

require_once dirname(__DIR__) . '/toko/ProfilToko.php';

class SuperAdminProfilToko
{
    public function detail(): array
    {
        $p = (new ProfilToko())->detail();
        $rekening = (new ProfilToko())->rekeningBank();
        $bank = [];
        foreach (ProfilToko::layananBank() as $kode => $nama) {
            $akun = $rekening[$kode] ?? [];
            $bank[$kode] = ['nomor' => $akun['nomor'] ?? '', 'atas_nama' => $akun['atas_nama'] ?? ''];
        }
        return ['nama' => $p['nama'], 'slogan' => $p['slogan'], 'deskripsi' => implode("\n\n", $p['deskripsi']),
            'alamat' => $p['alamat'], 'whatsapp' => $p['whatsapp'], 'instagram' => $p['instagram'],
            'hari_operasional' => $p['hariOperasional'], 'jam_operasional' => $p['jamOperasional'],
            'bank' => $bank, 'logo' => $p['logo'],
            'versi' => DataModel::$data['app_profil_toko']['versi'] ?? 0];
    }

    public static function bank(): array { return ProfilToko::layananBank(); }

    public function simpan(array $input, array $files): array
    {
        $lama = $this->detail();
        $gagal = static fn(string $pesan, int $kode = 400): array => ['error' => $pesan, 'kode' => $kode];
        if (($input['versi'] ?? null) !== (string) $lama['versi']) return $gagal('Profil toko sudah berubah. Muat ulang halaman sebelum menyimpan.', 409);
        $baru = [];
        $labels = ['nama' => 'Nama toko', 'slogan' => 'Slogan', 'deskripsi' => 'Deskripsi toko', 'alamat' => 'Alamat toko',
            'whatsapp' => 'Nomor WhatsApp', 'instagram' => 'Instagram', 'hari_operasional' => 'Hari operasional', 'jam_operasional' => 'Jam operasional'];
        foreach (['nama' => 120, 'slogan' => 160, 'deskripsi' => 2000, 'alamat' => 1000, 'whatsapp' => 25,
            'instagram' => 31, 'hari_operasional' => 120, 'jam_operasional' => 80] as $key => $max) {
            if (!is_string($input[$key] ?? null) || mb_strlen(trim($input[$key])) > $max) return $gagal($labels[$key] . ' tidak valid atau terlalu panjang.');
            $baru[$key] = trim($input[$key]);
            if ($baru[$key] === '') return $gagal($labels[$key] . ' wajib diisi.');
        }
        $nomorValid = static function(string $nomor): ?string {
            if (!preg_match('/^\+?[0-9\s()-]+$/D', $nomor)) return null;
            $angka = preg_replace('/\D/', '', $nomor);
            return preg_match('/^(?:08|628)\d{8,11}$/D', $angka) ? $angka : null;
        };
        $baru['whatsapp'] = $nomorValid($baru['whatsapp']);
        if ($baru['whatsapp'] === null) return $gagal('Nomor WhatsApp harus diawali 08 atau +628 dan memiliki panjang yang valid.');
        if (!preg_match('/^@?[a-zA-Z0-9._]{1,30}$/D', $baru['instagram'])) return $gagal('Isi nama akun Instagram tanpa tautan atau spasi.');
        $baru['instagram'] = ltrim($baru['instagram'], '@');
        if (!is_array($input['bank'] ?? null)) return $gagal('Isian rekening bank tidak valid.');
        $bank = [];
        foreach (self::bank() as $kode => $nama) {
            $akun = $input['bank'][$kode] ?? [];
            if (!is_array($akun) || !is_string($akun['nomor'] ?? null) || !is_string($akun['atas_nama'] ?? null)) return $gagal('Isian akun ' . $nama . ' tidak valid.');
            $nomor = trim($akun['nomor']); $pemilik = trim($akun['atas_nama']);
            if ($nomor === '' && $pemilik === '') continue;
            if ($nomor === '' || $pemilik === '') return $gagal('Lengkapi nomor dan nama pemilik akun ' . $nama . ', atau kosongkan keduanya.');
            if (mb_strlen($nomor) > 25 || mb_strlen($pemilik) > 120 || !preg_match('/^[0-9]{6,25}$/D', $nomor)) return $gagal('Nomor atau nama pemilik akun ' . $nama . ' tidak valid. Gunakan nomor rekening berisi 6–25 angka tanpa spasi.');
            $bank[$kode] = ['kode' => $kode, 'nomor' => $nomor, 'atas_nama' => $pemilik];
        }
        foreach (['logo'] as $key) {
            $hasil = $this->gambar($files[$key] ?? null);
            if ($hasil['error'] !== null) return $gagal(ucfirst($key) . ': ' . $hasil['error']);
            $baru[$key] = $hasil['gambar'] ?? $lama[$key];
        }
        $berubah = $baru !== array_intersect_key($lama, $baru);
        foreach ($lama['bank'] as $kode => $tersimpan) {
            $akun = $bank[$kode] ?? ['nomor' => '', 'atas_nama' => ''];
            if ($tersimpan !== ['nomor' => $akun['nomor'], 'atas_nama' => $akun['atas_nama']]) {
                $berubah = true;
                break;
            }
        }
        if (!$berubah) return ['error' => null, 'berubah' => false];
        $baru['versi'] = $lama['versi'] + 1;
        DataModel::$data['app_profil_toko'] = $baru;
        DataModel::$data['app_bank_toko'] = $bank;
        return ['error' => null, 'berubah' => true];
    }

    private function gambar($file): array
    {
        if ($file === null || (is_array($file) && ($file['error'] ?? null) === UPLOAD_ERR_NO_FILE)) return ['error' => null, 'gambar' => null];
        $gagal = ['error' => 'Pilih gambar PNG atau JPG maksimal 2 MB dan 4096 × 4096 piksel.'];
        if (!is_array($file) || ($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) return $gagal;
        $size = filesize($file['tmp_name']);
        $info = @getimagesize($file['tmp_name']);
        if (!$size || $size > 2 * 1024 * 1024 || !$info || !in_array($info['mime'] ?? '', ['image/png', 'image/jpeg'], true)
            || $info[0] > 4096 || $info[1] > 4096) return $gagal;
        $isi = file_get_contents($file['tmp_name']);
        return $isi === false ? $gagal : ['error' => null, 'gambar' => 'data:' . $info['mime'] . ';base64,' . base64_encode($isi)];
    }
}
