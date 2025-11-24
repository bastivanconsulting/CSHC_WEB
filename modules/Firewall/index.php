<?php
/**
 * Module: Firewall (WAF & Rate Limiting)
 * Description: Bloque les IP malveillantes, limite le taux de requêtes, et protège les sites externes via API.
 */

$db = Database::connect();

// 1. AUTO-MIGRATION
try {
    $db->query("SELECT 1 FROM firewall_bans LIMIT 1");
    // Migration: Ajout colonne domain si absente
    try {
        $db->query("SELECT domain FROM firewall_bans LIMIT 1");
    } catch (Exception $e) {
        $db->exec("ALTER TABLE firewall_bans ADD COLUMN domain VARCHAR(100) DEFAULT 'local' AFTER ip_address");
        try { $db->exec("CREATE INDEX idx_domain ON firewall_bans(domain)"); } catch(Exception $ex) {}
    }
} catch (Exception $e) {
    // Création table bans
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS firewall_bans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            domain VARCHAR(100) DEFAULT 'local',
            reason VARCHAR(255) DEFAULT 'Manual Ban',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NULL,
            INDEX(ip_address),
            INDEX(domain)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Exception $ex) {}
}

try {
    // Check if table exists
    $db->query("SELECT 1 FROM firewall_traffic LIMIT 1");

    // Check if 'domain' column exists
    try {
        $db->query("SELECT domain FROM firewall_traffic LIMIT 1");
    } catch (Exception $e) {
        // Column missing - ALTER TABLE
        try {
            $db->exec("ALTER TABLE firewall_traffic ADD COLUMN domain VARCHAR(100) DEFAULT 'local'");
            $db->exec("ALTER TABLE firewall_traffic DROP PRIMARY KEY, ADD PRIMARY KEY (ip_address, domain)");
        } catch (Exception $ex) {}
    }

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
    } catch (Exception $ex) {}
}

// Creation table firewall_sites
try {
    $db->query("SELECT 1 FROM firewall_sites LIMIT 1");
    // Migration: Ajout colonnes user_id et mode si absentes
    try {
        $db->query("SELECT user_id FROM firewall_sites LIMIT 1");
    } catch (Exception $e) {
        $db->exec("ALTER TABLE firewall_sites ADD COLUMN user_id INT NULL AFTER id");
        $db->exec("ALTER TABLE firewall_sites ADD COLUMN mode ENUM('active', 'challenge', 'monitor') DEFAULT 'active' AFTER ban_duration");
        $db->exec("ALTER TABLE firewall_sites ADD COLUMN secret_token VARCHAR(64) NULL");
    }
    // Migration: Ajout verification
    try {
        $db->query("SELECT verification_token FROM firewall_sites LIMIT 1");
    } catch (Exception $e) {
        $db->exec("ALTER TABLE firewall_sites ADD COLUMN verification_token VARCHAR(64) NULL AFTER secret_token");
        $db->exec("ALTER TABLE firewall_sites ADD COLUMN verified TINYINT(1) DEFAULT 0 AFTER verification_token");
    }

} catch (Exception $e) {
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS firewall_sites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            domain VARCHAR(255) NOT NULL,
            req_limit INT DEFAULT 100,
            req_window INT DEFAULT 60,
            ban_duration INT DEFAULT 3600,
            mode ENUM('active', 'challenge', 'monitor') DEFAULT 'active',
            secret_token VARCHAR(64) NULL,
            verification_token VARCHAR(64) NULL,
            verified TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(domain)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Exception $ex) {}
}

// 2. API LOGIC (Pour sites externes)
if (isset($_GET['firewall_api'])) {
    if ($_GET['firewall_api'] === 'check') {
        firewall_api_check();
        exit;
    }
    if ($_GET['firewall_api'] === 'verify') {
        firewall_api_verify();
        exit;
    }
}

// 3. JS SERVING
if (isset($_GET['firewall_js'])) {
    // CORRECTION CORS: Autoriser le chargement du script depuis n'importe quelle origine
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/javascript');
    $apiUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/?firewall_api";
    echo <<<JS
(function(){
    var API_URL = "$apiUrl";
    var domain = window.location.hostname;

    // Helper: Get Token
    function getToken() {
        return localStorage.getItem('cshc_waf_token_' + domain);
    }

    // Helper: Set Token
    function setToken(token) {
        localStorage.setItem('cshc_waf_token_' + domain, token);
    }

    // Main Check
    function checkAccess() {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", API_URL + "=check", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var res = JSON.parse(xhr.responseText);
                handleResponse(res);
            }
        };
        var token = getToken();
        var data = "origin=" + encodeURIComponent(domain);
        if(token) data += "&token=" + encodeURIComponent(token);

        // Collect minimal browser signals for basic analysis
        data += "&ua=" + encodeURIComponent(navigator.userAgent);

        xhr.send(data);
    }

    function handleResponse(res) {
        if(res.status === 'blocked') {
            document.body.innerHTML = '<div style="background:#020617;color:#ef4444;height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:sans-serif;text-align:center;"><h1 style="font-size:3rem;margin-bottom:1rem;">ACCESS DENIED</h1><p>Your IP has been blocked by CSHC Security.</p><p style="color:#94a3b8;font-size:0.9rem;">ID: ' + (res.reason || 'WAF') + '</p></div>';
        }
        else if(res.status === 'challenge') {
            showChallenge();
        }
    }

    function showChallenge() {
        var html = '<div id="cshc-challenge-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(2, 6, 23, 0.95);backdrop-filter:blur(10px);z-index:999999;display:flex;align-items:center;justify-content:center;font-family:\'Segoe UI\', sans-serif;">' +
            '<div style="background:rgba(30, 41, 59, 0.6);border:1px solid rgba(255,255,255,0.1);padding:3rem;border-radius:1rem;text-align:center;max-width:400px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">' +
                '<div style="margin-bottom:1.5rem;">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>' +
                '</div>' +
                '<h2 style="color:white;margin-bottom:1rem;font-size:1.5rem;">Security Check</h2>' +
                '<p style="color:#94a3b8;margin-bottom:2rem;">Please verify you are human to access this site.</p>' +
                '<button id="cshc-solve-btn" style="background:#2563eb;color:white;border:none;padding:0.75rem 2rem;border-radius:0.5rem;font-weight:bold;cursor:pointer;transition:all 0.2s;">Verify Now</button>' +
            '</div>' +
        '</div>';

        var div = document.createElement('div');
        div.innerHTML = html;
        document.body.appendChild(div);

        document.getElementById('cshc-solve-btn').onclick = function() {
            this.innerHTML = 'Verifying...';
            this.style.opacity = '0.7';
            this.disabled = true;

            setTimeout(function(){
                verifyChallenge();
            }, 1500);
        };
    }

    function verifyChallenge() {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", API_URL + "=verify", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var res = JSON.parse(xhr.responseText);
                if(res.status === 'success') {
                    setToken(res.token);
                    document.getElementById('cshc-challenge-overlay').remove();
                } else {
                    alert('Verification failed. Please try again.');
                    location.reload();
                }
            }
        };
        xhr.send("origin=" + encodeURIComponent(domain));
    }

    checkAccess();
})();
JS;
    exit;
}

// 4. LOGIQUE WAF INTERNE & HELPER FUNCTIONS

// --- INTELLIGENT WAF RULES ---
function firewall_analyze_payload() {
    // Basic OWASP-like signatures (SQLi, XSS, Path Traversal)
    $patterns = [
        // SQL Injection
        '/union\s+select/i',
        '/union\s+all\s+select/i',
        '/information_schema/i',
        '/waitfor\s+delay/i',
        '/exec\s+\(/i',
        '/concat\s*\(/i',
        // XSS
        '/<script/i',
        '/javascript:/i',
        '/onerror\s*=/i',
        '/onload\s*=/i',
        // Path Traversal
        '/\.\.\//',
        '/\/etc\/passwd/',
        '/\/bin\/sh/',
        // Shell Injection
        '/;.*(ls|cat|wget|curl|nc)/i'
    ];

    $check = null;
    $check = function($data) use ($patterns, &$check) {
        if (is_array($data)) {
            foreach ($data as $value) {
                if ($check($value)) return true;
            }
        } else {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $data)) return true;
            }
        }
        return false;
    };

    if ($check($_GET) || $check($_POST) || $check($_COOKIE)) {
        return true;
    }
    return false;
}
// -----------------------------

function firewall_get_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function firewall_api_check() {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    $origin = $_POST['origin'] ?? '';
    $origin = parse_url($origin, PHP_URL_HOST) ?: $origin;
    if (!$origin) $origin = 'unknown';

    $db = Database::connect();

    // Vérifier si le site est autorisé
    $stmt = $db->prepare("SELECT * FROM firewall_sites WHERE domain = ?");
    $stmt->execute([$origin]);
    $site = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$site) {
        echo json_encode(['status' => 'blocked', 'reason' => 'Unregistered Domain']);
        return;
    }

    // [VERIFICATION] Si pas vérifié, on bloque ou on met en warning?
    // Le client dit "Je pense aussi qu'il faut faire une validation".
    // Pour l'instant si pas vérifié, on peut bloquer.
    if ($site['verified'] == 0) {
        echo json_encode(['status' => 'blocked', 'reason' => 'Domain Verification Pending']);
        return;
    }

    $ip = firewall_get_ip();

    // [INTELLIGENT WAF] Analyze Payload
    if (firewall_analyze_payload()) {
        firewall_ban_ip($ip, "Malicious Payload Detected (WAF)", 86400, $origin);
        echo json_encode(['status' => 'blocked', 'reason' => 'Malicious Payload']);
        return;
    }

    // 1. Check Bans
    $stmt = $db->prepare("SELECT * FROM firewall_bans WHERE ip_address = ? AND (domain = 'local' OR domain = ?) AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$ip, $origin]);
    $ban = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ban) {
        echo json_encode(['status' => 'blocked', 'reason' => $ban['reason']]);
        return;
    }

    // 2. Check Challenge Mode
    if ($site['mode'] === 'challenge') {
        $clientToken = $_POST['token'] ?? '';
        $serverToken = hash('sha256', $ip . 'CSHC_SALT_' . date('Ymd'));
        if ($clientToken !== $serverToken) {
            echo json_encode(['status' => 'challenge']);
            return;
        }
    }

    // 3. Enforce Limits
    firewall_enforce_limit($ip, $site['req_limit'], $site['req_window'], $site['ban_duration'], $origin);

    echo json_encode(['status' => 'allowed']);
}

function firewall_api_verify() {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    $origin = $_POST['origin'] ?? '';
    $ip = firewall_get_ip();

    $token = hash('sha256', $ip . 'CSHC_SALT_' . date('Ymd'));
    echo json_encode(['status' => 'success', 'token' => $token]);
}

function firewall_enforce_limit($ip, $limit, $window, $banDuration = 3600, $domain = 'local') {
    $db = Database::connect();
    $now = time();

    if (rand(1, 100) === 1) {
        $db->exec("DELETE FROM firewall_traffic WHERE first_request_at < " . ($now - $window));
    }

    $stmt = $db->prepare("SELECT * FROM firewall_traffic WHERE ip_address = ? AND domain = ?");
    $stmt->execute([$ip, $domain]);
    $traffic = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($traffic) {
        if ($now - $traffic['first_request_at'] > $window) {
            $upd = $db->prepare("UPDATE firewall_traffic SET request_count = 1, first_request_at = ? WHERE ip_address = ? AND domain = ?");
            $upd->execute([$now, $ip, $domain]);
        } else {
            if ($traffic['request_count'] >= $limit) {
                firewall_ban_ip($ip, "Rate Limit Exceeded on $domain", $banDuration, $domain);

                if ($domain === 'local') {
                    firewall_render_denied($ip, "Rate Limit Exceeded");
                    exit;
                } else {
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
    $db = Database::connect();
    $ip = firewall_get_ip();

    // Check Bans
    $stmt = $db->prepare("SELECT * FROM firewall_bans WHERE ip_address = ? AND domain = 'local' AND (expires_at IS NULL OR expires_at > NOW())");
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

function firewall_ban_ip($ip, $reason, $durationSeconds = 86400, $domain = 'local') {
    $db = Database::connect();
    $expires = date('Y-m-d H:i:s', time() + $durationSeconds);
    $stmt = $db->prepare("INSERT INTO firewall_bans (ip_address, domain, reason, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$ip, $domain, $reason, $expires]);
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
    // Intelligent WAF for Local
    if (firewall_analyze_payload()) {
        firewall_ban_ip(firewall_get_ip(), "Malicious Payload (Local)", 86400, 'local');
        firewall_render_denied(firewall_get_ip(), "Malicious Payload");
        exit;
    }
    firewall_check();
}
