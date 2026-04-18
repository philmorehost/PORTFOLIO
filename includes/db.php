<?php
$config_path = __DIR__ . '/config.php';

// Check if config file exists and is not empty
if (!file_exists($config_path) || filesize($config_path) < 10) {
    if (strpos($_SERVER['REQUEST_URI'], '/install') === false) {
        header("Location: /install/");
        exit;
    }
} else {
    require_once $config_path;

    // Final check for defined constants to prevent fatal errors
    if (!defined('DB_HOST')) {
        if (strpos($_SERVER['REQUEST_URI'], '/install') === false) {
            header("Location: /install/");
            exit;
        }
    } else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if (strpos($_SERVER['REQUEST_URI'], '/install') === false) {
                header("Location: /install/");
                exit;
            }
        }
    }
}
