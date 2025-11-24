<?php
/**
 * Module: Forgot Password
 * Description: Gestion mot de passe oublié avec Captcha et Token de sécurité.
 */

use Modules\ForgotPassword\Controllers\ForgotPasswordController;

// 1. INITIALISATION BDD (Ajout des colonnes si absentes)
$db = Database::connect();
try {
    $db->query("SELECT reset_token FROM users LIMIT 1");
} catch (Exception $e) {
    // Les colonnes n'existent pas, on les crée
    $db->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) NULL AFTER role");
    $db->exec("ALTER TABLE users ADD COLUMN reset_expires DATETIME NULL AFTER reset_token");
}

// 2. ROUTE : FORMULAIRE DEMANDE (Email + Captcha)
Hook::listen('register_routes', function($routes) {
    $routes['forgot-password'] = [
        'controller' => ForgotPasswordController::class,
        'method' => 'forgotPassword'
    ];
    return $routes;
});

// 3. ROUTE : FORMULAIRE RESET (Nouveau mot de passe)
Hook::listen('register_routes', function($routes) {
    $routes['reset-password'] = [
        'controller' => ForgotPasswordController::class,
        'method' => 'resetPassword'
    ];
    return $routes;
});
