<?php
class Rental {
    private $conn;
    private $table_name = "rentals";

    public $id;
    public $user_id;
    public $car_id;
    public $rental_date;
    public $return_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (user_id, car_id, rental_date, return_date) VALUES (:user_id, :car_id, :rental_date, :return_date)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':car_id', $this->car_id);
        $stmt->bindParam(':rental_date', $this->rental_date);
        $stmt->bindParam(':return_date', $this->return_date);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();

        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET return_date = :return_date WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':return_date', $this->return_date);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function createRental($user_id, $car_id, $rental_date, $return_date) {
        $query = "INSERT INTO rentals (user_id, car_id, rental_date, return_date) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $car_id, $rental_date, $return_date]);
    }

    public function getRentalsByUserId($user_id) {
        $query = "SELECT * FROM rentals WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>