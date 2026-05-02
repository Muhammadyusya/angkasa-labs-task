<?php

declare(strict_types=1);

require_once 'includes/auth.php';
require_once 'koneksi.php';

// RBAC Check: Hanya Role privileged yang bisa tarik laporan mentah perusahaan
$userRole = $_SESSION['role'] ?? 'Staff';
if ($userRole === 'Staff') {
    http_response_code(403);
    die("Akses Ditolak: Anda tidak memiliki otoritas untuk mengunduh laporan sistem.");
}

$timestamp = date('Ymd_His');
$fileName  = "Laporan_Operasional_AngkasaLabs_$timestamp.xls";

// Force download header untuk format Excel (HTML Table compatibility mode)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$fileName\"");
header("Pragma: no-cache");
header("Expires: 0");

try {
    // Kita sort berdasarkan status dan prioritas agar laporan enak dibaca manajerial
    $query = "SELECT * FROM tasks ORDER BY status ASC, prioritas DESC";
    $stmt  = $pdo->query($query);
    $reportData = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Report Export Error: " . $e->getMessage());
    die("Gagal menarik data dari database.");
}
?>

<table border="1">
  <thead>
    <tr>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">ID TUGAS</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">KLIEN / PROYEK</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">SPESIFIKASI PEKERJAAN</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">PIC INTERNAL</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">PRIORITAS</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">STATUS SAAT INI</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">TENGGAT WAKTU</th>
      <th style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 10px;">AUDIT TRAIL</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($reportData as $row): ?>
    <tr>
      <td style="padding: 5px;">AL-<?= sprintf('%04d', $row['id']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($row['nama_klien']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($row['deskripsi']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($row['pic']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($row['prioritas']) ?></td>
      <td style="padding: 5px;"><?= htmlspecialchars($row['status']) ?></td>
      <td style="padding: 5px;">
        <?= !empty($row['due_date']) ? date('d M Y', strtotime($row['due_date'])) : '-' ?>
      </td>
      <td style="padding: 5px;">
        Updated by: <?= htmlspecialchars($row['updated_by'] ?? 'System') ?> 
        at (<?= htmlspecialchars($row['updated_at'] ?? '-') ?>)
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>