<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require dirname(__DIR__) . '/app/core/Database.php';
require dirname(__DIR__) . '/app/models/pesanan/Pesanan.php';

$db = null;
$terkunci = false;
try {
    $db = Database::connection();
    $terkunci = (int) $db->query("SELECT GET_LOCK('ayu_cake_write',15)")->fetchColumn() === 1;
    if (!$terkunci) throw new RuntimeException('Sistem sedang sibuk. Pemeriksaan akan dicoba pada jadwal berikutnya.');
    $db->beginTransaction();
    $jumlah = Pesanan::batalkanLewatTenggat();
    $db->commit();
    echo $jumlah . " pesanan melewati tenggat dibatalkan.\n";
} catch (Throwable $e) {
    if ($db !== null && $db->inTransaction()) $db->rollBack();
    file_put_contents('php://stderr', 'Pemeriksaan tenggat: ' . $e->getMessage() . "\n");
    exit(1);
} finally {
    if ($db !== null && $terkunci) $db->query("SELECT RELEASE_LOCK('ayu_cake_write')");
}
