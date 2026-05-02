<?php
// src/selesai.php
require_once 'koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE tasks SET status = 'Selesai' WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        die("Gagal mengubah status: " . $e->getMessage());
    }
}

// Kembalikan ke halaman asal (bisa dari daftar_tugas atau index)
$referer = $_SERVER['HTTP_REFERER'] ?? 'daftar_tugas.php';
header("Location: $referer");
exit;
?>