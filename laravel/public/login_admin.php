<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($email === 'tafraouti.sanae1@gmail.com') {
        if ($password === 'admin123') { 
            $_SESSION['admin_email'] = $email;
            header('Location: admin.php');
            exit();
        }
    }
    
    $error = "Email ou mot de passe incorrect";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Administrateur - Portail GI1</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-group label {
            font-weight: bold;
            color: #2c3e50;
        }
        .form-group input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
        }
        .submit-btn {
            background: #2c3e50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .submit-btn:hover {
            background: #34495e;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo-container">
                <img src="ensate.png" alt="Logo UAE" class="logo">
            </div>
            <div class="nav-links">
                <a href="GI1.html">Accueil</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="login-container">
            <h1>Connexion Administrateur</h1>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form class="login-form" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="submit-btn">Se connecter</button>
            </form>
        </div>
    </main>

    <footer>
        <center>
        <div class="footer-content">
            <p>©2025 Portail Génie Informatique 1 -ENSA TÉTOUAN. TOUS LES DROITS SONT RÉSERVÉS.</p>
        </div>
        </center>         
    </footer>
</body>
</html>