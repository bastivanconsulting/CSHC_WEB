<?php
/**
 * Module: Audit Log
 * Description: Enregistre les événements de sécurité (connexions, tentatives, déconnexions).
 */

// 1. AUTO-MIGRATION : Création de la table audit_logs
$db = Database::connect();

try {
    $db->query("SELECT 1 FROM audit_logs LIMIT 1");
} catch (Exception $e) {
    // La table n'existe pas, on la crée
    try {
        $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            event VARCHAR(50) NOT NULL,
            details TEXT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->exec($sql);
    } catch (Exception $ex) {
        // Log error silently or display if debug
    }
}

// Fonction utilitaire pour logger
function audit_log($event, $details = null, $userId = null) {
    try {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, event, details, ip_address) VALUES (?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt->execute([$userId, $event, $details, $ip]);
    } catch (Exception $e) {
        // Fail silently to not break the app flow
    }
}

// 2. LISTENERS

// Tentative de connexion (avant vérification)
Hook::listen('before_login_attempt', function($args) {
    // On ne loggue pas tout pour éviter de spammer la DB, mais on pourrait.
    // Ici on peut juste initier quelque chose si besoin.
});

// Connexion réussie
Hook::listen('user_login_success', function($args) {
    $user = $args['user'];
    audit_log('LOGIN_SUCCESS', 'Connexion réussie pour ' . $user['email'], $user['id']);
});

// Connexion échouée
Hook::listen('user_login_failed', function($args) {
    $email = $args['email'];
    audit_log('LOGIN_FAILED', 'Echec de connexion pour ' . $email, null);
});

// Déconnexion
Hook::listen('user_logout', function($args) {
    $user = $args['user'];
    $userId = $user ? $user['id'] : null;
    $email = $user ? $user['email'] : 'Unknown';
    audit_log('LOGOUT', 'Déconnexion de ' . $email, $userId);
});
