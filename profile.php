<?php
require_once 'backend/config.php';
// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$page_title='Profile';
$page_slug='profile'; // Untuk styling body jika diperlukan
include 'inc/header.php'; // Muat header HTML
include 'inc/navbar.php'; // Muat sidebar dan topbar

// Tentukan base_path untuk script JS lokal
$base_path = './';
?>

<div class="table-wrapper-card"> <h5 class="mb-4"><i class="bi bi-person-badge me-2"></i>Profil Pengguna</h5>
    <hr> <div class="row">
        <div class="col-md-3 fw-semibold">Nama:</div>
        <div class="col-md-9"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
    </div>
    <hr class="my-2"> <div class="row">
        <div class="col-md-3 fw-semibold">Email:</div>
        <div class="col-md-9"><?= htmlspecialchars($_SESSION['user']['email']) ?></div>
    </div>
    <hr class="my-2"> <div class="row">
        <div class="col-md-3 fw-semibold">Role:</div>
        <div class="col-md-9">
            <span class="badge <?= ($_SESSION['user']['role'] == 'admin') ? 'bg-success' : 'bg-secondary' ?>">
                <?= ucfirst(htmlspecialchars($_SESSION['user']['role'])) // ucfirst() membuat huruf pertama kapital ?>
            </span>
        </div>
    </div>

    <hr>
    <div class="mt-3 text-end d-flex justify-content-end gap-2">
        <a href="edit_profile.php" class="btn btn-sm btn-green"><i class="bi bi-pencil me-1"></i> Edit Profil</a>
    
    </div>
    </div>
<?php
// Menutup tag div dari navbar.php (.content-wrapper, .main, .app)
echo '</div></main></div>';
?>
<script src="<?= $base_path ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?>assets/js/main.js"></script>
</body>
</html>