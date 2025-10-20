<?php
require_once __DIR__ . '/../../backend/config.php';
if (!isset($_SESSION['user'])) { header('Location: ../../login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul   = htmlspecialchars(trim($_POST['judul'] ?? ''), ENT_QUOTES, 'UTF-8');
    $isi     = htmlspecialchars(trim($_POST['isi'] ?? ''), ENT_QUOTES, 'UTF-8');
    $tanggal = $_POST['tanggal'] ?? '';
    $user_ids = $_POST['user_ids'] ?? []; // daftar user yang dipilih

    // Handle file upload (optional)
    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $uploaddir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);

        $allowed_ext = ['pdf', 'docx', 'jpg', 'png'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_ext)) {
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $_FILES['file']['name']);
            $target = $uploaddir . $filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                $file_path = 'uploads/' . $filename;
            }
        } else {
            echo "<script>alert('Jenis file tidak diizinkan.');window.location='../../notulen/tambah.php';</script>";
            exit;
        }
    }

    // Validasi field wajib
    if (!$judul || !$isi || !$tanggal) {
        echo "<script>alert('Lengkapi semua field.');window.location='../../notulen/tambah.php';</script>";
        exit;
    }

    // Simpan notulen utama
    $stmt = $conn->prepare("INSERT INTO notulen (judul, isi, tanggal, file_path, penulis_id) VALUES (?, ?, ?, ?, ?)");
    $penulis_id = $_SESSION['user']['id'];
    $stmt->bind_param('ssssi', $judul, $isi, $tanggal, $file_path, $penulis_id);
    if ($stmt->execute()) {
        $new_notulen_id = $conn->insert_id;

        // Simpan user peserta ke tabel relasi
        if (!empty($user_ids)) {
            $stmt_rel = $conn->prepare("INSERT INTO notulen_users (notulen_id, user_id) VALUES (?, ?)");
            foreach ($user_ids as $uid) {
                $stmt_rel->bind_param('ii', $new_notulen_id, $uid);
                $stmt_rel->execute();
            }
            $stmt_rel->close();
        }

        // Tambahkan notifikasi untuk peserta
        $message = "Notulen baru ditambahkan: " . $judul;
        $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, notulen_id, message) VALUES (?, ?, ?)");
        foreach ($user_ids as $uid) {
            $stmt_notif->bind_param('iis', $uid, $new_notulen_id, $message);
            $stmt_notif->execute();
        }
        $stmt_notif->close();

        echo "<script>alert('Notulen dan peserta berhasil disimpan.');window.location='../../dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menyimpan notulen.');window.location='../../notulen/tambah.php';</script>";
        exit;
    }
}
header('Location: ../../notulen/tambah.php');
?>
