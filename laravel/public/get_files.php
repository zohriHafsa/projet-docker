<?php
header('Content-Type: application/json');

$module = $_GET['module'] ?? '';
$submodule = $_GET['submodule'] ?? '';

// Définir les chemins des modules
$module_paths = [
    'LE1' => 'Semestre1/LE1',
    'LE2' => 'Semestre2/LE2',
    'ThG_RO' => 'Semestre1/ThG_RO',
    'BDR' => 'Semestre1/BDR',
    'DS' => 'Semestre1/DS',
    'C' => 'Semestre1/C',
    'RI' => 'Semestre1/RI',
    'AO' => 'Semestre1/AO',
    'TLC' => 'Semestre2/TLC',
    'DW' => 'Semestre2/DW',
    'MOO' => 'Semestre2/MOO',
    'POO' => 'Semestre2/POO',
    'CAS' => 'Semestre2/CAS',
    'SEL' => 'Semestre2/SEL'
];

$response = [
    'cours' => [],
    'td' => [],
    'tp' => []
];

if (isset($module_paths[$module])) {
    $base_path = $module_paths[$module];
    $scan_path = $base_path;
    
    // Si un sous-module est spécifié, ajouter son chemin
    if ($submodule) {
        $scan_path .= '/' . $submodule;
    }
    
    // Vérifier si le chemin existe
    if (is_dir($scan_path)) {
        $files = scandir($scan_path);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $lower = strtolower($file);
            // Vérifier si le fichier est un PDF
            if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                if ($submodule) {
                    // Si on est dans un sous-module, tout PDF est affiché
                    if (strpos($lower, 'td') !== false) {
                        $response['td'][] = $file;
                    } elseif (strpos($lower, 'tp') !== false) {
                        $response['tp'][] = $file;
                    } else {
                        $response['cours'][] = $file;
                    }
                } else {
                    // Logique générique pour les modules sans sous-modules
                    if (preg_match('/^cours[-_ ]?/i', $file)) {
                        $response['cours'][] = $file;
                    } elseif (preg_match('/^td[-_ ]?/i', $file)) {
                        $response['td'][] = $file;
                    } elseif (preg_match('/^tp[-_ ]?/i', $file)) {
                        $response['tp'][] = $file;
                    } else {
                        // Si le nom ne commence pas par cours/td/tp, on l'ajoute à 'cours' par défaut
                        $response['cours'][] = $file;
                    }
                }
            }
        }
    } else {
        // Si le dossier n'existe pas, le créer
        if (!file_exists($scan_path)) {
            mkdir($scan_path, 0777, true);
        }
    }
}

echo json_encode($response); 