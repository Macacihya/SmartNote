<?php
require_once __DIR__ . '/../../backend/config.php';
if (!isset($_SESSION['user'])) { header('Location: ../../login.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    // Perbaikan MySQLi
    $stmt = $conn->prepare('SELECT file_path FROM notulen WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    // Akhir Perbaikan

    if ($row && $row['file_path']) {
        $path = __DIR__ . '/../../' . $row['file_path'];
        if (file_exists($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($path).'"');
            header('Expires: 0'); header('Cache-Control: must-revalidate'); header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path); exit;
        } else { echo 'File tidak ditemukan.'; }
    } else { echo 'Tidak ada file.'; }
}
header('Location: ../../dashboard.php');
?>