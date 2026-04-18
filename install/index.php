<?php
session_start();

$config_file = '../includes/config.php';
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
            $_SESSION['db_config'] = ['host' => $db_host, 'name' => $db_name, 'user' => $db_user, 'pass' => $db_pass];
            header("Location: ?step=3");
            exit;
        } catch (PDOException $e) { $error = "Connection failed: " . $e->getMessage(); }
    } elseif ($step == 3) {
        $admin_user = $_POST['admin_user'];
        $admin_pass = $_POST['admin_pass'];
        $deepseek_key = $_POST['deepseek_key'];
        $gemini_key = $_POST['gemini_key'];
        $psi_key = $_POST['psi_key'];

        if (empty($admin_user) || empty($admin_pass)) {
            $error = "Admin credentials required.";
        } else {
            $db = $_SESSION['db_config'];
            try {
                $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']}", $db['user'], $db['pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Run Full Schema
                $schema = file_get_contents('../final_schema.sql');
                $queries = explode(';', $schema);
                foreach ($queries as $q) { if (trim($q)) $pdo->exec($q); }

                // Create Initial Profile
                $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admin_profile (username, password, full_name, bio) VALUES (?, ?, ?, ?)");
                $stmt->execute([$admin_user, $hashed_pass, 'Cyber Architect', 'Expert Full-Stack Developer']);

                // Create Initial API Settings
                $stmt = $pdo->prepare("INSERT INTO api_settings (provider, deepseek_key, gemini_key, psi_key) VALUES (?, ?, ?, ?)");
                $stmt->execute(['gemini', $deepseek_key, $gemini_key, $psi_key]);

                // Save Config
                $config_content = "<?php\n";
                $config_content .= "define('DB_HOST', '" . addslashes($db['host']) . "');\n";
                $config_content .= "define('DB_NAME', '" . addslashes($db['name']) . "');\n";
                $config_content .= "define('DB_USER', '" . addslashes($db['user']) . "');\n";
                $config_content .= "define('DB_PASS', '" . addslashes($db['pass']) . "');\n";
                file_put_contents($config_file, $config_content);

                header("Location: ?step=4");
                exit;
            } catch (Exception $e) { $error = "Installation failed: " . $e->getMessage(); }
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
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full p-8 bg-gray-900 rounded-xl border border-gray-800 shadow-2xl">
        <h1 class="text-3xl font-black italic uppercase tracking-tighter mb-6">System <span class="accent-orange">Nexus</span> Installer</h1>
        <?php if ($error): ?><div class="bg-red-900/50 border border-red-500 text-red-200 p-4 rounded mb-6 text-sm"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <?php if ($step == 1): ?>
            <div class="space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 border-b border-gray-800 pb-2">Stage 1: Environment</h2>
                <ul class="space-y-2 text-sm">
                    <li class="flex justify-between"><span>PHP 8.1+</span><span class="<?php echo PHP_VERSION_ID >= 80100 ? 'text-green-500' : 'text-red-500'; ?>"><?php echo PHP_VERSION; ?></span></li>
                    <li class="flex justify-between"><span>PDO MySQL</span><span class="<?php echo extension_loaded('pdo_mysql') ? 'text-green-500' : 'text-red-500'; ?>">Active</span></li>
                </ul>
                <a href="?step=2" class="block w-full text-center bg-orange text-black font-black py-3 rounded uppercase tracking-widest mt-6">Continue</a>
            </div>
        <?php elseif ($step == 2): ?>
            <form method="POST" class="space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Stage 2: Database</h2>
                <input type="text" name="db_host" value="localhost" placeholder="DB Host" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                <input type="text" name="db_name" required placeholder="DB Name" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                <input type="text" name="db_user" required placeholder="DB User" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                <input type="password" name="db_pass" placeholder="DB Pass" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                <button type="submit" class="w-full bg-orange text-black font-black py-3 rounded uppercase tracking-widest mt-6">Test & Proceed</button>
            </form>
        <?php elseif ($step == 3): ?>
            <form method="POST" class="space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Stage 3: Nexus Prep</h2>
                <input type="text" name="admin_user" required placeholder="Admin Username" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange">
                <input type="password" name="admin_pass" required placeholder="Admin Password" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange text-sm font-mono">
                <div class="h-px bg-gray-800 my-4"></div>
                <input type="password" name="deepseek_key" placeholder="DeepSeek API Key" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange text-xs font-mono">
                <input type="password" name="gemini_key" placeholder="Gemini/Cloud API Key" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange text-xs font-mono">
                <input type="password" name="psi_key" placeholder="PageSpeed API Key" class="w-full bg-black border border-gray-800 rounded p-2 outline-none focus:border-orange text-xs font-mono">
                <button type="submit" class="w-full bg-orange text-black font-black py-3 rounded uppercase tracking-widest mt-6">Initialize Portfolio</button>
            </form>
        <?php elseif ($step == 4): ?>
            <div class="text-center space-y-4">
                <h2 class="text-xl font-bold uppercase italic text-green-500">Nexus Online</h2>
                <p class="text-sm text-gray-400">System initialized successfully. Delete the <strong>/install</strong> folder immediately.</p>
                <a href="../" class="block w-full bg-orange text-black font-black py-3 rounded uppercase tracking-widest mt-6">Enter Grid</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
