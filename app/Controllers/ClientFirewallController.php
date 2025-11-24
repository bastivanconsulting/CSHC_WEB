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

        // Self-healing: Generate tokens for legacy sites that have none
        foreach ($sites as &$site) {
            if (empty($site['verification_token'])) {
                $token = bin2hex(random_bytes(16));
                $db->prepare("UPDATE firewall_sites SET verification_token = ? WHERE id = ?")
                   ->execute([$token, $site['id']]);
                $site['verification_token'] = $token;
            }
        }
        unset($site); // Break reference

        View::render('client_firewall', ['sites' => $sites], 'front');
    }

    public function add() {
        $this->checkClient();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputDomain = trim($_POST['domain']);
            $limit = (int)$_POST['req_limit'];
            $window = (int)$_POST['req_window'];
            $mode = $_POST['mode'];

            if (empty($inputDomain)) {
                header('Location: /client/firewall?error=empty'); exit;
            }

            // Logic: Add www automatically EXCEPT for subdomains
            // Detect subdomain: more than 2 parts (e.g., sub.domain.com vs domain.com)
            // Remove protocol and www first
            $cleanDomain = preg_replace('#^https?://#', '', $inputDomain);
            $cleanDomain = preg_replace('#^www\.#', '', $cleanDomain);

            $parts = explode('.', $cleanDomain);
            $domainsToAdd = [$cleanDomain]; // Always add base (e.g. example.com)

            // If it's a root domain (2 parts), add www version
            if (count($parts) === 2) {
                $domainsToAdd[] = 'www.' . $cleanDomain;
            }
            // If user typed www.example.com, we striped it to example.com, so we added both.
            // If user typed sub.example.com (3 parts), we keep only sub.example.com.

            $db = Database::connect();
            $successCount = 0;
            $verificationToken = bin2hex(random_bytes(16)); // Token for verification

            foreach ($domainsToAdd as $domain) {
                try {
                    // Check uniqueness globally (Prevent User B from hijacking User A's domain)
                    $stmt = $db->prepare("SELECT user_id FROM firewall_sites WHERE domain = ?");
                    $stmt->execute([$domain]);
                    if ($stmt->fetch()) {
                        // Already exists
                        continue;
                    }

                    $stmt = $db->prepare("INSERT INTO firewall_sites (user_id, domain, req_limit, req_window, ban_duration, mode, verification_token, verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
                    $stmt->execute([
                        $_SESSION['user']['id'],
                        $domain,
                        $limit > 0 ? $limit : 100,
                        $window > 0 ? $window : 60,
                        3600,
                        in_array($mode, ['active', 'challenge', 'monitor']) ? $mode : 'active',
                        $verificationToken
                    ]);
                    $successCount++;
                } catch (Exception $e) {
                    // Ignore duplicates
                }
            }

            if ($successCount > 0) {
                $_SESSION['flash_success'] = "$successCount domaine(s) ajouté(s). Veuillez vérifier la propriété.";
            } else {
                $_SESSION['flash_error'] = "Domaine(s) déjà existant(s) ou invalide(s).";
            }
        }
        header('Location: /client/firewall');
    }

    public function verify() {
        $this->checkClient();
        if (isset($_POST['id'])) {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM firewall_sites WHERE id = ? AND user_id = ?");
            $stmt->execute([$_POST['id'], $_SESSION['user']['id']]);
            $site = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($site) {
                // DNS Verification: Check for TXT record "_cshc-verify" containing the token
                // Or checking TXT on the domain root
                // Simplest: TXT on _cshc-verify.domain.com
                $txtDomain = "_cshc-verify." . $site['domain'];
                $records = dns_get_record($txtDomain, DNS_TXT);

                $verified = false;
                if ($records) {
                    foreach ($records as $r) {
                        if (isset($r['txt']) && trim($r['txt']) === $site['verification_token']) {
                            $verified = true;
                            break;
                        }
                    }
                }

                if ($verified) {
                    $db->prepare("UPDATE firewall_sites SET verified = 1 WHERE id = ?")->execute([$site['id']]);
                    $_SESSION['flash_success'] = "Domaine {$site['domain']} vérifié avec succès !";
                } else {
                    $_SESSION['flash_error'] = "Vérification échouée. Aucun enregistrement TXT valide trouvé sur $txtDomain.";
                }
            }
        }
        header('Location: /client/firewall');
    }

    public function update() {
        $this->checkClient();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $mode = $_POST['mode'];
            $limit = (int)$_POST['req_limit'];
            $window = (int)$_POST['req_window'];

            $db = Database::connect();
            // Verify ownership
            $stmt = $db->prepare("SELECT 1 FROM firewall_sites WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $_SESSION['user']['id']]);
            if ($stmt->fetch()) {
                $upd = $db->prepare("UPDATE firewall_sites SET mode = ?, req_limit = ?, req_window = ? WHERE id = ?");
                $upd->execute([
                    in_array($mode, ['active', 'challenge', 'monitor']) ? $mode : 'active',
                    $limit > 0 ? $limit : 100,
                    $window > 0 ? $window : 60,
                    $id
                ]);
                $_SESSION['flash_success'] = "Paramètres mis à jour.";
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
