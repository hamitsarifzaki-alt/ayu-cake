<?php
/** Instalasi hanya melalui CLI, tidak dapat dijalankan dari browser. Aman dijalankan ulang. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$c = require dirname(__DIR__).'/config/database.php';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $c['name'])) throw new RuntimeException('Nama database tidak valid.');
$db = new PDO("mysql:host={$c['host']};port={$c['port']};charset=utf8mb4",$c['user'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec('CREATE DATABASE IF NOT EXISTS `'.$c['name'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db->exec('USE `'.$c['name'].'`');
$sql = file_get_contents(__DIR__.'/schema.sql');
foreach (explode(';',$sql) as $statement) if (trim($statement) !== '') $db->exec($statement);
$columns = $db->query("SHOW COLUMNS FROM detail_pesanan LIKE 'kategori'")->fetchAll();
if (!$columns) $db->exec("ALTER TABLE detail_pesanan ADD kategori VARCHAR(80) NOT NULL DEFAULT '' AFTER gambar");
require_once __DIR__.'/migrate-bank.php';
migrasiBank($db);
require_once __DIR__.'/migrate-hapus-admin.php';
siapkanHapusAdmin($db);
require_once __DIR__.'/migrate-hapus-pelanggan.php';
siapkanHapusPelanggan($db);
$seed = json_decode(file_get_contents(__DIR__.'/seed-data.json'),true,512,JSON_THROW_ON_ERROR);
require_once __DIR__.'/migrate-faq-pengiriman.php';
$db->beginTransaction();
try {
    $insert = static function(string $table,array $row) use ($db): void {
        $sql='INSERT IGNORE INTO `'.$table.'` (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',array_fill(0,count($row),'?')).')';
        $db->prepare($sql)->execute(array_values($row));
    };
    $email = getenv('AYU_CAKE_SUPERADMIN_EMAIL') ?: '';
    $password = getenv('AYU_CAKE_SUPERADMIN_PASSWORD') ?: '';
    if ($email !== '' || $password !== '') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || strlen($password) > 72) {
            throw new RuntimeException('Isi email Super Admin yang valid dan kata sandi 8–72 byte.');
        }
        $insert('pengguna', [
            'nama' => 'Super Admin',
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'peran' => 'superadmin',
        ]);
    }
    foreach (['bolu'=>['Bolu','Pilihan bolu kukus dan panggang.'],'brownies'=>['Brownies','Pilihan brownies cokelat.'],'kue-basah'=>['Kue Basah','Pilihan kue basah tradisional.']] as $id=>[$name,$description]) $insert('kategori',['id'=>$id,'nama'=>$name,'deskripsi'=>$description]);
    foreach ($seed['produk'] as $p) {
        $p['kategori_id'] = $p['id'] === 3 ? 'brownies' : $p['kategori']; unset($p['kategori']);
        $insert('produk',$p);
    }
    foreach ($seed['faq'] as $i=>$f) { $f['terbuka']=(int)$f['terbuka']; $insert('faq',['id'=>$i+1]+$f); }
    // Selaraskan jawaban bawaan lama. FAQ yang telah diedit pengelola dipertahankan.
    perbaruiFaqPengiriman($db, $seed['faq']);
    $insert('profil_toko',['id'=>1,'nama'=>'Ayu Cake','slogan'=>'Kue untuk setiap cerita','deskripsi'=>'Ayu Cake menyediakan kue basah dan bolu dari Desa Bukit Terap, Kecamatan Tukak Sadai, Bangka Selatan.',
        'alamat'=>'Desa Bukit Terap, Kecamatan Tukak Sadai, Kabupaten Bangka Selatan, Bangka Belitung.','whatsapp'=>'','instagram'=>'','hari_operasional'=>'','jam_operasional'=>'08:00 - 22:00',
        'logo'=>'']);
    $db->commit(); echo "Database {$c['name']} siap. Data yang sudah ada dipertahankan.\n";
} catch (Throwable $e) { $db->rollBack(); throw $e; }
