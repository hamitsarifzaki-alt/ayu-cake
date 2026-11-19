# Ayu Cake

Website pemesanan kue basah berbasis PHP, MariaDB, dan Apache untuk tugas Cloud Computing Universitas Bangka Belitung. Aplikasi mendukung pelanggan, Admin, dan Super Admin.

## Instalasi

1. Siapkan PHP 8 dengan PDO MySQL, mbstring, fileinfo, dan zip, serta MariaDB dan Apache.
2. Atur koneksi basis data melalui variabel lingkungan DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, dan DB_PASSWORD.
3. Untuk membuat akun Super Admin pertama, isi AYU_CAKE_SUPERADMIN_EMAIL dan AYU_CAKE_SUPERADMIN_PASSWORD pada lingkungan terminal. Jalankan `php database/install.php`.
4. Arahkan Apache ke folder aplikasi, izinkan aturan .htaccess, lalu buka alamat yang dikonfigurasi dalam browser desktop.
5. Isi kontak toko dan data rekening melalui panel Super Admin untuk menguji alur pemesanan dan pembayaran.

Repositori berisi sumber aplikasi, aset katalog, dan skema. Skrip pemasangan menyediakan data awal katalog dan FAQ. Untuk penggunaan pengujian, siapkan identitas dan transaksi contoh sendiri.
