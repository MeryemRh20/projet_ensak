<?php
session_start();
require_once 'cnx.php';

$annees = ['1ère année', '2ème année', '3ème année', '4ème année', '5ème année'];
$filieres = [
    'genie informatique' => 'Génie Informatique',
    'genie industriel' => 'Génie Industriel',
    'genie electrique' => 'Génie Électrique',
    'mecatronique' => 'Mécatronique',
    'reseaux' => 'Réseaux',
    'biee' => 'BIEE'
];

$connexion = new Connexion();
$db = $connexion->getConnexion();

// Nombre total de projets
$nb_total = $db->query("SELECT COUNT(*) FROM projets")->fetchColumn();

// Projets par validation
$query_validation = $db->query("
    SELECT 
        CASE 
            WHEN validé = 1 THEN 'Validé'
            ELSE 'Non validé'
        END AS statut,
        COUNT(*) AS total
    FROM projets
    GROUP BY validé
");
$stats_statut = $query_validation->fetchAll(PDO::FETCH_ASSOC);

// Projets par filière (dynamique selon la base)
$stmt = $db->query("
    SELECT e.filiere, COUNT(*) as total 
    FROM projets p
    JOIN etudiants e ON p.id_etudiant = e.id
    GROUP BY e.filiere
");

$stats_filiere = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $code = strtolower(trim($row['filiere']));
    $nom = $filieres[$code] ?? ucfirst($row['filiere']); // Si non reconnu, on affiche brut avec ucfirst
    $stats_filiere[] = ['code' => $code, 'nom' => $nom, 'total' => $row['total']];
}

// Projets par année
$stats_annee = [];
foreach ($annees as $an) {
    $annee_num = preg_replace('/[^0-9]/', '', $an);
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM projets p
        JOIN etudiants e ON p.id_etudiant = e.id
        WHERE e.annee_etude = ?
    ");
    $stmt->execute([$annee_num]);
    $stats_annee[] = ['nom' => $an, 'total' => $stmt->fetchColumn()];
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques - Admin</title>
    <link rel="stylesheet" href="css/etudiant.css">
    <style>
        .stat-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .stat-table th, .stat-table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        .stat-table th {
            background-color: #f9f9f9;
            color: #2c3e50;
        }
        .stat-icon {
            font-size: 26px;
            margin-right: 10px;
        }
        .no-data {
            font-style: italic;
            color: #888;
        }
        .project-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="top-bar">
    <div class="logo">
        <img src="image/logo_ensa.png" alt="ENSA Logo">
    </div>
    <div class="top-icons">
        <a href="#" target="_blank"><img src="image/ig-icon.png" alt="Instagram"></a>
        <a href="#" target="_blank"><img src="image/link-icone.png" alt="Lien utile"></a>
        <div class="dropdown">
            <button class="dropbtn">&#9776;</button>
            <div class="dropdown-content">
                <a href="index.php">Accueil</a>
                <a href="dashboard.php">Projets</a>
                <a href="statistiques.php">Statistiques</a>
                <a href="gestion_utilisateurs.php">Utilisateurs</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</div>

<header>
    <h1>Statistiques des projets étudiants</h1>
</header>

<div class="container">

    <div class="project-card">
        <h2><span class="stat-icon">📊</span>Nombre total de projets</h2>
        <p><strong><?= $nb_total ?></strong> projets enregistrés dans le système.</p>
    </div>

    <div class="project-card">
        <h2><span class="stat-icon">📂</span>Projets par statut</h2>
        <?php if (count($stats_statut) > 0): ?>
            <table class="stat-table">
                <thead><tr><th>Statut</th><th>Nombre</th></tr></thead>
                <tbody>
                    <?php foreach ($stats_statut as $row): ?>
                        <tr><td><?= htmlspecialchars(ucfirst($row['statut'])) ?></td><td><?= $row['total'] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">Aucun projet classé par statut.</p>
        <?php endif; ?>
    </div>

    <div class="project-card">
        <h2><span class="stat-icon">🗓️</span>Projets par année</h2>
        <table class="stat-table">
            <thead><tr><th>Année</th><th>Nombre</th></tr></thead>
            <tbody>
                <?php foreach ($stats_annee as $row): ?>
                    <tr><td><?= htmlspecialchars($row['nom']) ?></td><td><?= $row['total'] ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="project-card">
        <h2><span class="stat-icon">🎓</span>Projets par filière</h2>
        <?php if (count($stats_filiere) > 0): ?>
            <table class="stat-table">
                <thead><tr><th>Filière</th><th>Nombre</th></tr></thead>
                <tbody>
                    <?php foreach ($stats_filiere as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nom']) ?></td>
                            <td><?= $row['total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">Aucune donnée par filière.</p>
        <?php endif; ?>
    </div>

</div>

<script>
document.querySelector(".dropbtn").addEventListener("click", function () {
    document.querySelector(".dropdown-content").classList.toggle("show");
});
window.addEventListener("click", function (e) {
    if (!e.target.matches('.dropbtn')) {
        document.querySelector(".dropdown-content").classList.remove("show");
    }
});
</script>
</body>
</html>