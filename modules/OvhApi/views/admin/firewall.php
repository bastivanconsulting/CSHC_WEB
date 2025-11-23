<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="/admin/ovh/manage" class="text-decoration-none text-muted small mb-1 d-block"><i class="fas fa-arrow-left me-1"></i> Retour aux IPs</a>
            <h2 class="h4 text-white mb-0">Gestion Firewall : <span class="text-info font-monospace"><?= htmlspecialchars($ip) ?></span></h2>
        </div>

        <?php if ($firewall !== false): ?>
            <div class="d-flex align-items-center gap-3">
                <span class="badge <?= ($firewall['enabled'] ?? false) ? 'bg-success' : 'bg-danger' ?> p-2">
                    <?= ($firewall['enabled'] ?? false) ? 'ACTIF' : 'INACTIF' ?>
                </span>

                <form action="/admin/ovh/firewall/toggle" method="POST">
                    <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">
                    <?php if ($firewall['enabled'] ?? false): ?>
                        <input type="hidden" name="enabled" value="0">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-power-off me-1"></i> Désactiver
                        </button>
                    <?php else: ?>
                        <input type="hidden" name="enabled" value="1">
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-power-off me-1"></i> Activer
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($firewall === false): ?>
        <div class="text-center py-5">
            <h4 class="text-muted mb-3">Le Firewall n'est pas activé sur cette IP</h4>
            <form action="/admin/ovh/firewall/create" method="POST">
                <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle me-2"></i> Créer le Firewall
                </button>
            </form>
        </div>
    <?php else: ?>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card bg-dark border-secondary shadow-lg">
                    <div class="card-header bg-transparent border-secondary d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-white mb-0">Règles de filtrage</h5>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#addRuleModal">
                            <i class="fas fa-plus me-1"></i> Nouvelle Règle
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0 align-middle">
                                <thead class="bg-secondary bg-opacity-10 text-uppercase text-muted small">
                                    <tr>
                                        <th class="ps-4"># Sequence</th>
                                        <th>Action</th>
                                        <th>Protocol</th>
                                        <th>Source IP</th>
                                        <th>Dest. Port</th>
                                        <th>State</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rules)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                Aucune règle définie.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($rules as $rule): ?>
                                            <tr>
                                                <td class="ps-4 text-white"><?= htmlspecialchars($rule['sequence']) ?></td>
                                                <td>
                                                    <span class="badge <?= ($rule['action'] === 'permit') ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= htmlspecialchars($rule['action']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-white"><?= htmlspecialchars($rule['protocol']) ?></td>
                                                <td class="text-white"><?= htmlspecialchars($rule['source'] ?? 'Any') ?></td>
                                                <td class="text-white"><?= htmlspecialchars($rule['destinationPort'] ?? 'Any') ?></td>
                                                <td>
                                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($rule['state']) ?></span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <form action="/admin/ovh/firewall/delete" method="POST" onsubmit="return confirm('Supprimer cette règle ?');">
                                                        <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">
                                                        <input type="hidden" name="sequence" value="<?= htmlspecialchars($rule['sequence']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
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
        </div>

        <!-- Add Rule Modal -->
        <div class="modal fade" id="addRuleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-dark border-secondary text-white">
                    <form action="/admin/ovh/firewall/add" method="POST">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title">Ajouter une règle</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">

                            <div class="mb-3">
                                <label class="form-label">Sequence (0-99)</label>
                                <input type="number" name="sequence" class="form-control bg-dark text-white border-secondary" required min="0" max="99">
                                <small class="text-muted">L'ordre de priorité (0 = plus haut).</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Action</label>
                                <select name="action" class="form-select bg-dark text-white border-secondary">
                                    <option value="permit">Permit (Autoriser)</option>
                                    <option value="deny">Deny (Bloquer)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Protocol</label>
                                <select name="protocol" class="form-select bg-dark text-white border-secondary">
                                    <option value="tcp">TCP</option>
                                    <option value="udp">UDP</option>
                                    <option value="icmp">ICMP</option>
                                    <option value="gre">GRE</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Source IP (Optional)</label>
                                <input type="text" name="source" class="form-control bg-dark text-white border-secondary" placeholder="x.x.x.x/xx">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Destination Port (Optional)</label>
                                <input type="number" name="destinationPort" class="form-control bg-dark text-white border-secondary">
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-info">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>
