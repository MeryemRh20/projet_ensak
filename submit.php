<?php

session_start();
require_once 'cnx.php';

// Initialisation de la connexion
$db = new Connexion();
$conn = $db->getConnexion();

if (!isset($_SESSION['id_etudiant'])) {
    header('Location: connect_etudiant.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données POST
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $categorie = $_POST['categorie'] ?? '';
    $type_projet = $_POST['type_projet'] ?? '';
    $stage_type = ($type_projet === 'stage') ? ($_POST['stage_type'] ?? '') : null;
    $id_etudiant = $_SESSION['id_etudiant'];

    // Validation des champs
    if (empty($titre)) $errors[] = "Le titre est obligatoire";
    if (empty($description)) $errors[] = "La description est obligatoire";
    if (empty($categorie)) $errors[] = "La catégorie est obligatoire";
    if (empty($type_projet)) $errors[] = "Le type de projet est obligatoire";
    if ($type_projet === 'stage' && empty($stage_type)) {
        $errors[] = "Le type de stage est obligatoire";
    }
    if (empty($_FILES['livrables']['name'][0])) $errors[] = "Au moins un fichier livrable est requis";

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("INSERT INTO projets (titre, description, categorie, type_projet, stage_type, id_etudiant) 
                                  VALUES (:titre, :description, :categorie, :type_projet, :stage_type, :id_etudiant)");
            $stmt->execute([
                ':titre' => $titre,
                ':description' => $description,
                ':categorie' => $categorie,
                ':type_projet' => $type_projet,
                ':stage_type' => $stage_type,
                ':id_etudiant' => $id_etudiant
            ]);
            
            $id_projet = $conn->lastInsertId();

            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['livrables']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['livrables']['error'][$key] === UPLOAD_ERR_OK) {
                    $originalName = basename($_FILES['livrables']['name'][$key]);
                    $fileExt = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newName = uniqid() . '.' . $fileExt;
                    $targetPath = $uploadDir . $newName;
                    
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $stmt = $conn->prepare("INSERT INTO livrables (id_projet, nom_fichier, type_fichier, chemin_fichier) 
                                              VALUES (:id_projet, :nom_fichier, :type_fichier, :chemin_fichier)");
                        $stmt->execute([
                            ':id_projet' => $id_projet,
                            ':nom_fichier' => $originalName,
                            ':type_fichier' => $fileExt,
                            ':chemin_fichier' => $targetPath
                        ]);
                    }
                }
            }

            $conn->commit();
            $success = true;
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Erreur technique: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soumission de Projet - Espace Étudiant</title>
    <link rel="stylesheet" href="css/update.css">
    <script>
        function updateStageType() {
            const projectType = document.getElementById('type_projet').value;
            const stageTypeDiv = document.getElementById('stage-type-group');
            
            if (projectType === 'stage') {
                stageTypeDiv.style.display = 'block';
            } else {
                stageTypeDiv.style.display = 'none';
            }
        }
        
        // Appeler la fonction au chargement de la page
        window.onload = updateStageType;
    </script>
</head>
<body>
    <div class="top-bar">
        <a href="etudiant.php" class="back-button">Retour à mes projets</a>
    </div>
    <div class="container">
        <h1>Soumettre un Nouveau Projet</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <h3>Erreurs :</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($success): ?>
            <div class="success">
                Projet soumis avec succès!
            </div>
        <?php endif; ?>
        
        <form action="submit.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre">Titre du Projet:</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="categorie">Catégorie:</label>
                <input type="text" id="categorie" name="categorie" value="<?= htmlspecialchars($_POST['categorie'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="type_projet">Type de Projet:</label>
                <select id="type_projet" name="type_projet" required onchange="updateStageType()">
                    <option value="">-- Sélectionnez --</option>
                    <option value="stage" <?= ($_POST['type_projet'] ?? '') === 'stage' ? 'selected' : '' ?>>Stage</option>
                    <option value="module" <?= ($_POST['type_projet'] ?? '') === 'module' ? 'selected' : '' ?>>Module</option>
                </select>
            </div>
            
            <div class="form-group" id="stage-type-group" style="display: none;">
                <label for="stage_type">Type de Stage:</label>
                <select id="stage_type" name="stage_type">
                    <option value="">-- Sélectionnez le type de stage --</option>
                    <option value="observation" <?= ($_POST['stage_type'] ?? '') === 'observation' ? 'selected' : '' ?>>Stage d'observation</option>
                    <option value="pfa" <?= ($_POST['stage_type'] ?? '') === 'pfa' ? 'selected' : '' ?>>Stage PFA</option>
                    <option value="pfe" <?= ($_POST['stage_type'] ?? '') === 'pfe' ? 'selected' : '' ?>>Stage PFE</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="livrables">Livrables:</label>
                <input type="file" id="livrables" name="livrables[]" multiple>
                <small>Vous pouvez sélectionner plusieurs fichiers (rapport PDF, diaporama, code source...)</small>
            </div>
            
            <div class="form-actions">
                <input type="submit" class="btn-submit" value="Soumettre le Projet">
                <a href="etudiant.php" class="btn-cancel">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>