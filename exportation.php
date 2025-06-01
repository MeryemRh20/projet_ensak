<?php
session_start();
require_once 'auth/check_admin.php'; // Active si nécessaire
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Exporter les projets</title>
    <link rel="stylesheet" href="etudiants.css">
    <style>
        .export-buttons {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .export-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .export-card h2 {
            margin-bottom: 20px;
            font-size: 22px;
        }
        .export-card p {
            color: #555;
        }
        .btn-export {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        .btn-excel {
            background-color: #27ae60;
        }
        .btn-excel:hover {
            background-color: #219150;
        }
        .btn-pdf {
            background-color: #e74c3c;
        }
        .btn-pdf:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<div class="top-bar">
    <div class="logo">
        <img src="logo_ensa.png" alt="ENSA Logo">
    </div>
    <div class="top-icons">
        <a href="#" target="_blank"><img src="ig-icon.png" alt="Instagram"></a>
        <a href="#" target="_blank"><img src="link-icone.png" alt="Lien utile"></a>
        <div class="dropdown">
            <button class="dropbtn">&#9776;</button>
            <div class="dropdown-content">
                <a href="dashboard.php">Tableau de bord</a>
                <a href="statistiques.php">Statistiques</a>
                <a href="gestion_utilisateurs.php">Utilisateurs</a>
                <a href="exporter_projets.php">Exportation</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</div>

<header>
    <h1>📤 Exporter les projets étudiants</h1>
</header>

<div class="container">
    <div class="export-card">
        <h2>Choisissez un format d’exportation</h2>
        <p>Vous pouvez exporter tous les projets de la base au format Excel ou PDF.</p>
        <div class="export-buttons">
            <a href="export_excel.php" class="btn-export btn-excel">📊 Exporter en Excel</a>
            <a href="export_pdf.php" class="btn-export btn-pdf">🧾 Exporter en PDF</a>
        </div>
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
