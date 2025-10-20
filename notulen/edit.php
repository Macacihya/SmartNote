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
    // --- PERBARUI QUERY: Tambahkan 'file_path' ---
    $stmt = $conn->prepare('SELECT id, judul, tanggal, isi, penulis_id, file_path FROM notulen WHERE id = ?');
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

// --- TAMBAHAN: Ambil semua user 'peserta' ---
$stmt_all_users = $conn->query("SELECT id, name FROM users WHERE role = 'peserta'");

// --- TAMBAHAN: Ambil peserta yang saat ini terhubung dengan notulen ---
$current_peserta_ids = [];
$stmt_current_users = $conn->prepare("SELECT user_id FROM notulen_users WHERE notulen_id = ?");
if ($stmt_current_users) {
    $stmt_current_users->bind_param('i', $id);
    $stmt_current_users->execute();
    $result_current = $stmt_current_users->get_result();
    while ($row_current = $result_current->fetch_assoc()) {
        $current_peserta_ids[] = $row_current['user_id'];
    }
    $stmt_current_users->close();
}
// --- AKHIR TAMBAHAN ---


// Set variabel untuk header
$page_title = 'Edit Notulen';
include '../inc/header.php'; // Muat header HTML
include '../inc/navbar.php'; // Muat sidebar dan topbar

// Tentukan base_path untuk script JS lokal (karena file ini ada di subfolder 'notulen')
$base_path = '../';
?>

<div class="table-wrapper-card"> <h5 class="mb-4">Edit Notulen</h5>
    
    <form method="post" action="../backend/notulen/save_edit.php" enctype="multipart/form-data">
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

        <div class="mb-3">
            <label for="file" class="form-label">Ganti Lampiran (Opsional)</label>
            <input type="file" name="file" id="file" class="form-control">
            
            <?php 
            // Cek apakah file_path ada dan file-nya benar-benar ada di server
            $current_file_path = '../' . ltrim($data['file_path'], '/');
            if (!empty($data['file_path']) && file_exists($current_file_path)): 
            ?>
                <small class="form-text text-muted mt-1 d-block">
                    File saat ini: 
                    <a href="../backend/notulen/download.php?id=<?= $data['id'] ?>" target="_blank">
                        <i class="bi bi-file-earmark-arrow-down"></i> <?= htmlspecialchars(basename($data['file_path'])) ?>
                    </a>
                </small>
                <small class="form-text text-info d-block">Mengupload file baru akan otomatis menggantikan file lama.</small>
            <?php else: ?>
                <small class="form-text text-muted mt-1 d-block">Belum ada file terlampir.</small>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Peserta Notulen</label>
            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                <?php if ($stmt_all_users && $stmt_all_users->num_rows > 0): ?>
                    <?php while ($user = $stmt_all_users->fetch_assoc()): ?>
                        <?php
                            // Cek apakah user ini ada di daftar peserta notulen saat ini
                            $isChecked = in_array($user['id'], $current_peserta_ids) ? 'checked' : '';
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="user_ids[]" value="<?= $user['id'] ?>" id="user_<?= $user['id'] ?>" <?= $isChecked ?>>
                            <label class="form-check-label" for="user_<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['name']) ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                     <small class="text-muted">Tidak ada pengguna dengan role 'peserta'.</small>
                <?php endif; ?>
            </div>
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

// Bebaskan memori
if (isset($stmt_all_users) && is_object($stmt_all_users)) $stmt_all_users->free();
?>
<script src="<?= $base_path ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?>assets/js/main.js"></script>
</body>
</html>