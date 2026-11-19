<?php


class AdminBooking
{
    public const KAPASITAS_MAKSIMAL = 50;

    private DateTimeImmutable $hari;

    public function __construct(?DateTimeImmutable $hari = null)
    {
        $this->hari = ($hari ?? new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta')))->setTime(0, 0);
    }

    public function tanggalValid(string $nilai): bool
    {
        if (!preg_match('/^(20\d{2}|2100)-\d{2}-\d{2}$/D', $nilai)) return false;
        $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d', $nilai, new DateTimeZone('Asia/Jakarta'));
        return $tanggal !== false && $tanggal->format('Y-m-d') === $nilai;
    }

    public function ringkasan(array $pesanan, ?string $tanggal = null): array
    {
        $tanggal ??= $this->hari->modify('+5 days')->format('Y-m-d');
        $terpakai = 0;
        foreach ($pesanan as $order) {
            if ($order['jenis'] === 'Booking' && $order['tanggal'] === $tanggal && $order['status'] !== 'Dibatalkan') {
                $terpakai += array_sum(array_column($order['items'], 'jumlah'));
            }
        }
        $aturan = DataModel::$data['app_admin_booking'][$tanggal] ?? [];
        $maksimal = min(self::KAPASITAS_MAKSIMAL, max(0, (int) ($aturan['maksimal'] ?? self::KAPASITAS_MAKSIMAL)));
        $dibuka = $aturan['dibuka'] ?? true;
        $sisa = max(0, $maksimal - $terpakai);
        return ['tanggal' => $tanggal, 'terpakai' => $terpakai, 'maksimal' => $maksimal,
            'sisa' => $sisa, 'dibuka' => $dibuka, 'status' => !$dibuka ? 'Ditutup' : ($sisa === 0 ? 'Penuh' : 'Tersedia'),
            'versi' => $aturan['versi'] ?? 0, 'lampau' => $tanggal < $this->hari->format('Y-m-d')];
    }

    public function kalender(array $input, array $pesanan): array
    {
        $tanggal = is_string($input['tanggal'] ?? null) && $this->tanggalValid($input['tanggal'])
            ? $input['tanggal'] : null;
        $bulan = is_string($input['bulan'] ?? null) && $this->tanggalValid($input['bulan'] . '-01')
            ? $input['bulan'] : substr($tanggal ?? $this->hari->modify('+5 days')->format('Y-m-d'), 0, 7);
        $awal = new DateTimeImmutable($bulan . '-01', new DateTimeZone('Asia/Jakarta'));
        if ($tanggal !== null && substr($tanggal, 0, 7) !== $bulan) $tanggal = null;
        $hari = [];
        for ($n = 1; $n <= (int) $awal->format('t'); $n++) {
            $nilai = $bulan . '-' . sprintf('%02d', $n);
            $hari[] = $this->ringkasan($pesanan, $nilai);
        }
        $sebelum = $awal->modify('-1 month');
        $sesudah = $awal->modify('+1 month');
        $nav = static function (DateTimeImmutable $waktu): ?array {
            if ((int) $waktu->format('Y') < 2000 || (int) $waktu->format('Y') > 2100) return null;
            return ['bulan' => $waktu->format('Y-m')];
        };
        return ['bulan' => $bulan, 'awal' => $awal, 'hariIni' => $this->hari->format('Y-m-d'),
            'pilihan' => $tanggal !== null ? $this->ringkasan($pesanan, $tanggal) : null, 'hari' => $hari,
            'offset' => (int) $awal->format('N') - 1, 'sebelum' => $nav($sebelum), 'sesudah' => $nav($sesudah)];
    }

    public function simpan(array $input, array $pesanan): array
    {
        $tanggal = is_string($input['tanggal'] ?? null) ? $input['tanggal'] : '';
        if (!$this->tanggalValid($tanggal)) return ['error' => 'Tanggal tidak valid. Pilih tanggal dari kalender.', 'kode' => 400];
        $aturan = $this->ringkasan($pesanan, $tanggal);
        if ($aturan['lampau']) return ['error' => 'Pengaturan tanggal yang sudah lewat tidak dapat diubah.', 'kode' => 400];
        if ((string) $aturan['versi'] !== ($input['versi'] ?? null)) return ['error' => 'Pengaturan sudah berubah di tab lain. Muat ulang tanggal sebelum menyimpan.', 'kode' => 409];
        $status = $input['ketersediaan'] ?? null;
        if (!is_string($status) || !in_array($status, ['dibuka', 'ditutup'], true)) return ['error' => 'Pilih ketersediaan tanggal yang valid.', 'kode' => 400];
        $kapasitas = is_string($input['kapasitas'] ?? null) ? filter_var(
            $input['kapasitas'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0, 'max_range' => self::KAPASITAS_MAKSIMAL]]
        ) : false;
        if ($kapasitas === false) return ['error' => 'Kapasitas harus berupa angka bulat antara 0 dan ' . self::KAPASITAS_MAKSIMAL . ' loyang per hari.', 'kode' => 400];
        if ($kapasitas < $aturan['terpakai']) return ['error' => 'Kapasitas tidak boleh kurang dari ' . $aturan['terpakai'] . ' loyang yang sudah terpakai.', 'kode' => 400];
        DataModel::$data['app_admin_booking'][$tanggal] = ['dibuka' => $status === 'dibuka', 'maksimal' => $kapasitas, 'versi' => $aturan['versi'] + 1];
        return ['error' => null];
    }
}
