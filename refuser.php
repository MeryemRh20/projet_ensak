<?php
session_start();
require_once 'cnx.php';

$connexion = new Connexion();
$pdo = $connexion->getConnexion();


// 🔐 Vérifier que l’enseignant est connecté
if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

$id_enseignant = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_projet = $_POST['id_projet'] ?? null;
    $remarque = trim($_POST['remarque'] ?? '');
    $note = trim($_POST['note'] ?? ''); // nouvelle récupération de la note

    if (!$id_projet || $remarque === '') {
        die("Projet ou remarque manquant.");
    }

    try {
        // ✅ 1. Mettre à jour le projet : refusé (validé = 0) + stocker la note
        $stmt = $pdo->prepare("
            UPDATE projets 
            SET validé = 0, note = :note 
            WHERE id = :id
        ");
        $stmt->execute([
            'note' => $note !== '' ? $note : '0', // Si vide → 0
            'id' => $id_projet
        ]);

        // ✅ 2. Ajouter le commentaire du refus dans la table commentaires
        $stmt2 = $pdo->prepare("
            INSERT INTO commentaires (id_projet, contenu, auteur_type, auteur_id)
            VALUES (:id_projet, :contenu, 'enseignant', :auteur_id)
        ");
        $stmt2->execute([
            'id_projet' => $id_projet,
            'contenu' => "Refus : " . $remarque,
            'auteur_id' => $id_enseignant
        ]);

        // ✅ 3. Redirection avec message
        header("Location: projets.php?refuse=1");
        exit;
    } catch (PDOException $e) {
        die("Erreur lors du refus : " . $e->getMessage());
    }
}
?>
