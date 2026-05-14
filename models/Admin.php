<?php
// models/Admin.php
class Admin {
    private $pdo;
    public $id;
    public $email;
    public $entreprise_id;
    public $role = 'owner';
    public $lieu_id;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (isset($_SESSION['entreprise_id'])) {
            $this->entreprise_id = $_SESSION['entreprise_id'];
        }
        if (isset($_SESSION['user_role'])) {
            $this->role = $_SESSION['user_role'] ?? 'owner';
        }
        if (isset($_SESSION['admin_lieu_id'])) {
            $this->lieu_id = $_SESSION['admin_lieu_id'];
        }
    }
    
    public function findByEmail($email) {
        // En phase de connexion, on cherche l'email de manière globale
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE id = ? AND (entreprise_id = ? OR entreprise_id IS NULL)");
        $stmt->execute([$id, $this->entreprise_id]);
        return $stmt->fetch();
    }
    
    public function updatePassword($id, $newHash) {
        $stmt = $this->pdo->prepare("UPDATE admins SET password = ? WHERE id = ? AND (entreprise_id = ? OR ? IS NULL)");
        // L'admin global (super-admin) a entreprise_id NULL, on permet l'update s'il est auto-édité ou filtré
        return $stmt->execute([$newHash, $id, $this->entreprise_id, $this->entreprise_id]);
    }

    public function create($email, $password, $role = 'manager', $lieu_id = null) {
        $sql = "INSERT INTO admins (entreprise_id, email, password, role, lieu_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        return $stmt->execute([$this->entreprise_id, $email, $hash, $role, $lieu_id]);
    }

    public function getAllByEntreprise() {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE entreprise_id = ? ORDER BY id ASC");
        $stmt->execute([$this->entreprise_id]);
        return $stmt->fetchAll();
    }

    public function delete($id) {
        // Un admin ne peut supprimer que les admins de SON entreprise
        $stmt = $this->pdo->prepare("DELETE FROM admins WHERE id = ? AND entreprise_id = ?");
        return $stmt->execute([$id, $this->entreprise_id]);
    }
}
