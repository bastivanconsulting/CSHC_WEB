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
    'manual' => ['name' => 'Manuel / Personnalisé', 'type' => 'OIDC'],
    'saml_manual' => ['name' => 'SAML (Métadonnées URL)', 'type' => 'SAML'],
    'google' => ['name' => 'Google Workspace / Cloud', 'type' => 'OIDC', 'client_id' => 'VOTRE_ID.apps.googleusercontent.com'],
    'azure' => ['name' => 'Microsoft Azure AD / Office 365', 'type' => 'OIDC', 'metadata_url' => 'https://login.microsoftonline.com/VOTRE_TENANT_ID/v2.0/.well-known/openid-configuration'],
    'whmcs' => ['name' => 'WHMCS (OIDC/API)', 'type' => 'OIDC'],
];

// 1. INITIALISATION BDD (Création de la table de configuration)
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) { /* Ne rien faire si la table existe */ }


// 2. SIGNALER QUE CE MODULE A UNE PAGE DE CONFIGURATION
Hook::listen('get_module_config_url', function($moduleName) {
    if ($moduleName === SSO_MODULE_NAME) {
        return '/admin/sso-config';
    }
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

    // CORRECTION CRITIQUE: Mise en try/catch pour la lecture des fournisseurs
    try {
        // Charger la liste des fournisseurs
        $providers = $db->query("SELECT id, name, domain, sso_type, metadata_url, client_id FROM sso_providers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Si la colonne n'est pas trouvée (problème de synchronisation de schéma), on ignore le crash
        if (strpos($e->getMessage(), 'Unknown column') !== false) {
             return [
                'is_active' => $isActive,
                'providers' => []
            ];
        }
        // Si c'est une autre erreur, on la relance
        throw $e;
    }
    
    return [
        'is_active' => $isActive,
        'providers' => $providers
    ];
}


// 3. CRÉER LA ROUTE DE CONFIGURATION
Hook::listen('register_routes', function($routes) use ($sso_templates) {
    
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
                    
                    // Validation simple
                    if (empty($name) || empty($sso_type)) { throw new Exception("Nom et Type sont obligatoires."); }
                    
                    $sql = "INSERT INTO sso_providers (name, domain, sso_type, metadata_url, client_id, client_secret) 
                            VALUES (?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE domain=VALUES(domain), sso_type=VALUES(sso_type), metadata_url=VALUES(metadata_url), client_id=VALUES(client_id), client_secret=VALUES(client_secret)";
                    
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$name, $domain, $sso_type, $metadata, $client_id, $client_secret]);
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
                        <h5 class="text-warning mb-3">Ajouter un Fournisseur IdP (SAML ou OIDC)</h5>
                        <form method="POST">
                            <input type="hidden" name="save_provider" value="1">
                            
                            <!-- Modèle de Configuration -->
                            <div class="mb-3">
                                <label class="small text-secondary">Sélectionner un Modèle de base</label>
                                <select class="form-select bg-dark text-white" id="ssoTemplate" onchange="applyTemplate()">
                                    <?php foreach($sso_templates as $key => $tpl): ?>
                                        <option value='<?= $key ?>' data-type='<?= $tpl['type'] ?>' data-metadata='<?= $tpl['metadata_url'] ?? '' ?>' data-id='<?= $tpl['client_id'] ?? '' ?>'>
                                            <?= $tpl['name'] ?> (<?= $tpl['type'] ?>)
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
                            
                            <!-- Type (Caché ou Affiché) -->
                            <input type="hidden" name="sso_type" id="ssoTypeField">

                            <!-- SAML FIELDS -->
                            <div id="samlFields" style="display:none;">
                                <h6 class="small text-info mb-3">Configuration SAML (Métadonnées)</h6>
                                <div class="mb-3">
                                    <label class="small text-secondary">URL des Métadonnées IDP</label>
                                    <input type="url" name="metadata_url" id="metadataUrl" class="form-control" placeholder="https://idp.client.com/saml/metadata.xml">
                                </div>
                            </div>
                            
                            <!-- OIDC FIELDS -->
                            <div id="oidcFields" style="display:none;">
                                <h6 class="small text-info mb-3">Configuration OIDC / OAuth2</h6>
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
                                <thead><tr><th>Nom & Domaine</th><th>Type</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php if(empty($providers)): ?>
                                        <tr><td colspan="3" class="text-center text-muted py-4">Aucune configuration SSO.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach($providers as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-white"><?= htmlspecialchars($p['name']) ?></div>
                                            <?php if($p['domain']): ?><small class="text-muted font-monospace"><?= htmlspecialchars($p['domain']) ?></small><?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $p['sso_type'] === 'SAML' ? 'info' : 'primary' ?>"><?= $p['sso_type'] ?></span>
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
            // Script d'initialisation pour afficher/masquer les champs OIDC/SAML
            
            const templateSelector = document.getElementById('ssoTemplate');
            const providerName = document.getElementById('providerName');
            const ssoTypeField = document.getElementById('ssoTypeField');
            
            const samlFields = document.getElementById('samlFields');
            const oidcFields = document.getElementById('oidcFields');
            const metadataUrl = document.getElementById('metadataUrl');
            const clientId = document.getElementById('clientId');
            
            window.applyTemplate = function() {
                const selected = templateSelector.options[templateSelector.selectedIndex];
                const type = selected.getAttribute('data-type');
                const metadata = selected.getAttribute('data-metadata');
                const id = selected.getAttribute('data-id');
                const name = selected.text.split(' (')[0];

                // Mettre à jour les champs
                providerName.value = name;
                ssoTypeField.value = type;
                
                metadataUrl.value = metadata;
                clientId.value = id;

                // Afficher/Masquer les blocs
                if (type === 'SAML') {
                    samlFields.style.display = 'block';
                    oidcFields.style.display = 'none';
                    metadataUrl.setAttribute('required', 'required');
                    clientId.removeAttribute('required');
                } else if (type === 'OIDC') {
                    samlFields.style.display = 'none';
                    oidcFields.style.display = 'block';
                    metadataUrl.removeAttribute('required');
                    clientId.setAttribute('required', 'required');
                } else {
                    samlFields.style.display = 'none';
                    oidcFields.style.display = 'none';
                    metadataUrl.removeAttribute('required');
                    clientId.removeAttribute('required');
                }
            };

            // Appliquer le modèle par défaut (Manuel / Personnalisé)
            applyTemplate();
        });
        </script>
        <?php 
        $content = ob_get_clean();
        require __DIR__ . '/../../themes/admin_default/layout.php';
    };
    return $routes;
});