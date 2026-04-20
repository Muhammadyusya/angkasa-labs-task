<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tugas = $_POST['id_tugas'] ?? '';

    if (empty($id_tugas)) {
        die("Error: ID Tugas tidak ditemukan!");
    }

    $sql = "DELETE FROM tugas WHERE id_tugas = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("s", $id_tugas);
    
    if ($stmt->execute()) {
        echo "Sukses: Tugas berhasil dihapus.";
    } else {
        echo "Error sistem: " . $stmt->error;
    }
}
?>