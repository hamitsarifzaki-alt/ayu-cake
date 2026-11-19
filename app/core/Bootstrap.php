<?php

date_default_timezone_set('Asia/Jakarta');
ini_set('display_errors', '0');
ini_set('session.use_strict_mode', '1');
if (session_status() === PHP_SESSION_NONE) {
    session_name('AYUCAKESESSID');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'path' => rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/').'/']);
    session_start();
}
// Sesi prototipe tidak menjadi sumber akun/izin pada backend.
if (($_SESSION['backend_version'] ?? null) !== 1) {
    $_SESSION = ['backend_version' => 1];
    session_regenerate_id(true);
}
header('X-Content-Type-Options: nosniff');
require_once APP_PATH . '/models/database/DataModel.php';
ob_start();
register_shutdown_function(static function (): void {
    $fatal = error_get_last();
    $failed = $fatal && in_array($fatal['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true);
    try {
        DataModel::finish(!$failed && http_response_code() < 500);
    } catch (Throwable $e) {
        error_log('Ayu Cake database: '.$e->getMessage());
        while (ob_get_level()) ob_end_clean();
        header_remove('Location');
        header_remove('Content-Length');
        http_response_code(in_array($e->getCode(), [409, 503], true) ? $e->getCode() : 500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>Perubahan belum tersimpan</h1><p>Silakan muat ulang halaman dan coba kembali.</p>';
    }
});
set_exception_handler(static function (Throwable $e): void {
    error_log('Ayu Cake: '.$e->getMessage());
    http_response_code(500);
    while (ob_get_level()) ob_end_clean();
    echo '<h1>Aplikasi belum dapat memproses permintaan</h1><p>Periksa koneksi database atau hubungi pengelola.</p>';
});
DataModel::begin();
require_once APP_PATH . '/models/akun/Akun.php';
Akun::periksaAkses();
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['aksi_pelanggan'])) {
    if ($_POST['aksi_pelanggan'] === 'keluar') {
        $token = $_POST['token_auth'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['app_token_auth'] ?? '', $token) || $token === '') {
            http_response_code(403);
            exit('Formulir keluar tidak valid.');
        }
        Akun::keluar();
    }
    header('Location: '.BASE_URL.'/index.php?page=home', true, 303);
    exit;
}
