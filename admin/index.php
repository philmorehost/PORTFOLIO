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
        $performance_scores = $_POST['performance_scores'];
        $code_snippet = $_POST['code_snippet'];

        if (strpos($thumbnail_url, 'data:image') === 0) {
            $thumbnail_url = save_local_image($thumbnail_url, $slug);
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE projects SET title=?, slug=?, content=?, tech_stack=?, url=?, thumbnail_url=?, gallery_images=?, demo_login=?, access_points=?, type=?, wa_message=?, seo_data=?, is_pinned=?, performance_scores=?, code_snippet=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $tech_stack, $url, $thumbnail_url, $gallery_images, $demo_login, $access_points, $type, $wa_message, $seo_data, $is_pinned, $performance_scores, $code_snippet, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (title, slug, content, tech_stack, url, thumbnail_url, gallery_images, demo_login, access_points, type, wa_message, seo_data, is_pinned, performance_scores, code_snippet) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $tech_stack, $url, $thumbnail_url, $gallery_images, $demo_login, $access_points, $type, $wa_message, $seo_data, $is_pinned, $performance_scores, $code_snippet]);
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
    <link rel="stylesheet" href="/assets/css/theme.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-pitch-black text-white p-4 md:p-10">
    <div class="max-w-6xl mx-auto space-y-12 pb-20">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <h1 class="text-3xl md:text-5xl font-black italic uppercase text-glow-orange">Admin <span class="text-sharp-orange">Console</span></h1>
            <div class="flex items-center gap-4">
                <a href="/admin/nexus" class="p-2 px-4 rounded-lg bg-white/5 border border-white/10 hover:border-glossy-purple transition-all text-[10px] font-black uppercase flex items-center gap-2"><i data-lucide="cpu" class="w-4 h-4"></i> Nexus</a>
                <a href="/admin/profile" class="p-2 px-4 rounded-lg bg-white/5 border border-white/10 hover:border-sharp-orange transition-all text-[10px] font-black uppercase flex items-center gap-2"><i data-lucide="user" class="w-4 h-4"></i> Profile</a>
                <a href="/admin/login.php?logout=1" class="p-2 rounded-lg bg-white/5 border border-white/10 text-text-dim hover:text-red-500 transition-colors"><i data-lucide="log-out"></i></a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-8">
            <div class="space-y-8">
                <!-- Project Engine -->
                <div id="engineContainer" class="bg-white/5 border border-white/10 p-8 rounded-2xl space-y-6 transition-colors duration-500 mode-ai">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-black uppercase tracking-[0.4em] flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4"></i> Project Engine
                        </h2>
                    </div>

                    <div class="flex gap-4">
                        <input type="text" id="targetUrl" placeholder="https://example.com" class="flex-1 bg-black/40 border border-white/10 rounded-lg p-4 outline-none focus:border-sharp-orange transition-all font-mono text-sm">
                        <button onclick="triggerCapture()" id="captureBtn" class="px-8 py-4 bg-glossy-purple text-white font-black rounded-lg uppercase tracking-widest text-xs flex items-center gap-2 shadow-xl hover:brightness-110">
                            <i data-lucide="sparkles" id="captureIcon" class="w-4 h-4"></i> <span id="captureText">AI SYNC</span>
                        </button>
                    </div>

                    <form method="POST" class="space-y-6" id="projectForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="id" id="projectId">
                        <input type="hidden" name="thumbnail_url" id="projectThumb">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="title" id="projectTitle" placeholder="TITLE" required class="bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-sm">
                            <input type="text" name="url" id="projectUrl" placeholder="URL" required class="bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-sm">
                        </div>

                        <div id="previewArea" class="hidden aspect-video w-full rounded-xl overflow-hidden bg-black/40 border border-white/5 relative mb-4">
                            <img id="thumbPreview" class="w-full h-full object-cover">
                        </div>

                        <textarea name="content" id="projectContent" placeholder="Describe the system architecture..." rows="8" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-sm leading-relaxed"></textarea>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                             <div>
                                <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Performance (JSON Scorecard)</label>
                                <input type="text" name="performance_scores" id="projectPerf" value='{"security":98,"ui_ux":95,"scalability":90}' class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-[10px] font-mono">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-text-dim uppercase mb-1">WhatsApp Message</label>
                                <input type="text" name="wa_message" id="projectWa" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange text-[10px]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Ghost Code Snippet</label>
                            <textarea name="code_snippet" id="projectCode" rows="4" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange font-mono text-xs"></textarea>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-white/5">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_pinned" id="projectPinned" class="w-4 h-4 accent-sharp-orange">
                                    <span class="text-[10px] font-black uppercase text-text-dim">Pin Node</span>
                                </label>
                                <select name="type" id="projectType" class="bg-black/40 border border-white/10 rounded p-1 text-[10px] font-black uppercase outline-none">
                                    <option value="web">WEB</option>
                                    <option value="app">APP</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="resetForm()" class="px-6 py-3 bg-white/5 border border-white/10 text-text-dim font-black rounded-lg uppercase tracking-widest text-[10px]">Reset</button>
                                <button type="submit" name="save_project" class="px-10 py-3 bg-sharp-orange text-black font-black rounded-lg uppercase tracking-widest text-[10px] shadow-lg">Publish Node</button>
                            </div>
                        </div>

                        <input type="hidden" name="tech_stack" id="projectTech" value='[]'>
                        <input type="hidden" name="gallery_images" id="projectGallery" value='[]'>
                        <input type="hidden" name="demo_login" id="projectDemo" value='{}'>
                        <input type="hidden" name="access_points" id="projectAccess" value='{}'>
                        <input type="hidden" name="seo_data" id="projectSeo" value='{}'>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="space-y-4">
                <h2 class="text-xl font-black italic uppercase tracking-tighter text-glow-orange">Grid <span class="text-sharp-orange">Nodes</span></h2>
                <div class="space-y-3">
                    <?php foreach ($projects as $proj): ?>
                        <div class="bg-white/5 border border-white/10 p-4 rounded-xl flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <img src="<?php echo e($proj['thumbnail_url']); ?>" class="w-10 h-10 rounded border border-white/10 object-cover">
                                <div>
                                    <div class="text-[11px] font-bold uppercase"><?php echo e($proj['title']); ?></div>
                                    <div class="text-[9px] text-text-dim font-mono"><?php echo e($proj['slug']); ?></div>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button data-proj='<?php echo e(json_encode($proj), ENT_QUOTES, 'UTF-8'); ?>' onclick='editProject(JSON.parse(this.dataset.proj))' class="p-2 hover:text-sharp-orange"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                <form method="POST" onsubmit="return confirm('Purge Node?')" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="id" value="<?php echo $proj['id']; ?>">
                                    <button type="submit" name="delete_project" class="p-2 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
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

        function editProject(proj) {
            document.getElementById('projectId').value = proj.id;
            document.getElementById('projectTitle').value = proj.title;
            document.getElementById('projectUrl').value = proj.url;
            document.getElementById('projectType').value = proj.type;
            document.getElementById('projectContent').value = proj.content;
            document.getElementById('projectPerf').value = proj.performance_scores;
            document.getElementById('projectCode').value = proj.code_snippet;
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

        async function triggerCapture() {
            const url = document.getElementById('targetUrl').value;
            if (!url) return alert('Enter target URL');
            const btn = document.getElementById('captureBtn');
            btn.disabled = true;
            btn.innerText = 'SYNCING...';

            try {
                const formData = new FormData();
                formData.append('url', url);
                const response = await fetch('/admin/ai_generate', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.error) throw new Error(data.error);

                document.getElementById('projectUrl').value = url;
                document.getElementById('projectTitle').value = data.metaTitle.split('|')[0].trim();
                document.getElementById('projectContent').value = data.content;
                document.getElementById('projectWa').value = data.waMessage;
                document.getElementById('projectPerf').value = JSON.stringify(data.performance_scores);
                document.getElementById('projectCode').value = data.code_snippet;
                document.getElementById('projectTech').value = JSON.stringify(data.techStack);
                document.getElementById('projectSeo').value = JSON.stringify({
                    metaTitle: data.metaTitle, metaDescription: data.metaDescription, keywords: data.keywords
                });

                // PSI Screenshot
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
                }
                alert('Intelligence Sync Complete.');
            } catch (e) {
                alert('Sync Error: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.innerText = 'AI SYNC';
            }
        }

        function resetForm() {
            document.getElementById('projectForm').reset();
            document.getElementById('projectId').value = '';
            document.getElementById('previewArea').classList.add('hidden');
        }
    </script>
</body>
</html>
