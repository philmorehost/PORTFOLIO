<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();

$csrf_token = generate_csrf_token();
$success = '';
$baseUrl = get_base_url();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['update_profile'])) {
        $name = $_POST['full_name'];
        $email = $_POST['email'];
        $bio = $_POST['bio'];
        $wa = $_POST['whatsapp_number'];
        $role = $_POST['role'] ?? 0;
        $legacy = $_POST['legacy_notes'];

        $stmt = $pdo->prepare("UPDATE admin_profile SET full_name=?, email=?, bio=?, whatsapp_number=?, role=?, legacy_notes=? WHERE id=1");
        $stmt->execute([$name, $email, $bio, $wa, $role, $legacy]);
        $success = "Profile updated.";
    }

    if (isset($_POST['update_pass'])) {
        $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin_profile SET password=? WHERE id=1");
        $stmt->execute([$new_pass]);
        $success = "Password rotated.";
    }
}

$profile = get_admin_profile($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Portfolio 1.0</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/theme.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-pitch-black text-white p-4 md:p-10">
    <div class="max-w-4xl mx-auto space-y-12 pb-20">
        <div class="flex items-center justify-between bg-white/5 border border-white/10 p-8 rounded-3xl backdrop-blur-xl">
            <h1 class="text-3xl md:text-5xl font-black italic uppercase text-glow-orange">Admin <span class="text-sharp-orange">Profile</span></h1>
            <a href="/admin" class="text-text-dim hover:text-sharp-orange transition-colors flex items-center gap-2 font-black uppercase text-xs">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded text-xs text-center font-bold"><?php echo e($success); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <form method="POST" class="bg-white/5 border border-white/10 p-8 rounded-3xl space-y-6 shadow-2xl">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <h2 class="text-xs font-black text-sharp-orange uppercase tracking-[0.4em]">Identity</h2>
                <div class="space-y-4">
                    <input type="text" name="full_name" value="<?php echo e($profile['full_name']); ?>" placeholder="Name" class="w-full bg-black/40 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange transition-all">
                    <input type="email" name="email" value="<?php echo e($profile['email']); ?>" placeholder="Email" class="w-full bg-black/40 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange transition-all">
                    <input type="text" name="whatsapp_number" value="<?php echo e($profile['whatsapp_number']); ?>" placeholder="WhatsApp (e.g. 234...)" class="w-full bg-black/40 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange transition-all">
                    <div class="flex gap-4">
                        <select name="role" class="bg-black/40 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange flex-1">
                            <option value="0" <?php echo ($profile['role'] ?? 0) == 0 ? 'selected' : ''; ?>>Level 0: Super Admin</option>
                            <option value="1" <?php echo ($profile['role'] ?? 0) == 1 ? 'selected' : ''; ?>>Level 1: Restricted Admin</option>
                            <option value="2" <?php echo ($profile['role'] ?? 0) == 2 ? 'selected' : ''; ?>>Level 2: Standard User</option>
                        </select>
                    </div>
                    <textarea name="legacy_notes" rows="3" placeholder="Legacy Note Payload (PINs, answers...)" class="w-full bg-black/40 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange transition-all font-mono text-xs"><?php echo e($profile['legacy_notes'] ?? ''); ?></textarea>
                    <textarea name="bio" rows="4" placeholder="Bio" class="w-full bg-black/40 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange transition-all"><?php echo e($profile['bio']); ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="w-full py-4 bg-sharp-orange text-black font-black rounded-xl uppercase tracking-widest text-[10px] shadow-lg">Save Profile</button>
            </form>

            <form method="POST" class="bg-white/5 border border-white/10 p-8 rounded-3xl space-y-6 shadow-2xl">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <h2 class="text-xs font-black text-red-500 uppercase tracking-[0.4em]">Security</h2>
                <div class="space-y-4">
                    <input type="password" name="new_password" placeholder="NEW PASSWORD" required class="w-full bg-black/40 border border-white/10 rounded-xl p-4 outline-none focus:border-red-500 transition-all font-mono">
                </div>
                <button type="submit" name="update_pass" class="w-full py-4 bg-red-500 text-white font-black rounded-xl uppercase tracking-widest text-[10px] shadow-lg">Rotate Key</button>
            </form>
        </div>

        <?php
        $token = md5($profile['id'] . $profile['username'] . $profile['password']);
        $loginUrl = $baseUrl . "/admin/login.php?token=" . $token;
        ?>
        <div class="bg-white/5 border border-white/10 p-8 rounded-3xl space-y-6 shadow-2xl">
            <h2 class="text-xs font-black text-glossy-purple uppercase tracking-[0.4em]">One-Click Access Terminal</h2>
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <input type="text" readonly value="<?php echo $loginUrl; ?>" class="flex-1 bg-black/40 border border-white/10 rounded-xl p-4 outline-none font-mono text-xs text-glossy-purple">
                <button onclick="copyUrl('<?php echo $loginUrl; ?>')" class="px-8 py-4 bg-glossy-purple text-white font-black rounded-xl uppercase tracking-widest text-[10px] shadow-lg">Copy URL</button>
            </div>
            <p class="text-[9px] text-text-dim uppercase tracking-widest">Warning: This URL provides direct access to the Nexus Grid. Do not share.</p>
        </div>
    </div>
    <script>
        lucide.createIcons();
        function copyUrl(url) {
            navigator.clipboard.writeText(url).then(() => alert('Access URL Copied to Clipboard.'));
        }
    </script>
    <script>lucide.createIcons();</script>
</body>
</html>
