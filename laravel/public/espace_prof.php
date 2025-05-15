<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

if (!isset($_SESSION['prof_id'])) {
    header('Location: login_prof.php');
    exit();
}

// Vérifier si l'email est défini dans la session
if (!isset($_SESSION['prof_email'])) {
    // Si l'email n'est pas dans la session, le récupérer depuis la base de données
    try {
        $stmt = $pdo->prepare("SELECT email FROM professeurs WHERE id = ?");
        $stmt->execute([$_SESSION['prof_id']]);
        $prof = $stmt->fetch();
        if ($prof) {
            $_SESSION['prof_email'] = $prof['email'];
        } else {
            // Si le professeur n'est pas trouvé, déconnecter
            session_destroy();
            header('Location: login_prof.php');
            exit();
        }
    } catch(PDOException $e) {
        error_log("Erreur lors de la récupération de l'email : " . $e->getMessage());
        // En cas d'erreur, utiliser un email par défaut
        $_SESSION['prof_email'] = 'professeur@uae.ac.ma';
    }
}

require_once 'log_actions.php';

// Définir les modules et leurs chemins
$modules = [
    'Semestre 1' => [
        'Théorie des Graphes et Recherche Opérationnel' => 'Semestre1/ThG_RO',
        'Réseaux informatiques' => 'Semestre1/RI',
        'Langues étrangères 1' => 'Semestre1/LE1',
        'Digital Skills' => 'Semestre1/DS',
        'Structure de Données en C' => 'Semestre1/C',
        'Bases de données Relationnelle' => 'Semestre1/BDR',
        'Architecture des Ordinateurs' => 'Semestre1/AO',
    ],
    'Semestre 2' => [
        'Théories des Langages et compilation' => 'Semestre2/TLC',
        'Développement Web' => 'Semestre2/DW',
        'Langues étrangères 2' => 'Semestre2/LE2',
        'Modélisation Orientée Objet' => 'Semestre2/MOO',
        'Programmation Orientée Objet Java' => 'Semestre2/POO',
        'Culture & arts & sport skills' => 'Semestre2/CAS',
        'Systèmes d\'Exploitation et Linux' => 'Semestre2/SEL',
    ]
];

$msg = '';
// Gestion de l'upload
if (isset($_POST['action']) && $_POST['action'] === 'ajouter' && isset($_POST['module_path']) && isset($_POST['type_fichier'])) {
    $modulePath = $_POST['module_path'];
    $type = $_POST['type_fichier'];
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Nettoyage du nom d'affichage : lettres, chiffres, tirets, underscores uniquement
        $displayName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $_POST['display_name']);
        $displayName = trim(preg_replace('/_+/', '_', $displayName), '_');
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        
        // Ajout du sous-module au chemin si nécessaire
        if (isset($_POST['submodule'])) {
            $submodule = $_POST['submodule'];
            $uploadDir = $modulePath . '/' . $submodule . '/';
        } else {
            $uploadDir = $modulePath . '/';
        }
        
        // Créer le dossier du sous-module s'il n'existe pas
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $prefixes = [
            'Semestre1/ThG_RO' => [
                'Théorie des Graphes' => ['cours' => 'graphes-cours', 'td' => 'graphes-td'],
                'Recherche Opérationnel' => ['cours' => 'ro-cours', 'td' => 'ro-td'],
            ],
            'Semestre1/LE1' => [
                'Espagnol' => ['cours' => 'espagnol-cours', 'td' => 'espagnol-td'],
                'Français' => ['cours' => 'francais-cours', 'td' => 'francais-td'],
            ],
            'Semestre2/LE2' => [
                'Anglais' => ['cours' => 'anglais-cours', 'td' => 'anglais-td'],
                'Français' => ['cours' => 'francais-cours', 'td' => 'francais-td'],
            ]
        ];

        $prefix = '';
        if (
            isset($_POST['module_path'], $_POST['submodule'], $_POST['type_fichier']) &&
            isset($prefixes[$_POST['module_path']][$_POST['submodule']][$_POST['type_fichier']])
        ) {
            $prefix = $prefixes[$_POST['module_path']][$_POST['submodule']][$_POST['type_fichier']];
        }

        // Ajouter le préfixe s'il n'est pas déjà présent
        if ($prefix && stripos($displayName, $prefix) !== 0) {
            $displayName = $prefix . '-' . $displayName;
        }

        $filename = $displayName . '.' . $ext;
        
        $uploadFile = $uploadDir . basename($filename);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            $msg = "Fichier ajouté avec succès.";
            // Enregistrer l'action dans les logs
            $actionDetails = formatActionDetails(
                $modulePath,
                $type,
                $filename,
                isset($_POST['submodule']) ? $_POST['submodule'] : null
            );
            logProfessorAction($_SESSION['prof_email'], 'Ajout de fichier', $actionDetails);
        } else {
            $msg = "Erreur lors de l'ajout du fichier.";
        }
    }
}

// Fonction pour supprimer un lien d'un fichier HTML (améliorée)
function removeLinkFromHTML($modulePath, $fileName, $submodule = '') {
    $htmlFile = $modulePath . '/' . basename($modulePath) . '.html';
    if (!file_exists($htmlFile)) {
        return false;
    }
    $content = file_get_contents($htmlFile);
    // Supprimer le bloc <div class="pdf-card"> ... </div> avec espaces/retours à la ligne autour
    $pattern = '/\s*<div class="pdf-card">\s*<h3>.*?<\/h3>\s*<a href="' . preg_quote($fileName, '/') . '".*?<\/a>\s*<\/div>\s*/is';
    $newContent = preg_replace($pattern, '', $content);
    // Nettoyer les lignes vides multiples
    $newContent = preg_replace('/(\r?\n){3,}/', "\n\n", $newContent);
    if ($newContent !== $content) {
        return file_put_contents($htmlFile, $newContent) !== false;
    }
    return false;
}

// Gestion de la suppression
if (
    isset($_POST['action']) && $_POST['action'] === 'supprimer'
    && isset($_POST['module_path']) && isset($_POST['type_fichier'])
    && isset($_POST['file_name'])
    && isset($_POST['valider_suppression'])
) {
    $modulePath = $_POST['module_path'];
    $fileName = $_POST['file_name'];
    $submodule = isset($_POST['submodule']) ? $_POST['submodule'] : '';
    $filePath = $modulePath . '/';
    if (!empty($submodule)) {
        $filePath .= $submodule . '/';
    }
    $filePath .= $fileName;

    // Vérifier si le fichier est un fichier statique (déjà présent dans le HTML)
    $isStaticFile = false;
    $htmlFile = $modulePath . '/' . basename($modulePath) . '.html';
    if (file_exists($htmlFile)) {
        $content = file_get_contents($htmlFile);
        if (strpos($content, $fileName) !== false) {
            $isStaticFile = true;
        }
    }

    if (file_exists($filePath)) {
        // Supprimer le fichier
        if (unlink($filePath)) {
            $msg = "Fichier supprimé avec succès.";
            // Si c'est un fichier statique, supprimer aussi le lien dans le HTML
            if ($isStaticFile) {
                if (removeLinkFromHTML($modulePath, $fileName, $submodule)) {
                    $msg .= " Le lien a été supprimé de la page HTML.";
                } else {
                    $msg .= " Le fichier a été supprimé mais il y a eu une erreur lors de la suppression du lien dans la page HTML.";
                }
            }
            // Enregistrer l'action dans les logs
            $actionDetails = formatActionDetails(
                $modulePath,
                $_POST['type_fichier'],
                $fileName,
                $submodule
            );
            logProfessorAction($_SESSION['prof_email'], 'Suppression de fichier', $actionDetails);
        } else {
            $msg = "Erreur lors de la suppression du fichier.";
        }
    } else {
        $msg = "Fichier introuvable sur le serveur.";
    }
    // Si AJAX, retourner un JSON et exit
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'file' => $fileName, 'msg' => $msg]);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Professeur - Portail GI1</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-form { max-width: 500px; margin: 2rem auto; background: #fafbfc; border-radius: 10px; padding: 2rem; box-shadow: 0 2px 8px #eee; }
        .main-form label { font-weight: bold; margin-top: 1rem; display: block; }
        .main-form select, .main-form input[type='file'] { width: 100%; margin-bottom: 1rem; }
        .main-form button { margin-top: 1rem; }
        .msg { color: green; font-weight: bold; text-align: center; }
        .error { color: red; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo-container">
                <img src="ensate.png" alt="Logo" class="logo">
            </div>
            <div class="nav-links">
                <a href="GI1.html">Accueil</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </nav>
    </header>
    <main>
        <h1 style="text-align:center;">Espace Professeur</h1>
        <!--<p style="text-align:center;">Bienvenue, <?php echo htmlspecialchars($_SESSION['prof_nom'] . ' ' . $_SESSION['prof_prenom']); ?> !</p>-->
        <?php if (isset($msg)) echo '<p class="msg">' . $msg . '</p>'; ?>
        <?php if (isset($_GET['success'])) $msg = "Fichier ajouté avec succès."; ?>
        <form class="main-form" method="POST" enctype="multipart/form-data">
            <label for="semestre">Semestre :</label>
            <select id="semestre" name="semestre" onchange="this.form.submit()" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($modules as $sem => $mods): ?>
                    <option value="<?php echo $sem; ?>" <?php if(isset($_POST['semestre']) && $_POST['semestre'] == $sem) echo 'selected'; ?>><?php echo $sem; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="module">Module :</label>
            <select id="module" name="module_path" onchange="this.form.submit()" required>
                <option value="">-- Choisir un semestre d'abord --</option>
                <?php
                if(isset($_POST['semestre']) && isset($modules[$_POST['semestre']])) {
                    foreach($modules[$_POST['semestre']] as $name => $path) {
                        $selected = (isset($_POST['module_path']) && $_POST['module_path'] == $path) ? 'selected' : '';
                        echo "<option value=\"$path\" $selected>$name</option>";
                    }
                }
                ?>
            </select>
            <label for="action">Action :</label>
            <select id="action" name="action" required>
                <option value="">-- Choisir --</option>
                <option value="ajouter" <?php if(isset($_POST['action']) && $_POST['action']=='ajouter') echo 'selected'; ?>>Ajouter</option>
                <option value="supprimer" <?php if(isset($_POST['action']) && $_POST['action']=='supprimer') echo 'selected'; ?>>Supprimer</option>
            </select>
            <label for="type_fichier">Type de fichier :</label>
            <select id="type_fichier" name="type_fichier" onchange="this.form.submit()" required>
                <option value="">-- Choisir --</option>
                <option value="cours" <?php if(isset($_POST['type_fichier']) && $_POST['type_fichier']=='cours') echo 'selected'; ?>>Cours</option>
                <option value="td" <?php if(isset($_POST['type_fichier']) && $_POST['type_fichier']=='td') echo 'selected'; ?>>TD</option>
                <option value="tp" <?php if(isset($_POST['type_fichier']) && $_POST['type_fichier']=='tp') echo 'selected'; ?>>TP</option>
            </select>

            <?php
            // Ajout du champ sous-module pour les modules composés
            $composed_modules = [
                'Semestre1/ThG_RO' => ['Théorie des Graphes', 'Recherche Opérationnel'],
                'Semestre1/LE1' => ['Espagnol', 'Français'],
                'Semestre2/LE2' => ['Anglais', 'Français']
            ];

            if(isset($_POST['module_path']) && isset($composed_modules[$_POST['module_path']])) {
                echo '<label for="submodule">Sous-module :</label>';
                echo '<select id="submodule" name="submodule" onchange="this.form.submit()" required>';
                echo '<option value="">-- Choisir --</option>';
                foreach($composed_modules[$_POST['module_path']] as $submodule) {
                    $selected = (isset($_POST['submodule']) && $_POST['submodule'] == $submodule) ? 'selected' : '';
                    echo "<option value=\"$submodule\" $selected>$submodule</option>";
                }
                echo '</select>';
            }
            ?>

            <?php
            // Affichage de la liste des fichiers à supprimer
            if(isset($_POST['action']) && $_POST['action']=='supprimer' && isset($_POST['module_path']) && isset($_POST['type_fichier'])) {
                $modulePath = $_POST['module_path'];
                $type = $_POST['type_fichier'];
                $files = [];
                $submodule = isset($_POST['submodule']) ? $_POST['submodule'] : '';
                $scanPaths = [];
                if ($submodule) {
                    $scanPaths[] = $modulePath . '/' . $submodule;
                }
                $scanPaths[] = $modulePath; // Toujours scanner aussi le dossier principal

                // Préfixes par sous-module
                $prefixes = [
                    'Semestre1/ThG_RO' => [
                        'Théorie des Graphes' => ['cours' => 'graphes-cours', 'td' => 'graphes-td'],
                        'Recherche Opérationnel' => ['cours' => 'ro-cours', 'td' => 'ro-td'],
                    ],
                    'Semestre1/LE1' => [
                        'Espagnol' => ['cours' => 'espagnol-cours', 'td' => 'espagnol-td'],
                        'Français' => ['cours' => 'francais-cours', 'td' => 'francais-td'],
                    ],
                    'Semestre2/LE2' => [
                        'Anglais' => ['cours' => 'anglais-cours', 'td' => 'anglais-td'],
                        'Français' => ['cours' => 'francais-cours', 'td' => 'francais-td'],
                    ]
                ];

                foreach ($scanPaths as $scanPath) {
                    if (is_dir($scanPath)) {
                        foreach (scandir($scanPath) as $file) {
                            if ($file === '.' || $file === '..') continue;
                            $lower = strtolower($file);
                            $ext = pathinfo($file, PATHINFO_EXTENSION);
                            if ($ext !== 'pdf') continue; // Ne prendre que les PDF

                            // Cas modules à sous-modules
                            if (isset($prefixes[$modulePath]) && $submodule && isset($prefixes[$modulePath][$submodule][$type])) {
                                $prefix = $prefixes[$modulePath][$submodule][$type];
                                if (
                                    preg_match('/^' . preg_quote(strtolower($prefix), '/') . '[-_ ]?/', $lower)
                                    || ($type === 'td' && preg_match('/^td[-_ ]?/', $lower))
                                    || ($type === 'cours' && preg_match('/^cours[-_ ]?/', $lower))
                                ) {
                                    $files[] = $file;
                                }
                            } else {
                                // Logique générique
                                if ($type === 'cours' && preg_match('/^cours[-_ ]?/', $lower)) $files[] = $file;
                                if ($type === 'td' && preg_match('/^td[-_ ]?/', $lower)) $files[] = $file;
                                if ($type === 'tp' && preg_match('/^tp[-_ ]?/', $lower)) $files[] = $file;
                            }
                        }
                    }
                }
                // Supprimer les doublons éventuels
                $files = array_unique($files);
                if (!empty($files)) {
                    echo '<label for="file_name">Fichier à supprimer :</label>';
                    echo '<select name="file_name" id="file_name" required>';
                    foreach ($files as $f) {
                        echo '<option value="'.htmlspecialchars($f).'">'.$f.'</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<p style="color:red;">Aucun fichier trouvé pour ce type.</p>';
                }
            }
            ?>
            <?php if(isset($_POST['action']) && $_POST['action']=='ajouter'): ?>
                <!-- Section ajout (gardez votre code d'ajout ici) -->
                <div id="add-section">
                    <label for="display_name">Nom d'affichage :</label>
                    <input type="text" name="display_name" id="display_name" placeholder="Ex: Chapitre 1, TD 2, TP Linux..." required>
                    <label for="file">Fichier à ajouter :</label>
                    <input type="file" name="file" id="file">
                </div>
            <?php endif; ?>
            <button type="submit" name="valider_suppression" value="1">Valider</button>
        </form>
    </main>
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
    <script>
    // Réinitialiser le formulaire sauf le champ 'action' lors du changement d'action
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.querySelector('.main-form');
        var actionSelect = document.getElementById('action');
        var typeFichierSelect = document.getElementById('type_fichier');
        var validerBtn = form.querySelector('button[type="submit"][name="valider_suppression"]');

        // Confirmation avant suppression
        if (validerBtn) {
            validerBtn.addEventListener('click', function(e) {
                if (actionSelect.value === 'supprimer') {
                    var conf = confirm("Êtes-vous sûr de vouloir supprimer ce fichier ?");
                    if (!conf) {
                        e.preventDefault();
                    }
                }
            });
        }

        // Réinitialiser les champs après 'type_fichier' lors de son changement
        if (form && typeFichierSelect) {
            typeFichierSelect.addEventListener('change', function() {
                var found = false;
                Array.from(form.elements).forEach(function(el) {
                    if (found) {
                        if (el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button') {
                            if (el.tagName === 'SELECT' || el.tagName === 'INPUT') {
                                el.value = '';
                            }
                        }
                    }
                    if (el === typeFichierSelect) found = true;
                });
                form.submit();
            });
        }

        // Suppression dynamique
        const form = document.querySelector('.main-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (document.getElementById('action').value === 'supprimer') {
                    e.preventDefault();

                    const formData = new FormData(form);
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Trouver et supprimer la carte PDF correspondante
                            const fileName = data.file;
                            // Cherche le lien qui correspond au fichier supprimé
                            const link = Array.from(document.querySelectorAll('.pdf-card a')).find(a => a.getAttribute('href') && a.getAttribute('href').includes(fileName));
                            if (link) {
                                const card = link.closest('.pdf-card');
                                if (card) card.remove();
                            }
                            // Afficher le message de succès
                            const msgDiv = document.querySelector('.msg');
                            if (msgDiv) msgDiv.textContent = data.msg;
                        }
                    });
                }
            });
        }
    });
    </script>
</body>
</html> 