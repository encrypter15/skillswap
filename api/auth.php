<?php
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private $secret_key = "your_secret_key_32_chars_minimum";
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($email, $password) {
        try {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception("Invalid credentials");
            }

            $payload = [
                'iat' => time(),
                'exp' => time() + (60 * 60 * 24),
                'user_id' => $user['id'],
                'role' => $user['role'] ?? 'user'
            ];
            $token = JWT::encode($payload, $this->secret_key, 'HS256');
            return json_encode(['token' => $token, 'user' => $user]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(401);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(401);
            return false;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $auth = new Auth();
    echo $auth->login($data['email'], $data['password']);
}
?>
