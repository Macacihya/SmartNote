<?php
require_once __DIR__ . '/../config.php'; // Muat config database & session

// 1. Pastikan user login
if (!isset($_SESSION['user']['id'])) {
    header('Location: ../../login.php');
    exit;
}

// 2. Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../profile.php'); // Redirect jika akses langsung
    exit;
}

// 3. Pastikan user yang sedang login adalah ADMIN
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    echo "<script>alert('Hanya admin yang dapat menghapus akun.'); window.location='../../profile.php';</script>";
    exit;
}

// 4. Ambil ID user yang sedang login (yang akan dihapus)
$userIdToDelete = $_SESSION['user']['id'];

// --- Logika Tambahan (Opsional tapi Direkomendasikan) ---
// Misalnya, cek apakah ini admin terakhir? Jika ya, mungkin cegah penghapusan.
// $stmt_count_admin = $conn->prepare("SELECT COUNT(id) as admin_count FROM users WHERE role = 'admin'");
// if ($stmt_count_admin) {
//     $stmt_count_admin->execute();
//     $result_count = $stmt_count_admin->get_result();
//     $admin_count = $result_count->fetch_assoc()['admin_count'];
//     $stmt_count_admin->close();
//     if ($admin_count <= 1) {
//         echo "<script>alert('Tidak dapat menghapus admin terakhir.'); window.location='../../profile.php';</script>";
//         exit;
//     }
// }
// --- Akhir Logika Tambahan ---


// 5. Lakukan Proses Penghapusan
$stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'"); // Pastikan lagi role admin
if ($stmt_delete) {
    $stmt_delete->bind_param('i', $userIdToDelete);

    if ($stmt_delete->execute()) {
        // Jika berhasil dihapus:
        // a. Hapus foto profil dari server (jika ada)
        if (isset($_SESSION['user']['profile_picture_path']) && !empty($_SESSION['user']['profile_picture_path'])) {
            $filePath = __DIR__ . "/../../" . $_SESSION['user']['profile_picture_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // b. Hancurkan session (logout)
        session_unset();
        session_destroy();

        // c. Redirect ke halaman login dengan pesan sukses
        //    Gunakan parameter GET untuk pesan agar bisa ditampilkan di login.php
        header('Location: ../../login.php?message=account_deleted');
        exit;

    } else {
        // Error saat eksekusi delete
        error_log("Gagal eksekusi delete user: " . $stmt_delete->error);
        echo "<script>alert('Gagal menghapus akun. Silakan coba lagi.'); window.location='../../profile.php';</script>";
    }
    $stmt_delete->close();
} else {
    // Error saat prepare statement delete
    error_log("Gagal prepare statement delete user: " . $conn->error);
    echo "<script>alert('Terjadi kesalahan saat mencoba menghapus akun.'); window.location='../../profile.php';</script>";
}

exit;
?>