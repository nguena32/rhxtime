<?php
// Load env directly
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            putenv(trim($name) . '=' . trim($value));
        }
    }
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'rhtimes';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $queries = [
        "ALTER TABLE pointages ADD INDEX idx_ptg_user_date (user_id, heure_pointage)",
        "ALTER TABLE pointages ADD INDEX idx_ptg_entreprise_date (entreprise_id, heure_pointage)",
        "ALTER TABLE users ADD INDEX idx_user_active_ent (entreprise_id, is_active)"
    ];

    foreach ($queries as $sql) {
        try {
            $pdo->exec($sql);
            echo "Succès : $sql\n";
        } catch (PDOException $e) {
            echo "Erreur sur : $sql -> " . $e->getMessage() . "\n";
        }
    }
    echo "Optimisation terminée.\n";
} catch (Exception $e) {
    echo "Erreur fatale : " . $e->getMessage() . "\n";
}

