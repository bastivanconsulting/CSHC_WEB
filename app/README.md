# 🧠 App Core

Ce dossier contient le cœur logique de l'application CSHC. Il est divisé en deux sous-dossiers principaux : `Controllers` et `Core`.

## 📁 Controllers

Les contrôleurs orchestrent la réponse aux requêtes utilisateurs. Ils récupèrent les données via les modèles (ou directement via `Database` pour l'instant) et les passent aux Vues.

*   **`AdminController.php`** : Gère tout le back-office (Dashboard, Users, Services, Settings, Modules). Vérifie les droits d'accès via `checkAdmin()`.
*   **`AuthController.php`** : Gère l'authentification (Login, Logout) et l'espace client de base (`dashboard`).
*   **`HomeController.php`** : Gère la page d'accueil publique.
*   **`ClientController.php`** : Actions spécifiques aux clients (ex: demande de résiliation).

## 📁 Core

Les classes utilitaires et structurelles du framework maison.

| Classe | Description |
| :--- | :--- |
| **`Database.php`** | Singleton gérant la connexion PDO à la base de données. |
| **`Router.php`** | Analyse l'URL et dirige vers le bon Contrôleur/Action. Gère aussi les routes dynamiques des modules. |
| **`View.php`** | Gère le rendu des templates HTML situés dans `themes/`. Injecte les variables. |
| **`Hook.php`** | Système d'événements. Permet aux modules d'injecter du code à des endroits précis (`actions` et `filters`). |
| **`ModuleManager.php`** | Scanne, active, désactive et charge les modules présents dans `modules/`. |
| **`Settings.php`** | Gestionnaire de configuration globale (site_name, site_url). Utilise un cache statique et une base de données. |

---

### 💡 Note aux développeurs

Pour ajouter une nouvelle page :
1.  Créer une méthode dans un Contrôleur existant ou créer un nouveau Contrôleur.
2.  Déclarer la route dans `public/index.php`.
3.  Créer la vue correspondante dans `themes/[theme_actif]/`.
