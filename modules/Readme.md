================================================================================ 
GUIDE DE DÉVELOPPEMENT DES MODULES CSHC

Ce système modulaire permet d'étendre les fonctionnalités de CSHC (CoreSecure
Hybrid Connect) en utilisant une architecture MVC (Modèle-Vue-Contrôleur)
moderne et robuste.

STRUCTURE D'UN MODULE

Chaque module est un dossier situé dans /modules/.
Il est recommandé de suivre la structure MVC suivante :

/modules
└── MonSuperModule/
    ├── Controllers/
    │   └── MonController.php
    ├── Views/
    │   └── index.php
    ├── index.php       <-- Point d'entrée obligatoire
    └── style.css       (optionnel)

Pour que le module soit chargé, il doit être ACTIVÉ dans le panneau
d'administration (/admin/modules).

LE SYSTÈME DE HOOKS ET DE ROUTAGE

L'interaction avec le cœur se fait via la classe Hook dans le fichier `index.php` du module.

A. Enregistrement des Routes (Controller)

Pour ajouter une nouvelle page, utilisez le hook `register_routes`.
Le système supporte désormais l'appel direct aux classes de contrôleurs (autoloadées).

Exemple :

```php
// modules/MonModule/index.php
use Modules\MonModule\Controllers\MonController;

Hook::listen('register_routes', function($routes) {
    // URL: /ma-page -> Appelle MonController::maMethode()
    $routes['ma-page'] = [
        'controller' => MonController::class,
        'method' => 'maMethode'
    ];
    return $routes;
});
```

B. Le Contrôleur (Namespace `Modules\NomDuModule\Controllers`)

Le contrôleur contient la logique métier. Grâce à l'autoloader PSR-4,
les classes sont chargées automatiquement.

Exemple :

```php
// modules/MonModule/Controllers/MonController.php
namespace Modules\MonModule\Controllers;

use View;

class MonController {
    public function maMethode() {
        // Logique...
        $data = ['titre' => 'Bienvenue'];

        // Affiche la vue située dans modules/MonModule/Views/index.php
        // Plus besoin de spécifier le chemin complet !
        View::render('index', $data);
    }
}
```

C. Les Vues (Views)

Placez vos fichiers templates dans le dossier `Views` de votre module.
Le système de vue détecte automatiquement le dossier du module appelant.

AUTRES HOOKS UTILES

Hook::listen('nom_evenement', function($args) { ... })
Utilisé pour injecter du HTML ou exécuter une action.

Hook::filter('nom_filtre', function($valeur, $args) { ... })
Utilisé pour modifier une donnée.

LISTE DES HOOKS PRINCIPAUX

--- DESIGN GLOBAL ---
[global_head] : Injecter du CSS/Meta dans <head>.
[navbar_items] : Ajouter des liens dans le menu.
[footer_content] : Ajouter du contenu dans le footer.

--- ESPACE CLIENT ---
[client_dashboard_widgets] : Ajouter des widgets sur le tableau de bord.
[client_dashboard_service_details] : Afficher des détails techniques sur une carte service.

--- ADMINISTRATION ---
[admin_service_form_fields] : Champs additionnels formulaire création service.
[admin_service_edit_fields] : Champs additionnels formulaire édition service.
[save_service_data] (Filter) : Intercepter et sauvegarder les données custom.

EXEMPLE COMPLET : MODULE "HELLO WORLD"

1. Fichier: modules/Hello/index.php
```php
<?php
use Modules\Hello\Controllers\HelloController;

Hook::listen('register_routes', function($routes) {
    $routes['hello'] = [
        'controller' => HelloController::class,
        'method' => 'world'
    ];
    return $routes;
});
```

2. Fichier: modules/Hello/Controllers/HelloController.php
```php
<?php
namespace Modules\Hello\Controllers;
use View;

class HelloController {
    public function world() {
        View::render('hello_view', [], 'front');
    }
}
```

3. Fichier: modules/Hello/Views/hello_view.php
```html
<h1>Hello World from Module!</h1>
```
