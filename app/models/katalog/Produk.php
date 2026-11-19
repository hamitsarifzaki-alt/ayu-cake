<?php

class Produk
{
    public function semua(): array
    {
        return array_values(array_filter(DataModel::$data['app_admin_produk']['items'], static fn ($p) => !$p['dihapus'] && $p['aktif']));
    }
    public function cari(int $id): ?array
    {
        foreach ($this->semua() as $p) if ($p['id'] === $id) return $p;
        return null;
    }
}
