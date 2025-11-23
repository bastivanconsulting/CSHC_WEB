<!-- Stats Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="glass-card h-100">
            <div class="glass-card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-uppercase text-muted text-xs fw-bold mb-1">Utilisateurs</h6>
                    <div class="h3 fw-bold text-white mb-0"><?= $stats['total_users'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card h-100">
            <div class="glass-card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-success bg-opacity-10 text-success">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-uppercase text-muted text-xs fw-bold mb-1">Services Actifs</h6>
                    <div class="h3 fw-bold text-white mb-0"><?= $stats['active_services'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card h-100">
            <div class="glass-card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-uppercase text-muted text-xs fw-bold mb-1">En Attente</h6>
                    <div class="h3 fw-bold text-white mb-0"><?= $stats['pending_services'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card h-100">
            <div class="glass-card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-info bg-opacity-10 text-info">
                    <i class="fas fa-euro-sign fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-uppercase text-muted text-xs fw-bold mb-1">Revenu Mensuel</h6>
                    <div class="h3 fw-bold text-white mb-0"><?= number_format($stats['monthly_revenue'], 2) ?> €</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Services -->
    <div class="col-lg-8">
        <div class="glass-card mb-4">
            <div class="glass-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-white">Activités Récentes</h5>
                <a href="/admin/services" class="btn btn-sm btn-outline-light rounded-pill">Tout voir</a>
            </div>
            <div class="glass-card-body p-0">
                <div class="table-responsive">
                    <table class="table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($stats['recent_services'] as $s): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center text-xs text-white" style="width:24px; height:24px;">
                                            <?= strtoupper(substr($s['email'], 0, 1)) ?>
                                        </div>
                                        <span class="text-white text-sm"><?= htmlspecialchars($s['email']) ?></span>
                                    </div>
                                </td>
                                <td class="text-white fw-medium"><?= htmlspecialchars($s['name']) ?></td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><?= htmlspecialchars($s['type']) ?></span></td>
                                <td>
                                    <?php if($s['status'] === 'active'): ?>
                                        <span class="badge status-active"><i class="fas fa-check me-1"></i> Actif</span>
                                    <?php elseif($s['status'] === 'pending'): ?>
                                        <span class="badge status-pending"><i class="fas fa-clock me-1"></i> Attente</span>
                                    <?php else: ?>
                                        <span class="badge status-suspended"><i class="fas fa-ban me-1"></i> Suspendu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="/admin/edit-service?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-light border-0"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($stats['recent_services'])): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Aucune activité récente.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & System -->
    <div class="col-lg-4">
        <div class="glass-card mb-4">
            <div class="glass-card-header">
                <h5 class="mb-0 fw-bold text-white">Actions Rapides</h5>
            </div>
            <div class="glass-card-body">
                <div class="d-grid gap-3">
                    <button class="btn btn-outline-light text-start p-3 d-flex align-items-center gap-3 hover-bg-surface" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <div class="bg-success bg-opacity-10 text-success p-2 rounded">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white">Nouveau Client</div>
                            <div class="text-xs text-muted">Créer un compte utilisateur</div>
                        </div>
                    </button>

                    <a href="/admin/services" class="btn btn-outline-light text-start p-3 d-flex align-items-center gap-3 hover-bg-surface">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white">Ajouter un Service</div>
                            <div class="text-xs text-muted">Provisionner une nouvelle ligne</div>
                        </div>
                    </a>

                    <a href="/admin/settings" class="btn btn-outline-light text-start p-3 d-flex align-items-center gap-3 hover-bg-surface">
                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white">Configuration</div>
                            <div class="text-xs text-muted">Paramètres globaux du site</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
