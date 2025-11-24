<?php
use Modules\OvhApi\Controllers\OvhController;

// Enregistrement des routes du module OVH
Hook::listen('register_routes', function ($routes) {
    // Dashboard & Settings
    $routes['admin/ovh'] = ['controller' => OvhController::class, 'method' => 'index'];
    $routes['admin/ovh/settings'] = ['controller' => OvhController::class, 'method' => 'settings'];

    // IP Actions
    $routes['admin/ovh/ip/(.*)'] = ['controller' => OvhController::class, 'method' => 'viewIp'];

    // Form Actions (POST)
    $routes['admin/ovh/action/firewall/toggle'] = ['controller' => OvhController::class, 'method' => 'toggleFirewall'];
    $routes['admin/ovh/action/firewall/rule/add'] = ['controller' => OvhController::class, 'method' => 'addRule'];
    $routes['admin/ovh/action/firewall/rule/delete'] = ['controller' => OvhController::class, 'method' => 'deleteRule'];
    $routes['admin/ovh/action/ptr'] = ['controller' => OvhController::class, 'method' => 'updatePtr'];

    return $routes;
});

// Ajout du lien dans la Navbar Admin
Hook::listen('navbar_items', function () {
    return '<li class="nav-item">
                <a class="nav-link" href="/admin/ovh">
                    <i class="fas fa-cloud me-1"></i> OVH Cloud
                </a>
            </li>';
});
