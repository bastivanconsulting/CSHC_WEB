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

    public function vpnCloud() {
        View::render('vpn_cloud', [
            'title' => Settings::get('site_name') . ' | VPN Cloud HA'
        ], 'front');
    }

    public function vpls() {
        View::render('vpls', [
            'title' => Settings::get('site_name') . ' | VPLS Privé'
        ], 'front');
    }

    public function antiDdos() {
        View::render('anti_ddos', [
            'title' => Settings::get('site_name') . ' | Protection Anti-DDoS'
        ], 'front');
    }
}