<?php
class View {
    
    /**
     * Affiche une vue en utilisant le thème actif.
     * @param string $viewName  Nom du fichier sans extension (ex: 'home') ou chemin relatif si commence par 'modules/'
     * @param array  $data      Données à extraire pour la vue (ex: ['user' => $u])
     * @param string $context   'front' ou 'admin' pour choisir le thème
     */
    public static function render($viewName, $data = [], $context = 'front') {
        extract($data);

        $theme = self::getActiveTheme($context);
        $baseDir = __DIR__ . '/../../themes/' . $theme;

        // SUPPORT DES VUES DE MODULES
        if (strpos($viewName, 'modules/') === 0) {
            // Si le nom commence par "modules/", on cherche directement dans le dossier du module
            // Ex: "modules/Support/views/index" -> "./modules/Support/views/index.php" (relative to root)
            $root = __DIR__ . '/../../';
            $viewFile = $root . $viewName . '.php';
        } else {
            // Comportement standard : cherche dans le thème
            $viewFile = $baseDir . '/' . $viewName . '.php';
        }

        $layoutFile = $baseDir . '/layout.php';

        if (!file_exists($viewFile)) {
            die("<div style='background:#333; color:#ff5555; padding:20px; font-family:sans-serif;'>
                    <h3>Erreur de Template</h3>
                    <p>Impossible de trouver la vue : <strong>$viewName</strong></p>
                    <p>Chemin cherché : <code>$viewFile</code></p>
                 </div>");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    private static function getActiveTheme($context) {
        $default = ($context === 'admin') ? 'admin_default' : 'default';
        try {
            // Simplified for environment without DB
            return $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    public static function asset($path) {
        return "/themes/assets/" . ltrim($path, '/');
    }
}
