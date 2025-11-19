<?php
// src/Controllers/AuthController.php
namespace Src\Controllers;

class AuthController {

    private $conn;

    public function __construct() {
        $this->conn = \Src\Database\Connection::make();
    }

    public function login() {
        session_start();
        $data = \Src\Core\Request::body();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            echo json_encode(['status' => 'error', 'message' => 'Email and password required']);
            return;
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE mail=?  LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || $user['password'] !== $password) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
            return;
        }

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['type'] = $user['type'];

        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Logged out']);
    }
}
