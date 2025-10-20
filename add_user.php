<?php
require_once 'backend/config.php';
// 1. Pastikan user login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
// 2. Pastikan user adalah ADMIN
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    // Jika bukan admin, redirect ke dashboard (atau tampilkan pesan akses ditolak)
    echo "<script>alert('Akses ditolak. Fitur ini hanya untuk admin.'); window.location='dashboard.php';</script>";
    exit;
}

$page_title = 'Tambah Pengguna Baru';
$page_slug = 'add_user'; // Untuk styling body & navbar active state
include 'inc/header.php';
include 'inc/navbar.php';

// Tentukan base_path untuk script JS lokal
$base_path = './';
?>

<div class="table-wrapper-card">
    <h5 class="mb-4"><i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna Baru</h5>
    <hr>

    <form method="post" action="backend/admin/add_user_process.php" id="add-user-form"
        onsubmit="return validateAddUserPassword()">

        <div class="mb-3 row">
            <label for="name" class="col-sm-3 col-form-label fw-semibold">Nama Lengkap</label>
            <div class="col-sm-9">
                <input type="text" name="name" id="name" class="form-control" placeholder="Masukkan nama pengguna baru"
                    required>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="email" class="col-sm-3 col-form-label fw-semibold">Email</label>
            <div class="col-sm-9">
                <input type="email" name="email" id="email" class="form-control"
                    placeholder="Masukkan email pengguna baru" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="password" class="col-sm-3 col-form-label fw-semibold">Password</label>
            <div class="col-sm-9">
                <input type="password" name="password" id="password" class="form-control"
                    placeholder="Minimal 6 karakter" required minlength="6" aria-describedby="passwordHelpAddUser">
                <div id="passwordHelpAddUser" class="form-text text-danger" style="display: none;">Password harus
                    minimal 6 karakter.</div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="role" class="col-sm-3 col-form-label fw-semibold">Role</label>
            <div class="col-sm-9">
                <select name="role" id="role" class="form-select" required>
                    <option value="peserta" selected>Peserta</option>
                </select>
                <small class="form-text text-muted">Pilih peran untuk pengguna baru.</small>
            </div>
        </div>

        <hr>
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="dashboard.php" class="btn btn-sm btn-outline">Batal</a>
            <button type="submit" class="btn btn-sm btn-green"><i class="bi bi-person-plus me-1"></i> Tambahkan
                Pengguna</button>
        </div>

    </form>
</div>
<?php
// Menutup tag dari navbar.php
echo '</div></main></div>';
?>
<script src="<?= $base_path ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?>assets/js/main.js"></script>

<script>
    function validateAddUserPassword() {
        const passwordInput = document.getElementById('password');
        const passwordHelp = document.getElementById('passwordHelpAddUser');
        if (passwordInput.value.length < 6) {
            passwordHelp.style.display = 'block';
            passwordInput.focus();
            return false; // Hentikan submit
        } else {
            passwordHelp.style.display = 'none';
            return true; // Lanjutkan submit
        }
    }

    // Sembunyikan error saat user mengetik
    const passwordInputAddUser = document.getElementById('password');
    if (passwordInputAddUser) {
        passwordInputAddUser.addEventListener('input', function () {
            const passwordHelp = document.getElementById('passwordHelpAddUser');
            if (passwordInputAddUser.value.length >= 6) {
                passwordHelp.style.display = 'none';
            }
        });
    }
</script>

</body> 

</html>