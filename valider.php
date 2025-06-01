<?php
session_start();
require_once 'cnx.php';

$connexion = new Connexion();
$pdo = $connexion->getConnexion();


// 🔐 Sécurité : enseignant connecté ?
if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

$id_enseignant = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_projet = $_POST['id_projet'] ?? null;
    $remarque = trim($_POST['remarque'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if (!$id_projet || $note === '') {
        die("Paramètres manquants ou note vide.");
    }

    try {
        // ✅ 1. Mettre à jour le projet : validé = 1 + enregistrer la note
        $stmt = $pdo->prepare("UPDATE projets SET validé = 1, note = :note WHERE id = :id");
        $stmt->execute([
            'note' => $note,
            'id' => $id_projet
        ]);

        // ✅ 2. Ajouter la remarque dans les commentaires
        if (!empty($remarque)) {
            $stmt2 = $pdo->prepare("
                INSERT INTO commentaires (id_projet, contenu, auteur_type, auteur_id)
                VALUES (:id_projet, :contenu, 'enseignant', :auteur_id)
            ");
            $stmt2->execute([
                'id_projet' => $id_projet,
                'contenu' => $remarque,
                'auteur_id' => $id_enseignant
            ]);
        }

        // ✅ 3. Redirection
        header("Location: projets.php?success=1");
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de la validation : " . $e->getMessage());
    }
}
?>

