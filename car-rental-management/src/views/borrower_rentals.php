<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'borrower') {
    header('Location: login.php');
    exit();
}

include_once '../config/db.php';
$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Proses upload bukti pembayaran
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rental_id'])) {
    $rental_id = $_POST['rental_id'];
    
    // Verifikasi rental milik user ini
    $checkStmt = $conn->prepare("SELECT id FROM rentals WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$rental_id, $user_id]);
    if ($checkStmt->rowCount() == 0) {
        $message = 'Error: Data sewa tidak ditemukan.';
    } else {
        // Upload file
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $filename = 'payment_' . time() . '_' . $_FILES['payment_proof']['name'];
            $targetDir = '../../public/uploads/';
            
            // Buat direktori jika belum ada
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            $targetFile = $targetDir . $filename;
            
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetFile)) {
                // Update database
                $stmt = $conn->prepare("UPDATE rentals SET payment_proof = ?, payment_date = NOW(), payment_status = 'pending' WHERE id = ? AND user_id = ?");
                if ($stmt->execute([$filename, $rental_id, $user_id])) {
                    $message = 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.';
                } else {
                    $message = 'Error: Gagal memperbarui data di database.';
                }
            } else {
                $message = 'Error: Gagal mengunggah file.';
            }
        } else {
            $message = 'Error: Silakan pilih file bukti pembayaran.';
        }
    }
}

// Ambil daftar rental untuk user ini - PERBAIKAN: hapus c.rental_rate dari field list
$stmt = $conn->prepare("SELECT r.*, c.make, c.model, c.image, t.type_name
                        FROM rentals r 
                        JOIN cars c ON r.car_id = c.id 
                        JOIN vehicle_types t ON c.type_id = t.id
                        WHERE r.user_id = ? 
                        ORDER BY r.rental_date DESC");
$stmt->execute([$user_id]);
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Sewa</title>
    <!-- CSS Library -->
    <link rel="stylesheet" href="../../public/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/styles.css">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
           STYLE CARD
           ==================================================== */
        .card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            border: none;
        }

        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }
        
        /* ====================================================
           STYLE TABEL
           ==================================================== */
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            color: #555;
        }
        
        .table thead th {
            background: #007bff !important;
            color: #fff !important;
            border-color: #007bff;
        }
        
        /* ====================================================
           STYLE STATUS BADGES
           ==================================================== */
        .badge {
            padding: 6px 12px;
            border-radius: 30px;
            font-weight: 500;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-approved {
            background-color: #28a745;
            color: white;
        }
        
        .badge-rejected {
            background-color: #dc3545;
            color: white;
        }
        
        /* ====================================================
           STYLE UPLOAD BUKTI
           ==================================================== */
        .upload-proof {
            background-color: #f8f9fa;
            border: 1px dashed #ccc;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 15px;
        }
        
        .proof-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: none;
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
           STYLE BUTTON & FORM CONTROLS
           ==================================================== */
        .btn {
            border-radius: 5%;
            padding: 8px 16px;
            transition: all 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .form-control {
            border-radius: 5%;
        }

        /* ====================================================
           STYLE BUKTI PEMBAYARAN
           ==================================================== */
        .proof-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .proof-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
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
                        <a class="nav-link text-white fw-semibold" href="borrower_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold" href="car_list.php">Daftar Mobil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold active" href="borrower_rentals.php">Riwayat Sewa</a>
                    </li>
                </ul>
                <!-- Tombol logout sebelah kanan -->
                <div class="d-flex">
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt"></i> Logout
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
            <h2 class="mb-4">Riwayat Sewa</h2>
            
            <!-- Pesan status -->
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?> mb-4">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 'payment_uploaded'): ?>
                <div class="alert alert-success mb-4">
                    <i class="fas fa-check-circle"></i> Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.
                </div>
            <?php endif; ?>
            
            <?php if (empty($rentals)): ?>
                <div class="card">
                    <div class="card-body p-5 text-center">
                        <h4 class="text-muted mb-3">Belum ada riwayat sewa</h4>
                        <p class="mb-4">Anda belum memiliki transaksi sewa. Silakan sewa mobil terlebih dahulu.</p>
                        <a href="car_list.php" class="btn btn-primary">Lihat Daftar Mobil</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Daftar Riwayat Sewa -->
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Mobil</th>
                            <th>Jenis</th>
                            <th>Tanggal Sewa</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rentals as $i => $rental): ?>
                        <tr>
                            <td><?php echo $i+1; ?></td>
                            <td>
                                <?php if (!empty($rental['image'])): ?>
                                    <img src="../../public/img/<?php echo htmlspecialchars($rental['image']); ?>" alt="" style="width:50px;height:32px;object-fit:cover;border-radius:4px;margin-right:8px;">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($rental['make'] . ' ' . $rental['model']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($rental['type_name']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($rental['rental_date'])); ?></td>
                            <td>
                                <?php if ($rental['payment_status'] == 'approved'): ?>
                                    <span class="badge bg-success">Disetujui</span>
                                    <a href="generate_receipt.php?id=<?php echo $rental['id']; ?>" target="_blank" class="btn btn-sm btn-light ms-2">
                                        <i class="fas fa-file-pdf"></i> Cetak Kwitansi
                                    </a>
                                <?php elseif ($rental['payment_status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                <?php elseif ($rental['payment_status'] == 'rejected'): ?>
                                    <span class="badge bg-danger">Ditolak</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum Bayar</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Modal untuk melihat bukti pembayaran -->
        <div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bukti Pembayaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-3">
                            <h6 id="carDetails" class="mb-0"></h6>
                            <p id="rentalDetails" class="text-muted small"></p>
                            <span id="statusBadge"></span>
                        </div>
                        <img src="" id="proofImage" class="img-fluid" style="max-height: 80vh;" alt="Bukti Pembayaran">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <a href="#" id="downloadPdfBtn" class="btn btn-success d-none">
                            <i class="fas fa-download"></i> Unduh Bukti (PDF)
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
    
    <!-- Script untuk preview gambar dan modal bukti pembayaran -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview gambar sebelum upload
        const fileInputs = document.querySelectorAll('.proof-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                const preview = this.parentElement.querySelector('.proof-preview');
                
                if (file) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(file);
                } else {
                    preview.style.display = 'none';
                }
            });
        });
        
        // Tampilkan gambar bukti pembayaran di modal
        const viewButtons = document.querySelectorAll('.view-proof');
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const imgSrc = this.getAttribute('data-img');
                const id = this.getAttribute('data-id');
                const make = this.getAttribute('data-make');
                const model = this.getAttribute('data-model');
                const rentalDate = this.getAttribute('data-rental-date');
                const returnDate = this.getAttribute('data-return-date');
                const status = this.getAttribute('data-status');
                
                // Set detail mobil dan tanggal sewa
                document.getElementById('carDetails').textContent = make + ' ' + model;
                document.getElementById('rentalDetails').textContent = 'Tanggal Sewa: ' + rentalDate + ' | Tanggal Kembali: ' + returnDate;
                
                // Set status badge
                const statusBadge = document.getElementById('statusBadge');
                if (status === 'approved') {
                    statusBadge.innerHTML = '<span class="badge bg-success">Pembayaran Disetujui</span>';
                    document.getElementById('downloadPdfBtn').classList.remove('d-none');
                    document.getElementById('downloadPdfBtn').href = 'generate_receipt.php?id=' + id;
                } else if (status === 'rejected') {
                    statusBadge.innerHTML = '<span class="badge bg-danger">Pembayaran Ditolak</span>';
                    document.getElementById('downloadPdfBtn').classList.add('d-none');
                } else if (status === 'pending') {
                    statusBadge.innerHTML = '<span class="badge bg-warning text-dark">Menunggu Konfirmasi</span>';
                    document.getElementById('downloadPdfBtn').classList.add('d-none');
                } else {
                    statusBadge.innerHTML = '<span class="badge bg-secondary">Belum Dibayar</span>';
                    document.getElementById('downloadPdfBtn').classList.add('d-none');
                }
                
                document.getElementById('proofImage').src = imgSrc;
            });
        });
    });
    </script>
</body>
</html>