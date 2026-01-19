<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function signup($name, $email, $password) {
        $hashed_pwd = password_hash($password, PASSWORD_BCRYPT);
        $query = "INSERT INTO {$this->table} (name, email, password) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $name, $email, $hashed_pwd);
        return $stmt->execute();
    }

    public function login($email, $password) {
        $query = "SELECT id, name, email, password, is_blocked FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (!$user['is_blocked'] && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                return true;
            }
        }
        return false;
    }

    public function getUserById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateProfile($id, $name, $email) {
        $query = "UPDATE {$this->table} SET name = ?, email = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $name, $email, $id);
        return $stmt->execute();
    }

    public function changePassword($id, $old_pwd, $new_pwd) {
        $user = $this->getUserById($id);
        if (password_verify($old_pwd, $user['password'])) {
            $hashed = password_hash($new_pwd, PASSWORD_BCRYPT);
            $query = "UPDATE {$this->table} SET password = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $hashed, $id);
            return $stmt->execute();
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_destroy();
        return true;
    }
}
?>
