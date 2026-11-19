<?php

class AkunSuperAdmin
{

    public static function emailSuperAdmin(string $email): bool
    {
        return strtolower(trim($email)) === (DataModel::$data['superadmin']['email'] ?? null);
    }

    public static function aktif(): ?array
    {
        $akun = $_SESSION['app_superadmin'] ?? null;
        return is_array($akun) && ($akun['email'] ?? null) === (DataModel::$data['superadmin']['email'] ?? null) && (bool) (DataModel::$data['superadmin']['aktif'] ?? false) && ($akun['auth_versi'] ?? 0) === (int) DataModel::$data['superadmin']['auth_versi']
            && ($akun['peran'] ?? null) === 'Super Admin' && ($akun['terautentikasi'] ?? false) === true ? $akun : null;
    }

    public static function masuk(string $email, string $sandi): bool
    {
        if (!self::emailSuperAdmin($email) || !DataModel::$data['superadmin']['aktif'] || !password_verify($sandi, DataModel::$data['superadmin']['password_hash'])) return false;
        require_once dirname(__DIR__) . '/akun/Akun.php';
        if (is_array($_SESSION['app_pelanggan'] ?? null)) Akun::keluar();
        else session_regenerate_id(true);
        AkunAdmin::tutupSesi();
        $_SESSION['app_superadmin'] = ['nama' => DataModel::$data['superadmin']['nama'], 'email' => DataModel::$data['superadmin']['email'], 'auth_versi' => (int) DataModel::$data['superadmin']['auth_versi'],
            'peran' => 'Super Admin', 'terautentikasi' => true];
        $_SESSION['app_superadmin_token'] = bin2hex(random_bytes(32));
        unset($_SESSION['app_token_auth']);
        return true;
    }

    public static function tutupSesi(): void
    {
        unset($_SESSION['app_superadmin'], $_SESSION['app_superadmin_token']);
    }

    public static function keluar(): void
    {
        self::tutupSesi();
        session_regenerate_id(true);
    }
}
