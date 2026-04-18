<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();

$csrf_token = generate_csrf_token();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['update_profile'])) {
        update_setting($pdo, 'admin_name', $_POST['admin_name']);
        update_setting($pdo, 'admin_email', $_POST['admin_email']);
        update_setting($pdo, 'admin_bio', $_POST['admin_bio']);
        $success = "Profile data updated.";
    }

    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];

        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch();

        if (password_verify($old_pass, $user['password'])) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['admin_id']]);
            $success = "Password rotated successfully.";
        } else {
            $error = "Old password verification failed.";
        }
    }
}

$admin_name = get_setting($pdo, 'admin_name', 'Cyber Architect');
$admin_email = get_setting($pdo, 'admin_email', 'philmorehost@gmail.com');
$admin_bio = get_setting($pdo, 'admin_bio', 'Expert Full-Stack Developer & Security Specialist');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile & Security | Portfolio 1.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-pitch-black text-white p-4 md:p-10">
    <div class="max-w-4xl mx-auto space-y-12 pb-20">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl md:text-5xl font-black italic uppercase text-glow-orange">Admin <span class="text-sharp-orange">Profile</span></h1>
            <a href="/admin" class="text-text-dim hover:text-sharp-orange transition-colors flex items-center gap-2 font-black uppercase text-xs">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Console
            </a>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded text-xs text-center font-bold"><?php echo e($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded text-xs text-center font-bold"><?php echo e($error); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Profile Info -->
            <form method="POST" class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <h2 class="text-xs font-black text-sharp-orange uppercase tracking-[0.4em] flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4"></i> Identity Metadata
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Full Name</label>
                        <input type="text" name="admin_name" value="<?php echo e($admin_name); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Email Address</label>
                        <input type="email" name="admin_email" value="<?php echo e($admin_email); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Professional Bio</label>
                        <textarea name="admin_bio" rows="4" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange"><?php echo e($admin_bio); ?></textarea>
                    </div>
                </div>
                <button type="submit" name="update_profile" class="w-full py-3 bg-sharp-orange text-black font-black rounded uppercase tracking-widest text-[10px]">Update Profile</button>
            </form>

            <!-- Security / Password -->
            <form method="POST" class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <h2 class="text-xs font-black text-red-500 uppercase tracking-[0.4em] flex items-center gap-2">
                    <i data-lucide="shield-lock" class="w-4 h-4"></i> Password Rotation
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Current Master Key</label>
                        <input type="password" name="old_pass" required class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">New Rotation Key</label>
                        <input type="password" name="new_pass" required class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-red-500">
                    </div>
                </div>
                <button type="submit" name="update_password" class="w-full py-3 bg-red-500/10 border border-red-500 text-red-500 font-black rounded uppercase tracking-widest text-[10px] hover:bg-red-500 hover:text-white transition-all">Initialize Rotation</button>
            </form>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
