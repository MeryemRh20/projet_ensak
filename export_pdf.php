<?php
require_once 'cnx.php';
require_once 'cnx.php';
require('fpdf/fpdf.php'); // assure-toi que fpdf est bien installé

$connexion = new Connexion();
$db = $connexion->getConnexion();

$query = $db->query("
    SELECT p.titre, p.description, p.date_soumission AS date_creation, p.validé,
           CONCAT(et.prenom, ' ', et.nom) AS etudiant
    FROM projets p
    JOIN etudiants et ON p.id_etudiant = et.id
");

$projets = $query->fetchAll(PDO::FETCH_ASSOC);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Liste des Projets ENSA',0,1,'C');
$pdf->Ln(10);
$pdf->SetFont('Arial','',11);

foreach ($projets as $p) {
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(30, 8, 'Titre :');
    $pdf->SetFont('Arial','',11);
    $pdf->MultiCell(0, 8, $p['titre']);

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(30, 8, 'Description :');
    $pdf->SetFont('Arial','',11);
    $pdf->MultiCell(0, 8, $p['description']);

    $pdf->Cell(30, 8, 'Date :');
    $pdf->Cell(0, 8, $p['date_creation'], 0, 1);

    $pdf->Cell(30, 8, 'Statut :');
    $statut = $p['validé'] ? 'Validé' : 'Non validé';
    $pdf->Cell(0, 8, $statut, 0, 1);

    $pdf->Cell(30, 8, 'Étudiant :');
    $pdf->Cell(0, 8, $p['etudiant'], 0, 1);

    $pdf->Ln(5);
}

$pdf->Output('D', 'tous_les_projets_ensa.pdf');
exit;
?>
