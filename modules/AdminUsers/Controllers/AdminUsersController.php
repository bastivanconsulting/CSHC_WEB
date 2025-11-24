<?php
namespace Modules\AdminUsers\Controllers;

use Database;
use View;
use PDO;

class AdminUsersController {

    public function index() {
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
            } catch (\Exception $e) {
                 $message = "<div class='alert alert-danger'><i class='fas fa-times'></i> Erreur : Email déjà utilisé ou BDD.</div>";
            }
        }

        // --- AFFICHAGE DE LA VUE ---
        $admins = $db->query("SELECT id, email, created_at FROM users WHERE role='admin' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Affiche la vue avec le nom court, grâce à l'auto-découverte dans View::render
        View::render('admin_users_management', ['admins' => $admins, 'message' => $message], 'admin');
    }
}
