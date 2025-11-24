<?php
// modules/FTTxDetails/Views/admin_edit_fields.php
$fttx_type = $fttx_type ?? 'FTTH';
$carrier = htmlspecialchars($fttx_carrier ?? '');
$pppoe_user = htmlspecialchars($fttx_pppoe_user ?? '');
$pppoe_pass = htmlspecialchars($fttx_pppoe_pass ?? '');
$pppoe_required = !empty($fttx_pppoe_required) ? 'checked' : '';
?>
<div class='card bg-dark border-info mb-3'>
    <div class='card-header bg-transparent border-info text-info fw-bold small'>
        <i class='fas fa-sim-card me-2'></i>CONFIGURATION FIBRE
    </div>
    <div class='card-body'>
        <div class='row g-3'>
            <div class='col-md-6'>
                <label class='small text-secondary'>Type de Fibre</label>
                <select name='fttx_type' class='form-select'>
                    <option value='FTTH' <?= ($fttx_type == 'FTTH' ? 'selected' : '') ?>>FTTH (Fiber to the Home)</option>
                    <option value='FTTO' <?= ($fttx_type == 'FTTO' ? 'selected' : '') ?>>FTTO (Fiber to the Office / DÉDIÉ)</option>
                    <option value='FTTA' <?= ($fttx_type == 'FTTA' ? 'selected' : '') ?>>FTTA (Agrégation)</option>
                </select>
            </div>
            <div class='col-md-6'>
                <label class='small text-secondary'>Opérateur de Collecte</label>
                <input type='text' name='fttx_carrier' class='form-control' value='<?= $carrier ?>'>
            </div>
            <div class='col-md-6'>
                <label class='small text-secondary'>Identifiant PPPoE</label>
                <input type='text' name='fttx_pppoe_user' class='form-control' value='<?= $pppoe_user ?>'>
            </div>
            <div class='col-md-6'>
                <label class='small text-secondary'>Mot de passe PPPoE</label>
                <input type='password' name='fttx_pppoe_pass' class='form-control' value='<?= $pppoe_pass ?>'>
            </div>
            <div class='col-12'>
                <div class='form-check form-switch'>
                    <input class='form-check-input' type='checkbox' name='fttx_pppoe_required' value='1' <?= $pppoe_required ?>>
                    <label class='form-check-label text-white'>Authentification PPPoE requise</label>
                </div>
            </div>
        </div>
    </div>
</div>
