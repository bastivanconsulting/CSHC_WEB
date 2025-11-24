<?php
class View {
    
    /**
     * Affiche une vue en utilisant le thème actif.
     * @param string $viewName  Nom du fichier sans extension (ex: 'home') ou chemin relatif si commence par 'modules/'
     * @param array  $data      Données à extraire pour la vue (ex: ['user' => $u])
     * @param string $context   'front' ou 'admin' pour choisir le thème
     * @param bool   $useLayout Utiliser le layout global (header/footer) ou juste la vue brute
     */
    public static function render($viewName, $data = [], $context = 'front', $useLayout = true) {
        extract($data);

        $theme = self::getActiveTheme($context);
        $baseDir = __DIR__ . '/../../themes/' . $theme;
        $viewFile = '';

        // 1. SUPPORT DES VUES AVEC CHEMIN EXPLICITE 'modules/...'
        if (strpos($viewName, 'modules/') === 0) {
            $root = __DIR__ . '/../../';
            $viewFile = $root . $viewName . '.php';
        } else {
            // 2. CHERCHER DANS LE THÈME ACTIF
            $themeFile = $baseDir . '/' . $viewName . '.php';

            if (file_exists($themeFile)) {
                $viewFile = $themeFile;
            } else {
                // 3. AUTO-DÉCOUVERTE DU MODULE (Si appelé depuis un contrôleur de module)
                $moduleFile = self::discoverModuleView($viewName);
                if ($moduleFile && file_exists($moduleFile)) {
                    $viewFile = $moduleFile;
                } else {
                    // Défaut : on garde le chemin du thème pour l'erreur 404
                    $viewFile = $themeFile;
                }
            }
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

        if ($useLayout && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Tente de deviner le chemin de la vue en fonction de la classe appelante.
     * Si View::render('index') est appelé depuis Modules\MonModule\Controllers\MonController,
     * on cherche dans modules/MonModule/Views/index.php
     */
    private static function discoverModuleView($viewName) {
        // On augmente la limite à 5 pour être sûr de remonter au contrôleur
        // 0: discoverModuleView
        // 1: render
        // 2: Controller Method (C'est lui qu'on veut)
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        // On cherche le premier appelant qui est une classe
        foreach ($trace as $step) {
            if (isset($step['class']) && strpos($step['class'], 'Modules\\') === 0) {
                // Format: Modules\NomModule\Controllers\NomController
                $parts = explode('\\', $step['class']);
                // $parts[0] = Modules
                // $parts[1] = NomModule
                if (isset($parts[1])) {
                    $moduleName = $parts[1];
                    return __DIR__ . '/../../modules/' . $moduleName . '/Views/' . $viewName . '.php';
                }
            }
        }
        return null;
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
