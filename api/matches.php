<?php
class Matches {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find($userSUR_id) {
        try {
            $query = "SELECT u.id, u.username, s.skill_name, s.proficiency
                     FROM users u
                     JOIN skills s ON s.user_id = u.id
                     WHERE s.type = 'offer'
                     AND s.skill_name IN (
                         SELECT skill_name 
                         FROM skills 
                         WHERE user_id = ? AND type = 'want'
                     )
                     AND u.id != ?
                     ORDER BY s.proficiency DESC
                     LIMIT 10";
                     
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return json_encode($result);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $matches = new Matches();
    echo $matches->find($_GET['user_id']);
}
?>
