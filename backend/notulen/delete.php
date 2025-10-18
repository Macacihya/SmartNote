<?php
require_once __DIR__ . '/../../backend/config.php';
if (!isset($_SESSION['user'])) { header('Location: ../../login.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    // Perbaikan MySQLi - Cek Kepemilikan
    $stmt_check = $conn->prepare('SELECT penulis_id, file_path FROM notulen WHERE id = ?');
    $stmt_check->bind_param('i', $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();
    // Akhir Perbaikan
    
    if (!$row) { header('Location: ../../dashboard.php'); exit; }
    if ($row['penulis_id'] != $_SESSION['user']['id'] && $_SESSION['user']['role'] !== 'admin') {
        echo "<script>alert('Tidak punya akses.');window.location='../../dashboard.php';</script>"; exit;
    }

    // Hapus file jika ada
    if ($row['file_path']) {
        $fp = __DIR__ . '/../../' . $row['file_path'];
        if (file_exists($fp)) @unlink($fp);
    }

    // Perbaikan MySQLi - Hapus Data
    $stmt_delete = $conn->prepare('DELETE FROM notulen WHERE id = ?');
    $stmt_delete->bind_param('i', $id);
    $stmt_delete->execute();
    // Akhir Perbaikan

    echo "<script>alert('Notulen dihapus.');window.location='../../dashboard.php';</script>";
    exit;
}
header('Location: ../../dashboard.php');
?>