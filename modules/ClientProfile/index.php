<?php
/**
 * Module: Client Profile Extended
 * Description: Ajoute Nom, Prénom, Adresse, Type, SIRET et TVA aux utilisateurs.
 */

use Modules\ClientProfile\Controllers\ClientProfileController;

// 1. AUTO-MIGRATION ROBUSTE (Création des colonnes si elles manquent)
$db = Database::connect();

$columnsToAdd = [
    'lastname'    => "VARCHAR(100) NULL AFTER email",
    'firstname'   => "VARCHAR(100) NULL AFTER lastname",
    'address'     => "TEXT NULL AFTER firstname",
    'client_type' => "ENUM('particulier', 'entreprise') DEFAULT 'particulier' AFTER address",
    'siret'       => "VARCHAR(50) NULL AFTER client_type",
    'vat_number'  => "VARCHAR(50) NULL AFTER siret"
];

foreach ($columnsToAdd as $col => $sqlDef) {
    try {
        $db->query("SELECT $col FROM users LIMIT 1");
    } catch (Exception $e) {
        try {
            $db->exec("ALTER TABLE users ADD COLUMN $col $sqlDef");
        } catch (Exception $ex) { /* Silence is golden */ }
    }
}

// 2. ADMIN : CHAMPS FORMULAIRE
Hook::listen('admin_user_form_fields', function($args) {
    // We instantiate the controller and call the method
    // Note: Hooks don't use the dependency injection or route dispatching,
    // so we manually instantiate.
    $controller = new ClientProfileController();
    return $controller->renderAdminFormFields($args);
});

// 3. ADMIN : SAUVEGARDE DES DONNÉES
Hook::listen('after_user_save', function($args) {
    $controller = new ClientProfileController();
    $controller->handleUserSave($args);
});

// 4. CLIENT : AFFICHAGE DASHBOARD (Widget Profil SIMPLE)
Hook::listen('client_dashboard_widgets', function() {
    $controller = new ClientProfileController();
    return $controller->renderClientDashboardWidget();
});
