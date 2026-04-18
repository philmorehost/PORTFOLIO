<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();

$csrf_token = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['update_nexus'])) {
        update_setting($pdo, 'active_ai_provider', $_POST['active_ai_provider']);
        update_setting($pdo, 'deepseek_api_key', $_POST['deepseek_api_key']);
        update_setting($pdo, 'gemini_api_key', $_POST['gemini_api_key']);
        update_setting($pdo, 'google_psi_key', $_POST['google_psi_key']);
        update_setting($pdo, 'deepseek_base_url', $_POST['deepseek_base_url']);
        $success = "Nexus configurations updated.";
    }
}

$active_provider = get_setting($pdo, 'active_ai_provider', 'gemini');
$deepseek_key = get_setting($pdo, 'deepseek_api_key', '');
$gemini_key = get_setting($pdo, 'gemini_api_key', '');
$psi_key = get_setting($pdo, 'google_psi_key', '');
$deepseek_base = get_setting($pdo, 'deepseek_base_url', 'https://api.deepseek.com');

$stmt = $pdo->query("SELECT * FROM api_logs ORDER BY created_at DESC LIMIT 10");
$logs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nexus API Manager | Portfolio 1.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
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

        <?php if (isset($success)): ?>
            <div class="bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded text-xs text-center font-bold">
                <?php echo e($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <!-- Provider Toggle -->
            <div class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
                <h2 class="text-xs font-black text-glossy-purple uppercase tracking-[0.4em] flex items-center gap-2">
                    <i data-lucide="cpu" class="w-4 h-4"></i> Core Intelligence Provider
                </h2>
                <div class="flex gap-4">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="active_ai_provider" value="deepseek" <?php echo $active_provider === 'deepseek' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-4 border border-white/10 rounded-xl bg-black/40 peer-checked:border-glossy-purple peer-checked:bg-glossy-purple/10 transition-all text-center">
                            <div class="font-black uppercase italic tracking-tighter text-sm mb-1">DeepSeek R1/V3</div>
                            <div class="text-[9px] text-text-dim uppercase font-mono">High-Reasoning API</div>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="active_ai_provider" value="gemini" <?php echo $active_provider === 'gemini' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-4 border border-white/10 rounded-xl bg-black/40 peer-checked:border-glossy-purple peer-checked:bg-glossy-purple/10 transition-all text-center">
                            <div class="font-black uppercase italic tracking-tighter text-sm mb-1">Google Gemini</div>
                            <div class="text-[9px] text-text-dim uppercase font-mono">Fast Multimodal</div>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="active_ai_provider" value="manual" <?php echo $active_provider === 'manual' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-4 border border-white/10 rounded-xl bg-black/40 peer-checked:border-sharp-orange peer-checked:bg-sharp-orange/10 transition-all text-center">
                            <div class="font-black uppercase italic tracking-tighter text-sm mb-1">Manual Mode</div>
                            <div class="text-[9px] text-text-dim uppercase font-mono">Zero API Usage</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Key Management -->
            <div class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
                <h2 class="text-xs font-black text-glossy-purple uppercase tracking-[0.4em] flex items-center gap-2">
                    <i data-lucide="key" class="w-4 h-4"></i> Secure Key Storage
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">DeepSeek API Key</label>
                        <input type="password" name="deepseek_api_key" value="<?php echo e($deepseek_key); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-glossy-purple font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">DeepSeek Base URL</label>
                        <input type="text" name="deepseek_base_url" value="<?php echo e($deepseek_base); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-glossy-purple font-mono" placeholder="https://api.deepseek.com">
                    </div>
                    <div class="h-px bg-white/5"></div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Gemini API Key</label>
                        <input type="password" name="gemini_api_key" value="<?php echo e($gemini_key); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-glossy-purple font-mono">
                    </div>
                    <div class="h-px bg-white/5"></div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Google PSI Key (PageSpeed)</label>
                        <input type="password" name="google_psi_key" value="<?php echo e($psi_key); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange font-mono">
                    </div>
                </div>
            </div>

            <button type="submit" name="update_nexus" class="w-full py-5 bg-glossy-purple text-white font-black rounded-xl uppercase tracking-widest text-sm shadow-[0_0_30px_rgba(191,0,255,0.3)] hover:brightness-110 active:scale-95 transition-all">Synchronize Nexus Configuration</button>
        </form>

        <!-- Usage Monitor -->
        <div class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
            <h2 class="text-xs font-black text-text-dim uppercase tracking-[0.4em] flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-sharp-orange"></i> Nexus Usage Monitor (Last 10)
            </h2>
            <div class="space-y-2">
                <?php foreach ($logs as $log): ?>
                    <div class="flex items-center justify-between p-3 bg-black/40 rounded-lg border border-white/5 text-[11px] font-mono uppercase">
                        <div class="flex items-center gap-4">
                            <span class="text-glossy-purple font-bold"><?php echo e($log['provider']); ?></span>
                            <span class="text-text-dim"><?php echo e($log['endpoint']); ?></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="<?php echo $log['status'] === 'success' ? 'text-green-500' : 'text-red-500'; ?>"><?php echo e($log['status']); ?></span>
                            <span class="text-text-dim"><?php echo e($log['response_time']); ?>ms</span>
                            <span class="text-text-dim"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                    <div class="text-center py-4 text-text-dim font-mono text-[10px]">NO_LOGS_CAPTURED</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
