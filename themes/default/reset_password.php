<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5">
            <div class="glass-card p-4 shadow-lg">
                <h3 class="text-white mb-4 text-center">Nouveau mot de passe</h3>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger border-danger bg-danger bg-opacity-10 text-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if($valid_link): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="text-secondary small">Nouveau mot de passe</label>
                        <input type="password" name="password" class="form-control bg-dark border-secondary text-white" required minlength="8">
                    </div>
                    <div class="mb-4">
                        <label class="text-secondary small">Confirmer le mot de passe</label>
                        <input type="password" name="confirm_password" class="form-control bg-dark border-secondary text-white" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Changer le mot de passe</button>
                </form>
                <?php else: ?>
                    <div class="text-center">
                        <p class="text-muted">Le lien a expiré ou est invalide.</p>
                        <a href="/forgot-password" class="btn btn-primary">Refaire une demande</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>