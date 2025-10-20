<?php
require_once 'backend/config.php';
if (isset($_SESSION['user']))
  header('Location: dashboard.php');
$page_title = 'Login';
$page_slug = 'login'; // <-- Penting untuk CSS
include 'inc/header.php';
?>
<div class="auth-container">
  <div class="auth-sidebar">
    <h1>Akses <span class="text-green">notulen</span>, dokumen, dan fitur lainnya dengan mudah.</h1>
    <p class="lead">Silakan masuk ke akun Anda untuk melanjutkan.</p>
  </div>
  <div class="auth-main">
    <div class="auth-card">
      <h3>Login</h3>
      <?php if (isset($_GET['message']) && $_GET['message'] === 'account_deleted'): ?>
        <div class="alert alert-success alert-dismissble fade show small" role="alert">
          Akun Anda telah berhasil dihapus.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
            style="padding: 0.5rem 0.75rem;"></button>
        </div>
      <?php endif; ?>
      <form action="backend/auth/login_process.php" method="post">
        <form action="backend/auth/login_process.php" method="post">
          <div class="form-group mb-3">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="Email Anda" required>
          </div>
          <div class="form-group mb-3">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
          </div>
          <div class="auth-footer">
            <p>Belum punya akun? <a href="register.php">Daftar</a></p>
            <button class="btn btn-green" type="submit">Login</button>
          </div>
        </form>
    </div>
  </div>
</div>
</body>

</html>