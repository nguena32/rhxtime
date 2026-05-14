<?php
// utils/NotificationService.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationService {
    /**
     * Récupère les paramètres SMTP : 
     * Priorité 1 → BDD (platform_settings, configurés par le Super-Admin)
     * Priorité 2 → Variables d'environnement (.env)
     * Priorité 3 → Valeurs par défaut (Mailtrap)
     */
    private static function getSmtpCredentials($pdo = null) {
        $host = '';
        $user = '';
        $pass = '';
        $port = 465;

        // 1. Tenter de lire depuis la BDD via SuperAdminController
        if ($pdo !== null) {
            try {
                $dbConfig = SuperAdminController::getSmtpConfig($pdo);
                if (!empty($dbConfig['SMTP_HOST'])) {
                    $host = $dbConfig['SMTP_HOST'];
                    $user = $dbConfig['SMTP_USER'] ?? '';
                    $pass = $dbConfig['SMTP_PASS'] ?? '';
                    $port = intval($dbConfig['SMTP_PORT'] ?? 465);
                }
            } catch (\Exception $e) {
                // Silencieux : fallback vers .env
            }
        }

        // 2. Fallback vers les variables d'environnement
        if (empty($host)) {
            $host = getenv('SMTP_HOST') ?: 'smtp.mailtrap.io';
            $user = getenv('SMTP_USER') ?: 'test';
            $pass = getenv('SMTP_PASS') ?: 'test';
            $port = intval(getenv('SMTP_PORT') ?: 2525);
        }

        return compact('host', 'user', 'pass', 'port');
    }

    /**
     * Envoie une alerte d'anomalie GPS à l'administrateur
     * Désormais asynchrone (Pousse dans la file email_queue)
     */
    public static function sendAnomalyAlert($adminEmail, $data, $pdo = null) {
        $html = "<h3>Alerte de Pointage : Hors Zone</h3>";
        $html .= "<p><strong>Employé :</strong> {$data['employe_nom']}</p>";
        $html .= "<p><strong>Heure :</strong> {$data['heure']}</p>";
        $html .= "<p><strong>Lieu prévu :</strong> {$data['lieu_nom']}</p>";
        $html .= "<p><strong>GPS réel :</strong> Lat {$data['gps_lat']}, Lng {$data['gps_lng']}</p>";
        
        if ($pdo !== null) {
            $stmt = $pdo->prepare("INSERT INTO email_queue (to_email, subject, body, statut) VALUES (?, ?, ?, 'PENDING')");
            return $stmt->execute([$adminEmail, 'Alerte Pointage - Anomalie Détectée', $html]);
        }
        
        $smtp = self::getSmtpCredentials($pdo);
        return self::sendMail($adminEmail, 'Alerte Pointage - Anomalie Détectée', $html, $smtp);
    }

    /**
     * Envoie un email de vérification de compte (async via email_queue)
     * @param string $toEmail  Email du destinataire
     * @param array  $data     ['nom', 'verification_url']
     * @param PDO    $pdo      Connexion PDO pour la file d'attente
     * @param bool   $immediate Si true, envoi immédiat sans passer par la queue
     */
    public static function sendEmailVerification(string $toEmail, array $data, $pdo, $immediate = false) {
        $nom           = htmlspecialchars($data['nom'] ?? 'Responsable');
        $url           = htmlspecialchars($data['verification_url'] ?? '#');
        $year          = date('Y');

        $subject = '✅ RHXtimes — Confirmez votre adresse email';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title>Confirmez votre email</title>
  <!--[if mso]>
  <noscript>
    <xml>
      <o:OfficeDocumentSettings>
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
  </noscript>
  <![endif]-->
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:'Segoe UI',Arial,sans-serif;-webkit-font-smoothing:antialiased;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
HTML;

        // On concatène le reste du HTML (simplifié pour le remplacement ici mais gardant la structure)
        $htmlBody = <<<HTML
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f4f5f7;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" role="presentation" style="max-width:560px;width:100%;">

          <!-- HEADER -->
          <tr>
            <td style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);border-radius:16px 16px 0 0;padding:36px 40px;text-align:center;">
              <div style="font-size:32px;margin-bottom:8px;">🔐</div>
              <h1 style="color:#ffffff;margin:0;font-size:24px;font-weight:800;letter-spacing:-0.5px;">Vérifiez votre adresse email</h1>
              <p style="color:rgba(255,255,255,0.75);margin:10px 0 0;font-size:14px;">Une étape rapide pour sécuriser votre compte RHXtimes</p>
            </td>
          </tr>

          <!-- BODY -->
          <tr>
            <td style="background:#ffffff;padding:36px 40px;border:1px solid #e2e8f0;border-top:none;">
              <p style="color:#334155;font-size:15px;line-height:1.7;margin-top:0;">
                Bonjour <strong style="color:#1e293b;">{$nom}</strong>,
              </p>
              <p style="color:#475569;font-size:15px;line-height:1.7;">
                Merci de vous être inscrit sur <strong>RHXtimes</strong> – la solution de pointage intelligente pour vos équipes.<br>
                Pour activer votre compte et accéder à votre tableau de bord, veuillez confirmer votre adresse email en cliquant sur le bouton ci-dessous.
              </p>

              <!-- CTA -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
                <tr>
                  <td align="center">
                    <a href="{$url}"
                       style="display:inline-block;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#ffffff;text-decoration:none;padding:16px 40px;border-radius:12px;font-size:16px;font-weight:700;letter-spacing:0.2px;box-shadow:0 4px 15px rgba(79,70,229,0.35);">
                      ✉️ &nbsp; Confirmer mon adresse email
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Expiration notice -->
              <div style="background:#fef9ec;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;margin-bottom:24px;">
                <p style="margin:0;color:#92400e;font-size:13px;">
                  ⏱️ <strong>Ce lien expire dans 24 heures.</strong> Après ce délai, vous devrez en demander un nouveau depuis la page de connexion.
                </p>
              </div>

              <!-- Security note -->
              <p style="color:#94a3b8;font-size:12px;line-height:1.6;border-top:1px solid #f1f5f9;padding-top:18px;margin-bottom:0;">
                Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet email en toute sécurité.<br>
                Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                <span style="color:#4f46e5;word-break:break-all;">{$url}</span>
              </p>
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="background:#f8fafc;border-radius:0 0 16px 16px;padding:20px 40px;text-align:center;border:1px solid #e2e8f0;border-top:none;">
              <p style="margin:0;color:#94a3b8;font-size:12px;">
                © {$year} <strong style="color:#475569;">RHXtimes</strong> by Marvens Group — Tous droits réservés.<br>
                Ce message a été envoyé automatiquement, merci de ne pas y répondre.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
        $html .= $htmlBody;

        $success = false;
        $errorMsg = null;

        if ($immediate) {
            $smtp = self::getSmtpCredentials($pdo);
            $result = self::sendMail($toEmail, $subject, $html, $smtp);
            if ($result['success']) {
                $success = true;
            } else {
                $errorMsg = $result['error'];
            }
        }

        // ── Envoi asynchrone via email_queue (ou repli en cas d'échec de l'envoi immédiat) ──
        if (!$success) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO email_queue (to_email, subject, body, statut, last_error) VALUES (?, ?, ?, 'PENDING', ?)"
                );
                return $stmt->execute([$toEmail, $subject, $html, $errorMsg]);
            } catch (\Exception $e) {
                error_log("email_queue insert failed: " . $e->getMessage());
                // Si même l'insertion échoue (ex: colonne manquante), on tente sans last_error
                try {
                    $stmt = $pdo->prepare("INSERT INTO email_queue (to_email, subject, body, statut) VALUES (?, ?, ?, 'PENDING')");
                    return $stmt->execute([$toEmail, $subject, $html]);
                } catch (\Exception $ex) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Envoie un message de contact de la landing page vers l'admin (SMTP_USER)
     */
    public static function sendContactMessage(array $data, $pdo) {
        $prenom  = htmlspecialchars($data['prenom'] ?? 'Visiteur');
        $email   = htmlspecialchars($data['email'] ?? '');
        $tel     = htmlspecialchars($data['tel'] ?? 'Non renseigné');
        $message = nl2br(htmlspecialchars($data['message'] ?? ''));
        $year    = date('Y');

        // Récupérer le destinataire (l'adresse SMTP paramétrée)
        $smtp = self::getSmtpCredentials($pdo);
        $to   = $smtp['user']; // Destinataire = Utilisateur SMTP par défaut

        if (empty($to)) {
            error_log("Erreur NotificationService: Impossible de trouver l'adresse email de destination (SMTP_USER vide).");
            return false;
        }

        $subject = "🆕 RHXtimes : Nouveau message de " . $prenom;

        $html = "
        <div style=\"font-family: 'Segoe UI', Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; background: #ffffff;\">
            <div style=\"background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 30px; color: #ffffff; text-align: center;\">
                <div style=\"font-size: 32px; margin-bottom: 10px;\">📧</div>
                <h2 style=\"margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;\">Demande de contact</h2>
                <p style=\"margin: 5px 0 0; opacity: 0.8; font-size: 14px;\">Provenance : Landing Page RHXtimes</p>
            </div>
            <div style=\"padding: 35px; line-height: 1.6; color: #334155;\">
                <p style=\"margin-bottom: 25px;\">Vous avez reçu un nouveau message via la bulle de contact.</p>
                
                <table width=\"100%\" style=\"border-collapse: collapse; margin-bottom: 25px; background: #f8fafc; border-radius: 12px; overflow: hidden;\">
                    <tr>
                        <td style=\"padding: 15px; border-bottom: 1px solid #e2e8f0; width: 100px; font-weight: 700; color: #64748b; font-size: 13px;\">NOM</td>
                        <td style=\"padding: 15px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-weight: 600;\">{$prenom}</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 15px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #64748b; font-size: 13px;\">EMAIL</td>
                        <td style=\"padding: 15px; border-bottom: 1px solid #e2e8f0; color: #4f46e5; font-weight: 600;\">{$email}</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 15px; font-weight: 700; color: #64748b; font-size: 13px;\">TÉL.</td>
                        <td style=\"padding: 15px; color: #1e293b; font-weight: 600;\">{$tel}</td>
                    </tr>
                </table>

                <div style=\"background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;\">
                    <p style=\"margin: 0 0 10px; font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 800; letter-spacing: 0.5px;\">Message :</p>
                    <p style=\"margin: 0; font-style: italic; color: #1e293b; line-height: 1.5;\">{$message}</p>
                </div>

                <hr style=\"border: none; border-top: 1px solid #f1f5f9; margin: 30px 0;\">
                <p style=\"font-size: 12px; color: #94a3b8; text-align: center; margin: 0;\">
                    © {$year} <strong>RHXtimes</strong> by Marvens Group. <br>
                    Ce mail vous a été envoyé car vous êtes l'administrateur de la plateforme.
                </p>
            </div>
        </div>";
        
        return self::sendMail($to, $subject, $html, $smtp)['success'];
    }

    /**
     * Notification pour le système de support (Tickets)
     */
    public static function sendSupportNotification($toEmail, $subject, $messageContent, $pdo) {
        $year = date('Y');
        $html = "
        <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;\">
            <div style=\"background: #4f46e5; padding: 25px; color: #fff; text-align: center;\">
                <h2 style=\"margin: 0; font-size: 20px;\">RHXtimes Support</h2>
            </div>
            <div style=\"padding: 30px; line-height: 1.6; color: #334155;\">
                {$messageContent}
                <hr style=\"border: none; border-top: 1px solid #f1f5f9; margin: 25px 0;\">
                <p style=\"font-size: 12px; color: #94a3b8; text-align: center;\">© {$year} RHXtimes Support. Veuillez ne pas répondre directement à cet email.</p>
            </div>
        </div>";
        
        try {
            $stmt = $pdo->prepare("INSERT INTO email_queue (to_email, subject, body, statut) VALUES (?, ?, ?, 'PENDING')");
            return $stmt->execute([$toEmail, $subject, $html]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @deprecated Utiliser sendEmailVerification() à la place.
     */
    public static function sendAccountValidation($toEmail, $data, $pdo = null) {
        if ($pdo && isset($data['verification_url'])) {
            return self::sendEmailVerification($toEmail, $data, $pdo);
        }
        // Fallback legacy
        $smtp = self::getSmtpCredentials($pdo);
        $html = '<p>Bienvenue ' . htmlspecialchars($data['nom'] ?? '') . '. <a href="' . htmlspecialchars($data['validation_url'] ?? '#') . '">Validez votre compte</a></p>';
        return self::sendMail($toEmail, 'RHXtimes - Validez votre compte', $html, $smtp)['success'];
    }


    /**
     * Méthode centralisée d'envoi d'email
     * Retourne un tableau ['success' => bool, 'error' => string|null]
     */
    public static function sendMail($to, $subject, $htmlBody, $smtp) {
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $mail = new PHPMailer(true);
            try {
                $mail->Timeout = 5;
                $mail->isSMTP();
                $mail->Host       = $smtp['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtp['user'];
                $mail->Password   = $smtp['pass'];
                $mail->Port       = $smtp['port'];
                $mail->SMTPSecure = ($smtp['port'] == 465) ? 'ssl' : (($smtp['port'] == 587) ? 'tls' : '');
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($smtp['user'], 'RHXtimes Alertes');
                $mail->addAddress($to);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlBody;

                $mail->send();
                return ['success' => true, 'error' => null];
            } catch (Exception $e) {
                error_log("Erreur PHPMailer: {$mail->ErrorInfo}");
                return ['success' => false, 'error' => $mail->ErrorInfo];
            }
        } else {
            error_log("NOTIFICATION SIMULEE: PHPMailer non trouvé. Email prêt pour $to. Sujet: $subject");
            return ['success' => false, 'error' => 'PHPMailer not found'];
        }
    }
}
