<?php
require_once '../backend/config.php';
// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$data = null; // Inisialisasi data notulen

// Ambil data notulen yang akan diedit dari database
if ($id > 0) {
    $stmt = $conn->prepare('SELECT id, judul, tanggal, isi, penulis_id FROM notulen WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc(); // Simpan data notulen
        $stmt->close(); // Tutup statement setelah selesai
    } else {
        // Catat error jika query gagal disiapkan
        error_log("Gagal prepare statement get notulen edit: " . $conn->error);
    }
}

// Redirect ke dashboard jika data tidak ditemukan atau ID tidak valid
if (!$data) {
    header('Location: ../dashboard.php');
    exit;
}

// Periksa hak akses: hanya Admin atau penulis asli yang boleh edit
if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['id'] !== $data['penulis_id']) {
    // Tampilkan pesan error jika tidak punya akses
    echo "<script>alert('Tidak punya akses untuk mengedit notulen ini.');window.location='../dashboard.php';</script>";
    exit;
}

// Set variabel untuk header
$page_title = 'Edit Notulen';
include '../inc/header.php'; // Muat header HTML
include '../inc/navbar.php'; // Muat sidebar dan topbar

// Tentukan base_path untuk script JS lokal (karena file ini ada di subfolder 'notulen')
$base_path = '../';
?>

<div class="table-wrapper-card"> <h5 class="mb-4">Edit Notulen</h5>
    
    <form method="post" action="../backend/notulen/save_edit.php">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">

        <div class="mb-3">
            <label for="judul" class="form-label">Judul</label>
            <input type="text" name="judul" id="judul" class="form-control" value="<?= htmlspecialchars($data['judul']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal Rapat</label>
            <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= htmlspecialchars($data['tanggal']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="isi" class="form-label">Isi Notulen</label>
            <textarea name="isi" id="isi" rows="8" class="form-control" required><?= htmlspecialchars($data['isi']) ?></textarea>
        </div>

        <hr>
        
        <div class="mt-3 d-flex gap-2 justify-content-end">
             <a href="detail.php?id=<?= $data['id'] ?>" class="btn btn-sm btn-outline">Kembali</a>
             
             <button type="submit" class="btn btn-sm btn-green"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
        </div>
        </form>
    </div>
<?php
// Menutup tag div dari navbar.php (.content-wrapper, .main, .app)
echo '</div></main></div>';
?>
<script src="<?= $base_path ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?>assets/js/main.js"></script>
</body>
</html>