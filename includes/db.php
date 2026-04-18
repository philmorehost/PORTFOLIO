<?php
$config_path = __DIR__ . '/config.php';

function redirectToInstall() {
    if (strpos($_SERVER['REQUEST_URI'], '/install') === false) {
        header("Location: /install/");
        exit;
    }
}

if (!file_exists($config_path) || filesize($config_path) < 10) {
    redirectToInstall();
} else {
    require_once $config_path;
    if (!defined('DB_HOST')) {
        redirectToInstall();
    } else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // CRITICAL CHECK: Verify if base tables exist
            $tables = ['admin_profile', 'api_settings', 'projects'];
            foreach ($tables as $table) {
                $check = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($check->rowCount() == 0) {
                    redirectToInstall();
                }
            }

        } catch (PDOException $e) {
            redirectToInstall();
        }
    }
}
