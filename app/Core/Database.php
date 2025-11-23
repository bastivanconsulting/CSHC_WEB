<?php
// app/Core/Database.php

class Database {
    private static $instance = null;

    public static function connect() {
        // 1. Vérification : Le fichier config existe-t-il ?
        $configFile = __DIR__ . '/../config.php';
        
        if (!file_exists($configFile)) {
            // Si non, on redirige vers l'installateur ou on affiche un message clair
            // On tente de deviner l'URL de l'installateur
            $installUrl = '/install.php'; 
            
            die("
                <div style='font-family:sans-serif; text-align:center; margin-top:50px;'>
                    <h1>Configuration manquante</h1>
                    <p>Le fichier de configuration <code>app/config.php</code> est introuvable.</p>
                    <a href='$installUrl' style='background:#0d6efd; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>
                        Lancer l'installation automatique
                    </a>
                    <br><br>
                    <small>Si le bouton ne fonctionne pas, vérifiez que <code>install.php</code> est bien à la racine du site.</small>
                </div>
            ");
        }

        // 2. Si le fichier existe, on le charge
        require_once $configFile;

        if (self::$instance === null) {
            try {
                self::$instance = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) { 
                die("Erreur de connexion à la Base de Données : " . $e->getMessage()); 
            }
        }
        return self::$instance;
    }
}