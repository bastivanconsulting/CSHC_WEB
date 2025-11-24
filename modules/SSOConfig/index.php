<?php
/**
 * Module: SSO Configuration (SAML/OpenID Connect)
 * Description: Ajoute une page pour configurer l'authentification unique (SSO) par fournisseur, 
 * incluant des modèles (Google, Azure, WHMCS) pour faciliter la configuration.
 */

define('SSO_SETTINGS_KEY', 'sso_config');
define('SSO_MODULE_NAME', 'SSOConfig');

// Définition des modèles préconfigurés
$sso_templates = [
    'manual' => ['name' => 'Manuel / Personnalisé (OIDC)', 'type' => 'OIDC'],
    // 'saml_manual' => ['name' => 'SAML (Métadonnées URL)', 'type' => 'SAML'], // TODO: Implémenter SAML
    'google' => ['name' => 'Google Workspace / Cloud', 'type' => 'OIDC', 'client_id' => 'VOTRE_ID.apps.googleusercontent.com', 'metadata_url' => 'https://accounts.google.com/.well-known/openid-configuration'],
    'azure' => ['name' => 'Microsoft Azure AD / Office 365', 'type' => 'OIDC', 'metadata_url' => 'https://login.microsoftonline.com/VOTRE_TENANT_ID/v2.0/.well-known/openid-configuration'],
    'okta' => ['name' => 'Okta', 'type' => 'OIDC', 'metadata_url' => 'https://VOTRE_DOMAINE.okta.com/.well-known/openid-configuration'],
    'cloudflare' => ['name' => 'Cloudflare Zero Trust', 'type' => 'OIDC', 'metadata_url' => 'https://VOTRE_TEAM.cloudflareaccess.com/.well-known/openid-configuration'],
    'keycloak' => ['name' => 'KeyCloak', 'type' => 'OIDC', 'metadata_url' => 'https://sso.votredomaine.com/realms/VOTRE_REALM/.well-known/openid-configuration'],
];

// 1. INITIALISATION BDD
$db = Database::connect();
try {
    // Exécuter la création avec IF NOT EXISTS pour la robustesse
    $db->exec("CREATE TABLE IF NOT EXISTS sso_providers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        domain VARCHAR(100) NULL,             /* Domaine du client (ex: @acme.com) */
        sso_type ENUM('SAML', 'OIDC') NOT NULL,
        metadata_url TEXT NULL,
        client_id VARCHAR(255) NULL,
        client_secret VARCHAR(255) NULL,
        auto_create_user BOOLEAN DEFAULT FALSE,
        default_role ENUM('client', 'admin') DEFAULT 'client',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Vérification des colonnes pour migration (approche plus légère que try/catch aveugle)
    $columns = $db->query("SHOW COLUMNS FROM sso_providers")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('auto_create_user', $columns)) {
        $db->exec("ALTER TABLE sso_providers ADD COLUMN auto_create_user BOOLEAN DEFAULT FALSE");
    }
    if (!in_array('default_role', $columns)) {
        $db->exec("ALTER TABLE sso_providers ADD COLUMN default_role ENUM('client', 'admin') DEFAULT 'client'");
    }

} catch (Exception $e) { /* Ne rien faire si erreur */ }


// 2. SIGNALER QUE CE MODULE A UNE PAGE DE CONFIGURATION
Hook::listen('get_module_config_url', function($moduleName) {
    if ($moduleName === SSO_MODULE_NAME) {
        return '/admin/sso-config';
    }
});

// 3. HOOK POUR AFFICHER LES BOUTONS SUR LA PAGE DE LOGIN
Hook::listen('get_sso_login_options', function($providers_list = []) {
    $ssoState = getSsoState();
    if ($ssoState['is_active']) {
        foreach($ssoState['providers'] as $p) {
            $providers_list[] = $p;
        }
    }
    return $providers_list;
});


/**
 * Fonction utilitaire pour charger l'état d'activation et les fournisseurs SSO.
 * @return array
 */
function getSsoState() {
    $db = Database::connect();
    
    // Charger l'état d'activation global (stocké dans settings)
    $stmt_active = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt_active->execute([SSO_SETTINGS_KEY]);
    $isActive = (json_decode($stmt_active->fetchColumn() ?: '{}', true)['is_active'] ?? 'off') === 'on';

    try {
        // Charger la liste des fournisseurs
        $providers = $db->query("SELECT * FROM sso_providers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [
            'is_active' => $isActive,
            'providers' => []
        ];
    }
    
    return [
        'is_active' => $isActive,
        'providers' => $providers
    ];
}

// Helper pour les requêtes HTTP (OIDC)
function sso_http_request($url, $method = 'GET', $data = [], $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => $response];
}


// 4. ROUTAGE ET LOGIQUE SSO
Hook::listen('register_routes', function($routes) use ($sso_templates) {
    
    // Route ADMIN - Configuration
    $routes['admin/sso-config'] = function() use ($sso_templates) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /login'); exit;
        }

        $db = Database::connect();
        $message = "";

        // Traitement du formulaire de configuration
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // --- Traitement de l'activation/désactivation globale ---
            if (isset($_POST['set_active'])) {
                $isActive = isset($_POST['is_active']) ? 'on' : 'off';
                $jsonSettings = json_encode(['is_active' => $isActive]);
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                      ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([SSO_SETTINGS_KEY, $jsonSettings]);
                $message = "<div class='alert alert-success'><i class='fas fa-check'></i> Statut SSO mis à jour.</div>";
            }
            
            // --- Traitement de l'ajout/édition de fournisseur ---
            if (isset($_POST['save_provider'])) {
                try {
                    $name = $_POST['name'];
                    $sso_type = $_POST['sso_type'];
                    $domain = $_POST['domain'];
                    $metadata = $_POST['metadata_url'];
                    $client_id = $_POST['client_id'];
                    $client_secret = $_POST['client_secret'];
                    
                    // Nouveaux champs
                    $auto_create = isset($_POST['auto_create_user']) ? 1 : 0;
                    $default_role = $_POST['default_role'] ?? 'client';

                    // Validation simple
                    if (empty($name) || empty($sso_type)) { throw new Exception("Nom et Type sont obligatoires."); }
                    
                    $sql = "INSERT INTO sso_providers (name, domain, sso_type, metadata_url, client_id, client_secret, auto_create_user, default_role)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE domain=VALUES(domain), sso_type=VALUES(sso_type), metadata_url=VALUES(metadata_url), client_id=VALUES(client_id), client_secret=VALUES(client_secret), auto_create_user=VALUES(auto_create_user), default_role=VALUES(default_role)";
                    
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$name, $domain, $sso_type, $metadata, $client_id, $client_secret, $auto_create, $default_role]);
                    $message = "<div class='alert alert-success'><i class='fas fa-check'></i> Fournisseur <strong>$name</strong> sauvegardé.</div>";
    
                } catch (Exception $e) {
                    $message = "<div class='alert alert-danger'><i class='fas fa-times'></i> Erreur : " . $e->getMessage() . "</div>";
                }
            }

            // --- Traitement de la suppression ---
            if (isset($_POST['delete_provider']) && is_numeric($_POST['provider_id'])) {
                try {
                    $stmt = $db->prepare("DELETE FROM sso_providers WHERE id = ?");
                    $stmt->execute([$_POST['provider_id']]);
                    $message = "<div class='alert alert-success'><i class='fas fa-check'></i> Fournisseur SSO supprimé.</div>";
                } catch (Exception $e) {
                    $message = "<div class='alert alert-danger'><i class='fas fa-times'></i> Erreur SQL : " . $e->getMessage() . "</div>";
                }
            }
        }
        
        // Chargement de l'état actuel et des fournisseurs
        $ssoState = getSsoState();
        $isActive = $ssoState['is_active'];
        $providers = $ssoState['providers'];

        // Vue
        ob_start();
        ?>
        <div class="container mt-5">
            <h2 class="text-white mb-4"><i class="fas fa-fingerprint text-info me-2"></i>Configuration Authentification Unique (SSO)</h2>
            <a href="/admin/modules" class="btn btn-outline-light btn-sm mb-4">Retour Modules</a>
            
            <?= $message ?>
            
            <!-- Bloc d'Activation Globale -->
            <div class="glass-card p-4 mb-4">
                <h5 class="text-warning mb-3"><i class="fas fa-power-off me-2"></i> Activation Globale</h5>
                <p class="text-muted small">Activez/Désactivez le bouton SSO sur la page de connexion.</p>
                <form method="POST">
                    <input type="hidden" name="set_active" value="1">
                    <div class="form-check form-switch d-flex justify-content-between align-items-center">
                        <label class="form-check-label fw-bold text-white" for="ssoActiveSwitch">Activer l'option SSO</label>
                        <input class="form-check-input" type="checkbox" role="switch" id="ssoActiveSwitch" name="is_active" onchange="this.form.submit()" <?= $isActive ? 'checked' : '' ?>>
                    </div>
                </form>
            </div>


            <div class="row g-4">
                
                <!-- Formulaire d'ajout/édition de Fournisseur -->
                <div class="col-lg-6">
                    <div class="glass-card p-4">
                        <h5 class="text-warning mb-3">Ajouter/Modifier un Fournisseur</h5>
                        <form method="POST">
                            <input type="hidden" name="save_provider" value="1">
                            
                            <!-- Modèle de Configuration -->
                            <div class="mb-3">
                                <label class="small text-secondary">Sélectionner un Modèle</label>
                                <select class="form-select bg-dark text-white" id="ssoTemplate" onchange="applyTemplate()">
                                    <?php foreach($sso_templates as $key => $tpl): ?>
                                        <option value='<?= $key ?>' data-type='<?= $tpl['type'] ?>' data-metadata='<?= $tpl['metadata_url'] ?? '' ?>' data-id='<?= $tpl['client_id'] ?? '' ?>'>
                                            <?= $tpl['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="small text-secondary">Nom du Fournisseur (Unique)</label>
                                <input type="text" name="name" id="providerName" class="form-control" required placeholder="Ex: Client_ACME_SAML">
                            </div>

                            <div class="mb-3">
                                <label class="small text-secondary">Domaine Associé (ex: @acme.com) <span class="text-danger small">*Optionnel</span></label>
                                <input type="text" name="domain" class="form-control" placeholder="@votredomaine.com">
                            </div>

                            <hr class="border-secondary opacity-25 my-4">

                            <!-- Mode Provisioning (Mode "i") -->
                            <div class="mb-3">
                                <h6 class="small text-info">Provisioning Utilisateur (Mode "i")</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="autoCreateUser" name="auto_create_user" value="1">
                                    <label class="form-check-label text-white small" for="autoCreateUser">Créer automatiquement l'utilisateur s'il n'existe pas</label>
                                </div>

                                <label class="small text-secondary">Rôle par défaut</label>
                                <select name="default_role" class="form-select bg-dark text-white">
                                    <option value="client">Client</option>
                                    <option value="admin">Administrateur</option>
                                </select>
                            </div>

                            <hr class="border-secondary opacity-25 my-4">
                            
                            <!-- Type (Caché ou Affiché) -->
                            <input type="hidden" name="sso_type" id="ssoTypeField">

                            <!-- SAML FIELDS (Désactivé pour le moment) -->
                            <div id="samlFields" style="display:none;">
                                <div class="alert alert-warning small">
                                    <i class="fas fa-exclamation-triangle"></i> Le support SAML n'est pas encore disponible. Veuillez utiliser OIDC.
                                </div>
                            </div>
                            
                            <!-- OIDC FIELDS -->
                            <div id="oidcFields" style="display:none;">
                                <h6 class="small text-info mb-3">Configuration OIDC / OAuth2</h6>
                                <div class="mb-3">
                                    <label class="small text-secondary">URL de découverte (.well-known)</label>
                                    <input type="url" name="metadata_url" id="oidcMetadataUrl" class="form-control" placeholder="https://.../.well-known/openid-configuration">
                                </div>
                                <div class="mb-3">
                                    <label class="small text-secondary">Client ID</label>
                                    <input type="text" name="client_id" id="clientId" class="form-control" placeholder="Clé d'application OIDC">
                                </div>
                                <div class="mb-3">
                                    <label class="small text-secondary">Client Secret</label>
                                    <input type="text" name="client_secret" class="form-control" placeholder="Secret de l'application OIDC">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mt-3">Sauvegarder la Configuration</button>
                        </form>
                    </div>
                </div>

                <!-- Liste des Configurations -->
                <div class="col-lg-6">
                    <div class="glass-card p-0">
                        <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                            <h5 class="text-white mb-0">Fournisseurs configurés</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-dark-transparent align-middle mb-0">
                                <thead><tr><th>Nom</th><th>Détails</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php if(empty($providers)): ?>
                                        <tr><td colspan="3" class="text-center text-muted py-4">Aucune configuration SSO.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach($providers as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-white"><?= htmlspecialchars($p['name']) ?></div>
                                            <span class="badge bg-<?= $p['sso_type'] === 'SAML' ? 'info' : 'primary' ?>"><?= $p['sso_type'] ?></span>
                                        </td>
                                        <td>
                                            <div class="small text-muted">Auto-création: <?= $p['auto_create_user'] ? '<span class="text-success">Oui</span>' : '<span class="text-secondary">Non</span>' ?></div>
                                            <div class="small text-muted">Rôle: <?= htmlspecialchars($p['default_role']) ?></div>
                                        </td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ?')">
                                                <input type="hidden" name="delete_provider" value="1">
                                                <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const templateSelector = document.getElementById('ssoTemplate');
            const providerName = document.getElementById('providerName');
            const ssoTypeField = document.getElementById('ssoTypeField');
            
            const samlFields = document.getElementById('samlFields');
            const oidcFields = document.getElementById('oidcFields');
            const metadataUrl = document.getElementById('metadataUrl');
            const oidcMetadataUrl = document.getElementById('oidcMetadataUrl');
            const clientId = document.getElementById('clientId');
            
            window.applyTemplate = function() {
                const selected = templateSelector.options[templateSelector.selectedIndex];
                const type = selected.getAttribute('data-type');
                const metadata = selected.getAttribute('data-metadata');
                const id = selected.getAttribute('data-id');
                const name = selected.text.split(' (')[0];

                if(name !== 'Manuel / Personnalisé') {
                    // providerName.value = name; // Ne pas écraser si l'utilisateur a déjà tapé
                }
                ssoTypeField.value = type;
                
                if (type === 'OIDC') {
                     oidcMetadataUrl.value = metadata;
                } else {
                     metadataUrl.value = metadata;
                }
                clientId.value = id;

                // Afficher/Masquer les blocs
                if (type === 'SAML') {
                    samlFields.style.display = 'block';
                    oidcFields.style.display = 'none';
                    metadataUrl.setAttribute('required', 'required');
                    oidcMetadataUrl.removeAttribute('required');
                    clientId.removeAttribute('required');
                } else if (type === 'OIDC') {
                    samlFields.style.display = 'none';
                    oidcFields.style.display = 'block';
                    metadataUrl.removeAttribute('required');
                    // oidcMetadataUrl.setAttribute('required', 'required'); // Pas forcément requis si URLs manuelles mais on recommande
                    clientId.setAttribute('required', 'required');
                }
            };

            applyTemplate();
        });
        </script>
        <?php 
        $content = ob_get_clean();
        require __DIR__ . '/../../themes/admin_default/layout.php';
    };

    // --- LOGIQUE SSO PUBLIC ---

    // 1. INIT LOGIN (Redirection vers IdP)
    $routes['auth/sso/login/(\d+)'] = function($providerId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM sso_providers WHERE id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider) { die("Fournisseur SSO introuvable."); }

        if ($provider['sso_type'] === 'OIDC') {
            // Découverte OIDC (fetch metadata)
            $metadataUrl = $provider['metadata_url'];
            if (!$metadataUrl) { die("URL de métadonnées manquante pour ce fournisseur OIDC."); }

            // Cache simple des métadonnées (optionnel mais recommandé pour perf, ici direct)
            $metaRes = sso_http_request($metadataUrl);
            if ($metaRes['code'] !== 200) { die("Impossible de récupérer les métadonnées OIDC: " . $metaRes['body']); }

            $metadata = json_decode($metaRes['body'], true);
            $authEndpoint = $metadata['authorization_endpoint'] ?? null;

            if (!$authEndpoint) { die("Endpoint d'autorisation introuvable dans les métadonnées."); }

            // Génération State / Nonce
            $state = bin2hex(random_bytes(16));
            $nonce = bin2hex(random_bytes(16));

            $_SESSION['sso_state'] = $state;
            $_SESSION['sso_provider_id'] = $providerId;
            $_SESSION['sso_nonce'] = $nonce;

            // Construction URL Auth
            $redirectUri = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/auth/sso/callback';

            $params = [
                'client_id' => $provider['client_id'],
                'response_type' => 'code',
                'scope' => 'openid profile email',
                'redirect_uri' => $redirectUri,
                'state' => $state,
                'nonce' => $nonce
            ];

            $loginUrl = $authEndpoint . '?' . http_build_query($params);
            header("Location: $loginUrl");
            exit;

        } elseif ($provider['sso_type'] === 'SAML') {
            die("Le support SAML n'est pas encore implémenté complètement. Veuillez utiliser OIDC pour le moment.");
        }
    };

    // 2. CALLBACK (Retour de l'IdP)
    $routes['auth/sso/callback'] = function() {
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            die("Requête invalide (code ou state manquant).");
        }

        $code = $_GET['code'];
        $state = $_GET['state'];

        if (!isset($_SESSION['sso_state']) || $_SESSION['sso_state'] !== $state) {
            die("Erreur de sécurité : State mismatch (CSRF detected).");
        }

        $providerId = $_SESSION['sso_provider_id'];
        unset($_SESSION['sso_state']); // Clean up

        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM sso_providers WHERE id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider || $provider['sso_type'] !== 'OIDC') { die("Provider invalide."); }

        // Récupérer endpoint token via metadata
        $metaRes = sso_http_request($provider['metadata_url']);
        $metadata = json_decode($metaRes['body'], true);

        $tokenEndpoint = $metadata['token_endpoint'];
        $userInfoEndpoint = $metadata['userinfo_endpoint'];
        $redirectUri = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/auth/sso/callback';

        // Échange Code -> Token
        $postData = [
            'grant_type' => 'authorization_code',
            'client_id' => $provider['client_id'],
            'client_secret' => $provider['client_secret'],
            'code' => $code,
            'redirect_uri' => $redirectUri
        ];

        $tokenRes = sso_http_request($tokenEndpoint, 'POST', $postData);
        $tokenData = json_decode($tokenRes['body'], true);

        if (!isset($tokenData['access_token'])) {
            die("Erreur lors de l'échange de token: " . $tokenRes['body']);
        }

        $accessToken = $tokenData['access_token'];

        // Récupérer Infos User
        $userRes = sso_http_request($userInfoEndpoint, 'GET', [], ["Authorization: Bearer $accessToken"]);
        $userInfo = json_decode($userRes['body'], true);

        if (!isset($userInfo['email'])) {
            die("Impossible de récupérer l'email de l'utilisateur.");
        }

        $email = $userInfo['email'];
        // $firstName = $userInfo['given_name'] ?? '';
        // $lastName = $userInfo['family_name'] ?? '';

        // --- LOGIQUE DE CONNEXION / CRÉATION ---

        $stmtUser = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmtUser->execute([$email]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Utilisateur existant -> Login
             if ($user['status'] === 'suspended') {
                 die("Compte suspendu.");
             }
             $_SESSION['user'] = $user;
             // Update last login ip?
             Hook::call('user_login_success', ['user' => $user]);

             if($user['role'] === 'admin') {
                header('Location: /admin');
             } else {
                header('Location: /dashboard');
             }
             exit;

        } else {
            // Utilisateur inexistant
            if ($provider['auto_create_user']) {
                // "Mode i" : Création automatique
                $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT); // Random password
                $role = $provider['default_role'];

                try {
                    // Insérer l'utilisateur
                    $insert = $db->prepare("INSERT INTO users (email, password, role, status, created_at) VALUES (?, ?, ?, 'active', NOW())");
                    $insert->execute([$email, $password, $role]);
                    $newUserId = $db->lastInsertId();

                    // Recharger l'utilisateur
                    $stmtUser->execute([$email]);
                    $newUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

                    $_SESSION['user'] = $newUser;
                    $_SESSION['flash_success'] = "Bienvenue ! Votre compte a été créé automatiquement via SSO.";

                    Hook::call('user_login_success', ['user' => $newUser]);

                    if($role === 'admin') {
                        header('Location: /admin');
                    } else {
                        header('Location: /dashboard');
                    }
                    exit;

                } catch (Exception $e) {
                    die("Erreur lors de la création du compte : " . $e->getMessage());
                }

            } else {
                $_SESSION['flash_error'] = "Aucun compte associé à cet email ($email). Veuillez contacter l'administrateur.";
                header('Location: /login');
                exit;
            }
        }

    };

    return $routes;
});
