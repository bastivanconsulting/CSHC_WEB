<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-white">Mot de passe oublié ?</h2>
                <p class="text-muted">Entrez votre email pour recevoir un lien de réinitialisation.</p>
            </div>

            <div class="glass-card p-4 shadow-lg">
                <?php if(isset($error) && $error): ?>
                    <div class="alert alert-danger border-danger bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($success) && $success): ?>
                    <div class="alert alert-success border-success bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/login" class="btn btn-outline-light btn-sm">Retour à la connexion</a>
                    </div>
                <?php else: ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Votre Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control bg-dark border-secondary text-white" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <!-- C'est ici que la variable $captcha est utilisée, et non $valid_link -->
                        <label class="form-label text-secondary small">Sécurité : Combien font <strong class="text-warning"><?= $captcha ?? '1+1' ?></strong> ?</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-warning"><i class="fas fa-robot"></i></span>
                            <input type="number" name="captcha" class="form-control bg-dark border-secondary text-white" placeholder="Résultat" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Envoyer le lien</button>
                </form>
                
                <div class="text-center mt-3">
                    <a href="/login" class="text-muted small text-decoration-none">Annuler et revenir</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>