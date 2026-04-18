<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$profile = get_admin_profile($pdo);
$appTitle = $profile['full_name'] . " | Portfolio 1.0";
$heroSubtext = $profile['bio'];

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
    <title><?php echo e($appTitle); ?></title>
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

    <nav id="main-nav" class="flex items-center justify-between px-4 md:px-10 py-6 border-b border-white/10 sticky top-0 bg-pitch-black/80 backdrop-blur-md z-50 nav-visible">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-sharp-orange rounded-lg flex items-center justify-center font-black italic text-black">P</div>
            <span class="text-xl font-black italic tracking-tighter uppercase"><?php echo e(strtoupper(explode(' ', $profile['full_name'] ?? 'P')[0])); ?>_CORE</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="/" class="text-[11px] font-black uppercase tracking-widest text-text-dim hover:text-white transition-colors">Nodes</a>
            <?php if (is_logged_in()): ?>
                <a href="/admin" class="text-[11px] font-black uppercase tracking-widest text-text-dim hover:text-sharp-orange transition-colors">Admin</a>
            <?php endif; ?>
            <div class="h-4 w-px bg-white/10"></div>
            <div class="flex items-center gap-2 text-[10px] font-mono text-text-dim uppercase tracking-[2px]">
                <span class="w-2 h-2 rounded-full bg-sharp-orange animate-pulse"></span>
                LIVE_HUB
            </div>
        </div>
    </nav>

    <main data-barba="container" data-barba-namespace="home" class="px-4 md:px-10 py-5 min-h-screen">
        <div class="space-y-12">
            <!-- Hero -->
            <section class="py-20 relative overflow-hidden">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-sharp-orange/5 blur-[120px] rounded-full -z-10"></div>
                <div class="flex flex-col lg:flex-row items-center gap-12">
                    <div class="flex-1 text-center lg:text-left space-y-8">
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4">
                            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[11px] font-bold tracking-[0.4em] uppercase text-text-dim">
                                <span class="w-1.5 h-1.5 rounded-full bg-sharp-orange animate-pulse"></span> Intelligence Synced
                            </div>
                            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[11px] font-bold tracking-[0.4em] uppercase text-glossy-purple">
                                <i data-lucide="sparkles" class="w-3 h-3"></i> Nexus Active
                            </div>
                        </div>
                        <h1 class="text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-black tracking-tighter italic leading-[0.9] text-glow-orange uppercase">
                            <?php echo e(e($profile['full_name'])); ?><br>
                            <span class="text-sharp-orange">ARCHITECTURE.</span>
                        </h1>
                        <p class="text-text-dim max-w-2xl mx-auto lg:mx-0 text-lg leading-relaxed font-medium">
                            <?php echo e(e($profile['bio'])); ?>
                        </p>
                    </div>
                    <div class="w-full lg:w-[450px] grid grid-cols-2 gap-4">
                        <?php foreach ($heroProjects as $idx => $proj): ?>
                            <a href="/project/<?php echo e($proj['slug']); ?>" class="group block aspect-square bg-white/5 border border-white/10 rounded-xl overflow-hidden relative">
                                <img src="<?php echo e($proj['screenshot_path']); ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-x-0 bottom-0 p-3 bg-black/60 backdrop-blur-sm">
                                    <div class="text-[9px] font-black uppercase text-white truncate"><?php echo e($proj['title']); ?></div>
                                    <div class="text-[7px] font-mono text-sharp-orange uppercase">Node: <?php echo e($idx + 1); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
                <?php foreach ($projects as $project):
                    $tech = json_decode($project['tech_stack'], true) ?? [];
                ?>
                    <a href="/project/<?php echo e($project['slug']); ?>" class="group block bg-white/5 border border-white/10 rounded-xl overflow-hidden hover:border-sharp-orange transition-all duration-500 hover:-translate-y-2 relative">
                        <div class="aspect-[16/10] relative overflow-hidden bg-black/40">
                            <img src="<?php echo e($project['screenshot_path']); ?>" alt="<?php echo e($project['title']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" loading="lazy">
                            <div class="absolute inset-x-0 bottom-0 h-1/3 bg-black/60 backdrop-blur-sm px-8 pt-4">
                                <div class="text-[10px] font-black text-white uppercase tracking-widest"><?php echo e($project['title']); ?></div>
                            </div>
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span class="px-2 py-1 rounded text-[9px] font-black uppercase tracking-widest border border-white/10 bg-black/60 text-white">
                                    <?php echo e($project['category']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-8 space-y-4">
                            <h3 class="text-2xl font-black tracking-tight group-hover:text-sharp-orange transition-colors uppercase italic"><?php echo e($project['title']); ?></h3>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach (array_slice($tech, 0, 3) as $t): ?>
                                    <span class="text-[9px] font-mono text-text-dim uppercase">/<?php echo e($t['name'] ?? $t); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="pt-4 flex items-center justify-between border-t border-white/5">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="activity" class="w-3 h-3 text-sharp-orange animate-pulse"></i>
                                    <span class="text-[10px] font-bold text-text-dim uppercase tracking-widest italic">Sync: Active</span>
                                </div>
                                <div class="text-[11px] font-mono text-sharp-orange font-bold uppercase"><?php echo e($project['inquiries_count']); ?> INQ</div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer class="px-4 md:px-10 py-10 border-t border-white/10 mt-20">
        <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="text-center md:text-left">
                <div class="text-xl font-black italic tracking-tighter uppercase mb-2"><?php echo e(strtoupper(explode(' ', $profile['full_name'] ?? 'P')[0])); ?>_CORE</div>
                <p class="text-[10px] font-mono text-text-dim uppercase tracking-[3px]">&copy; <?php echo date('Y'); ?> // AI-DRIVEN_INTERFACE</p>
            </div>
            <div class="flex flex-col items-center md:items-end gap-4">
                <div class="text-[10px] font-black uppercase tracking-widest text-text-dim mb-1">Notify me of new builds</div>
                <form class="flex gap-2">
                    <input type="email" placeholder="EMAIL_ADDRESS" class="bg-white/5 border border-white/10 rounded px-4 py-2 text-xs outline-none focus:border-sharp-orange transition-all font-mono w-64">
                    <button class="bg-sharp-orange text-black px-4 py-2 rounded text-xs font-black uppercase tracking-widest hover:brightness-110 transition-all">Join</button>
                </form>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            const nav = document.getElementById('main-nav');
            if (currentScroll <= 0) { nav.classList.remove('nav-hidden'); return; }
            if (currentScroll > lastScroll && !nav.classList.contains('nav-hidden')) {
                nav.classList.add('nav-hidden');
            } else if (currentScroll < lastScroll && nav.classList.contains('nav-hidden')) {
                nav.classList.remove('nav-hidden');
            }
            lastScroll = currentScroll;
        });

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
