<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();

$csrf_token = generate_csrf_token();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['update_nexus'])) {
        $provider = $_POST['provider'];
        $ds_key = $_POST['deepseek_key'];
        $gm_key = $_POST['gemini_key'];
        $psi_key = $_POST['psi_key'];
        $ds_base = $_POST['deepseek_base_url'];

        $stmt = $pdo->prepare("UPDATE api_settings SET provider=?, deepseek_key=?, gemini_key=?, psi_key=?, deepseek_base_url=? WHERE id=1");
        $stmt->execute([$provider, $ds_key, $gm_key, $psi_key, $ds_base]);
        $success = "Nexus configurations updated.";
    }
}

$api = get_api_settings($pdo);

$stmt = $pdo->query("SELECT * FROM api_logs ORDER BY created_at DESC LIMIT 10");
$logs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nexus API Manager | Portfolio 1.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/theme.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-pitch-black text-white p-4 md:p-10">
    <div class="max-w-4xl mx-auto space-y-12 pb-20">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl md:text-5xl font-black italic uppercase text-glow-purple">Nexus <span class="text-glossy-purple">API Manager</span></h1>
            <a href="/admin" class="text-text-dim hover:text-sharp-orange transition-colors flex items-center gap-2 font-black uppercase text-xs">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Console
            </a>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded text-xs text-center font-bold"><?php echo e($success); ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
                <h2 class="text-xs font-black text-glossy-purple uppercase tracking-[0.4em] flex items-center gap-2">
                    <i data-lucide="cpu" class="w-4 h-4"></i> Primary Engine Selection
                </h2>
                <div class="flex gap-4">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="provider" value="deepseek" <?php echo $api['provider'] === 'deepseek' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-4 border border-white/10 rounded-xl bg-black/40 peer-checked:border-glossy-purple peer-checked:bg-glossy-purple/10 transition-all text-center">
                            <div class="font-black uppercase italic tracking-tighter text-sm mb-1">DeepSeek R1/V3</div>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="provider" value="gemini" <?php echo $api['provider'] === 'gemini' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-4 border border-white/10 rounded-xl bg-black/40 peer-checked:border-glossy-purple peer-checked:bg-glossy-purple/10 transition-all text-center">
                            <div class="font-black uppercase italic tracking-tighter text-sm mb-1">Google Gemini</div>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="provider" value="manual" <?php echo $api['provider'] === 'manual' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-4 border border-white/10 rounded-xl bg-black/40 peer-checked:border-sharp-orange peer-checked:bg-sharp-orange/10 transition-all text-center">
                            <div class="font-black uppercase italic tracking-tighter text-sm mb-1">Manual Mode</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
                <h2 class="text-xs font-black text-glossy-purple uppercase tracking-[0.4em] flex items-center gap-2">
                    <i data-lucide="key" class="w-4 h-4"></i> Secure Key Storage
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">DeepSeek API Key</label>
                        <input type="password" name="deepseek_key" value="<?php echo e($api['deepseek_key']); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-glossy-purple font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">DeepSeek Base URL</label>
                        <input type="text" name="deepseek_base_url" value="<?php echo e($api['deepseek_base_url']); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-glossy-purple font-mono">
                    </div>
                    <div class="h-px bg-white/5"></div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Gemini API Key</label>
                        <input type="password" name="gemini_key" value="<?php echo e($api['gemini_key']); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-glossy-purple font-mono">
                    </div>
                    <div class="h-px bg-white/5"></div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Google PSI Key (PageSpeed)</label>
                        <input type="password" name="psi_key" value="<?php echo e($api['psi_key']); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange font-mono">
                    </div>
                </div>
            </div>

            <button type="submit" name="update_nexus" class="w-full py-5 bg-glossy-purple text-white font-black rounded-xl uppercase tracking-widest text-sm shadow-[0_0_30px_rgba(191,0,255,0.3)] hover:brightness-110 active:scale-95 transition-all">Update Nexus</button>
        </form>

        <div class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
            <h2 class="text-xs font-black text-text-dim uppercase tracking-[0.4em] flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-sharp-orange"></i> Last 10 Logs
            </h2>
            <div class="space-y-2">
                <?php foreach ($logs as $log): ?>
                    <div class="flex items-center justify-between p-3 bg-black/40 rounded-lg border border-white/5 text-[11px] font-mono uppercase">
                        <span class="text-glossy-purple font-bold"><?php echo e($log['provider']); ?></span>
                        <span class="<?php echo $log['status'] === 'success' ? 'text-green-500' : 'text-red-500'; ?>"><?php echo e($log['status']); ?></span>
                        <span class="text-text-dim"><?php echo e($log['response_time']); ?>ms</span>
                        <span class="text-text-dim"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
