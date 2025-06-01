<?php
session_start();
require_once 'cnx.php';

$connexion = new Connexion();
$pdo = $connexion->getConnexion();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Projet non spécifié.");
}

$id_projet = $_GET['id'];

// Récupération des détails du projet
$query = "
SELECT 
    projets.*,
    etudiants.nom AS nom_etudiant,
    etudiants.prenom AS prenom_etudiant,
    etudiants.annee_etude
FROM projets
JOIN etudiants ON projets.id_etudiant = etudiants.id
WHERE projets.id = :id_projet
";

$stmt = $pdo->prepare($query);
$stmt->execute(['id_projet' => $id_projet]);
$projet = $stmt->fetch();

if (!$projet) {
    die("Projet introuvable.");
}

// Vérification du statut
$stmtC = $pdo->prepare("SELECT COUNT(*) FROM commentaires WHERE id_projet = :id AND auteur_type = 'enseignant'");
$stmtC->execute(['id' => $projet['id']]);
$has_comment = $stmtC->fetchColumn();

if ($projet['validé'] == 1) {
    $statut = 'validé';
} elseif ($has_comment > 0) {
    $statut = 'refusé';
} else {
    $statut = 'en attente';
}

// Récupération des livrables
$stmtL = $pdo->prepare("SELECT * FROM livrables WHERE id_projet = :id");
$stmtL->execute(['id' => $id_projet]);
$livrables = $stmtL->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du projet</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/details.css">
</head>
<body>

<!-- Top navigation -->
<div class="top-nav py-2 px-4" style="display: flex; justify-content: space-between; align-items: center;">
    <div class="logo">
        <a href="https://ensa.uit.ac.ma/" target="_blank" rel="noopener noreferrer">
            <img src="image/logo_ensa.png" alt="Logo ENSA Kénitra">
        </a>
    </div>
    <a class="retour" href="projets.php" style="white-space: nowrap;">Retour à la liste</a>
</div>

<div class="container mt-4">
    <h2 class="mb-4"><b>Détails du projet</b></h2>
    <div class="form-section">
        <div class="carte-projet">
            <h3><?= htmlspecialchars($projet['titre']) ?></h3>
            <div class="badges">
                <span class="badge bg-secondary"><?= htmlspecialchars($projet['categorie']) ?></span>
                <span class="badge bg-secondary"><?= htmlspecialchars($projet['type_projet']) ?></span>
                <span class="badge <?= $statut === 'validé' ? 'bg-success' : ($statut === 'refusé' ? 'bg-danger' : 'bg-warning') ?>">
                    <?= ucfirst($statut) ?>
                </span>
            </div><hr>

            <p class="section-titre">Description</p>
            <p><?= nl2br(htmlspecialchars($projet['description'])) ?></p><hr>

            <p class="section-titre">Informations sur le projet</p>
            <p><strong>Étudiant :</strong> <?= htmlspecialchars($projet['prenom_etudiant'] . ' ' . $projet['nom_etudiant']) ?></p>
            <p><strong>Année d'étude :</strong> <?= htmlspecialchars($projet['annee_etude']) ?></p><hr>

            <p class="section-titre">Fichiers</p>
            <div class="fichier">
                <?php if (count($livrables) > 0): ?>
                    <?php foreach ($livrables as $fichier): ?>
                        <i>
                            <?= $fichier['type_fichier'] === 'pdf' ? '📄' : ($fichier['type_fichier'] === 'zip' ? '💻' : '📎') ?>
                        </i>
                        <a href="download.php?file=<?= urlencode($fichier['chemin_fichier']) ?>&id=<?= $projet['id'] ?>">
                            <?= htmlspecialchars($fichier['nom_fichier']) ?>
                        </a><br>
                    <?php endforeach; ?>
                <?php else: ?>
                    <em>Aucun fichier soumis.</em>
                <?php endif; ?>
            </div>
        </div><br><hr>

        <!-- ✅ Formulaire de validation -->
        <form method="post" action="valider.php">
            <input type="hidden" name="id_projet" value="<?= $projet['id'] ?>">
            <div class="mb-3">
                <label for="remarque" class="form-label">Remarque :</label>
                <textarea name="remarque" id="remarque" class="form-control" rows="4"></textarea>
            </div>
            <div class="mb-3">
                <label for="note" class="form-label">Note (/20) :</label>
                <input type="number" name="note" id="note" class="form-control" min="0" max="20" step="0.5">
            </div>
            <button type="submit" class="btn btn-success">Valider le projet</button>
        </form>

        <!-- ❌ Formulaire de refus -->
        <form method="post" action="refuser.php" class="mt-3">
            <input type="hidden" name="id_projet" value="<?= $projet['id'] ?>">
            <div class="mb-3">
                <label for="remarque_refus" class="form-label">Motif du refus :</label>
                <textarea name="remarque" id="remarque_refus" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="note_refus" class="form-label">Note (/20) attribuée au refus :</label>
                <input type="number" name="note" id="note_refus" class="form-control" min="0" max="20" step="0.5" value="0">
            </div>

            <button type="submit" class="btn btn-danger">Refuser le projet</button>
        </form>
    </div>
</div>

</body>
</html>