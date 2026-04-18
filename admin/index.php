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
        $tech_stack = $_POST['tech_stack']; // JSON string
        $seo_tags = $_POST['seo_tags']; // JSON string
        $wa_custom_message = $_POST['wa_custom_message'];
        $perf = $_POST['performance_scores']; // JSON string
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
    <title>Dashboard | Portfolio 1.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/theme.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .mode-ai .engine-border { border-color: var(--glossy-purple); }
        .mode-ai .engine-text { color: var(--glossy-purple); }
        .mode-ai .engine-btn { background-color: var(--glossy-purple); }
        .mode-manual .engine-border { border-color: var(--sharp-orange); }
        .mode-manual .engine-text { color: var(--sharp-orange); }
        .mode-manual .engine-btn { background-color: var(--sharp-orange); }
    </style>
</head>
<body class="bg-pitch-black text-white p-4 md:p-10">
    <div class="max-w-6xl mx-auto space-y-12 pb-20">
        <header class="flex flex-col md:flex-row items-center justify-between gap-6">
            <h1 class="text-3xl md:text-5xl font-black italic uppercase text-glow-orange">Grid <span class="text-sharp-orange">Console</span></h1>
            <div class="flex items-center gap-3">
                <a href="/admin/nexus" class="p-2 px-4 rounded-lg bg-white/5 border border-white/10 hover:border-glossy-purple transition-all text-[10px] font-black uppercase flex items-center gap-2"><i data-lucide="cpu" class="w-4 h-4"></i> Nexus</a>
                <a href="/admin/profile" class="p-2 px-4 rounded-lg bg-white/5 border border-white/10 hover:border-sharp-orange transition-all text-[10px] font-black uppercase flex items-center gap-2"><i data-lucide="user" class="w-4 h-4"></i> Profile</a>
                <a href="/admin/login.php?logout=1" class="p-2 rounded-lg bg-white/5 border border-white/10 text-text-dim hover:text-red-500 transition-colors"><i data-lucide="log-out"></i></a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-8">
            <!-- Project Engine -->
            <div id="engineContainer" class="bg-white/5 border border-white/10 p-8 rounded-2xl space-y-6 transition-colors duration-500 mode-ai">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase engine-text tracking-[0.4em] flex items-center gap-2"><i data-lucide="zap" class="w-4 h-4"></i> Project Engine</h2>
                    <div class="flex bg-black/40 p-1 rounded-lg border border-white/5">
                        <button onclick="setMode('ai')" id="modeAi" class="px-4 py-1.5 rounded text-[9px] font-black uppercase transition-all bg-glossy-purple text-white">AI AUTO</button>
                        <button onclick="setMode('manual')" id="modeManual" class="px-4 py-1.5 rounded text-[9px] font-black uppercase transition-all text-text-dim">MANUAL</button>
                    </div>
                </div>

                <div class="flex gap-4">
                    <input type="text" id="targetUrl" placeholder="https://target-site.com" class="flex-1 bg-black/40 border border-white/10 rounded-lg p-4 outline-none engine-border transition-all font-mono text-sm">
                    <button onclick="triggerCapture()" id="captureBtn" class="px-8 py-4 engine-btn text-black font-black rounded-lg uppercase tracking-widest text-xs flex items-center gap-2 shadow-xl hover:brightness-110">
                        <i data-lucide="sparkles" id="captureIcon" class="w-4 h-4"></i> <span id="captureText">AI SYNC</span>
                    </button>
                </div>

                <form method="POST" class="space-y-6" id="projectForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="projectId">
                    <input type="hidden" name="screenshot_path" id="projectThumb">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="title" id="projectTitle" placeholder="PROJECT TITLE" required class="bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-sm">
                        <input type="text" name="demo_link" id="projectUrl" placeholder="DEMO URL" required class="bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-sm">
                    </div>

                    <div id="previewArea" class="hidden aspect-video w-full rounded-xl overflow-hidden bg-black/40 border border-white/5 relative mb-4">
                        <img id="thumbPreview" class="w-full h-full object-cover">
                    </div>

                    <textarea name="description" id="projectDesc" placeholder="Project Description (Markdown)..." rows="6" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-sm leading-relaxed"></textarea>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="wa_custom_message" id="projectWa" placeholder="WhatsApp Custom Message" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-[10px]">
                        <input type="text" name="slug" id="projectSlug" placeholder="SLUG (auto)" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-[10px]">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[9px] font-black text-text-dim uppercase mb-1">Performance (JSON)</label>
                            <input type="text" name="performance_scores" id="projectPerf" value='{"security":98,"ui_ux":95,"scalability":90}' class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-glossy-purple text-[10px] font-mono">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-text-dim uppercase mb-1">Tech Stack (JSON)</label>
                            <input type="text" name="tech_stack" id="projectTech" value='[]' class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-[10px] font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] font-black text-text-dim uppercase mb-1">Vault Code Snippet</label>
                        <textarea name="code_snippet" id="projectCode" rows="4" placeholder="PHP, JS, or Kotlin code..." class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange font-mono text-xs"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-white/5">
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_pinned" id="projectPinned" class="w-4 h-4 accent-sharp-orange">
                                <span class="text-[10px] font-black uppercase text-text-dim">Pin to Hero</span>
                            </label>
                            <select name="category" id="projectCat" class="bg-black/40 border border-white/10 rounded p-1 text-[10px] font-black uppercase outline-none">
                                <option value="WEB">WEB</option>
                                <option value="APP">APP</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="resetForm()" class="px-6 py-3 bg-white/5 border border-white/10 text-text-dim font-black rounded-lg uppercase tracking-widest text-[10px]">Reset</button>
                            <button type="submit" name="save_project" class="px-10 py-3 bg-sharp-orange text-black font-black rounded-lg uppercase tracking-widest text-[10px] shadow-lg">Publish Node</button>
                        </div>
                    </div>

                    <input type="hidden" name="seo_tags" id="projectSeo" value='{}'>
                </form>
            </div>

            <!-- List -->
            <div class="space-y-4">
                <h2 class="text-xl font-black italic uppercase tracking-tighter text-glow-orange">Active <span class="text-sharp-orange">Nodes</span></h2>
                <div class="space-y-3">
                    <?php foreach ($projects as $proj): ?>
                        <div class="bg-white/5 border border-white/10 p-4 rounded-xl flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <img src="<?php echo e($proj['screenshot_path']); ?>" class="w-12 h-12 rounded border border-white/10 object-cover">
                                <div>
                                    <div class="text-[11px] font-bold uppercase"><?php echo e($proj['title']); ?></div>
                                    <div class="text-[9px] text-text-dim font-mono italic"><?php echo e($proj['category']); ?></div>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button data-proj='<?php echo e(json_encode($proj), ENT_QUOTES, 'UTF-8'); ?>' onclick='editProject(JSON.parse(this.dataset.proj))' class="p-2 hover:text-sharp-orange transition-colors"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                <form method="POST" onsubmit="return confirm('Purge Node?')" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="id" value="<?php echo $proj['id']; ?>">
                                    <button type="submit" name="delete_project" class="p-2 hover:text-red-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                modeAi.classList.add('bg-glossy-purple', 'text-white');
                modeAi.classList.remove('text-text-dim');
                modeManual.classList.remove('bg-sharp-orange', 'text-black');
                modeManual.classList.add('text-text-dim');
                captureText.innerText = 'AI SYNC';
                captureIcon.setAttribute('data-lucide', 'sparkles');
            } else {
                container.classList.remove('mode-ai');
                container.classList.add('mode-manual');
                modeManual.classList.add('bg-sharp-orange', 'text-black');
                modeManual.classList.remove('text-text-dim');
                modeAi.classList.remove('bg-glossy-purple', 'text-white');
                modeAi.classList.add('text-text-dim');
                captureText.innerText = 'MANUAL CAPTURE';
                captureIcon.setAttribute('data-lucide', 'camera');
            }
            lucide.createIcons();
        }

        async function triggerCapture() {
            const url = document.getElementById('targetUrl').value;
            if (!url) return alert('Enter target URL');
            const btn = document.getElementById('captureBtn');
            btn.disabled = true;
            btn.innerText = 'PROCESSING...';

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

                // PSI Capture
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
                }
                alert('Engine Sync Complete.');
            } catch (e) {
                alert('Error: ' + e.message + '. Try Manual Mode.');
                setMode('manual');
            } finally {
                btn.disabled = false;
                btn.innerText = currentMode === 'ai' ? 'AI SYNC' : 'MANUAL CAPTURE';
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
            document.getElementById('previewArea').classList.add('hidden');
        }
    </script>
</body>
</html>
