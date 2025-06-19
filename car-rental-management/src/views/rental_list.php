<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
include_once '../config/db.php';

$db = new Database();
$conn = $db->getConnection();

// Proses persetujuan atau penolakan pembayaran
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $stmt = $conn->prepare("UPDATE rentals SET payment_status = 'approved' WHERE id = ?");
    $stmt->execute([$_GET['approve']]);
    header('Location: rental_list.php?status=approved');
    exit();
}

if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $stmt = $conn->prepare("UPDATE rentals SET payment_status = 'rejected' WHERE id = ?");
    $stmt->execute([$_GET['reject']]);
    
    // Juga kembalikan mobil menjadi tersedia
    $updateCar = $conn->prepare("UPDATE cars c
                                JOIN rentals r ON c.id = r.car_id
                                SET c.availability = 1
                                WHERE r.id = ?");
    $updateCar->execute([$_GET['reject']]);
    
    header('Location: rental_list.php?status=rejected');
    exit();
}

// Ambil data sewa dengan filter jika ada
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$query = "SELECT r.id, u.username, c.make, c.model, c.image, r.rental_date, r.return_date, 
                 r.payment_proof, r.payment_status, r.payment_date 
          FROM rentals r
          JOIN users u ON r.user_id = u.id
          JOIN cars c ON r.car_id = c.id";

if ($filter === 'pending') {
    $query .= " WHERE r.payment_status = 'pending' OR r.payment_status IS NULL";
} elseif ($filter === 'approved') {
    $query .= " WHERE r.payment_status = 'approved'";
} elseif ($filter === 'rejected') {
    $query .= " WHERE r.payment_status = 'rejected'";
}

$query .= " ORDER BY r.rental_date DESC";

$stmt = $conn->query($query);
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Sewa Mobil</title>
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
           STYLE UNTUK TABEL
           ==================================================== */
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .table-dark {
            background: linear-gradient(90deg, #343a40 60%, #212529 100%);
        }
        
        /* Cell style */
        .table td, .table th {
            vertical-align: middle;
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
           STYLE THUMBNAIL
           ==================================================== */
        .car-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .payment-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .payment-thumb:hover {
            transform: scale(1.1);
        }

        /* ====================================================
           STYLE MODAL
           ==================================================== */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            border-bottom: 1px solid #f1f1f1;
            padding: 15px 20px;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            border-top: 1px solid #f1f1f1;
            padding: 15px 20px;
        }
        
        /* ====================================================
           STYLE UNTUK TOMBOL
           ==================================================== */
        .btn {
            border-radius: 5%;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* ====================================================
           STYLE CARD
           ==================================================== */
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 16px;
            border: none;
        }

        /* ====================================================
           STYLE ALERT
           ==================================================== */
        .alert-success {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
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
           STYLE FILTER BUTTONS
           ==================================================== */
        .filter-buttons {
            margin-bottom: 1.5rem;
        }
        
        .filter-buttons .btn {
            border-radius: 5%;
            margin-right: 0.5rem;
            transition: all 0.2s;
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
                    <h2 class="mb-4">Daftar Sewa Mobil</h2>
                    
                    <!-- Notifikasi status -->
                    <?php if (isset($_GET['status'])): ?>
                        <?php if ($_GET['status'] === 'approved'): ?>
                            <div class="alert alert-success mb-4">Pembayaran berhasil disetujui!</div>
                        <?php elseif ($_GET['status'] === 'rejected'): ?>
                            <div class="alert alert-warning mb-4">Pembayaran telah ditolak!</div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Filter Buttons -->
                    <div class="filter-buttons">
                        <a href="rental_list.php" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Semua</a>
                        <a href="rental_list.php?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Menunggu</a>
                        <a href="rental_list.php?filter=approved" class="btn <?php echo $filter === 'approved' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Disetujui</a>
                        <a href="rental_list.php?filter=rejected" class="btn <?php echo $filter === 'rejected' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Ditolak</a>
                    </div>
                    
                    <?php if (empty($rentals)): ?>
                        <div class="text-center p-5">
                            <h4>Tidak ada data sewa yang tersedia</h4>
                            <p class="text-muted">Tidak ada data sewa yang sesuai dengan filter yang dipilih</p>
                        </div>
                    <?php else: ?>
                        <!-- Tabel Daftar Sewa -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Mobil</th>
                                        <th>Peminjam</th>
                                        <th>Tanggal Sewa</th>
                                        <th>Tanggal Kembali</th>
                                        <th>Bukti Bayar</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    <?php foreach ($rentals as $rental): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <?php if (!empty($rental['image'])): ?>
                                                <img src="../../public/img/<?php echo $rental['image']; ?>" class="car-thumb" alt="<?php echo $rental['make'] . ' ' . $rental['model']; ?>">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/60x60?text=No+Image" class="car-thumb" alt="No Image">
                                            <?php endif; ?>
                                            <span class="ms-2"><?php echo $rental['make'] . ' ' . $rental['model']; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($rental['username']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($rental['rental_date'])); ?></td>
                                        <td><?php echo date('d M Y', strtotime($rental['return_date'])); ?></td>
                                        <td>
                                            <?php if (!empty($rental['payment_proof'])): ?>
                                                <img src="../../public/uploads/<?php echo $rental['payment_proof']; ?>" 
                                                     class="payment-thumb" 
                                                     alt="Bukti Pembayaran"
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#paymentModal" 
                                                     data-payment="<?php echo $rental['payment_proof']; ?>"
                                                     data-rental="<?php echo $rental['id']; ?>">
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Belum Ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (empty($rental['payment_status']) || $rental['payment_status'] == 'pending'): ?>
                                                <span class="badge badge-pending">Menunggu</span>
                                            <?php elseif ($rental['payment_status'] == 'approved'): ?>
                                                <span class="badge badge-approved">Disetujui</span>
                                            <?php elseif ($rental['payment_status'] == 'rejected'): ?>
                                                <span class="badge badge-rejected">Ditolak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($rental['payment_proof']) && (empty($rental['payment_status']) || $rental['payment_status'] == 'pending')): ?>
                                                <a href="rental_list.php?approve=<?php echo $rental['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Setujui pembayaran ini?')">
                                                    <i class="bi bi-check-lg"></i> Setujui
                                                </a>
                                                <a href="rental_list.php?reject=<?php echo $rental['id']; ?>" class="btn btn-danger btn-sm mt-1" onclick="return confirm('Tolak pembayaran ini?')">
                                                    <i class="bi bi-x-lg"></i> Tolak
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Tombol Kembali -->
                    <div class="mt-4">
                        <a href="admin_dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal untuk melihat bukti pembayaran -->
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bukti Pembayaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="" id="paymentImage" class="img-fluid" style="max-height: 70vh;">
                    </div>
                    <div class="modal-footer">
                        <a href="#" id="downloadBtn" class="btn btn-info" download>
                            <i class="bi bi-download"></i> Download
                        </a>
                        <a href="#" id="approveBtn" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Setujui
                        </a>
                        <a href="#" id="rejectBtn" class="btn btn-danger">
                            <i class="bi bi-x-lg"></i> Tolak
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
                    <a href="https://wa.me/6281234567890" class="text-white" target="_blank">
                        <i class="bi bi-whatsapp"></i> +62 812-3456-7890
                    </a>
                </div>
                <!-- Kolom jam operasional -->
                <div class="col-md-3 text-md-start text-center mb-3 mb-md-0">
                    <strong>Jam Operasional:</strong><br>
                    <div class="footer-address">
                        Senin - Jumat: 09.00 - 17.00 WIB<br>
                        Sabtu: 09.00 - 14.00 WIB<br>
                        Minggu: Tutup
                    </div>
                </div>
                <!-- Kolom link cepat -->
                <div class="col-md-4 text-md-start text-center">
                    <strong>Link Cepat:</strong><br>
                    <a href="admin_dashboard.php" class="text-white">
                        <i class="bi bi-arrow-right"></i> Dashboard Admin
                    </a><br>
                    <a href="rental_list.php" class="text-white">
                        <i class="bi bi-arrow-right"></i> Daftar Sewa Mobil
                    </a><br>
                    <a href="logout.php" class="text-white">
                        <i class="bi bi-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ====================================================
         SCRIPT
         ==================================================== -->
    <script src="../../public/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        // ====================================================
        // SCRIPT UNTUK MODAL BUKTI PEMBAYARAN
        // ==================================================== //
        var paymentModal = document.getElementById('paymentModal');
        paymentModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Tombol yang diklik
            var paymentImage = button.getAttribute('data-payment'); // Ambil data-payment dari tombol
            var rentalId = button.getAttribute('data-rental'); // Ambil data-rental dari tombol
            
            var modalImage = document.getElementById('paymentImage');
            modalImage.src = '../../public/uploads/' + paymentImage; // Set src img modal
            
            // Set link download
            var downloadBtn = document.getElementById('downloadBtn');
            downloadBtn.href = '../../public/uploads/' + paymentImage;
            
            // Set aksi tombol setujui dan tolak
            var approveBtn = document.getElementById('approveBtn');
            var rejectBtn = document.getElementById('rejectBtn');
            
            approveBtn.onclick = function() {
                window.location.href = 'rental_list.php?approve=' + rentalId;
            }
            
            rejectBtn.onclick = function() {
                window.location.href = 'rental_list.php?reject=' + rentalId;
            }
        });
    </script>
</body>
</html>