<?php

$alamatAplikasi = rtrim(getenv('APP_URL') ?: 'http://localhost/ayu-cake', '/');

// Pertahankan asal halaman saat laptop mengakses alias lokal. Sesi localhost
// berbeda dari sesi alamat IP; navigasi dan AJAX tidak boleh berpindah asal.
$bagianAlamat = parse_url($alamatAplikasi);
$portAlamat = isset($bagianAlamat['port']) ? ':' . $bagianAlamat['port'] : '';
$hostPermintaan = strtolower($_SERVER['HTTP_HOST'] ?? '');
if (in_array($hostPermintaan, ['localhost' . $portAlamat, '127.0.0.1' . $portAlamat], true)) {
    $alamatAplikasi = ($bagianAlamat['scheme'] ?? 'http') . '://' . $hostPermintaan
        . ($bagianAlamat['path'] ?? '');
}
define('BASE_URL', $alamatAplikasi);
define('APP_PATH', dirname(__DIR__) . '/app');
