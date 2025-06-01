<?php
require_once 'cnx.php';
session_start();

// Créer une instance de connexion
$connexion = new Connexion();
$pdo = $connexion->getConnexion();

// Initialisation
$error = '';
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Vérifier que le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Vérifier l'email dans la base
        $sql = "SELECT * FROM enseignant WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérification du mot de passe (hashé)
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['email'] = $user['email'];

                // Redirection
                header('Location: dash.php');
                exit();
            } else {
                $error = 'Mot de passe incorrect.';
            }
        } else {
            $error = 'Aucun compte trouvé avec cet email.';
        }
    }
}

// Affichage de l'erreur si nécessaire
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
    <script src="https://kit.fontawesome.com/0c03620f7c.js" crossorigin="anonymous"></script>
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta charset="UTF-8">
    <title>connect</title>
</head>
<body class="form">
   <!-- Afficher les erreurs -->
   <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    
    
<!-- Bootstrap CSS -->


<style>
  .nav-link {
    font-size: 1rem;
    font-weight: bold;
    white-space: nowrap;
    color: #003366 !important;
  }

  .navbar-toggler {
    border: none;
  }

  .responsive-title {
    font-size: 3vw;
    white-space: nowrap;
  }

  @media (min-width: 768px) {
    .responsive-title {
      font-size: 2rem;
    }
  }

  .header-bar {
    flex-wrap: nowrap;
    overflow: hidden;
  }
</style>

<div class="container-fluid border-bottom py-3 mb-3">
  <div class="d-flex align-items-center justify-content-between header-bar">

    <!-- Logo -->
    <div class="text-start me-2">
      <img src="image/logo.png" alt="Logo" class="img-fluid" style="max-height: 60px;">
    </div>

    <!-- Titre -->
    <div class="text-center flex-grow-1 mx-2">
      <h3 class="m-0 responsive-title">Gestion des Projets des Étudiants</h3>
    </div>

    <!-- Menu + hamburger -->
    <div class="text-end ms-2">
      <nav class="navbar navbar-expand-md p-0">
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="menuNav">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a href="first_page.php" class="nav-link">Accueil</a>
            </li>
            
           
          </ul>
        </div>
      </nav>
    </div>

  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>







          
<div class="b-example-divider" >
          <div class="container form" >

          <div class="row justify-content-center " >
        <div class="col-md-4 card"> 

    
    <div class="imgcontainer">
       
    <img src="image/teacher.svg" alt="Avatar" class="avatar" width="80" height="80">
   
    </div>

    <div class="container">
      <form action="connect_prof.php" method="POST">
      <label for="uname"><b>email</b></label>
        <input type="text" placeholder="Adresse e-mail" name="email" required>

        <label for="psw"><b>mot de passe</b></label>
        <input type="password" placeholder=" Mot de passe" name="password" required>
        <input type="submit" class="btn btn-primary" value="Se connecter">
      </form>
       

      


        <p>
         vous n'avez pas de compte ? <a href="inscription_prof.php">Inscrivez-vous</a>

        </p>
        
      

        

        
        


    
            </div>
         
    
 


    </div>
    

  </form>
  



</div>
</div>
</div>











</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>

  
<div class="row justify-content-center grid2">
      
      <footer class="mainfooter" role="contentinfo">
        <div class="container">
          <div class="row text-start align-items-start">
            <!-- Colonne 1 -->
            <div class="col-md-3 col-12 mb-4">
              <div class="footer-pad">
                <img src="image/log.png" alt="Logo" width="200px" height="50px" class="mb-2">
                <p>À l’ENSA, nous formons des ingénieurs d’excellence, prêts à innover et à relever les défis de demain  Une formation accessible, pratique et tournée vers l’avenir .</p>
              </div>
            </div>
      
            <!-- Séparateur -->
            <div class="d-none d-md-block col-md-1 vr-line"></div>
      
            <!-- Colonne 2 -->
            <div class="col-md-2 col-12 mb-4">
              <div class="footer-pad">
                <h5>INFO</h5>
                <ul class="list-unstyled  " style="padding-top:10px ;">
                  <li><a href="https://ensa.uit.ac.ma">ensa.uit.ac.ma</a></li>
                  <li><a href="https://dlc.uit.ac.ma">dlc.uit.ac.ma</a></li>
                  <li><a href="https://www.univ.dz">ent.uit.ac.ma</a></li>
                </ul>
              </div>
            </div>
      
            <!-- Séparateur -->
            <div class="d-none d-md-block col-md-1 vr-line"></div>
      
            <!-- Colonne 3 -->
            <div class="col-md-2 col-12 mb-4">
              <div class="footer-pad">
                <h5>Nous contacter</h5>
                <ul class="list-unstyled" style="padding-top:15px ;">
                  <li><i class="fas fa-map-marker-alt"></i> Campus universitaire, BP 241, Kénitra – Maroc</li>
                  <li><i class="fas fa-phone"></i> (+212) 5 37 37 67 65</li>
                  <li><i class="fas fa-envelope"></i> univ@gmail.com</li>
                </ul>
              </div>
            </div>
      
            <!-- Séparateur -->
            <div class="d-none d-md-block col-md-1 vr-line"></div>
      
            <!-- Colonne 4 -->
           
            <div class="col-md-2 col-12 mb-4">
              <div class="footer-pad">
                <h5 style="padding-bottom: 15px;">Réseaux Sociaux</h5>
                <ul class="social-network social-circle" style="padding-top:80px ;">
                  <li><a href="#" class="icoFacebook" title="Facebook"><i class="fa fa-facebook"></i></a></li>
                  <li><a href="#" class="icoLinkedin" title="Linkedin"><i class="fa fa-linkedin"></i></a></li>
                  <li><a href="#" class="icoTwitter" title="Twitter"><i class="fa fa-twitter"></i></a></li>
                  <li><a href="#" class="icoYoutube" title="Youtube"><i class="fa fa-youtube"></i></a></li>
                </ul>
              </div>
            </div>
            
          </div>
      
          <!-- Ligne copyright -->
          <div class="row">
            <div class="col-12 text-center mt-4">
              <div class="col-md-12 copy" style="margin-top: 10px;"></div>
              <p>&copy; École Nationale des Sciences Appliquées © 2025 Université Ibn Tofail. All Rights Reserved.</p>
            </div>
          </div>
        </div>
      </footer>

    </div>


     
   
  </body>

</html>