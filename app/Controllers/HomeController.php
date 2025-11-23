<?php
class HomeController {
    public function index() {
        // [HOOK] Avant l'affichage : Un module peut vouloir rediriger l'utilisateur ou loguer la visite ici
        Hook::call('before_home_render');
        
        // Utilisation du moteur de template avec le contexte 'front'
        // Cela va chercher themes/default/home.php et l'injecter dans themes/default/layout.php
        View::render('home', [], 'front');
    }

    public function infrastructure() {
        View::render('infrastructure', [
            'title' => Settings::get('site_name') . ' | Infrastructure'
        ], 'front');
    }
}