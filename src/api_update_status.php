<?php
require_once 'includes/auth.php';
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $task_id = $data['id'] ?? null;
    $new_status = $data['status'] ?? null;
    $user_name = $_SESSION['nama'] ?? 'System'; // Tangkap nama orang yang sedang login

    if ($task_id && $new_status) {
        try {
            // Catat status baru dan SIAPA yang memindahkan
            $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_by = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_name, $task_id]);
            
            echo json_encode(['success' => true, 'message' => 'Status berhasil dipindah.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database Error.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode Ditolak.']);
}
?>