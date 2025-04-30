<?php
session_start();
require_once 'error_reporting.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!preg_match('/^[a-zA-Z0-9._-]+@etu\.uae\.ac\.ma$/', $email)) {
        $error = "L'email doit être au format @etu.uae.ac.ma";
    } else {
        try {
            
            $check_email = $pdo->prepare("SELECT COUNT(*) FROM etudiants WHERE email = ?");
            $check_email->execute([$email]);
            $email_exists = $check_email->fetchColumn();

            if ($email_exists) {
                $error = "Cette adresse email est déjà utilisée. Veuillez vous connecter ou utiliser une autre adresse email.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO etudiants (nom, prenom, email, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $email, $password]);
                
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['email'] = $email;
                header("Location: accueil.html");
                exit();
            }
        } catch(PDOException $e) {
            $error = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Portail GI1</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav >
            <div class="logo-container">
                <img src="ensate.png" alt="Logo" class="logo">
            </div>
        </nav>
    </header>
    <div class="auth-container">
        <h2>Inscription au Portail GI1</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="register.php">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="email" name="email" placeholder="Email (@etu.uae.ac.ma)" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">S'inscrire</button>
        </form>
        <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
        <p><a href="GI1.html">Retour </a></p>
    </div>
    
    <footer>
        <p>suivez-nous et contactez-nous ici ! </p> 
        <div class="social-links">
            <center>
                <a href="https://www.instagram.com/ensa_tetouan_officiel?igsh=N3U1MTMzdDYxbmFp" class="social-icon" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="mailto:ton-ensate@uae.ac.ma" class="social-icon"  target="_blank"><i class="far fa-envelope"></i></a>
            </center>
        </div> 
        <hr>
        <p>©2025 ENSA TÉTOUAN-Université Abdelmalek Essaâdi </p>
    </footer>
</body>
</html>