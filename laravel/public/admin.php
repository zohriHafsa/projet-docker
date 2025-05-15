<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_email']) || $_SESSION['admin_email'] !== 'tafraouti.sanae1@gmail.com') {
    header('Location: login_admin.php');
    exit();
}


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    $countSql = "SELECT COUNT(*) as total FROM admin_logs";
    $totalLogs = $pdo->query($countSql)->fetch()['total'];
    $totalPages = ceil($totalLogs / $perPage);

    $sql = "SELECT * FROM admin_logs ORDER BY action_date DESC LIMIT $perPage OFFSET $offset";
    $stmt = $pdo->query($sql);
    $logs = $stmt->fetchAll();

    $statsSql = "SELECT 
        COUNT(*) as total_actions,
        COUNT(DISTINCT professor_email) as total_professors,
        SUM(CASE WHEN action_type = 'Ajout de fichier' THEN 1 ELSE 0 END) as total_uploads,
        SUM(CASE WHEN action_type = 'Suppression de fichier' THEN 1 ELSE 0 END) as total_deletes
        FROM admin_logs";
    $stats = $pdo->query($statsSql)->fetch();
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Administrateur - Portail GI1</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        .admin-logs {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .log-entry {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            display: grid;
            grid-template-columns: 150px 1fr 200px;
            gap: 20px;
            align-items: center;
        }
        .log-entry:last-child {
            border-bottom: none;
        }
        .log-date {
            color: #666;
            font-size: 0.9em;
        }
        .log-action {
            font-weight: bold;
            color: #2c3e50;
        }
        .log-details {
            color: #666;
        }
        .log-professor {
            color: #3498db;
            font-weight: 500;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #2c3e50;
        }
        .pagination a.active {
            background: #2c3e50;
            color: white;
            border-color: #2c3e50;
        }
        .error-message {
            color: #e74c3c;
            padding: 10px;
            margin: 10px 0;
            background-color: #fde8e8;
            border-radius: 4px;
        }
        .action-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 500;
        }
        .action-upload {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .action-delete {
            background: #ffebee;
            color: #c62828;
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
                <a href="logout.php">Déconnexion</a>
            </div>
        </nav>
    </header>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Espace Administrateur</h1>
            <div class="admin-info">
                <p>Connecté en tant que : <?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_actions']; ?></div>
                    <div class="stat-label">Actions Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_professors']; ?></div>
                    <div class="stat-label">Professeurs Actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_uploads']; ?></div>
                    <div class="stat-label">Fichiers Ajoutés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_deletes']; ?></div>
                    <div class="stat-label">Fichiers Supprimés</div>
                </div>
            </div>

            <div class="admin-logs">
                <h2>Historique des Actions</h2>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <div class="log-entry">
                            <div class="log-date">
                                <?php echo date('d/m/Y H:i', strtotime($log['action_date'])); ?>
                            </div>
                            <div class="log-details">
                                <span class="action-badge <?php echo $log['action_type'] === 'Ajout de fichier' ? 'action-upload' : 'action-delete'; ?>">
                                    <?php echo htmlspecialchars($log['action_type']); ?>
                                </span>
                                <br>
                                <?php echo htmlspecialchars($log['action_details']); ?>
                            </div>
                            <div class="log-professor">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($log['professor_email']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Aucune action enregistrée.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
</body>
</html> 