<?php
// models/User.php
class User {
    private $pdo;
    public $entreprise_id;
    private $restricted_lieu_id;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (isset($_SESSION['entreprise_id'])) {
            $this->entreprise_id = $_SESSION['entreprise_id'];
        }
        $this->restricted_lieu_id = $_SESSION['admin_lieu_id'] ?? null;
    }

    public function countAll($search = '') {
        $sql = "SELECT count(*) FROM users WHERE entreprise_id = ?";
        $params = [$this->entreprise_id];
        if($this->restricted_lieu_id) {
            $sql .= " AND lieu_id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        if(!empty($search)) {
            $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR telephone LIKE ? OR email LIKE ?)";
            $searchWildcard = "%" . $search . "%";
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function countActive() {
        $sql = "SELECT count(*) FROM users WHERE is_active=1 AND entreprise_id = ?";
        $params = [$this->entreprise_id];
        if($this->restricted_lieu_id) {
            $sql .= " AND lieu_id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getAllBasic() {
        $sql = "SELECT id, nom, prenom FROM users WHERE entreprise_id = ? ";
        $params = [$this->entreprise_id];
        if($this->restricted_lieu_id) {
            $sql .= " AND lieu_id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        $sql .= " ORDER BY nom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAllWithLieu($limit = 50, $offset = 0, $search = '') {
        $sql = "SELECT u.*, l.nom_lieu FROM users u LEFT JOIN lieux l ON u.lieu_id = l.id WHERE u.entreprise_id = ?";
        $params = [$this->entreprise_id];
        if($this->restricted_lieu_id) {
            $sql .= " AND u.lieu_id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        if(!empty($search)) {
            $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.telephone LIKE ? OR u.email LIKE ?)";
            $searchWildcard = "%" . $search . "%";
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
        }
        $sql .= " ORDER BY u.nom LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByEmailOrPhone($identifiant) {
        // Utilisé pour le login globalement, ne pas filtrer par entreprise_id ici
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? OR telephone = ?");
        $stmt->execute([$identifiant, $identifiant]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? AND entreprise_id = ?");
        $stmt->execute([$id, $this->entreprise_id]);
        return $stmt->fetch();
    }

    public function updateDeviceId($id, $deviceId) {
        $stmt = $this->pdo->prepare("UPDATE users SET device_id = ? WHERE id = ? AND entreprise_id = ?");
        return $stmt->execute([$deviceId, $id, $this->entreprise_id]);
    }

    public function clearDeviceId($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET device_id = NULL WHERE id = ? AND entreprise_id = ?");
        return $stmt->execute([$id, $this->entreprise_id]);
    }

    public function create($nom, $prenom, $fonction, $tel, $email, $pass, $lieu_id, $salaire = 0) {
        $sql = "INSERT INTO users (entreprise_id, nom, prenom, fonction, telephone, email, password, lieu_id, is_active, salaire_mensuel, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$this->entreprise_id, $nom, $prenom, $fonction, $tel, $email, $pass, $lieu_id, $salaire]);
    }

    public function update($id, $nom, $prenom, $fonction, $tel, $email, $lieu_id, $salaire = 0) {
        $sql = "UPDATE users SET nom=?, prenom=?, fonction=?, telephone=?, email=?, lieu_id=?, salaire_mensuel=? WHERE id=? AND entreprise_id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nom, $prenom, $fonction, $tel, $email, $lieu_id, $salaire, $id, $this->entreprise_id]);
    }

    public function updatePassword($id, $newHash) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND entreprise_id = ?");
        return $stmt->execute([$newHash, $id, $this->entreprise_id]);
    }

    public function toggleActive($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND entreprise_id = ?");
        return $stmt->execute([$id, $this->entreprise_id]);
    }
}
