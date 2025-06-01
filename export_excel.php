<?php
require_once 'cnx.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=projets_ensa.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Titre', 'Description', 'Date', 'Statut', 'Étudiant', 'Encadrant']);

$query = $db->query("SELECT p.titre, p.description, p.date_creation, p.statut,
    CONCAT(et.prenom, ' ', et.nom) AS etudiant, enc.nom AS encadrant
    FROM projets p
    JOIN etudiants et ON p.etudiant_id = et.id
    JOIN encadrant enc ON p.encadrant_id = enc.id");

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>