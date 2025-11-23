<?php
/**
 * Module: Forfait Data Mobile 4G/5G
 * Description: Ajoute la gestion des cartes SIM, quotas et options 5G.
 */

// 1. AJOUTER L'OPTION DANS LE MENU DÉROULANT (Admin > Création)
Hook::listen('register_service_type', function() {
    return '<option value="mobile_data">Forfait Data Mobile 4G/5G</option>';
});

// 2. AFFICHER LES CHAMPS SPÉCIFIQUES (Admin > Formulaire Création)
Hook::listen('admin_service_form_fields', function() {
    return "
    <div class='module-section' data-type='mobile_data' style='display:none;'>
        <div class='card bg-dark border-info mb-3'>
            <div class='card-header bg-transparent border-info text-info fw-bold small'>
                <i class='fas fa-sim-card me-2'></i>CONFIGURATION MOBILE
            </div>
            <div class='card-body'>
                <div class='row g-3'>
                    <div class='col-md-6'>
                        <label class='small text-secondary'>Quota Data (Go)</label>
                        <input type='number' name='mobile_quota' class='form-control' placeholder='Ex: 200'>
                    </div>
                    <div class='col-md-6'>
                        <label class='small text-secondary'>Numéro SIM (ICCID)</label>
                        <input type='text' name='mobile_iccid' class='form-control' placeholder='8933...'>
                    </div>
                    <div class='col-12'>
                        <div class='form-check form-switch'>
                            <input class='form-check-input' type='checkbox' name='mobile_5g' id='mobile5gSwitch' value='1'>
                            <label class='form-check-label text-white' for='mobile5gSwitch'>Option 5G Prioritaire</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Petit script pour afficher/masquer les champs selon le type choisi
        document.getElementById('serviceTypeSelector').addEventListener('change', function(e) {
            let section = document.querySelector('.module-section[data-type=\"mobile_data\"]');
            if(e.target.value === 'mobile_data') {
                section.style.display = 'block';
                // On peut pré-remplir le hardware pour aider
                let hwSelect = document.querySelector('select[name=\"hardware\"]');
                if(hwSelect) hwSelect.value = 'Autre'; 
            } else {
                section.style.display = 'none';
            }
        });
    </script>
    ";
});

// 3. AFFICHER LES CHAMPS EN ÉDITION (Admin > Formulaire Edition)
Hook::listen('admin_service_edit_fields', function($args) {
    // On ne s'affiche que si c'est un service Mobile
    if ($args['service']['type'] !== 'mobile_data') return;

    // Récupération des données JSON stockées
    $data = json_decode($args['service']['custom_fields'], true) ?? [];
    $quota = $data['mobile_quota'] ?? '';
    $iccid = $data['mobile_iccid'] ?? '';
    $is5g  = !empty($data['mobile_5g']) ? 'checked' : '';

    return "
    <div class='card bg-dark border-info mb-3'>
        <div class='card-header bg-transparent border-info text-info fw-bold small'>
            <i class='fas fa-sim-card me-2'></i>CONFIGURATION MOBILE
        </div>
        <div class='card-body'>
            <div class='row g-3'>
                <div class='col-md-6'>
                    <label class='small text-secondary'>Quota Data (Go)</label>
                    <input type='number' name='mobile_quota' class='form-control' value='{$quota}'>
                </div>
                <div class='col-md-6'>
                    <label class='small text-secondary'>Numéro SIM (ICCID)</label>
                    <input type='text' name='mobile_iccid' class='form-control' value='{$iccid}'>
                </div>
                <div class='col-12'>
                    <div class='form-check form-switch'>
                        <input class='form-check-input' type='checkbox' name='mobile_5g' value='1' {$is5g}>
                        <label class='form-check-label text-white'>Option 5G Prioritaire</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ";
});

// 4. SAUVEGARDER LES DONNÉES (Action SQL)
Hook::listen('save_service_data', function($currentData, $args) {
    // Si le type n'est pas mobile_data, on ne touche à rien
    if ($args['post']['type'] !== 'mobile_data') return $currentData;

    // Sinon on ajoute nos données au tableau qui sera converti en JSON
    $currentData['mobile_quota'] = $args['post']['mobile_quota'] ?? 0;
    $currentData['mobile_iccid'] = $args['post']['mobile_iccid'] ?? '';
    $currentData['mobile_5g']    = isset($args['post']['mobile_5g']) ? true : false;
    
    return $currentData;
});

// 5. AFFICHER DANS LA LISTE ADMIN (Tableau)
Hook::listen('admin_service_list_details', function($args) {
    if ($args['service']['type'] !== 'mobile_data') return;

    $data = json_decode($args['service']['custom_fields'], true);
    if ($data) {
        $badge5g = !empty($data['mobile_5g']) ? '<span class="badge bg-info text-dark ms-1">5G</span>' : '';
        return "<div class='text-warning small mt-1'><i class='fas fa-database'></i> {$data['mobile_quota']} Go {$badge5g}</div>";
    }
});

// 6. AFFICHER SUR LE DASHBOARD CLIENT (Badges Titre)
Hook::listen('client_dashboard_badges', function($args) {
    if ($args['service']['type'] !== 'mobile_data') return;

    $data = json_decode($args['service']['custom_fields'], true);
    if (!empty($data['mobile_5g'])) {
        return '<span class="badge bg-info text-dark ms-2"><i class="fas fa-bolt"></i> 5G</span>';
    }
});

// 7. AFFICHER SUR LE DASHBOARD CLIENT (Détails techniques)
Hook::listen('client_dashboard_service_details', function($args) {
    if ($args['service']['type'] !== 'mobile_data') return;

    $data = json_decode($args['service']['custom_fields'], true);
    if ($data) {
        return "
        <div class='p-2 rounded bg-dark border border-secondary mt-2'>
            <div class='d-flex justify-content-between align-items-center mb-1'>
                <small class='text-muted text-uppercase'>Enveloppe Data</small>
                <span class='text-white fw-bold'>{$data['mobile_quota']} Go</span>
            </div>
            <div class='progress' style='height: 6px;'>
                <div class='progress-bar bg-info' role='progressbar' style='width: 0%'></div> <!-- On pourrait connecter une API temps réel ici -->
            </div>
            <div class='d-flex justify-content-between align-items-center mt-2'>
                <small class='text-muted text-uppercase'>Carte SIM</small>
                <span class='font-monospace text-white small'>{$data['mobile_iccid']}</span>
            </div>
        </div>
        ";
    }
});