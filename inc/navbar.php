<?php
$base = (strpos($_SERVER['PHP_SELF'],'/notulen/') !== false) ? '../' : './';
$active_page = basename($_SERVER['PHP_SELF'], ".php");

// --- LOGIKA MENGAMBIL NOTIFIKASI ---
// Pastikan $conn tersedia; jika tidak, tambahkan require_once '../backend/config.php'; di atas
if (!isset($conn)) {
    // Sesuaikan path jika file config.php tidak selalu di '../backend/' dari semua lokasi
    // Ini asumsi kasar, mungkin perlu disesuaikan tergantung struktur pemanggilan
    $configPath = __DIR__ . '/../backend/config.php'; 
    if(file_exists($configPath)){
      require_once $configPath;
    } else {
      // Fallback atau error handling jika config tidak ditemukan
      // Untuk sementara, kita set notif_count ke 0 jika koneksi gagal
       $notif_count = 0;
       $notif_list = null; // atau objek kosong/array kosong
       // Sebaiknya berikan pesan error atau log
       // error_log("Config DB tidak ditemukan di navbar.php"); 
    }
}

// Hanya jalankan query jika $conn ada
$notif_count = 0;
$notif_list = null; 
if (isset($conn) && isset($_SESSION['user']['id'])) {
    $current_user_id = $_SESSION['user']['id'];

    // 1. Hitung notifikasi yang belum dibaca
    $stmt_count = $conn->prepare("SELECT COUNT(id) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    if ($stmt_count) {
        $stmt_count->bind_param('i', $current_user_id);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $count_data = $result_count->fetch_assoc();
        $notif_count = $count_data ? $count_data['unread_count'] : 0;
        $stmt_count->close();
    } else {
        // Handle error prepare statement
        error_log("Gagal prepare statement count notif: " . $conn->error);
    }


    // 2. Ambil 5 notifikasi terbaru
    $stmt_list = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
     if ($stmt_list) {
        $stmt_list->bind_param('i', $current_user_id);
        $stmt_list->execute();
        $notif_list = $stmt_list->get_result();
        // $stmt_list->close(); // Jangan ditutup di sini jika masih mau dipakai di loop bawah
    } else {
         error_log("Gagal prepare statement list notif: " . $conn->error);
         $notif_list = null; // Set ke null jika query gagal
    }
}
// --- AKHIR LOGIKA NOTIFIKASI ---
?>
<div class="app">

  <div class="custom-modal-overlay" id="custom-confirm-overlay"></div>
  <div class="custom-modal" id="custom-confirm-modal">
    <h5 class="custom-modal-title" id="confirm-modal-title">Yakin mau keluar?</h5>
    <div class="custom-modal-buttons">
      <button class="btn-modal-no" id="confirm-btn-no">Tidak</button>
      <button class="btn-modal-yes" id="confirm-btn-yes">Ya</button>
    </div>
  </div>
  <div class="sidebar-overlay" id="sidebar-overlay"></div>
  
  <aside class="sidebar" id="sidebar">
    <div>
      <div class="d-flex justify-content-between align-items-center d-lg-none mb-3">
        <h5 class="ms-3 mb-0" style="color: var(--green);">Menu</h5>
        <button class="btn-close me-2" id="sidebar-close-btn"></button>
      </div>
      <h5 class="ms-3 mb-3 d-none d-lg-block">Menu</h5>
      <nav class="nav-links">
        <a href="<?= $base ?>dashboard.php" class="nav-link <?= ($active_page == 'dashboard') ? 'active' : ''; ?>"> 
          <i class="bi bi-grid me-2"></i> Dashboard
        </a>
        <?php if(isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a href="<?= $base ?>notulen/tambah.php" class="nav-link <?= ($active_page == 'tambah') ? 'active' : ''; ?>">
          <i class="bi bi-file-earmark-plus me-2"></i> Notulen
        </a>
        <?php endif; ?>

          <?php // Tampilkan link Kelola Pengguna hanya untuk Admin
        if(isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a href="<?= $base ?>manage_users.php" class="nav-link <?= ($active_page == 'manage_users') ? 'active' : ''; ?>">
          <i class="bi bi-people me-2"></i> Kelola Pengguna
        </a>
        <?php endif; ?>

        <?php // Tampilkan link Tambah Pengguna hanya untuk Admin
        if(isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a href="<?= $base ?>add_user.php" class="nav-link <?= ($active_page == 'add_user') ? 'active' : ''; ?>">
          <i class="bi bi-person-plus me-2"></i> Tambah Pengguna
        </a>
        <?php endif; ?>

        <a href="<?= $base ?>profile.php" class="nav-link <?= ($active_page == 'profile') ? 'active' : ''; ?>">
          <i class="bi bi-person me-2"></i> Profile
        </a>
      </nav>
    </div>
    <div>
      <form action="<?= $base ?>backend/auth/logout.php" method="post" id="logout-form">
        <button class="logout-btn w-100" type="button" 
                data-confirm="Yakin mau keluar?" 
                data-form-target="#logout-form">
          <i class="bi bi-box-arrow-left me-2"></i>Logout
        </button>
      </form>
    </div>
  </aside>
  
  <main class="main flex-grow-1">
    <div class="topbar">
      <button class="btn mobile-toggle-btn d-lg-none" id="sidebar-toggle-btn">
        <i class="bi bi-list"></i>
      </button>

      <div class="search-wrapper position-relative">
        <i class="bi bi-search position-absolute"></i>
        <input class="form-control ps-5" placeholder="Search..." id="top-search-input">
        
        <div class="search-suggestions-container" id="search-suggestions">
          {/* */}
        </div>
        </div>

      <?php if(isset($_SESSION['user']['name'])): ?>
      <h5 class="mb-0 me-3 d-none d-lg-block">
        Halo, <?= htmlspecialchars($_SESSION['user']['name']) ?>
      </h5>
      <?php endif; ?>

      <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
          <a href="#" class="position-relative" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell fs-4"></i>
            <?php if ($notif_count > 0): ?>
              <span class="badge rounded-pill bg-success position-absolute top-0 start-100 translate-middle" style="font-size:10px">
                <?= $notif_count ?>
              </span>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
            <li class="dropdown-header">Notifikasi</li>
            <?php if ($notif_list && $notif_list->num_rows > 0): ?>
              <?php while ($notif = $notif_list->fetch_assoc()): ?>
                <li>
                  <a class="dropdown-item <?= $notif['is_read'] ? 'read' : 'unread' ?>" 
                      href="<?= $base ?>notulen/detail.php?id=<?= $notif['notulen_id'] ?>&notif_id=<?= $notif['id'] ?>">
                    <small><?= htmlspecialchars($notif['message']) ?></small>
                  </a>
                </li>
              <?php endwhile; ?>
               <?php $stmt_list->close(); // Tutup statement list setelah loop ?>
            <?php else: ?>
              <li><span class="dropdown-item text-muted text-center"><small>Tidak ada notifikasi</small></span></li>
            <?php endif; ?>
          </ul>
        </div>
    <?php 
      // Cek apakah path foto ada di session dan file nya ada di server
      $profilePic = (isset($_SESSION['user']['profile_picture_path']) && file_exists($base . $_SESSION['user']['profile_picture_path'])) 
                    ? $base . htmlspecialchars($_SESSION['user']['profile_picture_path']) 
                    : 'foto/people.png'; // Fallback ke placeholder
    ?>
    <img src="<?= $profilePic ?>" 
        width="44" height="44" 
        class="rounded-circle" 
        alt="Foto Profil" 
        style="object-fit: cover;">
      </div>
    </div>
    
    <div class="content-wrapper">