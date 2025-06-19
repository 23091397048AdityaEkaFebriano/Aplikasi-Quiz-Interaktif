<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include_once '../config/db.php';

// Ambil data tipe kendaraan
$db = new Database();
$conn = $db->getConnection();
$types = [];
$stmt = $conn->query("SELECT id, type_name FROM vehicle_types");
if ($stmt) {
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Proses submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $type_id = $_POST['type_id'];
    $availability = isset($_POST['availability']) ? 1 : 0;
    $image = null;

    // Upload gambar
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imgName = basename($_FILES['image']['name']);
        $targetDir = '../../public/img/';
        $targetFile = $targetDir . $imgName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $imgName;
        }
    }

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO cars (make, model, year, type_id, availability, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$make, $model, $year, $type_id, $availability, $image]);
    header('Location: car_list.php?success=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mobil</title>
    <!-- CSS Library -->
    <link rel="stylesheet" href="../../public/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/styles.css">
    <!-- Google Fonts: Poppins -->
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

        /* STYLE NAVBAR LINK */
        .navbar-nav .nav-link {
            padding-bottom: 8px; /* Jarak dengan garis bawah */
        }

        /* STYLE CONTAINER UTAMA */
        .page-container {
            flex: 1; /* Mengisi ruang yang tersedia */
            padding-bottom: 40px; /* Padding bawah agar tidak terlalu dekat dengan footer */
        }

        /* STYLE UNTUK FORM */
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            border-color: #007bff;
        }

        .form-control {
            border-radius: 5%;
        }

        .btn {
            border-radius: 5%;
        }

        .btn-primary {
            background: #007bff;
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

        /* STYLE CARD FORM */
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 16px;
            border: none;
        }

        /* STYLE FOOTER */
        .footer {
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            padding: 24px 0 10px 0;
            margin-top: auto; /* Push footer to bottom */
            text-align: center;
            margin-bottom: 0; /* Menghilangkan margin bawah */
        }

        /* STYLE SOCIAL LINKS */
        .social-link {
            transition: opacity 0.2s;
        }
        .social-link:hover {
            opacity: 0.7;
        }

        /* STYLE ALAMAT FOOTER */
        .footer-address {
            line-height: 1.8;
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
                <!-- Menu sebelah kiri - hanya ada Daftar Mobil -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold" href="car_list.php">Daftar Mobil</a>
                    </li>
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

    <!-- CONTAINER UTAMA -->
    <div class="page-container">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body p-4">
                            <h2 class="mb-4 text-center">Tambah Mobil</h2>
                            <!-- Form tambah mobil -->
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="make" class="form-label">Merk</label>
                                    <input type="text" class="form-control" id="make" name="make" required>
                                </div>
                                <div class="mb-3">
                                    <label for="model" class="form-label">Model</label>
                                    <input type="text" class="form-control" id="model" name="model" required>
                                </div>
                                <div class="mb-3">
                                    <label for="year" class="form-label">Tahun</label>
                                    <input type="number" class="form-control" id="year" name="year" required>
                                </div>
                                <div class="mb-3">
                                    <label for="type_id" class="form-label">Tipe Kendaraan</label>
                                    <select class="form-control" id="type_id" name="type_id" required>
                                        <option value="">Pilih Tipe</option>
                                        <?php foreach ($types as $type): ?>
                                            <option value="<?php echo $type['id']; ?>"><?php echo $type['type_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Gambar Mobil</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" value="1" id="availability" name="availability" checked>
                                    <label class="form-check-label" for="availability">
                                        Tersedia
                                    </label>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary px-4">Tambah Mobil</button>
                                    <a href="car_list.php" class="btn btn-secondary">Kembali</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
    
    <!-- Bootstrap JavaScript -->
    <script src="../../public/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>