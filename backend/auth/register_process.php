<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? 'peserta', ['admin', 'peserta']) ? $_POST['role'] : 'peserta';

    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        echo "<script>alert('Isi semua field dengan benar');window.location='../../register.php';</script>";
        exit;
    }

    // Cek apakah email sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email sudah terdaftar');window.location='../../login.php';</script>";
        exit;
    }
    $stmt->close();

    // Masukkan user baru
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins_stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $ins_stmt->bind_param('ssss', $name, $email, $hash, $role);
    $ins_stmt->execute();

    echo "<script>alert('Registrasi berhasil. Silakan login.');window.location='../../login.php';</script>";
    exit;
}
header('Location:../../register.php');
?>