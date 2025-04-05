<?php
class Progress {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function update($user_id, $skill_id, $progress_level) {
        try {
            $stmt = $this->db->prepare("INSERT INTO progress (user_id, skill_id, progress_level) 
                                      VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE progress_level = ?");
            $stmt->bind_param("iiii", $user_id, $skill_id, $progress_level, $progress_level);
            $stmt->execute();
            
            $badges = $this->checkBadges($user_id, $progress_level);
            return json_encode(['success' => true, 'badges' => $badges]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    private function checkBadges($user_id, $progress_level) {
        $badges = [];
        if ($progress_level >= 80) {
            $badges[] = "Expert";
        } elseif ($progress_level >= 50) {
            $badges[] = "Intermediate";
        }
        return $badges;
    }

    public function getProgress($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT p.*, s.skill_name 
                                      FROM progress p 
                                      JOIN skills s ON p.skill_id = s.id 
                                      WHERE p.user_id = ?");
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
    $progress = new Progress();
    echo $progress->update($data['user_id'], $data['skill_id'], $data['progress_level']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $progress = new Progress();
    echo $progress->getProgress($_GET['user_id']);
}
?>
