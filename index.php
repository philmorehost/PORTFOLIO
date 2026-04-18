<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$profile = get_admin_profile($pdo);
$full_name = $profile['full_name'] ?? 'Cyber Architect';
$bio = $profile['bio'] ?? 'Expert Full-Stack Developer & Security Specialist';
$appTitle = e($full_name) . " | Portfolio & System Architecture";
$metaDesc = e(substr($bio, 0, 160));

$stmt = $pdo->query("SELECT * FROM projects ORDER BY is_pinned DESC, created_at DESC");
$projects = $stmt->fetchAll();

$heroProjects = array_slice(array_filter($projects, function($p) { return $p['is_pinned']; }), 0, 4);
if (empty($heroProjects)) $heroProjects = array_slice($projects, 0, 4);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $appTitle; ?></title>
    <meta name="description" content="<?php echo $metaDesc; ?>">

    <!-- SEO & Social -->
    <link rel="canonical" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>">
    <meta property="og:title" content="<?php echo $appTitle; ?>">
    <meta property="og:description" content="<?php echo $metaDesc; ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Critical Styles -->
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
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/@barba/core"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body class="bg-pitch-black text-white selection:bg-sharp-orange selection:text-white" data-barba="wrapper">

    <nav id="main-nav" class="flex items-center justify-between px-6 md:px-12 py-6 border-b border-white/10 sticky top-0 bg-pitch-black/90 backdrop-blur-xl z-50 nav-visible">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-sharp-orange rounded-xl flex items-center justify-center font-black italic text-black text-xl shadow-[0_0_15px_rgba(255,102,0,0.4)]">P</div>
            <span class="text-2xl font-black italic tracking-tighter uppercase hidden sm:block"><?php echo e(strtoupper(explode(' ', $full_name)[0])); ?>_CORE</span>
        </div>
        <div class="flex items-center gap-8">
            <a href="/" class="text-[12px] font-black uppercase tracking-widest text-text-dim hover:text-white transition-colors">Grid_Nodes</a>
            <?php if (is_logged_in()): ?>
                <a href="admin/" class="text-[12px] font-black uppercase tracking-widest text-sharp-orange hover:brightness-125 transition-all flex items-center gap-2">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Admin
                </a>
            <?php endif; ?>
            <div class="h-5 w-px bg-white/10 hidden md:block"></div>
            <div class="hidden md:flex items-center gap-3 text-[10px] font-mono text-text-dim uppercase tracking-[3px]">
                <span class="w-2 h-2 rounded-full bg-sharp-orange animate-pulse shadow-[0_0_10px_#FF6600]"></span>
                LIVE_INTERFACE
            </div>
        </div>
    </nav>

    <main data-barba="container" data-barba-namespace="home" class="px-6 md:px-12 py-10 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-24">

            <!-- Hero Section -->
            <section class="py-12 md:py-24 relative">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-sharp-orange/10 blur-[150px] rounded-full -z-10"></div>
                <div class="flex flex-col lg:flex-row items-center gap-16">
                    <div class="flex-1 text-center lg:text-left space-y-10">
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4">
                            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/5 border border-white/10 text-[11px] font-bold tracking-[0.4em] uppercase text-text-dim">
                                <span class="w-2 h-2 rounded-full bg-sharp-orange animate-ping"></span> Intelligence_Synced
                            </div>
                            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-glossy-purple/10 border border-glossy-purple/30 text-[11px] font-bold tracking-[0.4em] uppercase text-glossy-purple shadow-[0_0_20px_rgba(191,0,255,0.1)]">
                                <i data-lucide="sparkles" class="w-4 h-4"></i> Nexus_v1.0_Active
                            </div>
                        </div>
                        <h1 class="text-5xl sm:text-7xl md:text-8xl lg:text-9xl font-black tracking-tighter italic leading-[0.85] text-glow-orange uppercase">
                            <?php echo e(e($full_name)); ?><br>
                            <span class="text-sharp-orange">ARCHITECTURE.</span>
                        </h1>
                        <p class="text-text-dim max-w-2xl mx-auto lg:mx-0 text-xl md:text-2xl leading-relaxed font-medium">
                            <?php echo e(e($bio)); ?>
                        </p>
                    </div>

                    <!-- Pinned Grid -->
                    <div class="w-full lg:w-[500px] grid grid-cols-2 gap-6">
                        <?php foreach ($heroProjects as $idx => $proj): ?>
                            <a href="project/<?php echo e($proj['slug']); ?>" class="group block aspect-square bg-white/5 border border-white/10 rounded-2xl overflow-hidden relative shadow-2xl hover:border-sharp-orange/50 transition-all duration-500">
                                <img src="<?php echo e($proj['screenshot_path']); ?>" alt="<?php echo e($proj['title']); ?>" width="400" height="400" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 opacity-80 group-hover:opacity-100" loading="eager">
                                <div class="absolute inset-x-0 bottom-0 p-5 bg-gradient-to-t from-black via-black/80 to-transparent backdrop-blur-[2px]">
                                    <div class="text-[10px] font-black uppercase text-white tracking-widest truncate"><?php echo e($proj['title']); ?></div>
                                    <div class="text-[8px] font-mono text-sharp-orange uppercase mt-1 tracking-tighter">Access_Node: <?php echo str_pad($idx + 1, 2, '0', STR_PAD_LEFT); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Project Grid Section -->
            <section class="space-y-12">
                <div class="flex items-end justify-between border-b border-white/10 pb-8">
                    <h2 class="text-3xl md:text-5xl font-black italic uppercase tracking-tighter"><span class="text-sharp-orange">System</span>_Nodes</h2>
                    <div class="text-[11px] font-mono text-text-dim uppercase tracking-[3px]">Total_Active: <?php echo count($projects); ?></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-12">
                    <?php foreach ($projects as $project):
                        $tech = json_decode($project['tech_stack'], true) ?? [];
                        $seo = json_decode($project['seo_tags'], true) ?? [];
                    ?>
                        <a href="project/<?php echo e($project['slug']); ?>" class="group block bg-white/5 border border-white/10 rounded-3xl overflow-hidden hover:border-sharp-orange transition-all duration-700 hover:-translate-y-3 relative shadow-xl">
                            <div class="aspect-[16/10] relative overflow-hidden bg-black/40">
                                <img src="<?php echo e($project['screenshot_path']); ?>" alt="<?php echo e($project['title']); ?>" width="600" height="375" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 opacity-70 group-hover:opacity-100" loading="lazy">
                                <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black via-black/60 to-transparent p-8 flex flex-col justify-end">
                                    <div class="text-[11px] font-black text-white uppercase tracking-[4px]"><?php echo e($project['title']); ?></div>
                                </div>
                                <div class="absolute top-6 left-6">
                                    <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border border-white/10 bg-black/80 text-white backdrop-blur-md">
                                        <?php echo e($project['category']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="p-8 space-y-6">
                                <p class="text-sm text-text-dim line-clamp-2 leading-relaxed font-medium">
                                    <?php echo e($seo['metaDescription'] ?? 'Advanced full-stack solution with integrated AI capabilities.'); ?>
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_slice($tech, 0, 3) as $t): ?>
                                        <span class="text-[9px] font-mono text-text-dim uppercase border border-white/5 px-2 py-1 rounded bg-white/5 tracking-tighter">/<?php echo e($t['name'] ?? $t); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="pt-6 flex items-center justify-between border-t border-white/5">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="activity" class="w-3 h-3 text-sharp-orange animate-pulse"></i>
                                        <span class="text-[10px] font-bold text-text-dim uppercase tracking-widest italic">Sync_Status: Online</span>
                                    </div>
                                    <div class="text-[12px] font-black text-sharp-orange uppercase tracking-widest flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sharp-orange"></span>
                                        <?php echo e($project['inquiries_count']); ?>_INQ
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <footer class="px-6 md:px-12 py-20 border-t border-white/10 mt-24 bg-black/50">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
            <div class="text-center md:text-left space-y-4">
                <div class="text-2xl font-black italic tracking-tighter uppercase"><?php echo e(strtoupper(explode(' ', $full_name)[0])); ?>_CORE</div>
                <p class="text-[11px] font-mono text-text-dim uppercase tracking-[5px]">&copy; <?php echo date('Y'); ?> // AI-DRIVEN_INTERFACE</p>
                <div class="flex justify-center md:justify-start gap-4 pt-4">
                     <i data-lucide="github" class="w-5 h-5 text-text-dim hover:text-white cursor-pointer transition-colors"></i>
                     <i data-lucide="linkedin" class="w-5 h-5 text-text-dim hover:text-white cursor-pointer transition-colors"></i>
                     <i data-lucide="twitter" class="w-5 h-5 text-text-dim hover:text-white cursor-pointer transition-colors"></i>
                </div>
            </div>

            <div class="bg-white/5 border border-white/10 p-8 rounded-3xl space-y-6">
                <div class="text-[12px] font-black uppercase tracking-[3px] text-sharp-orange">Notify me of new builds</div>
                <form class="flex flex-col sm:flex-row gap-3">
                    <input type="email" placeholder="CORE_RECEPTOR_EMAIL" aria-label="Email for newsletter" class="flex-1 bg-black/40 border border-white/10 rounded-xl px-5 py-4 text-sm outline-none focus:border-sharp-orange transition-all font-mono">
                    <button class="bg-sharp-orange text-black px-8 py-4 rounded-xl text-sm font-black uppercase tracking-widest hover:brightness-110 active:scale-95 transition-all shadow-[0_0_20px_rgba(255,102,0,0.3)]">Join_Grid</button>
                </form>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        // Vanish Header logic
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            const nav = document.getElementById('main-nav');
            if (currentScroll <= 10) {
                nav.classList.remove('nav-hidden');
                nav.classList.remove('bg-black/90');
                return;
            }
            nav.classList.add('bg-black/90');
            if (currentScroll > lastScroll && !nav.classList.contains('nav-hidden')) {
                nav.classList.add('nav-hidden');
            } else if (currentScroll < lastScroll && nav.classList.contains('nav-hidden')) {
                nav.classList.remove('nav-hidden');
            }
            lastScroll = currentScroll;
        }, { passive: true });

        barba.init({
            transitions: [{
                name: 'opacity-transition',
                leave(data) { return gsap.to(data.current.container, { opacity: 0, y: 20, duration: 0.4 }); },
                enter(data) {
                    lucide.createIcons();
                    window.scrollTo(0, 0);
                    return gsap.from(data.next.container, { opacity: 0, y: 20, duration: 0.4, clearProps: "all" });
                }
            }]
        });
    </script>
</body>
</html>
