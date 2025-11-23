<?php
/**
 * Module: Client Profile Extended
 * Description: Ajoute Nom, Prénom, Adresse, Type, SIRET et TVA aux utilisateurs.
 */

// 1. AUTO-MIGRATION ROBUSTE (Création des colonnes si elles manquent)
$db = Database::connect();

$columnsToAdd = [
    'lastname'    => "VARCHAR(100) NULL AFTER email",
    'firstname'   => "VARCHAR(100) NULL AFTER lastname",
    'address'     => "TEXT NULL AFTER firstname",
    'client_type' => "ENUM('particulier', 'entreprise') DEFAULT 'particulier' AFTER address",
    'siret'       => "VARCHAR(50) NULL AFTER client_type",
    'vat_number'  => "VARCHAR(50) NULL AFTER siret"
];

foreach ($columnsToAdd as $col => $sqlDef) {
    try {
        $db->query("SELECT $col FROM users LIMIT 1");
    } catch (Exception $e) {
        try {
            $db->exec("ALTER TABLE users ADD COLUMN $col $sqlDef");
        } catch (Exception $ex) { /* Silence is golden */ }
    }
}

// 2. ADMIN : CHAMPS FORMULAIRE (Identique à avant)
Hook::listen('admin_user_form_fields', function($args) {
    $u = $args['user'] ?? []; 
    
    $lastname = htmlspecialchars($u['lastname'] ?? '');
    $firstname = htmlspecialchars($u['firstname'] ?? '');
    $address = htmlspecialchars($u['address'] ?? '');
    $siret = htmlspecialchars($u['siret'] ?? '');
    $vat = htmlspecialchars($u['vat_number'] ?? '');
    
    $isCompany = ($u['client_type'] ?? '') === 'entreprise' ? 'selected' : '';
    $isIndividual = ($u['client_type'] ?? '') === 'particulier' ? 'selected' : '';

    return "
    <div class='mt-3 pt-3 border-top border-secondary'>
        <h6 class='text-info mb-3'><i class='fas fa-id-card me-2'></i>Informations Légales & Contact</h6>
        
        <div class='row g-2'>
            <div class='col-md-6'>
                <label class='small text-secondary'>Nom</label>
                <input type='text' name='lastname' class='form-control' value='$lastname' placeholder='Doe'>
            </div>
            <div class='col-md-6'>
                <label class='small text-secondary'>Prénom</label>
                <input type='text' name='firstname' class='form-control' value='$firstname' placeholder='John'>
            </div>
            
            <div class='col-12'>
                <label class='small text-secondary'>Adresse Complète</label>
                <textarea name='address' class='form-control' rows='2' placeholder='123 Rue de la Tech...'>$address</textarea>
            </div>

            <div class='col-md-12'>
                <label class='small text-secondary'>Type de Client</label>
                <select name='client_type' class='form-select' id='clientTypeSelector' onchange='toggleCompanyFields()'>
                    <option value='particulier' $isIndividual>Particulier</option>
                    <option value='entreprise' $isCompany>Entreprise (Pro)</option>
                </select>
            </div>

            <div class='col-md-6 company-field' style='display:none;'>
                <label class='small text-secondary'>SIRET</label>
                <input type='text' name='siret' class='form-control font-monospace' value='$siret' placeholder='14 chiffres'>
            </div>
            <div class='col-md-6 company-field' style='display:none;'>
                <label class='small text-secondary'>Numéro TVA</label>
                <input type='text' name='vat_number' class='form-control font-monospace' value='$vat' placeholder='FR...'>
            </div>
        </div>
    </div>

    <script>
    function toggleCompanyFields() {
        let select = document.getElementById('clientTypeSelector');
        if(!select) return;
        let type = select.value;
        let fields = document.querySelectorAll('.company-field');
        fields.forEach(f => f.style.display = (type === 'entreprise' ? 'block' : 'none'));
    }
    setTimeout(toggleCompanyFields, 100);
    </script>
    ";
});

// 3. ADMIN : SAUVEGARDE DES DONNÉES (Identique)
Hook::listen('after_user_save', function($args) {
    $userId = $args['user_id'] ?? null;
    $post = $args['post'] ?? [];

    if (!$userId || !isset($post['client_type'])) {
        return;
    }

    $db = Database::connect();
    
    $lastname = !empty($post['lastname']) ? $post['lastname'] : null;
    $firstname = !empty($post['firstname']) ? $post['firstname'] : null;
    $address = !empty($post['address']) ? $post['address'] : null;
    $clientType = $post['client_type'];
    
    $siret = ($clientType === 'entreprise' && !empty($post['siret'])) ? $post['siret'] : null;
    $vat = ($clientType === 'entreprise' && !empty($post['vat_number'])) ? $post['vat_number'] : null;

    try {
        $sql = "UPDATE users SET 
                lastname = ?, firstname = ?, address = ?, 
                client_type = ?, siret = ?, vat_number = ? 
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$lastname, $firstname, $address, $clientType, $siret, $vat, $userId]);
        
    } catch (Exception $e) {
        // Log ou gestion d'erreur
    }
});

// 4. CLIENT : AFFICHAGE DASHBOARD (Widget Profil SIMPLE)
Hook::listen('client_dashboard_widgets', function() {
    if (!isset($_SESSION['user']['id'])) return;
    
    $db = Database::connect();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($u['lastname']) && empty($u['firstname'])) return;

    $nomComplet = strtoupper($u['lastname'] ?? '') . " " . ($u['firstname'] ?? '');
    $isCompany = ($u['client_type'] ?? '') === 'entreprise';
    $icon = $isCompany ? 'fa-building' : 'fa-user-circle';

    $typeBadge = $isCompany
        ? '<span class="badge bg-warning text-dark ms-2"><i class="fas fa-building me-1"></i> PRO</span>' 
        : '<span class="badge bg-secondary ms-2"><i class="fas fa-user me-1"></i> PERSO</span>';

    $adresse = !empty($u['address']) ? nl2br(htmlspecialchars($u['address'])) : '<em class="text-muted">Adresse non renseignée</em>';

    // AFFICHAGE CARTE PROFIL SIMPLE
    return "
    <div class='glass-card p-4 mb-4 border-top border-4 border-info'>
        <div class='row align-items-center'>
            
            <!-- Colonne 1: Infos Texte -->
            <div class='col-md-10'>
                <div class='d-flex align-items-center mb-2'>
                    <h4 class='text-white mb-0'>$nomComplet</h4>
                    $typeBadge
                </div>
                
                <p class='text-secondary mb-1 small'><i class='fas fa-envelope me-2 text-primary'></i> {$u['email']}</p>
                
                <p class='text-secondary small mt-2 mb-0'>
                    <i class='fas fa-map-marker-alt me-2 text-info'></i> $adresse
                </p>

                " . ($isCompany ? "
                <div class='mt-2 pt-2 border-top border-secondary border-opacity-25'>
                    <span class='me-3 text-secondary small'>SIRET: <strong class='text-white font-monospace'>{$u['siret']}</strong></span>
                    <span class='text-secondary small'>TVA: <strong class='text-white font-monospace'>{$u['vat_number']}</strong></span>
                </div>
                " : "") . "
            </div>

            <!-- Colonne 2: Avatar (Simple, discret) -->
            <div class='col-md-2 text-end'>
                <div style='width: 60px; height: 60px; margin-left: auto;' class='rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center'>
                     <i class='fas $icon fa-2x text-info opacity-75'></i>
                </div>
                
                <a href='/admin/edit-user?id={$u['id']}' class='text-primary small mt-2 d-block text-decoration-none'></a>
            </div>
        </div>
    </div>
    ";
});