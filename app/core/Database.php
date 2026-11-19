<?php

/** Satu koneksi PDO per permintaan; query disiapkan dengan parameter terikat. */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $c = require dirname(__DIR__, 2) . '/config/database.php';
            self::$connection = new PDO(
                "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4",
                $c['user'],
                $c['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false]
            );
            self::$connection->exec("SET time_zone = '+07:00'");
        }
        return self::$connection;
    }
}
