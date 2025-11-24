<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-white">
                Gestion IP: <span class="font-monospace text-warning"><?= htmlspecialchars($ip) ?></span>
            </h2>
            <div class="text-secondary small mt-1">
                <?= isset($details['type']) ? strtoupper($details['type']) : 'IP Address' ?>
                &bull; <?= isset($details['country']) ? strtoupper($details['country']) : 'Unknown' ?>
            </div>
        </div>
        <a href="/admin/ovh" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="fas fa-times-circle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success border-0 shadow-sm">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- --- GAUCHE: FIREWALL STATUS & PTR --- -->
        <div class="col-md-4 mb-4">
            <!-- REVERSE DNS -->
            <div class="card glass-card border-0 text-white mb-4">
                <div class="card-header bg-transparent border-secondary fw-bold text-uppercase small">Reverse DNS (PTR)</div>
                <div class="card-body">
                    <form action="/admin/ovh/action/ptr" method="POST">
                        <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">
                        <div class="input-group">
                            <input type="text" name="reverse" class="form-control bg-dark text-white border-secondary" placeholder="domain.com." value="<?= htmlspecialchars($ptr) ?>">
                            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-save"></i></button>
                        </div>
                        <div class="form-text text-secondary small mt-2">Le reverse doit se terminer par un point (ex: server.com.)</div>
                    </form>
                </div>
            </div>

            <!-- FIREWALL TOGGLE -->
            <div class="card glass-card border-0 text-white">
                <div class="card-header bg-transparent border-secondary fw-bold text-uppercase small d-flex justify-content-between align-items-center">
                    Firewall Network
                    <?php if ($firewall && $firewall['enabled']): ?>
                        <span class="badge bg-success">ACTIF</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">INACTIF</span>
                    <?php endif; ?>
                </div>
                <div class="card-body text-center py-4">
                    <form action="/admin/ovh/action/firewall/toggle" method="POST">
                        <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">

                        <?php if ($firewall && $firewall['enabled']): ?>
                            <div class="mb-3 text-success">
                                <i class="fas fa-shield-alt fa-3x"></i>
                            </div>
                            <p class="text-secondary small mb-3">Le firewall protège actuellement cette IP.</p>
                            <input type="hidden" name="action" value="disable">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-power-off me-2"></i>Désactiver le Firewall
                            </button>
                        <?php else: ?>
                            <div class="mb-3 text-secondary">
                                <i class="fas fa-shield-alt fa-3x opacity-25"></i>
                            </div>
                            <p class="text-secondary small mb-3">Le firewall est désactivé.</p>
                            <input type="hidden" name="action" value="enable">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-power-off me-2"></i>Activer le Firewall
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- --- DROITE: REGLES FIREWALL --- -->
        <div class="col-md-8">
            <div class="card glass-card border-0 text-white h-100">
                <div class="card-header bg-transparent border-secondary d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Règles du Firewall</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRuleModal" <?= (!$firewall || !$firewall['enabled']) ? 'disabled' : '' ?>>
                        <i class="fas fa-plus me-2"></i>Ajouter une règle
                    </button>
                </div>

                <?php if (!$firewall || !$firewall['enabled']): ?>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-muted opacity-50">
                        <i class="fas fa-ban fa-3x mb-3"></i>
                        <p>Le firewall doit être activé pour gérer les règles.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="bg-dark bg-opacity-50 text-secondary text-uppercase small">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Action</th>
                                    <th>Proto</th>
                                    <th>Source</th>
                                    <th>Ports (Src -> Dst)</th>
                                    <th>Etat</th>
                                    <th class="text-end pe-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rules)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Aucune règle définie.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rules as $rule): ?>
                                        <tr>
                                            <td class="ps-4 text-muted small">#<?= $rule['sequence'] ?></td>
                                            <td>
                                                <?php if ($rule['action'] === 'permit'): ?>
                                                    <span class="badge bg-success">PERMIT</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">DENY</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-secondary"><?= strtoupper($rule['protocol']) ?></span></td>
                                            <td class="font-monospace small"><?= $rule['source'] ?: 'Any' ?></td>
                                            <td class="font-monospace small">
                                                <?php if ($rule['protocol'] === 'icmp' || $rule['protocol'] === 'gre'): ?>
                                                    -
                                                <?php else: ?>
                                                    <?= isset($rule['sourcePort']) ? $rule['sourcePort'] : '*' ?> <i class="fas fa-arrow-right text-muted mx-1"></i> <?= isset($rule['destinationPort']) ? $rule['destinationPort'] : '*' ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $rule['state'] === 'ok' ? 'info' : 'warning' ?>"><?= $rule['state'] ?></span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <form action="/admin/ovh/action/firewall/rule/delete" method="POST" onsubmit="return confirm('Supprimer cette règle ?');">
                                                    <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">
                                                    <input type="hidden" name="sequence" value="<?= $rule['sequence'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0">
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout Règle -->
<div class="modal fade" id="addRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Ajouter une règle Firewall</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/ovh/action/firewall/rule/add" method="POST">
                <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label small text-uppercase text-secondary">Priorité (0-19)</label>
                        <input type="number" name="sequence" class="form-control bg-dark text-white border-secondary" min="0" max="19" required>
                        <div class="form-text text-secondary">Ordre d'exécution (0 est prioritaire).</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small text-uppercase text-secondary">Action</label>
                            <select name="action" class="form-select bg-dark text-white border-secondary">
                                <option value="permit">PERMIT (Autoriser)</option>
                                <option value="deny">DENY (Bloquer)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-uppercase text-secondary">Protocole</label>
                            <select name="protocol" class="form-select bg-dark text-white border-secondary" id="protoSelect" onchange="togglePorts()">
                                <option value="tcp">TCP</option>
                                <option value="udp">UDP</option>
                                <option value="icmp">ICMP</option>
                                <option value="gre">GRE</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-uppercase text-secondary">Source IP (Optionnel)</label>
                        <input type="text" name="source" class="form-control bg-dark text-white border-secondary" placeholder="ex: 1.2.3.4/32">
                    </div>

                    <div class="row g-2" id="portFields">
                        <div class="col-6">
                            <label class="form-label small text-uppercase text-secondary">Port Source</label>
                            <input type="number" name="sourcePort" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-uppercase text-secondary">Port Dest.</label>
                            <input type="number" name="destinationPort" class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePorts() {
    const proto = document.getElementById('protoSelect').value;
    const ports = document.getElementById('portFields');
    if (proto === 'tcp' || proto === 'udp') {
        ports.style.display = 'flex';
    } else {
        ports.style.display = 'none';
    }
}
</script>
