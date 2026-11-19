<?php

require_once dirname(__DIR__) . '/akun/AkunAdmin.php';
require_once dirname(__DIR__) . '/akun/AkunSuperAdmin.php';
require_once dirname(__DIR__) . '/akun/KelolaAkunPelanggan.php';

/** Autentikasi pelanggan; sesi menyimpan identitas, token, dan pesan sementara. */
class Akun
{
    public static function cari(string $email): ?array
    {
        return DataModel::$data['app_akun'][strtolower(trim($email))] ?? null;
    }
    public static function daftar(array $profile, string $password): bool
    {
        $email = strtolower(trim($profile['email']));
        if (self::cari($email) || AkunAdmin::emailAdmin($email) || AkunSuperAdmin::emailSuperAdmin($email)) return false;
        $profile = array_intersect_key($profile, array_flip(['nama', 'email', 'telepon']));
        $profile['email'] = $email;
        $authVersi = 1;
        foreach (DataModel::$data['app_kelola_pelanggan']['items'] as $arsip => $akun) {
            if (($akun['email_pelanggan_dihapus'] ?? null) === $email) {
                $authVersi = max($authVersi, DataModel::$data['app_akun'][$arsip]['auth_versi'] + 1);
            }
        }
        DataModel::$data['app_akun'][$email] = ['profil' => $profile, 'hash' => password_hash($password, PASSWORD_DEFAULT), 'auth_versi' => $authVersi,
            'data' => ['app_keranjang' => [], 'app_pesanan' => []]];
        DataModel::$data['app_kelola_pelanggan']['items'][$email] = ['id' => DataModel::newUserId($email), 'email' => $email, 'profil' => $profile, 'aktif' => true, 'versi' => 1];
        return true;
    }
    public static function masuk(string $email, string $password): bool
    {
        $email = strtolower(trim($email));
        $a = self::cari($email);
        require_once dirname(__DIR__) . '/akun/ProfilBelanjaAdmin.php';
        if ($a !== null && ProfilBelanjaAdmin::emailTerhubung($email)) return false;
        if ($a === null || !(new KelolaAkunPelanggan())->bolehMasuk($email) || !password_verify($password, $a['hash'])) return false;
        AkunAdmin::tutupSesi();
        AkunSuperAdmin::tutupSesi();
        session_regenerate_id(true);
        $_SESSION['app_pelanggan'] = $a['profil'];
        $_SESSION['app_auth_versi'] = $a['auth_versi'];
        self::bersihkanToken();
        DataModel::bindCustomer($email);
        return true;
    }
    public static function periksaAkses(): void
    {
        // Akhiri sesi pelanggan yang berasal dari fitur belanja Admin lama.
        if (isset($_SESSION['app_belanja_admin'])) {
            self::keluar();
            return;
        }
        $email = $_SESSION['app_pelanggan']['email'] ?? null;
        if (!is_string($email)) {
            DataModel::bindCustomer(null);
            return;
        }
        $a = self::cari($email);
        if (!$a || !(new KelolaAkunPelanggan())->bolehMasuk($email) || ($_SESSION['app_auth_versi'] ?? 0) !== $a['auth_versi']) {
            self::keluar();
            return;
        }
        $_SESSION['app_pelanggan'] = $a['profil'];
        DataModel::bindCustomer($email);
    }
    public static function keluar(): void
    {
        unset($_SESSION['app_pelanggan'],$_SESSION['app_auth_versi'],$_SESSION['app_belanja_admin']);
        self::bersihkanToken();
        DataModel::bindCustomer(null);
        session_regenerate_id(true);
    }
    private static function bersihkanToken(): void
    {
        foreach (['app_token_auth', 'app_token_checkout', 'app_token_pesanan', 'app_keranjang_token'] as $k) unset($_SESSION[$k]);
    }
    public static function gantiSandi(string $email, string $hash): void
    {
        if (!isset(DataModel::$data['app_akun'][$email])) return;
        DataModel::$data['app_akun'][$email]['hash'] = $hash;
        DataModel::$data['app_akun'][$email]['auth_versi']++;
        if (($_SESSION['app_pelanggan']['email'] ?? null) === $email) {
            $_SESSION['app_auth_versi'] = DataModel::$data['app_akun'][$email]['auth_versi'];
            session_regenerate_id(true);
        }
    }
}
