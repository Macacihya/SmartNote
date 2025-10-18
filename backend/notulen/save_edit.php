<?php
require_once __DIR__ . '/../../backend/config.php';
if (!isset($_SESSION['user'])) { header('Location: ../../login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $editor_id = $_SESSION['user']['id'];
    $editor_name = $_SESSION['user']['name'];

    if (!$id || !$judul || !$isi || !$tanggal) { echo "<script>alert('Lengkapi semua field.');window.location='../../dashboard.php';</script>"; exit; }
    
    $stmt_check = $conn->prepare('SELECT penulis_id FROM notulen WHERE id = ?');
    $stmt_check->bind_param('i', $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();
    
    if (!$row) { header('Location: ../../dashboard.php'); exit; }
    
    $penulis_asli_id = $row['penulis_id']; // Simpan ID penulis asli

    if ($penulis_asli_id != $editor_id && $_SESSION['user']['role'] !== 'admin') {
        echo "<script>alert('Tidak punya akses.');window.location='../../dashboard.php';</script>"; exit;
    }

    $stmt_update = $conn->prepare('UPDATE notulen SET judul=?, isi=?, tanggal=? WHERE id=?');
    $stmt_update->bind_param('sssi', $judul, $isi, $tanggal, $id);
    
    if ($stmt_update->execute()) {
        // --- LOGIKA NOTIFIKASI EDIT ---
        // Kirim notif ke penulis asli, JIKA editornya adalah orang lain
        if ($penulis_asli_id != $editor_id) {
            $message = "Notulen Anda '".htmlspecialchars($judul)."' telah di-edit oleh ".htmlspecialchars($editor_name);
            $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, notulen_id, message) VALUES (?, ?, ?)");
            $stmt_notif->bind_param('iis', $penulis_asli_id, $id, $message);
            $stmt_notif->execute();
        }
        // --- AKHIR LOGIKA NOTIFIKASI ---
    }
    
    echo "<script>alert('Perubahan disimpan.');window.location='../../notulen/detail.php?id={$id}';</script>"; exit;
}
header('Location: ../../dashboard.php');
?>