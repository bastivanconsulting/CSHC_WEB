<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-white">Éditer Client #<?= $user['id'] ?></h3>
                    <a href="/admin" class="btn btn-outline-light btn-sm">Retour</a>
                </div>

                <form action="/admin/edit-user" method="POST">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    
                    <!-- Champs de base (Core) -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-secondary">Email Connexion</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-secondary">Changer Mot de passe (Laisser vide si inchangé)</label>
                            <input type="text" name="password" class="form-control" placeholder="Nouveau mot de passe">
                        </div>
                        <div class="col-md-6">
                            <label class="small text-secondary">Rôle</label>
                            <select name="role" class="form-select">
                                <option value="client" <?= $user['role'] == 'client' ? 'selected' : '' ?>>Client</option>
                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrateur</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-secondary">Statut</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Actif</option>
                                <option value="suspended" <?= $user['status'] == 'suspended' ? 'selected' : '' ?>>Suspendu</option>
                            </select>
                        </div>
                    </div>

                    <!-- [HOOK] Injection des champs Module (Nom, Adresse, Siret...) -->
                    <!-- On passe l'user actuel pour pré-remplir les champs -->
                    <?= Hook::call('admin_user_form_fields', ['user' => $user]) ?>

                    <div class="mt-4 text-end">
                        <button class="btn btn-primary px-4">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>