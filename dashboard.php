<?php
require_once 'backend/config.php';
if (!isset($_SESSION['user'])) header('Location: login.php');
$page_title='Dashboard'; $page_slug='dashboard';
include 'inc/header.php';
include 'inc/navbar.php';

// --- LOGIKA LIMIT BARU ---
// 1. Ambil nilai limit dari URL, default ke 6 jika tidak ada
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
// Pastikan limit adalah salah satu dari nilai yang valid
if (!in_array($limit, [6, 10, 20])) {
    $limit = 6;
}
// --- AKHIR LOGIKA LIMIT ---

// Ambil 3 notulen terbaru untuk highlight (Query ini tetap sama)
$stmt_highlight = $conn->query("SELECT * FROM notulen ORDER BY tanggal DESC LIMIT 3");
?>

<div class="row g-3 highlight-row">
  <?php while ($r = $stmt_highlight->fetch_assoc()): ?>
  <div class="col-md-4">
    <div class="highlight-card">
      <div class="d-flex justify-content-between">
        <div>
          <span class="text-muted"><?= htmlspecialchars(date('d/m/Y', strtotime($r['tanggal']))) ?></span>
          <h6 class="mb-1 mt-1"><?= htmlspecialchars($r['judul']) ?></h6>
          <p class="mb-0"><?= htmlspecialchars(substr($r['isi'],0,80)) ?>...</p>
        </div>
        <a href="notulen/detail.php?id=<?= $r['id'] ?>" class="btn-view-highlight"><i class="bi bi-search"></i></a>
      </div>
    </div>
  </div>
  <?php endwhile; ?>
</div>
<div class="table-wrapper-card mt-4">
  <div class="table-toolbar">
    <div class="d-flex align-items-center gap-2">
      <select class="form-select form-select-sm" style="width: 70px;" id="limit-select">
        <option value="6" <?= ($limit == 6) ? 'selected' : '' ?>>6</option>
        <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
        <option value="20" <?= ($limit == 20) ? 'selected' : '' ?>>20</option>
      </select>
      </div>
    <div class="search-table-wrapper position-relative" style="width: 250px;">
      <i class="bi bi-search position-absolute"></i>
      <input class="form-control form-control-sm ps-4" placeholder="Search...">
    </div>
  </div>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>No</th>
          <th>Judul rapat</th>
          <th>Tanggal rapat</th>
          <th>Pembuat Notulen</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php
      // --- PERBARUI QUERY TABEL DENGAN LIMIT ---
      // 1. Hitung total data untuk info pagination
      $total_result = $conn->query("SELECT COUNT(id) as total FROM notulen");
      $total_data = $total_result->fetch_assoc()['total'];

      // 2. Query data dengan LIMIT
      $stmt_table = $conn->prepare("SELECT n.*, u.name as penulis FROM notulen n JOIN users u ON n.penulis_id = u.id ORDER BY n.tanggal DESC LIMIT ?");
      $stmt_table->bind_param('i', $limit);
      $stmt_table->execute();
      $result_table = $stmt_table->get_result();
      // --- AKHIR PERBARUAN QUERY ---

      $i=1;
      if ($result_table->num_rows > 0):
          while($n = $result_table->fetch_assoc()):
      ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($n['judul']) ?></td>
          <td><?= htmlspecialchars(date('d/m/Y', strtotime($n['tanggal']))) ?></td>
          <td><?= htmlspecialchars($n['penulis']) ?></td>
          <td>
            <a class="btn btn-sm aksi-btn btn-view" href="notulen/detail.php?id=<?= $n['id'] ?>"><i class="bi bi-eye"></i></a>
            <?php if(isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
              <a class="btn btn-sm aksi-btn btn-edit" href="notulen/edit.php?id=<?= $n['id'] ?>"><i class="bi bi-pencil"></i></a>
              <a class="btn btn-sm aksi-btn btn-delete" href="backend/notulen/delete.php?id=<?= $n['id'] ?>" data-confirm="Yakin ingin menghapus notulen ini?"><i class="bi bi-trash"></i></a>
            <?php elseif ($n['file_path']): ?>
              <a class="btn btn-sm aksi-btn btn-download" href="backend/notulen/download.php?id=<?= $n['id'] ?>"><i class="bi bi-download"></i></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php
          endwhile;
      else: ?>
        <tr><td colspan="5" class="text-center text-muted">Belum ada data notulen.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="pagination-footer">
    <span class="text-muted">Menampilkan <?= $result_table->num_rows ?> dari <?= $total_data ?> data</span>
    <nav>
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
      </ul>
    </nav>
  </div>
</div>

<?php
echo '</div></main></div>';
?>
<script src="./assets/js/bootstrap.bundle.min.js"></script>
<script src="./assets/js/main.js"></script>

<script>
document.getElementById('limit-select').addEventListener('change', function() {
  const selectedLimit = this.value;
  // Redirect ke halaman yang sama dengan parameter limit baru
  window.location.href = 'dashboard.php?limit=' + selectedLimit;
});
</script>
</body>
</html>