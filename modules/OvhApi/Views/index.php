<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white">
            <i class="fas fa-cloud text-warning me-2"></i>OVH Cloud
        </h2>
        <div>
            <a href="/admin/ovh/settings" class="btn btn-outline-light">
                <i class="fas fa-cog me-2"></i>Configuration
            </a>
        </div>
    </div>

    <!-- TABS -->
    <ul class="nav nav-tabs border-secondary mb-4">
        <li class="nav-item">
            <a class="nav-link active bg-dark text-white border-secondary border-bottom-0" href="/admin/ovh">
                <i class="fas fa-network-wired me-2"></i>Adresses IP
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-secondary" href="/admin/ovh/servers">
                <i class="fas fa-server me-2"></i>Serveurs Dédiés
            </a>
        </li>
    </ul>

    <?php if (!$configured): ?>
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Le module OVH n'est pas configuré.
            <a href="/admin/ovh/settings" class="alert-link">Cliquez ici pour renseigner vos clés API.</a>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="fas fa-times-circle me-2"></i> Erreur API : <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card glass-card border-0 text-white">
        <div class="card-header bg-transparent border-secondary d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">IP Failover & Services</h5>
            <span class="badge bg-primary rounded-pill"><?= count($ips) ?> IPs</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-dark bg-opacity-50 text-secondary text-uppercase small">
                        <tr>
                            <th class="ps-4">IP Address</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ips)): ?>
                            <tr>
                                <td colspan="2" class="text-center py-5 text-muted">
                                    <i class="fas fa-search fa-3x mb-3 opacity-50"></i><br>
                                    Aucune IP trouvée ou API non connectée.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ips as $ip): ?>
                                <tr>
                                    <td class="ps-4 fw-bold font-monospace"><?= htmlspecialchars($ip) ?></td>
                                    <td class="text-end pe-4">
                                        <!-- On remplace les / par _ pour éviter les problèmes de routing Apache/PHP avec les %2F -->
                                        <a href="/admin/ovh/ip/<?= urlencode(str_replace('/', '_', $ip)) ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-shield-alt me-1"></i> Gérer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
