<?php

namespace Modules\OvhApi\Controllers;

use Settings;
use View;
use Modules\OvhApi\Services\OvhApiClient;

// Ensure dependencies are loaded
require_once __DIR__ . '/../../../../app/Controllers/AdminController.php';
require_once __DIR__ . '/../Services/OvhApiClient.php';

class AdminOvhController extends \AdminController {

    private function getClient() {
        $appKey = Settings::get('ovh_app_key');
        $appSecret = Settings::get('ovh_app_secret');
        $consumerKey = Settings::get('ovh_consumer_key');
        $endpoint = Settings::get('ovh_endpoint', 'ovh-eu');

        if (!$appKey || !$appSecret || !$consumerKey) {
            return null;
        }

        return new OvhApiClient($appKey, $appSecret, $consumerKey, $endpoint);
    }

    private function checkAdminAuth() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /login'); exit;
        }
    }

    public function settings() {
        $this->checkAdminAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Settings::set('ovh_app_key', trim($_POST['ovh_app_key']));
            Settings::set('ovh_app_secret', trim($_POST['ovh_app_secret']));
            Settings::set('ovh_consumer_key', trim($_POST['ovh_consumer_key']));
            Settings::set('ovh_endpoint', trim($_POST['ovh_endpoint']));

            // Redirect to self to show saved values
            header('Location: /admin/ovh/settings'); exit;
        }

        $settings = [
            'ovh_app_key' => Settings::get('ovh_app_key'),
            'ovh_app_secret' => Settings::get('ovh_app_secret'),
            'ovh_consumer_key' => Settings::get('ovh_consumer_key'),
            'ovh_endpoint' => Settings::get('ovh_endpoint', 'ovh-eu'),
        ];

        View::render('modules/OvhApi/views/admin/settings', ['settings' => $settings], 'admin');
    }

    public function index() {
        $this->checkAdminAuth();
        $client = $this->getClient();
        $ips = [];
        $error = null;

        if ($client) {
            try {
                // Get list of IPs blocks/individual IPs
                $ipBlocks = $client->getIps();

                // For this demo, we might want to fetch details for each to get PTR
                // But /ip returns list of strings "192.168.1.1/32"
                if (is_array($ipBlocks)) {
                    foreach ($ipBlocks as $block) {
                        try {
                            $details = $client->getIpDetails($block);
                            // Add to list
                            $ips[] = $details;
                        } catch (\Exception $e) {
                            // Skip if one fails
                        }
                    }
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = "Veuillez configurer l'API OVH dans les paramètres.";
        }

        View::render('modules/OvhApi/views/admin/index', ['ips' => $ips, 'error' => $error], 'admin');
    }

    public function updatePtr() {
        $this->checkAdminAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = $_POST['ip'];
            $ptr = $_POST['ptr'];

            $client = $this->getClient();
            if ($client) {
                try {
                    $client->updatePtr($ip, $ptr);
                } catch (\Exception $e) {
                    // Handle error (maybe flash message)
                }
            }
        }
        header('Location: /admin/ovh/manage'); exit;
    }

    public function firewall() {
        $this->checkAdminAuth();
        if (!isset($_GET['ip'])) {
            header('Location: /admin/ovh/manage'); exit;
        }

        $ip = $_GET['ip'];
        // Remove CIDR mask if present for display, but keep for API if needed?
        // Usually firewall is on the IP itself. OVH IPs usually come as x.x.x.x without mask in some calls, or with mask.
        // Let's assume the IP passed is valid.

        $client = $this->getClient();
        $firewall = null;
        $rules = [];
        $error = null;

        if ($client) {
            try {
                // Check if firewall exists
                try {
                    $firewall = $client->getFirewall($ip);
                    $rules = $client->getFirewallRules($ip);
                } catch (\Exception $e) {
                    // Firewall might not be created.
                    // If 404, it means no firewall configured.
                    if (strpos($e->getMessage(), '404') !== false) {
                        $firewall = false; // Not active/created
                    } else {
                        $error = $e->getMessage();
                    }
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        View::render('modules/OvhApi/views/admin/firewall', [
            'ip' => $ip,
            'firewall' => $firewall,
            'rules' => $rules,
            'error' => $error
        ], 'admin');
    }

    public function createFirewall() {
        $this->checkAdminAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = $_POST['ip'];
            $client = $this->getClient();
            if ($client) {
                try {
                    $client->createFirewall($ip);
                } catch (\Exception $e) {
                    // handle error
                }
            }
        }
        header('Location: /admin/ovh/firewall?ip=' . urlencode($ip)); exit;
    }

    public function toggleFirewall() {
        $this->checkAdminAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = $_POST['ip'];
            $enabled = isset($_POST['enabled']) ? $_POST['enabled'] : 0;

            $client = $this->getClient();
            if ($client) {
                try {
                    $client->toggleFirewall($ip, $enabled);
                } catch (\Exception $e) {
                    // handle error
                }
            }
        }
        header('Location: /admin/ovh/firewall?ip=' . urlencode($ip)); exit;
    }

    public function addRule() {
        $this->checkAdminAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = $_POST['ip'];

            $data = [
                'sequence' => (int)$_POST['sequence'],
                'action' => $_POST['action'],
                'protocol' => $_POST['protocol'],
                'source' => !empty($_POST['source']) ? $_POST['source'] : null,
                'destinationPort' => !empty($_POST['destinationPort']) ? (int)$_POST['destinationPort'] : null,
            ];

            // Clean nulls
            if (empty($data['source'])) unset($data['source']);
            if (empty($data['destinationPort'])) unset($data['destinationPort']);

            $client = $this->getClient();
            if ($client) {
                try {
                    $client->createFirewallRule($ip, $data);
                } catch (\Exception $e) {
                    // handle error
                }
            }
        }
        header('Location: /admin/ovh/firewall?ip=' . urlencode($ip)); exit;
    }

    public function deleteRule() {
        $this->checkAdminAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = $_POST['ip'];
            $sequence = $_POST['sequence'];

            $client = $this->getClient();
            if ($client) {
                try {
                    $client->deleteFirewallRule($ip, $sequence);
                } catch (\Exception $e) {
                    // handle error
                }
            }
        }
        header('Location: /admin/ovh/firewall?ip=' . urlencode($ip)); exit;
    }
}
