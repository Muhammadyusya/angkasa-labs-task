<?php

declare(strict_types=1);

require_once 'includes/auth.php';
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);

$taskId    = $payload['id'] ?? null;
$newStatus = $payload['status'] ?? null;
$editor    = $_SESSION['nama'] ?? 'System';

if (!$taskId || !$newStatus) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing task ID or status']);
    exit;
}

try {
    // Kita simpan $editor buat audit trail, biar ketauan siapa yang geser task di board
    $query = "UPDATE tasks SET status = ?, updated_by = ? WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$newStatus, $editor, $taskId]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Task status updated successfully'
    ]);
} catch (PDOException $e) {
    // Error log sebenernya harus masuk ke internal logger, jangan di-expose ke user
    error_log("Database Error on Task Update: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
}