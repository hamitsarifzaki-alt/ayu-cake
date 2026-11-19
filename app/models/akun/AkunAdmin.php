<?php

require_once dirname(__DIR__) . '/akun/KelolaAkunAdmin.php';

class AkunAdmin
{
    public static function emailAdmin(string $email): bool
    {
        return (new KelolaAkunAdmin())->cariEmail($email) !== null;
    }

    public static function aktif(): ?array
    {
        $admin = $_SESSION['app_admin'] ?? null;
        $model = new KelolaAkunAdmin();
        $akun = is_array($admin) ? (isset($admin['id']) ? $model->cari((int) $admin['id']) : $model->cariEmail($admin['email'] ?? '')) : null;
        if (!is_array($admin) || $akun === null || !$akun['aktif'] || ($admin['email'] ?? null) !== $akun['email']
            || (isset($admin['auth_versi']) && $admin['auth_versi'] !== $akun['auth_versi'])
            || ($admin['peran'] ?? null) !== 'Admin'
            || ($admin['terautentikasi'] ?? false) !== true) {
            // Sesi pratinjau dashboard lama tidak dianggap berhasil masuk.
            unset($_SESSION['app_admin'], $_SESSION['app_admin_token']);
            return null;
        }
        $admin['id'] = $akun['id'];
        $admin['auth_versi'] = $akun['auth_versi'];
        $admin['nama'] = $akun['nama'];
        $_SESSION['app_admin'] = $admin;
        return $admin;
    }

    public static function masuk(string $email, string $sandi): bool
    {
        $akun = (new KelolaAkunAdmin())->cariEmail($email);
        if ($akun === null || !$akun['aktif'] || !password_verify($sandi, $akun['hash'])) return false;

        require_once dirname(__DIR__) . '/akun/Akun.php';
        if (is_array($_SESSION['app_pelanggan'] ?? null)) {
            // Simpan data pelanggan sebelum berpindah identitas ke admin.
            Akun::keluar();
        } else {
            session_regenerate_id(true);
        }
        $_SESSION['app_admin'] = [
            'id' => $akun['id'], 'nama' => $akun['nama'], 'email' => $akun['email'], 'auth_versi' => $akun['auth_versi'],
            'peran' => 'Admin', 'terautentikasi' => true,
        ];
        require_once dirname(__DIR__) . '/akun/AkunSuperAdmin.php';
        AkunSuperAdmin::tutupSesi();
        $_SESSION['app_admin_token'] = bin2hex(random_bytes(32));
        unset($_SESSION['app_admin_login_token'], $_SESSION['app_admin_pesan'],
            $_SESSION['app_token_auth']);
        return true;
    }

    public static function keluar(): void
    {
        self::tutupSesi();
        session_regenerate_id(true);
    }

    public static function tutupSesi(): void
    {
        if (isset($_SESSION['app_belanja_admin'])) {
            unset($_SESSION['app_pelanggan'], $_SESSION['app_auth_versi'], $_SESSION['app_belanja_admin']);
            DataModel::bindCustomer(null);
        }
        unset($_SESSION['app_admin'], $_SESSION['app_admin_token'],
            $_SESSION['app_admin_login_token']);
    }
}
