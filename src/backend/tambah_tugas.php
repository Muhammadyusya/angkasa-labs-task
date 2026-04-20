<?php
require_once 'koneksi.php';

// Cek apakah ada request POST dari form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tangkap data dari frontend
    $id_proyek = $_POST['id_proyek'] ?? '';
    $id_penanggung_jawab = $_POST['id_penanggung_jawab'] ?? '';
    $judul_tugas = $_POST['judul_tugas'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $prioritas = $_POST['prioritas'] ?? 'sedang';
    $status_tugas = $_POST['status_tugas'] ?? 'belum mulai';

    // Validasi kosong
    if (empty(trim($judul_tugas)) || empty($id_proyek) || empty($id_penanggung_jawab)) {
        die("Error: Proyek, PIC, dan Judul Tugas wajib diisi!");
    }

    // Generate ID Alfanumerik (TGS-XXXXX)
    $karakter = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $kode_acak = substr(str_shuffle($karakter), 0, 5);
    $id_tugas = "TGS-" . $kode_acak;

    // Masukin ke database (semua parameter pake 's' karena VARCHAR/STRING)
    $sql = "INSERT INTO tugas (id_tugas, id_proyek, id_penanggung_jawab, judul_tugas, deskripsi, deadline, prioritas, status_tugas) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ssssssss", $id_tugas, $id_proyek, $id_penanggung_jawab, $judul_tugas, $deskripsi, $deadline, $prioritas, $status_tugas);
    
    if ($stmt->execute()) {
        echo "Sukses: Tugas berhasil ditambah dengan ID $id_tugas";
    } else {
        echo "Error sistem: " . $stmt->error;
    }
}
?>