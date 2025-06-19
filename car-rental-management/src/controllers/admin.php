<?php
session_start();
require_once '../config/db.php';

class AdminController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function addUser($username, $password, $role) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }

    public function manageCars($action, $carData = null) {
        if ($action == 'add') {
            $query = "INSERT INTO cars (make, model, year, type_id, availability) VALUES (:make, :model, :year, :type_id, :availability)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':make', $carData['make']);
            $stmt->bindParam(':model', $carData['model']);
            $stmt->bindParam(':year', $carData['year']);
            $stmt->bindParam(':type_id', $carData['type_id']);
            $stmt->bindParam(':availability', $carData['availability']);
            return $stmt->execute();
        }
        // Additional actions like update and delete can be implemented here
    }

    public function viewUsers() {
        $query = "SELECT * FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function viewCars() {
        $query = "SELECT * FROM cars";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$adminController = new AdminController();
?>