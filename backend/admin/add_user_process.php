<?php
require_once __DIR__ . '/../config.php'; // Muat config database & session

// 1. Pastikan user login DAN adalah ADMIN
if (!isset($_SESSION['user']['id']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    // Jika tidak login atau bukan admin, redirect atau tampilkan error
    header('Location: ../../login.php'); // Arahkan ke login jika tidak ada sesi
    // Atau bisa juga: echo "<script>alert('Akses ditolak.'); window.location='../../dashboard.php';</script>";
    exit;
}

// 2. Hanya proses jika request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../add_user.php'); // Redirect jika akses langsung
    exit;
}

// 3. Ambil data dari form
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? ''; // Jangan trim password
$role = in_array($_POST['role'] ?? 'peserta', ['admin', 'peserta']) ? $_POST['role'] : 'peserta'; // Ambil role dari form

// 4. Validasi Input Server-Side
if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
    echo "<script>alert('Isi semua field (Nama, Email, Password) dengan benar.');window.location='../../add_user.php';</script>";
    exit;
}
if (strlen($password) < 6) {
    echo "<script>alert('Password harus minimal 6 karakter.');window.location='../../add_user.php';</script>";
    exit;
}

// 5. Cek apakah email sudah ada
$stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
if ($stmt_check) {
    $stmt_check->bind_param('s', $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Jika email sudah ada
        echo "<script>alert('Email \"".htmlspecialchars($email)."\" sudah terdaftar. Gunakan email lain.');window.location='../../add_user.php';</script>";
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();
} else {
    error_log("Add User: Gagal prepare statement cek email: " . $conn->error);
    echo "<script>alert('Terjadi kesalahan saat memeriksa email. Silakan coba lagi.');window.location='../../add_user.php';</script>";
    exit;
}

// 6. Hash Password
$hash = password_hash($password, PASSWORD_DEFAULT);

// 7. Masukkan User Baru ke Database
$ins_stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
if ($ins_stmt) {
    $ins_stmt->bind_param('ssss', $name, $email, $hash, $role);

    if ($ins_stmt->execute()) {
        // Berhasil ditambahkan
        echo "<script>alert('Pengguna \"".htmlspecialchars($name)."\" berhasil ditambahkan.');window.location='../../dashboard.php';</script>"; // Redirect ke dashboard (atau halaman manajemen user nanti)
    } else {
        // Gagal eksekusi
        error_log("Add User: Gagal eksekusi insert user: " . $ins_stmt->error);
        echo "<script>alert('Gagal menambahkan pengguna. Silakan coba lagi.');window.location='../../add_user.php';</script>";
    }
    $ins_stmt->close();
} else {
    // Gagal prepare
    error_log("Add User: Gagal prepare statement insert user: " . $conn->error);
    echo "<script>alert('Terjadi kesalahan saat menambahkan pengguna. Silakan coba lagi.');window.location='../../add_user.php';</script>";
}

exit; // Akhiri script
?>