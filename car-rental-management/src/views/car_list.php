<?php
// Memulai session untuk mengakses data login user
session_start();
// Mengimpor file koneksi database dan model mobil
include_once '../config/db.php';

// Membuat koneksi ke database
$db = new Database();
$conn = $db->getConnection();

// Filter mobil berdasarkan pencarian atau tipe
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? (int)$_GET['type'] : 0;

// Query untuk mendapatkan semua tipe kendaraan
$typeStmt = $conn->query("SELECT * FROM vehicle_types ORDER BY type_name");
$types = $typeStmt->fetchAll(PDO::FETCH_ASSOC);

// Query dasar untuk mobil
$query = "SELECT c.*, t.type_name 
          FROM cars c
          JOIN vehicle_types t ON c.type_id = t.id
          WHERE 1=1";
$params = [];

// Tambahkan filter pencarian
if (!empty($search)) {
    $query .= " AND (c.make LIKE ? OR c.model LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Tambahkan filter tipe
if ($type > 0) {
    $query .= " AND c.type_id = ?";
    $params[] = $type;
}

// Tambahkan pengurutan
$query .= " ORDER BY c.make, c.model";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug error jika ada
if (isset($_GET['error']) && $_GET['error'] == 'car_not_found') {
    // Log error untuk debugging
    error_log("Car not found error triggered. Last SQL: $query");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mobil</title>
    <!-- Import file CSS Bootstrap dan custom CSS -->
    <link rel="stylesheet" href="../../public/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/styles.css">
    <!-- Google Fonts: Poppins untuk tampilan font yang modern -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* STYLE DASAR HALAMAN */
        body {
            font-family: 'Poppins', Arial, sans-serif; /* Font utama */
            margin: 0;
            padding: 0;
            min-height: 100vh; /* Tinggi minimum seluruh viewport */
            display: flex;
            flex-direction: column; /* Layout flex untuk footer tetap di bawah */
        }

        /* STYLE KARTU MOBIL */
        .car-card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); /* Bayangan kartu */
            border-radius: 16px; /* Sudut bulat kartu */
            margin-bottom: 32px;
            overflow: hidden;
            transition: transform 0.2s; /* Animasi hover */
        }
        .car-card:hover {
            transform: translateY(-5px) scale(1.01); /* Efek mengambang saat hover */
        }
        .car-img {
            width: 100%;
            height: 220px;
            object-fit: cover; /* Gambar tetap proporsional */
            border-radius: 16px 16px 0 0; /* Sudut bulat atas gambar */
        }

        /* STYLE TOMBOL WHATSAPP */
        .wa-btn {
            background: #25d366; /* Warna dasar WhatsApp */
            color: #fff;
            border-radius: 5%; /* Radius tombol sesuai permintaan */
            font-weight: bold;
            padding: 6px 18px;
            text-decoration: none;
            transition: background 0.2s; /* Animasi hover */
            display: inline-block;
        }
        .wa-btn:hover {
            background: #128c7e; /* Warna lebih gelap saat hover */
            color: #fff;
        }

        /* STYLE FOOTER */
        .footer {
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            padding: 24px 0 10px 0;
            margin-top: 40px;
            text-align: center;
            margin-bottom: 0; /* Menghilangkan margin bawah */
        }

        /* STYLE NAVBAR */
        .navbar {
            background: linear-gradient(90deg, #007bff 60%, #0056b3 100%); /* Gradient biru */
            border-radius: 0; /* Nav tanpa radius sesuai permintaan */
        }
        /* Garis pemisah di navbar */
        .nav-divider {
            color: rgba(255,255,255,0.5);
            margin: 0 10px;
        }

        /* STYLE FORM PENCARIAN */
        .search-form {
            max-width: 400px;
        }
        /* Input search dengan radius 5% sesuai permintaan */
        .search-form input, 
        .search-form .btn {
            border-radius: 5%;
        }
        .search-form input:hover {
            border-color: #007bff; /* Ubah warna stroke saat hover */
            border-width: 1px; /* Tetap 1px tidak berubah */
        }

        /* STYLE NOTIFIKASI SUKSES */
        .success-alert {
            background-color: rgba(40, 167, 69, 0.9);
            color: white;
            border-radius: 8px;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            z-index: 9999; /* Di atas semua elemen */
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: fadeOut 5s forwards; /* Animasi menghilang */
        }
        /* Animasi menghilang notifikasi */
        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; visibility: hidden; }
        }

        /* STYLE LINK SOSIAL MEDIA */
        .social-link {
            transition: opacity 0.2s; /* Animasi hover */
        }
        .social-link:hover {
            opacity: 0.7; /* Lebih transparan saat hover */
        }

        /* STYLE ALAMAT DI FOOTER */
        .footer-address {
            line-height: 1.8; /* Spasi baris alamat */
        }

        /* STYLE NAVBAR LINK */
        .navbar-nav .nav-link {
            padding-bottom: 8px; /* Jarak dengan garis bawah */
        }

        /* STYLE CONTAINER UTAMA */
        .page-container {
            flex: 1; /* Mengisi ruang yang tersedia */
        }

        /* Tambahan style untuk tombol di dalam kartu mobil */
        .car-card .btn {
            border-radius: 5% !important;
        }
    </style>
</head>
<body>
    <!-- NAVIGATION BAR -->
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
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <!-- Menu Tambah Mobil khusus admin -->
                    <li class="nav-item">
                        <a class="nav-link active" href="car_add.php">Tambahkan Mobil</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <!-- Tombol dashboard sebelah kanan -->
                <div class="d-flex">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <!-- Link kembali ke dashboard admin -->
                        <a href="admin_dashboard.php" class="btn btn-outline-light">
                            <i class="bi bi-arrow-left"></i> Dashboard Admin
                        </a>
                    <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'borrower'): ?>
                        <!-- Link kembali ke dashboard penyewa -->
                        <a href="borrower_dashboard.php" class="btn btn-outline-light">
                            <i class="bi bi-arrow-left"></i> Dashboard Penyewa
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- NOTIFIKASI SUKSES jika parameter success=1 -->
    <?php if (isset($_GET['success'])): ?>
    <div class="success-alert">
        Mobil berhasil ditambahkan!
    </div>
    <?php endif; ?>

    <!-- CONTAINER UTAMA -->
    <div class="page-container">
        <div class="container my-4">
            <!-- Header dan Form Pencarian -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <!-- Judul halaman -->
                <h2 class="mb-0">Daftar Mobil:</h2>
                <!-- Form pencarian -->
                <form class="search-form d-flex" method="GET">
                    <input type="text" name="search" class="form-control me-2" placeholder="Cari merek" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary me-2">Cari</button>
                    <?php if ($search): ?>
                        <!-- Tombol reset hanya muncul jika ada pencarian -->
                        <a href="car_list.php" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- DAFTAR MOBIL -->
            <div class="row">
                <?php if (empty($cars)): ?>
                    <!-- Pesan jika tidak ada mobil ditemukan -->
                    <div class="col-12 text-center py-5">
                        <h4>Tidak ada mobil yang ditemukan</h4>
                        <?php if ($search): ?>
                            <a href="car_list.php" class="btn btn-outline-primary mt-3">Lihat semua mobil</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Loop untuk menampilkan setiap mobil -->
                    <?php foreach ($cars as $car): ?>
                        <div class="col-md-6 col-lg-4">
                            <!-- Kartu mobil -->
                            <div class="car-card mb-4 bg-white">
                                <!-- Gambar mobil -->
                                <img src="<?php echo !empty($car['image']) ? '../../public/img/' . $car['image'] : 'https://via.placeholder.com/300x200?text=Mobil' ?>" 
                                     alt="<?php echo $car['make'] . ' ' . $car['model']; ?>" class="car-img">
                                <div class="card-body p-3">
                                    <h5 class="card-title"><?php echo $car['make'] . ' ' . $car['model']; ?></h5>
                                    <p class="mb-2">Tahun <?php echo $car['year']; ?></p>
                                    <p class="mb-2">Tipe: <?php echo $car['type_name']; ?></p>
                                    <!-- Tambahkan badge status tersedia/tidak -->
                                    <?php if ($car['availability'] == 1): ?>
                                        <span class="badge bg-success mb-2">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger mb-2">Tidak Tersedia</span>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'borrower'): ?>
                                        <div class="d-flex mt-3">
                                            <?php if ($car['availability'] == 1): ?>
                                                <a href="rental_add.php?car_id=<?php echo $car['id']; ?>" class="btn btn-primary flex-fill">Sewa Sekarang</a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary flex-fill" disabled>Tidak Tersedia</button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- FOOTER -->
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

    <!-- Bootstrap JavaScript untuk fungsi navbar collapse dll -->
    <script src="../../public/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>