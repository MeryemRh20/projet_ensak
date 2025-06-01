<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['id_etudiant'])) {
    header('Location: login.php');
    exit();
}

$db = new Connexion();
$pdo = $db->getConnexion();

// Récupérer les informations de l'étudiant
$stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = :id");
$stmt->execute([':id' => $_SESSION['id_etudiant']]);
$etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement du formulaire de modification
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des données
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $annee_etude = intval($_POST['annee_etude']);
        $filiere = htmlspecialchars($_POST['filiere']);
        
        // Vérifier si changement de mot de passe
        if (!empty($_POST['nouveau_mot_de_passe'])) {
            $mot_de_passe = password_hash($_POST['nouveau_mot_de_passe'], PASSWORD_DEFAULT);
            $sql = "UPDATE etudiants SET nom=:nom, prenom=:prenom, email=:email, 
                    annee_etude=:annee_etude, filiere=:filiere, mot_de_passe=:mot_de_passe 
                    WHERE id=:id";
            $params = [
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':annee_etude' => $annee_etude,
                ':filiere' => $filiere,
                ':mot_de_passe' => $mot_de_passe,
                ':id' => $_SESSION['id_etudiant']
            ];
        } else {
            $sql = "UPDATE etudiants SET nom=:nom, prenom=:prenom, email=:email, 
                    annee_etude=:annee_etude, filiere=:filiere 
                    WHERE id=:id";
            $params = [
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':annee_etude' => $annee_etude,
                ':filiere' => $filiere,
                ':id' => $_SESSION['id_etudiant']
            ];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $message = '<div class="alert success">Profil mis à jour avec succès!</div>';
        // Rafraîchir les données
        $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['id_etudiant']]);
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = '<div class="alert error">Erreur lors de la mise à jour: '.$e->getMessage().'</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Étudiant</title>
    <link rel="stylesheet" href="css/profils.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo">
            <a href="https://ensa.uit.ac.ma/" target="_blank" rel="noopener noreferrer"><img src="image/logo_ensa.png" alt="Logo ENSA Kénitra"></a>
        </div>
        <div class="top-icons">
            <a href="etudiant.php">Retour à l'accueil</a>
            <a href="logout.php" title="Déconnexion">🚪</a>
        </div>
    </div>

    <div class="profile-container">
        <h1 class="profile-header">Mon Profil</h1>
        
        <?php echo $message; ?>
        
        <form method="POST" action="profil.php">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($etudiant['nom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($etudiant['prenom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($etudiant['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="annee_etude">Année d'étude</label>
                    <select id="annee_etude" name="annee_etude" required>
                        <option value="1" <?= $etudiant['annee_etude'] == 1 ? 'selected' : '' ?>>1ère année cycle préparatoire</option>
                        <option value="2" <?= $etudiant['annee_etude'] == 2 ? 'selected' : '' ?>>2ème année cycle préparatoire</option>
                        <option value="3" <?= $etudiant['annee_etude'] == 3 ? 'selected' : '' ?>>1er année cycle ingénierie</option>
                        <option value="4" <?= $etudiant['annee_etude'] == 4 ? 'selected' : '' ?>>2ème année cycle ingénierie</option>
                        <option value="4" <?= $etudiant['annee_etude'] == 4 ? 'selected' : '' ?>>3ème année cycle ingénierie</option>
                    </select>
            </div>

            
            <div class="form-group">
                <label for="filiere">Filière</label>
                <input type="text" id="filiere" name="filiere" value="<?= htmlspecialchars($etudiant['filiere']) ?>" required>
            </div>
            
            <div class="form-group password-wrapper">
                <label for="nouveau_mot_de_passe">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe">
                <span class="password-toggle" onclick="togglePassword('nouveau_mot_de_passe')">Afficher</span>
            </div>

            
            <button type="submit" class="btn-submit">Enregistrer les modifications</button>
        </form>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>