<?php
/**
 * utils/EmailWorker.php
 * Script à exécuter via CRON (toutes les minutes) pour traiter la file d'attente des emails.
 * Commande : php utils/EmailWorker.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/NotificationService.php';

// Charger PHPMailer si disponible
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

$stmt = $pdo->prepare("SELECT * FROM email_queue WHERE statut = 'PENDING' AND attempts < 3 ORDER BY created_at ASC LIMIT 10");
$stmt->execute();
$emails = $stmt->fetchAll();

foreach ($emails as $email) {
    echo "Processing email to: " . $email['to_email'] . "\n";
    
    // Marquer comme en cours
    $pdo->prepare("UPDATE email_queue SET attempts = attempts + 1 WHERE id = ?")->execute([$email['id']]);

    $smtp = NotificationService::getSmtpCredentials($pdo);
    $result = NotificationService::sendMail($email['to_email'], $email['subject'], $email['body'], $smtp);

    if ($result['success']) {
        $pdo->prepare("UPDATE email_queue SET statut = 'SENT', last_error = NULL WHERE id = ?")->execute([$email['id']]);
        echo "Successfully sent.\n";
    } else {
        $error = $result['error'] ?? 'Unknown error';
        echo "Failed to send: $error\n";
        
        // Mise à jour de l'erreur en base (on tente avec la colonne last_error)
        try {
            $pdo->prepare("UPDATE email_queue SET last_error = ? WHERE id = ?")->execute([$error, $email['id']]);
        } catch (\Exception $e) {
            // Silencieux si la colonne n'existe pas encore
        }

        if ($email['attempts'] >= 2) {
            $pdo->prepare("UPDATE email_queue SET statut = 'FAILED' WHERE id = ?")->execute([$email['id']]);
        }
    }
}
