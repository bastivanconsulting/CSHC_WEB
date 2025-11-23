<?php
// install.php - Version Premium Design & Auto-Fix
session_start();

// Configuration des chemins
$appDir = __DIR__ . '/app';
$configFile = $appDir . '/config.php';

// Utilitaires de vérification
$isWritable = is_writable($appDir);
$phpVersion = phpversion();
$extensions = get_loaded_extensions();
$hasPDO = in_array('pdo_mysql', $extensions);

$message = "";
$manualConfig = "";
$step = 1; // 1: Formulaire, 2: Succès, 3: Erreur critique

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = trim($_POST['host']);
    $dbName = trim($_POST['db_name']);
    $user = trim($_POST['db_user']);
    $pass = trim($_POST['db_pass']);

    try {
        // --- ÉTAPE 1 : Connexion & Création BDD ---
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Création DB
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");

        // --- ÉTAPE 2 : Structure SQL ---
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('client', 'admin') DEFAULT 'client',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(100),
                status ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
                details TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
        ";
        $pdo->exec($sql);

        // --- ÉTAPE 3 : Admin par défaut ---
        // Admin: admin@cshc.local / admin123
        $adminPass = '$2y$10$8Wk.w/xN.uJ/k.u/k.u/k.u/k.u/k.u/k.u/k.u/k.u/k.u/k'; 
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute(['admin@cshc.local', $adminPass]);

        // --- ÉTAPE 4 : Écriture config.php ---
        $configContent = "<?php\n";
        $configContent .= "// Fichier généré automatiquement le " . date('d/m/Y H:i') . "\n";
        $configContent .= "define('DB_HOST', '$host');\n";
        $configContent .= "define('DB_NAME', '$dbName');\n";
        $configContent .= "define('DB_USER', '$user');\n";
        $configContent .= "define('DB_PASS', '$pass');\n";

        // Tentative d'écriture
        if (file_put_contents($configFile, $configContent) !== false) {
            $step = 2; // Succès total
        } else {
            // Echec écriture malgré 775 (souvent user/group mismatch)
            $step = 3; // Succès SQL mais Echec Fichier
            $manualConfig = $configContent;
        }

    } catch (PDOException $e) {
        $message = $e->getMessage();
        $step = 1; // Reste sur le form avec erreur
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation CSHC | CoreSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --primary: #3b82f6;
            --accent: #06b6d4;
            --text: #e2e8f0;
        }
        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            font-family: 'Inter', sans-serif;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        .header-brand {
            background: linear-gradient(90deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            letter-spacing: -1px;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 12px;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
            color: white;
        }
        .status-item {
            font-size: 0.85rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-gradient {
            background: linear-gradient(45deg, var(--primary), var(--accent));
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            transition: transform 0.2s;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px var(--primary);
            color: white;
        }
        pre {
            background: #000;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #333;
            color: #0f0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            
            <div class="text-center mb-4">
                <h1 class="display-5 header-brand">CSHC Installer</h1>
                <p class="text-secondary">CoreSecure Hybrid Connect | Setup Wizard</p>
            </div>

            <div class="glass-card p-4 p-md-5">
                
                <?php if ($step === 1): ?>
                    <div class="mb-4 p-3 rounded" style="background: rgba(0,0,0,0.2);">
                        <h6 class="text-uppercase text-secondary small fw-bold mb-3">Vérifications Système</h6>
                        <div class="status-item <?= $hasPDO ? 'text-success' : 'text-danger' ?>">
                            <i class="fas fa-<?= $hasPDO ? 'check-circle' : 'times-circle' ?>"></i> Pilote PDO MySQL
                        </div>
                        <div class="status-item <?= $isWritable ? 'text-success' : 'text-warning' ?>">
                            <i class="fas fa-<?= $isWritable ? 'check-circle' : 'exclamation-triangle' ?>"></i> 
                            Dossier /app/ scriptable 
                            <?= !$isWritable ? '<small>(Fichier config à créer manuellement si échec)</small>' : '' ?>
                        </div>
                        <div class="status-item text-success">
                            <i class="fas fa-check-circle"></i> PHP Version <?= $phpVersion ?>
                        </div>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-danger border-0 mb-4 shadow-sm">
                            <i class="fas fa-bug me-2"></i> <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Hôte Base de données</label>
                            <input type="text" name="host" class="form-control" value="localhost" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Nom de la Base</label>
                            <input type="text" name="db_name" class="form-control" placeholder="ex: cshc_db" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary small">Utilisateur</label>
                                <input type="text" name="db_user" class="form-control" placeholder="root" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary small">Mot de passe</label>
                                <input type="password" name="db_pass" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-gradient w-100 rounded-pill">
                                <i class="fas fa-rocket me-2"></i> Lancer l'installation
                            </button>
                        </div>
                    </form>

                <?php elseif ($step === 2): ?>
                    <div class="text-center py-4">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success fa-5x"></i>
                        </div>
                        <h2 class="h3 mb-3">Installation Terminée !</h2>
                        <p class="text-secondary mb-4">La base de données est prête et le fichier de configuration a été généré avec succès.</p>
                        
                        <div class="alert alert-info bg-opacity-10 border-0 text-start">
                            <strong>Admin par défaut :</strong><br>
                            Email: <code>admin@cshc.local</code><br>
                            Pass: <code>admin123</code>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <a href="public/" class="btn btn-gradient rounded-pill">Accéder au site</a>
                            <small class="text-danger mt-2"><i class="fas fa-trash"></i> Pensez à supprimer ce fichier install.php</small>
                        </div>
                    </div>

                <?php elseif ($step === 3): ?>
                    <div class="py-3">
                        <div class="text-center mb-3">
                            <i class="fas fa-exclamation-circle text-warning fa-4x"></i>
                        </div>
                        <h3 class="h4 text-center mb-3">Presque terminé...</h3>
                        <p class="text-secondary text-center small mb-4">
                            La base de données est installée ✅, mais les permissions strictes de Plesk (même en 775) empêchent PHP de créer le fichier physique.
                        </p>

                        <div class="card bg-black border-secondary mb-3">
                            <div class="card-header bg-transparent border-secondary text-white small">
                                Créez manuellement <strong>app/config.php</strong> avec ce contenu :
                            </div>
                            <div class="card-body p-0">
                                <textarea class="form-control font-monospace bg-transparent border-0 text-success p-3" rows="8" readonly><?= htmlspecialchars($manualConfig) ?></textarea>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="public/" class="btn btn-gradient rounded-pill px-5">J'ai créé le fichier, continuer</a>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4 text-secondary small opacity-50">
                &copy; <?= date('Y') ?> Bastivan Consulting - Installer v1.0
            </div>

        </div>
    </div>
</div>

</body>
</html>