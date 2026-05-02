<?php

declare(strict_types=1);

require_once 'includes/auth.php';
require_once 'koneksi.php';

/**
 * Endpoint ini menghapus task secara permanen.
 * RBAC check: Hanya admin atau manager yang boleh delete.
 */
$userRole = $_SESSION['role'] ?? 'Staff';
if ($userRole === 'Staff') {
    header("Location: daftar_tugas.php?error=unauthorized");
    exit;
}

$taskId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($taskId) {
    try {
        $query = "DELETE FROM tasks WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$taskId]);
        
        // Redirect dengan state success buat trigger notif di UI
        header("Location: daftar_tugas.php?status=deleted");
        exit;
    } catch (PDOException $e) {
        error_log("Critical Delete Error: " . $e->getMessage());
        die("System Failure: Unable to remove record.");
    }
}

header("Location: daftar_tugas.php");
exit;