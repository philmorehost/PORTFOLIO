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
$seo = json_decode($project['seo_tags'], true) ?? [];
$profile = get_admin_profile($pdo);
$waNumber = $profile['whatsapp_number'] ?? '2348123456789';

$pageTitle = e($project['title']) . " | " . e($profile['full_name']);
$metaDesc = e($seo['metaDescription'] ?? substr($project['description'], 0, 160));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $metaDesc; ?>">
    <meta name="keywords" content="<?php echo e(implode(', ', $seo['keywords'] ?? [])); ?>">

    <!-- OG Tags -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $metaDesc; ?>">
    <meta property="og:image" content="<?php echo $project['screenshot_path']; ?>">
    <meta property="og:type" content="article">

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

    <nav id="main-nav" class="flex items-center justify-between px-6 md:px-12 py-6 border-b border-white/10 sticky top-0 bg-pitch-black/80 backdrop-blur-md z-50 nav-visible">
        <a href="/" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-sharp-orange rounded-xl flex items-center justify-center font-black italic text-black text-xl">P</div>
            <span class="text-xl font-black italic tracking-tighter uppercase"><?php echo strtoupper(explode(' ', $profile['full_name'] ?? 'P')[0]); ?>_CORE</span>
        </a>
        <div class="hidden md:flex items-center gap-3 text-[10px] font-mono text-text-dim uppercase tracking-[3px]">
            <span class="w-2 h-2 rounded-full bg-sharp-orange animate-pulse"></span>
            NODE_DETAIL_ACTIVE // <?php echo e($project['title']); ?>
        </div>
    </nav>

    <main data-barba="container" data-barba-namespace="project" class="px-6 md:px-12 py-10 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-16">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8 border-b border-white/5 pb-12">
                <div class="flex items-center gap-6">
                    <a href="/" class="p-4 rounded-2xl bg-white/5 border border-white/10 text-text-dim hover:text-sharp-orange hover:border-sharp-orange/50 transition-all shadow-xl group">
                        <i data-lucide="arrow-left" class="w-6 h-6 group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <div class="space-y-1">
                        <h1 class="text-4xl md:text-7xl font-black italic uppercase tracking-tighter text-glow-orange leading-none"><?php echo e($project['title']); ?></h1>
                        <p class="text-[10px] font-mono text-text-dim uppercase tracking-[5px] ml-1">Codex_Identifier: PB_<?php echo strtoupper(substr($project['slug'], 0, 8)); ?></p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <button onclick="openVault()" class="px-8 py-4 bg-glossy-purple/10 border border-glossy-purple/40 text-glossy-purple font-black rounded-2xl uppercase tracking-widest text-[11px] flex items-center gap-3 hover:bg-glossy-purple hover:text-white transition-all shadow-2xl">
                        <i data-lucide="terminal" class="w-5 h-5"></i> View_Architecture
                    </button>
                    <a href="<?php echo e($project['demo_link']); ?>" target="_blank" class="px-8 py-4 bg-white/5 border border-white/10 text-white font-black rounded-2xl uppercase tracking-widest text-[11px] flex items-center gap-3 hover:bg-white/10 transition-all">
                        <i data-lucide="external-link" class="w-5 h-5"></i> Live_Demo
                    </a>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_450px] gap-12">
                <!-- Preview Side -->
                <div class="space-y-12">
                    <div class="bg-black/60 border border-white/10 rounded-[2.5rem] overflow-hidden aspect-video relative group shadow-[0_0_50px_rgba(0,0,0,0.5)]">
                        <div class="absolute top-0 inset-x-0 h-10 bg-white/5 border-b border-white/10 flex items-center px-6 justify-between z-20">
                            <div class="flex gap-2">
                                <div class="w-2 h-2 rounded-full bg-white/20"></div>
                                <div class="w-2 h-2 rounded-full bg-white/20"></div>
                                <div class="w-2 h-2 rounded-full bg-white/20"></div>
                            </div>
                            <div class="text-[9px] font-mono text-text-dim truncate max-w-xs"><?php echo e($project['demo_link']); ?></div>
                        </div>
                        <iframe id="projectIframe" src="<?php echo e($project['demo_link']); ?>" class="w-full h-full border-0 pt-10 transition-all duration-700 ease-in-out" title="Live Preview" loading="lazy"></iframe>

                        <!-- Controls -->
                        <div class="absolute bottom-8 right-8 flex flex-col gap-3 opacity-0 group-hover:opacity-100 transition-all translate-y-4 group-hover:translate-y-0">
                            <button onclick="zoomIframe(1.2)" class="p-3 bg-black/80 backdrop-blur-xl rounded-xl border border-white/10 hover:text-sharp-orange transition-colors shadow-2xl"><i data-lucide="zoom-in" class="w-5 h-5"></i></button>
                            <button onclick="zoomIframe(1)" class="p-3 bg-black/80 backdrop-blur-xl rounded-xl border border-white/10 hover:text-sharp-orange transition-colors shadow-2xl"><i data-lucide="rotate-ccw" class="w-5 h-5"></i></button>
                            <button onclick="toggleViewMode()" class="p-3 bg-black/80 backdrop-blur-xl rounded-xl border border-white/10 hover:text-sharp-orange transition-colors shadow-2xl"><i data-lucide="smartphone" class="w-5 h-5"></i></button>
                        </div>
                    </div>

                    <!-- Tech Badges -->
                    <div class="space-y-6">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.4em] text-text-dim flex items-center gap-3 ml-2"><i data-lucide="layers" class="w-4 h-4"></i> Detected_System_Stack</h3>
                        <div class="flex flex-wrap gap-3">
                            <?php foreach ($tech as $t): ?>
                                <span class="px-6 py-3 bg-sharp-orange/10 border border-sharp-orange/30 text-sharp-orange text-[11px] font-black rounded-2xl uppercase tracking-[0.2em] shadow-[0_0_20px_rgba(255,102,0,0.1)] hover:bg-sharp-orange hover:text-black transition-all cursor-default">
                                    <?php echo e($t['name'] ?? $t); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Info Side -->
                <div class="space-y-10">
                    <!-- Tech Pulse -->
                    <div class="bg-white/5 border border-white/10 p-10 rounded-[2.5rem] space-y-8 glass-purple shadow-2xl">
                        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-glossy-purple flex items-center gap-3 border-b border-glossy-purple/20 pb-6"><i data-lucide="zap" class="w-5 h-5"></i> Tech_Pulse_Metrics</h3>
                        <div class="space-y-8">
                            <?php foreach ($perf as $label => $score): ?>
                                <div class="space-y-3">
                                    <div class="flex justify-between text-[11px] font-black uppercase tracking-[0.3em]">
                                        <span class="text-text-dim"><?php echo str_replace('_', ' ', $label); ?></span>
                                        <span class="text-glossy-purple text-glow-purple"><?php echo $score; ?>%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden border border-white/5">
                                        <div class="meter-fill" style="width: <?php echo $score; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Article -->
                    <div class="bg-white/5 border border-white/10 p-10 rounded-[2.5rem] space-y-6 shadow-xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <i data-lucide="quote" class="w-12 h-12 text-sharp-orange"></i>
                        </div>
                        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-sharp-orange flex items-center gap-3"><i data-lucide="file-text" class="w-5 h-5"></i> Power_Pitch</h3>
                        <div class="prose prose-invert prose-sm leading-relaxed text-white/70 italic font-medium">
                            <?php echo nl2br(e($project['description'])); ?>
                        </div>
                    </div>

                    <!-- CTA -->
                    <a href="https://wa.me/<?php echo e($waNumber); ?>?text=<?php echo urlencode($project['wa_custom_message']); ?>" target="_blank" class="block w-full py-6 bg-sharp-orange text-black font-black rounded-[1.5rem] uppercase tracking-[0.3em] text-[12px] text-center shadow-[0_0_40px_rgba(255,102,0,0.4)] hover:scale-[1.02] active:scale-95 transition-all">Establish_Connection</a>
                </div>
            </div>
        </div>
    </main>

    <!-- The Vault Modal -->
    <div id="vaultModal" class="fixed inset-0 z-[100] bg-black/95 backdrop-blur-3xl flex items-center justify-center p-6 opacity-0 pointer-events-none transition-all duration-700">
        <div class="max-w-5xl w-full bg-black/60 border border-glossy-purple/30 rounded-[3rem] overflow-hidden glass-purple shadow-[0_0_100px_rgba(191,0,255,0.2)]">
            <div class="p-8 border-b border-white/10 flex items-center justify-between bg-white/5">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-glossy-purple rounded-xl flex items-center justify-center text-white"><i data-lucide="terminal" class="w-5 h-5"></i></div>
                    <span class="text-sm font-black uppercase tracking-[0.3em]">The_Vault: System_Architecture</span>
                </div>
                <button onclick="closeVault()" class="p-3 rounded-xl bg-white/5 text-text-dim hover:text-white hover:bg-white/10 transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div class="p-10 max-h-[70vh] overflow-y-auto custom-scroll">
                <div class="code-terminal shadow-2xl">
                    <pre class="text-white/90 leading-relaxed"><code><?php echo e($project['code_snippet']); ?></code></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openVault() {
            const modal = document.getElementById('vaultModal');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            gsap.from("#vaultModal > div", { y: 100, opacity: 0, scale: 0.9, duration: 0.8, ease: "power4.out" });
        }

        function closeVault() {
            const modal = document.getElementById('vaultModal');
            gsap.to("#vaultModal > div", { y: 50, opacity: 0, duration: 0.5, onComplete: () => {
                modal.classList.add('opacity-0', 'pointer-events-none');
            }});
        }

        let zoom = 1;
        function zoomIframe(val) {
            zoom = val;
            const iframe = document.getElementById('projectIframe');
            iframe.style.transform = `scale(${zoom})`;
            iframe.style.transformOrigin = 'top center';
        }

        let isMobile = false;
        function toggleViewMode() {
            isMobile = !isMobile;
            const iframeCont = document.getElementById('projectIframe').parentElement;
            iframeCont.style.maxWidth = isMobile ? '400px' : '100%';
            iframeCont.style.margin = isMobile ? '0 auto' : '0';
            iframeCont.style.borderRadius = isMobile ? '3rem' : '2.5rem';
        }

        barba.init({
            transitions: [{
                name: 'fade',
                leave(data) { return gsap.to(data.current.container, { opacity: 0, scale: 0.98, duration: 0.5 }); },
                enter(data) {
                    lucide.createIcons();
                    window.scrollTo(0, 0);
                    return gsap.from(data.next.container, { opacity: 0, scale: 1.02, duration: 0.5, clearProps: "all" });
                }
            }]
        });
    </script>
</body>
</html>
