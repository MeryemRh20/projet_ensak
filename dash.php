<?php
session_start();
require_once 'cnx.php';

$connexion = new Connexion();
$pdo = $connexion->getConnexion();


///////////////////////
// 🔐 SIMULATION DE CONNEXION ENSEIGNANT
if (!isset($_SESSION['user_id']) || !isset($_SESSION['prenom']) || !isset($_SESSION['nom'])) {
    header("Location: connect_prof.php");
    exit;
}

$id_enseignant = $_SESSION['user_id'];
$nom_complet = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
///////////////////////
// 📊 STATISTIQUES DES PROJETS

// Total
$totalQuery = $pdo->query("SELECT COUNT(*) FROM projets");
$total = $totalQuery->fetchColumn();

// Validés
$valideQuery = $pdo->query("SELECT COUNT(*) FROM projets WHERE validé = 1");
$valides = $valideQuery->fetchColumn();

// Refusés : validé = 0 ET commentaire laissé par un enseignant
$refuseQuery = $pdo->query("
    SELECT COUNT(DISTINCT projets.id)
    FROM projets
    JOIN commentaires ON commentaires.id_projet = projets.id
    WHERE projets.validé = 0 AND commentaires.auteur_type = 'enseignant'
");
$refuses = $refuseQuery->fetchColumn();

// En attente : validé = 0 ET aucun commentaire enseignant
$attenteQuery = $pdo->query("
    SELECT COUNT(*)
    FROM projets
    WHERE validé = 0 AND id NOT IN (
        SELECT id_projet FROM commentaires WHERE auteur_type = 'enseignant'
    )
");
$en_attente = $attenteQuery->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Enseignant</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<!-- 🔵 Top bar -->
<nav class="py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
    <div class="logo">
        <a href="https://ensa.uit.ac.ma/" target="_blank">
            <img src="image/logo_ensa.png" alt="Logo ENSA Kénitra">
        </a>
    </div>
    <div class="top-icons d-flex align-items-center gap-3">
        <a href="https://www.instagram.com/ensak.official" target="_blank"><img src="image/ig-icon.png" alt="Instagram"></a>
        <a href="https://www.linkedin.com/company/ensa-kenitra-official?originalSubdomain=ma" target="_blank"><img src="image/link-icone.png" alt="LinkedIn"></a>
        <div class="dropdown">
            <button class="dropbtn">☰</button>
            <div class="dropdown-content">
                <a href="dash.php">Mon Profil</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<!-- 🔷 Titre -->
<div class="top-nav py-2 px-4 d-flex justify-content-center align-items-center" style="background-color:#002f86; color:white;">
    <h3 class="m-0"><b>Espace Enseignant</b></h3>
</div>

<!-- ✅ Contenu -->
<div class="container mt-4">
    <h2 class="mb-4">Bienvenue, <?= htmlspecialchars($nom_complet) ?> !</h2>

    <div class="row">
        <div class="col-md-3">
            <div class="stat-card card-total text-center">
                <h5>Total Projets</h5>
                <h2><?= $total ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card card-attente text-center">
                <h5>En Attente</h5>
                <h2><?= $en_attente ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card card-valide text-center">
                <h5>Validés</h5>
                <h2><?= $valides ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card card-refuse text-center">
                <h5>Refusés</h5>
                <h2><?= $refuses ?></h2>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-3">
        <a href="projets.php" class="btn btn-detail">Voir tous les projets</a>
    </div>
</div>

<!-- ☰ Dropdown JS -->
<script>
document.querySelector(".dropbtn").addEventListener("click", function() {
    document.querySelector(".dropdown-content").classList.toggle("show");
});

window.addEventListener("click", function(event) {
    if (!event.target.matches('.dropbtn')) {
        const dropdowns = document.querySelectorAll(".dropdown-content");
        dropdowns.forEach(function(dropdown) {
            dropdown.classList.remove('show');
        });
    }
});
</script>

</body>
</html>