<?php
require_once 'backend/config.php';
// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$page_title = 'Dashboard';
$page_slug = 'dashboard';
include 'inc/header.php';
// Include navbar (ini akan memuat variabel $conn jika belum ada)
include 'inc/navbar.php';

// --- LOGIKA PAGINATION & LIMIT ---
// 1. Ambil nilai limit dari URL, default ke 6
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 6;
if (!in_array($limit, [6, 10, 20])) {
    $limit = 1;
}

// 2. Ambil halaman saat ini dari URL, default ke 1
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}

// 3. Hitung total data notulen
$total_result = $conn->query("SELECT COUNT(id) as total FROM notulen");
$total_data = ($total_result && $total_result->num_rows > 0) ? $total_result->fetch_assoc()['total'] : 0;
if($total_result) $total_result->free();

// 4. Hitung total halaman
$totalPages = ($limit > 0) ? ceil($total_data / $limit) : 0; // Hindari division by zero
// Pastikan halaman saat ini tidak melebihi total halaman (jika ada halaman)
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
}

// 5. Hitung OFFSET untuk query SQL
$offset = ($currentPage - 1) * $limit;
// --- AKHIR LOGIKA PAGINATION & LIMIT ---

// Ambil 3 notulen terbaru untuk bagian highlight
$stmt_highlight = $conn->query("SELECT id, judul, isi, tanggal FROM notulen ORDER BY tanggal DESC LIMIT 3");
?>

<div class="row g-3 highlight-row">
  <?php
    // Pastikan query highlight berhasil dan ada datanya
    if ($stmt_highlight && $stmt_highlight->num_rows > 0):
        while ($r = $stmt_highlight->fetch_assoc()):
            ?>
  <div class="col-md-4">
    <div class="highlight-card h-100">
      <div class="d-flex justify-content-between">
        <div>
          <span class="text-muted"><?= htmlspecialchars(date('d/m/Y', strtotime($r['tanggal']))) ?></span>
          <h6 class="mb-1 mt-1"><?= htmlspecialchars($r['judul']) ?></h6>
          <p class="mb-0"><?= htmlspecialchars(substr($r['isi'], 0, 80)) ?>...</p>
        </div>
        <a href="notulen/detail.php?id=<?= $r['id'] ?>" class="btn-view-highlight"><i class="bi bi-search"></i></a>
      </div>
    </div>
  </div>
  <?php
        endwhile; // <-- TAMBAHKAN TITIK KOMA DI SINI
    // Bebaskan memori hasil query highlight
    if(is_object($stmt_highlight)) $stmt_highlight->free();
    endif; // Akhir dari if ($stmt_highlight ...)
    ?>
</div>
<div class="table-wrapper-card mt-4">
  <div class="table-toolbar">
    <div class="d-flex align-items-center gap-2">
      <select class="form-select form-select-sm" style="width: 70px;" id="limit-select">
        <option value="6" <?= ($limit == 1) ? 'selected' : '' ?>>1</option>
        <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
        <option value="20" <?= ($limit == 20) ? 'selected' : '' ?>>20</option>
      </select>
    </div>
    <div class="search-table-wrapper position-relative" style="width: 250px;">
      <i class="bi bi-search position-absolute"></i>
      <input class="form-control form-control-sm ps-4" placeholder="Search in table..." id="table-search-input">
    </div>
  </div>

  <div class="table-responsive">
    <table class="table align-middle" id="notulen-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Judul rapat</th>
          <th>Tanggal rapat</th>
          <th>Pembuat Notulen</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="notulen-table-body">
      <?php
        // Query data notulen utama dengan LIMIT dan OFFSET
        $sql_table = "SELECT n.id, n.judul, n.isi, n.tanggal, n.file_path, n.penulis_id, u.name as penulis
                      FROM notulen n
                      JOIN users u ON n.penulis_id = u.id
                      ORDER BY n.id DESC
                      LIMIT ? OFFSET ?";
        $stmt_table = $conn->prepare($sql_table);
        $result_table = null; // Inisialisasi
        if ($stmt_table) {
            $stmt_table->bind_param('ii', $limit, $offset);
            $stmt_table->execute();
            $result_table = $stmt_table->get_result();
            $stmt_table->close(); // Tutup statement setelah get_result
        } else {
            error_log("Gagal prepare statement table data: " . $conn->error);
        }

        // Hitung nomor awal untuk halaman ini
        $start_number = $offset + 1;
        // Periksa apakah query berhasil dan ada datanya
        if ($result_table && $result_table->num_rows > 0):
            while ($n = $result_table->fetch_assoc()):
                // Siapkan Teks untuk Data Search
                $searchable_title = strtolower(htmlspecialchars($n['judul']));
                $searchable_date = strtolower(htmlspecialchars(date('d/m/Y', strtotime($n['tanggal']))));
                $searchable_author = strtolower(htmlspecialchars($n['penulis']));
                $searchable_content = strtolower(htmlspecialchars(substr(strip_tags($n['isi']), 0, 100)));
                $searchable_text = trim(preg_replace('/\s+/', ' ', $searchable_title . ' ' . $searchable_date . ' ' . $searchable_author . ' ' . $searchable_content));
                ?>
        <tr data-search="<?= $searchable_text ?>">
          <td><?= $start_number++ ?></td> <td><?= htmlspecialchars($n['judul']) ?></td>
          <td><?= htmlspecialchars(date('d/m/Y', strtotime($n['tanggal']))) ?></td>
          <td><?= htmlspecialchars($n['penulis']) ?></td>
          <td>
            <a class="btn btn-sm aksi-btn btn-view" href="notulen/detail.php?id=<?= $n['id'] ?>"><i class="bi bi-eye"></i></a>
            <?php
            // Tombol Aksi: Edit & Hapus (Hanya Admin)
            if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
              <a class="btn btn-sm aksi-btn btn-edit" href="notulen/edit.php?id=<?= $n['id'] ?>"><i class="bi bi-pencil"></i></a>
              <a class="btn btn-sm aksi-btn btn-delete" href="backend/notulen/delete.php?id=<?= $n['id'] ?>" data-confirm="Yakin ingin menghapus notulen ini?"><i class="bi bi-trash"></i></a>
            <?php
            // Tombol Aksi: Download (Peserta, jika ada file)
            elseif ($n['file_path']): ?>
              <a class="btn btn-sm aksi-btn btn-download" href="backend/notulen/download.php?id=<?= $n['id'] ?>"><i class="bi bi-download"></i></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php
            endwhile;
        else:
            // Tampilkan pesan jika tidak ada data
            ?>
        <tr id="no-data-row"><td colspan="5" class="text-center text-muted">Belum ada data notulen.</td></tr>
      <?php
        endif; // Akhir dari if ($result_table ...)
        // Bebaskan memori result set
        if(isset($result_table) && is_object($result_table)) $result_table->free();
        ?>
      </tbody>
    </table>
  </div>
  <div class="pagination-footer">
    <span class="text-muted">Menampilkan <?= ($result_table && method_exists($result_table, 'num_rows')) ? $result_table->num_rows : 0 ?> dari <?= $total_data ?> data</span>

    <nav>
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $currentPage - 1 ?>&limit=<?= $limit ?>">&laquo;</a>
        </li>

        <?php for ($page = 1; $page <= $totalPages; $page++): ?>
          <li class="page-item <?= ($page == $currentPage) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $page ?>&limit=<?= $limit ?>"><?= $page ?></a>
          </li>
        <?php endfor; ?>

        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $currentPage + 1 ?>&limit=<?= $limit ?>">&raquo;</a>
        </li>
      </ul>
    </nav>
    </div>
  </div>
<?php
// Menutup tag div dari navbar.php
echo '</div></main></div>';

$base_path = './'; // Base path untuk dashboard
?>
<script src="<?= $base_path ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?>assets/js/main.js"></script>

<script>
  // Event listener untuk dropdown limit
  document.getElementById('limit-select').addEventListener('change', function() {
    const selectedLimit = this.value; // Ambil nilai yang dipilih
    // Arahkan ulang halaman ke URL yang sama dengan parameter limit baru, reset ke page 1
    window.location.href = 'dashboard.php?limit=' + selectedLimit + '&page=1';
  });

  // Event listener untuk search di toolbar tabel
  const tableSearchInput = document.getElementById('table-search-input');
  const tableBody = document.getElementById('notulen-table-body');
  const noDataRowOriginalContent = 'Belum ada data notulen.'; // Simpan teks asli

  if (tableSearchInput && tableBody) {
    tableSearchInput.addEventListener('input', function() {
      const searchTerm = tableSearchInput.value.toLowerCase().trim();
      const tableRows = tableBody.querySelectorAll('tr[data-search]'); // Hanya pilih baris data
      let visibleRowCount = 0;
      const noDataRow = tableBody.querySelector('#no-data-row'); // Cari baris 'no data'

      tableRows.forEach(row => {
        const searchableText = row.dataset.search || '';
        if (searchableText.includes(searchTerm)) {
          row.style.display = ''; // Tampilkan baris
          visibleRowCount++;
        } else {
          row.style.display = 'none'; // Sembunyikan baris
        }
      });

      // Logika untuk menampilkan/menyembunyikan baris 'no data'
      if (noDataRow) { // Jika baris 'no data' memang ada di HTML awal
          if (visibleRowCount === 0 && tableRows.length > 0) { // Tampilkan jika filter tidak menemukan hasil
              noDataRow.style.display = ''; // Tampilkan baris 'no data'
              const headerCols = document.querySelectorAll('#notulen-table thead th').length;
              noDataRow.querySelector('td').setAttribute('colspan', headerCols);
              noDataRow.querySelector('td').textContent = 'Data tidak ditemukan untuk "' + tableSearchInput.value + '".'; // Pesan saat search
          } else if (tableRows.length === 0) { // Jika memang tidak ada data dari awal
               noDataRow.style.display = '';
               noDataRow.querySelector('td').textContent = noDataRowOriginalContent; // Kembalikan teks asli
               const headerCols = document.querySelectorAll('#notulen-table thead th').length;
               noDataRow.querySelector('td').setAttribute('colspan', headerCols);
          }
          else { // Sembunyikan jika ada hasil filter
              noDataRow.style.display = 'none';
          }
      } else if (visibleRowCount === 0 && tableRows.length > 0) {
          // Kasus jika baris 'no data' tidak ada tapi perlu ditampilkan (karena filter)
          console.log("Baris 'no data' tidak ditemukan di HTML.");
      }

    });
  }
</script>

</body>
</html>