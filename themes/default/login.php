<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-6 col-lg-4">
            
            <!-- En-tête avec Logo -->
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle p-4 mb-3 border border-primary border-opacity-25 shadow-lg" style="width: 80px; height: 80px;">
                    <i class="fas fa-network-wired fa-2x text-primary"></i>
                </div>
                <h1 class="h3 fw-bold text-white mb-1">CSHC Connect</h1>
                <p class="text-secondary small">Portail d'accès sécurisé</p>
            </div>

            <!-- Carte de Connexion -->
            <div class="glass-card p-4 p-sm-5 shadow-lg position-relative overflow-hidden border-0">
                
                <!-- Petit effet de lumière en fond -->
                <div class="position-absolute top-0 start-50 translate-middle bg-primary opacity-25" style="width: 120px; height: 120px; filter: blur(50px); pointer-events: none;"></div>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger d-flex align-items-center mb-4 rounded-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div class="small fw-bold"><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="position-relative z-1">
                    
                    <!-- Email avec Floating Label -->
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control bg-dark border-secondary text-white" id="floatingInput" placeholder="name@example.com" required style="background: rgba(15, 23, 42, 0.5) !important;">
                        <label for="floatingInput" class="text-secondary">Email Professionnel</label>
                    </div>

                    <!-- Mot de passe avec Floating Label -->
                    <div class="form-floating mb-4">
                        <input type="password" name="password" class="form-control bg-dark border-secondary text-white" id="floatingPassword" placeholder="Password" required style="background: rgba(15, 23, 42, 0.5) !important;">
                        <label for="floatingPassword" class="text-secondary">Mot de passe</label>
                    </div>

                    <!-- Options -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" id="rememberMe">
                            <label class="form-check-label text-secondary small" for="rememberMe">
                                Se souvenir
                            </label>
                        </div>
                        <a href="/forgot-password" class="text-primary small text-decoration-none">Mot de passe oublié ?</a>
                    </div>

                    <!-- Bouton Action -->
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-lg text-uppercase mb-3" style="letter-spacing: 1px;">
                        <i class="fas fa-lock me-2"></i> Se connecter
                    </button>
                </form>
                
                <!-- Bloc SSO -->
                <?php 
                    $sso_providers = Hook::filter('get_sso_login_options', []);
                    
                    if (!empty($sso_providers)): 
                ?>
                    <div class="d-flex align-items-center my-3">
                        <hr class="flex-grow-1 border-secondary opacity-25">
                        <span class="px-3 text-muted small">OU SSO</span>
                        <hr class="flex-grow-1 border-secondary opacity-25">
                    </div>

                    <!-- Boutons SSO Dynamiques -->
                    <?php foreach($sso_providers as $provider): ?>
                        <?php
                            // Déterminer le type de SSO pour l'icône/le style (simulé)
                            $providerType = $provider['sso_type'] ?? 'OIDC';
                            $icon = ($providerType === 'SAML') ? 'fas fa-id-card' : 'fas fa-fingerprint';
                        ?>
                        <!-- Utilisation de btn-outline-light pour la visibilité sur fond sombre -->
                        <a href="/auth/sso/login/<?= $provider['id'] ?>" class="btn btn-outline-light w-100 mb-2 fw-bold d-flex align-items-center justify-content-center py-2">
                            <i class="<?= $icon ?> me-2"></i> 
                            SSO via <?= htmlspecialchars($provider['name']) ?>
                        </a>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

            <!-- Pied de page discret -->
            <div class="text-center mt-4">
                <p class="text-muted small opacity-75 mb-1">
                    <i class="fas fa-shield-alt me-1 text-success"></i> 
                    Accès chiffré & audité.
                </p>
                <p class="text-muted" style="font-size: 0.7rem;">
                    IP: <?= $_SERVER['REMOTE_ADDR'] ?> • ID: <?= uniqid() ?>
                </p>
            </div>

        </div>
    </div>
</div>