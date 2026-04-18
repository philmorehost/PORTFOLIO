<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = ?");
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) { header("Location: /"); exit; }

$tech = json_decode($project['tech_stack'], true) ?? [];
$perf = json_decode($project['performance_scores'], true) ?? ['security' => 98, 'ui_ux' => 95, 'scalability' => 90];
$profile = get_admin_profile($pdo);
$waNumber = $profile['whatsapp_number'] ?? '2348123456789';

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
            <span class="text-xl font-black italic tracking-tighter uppercase"><?php echo strtoupper(explode(' ', $profile['full_name'] ?? 'P')[0]); ?>_CORE</span>
        </a>
        <div class="text-[10px] font-mono text-text-dim uppercase tracking-widest hidden md:block">
            NODE_ACTIVE: <?php echo e($project['title']); ?> // DEEPSEEK_V3_SYNC
        </div>
    </nav>

    <main data-barba="container" data-barba-namespace="project" class="px-4 md:px-10 py-10 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-12">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <a href="/" class="p-2 rounded-full bg-white/5 border border-white/10 text-text-dim hover:text-sharp-orange transition-all"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
                    <h1 class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-glow-orange"><?php echo e($project['title']); ?></h1>
                </div>
                <div class="flex gap-3">
                    <button onclick="openVault()" class="px-6 py-3 bg-glossy-purple/10 border border-glossy-purple text-glossy-purple font-black rounded-lg uppercase tracking-widest text-[10px] flex items-center gap-2 hover:bg-glossy-purple hover:text-white transition-all">
                        <i data-lucide="terminal" class="w-4 h-4"></i> View Logic
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-8">
                <div class="space-y-8">
                    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden aspect-video relative group">
                        <iframe id="projectIframe" src="<?php echo e($project['demo_link']); ?>" class="w-full h-full border-0 transition-transform duration-500" title="Live Preview" loading="lazy"></iframe>
                        <div class="absolute top-4 right-4 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="zoomIframe(1.1)" class="p-2 bg-black/60 rounded border border-white/10 hover:text-sharp-orange transition-colors"><i data-lucide="zoom-in" class="w-4 h-4"></i></button>
                            <button onclick="zoomIframe(1)" class="p-2 bg-black/60 rounded border border-white/10 hover:text-sharp-orange transition-colors"><i data-lucide="rotate-ccw" class="w-4 h-4"></i></button>
                            <button onclick="toggleViewMode()" class="p-2 bg-black/60 rounded border border-white/10 hover:text-sharp-orange transition-colors"><i data-lucide="smartphone" class="w-4 h-4"></i></button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($tech as $t): ?>
                            <span class="px-4 py-2 bg-sharp-orange/10 border border-sharp-orange text-sharp-orange text-[10px] font-black rounded-full uppercase tracking-widest shadow-[0_0_15px_rgba(255,102,0,0.15)]">
                                <?php echo e($t['name'] ?? $t); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-white/5 border border-white/10 p-8 rounded-2xl space-y-6 glass-purple">
                        <h3 class="text-xs font-black uppercase tracking-[0.4em] text-glossy-purple flex items-center gap-2"><i data-lucide="zap" class="w-4 h-4"></i> Tech Pulse</h3>
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

                    <div class="bg-white/5 border border-white/10 p-8 rounded-2xl space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-[0.4em] text-sharp-orange flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4"></i> Power Pitch</h3>
                        <div class="prose prose-invert prose-sm leading-relaxed text-white/70 italic">
                            <?php echo nl2br(e($project['description'])); ?>
                        </div>
                    </div>

                    <a href="https://wa.me/<?php echo e($waNumber); ?>?text=<?php echo urlencode($project['wa_custom_message']); ?>" target="_blank" class="block w-full py-5 bg-sharp-orange text-black font-black rounded-xl uppercase tracking-widest text-sm text-center shadow-[0_0_30px_rgba(255,102,0,0.3)] hover:scale-[1.02] transition-all">Get Solution Like This</a>
                </div>
            </div>
        </div>
    </main>

    <!-- The Vault Modal -->
    <div id="vaultModal" class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-xl flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-500">
        <div class="max-w-4xl w-full bg-white/5 border border-glossy-purple rounded-2xl overflow-hidden glass-purple">
            <div class="p-6 border-b border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i data-lucide="terminal" class="w-5 h-5 text-glossy-purple"></i>
                    <span class="text-sm font-black uppercase tracking-[0.2em]">The Vault: System Architecture</span>
                </div>
                <button onclick="closeVault()" class="text-text-dim hover:text-white transition-colors"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div class="p-8">
                <div class="code-terminal">
                    <pre class="text-white/80"><code><?php echo e($project['code_snippet']); ?></code></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openVault() {
            const modal = document.getElementById('vaultModal');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            gsap.from("#vaultModal > div", { y: 50, opacity: 0, duration: 0.5 });
        }

        function closeVault() {
            const modal = document.getElementById('vaultModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
        }

        let zoom = 1;
        function zoomIframe(val) {
            zoom = val;
            document.getElementById('projectIframe').style.transform = `scale(${zoom})`;
        }

        let isMobile = false;
        function toggleViewMode() {
            isMobile = !isMobile;
            const iframe = document.getElementById('projectIframe').parentElement;
            iframe.style.width = isMobile ? '375px' : '100%';
            iframe.style.margin = isMobile ? '0 auto' : '0';
        }

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
