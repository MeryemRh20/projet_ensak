<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['id_etudiant']) || !isset($_GET['id'])) {
    header('Location: login.php');
    exit();
}

$db = new Connexion();
$pdo = $db->getConnexion();

try {
    // 1. Récupérer les infos du projet pour vérifier l'appartenance
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE id = ? AND id_etudiant = ?");
    $stmt->execute([$_GET['id'], $_SESSION['id_etudiant']]);
    $projet = $stmt->fetch();

    if (!$projet) {
        die("Projet non trouvé ou non autorisé");
    }

    $stmt = $pdo->prepare("SELECT chemin_fichier FROM livrables WHERE id_projet = ?");
    $stmt->execute([$_GET['id']]);
    $livrables = $stmt->fetchAll();

    foreach ($livrables as $livrable) {
        $filePath = 'uploads/' . $livrable['chemin_fichier'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $pdo->beginTransaction();
    $pdo->exec("DELETE FROM commentaires WHERE id_projet = " . $_GET['id']);
    $pdo->exec("DELETE FROM livrables WHERE id_projet = " . $_GET['id']);
    $pdo->exec("DELETE FROM projets WHERE id = " . $_GET['id']);
    $pdo->commit();

    header('Location: etudiant.php?success=1');
} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur : " . $e->getMessage());
}
?>