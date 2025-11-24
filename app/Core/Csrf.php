<?php
class Csrf {

    // Génère un token et le stocke en session si nécessaire
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Vérifie le token (POST par défaut)
    public static function check() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                // Echec CSRF
                http_response_code(403);
                die('Erreur de sécurité (CSRF). Veuillez recharger la page.');
            }
        }
        return true;
    }

    // Retourne le champ input caché
    public static function field() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
