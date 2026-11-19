<?php

require_once APP_PATH.'/models/notifikasi/Notifikasi.php';

class NotifikasiController
{
    public function ringkasan(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        if (!isset($_SESSION['app_pelanggan']) && AkunAdmin::aktif() === null) {
            http_response_code(401);
            echo '{}';
            return;
        }
        echo json_encode(['pelanggan' => Notifikasi::ringkasan('pelanggan'), 'admin' => Notifikasi::ringkasan('admin'),
            'admin-pembayaran' => Notifikasi::ringkasan('admin', true)], JSON_THROW_ON_ERROR);
    }
}
