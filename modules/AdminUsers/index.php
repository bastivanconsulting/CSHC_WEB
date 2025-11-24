<?php
/**
 * Module: Admin Users Management
 * Description: Fournit une interface pour créer et supprimer des utilisateurs de type 'admin'
 * sans toucher à la liste des clients standards.
 */

use Modules\AdminUsers\Controllers\AdminUsersController;

define('ADMIN_USERS_MODULE_NAME', 'AdminUsers');

// 1. AJOUTER LA ROUTE ET LE LIEN DANS LE MENU ADMIN
Hook::listen('register_routes', function($routes) {
    // Nouvelle URL : /admin/super-admins
    $routes['admin/super-admins'] = [
        'controller' => AdminUsersController::class,
        'method' => 'index'
    ];
    
    return $routes;
});

// 2. AJOUTER LE LIEN DANS LE MENU ADMIN
Hook::listen('navbar_items', function() {
    if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
        // Le lien sera mis en évidence
        return '<li class="nav-item"><a class="nav-link" href="/admin/super-admins"><i class="fas fa-user-shield me-1"></i> Super-Admins</a></li>';
    }
});
