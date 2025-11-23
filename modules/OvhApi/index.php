<?php

// Hook to add menu item in Admin Panel
Hook::listen('navbar_items', function() {
    // Only for admin role
    if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
        echo '<li class="nav-item"><a class="nav-link" href="/admin/ovh/manage"><i class="fas fa-network-wired me-1"></i> Réseau (OVH)</a></li>';
    }
});

// Register Routes
Hook::listen('register_routes', function() {
    return [
        'admin/ovh/settings' => [
            'controller' => 'Modules\OvhApi\Controllers\AdminOvhController',
            'method' => 'settings'
        ],
        'admin/ovh/manage' => [
            'controller' => 'Modules\OvhApi\Controllers\AdminOvhController',
            'method' => 'index'
        ],
        'admin/ovh/ptr' => [
            'controller' => 'Modules\OvhApi\Controllers\AdminOvhController',
            'method' => 'updatePtr'
        ],
        'admin/ovh/firewall' => [
            'controller' => 'Modules\OvhApi\Controllers\AdminOvhController',
            'method' => 'firewall'
        ],
        'admin/ovh/firewall/create' => [
            'controller' => 'Modules\OvhApi\Controllers\AdminOvhController',
            'method' => 'createFirewall'
        ],
        'admin/ovh/firewall/toggle' => [
            'controller' => 'Modules\OvhApi\Controllers\AdminOvhController',
            'method' => 'toggleFirewall'
        ],
        'admin/ovh/firewall/add' => [
            'controller' => 'Modules\OvhApi\Controllers\AdminOvhController',
            'method' => 'addRule'
        ],
        'admin/ovh/firewall/delete' => [
            'controller' => 'Modules\OvhApi\Controllers\AdminOvhController',
            'method' => 'deleteRule'
        ],
    ];
});
