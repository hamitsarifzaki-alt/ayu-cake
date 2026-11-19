<?php


class KelolaAkunAdmin
{
    private const DATA = 'app_kelola_admin';

    public function semua(): array
    {
        $terdaftar = array_filter(DataModel::$data[self::DATA]['items'], static fn ($a) => $a['email_admin_dihapus'] === null);
        return array_values(array_map(static function ($a) {
            unset($a['hash']);
            return $a;
        }, $terdaftar));
    }

    public function cari(int $id): ?array
    {
        $akun = DataModel::$data[self::DATA]['items'][$id] ?? null;
        return ($akun['email_admin_dihapus'] ?? null) === null ? $akun : null;
    }

    public function cariEmail(string $email): ?array
    {
        $email = strtolower(trim($email));
        foreach (DataModel::$data[self::DATA]['items'] as $a) if ($a['email_admin_dihapus'] === null && $a['email'] === $email) return $a;
        return null;
    }

    public function simpan(?int $id, array $input): array
    {
        $gagal = static fn (string $error, int $kode = 400): array => ['error' => $error, 'kode' => $kode];
        $lama = $id === null ? null : $this->cari($id);
        if ($id !== null && $lama === null) return $gagal('Akun admin tidak ditemukan.', 404);
        if ($lama !== null && ($input['versi'] ?? null) !== (string) $lama['versi']) return $gagal('Akun sudah berubah. Muat ulang form sebelum menyimpan.', 409);
        $nama = is_string($input['nama'] ?? null) ? trim($input['nama']) : '';
        $nama = preg_replace('/\s+/u', ' ', $nama) ?? '';
        $email = is_string($input['email'] ?? null) ? strtolower(trim($input['email'])) : '';
        $sandi = is_string($input['password'] ?? null) ? $input['password'] : null;
        $konfirmasi = is_string($input['konfirmasi'] ?? null) ? $input['konfirmasi'] : null;
        if ($nama === '' || mb_strlen($nama) > 120) return $gagal('Nama admin wajib diisi, maksimal 120 karakter.');
        if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) return $gagal('Masukkan email admin yang valid.');
        $duplikat = $this->cariEmail($email);
        if (($duplikat !== null && $duplikat['id'] !== $id) || isset(DataModel::$data['app_akun'][$email])
            || (class_exists('KelolaAkunPelanggan') && (new KelolaAkunPelanggan())->cariEmail($email) !== null)
            || preg_match('/^pelanggan(?:[1-9]|1\d|2[0-3])@example\.com$/D', $email)) return $gagal('Email sudah digunakan. Gunakan email lain.');
        if ($sandi === null || $konfirmasi === null) return $gagal('Isian kata sandi tidak valid.');
        if ($lama === null || $sandi !== '' || $konfirmasi !== '') {
            if (strlen($sandi) < 8 || strlen($sandi) > 72 || trim($sandi) === '') return $gagal('Kata sandi harus berisi 8–72 karakter.');
            if ($sandi !== $konfirmasi) return $gagal('Konfirmasi kata sandi harus sama dengan kata sandi.');
        }
        $ubahAkses = $lama !== null && ($lama['email'] !== $email || $sandi !== '');
        $id ??= DataModel::$data[self::DATA]['berikutnya']++;
        DataModel::$data[self::DATA]['items'][$id] = ['id' => $id, 'nama' => $nama, 'email' => $email,
            'aktif' => $lama['aktif'] ?? true, 'versi' => ($lama['versi'] ?? 0) + 1,
            'auth_versi' => ($lama['auth_versi'] ?? 1) + ($ubahAkses ? 1 : 0),
            'hash' => $sandi !== '' ? password_hash($sandi, PASSWORD_DEFAULT) : $lama['hash'], 'email_admin_dihapus' => null];
        $this->cabutSesi($id, $ubahAkses);
        return ['error' => null, 'id' => $id];
    }

    public function hapus(int $id, ?string $versi): array
    {
        $a = $this->cari($id);
        if ($a === null) return ['error' => 'Akun admin tidak ditemukan.', 'kode' => 404];
        if ($versi !== (string) $a['versi']) return ['error' => 'Data akun berubah. Muat ulang konfirmasi sebelum menghapus.', 'kode' => 409];
        $this->cabutSesi($id, true);
        // Pertahankan relasi lama pada pesanan, kosongkan kredensial, dan bebaskan email untuk akun baru.
        DataModel::$data[self::DATA]['items'][$id] = array_replace($a, [
            'email' => 'hapus:' . $id,
            'email_admin_dihapus' => $a['email'],
            'aktif' => false,
            'hash' => '',
            'versi' => $a['versi'] + 1,
            'auth_versi' => $a['auth_versi'] + 1,
        ]);
        return ['error' => null];
    }

    private function cabutSesi(int $id, bool $cabut): void
    {
        if (!$cabut) return;
        $sesi = $_SESSION['app_admin'] ?? null;
        if (is_array($sesi) && (($sesi['id'] ?? null) === $id || ($sesi['email'] ?? null) === $this->cari($id)['email'])) {
            unset($_SESSION['app_admin'], $_SESSION['app_admin_token']);
        }
    }
}
