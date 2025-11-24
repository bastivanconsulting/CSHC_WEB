<?php
// themes/default/errors/debug.php
// $e is the Exception object
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Erreur Système - CSHC Debug</title>
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
        }
        .debug-header {
            background: rgba(220, 38, 38, 0.1);
            border-bottom: 1px solid rgba(220, 38, 38, 0.3);
            padding: 2rem 0;
            backdrop-filter: blur(10px);
        }
        .exception-title {
            color: #ef4444;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .exception-message {
            font-size: 1.25rem;
            color: #fff;
            margin-top: 0.5rem;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .trace-item {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
        }
        .trace-item:last-child { border-bottom: none; }
        .trace-file { color: #60a5fa; font-weight: 600; }
        .trace-line { color: #f59e0b; }
        .trace-class { color: #a5b4fc; }
        .trace-function { color: #34d399; }
        .env-badge {
            background: #ef4444;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <div class="debug-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-bug fa-2x text-danger me-3"></i>
                    <h5 class="mb-0 text-uppercase tracking-wider text-danger">Mode Debug Actif</h5>
                </div>
                <span class="env-badge">Erreur 500</span>
            </div>

            <div class="exception-title">
                <?= get_class($e) ?>
            </div>
            <div class="exception-message">
                <?= htmlspecialchars($e->getMessage()) ?>
            </div>
            <div class="mt-3 font-monospace text-secondary small">
                <i class="fas fa-file-code me-2"></i><?= $e->getFile() ?> : <span class="text-warning"><?= $e->getLine() ?></span>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <h6 class="text-uppercase text-secondary mb-4 small fw-bold tracking-wider">Stack Trace</h6>

        <div class="glass-card">
            <?php foreach ($e->getTrace() as $i => $trace): ?>
            <div class="trace-item d-flex">
                <div class="me-3 text-secondary opacity-50" style="width: 30px;">#<?= $i ?></div>
                <div class="flex-grow-1">
                    <?php if (isset($trace['file'])): ?>
                        <div class="mb-1">
                            <span class="trace-file"><?= $trace['file'] ?></span> : <span class="trace-line"><?= $trace['line'] ?></span>
                        </div>
                    <?php endif; ?>
                    <div>
                        <?php if (isset($trace['class'])): ?>
                            <span class="trace-class"><?= $trace['class'] ?></span><?= $trace['type'] ?>
                        <?php endif; ?>
                        <span class="trace-function"><?= $trace['function'] ?>()</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <h6 class="text-uppercase text-secondary mb-4 small fw-bold tracking-wider mt-5">Environnement</h6>
        <div class="glass-card p-4">
            <div class="row">
                <div class="col-md-3 text-secondary">PHP Version</div>
                <div class="col-md-9 text-white font-monospace"><?= phpversion() ?></div>

                <div class="col-md-3 text-secondary mt-2">Server Software</div>
                <div class="col-md-9 text-white font-monospace mt-2"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></div>

                <div class="col-md-3 text-secondary mt-2">Request URI</div>
                <div class="col-md-9 text-white font-monospace mt-2"><?= $_SERVER['REQUEST_URI'] ?? '' ?></div>

                <div class="col-md-3 text-secondary mt-2">Method</div>
                <div class="col-md-9 text-white font-monospace mt-2"><?= $_SERVER['REQUEST_METHOD'] ?? '' ?></div>
            </div>
        </div>
    </div>

</body>
</html>
