<?php
class Skills {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($user_id, $skill_name, $type, $proficiency) {
        try {
            $skill_name = filter_var($skill_name, FILTER_SANITIZE_STRING);
            if (!in_array($type, ['offer', 'want']) || !is_numeric($proficiency) || $proficiency < 0 || $proficiency > 100) {
                throw new Exception("Invalid skill parameters");
            }

            $stmt = $this->db->prepare("INSERT INTO skills (user_id, skill_name, type, proficiency) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $user_id, $skill_name, $type, $proficiency);
            $stmt->execute();
            return json_encode(['success' => true, 'skill_id' => $this->db->insert_id]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(400);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getUserSkills($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM skills WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return json_encode($result);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $skills = new Skills();
    echo $skills->create($data['user_id'], $data['skill_name'], $data['type'], $data['proficiency']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $skills = new Skills();
    echo $skills->getUserSkills($_GET['user_id']);
}
?>
