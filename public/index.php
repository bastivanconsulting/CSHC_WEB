<?php
// --- SECURITY: Session Hardening ---
// Doit être fait AVANT session_start()
session_set_cookie_params([
    'lifetime' => 0,            // Session cookie (jusqu'à la fermeture du navigateur)
    'path' => '/',
    'domain' => '',             // Domaine courant
    'secure' => true,           // Uniquement via HTTPS
    'httponly' => true,         // Inaccessible via JS (XSS protection)
    'samesite' => 'None'        // Nécessaire pour le contexte cross-site (SSO, etc.)
]);

session_start();

// --- SECURITY: HTTP Headers ---
header("X-Frame-Options: DENY"); // Empêche le clickjacking via iframe
header("X-Content-Type-Options: nosniff"); // Empêche le MIME-sniffing
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-XSS-Protection: 1; mode=block"); // Protection XSS basique (navigateurs anciens)
// HSTS (HTTP Strict Transport Security) - Force HTTPS
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
// CSP (Content Security Policy) - A ajuster selon les besoins (CDN, scripts externes...)
// On autorise 'self', 'unsafe-inline' (pour le moment nécessaire pour Alpine/Styles), et les images data:
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' https:;");

// 1. Loading Environment
require_once __DIR__ . '/../app/Core/Env.php';
Env::load(__DIR__ . '/../.env');

// 2. Error Handling (Must be loaded early)
require_once __DIR__ . '/../app/Core/ErrorHandler.php';
ErrorHandler::register();

// Autoloader simple
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Hook.php';
require_once __DIR__ . '/../app/Core/ModuleManager.php';
require_once __DIR__ . '/../app/Core/View.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

// Check Maintenance Mode
if (Env::get('MAINTENANCE_MODE') === true) {
    // Check if user is admin to bypass
    $isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';

    if (!$isAdmin && !isset($_GET['bypass_maintenance'])) {
         http_response_code(503);
         View::render('maintenance', [], 'front', false);
         exit;
    }
}

ModuleManager::boot();
$router = new Router();

// Définition des routes
$router->add('home', 'HomeController', 'index');
$router->add('infrastructure', 'HomeController', 'infrastructure');
$router->add('vpn-cloud', 'HomeController', 'vpnCloud');
$router->add('vpls', 'HomeController', 'vpls');
$router->add('anti-ddos', 'HomeController', 'antiDdos');
$router->add('login', 'AuthController', 'login');
$router->add('logout', 'AuthController', 'logout');
$router->add('dashboard', 'AuthController', 'dashboard');
$router->add('admin/update', 'AdminController', 'updateStatus');

// Routes Admin - NOUVELLE STRUCTURE
$router->add('admin', 'AdminController', 'index');           // Le Dashboard
$router->add('admin/services', 'AdminController', 'services'); // Page Services
$router->add('admin/users', 'AdminController', 'users');       // Page Utilisateurs

// Gestion Admin Utilisateurs
$router->add('admin/create-user', 'AdminController', 'createUser');
$router->add('admin/suspend-user', 'AdminController', 'suspendUser');
$router->add('admin/edit-user', 'AdminController', 'editUser');
// Gestion Admin Services
$router->add('admin/edit-service', 'AdminController', 'editService'); // Formulaire d'édition
$router->add('admin/save-service', 'AdminController', 'saveService'); // Sauvegarde modif
// Gestion Client
$router->add('client/cancel', 'ClientController', 'requestCancellation'); // Demande résiliation
$router->add('client/firewall', 'ClientFirewallController', 'index');
$router->add('client/firewall/add', 'ClientFirewallController', 'add');
$router->add('client/firewall/update', 'ClientFirewallController', 'update');
$router->add('client/firewall/delete', 'ClientFirewallController', 'delete');
$router->add('client/firewall/logs', 'ClientFirewallController', 'logs');
$router->add('client/firewall/block', 'ClientFirewallController', 'blockIp');
$router->add('client/firewall/unblock', 'ClientFirewallController', 'unblockIp');
$router->add('client/firewall/verify', 'ClientFirewallController', 'verify');

require_once __DIR__ . '/../app/Core/Settings.php';

// --- NOUVELLES ROUTES MODULES ---
$router->add('admin/modules', 'AdminController', 'listModules');      // La page visuelle
$router->add('admin/toggle-module', 'AdminController', 'toggleModule'); // L'action technique

// --- ROUTES SETTINGS ---
$router->add('admin/settings', 'AdminController', 'settings');
$router->add('admin/settings/save', 'AdminController', 'saveSettings');

// --- ROUTES FIREWALL SITES ---
$router->add('admin/firewall/sites', 'AdminController', 'firewallSites');
$router->add('admin/firewall/sites/add', 'AdminController', 'addFirewallSite');
$router->add('admin/firewall/sites/delete', 'AdminController', 'deleteFirewallSite');

// Dispatch
$url = isset($_GET['url']) ? $_GET['url'] : 'home';
$router->dispatch($url);
