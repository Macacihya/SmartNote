<?php
require_once 'backend/config.php';
// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Edit Profil';
$page_slug = 'edit_profile'; // Slug untuk body
include 'inc/header.php';
include 'inc/navbar.php';

// Ambil data user saat ini DARI DATABASE
$stmt_getUser = $conn->prepare("SELECT id, name, email, role, profile_picture_path FROM users WHERE id = ?");
if ($stmt_getUser) {
    $stmt_getUser->bind_param('i', $_SESSION['user']['id']);
    $stmt_getUser->execute();
    $result_getUser = $stmt_getUser->get_result();
    $currentUser = $result_getUser->fetch_assoc();
    $stmt_getUser->close();
} else {
    error_log("Gagal prepare statement get current user: " . $conn->error);
    $currentUser = $_SESSION['user']; // Fallback ke sesi jika DB error
}

// Tentukan path gambar profil yang akan ditampilkan di form
$profilePictureToDisplay = '';
if (isset($currentUser['profile_picture_path']) && !empty($currentUser['profile_picture_path'])) {
    // Pastikan file gambar ada di lokasi yang benar
    $fullPath = './' . $currentUser['profile_picture_path']; // Asumsi base path './'
    if (file_exists($fullPath)) {
        $profilePictureToDisplay = htmlspecialchars($fullPath);
    }
}
// Jika tidak ada path atau file tidak ditemukan, gunakan placeholder
if (empty($profilePictureToDisplay)) {
    $profilePictureToDisplay = 'foto/people.png'; // Placeholder default
}

// Tentukan base_path untuk script JS lokal
$base_path = './';
?>

<div class="table-wrapper-card">
    <h5 class="mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Profil Pengguna</h5>
    <hr>

    <form method="post" action="backend/auth/save_profile.php" enctype="multipart/form-data">

        <div class="mb-4 text-center">
            <label for="profile_picture" class="form-label">Foto Profil</label><br>
            <img src="<?= $profilePictureToDisplay ?>" alt="Foto Profil" class="rounded-circle mb-2"
                style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #eee;">
            <input type="file" name="profile_picture" id="profile_picture" class="form-control form-control-sm"
                style="max-width: 300px; margin: auto;">
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah foto. Format: JPG, PNG, GIF. Maks:
                2MB.</small>
        </div>
        <hr>

        <div class="mb-3 row">
            <label for="name" class="col-sm-3 col-form-label fw-semibold">Nama</label>
            <div class="col-sm-9">
                <input type="text" name="name" id="name" class="form-control"
                    value="<?= htmlspecialchars($currentUser['name']) ?>" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="email" class="col-sm-3 col-form-label fw-semibold">Email</label>
            <div class="col-sm-9">
                <input type="email" id="email" class="form-control"
                    value="<?= htmlspecialchars($currentUser['email']) ?>" disabled readonly>
                <small class="form-text text-muted">Email tidak dapat diubah.</small>
            </div>
        </div>

        <hr>
        <h6 class="mb-3 text-muted">Ubah Password (Opsional)</h6>

        <div class="mb-3 row">
            <label for="current_password" class="col-sm-3 col-form-label fw-semibold">Password Saat Ini</label>
            <div class="col-sm-9">
                <input type="password" name="current_password" id="current_password" class="form-control"
                    placeholder="Masukkan password saat ini jika ingin ganti">
            </div>
        </div>

        <div class="mb-3 row">
            <label for="new_password" class="col-sm-3 col-form-label fw-semibold">Password Baru</label>
            <div class="col-sm-9">
                <input type="password" name="new_password" id="new_password" class="form-control"
                    placeholder="Kosongkan jika tidak ganti password">
            </div>
        </div>

        <div class="mb-3 row">
            <label for="confirm_password" class="col-sm-3 col-form-label fw-semibold">Konfirmasi Password Baru</label>
            <div class="col-sm-9">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                    placeholder="Ulangi password baru">
            </div>
        </div>

        <hr>
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="profile.php" class="btn btn-sm btn-outline">Batal</a>
            <button type="submit" class="btn btn-sm btn-green"><i class="bi bi-save me-1"></i> Simpan Perubahan
                Profil</button>
        </div>
    </form>
</div>
<?php
// Menutup tag dari navbar.php
echo '</div></main></div>';
?>
<script src="<?= $base_path ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?>assets/js/main.js"></script>
</body>

</html>