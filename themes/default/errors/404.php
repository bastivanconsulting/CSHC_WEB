<?php
// themes/default/errors/404.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page Introuvable - CSHC</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bs-body-bg: #020617;
            --bs-body-color: #cbd5e1;
            --bs-font-sans-serif: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            text-align: center;
            padding: 3rem;
            max-width: 600px;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }
        .btn-home {
            background: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.2);
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        .btn-home:hover {
            background: rgba(245, 158, 11, 0.2);
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="error-card">
        <div class="error-code">404</div>
        <h2 class="text-white mb-3">Page Introuvable</h2>
        <p class="text-secondary mb-5">
            Désolé, la page que vous recherchez semble avoir été déplacée ou n'existe plus.
        </p>

        <a href="/" class="btn-home">
            <i class="fas fa-arrow-left me-2"></i> Retour à l'accueil
        </a>
    </div>

</body>
</html>
