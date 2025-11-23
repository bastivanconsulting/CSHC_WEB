<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white mb-0">Gestion IP & Failover</h2>
        <a href="/admin/ovh/settings" class="btn btn-outline-light btn-sm"><i class="fas fa-cog me-1"></i> Configuration</a>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card bg-dark border-secondary shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-secondary bg-opacity-10 text-uppercase text-muted small">
                        <tr>
                            <th class="ps-4">IP Address</th>
                            <th>Type</th>
                            <th>Pays</th>
                            <th>Reverse DNS (PTR)</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ips)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-network-wired fa-3x mb-3 opacity-25"></i><br>
                                    Aucune IP trouvée ou erreur API.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ips as $ip): ?>
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold text-info">
                                        <?= htmlspecialchars($ip['ip'] ?? 'N/A') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($ip['type'] ?? 'Dedicated') ?></span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($ip['country'] ?? 'N/A') ?>
                                    </td>
                                    <td>
                                        <form action="/admin/ovh/ptr" method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="ip" value="<?= htmlspecialchars($ip['ip']) ?>">
                                            <input type="text" name="ptr" class="form-control form-control-sm bg-dark text-white border-secondary"
                                                   value="<?= htmlspecialchars($ip['reverse'] ?? '') ?>" placeholder="Reverse DNS">
                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Mettre à jour">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="/admin/ovh/firewall?ip=<?= urlencode($ip['ip']) ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-fire me-1"></i> Firewall
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
