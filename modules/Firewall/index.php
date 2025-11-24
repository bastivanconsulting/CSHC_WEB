<?php
/**
 * Module: Firewall (WAF & Rate Limiting)
 * Description: Bloque les IP malveillantes et limite le taux de requêtes.
 */

$db = Database::connect();

// 1. AUTO-MIGRATION
try {
    $db->query("SELECT 1 FROM firewall_bans LIMIT 1");
} catch (Exception $e) {
    // Création table bans
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS firewall_bans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            reason VARCHAR(255) DEFAULT 'Manual Ban',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NULL,
            INDEX(ip_address)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Exception $ex) {}
}

try {
    $db->query("SELECT 1 FROM firewall_traffic LIMIT 1");
} catch (Exception $e) {
    // Création table traffic (Rate Limiting)
    // On utilise MEMORY si possible pour la perf, sinon InnoDB
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS firewall_traffic (
            ip_address VARCHAR(45) NOT NULL,
            request_count INT DEFAULT 1,
            first_request_at INT NOT NULL,
            PRIMARY KEY (ip_address)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Exception $ex) {}
}

// 2. LOGIQUE WAF

function firewall_get_ip() {
    // Attention aux proxies (X-Forwarded-For), à sécuriser selon l'infra.
    // Ici on prend REMOTE_ADDR par défaut pour la sécurité (difficile à spoofer en TCP).
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function firewall_check() {
    // CORRECTION: Utilisation de Database::connect() au lieu de global $db
    // Cela évite l'erreur "Call to a member function prepare() on null"
    $db = Database::connect();
    $ip = firewall_get_ip();

    // A. CHECK BANS
    $stmt = $db->prepare("SELECT * FROM firewall_bans WHERE ip_address = ? AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$ip]);
    $ban = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ban) {
        firewall_render_denied($ip, $ban['reason']);
        exit;
    }

    // B. RATE LIMITING
    // Récupération des limites via Settings (ou valeurs par défaut)
    // Settings::get() est statique et dispo si Settings chargé (ce qui est le cas dans index.php)
    // Si Settings n'est pas dispo (ex: script externe), on garde le défaut.
    if (class_exists('Settings')) {
        $limit = (int) Settings::get('firewall_limit', 100);
        $window = (int) Settings::get('firewall_window', 60);
    } else {
        $limit = 100;
        $window = 60;
    }

    // Sécurité anti-crash si admin met 0
    if ($limit <= 0) $limit = 100;
    if ($window <= 0) $window = 60;

    $now = time();

    // Nettoyage périodique (chance 1/100) pour éviter d'exploser la table
    if (rand(1, 100) === 1) {
        $db->exec("DELETE FROM firewall_traffic WHERE first_request_at < " . ($now - $window));
    }

    $stmt = $db->prepare("SELECT * FROM firewall_traffic WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $traffic = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($traffic) {
        // Fenêtre expirée ?
        if ($now - $traffic['first_request_at'] > $window) {
            // Reset
            $upd = $db->prepare("UPDATE firewall_traffic SET request_count = 1, first_request_at = ? WHERE ip_address = ?");
            $upd->execute([$now, $ip]);
        } else {
            // Incrément
            if ($traffic['request_count'] >= $limit) {
                // BAN !
                firewall_ban_ip($ip, "Rate Limit Exceeded ($limit req/$window s)", 3600); // Ban 1h
                firewall_render_denied($ip, "Rate Limit Exceeded");
                exit;
            }
            $upd = $db->prepare("UPDATE firewall_traffic SET request_count = request_count + 1 WHERE ip_address = ?");
            $upd->execute([$ip]);
        }
    } else {
        // Nouveau
        $ins = $db->prepare("INSERT INTO firewall_traffic (ip_address, request_count, first_request_at) VALUES (?, 1, ?)");
        $ins->execute([$ip, $now]);
    }
}

function firewall_ban_ip($ip, $reason, $durationSeconds = 86400) {
    // CORRECTION: Utilisation de Database::connect()
    $db = Database::connect();
    $expires = date('Y-m-d H:i:s', time() + $durationSeconds);
    $stmt = $db->prepare("INSERT INTO firewall_bans (ip_address, reason, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$ip, $reason, $expires]);
}

function firewall_render_denied($ip, $reason) {
    // On charge la vue directement
    // On définit les variables pour la vue
    $error_ip = $ip;
    $error_reason = $reason;

    // On force le status 403
    http_response_code(403);

    // On essaie de charger le thème, sinon fallback simple
    if (file_exists(__DIR__ . '/../../themes/default/access_denied.php')) {
        // Simulation View::render sans le wrapper pour être sûr
        // Mais on veut quand même le style si possible.
        // Utilisons View::render avec layout false, ou include direct.
        // Comme on est dans un module, View est dispo.

        // On doit s'assurer que View est chargé (normalement oui via index.php)
        if (class_exists('View')) {
            View::render('access_denied', ['ip' => $ip, 'reason' => $reason], 'default', false);
        } else {
            include __DIR__ . '/../../themes/default/access_denied.php';
        }
    } else {
        echo "<h1>Access Denied</h1><p>IP: $ip</p><p>Reason: $reason</p>";
    }
}

// Exécution immédiate
firewall_check();
