<?php
// modules/FTTxDetails/Views/client_dashboard_details.php
$typeBadge = $fttx_type === 'FTTO'
    ? '<span class="badge bg-danger text-white ms-1">DÉDIÉ FTTO</span>'
    : '<span class="badge bg-primary text-white ms-1">PARTAGÉ FTTH</span>';

$pppoeRequired = !empty($fttx_pppoe_required)
    ? '<span class="text-warning small fw-bold"><i class="fas fa-key me-1"></i> PPPoE Requis</span>'
    : '<span class="text-success small fw-bold"><i class="fas fa-check me-1"></i> IP Statique</span>';

$pppoeDetails = '';
if (!empty($fttx_pppoe_required) && !empty($fttx_pppoe_user)) {
     $pppoeDetails = "<p class='mb-0 mt-2 small'>
        Identifiant PPPoE: <strong class='font-monospace text-white'>" . htmlspecialchars($fttx_pppoe_user) . "</strong>
    </p>";
}
?>

<div class='mt-2 p-2 rounded bg-dark border border-secondary'>
    <div class='d-flex justify-content-between align-items-center mb-1'>
        <small class='text-muted text-uppercase'>Type de Ligne</small>
        <?= $typeBadge ?>
    </div>
    <div class='d-flex justify-content-between align-items-center mt-2'>
        <small class='text-muted text-uppercase'>Collecte</small>
        <span class='text-white small'><?= htmlspecialchars($fttx_carrier ?? 'CSHC Propre') ?></span>
    </div>
    <div class='d-flex justify-content-between align-items-center mt-2 border-top border-secondary pt-2'>
        <small class='text-muted text-uppercase'>Authentification</small>
        <?= $pppoeRequired ?>
    </div>
    <?= $pppoeDetails ?>
</div>
