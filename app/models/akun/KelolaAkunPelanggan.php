<?php

require_once __DIR__ . '/ProfilBelanjaAdmin.php';

class KelolaAkunPelanggan
{
    private const DATA = 'app_kelola_pelanggan';
    public const PER_HALAMAN = 10;

    public function daftar(array $input): array
    {
        $semua = $this->semua();
        $cari = is_string($input['cari'] ?? null) ? mb_substr(trim($input['cari']), 0, 120) : '';
        $hasil = array_values(array_filter($semua, static function(array $akun) use ($cari): bool {
            return $cari === '' || mb_stripos(implode(' ', [$akun['nama'], $akun['email'], $akun['telepon'], sprintf('PLG-%03d', $akun['id'])]), $cari) !== false;
        }));
        $jumlahHalaman = max(1, (int) ceil(count($hasil) / self::PER_HALAMAN));
        $diminta = !isset($input['halaman']) ? 1 : (is_string($input['halaman']) ? filter_var($input['halaman'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false);
        $halaman = min($jumlahHalaman, $diminta === false ? 1 : $diminta);
        return ['items' => array_slice($hasil, ($halaman - 1) * self::PER_HALAMAN, self::PER_HALAMAN),
            'jumlah' => count($hasil), 'total' => count($semua), 'halaman' => $halaman,
            'jumlahHalaman' => $jumlahHalaman, 'halamanValid' => $diminta !== false,
            'cari' => $cari, 'perHalaman' => self::PER_HALAMAN];
    }

    private function siapkan(): void
    {
        DataModel::$data[self::DATA] ??= ['berikutnya' => 1, 'items' => []];
        foreach (DataModel::$data['app_akun'] as $email => $akun) {
            $this->tambahkan($email, [], true);
        }
    }

    private function tambahkan(string $email, array $profil, bool $aktif): void
    {
        if (isset(DataModel::$data[self::DATA]['items'][$email])) return;
        DataModel::$data[self::DATA]['items'][$email] = ['id' => DataModel::$data[self::DATA]['berikutnya']++,
            'email' => $email, 'profil' => $profil, 'aktif' => $aktif, 'versi' => 1];
    }

    public function semua(): array
    {
        $this->siapkan();
        $hasil = [];
        foreach (DataModel::$data[self::DATA]['items'] as $email => $a) {
            if (($a['email_pelanggan_dihapus'] ?? null) !== null) continue;
            $profil = DataModel::$data['app_akun'][$email]['profil'] ?? $a['profil'];
            if (($_SESSION['app_pelanggan']['email'] ?? null) === $email) $profil = $_SESSION['app_pelanggan'];
            $hasil[] = ['id' => $a['id'], 'nama' => $profil['nama'] ?? '', 'email' => $email,
                'telepon' => $profil['telepon'] ?? '',
                'aktif' => $a['aktif'], 'versi' => $a['versi'], 'contoh' => !isset(DataModel::$data['app_akun'][$email])];
        }
        return $hasil;
    }

    public function cari(int $id): ?array
    {
        foreach ($this->semua() as $a) if ($a['id'] === $id) return $a;
        return null;
    }

    public function cariEmail(string $email): ?array
    {
        $email = strtolower(trim($email));
        foreach ($this->semua() as $a) if ($a['email'] === $email) return $a;
        return null;
    }

    public function bolehMasuk(string $email): bool
    {
        return $this->cariEmail($email)['aktif'] ?? false;
    }

    public function dapatUbahSandi(array $akun): bool
    {
        return !$akun['contoh'] && !ProfilBelanjaAdmin::emailTerhubung($akun['email']);
    }

    public function ubahSandi(int $id, array $input): array
    {
        $akun = $this->cari($id);
        if ($akun === null || !$this->dapatUbahSandi($akun)) {
            return ['error' => 'Akun pelanggan tidak ditemukan.', 'kode' => 404];
        }
        if (($input['versi'] ?? null) !== (string) $akun['versi']) {
            return ['error' => 'Data akun sudah berubah. Muat ulang detail sebelum melanjutkan.', 'kode' => 409];
        }
        $sandi = $input['password'] ?? null;
        if (!is_string($sandi) || mb_strlen($sandi, 'UTF-8') < 8 || strlen($sandi) > 72 || trim($sandi) === '' || str_contains($sandi, "\0")) {
            return ['error' => 'Isi kata sandi baru dengan minimal 8 karakter dan maksimal 72 byte.', 'kode' => 400];
        }
        if ($sandi !== ($input['konfirmasi_password'] ?? null)) {
            return ['error' => 'Konfirmasi kata sandi harus sama dengan kata sandi baru.', 'kode' => 400];
        }
        if (password_verify($sandi, Akun::cari($akun['email'])['hash'])) {
            return ['error' => 'Gunakan kata sandi yang berbeda dari kata sandi sebelumnya.', 'kode' => 400];
        }

        Akun::gantiSandi($akun['email'], password_hash($sandi, PASSWORD_DEFAULT));
        DataModel::$data[self::DATA]['items'][$akun['email']]['versi']++;
        return ['error' => null];
    }

    public function pesananAktif(string $email): array
    {
        $pesanan = DataModel::$data['app_akun'][$email]['data']['app_pesanan'] ?? [];
        return array_filter($pesanan, static fn (array $p): bool => !in_array($p['status'], ['Selesai', 'Dibatalkan'], true));
    }

    public function hapus(int $id, ?string $versi): array
    {
        $a = $this->cari($id);
        if ($a === null || $a['contoh']) return ['error' => 'Akun pelanggan tidak ditemukan.', 'kode' => 404];
        if ($versi !== (string) $a['versi']) return ['error' => 'Data akun berubah. Muat ulang konfirmasi sebelum menghapus.', 'kode' => 409];

        $email = $a['email'];
        if ($this->pesananAktif($email) !== []) {
            return ['error' => 'Akun masih memiliki pesanan aktif.', 'kode' => 409];
        }
        if (($_SESSION['app_pelanggan']['email'] ?? null) === $email) Akun::keluar();
        $arsip = 'hapus:' . $id;
        $akun = DataModel::$data['app_akun'][$email];
        $meta = DataModel::$data[self::DATA]['items'][$email];
        unset(DataModel::$data['app_akun'][$email], DataModel::$data[self::DATA]['items'][$email]);
        $akun['profil']['email'] = $arsip;
        $akun['hash'] = '';
        $akun['auth_versi']++;
        DataModel::$data['app_akun'][$arsip] = $akun;
        DataModel::$data[self::DATA]['items'][$arsip] = array_replace($meta, [
            'email' => $arsip,
            'profil' => $akun['profil'],
            'aktif' => false,
            'versi' => $meta['versi'] + 1,
            'email_pelanggan_dihapus' => $email,
        ]);
        return ['error' => null];
    }
}
