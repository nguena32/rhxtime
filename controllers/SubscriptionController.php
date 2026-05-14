<?php
// controllers/SubscriptionController.php
class SubscriptionController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function initPayment() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $entreprise_id = $_SESSION['entreprise_id'];
        $plan_id = (int)($_POST['plan_id'] ?? 1); 
        $billing_cycle = $_POST['billing_cycle'] ?? 'mensuel'; // 'mensuel' or 'annuel'
        
        // Fetch plan details from DB
        $stmtP = $this->pdo->prepare("SELECT nom, montant_mensuel, montant_annuel_mensualise FROM plans WHERE id = ?");
        $stmtP->execute([$plan_id]);
        $planDetails = $stmtP->fetch();
        
        if (!$planDetails) {
            die("Plan invalide.");
        }

        // Calculation of amount based on cycle
        if ($billing_cycle === 'annuel' && (float)$planDetails['montant_annuel_mensualise'] > 0) {
            $amount = (float)$planDetails['montant_annuel_mensualise'] * 12;
            $description = 'Abonnement RHXtimes Annuel - Plan ' . $planDetails['nom'];
        } else {
            $amount = (float)$planDetails['montant_mensuel'];
            $description = 'Abonnement RHXtimes Mensuel - Plan ' . $planDetails['nom'];
            $billing_cycle = 'mensuel'; // Force if invalid
        }
        
        $transaction_id = 'ORD-' . time() . '-' . rand(1000, 9999);
        
        // Save transaction with billing cycle
        // Important: check if column exists before, or assume it's created
        $stmt = $this->pdo->prepare("INSERT INTO transactions (entreprise_id, transaction_id, montant, statut, plan_choisi, billing_cycle) VALUES (?, ?, ?, 'PENDING', ?, ?)");
        $stmt->execute([$entreprise_id, $transaction_id, $amount, $planDetails['nom'], $billing_cycle]);

        // Détection automatique de la passerelle active (configurée par le Super-Admin)
        $gateway = SuperAdminController::getActiveGateway($this->pdo);

        if ($gateway === 'moneyfusion') {
            $this->handleMoneyfusionPayment($transaction_id, $amount, $description);
        } else {
            $this->handleCinetpayPayment($transaction_id, $amount, $description);
        }
    }

    private function handleCinetpayPayment($transaction_id, $amount, $description) {
        $cpConfig = SuperAdminController::getCinetpayConfig($this->pdo);
        $apikey = $cpConfig['CINETPAY_API_KEY'];
        $site_id = $cpConfig['CINETPAY_SITE_ID'];
        
        if (empty($site_id)) {
            die("Erreur : Configuration CinetPay manquante.");
        }

        $data = [
            'apikey' => $apikey,
            'site_id' => $site_id,
            'transaction_id' => $transaction_id,
            'amount' => $amount,
            'currency' => 'XAF',
            'description' => $description,
            'return_url' => "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?page=subscription_return&transaction_id=" . $transaction_id,
            'notify_url' => "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?page=subscription_notify",
            'customer_name' => $_SESSION['admin_email'] ?? 'Client',
            'customer_email' => $_SESSION['admin_email'] ?? 'test@test.com',
            'customer_country' => 'CM'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-checkout.cinetpay.com/v2/payment');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $res = json_decode($response, true);
        if (isset($res['code']) && $res['code'] == '201') {
            header('Location: ' . $res['data']['payment_url']);
            exit;
        } else {
            die('Erreur d\'initialisation CinetPay: ' . ($res['description'] ?? 'Réponse invalide'));
        }
    }

    private function handleMoneyfusionPayment($transaction_id, $amount, $description) {
        $mfConfig = SuperAdminController::getMoneyfusionConfig($this->pdo);
        $apiUrl = $mfConfig['MONEYFUSION_API_URL'];

        if (empty($apiUrl)) {
            die("Erreur : Configuration Money Fusion manquante.");
        }

        $data = [
            'totalPrice' => $amount,
            'article' => [
                [ $description => $amount ]
            ],
            'personal_Info' => [
                [
                    'userId' => $_SESSION['admin_id'],
                    'orderId' => $transaction_id
                ]
            ],
            'numeroSend' => '', // Sera demandé sur la page de paiement
            'nomclient' => $_SESSION['admin_email'] ?? 'Client',
            'return_url' => "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?page=subscription_return&transaction_id=" . $transaction_id,
            'webhook_url' => "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?page=subscription_moneyfusion_notify"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response, true);
        if (isset($res['statut']) && $res['statut'] === true && !empty($res['url'])) {
            header('Location: ' . $res['url']);
            exit;
        } else {
            die('Erreur d\'initialisation Money Fusion: ' . ($res['message'] ?? 'Réponse invalide'));
        }
    }

    public function returnUrl() {
        if (!isset($_SESSION['admin_id'])) {
             header('Location: index.php?page=login');
             exit;
        }
        $transaction_id = $_GET['transaction_id'] ?? '';
        $stmt = $this->pdo->prepare("SELECT statut FROM transactions WHERE transaction_id = ?");
        $stmt->execute([$transaction_id]);
        $status = $stmt->fetchColumn();

        echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
        if ($status === 'ACCEPTED') {
            echo "<h2>Félicitations !</h2><p>Votre abonnement a été activé avec succès.</p>";
            echo "<a href='index.php?page=admin_dashboard'>Accéder à mon tableau de bord</a>";
        } else {
            echo "<h2>Paiement en cours</h2><p>Dès confirmation par l'opérateur, votre accès sera activé automatiquement.</p>";
            echo "<a href='index.php?page=admin_dashboard'>Retour au tableau de bord</a>";
        }
        echo "</div>";
    }

    public function cinetpayNotify() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cpm_trans_id = $_POST['cpm_trans_id'] ?? '';
            $cpConfig = SuperAdminController::getCinetpayConfig($this->pdo);

            $data = ['apikey' => $cpConfig['CINETPAY_API_KEY'], 'site_id' => $cpConfig['CINETPAY_SITE_ID'], 'transaction_id' => $cpm_trans_id];

            $ch = curl_init('https://api-checkout.cinetpay.com/v2/payment/check');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $response = curl_exec($ch);
            curl_close($ch);

            $res = json_decode($response, true);
            if (isset($res['code']) && $res['code'] == '00') {
                $status = $res['data']['status'];
                if ($status === 'ACCEPTED') {
                    $this->processSuccess($cpm_trans_id);
                } else {
                    $stmt = $this->pdo->prepare("UPDATE transactions SET statut = ? WHERE transaction_id = ?");
                    $stmt->execute([$status, $cpm_trans_id]);
                }
            }
        }
        echo "OK";
    }

    public function moneyfusionNotify() {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data || !isset($data['tokenPay']) || !isset($data['event'])) {
            http_response_code(400);
            echo "INVALID_PAYLOAD";
            exit;
        }

        $event = $data['event'];
        $transaction_id = '';
        if (isset($data['personal_Info'][0]['orderId'])) {
            $transaction_id = $data['personal_Info'][0]['orderId'];
        }

        if (empty($transaction_id)) {
            http_response_code(400);
            echo "NO_ORDER_ID";
            exit;
        }

        if ($event === 'payin.session.completed') {
            $this->processSuccess($transaction_id);
        } else if ($event === 'payin.session.cancelled') {
            $stmt = $this->pdo->prepare("UPDATE transactions SET statut = 'CANCELLED' WHERE transaction_id = ?");
            $stmt->execute([$transaction_id]);
        }

        echo "OK";
    }

    private function processSuccess($transaction_id) {
        // Protection Idempotence
        $stmtCheck = $this->pdo->prepare("SELECT statut FROM transactions WHERE transaction_id = ?");
        $stmtCheck->execute([$transaction_id]);
        $oldStatus = $stmtCheck->fetchColumn();
        if ($oldStatus === 'ACCEPTED') {
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE transactions SET statut = 'ACCEPTED' WHERE transaction_id = ?");
        $stmt->execute([$transaction_id]);

        $stmtGet = $this->pdo->prepare("SELECT entreprise_id, plan_choisi, billing_cycle FROM transactions WHERE transaction_id = ?");
        $stmtGet->execute([$transaction_id]);
        $tx = $stmtGet->fetch();
        if ($tx) {
            $eid = $tx['entreprise_id'];
            $plan_name = $tx['plan_choisi']; 
            $cycle = $tx['billing_cycle'] ?? 'mensuel';
            
            $stmtP3 = $this->pdo->prepare("SELECT id FROM plans WHERE nom = ?");
            $stmtP3->execute([$plan_name]);
            $pid = $stmtP3->fetchColumn();

            $interval = ($cycle === 'annuel') ? '1 YEAR' : '1 MONTH';
            
            $stmtUpdate = $this->pdo->prepare("UPDATE entreprises SET plan_id = ?, expiration_date = DATE_ADD(GREATEST(IFNULL(expiration_date, NOW()), NOW()), INTERVAL $interval), statut = 'Active', has_had_trial = 1 WHERE id = ?");
            $stmtUpdate->execute([$pid, $eid]);

            require_once 'core/Security.php';
            $stmtE = $this->pdo->prepare("SELECT expiration_date FROM entreprises WHERE id = ?");
            $stmtE->execute([$eid]);
            $newExp = $stmtE->fetchColumn();
            $signature = Security::generateExpirationSignature($eid, $newExp);
            
            $stmtUpdSig = $this->pdo->prepare("UPDATE entreprises SET security_signature = ? WHERE id = ?");
            $stmtUpdSig->execute([$signature, $eid]);
        }
    }

    public function applyPromoCode() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_dashboard');
            exit;
        }

        sleep(1); // Anti Brute-force
        
        require_once 'core/Security.php';
        Security::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $code = strtoupper(trim($_POST['promo_code'] ?? ''));
        $entreprise_id = $_SESSION['entreprise_id'] ?? null;
        $admin_id = $_SESSION['admin_id'] ?? null;

        if (!$code || !$entreprise_id) {
            header('Location: index.php?page=admin_dashboard&error=code_invalide');
            exit;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1 FOR UPDATE");
            $stmt->execute([$code]);
            $promo = $stmt->fetch();

            if (!$promo) {
                $this->pdo->rollBack();
                header('Location: index.php?page=admin_dashboard&error=code_invalide');
                exit;
            }

            if ($promo['used_count'] >= $promo['usage_limit']) {
                $this->pdo->rollBack();
                header('Location: index.php?page=admin_dashboard&error=code_epuise');
                exit;
            }

            if ($promo['expires_at'] && strtotime($promo['expires_at']) < time()) {
                $this->pdo->rollBack();
                header('Location: index.php?page=admin_dashboard&error=code_expire');
                exit;
            }

            // Update Promo code usage
            $stmtUpdPromo = $this->pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?");
            $stmtUpdPromo->execute([$promo['id']]);

            // Extend the expiration date
            $days = (int)$promo['days_to_add'];
            $stmtEnt = $this->pdo->prepare("SELECT expiration_date FROM entreprises WHERE id = ? FOR UPDATE");
            $stmtEnt->execute([$entreprise_id]);
            $oldExp = $stmtEnt->fetchColumn();

            $stmtUpdateExp = $this->pdo->prepare("UPDATE entreprises SET expiration_date = DATE_ADD(GREATEST(IFNULL(expiration_date, NOW()), NOW()), INTERVAL ? DAY), statut = 'Active' WHERE id = ?");
            $stmtUpdateExp->execute([$days, $entreprise_id]);

            // Re-fetch precisely to compute signature
            $stmtE2 = $this->pdo->prepare("SELECT expiration_date FROM entreprises WHERE id = ?");
            $stmtE2->execute([$entreprise_id]);
            $newExp = $stmtE2->fetchColumn();

            $signature = Security::generateExpirationSignature($entreprise_id, $newExp);
            $stmtSig = $this->pdo->prepare("UPDATE entreprises SET security_signature = ? WHERE id = ?");
            $stmtSig->execute([$signature, $entreprise_id]);

            // Audit Log
            $stmtLog = $this->pdo->prepare("INSERT INTO audit_logs (admin_id, entreprise_id, action, old_value, new_value, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $stmtLog->execute([$admin_id, $entreprise_id, 'APPLY_PROMO_CODE', $oldExp, $newExp, $ip]);

            $this->pdo->commit();
            header('Location: index.php?page=admin_dashboard&success=promo_applique');
            exit;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage());
            header('Location: index.php?page=admin_dashboard&error=erreur_technique');
            exit;
        }
    }
}
