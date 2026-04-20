<?php
require_once 'koneksi.php';

// Ambil data tugas dan di-JOIN biar nama proyek dan PIC-nya ketahuan
$sql = "SELECT t.*, p.nama_proyek, u.nama as penanggung_jawab 
        FROM tugas t
        JOIN proyek p ON t.id_proyek = p.id_proyek
        JOIN pengguna u ON t.id_penanggung_jawab = u.id_pengguna
        ORDER BY t.deadline ASC";
        
$result = $koneksi->query($sql);
$data = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Bikin output jadi format JSON buat frontend
header('Content-Type: application/json');
echo json_encode($data);
?>