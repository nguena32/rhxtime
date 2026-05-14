<?php
// config/db.php

// 1. FORCER L'HEURE PHP (Cameroun/Douala)
date_default_timezone_set('Africa/Douala');

// Fonction pour charger un .env uniquement si les variables critiques ne sont pas déjà dans le système
$envFile = __DIR__ . '/../.env';
$isCloudEnv = (getenv('DB_HOST') && getenv('DB_NAME'));

if (!$isCloudEnv && file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, "\"' \t\n\r\0\x0B");
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$host = getenv('DB_HOST') ?: '';
$db   = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';

if (empty($host) || empty($db) || empty($user)) {
    die("Erreur de configuration : Paramètres de base de données manquants.");
}
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // 2. FORCER L'HEURE MYSQL
    $pdo->exec("SET time_zone = '+01:00';");
    
} catch (\PDOException $e) {
    // En production on cache l'erreur SQL pour ne pas divulguer des infos BDD
    error_log($e->getMessage());
    die("Erreur technique : connexion base de données impossible.");
}
?>