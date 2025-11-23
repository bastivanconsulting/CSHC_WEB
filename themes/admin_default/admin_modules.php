<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white"><i class="fas fa-cubes me-2 text-info"></i>Gestion des Modules</h2>
            <p class="text-muted">Activez ou désactivez les extensions du système CSHC.</p>
        </div>
        <a href="/admin" class="btn btn-outline-light btn-sm">Retour Admin</a>
    </div>

    <div class="glass-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark-transparent align-middle mb-0">
                <thead>
                    <tr class="text-secondary small text-uppercase">
                        <th class="ps-4">Nom du Module</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($modules as $m): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="ps-4">
                            <div class="fw-bold text-white fs-5"><?= htmlspecialchars($m['name']) ?></div>
                            <small class="text-muted font-monospace">/modules/<?= htmlspecialchars($m['name']) ?>/index.php</small>
                        </td>
                        <td>
                            <?php if($m['status'] === 'active'): ?>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i> ACTIF
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary px-3 py-2">
                                    <i class="fas fa-ban me-1"></i> DÉSACTIVÉ
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                
                                <?php 
                                // [LOGIQUE CONFIGURATION]
                                // On affiche le bouton config SEULEMENT si le module est actif 
                                // et qu'il répond au Hook 'get_module_config_url'
                                if ($m['status'] === 'active') {
                                    $configUrl = Hook::call('get_module_config_url', $m['name']);
                                    if (!empty($configUrl)): 
                                ?>
                                    <a href="<?= $configUrl ?>" class="btn btn-outline-warning btn-sm" title="Configurer">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                <?php 
                                    endif; 
                                }
                                ?>

                                <form action="/admin/toggle-module" method="POST">
                                    <input type="hidden" name="name" value="<?= $m['name'] ?>">
                                    
                                    <?php if($m['status'] === 'active'): ?>
                                        <input type="hidden" name="status" value="disabled">
                                        <button class="btn btn-danger btn-sm px-3">
                                            <i class="fas fa-power-off me-2"></i>Désactiver
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="status" value="active">
                                        <button class="btn btn-success btn-sm px-3">
                                            <i class="fas fa-play me-2"></i>Activer
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($modules)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-3 d-block"></i>
                                Aucun module détecté dans le dossier <code>/modules/</code>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>