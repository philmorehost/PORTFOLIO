<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();

$csrf_token = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['save_settings'])) {
        update_setting($pdo, 'appTitle', $_POST['appTitle']);
        update_setting($pdo, 'heroSubtext', $_POST['heroSubtext']);
        update_setting($pdo, 'waNumber', $_POST['waNumber']);
    }

    if (isset($_POST['save_project'])) {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'];
        $slug = $_POST['slug'] ?: strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $content = $_POST['content'];
        $tech_stack = $_POST['tech_stack'];
        $url = $_POST['url'];
        $thumbnail_url = $_POST['thumbnail_url'];
        $gallery_images = $_POST['gallery_images'];
        $demo_login = $_POST['demo_login'];
        $access_points = $_POST['access_points'];
        $type = $_POST['type'];
        $wa_message = $_POST['wa_message'];
        $seo_data = $_POST['seo_data'];
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;

        // Handle local image saving if it's a data URL
        if (strpos($thumbnail_url, 'data:image') === 0) {
            $thumbnail_url = save_local_image($thumbnail_url, $slug);
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE projects SET title=?, slug=?, content=?, tech_stack=?, url=?, thumbnail_url=?, gallery_images=?, demo_login=?, access_points=?, type=?, wa_message=?, seo_data=?, is_pinned=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $tech_stack, $url, $thumbnail_url, $gallery_images, $demo_login, $access_points, $type, $wa_message, $seo_data, $is_pinned, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (title, slug, content, tech_stack, url, thumbnail_url, gallery_images, demo_login, access_points, type, wa_message, seo_data, is_pinned) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $tech_stack, $url, $thumbnail_url, $gallery_images, $demo_login, $access_points, $type, $wa_message, $seo_data, $is_pinned]);
        }
    }

    if (isset($_POST['delete_project'])) {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }

    if (isset($_POST['capture_psi'])) {
        $psi_img = capture_screenshot_psi($_POST['url']);
        echo json_encode(['screenshot' => $psi_img]);
        exit;
    }
}

$appTitle = get_setting($pdo, 'appTitle', 'CYBER-PULSE');
$heroSubtext = get_setting($pdo, 'heroSubtext', '');
$waNumber = get_setting($pdo, 'waNumber', '2348123456789');

$stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
$projects = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Portfolio 1.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
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
    <div class="max-w-4xl mx-auto space-y-12 pb-20">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl md:text-5xl font-black italic uppercase text-glow-orange">Admin <span class="text-sharp-orange">Console</span></h1>
            <a href="/admin/login.php?logout=1" class="text-text-dim hover:text-sharp-orange transition-colors"><i data-lucide="log-out"></i></a>
        </div>

        <!-- Global Settings -->
        <div class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
            <h2 class="text-xs font-black text-sharp-orange uppercase tracking-[0.4em] flex items-center gap-2">
                <i data-lucide="settings" class="w-4 h-4"></i> Global Settings
            </h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">App Title</label>
                        <input type="text" name="appTitle" value="<?php echo e($appTitle); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Hero Subtext</label>
                        <input type="text" name="heroSubtext" value="<?php echo e($heroSubtext); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">WhatsApp Number</label>
                        <input type="text" name="waNumber" value="<?php echo e($waNumber); ?>" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange" placeholder="e.g. 2348123456789">
                    </div>
                </div>
                <button type="submit" name="save_settings" class="w-full py-3 bg-white/5 border border-white/10 hover:border-sharp-orange text-white font-bold rounded uppercase tracking-widest text-[10px] transition-all">Update Sync</button>
            </form>
        </div>

        <!-- Hybrid Project Engine -->
        <div id="engineContainer" class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6 transition-colors duration-500 mode-ai">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-black engine-text uppercase tracking-[0.4em] flex items-center gap-2 transition-colors">
                    <i data-lucide="zap" class="w-4 h-4"></i> Project Engine
                </h2>
                <div class="flex bg-black/40 p-1 rounded-lg border border-white/5">
                    <button onclick="setMode('ai')" id="modeAi" class="px-4 py-1.5 rounded text-[9px] font-black uppercase transition-all bg-glossy-purple text-white">AI AUTO</button>
                    <button onclick="setMode('manual')" id="modeManual" class="px-4 py-1.5 rounded text-[9px] font-black uppercase transition-all text-text-dim">MANUAL</button>
                </div>
            </div>

            <div class="flex gap-4 mb-4">
                <input type="text" id="targetUrl" placeholder="https://example.com" class="flex-1 bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange transition-all engine-border">
                <button onclick="triggerCapture()" id="captureBtn" class="px-6 py-3 engine-btn text-black font-black rounded uppercase tracking-widest text-[10px] flex items-center gap-2 transition-all">
                    <i data-lucide="sparkles" id="captureIcon" class="w-4 h-4"></i> <span id="captureText">AI SYNC</span>
                </button>
            </div>

            <form method="POST" class="space-y-4" id="projectForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="id" id="projectId">
                <input type="hidden" name="thumbnail_url" id="projectThumb">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Title</label>
                        <input type="text" name="title" id="projectTitle" placeholder="TITLE" required class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">URL</label>
                        <input type="text" name="url" id="projectUrl" placeholder="URL" required class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Type</label>
                        <select name="type" id="projectType" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                            <option value="web">WEB</option>
                            <option value="app">APP</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Slug</label>
                        <input type="text" name="slug" id="projectSlug" placeholder="SLUG" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                    </div>
                </div>

                <div id="previewArea" class="hidden aspect-video w-full rounded-xl overflow-hidden bg-black/40 border border-white/5 relative group mb-4">
                    <img id="thumbPreview" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                         <span class="text-[10px] font-black uppercase tracking-widest text-white">Visual Captured</span>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Content (Markdown)</label>
                    <textarea name="content" id="projectContent" placeholder="Describe the system architecture..." rows="6" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange"></textarea>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-text-dim uppercase mb-1">WhatsApp Message</label>
                    <textarea name="wa_message" id="projectWa" placeholder="Default inquiry message..." rows="2" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange"></textarea>
                </div>

                <input type="hidden" name="tech_stack" id="projectTech" value='[]'>
                <input type="hidden" name="gallery_images" id="projectGallery" value='[]'>
                <input type="hidden" name="demo_login" id="projectDemo" value='{}'>
                <input type="hidden" name="access_points" id="projectAccess" value='{}'>
                <input type="hidden" name="seo_data" id="projectSeo" value='{}'>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_pinned" id="projectPinned" class="w-4 h-4 accent-sharp-orange">
                    <label for="projectPinned" class="text-xs uppercase font-black text-text-dim">Pin to Hero</label>
                </div>

                <button type="submit" name="save_project" class="w-full py-4 engine-btn text-black font-black rounded uppercase tracking-widest text-sm transition-all shadow-xl">Publish Node</button>
                <button type="button" onclick="resetForm()" class="w-full py-2 bg-white/5 border border-white/10 text-text-dim font-bold rounded uppercase tracking-widest text-[10px] transition-all">Reset Form</button>
            </form>
        </div>

        <!-- Project List -->
        <div class="space-y-4">
            <h2 class="text-xl font-black italic uppercase tracking-tighter text-glow-orange">Grid <span class="text-sharp-orange">Nodes</span></h2>
            <div class="grid grid-cols-1 gap-4">
                <?php foreach ($projects as $proj): ?>
                    <div class="bg-white/5 border border-white/10 p-4 rounded-xl flex items-center justify-between group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded bg-black border border-white/10 flex items-center justify-center overflow-hidden">
                                <?php if ($proj['thumbnail_url']): ?>
                                    <img src="<?php echo e($proj['thumbnail_url']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i data-lucide="image" class="w-4 h-4 text-white/20"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="text-sm font-bold uppercase"><?php echo e($proj['title']); ?></div>
                                <div class="text-[10px] text-text-dim font-mono"><?php echo e($proj['slug']); ?></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button data-proj='<?php echo e(json_encode($proj), ENT_QUOTES, 'UTF-8'); ?>' onclick='editProject(JSON.parse(this.dataset.proj))' class="p-2 bg-white/5 border border-white/10 rounded hover:text-sharp-orange transition-colors"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                            <form method="POST" onsubmit="return confirm('Purge this node?')" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="id" value="<?php echo $proj['id']; ?>">
                                <button type="submit" name="delete_project" class="p-2 bg-white/5 border border-white/10 rounded hover:text-red-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
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

        function triggerCapture() {
            const url = document.getElementById('targetUrl').value;
            if (!url) return alert('Enter a target URL');

            if (currentMode === 'ai') {
                aiScan(url);
            } else {
                psiCapture(url);
            }
        }

        async function aiScan(url) {
            const btn = document.getElementById('captureBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'AI ANALYZING...';
            btn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('url', url);

                const response = await fetch('/admin/ai_generate', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.error) {
                    alert('AI Error: ' + data.error + '. Switching to Manual Mode.');
                    setMode('manual');
                } else {
                    document.getElementById('projectUrl').value = url;
                    document.getElementById('projectTitle').value = data.metaTitle.split('|')[0].trim();
                    document.getElementById('projectContent').value = data.content;
                    document.getElementById('projectWa').value = data.waMessage;
                    document.getElementById('projectTech').value = JSON.stringify(data.techStack);
                    document.getElementById('projectSeo').value = JSON.stringify({
                        metaTitle: data.metaTitle,
                        metaDescription: data.metaDescription,
                        keywords: data.keywords
                    });

                    // Trigger PSI for the screenshot anyway in AI mode for better consistency if needed,
                    // or use the auto-thumbnail. Let's use PSI for reliability.
                    psiCapture(url, true);
                }
            } catch (e) {
                console.error(e);
                alert('AI Engine Failure. Switching to Manual.');
                setMode('manual');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        async function psiCapture(url, silent = false) {
            const btn = document.getElementById('captureBtn');
            const originalText = btn.innerHTML;
            if (!silent) {
                btn.innerHTML = 'CAPTURING Visual...';
                btn.disabled = true;
            }

            try {
                const formData = new FormData();
                formData.append('url', url);
                formData.append('capture_psi', '1');
                formData.append('csrf_token', '<?php echo $csrf_token; ?>');

                const response = await fetch('/admin/index', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.screenshot) {
                    document.getElementById('projectThumb').value = data.screenshot;
                    document.getElementById('thumbPreview').src = data.screenshot;
                    document.getElementById('previewArea').classList.remove('hidden');
                    document.getElementById('projectUrl').value = url;
                    if (!silent) alert('Interface Snapshot Secured.');
                } else {
                    if (!silent) alert('Capture Failed. Check API connectivity.');
                }
            } catch (e) {
                console.error(e);
                if (!silent) alert('Capture Engine Error.');
            } finally {
                if (!silent) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }
        }

        function editProject(proj) {
            document.getElementById('projectId').value = proj.id;
            document.getElementById('projectTitle').value = proj.title;
            document.getElementById('projectUrl').value = proj.url;
            document.getElementById('projectType').value = proj.type;
            document.getElementById('projectSlug').value = proj.slug;
            document.getElementById('projectContent').value = proj.content;
            document.getElementById('projectTech').value = proj.tech_stack;
            document.getElementById('projectSeo').value = proj.seo_data;
            document.getElementById('projectThumb').value = proj.thumbnail_url;
            document.getElementById('projectWa').value = proj.wa_message;
            document.getElementById('projectPinned').checked = proj.is_pinned == 1;

            if (proj.thumbnail_url) {
                document.getElementById('thumbPreview').src = proj.thumbnail_url;
                document.getElementById('previewArea').classList.remove('hidden');
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('projectForm').reset();
            document.getElementById('projectId').value = '';
            document.getElementById('projectTech').value = '[]';
            document.getElementById('projectSeo').value = '{}';
            document.getElementById('projectThumb').value = '';
            document.getElementById('previewArea').classList.add('hidden');
        }
    </script>
</body>
</html>
