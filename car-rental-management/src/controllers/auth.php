<?php
session_start();
require_once '../config/db.php';

class Auth {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function register($username, $password, $role) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
        header("Location: ../views/login.php");
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getUserRole() {
        return $_SESSION['role'] ?? null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();

    // Register
    if (isset($_GET['action']) && $_GET['action'] === 'register') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        if ($auth->register($username, $password, $role)) {
            header("Location: ../views/login.php?success=register");
            exit();
        } else {
            header("Location: ../views/register.php?error=register");
            exit();
        }
    }

    // Login
    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['role'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        if ($auth->login($username, $password) && $_SESSION['role'] === $role) {
            if ($role === 'admin') {
                header("Location: ../views/admin_dashboard.php");
            } else {
                header("Location: ../views/borrower_dashboard.php");
            }
            exit();
        } else {
            header("Location: ../views/login.php?error=login");
            exit();
        }
    }
}

// Optional: handle logout via GET
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new Auth();
    $auth->logout();
}
?>