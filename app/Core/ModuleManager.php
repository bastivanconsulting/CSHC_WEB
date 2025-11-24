<?php
class ModuleManager {
    
    // Charge uniquement les modules ACTIFS
    public static function boot() {
        // Enregistrement de l'autoloader pour les modules
        spl_autoload_register(function ($class) {
            // Préfixe de namespace pour les modules
            $prefix = 'Modules\\';

            // Base directory pour les modules
            $base_dir = __DIR__ . '/../../modules/';

            // Vérifie si la classe utilise le préfixe
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            // Récupère le nom relatif de la classe
            $relative_class = substr($class, $len);

            // Remplace le namespace prefix par le base directory, remplace les séparateurs de namespace
            // par des séparateurs de dossier, et ajoute .php
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            // Si le fichier existe, on le charge
            if (file_exists($file)) {
                require $file;
            }
        });

        $moduleDir = __DIR__ . '/../../modules';
        if (!is_dir($moduleDir)) return;

        // 1. Synchronisation Fichiers <-> Base de données
        self::syncModules($moduleDir);

        // 2. Récupération des modules actifs
        $db = Database::connect();
        try {
            $activeModules = $db->query("SELECT name FROM modules WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            // Fallback si la table n'existe pas encore (premier run)
            return;
        }

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
        try {
            $existing = $db->query("SELECT name FROM modules")->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            // Création de la table si elle n'existe pas
            $db->exec("CREATE TABLE IF NOT EXISTS modules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) UNIQUE NOT NULL,
                status VARCHAR(50) DEFAULT 'disabled'
            )");
            $existing = [];
        }

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
