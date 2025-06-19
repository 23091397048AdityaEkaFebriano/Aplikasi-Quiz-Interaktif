<?php
// session_start();
require_once '../config/db.php';
require_once '../models/car.php';
require_once '../models/rental.php';

class BorrowerController {
    private $db;
    private $carModel;
    private $rentalModel;

    public function __construct() {
        $this->db = new Database();
        $this->carModel = new Car($this->db->getConnection());
        $this->rentalModel = new Rental($this->db->getConnection());
    }

    // Tambahkan method ini jika belum ada di class Car
    public function getAvailableCars() {
        $stmt = $this->carModel->read();
        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Ambil nama tipe kendaraan
        foreach ($cars as &$car) {
            $typeId = $car['type_id'];
            $typeStmt = $this->db->getConnection()->prepare("SELECT type_name FROM vehicle_types WHERE id = ?");
            $typeStmt->execute([$typeId]);
            $type = $typeStmt->fetch(PDO::FETCH_ASSOC);
            $car['type_name'] = $type ? $type['type_name'] : '';
        }
        return $cars;
    }

    public function viewAvailableCars() {
        $cars = $this->getAvailableCars();
        include '../views/car_list.php';
    }

    public function rentCar($carId) {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $rentalDate = date('Y-m-d H:i:s');
            $returnDate = date('Y-m-d H:i:s', strtotime('+7 days'));

            $this->rentalModel->createRental($userId, $carId, $rentalDate, $returnDate);

            // Update car availability
            $update = $this->db->getConnection()->prepare("UPDATE cars SET availability = 0 WHERE id = ?");
            $update->execute([$carId]);

            header('Location: ../views/borrower_dashboard.php?message=Car rented successfully');
        } else {
            header('Location: ../views/login.php');
        }
    }

    public function viewMyRentals() {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $rentals = $this->rentalModel->getRentalsByUserId($userId);
            include '../views/borrower_dashboard.php';
        } else {
            header('Location: ../views/login.php');
        }
    }
}

$borrowerController = new BorrowerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'rent') {
        $borrowerController->rentCar($_POST['car_id']);
    }
}
?>