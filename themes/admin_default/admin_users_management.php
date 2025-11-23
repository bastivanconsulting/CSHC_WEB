<div class="container mt-5">
    <h2 class="fw-bold text-white mb-4"><i class="fas fa-user-shield me-2 text-info"></i>Gestion des Administrateurs</h2>
    <a href="/admin" class="btn btn-outline-light btn-sm mb-4">Retour au Dashboard</a>

    <?= $message ?>

    <div class="row g-4">
        <!-- Bloc Création Admin -->
        <div class="col-lg-5">
            <div class="glass-card p-4">
                <h5 class="text-white mb-3"><i class="fas fa-plus-circle me-2 text-success"></i>Créer un Compte Administrateur</h5>
                <form method="POST">
                    <input type="hidden" name="create_admin" value="1">
                    <div class="mb-3">
                        <label class="small text-secondary">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary">Mot de passe initial</label>
                        <input type="text" name="password" class="form-control" value="<?= bin2hex(random_bytes(6)) ?>" required>
                    </div>
                    <button class="btn btn-success w-100 mt-3">Créer l'Administrateur</button>
                </form>
            </div>
        </div>

        <!-- Liste des Administrateurs -->
        <div class="col-lg-7">
            <div class="glass-card p-0">
                 <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                    <h5 class="text-white mb-0">Comptes Super-Admin Actuels</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark-transparent align-middle mb-0">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th class="ps-4">Email</th>
                                <th>Créé le</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($admins as $a): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-white">
                                    <?= htmlspecialchars($a['email']) ?>
                                    <?php if($a['id'] === $_SESSION['user']['id']): ?>
                                        <span class="badge bg-primary ms-2">Vous</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= substr($a['created_at'], 0, 10) ?></td>
                                <td>
                                    <?php if(count($admins) > 1 && $a['id'] !== $_SESSION['user']['id']): ?>
                                        <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ?')">
                                            <input type="hidden" name="delete_admin" value="1">
                                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Protection</span>
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