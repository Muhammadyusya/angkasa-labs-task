<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika tidak ada tiket masuk (user_id), tendang kembali ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>