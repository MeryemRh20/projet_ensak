<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['id_etudiant'])) {
    header('Location: connect_etudiant.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: etudiant.php');
    exit();
}

$db = new Connexion();
$pdo = $db->getConnexion();

try {
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE id = :id AND id_etudiant = :id_etudiant");
    $stmt->execute([
        ':id' => $_GET['id'],
        ':id_etudiant' => $_SESSION['id_etudiant']
    ]);
    $projet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$projet) {
        header('Location: etudiant.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM livrables WHERE id_projet = :id_projet");
    $stmt->execute([':id_projet' => $_GET['id']]);
    $livrables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT 
            c.*,
            COALESCE(e.prenom, p.prenom, en.prenom) AS auteur_prenom,
            COALESCE(e.nom, p.nom, en.nom) AS auteur_nom,
            COALESCE(e.email, p.email, en.email) AS auteur_email,
            CASE c.auteur_type
                WHEN 'etudiant' THEN 'Étudiant'
                WHEN 'enseignant' THEN 'Enseignant'
                WHEN 'admin' THEN 'Admin'
            END AS auteur_type
        FROM commentaires c
        LEFT JOIN etudiants e ON c.auteur_id = e.id AND c.auteur_type = 'etudiant'
        LEFT JOIN enseignant p ON c.auteur_id = p.id AND c.auteur_type = 'enseignant'
        LEFT JOIN admin en ON c.auteur_id = en.id AND c.auteur_type = 'admin'
        WHERE c.id_projet = :id_projet
        ORDER BY c.date_commentaire DESC
    ");
    $stmt->execute([':id_projet' => $_GET['id']]);
    $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du projet - <?= htmlspecialchars($projet['titre']) ?></title>
    <link rel="stylesheet" href="css/view.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo">
            <a href="https://ensa.uit.ac.ma/" target="_blank" rel="noopener noreferrer">
                <img src="image/logo_ensa.png" alt="Logo ENSA Kénitra">
            </a>
        </div>
        <div class="top-icons">
            <a href="etudiant.php">Retour à la liste</a>
            <div class="dropdown">
                <button class="dropbtn">☰</button>
                <div class="dropdown-content">
                    <a href="profil.php">Mon Profil</a>
                    <a href="logout.php">Déconnexion</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <header>
            <h1>Détails du projet</h1>
        </header>

        <div class="project-details">
            <div class="detail-header">
                <h2><?= htmlspecialchars($projet['titre']) ?></h2>
                <div class="detail-meta">
                    <span class="badge"><?= htmlspecialchars($projet['categorie']) ?></span>
                    <span class="badge"><?= $projet['type_projet'] === 'stage' ? 'Stage' : 'Module' ?></span>
                    <?php
                    $valid = $projet['validé'];
                    if ($valid === '1' || $valid === 1) {
                        echo '<span class="status-badge validated">Validé</span>';
                    } elseif ($valid === '0' || $valid === 0) {
                        echo '<span class="status-badge rejected">Refusé</span>';
                    } else {
                        echo '<span class="status-badge pending">En attente</span>';
                    }
                    ?>
                </div>
            </div>

            <div class="detail-section">
                <h3>Description</h3>
                <p><?= nl2br(htmlspecialchars($projet['description'])) ?></p>
            </div>

            <div class="detail-section">
                <h3>Informations techniques</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Date de soumission:</span>
                        <span><?= date('d/m/Y', strtotime($projet['date_soumission'])) ?></span>
                    </div>
                    <br>
                    <div class="detail-item">
                        <span class="detail-label">Note du professeur:</span>
                        <span>
                            <?= isset($projet['note']) ? htmlspecialchars($projet['note']) . '/20' : 'Non noté' ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if (!empty($livrables)): ?>
            <div class="detail-section">
                <h3>Fichiers joints</h3>
                <div class="file-list">
                    <?php foreach ($livrables as $livrable): ?>
                    <div class="file-item">
                        <span class="file-icon">📄</span>
                        <span class="file-name"><?= htmlspecialchars($livrable['nom_fichier']) ?></span>
                        <a href="download.php?file=<?= urlencode($livrable['chemin_fichier']) ?>&id=<?= $projet['id'] ?>" 
                        class="btn-download">Télécharger</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-section">
                <h3>Commentaires</h3>
                <?php if (!empty($commentaires)): ?>
                    <div class="comment-list">
                        <?php foreach ($commentaires as $commentaire): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <span class="comment-author">
                                    <?= htmlspecialchars($commentaire['auteur_prenom'] . ' ' . $commentaire['auteur_nom']) ?>
                                </span>
                                <span class="comment-date"><?= date('d/m/Y H:i', strtotime($commentaire['date_commentaire'])) ?></span>
                            </div>
                            <div class="comment-content">
                                <?= nl2br(htmlspecialchars($commentaire['contenu'])) ?>
                            </div>
                            <div class="comment-meta">
                                <small>Posté par: <?= htmlspecialchars($commentaire['auteur_type']) ?> (<?= htmlspecialchars($commentaire['auteur_email']) ?>)</small>
                            </div>
                            <?php if (
                                isset($_SESSION['id_etudiant']) &&
                                $commentaire['auteur_type'] === 'Étudiant' && 
                                $commentaire['auteur_id'] == $_SESSION['id_etudiant']
                            ): ?>
                            <form method="POST" action="delete_comment.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">
                                <input type="hidden" name="comment_id" value="<?= $commentaire['id'] ?>">
                                <input type="hidden" name="id_projet" value="<?= $_GET['id'] ?>">
                                <button type="submit" class="btn-supprimer btn-supprimer-sm">Supprimer</button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucun commentaire pour ce projet.</p>
                <?php endif; ?>

                <form action="add_comments.php" method="POST" class="comment-form">
                    <input type="hidden" name="id_projet" value="<?= $projet['id'] ?>">
                    <textarea name="contenu" placeholder="Ajouter un commentaire..." required></textarea>
                    <button type="submit" class="btn-submit">Envoyer</button>
                </form>
            </div>

            <div class="detail-actions">
                <a href="update.php?id=<?= $projet['id'] ?>" class="btn-edit">Modifier le projet</a>
                <a href="delete.php?id=<?= $projet['id'] ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet?');">Supprimer le projet</a>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('.dropbtn').addEventListener('click', function(event) {
            event.stopPropagation();
            document.querySelector('.dropdown-content').classList.toggle('show');
        });

        window.addEventListener('click', function() {
            const dropdown = document.querySelector('.dropdown-content');
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>
