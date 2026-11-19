<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
/** Tambahan rekening bank; seluruh data pembayaran sebelumnya dipertahankan. */
function migrasiBank(PDO $db): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS bank_toko (kode VARCHAR(20) PRIMARY KEY, nomor VARCHAR(25) NOT NULL, atas_nama VARCHAR(120) NOT NULL) ENGINE=InnoDB');
    if (!$db->query("SHOW COLUMNS FROM pesanan LIKE 'tujuan_bank'")->fetch()) {
        $db->exec('ALTER TABLE pesanan ADD tujuan_bank TEXT NULL');
    }
    // Pada instalasi lama, pengisian profil toko tidak lagi mengirim kolom ini.
    $kolomProfil = $db->query('SHOW COLUMNS FROM profil_toko')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($kolomProfil as $kolom) {
        if ($kolom['Default'] === null && in_array($kolom['Field'], ['ewallet_nama', 'ewallet_nomor', 'atas_nama', 'qris'], true)) {
            $db->exec("ALTER TABLE profil_toko ALTER COLUMN `{$kolom['Field']}` SET DEFAULT ''");
        }
    }
    $db->exec("UPDATE faq SET jawaban=REPLACE(jawaban,'e-wallet','transfer bank'),versi=versi+1 WHERE jawaban LIKE '%e-wallet%'");
    $db->exec("UPDATE faq SET jawaban=REPLACE(jawaban,'transfer bank atau QRIS yang tersedia','transfer bank ke rekening BRI atau SeaBank yang tersedia'),versi=versi+1 WHERE jawaban LIKE '%transfer bank atau QRIS yang tersedia%'");
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require_once dirname(__DIR__).'/app/core/Database.php';
    migrasiBank(Database::connection());
    echo "Penyimpanan rekening bank siap; data sebelumnya dipertahankan.\n";
}
