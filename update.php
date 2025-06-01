<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['id_etudiant'])) {
    header('Location: connect_etudiant.php');
    exit();
}

$db = new Connexion();
$pdo = $db->getConnexion();

$project_id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE id = :id AND id_etudiant = :etudiant_id");
    $stmt->execute([':id' => $project_id, ':etudiant_id' => $_SESSION['id_etudiant']]);
    $projet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$projet) {
        die("Projet non trouvé ou vous n'avez pas les droits de modification.");
    }

    $stmt = $pdo->prepare("SELECT * FROM livrables WHERE id_projet = :id_projet");
    $stmt->execute([':id_projet' => $project_id]);
    $livrables = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $type_projet = trim($_POST['type_projet'] ?? '');
    $stage_type = ($type_projet === 'stage') ? ($_POST['stage_type'] ?? '') : null;

    if ($type_projet === 'stage' && empty($stage_type)) {
        $error = "Le type de stage est obligatoire";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE projets SET 
                                titre = :titre, 
                                description = :description, 
                                categorie = :categorie,
                                type_projet = :type_projet,
                                stage_type = :stage_type
                                WHERE id = :id AND id_etudiant = :etudiant_id");
            $stmt->execute([
                ':titre' => $titre,
                ':description' => $description,
                ':categorie' => $categorie,
                ':type_projet' => $type_projet,
                ':stage_type' => $stage_type,
                ':id' => $project_id,
                ':etudiant_id' => $_SESSION['id_etudiant']
            ]);

        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!empty($_FILES['new_files']['name'][0])) {
            foreach ($_FILES['new_files']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['new_files']['error'][$key] === UPLOAD_ERR_OK) {
                    $originalName = basename($_FILES['new_files']['name'][$key]);
                    $fileExt = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newName = uniqid() . '.' . $fileExt;
                    $targetPath = $uploadDir . $newName;
                    
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $stmt = $pdo->prepare("INSERT INTO livrables 
                                            (id_projet, nom_fichier, type_fichier, chemin_fichier) 
                                            VALUES (:id_projet, :nom_fichier, :type_fichier, :chemin_fichier)");
                        $stmt->execute([
                            ':id_projet' => $project_id,
                            ':nom_fichier' => $originalName,
                            ':type_fichier' => $fileExt,
                            ':chemin_fichier' => $targetPath
                        ]);
                    }
                }
            }
        }
        

        if (!empty($_POST['delete_files'])) {
            foreach ($_POST['delete_files'] as $file_id) {
                $stmt = $pdo->prepare("SELECT chemin_fichier FROM livrables WHERE id = :file_id AND id_projet = :project_id");
                $stmt->execute([':file_id' => $file_id, ':project_id' => $project_id]);
                $file = $stmt->fetch();
                
                if ($file && file_exists($file['chemin_fichier'])) {
                    unlink($file['chemin_fichier']);
                }

                $stmt = $pdo->prepare("DELETE FROM livrables WHERE id = :file_id");
                $stmt->execute([':file_id' => $file_id]);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Projet mis à jour avec succès!";
        header("Location: etudiant.php");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Erreur lors de la mise à jour: " . $e->getMessage();
    }
}
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Projet - Espace Étudiant</title>
    <link rel="stylesheet" href="css/update.css">
    <script>
        function toggleFileUpload() {
            const checkboxes = document.querySelectorAll('input[name="delete_files[]"]:checked');
            const fileUpload = document.getElementById('file-upload-section');
            
            if (checkboxes.length > 0) {
                fileUpload.style.display = 'block';
            } else {
                fileUpload.style.display = 'none';
            }
        }

        function updateStageType() {
            const projectType = document.getElementById('type_projet').value;
            const stageTypeDiv = document.getElementById('stage-type-group');
            
            if (projectType === 'stage') {
                stageTypeDiv.style.display = 'block';
            } else {
                stageTypeDiv.style.display = 'none';
            }
        }

        // Initialiser au chargement
        window.onload = function() {
            updateStageType();
            toggleFileUpload();
        };
    </script>
</head>
<body>
    <div class="top-bar">
        <a href="etudiant.php" class="back-button">Retour à mes projets</a>
    </div>

    <div class="container">
        <h1>Modifier le Projet</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="project_id" value="<?= $project_id ?>">
            
            <div class="form-group">
                <label for="titre">Titre:</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($projet['titre']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?= htmlspecialchars($projet['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="categorie">Catégorie:</label>
                <input type="text" id="categorie" name="categorie" value="<?= htmlspecialchars($projet['categorie']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="type_projet">Type de Projet:</label>
                <select id="type_projet" name="type_projet" required onchange="updateStageType()">
                    <option value="">-- Sélectionnez --</option>
                    <option value="module" <?= $projet['type_projet'] === 'module' ? 'selected' : '' ?>>Module</option>
                    <option value="stage" <?= $projet['type_projet'] === 'stage' ? 'selected' : '' ?>>Stage</option>
                </select>
            </div>
            
            <div class="form-group" id="stage-type-group" style="display: <?= $projet['type_projet'] === 'stage' ? 'block' : 'none' ?>;">
                <label for="stage_type">Type de Stage:</label>
                <select id="stage_type" name="stage_type">
                    <option value="">-- Sélectionnez --</option>
                    <option value="observation" <?= ($projet['stage_type'] ?? '') === 'observation' ? 'selected' : '' ?>>Stage d'observation</option>
                    <option value="pfa" <?= ($projet['stage_type'] ?? '') === 'pfa' ? 'selected' : '' ?>>Stage PFA</option>
                    <option value="pfe" <?= ($projet['stage_type'] ?? '') === 'pfe' ? 'selected' : '' ?>>Stage PFE</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Fichiers existants:</label>
                <?php if (!empty($livrables)): ?>
                    <?php foreach ($livrables as $file): ?>
                        <div class="file-item">
                            <span><?= htmlspecialchars($file['nom_fichier']) ?></span>
                            <div class="file-actions">
                                <a href="<?= $file['chemin_fichier'] ?>" target="_blank" class="btn-view">Voir</a>
                                <label>
                                    <input type="checkbox" name="delete_files[]" value="<?= $file['id'] ?>" onclick="toggleFileUpload()"> Supprimer
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun fichier associé</p>
                <?php endif; ?>
            </div>
            
            <div id="file-upload-section" class="form-group" style="display: none;">
                <label for="new_files">Ajouter des fichiers:</label>
                <input type="file" id="new_files" name="new_files[]" multiple>
                <small>Format acceptés: PDF, ZIP, etc. (max 10Mo)</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">Enregistrer les modifications</button>
                <a href="etudiant.php" class="btn-cancel">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>