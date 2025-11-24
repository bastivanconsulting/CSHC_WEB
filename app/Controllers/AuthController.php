<?php
class AuthController {
    
    public function login() {
        // --- CORRECTION CRITIQUE : Redirection si déjà connecté ---
        if (isset($_SESSION['user'])) {
             // Rediriger vers la bonne destination selon le rôle
             if ($_SESSION['user']['role'] === 'admin') {
                 header('Location: /admin');
             } else {
                 header('Location: /dashboard');
             }
             exit;
        }
        // --------------------------------------------------------

        // [HOOK] Before Login
        Hook::call('before_login_attempt');
        
        // Tableau de données à envoyer à la vue
        $data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Protection
            Csrf::check();

            $email = $_POST['email'];
            $password = $_POST['password'];
            
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                
                // Vérification statut suspendu
                if ($user['status'] === 'suspended') {
                    $data['error'] = "Ce compte a été suspendu. Contactez le support.";
                } else {
                    $_SESSION['user'] = $user;
                    
                    // [HOOK] Login Success
                    Hook::call('user_login_success', ['user' => $user]);

                    if($user['role'] === 'admin') {
                        header('Location: /admin');
                    } else {
                        header('Location: /dashboard');
                    }
                    exit;
                }
            } else {
                // [HOOK] Login Failed
                Hook::call('user_login_failed', ['email' => $email]);
                $data['error'] = "Identifiants incorrects";
            }
        }
        
        // Rendu via le moteur de template (Thème 'front')
        View::render('login', $data, 'front');
    }

    public function dashboard() {
        if (!isset($_SESSION['user'])) { header('Location: /login'); exit; }
        
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$_SESSION['user']['id']]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // FILTRAGE AVANT LE HOOK : Retirer les services définitivement terminés
        $today = date('Y-m-d');
        $services = array_filter($services, function($s) use ($today) {
            // Règle 1: Masquer si le statut est 'terminated'
            if ($s['status'] === 'terminated') {
                return false;
            }
            
            // Règle 2: Masquer si une date de résiliation est définie ET qu'elle est passée
            if (!empty($s['termination_date']) && $s['termination_date'] < $today) {
                return false;
            }
            
            // Afficher tous les autres (actifs, suspendus, ou en attente de résiliation)
            return true;
        });

        // [HOOK] Filter Services
        $services = Hook::filter('client_dashboard_data', $services);

        // Rendu via le moteur de template (Thème 'front')
        View::render('dashboard', ['services' => $services], 'front');
    }

    public function logout() {
        // [HOOK] Logout
        Hook::call('user_logout', ['user' => $_SESSION['user'] ?? null]);
        
        session_destroy();
        header('Location: /');
    }
}