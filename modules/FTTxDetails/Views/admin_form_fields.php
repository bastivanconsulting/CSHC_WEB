<?php
// modules/FTTxDetails/Views/admin_form_fields.php
?>
<div class='module-section' data-type='<?= $fttx_type_key ?>' style='display:none;'>
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
        if(type === '<?= $fttx_type_key ?>') {
            let section = document.querySelector('.module-section[data-type=\"<?= $fttx_type_key ?>\"]');
            if(section) section.style.display = 'block';
        }
    });

    // Exécution immédiate pour la création (le type FTTx est déjà listé dans le select de base)
    setTimeout(() => {
        let selector = document.getElementById('serviceTypeSelector');
        if (selector.value === '<?= $fttx_type_key ?>') {
            let section = document.querySelector('.module-section[data-type=\"<?= $fttx_type_key ?>\"]');
            if(section) section.style.display = 'block';
        }
    }, 50);
</script>
