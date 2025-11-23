<?php
/**
 * Module: FTTx Details
 * Description: Ajoute des champs spécifiques aux lignes Fibre (FTTH/FTTO, Opérateur, PPPoE).
 */

// Clé unique pour ce module
define('FTTX_TYPE_KEY', 'FTTx');

// 1. AFFICHER LES CHAMPS SPÉCIFIQUES (Admin > Formulaire Création)
Hook::listen('admin_service_form_fields', function() {
    // Initialisation des champs
    $fttx_type_ftth = '';
    $fttx_type_ftto = '';

    // HTML des champs spécifiques
    return "
    <div class='module-section' data-type='" . FTTX_TYPE_KEY . "' style='display:none;'>
        <div class='card bg-dark border-info mb-3'>
            <div class='card-header bg-transparent border-info text-info fw-bold small'>
                <i class='fas fa-fiber-optic fa-2x me-2'></i>CONFIGURATION FIBRE
            </div>
            <div class='card-body'>
                <div class='row g-3'>
                    <div class='col-md-6'>
                        <label class='small text-secondary'>Type de Fibre</label>
                        <select name='fttx_type' class='form-select'>
                            <option value='FTTH'>FTTH (Fiber to the Home)</option>
                            <option value='FTTO'>FTTO (Fiber to the Office / DÉDIÉ)</option>
                            <option value='FTTA'>FTTA (Agrégation)</option>
                        </select>
                    </div>
                    <div class='col-md-6'>
                        <label class='small text-secondary'>Opérateur de Collecte</label>
                        <input type='text' name='fttx_carrier' class='form-control' placeholder='Orange, SFR, Kosc, etc.'>
                    </div>
                    <div class='col-md-6'>
                        <label class='small text-secondary'>Identifiant PPPoE</label>
                        <input type='text' name='fttx_pppoe_user' class='form-control' placeholder='Laisser vide si IP Statique'>
                    </div>
                    <div class='col-md-6'>
                        <label class='small text-secondary'>Mot de passe PPPoE</label>
                        <input type='password' name='fttx_pppoe_pass' class='form-control'>
                    </div>
                    <div class='col-12'>
                        <div class='form-check form-switch'>
                            <input class='form-check-input' type='checkbox' name='fttx_pppoe_required' id='pppoeSwitch' value='1'>
                            <label class='form-check-label text-white' for='pppoeSwitch'>Authentification PPPoE requise par le client</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Script pour afficher/masquer les champs selon le type choisi
        document.getElementById('serviceTypeSelector').addEventListener('change', function(e) {
            // Masquer tous les modules
            document.querySelectorAll('.module-section').forEach(el => el.style.display = 'none');
            
            // Afficher seulement le module FTTx si le type est 'FTTx'
            let type = e.target.value;
            if(type === 'FTTx') {
                let section = document.querySelector('.module-section[data-type=\"' + FTTX_TYPE_KEY + '\"]');
                if(section) section.style.display = 'block';
            }
        });
        
        // Exécution immédiate pour la création (le type FTTx est déjà listé dans le select de base)
        setTimeout(() => {
            let selector = document.getElementById('serviceTypeSelector');
            if (selector.value === 'FTTx') {
                let section = document.querySelector('.module-section[data-type=\"' + FTTX_TYPE_KEY + '\"]');
                if(section) section.style.display = 'block';
            }
        }, 50);
    </script>
    ";
});

// 2. AFFICHER LES CHAMPS EN ÉDITION (Admin > Formulaire Edition)
Hook::listen('admin_service_edit_fields', function($args) {
    if ($args['service']['type'] !== FTTX_TYPE_KEY) return;

    // Récupération des données JSON stockées
    $data = json_decode($args['service']['custom_fields'], true) ?? [];
    
    $fttx_type = $data['fttx_type'] ?? 'FTTH';
    $carrier = htmlspecialchars($data['fttx_carrier'] ?? '');
    $pppoe_user = htmlspecialchars($data['fttx_pppoe_user'] ?? '');
    $pppoe_pass = htmlspecialchars($data['fttx_pppoe_pass'] ?? '');
    $pppoe_required = !empty($data['fttx_pppoe_required']) ? 'checked' : '';

    return "
    <div class='card bg-dark border-info mb-3'>
        <div class='card-header bg-transparent border-info text-info fw-bold small'>
            <i class='fas fa-sim-card me-2'></i>CONFIGURATION FIBRE
        </div>
        <div class='card-body'>
            <div class='row g-3'>
                <div class='col-md-6'>
                    <label class='small text-secondary'>Type de Fibre</label>
                    <select name='fttx_type' class='form-select'>
                        <option value='FTTH' " . ($fttx_type == 'FTTH' ? 'selected' : '') . ">FTTH (Fiber to the Home)</option>
                        <option value='FTTO' " . ($fttx_type == 'FTTO' ? 'selected' : '') . ">FTTO (Fiber to the Office / DÉDIÉ)</option>
                        <option value='FTTA' " . ($fttx_type == 'FTTA' ? 'selected' : '') . ">FTTA (Agrégation)</option>
                    </select>
                </div>
                <div class='col-md-6'>
                    <label class='small text-secondary'>Opérateur de Collecte</label>
                    <input type='text' name='fttx_carrier' class='form-control' value='$carrier'>
                </div>
                <div class='col-md-6'>
                    <label class='small text-secondary'>Identifiant PPPoE</label>
                    <input type='text' name='fttx_pppoe_user' class='form-control' value='$pppoe_user'>
                </div>
                <div class='col-md-6'>
                    <label class='small text-secondary'>Mot de passe PPPoE</label>
                    <input type='password' name='fttx_pppoe_pass' class='form-control' value='$pppoe_pass'>
                </div>
                <div class='col-12'>
                    <div class='form-check form-switch'>
                        <input class='form-check-input' type='checkbox' name='fttx_pppoe_required' value='1' {$pppoe_required}>
                        <label class='form-check-label text-white'>Authentification PPPoE requise</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ";
});

// 3. SAUVEGARDER LES DONNÉES (Action SQL via Hook Filter)
Hook::listen('save_service_data', function($currentData, $args) {
    if ($args['post']['type'] !== FTTX_TYPE_KEY) return $currentData;

    // Ajout des données spécifiques au FTTx
    $currentData['fttx_type'] = $args['post']['fttx_type'] ?? 'FTTH';
    $currentData['fttx_carrier'] = $args['post']['fttx_carrier'] ?? '';
    $currentData['fttx_pppoe_user'] = $args['post']['fttx_pppoe_user'] ?? '';
    $currentData['fttx_pppoe_pass'] = $args['post']['fttx_pppoe_pass'] ?? '';
    $currentData['fttx_pppoe_required'] = isset($args['post']['fttx_pppoe_required']) ? true : false;
    
    return $currentData;
});

// 4. AFFICHER SUR LE DASHBOARD CLIENT (Détails techniques)
Hook::listen('client_dashboard_service_details', function($args) {
    if ($args['service']['type'] !== FTTX_TYPE_KEY) return;

    $data = json_decode($args['service']['custom_fields'], true);
    if (!$data) return;

    $typeBadge = $data['fttx_type'] === 'FTTO' 
        ? '<span class="badge bg-danger text-white ms-1">DÉDIÉ FTTO</span>' 
        : '<span class="badge bg-primary text-white ms-1">PARTAGÉ FTTH</span>';
    
    $pppoeRequired = !empty($data['fttx_pppoe_required']) 
        ? '<span class="text-warning small fw-bold"><i class="fas fa-key me-1"></i> PPPoE Requis</span>'
        : '<span class="text-success small fw-bold"><i class="fas fa-check me-1"></i> IP Statique</span>';

    $pppoeDetails = '';
    if (!empty($data['fttx_pppoe_required']) && !empty($data['fttx_pppoe_user'])) {
         $pppoeDetails = "<p class='mb-0 mt-2 small'>
            Identifiant PPPoE: <strong class='font-monospace text-white'>" . htmlspecialchars($data['fttx_pppoe_user']) . "</strong>
        </p>";
    }
    
    return "
    <div class='mt-2 p-2 rounded bg-dark border border-secondary'>
        <div class='d-flex justify-content-between align-items-center mb-1'>
            <small class='text-muted text-uppercase'>Type de Ligne</small>
            $typeBadge
        </div>
        <div class='d-flex justify-content-between align-items-center mt-2'>
            <small class='text-muted text-uppercase'>Collecte</small>
            <span class='text-white small'>" . htmlspecialchars($data['fttx_carrier'] ?? 'CSHC Propre') . "</span>
        </div>
        <div class='d-flex justify-content-between align-items-center mt-2 border-top border-secondary pt-2'>
            <small class='text-muted text-uppercase'>Authentification</small>
            $pppoeRequired
        </div>
        $pppoeDetails
    </div>
    ";
});