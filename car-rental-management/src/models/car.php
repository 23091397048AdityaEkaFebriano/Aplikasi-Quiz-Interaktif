<?php
class Car {
    private $conn;
    private $table_name = "cars";

    public $id;
    public $make;
    public $model;
    public $year;
    public $type_id;
    public $availability;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (make, model, year, type_id, availability) VALUES (:make, :model, :year, :type_id, :availability)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':make', $this->make);
        $stmt->bindParam(':model', $this->model);
        $stmt->bindParam(':year', $this->year);
        $stmt->bindParam(':type_id', $this->type_id);
        $stmt->bindParam(':availability', $this->availability);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE availability = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET make = :make, model = :model, year = :year, type_id = :type_id, availability = :availability WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':make', $this->make);
        $stmt->bindParam(':model', $this->model);
        $stmt->bindParam(':year', $this->year);
        $stmt->bindParam(':type_id', $this->type_id);
        $stmt->bindParam(':availability', $this->availability);

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

    public function getAvailableCars() {
        $query = "SELECT c.*, vt.type_name FROM cars c LEFT JOIN vehicle_types vt ON c.type_id = vt.id WHERE c.availability = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCars() {
        $query = "SELECT c.*, t.type_name FROM cars c 
                  JOIN vehicle_types t ON c.type_id = t.id 
                  ORDER BY c.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchCars($keyword) {
        $keyword = "%$keyword%";
        $query = "SELECT c.*, t.type_name FROM cars c 
                  JOIN vehicle_types t ON c.type_id = t.id 
                  WHERE c.make LIKE ? OR c.model LIKE ?
                  ORDER BY c.availability DESC, c.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$keyword, $keyword]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>