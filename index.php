<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartNote - Notulen Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    /* CSS Kustom */
    body {
        /* Font Poppins diterapkan */
        font-family: 'Poppins', sans-serif;
    }

    .hero-bg {
        /* Warna gradient Hero Section (Biru ke Hijau) */
        background: linear-gradient(135deg, #007bff 0%, #00d462 100%);
    }

    /* WARNA TEXT GRADIENT (Biru-Hijau) */
    .text-primary-gradient {
        background-image: linear-gradient(135deg, #007bff 0%, #00d462 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
    }


    /* KOTAK PLACEHOLDER GAMBAR FITUR (NETRAL) */
    .placeholder-img {
        width: 100%;
        height: 180px;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        margin-bottom: 1rem;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        border-radius: 0;
        overflow: hidden; /* Penting untuk gambar di dalamnya */
        cursor: pointer; /* Menandakan elemen bisa diklik */
    }
    
    .placeholder-img img {
        width: 100%;
        height: 100%;
        object-fit: cover; 
        transition: transform 0.3s ease;
    }

    .placeholder-img:hover img {
         transform: scale(1.05); /* Efek zoom halus saat hover */
    }


    /* GAYA TEAM FOTO (Lingkaran) */
    .team-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        margin: 0 auto 1rem auto;
        border: 4px solid #fff;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .team-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    /* GAYA TAMBAHAN: EFEK HOVER KARTU */
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 123, 255, 0.15) !important;
    }


    section {
        padding: 60px 0;
    }

    footer {
        padding: 20px 0;
    }

    html {
        scroll-behavior: smooth;
    }

    /* PERBAIKAN RESPONSIVITAS MOBILE DENGAN MEDIA QUERIES (Fokus perbaikan font di sini) */
    @media (max-width: 768px) {
        /* Untuk layar dengan lebar di bawah 768px (seperti ponsel) */
        
        /* Kurangi padding section agar lebih ringkas */
        section {
            padding: 40px 0;
        }

        /* PERBAIKAN FONT MOBILE: Judul utama di Hero */
        .display-4 {
            font-size: 1.8rem !important; /* Dikecilkan */
        }
        
        /* PERBAIKAN FONT MOBILE: Deskripsi utama di Hero */
        .lead {
            font-size: 1rem !important;
        }

        /* Jarak antar kolom di Team dan Fitur agar lebih rapat */
        .row.g-4 {
            --bs-gutter-y: 2rem; /* Kurangi jarak vertikal */
        }

        /* PERBAIKAN FONT MOBILE: Judul kartu */
        .card-title {
            font-size: 1rem !important; /* Dikecilkan */
        }
        
        /* Khusus untuk kartu tim di mode mobile */
        .team-img {
            width: 120px;
            height: 120px;
        }
        
        /* PERBAIKAN FONT MOBILE: Deskripsi kartu tim/fitur */
        .team-card .card-text, .card-text {
            font-size: 0.8rem !important; /* Dikecilkan */
        }
    }

    /* Perbaikan Anchor Link */
    #hero,
    #fitur-unggulan,
    #hubungi-kami {
        scroll-margin-top: 70px;
    }
    
    #hero {
        min-height: 100vh !important;
    }
</style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#hero">SmartNote</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="#hero">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur-unggulan">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#hubungi-kami">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="hero" class="hero-bg text-white text-center d-flex align-items-center justify-content-center">
        <div class="container py-5">
            <h1 class="display-4 fw-bold">Selamat Datang di SmartNote</h1>
            <p class="lead mt-3">Sistem cerdas untuk merekam, menyusun, dan mendistribusikan notulen rapat tim Anda
                secara otomatis dan terstruktur.</p>
            <a href="#fitur-unggulan" class="btn btn-light btn-lg mt-4">
                Lihat Keunggulan Fitur
                <i class="bi bi-arrow-down ms-2"></i>
            </a>
        </div>
    </header>

    <section id="fitur-unggulan" class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-5 text-primary-gradient">Keunggulan SmartNote</h2>
            <div class="row text-center g-4">
                
                <div class="col-md-4">
                    <div class="card p-3 shadow-sm h-100 border-0">
                        <div class="placeholder-img" data-bs-toggle="modal" data-bs-target="#imageModal" data-image-src="https://via.placeholder.com/1200x800/007bff/FFFFFF?text=SCREENSHOT+Perekaman+Real-time" data-image-title="Perekaman Real-time SmartNote">
                            <img src="https://via.placeholder.com/600x400/007bff/FFFFFF?text=Perekaman" alt="Perekaman Real-time" class="img-fluid">
                        </div>
                        <h5 class="card-title mt-2">Perekaman Real-time</h5>
                        <p class="card-text text-muted">Rekam ucapan dalam rapat dan ubah menjadi teks secara instan,
                            memastikan tidak ada poin yang terlewat.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card p-3 shadow-sm h-100 border-0">
                        <div class="placeholder-img" data-bs-toggle="modal" data-bs-target="#imageModal" data-image-src="https://via.placeholder.com/1200x800/00d462/FFFFFF?text=SCREENSHOT+Struktur+Otomatis" data-image-title="Struktur Otomatis SmartNote">
                            <img src="https://via.placeholder.com/600x400/00d462/FFFFFF?text=Struktur" alt="Struktur Otomatis" class="img-fluid">
                        </div>
                        <h5 class="card-title mt-2">Struktur Otomatis</h5>
                        <p class="card-text text-muted">Secara otomatis mengelompokkan hasil notulen berdasarkan topik,
                            keputusan, dan poin tindakan (Action Items).</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card p-3 shadow-sm h-100 border-0">
                        <div class="placeholder-img" data-bs-toggle="modal" data-bs-target="#imageModal" data-image-src="https://via.placeholder.com/1200x800/ffc107/333333?text=SCREENSHOT+Distribusi+Instan" data-image-title="Distribusi Instan SmartNote">
                            <img src="https://via.placeholder.com/600x400/ffc107/333333?text=Distribusi" alt="Distribusi Instan" class="img-fluid">
                        </div>
                        <h5 class="card-title mt-2">Distribusi Instan</h5>
                        <p class="card-text text-muted">Bagikan hasil notulen rapat ke seluruh peserta atau pemangku
                            kepentingan segera setelah rapat selesai.</p>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5 text-primary-gradient">Anggota Tim PBL SmartNote</h2>
            <div class="row text-center g-4 justify-content-center">

                <div class="col-md-3 col-6">
                    <div class="card p-3 shadow-sm h-100 border-0 team-card">
                        <img src="./foto/Rian.jpg" alt="Foto Rian - Ketua Proyek"
                            class="team-img">
                            <h5 class="card-title mt-2">Rian (Ketua Proyek dan Frontend Developer)</h5>
                            <p class="card-text text-muted">Mengimplementasikan desain visual menjadi kode tampilan yang interaktif.</p>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="card p-3 shadow-sm h-100 border-0 team-card">
                        <img src="./foto/yohana.png" alt="Foto Yohana - Spesialis UI/UX"
                            class="team-img">
                        <h5 class="card-title mt-2">Yohana (Spesialis UI/UX)</h5>
                        <p class="card-text text-muted">Bertanggung jawab atas desain antarmuka dan pengalaman pengguna.</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-6">
                    <div class="card p-3 shadow-sm h-100 border-0 team-card">
                        <img src="./foto/della.png" alt="Foto Della - Frontend Developer"
                        class="team-img">
                        <h5 class="card-title mt-2">Della (Spesialis UI/UX)</h5>
                        <p class="card-text text-muted">Bertanggung jawab atas desain antarmuka dan pengalaman pengguna.</p>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="card p-3 shadow-sm h-100 border-0 team-card">
                        <img src="./foto/didit.jpg" alt="Foto Didit - Backend Developer"
                            class="team-img">
                        <h5 class="card-title mt-2">Didit (Backend Developer)</h5>
                        <p class="card-text text-muted">Mengembangkan logika server, database, dan API untuk fungsionalitas.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section id="hubungi-kami" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Hubungi Kami</h2>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card p-4 shadow-sm border-0">
                        <form>
                            <div class="mb-3">
                                <label for="namaLengkap" class="form-label text-muted">Nama Lengkap</label>
                                <input type="text" class="form-control" id="namaLengkap"
                                    placeholder="Masukkan Nama Anda">
                            </div>
                            <div class="mb-3">
                                <label for="alamatEmail" class="form-label text-muted">Alamat Email</label>
                                <input type="email" class="form-control" id="alamatEmail"
                                    placeholder="Masukkan email Anda">
                            </div>
                            <div class="mb-3">
                                <label for="pesan" class="form-label text-muted">Pesan</label>
                                <textarea class="form-control" id="pesan" rows="4"
                                    placeholder="Tulis pesan Anda di sini..."></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-light text-center py-3">
        <div class="container">
            <p class="m-0 text-muted">&copy; 2025 SmartNote</p>
        </div>
    </footer>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Detail Fitur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" alt="Gambar Fitur SmartNote" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imageModal = document.getElementById('imageModal');
            imageModal.addEventListener('show.bs.modal', function (event) {
                // Tombol/Div yang memicu modal
                const triggerElement = event.relatedTarget; 
                
                // Ekstrak informasi dari data-attributes
                const imageSrc = triggerElement.getAttribute('data-image-src');
                const imageTitle = triggerElement.getAttribute('data-image-title');
                
                // Perbarui konten modal
                const modalImage = imageModal.querySelector('#modalImage');
                const modalTitle = imageModal.querySelector('.modal-title');
                
                modalImage.src = imageSrc;
                modalTitle.textContent = imageTitle;
            });
        });
    </script>
</body>

</html>