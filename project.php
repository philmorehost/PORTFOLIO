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
$gallery = json_decode($project['gallery'], true) ?? [];
$demoAccess = json_decode($project['demo_access'], true) ?? [];
$perf = json_decode($project['performance_scores'], true) ?? ['security' => 98, 'ui_ux' => 95, 'scalability' => 90];
$seo = json_decode($project['seo_tags'], true) ?? [];
$profile = get_admin_profile($pdo);
$waNumber = $profile['whatsapp_number'] ?? '2348123456789';
$baseUrl = get_base_url();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($project['title']); ?> | <?php echo e($profile['full_name']); ?></title>

    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/@barba/core"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body class="bg-pitch-black text-white selection:bg-sharp-orange" data-barba="wrapper">

    <nav id="main-nav" class="flex items-center justify-between px-6 md:px-12 py-6 border-b border-white/10 sticky top-0 bg-pitch-black/80 backdrop-blur-md z-[100]">
        <a href="/" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-sharp-orange rounded-xl flex items-center justify-center font-black italic text-black text-xl shadow-lg">P</div>
            <span class="text-xl font-black italic uppercase hidden sm:block"><?php echo strtoupper(explode(' ', $profile['full_name'])[0]); ?>_CORE</span>
        </a>
        <div class="text-[10px] font-mono text-text-dim uppercase tracking-widest hidden md:block">
            NODE_ACTIVE: <?php echo e($project['title']); ?> // AI_V3_SYNC
        </div>
    </nav>

    <main data-barba="container" data-barba-namespace="project" class="px-6 md:px-12 py-10 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-12">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8 border-b border-white/5 pb-12">
                <div class="flex items-center gap-6">
                    <a href="/" class="p-4 rounded-2xl bg-white/5 border border-white/10 text-text-dim hover:text-sharp-orange transition-all group shadow-xl">
                        <i data-lucide="arrow-left" class="w-6 h-6 group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <h1 class="text-4xl md:text-7xl font-black italic uppercase tracking-tighter text-glow-orange leading-none"><?php echo e($project['title']); ?></h1>
                </div>
                <button onclick="openVault()" class="px-8 py-4 bg-glossy-purple/10 border border-glossy-purple/40 text-glossy-purple font-black rounded-2xl uppercase tracking-widest text-[11px] flex items-center gap-3 hover:bg-glossy-purple hover:text-white transition-all shadow-2xl">
                    <i data-lucide="terminal" class="w-5 h-5"></i> View_Architecture
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_450px] gap-12">
                <div class="space-y-12">
                    <div class="bg-black/60 border border-white/10 rounded-[2.5rem] overflow-hidden aspect-video relative group shadow-2xl">
                        <iframe id="projectIframe" src="<?php echo e($project['demo_link']); ?>" class="w-full h-full border-0 transition-all duration-1000 ease-in-out" title="Live Preview" loading="lazy"></iframe>
                        <div class="absolute bottom-8 right-8 flex flex-col gap-3 opacity-0 group-hover:opacity-100 transition-all">
                            <button onclick="zoomIframe(1.2)" class="p-3 bg-black/80 backdrop-blur-xl rounded-xl border border-white/10 hover:text-sharp-orange transition-colors"><i data-lucide="zoom-in" class="w-5 h-5"></i></button>
                            <button onclick="zoomIframe(1)" class="p-3 bg-black/80 backdrop-blur-xl rounded-xl border border-white/10 hover:text-sharp-orange transition-colors"><i data-lucide="rotate-ccw" class="w-5 h-5"></i></button>
                            <button onclick="toggleViewMode()" class="p-3 bg-black/80 backdrop-blur-xl rounded-xl border border-white/10 hover:text-sharp-orange transition-colors"><i data-lucide="smartphone" class="w-5 h-5"></i></button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($tech as $t): ?>
                            <span class="px-6 py-3 bg-sharp-orange/10 border border-sharp-orange/30 text-sharp-orange text-[11px] font-black rounded-2xl uppercase tracking-[0.2em] shadow-lg hover:bg-sharp-orange hover:text-black transition-all cursor-default">
                                <?php echo e($t['name'] ?? $t); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($gallery)): ?>
                    <div class="space-y-6 pt-6">
                        <h3 class="text-[10px] font-black uppercase text-white/40 tracking-[0.4em]">Visual_Manifest</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                            <?php foreach ($gallery as $img): ?>
                                <div class="aspect-square rounded-2xl overflow-hidden border border-white/10 hover:border-sharp-orange transition-all cursor-zoom-in" onclick="viewImage('<?php echo $img; ?>')">
                                    <img src="<?php echo $img; ?>" class="w-full h-full object-cover">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-10">
                    <div class="bg-white/5 border border-white/10 p-10 rounded-[2.5rem] space-y-8 glass-purple shadow-2xl">
                        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-glossy-purple flex items-center gap-3 pb-6 border-b border-white/5"><i data-lucide="zap" class="w-5 h-5"></i> Tech_Pulse</h3>
                        <div class="space-y-8">
                            <?php foreach ($perf as $label => $score): ?>
                                <div class="space-y-3">
                                    <div class="flex justify-between text-[11px] font-black uppercase tracking-[0.3em]">
                                        <span class="text-text-dim"><?php echo str_replace('_', ' ', $label); ?></span>
                                        <span class="text-glossy-purple"><?php echo $score; ?>%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden border border-white/5">
                                        <div class="meter-fill" style="width: <?php echo $score; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white/5 border border-white/10 p-10 rounded-[2.5rem] space-y-6 shadow-xl relative overflow-hidden">
                        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-sharp-orange flex items-center gap-3"><i data-lucide="file-text" class="w-5 h-5"></i> Power_Pitch</h3>
                        <div class="prose prose-invert prose-sm leading-relaxed text-white/70 italic font-medium">
                            <?php echo nl2br(e($project['description'])); ?>
                        </div>
                    </div>

                    <?php
                    $hasDemo = false;
                    foreach(['l0', 'l1', 'l2'] as $l) {
                        if (!empty($demoAccess[$l]['url']) || !empty($demoAccess[$l]['bypass'])) {
                            $hasDemo = true; break;
                        }
                    }
                    if ($hasDemo): ?>
                    <div class="bg-white/5 border border-white/10 p-10 rounded-[2.5rem] space-y-8 shadow-xl">
                        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-sharp-orange flex items-center gap-3"><i data-lucide="shield-check" class="w-5 h-5"></i> Multi-Tier Demo Access</h3>

                        <div class="space-y-6">
                            <?php
                            $levels = [
                                'l0' => ['name' => 'Level 0: Super Admin', 'color' => 'glossy-purple'],
                                'l1' => ['name' => 'Level 1: Restricted', 'color' => 'sharp-orange'],
                                'l2' => ['name' => 'Level 2: Standard', 'color' => 'white']
                            ];
                            foreach($levels as $key => $info):
                                if(empty($demoAccess[$key]['url']) && empty($demoAccess[$key]['bypass'])) continue;
                                $target = !empty($demoAccess[$key]['bypass']) ? $demoAccess[$key]['bypass'] : $demoAccess[$key]['url'];
                            ?>
                                <div class="p-6 rounded-2xl bg-black/40 border border-white/5 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-<?php echo $info['color']; ?>"></div>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-white/60"><?php echo $info['name']; ?></span>
                                        </div>
                                        <a href="<?php echo e($target); ?>" target="_blank" class="px-4 py-2 bg-<?php echo $info['color']; ?> <?php echo $key === 'l2' ? 'text-black' : ($key === 'l0' ? 'text-white' : 'text-black'); ?> rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-2 hover:scale-105 transition-all">
                                            <?php echo !empty($demoAccess[$key]['bypass']) ? 'One-Click Access' : 'Launch Demo'; ?>
                                            <i data-lucide="external-link" class="w-3 h-3"></i>
                                        </a>
                                    </div>

                                    <?php if(!empty($demoAccess[$key]['user']) || !empty($demoAccess[$key]['pass'])): ?>
                                        <div class="grid grid-cols-2 gap-4 pt-2">
                                            <?php if(!empty($demoAccess[$key]['user'])): ?>
                                                <div class="bg-black/60 p-3 rounded-xl border border-white/5">
                                                    <div class="text-[8px] text-text-dim uppercase mb-1">Username</div>
                                                    <div class="text-[10px] font-mono text-white flex items-center justify-between">
                                                        <span><?php echo e($demoAccess[$key]['user']); ?></span>
                                                        <button onclick="navigator.clipboard.writeText('<?php echo e($demoAccess[$key]['user']); ?>')" class="hover:text-sharp-orange"><i data-lucide="copy" class="w-3 h-3"></i></button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if(!empty($demoAccess[$key]['pass'])): ?>
                                                <div class="bg-black/60 p-3 rounded-xl border border-white/5">
                                                    <div class="text-[8px] text-text-dim uppercase mb-1">Password</div>
                                                    <div class="text-[10px] font-mono text-white flex items-center justify-between">
                                                        <span><?php echo e($demoAccess[$key]['pass']); ?></span>
                                                        <button onclick="navigator.clipboard.writeText('<?php echo e($demoAccess[$key]['pass']); ?>')" class="hover:text-sharp-orange"><i data-lucide="copy" class="w-3 h-3"></i></button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($demoAccess[$key]['note'])): ?>
                                        <div class="bg-glossy-purple/5 p-4 rounded-xl border border-glossy-purple/20">
                                            <div class="text-[8px] text-glossy-purple font-black uppercase mb-1 tracking-widest">Legacy Note Payload</div>
                                            <p class="text-[10px] text-white/80 leading-relaxed"><?php echo e($demoAccess[$key]['note']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <a href="https://wa.me/<?php echo e($waNumber); ?>?text=<?php echo urlencode($project['wa_custom_message']); ?>" target="_blank" class="block w-full py-6 bg-sharp-orange text-black font-black rounded-[1.5rem] uppercase tracking-[0.3em] text-[12px] text-center shadow-[0_0_40px_rgba(255,102,0,0.4)] hover:scale-[1.02] active:scale-95 transition-all">Establish_Connection</a>
                </div>
            </div>
        </div>
    </main>

    <div id="vaultModal" class="fixed inset-0 z-[100] bg-black/95 backdrop-blur-3xl flex items-center justify-center p-6 opacity-0 pointer-events-none transition-all duration-700">
        <div class="max-w-5xl w-full bg-black/60 border border-glossy-purple/30 rounded-[3rem] overflow-hidden glass-purple shadow-2xl">
            <div class="p-8 border-b border-white/10 flex items-center justify-between bg-white/5">
                <div class="flex items-center gap-4">
                    <i data-lucide="terminal" class="w-5 h-5 text-glossy-purple"></i>
                    <span class="text-sm font-black uppercase tracking-[0.3em]">The_Vault</span>
                </div>
                <button onclick="closeVault()" class="p-3 text-text-dim hover:text-white transition-all"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div class="p-10 max-h-[70vh] overflow-y-auto custom-scroll">
                <div class="code-terminal">
                    <pre class="text-white/90 leading-relaxed"><code><?php echo e($project['code_snippet']); ?></code></pre>
                </div>
            </div>
        </div>
    </div>

    <div id="imageModal" class="fixed inset-0 z-[110] bg-black/95 backdrop-blur-3xl flex items-center justify-center p-6 opacity-0 pointer-events-none transition-all duration-500" onclick="closeImage()">
        <img id="modalImg" class="max-w-full max-h-full rounded-2xl shadow-2xl scale-95 transition-transform duration-500">
    </div>

    <script>
        lucide.createIcons();

        function viewImage(src) {
            const modal = document.getElementById('imageModal');
            const img = document.getElementById('modalImg');
            img.src = src;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => img.classList.remove('scale-95'), 10);
        }

        function closeImage() {
            const modal = document.getElementById('imageModal');
            const img = document.getElementById('modalImg');
            img.classList.add('scale-95');
            modal.classList.add('opacity-0', 'pointer-events-none');
        }

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

        if (typeof barba !== 'undefined') {
            barba.init({
                transitions: [{
                    name: 'fade',
                    leave(data) { return gsap.to(data.current.container, { opacity: 0, y: 20 }); },
                    enter(data) {
                        lucide.createIcons();
                        window.scrollTo(0, 0);
                        return gsap.from(data.next.container, { opacity: 0, y: 20 });
                    }
                }]
            });
        }
    </script>
</body>
</html>
