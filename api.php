<?php
// api.php
header("Content-Type: application/json; charset=UTF-8");

// 1. Inclusion de la config et du contrôleur MVC
require_once 'config/db.php';
require_once 'core/Security.php';

spl_autoload_register(function ($class_name) {
    if (file_exists('models/' . $class_name . '.php')) {
        require_once 'models/' . $class_name . '.php';
    } elseif (file_exists('controllers/' . $class_name . '.php')) {
        require_once 'controllers/' . $class_name . '.php';
    }
});

// 2. Instanciation
$apiController = new ApiController($pdo);

// 3. Exécution
$apiController->handleScan();
?>