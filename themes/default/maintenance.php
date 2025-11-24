<?php
// themes/default/maintenance.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Maintenance en cours - CSHC</title>
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
            overflow: hidden;
            position: relative;
        }
        .maintenance-card {
            text-align: center;
            padding: 3rem;
            max-width: 700px;
            z-index: 10;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .icon-container {
            width: 80px;
            height: 80px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }
        .icon-container i {
            font-size: 2.5rem;
            color: #3b82f6;
        }
        .progress-bar-custom {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 2rem;
            overflow: hidden;
            position: relative;
        }
        .progress-value {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 30%;
            background: #3b82f6;
            animation: indeterminate 2s infinite linear;
        }
        @keyframes indeterminate {
            0% { left: -30%; width: 30%; }
            50% { left: 30%; width: 40%; }
            100% { left: 100%; width: 30%; }
        }
    </style>
</head>
<body>

    <!-- Background Elements -->
    <div style="position: absolute; top: -10%; left: -10%; width: 50vw; height: 50vw; background: radial-gradient(circle, rgba(37,99,235,0.1) 0%, rgba(0,0,0,0) 70%); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: -10%; right: -10%; width: 50vw; height: 50vw; background: radial-gradient(circle, rgba(147,51,234,0.05) 0%, rgba(0,0,0,0) 70%); border-radius: 50%;"></div>

    <div class="maintenance-card">
        <div class="icon-container">
            <i class="fas fa-tools"></i>
        </div>
        <h2 class="text-white mb-3 fw-bold">Maintenance Programmée</h2>
        <p class="text-secondary mb-4 fs-5">
            Nous effectuons actuellement une mise à jour de nos infrastructures pour améliorer nos services.
            L'accès sera rétabli dans quelques instants.
        </p>

        <div class="d-flex justify-content-center gap-4 text-sm text-secondary">
            <span><i class="fas fa-server me-2 text-primary"></i>Mise à jour Core</span>
            <span><i class="fas fa-shield-alt me-2 text-primary"></i>Sécurité</span>
        </div>

        <div class="progress-bar-custom">
            <div class="progress-value"></div>
        </div>
    </div>

</body>
</html>
