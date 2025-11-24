<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h2 fw-bold text-white mb-2">Protection WAF</h1>
            <p class="text-secondary">Protégez vos sites externes avec notre pare-feu intelligent.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
            <i class="fas fa-plus me-2"></i> Ajouter un site
        </button>
    </div>

    <!-- Flash Messages -->
    <?php if(isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger mb-4">
            <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success bg-success bg-opacity-10 border-success text-success mb-4">
            <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if(empty($sites)): ?>
            <div class="col-12">
                <div class="glass-card p-5 text-center">
                    <div class="mb-4 opacity-50">
                        <i class="fas fa-shield-virus fa-4x text-secondary"></i>
                    </div>
                    <h3 class="text-white h5">Aucun site protégé</h3>
                    <p class="text-secondary mb-4">Commencez par ajouter un nom de domaine pour générer votre script de protection.</p>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
                        Protéger mon premier site
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach($sites as $s): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="glass-card h-100 d-flex flex-column">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title text-white fw-bold mb-0 text-break">
                                    <i class="fas fa-globe me-2 text-primary"></i> <?= htmlspecialchars($s['domain']) ?>
                                </h5>
                                <div class="dropdown">
                                    <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                                        <li>
                                            <form action="/client/firewall/delete" method="POST" onsubmit="return confirm('Arrêter la protection ?');">
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash me-2"></i> Supprimer
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mb-3">
                                <?php if($s['mode'] === 'active'): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">
                                        <i class="fas fa-check me-1"></i> Actif
                                    </span>
                                <?php elseif($s['mode'] === 'challenge'): ?>
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25">
                                        <i class="fas fa-fingerprint me-1"></i> Challenge JS
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25">
                                        <i class="fas fa-eye me-1"></i> Monitoring
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between text-secondary small mb-3">
                                <span>Limit: <strong class="text-white"><?= $s['req_limit'] ?></strong> req</span>
                                <span>Window: <strong class="text-white"><?= $s['req_window'] ?></strong>s</span>
                            </div>

                            <div class="mt-auto pt-3 border-top border-secondary border-opacity-25 d-flex gap-2">
                                <button class="btn btn-sm btn-outline-light flex-grow-1" onclick="copyScript('<?= $s['domain'] ?>')">
                                    <i class="fas fa-code me-2"></i> Script
                                </button>
                                <a href="/client/firewall/logs" class="btn btn-sm btn-outline-info flex-grow-1">
                                    <i class="fas fa-chart-line me-2"></i> Logs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="addSiteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border border-secondary border-opacity-25 text-white">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title">Ajouter un site</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/client/firewall/add" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Domaine</label>
                        <input type="text" name="domain" class="form-control bg-dark text-white border-secondary" placeholder="ex: myshop.com" required>
                        <div class="form-text text-secondary">Sans http:// ni https://</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Mode de Protection</label>
                        <select name="mode" class="form-select bg-dark text-white border-secondary">
                            <option value="active">Bloquer (Standard)</option>
                            <option value="challenge">Challenge JS (Cloudflare-style)</option>
                            <option value="monitor">Monitoring (Log Only)</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-secondary small text-uppercase">Limite (req)</label>
                            <input type="number" name="req_limit" class="form-control bg-dark text-white border-secondary" value="100" min="10" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-secondary small text-uppercase">Fenêtre (sec)</label>
                            <input type="number" name="req_window" class="form-control bg-dark text-white border-secondary" value="60" min="10" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Activer la protection</button>
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
        alert('Code copié ! Ajoutez-le dans le <head> de votre site ' + domain);
    });
}
</script>
