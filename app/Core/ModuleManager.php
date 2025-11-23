<?php
class ModuleManager {
    
    // Charge uniquement les modules ACTIFS
    public static function boot() {
        $moduleDir = __DIR__ . '/../../modules';
        if (!is_dir($moduleDir)) return;

        // 1. Synchronisation Fichiers <-> Base de données
        self::syncModules($moduleDir);

        // 2. Récupération des modules actifs
        $db = Database::connect();
        $activeModules = $db->query("SELECT name FROM modules WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);

        // 3. Chargement
        foreach ($activeModules as $moduleName) {
            $entryFile = $moduleDir . '/' . $moduleName . '/index.php';
            if (file_exists($entryFile)) {
                require_once $entryFile;
            }
        }
    }

    // Détecte les nouveaux dossiers et les ajoute en BDD (statut disabled)
    private static function syncModules($dir) {
        $folders = scandir($dir);
        $db = Database::connect();
        
        // Récupérer les modules déjà connus en BDD
        $existing = $db->query("SELECT name FROM modules")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($folders as $folder) {
            if ($folder === '.' || $folder === '..') continue;
            if (is_dir($dir . '/' . $folder)) {
                // Si le dossier existe mais n'est pas en BDD, on l'ajoute
                if (!in_array($folder, $existing)) {
                    $stmt = $db->prepare("INSERT INTO modules (name, status) VALUES (?, 'disabled')");
                    $stmt->execute([$folder]);
                }
            }
        }
    }

    // Récupère la liste complète pour l'Admin Panel
    public static function getAll() {
        $db = Database::connect();
        return $db->query("SELECT * FROM modules ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // Active ou Désactive un module
    public static function toggle($name, $status) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE modules SET status = ? WHERE name = ?");
        $stmt->execute([$status, $name]);
    }
}