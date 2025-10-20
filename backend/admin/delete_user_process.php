<?php
require_once __DIR__ . '/../config.php'; // Muat config database & session

// 1. Pastikan user login DAN adalah ADMIN
if (!isset($_SESSION['user']['id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

// 2. Hanya proses jika request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../manage_users.php'); // Redirect jika akses langsung
    exit;
}

// 3. Ambil ID user yang akan dihapus dari form
$userIdToDelete = filter_input(INPUT_POST, 'user_id_to_delete', FILTER_VALIDATE_INT);

// 4. Validasi ID
if (!$userIdToDelete || $userIdToDelete <= 0) {
    header('Location: ../../manage_users.php?status=error'); // ID tidak valid
    exit;
}

// 5. Pastikan admin tidak menghapus dirinya sendiri
if ($userIdToDelete === $_SESSION['user']['id']) {
    header('Location: ../../manage_users.php?status=self'); // Tidak bisa hapus diri sendiri
    exit;
}

// 6. Dapatkan data user yang akan dihapus (terutama role dan path foto)
$stmt_get = $conn->prepare("SELECT role, profile_picture_path FROM users WHERE id = ?");
$userDataToDelete = null;
if ($stmt_get) {
    $stmt_get->bind_param('i', $userIdToDelete);
    $stmt_get->execute();
    $result_get = $stmt_get->get_result();
    $userDataToDelete = $result_get->fetch_assoc();
    $stmt_get->close();
} else {
    error_log("Delete User: Gagal prepare get user data: " . $conn->error);
    header('Location: ../../manage_users.php?status=error');
    exit;
}

// 7. Cek apakah user ditemukan dan apakah rolenya adalah 'peserta'
if (!$userDataToDelete) {
    header('Location: ../../manage_users.php?status=error'); // User tidak ditemukan
    exit;
}
if ($userDataToDelete['role'] !== 'peserta') {
    header('Location: ../../manage_users.php?status=not_participant'); // Hanya boleh hapus peserta
    exit;
}

// 8. Lakukan Proses Penghapusan
$stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'peserta'"); // Double check role
if ($stmt_delete) {
    $stmt_delete->bind_param('i', $userIdToDelete);

    if ($stmt_delete->execute()) {
        // Jika berhasil dihapus:
        // a. Hapus foto profil dari server (jika ada)
        if (!empty($userDataToDelete['profile_picture_path'])) {
             $filePath = __DIR__ . "/../../" . $userDataToDelete['profile_picture_path'];
             if (file_exists($filePath)) {
                 @unlink($filePath);
             }
        }

        // b. Redirect kembali ke manage_users dengan pesan sukses
        header('Location: ../../manage_users.php?status=deleted');
        exit;

    } else {
        // Error saat eksekusi delete
        error_log("Delete User: Gagal eksekusi delete: " . $stmt_delete->error);
        header('Location: ../../manage_users.php?status=error');
    }
    $stmt_delete->close();
} else {
    // Error saat prepare statement delete
    error_log("Delete User: Gagal prepare statement delete: " . $conn->error);
    header('Location: ../../manage_users.php?status=error');
}

exit;
?>