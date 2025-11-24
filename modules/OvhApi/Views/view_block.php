<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-white">
                <i class="fas fa-sitemap text-warning me-2"></i>Bloc IP: <span class="font-monospace"><?= htmlspecialchars($block) ?></span>
            </h2>
            <div class="text-secondary small mt-1">Gestion des IPs individuelles du bloc</div>
        </div>
        <a href="/admin/ovh" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <div class="card glass-card border-0 text-white">
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
                        <?php foreach ($ips as $ipAddr): ?>
                            <tr>
                                <td class="ps-4 font-monospace fw-bold">
                                    <i class="fas fa-microchip text-muted me-2"></i><?= htmlspecialchars($ipAddr) ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="/admin/ovh/ip/<?= urlencode($ipAddr) ?>?block=<?= urlencode($block) ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-shield-alt me-1"></i> Gérer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
