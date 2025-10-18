<?php
// backend/config.php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'notulen_db';
$DB_USER = 'root';
$DB_PASS = '';

// Membuat koneksi menggunakan MySQLi
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die('Koneksi DB gagal: ' . $conn->connect_error);
}

// Atur charset
$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>