<?php

require_once dirname(__DIR__) . '/faq/Faq.php';

class SuperAdminFaq
{
    private function siapkan(): void
    {
    }

    public function semua(): array
    {
        $this->siapkan();
        return array_values(array_filter(DataModel::$data['app_faq']['items'], static fn ($f) => !$f['dihapus']));
    }

    public function cari(int $id): ?array
    {
        $this->siapkan();
        $f = DataModel::$data['app_faq']['items'][$id] ?? null;
        return $f !== null && !$f['dihapus'] ? $f : null;
    }

    public function simpan(?int $id, array $input): array
    {
        $this->siapkan();
        $lama = $id === null ? null : $this->cari($id);
        if ($id !== null && $lama === null) return ['error' => 'FAQ tidak ditemukan. Pilih kembali dari daftar.', 'kode' => 404];
        if ($lama !== null && ($input['versi'] ?? null) !== (string) $lama['versi']) return ['error' => 'FAQ sudah berubah. Muat ulang form sebelum menyimpan.', 'kode' => 409];
        $q = is_string($input['pertanyaan'] ?? null) ? trim($input['pertanyaan']) : '';
        $q = preg_replace('/\s+/u', ' ', $q) ?? '';
        $a = is_string($input['jawaban'] ?? null) ? trim($input['jawaban']) : '';
        if ($q === '' || mb_strlen($q) > 300) return ['error' => 'Pertanyaan wajib diisi, maksimal 300 karakter.', 'kode' => 400];
        if ($a === '' || mb_strlen($a) > 4000) return ['error' => 'Jawaban wajib diisi, maksimal 4.000 karakter.', 'kode' => 400];
        foreach ($this->semua() as $f) if ($f['id'] !== $id && mb_strtolower($f['pertanyaan']) === mb_strtolower($q)) return ['error' => 'Pertanyaan sudah tersedia. Edit FAQ tersebut atau gunakan pertanyaan lain.', 'kode' => 400];
        $id ??= DataModel::$data['app_faq']['berikutnya']++;
        $baru = ['id' => $id, 'pertanyaan' => $q, 'jawaban' => $a, 'terbuka' => $lama['terbuka'] ?? false, 'versi' => ($lama['versi'] ?? 0) + 1, 'dihapus' => false];
        DataModel::$data['app_faq']['items'][$id] = $baru;
        return ['error' => null, 'id' => $id];
    }

    public function status(int $id, array $input): array
    {
        $this->siapkan();
        $f = DataModel::$data['app_faq']['items'][$id] ?? null;
        if ($f === null) return ['error' => 'FAQ tidak ditemukan.', 'kode' => 404];
        if (($input['versi'] ?? null) !== (string) $f['versi']) return ['error' => 'FAQ sudah berubah. Muat ulang daftar sebelum melanjutkan.', 'kode' => 409];
        if (!in_array($input['aksi'] ?? null, ['hapus', 'pulihkan'], true)) return ['error' => 'Tindakan FAQ tidak valid.', 'kode' => 400];
        $hapus = $input['aksi'] === 'hapus';
        if ($f['dihapus'] === $hapus) return ['error' => 'Status FAQ sudah berubah. Muat ulang daftar.', 'kode' => 409];
        if (!$hapus) foreach ($this->semua() as $lain) if (mb_strtolower($lain['pertanyaan']) === mb_strtolower($f['pertanyaan'])) return ['error' => 'Pertanyaan yang sama sudah tersedia. FAQ tidak dapat dipulihkan.', 'kode' => 400];
        DataModel::$data['app_faq']['items'][$id]['dihapus'] = $hapus;
        DataModel::$data['app_faq']['items'][$id]['versi']++;
        return ['error' => null, 'id' => $id, 'versi' => $f['versi'] + 1];
    }
}
