<?php
class View {
    
    /**
     * Affiche une vue en utilisant le thème actif.
     * @param string $viewName  Nom du fichier sans extension (ex: 'home')
     * @param array  $data      Données à extraire pour la vue (ex: ['user' => $u])
     * @param string $context   'front' ou 'admin' pour choisir le thème
     */
    public static function render($viewName, $data = [], $context = 'front') {
        // 1. Transforme le tableau ['cle' => 'valeur'] en variables $cle = 'valeur'
        extract($data);

        // 2. Récupère le thème actif (ex: 'default' ou 'admin_default')
        $theme = self::getActiveTheme($context);

        // 3. Construction des chemins absolus
        $baseDir = __DIR__ . '/../../themes/' . $theme;
        $viewFile = $baseDir . '/' . $viewName . '.php';
        $layoutFile = $baseDir . '/layout.php';

        // 4. Vérification de l'existence du fichier vue
        if (!file_exists($viewFile)) {
            die("<div style='background:#333; color:#ff5555; padding:20px; font-family:sans-serif;'>
                    <h3>Erreur de Template</h3>
                    <p>Impossible de trouver la vue : <strong>$viewName.php</strong></p>
                    <p>Chemin cherché : <code>$viewFile</code></p>
                    <p>Vérifiez que le fichier existe bien dans le dossier <code>themes/$theme/</code></p>
                 </div>");
        }

        // 5. Rendu de la vue (Mise en mémoire tampon)
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // 6. Inclusion du layout (Cadre global)
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            // Si pas de layout, on affiche le contenu brut (utile pour les modales ou AJAX)
            echo $content;
        }
    }

    /**
     * Récupère le dossier du thème actif depuis la BDD.
     */
    private static function getActiveTheme($context) {
        $default = ($context === 'admin') ? 'admin_default' : 'default';
        
        try {
            $db = Database::connect();
            // On vérifie la table settings
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute(['theme_' . $context]);
            $result = $stmt->fetch(PDO::FETCH_COLUMN);
            
            return $result ?: $default;
        } catch (Exception $e) {
            // Si la BDD n'est pas encore prête, on renvoie le défaut
            return $default;
        }
    }

    /**
     * Helper pour générer des liens vers les assets (CSS, JS, IMG).
     * Utilisation : <link href="<?= View::asset('css/style.css') ?>">
     */
    public static function asset($path) {
        // Retourne un chemin relatif simple.
        // Vous pouvez adapter ceci si vos assets sont stockés ailleurs.
        return "/themes/assets/" . ltrim($path, '/');
    }
}