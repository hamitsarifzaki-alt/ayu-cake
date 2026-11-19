<?php

require_once dirname(__DIR__) . '/katalog/Produk.php';
require_once dirname(__DIR__) . '/katalog/AdminKategori.php';

/** Salinan katalog khusus admin; tidak mengubah model atau sesi pelanggan. */
class AdminProduk
{
    public function kategori(): array
    {
        return (new AdminKategori())->peta();
    }

    private function siapkan(): void
    {
    }

    public function semua(): array
    {
        $this->siapkan();
        return array_values(array_filter(DataModel::$data['app_admin_produk']['items'], static fn ($p) => !$p['dihapus']));
    }

    public function cari(int $id): ?array
    {
        $this->siapkan();
        $produk = DataModel::$data['app_admin_produk']['items'][$id] ?? null;
        return $produk !== null && !$produk['dihapus'] ? $produk : null;
    }

    public static function tersedia(array $produk): bool
    {
        return $produk['aktif'] && ($produk['jenis'] === 'Pre-Order' || $produk['stok'] > 0);
    }

    public function daftar(array $input): array
    {
        $teks = static fn ($k) => is_string($input[$k] ?? null) ? trim($input[$k]) : '';
        $cari = mb_substr($teks('cari'), 0, 120);
        $kategori = isset($this->kategori()[$teks('kategori')]) ? $teks('kategori') : 'semua';
        $status = in_array($teks('status'), ['tersedia', 'tidak-tersedia'], true) ? $teks('status') : 'semua';
        $semua = $this->semua();
        $hasil = array_values(array_filter($semua, static fn ($p) =>
            ($cari === '' || mb_stripos($p['nama'], $cari) !== false)
            && ($kategori === 'semua' || $p['kategori'] === $kategori)
            && ($status === 'semua' || self::tersedia($p) === ($status === 'tersedia'))));
        $totalHalaman = max(1, (int) ceil(count($hasil) / 7));
        $halaman = min($totalHalaman, filter_var($teks('halaman'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1);
        return ['items' => array_slice($hasil, ($halaman - 1) * 7, 7), 'jumlah' => count($hasil),
            'total' => count($semua), 'cari' => $cari, 'kategori' => $kategori, 'status' => $status,
            'halaman' => $halaman, 'totalHalaman' => $totalHalaman];
    }

    public function simpan(?int $id, array $input, ?array $unggah): array
    {
        $lama = $id === null ? null : $this->cari($id);
        if ($id !== null && $lama === null) return ['error' => 'Produk tidak ditemukan.', 'kode' => 404];
        if ($lama !== null && (string) $lama['versi'] !== ($input['versi'] ?? null)) {
            return ['error' => 'Produk sudah berubah di tab lain. Muat ulang form sebelum menyimpan.', 'kode' => 409];
        }
        $teks = static fn ($k) => is_string($input[$k] ?? null) ? trim($input[$k]) : '';
        $nama = $teks('nama');
        $deskripsi = $teks('deskripsi');
        $harga = filter_var($teks('harga'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1000, 'max_range' => 10000000]]);
        $stok = filter_var($teks('stok'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000]]);
        $error = null;
        if ($nama === '' || mb_strlen($nama) > 120) $error = 'Nama produk wajib diisi, maksimal 120 karakter.';
        elseif (!isset($this->kategori()[$teks('kategori')])) $error = 'Pilih kategori produk yang tersedia.';
        elseif (!in_array($teks('jenis'), ['Ready Stock', 'Pre-Order'], true)) $error = 'Pilih jenis pemesanan yang valid.';
        elseif ($harga === false) $error = 'Harga harus berupa angka bulat antara Rp1.000 dan Rp10.000.000.';
        elseif ($stok === false) $error = 'Stok harus berupa angka bulat antara 0 dan 10.000 loyang.';
        elseif (!in_array($teks('aktif'), ['1', '0'], true)) $error = 'Pilih ketersediaan produk yang valid.';
        elseif ($deskripsi === '' || mb_strlen($deskripsi) > 2000) $error = 'Deskripsi wajib diisi, maksimal 2.000 karakter.';
        foreach ($this->semua() as $produk) {
            if ($produk['id'] !== $id && mb_strtolower($produk['nama']) === mb_strtolower($nama)) {
                $error ??= 'Nama produk sudah digunakan. Gunakan nama lain.';
            }
        }
        if ($error !== null) return ['error' => $error, 'kode' => 400];
        $foto = $lama['foto'] ?? null;
        if ($unggah !== null && ($unggah['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (!is_int($unggah['error'] ?? null) || $unggah['error'] !== UPLOAD_ERR_OK
                || !is_string($unggah['tmp_name'] ?? null) || !is_uploaded_file($unggah['tmp_name'])
                || ($unggah['size'] ?? 0) > 2 * 1024 * 1024) {
                return ['error' => 'Foto gagal diunggah. Gunakan JPG, PNG, atau WebP maksimal 2 MB.', 'kode' => 400];
            }
            $bytes = file_get_contents($unggah['tmp_name']);
            $ukuran = @getimagesizefromstring($bytes);
            if ($ukuran === false || !in_array($ukuran[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
                || $ukuran[0] > 4000 || $ukuran[1] > 4000) {
                return ['error' => 'Foto harus berupa gambar JPG, PNG, atau WebP, maksimal 4.000 × 4.000 piksel.', 'kode' => 400];
            }
            $foto = ['mime' => $ukuran['mime'], 'data' => $bytes];
        }
        $this->siapkan();
        if ($id === null) $id = DataModel::$data['app_admin_produk']['berikutnya']++;
        $gambar = $lama['gambar'] ?? '';
        if ($foto !== null && $foto !== ($lama['foto'] ?? null)) {
            $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$foto['mime']];
            $gambar = 'produk-' . $id . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
            $folder = dirname(__DIR__, 3) . '/public/assets/images/';
            if (file_put_contents($folder . $gambar, $foto['data'], LOCK_EX) === false) {
                return ['error' => 'Foto produk belum dapat disimpan.', 'kode' => 500];
            }
        }
        DataModel::$data['app_admin_produk']['items'][$id] = [
            'id' => $id, 'nama' => $nama, 'kategori' => $teks('kategori'), 'harga' => $harga,
            'stok' => $stok, 'jenis' => $teks('jenis'), 'aktif' => $teks('aktif') === '1',
            'deskripsi' => $deskripsi, 'gambar' => $gambar, 'foto' => $foto,
            'versi' => ($lama['versi'] ?? 0) + 1, 'dihapus' => false,
        ];
        return ['error' => null, 'id' => $id];
    }

    public function hapus(int $id, string $versi): ?string
    {
        $produk = $this->cari($id);
        if ($produk === null) return 'Produk tidak ditemukan atau sudah dihapus.';
        if ((string) $produk['versi'] !== $versi) return 'Produk sudah berubah. Periksa kembali sebelum menghapus.';
        DataModel::$data['app_admin_produk']['items'][$id]['dihapus'] = true;
        DataModel::$data['app_admin_produk']['items'][$id]['versi']++;
        return null;
    }

    public function pulihkan(int $id): bool
    {
        $this->siapkan();
        $produk = DataModel::$data['app_admin_produk']['items'][$id] ?? null;
        if ($produk === null || !$produk['dihapus']) return false;
        if (!isset($this->kategori()[$produk['kategori']])) return false;
        foreach ($this->semua() as $aktif) {
            if (mb_strtolower($aktif['nama']) === mb_strtolower($produk['nama'])) return false;
        }
        DataModel::$data['app_admin_produk']['items'][$id]['dihapus'] = false;
        DataModel::$data['app_admin_produk']['items'][$id]['versi']++;
        return true;
    }
}
