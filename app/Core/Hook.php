<?php
class Hook {
    private static $listeners = [];

    // Permet à un module de s'accrocher
    public static function listen($hookName, $callback) {
        self::$listeners[$hookName][] = $callback;
    }

    // Appelé par le site pour afficher le contenu des modules
    public static function call($hookName, $args = []) {
        $output = "";
        if (isset(self::$listeners[$hookName])) {
            foreach (self::$listeners[$hookName] as $callback) {
                // CORRECTION ICI : On utilise call_user_func au lieu de call_user_func_array
                // Cela passe le tableau $args comme UN SEUL argument à la fonction,
                // au lieu d'essayer de le décomposer en paramètres nommés.
                $output .= call_user_func($callback, $args);
            }
        }
        return $output;
    }
    
    // Appelé pour filtrer des données (prix, tableaux...)
    public static function filter($hookName, $value, $args = []) {
        if (isset(self::$listeners[$hookName])) {
            foreach (self::$listeners[$hookName] as $callback) {
                $value = call_user_func($callback, $value, $args);
            }
        }
        return $value;
    }
}