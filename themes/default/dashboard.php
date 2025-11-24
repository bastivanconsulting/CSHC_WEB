<div class="container py-4">
    
    <!-- [HOOK] Top Dashboard : Pour afficher une bannière de maintenance ou une promo -->
    <?= Hook::call('client_dashboard_top') ?>

    <h2 class="fw-bold text-white mb-4">Tableau de Bord</h2>
    
    <!-- [HOOK] Widgets : Pour afficher des graphiques globaux ou le Profil Client -->
    <?= Hook::call('client_dashboard_widgets') ?>

    <!-- WAF Promo / Shortcut -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-primary bg-opacity-10 me-3 text-primary border border-primary border-opacity-25">
                        <i class="fas fa-shield-virus fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="text-white mb-1">Protection WAF & Anti-DDoS</h4>
                        <p class="text-secondary small mb-0">Sécurisez vos sites externes avec notre pare-feu applicatif (Web Application Firewall).</p>
                    </div>
                </div>
                <a href="/client/firewall" class="btn btn-primary">
                    Gérer ma protection <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach($services as $s): ?>
        <div class="col-lg-6">
            <div class="glass-card h-100 position-relative overflow-hidden">
                <?php 
                    $statusColor = match($s['status']) {
                        'active' => 'var(--success)',
                        'terminated' => 'var(--secondary)',
                        default => 'var(--danger)'
                    };
                ?>
                <div class="position-absolute top-0 start-0 bottom-0" style="width: 4px; background: <?= $statusColor ?>;"></div>
                
                <div class="card-body p-4 ps-5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="fw-bold mb-0 text-white"><?= htmlspecialchars($s['name']) ?></h4>
                            <span class="badge bg-primary bg-opacity-10 text-primary"><?= $s['type'] ?></span>
                            
                            <!-- [HOOK] Badges : Le module Mobile pourra insérer ici le badge "5G" -->
                            <?= Hook::call('client_dashboard_badges', ['service' => $s]) ?>
                        </div>
                        <div class="text-end">
                            <div class="h4 mb-0 text-white"><?= number_format($s['price'], 2) ?> €<span class="fs-6 text-muted">/mois</span></div>
                            <small class="text-muted"><?= $s['engagement'] ?></small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3 text-secondary small">
                        <div class="col-6">IP: <span class="text-white font-monospace"><?= $s['ip_address'] ?></span></div>
                        <div class="col-6">Loc: <span class="text-white"><?= $s['location'] ?></span></div>
                    </div>

                    <!-- [HOOK] Details : Le module peut insérer ici le Numéro SIM, VLAN, etc. -->
                    <div class="module-details mb-3">
                        <?= Hook::call('client_dashboard_service_details', ['service' => $s]) ?>
                    </div>

                    <hr class="border-secondary opacity-25">

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <?php if($s['termination_date']): ?>
                                <span class="text-warning small"><i class="fas fa-clock"></i> Résiliation demandée pour le <?= $s['termination_date'] ?></span>
                            <?php else: ?>
                                <span class="text-success small"><i class="fas fa-check-circle"></i> Service actif</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($s['status'] === 'active' && !$s['termination_date']): ?>
                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $s['id'] ?>">
                                Résilier
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Modal de Résiliation -->
            <div class="modal fade" id="cancelModal<?= $s['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark border border-secondary text-white">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title">Demande de résiliation</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="/client/cancel" method="POST">
                            <div class="modal-body">
                                <p>Vous êtes sur le point de demander la résiliation du service <strong><?= htmlspecialchars($s['name']) ?></strong>.</p>
                                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Date de fin souhaitée</label>
                                    <input type="date" name="date" class="form-control bg-secondary text-white border-0" required min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="alert alert-warning bg-opacity-10 border-warning text-warning small">
                                    <i class="fas fa-info-circle"></i> Conformément à votre engagement (<?= $s['engagement'] ?>), des frais peuvent s'appliquer si la date est anticipée.
                                </div>
                            </div>
                            <div class="modal-footer border-secondary">
                                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-danger">Confirmer la demande</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

    <!-- [HOOK] Bottom Dashboard : Pour afficher l'historique ou des pubs en bas de page -->
    <?= Hook::call('client_dashboard_bottom') ?>

</div>