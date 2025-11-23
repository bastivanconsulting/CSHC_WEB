<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white">Paramètres Généraux</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card glass-card border-0 text-white">
                <div class="card-body p-4">
                    <form action="/admin/settings/save" method="POST">

                        <div class="mb-4">
                            <label for="site_name" class="form-label text-secondary small text-uppercase fw-bold">Nom du Site</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" required>
                            <div class="form-text text-secondary">Ce nom apparaîtra dans le titre des pages et les emails.</div>
                        </div>

                        <div class="mb-4">
                            <label for="site_url" class="form-label text-secondary small text-uppercase fw-bold">URL du Site (WordPress URL)</label>
                            <input type="url" class="form-control bg-dark text-white border-secondary" id="site_url" name="site_url" value="<?= htmlspecialchars($settings['site_url']) ?>" required>
                            <div class="form-text text-secondary">L'URL principale du site (ex: https://dev.cshybridconnect.com/). Doit inclure http:// ou https://.</div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i> Enregistrer les modifications
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
             <div class="card glass-card border-0 text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title text-info mb-3"><i class="fas fa-info-circle me-2"></i>Informations</h5>
                    <p class="card-text text-secondary small">
                        Ces paramètres contrôlent l'identité principale de votre site CSHC.
                        Ils sont stockés en base de données et peuvent être modifiés à tout moment.
                    </p>
                    <p class="card-text text-secondary small">
                        <strong>Version actuelle :</strong> 1.0.0
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
