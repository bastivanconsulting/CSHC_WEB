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
}
