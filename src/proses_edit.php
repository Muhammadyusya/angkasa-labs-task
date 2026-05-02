<?php
require_once 'includes/auth.php';
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id         = $_POST['id'];
    $nama_klien = trim($_POST['nama_klien']);
    $pic        = trim($_POST['pic']);
    $deskripsi  = trim($_POST['deskripsi']);
    $prioritas  = $_POST['prioritas'];
    $status     = $_POST['status'];
    // Tangkap data kalender
    $due_date   = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    try {
        // Suntikkan due_date ke dalam query UPDATE
        $sql = "UPDATE tasks SET nama_klien = ?, pic = ?, deskripsi = ?, prioritas = ?, status = ?, due_date = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama_klien, $pic, $deskripsi, $prioritas, $status, $due_date, $id]);
        
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data operasional berhasil diperbarui!'];
        header("Location: daftar_tugas.php");
        exit;
    } catch (PDOException $e) {
        die("Gagal memperbarui data: " . $e->getMessage());
    }
} else {
    header("Location: daftar_tugas.php");
    exit;
}
?>