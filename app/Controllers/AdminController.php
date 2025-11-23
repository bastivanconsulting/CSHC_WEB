<?php
class AdminController {
    
    private function checkAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /login'); exit;
        }
    }

    // --- 1. PAGE DASHBOARD (Accueil) ---
    public function index() {
        $this->checkAdmin();
        $db = Database::connect();

        // Calcul des statistiques pour le Dashboard
        $stats = [
            'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn(),
            'active_services' => $db->query("SELECT COUNT(*) FROM services WHERE status='active'")->fetchColumn(),
            'pending_services' => $db->query("SELECT COUNT(*) FROM services WHERE status='pending'")->fetchColumn(),
            'monthly_revenue' => $db->query("SELECT SUM(price) FROM services WHERE status='active'")->fetchColumn() ?: 0,
            'recent_services' => $db->query("SELECT s.*, u.email FROM services s JOIN users u ON s.user_id = u.id ORDER BY s.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC)
        ];

        // Affiche la vue 'admin.php' (Le Dashboard avec les cartes)
        View::render('admin', ['stats' => $stats], 'admin');
    }

    // --- 2. PAGE SERVICES (Liste + Ajout) ---
    public function services() {
        $this->checkAdmin();
        $db = Database::connect();

        // Traitement du formulaire "Ajout Service"
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
            // Hook pour les modules (Mobile, Fibre...)
            $customData = Hook::filter('save_service_data', [], ['post' => $_POST]);
            $customJson = json_encode($customData);
            
            // CORRECTION CRITIQUE: Assurer que le prix est un nombre (0.00 si vide)
            $price = floatval($_POST['price'] ?? 0); 
            
            $sql = "INSERT INTO services 
                    (user_id, name, type, ip_address, location, bandwidth, carrier, hardware, details, price, engagement, custom_fields) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            // CORRECTION ROBUSTESSE: Utilisation de ?? null pour les autres champs potentiellement vides
            $stmt->execute([
                $_POST['user_id'], 
                $_POST['name'], 
                $_POST['type'], 
                $_POST['ip_address'] ?? null,
                $_POST['location'] ?? null,
                $_POST['bandwidth'] ?? null,
                $_POST['carrier'] ?? null,
                $_POST['hardware'] ?? null,
                $_POST['details'] ?? null,
                $price, // Utilisation de la variable corrigée
                $_POST['engagement'], 
                $customJson
            ]);
            
            header('Location: /admin/services'); exit;
        }

        // Récupération des données pour l'affichage
        $users = $db->query("SELECT * FROM users WHERE role != 'admin' ORDER BY email ASC")->fetchAll(PDO::FETCH_ASSOC);
        $services = $db->query("SELECT s.*, u.email FROM services s JOIN users u ON s.user_id = u.id ORDER BY s.id DESC")->fetchAll(PDO::FETCH_ASSOC);

        // Affiche la vue 'admin_services.php'
        View::render('admin_services', ['users' => $users, 'services' => $services], 'admin');
    }

    // --- 3. PAGE UTILISATEURS (Liste + Création) ---
    public function users() {
        $this->checkAdmin();
        $db = Database::connect();
        
        // Récupération de tous les utilisateurs
        $users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Affiche la vue 'admin_users.php'
        View::render('admin_users', ['users' => $users], 'admin');
    }

    // --- ACTIONS UTILISATEURS ---

    public function createUser() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::connect();
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            try {
                $stmt = $db->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, 'client', 'active')");
                $stmt->execute([$_POST['email'], $pass]);
                $newUserId = $db->lastInsertId();
                
                // Hook pour enregistrer les infos profil (Nom, Siret...)
                Hook::call('after_user_save', ['user_id' => $newUserId, 'post' => $_POST]);
            } catch (Exception $e) { /* Gérer l'erreur si besoin */ }
        }
        header('Location: /admin/users'); // Retour à la liste des users
    }

    public function suspendUser() {
        $this->checkAdmin();
        if (isset($_POST['user_id']) && isset($_POST['status'])) {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$_POST['status'], $_POST['user_id']]);
        }
        header('Location: /admin/users');
    }

    public function editUser() {
        $this->checkAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::connect();
            $userId = $_POST['id'];

            // Traitement de la sauvegarde
            $sql = "UPDATE users SET email = ?, role = ?, status = ? WHERE id = ?";
            $params = [$_POST['email'], $_POST['role'], $_POST['status'], $userId];
            
            if (!empty($_POST['password'])) {
                $sql = "UPDATE users SET email = ?, role = ?, status = ?, password = ? WHERE id = ?";
                $params = [$_POST['email'], $_POST['role'], $_POST['status'], password_hash($_POST['password'], PASSWORD_DEFAULT), $userId];
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // Hook pour enregistrer les infos profil (Nom, Siret...)
            Hook::call('after_user_save', ['user_id' => $userId, 'post' => $_POST]);
            
            header('Location: /admin/users'); exit;
        }

        // Traitement de l'affichage (GET)
        if (!isset($_GET['id'])) { header('Location: /admin/users'); exit; }
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        View::render('admin_edit_user', ['user' => $user], 'admin');
    }

    // --- ACTIONS SERVICES ---

    public function saveService() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customData = Hook::filter('save_service_data', [], ['post' => $_POST]);
            $customJson = json_encode($customData);
            $db = Database::connect();
            
            // CORRECTION CRITIQUE: Assurer que le prix est un nombre (0.00 si vide)
            $price = floatval($_POST['price'] ?? 0); 
            
            // CORRECTION ROBUSTESSE: Utilisation de ?? null pour les champs potentiellement vides
            $sql = "UPDATE services SET name=?, type=?, ip_address=?, location=?, bandwidth=?, carrier=?, hardware=?, details=?, price=?, engagement=?, custom_fields=? WHERE id=?";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $_POST['name'], 
                $_POST['type'], 
                $_POST['ip_address'] ?? null,   
                $_POST['location'] ?? null,     
                $_POST['bandwidth'] ?? null,    
                $_POST['carrier'] ?? null,      
                $_POST['hardware'] ?? null,     
                $_POST['details'] ?? null,
                $price, // Utilisation de la variable corrigée
                $_POST['engagement'], 
                $customJson, 
                $_POST['id']
            ]);
        }
        header('Location: /admin/services');
    }

    public function updateStatus() {
        $this->checkAdmin();
        if (isset($_POST['service_id']) && isset($_POST['status'])) {
            $db = Database::connect();
            $serviceId = $_POST['service_id'];
            $newStatus = $_POST['status'];
            
            // 1. Récupérer les données utilisateur et service avant la mise à jour (pour le Hook)
            $stmt = $db->prepare("SELECT s.*, u.email, u.firstname, u.lastname FROM services s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
            $stmt->execute([$serviceId]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($service) {
                // 2. Mettre à jour le statut
                $db->prepare("UPDATE services SET status = ? WHERE id = ?")->execute([$newStatus, $serviceId]);

                // 3. Déclencher le Hook de notification (Le module ServiceNotification écoute ceci)
                Hook::call('after_service_status_update', [
                    'new_status' => $newStatus,
                    'service' => $service,
                    'user' => [
                        'id' => $service['user_id'],
                        'email' => $service['email'],
                        'firstname' => $service['firstname'],
                        'lastname' => $service['lastname']
                    ]
                ]);
            }
        }
        header('Location: /admin/services');
    }

    public function editService() {
        $this->checkAdmin();
        if (!isset($_GET['id'])) { header('Location: /admin/services'); exit; }
        
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        
        View::render('admin_edit_service', ['service' => $service], 'admin');
    }

    // --- ACTIONS MODULES ---

    public function listModules() {
        $this->checkAdmin();
        $modules = ModuleManager::getAll();
        View::render('admin_modules', ['modules' => $modules], 'admin');
    }

    public function toggleModule() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            ModuleManager::toggle($_POST['name'], $_POST['status']);
        }
        header('Location: /admin/modules'); exit;
    }

    // --- PARAMÈTRES (SETTINGS) ---

    public function settings() {
        $this->checkAdmin();
        // Force loading settings (which will create table if missing due to our lazy logic in Settings::get/load)
        // But Settings::load is private. We can call Settings::getAll() or Settings::get('anything').
        // Actually, we need to pass the current values to the view.

        $settings = Settings::getAll();
        // Ensure defaults if empty (though createTable handles it, but just in case)
        if (empty($settings['site_url'])) $settings['site_url'] = 'https://dev.cshybridconnect.com/';
        if (empty($settings['site_name'])) $settings['site_name'] = 'CSHC';

        View::render('admin_settings', ['settings' => $settings], 'admin');
    }

    public function saveSettings() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteUrl = rtrim($_POST['site_url'], '/') . '/'; // Ensure trailing slash
            $siteName = trim($_POST['site_name']);

            Settings::set('site_url', $siteUrl);
            Settings::set('site_name', $siteName);

            // On pourrait ajouter un flash message ici
        }
        header('Location: /admin/settings'); exit;
    }
}