<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'borrower') {
    header('Location: login.php');
    exit();
}
include_once '../config/db.php';
include_once '../controllers/borrower.php';

$borrower = new BorrowerController();
$cars = $borrower->getAvailableCars();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penyewa</title>
    <!-- CSS Library -->
    <link rel="stylesheet" href="../../public/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/styles.css">
    <!-- Google Fonts: Poppins -->
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

        /* ====================================================
           STYLE CONTAINER UTAMA
           ==================================================== */
        .page-container {
            flex: 1; /* Mengisi ruang yang tersedia */
            padding-bottom: 40px; /* Padding bawah agar tidak terlalu dekat dengan footer */
        }

        /* ====================================================
           STYLE DASHBOARD BORROWER
           ==================================================== */
        .dashboard-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 32px 28px;
            text-align: center;
        }
        
        .dashboard-title {
            font-weight: 700;
            color: #007bff;
            margin-bottom: 18px;
        }
        
        .dashboard-img {
            width: 320px;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.10);
        }
        
        .dashboard-desc {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 28px;
            line-height: 1.6;
        }
        
        /* ====================================================
           STYLE TOMBOL
           ==================================================== */
        .btn {
            border-radius: 5%; /* Ubah border radius menjadi 5% */
            transition: transform 0.2s, background 0.2s;
        }
        
        .btn-main {
            background: #007bff;
            color: #fff;
            font-weight: 600;
            padding: 12px 28px;
            border: none;
        }
        
        .btn-main:hover {
            background: #0056b3;
            color: #fff;
            transform: translateY(-3px);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
        }
        
        .btn-warning:hover,
        .btn-danger:hover {
            transform: translateY(-3px);
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
           STYLE SOCIAL LINKS
           ==================================================== */
        .social-link {
            transition: opacity 0.2s;
        }
        .social-link:hover {
            opacity: 0.7;
        }

        /* ====================================================
           STYLE ALAMAT FOOTER
           ==================================================== */
        .footer-address {
            line-height: 1.8;
        }
        
        /* ====================================================
           STYLE CARD MOBIL
           ==================================================== */
        .car-card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s;
            margin-bottom: 20px;
            overflow: hidden;
            border: none;
        }
        
        .car-card:hover {
            transform: translateY(-5px);
        }
        
        .car-img {
            height: 160px;
            object-fit: cover;
        }
        
        .car-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .car-price {
            color: #007bff;
            font-weight: bold;
            font-size: 1rem;
        }
        
        .car-detail {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .dashboard-btn-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .dashboard-btn-group .btn {
            min-width: 140px;
            padding: 6px 18px;
            font-size: 0.95rem;
            border-radius: 5%;
            font-weight: 600;
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
            <!-- Garis pemisah -->
            <span class="nav-divider">|</span>
            <!-- Tombol hamburger untuk mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Menu sebelah kiri -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold" href="car_list.php">Daftar Mobil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold" href="borrower_rentals.php">Riwayat Sewa</a>
                    </li>
                </ul>
                <!-- Tombol logout sebelah kanan -->
                <div class="d-flex">
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ====================================================
         CONTAINER UTAMA
         ==================================================== -->
    <div class="page-container">
        <div class="container mt-4">
            <!-- Dashboard Container -->
            <div class="dashboard-container">
                <img src="https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?auto=format&fit=crop&w=600&q=80" alt="Rental Mobil" class="dashboard-img">
                <h2 class="dashboard-title">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <div class="dashboard-desc">
                    Temukan dan sewa mobil terbaik untuk kebutuhan perjalanan Anda.<br>
                    Lihat daftar mobil yang tersedia, lakukan pemesanan, dan pantau status sewa Anda dengan mudah.
                </div>
                <div class="dashboard-btn-group">
                    <a href="car_list.php" class="btn btn-main">Lihat Mobil Selengkapnya</a>
                    <a href="borrower_rentals.php" class="btn btn-warning">Riwayat Sewa</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <!-- Mobil Tersedia -->
            <div class="mt-5">
                <h3 class="text-center mb-4">Mobil Tersedia Untuk Disewa</h3>
                <div class="row">
                    <?php foreach ($cars as $index => $car): ?>
                        <?php if ($index < 4): // Tampilkan hanya 4 mobil teratas ?>
                        <div class="col-md-3">
                            <div class="card car-card">
                                <img src="<?php echo !empty($car['image']) ? '../../public/img/'.$car['image'] : 'https://via.placeholder.com/300x200?text=Mobil' ?>" 
                                     class="card-img-top car-img" alt="<?php echo $car['make'] . ' ' . $car['model']; ?>">
                                <div class="card-body">
                                    <h5 class="car-title"><?php echo $car['make'] . ' ' . $car['model']; ?></h5>
                                    <p class="car-price">Tahun <?php echo $car['year']; ?></p>
                                    <p class="car-detail">
                                        <small>Tipe: <?php echo $car['type_name']; ?></small>
                                    </p>
                                    <a href="rental_add.php?car_id=<?php echo $car['id']; ?>" class="btn btn-primary btn-sm">Sewa Sekarang</a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php if (count($cars) > 4): ?>
                <div class="text-center mt-3">
                    <a href="car_list.php" class="btn btn-outline-primary">Lihat Semua Mobil</a>
                </div>
                <?php endif; ?>
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
                <!-- Kolom sosial media -->
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <strong>Ikuti Kami:</strong><br><br>
                    <!-- Icon Instagram -->
                    <a href="https://instagram.com/yourrental" class="social-link" target="_blank" style="margin:0 10px;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/instagram.svg" alt="Instagram" width="28" style="vertical-align:middle;filter:invert(1);">
                    </a>
                    <!-- Icon Twitter -->
                    <a href="https://twitter.com/yourrental" class="social-link" target="_blank" style="margin:0 10px;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/twitter.svg" alt="Twitter" width="28" style="vertical-align:middle;filter:invert(1);">
                    </a>
                    <!-- Icon Facebook -->
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

    <!-- Bootstrap JavaScript -->
    <script src="../../public/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>