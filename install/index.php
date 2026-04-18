<?php
session_start();

$config_file = '../includes/config.php';

if (file_exists($config_file)) {
    // Optionally check if DB is already set up, but for now let's just say if config exists, don't allow install
    // unless a specific flag is set.
    // die("Application already installed. Delete /install or config.php to restart.");
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 2) {
        $db_host = $_POST['db_host'];
        $db_name = $_POST['db_name'];
        $db_user = $_POST['db_user'];
        $db_pass = $_POST['db_pass'];

        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $_SESSION['db_config'] = [
                'host' => $db_host,
                'name' => $db_name,
                'user' => $db_user,
                'pass' => $db_pass
            ];
            header("Location: ?step=3");
            exit;
        } catch (PDOException $e) {
            $error = "Connection failed: " . $e->getMessage();
        }
    } elseif ($step == 3) {
        $admin_user = $_POST['admin_user'];
        $admin_pass = $_POST['admin_pass'];
        $gemini_key = $_POST['gemini_key'];

        if (empty($admin_user) || empty($admin_pass)) {
            $error = "Admin credentials required.";
        } else {
            $db = $_SESSION['db_config'];
            try {
                $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']}", $db['user'], $db['pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Run schema
                $schema = file_get_contents('../schema.sql');
                $pdo->exec($schema);

                // Create Admin
                $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
                $stmt->execute([$admin_user, $hashed_pass]);

                // Save Config
                $config_content = "<?php\n";
                $config_content .= "define('DB_HOST', '" . addslashes($db['host']) . "');\n";
                $config_content .= "define('DB_NAME', '" . addslashes($db['name']) . "');\n";
                $config_content .= "define('DB_USER', '" . addslashes($db['user']) . "');\n";
                $config_content .= "define('DB_PASS', '" . addslashes($db['pass']) . "');\n";
                $config_content .= "define('GEMINI_API_KEY', '" . addslashes($gemini_key) . "');\n";

                file_put_contents($config_file, $config_content);

                header("Location: ?step=4");
                exit;
            } catch (Exception $e) {
                $error = "Installation failed: " . $e->getMessage();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installer | Portfolio 1.0</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .accent-orange { color: #FF6600; }
        .bg-orange { background: #FF6600; }
        .border-orange { border-color: #FF6600; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full p-8 bg-gray-900 rounded-xl border border-gray-800 shadow-2xl">
        <h1 class="text-3xl font-black italic uppercase tracking-tighter mb-6">
            System <span class="accent-orange">Installer</span>
        </h1>

        <?php if ($error): ?>
            <div class="bg-red-900/50 border border-red-500 text-red-200 p-4 rounded mb-6 text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <div class="space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Stage 1: Environment Check</h2>
                <ul class="space-y-2 text-sm">
                    <li class="flex justify-between">
                        <span>PHP 8.1+</span>
                        <span class="<?php echo PHP_VERSION_ID >= 80100 ? 'text-green-500' : 'text-red-500'; ?>">
                            <?php echo PHP_VERSION; ?>
                        </span>
                    </li>
                    <li class="flex justify-between">
                        <span>PDO MySQL</span>
                        <span class="<?php echo extension_loaded('pdo_mysql') ? 'text-green-500' : 'text-red-500'; ?>">
                            <?php echo extension_loaded('pdo_mysql') ? 'Active' : 'Missing'; ?>
                        </span>
                    </li>
                    <li class="flex justify-between">
                        <span>CURL</span>
                        <span class="<?php echo extension_loaded('curl') ? 'text-green-500' : 'text-red-500'; ?>">
                            <?php echo extension_loaded('curl') ? 'Active' : 'Missing'; ?>
                        </span>
                    </li>
                </ul>
                <?php if (PHP_VERSION_ID >= 80100 && extension_loaded('pdo_mysql') && extension_loaded('curl')): ?>
                    <a href="?step=2" class="block w-full text-center bg-orange text-black font-black py-3 rounded uppercase tracking-widest mt-6">Continue</a>
                <?php else: ?>
                    <p class="text-red-500 text-xs mt-4">Environment requirements not met.</p>
                <?php endif; ?>
            </div>

        <?php elseif ($step == 2): ?>
            <form method="POST" class="space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Stage 2: Database Setup</h2>
                <div>
                    <label class="block text-[10px] uppercase font-black text-gray-500 mb-1">DB Host</label>
                    <input type="text" name="db_host" value="localhost" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-black text-gray-500 mb-1">DB Name</label>
                    <input type="text" name="db_name" required class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-black text-gray-500 mb-1">DB User</label>
                    <input type="text" name="db_user" required class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-black text-gray-500 mb-1">DB Pass</label>
                    <input type="password" name="db_pass" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                </div>
                <button type="submit" class="w-full bg-orange text-black font-black py-3 rounded uppercase tracking-widest mt-6">Test & Proceed</button>
            </form>

        <?php elseif ($step == 3): ?>
            <form method="POST" class="space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Stage 3: Account & API</h2>
                <div>
                    <label class="block text-[10px] uppercase font-black text-gray-500 mb-1">Admin Username</label>
                    <input type="text" name="admin_user" required class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-black text-gray-500 mb-1">Admin Password</label>
                    <input type="password" name="admin_pass" required class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-black text-gray-500 mb-1">Gemini API Key</label>
                    <input type="text" name="gemini_key" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                </div>
                <button type="submit" class="w-full bg-orange text-black font-black py-3 rounded uppercase tracking-widest mt-6">Install System</button>
            </form>

        <?php elseif ($step == 4): ?>
            <div class="text-center space-y-4">
                <div class="w-20 h-20 bg-green-500/10 border border-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-xl font-bold uppercase italic">Installation Complete</h2>
                <p class="text-sm text-gray-400">Your portfolio is now ready. For security, you must <strong>delete the /install folder</strong> immediately.</p>
                <a href="../" class="block w-full bg-orange text-black font-black py-3 rounded uppercase tracking-widest mt-6">Go to Site</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
