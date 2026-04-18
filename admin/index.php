<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();

$csrf_token = generate_csrf_token();
$api = get_api_settings($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['save_project'])) {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'];
        $slug = $_POST['slug'] ?: strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $category = $_POST['category'];
        $description = $_POST['description'];
        $screenshot_path = $_POST['screenshot_path'];
        $demo_link = $_POST['demo_link'];
        $tech_stack = $_POST['tech_stack'];
        $seo_tags = $_POST['seo_tags'];
        $wa_custom_message = $_POST['wa_custom_message'];
        $perf = $_POST['performance_scores'];
        $code = $_POST['code_snippet'];
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;

        if (strpos($screenshot_path, 'data:image') === 0) {
            $screenshot_path = save_local_image($screenshot_path, $slug);
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE projects SET title=?, slug=?, category=?, description=?, screenshot_path=?, demo_link=?, tech_stack=?, seo_tags=?, wa_custom_message=?, performance_scores=?, code_snippet=?, is_pinned=? WHERE id=?");
            $stmt->execute([$title, $slug, $category, $description, $screenshot_path, $demo_link, $tech_stack, $seo_tags, $wa_custom_message, $perf, $code, $is_pinned, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (title, slug, category, description, screenshot_path, demo_link, tech_stack, seo_tags, wa_custom_message, performance_scores, code_snippet, is_pinned) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $category, $description, $screenshot_path, $demo_link, $tech_stack, $seo_tags, $wa_custom_message, $perf, $code, $is_pinned]);
        }
    }

    if (isset($_POST['delete_project'])) {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }

    if (isset($_POST['capture_psi'])) {
        $psi_img = capture_screenshot_psi($pdo, $_POST['url']);
        echo json_encode(['screenshot' => $psi_img]);
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
$projects = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nexus Dashboard | Admin</title>
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
    <style>
        .mode-ai .engine-border { border-color: var(--glossy-purple); box-shadow: 0 0 15px rgba(191,0,255,0.2); }
        .mode-ai .engine-text { color: var(--glossy-purple); }
        .mode-ai .engine-btn { background-color: var(--glossy-purple); color: white; }

        .mode-manual .engine-border { border-color: var(--sharp-orange); box-shadow: 0 0 15px rgba(255,102,0,0.2); }
        .mode-manual .engine-text { color: var(--sharp-orange); }
        .mode-manual .engine-btn { background-color: var(--sharp-orange); color: black; }
    </style>
</head>
<body class="bg-pitch-black text-white p-4 md:p-10 font-sans">
    <div class="max-w-7xl mx-auto space-y-12 pb-20">
        <!-- Header -->
        <header class="flex flex-col lg:flex-row items-center justify-between gap-8 bg-white/5 border border-white/10 p-8 rounded-3xl backdrop-blur-xl">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-sharp-orange rounded-2xl flex items-center justify-center font-black italic text-black text-2xl shadow-[0_0_20px_rgba(255,102,0,0.5)]">P</div>
                <div>
                    <h1 class="text-2xl md:text-4xl font-black italic uppercase tracking-tighter">Nexus_<span class="text-sharp-orange">GRID</span></h1>
                    <p class="text-[10px] font-mono text-text-dim uppercase tracking-[3px]">System_Management_Protocol</p>
                </div>
            </div>
            <nav class="flex items-center gap-4">
                <a href="/admin/nexus" class="p-3 px-6 rounded-xl bg-white/5 border border-white/10 hover:border-glossy-purple hover:bg-glossy-purple/10 transition-all text-[11px] font-black uppercase flex items-center gap-2 tracking-widest"><i data-lucide="cpu" class="w-4 h-4"></i> Nexus_API</a>
                <a href="/admin/profile" class="p-3 px-6 rounded-xl bg-white/5 border border-white/10 hover:border-sharp-orange hover:bg-sharp-orange/10 transition-all text-[11px] font-black uppercase flex items-center gap-2 tracking-widest"><i data-lucide="user" class="w-4 h-4"></i> Identity</a>
                <div class="h-8 w-px bg-white/10 mx-2"></div>
                <a href="/admin/login.php?logout=1" class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white transition-all"><i data-lucide="log-out" class="w-5 h-5"></i></a>
            </nav>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_450px] gap-10">
            <!-- Project Engine -->
            <div id="engineContainer" class="bg-white/5 border border-white/10 p-10 rounded-3xl space-y-10 transition-all duration-500 mode-ai shadow-2xl">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 border-b border-white/5 pb-8">
                    <div class="space-y-1">
                        <h2 class="text-xl font-black uppercase engine-text tracking-[0.3em] flex items-center gap-3 transition-colors"><i data-lucide="zap" class="w-6 h-6"></i> Project_Engine</h2>
                        <p class="text-[9px] font-mono text-text-dim uppercase">Initialize_New_Node_Sequence</p>
                    </div>
                    <div class="flex bg-black/60 p-1.5 rounded-2xl border border-white/10 shadow-inner">
                        <button onclick="setMode('ai')" id="modeAi" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase transition-all bg-glossy-purple text-white shadow-lg">AI_AUTO</button>
                        <button onclick="setMode('manual')" id="modeManual" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase transition-all text-text-dim">MANUAL_CAPTURE</button>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-text-dim uppercase tracking-[2px]">Target_Access_URL</label>
                    <div class="flex gap-4">
                        <input type="text" id="targetUrl" placeholder="https://external-build.nexus" class="flex-1 bg-black/60 border border-white/10 rounded-2xl p-5 outline-none engine-border transition-all font-mono text-sm placeholder:text-white/10">
                        <button onclick="triggerCapture()" id="captureBtn" class="px-10 py-5 engine-btn font-black rounded-2xl uppercase tracking-[0.2em] text-[11px] flex items-center gap-3 shadow-2xl hover:scale-[1.02] active:scale-95 transition-all">
                            <i data-lucide="sparkles" id="captureIcon" class="w-5 h-5"></i> <span id="captureText">SYNC_NODE</span>
                        </button>
                    </div>
                </div>

                <form method="POST" class="space-y-8 pt-6" id="projectForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="projectId">
                    <input type="hidden" name="screenshot_path" id="projectThumb">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-text-dim uppercase tracking-widest ml-1">Title</label>
                            <input type="text" name="title" id="projectTitle" placeholder="NODE_IDENTIFIER" required class="w-full bg-white/5 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange transition-all font-bold text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-text-dim uppercase tracking-widest ml-1">Demo_Link</label>
                            <input type="text" name="demo_link" id="projectUrl" placeholder="HTTPS://..." required class="w-full bg-white/5 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange transition-all font-mono text-sm">
                        </div>
                    </div>

                    <div id="previewArea" class="hidden aspect-video w-full rounded-3xl overflow-hidden bg-black/60 border border-white/10 relative shadow-2xl group">
                        <img id="thumbPreview" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                             <span class="px-4 py-2 bg-black/80 rounded-lg border border-white/20 text-[10px] font-black uppercase tracking-widest">Visual_Hash_Confirmed</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-text-dim uppercase tracking-widest ml-1">Power_Pitch (Markdown)</label>
                        <textarea name="description" id="projectDesc" rows="8" placeholder="Enter system overview..." class="w-full bg-white/5 border border-white/10 rounded-2xl p-5 outline-none focus:border-sharp-orange transition-all text-sm leading-relaxed font-medium"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-text-dim uppercase tracking-widest ml-1">WhatsApp_Payload</label>
                            <input type="text" name="wa_custom_message" id="projectWa" class="w-full bg-white/5 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange text-[10px] font-mono">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-text-dim uppercase tracking-widest ml-1">Node_Slug</label>
                            <input type="text" name="slug" id="projectSlug" placeholder="AUTO_GENERATE" class="w-full bg-white/5 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange text-[10px] font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[9px] font-black text-text-dim uppercase tracking-widest ml-1">Tech_Pulse_Metrics (JSON)</label>
                            <input type="text" name="performance_scores" id="projectPerf" value='{"security":98,"ui_ux":95,"scalability":90}' class="w-full bg-black/60 border border-white/10 rounded-xl p-4 outline-none focus:border-glossy-purple text-[10px] font-mono">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[9px] font-black text-text-dim uppercase tracking-widest ml-1">Detected_Stack (JSON)</label>
                            <input type="text" name="tech_stack" id="projectTech" value='[]' class="w-full bg-black/60 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange text-[10px] font-mono">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[9px] font-black text-text-dim uppercase tracking-widest ml-1">Vault_Code_Snippet</label>
                        <textarea name="code_snippet" id="projectCode" rows="6" placeholder="// PHP, Kotlin, or JS architecture..." class="w-full bg-black/60 border border-white/10 rounded-2xl p-5 outline-none focus:border-sharp-orange font-mono text-xs leading-relaxed text-white/80"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-10 border-t border-white/5">
                        <div class="flex items-center gap-8">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_pinned" id="projectPinned" class="w-5 h-5 rounded-md accent-sharp-orange border-white/20 bg-black/40">
                                <span class="text-[11px] font-black uppercase text-text-dim group-hover:text-white transition-colors tracking-widest">Pin_Node</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <span class="text-[9px] font-black uppercase text-text-dim tracking-widest">Category:</span>
                                <select name="category" id="projectCat" class="bg-black/60 border border-white/10 rounded-lg p-2 text-[10px] font-black uppercase outline-none focus:border-sharp-orange">
                                    <option value="WEB">WEB_SYSTEM</option>
                                    <option value="APP">APP_BUILD</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-4 w-full sm:w-auto">
                            <button type="button" onclick="resetForm()" class="flex-1 sm:flex-none px-8 py-4 bg-white/5 border border-white/10 text-text-dim font-black rounded-2xl uppercase tracking-widest text-[11px] hover:bg-white/10 transition-all">Clear</button>
                            <button type="submit" name="save_project" class="flex-1 sm:flex-none px-12 py-4 bg-sharp-orange text-black font-black rounded-2xl uppercase tracking-widest text-[11px] shadow-[0_0_30px_rgba(255,102,0,0.3)] hover:brightness-110 active:scale-95 transition-all">Publish_Node</button>
                        </div>
                    </div>

                    <input type="hidden" name="seo_tags" id="projectSeo" value='{}'>
                </form>
            </div>

            <!-- Sidebar / List -->
            <div class="space-y-8">
                <div class="bg-white/5 border border-white/10 p-8 rounded-3xl space-y-8 shadow-2xl">
                    <div class="flex items-center justify-between border-b border-white/5 pb-6">
                        <h2 class="text-xl font-black italic uppercase tracking-tighter"><span class="text-sharp-orange">Live</span>_Nodes</h2>
                        <span class="text-[10px] font-mono text-text-dim px-3 py-1 bg-black/40 rounded-full border border-white/10">Active_Grid: <?php echo count($projects); ?></span>
                    </div>
                    <div class="space-y-4 max-h-[1200px] overflow-y-auto pr-2 custom-scroll">
                        <?php foreach ($projects as $proj): ?>
                            <div class="bg-black/40 border border-white/5 p-5 rounded-2xl flex items-center justify-between group hover:border-sharp-orange/30 transition-all duration-300">
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 rounded-xl border border-white/10 overflow-hidden shadow-2xl bg-black">
                                        <img src="<?php echo e($proj['screenshot_path']); ?>" alt="Node" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    </div>
                                    <div>
                                        <div class="text-[12px] font-black uppercase text-white tracking-tight leading-none mb-1"><?php echo e($proj['title']); ?></div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-[9px] text-text-dim font-mono italic"><?php echo e($proj['category']); ?></span>
                                            <?php if($proj['is_pinned']): ?>
                                                <i data-lucide="pin" class="w-2.5 h-2.5 text-sharp-orange fill-sharp-orange"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                    <button data-proj='<?php echo e(json_encode($proj), ENT_QUOTES, 'UTF-8'); ?>' onclick='editProject(JSON.parse(this.dataset.proj))' class="p-3 bg-white/5 rounded-xl hover:text-sharp-orange hover:bg-sharp-orange/10 transition-all"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                    <form method="POST" onsubmit="return confirm('Purge Node permanently?')" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="id" value="<?php echo $proj['id']; ?>">
                                        <button type="submit" name="delete_project" class="p-3 bg-white/5 rounded-xl hover:text-red-500 hover:bg-red-500/10 transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($projects)): ?>
                            <div class="text-center py-20 bg-black/20 rounded-2xl border border-dashed border-white/5">
                                <p class="text-[10px] font-mono text-text-dim uppercase tracking-[3px]">Grid_Empty: Standby_For_Deploy</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Metrics Card -->
                <div class="bg-glossy-purple/10 border border-glossy-purple/20 p-8 rounded-3xl space-y-6 shadow-[0_0_30px_rgba(191,0,255,0.1)]">
                     <h3 class="text-[10px] font-black uppercase tracking-[0.4em] text-glossy-purple flex items-center gap-2"><i data-lucide="activity" class="w-4 h-4"></i> Nexus_Health</h3>
                     <div class="space-y-4">
                         <div class="flex justify-between text-[11px] font-mono uppercase">
                             <span class="text-text-dim">Uptime</span>
                             <span class="text-green-500 font-black">99.99%</span>
                         </div>
                         <div class="flex justify-between text-[11px] font-mono uppercase">
                             <span class="text-text-dim">Intelligence</span>
                             <span class="text-glossy-purple font-black"><?php echo strtoupper($api['provider']); ?>_V3</span>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        let currentMode = 'ai';

        function setMode(mode) {
            currentMode = mode;
            const container = document.getElementById('engineContainer');
            const modeAi = document.getElementById('modeAi');
            const modeManual = document.getElementById('modeManual');
            const captureIcon = document.getElementById('captureIcon');
            const captureText = document.getElementById('captureText');

            if (mode === 'ai') {
                container.classList.remove('mode-manual');
                container.classList.add('mode-ai');
                modeAi.classList.add('bg-glossy-purple', 'text-white', 'shadow-lg');
                modeAi.classList.remove('text-text-dim');
                modeManual.classList.remove('bg-sharp-orange', 'text-black', 'shadow-lg');
                modeManual.classList.add('text-text-dim');
                captureText.innerText = 'SYNC_NODE';
                captureIcon.setAttribute('data-lucide', 'sparkles');
            } else {
                container.classList.remove('mode-ai');
                container.classList.add('mode-manual');
                modeManual.classList.add('bg-sharp-orange', 'text-black', 'shadow-lg');
                modeManual.classList.remove('text-text-dim');
                modeAi.classList.remove('bg-glossy-purple', 'text-white', 'shadow-lg');
                modeAi.classList.add('text-text-dim');
                captureText.innerText = 'CAPTURE_VISUAL';
                captureIcon.setAttribute('data-lucide', 'camera');
            }
            lucide.createIcons();
        }

        async function triggerCapture() {
            const url = document.getElementById('targetUrl').value;
            if (!url) return alert('Enter target URL for ingestion');
            const btn = document.getElementById('captureBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> PROCESSING_GRID...';
            lucide.createIcons();

            try {
                if (currentMode === 'ai') {
                    const formData = new FormData();
                    formData.append('url', url);
                    const response = await fetch('/admin/ai_generate', { method: 'POST', body: formData });
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);

                    document.getElementById('projectTitle').value = data.metaTitle.split('|')[0].trim();
                    document.getElementById('projectDesc').value = data.content;
                    document.getElementById('projectWa').value = data.waMessage;
                    document.getElementById('projectPerf').value = JSON.stringify(data.performance_scores);
                    document.getElementById('projectCode').value = data.code_snippet;
                    document.getElementById('projectTech').value = JSON.stringify(data.techStack);
                    document.getElementById('projectSeo').value = JSON.stringify({ metaTitle: data.metaTitle, metaDescription: data.metaDescription, keywords: data.keywords });
                    document.getElementById('projectUrl').value = url;
                }

                // PageSpeed Visual Capture
                const psiFormData = new FormData();
                psiFormData.append('url', url);
                psiFormData.append('capture_psi', '1');
                psiFormData.append('csrf_token', '<?php echo $csrf_token; ?>');
                const psiRes = await fetch('/admin/index', { method: 'POST', body: psiFormData });
                const psiData = await psiRes.json();

                if (psiData.screenshot) {
                    document.getElementById('projectThumb').value = psiData.screenshot;
                    document.getElementById('thumbPreview').src = psiData.screenshot;
                    document.getElementById('previewArea').classList.remove('hidden');
                    document.getElementById('projectUrl').value = url;
                    if (currentMode === 'manual') alert('Grid visual captured successfully.');
                } else if (currentMode === 'manual') {
                    throw new Error('Visual capture protocol failed.');
                }

                if (currentMode === 'ai') alert('Full Intelligence Sync Complete.');

            } catch (e) {
                alert('CRITICAL_ERROR: ' + e.message + '. Defaulting to Manual sequence.');
                if (currentMode === 'ai') setMode('manual');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
                lucide.createIcons();
            }
        }

        function editProject(proj) {
            document.getElementById('projectId').value = proj.id;
            document.getElementById('projectTitle').value = proj.title;
            document.getElementById('projectUrl').value = proj.demo_link;
            document.getElementById('projectCat').value = proj.category;
            document.getElementById('projectDesc').value = proj.description;
            document.getElementById('projectPerf').value = JSON.stringify(proj.performance_scores);
            document.getElementById('projectCode').value = proj.code_snippet;
            document.getElementById('projectTech').value = JSON.stringify(proj.tech_stack);
            document.getElementById('projectSeo').value = JSON.stringify(proj.seo_tags);
            document.getElementById('projectThumb').value = proj.screenshot_path;
            document.getElementById('projectWa').value = proj.wa_custom_message;
            document.getElementById('projectSlug').value = proj.slug;
            document.getElementById('projectPinned').checked = proj.is_pinned == 1;

            if (proj.screenshot_path) {
                document.getElementById('thumbPreview').src = proj.screenshot_path;
                document.getElementById('previewArea').classList.remove('hidden');
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('projectForm').reset();
            document.getElementById('projectId').value = '';
            document.getElementById('projectThumb').value = '';
            document.getElementById('previewArea').classList.add('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>
