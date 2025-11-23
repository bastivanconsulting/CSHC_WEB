<div class="container-fluid px-4 py-4">
    <h2 class="fw-bold text-white mb-4"><i class="fas fa-users me-2 text-info"></i>Gestion des Utilisateurs</h2>

    <div class="row g-4">
        <!-- Création -->
        <div class="col-lg-4">
            <div class="glass-card p-4">
                <h5 class="text-white mb-3"><i class="fas fa-user-plus me-2 text-success"></i>Nouveau Client</h5>
                <form action="/admin/create-user" method="POST">
                    <div class="mb-3">
                        <label class="small text-secondary">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary">Mot de passe provisoire</label>
                        <input type="text" name="password" class="form-control" value="<?= bin2hex(random_bytes(4)) ?>" required>
                    </div>
                    
                    <!-- Hook Module Profil -->
                    <?= Hook::call('admin_user_form_fields') ?>

                    <button class="btn btn-success w-100 mt-3">Créer le compte</button>
                </form>
            </div>
        </div>
        
        <!-- Liste -->
        <div class="col-lg-8">
            <div class="glass-card">
                <div class="table-responsive">
                    <table class="table table-dark-transparent align-middle mb-0">
                        <thead><tr><th class="ps-4">Email</th><th>Créé le</th><th>Statut</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="text-muted small"><?= $u['created_at'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $u['status'] == 'active' ? 'success' : 'danger' ?>"><?= ucfirst($u['status']) ?></span>
                                </td>
                                <td>
                                    <?php if($u['role'] !== 'admin'): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="/admin/edit-user?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-user-edit"></i></a>
                                        <form action="/admin/suspend-user" method="POST">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <?php if($u['status'] == 'active'): ?>
                                                <button name="status" value="suspended" class="btn btn-sm btn-danger"><i class="fas fa-pause"></i></button>
                                            <?php else: ?>
                                                <button name="status" value="active" class="btn btn-sm btn-success"><i class="fas fa-play"></i></button>
                                            <?php endif; ?>
                                        </form>
                                        <?= Hook::call('admin_user_actions', ['user' => $u]) ?>
                                    </div>
                                    <?php endif; ?>
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