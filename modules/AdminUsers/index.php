<?php
/**
 * Module: Admin Users Management
 * Description: Fournit une interface pour créer et supprimer des utilisateurs de type 'admin'
 * sans toucher à la liste des clients standards.
 */

define('ADMIN_USERS_MODULE_NAME', 'AdminUsers');

// 1. AJOUTER LA ROUTE ET LE LIEN DANS LE MENU ADMIN
Hook::listen('register_routes', function($routes) {
    // Nouvelle URL : /admin/super-admins
    $routes['admin/super-admins'] = function() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /login'); exit;
        }

        $db = Database::connect();
        $message = "";

        // --- TRAITEMENT DES ACTIONS (CRÉATION/SUPPRESSION) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (isset($_POST['create_admin'])) {
                    // Création
                    $email = $_POST['email'];
                    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, 'admin', 'active')");
                    $stmt->execute([$email, $pass]);
                    $message = "<div class='alert alert-success'><i class='fas fa-check'></i> L'administrateur **$email** a été créé.</div>";
                } elseif (isset($_POST['delete_admin']) && is_numeric($_POST['id'])) {
                    // Suppression
                    // Sécurité : Interdire la suppression du seul admin restant (ou de soi-même)
                    $currentAdminCount = $db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
                    if ($currentAdminCount > 1) {
                         $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
                         $stmt->execute([$_POST['id']]);
                         $message = "<div class='alert alert-success'><i class='fas fa-check'></i> Administrateur supprimé.</div>";
                    } else {
                        $message = "<div class='alert alert-danger'><i class='fas fa-times'></i> Opération annulée : impossible de supprimer le dernier compte administrateur.</div>";
                    }
                }
            } catch (Exception $e) {
                 $message = "<div class='alert alert-danger'><i class='fas fa-times'></i> Erreur : Email déjà utilisé ou BDD.</div>";
            }
        }
        
        // --- AFFICHAGE DE LA VUE ---
        $admins = $db->query("SELECT id, email, created_at FROM users WHERE role='admin' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Affiche la vue admin_users_management
        View::render('admin_users_management', ['admins' => $admins, 'message' => $message], 'admin');
    };
    
    return $routes;
});

// 2. AJOUTER LE LIEN DANS LE MENU ADMIN
Hook::listen('navbar_items', function() {
    if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
        // Le lien sera mis en évidence
        return '<li class="nav-item"><a class="nav-link" href="/admin/super-admins"><i class="fas fa-user-shield me-1"></i> Super-Admins</a></li>';
    }
});