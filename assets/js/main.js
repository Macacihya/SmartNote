document.addEventListener('DOMContentLoaded', function () {

  // --- LOGIKA SIDEBAR MOBILE ---
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('sidebar-toggle-btn');
  const closeBtn = document.getElementById('sidebar-close-btn');
  const overlay = document.getElementById('sidebar-overlay');

  if (sidebar && toggleBtn && closeBtn && overlay) {
    function openSidebar() {
      sidebar.classList.add('active');
      overlay.classList.add('active');
    }
    function closeSidebar() {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
    }
    toggleBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);
  }
  // --- AKHIR LOGIKA SIDEBAR ---


  // --- LOGIKA MODAL KONFIRMASI KUSTOM BARU ---
  const confirmModal = document.getElementById('custom-confirm-modal');
  const confirmOverlay = document.getElementById('custom-confirm-overlay');
  const confirmTitle = document.getElementById('confirm-modal-title');
  const btnYes = document.getElementById('confirm-btn-yes');
  const btnNo = document.getElementById('confirm-btn-no');

  let actionTarget = null; // Untuk menyimpan link href atau form

  function openConfirmModal(title, target) {
    confirmTitle.textContent = title;
    actionTarget = target;
    confirmModal.classList.add('active');
    confirmOverlay.classList.add('active');
  }

  function closeConfirmModal() {
    actionTarget = null;
    confirmModal.classList.remove('active');
    confirmOverlay.classList.remove('active');
  }

  function performAction() {
    if (actionTarget) {
      if (typeof actionTarget === 'string') {
        // Jika target adalah link (URL)
        window.location.href = actionTarget;
      } else if (typeof actionTarget.submit === 'function') {
        // Jika target adalah form
        actionTarget.submit();
      }
    }
    closeConfirmModal();
  }

  // Tambahkan event listener untuk tombol 'Ya' dan 'Tidak'
  if (confirmModal) {
    btnYes.addEventListener('click', performAction);
    btnNo.addEventListener('click', closeConfirmModal);
    confirmOverlay.addEventListener('click', closeConfirmModal);
  }

  // Cari semua tombol/link yang butuh konfirmasi
  document.querySelectorAll('[data-confirm]').forEach(element => {
    element.addEventListener('click', function (e) {
      e.preventDefault(); // HENTIKAN aksi default (logout/pindah halaman)
      
      const title = e.currentTarget.dataset.confirm;
      let target;

      // Cek apakah ini target form (untuk logout) atau link (untuk hapus)
      const formTargetSelector = e.currentTarget.dataset.formTarget;
      if (formTargetSelector) {
        target = document.querySelector(formTargetSelector); // Targetnya adalah elemen <form>
      } else {
        target = e.currentTarget.href; // Targetnya adalah URL dari <a>
      }
      openConfirmModal(title, target);
    });
  });
  // --- AKHIR LOGIKA MODAL KUSTOM ---
});