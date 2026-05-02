<?php
// Deteksi Environment: Apakah berjalan di localhost atau live server?
$is_localhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

if ($is_localhost) {
    // Kredensial Local (Laptop Lu)
    $host = 'localhost';
    $db   = 'angkasa_labs';
    $user = 'root';
    $pass = '';
    
    // Tampilkan error penuh di lokal untuk debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Kredensial Production (Server Hosting Lu)
    $host = 'localhost'; // Biasanya tetap localhost di cPanel
    $db   = 'nama_database_hosting_lu';
    $user = 'nama_user_hosting_lu';
    $pass = 'password_database_hosting_lu';
    
    // Matikan error agar struktur direktori server tidak bocor ke publik
    error_reporting(0);
    ini_set('display_errors', 0);
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Pesan error generik untuk production, pesan detail untuk localhost
    if ($is_localhost) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    } else {
        die("Koneksi infrastruktur terputus. Tim teknis sedang menangani masalah ini.");
    }
}
?>