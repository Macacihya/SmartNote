<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        echo "<script>alert('Isi email dan password dengan benar');window.location='../../login.php';</script>";
        exit;
    }

    $stmt = $conn->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
        header('Location: ../../dashboard.php');
        exit;
    } else {
        echo "<script>alert('Email atau password salah');window.location='../../login.php';</script>";
        exit;
    }
}
header('Location: ../../login.php');
?>