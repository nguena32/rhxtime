<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT nom, montant_mensuel, montant_annuel_mensualise FROM plans");
$plans = $stmt->fetchAll();
file_put_contents('scratch/plans_dump.json', json_encode($plans, JSON_PRETTY_PRINT));
echo "Dumped " . count($plans) . " plans.";
