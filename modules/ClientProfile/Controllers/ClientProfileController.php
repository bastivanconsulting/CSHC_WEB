<?php
namespace Modules\ClientProfile\Controllers;

use Database;
use Hook;
use PDO;

class ClientProfileController {

    // No explicit routes for this module, it uses hooks.
    // However, we can move the hook logic here if we want to be pure MVC,
    // but hooks are event-driven.
    // For now, I will keep the hooks in index.php but we could organize them better.
    // Actually, the user asked for "Modules must have their templates and their controller independent of the system".
    // This module generates HTML strings within hooks.
    // It is better to use View::render inside the hook callbacks, pointing to a module view.

    public function renderAdminFormFields($args) {
        $u = $args['user'] ?? [];
        // Use a view for this HTML chunk
        // Note: View::render usually echoes output. Hook expects return string.
        // We can capture output buffering.

        ob_start();
        \View::render('modules/ClientProfile/Views/admin_form_fields', ['u' => $u], 'admin');
        return ob_get_clean();
    }

    public function handleUserSave($args) {
        $userId = $args['user_id'] ?? null;
        $post = $args['post'] ?? [];

        if (!$userId || !isset($post['client_type'])) {
            return;
        }

        $db = Database::connect();

        $lastname = !empty($post['lastname']) ? $post['lastname'] : null;
        $firstname = !empty($post['firstname']) ? $post['firstname'] : null;
        $address = !empty($post['address']) ? $post['address'] : null;
        $clientType = $post['client_type'];

        $siret = ($clientType === 'entreprise' && !empty($post['siret'])) ? $post['siret'] : null;
        $vat = ($clientType === 'entreprise' && !empty($post['vat_number'])) ? $post['vat_number'] : null;

        try {
            $sql = "UPDATE users SET
                    lastname = ?, firstname = ?, address = ?,
                    client_type = ?, siret = ?, vat_number = ?
                    WHERE id = ?";

            $stmt = $db->prepare($sql);
            $stmt->execute([$lastname, $firstname, $address, $clientType, $siret, $vat, $userId]);

        } catch (\Exception $e) {
            // Log ou gestion d'erreur
        }
    }

    public function renderClientDashboardWidget() {
        if (!isset($_SESSION['user']['id'])) return;

        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($u['lastname']) && empty($u['firstname'])) return;

        ob_start();
        \View::render('modules/ClientProfile/Views/client_dashboard_widget', ['u' => $u], 'front');
        return ob_get_clean();
    }
}
