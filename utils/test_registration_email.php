<?php
/**
 * utils/test_registration_email.php
 * Script de diagnostic pour vérifier la génération du lien et l'envoi SMTP.
 */

header('Content-Type: text/plain; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/NotificationService.php';

echo "=== DIAGNOSTIC VALIDATION EMAIL ===\n\n";

// 1. Vérification configuration
echo "[1] Récupération config SMTP... ";
$smtp = NotificationService::getSmtpCredentials($pdo);
if (empty($smtp['host'])) {
    echo "ERREUR: Host SMTP vide.\n";
} else {
    echo "OK ({$smtp['host']})\n";
}

// 2. Simulation génération lien
echo "[2] Simulation génération lien de validation...\n";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$token    = bin2hex(random_bytes(32));
$verificationUrl = "{$protocol}://{$host}{$basePath}/index.php?page=verify_email&token={$token}";

echo "Url générée : {$verificationUrl}\n";
echo "Note: En mode CLI, le host/basePath peut être différent du mode Web.\n\n";

// 3. Test d'envoi réel
$testEmail = getenv('SMTP_USER') ?: 'test@mcm-maroc.com'; // Ou un email de test
echo "[3] Tentative d'envoi immédiat à : {$testEmail}...\n";

$data = [
    'nom' => 'Testeur RHXtimes',
    'verification_url' => $verificationUrl
];

$result = NotificationService::sendEmailVerification($testEmail, $data, $pdo, true);

if ($result) {
    echo "SUCCÈS: L'email a été envoyé avec succès !\n";
} else {
    echo "ÉCHEC: L'envoi a échoué. Vérifiez vos identifiants SMTP dans SuperAdmin.\n";
}

echo "\n=== FIN DU DIAGNOSTIC ===\n";
