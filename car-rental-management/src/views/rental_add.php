<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'borrower') {
    header('Location: login.php');
    exit();
}

include_once '../config/db.php';

// Pastikan car_id ada
if (!isset($_GET['car_id']) || empty($_GET['car_id'])) {
    header('Location: car_list.php?error=invalid_car');
    exit();
}

$car_id = $_GET['car_id'];

$db = new Database();
$conn = $db->getConnection();

// Ambil detail mobil
$stmt = $conn->prepare("SELECT c.*, t.type_name 
                        FROM cars c
                        JOIN vehicle_types t ON c.type_id = t.id 
                        WHERE c.id = ? AND c.availability = 1");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header('Location: car_list.php?error=car_not_found');
    exit();
}

// Pesan WhatsApp untuk negosiasi
$whatsappMessage = "Halo Admin, saya tertarik untuk menyewa mobil " . $car['make'] . " " . $car['model'] . " (ID: " . $car['id'] . "). Bisakah kita diskusikan harga dan ketersediaannya?";
$whatsappUrl = "https://wa.me/6281234567890?text=" . urlencode($whatsappMessage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Mobil - <?php echo $car['make'] . ' ' . $car['model']; ?></title>
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
           STYLE CAR DETAIL
           ==================================================== */
        .car-detail-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .car-detail-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .car-detail-info {
            padding: 25px;
        }
        
        .car-title {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: #333;
        }
        
        .car-subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 12px;
            font-size: 1rem;
        }
        
        .detail-label {
            font-weight: 600;
            width: 120px;
            color: #555;
        }
        
        .detail-value {
            color: #333;
        }
        
        /* ====================================================
           STYLE TOMBOL
           ==================================================== */
        .btn {
            border-radius: 5%;
            padding: 10px 20px;
            transition: all 0.2s;
            font-weight: 600;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        
        .wa-btn {
            background-color: #25D366;
            color: white;
            border: none;
            padding: 12px 24px;
            transition: background-color 0.2s, transform 0.2s;
        }
        
        .wa-btn:hover {
            background-color: #1da851;
            color: white;
            transform: translateY(-3px);
        }
        
        .wa-icon {
            vertical-align: middle;
            margin-right: 8px;
        }
        
        /* ====================================================
           STYLE RENTAL STEPS
           ==================================================== */
        .rental-steps {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .step {
            display: flex;
            margin-bottom: 25px;
            align-items: flex-start;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .step-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 6px;
            color: #333;
        }
        
        .step-desc {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
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
                <!-- Menu sebelah kiri -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold" href="car_list.php">Daftar Mobil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold" href="borrower_rentals.php">Riwayat Sewa</a>
                    </li>
                </ul>
                <!-- Tombol kembali sebelah kanan -->
                <div class="d-flex">
                    <a href="car_list.php" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left"></i> Kembali
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
            <div class="row">
                <!-- Kolom Detail Mobil -->
                <div class="col-md-7">
                    <div class="car-detail-card">
                        <!-- Gambar Mobil -->
                        <?php if (!empty($car['image'])): ?>
                            <img src="../../public/img/<?php echo $car['image']; ?>" alt="<?php echo $car['make'] . ' ' . $car['model']; ?>" class="car-detail-img">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/600x300?text=No+Image+Available" class="car-detail-img" alt="No Image">
                        <?php endif; ?>
                        
                        <!-- Informasi Detail Mobil -->
                        <div class="car-detail-info">
                            <h2 class="car-title"><?php echo $car['make'] . ' ' . $car['model']; ?></h2>
                            <p class="car-subtitle">Tahun <?php echo $car['year']; ?> | Tipe: <?php echo $car['type_name']; ?></p>
                            
                            <div class="detail-row">
                                <span class="detail-label">Merk:</span>
                                <span class="detail-value"><?php echo $car['make']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Model:</span>
                                <span class="detail-value"><?php echo $car['model']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Tahun:</span>
                                <span class="detail-value"><?php echo $car['year']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Tipe:</span>
                                <span class="detail-value"><?php echo $car['type_name']; ?></span>
                            </div>
                            
                            <!-- Tombol Kontak & Sewa -->
                            <div class="d-flex flex-column mt-4">
                                <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="btn wa-btn mb-3">
                                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/whatsapp.svg" alt="WhatsApp" class="wa-icon" style="filter:invert(1);width:20px;margin-right:8px;">
                                    Hubungi via WhatsApp
                                </a>
                                
                                <!-- Tombol Lanjutkan Sewa -->
                                <a href="rental_payment.php?car_id=<?php echo $car_id; ?>" class="btn btn-primary">
                                    <i class="bi bi-arrow-right"></i> Lanjut Sewa
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Kolom Langkah Penyewaan -->
                <div class="col-md-5">
                    <div class="rental-steps">
                        <h4 class="mb-4">Langkah Penyewaan:</h4>
                        
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <div class="step-title">Negosiasi via WhatsApp</div>
                                <div class="step-desc">Hubungi admin melalui WhatsApp untuk menegosiasikan harga dan ketersediaan waktu sewa.</div>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <div class="step-title">Upload Bukti Pembayaran</div>
                                <div class="step-desc">Setelah negosiasi selesai, lakukan pembayaran dan upload bukti transfer.</div>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <div class="step-title">Konfirmasi Admin</div>
                                <div class="step-desc">Admin akan memeriksa pembayaran Anda dan mengkonfirmasikan sewa.</div>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <div class="step-title">Ambil Mobil</div>
                                <div class="step-desc">Setelah sewa dikonfirmasi, Anda dapat mengambil mobil sesuai waktu yang telah disepakati.</div>
                            </div>
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