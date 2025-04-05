<?php
class Database {
    private $host = "localhost";
    private $db_name = "skillswap";
    private $username = "root";
    private $password = "";
    private static $instance = null;

    private function __construct() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        try {
            $conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            $conn->set_charset("utf8mb4");
            return $conn;
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
}
?>
