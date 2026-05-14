<?php
// models/Lieu.php
class Lieu {
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

    public function countAll() {
        $sql = "SELECT count(*) FROM lieux WHERE entreprise_id = ?";
        $params = [$this->entreprise_id];
        if($this->restricted_lieu_id) {
            $sql .= " AND id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getAll() {
        $sql = "SELECT * FROM lieux WHERE entreprise_id = ?";
        $params = [$this->entreprise_id];
        if($this->restricted_lieu_id) {
            $sql .= " AND id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        $sql .= " ORDER BY nom_lieu";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $sql = "SELECT * FROM lieux WHERE id = ? AND entreprise_id = ?";
        $params = [$id, $this->entreprise_id];
        if($this->restricted_lieu_id) {
            $sql .= " AND id = ?";
            $params[] = $this->restricted_lieu_id;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function findByToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM lieux WHERE qr_token = ? AND entreprise_id = ?");
        $stmt->execute([$token, $this->entreprise_id]);
        return $stmt->fetch();
    }

    public function create($nom, $lat, $lng, $rayon, $token, $tolerance = 0, $methode = 'QR_CODE') {
        if (empty($this->entreprise_id)) {
            error_log("Lieu::create - Erreur : entreprise_id manquant.");
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO lieux (entreprise_id, nom_lieu, gps_lat, gps_lng, rayon_metre, qr_token, tolerance_retard, methode_pointage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->entreprise_id, $nom, $lat, $lng, $rayon, $token, $tolerance, $methode]);
            
            $lieu_id = $this->pdo->lastInsertId();

            if (!$lieu_id) {
                throw new Exception("Erreur lors de la récupération de l'ID du lieu créé.");
            }

            // Création automatique du planning par défaut (Lundi-Vendredi 08:00-18:00, Weekend Repos)
            $stmtPlan = $this->pdo->prepare("INSERT INTO planning_lieux (entreprise_id, lieu_id, jour_semaine, heure_debut, heure_fin, is_repos) VALUES (?, ?, ?, ?, ?, ?)");
            
            for ($i = 1; $i <= 7; $i++) {
                $isRepos = ($i >= 6) ? 1 : 0; // 6 = Samedi, 7 = Dimanche
                $hDebut  = ($isRepos) ? '00:00' : '08:00';
                $hFin    = ($isRepos) ? '00:00' : '18:00';
                
                $stmtPlan->execute([
                    $this->entreprise_id,
                    $lieu_id,
                    $i,
                    $hDebut,
                    $hFin,
                    $isRepos
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("Erreur lors de la création du lieu et du planning : " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $nom, $lat, $lng, $rayon, $tolerance = 0, $methode = 'QR_CODE') {
        $sql = "UPDATE lieux SET nom_lieu=?, gps_lat=?, gps_lng=?, rayon_metre=?, tolerance_retard=?, methode_pointage=? WHERE id=? AND entreprise_id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nom, $lat, $lng, $rayon, $tolerance, $methode, $id, $this->entreprise_id]);
    }

    public function delete($id) {
        $l = $this->findById($id);
        if(!$l) return false;

        $this->pdo->prepare("DELETE FROM pointages WHERE lieu_id = ? AND entreprise_id = ?")->execute([$id, $this->entreprise_id]);
        $this->pdo->prepare("DELETE FROM planning_lieux WHERE lieu_id = ? AND entreprise_id = ?")->execute([$id, $this->entreprise_id]);
        $this->pdo->prepare("UPDATE users SET lieu_id = NULL WHERE lieu_id = ? AND entreprise_id = ?")->execute([$id, $this->entreprise_id]);

        $stmt = $this->pdo->prepare("DELETE FROM lieux WHERE id = ? AND entreprise_id = ?");
        return $stmt->execute([$id, $this->entreprise_id]);
    }

    public function getPlanning($lieu_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM planning_lieux WHERE lieu_id = ? AND entreprise_id = ? ORDER BY jour_semaine");
        $stmt->execute([$lieu_id, $this->entreprise_id]);
        return $stmt->fetchAll();
    }

    public function updatePlanning($lieu_id, $planning) {
        $l = $this->findById($lieu_id);
        if(!$l) return false;

        $this->pdo->prepare("DELETE FROM planning_lieux WHERE lieu_id = ? AND entreprise_id = ?")->execute([$lieu_id, $this->entreprise_id]);
        $stmt = $this->pdo->prepare("INSERT INTO planning_lieux (entreprise_id, lieu_id, jour_semaine, heure_debut, heure_fin, is_repos) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($planning as $p) {
            $stmt->execute([$this->entreprise_id, $lieu_id, $p['jour'], $p['debut'], $p['fin'], $p['repos']]);
        }
    }
}
