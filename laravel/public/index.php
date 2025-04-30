<?php
<<<<<<< HEAD
echo "<h1>Backend Test Réussi!</h1>";
echo "<p>Date: ".date('Y-m-d H:i:s')."</p>";
echo "<p>MySQL: ". (extension_loaded('pdo_mysql') ? '✔ Connecté' : '✖ Erreur')."</p>";
=======
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// Votre logique d'application ici
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Application</title>
    <link rel="stylesheet" href="/home/sanae/projet-docker/angular/src/styles.css">
</head>
<body>
    <h1>Bienvenue sur mon application</h1>
    
    <script src="/home/sanae/projet-docker/laravel/public/js/app.js"></script>
</body>
</html>
>>>>>>> c563d35 ([LD-27] Portail Genie Informatique1)
