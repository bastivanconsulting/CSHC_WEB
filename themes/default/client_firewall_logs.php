<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h2 fw-bold text-white mb-2">Logs & Blocages</h1>
            <p class="text-secondary">Analysez le trafic et bloquez les menaces.</p>
        </div>
        <a href="/client/firewall" class="btn btn-outline-light">
            <i class="fas fa-arrow-left me-2"></i> Retour WAF
        </a>
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

    <div class="row g-4">
        <!-- Bloquer IP Manuellement -->
        <div class="col-lg-4">
            <div class="glass-card p-4 h-100">
                <h5 class="text-white mb-4"><i class="fas fa-ban me-2 text-danger"></i>Bloquer une IP</h5>
                <form action="/client/firewall/block" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Adresse IP</label>
                        <input type="text" name="ip_address" class="form-control bg-dark text-white border-secondary" placeholder="ex: 1.2.3.4" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small text-uppercase">Sur le domaine</label>
                        <select name="domain" class="form-select bg-dark text-white border-secondary" required>
                            <?php foreach($domains as $d): ?>
                                <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">
                        Bloquer l'IP
                    </button>
                </form>
            </div>
        </div>

        <!-- IP Bannies -->
        <div class="col-lg-8">
            <div class="glass-card p-4 h-100">
                <h5 class="text-white mb-4"><i class="fas fa-list-ul me-2 text-warning"></i>IP Bannies (Actives)</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-borderless align-middle mb-0" style="background:transparent;">
                        <thead>
                            <tr class="text-secondary small text-uppercase border-bottom border-secondary border-opacity-25">
                                <th>IP</th>
                                <th>Domaine</th>
                                <th>Raison</th>
                                <th>Expire le</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($bans)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">Aucun bannissement actif.</td></tr>
                            <?php else: ?>
                                <?php foreach($bans as $b): ?>
                                <tr>
                                    <td class="font-monospace text-white"><?= $b['ip_address'] ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-25 text-white"><?= $b['domain'] ?></span></td>
                                    <td class="small text-secondary"><?= htmlspecialchars($b['reason']) ?></td>
                                    <td class="small"><?= $b['expires_at'] ?></td>
                                    <td class="text-end">
                                        <form action="/client/firewall/unblock" method="POST" class="d-inline" onsubmit="return confirm('Débloquer cette IP ?');">
                                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-unlock"></i></button>
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

        <!-- Trafic Suspect (Top Requêtes) -->
        <div class="col-12">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-white mb-0"><i class="fas fa-chart-bar me-2 text-info"></i>Top Trafic / IP (Fenêtre courante)</h5>
                    <span class="badge bg-info bg-opacity-10 text-info">50 derniers</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover table-borderless align-middle mb-0" style="background:transparent;">
                        <thead>
                            <tr class="text-secondary small text-uppercase border-bottom border-secondary border-opacity-25">
                                <th>IP</th>
                                <th>Domaine</th>
                                <th>Requêtes / Fenêtre</th>
                                <th>Dernière requête</th>
                                <th class="text-end">Action Rapide</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($logs)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">Aucun trafic suspect enregistré récemment.</td></tr>
                            <?php else: ?>
                                <?php foreach($logs as $l): ?>
                                <tr>
                                    <td class="font-monospace text-white"><?= $l['ip_address'] ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-25 text-white"><?= $l['domain'] ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold text-white me-2"><?= $l['request_count'] ?></span>
                                            <div class="progress w-100" style="height: 4px;">
                                                <div class="progress-bar bg-info" style="width: <?= min(100, $l['request_count']) ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small text-muted"><?= date('H:i:s', $l['first_request_at']) ?></td>
                                    <td class="text-end">
                                        <form action="/client/firewall/block" method="POST" class="d-inline">
                                            <input type="hidden" name="ip_address" value="<?= $l['ip_address'] ?>">
                                            <input type="hidden" name="domain" value="<?= $l['domain'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Bloquer</button>
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
