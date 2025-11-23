<?php

namespace Modules\OvhApi\Services;

class OvhApiClient {
    private $appKey;
    private $appSecret;
    private $consumerKey;
    private $endpoint;

    public function __construct($appKey, $appSecret, $consumerKey, $endpoint = 'ovh-eu') {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->consumerKey = $consumerKey;
        $this->endpoint = $this->getEndpointUrl($endpoint);
    }

    private function getEndpointUrl($endpoint) {
        switch ($endpoint) {
            case 'ovh-eu': return 'https://eu.api.ovh.com/1.0';
            case 'ovh-ca': return 'https://ca.api.ovh.com/1.0';
            case 'ovh-us': return 'https://api.ovhcloud.com/1.0';
            default: return 'https://eu.api.ovh.com/1.0';
        }
    }

    private function request($method, $path, $content = null) {
        $url = $this->endpoint . $path;
        $now = time() + 0; // Local time, might need sync if server time drifts

        // Simple time sync if needed (could be improved by fetching /auth/time)
        // For now, assume server time is correct.

        $body = '';
        if ($content !== null && $method !== 'GET') {
            $body = json_encode($content, JSON_UNESCAPED_SLASHES);
        }

        $signature = '$1$' . sha1($this->appSecret . '+' . $this->consumerKey . '+' . $method . '+' . $url . '+' . $body . '+' . $now);

        $headers = [
            'Content-Type: application/json',
            'X-Ovh-Application: ' . $this->appKey,
            'X-Ovh-Timestamp: ' . $now,
            'X-Ovh-Signature: ' . $signature,
            'X-Ovh-Consumer: ' . $this->consumerKey,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("CURL Error: " . $error);
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $msg = isset($decoded['message']) ? $decoded['message'] : 'Unknown OVH Error';
            throw new \Exception("OVH API Error ($httpCode): $msg");
        }

        return $decoded;
    }

    // --- IP Management ---

    public function getIps() {
        return $this->request('GET', '/ip');
    }

    public function getIpDetails($ip) {
        // Since URL encoded IP might be needed if it contains slashes (CIDR), but usually single IP in list
        return $this->request('GET', '/ip/' . urlencode($ip));
    }

    public function updatePtr($ip, $ptr) {
        return $this->request('PUT', '/ip/' . urlencode($ip), ['reverse' => $ptr]);
    }

    // --- Firewall Management ---

    // Check firewall status/details
    public function getFirewall($ip) {
        // OVH API: /ip/{ip}/firewall/{ip}
        return $this->request('GET', '/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip));
    }

    // Toggle firewall (Create or Remove firewall service on IP)
    // Actually, on OVH, "Activer/Désactiver" usually means enabling the firewall mitigation.
    // /ip/{ip}/firewall/{ip} -> enabled: boolean
    public function toggleFirewall($ip, $enabled) {
        return $this->request('PUT', '/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip), ['enabled' => (bool)$enabled]);
    }

    // If the firewall structure doesn't exist, we might need to create it first?
    // /ip/{ip}/firewall POST
    public function createFirewall($ip) {
         return $this->request('POST', '/ip/' . urlencode($ip) . '/firewall', ['ipOnFirewall' => $ip]);
    }

    public function deleteFirewall($ip) {
        return $this->request('DELETE', '/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip));
    }

    // Rules
    public function getFirewallRules($ip) {
        // Returns list of sequence numbers
        $sequences = $this->request('GET', '/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip) . '/rules');
        $rules = [];
        if (is_array($sequences)) {
            // Sort sequences to be sure
            sort($sequences);
            // Fetch details for each rule (This can be slow, maybe limit or paginate in real world)
            // For this task, we fetch them.
            foreach ($sequences as $seq) {
                 try {
                     $rules[] = $this->request('GET', '/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip) . '/rules/' . $seq);
                 } catch (\Exception $e) {
                     // Rule might be gone
                 }
            }
        }
        return $rules;
    }

    public function createFirewallRule($ip, $data) {
        // data: { sequence, action, protocol, source, destinationPort, ... }
        return $this->request('POST', '/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip) . '/rules', $data);
    }

    public function deleteFirewallRule($ip, $sequence) {
        return $this->request('DELETE', '/ip/' . urlencode($ip) . '/firewall/' . urlencode($ip) . '/rules/' . $sequence);
    }
}
