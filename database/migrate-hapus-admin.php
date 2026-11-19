<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function siapkanHapusAdmin(PDO $db): void
{
    $db->exec('ALTER TABLE pengguna ADD COLUMN IF NOT EXISTS email_admin_dihapus VARCHAR(254) NULL AFTER email');
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require_once dirname(__DIR__) . '/app/core/Database.php';
    siapkanHapusAdmin(Database::connection());
    echo "Struktur penghapusan akun admin siap.\n";
}
