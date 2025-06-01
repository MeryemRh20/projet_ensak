<?php
require_once 'cnx.php';

// Création de l'objet de connexion PDO
$connexion = new Connexion();
$pdo = $connexion->getConnexion();

// Liste des filières valides
$filieres= [
    'info',
    'indus',
    'electrique',
    'mecatronique',
    'reseau',
    'batiments'
];

$nom = $prenom = $email1 = $email2 = $password1 = $password2 = $filiere = $annee_etude = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email1 = trim($_POST['email1'] ?? '');
    $email2 = trim($_POST['email2'] ?? '');
    $password1 = trim($_POST['password1'] ?? '');
    $password2 = trim($_POST['password2'] ?? '');
    $filiere = trim($_POST['filiere'] ?? '');
    $annee_etude = trim($_POST['annee_etude'] ?? '');

    if (empty($nom) || empty($prenom) || empty($email1) || empty($email2) ||
        empty($password1) || empty($password2) || empty($annee_etude)) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } elseif ($email1 !== $email2) {
        $error = 'Les emails ne correspondent pas';
    } elseif (!preg_match('/@uit\.ac\.ma$/', $email1)) {
        $error = 'L\'adresse e-mail doit se terminer par @uit.ac.ma';
    } elseif ($password1 !== $password2) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (in_array($annee_etude, ['3', '4', '5']) && empty($filiere)) {
        $error = 'Vous devez sélectionner une filière pour les années d\'ingénierie';
    } elseif (!in_array($annee_etude, ['3', '4', '5']) && !empty($filiere)) {
        $error = 'La filière ne peut être sélectionnée qu\'en cycle ingénieur';
    } elseif (in_array($annee_etude, ['3', '4', '5']) && !in_array($filiere, $filieres)) {
        $error = 'Filière invalide sélectionnée';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM etudiants WHERE email = :email");
        $stmt->execute(['email' => $email1]);

        if ($stmt->rowCount() > 0) {
            $error = 'Cet email est déjà utilisé';
        } else {
            $hashed_password = password_hash($password1, PASSWORD_DEFAULT);

            // Pour les années 1 et 2, on ne stocke pas de filière
            if (!in_array($annee_etude, ['3', '4', '5'])) {
                $filiere = null;
            }

            $stmt = $pdo->prepare("
                INSERT INTO etudiants (nom, prenom, email, mot_de_passe, filiere, annee_etude)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :filiere, :annee_etude)
            ");
            $success = $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email1,
                'mot_de_passe' => $hashed_password,
                'filiere' => $filiere,
                'annee_etude' => $annee_etude
            ]);

            if ($success) {
                header('Location: connect_etudiant.php');
                exit();
            } else {
                $error = 'Une erreur est survenue lors de l\'inscription';
            }
        }
    }
}

if ($error) {
    echo "<p style='color:red; text-align:center;'>$error</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" type="text/css" href="connect.css">
    <link rel="stylesheet" type="text/css" href="css1/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css1/bootstrap-utilities.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css1/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="footer.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta charset="UTF-8">
    <title>connect</title>
    <script>
        function toggleFiliere() {
            var annee = document.querySelector('select[name="annee_etude"]').value;
            var filiereSelect = document.querySelector('select[name="filiere"]');
            
            if (annee === '3' || annee === '4' || annee === '5') {
                filiereSelect.disabled = false;
                filiereSelect.required = true;
            } else {
                filiereSelect.disabled = true;
                filiereSelect.required = false;
                filiereSelect.value = '';
            }
        }
    </script>
</head>
<body class="form" onload="toggleFiliere()">
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="container-fluid border-bottom py-3 mb-3">
        <div class="row align-items-center">
            <!-- Votre en-tête existant ici -->
        </div>
    </div>

    <div class="b-example-divider">
        <div class="container form">
            <div class="row justify-content-center">
                <div class="col-md-4 card">
                    <form action="inscription_etudiant.php" method="post">
                        <div class="imgcontainer">
                            <img src="image/student.svg" alt="Avatar" class="avatar" width="80" height="80">
                        </div>

                        <div class="container">
                            <label><b>Nom</b></label>
                            <input type="text" placeholder="Entrer votre Nom" name="nom" required>

                            <label><b>Prenom</b></label>
                            <input type="text" placeholder="Entrer votre prenom" name="prenom" required>

                            <label><b>email</b></label>
                            <input type="text" placeholder="Adresse e-mail" name="email1" required>

                            <label><b>confirmer email</b></label>
                            <input type="text" placeholder="Confirmer Adresse e-mail" name="email2" required>

                            <label><b>mot de passe</b></label>
                            <input type="password" placeholder="Entrer mot de passe" name="password1" required>

                            <label><b>confirmer le mot de passe</b></label>
                            <input type="password" placeholder="Confirmer mot de passe" name="password2" required>

                            <label><b> Année d'étude</b></label>
                            <select name="annee_etude" required onchange="toggleFiliere()">
                                <option value="" disabled selected>Sélectionnez votre année d'étude</option>
                                <option value="1">1ère année cycle préparatoire</option>
                                <option value="2">2ème année cycle préparatoire</option>
                                <option value="3">1ère année cycle ingénierie</option>
                                <option value="4">2ème année cycle ingénierie</option>
                                <option value="5">3ème année cycle ingénierie</option>
                            </select>

                            <label><b>Filière</b></label>
                            <select name="filiere" id="filiereSelect" disabled>
                                <option value="" disabled selected>Choisissez votre filière</option>
                                <option value="info">Génie Informatique</option>
                                <option value="indus">Génie Industriel</option>
                                <option value="electrique">Génie Électrique</option>
                                <option value="mecatronique">Mécatronique</option>
                                <option value="reseau">Réseaux</option>
                                <option value="batiments">BIEE</option>
                            </select>

                            <button class="btn btn-primary" type="submit">s'inscrire</button>
                            <p>vous avez un compte ? <a href="connect.php">Connectez-vous</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Votre pied de page existant ici -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
</body>
</html>