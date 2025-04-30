<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            header("Location: accueil.html");
            exit();
        } else {
            $error = "Email ou mot de passe incorrect";
        }
    } catch(PDOException $e) {
        echo "Erreur lors de la connexion : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Portail GI1</title>
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
        <h2>Connexion au Portail GI1</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email (@etu.uae.ac.ma)" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <p>Pas encore inscrit ? <a href="register.php">S'inscrire</a></p>
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