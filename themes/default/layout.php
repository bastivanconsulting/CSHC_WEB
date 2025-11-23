<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CSHC | CoreSecure Hybrid Connect' ?></title>
    <meta name="description" content="Solution VPN Cloud, VPLS, FTTx haute disponibilité.">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">

    <?= Hook::call('global_head') ?>
</head>
<body>
    <!-- Navigation Client -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/"><i class="fas fa-network-wired me-2"></i>CSHC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="/">Solutions</a></li>
                    <?= Hook::call('navbar_items') ?>
                    <?php if(isset($_SESSION['user'])): ?>
                        <li class="nav-item"><a class="nav-link active" href="/dashboard">Espace Client</a></li>
                        <?php if($_SESSION['user']['role'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link text-warning" href="/admin"><i class="fas fa-shield-halved"></i> Admin</a></li>
                        <?php endif; ?>
                        <li class="nav-item ms-3">
                            <a class="btn btn-outline-light btn-sm rounded-pill px-4" href="/logout">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-3">
                            <a class="btn btn-primary btn-sm rounded-pill" href="/login">Espace Client <i class="fas fa-arrow-right ms-1"></i></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 d-flex flex-column justify-content-center">
        <?= Hook::call('global_alert_banner') ?>
        <?= $content // Le contenu de la vue (home, login...) est injecté ici ?>
    </main>

    <footer class="mt-5">
        <div class="container text-center">
            <p class="mb-0 text-muted small">© <?= date('Y') ?> CSHC par <strong>Bastivan Consulting</strong>. Tous droits réservés.</p>
            <div class="mt-2">
                <span class="badge bg-dark border border-secondary">AS214939</span>
                <span class="badge bg-dark border border-secondary ms-1">Mikrotik</span>
                <span class="badge bg-dark border border-secondary ms-1">Arista</span>
            </div>
            <?= Hook::call('footer_content') ?>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= Hook::call('global_scripts') ?>
</body>
</html>