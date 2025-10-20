<?php
require_once '../backend/config.php';

// --- LOGIKA BACA NOTIFIKASI ---
if (isset($_GET['notif_id'])) {
    $notif_id = (int)$_GET['notif_id'];
    if (isset($_SESSION['user']['id'])) {
        $current_user_id = $_SESSION['user']['id'];
        $stmt_mark_read = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        if ($stmt_mark_read) {
            $stmt_mark_read->bind_param('ii', $notif_id, $current_user_id);
            $stmt_mark_read->execute();
            $stmt_mark_read->close();
        } else {
            error_log("Gagal prepare statement mark read notif: " . $conn->error);
        }
    }
}
// --- AKHIR LOGIKA BACA NOTIFIKASI ---

if (!isset($_SESSION['user'])) header('Location: ../login.php');

$id = (int)($_GET['id'] ?? 0);
$r = null; // Inisialisasi
if ($id > 0) {
    $stmt = $conn->prepare('SELECT n.*, u.name as penulis FROM notulen n JOIN users u ON n.penulis_id = u.id WHERE n.id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $r = $result->fetch_assoc();
        $stmt->close();
    } else {
        error_log("Gagal prepare statement get notulen detail: " . $conn->error);
    }
}

// Redirect jika notulen tidak ditemukan atau ID tidak valid
if (!$r) {
    header('Location: ../dashboard.php');
    exit;
}

$page_title = 'Detail Notulen';
include '../inc/header.php';
include '../inc/navbar.php';

// Tentukan base_path untuk script JS lokal
$base_path = '../';
?>

<div class="table-wrapper-card">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h3><?= htmlspecialchars($r['judul']) ?></h3>
      <small class="text-muted">Oleh: <?= htmlspecialchars($r['penulis']) ?></small>
    </div>
    <div class="fw-semibold text-end">
      Tanggal Rapat:<br><?= htmlspecialchars(date('d/m/Y', strtotime($r['tanggal']))) ?>
    </div>
  </div>

  <hr>

  <div class="notulen-content mb-4">
    <p><?= nl2br(htmlspecialchars($r['isi'])) ?></p>
  </div>

  <!-- === DAFTAR PESERTA RAPAT === -->
  <div class="mb-4">
  <h5 class="mb-3">Peserta Rapat:</h5>
  <?php
  $stmt_peserta = $conn->prepare("
      SELECT u.name 
      FROM notulen_users nu 
      JOIN users u ON nu.user_id = u.id 
      WHERE nu.notulen_id = ?
  ");
  if ($stmt_peserta) {
      $stmt_peserta->bind_param('i', $r['id']);
      $stmt_peserta->execute();
      $result_peserta = $stmt_peserta->get_result();
      
      if ($result_peserta->num_rows > 0) {
          echo "<div class='d-flex flex-wrap gap-2'>";
          while ($p = $result_peserta->fetch_assoc()) {
              echo "
                <span class='badge bg-success-subtle text-success border border-success rounded-pill px-3 py-2'>
                  <i class='bi bi-person-fill me-1'></i>" . htmlspecialchars($p['name']) . "
                </span>
              ";
          }
          echo "</div>";
      } else {
          echo "<p class='text-muted fst-italic'>Tidak ada peserta yang tercatat.</p>";
      }
      $stmt_peserta->close();
  } else {
      echo "<p class='text-danger'>Gagal mengambil data peserta: " . htmlspecialchars($conn->error) . "</p>";
  }
  ?>
</div>


  <!-- === LAMPIRAN FILE === -->
  <?php if ($r['file_path']): ?>
    <div class="mb-4">
      <h6 class="mb-2">Lampiran:</h6>
      <a class="btn aksi-btn btn-download" href="../backend/notulen/download.php?id=<?= $r['id'] ?>" title="Download Lampiran">
        <i class="bi bi-download"></i>
      </a>
    </div>
  <?php endif; ?>

  <hr>

  <div class="mt-3 d-flex gap-2 justify-content-end align-items-center">
    <?php if (isset($_SESSION['user']['role']) && ($_SESSION['user']['role'] === 'admin' || (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] === $r['penulis_id']))): ?>
      <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-green"><i class="bi bi-pencil me-1"></i> Edit</a>
      <a href="../backend/notulen/delete.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Yakin ingin menghapus notulen ini?"><i class="bi bi-trash me-1"></i> Hapus</a>
    <?php endif; ?>
    <a href="../dashboard.php" class="btn btn-sm btn-outline">Kembali</a>
  </div>
</div>

<?php echo '</div></main></div>'; ?>
<script src="<?= $base_path ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?>assets/js/main.js"></script>
</body>
</html>
