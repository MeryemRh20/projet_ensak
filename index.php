<?php
session_start();
require_once 'cnx.php';
//require_once 'auth/check_admin.php'; 
// active si tu veux restreindre l'accès
$connexion = new Connexion();
$db = $connexion->getConnexion();
$utilisateurs = $db->query("SELECT id, nom, prenom, email FROM etudiants ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des étudiants</title>
    <link rel="stylesheet" href="css/etudiant.css">
    <style>
        .footer {
    background-color: #002c84;
    color: white;
    text-align: center;
    padding: 15px 10px;
    font-size: 14px;
    border-top: 4px solid #001e5a;
    margin-top: 133px;
   
}
.footer p {
    margin: 0;
}

        .hero {
    background-color:rgb(3, 43, 124);
    color: white;
    padding: 60px 20px;
    text-align: center;
}
body, html {
    margin-top: 40px;
    padding: 0 ;
   
}
.hero h1 {
    font-size: 36px;
    font-weight: bold;
    margin: 0 0 15px;
}

.hero p {
    font-size: 18px;
    color: #d0d9e8;
    margin: 0;
}
.btn-list {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 40px;
}

.btn-link {
    display: inline-block;
    padding: 15px 30px;
    background-color:rgb(14, 187, 37);
    color: white;
    border-radius: 8px;
    font-size: 16px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.btn-link:hover {
    background-color: #0040c1;
}


        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .user-table th, .user-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .user-table th {
            background-color: #f9f9f9;
            color: #2c3e50;
        }
        .btn-role {
            background-color: #f39c12;
            color: white;
        }
        .btn-role:hover {
            background-color: #e67e22;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c0392b;
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


<div class="hero">
    <h1>Bienvenue dans l'espace administrateur </h1>
    <p>Suivi, gestion et valorisation des projets étudiants</p>
</div>

<div class="container">
    <div class="btn-list">
        <a href="dashboard.php" class="btn-link">📁 Voir les projets</a>
        <a href="statistiques.php" class="btn-link">📊 Voir les statistiques</a>
        <a href="gestion_utilisateurs.php" class="btn-link">👥 Gérer les utilisateurs</a>
        <a href="logout.php" class="btn-link">🚪 Déconnexion</a>
    </div>
</div>

<script>
document.querySelector(".dropbtn").addEventListener("click", function () {
    document.querySelector(".dropdown-content").classList.toggle("show");
});
window.addEventListener("click", function (e) {
    if (!e.target.matches('.dropbtn')) {
        document.querySelectorAll(".dropdown-content").forEach(d => d.classList.remove("show"));
    }
});
</script>

<footer class="footer">
    <p>© <?= date("Y") ?> École Nationale des Sciences Appliquées - Kénitra. Tous droits réservés.</p>
</footer>

</body>

</html>

