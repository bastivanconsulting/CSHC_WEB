<?php
// modules/ClientProfile/Views/client_dashboard_widget.php
$nomComplet = strtoupper($u['lastname'] ?? '') . " " . ($u['firstname'] ?? '');
$isCompany = ($u['client_type'] ?? '') === 'entreprise';
$icon = $isCompany ? 'fa-building' : 'fa-user-circle';

$typeBadge = $isCompany
    ? '<span class="badge bg-warning text-dark ms-2"><i class="fas fa-building me-1"></i> PRO</span>'
    : '<span class="badge bg-secondary ms-2"><i class="fas fa-user me-1"></i> PERSO</span>';

$adresse = !empty($u['address']) ? nl2br(htmlspecialchars($u['address'])) : '<em class="text-muted">Adresse non renseignée</em>';
?>

<div class='glass-card p-4 mb-4 border-top border-4 border-info'>
    <div class='row align-items-center'>

        <!-- Colonne 1: Infos Texte -->
        <div class='col-md-10'>
            <div class='d-flex align-items-center mb-2'>
                <h4 class='text-white mb-0'><?= $nomComplet ?></h4>
                <?= $typeBadge ?>
            </div>

            <p class='text-secondary mb-1 small'><i class='fas fa-envelope me-2 text-primary'></i> <?= $u['email'] ?></p>

            <p class='text-secondary small mt-2 mb-0'>
                <i class='fas fa-map-marker-alt me-2 text-info'></i> <?= $adresse ?>
            </p>

            <?php if ($isCompany): ?>
            <div class='mt-2 pt-2 border-top border-secondary border-opacity-25'>
                <span class='me-3 text-secondary small'>SIRET: <strong class='text-white font-monospace'><?= $u['siret'] ?></strong></span>
                <span class='text-secondary small'>TVA: <strong class='text-white font-monospace'><?= $u['vat_number'] ?></strong></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Colonne 2: Avatar (Simple, discret) -->
        <div class='col-md-2 text-end'>
            <div style='width: 60px; height: 60px; margin-left: auto;' class='rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center'>
                    <i class='fas <?= $icon ?> fa-2x text-info opacity-75'></i>
            </div>

            <a href='/admin/edit-user?id=<?= $u['id'] ?>' class='text-primary small mt-2 d-block text-decoration-none'></a>
        </div>
    </div>
</div>
