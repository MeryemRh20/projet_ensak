<?php
session_start();
require_once 'cnx.php';

if (!isset($_GET['id'])) {
    die("Étudiant non spécifié.");
}

$id = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $connexion = new Connexion();
    $db = $connexion->getConnexion();
    $stmt = $db->prepare("UPDATE etudiants SET nom = :nom, prenom = :prenom, email = :email WHERE id = :id");
    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':id' => $id
    ]);

    // Redirection correcte après enregistrement
    header("Location: gestion_utilisateurs.php");
    exit;
}
$connexion = new Connexion();
$db = $connexion->getConnexion();
$stmt = $db->prepare("SELECT * FROM etudiants WHERE id = ?");
$stmt->execute([$id]);
$etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$etudiant) {
    die("Étudiant introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier étudiant</title>
    <link rel="stylesheet" href="etudiants.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            margin: 0;
            padding: 40px;
            background-color: #f4f4f9;
        }
        .modifier-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        .modifier-container h2 {
            color: #002c84;
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        button[type="submit"],
        .btn-cancel {
            background-color: #002c84;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-cancel {
            background-color: #95a5a6;
            margin-left: 10px;
        }
        button:hover {
            background-color: #0040c1;
        }
        .btn-cancel:hover {
            background-color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="modifier-container">
        <h2>Modifier l'étudiant</h2>
        <form method="POST">
            <label>Nom :</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($etudiant['nom']) ?>" required>

            <label>Prénom :</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($etudiant['prenom']) ?>" required>

            <label>Email :</label>
            <input type="email" name="email" value="<?= htmlspecialchars($etudiant['email']) ?>" required>

            <button type="submit">Enregistrer</button>
            <a href="gestion_utilisateurs.php" class="btn-cancel">Annuler</a>
        </form>
    </div>
</body>
</html>
