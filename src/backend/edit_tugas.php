<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tugas = $_POST['id_tugas'] ?? '';
    $status_baru = $_POST['status_tugas'] ?? '';

    // Validasi status biar gak ada yang iseng masukin status ngasal
    $status_valid = ['belum mulai', 'sedang berjalan', 'selesai'];
    
    if (!in_array($status_baru, $status_valid) || empty($id_tugas)) {
        die("Error: Status tidak valid atau ID tugas kosong!");
    }

    $sql = "UPDATE tugas SET status_tugas = ? WHERE id_tugas = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ss", $status_baru, $id_tugas);
    
    if ($stmt->execute()) {
        echo "Sukses: Status tugas berhasil diperbarui.";
    } else {
        echo "Error sistem: " . $stmt->error;
    }
}
?>