<?php
class VehicleType {
    private $conn;
    private $table_name = "vehicle_types";

    public $id;
    public $type_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET type_name = :type_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type_name', $this->type_name);
        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT id, type_name FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET type_name = :type_name WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type_name', $this->type_name);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
?>