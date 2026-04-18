<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (login($pdo, $username, $password)) {
        header("Location: /admin");
        exit;
    } else {
        $error = "DECRYPT_FAILED: Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Portfolio 1.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-pitch-black text-white flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full p-10 bg-white/5 border border-white/10 rounded-[20px] backdrop-blur-xl">
        <div class="text-center space-y-4 mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-sharp-orange/10 border border-sharp-orange/20 mb-4">
                <i data-lucide="lock" class="w-8 h-8 text-sharp-orange"></i>
            </div>
            <h1 class="text-3xl font-black tracking-tighter uppercase italic text-glow-orange">Gatekeeper</h1>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-3 rounded mb-6 text-xs text-center">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div class="relative">
                <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-sharp-orange"></i>
                <input type="text" name="username" placeholder="USERNAME" required class="w-full bg-black/60 border border-white/10 rounded-xl py-4 pl-12 pr-4 outline-none focus:border-sharp-orange transition-all font-mono">
            </div>
            <div class="relative">
                <i data-lucide="key" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-sharp-orange"></i>
                <input type="password" name="password" placeholder="PASSWORD" required class="w-full bg-black/60 border border-white/10 rounded-xl py-4 pl-12 pr-4 outline-none focus:border-sharp-orange transition-all font-mono">
            </div>
            <button type="submit" class="w-full py-4 bg-sharp-orange text-black font-black rounded-xl uppercase tracking-[0.2em] italic text-sm hover:brightness-110 transition-all">Initialize Sync</button>
        </form>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
