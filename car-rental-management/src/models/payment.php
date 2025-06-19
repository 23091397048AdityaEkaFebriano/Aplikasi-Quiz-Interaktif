<?php
class Payment {
    private $conn;
    private $table_name = "payments";

    public $id;
    public $rental_id;
    public $amount;
    public $payment_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (rental_id, amount, payment_date) VALUES (:rental_id, :amount, :payment_date)";
        
        $stmt = $this->conn->prepare($query);

        $this->rental_id = htmlspecialchars(strip_tags($this->rental_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->payment_date = htmlspecialchars(strip_tags($this->payment_date));

        $stmt->bindParam(':rental_id', $this->rental_id);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':payment_date', $this->payment_date);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE rental_id = :rental_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rental_id', $this->rental_id);
        $stmt->execute();

        return $stmt;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>