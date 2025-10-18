<?php
require_once __DIR__ . '/../config.php'; // Load config & session

// Pastikan user login
if (!isset($_SESSION['user']['id'])) {
    header('Location: ../../login.php');
    exit;
}

// Hanya proses jika request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../profile.php'); // Redirect jika akses langsung
    exit;
}

// Ambil data dari form
$userId = $_SESSION['user']['id'];
$name = trim($_POST['name'] ?? '');
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// --- Validasi Nama ---
if (empty($name)) {
    echo "<script>alert('Nama tidak boleh kosong.'); window.location='../../edit_profile.php';</script>";
    exit;
}

// --- Proses Ubah Password (jika diisi) ---
$newPasswordHash = null; // Variabel untuk menyimpan hash password baru (jika ada)
if (!empty($newPassword)) {
    // 1. Pastikan password saat ini dimasukkan
    if (empty($currentPassword)) {
        echo "<script>alert('Masukkan Password Saat Ini untuk mengganti password.'); window.location='../../edit_profile.php';</script>";
        exit;
    }
    // 2. Pastikan password baru dan konfirmasi cocok
    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('Password Baru dan Konfirmasi Password tidak cocok.'); window.location='../../edit_profile.php';</script>";
        exit;
    }
    // 3. Verifikasi password saat ini
    $stmt_check = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    if ($stmt_check) {
        $stmt_check->bind_param('i', $userId);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $user_data = $result_check->fetch_assoc();
        $stmt_check->close();

        if (!$user_data || !password_verify($currentPassword, $user_data['password_hash'])) {
            echo "<script>alert('Password Saat Ini salah.'); window.location='../../edit_profile.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Gagal memverifikasi password saat ini.'); window.location='../../edit_profile.php';</script>";
        exit;
    }
    // 4. Hash password baru jika semua verifikasi lolos
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
}

// --- Proses Upload Foto Profil (jika ada) ---
$profilePicturePath = $_SESSION['user']['profile_picture_path'] ?? null; // Ambil path lama (jika ada)
$uploadOk = 1;
$imageFileType = null;

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $targetDir = __DIR__ . "/../../uploads/profiles/"; // Folder untuk foto profil
    // Buat folder jika belum ada
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Buat nama file unik (misal: user_1_timestamp.jpg)
    $filename = "user_" . $userId . "_" . time() . '.' . pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
    $targetFile = $targetDir . $filename;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Cek apakah file adalah gambar asli
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check === false) {
        echo "<script>alert('File bukan gambar.'); window.location='../../edit_profile.php';</script>";
        $uploadOk = 0;
    }

    // Cek ukuran file (misal maks 2MB)
    if ($_FILES["profile_picture"]["size"] > 2000000) {
        echo "<script>alert('Ukuran file terlalu besar (maks 2MB).'); window.location='../../edit_profile.php';</script>";
        $uploadOk = 0;
    }

    // Izinkan format tertentu
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "<script>alert('Hanya format JPG, JPEG, PNG & GIF yang diizinkan.'); window.location='../../edit_profile.php';</script>";
        $uploadOk = 0;
    }

    // Coba upload jika semua OK
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
            // Hapus foto lama jika ada dan berhasil upload baru
            if ($profilePicturePath && file_exists(__DIR__ . "/../../" . $profilePicturePath)) {
                 @unlink(__DIR__ . "/../../" . $profilePicturePath);
            }
             // Path relatif untuk disimpan di DB dan session
            $profilePicturePath = 'uploads/profiles/' . $filename; 
        } else {
            echo "<script>alert('Maaf, terjadi error saat mengupload file.'); window.location='../../edit_profile.php';</script>";
            $uploadOk = 0; // Set gagal jika move_uploaded_file error
        }
    }
} // Akhir cek upload file

// --- Update Database ---
// Siapkan query UPDATE
$sql = "UPDATE users SET name = ?";
$params = [$name]; // Mulai dengan parameter nama
$types = "s"; // Tipe data string untuk nama

// Tambahkan update password jika ada hash baru
if ($newPasswordHash !== null) {
    $sql .= ", password_hash = ?";
    $params[] = $newPasswordHash;
    $types .= "s"; // Tipe data string untuk hash
}

// Tambahkan update path foto jika ada upload baru yang sukses
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0 && $uploadOk == 1) {
    $sql .= ", profile_picture_path = ?";
    $params[] = $profilePicturePath;
    $types .= "s"; // Tipe data string untuk path
}

// Tambahkan WHERE clause
$sql .= " WHERE id = ?";
$params[] = $userId;
$types .= "i"; // Tipe data integer untuk ID

// Prepare dan execute statement
$stmt_update = $conn->prepare($sql);
if ($stmt_update) {
    $stmt_update->bind_param($types, ...$params); // Gunakan splat operator (...) untuk bind multiple params
    
    if ($stmt_update->execute()) {
        // --- Update Session ---
        $_SESSION['user']['name'] = $name; // Update nama di session
        // Update path foto di session jika berubah
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0 && $uploadOk == 1) {
             $_SESSION['user']['profile_picture_path'] = $profilePicturePath;
        }

        echo "<script>alert('Profil berhasil diperbarui.'); window.location='../../profile.php';</script>";
    } else {
        // Error saat execute
        error_log("Gagal update profil execute: " . $stmt_update->error);
        echo "<script>alert('Gagal memperbarui profil. Silakan coba lagi.'); window.location='../../edit_profile.php';</script>";
    }
    $stmt_update->close();
} else {
    // Error saat prepare
    error_log("Gagal update profil prepare: " . $conn->error);
    echo "<script>alert('Gagal menyiapkan pembaruan profil.'); window.location='../../edit_profile.php';</script>";
}

exit; // Akhiri script
?>