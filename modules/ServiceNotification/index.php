<?php
/**
 * Module: Service Notification Master
 * Description: Gère l'envoi d'emails professionnels lors des changements de statut (Création, Activation, Suspension).
 * Dépendance: Module SMTP (Hook 'send_email')
 */

// Fichier de stockage des options (pour les settings du module)
define('NOTIFICATION_SETTINGS_KEY', 'service_notification_settings');

// Les événements que l'on peut notifier
$notification_events = [
    'active'    => 'Activation de service (Client)',
    'suspended' => 'Suspension de service (Client)',
    'terminated'=> 'Résiliation confirmée (Client)',
    'pending'   => 'Nouvelle commande reçue (Admin)'
];

// =========================================================================
// 1. DÉFINITION DU TEMPLATE E-MAIL HTML (Titre et couleur dynamiques)
// =========================================================================
$email_template = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service CSHC - Notification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #444; margin: 0; padding: 0; background-color: #e8f0fe; }
        .container { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #d2e3fc; }
        /* CORRECTION : Couleur de l\'en-tête */
        .header { background-color: **HEADER_COLOR**; color: #ffffff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 30px; }
        .credentials-box { background-color: #f0f4ff; border: 1px solid #c8dcfc; padding: 20px; margin: 25px 0; border-radius: 6px; }
        .credentials-box h3 { color: #1a73e8; margin-top: 0; font-size: 18px; border-bottom: 1px solid #c8dcfc; padding-bottom: 10px; margin-bottom: 15px; }
        .credential-item { margin-bottom: 10px; font-size: 14px; display: flex; justify-content: space-between; }
        .credential-item strong { display: inline-block; width: 140px; color: #333; font-weight: 600; }
        .data-monospace { color: #000; font-family: monospace; background-color: #d2e3fc; padding: 3px 6px; border-radius: 3px; font-weight: bold; }
        /* CORRECTION : Couleur du bouton dynamique */
        .button { display: inline-block; background-color: **BUTTON_COLOR**; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 4px; font-weight: 600; margin-top: 20px; }
        .footer { background-color: #f4f7f6; color: #999; padding: 20px 30px; text-align: center; font-size: 12px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <h1>**HEADER_TITLE**</h1>
        </div>

        <!-- Contenu -->
        <div class="content">
            <p>Bonjour **CLIENT_NAME** (via **USER_EMAIL**),</p>
            
            <p>**BODY_INTRO**</p>

            <!-- Boîte des Identifiants (Clé) -->
            <div class="credentials-box">
                <h3><i class="fas fa-microchip" style="margin-right: 8px; color: #1a73e8;"></i> Spécifications Techniques</h3>
                
                <div class="credential-item">
                    <strong>Type de Service:</strong> 
                    <span class="data-monospace">**FTTX_TYPE**</span>
                </div>

                <!-- NOUVEAU BLOC DYNAMIQUE -->
                **DYNAMIC_BLOCK**
                
                <div style="margin-top: 15px; border-top: 1px dashed #c8dcfc; padding-top: 10px; font-size: 13px;">
                    **OTHER_INFO_DETAILS**
                </div>
                
            </div>

            <p>Connectez-vous à votre espace client pour consulter les détails, les factures, et les notes de configuration complètes :</p>
            
            <a href="**DASHBOARD_LINK**" class="button" style="color: #ffffff; text-align: center; text-decoration: none;">Accéder à votre Espace Client</a>

        </div>

        <!-- Pied de page -->
        <div class="footer">
            Ceci est un email automatique de CSHC par Bastivan Consulting.
        </div>
    </div>
</body>
</html>
';

// ----------------------------------------------------------------------------------
// 2. DÉCLARATION DU MODULE ET DE SA PAGE DE CONFIGURATION
// ----------------------------------------------------------------------------------

// Signalement de la page de config à l'Admin Modules
Hook::listen('get_module_config_url', function($moduleName) {
    if ($moduleName === 'ServiceNotification') {
        return '/admin/service-notify-config';
    }
});

// Enregistrement des routes
Hook::listen('register_routes', function($routes) use ($notification_events) {
    $routes['admin/service-notify-config'] = function() use ($notification_events) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /login'); exit;
        }

        $db = Database::connect();
        $message = "";

        // Traitement du formulaire de sauvegarde
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newSettings = [];
            foreach ($notification_events as $key => $label) {
                $newSettings[$key] = isset($_POST[$key]) ? 'on' : 'off';
            }
            
            // --- CORRECTION SQL ICI : Utilisation de la syntaxe ON DUPLICATE KEY UPDATE ---
            $jsonSettings = json_encode($newSettings);
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([NOTIFICATION_SETTINGS_KEY, $jsonSettings]);
            $message = "<div class='alert alert-success'><i class='fas fa-check'></i> Paramètres de notification sauvegardés.</div>";
        }

        // Chargement des options actuelles (ou par défaut OFF)
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([NOTIFICATION_SETTINGS_KEY]);
        $settings = json_decode($stmt->fetchColumn() ?: '{}', true);

        // Vue de configuration
        ob_start();
        ?>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="text-white"><i class="fas fa-bell text-warning me-2"></i>Configuration des Notifications</h2>
                        <a href="/admin/modules" class="btn btn-outline-light btn-sm">Retour Modules</a>
                    </div>
                    <?= $message ?>
                    
                    <div class="glass-card p-4">
                        <p class="text-muted">Choisissez les événements qui doivent déclencher l'envoi d'un email (via le module SMTP).</p>
                        <form method="POST">
                            <ul class="list-group list-group-flush bg-transparent">
                                <?php foreach ($notification_events as $key => $label): ?>
                                <li class="list-group-item bg-transparent text-white d-flex justify-content-between align-items-center border-secondary border-opacity-25 py-3">
                                    <label class="mb-0 fw-bold"><?= htmlspecialchars($label) ?></label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="<?= $key ?>" <?= ($settings[$key] ?? 'off') === 'on' ? 'checked' : '' ?>>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">Sauvegarder les préférences</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        $content = ob_get_clean();
        // Utilisation du layout Admin
        require __DIR__ . '/../../themes/admin_default/layout.php';
    };
    return $routes;
});


// ----------------------------------------------------------------------------------
// 3. ECOUTE DU CHANGEMENT DE STATUT (Déclenchement de l'envoi)
// ----------------------------------------------------------------------------------

// Écoute le Hook déclenché par AdminController::updateStatus()
Hook::listen('after_service_status_update', function($args) use ($email_template, $notification_events) {
    
    $newStatus = $args['new_status'] ?? '';
    $service = $args['service'] ?? [];
    $user = $args['user'] ?? [];
    
    // --- 1. Vérification des options Admin ---
    $db = Database::connect();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([NOTIFICATION_SETTINGS_KEY]);
    $settings = json_decode($stmt->fetchColumn() ?: '{}', true);

    // Si la notification pour ce statut est désactivée, on sort.
    if (($settings[$newStatus] ?? 'off') !== 'on') {
        return;
    }
    
    // --- Détermination du Titre, de la Couleur et de l'Intro ---
    $header_title = '';
    $header_color = '#1a73e8'; // Couleur par défaut (Bleu)
    $button_color = '#1a73e8';
    $body_intro = 'Nous vous confirmons que votre service est maintenant **activé** et opérationnel.';
    
    switch($newStatus) {
        case 'suspended':
            $header_title = 'Suspension de Service';
            $header_color = '#f59e0b'; // Orange (Warning)
            $button_color = '#f59e0b';
            $body_intro = 'Votre service est maintenant en état de **suspension**. Veuillez régulariser votre situation ou contacter le support.';
            break;
        case 'terminated':
            $header_title = 'Résiliation Confirmée';
            $header_color = '#ef4444'; // Rouge (Danger)
            $button_color = '#ef4444';
            $body_intro = 'Votre contrat a été **terminé** comme demandé. Vous ne serez plus facturé pour ce service.';
            break;
        case 'pending':
            // Notification Admin
            $header_title = 'Nouvelle Commande Reçue';
            $header_color = '#0ea5e9'; // Cyan/Info
            $button_color = '#0ea5e9';
            $body_intro = 'Une nouvelle commande est en attente de traitement.';
            break;
        case 'active':
        default:
            $header_title = 'Activation de Service Réussie !';
            $header_color = '#1a73e8'; // Bleu (Succès)
            $button_color = '#1a73e8';
            $body_intro = 'Nous vous confirmons que votre service est maintenant **activé** et opérationnel.';
            break;
    }


    // --- Traitement spécial pour les notifications Admin ---
    $recipientEmail = $user['email'];
    if ($newStatus === 'pending') {
        // Si c'est une nouvelle commande, on envoie à l'admin (configuré dans le module SMTP)
        $stmt_smtp = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt_smtp->execute(['smtp_config']); // Clé hypothétique pour la config SMTP, car ici on a le module
        $smtp_conf = json_decode($stmt_smtp->fetchColumn() ?: '{}', true);
        $recipientEmail = $smtp_conf['from'] ?? 'admin@cshc.local'; // Envoi à l'expéditeur défini dans SMTP
        $subject = "⭐ NOUVELLE COMMANDE: " . $service['name'] . " - " . $clientName;
    } else {
        $subject = "Votre Service CSHC - " . ($notification_events[$newStatus] ?? $newStatus);
    }

    // --- 2. Préparation des données d'email ---
    
    $customFields = json_decode($service['custom_fields'] ?? '{}', true);
    
    $ip = $service['ip_address'] ?? 'N/A';
    $details = $service['details'] ?? 'Veuillez consulter votre espace client pour les détails.';
    $serviceType = $service['type'];

    $fttx_type = $customFields['fttx_type'] ?? $service['type'] ?? 'Standard'; 
    $carrier = $customFields['fttx_carrier'] ?? 'CSHC Propre';
    
    $clientName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
    $clientName = $clientName ?: $user['email'];
    
    // Contenu personnalisé pour OTHER_INFO_DETAILS
    $otherInfoDetails = "Opérateur Collecte: " . htmlspecialchars($carrier) . "\nNotes: " . $details;
    
    // --- NOUVEAU BLOC DYNAMIQUE DE SPÉCIFICATIONS TECHNIQUES ---
    $dynamic_block = '';

    if ($serviceType === 'FTTx') {
        // Logique FTTx (avec PPPoE conditionnel)
        $pppoeUser = $customFields['fttx_pppoe_user'] ?? 'N/A';
        $pppoePass = '••••••••'; 
        $pppoeRequired = !empty($customFields['fttx_pppoe_required']);

        $pppoe_block = '';
        if ($pppoeRequired) {
            $pppoe_block = '
            <div class="credential-item">
                <strong>Utilisateur PPPoE:</strong> 
                <span class="data-monospace">'. htmlspecialchars($pppoeUser) .'</span>
            </div>
            <div class="credential-item">
                <strong>Mot de passe PPPoE:</strong> 
                <span class="data-monospace">'.$pppoePass.'</span>
            </div>
            ';
        }

        $dynamic_block = '
        <div class="credential-item">
            <strong>Adresse IP:</strong> 
            <span class="data-monospace">'. htmlspecialchars($ip) .'</span>
        </div>
        ' . $pppoe_block;

    } elseif ($serviceType === 'mobile_data') {
        // Logique Data Mobile
        $quota = $customFields['mobile_quota'] ?? 'N/A';
        $iccid = $customFields['mobile_iccid'] ?? 'N/A';

        $dynamic_block = '
        <div class="credential-item">
            <strong>Quota Data (Go):</strong> 
            <span class="data-monospace">'. htmlspecialchars($quota) .'</span>
        </div>
        <div class="credential-item">
            <strong>Numéro SIM (ICCID):</strong> 
            <span class="data-monospace">'. htmlspecialchars($iccid) .'</span>
        </div>
        ';
    }
    // Si autre (OpenVPN, VPLS), le bloc reste vide ou affiche juste l'IP si elle existe


    // --- 3. Remplacement des placeholders ---
    $email_body = str_replace(
        [
            '**HEADER_TITLE**',
            '**HEADER_COLOR**',
            '**BUTTON_COLOR**',
            '**BODY_INTRO**',
            '**CLIENT_NAME**', 
            '**USER_EMAIL**',
            '**FTTX_TYPE**',
            '**IP_ADDRESS**', 
            '**DYNAMIC_BLOCK**', // NOUVEAU HOOK PPPOE
            '**OTHER_INFO_DETAILS**', 
            '**DASHBOARD_LINK**'
        ],
        [
            htmlspecialchars($header_title),
            htmlspecialchars($header_color),
            htmlspecialchars($button_color),
            $body_intro,
            htmlspecialchars($clientName),
            htmlspecialchars($user['email']),
            htmlspecialchars($fttx_type),
            htmlspecialchars($ip),
            $dynamic_block, // Insertion du bloc conditionnel
            nl2br(htmlspecialchars($otherInfoDetails)),
            "https://" . ($_SERVER['HTTP_HOST'] ?? 'dev.cshybridconnect.com') . "/dashboard"
        ],
        $email_template
    );
    
    // --- 4. Envoi via le Hook SMTP ---
    Hook::call('send_email', [
        'to' => $recipientEmail,
        'subject' => $subject,
        'body' => $email_body
    ]);
});