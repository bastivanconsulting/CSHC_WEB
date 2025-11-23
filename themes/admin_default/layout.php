<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | <?= Settings::get('site_name') ?></title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    
    <?= Hook::call('global_head') ?>
</head>
<body class="bg-body">

    <!-- Sidebar Admin -->
    <aside class="admin-sidebar d-flex flex-column">
        <div class="mb-5 px-2">
            <a href="/admin" class="d-flex align-items-center gap-2 text-white text-decoration-none">
                <div class="bg-primary p-2 rounded-2">
                    <i class="fas fa-shield-alt text-white"></i>
                </div>
                <span class="h5 fw-bold mb-0 tracking-tight">Admin<span class="text-primary">Panel</span></span>
            </a>
        </div>

        <nav class="flex-grow-1">
            <p class="text-xs text-uppercase text-muted fw-bold px-3 mb-2 ls-1">Gestion</p>
            <a href="/admin" class="admin-nav-item <?= $_SERVER['REQUEST_URI'] === '/admin' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <a href="/admin/services" class="admin-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/services') === 0 ? 'active' : '' ?>">
                <i class="fas fa-server"></i> Services
            </a>
            <a href="/admin/users" class="admin-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') === 0 ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Utilisateurs
            </a>

            <p class="text-xs text-uppercase text-muted fw-bold px-3 mb-2 mt-4 ls-1">Système</p>
            <a href="/admin/modules" class="admin-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/modules') === 0 ? 'active' : '' ?>">
                <i class="fas fa-cubes"></i> Modules
            </a>
            <a href="/admin/settings" class="admin-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') === 0 ? 'active' : '' ?>">
                <i class="fas fa-sliders-h"></i> Paramètres
            </a>

            <?= Hook::call('navbar_items') ?>
        </nav>

        <div class="mt-auto border-top border-secondary border-opacity-25 pt-3">
            <a href="/" target="_blank" class="admin-nav-item text-muted">
                <i class="fas fa-external-link-alt"></i> Voir le site
            </a>
            <a href="/logout" class="admin-nav-item text-danger hover-bg-danger-soft">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <!-- Topbar Mobile (Visible uniquement sur mobile) -->
        <div class="d-lg-none mb-4 d-flex align-items-center justify-content-between p-3 glass-card">
            <span class="fw-bold text-white">Admin Panel</span>
            <button class="btn btn-sm btn-outline-light" onclick="document.querySelector('.admin-sidebar').style.display = 'flex';">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Header Page -->
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="h3 fw-bold text-white mb-1">
                    <?php
                        // Titre dynamique simple basé sur l'URL
                        if(strpos($_SERVER['REQUEST_URI'], '/admin/services') === 0) echo "Gestion des Services";
                        elseif(strpos($_SERVER['REQUEST_URI'], '/admin/users') === 0) echo "Utilisateurs";
                        elseif(strpos($_SERVER['REQUEST_URI'], '/admin/settings') === 0) echo "Paramètres";
                        elseif(strpos($_SERVER['REQUEST_URI'], '/admin/modules') === 0) echo "Modules";
                        else echo "Tableau de Bord";
                    ?>
                </h1>
                <p class="text-muted mb-0 text-sm">
                    <?= date('d F Y') ?> • <span class="text-success"><i class="fas fa-circle text-xs me-1"></i> Système Opérationnel</span>
                </p>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-md-block text-end">
                    <div class="text-white text-sm fw-medium"><?= $_SESSION['user']['email'] ?? 'Admin' ?></div>
                    <div class="text-xs text-muted">Super Administrateur</div>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-lg" style="width: 40px; height: 40px;">
                    <?= strtoupper(substr($_SESSION['user']['email'] ?? 'A', 0, 1)) ?>
                </div>
            </div>
        </header>

        <!-- Alertes Globales -->
        <?= Hook::call('global_alert_banner') ?>

        <!-- Contenu Vue -->
        <?= $content ?>

        <footer class="mt-5 text-center text-muted text-xs">
            CSHC Admin v2.0 • Propulsé par Bastivan Framework
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple script pour fermer la sidebar sur mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992 && !e.target.closest('.admin-sidebar') && !e.target.closest('.btn-outline-light')) {
                document.querySelector('.admin-sidebar').style.display = '';
            }
        });
    </script>
    <?= Hook::call('global_scripts') ?>
</body>
</html>
