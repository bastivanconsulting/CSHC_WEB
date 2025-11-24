<?php
namespace Modules\OvhApi\Classes;

class OvhApi {
    private $applicationKey;
    private $applicationSecret;
    private $consumerKey;
    private $endpoint;
    private $timeDelta = null;

    private $endpoints = [
        'ovh-eu'        => 'https://eu.api.ovh.com/1.0',
        'ovh-ca'        => 'https://ca.api.ovh.com/1.0',
        'kimsufi-eu'    => 'https://eu.api.kimsufi.com/1.0',
        'kimsufi-ca'    => 'https://ca.api.kimsufi.com/1.0',
        'soyoustart-eu' => 'https://eu.api.soyoustart.com/1.0',
        'soyoustart-ca' => 'https://ca.api.soyoustart.com/1.0',
    ];

    public function __construct($ak, $as, $endpoint, $ck) {
        $this->applicationKey = $ak;
        $this->applicationSecret = $as;
        $this->consumerKey = $ck;
        $this->endpoint = isset($this->endpoints[$endpoint]) ? $this->endpoints[$endpoint] : $endpoint;
    }

    /**
     * Calculate time delta between local and OVH server
     */
    private function calculateTimeDelta() {
        if ($this->timeDelta === null) {
            $ch = curl_init($this->endpoint . '/auth/time');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $serverTime = (int)$response;
            $this->timeDelta = $serverTime - time();
        }
        return $this->timeDelta;
    }

    /**
     * Generate signature for the request
     */
    private function getSignature($method, $url, $body, $timestamp) {
        $toSign = $this->applicationSecret . '+' .
                  $this->consumerKey . '+' .
                  $method . '+' .
                  $url . '+' .
                  $body . '+' .
                  $timestamp;

        return '$1$' . sha1($toSign);
    }

    /**
     * Execute HTTP request
     */
    private function request($method, $path, $content = null) {
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        $url = $this->endpoint . $path;

        // Body handling
        $body = '';
        if ($content !== null) {
            $body = json_encode($content);
        }

        // Timestamp & Signature
        $timestamp = time() + $this->calculateTimeDelta();
        $signature = $this->getSignature($method, $url, $body, $timestamp);

        // Headers
        $headers = [
            'Content-Type: application/json',
            'X-Ovh-Application: ' . $this->applicationKey,
            'X-Ovh-Timestamp: ' . $timestamp,
            'X-Ovh-Signature: ' . $signature,
            'X-Ovh-Consumer: ' . $this->consumerKey
        ];

        // Curl Init
        $ch = curl_init($url);
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
            throw new \Exception("Curl Error: $error");
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $msg = isset($decoded['message']) ? $decoded['message'] : 'Unknown Error';
            throw new \Exception("OVH API Error ($httpCode): $msg");
        }

        return $decoded;
    }

    public function get($path) {
        return $this->request('GET', $path);
    }

    public function post($path, $content = []) {
        return $this->request('POST', $path, $content);
    }

    public function put($path, $content = []) {
        return $this->request('PUT', $path, $content);
    }

    public function delete($path) {
        return $this->request('DELETE', $path);
    }
}
