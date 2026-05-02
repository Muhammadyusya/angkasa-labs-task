<?php
require_once 'includes/auth.php';
require_once 'koneksi.php';

// IMPLEMENTASI RBAC: Hanya Administrator atau Project Manager yang boleh menarik data perusahaan
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Staff') {
    die("Akses Ditolak: Otoritas Anda tidak diizinkan untuk mengunduh laporan sistem.");
}

// Format nama file dinamis sesuai waktu unduh
$tanggal = date('Ymd_His');
$filename = "Laporan_Operasional_AngkasaLabs_$tanggal.xls";

// Header untuk memaksa browser mengunduh file sebagai Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

try {
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY status ASC, prioritas DESC");
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error Database.");
}
?>

<!-- Kita menggunakan tabel HTML karena Excel bisa membacanya secara otomatis dan rapi -->
<table border="1">
  <thead>
    <tr>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">ID TUGAS</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">KLIEN / PROYEK</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">SPESIFIKASI PEKERJAAN
      </th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">PIC INTERNAL</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">PRIORITAS</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">STATUS SAAT INI</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">TENGGAT WAKTU</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">TERAKHIR DIUPDATE</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($tasks as $t): ?>
    <tr>
      <td style="padding: 5px;">AL-<?= sprintf('%04d', $t['id']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($t['nama_klien']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($t['deskripsi']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($t['pic']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($t['prioritas']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($t['status']) ?></td>
      <td style="padding: 5px;">
        <?= !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : 'Belum diatur' ?></td>
      <td style="padding: 5px;">
        Oleh: <?= htmlspecialchars($t['updated_by'] ?? 'System') ?>
        (<?= htmlspecialchars($t['updated_at'] ?? '-') ?>)
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>