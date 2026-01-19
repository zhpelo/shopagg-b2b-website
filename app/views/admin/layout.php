<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        body { background: #f4f7f9; min-height: 100vh; display: flex; flex-direction: column; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif; }
        .admin-main { flex: 1; }
        .admin-card { box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 8px; border: 1px solid #edf2f7; background: #fff; }
        
        /* 导航样式优化 */
        .admin-navbar { border-bottom: 1px solid #edf2f7; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .admin-subnav { background: #fff; border-bottom: 1px solid #edf2f7; padding: 0; margin-bottom: 1.5rem; }
        .admin-subnav .container { display: flex; align-items: stretch; overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; }
        .admin-subnav .navbar-item { 
            font-size: 0.9rem; 
            color: #64748b; 
            padding: 0.75rem 1rem;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .admin-subnav .navbar-item:hover { background: #f8fafc; color: #3b82f6; }
        .admin-subnav .navbar-item.is-active { 
            color: #3b82f6; 
            font-weight: 600; 
            border-bottom-color: #3b82f6;
            background: #eff6ff;
        }
        
        /* 隐藏滚动条但保持功能 */
        .admin-subnav .container::-webkit-scrollbar { display: none; }
        .admin-subnav .container { -ms-overflow-style: none; scrollbar-width: none; }

        .relative{position:relative}
        .media-select-item.is-selected{background-color:#f0f7ff; border-color: #3273dc !important;}
        
        /* 媒体网格样式 */
        #media-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; }
        .media-item { aspect-ratio: 1/1; border: 1px solid #dbdbdb; border-radius: 8px; overflow: hidden; background: #fff; cursor: grab; position: relative; }
        .media-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .media-item .delete { position: absolute; top: 8px; right: 8px; z-index: 10; display: none; }
        .media-item:hover .delete { display: block; }

        .media-add-btn { aspect-ratio: 1/1; border: 1px dashed #dbdbdb; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; background: #fdfdfd; transition: all .2s; }
        .media-add-btn:hover { border-color: #3273dc; background: #f9f9f9; }
        
        .media-placeholder { border: 1px dashed #dbdbdb; border-radius: 8px; padding: 40px; text-align: center; background: #fff; }
        .media-placeholder .buttons { justify-content: center; margin-bottom: 10px; }
        
        .ql-editor { font-size: 16px !important; min-height: 200px; }

        /* 响应式表格 */
        .table-container { overflow-x: auto; }
    </style>
</head>
<body>
    <?php if ($showNav ?? true): 
    $current_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $active_group = '';
    if (str_starts_with($current_path, '/admin/products') || str_starts_with($current_path, '/admin/categories')) $active_group = 'catalog';
    elseif (str_starts_with($current_path, '/admin/cases')) $active_group = 'cases';
    elseif (str_starts_with($current_path, '/admin/posts')) $active_group = 'blog';
    elseif (str_starts_with($current_path, '/admin/messages') || str_starts_with($current_path, '/admin/inquiries')) $active_group = 'inbox';
    elseif (str_starts_with($current_path, '/admin/staff')) $active_group = 'staff';

    $user_role = $_SESSION['admin_role'] ?? 'staff';
    $user_perms = $_SESSION['admin_permissions'] ?? [];
    
    $can_access = function($perm) use ($user_role, $user_perms) {
        return $user_role === 'admin' || in_array($perm, $user_perms);
    };
?>
    <!-- 第一级主导航 -->
    <nav class="navbar is-white admin-navbar" role="navigation" aria-label="main navigation">
        <div class="container">
            <div class="navbar-brand">
                <a class="navbar-item has-text-weight-bold is-size-5" href="/admin">
                    <span class="icon has-text-link mr-1"><i class="fas fa-rocket"></i></span>
                    <i>SHOPAGG Admin</i>  
                </a>
                <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="adminNavbar">
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </a>
            </div>

            <div id="adminNavbar" class="navbar-menu">
                <div class="navbar-start">
                    <a class="navbar-item <?= $current_path === '/admin' ? 'is-active' : '' ?>" href="/admin">
                        <span class="icon mr-1"><i class="fas fa-home"></i></span>仪表盘
                    </a>
                    <?php if ($can_access('products')): ?>
                    <a class="navbar-item <?= $active_group === 'catalog' ? 'is-active' : '' ?>" href="/admin/products">
                        <span class="icon mr-1"><i class="fas fa-box"></i></span>产品中心
                    </a>
                    <?php endif; ?>
                    <?php if ($can_access('cases')): ?>
                    <a class="navbar-item <?= $active_group === 'cases' ? 'is-active' : '' ?>" href="/admin/cases">
                        <span class="icon mr-1"><i class="fas fa-briefcase"></i></span>案例展示
                    </a>
                    <?php endif; ?>
                    <?php if ($can_access('blog')): ?>
                    <a class="navbar-item <?= $active_group === 'blog' ? 'is-active' : '' ?>" href="/admin/posts">
                        <span class="icon mr-1"><i class="fas fa-pen-nib"></i></span>内容管理
                    </a>
                    <?php endif; ?>
                    <?php if ($can_access('inbox')): ?>
                    <a class="navbar-item <?= $active_group === 'inbox' ? 'is-active' : '' ?>" href="/admin/messages">
                        <span class="icon mr-1"><i class="fas fa-envelope"></i></span>收件箱
                    </a>
                    <?php endif; ?>
                    <?php if ($user_role === 'admin'): ?>
                    <a class="navbar-item <?= $active_group === 'staff' ? 'is-active' : '' ?>" href="/admin/staff">
                        <span class="icon mr-1"><i class="fas fa-users"></i></span>员工管理
                    </a>
                    <?php endif; ?>
                </div>

                <div class="navbar-end">
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link">
                            <span class="icon mr-1"><i class="fas fa-user-circle"></i></span>
                            <?= h($_SESSION['admin_display_name'] ?? $_SESSION['admin_user'] ?? 'Admin') ?>
                        </a>
                        <div class="navbar-dropdown is-right">
                            <a class="navbar-item" href="/admin/profile">
                                <span class="icon mr-1"><i class="fas fa-id-card"></i></span>个人资料
                            </a>
                            <?php if ($can_access('settings')): ?>
                            <a class="navbar-item" href="/admin/settings">
                                <span class="icon mr-1"><i class="fas fa-cog"></i></span>系统设置
                            </a>
                            <?php endif; ?>
                            <hr class="navbar-divider">
                            <a class="navbar-item has-text-danger" href="/admin/logout">
                                <span class="icon mr-1"><i class="fas fa-sign-out-alt"></i></span>退出登录
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- 第二级二级导航 -->
    <?php if ($active_group): ?>
    <nav class="admin-subnav">
        <div class="container admin-subnav-container">
            <?php if ($active_group === 'catalog'): ?>
                <a class="navbar-item <?= str_contains($current_path, '/products') ? 'is-active' : '' ?>" href="/admin/products">产品列表</a>
                <a class="navbar-item <?= str_contains($current_path, '/categories') ? 'is-active' : '' ?>" href="/admin/categories">产品分类</a>
            <?php elseif ($active_group === 'cases'): ?>
                <a class="navbar-item is-active" href="/admin/cases">所有案例</a>
                <a class="navbar-item" href="/admin/cases/create">新增案例</a>
            <?php elseif ($active_group === 'blog'): ?>
                <a class="navbar-item is-active" href="/admin/posts">文章列表</a>
                <a class="navbar-item" href="/admin/posts/create">发布文章</a>
            <?php elseif ($active_group === 'inbox'): ?>
                <a class="navbar-item <?= str_contains($current_path, '/messages') ? 'is-active' : '' ?>" href="/admin/messages">联系留言</a>
                <a class="navbar-item <?= str_contains($current_path, '/inquiries') ? 'is-active' : '' ?>" href="/admin/inquiries">询单管理</a>
            <?php elseif ($active_group === 'staff'): ?>
                <a class="navbar-item <?= $current_path === '/admin/staff' ? 'is-active' : '' ?>" href="/admin/staff">员工列表</a>
                <a class="navbar-item <?= str_contains($current_path, '/staff/create') ? 'is-active' : '' ?>" href="/admin/staff/create">新增员工</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>

<?php endif; ?>

<div class="admin-main">
    <section class="section">
        <div class="container">
            <?= $content ?>
        </div>
    </section>
</div>

<!-- 统一媒体库模态框 -->
<div class="modal" id="media-library-modal">
    <div class="modal-background"></div>
    <div class="modal-card" style="width: 90%; max-width: 1000px;">
        <header class="modal-card-head">
            <p class="modal-card-title">选择媒体文件</p>
            <div class="field mb-0 mr-3">
                <div class="control">
                    <div class="file is-info is-small">
                        <label class="file-label">
                            <input class="file-input" type="file" id="media-upload-input" multiple accept="image/*">
                            <span class="file-cta">
                                <span class="file-icon"><i class="fas fa-upload"></i></span>
                                <span class="file-label">上传图片</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            <button type="button" class="delete close-modal" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
            <div id="upload-progress-container" class="mb-4 is-hidden">
                <div class="is-size-7 mb-1 is-flex is-justify-content-between">
                    <span id="upload-status-text">正在上传...</span>
                    <span id="upload-progress-percent">0%</span>
                </div>
                <progress id="overall-progress" class="progress is-info is-small" value="0" max="100">0%</progress>
            </div>
            <div id="media-library-list" class="columns is-multiline is-mobile">
                <!-- 动态加载图片列表 -->
            </div>
        </section>
        <footer class="modal-card-head" style="justify-content: flex-end;">
            <button type="button" class="button is-link" id="confirm-media-selection" style="display:none">确认选择</button>
            <button type="button" class="button close-modal ml-2">取消</button>
        </footer>
    </div>
</div>

<footer class="footer has-background-white border-top py-5">
    <div class="container has-text-centered">
        <p class="is-size-7 has-text-grey">
            &copy; <?= date('Y') ?> SHOPAGG B2B Management Platform. Powered by <a href="https://www.shopagg.com" target="_blank">SHOPAGG</a>.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
// 全局媒体库逻辑
let mediaLibraryCallback = null;
let isMultiSelect = false;

function openMediaLibrary(callback, multi = false) {
    mediaLibraryCallback = callback;
    isMultiSelect = multi;
    const modal = document.getElementById('media-library-modal');
    const confirmBtn = document.getElementById('confirm-media-selection');
    
    confirmBtn.style.display = multi ? 'inline-flex' : 'none';
    modal.classList.add('is-active');
    fetchMediaLibrary();
}

async function fetchMediaLibrary() {
    const container = document.getElementById('media-library-list');
    container.innerHTML = '<div class="column is-12 has-text-centered p-6"><p>正在加载...</p></div>';
    try {
        const res = await fetch('/admin/media-library');
        const files = await res.json();
        container.innerHTML = '';
        files.forEach(file => {
            const col = document.createElement('div');
            col.className = 'column is-2-desktop is-3-tablet is-4-mobile';
            col.innerHTML = `
                <div class="card media-select-item" data-url="${file}" style="cursor: pointer; border: 2px solid transparent; border-radius: 6px; overflow:hidden;">
                    <div class="card-image">
                        <figure class="image is-1by1">
                            <img src="${file}" style="object-fit: cover;">
                        </figure>
                    </div>
                </div>
            `;
            col.querySelector('.media-select-item').addEventListener('click', function() {
                if (isMultiSelect) {
                    this.classList.toggle('is-selected');
                } else {
                    if (mediaLibraryCallback) mediaLibraryCallback(this.dataset.url);
                    document.getElementById('media-library-modal').classList.remove('is-active');
                }
            });
            container.appendChild(col);
        });
    } catch (err) {
        container.innerHTML = '<div class="column is-12 has-text-centered p-6"><p class="has-text-danger">加载失败</p></div>';
    }
}

document.addEventListener("DOMContentLoaded", function () {
    // 手机端导航切换
    const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
    $navbarBurgers.forEach( el => {
        el.addEventListener('click', () => {
            const target = el.dataset.target;
            const $target = document.getElementById(target);
            el.classList.toggle('is-active');
            $target.classList.toggle('is-active');
        });
    });

    // 媒体库通用事件
    const modal = document.getElementById('media-library-modal');
    const closeBtns = document.querySelectorAll('.close-modal, .modal-background');
    closeBtns.forEach(btn => btn.addEventListener('click', () => modal.classList.remove('is-active')));

    const confirmBtn = document.getElementById('confirm-media-selection');
    confirmBtn.addEventListener('click', () => {
        const selected = Array.from(document.querySelectorAll('.media-select-item.is-selected')).map(el => el.dataset.url);
        if (mediaLibraryCallback) mediaLibraryCallback(selected);
        modal.classList.remove('is-active');
    });

    const uploadInput = document.getElementById('media-upload-input');
    if (uploadInput) {
        uploadInput.addEventListener('change', async function() {
            const files = Array.from(this.files);
            if (files.length === 0) return;
            const progressContainer = document.getElementById('upload-progress-container');
            const overallProgress = document.getElementById('overall-progress');
            const progressPercent = document.getElementById('upload-progress-percent');
            const statusText = document.getElementById('upload-status-text');

            progressContainer.classList.remove('is-hidden');
            overallProgress.value = 0;
            progressPercent.innerText = '0%';
            
            const fileProgresses = new Array(files.length).fill(0);
            const uploadTasks = files.map((file, index) => {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '/admin/upload-image', true);
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            fileProgresses[index] = e.loaded / e.total;
                            const totalProgress = fileProgresses.reduce((a, b) => a + b, 0) / files.length;
                            const percent = Math.round(totalProgress * 100);
                            overallProgress.value = percent;
                            progressPercent.innerText = percent + '%';
                        }
                    };
                    xhr.onload = () => resolve();
                    xhr.onerror = () => reject();
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('csrf', '<?= csrf_token() ?>');
                    xhr.send(formData);
                });
            });

            try {
                await Promise.all(uploadTasks);
                setTimeout(() => {
                    progressContainer.classList.add('is-hidden');
                    fetchMediaLibrary();
                }, 1000);
            } catch (err) { alert('上传失败'); }
            this.value = '';
        });
    }

    // 1. Quill Editor
    const editor = document.getElementById("quill-editor");
    const input = document.getElementById("content-input");
    if (editor && input) {
        const quill = new Quill(editor, {
            theme: "snow",
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ["bold", "italic", "underline", "strike"],
                    [{ list: "ordered" }, { list: "bullet" }],
                    ["link", "image"],
                    ["clean"]
                ]
            }
        });
        quill.root.innerHTML = input.value || "";
        const form = input.closest("form");
        form.addEventListener("submit", function () {
            input.value = quill.root.innerHTML;
        });

        // 调用统一媒体库
        const toolbar = quill.getModule("toolbar");
        toolbar.addHandler("image", function () {
            openMediaLibrary(function(url) {
                            const range = quill.getSelection(true);
                quill.insertEmbed(range ? range.index : 0, "image", url);
            });
        });
    }

    // 2. Product Image Preview
    const imageInput = document.querySelector("input[name='images[]']");
    const preview = document.getElementById("product-image-preview");
    if (imageInput && preview) {
        imageInput.addEventListener("change", function () {
            preview.innerHTML = "";
            Array.from(imageInput.files).slice(0, 6).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement("div");
                    div.className = "column is-3";
                    div.innerHTML = `<figure class="image"><img src="${e.target.result}"></figure>`;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // 3. Price Tiers
    const addTierBtn = document.getElementById("add-price-tier");
    const tierWrap = document.getElementById("price-tier-wrap");
    if (addTierBtn && tierWrap) {
        addTierBtn.addEventListener("click", function () {
            const row = document.createElement("div");
            row.className = "columns price-tier-row";
            row.innerHTML = `
                <div class="column"><div class="field"><label class="label is-size-7">最小数量</label><div class="control"><input class="input" name="price_min[]" type="number" min="1" required></div></div></div>
                <div class="column"><div class="field"><label class="label is-size-7">最大数量</label><div class="control"><input class="input" name="price_max[]" type="number" min="1" placeholder="可空"></div></div></div>
                <div class="column"><div class="field"><label class="label is-size-7">单价</label><div class="control"><input class="input" name="price_value[]" type="number" min="0" step="0.01" required></div></div></div>
                <div class="column"><div class="field"><label class="label is-size-7">货币</label><div class="control"><input class="input" name="price_currency[]" value="USD" required></div></div></div>
                <div class="column is-narrow"><div class="field"><label class="label is-size-7">操作</label><div class="control"><button type="button" class="button is-light remove-price-tier">删除</button></div></div></div>
            `;
            tierWrap.appendChild(row);
        });
        tierWrap.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-price-tier")) {
                e.target.closest(".price-tier-row").remove();
            }
        });
    }
});
</script>
</body>
</html>
