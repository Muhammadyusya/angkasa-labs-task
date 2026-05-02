<?php
// src/hapus.php
require_once 'koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        die("Gagal menghapus data: " . $e->getMessage());
    }
}

// Kembalikan ke halaman daftar tugas
header("Location: daftar_tugas.php");
exit;
?>