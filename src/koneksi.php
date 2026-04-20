<?php
// koneksi.php - Menggunakan PDO untuk standar industri

$host = '127.0.0.1'; // atau localhost
$db   = 'angkasa_labs';
$user = 'root'; // Ganti kalau XAMPP lu dipasangin password
$pass = ''; // Biasanya kosong untuk XAMPP default
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mode Error ketat
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Ambil data sebagai array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Matikan emulasi untuk keamanan
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Tangkap error dengan anggun, jangan tampilkan password di layar
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>