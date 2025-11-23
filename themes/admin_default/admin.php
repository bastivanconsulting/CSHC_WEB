<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white fw-bold">Tableau de Bord</h2>
            <p class="text-muted mb-0">Bienvenue sur la console d'administration CSHC.</p>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">VERSION PRO</span>
    </div>

    <!-- Raccourcis Rapides (Pour retrouver vos boutons créations !) -->
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="/admin/services" class="btn btn-primary w-100 py-3">
                <i class="fas fa-plus-circle me-2"></i> Créer / Gérer un Service
            </a>
        </div>
        <div class="col-md-6">
            <a href="/admin/users" class="btn btn-outline-light w-100 py-3">
                <i class="fas fa-user-plus me-2"></i> Créer / Gérer un Client
            </a>
        </div>
    </div>

    <!-- Cartes Statistiques (KPIs) -->
    <div class="row g-4 mb-4">
        <!-- Services Actifs -->
        <div class="col-xl-3 col-md-6">
            <div class="glass-card p-3 border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small text-muted fw-bold">Services Actifs</div>
                        <div class="h3 text-white mb-0"><?= $stats['active_services'] ?? 0 ?></div>
                    </div>
                    <i class="fas fa-server fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        
        <!-- Revenu Mensuel -->
        <div class="col-xl-3 col-md-6">
            <div class="glass-card p-3 border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small text-muted fw-bold">Revenu Mensuel</div>
                        <div class="h3 text-white mb-0"><?= number_format($stats['monthly_revenue'] ?? 0, 2) ?> €</div>
                    </div>
                    <i class="fas fa-euro-sign fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
        
        <!-- Commandes en Attente -->
        <div class="col-xl-3 col-md-6">
            <div class="glass-card p-3 border-start border-4 border-warning h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small text-muted fw-bold">En Attente</div>
                        <div class="h3 text-white mb-0"><?= $stats['pending_services'] ?? 0 ?></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Clients -->
        <div class="col-xl-3 col-md-6">
            <div class="glass-card p-3 border-start border-4 border-info h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small text-muted fw-bold">Total Clients</div>
                        <div class="h3 text-white mb-0"><?= $stats['total_users'] ?? 0 ?></div>
                    </div>
                    <i class="fas fa-users fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau Activité Récente -->
    <div class="glass-card">
        <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white"><i class="fas fa-history me-2 text-primary"></i>Dernières Activités</h5>
            <a href="/admin/services" class="btn btn-sm btn-outline-primary">Tout voir</a>
        </div>
        <div class="table-responsive">
            <table class="table table-dark-transparent align-middle mb-0">
                <thead>
                    <tr class="text-secondary small text-uppercase">
                        <th class="ps-4">Client</th>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Prix</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stats['recent_services'])): ?>
                        <?php foreach($stats['recent_services'] as $s): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><span class="badge bg-dark border border-secondary text-secondary"><?= $s['type'] ?></span></td>
                            <td><?= number_format($s['price'], 2) ?> €</td>
                            <td>
                                <?php 
                                $badge = match($s['status']) { 
                                    'active'=>'success', 
                                    'suspended'=>'warning', 
                                    'pending'=>'info', 
                                    'terminated'=>'danger',
                                    default=>'secondary' 
                                };
                                ?>
                                <span class='badge bg-<?= $badge ?> bg-opacity-25 text-<?= $badge ?> border border-<?= $badge ?>'><?= strtoupper($s['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Aucune activité récente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>