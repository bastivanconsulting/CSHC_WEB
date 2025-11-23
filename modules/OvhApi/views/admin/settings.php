<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-white mb-0">Configuration API OVH</h2>
    </div>

    <div class="card bg-dark border-secondary shadow-lg">
        <div class="card-header bg-transparent border-secondary">
            <h5 class="card-title text-white mb-0"><i class="fas fa-key me-2 text-warning"></i>Identifiants API</h5>
        </div>
        <div class="card-body text-white">
            <p class="text-muted mb-4">
                Pour configurer le module, vous devez créer des identifiants API OVH avec les droits d'accès nécessaires (IP, Firewall).
                <br>
                <a href="https://eu.api.ovh.com/createToken/" target="_blank" class="text-info">Créer un token API OVH</a>
            </p>

            <form action="/admin/ovh/settings" method="POST">
                <div class="mb-3">
                    <label class="form-label">Application Key (AK)</label>
                    <input type="text" name="ovh_app_key" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($settings['ovh_app_key'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Application Secret (AS)</label>
                    <input type="password" name="ovh_app_secret" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($settings['ovh_app_secret'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Consumer Key (CK)</label>
                    <input type="text" name="ovh_consumer_key" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($settings['ovh_consumer_key'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Endpoint</label>
                    <select name="ovh_endpoint" class="form-select bg-dark text-white border-secondary">
                        <option value="ovh-eu" <?= ($settings['ovh_endpoint'] == 'ovh-eu') ? 'selected' : '' ?>>OVH Europe (ovh-eu)</option>
                        <option value="ovh-ca" <?= ($settings['ovh_endpoint'] == 'ovh-ca') ? 'selected' : '' ?>>OVH Canada (ovh-ca)</option>
                        <option value="ovh-us" <?= ($settings['ovh_endpoint'] == 'ovh-us') ? 'selected' : '' ?>>OVH US (ovh-us)</option>
                    </select>
                </div>

                <hr class="border-secondary">

                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
            </form>
        </div>
    </div>
</div>
