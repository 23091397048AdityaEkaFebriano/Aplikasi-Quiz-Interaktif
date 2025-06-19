<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'borrower') {
    header('Location: login.php');
    exit();
}

// Check if id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid request');
}

include_once '../config/db.php';
$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];
$rental_id = $_GET['id'];

// Verify rental belongs to this user and is approved
// PERBAIKAN: Hilangkan c.rental_rate dari query
$stmt = $conn->prepare("SELECT r.*, c.make, c.model, c.year, t.type_name, u.username 
                        FROM rentals r 
                        JOIN cars c ON r.car_id = c.id 
                        JOIN vehicle_types t ON c.type_id = t.id
                        JOIN users u ON r.user_id = u.id
                        WHERE r.id = ? AND r.user_id = ? AND r.payment_status = 'approved'");
$stmt->execute([$rental_id, $user_id]);
$rental = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rental) {
    die('Rental not found or not approved');
}

// Calculate rental duration
$startDate = new DateTime($rental['rental_date']);
$endDate = new DateTime($rental['return_date']);
$interval = $startDate->diff($endDate);
$days = $interval->days > 0 ? $interval->days : 1; // Minimum 1 day

// Gunakan harga default karena kolom rental_rate tidak ada
$price_per_day = 500000; // Nilai default 500.000/hari
$total_price = $price_per_day * $days;

// Generate invoice number
$invoice_number = 'INV-' . date('Ymd') . '-' . $rental_id;

// Generate receipt date
$receipt_date = date('Y-m-d');

// Cek apakah library FPDF tersedia
if (!file_exists('../../vendor/fpdf/fpdf.php')) {
    // Jika tidak tersedia, tampilkan pesan error
    echo '<div style="padding: 20px; background-color: #f8d7da; border-radius: 5px; color: #721c24; margin: 20px;">';
    echo '<h3>Error:</h3>';
    echo '<p>Library FPDF tidak ditemukan. Silakan download dan install FPDF terlebih dahulu.</p>';
    echo '<p>Langkah-langkah instalasi FPDF:</p>';
    echo '<ol>';
    echo '<li>Download FPDF dari <a href="http://www.fpdf.org/" target="_blank">http://www.fpdf.org/</a></li>';
    echo '<li>Ekstrak file dan buat direktori <code>vendor/fpdf</code> di root proyek</li>';
    echo '<li>Salin file <code>fpdf.php</code> ke direktori tersebut</li>';
    echo '</ol>';
    echo '</div>';
    exit;
}

// Require FPDF library
require_once __DIR__ . '/../../vendor/fpdf/fpdf.php';

// Create new PDF instance
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo - jika logo tidak tersedia, jangan tampilkan
        if (file_exists('../../public/img/logo.png')) {
            $this->Image('../../public/img/logo.png', 10, 10, 30);
        }
        
        // Add line under header
        $this->SetDrawColor(0, 123, 255);
        $this->SetLineWidth(0.5);
        $this->Line(10, 40, 200, 40);
    }
    
    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Halaman '.$this->PageNo().' dari {nb}', 0, 0, 'C');
    }
}

// Instantiation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Set document information
$pdf->SetTitle('Bukti Pembayaran Rental Mobil');
$pdf->SetAuthor('Rental Mobil');
$pdf->SetCreator('Rental Mobil System');

// Add company information
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'RENTAL MOBIL', 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Jl. Raya Rental Mobil No. 123, Jakarta', 0, 1, 'R');
$pdf->Cell(0, 6, 'Telp: +62 812-3456-7890', 0, 1, 'R');
$pdf->Cell(0, 6, 'Email: info@rentalmobil.com', 0, 1, 'R');
$pdf->Ln(10);

// Add receipt title and number
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'BUKTI PEMBAYARAN', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'No. ' . $invoice_number, 0, 1, 'C');
$pdf->Ln(5);

// Add customer info
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(45, 8, 'Informasi Penyewa:', 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, $rental['username'], 0, 1);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(45, 8, 'Tanggal:', 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, date('d F Y', strtotime($receipt_date)), 0, 1);
$pdf->Ln(5);

// Add car info
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Informasi Mobil', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(45, 8, 'Merk & Model:', 0);
$pdf->Cell(0, 8, $rental['make'] . ' ' . $rental['model'] . ' (' . $rental['year'] . ')', 0, 1);
$pdf->Cell(45, 8, 'Tipe:', 0);
$pdf->Cell(0, 8, $rental['type_name'], 0, 1);
$pdf->Ln(5);

// Add rental details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Detail Sewa', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(45, 8, 'Tanggal Mulai:', 0);
$pdf->Cell(0, 8, date('d F Y H:i', strtotime($rental['rental_date'])), 0, 1);
$pdf->Cell(45, 8, 'Tanggal Kembali:', 0);
$pdf->Cell(0, 8, date('d F Y H:i', strtotime($rental['return_date'])), 0, 1);
$pdf->Cell(45, 8, 'Durasi:', 0);
$pdf->Cell(0, 8, $days . ' hari', 0, 1);
$pdf->Ln(5);

// Add payment details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Detail Pembayaran', 0, 1);

// Create payment table header
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(90, 8, 'Deskripsi', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Jumlah', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Harga/Hari', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Total', 1, 1, 'C', true);

// Add rental item
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(90, 8, $rental['make'] . ' ' . $rental['model'], 1);
$pdf->Cell(30, 8, $days . ' hari', 1, 0, 'C');
$pdf->Cell(30, 8, 'Rp ' . number_format($price_per_day, 0, ',', '.'), 1, 0, 'R');
$pdf->Cell(40, 8, 'Rp ' . number_format($price_per_day * $days, 0, ',', '.'), 1, 1, 'R');

// Add total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(150, 8, 'Total', 1, 0, 'R', true);
$pdf->Cell(40, 8, 'Rp ' . number_format($total_price, 0, ',', '.'), 1, 1, 'R', true);
$pdf->Ln(5);

// Add payment status
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 153, 0); // Green color
$pdf->Cell(0, 10, 'LUNAS', 0, 1, 'C');
$pdf->SetTextColor(0); // Reset to black
$pdf->Ln(5);

// Add thank you note
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Terima kasih telah menyewa di Rental Mobil.', 0, 1, 'C');
$pdf->Cell(0, 8, 'Bukti pembayaran ini adalah sah dan merupakan bukti pembayaran yang sah.', 0, 1, 'C');
$pdf->Ln(5);

// Add signature section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 8, 'Penyewa', 0, 0, 'C');
$pdf->Cell(95, 8, 'Admin', 0, 1, 'C');

$pdf->Ln(20); // Space for signature

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(95, 8, '(' . $rental['username'] . ')', 'T', 0, 'C');
$pdf->Cell(95, 8, '(Admin Rental Mobil)', 'T', 1, 'C');

// Output the PDF
$pdf->Output('Bukti_Pembayaran_' . $invoice_number . '.pdf', 'I');