<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['id_etudiant'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['comment_id'], $_POST['id_projet'])) {
    header('Location: etudiant.php');
    exit();
}

$db = new Connexion();
$pdo = $db->getConnexion();

try {
    // Vérifier si l'étudiant connecté est bien l'auteur du commentaire
    $stmt = $pdo->prepare("SELECT auteur_id, auteur_type FROM commentaires WHERE id = ?");
    $stmt->execute([$_POST['comment_id']]);
    $commentaire = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commentaire) {
        throw new Exception("Commentaire non trouvé");
    }

    // Seul l'étudiant auteur peut supprimer son commentaire
    if ($commentaire['auteur_type'] === 'etudiant' && $commentaire['auteur_id'] == $_SESSION['id_etudiant']) {
        $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id = ?");
        $stmt->execute([$_POST['comment_id']]);
        header("Location: view.php?id=" . $_POST['id_projet']);
        exit();
    } else {
        throw new Exception("Vous n'avez pas la permission de supprimer ce commentaire.");
    }

} catch (Exception $e) {
    die("Erreur lors de la suppression du commentaire : " . $e->getMessage());
}
