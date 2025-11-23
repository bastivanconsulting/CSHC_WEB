# 🧩 Modules System

Le système de modules de CSHC permet d'étendre les fonctionnalités de l'application sans modifier le code cœur (`app/`).

## 📦 Structure d'un Module

Chaque module réside dans son propre sous-dossier dans `modules/`.
Le point d'entrée est obligatoirement le fichier `index.php`.

Exemple :
```text
modules/
└── SuperFeature/
    ├── index.php       # Point d'entrée (Requis)
    ├── helper.php      # Fichiers optionnels
    └── assets/         # Images, JS, CSS spécifiques
```

## 🔌 Fonctionnement

1.  **Découverte** : Le `ModuleManager` scanne ce dossier à chaque démarrage. Les nouveaux dossiers sont ajoutés en base de données avec le statut `disabled`.
2.  **Activation** : L'administrateur active le module depuis le panel Admin.
3.  **Chargement** : Au démarrage (`index.php`), si le module est `active`, son fichier `index.php` est inclus (`require_once`).

## 🪝 Utilisation des Hooks

Les modules interagissent avec l'application principalement via la classe `Hook`.

### Actions (Injecter du contenu/code)
```php
// Dans modules/MyModule/index.php
Hook::add('footer_content', function() {
    echo '<p>Propulsé par MyModule</p>';
});
```
Hooks disponibles courants : `global_head`, `navbar_items`, `global_alert_banner`, `footer_content`, `global_scripts`, `after_user_save`.

### Filters (Modifier des données)
```php
Hook::add('save_service_data', function($data) {
    $data['my_custom_field'] = 'Valeur';
    return $data;
});
```

### Routes (Ajouter des pages)
```php
Hook::add('register_routes', function() {
    return [
        'ma-super-page' => function() {
            echo "Bienvenue sur ma page modulaire !";
        }
    ];
});
```
