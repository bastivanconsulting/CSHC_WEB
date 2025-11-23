<?php
// app/Core/Router.php

class Router {
    private $routes = [];

    public function add($route, $controller, $action) {
        $this->routes[$route] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch($url) {
        $url = trim($url, '/');
        if (empty($url)) $url = 'home';

        // 1. NOUVEAU : On demande aux modules s'ils ont des routes à ajouter
        // Les modules renvoient un tableau ['url' => function() { ... }]
        $moduleRoutes = Hook::filter('register_routes', []);
        
        // 2. Vérification si l'URL correspond à une route de module
        if (isset($moduleRoutes[$url])) {
            // On exécute la fonction définie par le module
            call_user_func($moduleRoutes[$url]);
            return;
        }

        // 3. Comportement classique (Contrôleurs standards)
        if (array_key_exists($url, $this->routes)) {
            $controllerName = $this->routes[$url]['controller'];
            $actionName = $this->routes[$url]['action'];
            
            require_once __DIR__ . "/../Controllers/$controllerName.php";
            $controller = new $controllerName();
            $controller->$actionName();
        } else {
            // 404
            echo "<div style='color:white; text-align:center; margin-top:50px;'>404 - Page Introuvable</div>";
        }
    }
}