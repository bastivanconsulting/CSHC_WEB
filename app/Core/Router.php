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
        $moduleRoutes = Hook::filter('register_routes', []);
        
        // Check for exact match first
        if (isset($moduleRoutes[$url])) {
            $this->executeModuleRoute($moduleRoutes[$url]);
            return;
        }

        // Check for regex match
        foreach ($moduleRoutes as $routePattern => $target) {
            // Convert simple route to regex if it contains regex-like characters but isn't delimited
            // Or just check if the pattern matches the URL
            // Assuming simple regex support like 'support/view/(\d+)'

            // We need to match the whole string
            $pattern = "#^" . $routePattern . "$#";

            if (preg_match($pattern, $url, $matches)) {
                // Remove the full match from array
                array_shift($matches);
                $this->executeModuleRoute($target, $matches);
                return;
            }
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
            // Check if 404 view exists, otherwise echo
            echo "<div style='color:white; text-align:center; margin-top:50px;'>404 - Page Introuvable</div>";
        }
    }

    private function executeModuleRoute($target, $params = []) {
        if (is_callable($target)) {
            call_user_func_array($target, $params);
        } elseif (is_array($target) && isset($target['controller']) && isset($target['method'])) {
            // It's a structured array ['controller' => '...', 'method' => '...']
            $controllerClass = $target['controller'];
            $method = $target['method'];

            // We assume the class is autoloaded or we need to require it.
            // Since we are using namespaces in the module, standard autoloading should handle it
            // if configured, OR we rely on the file existing in the right place.
            // Given the environment, let's try to instantiate.

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                call_user_func_array([$controller, $method], $params);
            } else {
                // Manually try to locate the file if psr-4 isn't fully set up or we are in a simple environment
                // Modules\Support\Controllers\TicketController -> modules/Support/Controllers/TicketController.php
                // Remove "Modules\" prefix
                $classNameClean = str_replace('Modules\\', '', $controllerClass);
                $parts = explode('\\', $classNameClean);
                // "Support", "Controllers", "TicketController"

                // Construct path relative to app root
                $path = __DIR__ . '/../../modules/' . implode('/', $parts) . '.php';

                if (file_exists($path)) {
                    require_once $path;
                    $controller = new $controllerClass();
                    call_user_func_array([$controller, $method], $params);
                } else {
                    die("Controller class $controllerClass not found.");
                }
            }
        } else {
             die("Invalid route target defined by module.");
        }
    }
}
