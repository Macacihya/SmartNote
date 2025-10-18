<?php
require_once __DIR__ . '/../../backend/config.php';
if (!isset($_SESSION['user'])) { header('Location: ../../login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    
    // handle optional file upload
    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $uploaddir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
        $filename = time() . '_' . basename($_FILES['file']['name']);
        $target = $uploaddir . $filename;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) $file_path = 'uploads/' . $filename;
    }

    if (!$judul || !$isi || !$tanggal) {
        echo "<script>alert('Lengkapi semua field.');window.location='../../notulen/tambah.php';</script>"; exit;
    }

    $stmt = $conn->prepare("INSERT INTO notulen (judul, isi, tanggal, file_path, penulis_id) VALUES (?, ?, ?, ?, ?)");
    $penulis_id = $_SESSION['user']['id'];
    $stmt->bind_param('ssssi', $judul, $isi, $tanggal, $file_path, $penulis_id);
    
    // --- PERBAIKAN LOGIKA ADA DI SINI ---
    if ($stmt->execute()) {
        $new_notulen_id = $conn->insert_id; // Dapatkan ID notulen yang baru saja dibuat

        // --- LOGIKA NOTIFIKASI BARU ---
        $message = "Notulen baru ditambahkan: " . htmlspecialchars($judul);
        $result_peserta = $conn->query("SELECT id FROM users WHERE role = 'peserta'");
        $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, notulen_id, message) VALUES (?, ?, ?)");
        while ($peserta = $result_peserta->fetch_assoc()) {
            $stmt_notif->bind_param('iis', $peserta['id'], $new_notulen_id, $message);
            $stmt_notif->execute();
        }
        // --- AKHIR LOGIKA NOTIFIKASI ---
        
        // Pindahkan pesan sukses ke SINI
        echo "<script>alert('Notulen disimpan.');window.location='../../dashboard.php';</script>"; exit;

    } else {
        // Tambahkan pesan error jika GAGAL
        echo "<script>alert('Gagal menyimpan notulen. Silakan coba lagi.');window.location='../../notulen/tambah.php';</script>"; exit;
    }
    // --- AKHIR PERBAIKAN ---
}
header('Location: ../../notulen/tambah.php');
?>