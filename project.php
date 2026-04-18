<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = ?");
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) { header("Location: /"); exit; }

$seo = json_decode($project['seo_data'], true);
$tech = json_decode($project['tech_stack'], true);
$perf = json_decode($project['performance_scores'], true) ?? ['security' => 98, 'ui_ux' => 95, 'scalability' => 90];
$waNumber = get_setting($pdo, 'waNumber', '2348123456789');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($project['title']); ?></title>
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
    <link rel="stylesheet" href="/assets/css/theme.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/@barba/core"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body class="bg-pitch-black text-white" data-barba="wrapper">

    <nav id="main-nav" class="flex items-center justify-between px-4 md:px-10 py-6 border-b border-white/10 sticky top-0 bg-pitch-black/80 backdrop-blur-md z-50">
        <a href="/" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-sharp-orange rounded-lg flex items-center justify-center font-black italic text-black">P</div>
            <span class="text-xl font-black italic tracking-tighter uppercase"><?php echo strtoupper(explode('-', get_setting($pdo, 'appTitle', 'CYBER-PULSE'))[0]); ?></span>
        </a>
        <div class="text-[10px] font-mono text-text-dim uppercase tracking-widest hidden md:block">
            NODE_ACTIVE: <?php echo e($project['title']); ?> // DEEPSEEK_V3_SYNC
        </div>
    </nav>

    <main data-barba="container" data-barba-namespace="project" class="px-4 md:px-10 py-10 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-12">
            <div class="flex items-center gap-4">
                <a href="/" class="p-2 rounded-full bg-white/5 border border-white/10 text-text-dim hover:text-sharp-orange transition-all"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
                <h1 class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-glow-orange"><?php echo e($project['title']); ?></h1>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-8">
                <!-- Project Preview -->
                <div class="space-y-8">
                    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden aspect-video relative group">
                        <iframe src="<?php echo e($project['url']); ?>" class="w-full h-full border-0" title="Live Preview" loading="lazy"></iframe>
                        <div class="absolute top-4 right-4 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="p-2 bg-black/60 rounded border border-white/10 hover:text-sharp-orange transition-colors"><i data-lucide="zoom-in" class="w-4 h-4"></i></button>
                            <button class="p-2 bg-black/60 rounded border border-white/10 hover:text-sharp-orange transition-colors"><i data-lucide="monitor" class="w-4 h-4"></i></button>
                        </div>
                    </div>

                    <!-- Ghost Code Snippet -->
                    <?php if ($project['code_snippet']): ?>
                        <div class="space-y-4">
                            <h3 class="text-xs font-black uppercase tracking-[0.4em] text-text-dim flex items-center gap-2"><i data-lucide="terminal" class="w-4 h-4 text-sharp-orange"></i> Ghost Code Snippet</h3>
                            <div class="code-terminal">
                                <pre class="text-white/80"><code><?php echo e($project['code_snippet']); ?></code></pre>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar Insights -->
                <div class="space-y-8">
                    <!-- Tech Pulse Scorecard -->
                    <div class="bg-white/5 border border-white/10 p-8 rounded-2xl space-y-6 glass-purple">
                        <h3 class="text-xs font-black uppercase tracking-[0.4em] text-glossy-purple flex items-center gap-2"><i data-lucide="zap" class="w-4 h-4"></i> Tech Pulse Scorecard</h3>
                        <div class="space-y-4">
                            <?php foreach ($perf as $label => $score): ?>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest">
                                        <span class="text-text-dim"><?php echo str_replace('_', ' ', $label); ?></span>
                                        <span class="text-glossy-purple"><?php echo $score; ?>%</span>
                                    </div>
                                    <div class="h-1 w-full bg-white/10 rounded-full overflow-hidden">
                                        <div class="meter-fill" style="width: <?php echo $score; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Article Content -->
                    <div class="bg-white/5 border border-white/10 p-8 rounded-2xl space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-[0.4em] text-sharp-orange flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4"></i> Power Pitch</h3>
                        <div class="prose prose-invert prose-sm leading-relaxed text-white/70 italic">
                            <?php echo nl2br(e($project['content'])); ?>
                        </div>
                    </div>

                    <!-- CTA -->
                    <a href="https://wa.me/<?php echo e($waNumber); ?>?text=<?php echo urlencode($project['wa_message']); ?>" target="_blank" class="block w-full py-5 bg-sharp-orange text-black font-black rounded-xl uppercase tracking-widest text-sm text-center shadow-[0_0_30px_rgba(255,102,0,0.3)] hover:scale-[1.02] transition-all">Get Solution Like This</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
        barba.init({
            transitions: [{
                name: 'fade',
                leave(data) { return gsap.to(data.current.container, { opacity: 0, y: 10 }); },
                enter(data) {
                    lucide.createIcons();
                    window.scrollTo(0, 0);
                    return gsap.from(data.next.container, { opacity: 0, y: 10 });
                }
            }]
        });
    </script>
</body>
</html>
