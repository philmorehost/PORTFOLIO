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

        <!-- Add/Edit Project (Simple Form) -->
        <div class="bg-white/5 border border-white/10 p-8 rounded-xl space-y-6">
            <h2 class="text-xs font-black text-sharp-orange uppercase tracking-[0.4em] flex items-center gap-2">
                <i data-lucide="plus-square" class="w-4 h-4"></i> Project Engine
            </h2>
            <div class="flex gap-4 mb-4">
                <input type="text" id="aiUrl" placeholder="https://example.com" class="flex-1 bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                <button onclick="aiScan()" id="aiBtn" class="px-6 py-3 bg-glossy-purple text-white font-bold rounded uppercase tracking-widest text-[10px] flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4"></i> AI SCAN
                </button>
            </div>
            <form method="POST" class="space-y-4" id="projectForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="id" id="projectId">
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
                        <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Slug (auto-generated if empty)</label>
                        <input type="text" name="slug" id="projectSlug" placeholder="SLUG" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-text-dim uppercase mb-1">Power Pitch Content</label>
                    <textarea name="content" id="projectContent" placeholder="CONTENT (Markdown)" rows="8" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange"></textarea>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-text-dim uppercase mb-1">WhatsApp Payload</label>
                    <textarea name="wa_message" id="projectWa" placeholder="WhatsApp message..." rows="2" class="w-full bg-black/40 border border-white/10 rounded-lg p-3 outline-none focus:border-sharp-orange"></textarea>
                </div>

                <input type="hidden" name="tech_stack" id="projectTech" value='[]'>
                <input type="hidden" name="gallery_images" id="projectGallery" value='[]'>
                <input type="hidden" name="demo_login" id="projectDemo" value='{}'>
                <input type="hidden" name="access_points" id="projectAccess" value='{}'>
                <input type="hidden" name="seo_data" id="projectSeo" value='{}'>
                <input type="hidden" name="thumbnail_url" id="projectThumb" value="">

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_pinned" id="projectPinned" class="w-4 h-4 accent-sharp-orange">
                    <label for="projectPinned" class="text-xs uppercase font-black text-text-dim">Pin to Hero</label>
                </div>

                <button type="submit" name="save_project" class="w-full py-4 bg-sharp-orange text-black font-black rounded uppercase tracking-widest text-sm transition-all shadow-[0_0_20px_rgba(255,102,0,0.3)]">Publish Node</button>
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
                            <button onclick='editProject(<?php echo json_encode($proj); ?>)' class="p-2 bg-white/5 border border-white/10 rounded hover:text-sharp-orange transition-colors"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
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

        function editProject(proj) {
            document.getElementById('projectId').value = proj.id;
            document.getElementById('projectTitle').value = proj.title;
            document.getElementById('projectUrl').value = proj.url;
            document.getElementById('projectType').value = proj.type;
            document.getElementById('projectSlug').value = proj.slug;
            document.getElementById('projectContent').value = proj.content;
            document.getElementById('projectTech').value = proj.tech_stack;
            document.getElementById('projectGallery').value = proj.gallery_images;
            document.getElementById('projectDemo').value = proj.demo_login;
            document.getElementById('projectAccess').value = proj.access_points;
            document.getElementById('projectSeo').value = proj.seo_data;
            document.getElementById('projectThumb').value = proj.thumbnail_url;
            document.getElementById('projectWa').value = proj.wa_message;
            document.getElementById('projectPinned').checked = proj.is_pinned == 1;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('projectForm').reset();
            document.getElementById('projectId').value = '';
            document.getElementById('projectTech').value = '[]';
            document.getElementById('projectGallery').value = '[]';
            document.getElementById('projectDemo').value = '{}';
            document.getElementById('projectAccess').value = '{}';
            document.getElementById('projectSeo').value = '{}';
            document.getElementById('projectThumb').value = '';
        }

        async function aiScan() {
            const url = document.getElementById('aiUrl').value;
            if (!url) return alert('Enter a URL first');

            const btn = document.getElementById('aiBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'SCANNING...';
            btn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('url', url);

                const response = await fetch('/admin/ai_generate', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.error) {
                    alert('AI Scan Failed: ' + data.error);
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

                    // Auto-screenshot using microlink as a fallback/example
                    const thumb = `https://api.microlink.io/?url=${encodeURIComponent(url)}&screenshot=true&embed=screenshot.url`;
                    document.getElementById('projectThumb').value = thumb;

                    alert('AI Analysis Complete. Review and publish.');
                }
            } catch (e) {
                console.error(e);
                alert('An error occurred during AI scan.');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
