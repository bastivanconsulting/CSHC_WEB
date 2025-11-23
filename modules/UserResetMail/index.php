<?php
/**
 * Module: User Reset & Mail
 * Description: Génère un nouveau mot de passe aléatoire et l'envoie immédiatement par email au client.
 * Dépendance: Module SMTP (pour l'envoi)
 */

// 1. AJOUTER LE BOUTON D'ACTION DANS LA LISTE UTILISATEURS
Hook::listen('admin_user_actions', function($args) {
    $userId = $args['user']['id'];
    $userEmail = htmlspecialchars($args['user']['email']);
    
    // On ajoute un formulaire discret qui demande confirmation
    return "
    <form action='/admin/user/reset-send' method='POST' class='d-inline ms-1' 
          onsubmit='return confirm(\"⚠️ ATTENTION : Cela va changer le mot de passe de $userEmail et lui envoyer le nouveau par email. Continuer ?\");'>
        <input type='hidden' name='user_id' value='$userId'>
        <button type='submit' class='btn btn-sm btn-outline-warning' title='Régénérer MDP et envoyer par mail'>
            <i class='fas fa-envelope-open-text'></i>
        </button>
    </form>
    ";
});

// 2. GÉRER L'ACTION DE RESET
Hook::listen('register_routes', function($routes) {
    
    $routes['admin/user/reset-send'] = function() {
        // Sécurité : Seul l'admin peut faire ça
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /login'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            $userId = $_POST['user_id'];
            
            $db = Database::connect();
            
            // Récupération infos utilisateur
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // A. Générer un mot de passe aléatoire sécurisé (8 chars)
                // Astuce : bin2hex donne des caractères hexadécimaux sûrs
                $newPassClear = substr(bin2hex(random_bytes(4)), 0, 8);
                
                // B. Mettre à jour en base de données (Haché)
                $hashedPass = password_hash($newPassClear, PASSWORD_DEFAULT);
                $update = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$hashedPass, $userId]);

                // C. Préparer l'email
                $subject = "🔒 Réinitialisation de vos accès CSHC";
                $body = "
                <div style='font-family: sans-serif; background: #f8f9fa; padding: 20px; border-radius: 5px;'>
                    <h2 style='color: #0f172a;'>Vos identifiants ont changé</h2>
                    <p>Bonjour,</p>
                    <p>Suite à votre demande ou à une intervention administrative, votre mot de passe pour l'espace client <strong>CSHC</strong> a été réinitialisé.</p>
                    <div style='background: #fff; padding: 15px; border-left: 4px solid #3b82f6; margin: 20px 0;'>
                        <p style='margin:0;'><strong>Identifiant :</strong> {$user['email']}</p>
                        <p style='margin:5px 0 0 0;'><strong>Nouveau mot de passe :</strong> <code style='background:#eee; padding:2px 5px; font-size: 1.2em;'>{$newPassClear}</code></p>
                    </div>
                    <p>Pour des raisons de sécurité, nous vous conseillons de modifier ce mot de passe dès votre prochaine connexion.</p>
                    <p><a href='https://{$_SERVER['SERVER_NAME']}/login' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Accéder à mon espace</a></p>
                    <hr>
                    <small style='color: #999;'>Ceci est un message automatique, merci de ne pas répondre.</small>
                </div>
                ";

                // D. Envoyer via le module SMTP (Magie des Hooks !)
                // On appelle le Hook 'send_email' que nous avons créé dans le module SMTP
                $result = Hook::call('send_email', [
                    'to' => $user['email'],
                    'subject' => $subject,
                    'body' => $body
                ]);

                // E. Feedback pour l'admin
                // Comme Hook::call retourne la concaténation des résultats, si le SMTP renvoie "1" (true), c'est bon.
                if (!empty($result)) {
                    echo "<script>alert('✅ Succès : Mot de passe changé et email envoyé à {$user['email']} !'); window.location.href='/admin';</script>";
                } else {
                    echo "<script>alert('⚠️ Attention : Mot de passe changé MAIS échec de l\'envoi du mail (Vérifiez module SMTP). Le nouveau pass est : $newPassClear'); window.location.href='/admin';</script>";
                }
                exit;
            }
        }
        header('Location: /admin');
    };

    return $routes;
});