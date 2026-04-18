<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = ?");
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: /");
    exit;
}

$seo = json_decode($project['seo_data'], true);
$tech = json_decode($project['tech_stack'], true);
$gallery = json_decode($project['gallery_images'], true) ?? [];
$access = json_decode($project['access_points'], true) ?? [];
$demo = json_decode($project['demo_login'], true) ?? [];
$perf = json_decode($project['performance'], true) ?? ['speed' => 95, 'security' => 98];

$appTitle = get_setting($pdo, 'appTitle', 'CYBER-PULSE');
$waNumber = get_setting($pdo, 'waNumber', '2348123456789');
$titleParts = explode('-', $appTitle);
$mainTitle = trim($titleParts[0]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($project['title']); ?> | <?php echo e($appTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'pitch-black': '#000000',
                        'sharp-orange': '#FF6600',
                        'glossy-purple': '#BF00FF',
                        'text-dim': '#888888',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/inter-ui/3.19.3/inter.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/@barba/core"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body class="bg-pitch-black text-white" data-barba="wrapper">

    <nav class="flex items-center justify-between px-4 md:px-10 py-6 border-b border-white/10 sticky top-0 bg-pitch-black/80 backdrop-blur-md z-50">
        <a href="/" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-sharp-orange rounded-lg flex items-center justify-center font-black italic text-black">P</div>
            <span class="text-xl font-black italic tracking-tighter uppercase"><?php echo e($mainTitle); ?></span>
        </a>
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 text-[10px] font-mono text-text-dim uppercase tracking-[2px]">
                <span class="w-2 h-2 rounded-full bg-sharp-orange animate-pulse"></span>
                Node_Detail_View
            </div>
        </div>
    </nav>

    <main data-barba="container" data-barba-namespace="project" class="px-4 md:px-10 py-5">
        <div class="space-y-8 pb-20 mt-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 h-auto">
                <div class="space-y-2">
                    <a href="/" class="inline-flex items-center gap-2 text-[11px] font-mono text-text-dim hover:text-sharp-orange transition-colors uppercase tracking-[0.2em]">
                        <i data-lucide="arrow-left" class="w-3 h-3"></i> Back to Grid
                    </a>
                    <div class="flex items-center gap-4">
                        <h1 class="text-4xl md:text-5xl font-black italic tracking-tighter uppercase whitespace-nowrap">
                            <?php echo e($project['title']); ?>
                        </h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase flex items-center gap-2 border <?php echo $project['type'] === 'web' ? 'border-sharp-orange/20 text-sharp-orange' : 'border-glossy-purple/20 text-glossy-purple'; ?>">
                            <i data-lucide="<?php echo $project['type'] === 'web' ? 'globe' : 'smartphone'; ?>" class="w-3 h-3"></i>
                            <?php echo $project['type'] === 'web' ? 'Web Solution' : 'App Engineering'; ?>
                        </div>
                    </div>
                </div>
                <div class="text-[11px] text-text-dim font-mono hidden md:block">
                    CODEX_ID: PB_<?php echo strtoupper(substr($project['slug'], 0, 4)); ?> // LATENCY: 24ms // ENCRYPTION: AES-256
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-[20px]">
                <!-- Left: Preview -->
                <div class="flex flex-col bg-white/5 border border-white/10 rounded-[12px] overflow-hidden">
                    <div class="h-[40px] border-b border-white/10 px-[15px] flex items-center justify-between">
                        <div class="flex items-center gap-[8px]">
                            <div class="w-2 h-2 rounded-full bg-white/20"></div>
                            <div class="w-2 h-2 rounded-full bg-white/20"></div>
                            <div class="w-2 h-2 rounded-full bg-white/20"></div>
                            <div class="ml-5 text-[11px] font-mono text-text-dim truncate max-w-[200px] md:max-w-md"><?php echo e($project['url']); ?></div>
                        </div>
                    </div>

                    <div class="flex-1 bg-[#111] m-[15px] rounded-[4px] border border-white/5 relative overflow-hidden h-[500px] md:h-[700px]">
                        <iframe src="<?php echo e($project['url']); ?>" class="w-full h-full border-0" title="Preview" loading="lazy"></iframe>
                    </div>
                </div>

                <!-- Right: Sidebar -->
                <div class="flex flex-col gap-[20px]">
                    <!-- Access Points -->
                    <?php if (!empty($access)): ?>
                        <div class="bg-white/5 border border-white/10 p-6 rounded-[12px] space-y-6">
                            <span class="text-[11px] font-black uppercase tracking-[0.3em] text-sharp-orange flex items-center gap-2">
                                <i data-lucide="terminal" class="w-3 h-3"></i> One-Click Access Nodes
                            </span>
                            <div class="grid grid-cols-1 gap-3">
                                <?php if (isset($access['superAdmin'])): ?>
                                    <a href="<?php echo e($access['superAdmin']['directLoginUrl'] ?: $access['superAdmin']['url']); ?>" target="_blank" class="flex items-center justify-between bg-black/40 p-4 rounded-xl border border-white/5 hover:border-sharp-orange transition-all">
                                        <div class="flex items-center gap-3">
                                            <i data-lucide="crown" class="w-4 h-4 text-sharp-orange"></i>
                                            <div class="text-left">
                                                <div class="text-[10px] font-black text-white uppercase tracking-widest">Level 0: Super Admin</div>
                                            </div>
                                        </div>
                                        <i data-lucide="mouse-pointer-2" class="w-4 h-4 text-text-dim"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- AI Content -->
                    <div class="glass-purple p-[24px] rounded-[12px]">
                        <h2 class="text-[24px] font-bold mb-[8px] tracking-tight"><?php echo e($project['title']); ?></h2>
                        <div class="text-[14px] leading-[1.6] text-white/80 mb-[20px] font-medium prose prose-invert prose-sm">
                            <?php echo nl2br(e($project['content'])); ?>
                        </div>
                        <div class="flex flex-wrap gap-[8px]">
                            <?php if (!empty($tech)): ?>
                                <?php foreach ($tech as $t): ?>
                                    <span class="font-mono text-[11px] px-[10px] py-[4px] bg-glossy-purple/20 border border-glossy-purple rounded-[4px] text-[#E0A0FF]">
                                        <?php echo e($t['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-[15px]">
                        <div class="bg-white/5 border border-white/10 p-[15px] rounded-[8px]">
                            <div class="text-[10px] uppercase text-text-dim mb-[5px] font-bold tracking-wider">Pulse</div>
                            <div class="text-[18px] font-bold font-mono text-glow-orange"><?php echo e($project['inquiries_count']); ?></div>
                        </div>
                        <div class="bg-white/5 border border-white/10 p-[15px] rounded-[8px]">
                            <div class="text-[10px] uppercase text-text-dim mb-[5px] font-bold tracking-wider">Status</div>
                            <div class="text-[18px] font-bold font-mono text-[#00FF00]">LIVE</div>
                        </div>
                    </div>

                    <!-- Performance -->
                    <div class="bg-white/5 border border-white/10 rounded-[12px] p-[20px] glass-purple">
                        <div class="flex justify-between items-end mb-4">
                            <span class="text-[10px] uppercase text-text-dim font-bold tracking-widest">Live Performance Node</span>
                            <span class="text-[24px] font-black font-mono text-glossy-purple"><?php echo $perf['speed']; ?>%</span>
                        </div>
                        <div class="h-[6px] w-full bg-white/10 rounded-full overflow-hidden">
                            <div class="meter-fill" style="width: <?php echo $perf['speed']; ?>%"></div>
                        </div>
                    </div>

                    <!-- WhatsApp CTA -->
                    <a href="https://wa.me/<?php echo e($waNumber); ?>?text=<?php echo urlencode($project['wa_message']); ?>" target="_blank" class="w-full bg-sharp-orange text-black py-[18px] rounded-[8px] font-extrabold text-[14px] uppercase tracking-[1px] flex items-center justify-center gap-[10px]">
                        <i data-lucide="message-square" class="w-5 h-5"></i>
                        Get <?php echo $project['type'] === 'web' ? 'Site' : 'App'; ?> Like This
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
