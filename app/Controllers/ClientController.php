<?php
class ClientController {
    public function requestCancellation() {
        // Sécurité
        if (!isset($_SESSION['user'])) { header('Location: /login'); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
            $db = Database::connect();
            
            // Vérification appartenance + Récupération infos pour le hook
            $stmt = $db->prepare("SELECT * FROM services WHERE id = ? AND user_id = ?");
            $stmt->execute([$_POST['service_id'], $_SESSION['user']['id']]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($service) {
                // Mise à jour BDD
                $update = $db->prepare("UPDATE services SET termination_date = ? WHERE id = ?");
                $update->execute([$_POST['date'], $_POST['service_id']]);

                // [HOOK] Notification : Permet d'envoyer un mail à l'admin via le module SMTP
                Hook::call('after_service_cancellation', [
                    'service' => $service, 
                    'user' => $_SESSION['user'],
                    'date' => $_POST['date']
                ]);
            }
        }
        // Redirection vers le dashboard (qui lui utilisera le Template)
        header('Location: /dashboard');
        exit;
    }
}