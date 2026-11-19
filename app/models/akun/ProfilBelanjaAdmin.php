<?php

/** Pemeriksaan akun pelanggan internal yang digunakan oleh fitur belanja Admin lama. */
class ProfilBelanjaAdmin
{
    public static function emailTerhubung(string $email): bool
    {
        $q = Database::connection()->prepare('SELECT 1 FROM profil_belanja_admin p JOIN pengguna u ON u.id=p.pelanggan_id WHERE u.email=?');
        $q->execute([strtolower(trim($email))]);
        return (bool) $q->fetchColumn();
    }
}
