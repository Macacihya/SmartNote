<?php
require_once __DIR__ . '/../../backend/config.php';
if (!isset($_SESSION['user'])) { header('Location: ../../login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $user_ids = $_POST['user_ids'] ?? []; // --- TAMBAHAN: Ambil daftar peserta ---
    $editor_id = $_SESSION['user']['id'];
    $editor_name = $_SESSION['user']['name'];

    if (!$id || !$judul || !$isi || !$tanggal) { echo "<script>alert('Lengkapi semua field.');window.location='../../dashboard.php';</script>"; exit; }
    
    // --- PERBARUI QUERY: Ambil juga 'file_path' lama ---
    $stmt_check = $conn->prepare('SELECT penulis_id, file_path FROM notulen WHERE id = ?');
    $stmt_check->bind_param('i', $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();
    
    if (!$row) { header('Location: ../../dashboard.php'); exit; }
    
    $penulis_asli_id = $row['penulis_id']; // Simpan ID penulis asli
    $old_file_path = $row['file_path']; // --- TAMBAHAN: Simpan path file lama ---

    if ($penulis_asli_id != $editor_id && $_SESSION['user']['role'] !== 'admin') {
        echo "<script>alert('Tidak punya akses.');window.location='../../dashboard.php';</script>"; exit;
    }

    // --- TAMBAHAN: Logika Upload File Baru (Mirip save_new.php) ---
    $new_file_path_sql_segment = ""; // Bagian query SQL untuk update file
    $new_file_path_value = null; // Nilai path baru untuk bind_param

    // Cek jika ada file baru yang diupload
    if (isset($_FILES['file']) && !empty($_FILES['file']['name']) && $_FILES['file']['error'] == 0) {
        $uploaddir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);

        $allowed_ext = ['pdf', 'docx', 'jpg', 'png'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed_ext)) {
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $_FILES['file']['name']);
            $target = $uploaddir . $filename;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                $new_file_path_value = 'uploads/' . $filename; // Path relatif baru
                $new_file_path_sql_segment = ", file_path = ?"; // Siapkan query

                // Hapus file lama jika ada
                if ($old_file_path) {
                    $fp_old = __DIR__ . '/../../' . $old_file_path;
                    if (file_exists($fp_old)) @unlink($fp_old);
                }
            } else {
                 echo "<script>alert('Gagal mengupload file baru.');window.location='../../notulen/edit.php?id={$id}';</script>"; exit;
            }
        } else {
            echo "<script>alert('Jenis file tidak diizinkan.');window.location='../../notulen/edit.php?id={$id}';</script>"; exit;
        }
    }
    // --- AKHIR LOGIKA UPLOAD FILE ---

    // --- PERBARUI QUERY UPDATE: Buat dinamis tergantung ada file baru atau tidak ---
    $sql = "UPDATE notulen SET judul = ?, isi = ?, tanggal = ? $new_file_path_sql_segment WHERE id = ?";
    $stmt_update = $conn->prepare($sql);

    // Bind parameters secara dinamis
    if ($new_file_path_value) {
        // Jika ada file baru: ssssi (string, string, string, string_path, int_id)
        $stmt_update->bind_param('ssssi', $judul, $isi, $tanggal, $new_file_path_value, $id);
    } else {
        // Jika tidak ada file baru: sssi (string, string, string, int_id)
        $stmt_update->bind_param('sssi', $judul, $isi, $tanggal, $id);
    }
    
    if ($stmt_update->execute()) {
        
        // --- TAMBAHAN: Update Relasi Peserta ---
        // 1. Hapus semua relasi peserta yang lama untuk notulen ini
        $stmt_del_rel = $conn->prepare("DELETE FROM notulen_users WHERE notulen_id = ?");
        $stmt_del_rel->bind_param('i', $id);
        $stmt_del_rel->execute();
        $stmt_del_rel->close();

        // 2. Tambahkan relasi peserta yang baru (jika ada yang dipilih)
        if (!empty($user_ids)) {
            $stmt_add_rel = $conn->prepare("INSERT INTO notulen_users (notulen_id, user_id) VALUES (?, ?)");
            foreach ($user_ids as $uid) {
                $stmt_add_rel->bind_param('ii', $id, $uid);
                $stmt_add_rel->execute();
            }
            $stmt_add_rel->close();
        }
        // --- AKHIR UPDATE RELASI PESERTA ---


        // --- LOGIKA NOTIFIKASI EDIT (Diperbarui) ---
        
        // 1. Kirim notif ke penulis asli, JIKA editornya adalah orang lain
        if ($penulis_asli_id != $editor_id) {
            $message = "Notulen Anda '".htmlspecialchars($judul)."' telah di-edit oleh ".htmlspecialchars($editor_name);
            $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, notulen_id, message) VALUES (?, ?, ?)");
            $stmt_notif->bind_param('iis', $penulis_asli_id, $id, $message);
            $stmt_notif->execute();
            $stmt_notif->close(); // Tutup statement ini
        }
        
        // 2. Kirim notif ke semua PESERTA BARU
        if (!empty($user_ids)) {
             $message_peserta = "Notulen '".htmlspecialchars($judul)."' yang Anda ikuti telah diperbarui.";
             $stmt_notif_peserta = $conn->prepare("INSERT INTO notifications (user_id, notulen_id, message) VALUES (?, ?, ?)");
             foreach ($user_ids as $uid) {
                // Jangan kirim notif ke penulis asli jika dia sudah dapat notif di atas
                if ($uid == $penulis_asli_id && $penulis_asli_id != $editor_id) {
                    continue; // Skip, sudah dapat notif "diedit oleh..."
                }
                $stmt_notif_peserta->bind_param('iis', $uid, $id, $message_peserta);
                $stmt_notif_peserta->execute();
             }
             $stmt_notif_peserta->close(); // Tutup statement ini
        }
        // --- AKHIR LOGIKA NOTIFIKASI ---
    }
    
    echo "<script>alert('Perubahan disimpan.');window.location='../../notulen/detail.php?id={$id}';</script>"; exit;
}
header('Location: ../../dashboard.php');
?>