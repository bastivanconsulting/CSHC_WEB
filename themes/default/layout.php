<!DOCTYPE html>
<html lang="fr" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? Settings::get('site_name') . ' | CoreSecure Hybrid Connect' ?></title>
    <meta name="description" content="Solution VPN Cloud, VPLS, FTTx haute disponibilité.">
    
    <!-- Fonts & Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Style Global -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">

    <?= Hook::call('global_head') ?>
</head>
<body class="d-flex flex-column h-100">

    <!-- Navigation Moderne -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= Settings::get('site_url') ?>">
                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                    <i class="fas fa-network-wired text-primary"></i>
                </div>
                <span><?= Settings::get('site_name') ?></span>
            </a>

            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars fa-lg"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="/">Solutions</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#features">Infrastructure</a></li>
                    <?= Hook::call('navbar_items') ?>

                    <li class="nav-item ms-lg-3 border-start border-secondary opacity-25 d-none d-lg-block" style="height: 24px;"></li>

                    <?php if(isset($_SESSION['user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    <?= strtoupper(substr($_SESSION['user']['email'], 0, 1)) ?>
                                </div>
                                <span class="d-none d-lg-block">Mon Compte</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark border-0 shadow-lg glass-card p-2 mt-2" style="background: var(--bg-card);">
                                <li><a class="dropdown-item rounded" href="/dashboard"><i class="fas fa-columns me-2 text-primary"></i> Dashboard</a></li>
                                <?php if($_SESSION['user']['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item rounded" href="/admin"><i class="fas fa-shield-alt me-2 text-warning"></i> Administration</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                                <li><a class="dropdown-item rounded text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-outline-light btn-sm px-4 rounded-pill" href="/login">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm px-4 rounded-pill shadow-lg" href="#contact">Commencer</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Espace pour la navbar fixe -->
    <div style="height: 76px;"></div>

    <main class="flex-grow-1">
        <!-- Alerte Globale -->
        <div class="container mt-3">
            <?= Hook::call('global_alert_banner') ?>
        </div>

        <!-- Contenu Page -->
        <?= $content ?>
    </main>

    <!-- Footer Pro -->
    <footer>
        <div class="container">
            <div class="row g-5 mb-5">
                <div class="col-lg-4">
                    <h5 class="text-white mb-4 d-flex align-items-center gap-2">
                        <i class="fas fa-network-wired text-primary"></i> <?= Settings::get('site_name') ?>
                    </h5>
                    <p class="small text-muted mb-4">
                        Opérateur d'infrastructure Cloud & Réseau nouvelle génération.
                        Nous connectons vos équipes avec sécurité et haute disponibilité.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-secondary hover-white"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-secondary hover-white"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-secondary hover-white"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="text-white mb-3 text-uppercase small fw-bold ls-1">Solutions</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="#" class="footer-link">Cloud VPN</a></li>
                        <li><a href="#" class="footer-link">VPLS Layer 2</a></li>
                        <li><a href="#" class="footer-link">Transit IP</a></li>
                        <li><a href="#" class="footer-link">Anti-DDoS</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="text-white mb-3 text-uppercase small fw-bold ls-1">Support</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="#" class="footer-link">Status Page</a></li>
                        <li><a href="#" class="footer-link">Documentation</a></li>
                        <li><a href="#" class="footer-link">API Reference</a></li>
                        <li><a href="#" class="footer-link">Ticket Support</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-white mb-3 text-uppercase small fw-bold ls-1">Réseau</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10">AS214939</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10">IPv6 Ready</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10">Anycast DNS</span>
                    </div>
                    <p class="small text-muted">
                        Infrastructures hébergées en France et en Europe.
                        Conforme RGPD.
                    </p>
                </div>
            </div>
            <div class="border-top border-secondary border-opacity-10 pt-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                <small class="text-muted mb-2 mb-md-0">
                    &copy; <?= date('Y') ?> <strong>Bastivan Consulting</strong>. Tous droits réservés.
                </small>
                <div class="d-flex gap-4 small">
                    <a href="#" class="text-muted text-decoration-none">Mentions Légales</a>
                    <a href="#" class="text-muted text-decoration-none">CGV</a>
                    <a href="#" class="text-muted text-decoration-none">Confidentialité</a>
                </div>
            </div>
            <?= Hook::call('footer_content') ?>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= Hook::call('global_scripts') ?>
</body>
</html>
