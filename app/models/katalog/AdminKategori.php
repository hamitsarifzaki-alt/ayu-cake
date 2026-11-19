<?php


/** Kategori milik sesi admin, terpisah dari kategori katalog pelanggan. */
class AdminKategori
{
    private function siapkan(): void
    {
    }

    public function semua(): array
    {
        $this->siapkan();
        return array_values(array_filter(DataModel::$data['app_admin_kategori']['items'], static fn ($k) => !$k['dihapus']));
    }

    public function peta(): array
    {
        return array_column($this->semua(), 'nama', 'id');
    }

    public function cari(string $id): ?array
    {
        $this->siapkan();
        $kategori = DataModel::$data['app_admin_kategori']['items'][$id] ?? null;
        return $kategori !== null && !$kategori['dihapus'] ? $kategori : null;
    }

    public function produkTerhubung(string $id): array
    {
        require_once dirname(__DIR__) . '/katalog/AdminProduk.php';
        return array_values(array_filter((new AdminProduk())->semua(), static fn ($p) => $p['kategori'] === $id));
    }

    public function daftar(): array
    {
        require_once dirname(__DIR__) . '/katalog/AdminProduk.php';
        $produk = (new AdminProduk())->semua();
        return array_map(static function ($kategori) use ($produk) {
            $kategori['jumlah'] = count(array_filter($produk, static fn ($p) => $p['kategori'] === $kategori['id']));
            return $kategori;
        }, $this->semua());
    }

    public function simpan(?string $id, array $input): array
    {
        $lama = $id === null ? null : $this->cari($id);
        if ($id !== null && $lama === null) return ['error' => 'Kategori tidak ditemukan.', 'kode' => 404];
        if ($lama !== null && (string) $lama['versi'] !== ($input['versi'] ?? null)) {
            return ['error' => 'Kategori sudah berubah di tab lain. Muat ulang form sebelum menyimpan.', 'kode' => 409];
        }
        $nama = is_string($input['nama'] ?? null) ? trim($input['nama']) : '';
        $nama = preg_replace('/\s+/u', ' ', $nama) ?? '';
        $deskripsi = is_string($input['deskripsi'] ?? null) ? trim($input['deskripsi']) : '';
        if ($nama === '' || mb_strlen($nama) > 80) return ['error' => 'Nama kategori wajib diisi, maksimal 80 karakter.', 'kode' => 400];
        if ($deskripsi === '' || mb_strlen($deskripsi) > 500) return ['error' => 'Deskripsi kategori wajib diisi, maksimal 500 karakter.', 'kode' => 400];
        foreach ($this->semua() as $kategori) {
            if ($kategori['id'] !== $id && mb_strtolower($kategori['nama']) === mb_strtolower($nama)) {
                return ['error' => 'Nama kategori sudah digunakan. Gunakan nama lain.', 'kode' => 400];
            }
        }
        if ($id === null) $id = 'kategori-' . DataModel::$data['app_admin_kategori']['berikutnya']++;
        DataModel::$data['app_admin_kategori']['items'][$id] = ['id' => $id, 'nama' => $nama,
            'deskripsi' => $deskripsi, 'versi' => ($lama['versi'] ?? 0) + 1, 'dihapus' => false];
        return ['error' => null, 'id' => $id];
    }

    public function hapus(string $id, string $versi): ?string
    {
        $kategori = $this->cari($id);
        if ($kategori === null) return 'Kategori tidak ditemukan atau sudah dihapus.';
        if ((string) $kategori['versi'] !== $versi) return 'Kategori sudah berubah. Periksa kembali sebelum menghapus.';
        if (count($this->produkTerhubung($id)) > 0) return 'Kategori masih digunakan produk. Pindahkan produk ke kategori lain sebelum menghapus.';
        DataModel::$data['app_admin_kategori']['items'][$id]['dihapus'] = true;
        DataModel::$data['app_admin_kategori']['items'][$id]['versi']++;
        return null;
    }

    public function pulihkan(string $id): bool
    {
        $this->siapkan();
        $kategori = DataModel::$data['app_admin_kategori']['items'][$id] ?? null;
        if ($kategori === null || !$kategori['dihapus']) return false;
        foreach ($this->semua() as $aktif) {
            if (mb_strtolower($aktif['nama']) === mb_strtolower($kategori['nama'])) return false;
        }
        DataModel::$data['app_admin_kategori']['items'][$id]['dihapus'] = false;
        DataModel::$data['app_admin_kategori']['items'][$id]['versi']++;
        return true;
    }
}
