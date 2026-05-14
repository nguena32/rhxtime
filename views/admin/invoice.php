<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture - <?= htmlspecialchars($tx['transaction_id']) ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 40px; color: #333; background: #fff; line-height: 1.6; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); font-size: 16px; }
        .invoice-box table { width: 100%; text-align: left; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #333; }
        .invoice-box table tr.information table td { padding-bottom: 40px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .invoice-box table tr.details td { padding-bottom: 20px; }
        .invoice-box table tr.item td{ border-bottom: 1px solid #eee; }
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; font-size: 18px; }
        .right { text-align: right; }
        @media print {
            body { padding: 0; }
            .invoice-box { border: none; box-shadow: none; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <!-- Logo RHXtimes text as placeholder -->
                                <strong style="color:#4a6bfd; font-size: 32px;">RHXtimes</strong><br>
                                <span style="font-size: 14px; color: #777;">Services SAAS</span>
                            </td>
                            
                            <td class="right">
                                Reçu N° : <strong><?= htmlspecialchars($tx['transaction_id']) ?></strong><br>
                                Créé le : <?= date('d/m/Y H:i', strtotime($tx['date_paiement'])) ?><br>
                                Statut Paiement : <span style="color:green;">Payé en ligne via CinetPay</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <strong>Émetteur :</strong><br>
                                Marvens Group and Services SARL<br>
                                Douala, Cameroun<br>
                                contact@rhxtimes.com
                            </td>
                            
                            <td class="right">
                                <strong>Facturé à :</strong><br>
                                <?= htmlspecialchars($tx['nom']) ?><br>
                                Administratif Client<br>
                                ID Entreprise: <?= $tx['entreprise_id'] ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="heading">
                <td>Description de Service</td>
                <td class="right">Montant (FCFA)</td>
            </tr>
            
            <tr class="item last">
                <td>Renouvellement Abonnement - Plan <?= htmlspecialchars($tx['plan_choisi'] ?? ($tx['current_plan_nom'] ?? 'Standard')) ?></td>
                <td class="right"><?= number_format($tx['montant'], 0, ',', ' ') ?> FCFA</td>
            </tr>
            
            <br>
            <tr class="total">
                <td></td>
                <td class="right">
                   Total TTC: <?= number_format($tx['montant'], 0, ',', ' ') ?> FCFA
                </td>
            </tr>
        </table>
        
        <div style="margin-top: 50px; border-top: 1px solid #ddd; padding-top: 20px; text-align: center; color: #777; font-size: 12px;">
            <p>Ceci est un reçu généré électroniquement conforme aux normes OHADA. Aucune signature physique n'est requise.</p>
            <p>Merci pour votre confiance sur la plateforme RHXtimes SAAS.</p>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
