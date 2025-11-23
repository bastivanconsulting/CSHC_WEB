<div class="row g-4">
    <!-- Formulaire Ajout -->
    <div class="col-lg-4">
        <div class="glass-card position-sticky" style="top: 20px;">
            <div class="glass-card-header">
                <h5 class="mb-0 fw-bold text-white"><i class="fas fa-plus-circle me-2 text-success"></i>Nouveau Service</h5>
            </div>
            <div class="glass-card-body">
                <form method="POST">
                    <input type="hidden" name="add_service" value="1">

                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select name="user_id" class="form-select">
                            <?php foreach($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= $u['email'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nom Service</label>
                        <input type="text" name="name" class="form-control" placeholder="ex: VPN_Site_A" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select form-select-sm" id="serviceTypeSelector">
                                <option value="OpenVPN">OpenVPN</option>
                                <option value="Wireguard">Wireguard</option>
                                <option value="VPLS">VPLS</option>
                                <option value="FTTx">FTTx</option>
                                <?= Hook::call('register_service_type') ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Prix (€)</label>
                            <input type="number" step="0.01" name="price" class="form-control form-control-sm" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Hooks -->
                    <div id="modules-container" class="mb-3"><?= Hook::call('admin_service_form_fields') ?></div>

                    <div class="mb-3">
                        <label class="form-label">Engagement</label>
                        <select name="engagement" class="form-select form-select-sm">
                            <option>Sans engagement</option>
                            <option>12 mois</option>
                            <option>24 mois</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="form-label">Débit</label><input type="text" name="bandwidth" class="form-control form-control-sm" placeholder="1 Gbps"></div>
                        <div class="col-6">
                            <label class="form-label">Matériel</label>
                            <select name="hardware" class="form-select form-select-sm">
                                <option>Mikrotik</option><option>Arista</option><option>Cisco</option><option>Virtuel</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4"><label class="form-label">Détails Techniques</label><textarea name="details" class="form-control" rows="2" placeholder="IP, VLAN, notes..."></textarea></div>

                    <button class="btn btn-primary w-100 shadow-lg"><i class="fas fa-rocket me-2"></i> Provisionner</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Liste -->
    <div class="col-lg-8">
        <div class="glass-card h-100">
            <div class="glass-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-white">Services Déployés</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20"><?= count($services) ?> Total</span>
            </div>
            <div class="glass-card-body p-0">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Client / Offre</th>
                                <th>Détails</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($services as $s): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center text-xs text-white" style="width:24px; height:24px;">
                                            <?= strtoupper(substr($s['email'], 0, 1)) ?>
                                        </div>
                                        <span class="text-white fw-medium text-sm"><?= htmlspecialchars($s['email']) ?></span>
                                    </div>
                                    <div class="text-xs text-muted d-flex align-items-center gap-1">
                                        <i class="fas fa-tag text-primary"></i> <?= htmlspecialchars($s['name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 text-xs mb-1"><?= $s['type'] ?></span>
                                    <?php if($s['termination_date']): ?>
                                        <div class="text-danger text-xs fw-bold"><i class="fas fa-calendar-times me-1"></i> Fin: <?= $s['termination_date'] ?></div>
                                    <?php endif; ?>
                                    <?= Hook::call('admin_service_list_details', ['service' => $s]) ?>
                                </td>
                                <td class="text-white fw-bold text-sm">
                                    <?= number_format($s['price'], 2) ?> €
                                </td>
                                <td>
                                    <?php if($s['status'] === 'active'): ?>
                                        <span class="badge status-active"><i class="fas fa-check me-1"></i> Actif</span>
                                    <?php elseif($s['status'] === 'pending'): ?>
                                        <span class="badge status-pending"><i class="fas fa-clock me-1"></i> Attente</span>
                                    <?php elseif($s['status'] === 'suspended'): ?>
                                        <span class="badge status-suspended"><i class="fas fa-ban me-1"></i> Suspendu</span>
                                    <?php else: ?>
                                        <span class="badge status-suspended bg-dark text-muted"><i class="fas fa-times me-1"></i> Résilié</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-light border-0" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-dark border-secondary border-opacity-25 shadow-lg">
                                            <li><a class="dropdown-item" href="/admin/edit-service?id=<?= $s['id'] ?>"><i class="fas fa-edit me-2"></i> Modifier</a></li>

                                            <form action="/admin/update" method="POST" class="d-inline">
                                                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">

                                                <?php if($s['status'] == 'active'): ?>
                                                    <li><button name="status" value="suspended" class="dropdown-item text-warning"><i class="fas fa-pause me-2"></i> Suspendre</button></li>
                                                <?php elseif($s['status'] == 'suspended' || $s['status'] == 'pending'): ?>
                                                    <li><button name="status" value="active" class="dropdown-item text-success"><i class="fas fa-play me-2"></i> Activer</button></li>
                                                <?php endif; ?>

                                                <?php if($s['status'] != 'terminated'): ?>
                                                    <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
                                                    <li><button name="status" value="terminated" class="dropdown-item text-danger" onclick="return confirm('Cette action est irréversible.')"><i class="fas fa-trash me-2"></i> Résilier</button></li>
                                                <?php endif; ?>
                                            </form>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
