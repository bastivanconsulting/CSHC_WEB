<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration CSHC</title>
    
    <!-- CSS Global -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    
    <!-- Override CSS spécifique pour l'Admin -->
    <style>
        /* Fond plus sombre et uni pour l'admin */
        body { 
            background-image: none !important; 
            background-color: #0f172a !important; 
        }
        
        /* Navbar Admin distincte */
        .admin-navbar {
            background: #1e293b !important; /* Slate-800 */
            border-bottom: 1px solid #334155;
        }
        
        /* Highlight des liens actifs */
        .nav-link.active {
            background: rgba(245, 158, 11, 0.2) !important; /* Orange/Warning tint */
            color: #fbbf24 !important; /* Amber-400 */
        }
    </style>

    <!-- [HOOK] Pour que les modules Admin injectent leur CSS -->
    <?= Hook::call('global_head') ?>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="/admin">
                <i class="fas fa-shield-alt text-warning me-2"></i><?= Settings::get('site_name') ?> <span class="text-muted small">Admin</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNav">
                <!-- Menu Principal -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/modules"><i class="fas fa-cubes me-1"></i> Modules</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/settings"><i class="fas fa-sliders-h me-1"></i> Paramètres</a>
                    </li>
                    <!-- [HOOK] Les modules peuvent ajouter des liens ici -->
                    <?= Hook::call('navbar_items') ?>
                </ul>

                <!-- Menu Droite -->
                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-outline-secondary border-0" href="/" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i> Voir le site
                        </a>
                    </li>
                    
                    <!-- Dropdown User -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle btn btn-dark bg-opacity-25 text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-2"></i>
                            <?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['email']) : 'Admin' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-secondary">
                            <li><h6 class="dropdown-header">Session Administrateur</h6></li>
                            <li><a class="dropdown-item" href="/admin"><i class="fas fa-cogs me-2 text-warning"></i>Gestion</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu Principal -->
    <main class="flex-grow-1 py-4">
        <!-- Alerte globale (Maintenance, etc.) -->
        <div class="container-fluid px-4">
            <?= Hook::call('global_alert_banner') ?>
        </div>

        <!-- Injection du contenu de la vue -->
        <?= $content ?>
    </main>

    <!-- Footer Admin discret -->
    <footer class="py-3 mt-auto" style="background: #0f172a; border-top: 1px solid #1e293b;">
        <div class="container-fluid px-4 text-center">
            <small class="text-muted">
                CSHC Admin Panel &copy; <?= date('Y') ?> • <span class="font-monospace">v2.0 Modular Core</span>
            </small>
            <!-- [HOOK] Footer Content -->
            <?= Hook::call('footer_content') ?>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- [HOOK] Scripts JS des modules -->
    <?= Hook::call('global_scripts') ?>
</body>
</html>