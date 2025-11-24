<?php
/**
 * Module: FTTx Details
 * Description: Ajoute des champs spécifiques aux lignes Fibre (FTTH/FTTO, Opérateur, PPPoE).
 */

use Modules\FTTxDetails\Controllers\FTTxDetailsController;

// Clé unique pour ce module
define('FTTX_TYPE_KEY', 'FTTx');

// 1. AFFICHER LES CHAMPS SPÉCIFIQUES (Admin > Formulaire Création)
Hook::listen('admin_service_form_fields', function() {
    $controller = new FTTxDetailsController();
    return $controller->renderAdminFormFields();
});

// 2. AFFICHER LES CHAMPS EN ÉDITION (Admin > Formulaire Edition)
Hook::listen('admin_service_edit_fields', function($args) {
    $controller = new FTTxDetailsController();
    return $controller->renderAdminEditFields($args);
});

// 3. SAUVEGARDER LES DONNÉES (Action SQL via Hook Filter)
Hook::listen('save_service_data', function($currentData, $args) {
    $controller = new FTTxDetailsController();
    return $controller->handleSaveServiceData($currentData, $args);
});

// 4. AFFICHER SUR LE DASHBOARD CLIENT (Détails techniques)
Hook::listen('client_dashboard_service_details', function($args) {
    $controller = new FTTxDetailsController();
    return $controller->renderClientDashboardDetails($args);
});
