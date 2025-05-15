<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!preg_match('/^[a-zA-Z0-9._-]+@uae\.ac\.ma$/', $email)) {
        $error = "L'email doit être au format @uae.ac.ma";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM professeurs WHERE email = ?");
            $stmt->execute([$email]);
            $prof = $stmt->fetch();

            if ($prof && $password === $prof['mot_de_passe']) {
                $_SESSION['prof_id'] = $prof['id'];
                $_SESSION['prof_nom'] = $prof['nom'] ?? '';
                $_SESSION['prof_prenom'] = $prof['prenom'] ?? '';
                $_SESSION['prof_email'] = $prof['email'];
                header("Location: espace_prof.php");
                exit();
            } else {
                $error = "Email ou mot de passe incorrect";
            }
        } catch(PDOException $e) {
            $error = "Erreur lors de la connexion : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Professeur - Portail GI1</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo-container">
                <img src="ensate.png" alt="Logo" class="logo">
            </div>
        </nav>
    </header>
    <div class="auth-container">
        <h2>Connexion Professeur</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="login_prof.php">
            <input type="email" name="email" placeholder="Email (@uae.ac.ma)" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <p><a href="GI1.html">Retour à l'accueil</a></p>
    </div>
    <footer>
        <p>suivez-nous et contactez-nous ici ! </p>
        <div class="social-links">
            <center>
                <a href="https://www.instagram.com/tafraouti_sanae?igsh=MWxoYTh6b3ZmOGF2bQ==" class="social-icon" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="mailto:tafraouti.sanae1@gmail.com" class="social-icon"  target="_blank"><i class="far fa-envelope"></i></a>
            </center>
        </div>
        <hr>
        <p>©2025 Portail Génie Informatique 1 -ENSA TÉTOUAN </p>
    </footer>
</body>
</html>