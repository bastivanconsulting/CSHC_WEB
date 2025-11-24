<?php
namespace Modules\ClientProfile\Controllers;

use Database;
use Hook;
use PDO;

class ClientProfileController {

    public function renderAdminFormFields($args) {
        $u = $args['user'] ?? [];
        ob_start();
        \View::render('admin_form_fields', ['u' => $u], 'admin', false);
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
        \View::render('client_dashboard_widget', ['u' => $u], 'front', false);
        return ob_get_clean();
    }
}
