<?php
// Memulai sesi untuk memeriksa status login user
session_start();
// Memeriksa apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika bukan admin, redirect ke halaman login
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <!-- Import file CSS Bootstrap dan custom CSS -->
    <link rel="stylesheet" href="../../public/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/styles.css">
    <!-- Google Fonts: Poppins untuk tampilan font yang modern -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ====================================================
           STYLE DASAR HALAMAN 
           ==================================================== */
        body {
            font-family: 'Poppins', Arial, sans-serif; /* Font utama */
            margin: 0; /* Reset margin default */
            padding: 0; /* Reset padding default */
            min-height: 100vh; /* Tinggi minimum seluruh viewport */
            display: flex;
            flex-direction: column; /* Layout flex untuk footer tetap di bawah */
        }
        
        /* ====================================================
           STYLE CONTAINER UTAMA 
           ==================================================== */
        .page-container {
            flex: 1; /* Mengisi ruang yang tersedia */
        }
        
        /* ====================================================
           STYLE NAVBAR
           ==================================================== */
        .navbar {
            background: linear-gradient(90deg, #007bff 60%, #0056b3 100%); /* Gradient biru */
            border-radius: 0; /* Nav tanpa radius */
        }
        /* Garis pemisah di navbar */
        .nav-divider {
            color: rgba(255,255,255,0.5);
            margin: 0 10px;
        }
        
        /* Style untuk link pada navbar */
        .navbar-nav .nav-link {
            padding-bottom: 8px; /* Jarak dengan garis bawah */
        }

        /* ====================================================
           STYLE CARD MENU UTAMA
           ==================================================== */
        /* Style untuk gambar pada card */
        .feature-card img {
            width: 100%;
            height: 160px;
            object-fit: cover; /* Gambar tetap proporsional */
            border-radius: 10px 10px 0 0; /* Sudut bulat hanya di bagian atas */
        }
        
        /* Style untuk card menu */
        .feature-card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); /* Bayangan card */
            border-radius: 12px; /* Sudut bulat card */
            margin-bottom: 30px;
            transition: transform 0.2s; /* Animasi hover */
        }
        
        /* Efek hover pada card */
        .feature-card:hover {
            transform: translateY(-5px) scale(1.01); /* Efek mengambang saat hover */
        }
        
        /* Style untuk body card */
        .feature-card .card-body {
            min-height: 170px; /* Tinggi minimum agar semua card seragam */
        }
        
        /* ====================================================
           STYLE TOMBOL 
           ==================================================== */
        .btn {
            border-radius: 5% !important; /* Radius tombol 5% */
        }
        
        /* ====================================================
           STYLE FOOTER 
           ==================================================== */
        .footer {
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            padding: 24px 0 10px 0;
            margin-top: auto; /* Push footer to bottom */
            text-align: center;
            margin-bottom: 0; /* Menghilangkan margin bawah */
        }
        
        /* ====================================================
           STYLE SOCIAL MEDIA ICONS
           ==================================================== */
        .social-link {
            transition: opacity 0.2s; /* Animasi hover */
        }
        .social-link:hover {
            opacity: 0.7; /* Lebih transparan saat hover */
        }
        
        /* ====================================================
           STYLE ALAMAT DI FOOTER
           ==================================================== */
        .footer-address {
            line-height: 1.8; /* Spasi baris alamat */
        }
    </style>
</head>
<body>
    <!-- ====================================================
         NAVIGATION BAR
         ==================================================== -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <!-- Logo/Brand -->
            <a class="navbar-brand fw-bold" href="#">Rental Mobil</a>
            
            <!-- Tombol hamburger untuk tampilan mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Menu sebelah kiri - kosong sesuai permintaan -->
                <ul class="navbar-nav me-auto">
                    <!-- Tidak ada menu di sini -->
                </ul>
                
                <!-- Tombol logout di sebelah kanan -->
                <div class="d-flex">
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ====================================================
         CONTAINER UTAMA - ISI HALAMAN
         ==================================================== -->
    <div class="page-container">
        <div class="container mt-4">
            <!-- Header Halaman -->
            <header class="mb-4 text-center">
                <h1 class="mb-2">Admin Dashboard</h1>
                <p class="lead">Kelola seluruh data pengguna, mobil, transaksi sewa, dan laporan dengan mudah.</p>
            </header>
            
            <!-- Grid Menu Utama -->
            <div class="row">
                <!-- Menu Kelola Pengguna -->
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=400&q=80" alt="Kelola Pengguna" class="feature-img">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Pengguna</h5>
                            <p class="card-text">Tambah, lihat, dan kelola akun admin maupun peminjam.</p>
                            <a href="user_add.php" class="btn btn-success btn-sm mb-1">Tambah Pengguna</a>
                            <a href="user_list.php" class="btn btn-primary btn-sm">Lihat Pengguna</a>
                        </div>
                    </div>
                </div>

                <!-- Menu Kelola Mobil -->
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <img src="https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?auto=format&fit=crop&w=400&q=80" alt="Kelola Mobil" class="feature-img">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Mobil</h5>
                            <p class="card-text">Tambah data mobil baru, edit, atau lihat daftar mobil yang tersedia.</p>
                            <a href="car_add.php" class="btn btn-success btn-sm mb-1">Tambah Mobil</a>
                            <a href="car_list.php" class="btn btn-primary btn-sm">Lihat Mobil</a>
                        </div>
                    </div>
                </div>
                
                <!-- Menu Kelola Sewa - Opsi Alternatif 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <img src="https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=400&q=80" alt="Kelola Sewa" class="feature-img">
                        <div class="card-body">
                            <h5 class="card-title">Kelola Sewa</h5>
                            <p class="card-text">Pantau dan kelola seluruh transaksi penyewaan mobil.</p>
                            <a href="rental_list.php" class="btn btn-primary btn-sm">Lihat Sewa</a>
                        </div>
                    </div>
                </div>
                
                <!-- Menu Laporan -->
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=400&q=80" alt="Laporan" class="feature-img">
                        <div class="card-body">
                            <h5 class="card-title">Laporan</h5>
                            <p class="card-text">Lihat dan cetak laporan transaksi serta statistik rental mobil.</p>
                            <a href="report.php" class="btn btn-primary btn-sm">Lihat Laporan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ====================================================
         FOOTER
         ==================================================== -->
    <footer class="footer" style="background: linear-gradient(90deg, #0056b3 60%, #007bff 100%); color: #fff; border-radius: 0; margin-bottom: 0; padding-bottom: 20px;">
        <div class="container py-4">
            <div class="row">
                <!-- Kolom informasi kontak -->
                <div class="col-md-5 text-md-start text-center mb-3 mb-md-0">
                    <strong>Alamat:</strong><br>
                    <div class="footer-address">
                        Jl. Raya Rental Mobil No. 123<br>
                        Jakarta<br>
                    </div>
                    <strong>WhatsApp:</strong><br>
                    <a href="https://wa.me/6281234567890" target="_blank" style="color:#fff;text-decoration:underline;">+62 812-3456-7890</a><br>
                    <strong>Email:</strong><br>
                    <span style="color:#fff;">info@rentalmobil.com</span>
                </div>
                
                <!-- Kolom social media icons -->
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <strong>Ikuti Kami:</strong><br><br>
                    <!-- Instagram Icon -->
                    <a href="https://instagram.com/yourrental" class="social-link" target="_blank" style="margin:0 10px;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/instagram.svg" alt="Instagram" width="28" style="vertical-align:middle;filter:invert(1);">
                    </a>
                    <!-- Twitter Icon -->
                    <a href="https://twitter.com/yourrental" class="social-link" target="_blank" style="margin:0 10px;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/twitter.svg" alt="Twitter" width="28" style="vertical-align:middle;filter:invert(1);">
                    </a>
                    <!-- Facebook Icon -->
                    <a href="https://facebook.com/yourrental" class="social-link" target="_blank" style="margin:0 10px;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/facebook.svg" alt="Facebook" width="28" style="vertical-align:middle;filter:invert(1);">
                    </a>
                </div>
                
                <!-- Kolom peta lokasi -->
                <div class="col-md-4 text-center text-md-end">
                    <strong>Lokasi Kami:</strong><br>
                    <!-- Embedded Google Maps -->
                    <div style="width:100%;height:160px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.15);margin-top:8px;">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.3399999999997!2d106.816666!3d-6.200000!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f1c5e0b0b0b0%3A0x0!2sJl.%20Raya%20Rental%20Mobil%20No.%20123%2C%20Jakarta!5e0!3m2!1sen!2sid!4v1717000000000!5m2!1sen!2sid"
                            width="100%" height="160" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            tabindex="0">
                        </iframe>
                    </div>
                    <small class="d-block mt-2" style="color:#e0e0e0;">* Anda dapat scroll dan zoom langsung pada peta di atas</small>
                </div>
            </div>
            
            <!-- Footer credit -->
            <div class="mt-3 text-center" style="color:#e0e0e0;">Kelompok 1 | Astrid | Aditya | Naila | Senza</div>
        </div>
    </footer>

    <!-- Bootstrap JavaScript untuk interaktivitas komponen -->
    <script src="../../public/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>