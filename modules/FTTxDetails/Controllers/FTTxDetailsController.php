<?php
namespace Modules\FTTxDetails\Controllers;

use Hook;
use View;

class FTTxDetailsController {

    // We use a constant for the View logic, but constants in classes are accessed via self:: or Class::
    // It's cleaner to pass it or define it in the view.
    const FTTX_TYPE_KEY = 'FTTx';

    public function renderAdminFormFields() {
        // We need to pass the key to the view
        $data = ['fttx_type_key' => self::FTTX_TYPE_KEY];

        ob_start();
        View::render('modules/FTTxDetails/Views/admin_form_fields', $data, 'admin');
        return ob_get_clean();
    }

    public function renderAdminEditFields($args) {
        if ($args['service']['type'] !== self::FTTX_TYPE_KEY) return;

        // Récupération des données JSON stockées
        $data = json_decode($args['service']['custom_fields'], true) ?? [];
        $data['fttx_type_key'] = self::FTTX_TYPE_KEY;

        ob_start();
        View::render('modules/FTTxDetails/Views/admin_edit_fields', $data, 'admin');
        return ob_get_clean();
    }

    public function handleSaveServiceData($currentData, $args) {
        if ($args['post']['type'] !== self::FTTX_TYPE_KEY) return $currentData;

        // Ajout des données spécifiques au FTTx
        $currentData['fttx_type'] = $args['post']['fttx_type'] ?? 'FTTH';
        $currentData['fttx_carrier'] = $args['post']['fttx_carrier'] ?? '';
        $currentData['fttx_pppoe_user'] = $args['post']['fttx_pppoe_user'] ?? '';
        $currentData['fttx_pppoe_pass'] = $args['post']['fttx_pppoe_pass'] ?? '';
        $currentData['fttx_pppoe_required'] = isset($args['post']['fttx_pppoe_required']) ? true : false;

        return $currentData;
    }

    public function renderClientDashboardDetails($args) {
        if ($args['service']['type'] !== self::FTTX_TYPE_KEY) return;

        $data = json_decode($args['service']['custom_fields'], true);
        if (!$data) return;

        $data['fttx_type_key'] = self::FTTX_TYPE_KEY;

        ob_start();
        View::render('modules/FTTxDetails/Views/client_dashboard_details', $data, 'front');
        return ob_get_clean();
    }
}
