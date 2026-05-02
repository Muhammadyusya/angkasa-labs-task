<?php
// Panggil file koneksi database yang udah kita buat tadi
require_once 'koneksi.php';

// Pastikan request datang dari form submit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Tangkap data dari form
    $nama_klien = $_POST['nama_klien'] ?? '';
    $pic        = $_POST['pic'] ?? '';
    $deskripsi  = $_POST['deskripsi'] ?? '';
    $prioritas  = $_POST['prioritas'] ?? 'Sedang';
    $status     = $_POST['status'] ?? 'Pending';

    // Validasi Sisi Server (Syarat Mutlak UAS: Penanganan Error)
    if (empty($nama_klien) || empty($pic) || empty($deskripsi)) {
        // Kalau ada yang kosong, tendang balik ke form pakai alert
        echo "<script>
                alert('GAGAL: Nama Klien, PIC, dan Deskripsi tidak boleh kosong!');
                window.location.href = 'tambah_tugas.html';
             </script>";
        exit;
    }

    try {
        // Siapkan Query (Gunakan Prepared Statement untuk keamanan / anti SQL Injection)
        $sql = "INSERT INTO tasks (nama_klien, pic, deskripsi, prioritas, status) 
                VALUES (:nama_klien, :pic, :deskripsi, :prioritas, :status)";
        
        $stmt = $pdo->prepare($sql);
        
        // Eksekusi kueri dengan mengikat data dari form
        $stmt->execute([
            ':nama_klien' => $nama_klien,
            ':pic'        => $pic,
            ':deskripsi'  => $deskripsi,
            ':prioritas'  => $prioritas,
            ':status'     => $status
        ]);

        // Jika berhasil, arahkan ke halaman Daftar Tugas dengan pesan sukses
        echo "<script>
                alert('BERHASIL: Tugas baru untuk klien {$nama_klien} berhasil disimpan!');
                window.location.href = 'daftar_tugas.html';
              </script>";
        exit;

    } catch (PDOException $e) {
        // Penanganan error jika database bermasalah
        echo "Error Database: " . $e->getMessage();
    }
} else {
    // Jika ada yang iseng akses file ini langsung lewat URL, tendang ke form
    header("Location: tambah_tugas.html");
    exit;
}
?>