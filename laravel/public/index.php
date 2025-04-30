<?php
echo "<h1>Backend Test Réussi!</h1>";
echo "<p>Date: ".date('Y-m-d H:i:s')."</p>";
echo "<p>MySQL: ". (extension_loaded('pdo_mysql') ? '✔ Connecté' : '✖ Erreur')."</p>";