<?php $title = "Connexion | " . Settings::get('site_name'); ?>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="w-100" style="max-width: 420px;">

        <div class="text-center mb-4">
            <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3 animate-bounce">
                <i class="fas fa-network-wired text-primary fa-2x"></i>
            </div>
            <h1 class="h3 fw-bold text-white">Bienvenue</h1>
            <p class="text-muted">Connectez-vous à votre espace client</p>
        </div>

        <div class="glass-card shadow-lg animate-fade-in-up">
            <div class="glass-card-body p-4 p-md-5">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger mb-4 d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login">
                    <div class="mb-4">
                        <label class="form-label">Adresse Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-muted"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="nom@entreprise.com" required autofocus style="border-left: none;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Mot de passe</label>
                            <a href="/forgot-password" class="text-xs text-primary text-decoration-none">Oublié ?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-muted"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required style="border-left: none;">
                        </div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Se connecter <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <div class="text-center text-sm text-muted">
                        Pas encore client ? <a href="#" class="text-white fw-bold">Nous contacter</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4 text-muted text-xs">
            &copy; <?= date('Y') ?> <?= Settings::get('site_name') ?>. Sécurisé par SSL.
        </div>

    </div>
</div>

<style>
/* Petites animations CSS inline pour cette page */
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.animate-bounce { animation: bounce 3s infinite ease-in-out; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up { animation: fadeInUp 0.6s ease-out; }
</style>
