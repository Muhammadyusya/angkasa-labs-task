<?php
// setup_users.php (Jalankan file ini SEKALI SAJA di browser)
require_once 'koneksi.php'; // <-- Hapus 'src/', karena filenya sudah satu level

$users = [
    ['Admin Utama', 'admin@angkasalabs.com', 'admin123', 'Administrator'],
    ['Yusya Yanwar', 'yusya@angkasalabs.com', 'pm123', 'Project Manager'],
    ['Siska Amelia', 'siska@angkasalabs.com', 'staff123', 'Staff'],
    ['Adrian Wirawan', 'adrian@angkasalabs.com', 'staff123', 'Staff']
];

try {
    foreach ($users as $u) {
        $hashed_password = password_hash($u[2], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$u[0], $u[1], $hashed_password, $u[3]]);
    }
    echo "<h1>4 Akun berhasil di-generate dengan enkripsi Bcrypt!</h1>";
    echo "<p>Silakan hapus file ini dan menuju <a href='login.php'>Halaman Login</a>.</p>";
} catch (PDOException $e) {
    echo "Gagal: " . $e->getMessage();
}
?>