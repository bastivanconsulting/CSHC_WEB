<!DOCTYPE html>
<html lang="fr" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé - CSHC</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bs-body-font-family: 'Plus Jakarta Sans', sans-serif;
            --primary-color: #2563eb;
            --dark-bg: #020617;
            --card-bg: rgba(30, 41, 59, 0.7);
        }
        body {
            background-color: var(--dark-bg);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .bg-gradient-danger {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        }
        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: rgba(239, 68, 68, 0.5);
            animation: scan 3s linear infinite;
            z-index: 0;
            pointer-events: none;
        }
        @keyframes scan {
            0% { top: 0%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }
    </style>
</head>
<body>

    <div class="scan-line"></div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="glass-card p-5 text-center position-relative overflow-hidden">

                    <!-- Icon -->
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle p-4 border border-danger border-opacity-25 shadow-lg" style="width: 100px; height: 100px;">
                            <i class="fas fa-shield-halved fa-3x text-danger"></i>
                        </div>
                    </div>

                    <h1 class="h2 fw-bold text-white mb-2">Accès Refusé</h1>
                    <p class="text-secondary mb-4">Votre adresse IP a été bloquée par notre pare-feu de sécurité.</p>

                    <div class="bg-black bg-opacity-25 rounded p-3 mb-4 text-start font-monospace small border border-secondary border-opacity-25">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">IP:</span>
                            <span class="text-white"><?= htmlspecialchars($ip ?? 'Unknown') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Raison:</span>
                            <span class="text-danger"><?= htmlspecialchars($reason ?? 'Security Violation') ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary">ID Incident:</span>
                            <span class="text-white"><?= uniqid('WAF-') ?></span>
                        </div>
                    </div>

                    <p class="text-secondary small opacity-75">
                        Si vous pensez qu'il s'agit d'une erreur, veuillez contacter le support technique avec votre ID d'incident.
                    </p>

                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                         <span class="badge bg-dark border border-secondary text-secondary">CSHC WAF Protection</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>
