<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$profile = get_admin_profile($pdo);
$full_name = $profile['full_name'] ?? 'Cyber Architect';
$bio = $profile['bio'] ?? 'Expert Full-Stack Developer & Security Specialist';
$appTitle = e($full_name) . " | Portfolio_Nexus";
$baseUrl = get_base_url();

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

    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/theme.css">
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/@barba/core"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body class="bg-pitch-black text-white selection:bg-sharp-orange" data-barba="wrapper">

    <nav id="main-nav" class="flex items-center justify-between px-6 md:px-12 py-6 border-b border-white/10 sticky top-0 bg-pitch-black/90 backdrop-blur-xl z-[100]">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-sharp-orange rounded-xl flex items-center justify-center font-black italic text-black text-xl shadow-[0_0_20px_rgba(255,102,0,0.4)]">P</div>
            <span class="text-2xl font-black italic tracking-tighter uppercase hidden sm:block"><?php echo e(strtoupper(explode(' ', $full_name)[0])); ?>_CORE</span>
        </div>
        <div class="flex items-center gap-8">
            <a href="/" class="text-[12px] font-black uppercase tracking-widest text-text-dim hover:text-white transition-colors">Grid_Nodes</a>
            <?php if (is_logged_in()): ?>
                <a href="<?php echo $baseUrl; ?>/admin/" class="text-[12px] font-black uppercase tracking-widest text-sharp-orange hover:brightness-125 transition-all flex items-center gap-2 font-bold">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Admin
                </a>
            <?php endif; ?>
            <div class="h-5 w-px bg-white/10 hidden md:block"></div>
            <div class="hidden md:flex items-center gap-3 text-[10px] font-mono text-text-dim uppercase tracking-[3px]">
                <span class="w-2 h-2 rounded-full bg-sharp-orange animate-pulse"></span>
                HUB_ONLINE
            </div>
        </div>
    </nav>

    <main data-barba="container" data-barba-namespace="home" class="px-6 md:px-12 py-10 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-24">

            <section class="py-12 md:py-24 relative overflow-visible">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-sharp-orange/10 blur-[150px] rounded-full -z-10"></div>
                <div class="flex flex-col lg:flex-row items-center gap-16">
                    <div class="flex-1 text-center lg:text-left space-y-10">
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4">
                            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/5 border border-white/10 text-[11px] font-bold tracking-[0.4em] uppercase text-text-dim">
                                <span class="w-2 h-2 rounded-full bg-sharp-orange animate-ping"></span> Intelligence_Synced
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

                    <div class="w-full lg:w-[500px] grid grid-cols-2 gap-6">
                        <?php foreach ($heroProjects as $idx => $proj): ?>
                            <a href="<?php echo $baseUrl; ?>/project/<?php echo e($proj['slug']); ?>" class="group block aspect-square bg-white/5 border border-white/10 rounded-2xl overflow-hidden relative shadow-2xl hover:border-sharp-orange/50 transition-all duration-500">
                                <img src="<?php echo $baseUrl . e($proj['screenshot_path']); ?>" alt="Node" class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-all duration-1000 group-hover:scale-110">
                                <div class="absolute inset-x-0 bottom-0 p-5 bg-gradient-to-t from-black to-transparent">
                                    <div class="text-[10px] font-black uppercase text-white tracking-widest truncate"><?php echo e($proj['title']); ?></div>
                                    <div class="text-[8px] font-mono text-sharp-orange uppercase mt-1">Node_0<?php echo $idx + 1; ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="space-y-12">
                <div class="flex items-end justify-between border-b border-white/10 pb-8">
                    <h2 class="text-3xl md:text-5xl font-black italic uppercase tracking-tighter leading-none"><span class="text-sharp-orange">System</span>_Nodes</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
                    <?php foreach ($projects as $project):
                        $seo = json_decode($project['seo_tags'], true) ?? [];
                        $tech = json_decode($project['tech_stack'], true) ?? [];
                    ?>
                        <a href="<?php echo $baseUrl; ?>/project/<?php echo e($project['slug']); ?>" class="group block bg-white/5 border border-white/10 rounded-3xl overflow-hidden hover:border-sharp-orange transition-all duration-700 hover:-translate-y-3 relative shadow-xl">
                            <div class="aspect-[16/10] relative overflow-hidden bg-black/40">
                                <img src="<?php echo $baseUrl . e($project['screenshot_path']); ?>" alt="Project" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 opacity-70 group-hover:opacity-100" loading="lazy">
                                <div class="absolute top-6 left-6">
                                    <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border border-white/10 bg-black/80 text-white backdrop-blur-md">
                                        <?php echo e($project['category']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="p-8 space-y-6">
                                <h3 class="text-2xl font-black italic uppercase tracking-tight group-hover:text-sharp-orange transition-colors"><?php echo e($project['title']); ?></h3>
                                <p class="text-sm text-text-dim line-clamp-2 leading-relaxed"><?php echo e($seo['metaDescription'] ?? ''); ?></p>
                                <div class="pt-6 flex items-center justify-between border-t border-white/5">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="activity" class="w-3 h-3 text-sharp-orange animate-pulse"></i>
                                        <span class="text-[10px] font-bold text-text-dim uppercase tracking-widest">Online</span>
                                    </div>
                                    <div class="text-[12px] font-black text-sharp-orange uppercase"><?php echo e($project['inquiries_count']); ?>_INQ</div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <footer class="px-6 md:px-12 py-20 border-t border-white/10 mt-24">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-12">
            <div class="text-center md:text-left">
                <div class="text-2xl font-black italic uppercase mb-2"><?php echo e(strtoupper(explode(' ', $full_name)[0])); ?>_CORE</div>
                <p class="text-[10px] font-mono text-text-dim uppercase tracking-[5px]">&copy; <?php echo date('Y'); ?> // AI-DRIVEN_INTERFACE</p>
            </div>
            <form class="flex flex-col sm:flex-row gap-3 w-full max-w-md">
                <input type="email" placeholder="CORE_RECEPTOR_EMAIL" class="flex-1 bg-black/40 border border-white/10 rounded-xl px-5 py-4 text-sm outline-none focus:border-sharp-orange transition-all font-mono">
                <button class="bg-sharp-orange text-black px-8 py-4 rounded-xl text-sm font-black uppercase tracking-widest hover:brightness-110 shadow-lg transition-all active:scale-95">Join</button>
            </form>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            const nav = document.getElementById('main-nav');
            if (currentScroll > lastScroll && currentScroll > 100) {
                nav.classList.add('nav-hidden');
            } else {
                nav.classList.remove('nav-hidden');
            }
            lastScroll = currentScroll;
        });

        if (typeof barba !== 'undefined') {
            barba.init({
                transitions: [{
                    name: 'fade',
                    leave(data) { return gsap.to(data.current.container, { opacity: 0, y: 20, duration: 0.4 }); },
                    enter(data) {
                        lucide.createIcons();
                        window.scrollTo(0, 0);
                        return gsap.from(data.next.container, { opacity: 0, y: 20, duration: 0.4 });
                    }
                }]
            });
        }
    </script>
</body>
</html>
