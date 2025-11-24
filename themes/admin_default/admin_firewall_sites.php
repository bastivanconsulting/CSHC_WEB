<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white">Sites Protégés (WAF API)</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
            <i class="fas fa-plus me-2"></i> Ajouter un site
        </button>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card glass-card border-0 text-white">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dark table-borderless align-middle mb-0" style="background: transparent;">
                            <thead class="border-bottom border-secondary border-opacity-25 text-secondary small text-uppercase">
                                <tr>
                                    <th class="ps-4">Domaine</th>
                                    <th>Propriétaire</th>
                                    <th>Mode</th>
                                    <th>Limite</th>
                                    <th>Ban (sec)</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($sites)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-shield-alt fa-3x mb-3 opacity-25"></i><br>
                                            Aucun site externe protégé pour le moment.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($sites as $s): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-white">
                                            <i class="fas fa-globe me-2 text-info"></i> <?= htmlspecialchars($s['domain']) ?>
                                        </td>
                                        <td>
                                            <?php if($s['user_id']): ?>
                                                <span class="badge bg-primary bg-opacity-25 text-primary">Client #<?= $s['user_id'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success bg-opacity-25 text-success">Admin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(($s['mode'] ?? 'active') === 'challenge'): ?>
                                                <span class="badge bg-warning text-dark">Challenge</span>
                                            <?php elseif(($s['mode'] ?? 'active') === 'monitor'): ?>
                                                <span class="badge bg-info text-dark">Monitor</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-25 border border-secondary text-white">
                                                <?= $s['req_limit'] ?> / <?= $s['req_window'] ?>s
                                            </span>
                                        </td>
                                        <td><?= $s['ban_duration'] ?>s</td>
                                        <td class="text-end pe-4">
                                            <form action="/admin/firewall/sites/delete" method="POST" class="d-inline" onsubmit="return confirm('Supprimer la protection pour ce site ?');">
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
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
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="addSiteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border border-secondary border-opacity-25 text-white">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title">Ajouter un site à protéger</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/firewall/sites/add" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Domaine (sans http)</label>
                        <input type="text" name="domain" class="form-control bg-dark text-white border-secondary" placeholder="ex: mon-wordpress.com" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-secondary small text-uppercase">Limite (req)</label>
                            <input type="number" name="req_limit" class="form-control bg-dark text-white border-secondary" value="100" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-secondary small text-uppercase">Fenêtre (sec)</label>
                            <input type="number" name="req_window" class="form-control bg-dark text-white border-secondary" value="60" required>
                        </div>
                    </div>
                    <div class="mb-3">
                         <label class="form-label text-secondary small text-uppercase">Durée du Ban (sec)</label>
                         <input type="number" name="ban_duration" class="form-control bg-dark text-white border-secondary" value="3600" required>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Protéger</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyScript(domain) {
    const url = "<?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" ?>/?firewall_js=true";
    const script = `<script src="${url}"><\/script>`;
    navigator.clipboard.writeText(script).then(() => {
        alert('Code script copié ! Insérez-le dans le <head> de ' + domain);
    });
}
</script>
