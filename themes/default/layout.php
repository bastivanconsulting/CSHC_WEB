<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? Settings::get('site_name') . ' | Infrastructure Cloud' ?></title>
    <meta name="description" content="Solution VPN Cloud, VPLS, FTTx haute disponibilité.">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Font import moved to style.css for cleaner HTML -->
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">

    <?= Hook::call('global_head') ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= Settings::get('site_url') ?>">
                <i class="fas fa-cube me-2 text-primary"></i><?= Settings::get('site_name') ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="/">Solutions</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Infrastructure</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Support</a></li>
                    <?= Hook::call('navbar_items') ?>

                    <?php if(isset($_SESSION['user'])): ?>
                        <li class="nav-item ms-3">
                            <a class="nav-link active" href="/dashboard">
                                <i class="fas fa-user-circle me-1"></i> Espace Client
                            </a>
                        </li>
                        <?php if($_SESSION['user']['role'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link text-warning" href="/admin"><i class="fas fa-shield-halved"></i> Admin</a></li>
                        <?php endif; ?>
                        <li class="nav-item ms-2">
                            <a class="btn btn-outline-light btn-sm" href="/logout">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-4 border-start border-secondary ps-4 d-none d-lg-block"></li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-2" href="/login">Manager <i class="fas fa-arrow-right ms-1"></i></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        <?= Hook::call('global_alert_banner') ?>
        <?= $content ?>
    </main>

    <!-- Mega Footer -->
    <footer>
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-4">
                    <h5 class="text-white fw-bold mb-3"><i class="fas fa-cube me-2 text-primary"></i><?= Settings::get('site_name') ?></h5>
                    <p class="small text-muted mb-4">
                        Infrastructure Cloud & Réseau haute disponibilité opérée sur l'AS214939.
                        Nous connectons vos projets avec une sécurité et une performance sans compromis.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge badge-tech">AS214939</span>
                        <span class="badge badge-tech">ISO 27001</span>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">Solutions</h6>
                    <a href="#" class="footer-link">VPN Cloud HA</a>
                    <a href="#" class="footer-link">VPLS Privé</a>
                    <a href="#" class="footer-link">Anti-DDoS</a>
                    <a href="#" class="footer-link">Colocation</a>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">Compagnie</h6>
                    <a href="#" class="footer-link">À propos</a>
                    <a href="#" class="footer-link">Datacenters</a>
                    <a href="#" class="footer-link">Carrières</a>
                    <a href="#" class="footer-link">Partenaires</a>
                </div>
                <div class="col-lg-4">
                    <h6 class="footer-heading">Support & Légal</h6>
                    <div class="d-flex align-items-center mb-3 text-muted">
                        <i class="fas fa-phone-alt me-3"></i>
                        <span>+33 1 23 45 67 89</span>
                    </div>
                    <div class="d-flex align-items-center mb-4 text-muted">
                        <i class="fas fa-envelope me-3"></i>
                        <span>support@cshc.network</span>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-secondary hover-white"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-secondary hover-white"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-secondary hover-white"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <div class="row mt-5 pt-4 border-top border-white border-opacity-10">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small text-muted mb-0">© <?= date('Y') ?> CSHC. Propulsé par <strong>Bastivan Consulting</strong>.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-muted small me-3 text-decoration-none">CGV</a>
                    <a href="#" class="text-muted small me-3 text-decoration-none">Politique de confidentialité</a>
                    <a href="#" class="text-muted small text-decoration-none">SLA</a>
                </div>
            </div>
            <?= Hook::call('footer_content') ?>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= Hook::call('global_scripts') ?>
</body>
</html>