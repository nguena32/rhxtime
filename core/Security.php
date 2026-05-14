<?php
// core/Security.php

class Security {
    public static function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            // Détection si c'est un appel API (soit via api.php, soit via index.php?page=api_...)
            $is_api = (strpos($_SERVER['REQUEST_URI'], 'api.php') !== false || (isset($_GET['page']) && strpos($_GET['page'], 'api_') === 0));

            if ($is_api) {
                http_response_code(403);
                header('Content-Type: application/json');
                die(json_encode(['ok' => false, 'error' => 'csrf', 'message' => 'Token CSRF invalide ou expiré, veuillez rafraîchir la page.']));
            }
            
            header("Location: index.php?page=login&error=" . urlencode("Session expirée, veuillez vous reconnecter."));
            exit;
        }
    }

    public static function enforceCsrfGuard() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            self::verifyCsrfToken($token);
        }
    }

    public static function verifyCsrfGet() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $token = $_GET['csrf_token'] ?? '';
            self::verifyCsrfToken($token);
        }
    }

    public static function generateExpirationSignature($entrepriseId, $expirationDate) {
        $salt = getenv('APP_SECURITY_SALT');
        if (!$salt) {
            die("Erreur Critique : Sel de sécurité manquant. Veuillez configurer APP_SECURITY_SALT.");
        }
        $data = $entrepriseId . ($expirationDate ?: 'null') . $salt;
        return hash_hmac('sha256', $data, $salt);
    }

    public static function html($string) {
        if ($string === null) return '';
        return htmlspecialchars(strval($string), ENT_QUOTES, 'UTF-8');
    }

    public static function logAnomaly($message, $entreprise_id = null) {
        // Rotation spatio-temporelle : Un fichier de log par jour évite les fichiers goulots d'étranglement de plusieurs Go
        $logFile = __DIR__ . '/../security_anomalies_' . date('Y-m-d') . '.log';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $date = date('Y-m-d H:i:s');
        $id = $entreprise_id ?? ($_SESSION['entreprise_id'] ?? 'SYS');
        $entry = "[$date] [IP: $ip] [Ent: $id] ANOMALY: $message" . PHP_EOL;
        // FILE_APPEND avec LOCK_EX supprime les conflits d'écritures concurrentes IOPS en forçant une queue atomique.
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
