<?php
namespace Modules\OvhApi\Controllers;

use Settings;
use View;
use Database;
use Modules\OvhApi\Classes\OvhApi;

class OvhController {

    private function checkAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /login'); exit;
        }
    }

    private function getApiClient() {
        $ak = Settings::get('ovh_ak');
        $as = Settings::get('ovh_as');
        $ck = Settings::get('ovh_ck');
        $ep = Settings::get('ovh_endpoint', 'ovh-eu');

        if (!$ak || !$as || !$ck) {
            return null;
        }

        return new OvhApi($ak, $as, $ep, $ck);
    }

    // --- DASHBOARD : LISTE DES IPs ---
    public function index() {
        $this->checkAdmin();
        $ovh = $this->getApiClient();

        $ips = [];
        $error = null;

        if (!$ovh) {
            // Pas configuré -> Redirection Settings ou Message
            // Pour l'instant on laisse vide et la vue affichera un lien
        } else {
            try {
                // On récupère les IPs
                // API: GET /ip
                // Note: L'API retourne une liste de chaînes (les blocks IP).
                // On peut vouloir filtrer pour "failover" si besoin, mais l'API retourne tout.
                // On va afficher tout ce que l'API renvoie.
                $ipBlocks = $ovh->get('/ip');

                // Pour chaque IP, on pourrait vouloir récupérer les infos, mais c'est lourd.
                // On va juste lister les blocks pour commencer.
                // Si c'est un /32 ou un block, on l'affiche.

                // Pour enrichir, on peut essayer de récupérer le type pour savoir si c'est failover
                // GET /ip/{ip}
                // Mais faire N requêtes va être lent. On affiche la liste simple.
                $ips = $ipBlocks;

            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        View::render('modules/OvhApi/Views/index', [
            'ips' => $ips,
            'configured' => ($ovh !== null),
            'error' => $error
        ], 'admin');
    }

    // --- SETTINGS ---
    public function settings() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Settings::set('ovh_ak', trim($_POST['ovh_ak']));
            Settings::set('ovh_as', trim($_POST['ovh_as']));
            Settings::set('ovh_ck', trim($_POST['ovh_ck']));
            Settings::set('ovh_endpoint', trim($_POST['ovh_endpoint']));

            header('Location: /admin/ovh'); exit;
        }

        $settings = [
            'ovh_ak' => Settings::get('ovh_ak'),
            'ovh_as' => Settings::get('ovh_as'),
            'ovh_ck' => Settings::get('ovh_ck'),
            'ovh_endpoint' => Settings::get('ovh_endpoint', 'ovh-eu')
        ];

        View::render('modules/OvhApi/Views/settings', ['settings' => $settings], 'admin');
    }

    // --- DETAILS IP (Firewall & PTR) ---
    public function viewIp($encodedIp) {
        $this->checkAdmin();
        // $encodedIp est capture par regex, souvent il faut decoder car '/' -> '%2F'
        $ip = urldecode($encodedIp);

        $ovh = $this->getApiClient();
        if (!$ovh) { header('Location: /admin/ovh/settings'); exit; }

        $details = [];
        $firewall = null;
        $rules = [];
        $error = null;
        $ptr = null;

        try {
            // 1. Info IP
            $details = $ovh->get('/ip/' . urlencode($ip));

            // 2. Firewall Status
            // Check si le firewall est activé : GET /ip/{ip}/firewall/{ip}
            // Attention: Pour une IP Failover (souvent /32), l'IP du firewall est l'IP elle-même.
            // Si c'est un bloc, c'est plus complexe, mais le module est pour "IP Failover" qui sont souvent unitaires.
            // On tente de récupérer le firewall pour l'IP cible.
            try {
                $firewall = $ovh->get('/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip));

                if ($firewall && $firewall['enabled']) {
                    // 3. Rules (si activé)
                    $ruleIds = $ovh->get('/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip) . '/rules');
                    foreach ($ruleIds as $rid) {
                        // On récupère le détail de chaque règle (peut être lent s'il y en a beaucoup,
                        // optimisation future: ajax ou chargement lazy)
                        // Limitons à 20 pour éviter le timeout
                        $rules[] = $ovh->get('/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip) . '/rules/' . $rid);
                    }
                    // Trier par sequence
                    usort($rules, function($a, $b) {
                        return $a['sequence'] - $b['sequence'];
                    });
                }
            } catch (\Exception $e) {
                // Si 404, ça veut dire pas de firewall créé/activé souvent
                $firewall = null;
            }

            // 4. Reverse DNS (PTR)
            try {
               $ptrData = $ovh->get('/ip/' . urlencode($ip) . '/reverse/' . urlencode($ip));
               $ptr = $ptrData['reverse'] ?? '';
            } catch (\Exception $e) {
               $ptr = '';
            }


        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        View::render('modules/OvhApi/Views/ip_details', [
            'ip' => $ip,
            'details' => $details,
            'firewall' => $firewall,
            'rules' => $rules,
            'ptr' => $ptr,
            'error' => $error
        ], 'admin');
    }

    // --- ACTIONS: Firewall Toggle ---
    public function toggleFirewall() {
        $this->checkAdmin();
        $ip = $_POST['ip'];
        $action = $_POST['action']; // 'enable' or 'disable'

        $ovh = $this->getApiClient();

        try {
            if ($action === 'enable') {
                // 1. Tenter de créer le firewall (si inexistant)
                try {
                    $ovh->post('/ip/' . urlencode($ip) . '/firewall', ['ipOnFirewall' => $ip]);
                } catch (\Exception $e) {
                    // Ignore error if firewall already exists (409 Conflict)
                }

                // 2. Activer explicitement le firewall
                $ovh->put('/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip), ['enabled' => true]);
                $_SESSION['flash_success'] = "Firewall activé avec succès.";

            } else {
                // Désactivation
                $ovh->put('/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip), ['enabled' => false]);
                $_SESSION['flash_success'] = "Firewall désactivé.";
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Erreur: " . $e->getMessage();
        }

        header('Location: /admin/ovh/ip/' . urlencode($ip));
    }

    // --- ACTIONS: Add Rule ---
    public function addRule() {
        $this->checkAdmin();
        $ip = $_POST['ip'];

        $ovh = $this->getApiClient();

        $rule = [
            'sequence' => (int)$_POST['sequence'],
            'action'   => $_POST['action'],
            'protocol' => $_POST['protocol'],
        ];

        // Only add source if provided
        if (!empty($_POST['source'])) {
            $rule['source'] = $_POST['source'];
        }

        // Ports depend on protocol
        if (in_array($_POST['protocol'], ['tcp', 'udp'])) {
             if (!empty($_POST['sourcePort'])) $rule['sourcePort'] = (int)$_POST['sourcePort'];
             if (!empty($_POST['destinationPort'])) $rule['destinationPort'] = (int)$_POST['destinationPort'];
        }

        try {
            $ovh->post('/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip) . '/rules', $rule);
            $_SESSION['flash_success'] = "Règle ajoutée.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Erreur ajout règle: " . $e->getMessage();
        }

        header('Location: /admin/ovh/ip/' . urlencode($ip));
    }

    // --- ACTIONS: Delete Rule ---
    public function deleteRule() {
        $this->checkAdmin();
        $ip = $_POST['ip'];
        $sequence = $_POST['sequence'];

        $ovh = $this->getApiClient();

        try {
            $ovh->delete('/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip) . '/rules/' . $sequence);
            $_SESSION['flash_success'] = "Règle supprimée.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Erreur suppression règle: " . $e->getMessage();
        }

        header('Location: /admin/ovh/ip/' . urlencode($ip));
    }

    // --- ACTIONS: PTR ---
    public function updatePtr() {
        $this->checkAdmin();
        $ip = $_POST['ip'];
        $reverse = trim($_POST['reverse']);

        $ovh = $this->getApiClient();

        try {
             $ovh->post('/ip/' . urlencode($ip) . '/reverse', [
                 'ipReverse' => $ip,
                 'reverse' => $reverse
             ]);
             $_SESSION['flash_success'] = "Reverse DNS mis à jour.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Erreur PTR: " . $e->getMessage();
        }

        header('Location: /admin/ovh/ip/' . urlencode($ip));
    }
}
