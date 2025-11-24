<?php
class ClientFirewallController {

    private function checkClient() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login'); exit;
        }
    }

    public function index() {
        $this->checkClient();
        $db = Database::connect();

        $userId = $_SESSION['user']['id'];
        $sites = $db->prepare("SELECT * FROM firewall_sites WHERE user_id = ? ORDER BY created_at DESC");
        $sites->execute([$userId]);
        $sites = $sites->fetchAll(PDO::FETCH_ASSOC);

        View::render('client_firewall', ['sites' => $sites], 'front');
    }

    public function add() {
        $this->checkClient();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $domain = trim($_POST['domain']);
            $limit = (int)$_POST['req_limit'];
            $window = (int)$_POST['req_window'];
            $mode = $_POST['mode'];

            // Validation Domain
            if (empty($domain)) {
                // Error
                header('Location: /client/firewall?error=empty'); exit;
            }

            // EXCLUSION RULE
            if (preg_match('/(bastivan|cshybridconnect)/i', $domain)) {
                 $_SESSION['flash_error'] = "L'ajout de ce domaine est interdit.";
                 header('Location: /client/firewall'); exit;
            }

            $db = Database::connect();
            try {
                $stmt = $db->prepare("INSERT INTO firewall_sites (user_id, domain, req_limit, req_window, ban_duration, mode) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_SESSION['user']['id'],
                    $domain,
                    $limit > 0 ? $limit : 100,
                    $window > 0 ? $window : 60,
                    3600, // Default ban 1h
                    in_array($mode, ['active', 'challenge', 'monitor']) ? $mode : 'active'
                ]);
                $_SESSION['flash_success'] = "Domaine ajouté avec succès.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Ce domaine est déjà protégé.";
            }
        }
        header('Location: /client/firewall');
    }

    public function delete() {
        $this->checkClient();
        if (isset($_POST['id'])) {
            $db = Database::connect();
            $stmt = $db->prepare("DELETE FROM firewall_sites WHERE id = ? AND user_id = ?");
            $stmt->execute([$_POST['id'], $_SESSION['user']['id']]);
        }
        header('Location: /client/firewall');
    }

    public function logs() {
        $this->checkClient();
        $db = Database::connect();
        $userId = $_SESSION['user']['id'];

        // Fetch user's sites
        $stmt = $db->prepare("SELECT domain FROM firewall_sites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $domains = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($domains)) {
            View::render('client_firewall_logs', ['logs' => [], 'bans' => [], 'domains' => []], 'front');
            return;
        }

        // Fetch high traffic IPs (potential threats)
        $placeholders = implode(',', array_fill(0, count($domains), '?'));
        $stmt = $db->prepare("SELECT * FROM firewall_traffic WHERE domain IN ($placeholders) ORDER BY request_count DESC LIMIT 50");
        $stmt->execute($domains);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Bans
        $stmt = $db->prepare("SELECT * FROM firewall_bans WHERE domain IN ($placeholders) ORDER BY created_at DESC");
        $stmt->execute($domains);
        $bans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('client_firewall_logs', ['logs' => $logs, 'bans' => $bans, 'domains' => $domains], 'front');
    }

    public function blockIp() {
        $this->checkClient();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = trim($_POST['ip_address']);
            $domain = $_POST['domain'];

            // Verify ownership of domain
            $db = Database::connect();
            $stmt = $db->prepare("SELECT 1 FROM firewall_sites WHERE domain = ? AND user_id = ?");
            $stmt->execute([$domain, $_SESSION['user']['id']]);

            if ($stmt->fetch()) {
                 // Insert Ban via SQL directly to avoid module function dependency here
                 $expires = date('Y-m-d H:i:s', time() + 86400 * 30); // 30 days
                 $stmt = $db->prepare("INSERT INTO firewall_bans (ip_address, domain, reason, expires_at) VALUES (?, ?, 'Manual Block by Client', ?)");
                 $stmt->execute([$ip, $domain, $expires]);
                 $_SESSION['flash_success'] = "IP $ip bloquée sur $domain.";
            } else {
                 $_SESSION['flash_error'] = "Domaine invalide.";
            }
        }
        header('Location: /client/firewall/logs');
    }

    public function unblockIp() {
        $this->checkClient();
        if (isset($_POST['id'])) {
             $db = Database::connect();
             // Ensure the ban belongs to a domain owned by the user
             $stmt = $db->prepare("
                DELETE b FROM firewall_bans b
                JOIN firewall_sites s ON b.domain = s.domain
                WHERE b.id = ? AND s.user_id = ?
             ");
             $stmt->execute([$_POST['id'], $_SESSION['user']['id']]);
             $_SESSION['flash_success'] = "IP débloquée.";
        }
        header('Location: /client/firewall/logs');
    }
}
