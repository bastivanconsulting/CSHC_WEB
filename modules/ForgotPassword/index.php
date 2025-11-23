<?php
/**
 * Module: Forgot Password
 * Description: Gestion mot de passe oublié avec Captcha et Token de sécurité.
 */

// 1. INITIALISATION BDD (Ajout des colonnes si absentes)
$db = Database::connect();
try {
    $db->query("SELECT reset_token FROM users LIMIT 1");
} catch (Exception $e) {
    // Les colonnes n'existent pas, on les crée
    $db->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) NULL AFTER role");
    $db->exec("ALTER TABLE users ADD COLUMN reset_expires DATETIME NULL AFTER reset_token");
}

// 2. ROUTE : FORMULAIRE DEMANDE (Email + Captcha)
Hook::listen('register_routes', function($routes) {
    $routes['forgot-password'] = function() {
        $error = null;
        $success = null;

        // Génération Captcha Mathématique Simple
        if (!isset($_SESSION['captcha_res'])) {
            $n1 = rand(1, 9);
            $n2 = rand(1, 9);
            $_SESSION['captcha_res'] = $n1 + $n2;
            $_SESSION['captcha_str'] = "$n1 + $n2";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification Captcha
            if (intval($_POST['captcha']) !== $_SESSION['captcha_res']) {
                $error = "Calcul incorrect. Êtes-vous un robot ?";
                // On régénère le captcha pour éviter le brute force
                unset($_SESSION['captcha_res']); 
            } else {
                // Vérification Email
                $db = Database::connect();
                $stmt = $db->prepare("SELECT id, email FROM users WHERE email = ?");
                $stmt->execute([$_POST['email']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Génération Token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Sauvegarde Token
                    $upd = $db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                    $upd->execute([$token, $expires, $user['id']]);

                    // Envoi Email (via Module SMTP)
                    $link = "https://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $token;
                    $body = "
                    <h3>Réinitialisation de mot de passe</h3>
                    <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                    <p><a href='$link'>$link</a></p>
                    <p><small>Ce lien expire dans 1 heure.</small></p>
                    ";
                    
                    Hook::call('send_email', [
                        'to' => $user['email'],
                        'subject' => 'Réinitialisation Mot de Passe CSHC',
                        'body' => $body
                    ]);
                }
                
                // On affiche toujours succès pour ne pas dévoiler les emails existants
                $success = "Si cet email existe, un lien de réinitialisation a été envoyé.";
                unset($_SESSION['captcha_res']);
            }
        }

        // On régénère le texte du captcha s'il a été effacé
        if (!isset($_SESSION['captcha_res'])) {
            $n1 = rand(1, 9); $n2 = rand(1, 9);
            $_SESSION['captcha_res'] = $n1 + $n2;
            $_SESSION['captcha_str'] = "$n1 + $n2";
        }

        View::render('forgot_password', ['error' => $error, 'success' => $success, 'captcha' => $_SESSION['captcha_str']], 'front');
    };

    return $routes;
});

// 3. ROUTE : FORMULAIRE RESET (Nouveau mot de passe)
Hook::listen('register_routes', function($routes) {
    $routes['reset-password'] = function() {
        $token = $_GET['token'] ?? '';
        $error = null;
        
        if (!$token) { header('Location: /login'); exit; }

        $db = Database::connect();
        // Vérif token valide et non expiré
        $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Ce lien est invalide ou a expiré.";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
            $pass = $_POST['password'];
            $confirm = $_POST['confirm_password'];

            if ($pass !== $confirm) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif (strlen($pass) < 8) {
                $error = "Le mot de passe doit faire au moins 8 caractères.";
            } else {
                // Changement MDP et suppression token
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $upd = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                $upd->execute([$hash, $user['id']]);
                
                header('Location: /login?reset=success');
                exit;
            }
        }

        View::render('reset_password', ['error' => $error, 'token' => $token, 'valid_link' => ($user ? true : false)], 'front');
    };
    return $routes;
});