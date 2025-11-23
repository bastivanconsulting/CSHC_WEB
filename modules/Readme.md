================================================================================ 
GUIDE DE DÉVELOPPEMENT DES MODULES CSHC

Ce système modulaire permet d'étendre les fonctionnalités de CSHC (CoreSecure
Hybrid Connect) sans modifier le cœur de l'application.

STRUCTURE D'UN MODULE

Chaque module est un dossier situé dans /modules/. Il doit contenir au minimum
un fichier index.php.

Exemple de structure :
/modules
└── MonSuperModule/
├── index.php       <-- Point d'entrée obligatoire
├── style.css       (optionnel)
└── script.js       (optionnel)

Pour que le module soit chargé, il doit être ACTIVÉ dans le panneau
d'administration (/admin/modules).

LE SYSTÈME DE HOOKS

L'interaction avec le cœur se fait via la classe Hook. Il existe deux méthodes :

A. Hook::listen('nom_evenement', function($args) { ... })

Utilisé pour injecter du HTML, du CSS ou exécuter une action.

Exemple : Ajouter un bouton, envoyer un mail.

B. Hook::filter('nom_filtre', function($valeur, $args) { ... })

Utilisé pour modifier une donnée avant qu'elle ne soit utilisée.

Exemple : Changer le prix d'un service, ajouter une route.

LISTE COMPLÈTE DES HOOKS DISPONIBLES

--- DESIGN GLOBAL (Layout) ---
[global_head] (Listen)
Description : Injecter du CSS ou des balises <meta> dans le <head>.

[navbar_items] (Listen)
Description : Ajouter des <li><a href="...">...</a></li> dans le menu principal.

[global_alert_banner] (Listen)
Description : Afficher une alerte tout en haut de la page (ex: Maintenance).

[footer_content] (Listen)
Description : Ajouter du contenu dans le pied de page.

[global_scripts] (Listen)
Description : Injecter du Javascript <script> avant la fermeture du </body>.

--- PAGE D'ACCUEIL ---
[before_home_render] (Listen)
Description : Se déclenche avant l'affichage (utile pour rediriger ou loguer).

[home_after_hero] (Listen)
Description : Zone HTML juste après la bannière principale.

[home_bottom] (Listen)
Description : Zone HTML tout en bas de l'accueil.

--- ESPACE CLIENT (Dashboard) ---
[client_dashboard_data] (Filter)
Description : Modifier la liste des services $services avant affichage.

[client_dashboard_top] (Listen)
Description : Zone HTML haut de page (bannières promo).

[client_dashboard_widgets] (Listen)
Description : Zone HTML avant la liste des services (graphiques, stats).

[client_dashboard_badges] (Listen)
Description : Ajouter un badge à côté du titre du service (Reçoit $args['service']).

[client_dashboard_service_details] (Listen)
Description : Afficher des infos techniques (SIM, VLAN) dans la carte service.

[client_dashboard_bottom] (Listen)
Description : Zone HTML bas de page.

[after_service_cancellation] (Listen)
Description : Quand un client demande une résiliation (Reçoit service, user, date).

--- AUTHENTIFICATION & SÉCURITÉ ---
[before_login_attempt] (Listen)
Description : Avant le traitement du formulaire de login.

[user_login_success] (Listen)
Description : Quand un utilisateur se connecte (Reçoit $args['user']).

[user_login_failed] (Listen)
Description : Quand le mot de passe est faux (Reçoit $args['email']).

[user_logout] (Listen)
Description : Quand l'utilisateur se déconnecte.

--- ADMINISTRATION & SERVICES ---
[register_service_type] (Listen)
Description : Ajouter une <option> dans le sélecteur de type de service.

[admin_service_form_fields] (Listen)
Description : Ajouter des champs HTML dans le formulaire de création.

[save_service_data] (Filter) *** CRITIQUE ***
Description : Intercepte les données $_POST pour les sauvegarder dans le JSON.

[admin_service_list_details] (Listen)
Description : Afficher des infos dans la colonne "Prix / Eng." du tableau admin.

[admin_service_edit_fields] (Listen)
Description : Ajouter les champs pré-remplis dans le formulaire d'édition.

[admin_user_actions] (Listen)
Description : Ajouter des boutons d'action sur la liste des utilisateurs.

--- ROUTAGE ---
[register_routes] (Filter)
Description : Ajouter des URL personnalisées (ex: /mon-module/page).

EXEMPLES DE CODE (COPIER-COLLER)

EXEMPLE 1 : CRÉER UN NOUVEAU TYPE DE SERVICE (ex: "Offre Satellite")

<?php
// modules/Satellite/index.php

// 1. Ajouter l'option dans le menu déroulant
Hook::listen('register_service_type', function() {
return '<option value="satellite">Internet par Satellite</option>';
});

// 2. Afficher les champs spécifiques (ID Antenne)
Hook::listen('admin_service_form_fields', function() {
return "
<div class='module-field' data-type='satellite' style='display:none;'>
<label>ID Antenne</label>
<input type='text' name='sat_id' class='form-control'>
</div>
<script>
document.getElementById('serviceTypeSelector').addEventListener('change', function(e) {
// Logique JS pour afficher/masquer le champ...
});
</script>
";
});

// 3. Sauvegarder les données
Hook::listen('save_service_data', function($data, $args) {
if ($args['post']['type'] === 'satellite') {
$data['sat_id'] = $args['post']['sat_id'];
}
return $data;
});

// 4. Afficher côté client
Hook::listen('client_dashboard_service_details', function($args) {
$json = json_decode($args['service']['custom_fields'], true);
if ($args['service']['type'] === 'satellite') {
return "Antenne : " . $json['sat_id'];
}
});
?>

EXEMPLE 2 : ENVOYER UN MAIL LORS D'UNE CONNEXION

<?php
// modules/AlertLogin/index.php

Hook::listen('user_login_success', function($args) {
$email = $args['user']['email'];
mail('admin@cshc.local', 'Nouvelle connexion', "L'utilisateur $email vient de se connecter.");
});
?>