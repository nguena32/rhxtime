<?php
// utils/debug_email.php
// Script de diagnostic pour l'envoi d'emails

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/NotificationService.php';

echo "<h1>Dépannage Email RHXtimes</h1>";

// 1. DÉTECTION PHPMAILER
echo "<h2>1. Détection de PHPMailer</h2>";
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    echo "✅ Autoloader trouvé : $composerAutoload<br>";
    require_once $composerAutoload;
} else {
    echo "❌ Autoloader NON trouvé dans 'vendor/autoload.php'.<br>";
}

if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    echo "✅ Classe PHPMailer chargée avec succès.<br>";
} else {
    echo "❌ Classe PHPMailer INTROUVABLE. Le système ne peut pas envoyer d'emails via SMTP.<br>";
    echo "<i>Note: Assurez-vous que le dossier 'vendor' existe et contient PHPMailer.</i><br>";
}

// 2. VÉRIFICATION DES PARAMÈTRES
echo "<h2>2. Paramètres SMTP en base de données</h2>";
$settings = [];
$keys = ['SMTP_HOST', 'SMTP_USER', 'SMTP_PASS', 'SMTP_PORT'];
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM platform_settings WHERE setting_key IN (?,?,?,?)");
$stmt->execute($keys);
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

foreach($keys as $k) {
    if (isset($settings[$k])) {
        $val = ($k === 'SMTP_PASS') ? '******** (Chiffré)' : $settings[$k];
        echo "🔹 $k : <strong>$val</strong><br>";
    } else {
        echo "🔸 $k : <strong style='color:red;'>Non défini</strong><br>";
    }
}

// 3. TEST DE DÉCHIFFREMENT
echo "<h2>3. Test de Déchiffrement</h2>";
if (!empty($settings['SMTP_PASS'])) {
    $dbName = getenv('DB_NAME') ?: 'rhtimes_saas';
    echo "DB_NAME utilisé pour la clé : $dbName<br>";
    
    // On garde la clé technique identique pour ne pas casser le déchiffrement existant
    $key = hash('sha256', 'RHtimes_SMTP_' . $dbName . '_SecretKey2026', true);
    $data = base64_decode($settings['SMTP_PASS']);
    if ($data !== false && strpos($data, '::') !== false) {
        list($iv, $encrypted) = explode('::', $data, 2);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        if ($decrypted !== false) {
            echo "✅ Déchiffrement réussi.<br>";
            $settings['SMTP_PASS'] = $decrypted;
        } else {
            echo "❌ Échec du déchiffrement. La clé ou le sel ne correspond pas.<br>";
        }
    } else {
        echo "❌ Format de donnée chiffrée invalide.<br>";
    }
}

// 4. TEST DE CONNEXION RÉELLE (si PHPMailer dispo)
if (class_exists('PHPMailer\\PHPMailer\\PHPMailer') && !empty($settings['SMTP_HOST'])) {
    echo "<h2>4. Tentative de connexion SMTP (Debug activé)</h2>";
    echo "<pre style='background:#000; color:#0f0; padding:15px; border-radius:8px;'>";
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->SMTPDebug = 3; // Debug complet
        $mail->Debugoutput = 'echo';
        $mail->isSMTP();
        $mail->Host       = $settings['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $settings['SMTP_USER'];
        $mail->Password   = $settings['SMTP_PASS'];
        $mail->Port       = $settings['SMTP_PORT'];
        $mail->SMTPSecure = ($settings['SMTP_PORT'] == 465) ? 'ssl' : (($settings['SMTP_PORT'] == 587) ? 'tls' : '');
        $mail->Timeout    = 10;
        
        echo "Initialisation de la connexion vers " . $settings['SMTP_HOST'] . "...\n";
        $mail->smtpConnect();
        echo "\n✅ CONNEXION SMTP RÉUSSIE !";
        $mail->smtpClose();
    } catch (Exception $e) {
        echo "\n❌ ERREUR SMTP : " . $e->getMessage();
    }
    echo "</pre>";
}

echo "<hr><p>Fin du diagnostic.</p>";
