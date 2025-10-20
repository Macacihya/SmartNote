<?php
require_once '../backend/config.php';
if (!isset($_SESSION['user'])) header('Location: ../login.php');
if ($_SESSION['user']['role'] !== 'admin') { 
    echo "<script>alert('Hanya admin yang bisa menambah notulen');window.location='../dashboard.php';</script>"; 
    exit; 
}

$page_title = 'Tambah Notulen'; 
$page_slug = 'tambah';

include '../inc/header.php';
include '../inc/navbar.php';

// ambil daftar peserta dari tabel users
$result_users = $conn->query("SELECT id, name FROM users WHERE role = 'peserta'");
?>

<div class="container mt-4">
  <h5 class="mb-3">Tambah Notulen</h5>
  <form method="post" action="../backend/notulen/save_new.php" enctype="multipart/form-data">
    <div class="mb-2">
      <label>Judul</label>
      <input name="judul" class="form-control" required>
    </div>

    <div class="mb-2">
      <label>Tanggal</label>
      <input name="tanggal" type="date" class="form-control" required>
    </div>

    <div class="mb-2">
      <label>Isi</label>
      <textarea name="isi" rows="6" class="form-control" required></textarea>
    </div>

    <div class="mb-2">
      <label>Upload file (opsional)</label>
      <input type="file" name="file" class="form-control">
    </div>

    <div class="mb-3">
      <label>Peserta Notulen</label>
      <div class="border rounded p-2" style="max-height:200px; overflow-y:auto;">
        <?php while ($user = $result_users->fetch_assoc()): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="user_ids[]" value="<?= $user['id'] ?>" id="user_<?= $user['id'] ?>">
            <label class="form-check-label" for="user_<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></label>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-green">Simpan</button>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
