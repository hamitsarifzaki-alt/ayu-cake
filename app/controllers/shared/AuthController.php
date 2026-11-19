<?php

require_once APP_PATH.'/models/akun/Akun.php';

class AuthController extends Controller
{
    private function teks(string $key, bool $trim = true): string
    {
        $value = is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
        return $trim ? trim($value) : $value;
    }
    private function token(string $key): string
    {
        $_SESSION[$key] ??= bin2hex(random_bytes(32));
        return $_SESSION[$key];
    }
    private function tokenValid(string $sessionKey, string $field): bool
    {
        $value = $this->teks($field);
        return $value !== '' && hash_equals($this->token($sessionKey), $value);
    }
    private function kembaliJikaMasuk(): void
    {
        $page = AkunSuperAdmin::aktif() !== null ? 'superadmin-dashboard' : (AkunAdmin::aktif() !== null ? 'admin-dashboard' : (isset($_SESSION['app_pelanggan']) ? 'home' : null));
        if ($page !== null) {
            header('Location: '.BASE_URL.'/index.php?page='.$page, true, 303);
            exit;
        }
    }
    public function login(): void
    {
        header('Cache-Control: no-store, private');
        $this->kembaliJikaMasuk();
        $error = null;
        $email = $_SESSION['app_email_daftar'] ?? '';
        $message = $_SESSION['app_auth_pesan'] ?? null;
        unset($_SESSION['app_auth_pesan']);
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $email = $this->teks('email');
            $password = $this->teks('password', false);
            if (!$this->tokenValid('app_token_auth', 'token_auth')) $error = 'Formulir sudah tidak valid. Muat ulang halaman dan coba kembali.';
            elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 72) $error = 'Email atau kata sandi tidak sesuai. Periksa kembali isianmu.';
            else {
                $super = AkunSuperAdmin::emailSuperAdmin($email);
                $admin = AkunAdmin::emailAdmin($email);
                $success = $super ? AkunSuperAdmin::masuk($email, $password) : ($admin ? AkunAdmin::masuk($email, $password) : Akun::masuk($email, $password));
                if ($success) {
                    unset($_SESSION['app_email_daftar']);
                    header('Location: '.BASE_URL.'/index.php?page='.($super ? 'superadmin-dashboard' : ($admin ? 'admin-dashboard' : 'home')), true, 303);
                    exit;
                }
                $error = 'Email atau kata sandi tidak sesuai. Periksa kembali isianmu.';
            }
            http_response_code(400);
        }
        $this->view('auth/login', ['title' => 'Masuk | Ayu Cake', 'email' => $email, 'error' => $error, 'pesan' => $message, 'token' => $this->token('app_token_auth')]);
    }
    public function registrasi(): void
    {
        $this->kembaliJikaMasuk();
        $error = null;
        $input = ['nama' => '', 'email' => '', 'telepon' => ''];
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            foreach ($input as $key => $unused) $input[$key] = $this->teks($key);
            $password = $this->teks('password', false);
            $phone = $input['telepon'];
            if (!$this->tokenValid('app_token_auth', 'token_auth')) $error = 'Formulir sudah tidak valid. Muat ulang halaman dan coba kembali.';
            elseif ($input['nama'] === '' || mb_strlen($input['nama']) > 120) $error = 'Nama lengkap wajib diisi, maksimal 120 karakter.';
            elseif (strlen($input['email']) > 254 || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $error = 'Masukkan alamat email yang valid.';
            elseif (strlen($phone) > 25 || !preg_match('/\A\+?[0-9\s()-]+\z/', $phone)
                || !preg_match('/\A(?:08|628)[0-9]{8,11}\z/', preg_replace('/\D/', '', $phone))) $error = 'Masukkan nomor telepon yang valid, diawali 08 atau +628.';
            elseif (strlen($password) < 8 || strlen($password) > 72 || trim($password) === '') $error = 'Kata sandi harus berisi 8–72 karakter.';
            elseif ($password !== $this->teks('password_confirmation', false)) $error = 'Konfirmasi kata sandi harus sama dengan kata sandi.';
            elseif (!Akun::daftar($input, $password)) $error = 'Email sudah digunakan. Silakan masuk atau gunakan email lain.';
            else {
                $_SESSION['app_email_daftar'] = strtolower($input['email']);
                $_SESSION['app_auth_pesan'] = 'Akun berhasil dibuat. Silakan masuk dengan email dan kata sandimu.';
                header('Location: '.BASE_URL.'/index.php?page=login', true, 303);
                exit;
            }
            http_response_code(400);
        }
        $this->view('auth/registrasi', ['title' => 'Daftar | Ayu Cake', 'isian' => $input, 'error' => $error, 'token' => $this->token('app_token_auth')]);
    }
    public function lupaKataSandi(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }
        $this->view('auth/lupa-kata-sandi', ['title' => 'Lupa Kata Sandi | Ayu Cake']);
    }
}
