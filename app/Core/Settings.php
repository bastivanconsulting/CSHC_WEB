<?php
// app/Core/Settings.php

class Settings {
    private static $cache = null;

    private static function load() {
        if (self::$cache !== null) {
            return;
        }

        $db = Database::connect();
        try {
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            self::$cache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            // Table might not exist yet, treat as empty or try to create?
            // For robustness, let's return defaults if table missing
            self::$cache = [];
        }
    }

    public static function get($key, $default = '') {
        self::load();
        return isset(self::$cache[$key]) ? self::$cache[$key] : $default;
    }

    public static function set($key, $value) {
        $db = Database::connect();

        // Ensure table exists (Lazy migration for existing installs)
        // This is a bit heavy to check every time, but safe.
        // A better way would be to check only on exception in load(), but set() needs it to write.
        // Let's assume the table exists or we handle the error.

        try {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);

            // Update cache
            if (self::$cache === null) {
                self::$cache = [];
            }
            self::$cache[$key] = $value;
        } catch (PDOException $e) {
            // If table doesn't exist, create it and retry
             if (strpos($e->getMessage(), "Table") !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
                 self::createTable($db);
                 self::set($key, $value); // Retry
             } else {
                 throw $e;
             }
        }
    }

    public static function getAll() {
        self::load();
        return self::$cache;
    }

    private static function createTable($db) {
        $sql = "
            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(50) NOT NULL UNIQUE,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );
        ";
        $db->exec($sql);

        // Insert defaults if created now
        $defaults = [
            'site_url' => 'https://dev.cshybridconnect.com/',
            'site_name' => 'CSHC'
        ];
        foreach ($defaults as $k => $v) {
             $stmt = $db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
             $stmt->execute([$k, $v]);
        }
    }
}
