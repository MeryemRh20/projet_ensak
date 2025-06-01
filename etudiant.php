<?php

session_start();
require_once 'cnx.php';

if (!isset($_SESSION['id_etudiant'])) {
    header('Location: connect_etudiant.php');
    exit();
}

$db = new Connexion();
$pdo = $db->getConnexion();

try {
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE id_etudiant = :id_etudiant ORDER BY date_soumission DESC");
    $stmt->execute([':id_etudiant' => $_SESSION['id_etudiant']]);
    $projets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des projets : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Espace Étudiant - Mes Projets</title>
    <link rel="stylesheet" href="css/etudiant.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo">
            <a href="https://ensa.uit.ac.ma/" target="_blank" rel="noopener noreferrer">
                <img src="image/logo_ensa.png" alt="Logo ENSA Kénitra" />
            </a>
        </div>
        <div class="top-icons">
            <a href="https://www.instagram.com/ensak.official" target="_blank"><img src="image/ig-icon.png" alt="Instagram" /></a>
            <a href="https://www.linkedin.com/company/ensa-kenitra-official?originalSubdomain=ma" target="_blank"><img src="image/link-icone.png" alt="LinkedIn" /></a>
            <div class="dropdown">
                <button class="dropbtn">☰</button>
                <div class="dropdown-content">
                    <a href="profil.php">Mon Profil</a>
                    <a href="logout.php">Déconnexion</a>
                </div>
            </div>
        </div>
    </div>

    <header>
        <h1>Espace Étudiant - Mes Projets</h1>
    </header>

    <div class="container">
        <a href="submit.php" class="btn btn-add">+ Soumettre un Nouveau Projet</a>

        <?php if (empty($projets)): ?>
            <div class="no-projects">
                <h2>Vous n'avez aucun projet pour le moment</h2>
                <p>Commencez par soumettre votre premier projet en cliquant sur le bouton ci-dessus.</p>
            </div>
        <?php else: ?>
            <?php foreach ($projets as $projet): 
                try {
                    $stmt = $pdo->prepare("SELECT * FROM livrables WHERE id_projet = :id_projet");
                    $stmt->execute([':id_projet' => $projet['id']]);
                    $livrables = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Erreur lors de la récupération des livrables : " . $e->getMessage());
                }
            ?>
                <div class="project-card">
                    <div class="project-header">
                        <h2 class="project-title"><?= htmlspecialchars($projet['titre']) ?></h2>
                        <div class="project-meta">
                            <span><?= htmlspecialchars($projet['categorie']) ?></span>
                            <span><?= $projet['type_projet'] === 'stage' ? 'Stage ['.htmlspecialchars($projet['stage_type']).']' : 'Module' ?></span>
                            <span class="project-date"><?= date('d/m/Y', strtotime($projet['date_soumission'])) ?></span>
                            <span class="project-status
                                <?php 
                                    if ($projet['validé'] === '1' || $projet['validé'] === 1) echo 'status-validated'; 
                                    elseif ($projet['validé'] === '0' || $projet['validé'] === 0) echo 'status-refused'; 
                                    else echo 'status-pending'; 
                                ?>">
                                <?php 
                                    if ($projet['validé'] === '1' || $projet['validé'] === 1) echo 'Validé'; 
                                    elseif ($projet['validé'] === '0' || $projet['validé'] === 0) echo 'Refusé'; 
                                    else echo 'En attente'; 
                                ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($livrables)): ?>
                        <div class="project-files">
                            <h4>Fichiers associés :</h4>
                            <?php foreach ($livrables as $livrable): ?>
                                <div class="file-item">
                                    <span class="file-icon">📄</span>
                                    <span><?= htmlspecialchars($livrable['nom_fichier']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="project-actions">
                        <a href="update.php?id=<?= $projet['id'] ?>" class="btn btn-edit">Modifier le projet</a>
                        <a href="view.php?id=<?= $projet['id'] ?>" class="btn btn-view">Voir les détails</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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
