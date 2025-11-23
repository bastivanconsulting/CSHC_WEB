# 🎨 Thèmes

Ce dossier contient les gabarits (templates) HTML de l'application. CSHC sépare le thème "Front-end" (pour les clients) du thème "Admin".

## 📂 Structure

*   **`default/`** : Le thème public par défaut.
    *   `layout.php` : Le squelette principal (HTML, Head, Navbar, Footer). La variable `$content` est injectée au milieu.
    *   `home.php`, `login.php`, `dashboard.php` : Vues spécifiques.
*   **`admin_default/`** : Le thème de l'administration.
    *   `layout.php` : Structure avec Sidebar ou Navbar Admin spécifique.
    *   `admin.php` : Dashboard principal.
    *   `admin_settings.php`, `admin_users.php`, etc.

## 🖌️ Personnalisation

Pour modifier l'apparence :
1.  **CSS** : Les fichiers CSS sont situés dans `public/css/`. Les thèmes incluent `style.css` par défaut.
2.  **Layout** : Modifiez `layout.php` pour changer l'en-tête ou le pied de page global.
3.  **Variables** : Utilisez `Settings::get('site_name')` pour afficher le nom du site configuré dynamiquement.

## 📝 Conventions

*   Les vues reçoivent les données du contrôleur via un tableau `$data` extrait (les clés du tableau deviennent des variables : `['user' => $u]` devient `$user`).
*   Utilisez toujours `htmlspecialchars()` pour afficher des données utilisateur afin d'éviter les failles XSS.
