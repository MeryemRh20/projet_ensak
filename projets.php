<?php
session_start();
require_once 'cnx.php';

$connexion = new Connexion();
$pdo = $connexion->getConnexion();


// 🔐 Simulation de session enseignant
if (!isset($_SESSION['user_id'])) {
    $stmt = $pdo->query("SELECT * FROM enseignant LIMIT 1");
    $enseignant = $stmt->fetch();

    if ($enseignant) {
        $_SESSION['user_id'] = $enseignant['id'];
    } else {
        die("Aucun enseignant trouvé.");
    }
}

$id_enseignant = $_SESSION['user_id'];

// Récupération du nom complet
$stmt_nom = $pdo->prepare("SELECT nom, prenom FROM enseignant WHERE id = :id");
$stmt_nom->execute(['id' => $id_enseignant]);
$enseignant_info = $stmt_nom->fetch();
$nom_complet = $enseignant_info ? $enseignant_info['prenom'] . ' ' . $enseignant_info['nom'] : 'Enseignant';

// 🎯 Filtres
$module = $_GET['module'] ?? '';
$annee = $_GET['annee'] ?? '';
$statut = $_GET['statut'] ?? '';

// ⚙️ Requête
$query = "
SELECT 
    projets.id,
    projets.titre,
    projets.categorie AS nom_module,
    projets.type_projet,
    projets.date_soumission,
    projets.validé,
    etudiants.nom AS nom_etudiant,
    etudiants.prenom AS prenom_etudiant,
    etudiants.annee_etude
FROM projets
JOIN etudiants ON projets.id_etudiant = etudiants.id
WHERE 1=1
";

$params = [];

if ($module) {
    $query .= " AND projets.categorie LIKE :module";
    $params['module'] = "%$module%";
}
if ($annee) {
    $query .= " AND etudiants.annee_etude = :annee";
    $params['annee'] = $annee;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$projets_bruts = $stmt->fetchAll();

// 🔄 Génération des projets avec statut réel
$projets = [];

foreach ($projets_bruts as $p) {
    // Vérifier s'il existe un commentaire de l'enseignant
    $stmtC = $pdo->prepare("SELECT COUNT(*) FROM commentaires WHERE id_projet = :id AND auteur_type = 'enseignant'");
    $stmtC->execute(['id' => $p['id']]);
    $nb_comments = $stmtC->fetchColumn();

    if ($p['validé'] == 1) {
        $statut_final = 'validé';
    } elseif ($p['validé'] == 0 && $nb_comments > 0) {
        $statut_final = 'refusé';
    } else {
        $statut_final = 'en attente';
    }

    // Appliquer le filtre de statut
    if ($statut && $statut_final !== $statut) {
        continue;
    }

    $projets[] = [
        'id' => $p['id'],
        'titre' => $p['titre'],
        'nom_module' => $p['nom_module'],
        'annee_scolaire' => $p['annee_etude'],
        'prenom_etudiant' => $p['prenom_etudiant'],
        'nom_etudiant' => $p['nom_etudiant'],
        'statut' => $statut_final
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des projets</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/projets.css">
    <style>
        .statut {
            padding: 4px 10px;
            border-radius: 6px;
            color: white;
            font-size: 0.9rem;
        }
        .validé { background-color: #4caf50; }
        .refusé { background-color: #f44336; }
        .en-attente { background-color: #ff9800; }
        .btn-detail {
            background-color: #002f86;
            color: white;
        }
    </style>
</head>
<body>

<!-- Top bar -->
<div class="top-nav py-2 px-4 d-flex justify-content-between align-items-center" style="background-color: #002f86; color:white;">
    <div class="logo">
        <a href="https://ensa.uit.ac.ma/" target="_blank"><img src="image/logo_ensa.png" alt="Logo ENSA Kénitra" style="height: 50px;"></a>
    </div>
    <a class="retour btn btn-light" href="dash.php"> Retour à l'accueil</a>
</div>

<div class="container mt-4">
    <h2 class="mb-4"><b>Liste des projets étudiants</b></h2>

    <!-- Filtres -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="module" class="form-control" placeholder="Filtrer par module" value="<?= htmlspecialchars($module) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="annee" class="form-control" placeholder="Filtrer par année" value="<?= htmlspecialchars($annee) ?>">
        </div>
        <div class="col-md-3">
            <select name="statut" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="en attente" <?= $statut == 'en attente' ? 'selected' : '' ?>>En attente</option>
                <option value="validé" <?= $statut == 'validé' ? 'selected' : '' ?>>Validé</option>
                <option value="refusé" <?= $statut == 'refusé' ? 'selected' : '' ?>>Refusé</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100 custom-blue">Appliquer les filtres</button>
        </div>
    </form>

    <!-- Tableau -->
    <table class="table table-bordered table-striped">
        <thead class="">
            <tr>
                <th>Titre du projet</th>
                <th>Étudiant</th>
                <th>Module</th>
                <th>Année</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($projets) > 0): ?>
                <?php foreach ($projets as $projet): ?>
                    <tr>
                        <td><?= htmlspecialchars($projet['titre']) ?></td>
                        <td><?= htmlspecialchars($projet['prenom_etudiant'] . ' ' . $projet['nom_etudiant']) ?></td>
                        <td><?= htmlspecialchars($projet['nom_module']) ?></td>
                        <td><?= htmlspecialchars($projet['annee_scolaire']) ?></td>
                        <td>
                            <span class="statut <?= str_replace(' ', '-', $projet['statut']) ?>">
                                <?= ucfirst($projet['statut']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="projet_details.php?id=<?= $projet['id'] ?>" class="btn btn-detail">Voir détails</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Aucun projet trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>