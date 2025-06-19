<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include_once '../config/db.php';

$db = new Database();
$conn = $db->getConnection();

// Total pengguna
$totalUser = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAdmin = $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$totalBorrower = $conn->query("SELECT COUNT(*) FROM users WHERE role='borrower'")->fetchColumn();

// Total mobil
$totalCar = $conn->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$totalAvailable = $conn->query("SELECT COUNT(*) FROM cars WHERE availability=1")->fetchColumn();
$totalUnavailable = $conn->query("SELECT COUNT(*) FROM cars WHERE availability=0")->fetchColumn();

// Total sewa
$totalRental = $conn->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sistem Rental Mobil</title>
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

        /* ====================================================
           STYLE CONTAINER UTAMA
           ==================================================== */
        .page-container {
            flex: 1; /* Mengisi ruang yang tersedia */
            padding-bottom: 40px; /* Padding bawah agar tidak terlalu dekat dengan footer */
        }

        /* ====================================================
           STYLE UNTUK CARD
           ==================================================== */
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 16px;
            border: none;
            transition: transform 0.2s;
            margin-bottom: 20px;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            border-radius: 16px 16px 0 0 !important;
            font-weight: 600;
        }

        /* ====================================================
           STYLE TOMBOL
           ==================================================== */
        .btn {
            border-radius: 5%;
        }

        .btn-primary, .btn-success {
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            transition: background 0.2s;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* ====================================================
           STYLE REPORT STATS
           ==================================================== */
        .stats-value {
            font-size: 1.8rem;
            font-weight: 700;
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .stats-card {
            padding: 15px;
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
            <!-- Tombol hamburger untuk mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Menu sebelah kiri - kosong -->
                <ul class="navbar-nav me-auto">
                </ul>
                <!-- Tombol dashboard sebelah kanan -->
                <div class="d-flex">
                    <a href="admin_dashboard.php" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left"></i> Dashboard Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ====================================================
         CONTAINER UTAMA
         ==================================================== -->
    <div class="page-container">
        <div class="container mt-5">
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h2 class="mb-4 text-center">Laporan Sistem Rental Mobil</h2>
                    
                    <!-- Statistics Cards -->
                    <div class="row mt-4">
                        <!-- Pengguna Stats -->
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <i class="bi bi-people-fill"></i> Pengguna
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4 stats-card">
                                            <span class="stats-value"><?php echo $totalUser; ?></span>
                                            <span class="stats-label">Total Pengguna</span>
                                        </div>
                                        <div class="col-md-4 stats-card">
                                            <span class="stats-value"><?php echo $totalAdmin; ?></span>
                                            <span class="stats-label">Admin</span>
                                        </div>
                                        <div class="col-md-4 stats-card">
                                            <span class="stats-value"><?php echo $totalBorrower; ?></span>
                                            <span class="stats-label">Borrower</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mobil Stats -->
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <i class="bi bi-car-front-fill"></i> Mobil
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4 stats-card">
                                            <span class="stats-value"><?php echo $totalCar; ?></span>
                                            <span class="stats-label">Total Mobil</span>
                                        </div>
                                        <div class="col-md-4 stats-card">
                                            <span class="stats-value"><?php echo $totalAvailable; ?></span>
                                            <span class="stats-label">Tersedia</span>
                                        </div>
                                        <div class="col-md-4 stats-card">
                                            <span class="stats-value"><?php echo $totalUnavailable; ?></span>
                                            <span class="stats-label">Tidak Tersedia</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Transaksi Stats -->
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white">
                                    <i class="bi bi-receipt"></i> Transaksi Sewa
                                </div>
                                <div class="card-body text-center">
                                    <div class="stats-card">
                                        <span class="stats-value"><?php echo $totalRental; ?></span>
                                        <span class="stats-label">Total Transaksi Sewa</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Peringkat Tabel -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <i class="bi bi-star-fill"></i> Mobil Terpopuler
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr class="table-secondary">
                                                    <th>No</th>
                                                    <th>Merk & Model</th>
                                                    <th>Jumlah Sewa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $popularCars = $conn->query("SELECT c.make, c.model, COUNT(r.id) as rental_count 
                                                                        FROM cars c 
                                                                        JOIN rentals r ON c.id = r.car_id 
                                                                        GROUP BY c.id 
                                                                        ORDER BY rental_count DESC 
                                                                        LIMIT 5");
                                                $no = 1;
                                                while ($car = $popularCars->fetch(PDO::FETCH_ASSOC)) {
                                                    echo "<tr>
                                                          <td>{$no}</td>
                                                          <td>{$car['make']} {$car['model']}</td>
                                                          <td>{$car['rental_count']}</td>
                                                        </tr>";
                                                    $no++;
                                                }
                                                if ($no == 1) {
                                                    echo "<tr><td colspan='3' class='text-center'>Belum ada data sewa</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <i class="bi bi-person-check-fill"></i> Peminjam Teraktif
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr class="table-secondary">
                                                    <th>No</th>
                                                    <th>Username</th>
                                                    <th>Jumlah Sewa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $activeBorrowers = $conn->query("SELECT u.username, COUNT(r.id) as rental_count 
                                                                            FROM users u 
                                                                            JOIN rentals r ON u.id = r.user_id 
                                                                            GROUP BY u.id 
                                                                            ORDER BY rental_count DESC 
                                                                            LIMIT 5");
                                                $no = 1;
                                                while ($borrower = $activeBorrowers->fetch(PDO::FETCH_ASSOC)) {
                                                    echo "<tr>
                                                          <td>{$no}</td>
                                                          <td>{$borrower['username']}</td>
                                                          <td>{$borrower['rental_count']}</td>
                                                        </tr>";
                                                    $no++;
                                                }
                                                if ($no == 1) {
                                                    echo "<tr><td colspan='3' class='text-center'>Belum ada data peminjam aktif</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tombol Cetak -->
                    <div class="mt-4 text-center">
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="bi bi-printer"></i> Cetak Laporan
                        </button>
                        <a href="admin_dashboard.php" class="btn btn-secondary ms-2">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
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
    <!-- Media print styles -->
    <style media="print">
        @media print {
            .navbar, .footer, .btn {
                display: none !important;
            }
            .card {
                break-inside: avoid;
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
            .card-header {
                background-color: #f8f9fa !important;
                color: #333 !important;
            }
            .page-container {
                padding: 0 !important;
            }
            .stats-value {
                color: #000 !important;
            }
        }
    </style>
</body>
</html>