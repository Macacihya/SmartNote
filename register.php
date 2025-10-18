<?php
require_once 'backend/config.php';
if (isset($_SESSION['user'])) header('Location: dashboard.php');
$page_title='Register';
$page_slug='register'; // <-- Penting untuk CSS
include 'inc/header.php';
?>
<div class="auth-container">
  <div class="auth-sidebar">
    <h1>Buat akun <span class="text-green">SmartNote</span> baru Anda.</h1>
    <p class="lead">Isi formulir untuk mendaftar. Pilih 'Admin' jika Anda seorang notulis.</p>
  </div>
  <div class="auth-main">
    <div class="auth-card">
      <h3>Register</h3>
      <form action="backend/auth/register_process.php" method="post">
        <div class="form-group mb-3">
          <label for="name">Nama</label>
          <input name="name" id="name" class="form-control" placeholder="Nama Lengkap" required>
        </div>
        <div class="form-group mb-3">
          <label for="email">Email</label>
          <input name="email" id="email" type="email" class="form-control" placeholder="Email Anda" required>
        </div>
        <div class="form-group mb-3">
          <label for="password">Password</label>
          <input name="password" id="password" type="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="form-group mb-3">
          <label for="role">Role</label>
          <select name="role" id="role" class="form-select">
            <option value="peserta" selected>Peserta</option>
            <option value="admin">Admin (Notulis)</option>
          </select>
        </div>
        <div class="auth-footer">
          <p>Sudah punya akun? <a href="login.php">Login</a></p>
          <button class="btn btn-green" type="submit">Daftar</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>