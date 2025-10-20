<?php
require_once 'backend/config.php';
// 1. Pastikan user login dan adalah ADMIN
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php'); // Redirect jika tidak berhak
    exit;
}

$page_title = 'Kelola Pengguna';
$page_slug = 'manage_users';
include 'inc/header.php';
include 'inc/navbar.php';

// Ambil semua data pengguna dari database
$result_users = $conn->query("SELECT id, name, email, role, profile_picture_path FROM users WHERE role = 'peserta' ORDER BY role DESC, name ASC"); // Urutkan admin dulu, lalu nama

// Tentukan base_path untuk script JS lokal
$base_path = './';
?>

<div class="table-wrapper-card">
    <h5 class="mb-4"><i class="bi bi-people-fill me-2"></i>Kelola Pengguna Sistem</h5>
    <hr>

    <?php // Tampilkan pesan sukses/error jika ada dari redirect
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'deleted') {
                echo '<div class="alert alert-success alert-dismissible fade show small" role="alert">Pengguna berhasil dihapus.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.5rem 0.75rem;"></button></div>';
            } elseif ($_GET['status'] === 'error') {
                 echo '<div class="alert alert-danger alert-dismissible fade show small" role="alert">Gagal menghapus pengguna. Silakan coba lagi.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.5rem 0.75rem;"></button></div>';
            } elseif ($_GET['status'] === 'self') {
                 echo '<div class="alert alert-warning alert-dismissible fade show small" role="alert">Anda tidak dapat menghapus akun Anda sendiri.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.5rem 0.75rem;"></button></div>';
            } elseif ($_GET['status'] === 'not_participant') {
                 echo '<div class="alert alert-warning alert-dismissible fade show small" role="alert">Hanya akun peserta yang dapat dihapus dari halaman ini.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.5rem 0.75rem;"></button></div>';
            }
        }
    ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_users && $result_users->num_rows > 0):
                    $no = 1;
                    while ($user = $result_users->fetch_assoc()):
                        // Tentukan path gambar profil
                        $userProfilePic = '';
                        if (!empty($user['profile_picture_path']) && file_exists($base_path . $user['profile_picture_path'])) {
                            $userProfilePic = $base_path . htmlspecialchars($user['profile_picture_path']);
                        } else {
                            $userProfilePic = 'https://i.pravatar.cc/50?u=' . $user['id']; // Placeholder unik
                        }
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <img src="<?= $userProfilePic ?>" alt="Foto <?= htmlspecialchars($user['name']) ?>" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                        </td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                             <span class="badge <?= ($user['role'] == 'admin') ? 'bg-success' : 'bg-secondary' ?>">
                                <?= ucfirst(htmlspecialchars($user['role'])) ?>
                             </span>
                        </td>
                        <td>
                            <?php // Tampilkan tombol Hapus hanya untuk PESERTA dan BUKAN admin yang sedang login
                            if ($user['role'] === 'peserta'):
                            ?>
                                <form action="backend/admin/delete_user_process.php" method="post" id="delete-user-form-<?= $user['id'] ?>" class="d-inline form-delete-user">
                                    <input type="hidden" name="user_id_to_delete" value="<?= $user['id'] ?>">
                                    <button type="button" class="btn btn-sm aksi-btn btn-delete"
                                            data-confirm="Yakin ingin menghapus pengguna '<?= htmlspecialchars($user['name']) ?>' (<?= htmlspecialchars($user['email']) ?>)? Tindakan ini permanen."
                                            data-form-target="#delete-user-form-<?= $user['id'] ?>"
                                            title="Hapus Pengguna">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted"> - </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada pengguna terdaftar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
<?php
// Menutup tag dari navbar.php
echo '</div></main></div>';
?>
<script src="<?= $base_path ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?>assets/js/main.js"></script>

</body>
</html>