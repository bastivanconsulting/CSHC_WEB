<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white">
            <i class="fas fa-cog text-warning me-2"></i>Configuration OVH
        </h2>
        <a href="/admin/ovh" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card glass-card border-0 text-white">
                <div class="card-body p-4">
                    <form action="/admin/ovh/settings" method="POST">

                        <div class="mb-4">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Endpoint</label>
                            <select name="ovh_endpoint" class="form-select bg-dark text-white border-secondary">
                                <option value="ovh-eu" <?= $settings['ovh_endpoint'] == 'ovh-eu' ? 'selected' : '' ?>>OVH Europe (ovh-eu)</option>
                                <option value="ovh-ca" <?= $settings['ovh_endpoint'] == 'ovh-ca' ? 'selected' : '' ?>>OVH North America (ovh-ca)</option>
                                <option value="kimsufi-eu" <?= $settings['ovh_endpoint'] == 'kimsufi-eu' ? 'selected' : '' ?>>Kimsufi Europe</option>
                                <option value="soyoustart-eu" <?= $settings['ovh_endpoint'] == 'soyoustart-eu' ? 'selected' : '' ?>>So You Start Europe</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Application Key (AK)</label>
                            <input type="text" name="ovh_ak" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($settings['ovh_ak'] ?? '') ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Application Secret (AS)</label>
                            <input type="password" name="ovh_as" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($settings['ovh_as'] ?? '') ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Consumer Key (CK)</label>
                            <input type="text" name="ovh_ck" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($settings['ovh_ck'] ?? '') ?>" required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card glass-card border-0 text-white">
                <div class="card-body">
                    <h5 class="card-title text-info mb-3"><i class="fas fa-info-circle me-2"></i>Aide</h5>
                    <p class="small text-secondary">
                        Pour obtenir vos clés API, rendez-vous sur la page de création de tokens d'OVH :
                    </p>
                    <a href="https://eu.api.ovh.com/createToken/" target="_blank" class="btn btn-sm btn-outline-info w-100 mb-3">
                        <i class="fas fa-external-link-alt me-2"></i>Créer un Token OVH
                    </a>
                    <p class="small text-secondary mb-0">
                        Assurez-vous d'accorder les droits suivants :
                        <ul class="list-unstyled mt-2">
                            <li><span class="badge bg-secondary">GET</span> <code>/ip</code></li>
                            <li><span class="badge bg-secondary">GET</span> <code>/ip/*</code></li>
                            <li><span class="badge bg-secondary">POST</span> <code>/ip/*</code></li>
                            <li><span class="badge bg-secondary">PUT</span> <code>/ip/*</code></li>
                            <li><span class="badge bg-secondary">DELETE</span> <code>/ip/*</code></li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
