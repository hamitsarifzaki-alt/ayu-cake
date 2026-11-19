<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function siapkanHapusPelanggan(PDO $db): void
{
    $db->exec('ALTER TABLE pengguna ADD COLUMN IF NOT EXISTS email_pelanggan_dihapus VARCHAR(254) NULL AFTER email');
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require_once dirname(__DIR__) . '/app/core/Database.php';
    siapkanHapusPelanggan(Database::connection());
    echo "Struktur penghapusan akun pelanggan siap.\n";
}
