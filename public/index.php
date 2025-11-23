<?php
session_start();

// Autoloader simple
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Hook.php';
require_once __DIR__ . '/../app/Core/ModuleManager.php';
require_once __DIR__ . '/../app/Core/View.php'; 

ModuleManager::boot();
$router = new Router();

// Définition des routes
$router->add('home', 'HomeController', 'index');
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

// --- NOUVELLES ROUTES MODULES ---
$router->add('admin/modules', 'AdminController', 'listModules');      // La page visuelle
$router->add('admin/toggle-module', 'AdminController', 'toggleModule'); // L'action technique

// Dispatch
$url = isset($_GET['url']) ? $_GET['url'] : 'home';
$router->dispatch($url);