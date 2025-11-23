<div class="container-fluid px-4 py-4">
    <h2 class="fw-bold text-white mb-4"><i class="fas fa-network-wired me-2 text-info"></i>Gestion des Services</h2>

    <div class="row g-4">
        <!-- Formulaire Ajout -->
        <div class="col-lg-4">
            <div class="glass-card">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                    <h5 class="mb-0 fw-bold text-white"><i class="fas fa-plus-circle me-2 text-success"></i>Nouveau Service</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="add_service" value="1">
                        
                        <div class="mb-3">
                            <label class="small text-secondary fw-bold mb-1">CLIENT</label>
                            <select name="user_id" class="form-select bg-dark text-white border-secondary">
                                <?php foreach($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= $u['email'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="small text-secondary fw-bold mb-1">NOM SERVICE</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="small text-secondary fw-bold mb-1">TYPE</label>
                                <select name="type" class="form-select form-select-sm" id="serviceTypeSelector">
                                    <option value="OpenVPN">OpenVPN</option>
                                    <option value="Wireguard">Wireguard</option>
                                    <option value="VPLS">VPLS</option>
                                    <option value="FTTx">FTTx</option>
                                    <?= Hook::call('register_service_type') ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small text-secondary fw-bold mb-1">PRIX (€)</label>
                                <input type="number" step="0.01" name="price" class="form-control form-control-sm" placeholder="0.00">
                            </div>
                        </div>

                        <!-- Hooks -->
                        <div id="modules-container" class="mb-3"><?= Hook::call('admin_service_form_fields') ?></div>

                        <div class="mb-3">
                            <label class="small text-secondary fw-bold mb-1">ENGAGEMENT</label>
                            <select name="engagement" class="form-select form-select-sm">
                                <option>Sans engagement</option>
                                <option>12 mois</option>
                                <option>24 mois</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="small text-secondary fw-bold mb-1">DÉBIT</label><input type="text" name="bandwidth" class="form-control form-control-sm"></div>
                            <div class="col-6">
                                <label class="small text-secondary fw-bold mb-1">MATÉRIEL</label>
                                <select name="hardware" class="form-select form-select-sm">
                                    <option>Mikrotik</option><option>Arista</option><option>Cisco</option><option>Autre</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4"><label class="small text-secondary fw-bold mb-1">DÉTAILS</label><textarea name="details" class="form-control" rows="2"></textarea></div>

                        <button class="btn btn-primary w-100 fw-bold">DÉPLOYER</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste -->
        <div class="col-lg-8">
            <div class="glass-card h-100">
                <div class="table-responsive">
                    <table class="table table-dark-transparent align-middle mb-0">
                        <thead><tr class="text-secondary small text-uppercase"><th class="ps-4">Client / Offre</th><th>Prix</th><th>Statut</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach($services as $s): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td class="ps-4">
                                    <div class="fw-bold text-white"><?= htmlspecialchars($s['email']) ?></div>
                                    <div class="text-info small"><?= htmlspecialchars($s['name']) ?> <span class="badge bg-secondary ms-1"><?= $s['type'] ?></span></div>
                                    <?php if($s['termination_date']): ?><div class="text-danger small fw-bold mt-1">Résiliation : <?= $s['termination_date'] ?></div><?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= number_format($s['price'], 2) ?> €</div>
                                    <?= Hook::call('admin_service_list_details', ['service' => $s]) ?>
                                </td>
                                <td>
                                    <?php $badge = match($s['status']) { 'active'=>'success', 'suspended'=>'warning', 'pending'=>'info', 'terminated'=>'danger', default=>'secondary' }; ?>
                                    <span class="badge bg-<?= $badge ?> bg-opacity-25 text-<?= $badge ?> border border-<?= $badge ?>"><?= strtoupper($s['status']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="/admin/edit-service?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <form action="/admin/update" method="POST" class="d-inline">
                                            <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                            <?php if($s['status'] == 'active'): ?><button name="status" value="suspended" class="btn btn-sm btn-outline-warning"><i class="fas fa-pause"></i></button>
                                            <?php elseif($s['status'] == 'suspended'): ?><button name="status" value="active" class="btn btn-sm btn-outline-success"><i class="fas fa-play"></i></button>
                                            <?php elseif($s['status'] == 'pending'): ?><button name="status" value="active" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button>
                                            <?php endif; ?>
                                            <?php if($s['status'] != 'terminated'): ?><button name="status" value="terminated" class="btn btn-sm btn-outline-danger" onclick="return confirm('Sûr ?')"><i class="fas fa-times"></i></button><?php endif; ?>
                                        </form>
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
</div>