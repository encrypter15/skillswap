<?php
class Sessions {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function schedule($teacher_id, $learner_id, $skill_id, $scheduled_time) {
        try {
            $stmt = $this->db->prepare("INSERT INTO sessions (teacher_id, learner_id, skill_id, scheduled_time, status) 
                                      VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iiis", $teacher_id, $learner_id, $skill_id, $scheduled_time);
            $stmt->execute();
            return json_encode(['success' => true, 'session_id' => $this->db->insert_id]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function uploadMaterial($session_id, $file) {
        try {
            $target_dir = "uploads/session_$session_id/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($file["name"]);
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            if (!in_array($fileType, ['pdf', 'doc', 'docx', 'jpg', 'png'])) {
                throw new Exception("Invalid file type");
            }
            
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                return json_encode(['success' => true, 'file_path' => $target_file]);
            }
            throw new Exception("File upload failed");
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(400);
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sessions = new Sessions();
    if (isset($_FILES['material'])) {
        echo $sessions->uploadMaterial($data['session_id'], $_FILES['material']);
    } else {
        echo $sessions->schedule($data['teacher_id'], $data['learner_id'], $data['skill_id'], $data['scheduled_time']);
    }
}
?>
