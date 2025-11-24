<?php
/**
 * Module: Firewall (WAF & Rate Limiting)
 * Description: Bloque les IP malveillantes, limite le taux de requêtes, et protège les sites externes via API.
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
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS firewall_traffic (
            ip_address VARCHAR(45) NOT NULL,
            request_count INT DEFAULT 1,
            first_request_at INT NOT NULL,
            domain VARCHAR(100) DEFAULT 'local',
            PRIMARY KEY (ip_address, domain)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        // Note: Modified PK to include domain for multi-site separation
    } catch (Exception $ex) {
         // Migration for existing table without domain support if needed?
         // For now, assuming fresh or manually handled if key conflict.
         // Let's not break existing logic if column missing.
         try {
             $db->exec("ALTER TABLE firewall_traffic ADD COLUMN domain VARCHAR(100) DEFAULT 'local'");
             $db->exec("ALTER TABLE firewall_traffic DROP PRIMARY KEY, ADD PRIMARY KEY (ip_address, domain)");
         } catch(Exception $e2) {}
    }
}

// Creation table firewall_sites
try {
    $db->query("SELECT 1 FROM firewall_sites LIMIT 1");
} catch (Exception $e) {
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS firewall_sites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            domain VARCHAR(255) NOT NULL,
            req_limit INT DEFAULT 100,
            req_window INT DEFAULT 60,
            ban_duration INT DEFAULT 3600,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(domain)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Exception $ex) {}
}

// 2. API LOGIC (Pour sites externes)
if (isset($_GET['firewall_api']) && $_GET['firewall_api'] === 'check') {
    firewall_api_check();
    exit;
}

// 3. JS SERVING
if (isset($_GET['firewall_js'])) {
    header('Content-Type: application/javascript');
    $apiUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/?firewall_api=check";
    echo <<<JS
(function(){
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "$apiUrl", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var res = JSON.parse(xhr.responseText);
            if(res.status === 'blocked') {
                document.body.innerHTML = '<div style="background:#020617;color:white;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;"><h1>Access Denied by CSHC WAF</h1></div>';
                // Ou redirection: window.location.href = res.redirect_url;
            }
        }
    };
    xhr.send("origin=" + encodeURIComponent(window.location.hostname));
})();
JS;
    exit;
}

// 4. LOGIQUE WAF INTERNE
function firewall_get_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function firewall_api_check() {
    // CORS
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    $origin = $_POST['origin'] ?? '';
    // Nettoyage sommaire de l'origine
    $origin = parse_url($origin, PHP_URL_HOST) ?: $origin; // Si http://...
    if (!$origin) $origin = 'unknown';

    $db = Database::connect();

    // Vérifier si le site est autorisé
    $stmt = $db->prepare("SELECT * FROM firewall_sites WHERE domain = ?");
    $stmt->execute([$origin]);
    $site = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$site) {
        // Site non géré : on laisse passer ou on bloque ?
        // Par défaut on laisse passer mais sans protection
        echo json_encode(['status' => 'allowed', 'msg' => 'Site not protected']);
        return;
    }

    $ip = firewall_get_ip();

    // Check Global Ban
    $stmt = $db->prepare("SELECT * FROM firewall_bans WHERE ip_address = ? AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$ip]);
    $ban = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ban) {
        echo json_encode(['status' => 'blocked', 'reason' => $ban['reason']]);
        return;
    }

    // Rate Limit specific to site
    $limit = $site['req_limit'];
    $window = $site['req_window'];
    $banDuration = $site['ban_duration'];

    firewall_enforce_limit($ip, $limit, $window, $banDuration, $origin);

    echo json_encode(['status' => 'allowed']);
}

function firewall_enforce_limit($ip, $limit, $window, $banDuration = 3600, $domain = 'local') {
    $db = Database::connect();
    $now = time();

    // Nettoyage périodique
    if (rand(1, 100) === 1) {
        $db->exec("DELETE FROM firewall_traffic WHERE first_request_at < " . ($now - $window));
    }

    $stmt = $db->prepare("SELECT * FROM firewall_traffic WHERE ip_address = ? AND domain = ?");
    $stmt->execute([$ip, $domain]);
    $traffic = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($traffic) {
        if ($now - $traffic['first_request_at'] > $window) {
            // Reset
            $upd = $db->prepare("UPDATE firewall_traffic SET request_count = 1, first_request_at = ? WHERE ip_address = ? AND domain = ?");
            $upd->execute([$now, $ip, $domain]);
        } else {
            // Increment
            if ($traffic['request_count'] >= $limit) {
                // BAN
                firewall_ban_ip($ip, "Rate Limit Exceeded on $domain ($limit req/$window s)", $banDuration);

                // Si c'est l'API, on ne fait pas exit mais on retourne JSON dans l'appelant
                // Si c'est local, on fait exit
                if ($domain === 'local') {
                    firewall_render_denied($ip, "Rate Limit Exceeded");
                    exit;
                } else {
                    // Pour l'API, on doit retourner le blocage immédiatement
                    echo json_encode(['status' => 'blocked', 'reason' => "Rate Limit Exceeded"]);
                    exit;
                }
            }
            $upd = $db->prepare("UPDATE firewall_traffic SET request_count = request_count + 1 WHERE ip_address = ? AND domain = ?");
            $upd->execute([$ip, $domain]);
        }
    } else {
        $ins = $db->prepare("INSERT INTO firewall_traffic (ip_address, request_count, first_request_at, domain) VALUES (?, 1, ?, ?)");
        $ins->execute([$ip, $now, $domain]);
    }
}


function firewall_check() {
    // Mode Local
    $db = Database::connect();
    $ip = firewall_get_ip();

    // Check Bans
    $stmt = $db->prepare("SELECT * FROM firewall_bans WHERE ip_address = ? AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$ip]);
    $ban = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ban) {
        firewall_render_denied($ip, $ban['reason']);
        exit;
    }

    // Rate Limit Local
    if (class_exists('Settings')) {
        $limit = (int) Settings::get('firewall_limit', 100);
        $window = (int) Settings::get('firewall_window', 60);
    } else {
        $limit = 100;
        $window = 60;
    }
    if ($limit <= 0) $limit = 100;
    if ($window <= 0) $window = 60;

    firewall_enforce_limit($ip, $limit, $window, 3600, 'local');
}

function firewall_ban_ip($ip, $reason, $durationSeconds = 86400) {
    $db = Database::connect();
    $expires = date('Y-m-d H:i:s', time() + $durationSeconds);
    $stmt = $db->prepare("INSERT INTO firewall_bans (ip_address, reason, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$ip, $reason, $expires]);
}

function firewall_render_denied($ip, $reason) {
    http_response_code(403);
    if (file_exists(__DIR__ . '/../../themes/default/access_denied.php')) {
        if (class_exists('View')) {
            View::render('access_denied', ['ip' => $ip, 'reason' => $reason], 'default', false);
        } else {
            include __DIR__ . '/../../themes/default/access_denied.php';
        }
    } else {
        echo "<h1>Access Denied</h1><p>IP: $ip</p><p>Reason: $reason</p>";
    }
}

// Exécution immédiate (pour le trafic local)
if (!isset($_GET['firewall_api']) && !isset($_GET['firewall_js'])) {
    firewall_check();
}
