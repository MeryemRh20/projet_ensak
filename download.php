<?php
session_start();
require_once 'cnx.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Accès non autorisé.");
}

// Validation des entrées
$filePath = isset($_GET['file']) ? urldecode($_GET['file']) : null;
$projetId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$filePath || !$projetId) {
    header("HTTP/1.1 400 Bad Request");
    exit("Paramètres manquants.");
}

// Sécurité : vérifier l'appartenance du fichier au projet
$connexion = new Connexion();
$pdo = $connexion->getConnexion();

try {
    $stmt = $pdo->prepare("
        SELECT chemin_fichier, nom_fichier 
        FROM livrables 
        WHERE chemin_fichier = ? AND id_projet = ?
    ");
    $stmt->execute([$filePath, $projetId]);
    $fichier = $stmt->fetch();

    if (!$fichier) {
        header("HTTP/1.1 404 Not Found");
        exit("Fichier non trouvé.");
    }

    // Vérification physique du fichier
    if (!file_exists($filePath)) {
        header("HTTP/1.1 404 Not Found");
        exit("Fichier introuvable sur le serveur.");
    }

    // Téléchargement sécurisé
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($fichier['nom_fichier']) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Nettoyage du buffer et envoi du fichier
    ob_clean();
    flush();
    readfile($filePath);
    exit;

} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    exit("Erreur de base de données: " . $e->getMessage());
}
?>