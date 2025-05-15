<?php
require_once 'config.php';

function logProfessorAction($professorEmail, $actionType, $actionDetails) {
    global $pdo;
    
    try {
        $sql = "INSERT INTO admin_logs (professor_email, action_type, action_details) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$professorEmail, $actionType, $actionDetails]);
        return true;
    } catch(PDOException $e) {
        error_log("Erreur lors de l'enregistrement de l'action : " . $e->getMessage());
        return false;
    }
}


function formatActionDetails($module, $type, $fileName, $submodule = null) {
    $details = "Module: $module, Type: $type, Fichier: $fileName";
    if ($submodule) {
        $details .= ", Sous-module: $submodule";
    }
    return $details;
}
?> 