<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();

$csrf_token = generate_csrf_token();
$api = get_api_settings($pdo);
$baseUrl = get_base_url();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['switch_provider'])) {
        $stmt = $pdo->prepare("UPDATE api_settings SET provider=? WHERE id=1");
        $stmt->execute([$_POST['provider']]);
        header("Location: " . $baseUrl . "/admin/");
        exit;
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
        $gallery = $_POST['gallery']; // JSON string of Base64
        $demo_access = $_POST['demo_access']; // JSON string
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;

        if (strpos($screenshot_path, 'data:image') === 0) {
            $screenshot_path = save_local_image($screenshot_path, $slug);
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE projects SET title=?, slug=?, category=?, description=?, screenshot_path=?, demo_link=?, tech_stack=?, seo_tags=?, wa_custom_message=?, performance_scores=?, code_snippet=?, gallery=?, demo_access=?, is_pinned=? WHERE id=?");
            $stmt->execute([$title, $slug, $category, $description, $screenshot_path, $demo_link, $tech_stack, $seo_tags, $wa_custom_message, $perf, $code, $gallery, $demo_access, $is_pinned, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (title, slug, category, description, screenshot_path, demo_link, tech_stack, seo_tags, wa_custom_message, performance_scores, code_snippet, gallery, demo_access, is_pinned) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $category, $description, $screenshot_path, $demo_link, $tech_stack, $seo_tags, $wa_custom_message, $perf, $code, $gallery, $demo_access, $is_pinned]);
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
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-pitch-black text-white p-4 md:p-10 font-sans">
    <div class="max-w-7xl mx-auto space-y-12 pb-20">

        <header class="flex flex-col lg:flex-row items-center justify-between gap-8 bg-white/5 border border-white/10 p-8 rounded-3xl backdrop-blur-xl">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-sharp-orange rounded-2xl flex items-center justify-center font-black italic text-black text-2xl shadow-lg">P</div>
                <h1 class="text-2xl md:text-4xl font-black italic uppercase tracking-tighter">Nexus_<span class="text-sharp-orange">GRID</span></h1>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <form method="POST" class="flex bg-black/60 p-1.5 rounded-2xl border border-white/10">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="switch_provider" value="1">
                    <button type="submit" name="provider" value="gemini" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase transition-all <?php echo $api['provider'] === 'gemini' ? 'bg-glossy-purple text-white shadow-xl' : 'text-text-dim hover:text-white'; ?>">Gemini</button>
                    <button type="submit" name="provider" value="deepseek" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase transition-all <?php echo $api['provider'] === 'deepseek' ? 'bg-glossy-purple text-white shadow-xl' : 'text-text-dim hover:text-white'; ?>">DeepSeek</button>
                </form>

                <nav class="flex items-center gap-3">
                    <a href="<?php echo $baseUrl; ?>/admin/nexus" class="p-3 px-5 rounded-xl bg-white/5 border border-white/10 hover:border-glossy-purple transition-all text-[10px] font-black uppercase flex items-center gap-2 tracking-widest"><i data-lucide="cpu" class="w-4 h-4"></i> Nexus</a>
                    <a href="<?php echo $baseUrl; ?>/admin/profile" class="p-3 px-5 rounded-xl bg-white/5 border border-white/10 hover:border-sharp-orange transition-all text-[10px] font-black uppercase flex items-center gap-2 tracking-widest"><i data-lucide="user" class="w-4 h-4"></i> Identity</a>
                    <a href="<?php echo $baseUrl; ?>/admin/login.php?logout=1" class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500 transition-all"><i data-lucide="log-out" class="w-4 h-4"></i></a>
                </nav>
            </div>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_450px] gap-10">
            <div id="engineContainer" class="bg-white/5 border border-white/10 p-10 rounded-3xl space-y-10 transition-all duration-500 mode-ai shadow-2xl">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 border-b border-white/5 pb-8">
                    <h2 class="text-xl font-black uppercase engine-text tracking-[0.3em] flex items-center gap-3 transition-colors"><i data-lucide="zap" class="w-6 h-6"></i> Engine</h2>
                    <div class="flex bg-black/60 p-1.5 rounded-2xl border border-white/10">
                        <button onclick="setMode('ai')" id="modeAi" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase transition-all bg-glossy-purple text-white">AI_AUTO</button>
                        <button onclick="setMode('manual')" id="modeManual" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase transition-all text-text-dim">MANUAL</button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex gap-4">
                        <input type="text" id="targetUrl" placeholder="https://external-node.build" class="flex-1 bg-black/60 border border-white/10 rounded-2xl p-5 outline-none engine-border transition-all font-mono text-sm">
                        <button onclick="triggerCapture()" id="captureBtn" class="px-10 py-5 engine-btn font-black rounded-2xl uppercase tracking-[0.2em] text-[11px] flex items-center gap-3 shadow-2xl hover:scale-[1.02] transition-all">
                            <i data-lucide="sparkles" id="captureIcon" class="w-5 h-5"></i> <span id="captureText">SYNC</span>
                        </button>
                    </div>
                </div>

                <form method="POST" class="space-y-8 pt-6" id="projectForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="projectId">
                    <input type="hidden" name="screenshot_path" id="projectThumb">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="text" name="title" id="projectTitle" placeholder="NODE_TITLE" required class="w-full bg-white/5 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange transition-all font-bold text-sm">
                        <input type="text" name="demo_link" id="projectUrl" placeholder="DEMO_LINK" required class="w-full bg-white/5 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange transition-all font-mono text-sm">
                    </div>

                    <div id="previewArea" class="hidden aspect-video w-full rounded-3xl overflow-hidden bg-black/60 border border-white/10 relative shadow-2xl">
                        <img id="thumbPreview" class="w-full h-full object-cover">
                    </div>

                    <textarea name="description" id="projectDesc" rows="8" placeholder="Power Pitch (Markdown)..." class="w-full bg-white/5 border border-white/10 rounded-2xl p-5 outline-none focus:border-sharp-orange transition-all text-sm leading-relaxed font-medium"></textarea>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="text" name="wa_custom_message" id="projectWa" placeholder="WhatsApp_Payload" class="w-full bg-white/5 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange text-[10px] font-mono">
                        <input type="text" name="slug" id="projectSlug" placeholder="NODE_SLUG" class="w-full bg-white/5 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange text-[10px] font-mono">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="text" name="performance_scores" id="projectPerf" value='{"security":98,"ui_ux":95,"scalability":90}' class="w-full bg-black/60 border border-white/10 rounded-xl p-4 outline-none focus:border-glossy-purple text-[10px] font-mono text-white">
                        <input type="text" name="tech_stack" id="projectTech" value='[]' class="w-full bg-black/60 border border-white/10 rounded-xl p-4 outline-none focus:border-sharp-orange text-[10px] font-mono text-white">
                    </div>

                    <textarea name="code_snippet" id="projectCode" rows="6" placeholder="// Ghost Architecture Code..." class="w-full bg-black/60 border border-white/10 rounded-2xl p-5 outline-none focus:border-sharp-orange font-mono text-xs leading-relaxed text-white/80"></textarea>

                    <div class="bg-white/5 border border-white/10 p-6 rounded-2xl space-y-4">
                        <h3 class="text-[10px] font-black uppercase text-sharp-orange tracking-[0.3em]">Multi-Tier Demo Access</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input type="text" id="demoL0" placeholder="L0: Super (URL)" class="bg-black/60 border border-white/10 rounded-xl p-3 text-[10px] font-mono outline-none focus:border-sharp-orange">
                            <input type="text" id="demoL1" placeholder="L1: Restricted (URL)" class="bg-black/60 border border-white/10 rounded-xl p-3 text-[10px] font-mono outline-none focus:border-sharp-orange">
                            <input type="text" id="demoL2" placeholder="L2: Standard (URL)" class="bg-black/60 border border-white/10 rounded-xl p-3 text-[10px] font-mono outline-none focus:border-sharp-orange">
                        </div>
                        <input type="hidden" name="demo_access" id="projectDemoAccess" value='{}'>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black uppercase text-glossy-purple tracking-[0.3em]">Project Gallery (Max 5)</h3>
                        <div id="galleryDropzone" class="border-2 border-dashed border-white/10 rounded-2xl p-8 text-center hover:border-glossy-purple transition-all cursor-pointer bg-white/5">
                            <i data-lucide="image-plus" class="w-8 h-8 mx-auto mb-3 text-text-dim"></i>
                            <p class="text-[10px] text-text-dim font-black uppercase">Drag & Drop Images or Click to Upload</p>
                            <input type="file" id="galleryInput" multiple accept="image/*" class="hidden">
                        </div>
                        <div id="galleryPreview" class="flex flex-wrap gap-4 pt-4"></div>
                        <input type="hidden" name="gallery" id="projectGallery" value='[]'>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-10 border-t border-white/5">
                        <div class="flex items-center gap-8">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_pinned" id="projectPinned" class="w-5 h-5 rounded-md accent-sharp-orange border-white/20 bg-black/40">
                                <span class="text-[11px] font-black uppercase text-text-dim group-hover:text-white transition-colors tracking-widest">Pin_Node</span>
                            </label>
                            <select name="category" id="projectCat" class="bg-black/60 border border-white/10 rounded-lg p-2 text-[10px] font-black uppercase outline-none focus:border-sharp-orange">
                                <option value="WEB">WEB</option>
                                <option value="APP">APP</option>
                            </select>
                        </div>
                        <div class="flex gap-4 w-full sm:w-auto">
                            <button type="button" onclick="resetForm()" class="flex-1 sm:flex-none px-8 py-4 bg-white/5 border border-white/10 text-text-dim font-black rounded-2xl uppercase tracking-widest text-[11px] hover:bg-white/10 transition-all">Clear</button>
                            <button type="submit" name="save_project" class="flex-1 sm:flex-none px-12 py-4 bg-sharp-orange text-black font-black rounded-2xl uppercase tracking-widest text-[11px] shadow-lg hover:brightness-110 active:scale-95">Publish</button>
                        </div>
                    </div>
                    <input type="hidden" name="seo_tags" id="projectSeo" value='{}'>
                </form>
            </div>

            <div class="space-y-8">
                <div class="bg-white/5 border border-white/10 p-8 rounded-3xl space-y-8 shadow-2xl">
                    <h2 class="text-xl font-black italic uppercase tracking-tighter"><span class="text-sharp-orange">Active</span>_Nodes</h2>
                    <div class="space-y-4 max-h-[1000px] overflow-y-auto pr-2 custom-scroll">
                        <?php foreach ($projects as $proj): ?>
                            <div class="bg-black/40 border border-white/5 p-5 rounded-2xl flex items-center justify-between group hover:border-sharp-orange/30 transition-all duration-300">
                                <div class="flex items-center gap-5">
                                    <img src="<?php echo $baseUrl . $proj['screenshot_path']; ?>" class="w-14 h-14 rounded-xl border border-white/10 object-cover group-hover:scale-110 transition-transform">
                                    <div>
                                        <div class="text-[12px] font-black uppercase text-white leading-none mb-1"><?php echo e($proj['title']); ?></div>
                                        <span class="text-[9px] text-text-dim font-mono italic"><?php echo e($proj['category']); ?></span>
                                    </div>
                                </div>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                    <button data-proj='<?php echo e(json_encode($proj), ENT_QUOTES, 'UTF-8'); ?>' onclick='editProject(JSON.parse(this.dataset.proj))' class="p-3 bg-white/5 rounded-xl hover:text-sharp-orange transition-all"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                    <form method="POST" onsubmit="return confirm('Purge Node?')" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="id" value="<?php echo $proj['id']; ?>">
                                        <button type="submit" name="delete_project" class="p-3 bg-white/5 rounded-xl hover:text-red-500 transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
                captureText.innerText = 'SYNC';
                captureIcon.setAttribute('data-lucide', 'sparkles');
            } else {
                container.classList.remove('mode-ai');
                container.classList.add('mode-manual');
                modeManual.classList.add('bg-sharp-orange', 'text-black', 'shadow-lg');
                modeManual.classList.remove('text-text-dim');
                modeAi.classList.remove('bg-glossy-purple', 'text-white', 'shadow-lg');
                modeAi.classList.add('text-text-dim');
                captureText.innerText = 'CAPTURE';
                captureIcon.setAttribute('data-lucide', 'camera');
            }
            lucide.createIcons();
        }

        document.getElementById('projectForm').onsubmit = function() {
            const demo = {
                l0: document.getElementById('demoL0').value,
                l1: document.getElementById('demoL1').value,
                l2: document.getElementById('demoL2').value
            };
            document.getElementById('projectDemoAccess').value = JSON.stringify(demo);
        };

        async function triggerCapture() {
            const url = document.getElementById('targetUrl').value;
            if (!url) return alert('Enter target URL');
            const btn = document.getElementById('captureBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'PROCESSING...';

            try {
                if (currentMode === 'ai') {
                    const formData = new FormData();
                    formData.append('url', url);
                    const response = await fetch('<?php echo $baseUrl; ?>/admin/ai_generate', { method: 'POST', body: formData });
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

                const psiFormData = new FormData();
                psiFormData.append('url', url);
                psiFormData.append('capture_psi', '1');
                psiFormData.append('csrf_token', '<?php echo $csrf_token; ?>');
                const psiRes = await fetch('<?php echo $baseUrl; ?>/admin/index', { method: 'POST', body: psiFormData });
                const psiData = await psiRes.json();
                if (psiData.screenshot) {
                    document.getElementById('projectThumb').value = psiData.screenshot;
                    document.getElementById('thumbPreview').src = psiData.screenshot;
                    document.getElementById('previewArea').classList.remove('hidden');
                    document.getElementById('projectUrl').value = url;
                }
                alert('Intelligence_Sync_Confirmed.');
            } catch (e) {
                alert('SYNC_ERROR: ' + e.message + '. Defaulting to Manual sequence.');
                setMode('manual');
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

            galleryItems = JSON.parse(proj.gallery || '[]');
            renderGallery();

            const demo = JSON.parse(proj.demo_access || '{}');
            document.getElementById('demoL0').value = demo.l0 || '';
            document.getElementById('demoL1').value = demo.l1 || '';
            document.getElementById('demoL2').value = demo.l2 || '';
            document.getElementById('projectDemoAccess').value = proj.demo_access || '{}';

            if (proj.screenshot_path) {
                document.getElementById('thumbPreview').src = '<?php echo $baseUrl; ?>' + proj.screenshot_path;
                document.getElementById('previewArea').classList.remove('hidden');
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('projectForm').reset();
            document.getElementById('projectId').value = '';
            document.getElementById('projectThumb').value = '';
            document.getElementById('projectGallery').value = '[]';
            document.getElementById('galleryPreview').innerHTML = '';
            document.getElementById('projectDemoAccess').value = '{}';
            document.getElementById('demoL0').value = '';
            document.getElementById('demoL1').value = '';
            document.getElementById('demoL2').value = '';
            document.getElementById('previewArea').classList.add('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Gallery Logic
        const dropzone = document.getElementById('galleryDropzone');
        const galleryInput = document.getElementById('galleryInput');
        const galleryPreview = document.getElementById('galleryPreview');
        const projectGallery = document.getElementById('projectGallery');
        let galleryItems = [];

        dropzone.onclick = () => galleryInput.click();

        galleryInput.onchange = (e) => handleFiles(e.target.files);

        dropzone.ondragover = (e) => { e.preventDefault(); dropzone.classList.add('border-glossy-purple'); };
        dropzone.ondragleave = () => dropzone.classList.remove('border-glossy-purple');
        dropzone.ondrop = (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-glossy-purple');
            handleFiles(e.dataTransfer.files);
        };

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (galleryItems.length >= 5) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    galleryItems.push(e.target.result);
                    renderGallery();
                };
                reader.readAsDataURL(file);
            });
        }

        function renderGallery() {
            galleryPreview.innerHTML = '';
            galleryItems.forEach((item, idx) => {
                const div = document.createElement('div');
                div.className = 'relative w-24 h-24 rounded-xl overflow-hidden border border-white/10 group';
                div.innerHTML = `
                    <img src="${item}" class="w-full h-full object-cover">
                    <button type="button" onclick="removeGalleryItem(${idx})" class="absolute inset-0 bg-red-500/80 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                `;
                galleryPreview.appendChild(div);
            });
            projectGallery.value = JSON.stringify(galleryItems);
            lucide.createIcons();
        }

        function removeGalleryItem(idx) {
            galleryItems.splice(idx, 1);
            renderGallery();
        }
    </script>
</body>
</html>
