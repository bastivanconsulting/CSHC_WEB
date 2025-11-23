<?php
/**
 * Module: SMTP Master
 * Description: Gère l'envoi d'emails centralisé pour tous les autres modules.
 */

define('SMTP_CONFIG_FILE', __DIR__ . '/config.json');

// 1. SIGNALER QUE CE MODULE A UNE PAGE DE CONFIGURATION
Hook::listen('get_module_config_url', function($moduleName) {
    // On vérifie que c'est bien nous
    if ($moduleName === 'SMTP') {
        return '/admin/smtp';
    }
});

// (Lien du menu supprimé comme demandé)

// 2. CRÉER LA ROUTE DE CONFIGURATION
Hook::listen('register_routes', function($routes) {
    $routes['admin/smtp'] = function() {
        // Sécurité
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /login'); exit;
        }

        $message = "";
        
        // Traitement
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['test_email'])) {
                $testResult = SmtpHelper::send($_POST['test_email'], "Test SMTP CSHC", "Ceci est un email de test.");
                $message = $testResult === true 
                    ? "<div class='alert alert-success'>Email envoyé !</div>" 
                    : "<div class='alert alert-danger'>Erreur : $testResult</div>";
            } else {
                $config = [
                    'host' => $_POST['host'], 'port' => $_POST['port'],
                    'user' => $_POST['user'], 'pass' => $_POST['pass'],
                    'from' => $_POST['from'], 'name' => $_POST['name']
                ];
                file_put_contents(SMTP_CONFIG_FILE, json_encode($config));
                $message = "<div class='alert alert-success'>Configuration sauvegardée.</div>";
            }
        }

        $conf = file_exists(SMTP_CONFIG_FILE) ? json_decode(file_get_contents(SMTP_CONFIG_FILE), true) : [];
        
        // Vue
        ob_start();
        ?>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="text-white"><i class="fas fa-server text-warning me-2"></i>Configuration SMTP</h2>
                        <a href="/admin/modules" class="btn btn-outline-light btn-sm">Retour Modules</a>
                    </div>
                    <?= $message ?>
                    <div class="glass-card p-4">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-8"><label class="small text-secondary">Host</label><input type="text" name="host" class="form-control" value="<?= $conf['host'] ?? '' ?>" required></div>
                                <div class="col-md-4"><label class="small text-secondary">Port</label><input type="text" name="port" class="form-control" value="<?= $conf['port'] ?? '587' ?>" required></div>
                                <div class="col-md-6"><label class="small text-secondary">User</label><input type="text" name="user" class="form-control" value="<?= $conf['user'] ?? '' ?>" required></div>
                                <div class="col-md-6"><label class="small text-secondary">Pass</label><input type="password" name="pass" class="form-control" value="<?= $conf['pass'] ?? '' ?>" required></div>
                                <div class="col-md-6"><label class="small text-secondary">Email From</label><input type="email" name="from" class="form-control" value="<?= $conf['from'] ?? '' ?>" required></div>
                                <div class="col-md-6"><label class="small text-secondary">Name From</label><input type="text" name="name" class="form-control" value="<?= $conf['name'] ?? 'CSHC' ?>" required></div>
                            </div>
                            <div class="mt-4 text-end pt-3 border-top border-secondary">
                                <button class="btn btn-primary">Sauvegarder</button>
                            </div>
                        </form>
                    </div>
                    <div class="glass-card p-4 mt-4 border border-warning bg-dark bg-opacity-50">
                        <h5 class="text-warning mb-2">Test Configuration</h5>
                        <form method="POST" class="d-flex gap-2"><input type="email" name="test_email" class="form-control" placeholder="Email de test"><button class="btn btn-warning text-dark">Envoyer</button></form>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        $content = ob_get_clean();
        // Utilisation du layout Admin du nouveau thème
        require __DIR__ . '/../../themes/admin_default/layout.php';
    };
    return $routes;
});

// 3. SERVICE D'ENVOI
Hook::listen('send_email', function($args) {
    if (empty($args['to'])) return false;
    return SmtpHelper::send($args['to'], $args['subject'] ?? 'No Subject', $args['body'] ?? '');
});

class SmtpHelper {
    public static function send($to, $subject, $body) {
        if (!file_exists(SMTP_CONFIG_FILE)) return "Config manquante";
        $conf = json_decode(file_get_contents(SMTP_CONFIG_FILE), true);
        try {
            $socket = fsockopen($conf['host'], $conf['port'], $errno, $errstr, 10);
            if (!$socket) return $errstr;
            self::read($socket);
            self::cmd($socket, "EHLO " . $_SERVER['SERVER_NAME']);
            if ($conf['port'] == 587) {
                self::cmd($socket, "STARTTLS");
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                self::cmd($socket, "EHLO " . $_SERVER['SERVER_NAME']);
            }
            self::cmd($socket, "AUTH LOGIN");
            self::cmd($socket, base64_encode($conf['user']));
            self::cmd($socket, base64_encode($conf['pass']));
            self::cmd($socket, "MAIL FROM: <{$conf['from']}>");
            self::cmd($socket, "RCPT TO: <$to>");
            self::cmd($socket, "DATA");
            $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8\r\nTo: $to\r\nFrom: {$conf['name']} <{$conf['from']}>\r\nSubject: $subject\r\n";
            fputs($socket, "$headers\r\n$body\r\n.\r\n");
            self::read($socket);
            self::cmd($socket, "QUIT");
            fclose($socket);
            return true;
        } catch (Exception $e) { return $e->getMessage(); }
    }
    private static function cmd($s, $c) { fputs($s, $c . "\r\n"); return self::read($s); }
    private static function read($s) { 
        $r=""; while($str=fgets($s,515)){$r.=$str;if(substr($str,3,1)==" ")break;} return $r; 
    }
}