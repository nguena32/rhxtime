<?php
// models/Message.php
class Message {
    private $pdo;
    public $entreprise_id;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->entreprise_id = $_SESSION['entreprise_id'] ?? null;
    }

    public function sendMessage($user_id, $direction, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO messages (entreprise_id, user_id, direction, content) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$this->entreprise_id, $user_id, $direction, mb_substr(trim($content), 0, 255)]);
    }

    public function getConversation($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE entreprise_id = ? AND user_id = ? ORDER BY created_at ASC");
        $stmt->execute([$this->entreprise_id, $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNewMessages($user_id, $last_id = 0) {
        $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE entreprise_id = ? AND user_id = ? AND id > ? ORDER BY created_at ASC");
        $stmt->execute([$this->entreprise_id, $user_id, $last_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastId($user_id) {
        $stmt = $this->pdo->prepare("SELECT COALESCE(MAX(id), 0) FROM messages WHERE entreprise_id = ? AND user_id = ?");
        $stmt->execute([$this->entreprise_id, $user_id]);
        return (int)$stmt->fetchColumn();
    }

    public function getLastMessagesPerUser() {
        $sql = "SELECT m.*, u.nom, u.prenom 
                FROM messages m 
                INNER JOIN (
                    SELECT user_id, MAX(created_at) as max_date 
                    FROM messages 
                    WHERE entreprise_id = ? 
                    GROUP BY user_id
                ) latest ON m.user_id = latest.user_id AND m.created_at = latest.max_date
                JOIN users u ON m.user_id = u.id
                ORDER BY m.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->entreprise_id]);
        return $stmt->fetchAll();
    }
    
    public function countUnreadAdmin() {
        $stmt = $this->pdo->prepare("SELECT count(*) FROM messages WHERE entreprise_id = ? AND direction = 'to_admin' AND is_read = 0");
        $stmt->execute([$this->entreprise_id]);
        return $stmt->fetchColumn();
    }
    
    public function markAsReadForAdmin($user_id) {
        $stmt = $this->pdo->prepare("UPDATE messages SET is_read = 1 WHERE entreprise_id = ? AND user_id = ? AND direction = 'to_admin'");
        return $stmt->execute([$this->entreprise_id, $user_id]);
    }

    public function countUnreadEmployee($user_id) {
        $stmt = $this->pdo->prepare("SELECT count(*) FROM messages WHERE entreprise_id = ? AND user_id = ? AND direction = 'to_employee' AND is_read = 0");
        $stmt->execute([$this->entreprise_id, $user_id]);
        return $stmt->fetchColumn();
    }

    public function markAsReadForEmployee($user_id) {
        $stmt = $this->pdo->prepare("UPDATE messages SET is_read = 1 WHERE entreprise_id = ? AND user_id = ? AND direction = 'to_employee'");
        return $stmt->execute([$this->entreprise_id, $user_id]);
    }
}
?>
