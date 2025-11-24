<?php

use App\Core\Router;
use App\Core\Hook;

// Register routes
Router::add('GET', '/looking-glass', [
    'controller' => 'Modules\LookingGlass\Controllers\LookingGlassController',
    'method' => 'index'
]);

Router::add('POST', '/looking-glass/execute', [
    'controller' => 'Modules\LookingGlass\Controllers\LookingGlassController',
    'method' => 'execute'
]);

Router::add('GET', '/looking-glass/download/(.*)', [
    'controller' => 'Modules\LookingGlass\Controllers\LookingGlassController',
    'method' => 'download'
]);

// Add to Navbar (Public)
Hook::listen('navbar_items', function () {
    // Check if the current URI starts with /looking-glass
    $active = (strpos($_SERVER['REQUEST_URI'] ?? '', '/looking-glass') === 0) ? 'active' : '';
    echo '<li class="nav-item"><a class="nav-link ' . $active . '" href="/looking-glass">Looking Glass</a></li>';
});
