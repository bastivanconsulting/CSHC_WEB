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
        // $encodedIp est capture par regex.
        // 1. On remplace le séparateur de sécurité '_' par '/'
        $ip = str_replace('_', '/', $encodedIp);
        // 2. On décode au cas où
        $ip = urldecode($ip);

        $ovh = $this->getApiClient();
        if (!$ovh) { header('Location: /admin/ovh/settings'); exit; }

        $error = null;

        // --- CAS 1: C'est un BLOC CIDR (ex: 82.21.0.0/28) ---
        if (strpos($ip, '/') !== false && substr($ip, -3) !== '/32') {
            // C'est un bloc. On doit lister les IPs contenues dedans.
            $ipsInBlock = $this->cidrToIps($ip);

            View::render('modules/OvhApi/Views/view_block', [
                'block' => $ip,
                'ips' => $ipsInBlock
            ], 'admin');
            return;
        }

        // --- CAS 2: C'est une IP UNIQUE (ou /32) ---
        // Si c'est un /32, on peut traiter comme une IP simple pour OVH
        // Mais pour l'affichage on garde le /32 si présent, ou on l'enlève selon le besoin.
        // OVH attend l'IP encodée.

        $details = [];
        $firewall = null;
        $rules = [];
        $ptr = null;

        // Contexte du bloc parent si fourni
        $parentBlock = isset($_GET['block']) ? $_GET['block'] : null;

        try {
            // 1. Info IP
            // Si on a un parentBlock, on peut être tenté de l'utiliser mais GET /ip/{ip} marche souvent.
            // On essaie direct.
            try {
                $details = $ovh->get('/ip/' . urlencode($ip));
            } catch (\Exception $e) {
                // Si ça échoue, peut-être parce que l'IP n'est pas un objet racine mais fait partie d'un bloc.
                $details = ['ip' => $ip, 'type' => 'Part of Block ' . $parentBlock];
            }

            // 2. Firewall Status
            // Pour le firewall, l'URL est /ip/{ipBlock}/firewall/{ipOnFirewall}
            // Si l'IP est un Failover unitaire, ipBlock == ipOnFirewall.
            // Si l'IP fait partie d'un bloc (ex: 82.21.0.1 dans 82.21.0.0/28),
            // il FAUT utiliser le bloc comme base : /ip/82.21.0.0%2F28/firewall/82.21.0.1

            $firewallUrlBase = '/ip/' . urlencode($ip);
            if ($parentBlock) {
                 $firewallUrlBase = '/ip/' . urlencode($parentBlock);
            }

            try {
                $firewall = $ovh->get($firewallUrlBase . '/firewall/' . urlencode($ip));

                if ($firewall && $firewall['enabled']) {
                    // 3. Rules (si activé)
                    $ruleIds = $ovh->get($firewallUrlBase . '/firewall/' . urlencode($ip) . '/rules');
                    foreach ($ruleIds as $rid) {
                        $rules[] = $ovh->get($firewallUrlBase . '/firewall/' . urlencode($ip) . '/rules/' . $rid);
                    }
                    usort($rules, function($a, $b) {
                        return $a['sequence'] - $b['sequence'];
                    });
                }
            } catch (\Exception $e) {
                $firewall = null;
            }

            // 4. Reverse DNS (PTR)
            // Endpoint: /ip/{ipBlock}/reverse/{ip}
            // Si standalone: /ip/{ip}/reverse/{ip}
            // Si bloc: /ip/{block}/reverse/{ip}
            $reverseUrlBase = '/ip/' . urlencode($ip);
            if ($parentBlock) {
                $reverseUrlBase = '/ip/' . urlencode($parentBlock);
            }

            try {
               $ptrData = $ovh->get($reverseUrlBase . '/reverse/' . urlencode($ip));
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
            'error' => $error,
            'parentBlock' => $parentBlock // Passer à la vue pour les formulaires
        ], 'admin');
    }

    private function cidrToIps($cidr) {
        $ips = [];
        list($ip, $mask) = explode('/', $cidr);

        $mask = (int)$mask;
        $ipLong = ip2long($ip);

        // Calcul du nombre d'IPs
        $size = pow(2, 32 - $mask);

        // Start IP (Network Address)
        // Attention: ip2long peut être signé ou non selon système, mais PHP gère bien les opérations bitwise
        $start = $ipLong & ((-1 << (32 - $mask)));

        for ($i = 0; $i < $size; $i++) {
            $currentIp = long2ip($start + $i);
            // On exclut souvent Network (.0) et Broadcast (.255) sur des vrais réseaux routés,
            // mais chez OVH Failover, TOUTES les IPs sont souvent utilisables (sauf peut-être gateway si VRACK).
            // Dans le doute, on liste TOUT. Le client saura.
            $ips[] = $currentIp;
        }

        return $ips;
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

        header('Location: /admin/ovh/ip/' . urlencode(str_replace('/', '_', $ip)));
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

        header('Location: /admin/ovh/ip/' . urlencode(str_replace('/', '_', $ip)));
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

        header('Location: /admin/ovh/ip/' . urlencode(str_replace('/', '_', $ip)));
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

        header('Location: /admin/ovh/ip/' . urlencode(str_replace('/', '_', $ip)));
    }
}
