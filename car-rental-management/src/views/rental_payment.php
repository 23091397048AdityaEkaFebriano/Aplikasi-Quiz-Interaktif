<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'borrower') {
    header('Location: login.php');
    exit();
}

include_once '../config/db.php';
include_once '../models/car.php';

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Pastikan car_id ada
if (!isset($_GET['car_id']) || !is_numeric($_GET['car_id'])) {
    header('Location: car_list.php?error=invalid_car');
    exit();
}

$car_id = $_GET['car_id'];

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

// Proses form jika disubmit
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi checkbox konfirmasi
    if (!isset($_POST['confirm_negotiation']) || $_POST['confirm_negotiation'] !== 'on') {
        $message = '<div class="alert alert-danger">Anda harus mengkonfirmasi bahwa sudah melakukan negosiasi via WhatsApp.</div>';
    } else {
        // Proses upload gambar
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $filename = 'payment_' . time() . '_' . $_FILES['payment_proof']['name'];
            $targetDir = '../../public/uploads/';
            
            // Buat direktori jika belum ada
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            $targetFile = $targetDir . $filename;
            
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetFile)) {
                // Tambahkan data sewa dan upload bukti pembayaran
                try {
                    $conn->beginTransaction();
                    
                    // Insert data sewa
                    $rentalStmt = $conn->prepare("INSERT INTO rentals (user_id, car_id, rental_date, return_date, payment_status, payment_proof, payment_date) 
                                                 VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'pending', ?, NOW())");
                    $rentalStmt->execute([$user_id, $car_id, $filename]);
                    
                    // Update status mobil menjadi tidak tersedia
                    $updateStmt = $conn->prepare("UPDATE cars SET availability = 0 WHERE id = ?");
                    $updateStmt->execute([$car_id]);
                    
                    $conn->commit();
                    
                    // Redirect ke halaman riwayat sewa
                    header('Location: borrower_rentals.php?success=payment_uploaded');
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Error: Gagal upload file bukti pembayaran.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Error: Silakan pilih file bukti pembayaran.</div>';
        }
    }
}

// Format tanggal mulai
$startDate = date('d F Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran - <?php echo $car['make'] . ' ' . $car['model']; ?></title>
    <!-- CSS Library -->
    <link rel="stylesheet" href="../../public/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/styles.css">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ====================================================
           STYLE DASAR HALAMAN 
           ==================================================== */
        body {
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }

        /* ====================================================
           STYLE NAVBAR
           ==================================================== */
        .navbar {
            background: linear-gradient(90deg, #007bff 60%, #0056b3 100%);
            border-radius: 0;
            padding: 15px 0;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .nav-divider {
            color: rgba(255,255,255,0.5);
            margin: 0 15px;
            font-size: 1.2rem;
        }

        /* ====================================================
           STYLE CONTAINER UTAMA
           ==================================================== */
        .page-container {
            flex: 1;
            padding-bottom: 40px;
        }

        /* ====================================================
           STYLE PAYMENT CARD
           ==================================================== */
        .payment-card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 30px;
            background-color: white;
        }
        
        .payment-card-header {
            background: linear-gradient(90deg, #007bff 60%, #0056b3 100%);
            color: white;
            padding: 20px 30px;
            font-weight: 600;
            font-size: 1.4rem;
            border-bottom: none;
        }
        
        .payment-card-body {
            padding: 30px;
        }
        
        /* ====================================================
           STYLE CAR DETAILS
           ==================================================== */
        .car-details-card {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            border-left: 5px solid #007bff;
        }
        
        .car-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .car-image {
            width: 100%;
            height: 240px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .car-image-container:hover .car-image {
            transform: scale(1.05);
        }
        
        .car-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .car-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .car-detail-row {
            display: flex;
            margin-bottom: 12px;
            font-size: 1.1rem;
            align-items: center;
        }
        
        .car-detail-label {
            min-width: 150px;
            display: inline-block;
            font-weight: 600;
            color: #555;
        }
        
        .car-detail-value {
            color: #333;
            font-weight: 500;
        }
        
        /* ====================================================
           STYLE PAYMENT INSTRUCTIONS
           ==================================================== */
        .payment-instructions {
            background-color: #f8f9fa;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid #ffc107;
        }
        
        .instruction-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }
        
        .instruction-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .step-number {
            background-color: #007bff;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.1rem;
            color: #444;
        }
        
        .step-description {
            color: #666;
            line-height: 1.5;
            font-size: 0.95rem;
        }
        
        /* ====================================================
           STYLE UPLOAD FORM
           ==================================================== */
        .upload-form-container {
            background-color: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #28a745;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 1.1rem;
            color: #444;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            border: 1px solid #ddd;
            box-shadow: none;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }
        
        .form-text {
            color: #777;
            font-size: 0.9rem;
        }
        
        .form-check-label {
            font-weight: 500;
            padding-left: 5px;
        }
        
        .upload-button {
            background: #28a745;
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
            width: 100%;
            text-align: center;
        }
        
        .upload-button:hover {
            background: #218838;
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
        }
        
        /* ====================================================
           STYLE PREVIEW IMAGE
           ==================================================== */
        .img-preview-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .img-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            display: none;
        }
        
        .preview-text {
            margin-top: 10px;
            font-weight: 500;
            color: #777;
        }

        /* ====================================================
           STYLE FOOTER
           ==================================================== */
        .footer {
            background: linear-gradient(90deg, #0056b3 60%, #007bff 100%);
            color: #fff;
            border-radius: 0;
            margin-bottom: 0;
            padding: 30px 0;
            margin-top: auto;
        }

        .social-link {
            transition: opacity 0.2s;
        }
        
        .social-link:hover {
            opacity: 0.7;
        }

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
            <a class="navbar-brand" href="#">Rental Mobil</a>
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
                <!-- Tombol kembali sebelah kanan -->
                <div class="d-flex">
                    <a href="rental_add.php?car_id=<?php echo $car_id; ?>" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left"></i> Kembali
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
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Card utama -->
                    <div class="payment-card">
                        <div class="payment-card-header">
                            <i class="fas fa-credit-card me-2"></i> Upload Bukti Pembayaran
                        </div>
                        <div class="payment-card-body">
                            <!-- Pesan status -->
                            <?php echo $message; ?>
                            
                            <!-- Detail Mobil -->
                            <div class="car-details-card mb-4">
                                <div class="row">
                                    <div class="col-lg-5">
                                        <div class="car-image-container">
                                            <?php if (!empty($car['image'])): ?>
                                                <img src="../../public/img/<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>" class="car-image">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/500x300?text=<?php echo urlencode($car['make'] . ' ' . $car['model']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>" class="car-image">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <h3 class="car-title"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h3>
                                        <p class="car-subtitle">Tahun <?php echo htmlspecialchars($car['year']); ?> | <?php echo htmlspecialchars($car['type_name']); ?></p>
                                        <div class="car-detail-row" style="align-items:center;">
                                            <span class="car-detail-label" style="min-width:150px;display:inline-block;">
                                                <i class="fas fa-calendar me-2"></i> Tanggal Sewa:&nbsp;
                                            </span>
                                            <span class="car-detail-value" style="font-size:1.15rem; font-weight:600;">
                                                <?php echo $startDate; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Instruksi Pembayaran -->
                            <div class="payment-instructions mb-4">
                                <h4 class="instruction-title"><i class="fas fa-info-circle me-2"></i> Instruksi Pembayaran</h4>
                                
                                <div class="instruction-step">
                                    <div class="step-number">1</div>
                                    <div class="step-content">
                                        <div class="step-title">Lakukan Pembayaran</div>
                                        <div class="step-description">
                                            Silahkan transfer ke rekening berikut:
                                            <ul class="mt-2">
                                                <li><strong>Bank BCA:</strong> 1234567890 (a.n. Rental Mobil)</li>
                                                <li><strong>Bank Mandiri:</strong> 0987654321 (a.n. Rental Mobil)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="instruction-step">
                                    <div class="step-number">2</div>
                                    <div class="step-content">
                                        <div class="step-title">Screenshot Bukti Transfer</div>
                                        <div class="step-description">
                                            Setelah melakukan pembayaran, silahkan screenshot atau foto bukti transfer Anda.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="instruction-step">
                                    <div class="step-number">3</div>
                                    <div class="step-content">
                                        <div class="step-title">Upload Bukti Pembayaran</div>
                                        <div class="step-description">
                                            Upload bukti pembayaran pada formulir di bawah ini dan tunggu konfirmasi dari admin.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Upload Bukti Pembayaran -->
                            <div class="upload-form-container">
                                <h4 class="mb-4"><i class="fas fa-upload me-2"></i> Upload Bukti Pembayaran</h4>
                                
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-4">
                                        <label for="payment_proof" class="form-label">Bukti Pembayaran</label>
                                        <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/*" required>
                                        <small class="form-text">Format: JPG, PNG, atau JPEG. Maksimal 2MB.</small>
                                        
                                        <div class="img-preview-container">
                                            <img id="preview" class="img-preview" alt="Preview Bukti Pembayaran">
                                            <span class="preview-text" id="preview-text"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="confirm_negotiation" name="confirm_negotiation" required>
                                            <label class="form-check-label" for="confirm_negotiation">
                                                <strong>Dengan ini saya menyatakan telah melakukan negosiasi dan konfirmasi via WhatsApp dengan admin</strong>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="upload-button">
                                        <i class="fas fa-cloud-upload-alt me-2"></i> Upload Bukti Pembayaran
                                    </button>
                                </form>
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
    <footer class="footer">
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
    
    <!-- Preview Image Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('payment_proof');
            const imgPreview = document.getElementById('preview');
            const previewText = document.getElementById('preview-text');
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                        
                        // Mendapatkan nama file dan ukuran
                        const fileName = fileInput.files[0].name;
                        const fileSize = (fileInput.files[0].size / 1024).toFixed(2);
                        
                        // Menampilkan informasi file
                        previewText.textContent = fileName + ' (' + fileSize + ' KB)';
                        previewText.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                } else {
                    imgPreview.style.display = 'none';
                    previewText.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>