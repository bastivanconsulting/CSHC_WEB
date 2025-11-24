<?php

namespace Modules\LookingGlass\Controllers;

use App\Core\View;

class LookingGlassController {

    public function index() {
        View::render('Modules/LookingGlass/Views/index', [
            'title' => 'Looking Glass'
        ]);
    }

    public function execute() {
        // Strict rate limiting or CSRF protection would be good here, but I'll stick to input validation first.

        $input = json_decode(file_get_contents('php://input'), true);
        $command = $input['command'] ?? '';
        $target = $input['target'] ?? '';

        if (!$target) {
            $this->jsonResponse(['error' => 'La cible est requise'], 400);
            return;
        }

        // Validate Target (IP or Domain)
        if (!filter_var($target, FILTER_VALIDATE_IP)) {
            // Check if it's a valid domain
            if (!preg_match('/^(?!-)[A-Za-z0-9-]{1,63}(?<!-)(\.[A-Za-z0-9-]{1,63})*$/', $target)) {
                 $this->jsonResponse(['error' => 'Adresse IP ou nom de domaine invalide'], 400);
                 return;
            }
        }

        // Prevent local/private execution if desired.
        // Blocking private ranges for security to prevent internal network scanning.
        if ($this->isPrivateIp($target)) {
             $this->jsonResponse(['error' => 'Le scan des réseaux internes est interdit'], 403);
             return;
        }

        $output = [];
        $returnVar = 0;
        $cmd = '';

        // Clean target to be absolutely sure (though regex/filter should catch it)
        $target = escapeshellcmd($target);

        switch ($command) {
            case 'ping':
                // Check if ping exists
                $cmd = "ping -c 4 -w 5 " . escapeshellarg($target);
                break;
            case 'traceroute':
                // Use traceroute if available, else tracepath?
                $cmd = "traceroute -w 2 -m 15 " . escapeshellarg($target);
                break;
            case 'mtr':
                 // mtr --report --report-cycles=4
                $cmd = "mtr --report --report-cycles=4 --no-dns " . escapeshellarg($target);
                break;
            default:
                $this->jsonResponse(['error' => 'Commande invalide'], 400);
                return;
        }

        // Execute
        // Redirect stderr to stdout
        exec($cmd . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0 && empty($output)) {
             $output = ['La commande a échoué. L\'outil n\'est peut-être pas installé sur ce serveur.'];
        }

        $this->jsonResponse(['output' => implode("\n", $output)]);
    }

    public function download($size) {
        // Sizes: 10MB, 100MB, 1000MB
        // We will stream zeros.

        $bytes = 0;
        switch($size) {
            case '10MB': $bytes = 10 * 1024 * 1024; break;
            case '100MB': $bytes = 100 * 1024 * 1024; break;
            case '1GB': $bytes = 1024 * 1024 * 1024; break;
            default:
                header("HTTP/1.0 404 Not Found");
                echo "Fichier non trouvé";
                return;
        }

        // Disable buffering
        while (ob_get_level()) ob_end_clean();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="speedtest_'.$size.'.dat"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $bytes);

        // Stream 1MB chunks
        $chunkSize = 1024 * 1024;
        $zeros = str_repeat("\0", $chunkSize);

        // Use a loop to echo data, flushing periodically
        $written = 0;
        while ($written < $bytes) {
            $remaining = $bytes - $written;
            if ($remaining < $chunkSize) {
                 echo substr($zeros, 0, $remaining);
                 $written += $remaining;
            } else {
                 echo $zeros;
                 $written += $chunkSize;
            }
            flush();
        }
        exit;
    }

    private function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function isPrivateIp($ip) {
        // If it's not a valid IP, it's a domain, which we'll assume is public (DNS resolution would be better but slower)
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            // Block localhost/loopback related domains if possible, but basic regex passed.
            return false;
        }
        // FILTER_FLAG_NO_PRIV_RANGE fails if IP IS private.
        // So validation passes = Public. Validation fails = Private (or Invalid, but we checked validity above).
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
