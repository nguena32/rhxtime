<?php // views/admin/manual.php ?>
<style>
.manual-wrap{max-width:900px;margin:0 auto;padding:0 15px 60px}
.manual-hero{background:linear-gradient(135deg,#0f172a 0%,#1e40af 50%,#7c3aed 100%);border-radius:24px;padding:50px 40px;text-align:center;color:#fff;margin-bottom:40px;position:relative;overflow:hidden}
.manual-hero::before{content:'';position:absolute;top:-50%;right:-20%;width:400px;height:400px;background:radial-gradient(circle,rgba(255,255,255,.08) 0%,transparent 70%);border-radius:50%}
.manual-hero h1{font-size:2.2rem;font-weight:900;margin-bottom:10px;position:relative}
.manual-hero p{font-size:1.05rem;opacity:.8;max-width:600px;margin:0 auto 25px;position:relative}
.btn-back{display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e40af;padding:12px 28px;border-radius:12px;font-weight:700;text-decoration:none;transition:all .3s;box-shadow:0 8px 20px rgba(0,0,0,.15)}
.btn-back:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(0,0,0,.2)}
.manual-toc{background:#f8fafc;border:2px solid #e2e8f0;border-radius:16px;padding:28px 32px;margin-bottom:40px}
.manual-toc h3{color:#1e293b;font-size:1.1rem;margin-bottom:15px;font-weight:800}
.manual-toc ul{list-style:none;padding:0;display:grid;grid-template-columns:1fr 1fr;gap:8px}
.manual-toc a{color:#4f46e5;text-decoration:none;font-weight:600;font-size:.92rem;display:flex;align-items:center;gap:8px;padding:6px 0;transition:color .2s}
.manual-toc a:hover{color:#7c3aed}
.manual-part{background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;padding:18px 28px;border-radius:16px;margin:40px 0 25px;font-size:1.3rem;font-weight:900;display:flex;align-items:center;gap:12px}
.manual-section{background:#fff;border-radius:16px;border:1px solid #e2e8f0;padding:30px;margin-bottom:24px;box-shadow:0 4px 15px rgba(0,0,0,.04);transition:all .3s}
.manual-section:hover{box-shadow:0 8px 25px rgba(79,70,229,.08);border-color:#c7d2fe}
.manual-section h3{font-size:1.15rem;font-weight:800;margin-bottom:15px;display:flex;align-items:center;gap:10px;padding-bottom:12px;border-bottom:2px solid #f1f5f9}
.section-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.manual-section h4{color:#4f46e5;font-weight:700;font-size:.95rem;margin:18px 0 8px;display:flex;align-items:center;gap:6px}
.manual-section h4::before{content:'▸';color:#7c3aed}
.manual-section p,.manual-section li{color:#475569;line-height:1.7;font-size:.93rem}
.manual-section ul{padding-left:20px;margin:8px 0}
.manual-section li{margin-bottom:6px}
.manual-table{width:100%;border-collapse:collapse;margin:12px 0;font-size:.88rem}
.manual-table th{background:#f1f5f9;color:#1e293b;padding:10px 14px;text-align:left;font-weight:700;border-bottom:2px solid #e2e8f0}
.manual-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#475569}
.manual-table tr:hover td{background:#f8fafc}
.tip-box{background:linear-gradient(135deg,#ede9fe,#e0e7ff);border-left:4px solid #7c3aed;border-radius:0 12px 12px 0;padding:14px 18px;margin:15px 0;font-size:.88rem;color:#3730a3;font-weight:500}
.warn-box{background:#fef3c7;border-left:4px solid #f59e0b;border-radius:0 12px 12px 0;padding:14px 18px;margin:15px 0;font-size:.88rem;color:#92400e;font-weight:500}
@media(max-width:768px){.manual-toc ul{grid-template-columns:1fr}.manual-hero{padding:30px 20px}.manual-hero h1{font-size:1.6rem}.manual-section{padding:20px}}
</style>

<div class="manual-wrap">

<!-- HERO -->
<div class="manual-hero">
    <h1>📘 Manuel de l'Administrateur</h1>
    <p>Votre guide complet pour maîtriser RHXtimes — Pointage intelligent, Gestion RH et Préparation de la paie.</p>
</div>

<!-- TABLE DES MATIÈRES -->
<div class="manual-toc">
    <h3>📑 Sommaire</h3>
    <ul>
        <li><a href="#sec-dashboard">📊 Tableau de bord</a></li>
        <li><a href="#sec-xtimes">⏱️ Pointage Xtimes</a></li>
        <li><a href="#sec-lieux">📍 Lieux & Horaires</a></li>
        <li><a href="#sec-employes">👤 Gestion des Employés</a></li>
        <li><a href="#sec-managers">👥 Gestion des Managers</a></li>
        <li><a href="#sec-rapports">📈 Rapports de Pointages</a></li>
        <li><a href="#sec-paie">💰 Calcul de la Paie</a></li>
        <li><a href="#sec-messages">💬 Messagerie Interne</a></li>
        <li><a href="#sec-billing">💎 Mon Compte & Facturation</a></li>
        <li><a href="#sec-support">🆘 Support & Aide</a></li>
        <li><a href="#sec-faq">❓ FAQ</a></li>
    </ul>
</div>

<!-- ═══════ PARTIE 1 ═══════ -->
<div class="manual-part" id="part1">🖥️ PARTIE 1 — Configuration Minimale pour Bien Débuter</div>

<div class="manual-section">
    <h3><span class="section-icon" style="background:#e0e7ff;color:#4f46e5">💻</span> Appareils & Navigateur</h3>
    <table class="manual-table">
        <tr><th>Appareil</th><th>Usage</th><th>Pourquoi ?</th></tr>
        <tr><td><strong>Ordinateur</strong></td><td>✅ Admin, Rapports, Paie</td><td>Confort visuel pour tableaux et exports</td></tr>
        <tr><td><strong>Tablette</strong></td><td>✅ Supervision terrain</td><td>Bonne mobilité, écran suffisant</td></tr>
        <tr><td><strong>Smartphone</strong></td><td>⚠️ Consultation rapide</td><td>Idéal pour les employés (pointage)</td></tr>
    </table>
    <p>Utilisez un navigateur moderne : <strong>Google Chrome</strong> (recommandé), Safari, Edge ou Firefox.</p>

    <h4>Installer l'app sur votre écran d'accueil (PWA)</h4>
    <p><strong>Android :</strong> Chrome → Menu ⋮ → <strong>« Ajouter à l'écran d'accueil »</strong><br>
    <strong>iPhone :</strong> Safari → Icône Partager → <strong>« Sur l'écran d'accueil »</strong></p>
</div>

<div class="manual-section">
    <h3><span class="section-icon" style="background:#dcfce7;color:#16a34a">📍</span> Localisation GPS & Batterie</h3>
    <p>Le GPS est le <strong>cœur du système de pointage</strong>. Lorsque l'application demande l'autorisation, sélectionnez <strong>« Toujours autoriser »</strong>.</p>
    <div class="warn-box">⚠️ Désactivez le mode <strong>« Économie d'énergie ultra »</strong> pour le navigateur. Ce mode peut empêcher le GPS de fonctionner correctement.</div>
</div>

<div class="manual-section">
    <h3><span class="section-icon" style="background:#fef3c7;color:#d97706">🔐</span> Première Connexion</h3>
    <ul>
        <li>Vérifiez votre <strong>boîte email</strong> (y compris les spams) après l'inscription.</li>
        <li>Cliquez sur <strong>« ✉️ Confirmer mon adresse email »</strong>.</li>
        <li>Connectez-vous avec votre email et mot de passe.</li>
    </ul>
    <div class="warn-box">⏰ Le lien de vérification expire après <strong>24 heures</strong>. Utilisez « Renvoyer l'email » si besoin.</div>
</div>

<!-- ═══════ PARTIE 2 ═══════ -->
<div class="manual-part" id="part2">🎯 PARTIE 2 — Guide Complet du Tableau de Bord</div>

<!-- 1. DASHBOARD -->
<div class="manual-section" id="sec-dashboard">
    <h3><span class="section-icon" style="background:#e0e7ff;color:#4f46e5">📊</span> Tableau de Bord</h3>
    <h4>Utilité</h4>
    <p>Votre <strong>quartier général</strong>. En un coup d'œil, visualisez l'activité de votre entreprise en temps réel.</p>
    <h4>Compteurs affichés</h4>
    <table class="manual-table">
        <tr><th>Compteur</th><th>Couleur</th><th>Signification</th></tr>
        <tr><td>Total Employés</td><td>🔵 Indigo</td><td>Employés actifs dans votre entreprise</td></tr>
        <tr><td>Arrivées validées</td><td>🟢 Vert</td><td>Pointages d'arrivée du jour</td></tr>
        <tr><td>Départs validés</td><td>🔵 Bleu</td><td>Pointages de départ du jour</td></tr>
        <tr><td>Employés en retard</td><td>🟡 Orange</td><td>Arrivées après l'heure prévue</td></tr>
        <tr><td>Employés absents</td><td>🔴 Rouge</td><td>Aucun pointage enregistré</td></tr>
    </table>
    <p>Le <strong>flux d'activité</strong> sous les compteurs affiche les derniers pointages. Filtrez par employé ou date.</p>
</div>

<!-- 2. XTIMES -->
<div class="manual-section" id="sec-xtimes">
    <h3><span class="section-icon" style="background:#fef3c7;color:#d97706">⏱️</span> Pointage Xtimes</h3>
    <h4>Utilité</h4>
    <p>Vue opérationnelle de la journée : pour chaque employé planifié, son <strong>statut de présence</strong>.</p>
    <h4>Statuts possibles</h4>
    <table class="manual-table">
        <tr><th>Statut</th><th>Signification</th></tr>
        <tr><td>✅ A l'heure</td><td>Arrivée dans les délais</td></tr>
        <tr><td>⚠️ En retard</td><td>Arrivée après l'heure + tolérance</td></tr>
        <tr><td>🔵 Retard justifié</td><td>Justification validée par un admin</td></tr>
        <tr><td>🔴 Absent</td><td>Aucun pointage, journée planifiée</td></tr>
        <tr><td>🔵 Absence justifiée</td><td>Absence validée par un admin</td></tr>
        <tr><td>🟡 Non planifié</td><td>Pointage un jour de repos</td></tr>
    </table>
    <div class="tip-box">💡 Le bouton <strong>« Justifier »</strong> apparaît à côté des retards et absences. Cliquez dessus pour que la retenue ne soit pas comptée en paie.</div>
</div>

<!-- 3. LIEUX -->
<div class="manual-section" id="sec-lieux">
    <h3><span class="section-icon" style="background:#dcfce7;color:#16a34a">📍</span> Lieux & Horaires</h3>
    <h4>Créer un site de travail</h4>
    <ol style="color:#475569;line-height:1.8;font-size:.93rem;padding-left:20px">
        <li>Cliquez sur <strong>« + Ajouter un site »</strong></li>
        <li>Renseignez le <strong>Nom du site</strong> (ex: « Siège Bastos »)</li>
        <li>Cliquez sur <strong>📍 Utiliser ma position actuelle</strong> — soyez physiquement sur le lieu !</li>
        <li><strong>Rayon (mètres)</strong> : zone de pointage acceptée (recommandé : 50m bureau, 100-200m chantier)</li>
        <li><strong>Tolérance retard (minutes)</strong> : marge avant de marquer « En retard »</li>
        <li>Choisissez la <strong>méthode de pointage</strong> :
            <ul>
                <li><strong>📍 One-Tap</strong> — Un clic, le GPS vérifie la position</li>
                <li><strong>📷 QR Code</strong> — L'employé scanne un code affiché sur site</li>
            </ul>
        </li>
    </ol>
    <h4>Configurer les Horaires</h4>
    <p>Cliquez sur <strong>🕐 Horaires de Travail</strong> sur la fiche du site. Définissez pour chaque jour : heure de début, heure de fin, et si c'est un jour de repos.</p>
    <div class="tip-box">💡 <strong>QR Code :</strong> Imprimez-le en PDF depuis le bouton dédié sur la fiche du site. Plastifiez-le et affichez-le à l'entrée !</div>
</div>

<!-- 4. EMPLOYÉS -->
<div class="manual-section" id="sec-employes">
    <h3><span class="section-icon" style="background:#dbeafe;color:#2563eb">👤</span> Gestion des Employés</h3>
    <h4>Ajouter un employé</h4>
    <p>Cliquez sur <strong>« + Ajouter un employé »</strong> et renseignez : Nom, Prénom, Lieu d'affectation, Fonction, Salaire de base, Téléphone (identifiant de connexion), Mot de passe.</p>
    <h4>Actions disponibles</h4>
    <table class="manual-table">
        <tr><th>Bouton</th><th>Action</th></tr>
        <tr><td>📱 <strong>Débloquer</strong></td><td>Réinitialise la liaison téléphone. Utile si l'employé change d'appareil.</td></tr>
        <tr><td>✏️ <strong>Modifier</strong></td><td>Modifie les informations (nom, lieu, salaire, mot de passe).</td></tr>
        <tr><td>🚫 <strong>Suspendre</strong></td><td>Désactive le compte. L'employé ne peut plus pointer.</td></tr>
    </table>
    <h4>Sécurité : Liaison au Téléphone (Device ID)</h4>
    <p>À la première connexion, RHXtimes enregistre l'appareil de l'employé. <strong>Il ne pourra pointer que depuis ce téléphone</strong>, empêchant toute fraude.</p>
</div>

<!-- 5. MANAGERS -->
<div class="manual-section" id="sec-managers">
    <h3><span class="section-icon" style="background:#fce7f3;color:#db2777">👥</span> Gestion des Managers</h3>
    <p>Réservé aux comptes <strong>Propriétaire (Owner)</strong>. Déléguez la supervision à des collaborateurs de confiance.</p>
    <h4>Rôles disponibles</h4>
    <table class="manual-table">
        <tr><th>Rôle</th><th>Accès</th><th>Cas d'usage</th></tr>
        <tr><td><strong>Manager</strong></td><td>Site(s) affectés uniquement</td><td>Superviseur terrain, chef d'équipe</td></tr>
        <tr><td><strong>Co-Propriétaire</strong></td><td>Accès total (tous sites)</td><td>Associé, DG adjoint</td></tr>
    </table>
    <h4>Périmètre d'action</h4>
    <p><strong>Tous les sites</strong> = vision globale. <strong>Un site spécifique</strong> = vision restreinte aux employés de ce site.</p>
</div>

<!-- 6. RAPPORTS -->
<div class="manual-section" id="sec-rapports">
    <h3><span class="section-icon" style="background:#e0e7ff;color:#4f46e5">📈</span> Rapports de Pointages</h3>
    <h4>Utilité</h4>
    <p>Analysez l'assiduité sur une période définie. Idéal pour les bilans mensuels et réunions RH.</p>
    <h4>Fonctionnement</h4>
    <ol style="color:#475569;line-height:1.8;font-size:.93rem;padding-left:20px">
        <li>Sélectionnez une <strong>date de début</strong> et une <strong>date de fin</strong></li>
        <li>Filtrez par <strong>employé</strong> ou par <strong>statut</strong> (Retard, Absent…)</li>
        <li>Consultez le tableau : nom, lieu, heures, statut — triés par date décroissante</li>
    </ol>
</div>

<!-- 7. PAIE -->
<div class="manual-section" id="sec-paie">
    <h3><span class="section-icon" style="background:#dcfce7;color:#16a34a">💰</span> Calcul de la Paie</h3>
    <h4>Rapport de Paie Mensuel</h4>
    <p>Sélectionnez le <strong>mois</strong>, l'<strong>année</strong> et un <strong>lieu</strong> (optionnel), puis cliquez sur <strong>Filtrer</strong>.</p>
    <table class="manual-table">
        <tr><th>Colonne</th><th>Description</th></tr>
        <tr><td><strong>Salaire Base</strong></td><td>Montant mensuel de la fiche employé</td></tr>
        <tr><td><strong>Retards (h)</strong></td><td>Total des heures de retard du mois</td></tr>
        <tr><td><strong>Retenue</strong></td><td>Montant déduit proportionnellement</td></tr>
        <tr><td><strong>Net à Payer</strong></td><td>Salaire + Primes − Retenues</td></tr>
    </table>
    <h4>Justifier un Retard</h4>
    <p>Dans <strong>Pointage Xtimes</strong>, cliquez sur <strong>« Justifier »</strong> à côté du retard. Résultat : il ne sera pas compté en paie.</p>
    <h4>Exporter</h4>
    <p>Cliquez sur <strong>Exporter</strong> pour télécharger le rapport au format CSV (Excel, Google Sheets).</p>
</div>

<!-- 8. MESSAGERIE -->
<div class="manual-section" id="sec-messages">
    <h3><span class="section-icon" style="background:#fef3c7;color:#d97706">💬</span> Messagerie Interne</h3>
    <h4>Utilité</h4>
    <p>Envoyez des consignes, alertes ou informations à chaque employé. Tout est archivé et traçable.</p>
    <h4>Fonctionnement</h4>
    <ul>
        <li>Sélectionnez un employé dans la <strong>liste de gauche</strong></li>
        <li>Rédigez votre message et cliquez sur <strong>Envoyer</strong></li>
        <li>La conversation se met à jour en <strong>temps réel</strong></li>
    </ul>
    <div class="tip-box">💡 Utilisez la messagerie pour les rappels de consignes de sécurité, confirmations de congés, ou changements d'horaire.</div>
</div>

<!-- 9. FACTURATION -->
<div class="manual-section" id="sec-billing">
    <h3><span class="section-icon" style="background:#ede9fe;color:#7c3aed">💎</span> Mon Compte & Facturation</h3>
    <h4>Informations de l'Entreprise</h4>
    <p>Modifiez le nom, le responsable, le téléphone, la ville et la localisation de votre entreprise.</p>
    <h4>Mon Abonnement</h4>
    <p>Consultez votre <strong>plan actif</strong>, le <strong>statut</strong>, la <strong>date d'expiration</strong> et les <strong>quotas</strong> (employés, sites, managers max).</p>
    <h4>Mettre à Niveau</h4>
    <p>Basculez entre <strong>Mensuel</strong> et <strong>Annuel</strong> (−20%). Paiement via <strong>Orange Money</strong>, <strong>MTN MoMo</strong> ou <strong>Carte Bancaire</strong>.</p>
    <h4>Code Promo</h4>
    <p>Entrez votre code dans le champ dédié et cliquez sur 🎁. Les jours sont ajoutés immédiatement.</p>
</div>

<!-- 10. SUPPORT -->
<div class="manual-section" id="sec-support">
    <h3><span class="section-icon" style="background:#fee2e2;color:#dc2626">🆘</span> Support & Aide</h3>
    <h4>Ouvrir un Ticket</h4>
    <ol style="color:#475569;line-height:1.8;font-size:.93rem;padding-left:20px">
        <li>Cliquez sur <strong>« + Nouveau Ticket »</strong></li>
        <li>Renseignez l'objet, la priorité (Basse/Moyenne/Haute), le message</li>
        <li>Joignez une capture d'écran si besoin (JPG, PNG, PDF, max 5 Mo)</li>
    </ol>
    <h4>Suivi des tickets</h4>
    <table class="manual-table">
        <tr><th>Statut</th><th>Signification</th></tr>
        <tr><td>🟡 En attente</td><td>Ticket reçu, traitement en cours</td></tr>
        <tr><td>🟢 Réponse reçue</td><td>L'équipe RHXtimes vous a répondu</td></tr>
        <tr><td>⚪ Fermé</td><td>Problème résolu</td></tr>
    </table>
</div>

<!-- FAQ -->
<div class="manual-section" id="sec-faq">
    <h3><span class="section-icon" style="background:#fef3c7;color:#d97706">❓</span> Questions Fréquentes</h3>
    <table class="manual-table">
        <tr><th>Question</th><th>Réponse</th></tr>
        <tr><td>Un employé a changé de téléphone ?</td><td>Gestion des Employés → bouton <strong>📱 Débloquer</strong></td></tr>
        <tr><td>Pointage refusé « Hors Zone » ?</td><td>Augmentez le <strong>rayon de tolérance</strong> dans Lieux & Horaires</td></tr>
        <tr><td>Comment annuler un retard injuste ?</td><td>Pointage Xtimes → cliquez sur <strong>Justifier</strong></td></tr>
        <tr><td>Abonnement expiré ?</td><td>Renouvelez depuis <strong>Mon Compte & Facturation</strong>. Vos données sont conservées.</td></tr>
        <tr><td>Plusieurs sites possibles ?</td><td>Oui, selon votre forfait. Chaque site a ses propres horaires et QR Code.</td></tr>
    </table>
</div>

<!-- FOOTER -->
<p style="text-align:center;color:#94a3b8;margin-top:40px;font-size:.85rem">© 2026 RHXtimes by Marvens Group — Tous droits réservés.</p>

</div>
