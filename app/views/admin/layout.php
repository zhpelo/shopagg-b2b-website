<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
    <link rel="stylesheet" href="/app/views/admin/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="<?= ($showNav ?? true) ? '' : 'login-page' ?>">
    <?php if ($showNav ?? true): 
    $current_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $basePath = base_path();
    if ($basePath !== '' && strpos($current_path, $basePath) === 0) {
        $current_path = substr($current_path, strlen($basePath)) ?: '/';
    }
    $active_group = '';
    if (str_starts_with($current_path, '/admin/products') || str_starts_with($current_path, '/admin/product-categories')) $active_group = 'catalog';
    elseif (str_starts_with($current_path, '/admin/posts') || str_starts_with($current_path, '/admin/post-categories') || str_starts_with($current_path, '/admin/cases') || str_starts_with($current_path, '/admin/media')) $active_group = 'content';
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
                <a class="logo-link" href="<?= url('/admin') ?>" style="padding: 0.5rem;">
                    <img src="https://www.shopagg.com/wp-content/uploads/2024/12/shopagg-logo-b.png" alt="logo" style="height: 36px; max-height: 36px;">
                </a>
                <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="adminNavbar">
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </a>
            </div>

            <div id="adminNavbar" class="navbar-menu ml-6">
                <div class="navbar-start">
                    <a class="navbar-item <?= $current_path === '/admin' ? 'is-active' : '' ?>" href="<?= url('/admin') ?>">
                        <span class="icon mr-1"><i class="fas fa-home"></i></span>仪表盘
                    </a>
                    <?php if ($can_access('inbox')): ?>
                    <a class="navbar-item <?= $active_group === 'inbox' ? 'is-active' : '' ?>" href="<?= url('/admin/messages') ?>">
                        <span class="icon mr-1"><i class="fas fa-envelope"></i></span>收件箱
                    </a>
                    <?php endif; ?>
                    <?php if ($can_access('products')): ?>
                    <a class="navbar-item <?= $active_group === 'catalog' ? 'is-active' : '' ?>" href="<?= url('/admin/products') ?>">
                        <span class="icon mr-1"><i class="fas fa-box"></i></span>产品中心
                    </a>
                    <?php endif; ?>
                    <?php if ($can_access('blog') || $can_access('cases')): ?>
                    <a class="navbar-item <?= $active_group === 'content' ? 'is-active' : '' ?>" href="<?= url('/admin/posts') ?>">
                        <span class="icon mr-1"><i class="fas fa-pen-nib"></i></span>内容管理
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($user_role === 'admin'): ?>
                    <a class="navbar-item <?= $active_group === 'staff' ? 'is-active' : '' ?>" href="<?= url('/admin/staff') ?>">
                        <span class="icon mr-1"><i class="fas fa-users"></i></span>员工管理
                    </a>
                    <?php endif; ?>

                    <?php if ($can_access('settings')): ?>
                    <a class="navbar-item <?= $current_path === '/admin/settings' ? 'is-active' : '' ?>" href="<?= url('/admin/settings') ?>">
                        <span class="icon mr-1"><i class="fas fa-cog"></i></span>系统设置
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
                            <a class="navbar-item" href="<?= url('/admin/profile') ?>">
                                <span class="icon mr-2"><i class="fas fa-id-card"></i></span>个人资料
                            </a>
                            <a class="navbar-item" href="<?= url('/') ?>" target="_blank">
                                <span class="icon mr-2"><i class="fas fa-external-link-alt"></i></span>访问网站
                            </a>
                            <hr class="navbar-divider">
                            <a class="navbar-item has-text-danger" href="<?= url('/admin/logout') ?>">
                                <span class="icon mr-2"><i class="fas fa-sign-out-alt"></i></span>退出登录
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
                <a class="navbar-item <?= $current_path === '/admin/products' || str_contains($current_path, '/admin/products/') ? 'is-active' : '' ?>" href="<?= url('/admin/products') ?>">
                    <span class="icon mr-1"><i class="fas fa-list"></i></span>产品列表
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/admin/product-categories') ? 'is-active' : '' ?>" href="<?= url('/admin/product-categories') ?>">
                    <span class="icon mr-1"><i class="fas fa-folder"></i></span>产品分类
                </a>
            <?php elseif ($active_group === 'content'): ?>
                <a class="navbar-item <?= $current_path === '/admin/posts' || str_contains($current_path, '/admin/posts/') ? 'is-active' : '' ?>" href="<?= url('/admin/posts') ?>">
                    <span class="icon mr-1"><i class="fas fa-newspaper"></i></span>文章管理
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/admin/post-categories') ? 'is-active' : '' ?>" href="<?= url('/admin/post-categories') ?>">
                    <span class="icon mr-1"><i class="fas fa-folder"></i></span>文章分类
                </a>
                <a class="navbar-item <?= $current_path === '/admin/cases' || str_contains($current_path, '/admin/cases/') ? 'is-active' : '' ?>" href="<?= url('/admin/cases') ?>">
                    <span class="icon mr-1"><i class="fas fa-briefcase"></i></span>案例展示
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/admin/media') ? 'is-active' : '' ?>" href="<?= url('/admin/media') ?>">
                    <span class="icon mr-1"><i class="fas fa-photo-video"></i></span>媒体库
                </a>
            <?php elseif ($active_group === 'inbox'): ?>
                <a class="navbar-item <?= str_contains($current_path, '/messages') ? 'is-active' : '' ?>" href="<?= url('/admin/messages') ?>">
                    <span class="icon mr-1"><i class="fas fa-comment-dots"></i></span>联系留言
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/inquiries') ? 'is-active' : '' ?>" href="<?= url('/admin/inquiries') ?>">
                    <span class="icon mr-1"><i class="fas fa-file-invoice"></i></span>询单管理
                </a>
            <?php elseif ($active_group === 'staff'): ?>
                <a class="navbar-item <?= $current_path === '/admin/staff' ? 'is-active' : '' ?>" href="<?= url('/admin/staff') ?>">
                    <span class="icon mr-1"><i class="fas fa-list"></i></span>员工列表
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/create') ? 'is-active' : '' ?>" href="<?= url('/admin/staff/create') ?>">
                    <span class="icon mr-1"><i class="fas fa-user-plus"></i></span>新增员工
                </a>
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
            <p class="modal-card-title">
                <span class="icon mr-2"><i class="fas fa-images"></i></span>
                选择媒体文件
            </p>
            <div class="field mb-0 mr-3">
                <div class="control">
                    <div class="file is-info is-small">
                        <label class="file-label">
                            <input class="file-input" type="file" id="media-upload-input" multiple accept="image/*">
                            <span class="file-cta" style="border-radius: 8px;">
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
                <progress id="overall-progress" class="progress is-info is-small" value="0" max="100" style="border-radius: 50px;">0%</progress>
            </div>
            <div id="media-library-list" class="columns is-multiline is-mobile">
                <!-- 动态加载图片列表 -->
            </div>
        </section>
        <footer class="modal-card-head" style="justify-content: flex-end; border-top: 1px solid #e5e7eb; border-bottom: none;">
            <button type="button" class="button is-link" id="confirm-media-selection" style="display:none">确认选择</button>
            <button type="button" class="button is-light close-modal ml-2">取消</button>
        </footer>
    </div>
</div>

<footer class="footer has-background-white py-4" style="border-top: 1px solid #e5e7eb;">
    <div class="container has-text-centered">
        <p class="is-size-7 has-text-grey">
            &copy; <?= date('Y') ?> SHOPAGG B2B Management Platform. Powered by <a href="https://www.shopagg.com" target="_blank" style="color: #667eea;">SHOPAGG</a>.
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
    container.innerHTML = '<div class="column is-12 has-text-centered p-6"><span class="icon is-large has-text-grey-light"><i class="fas fa-spinner fa-pulse fa-2x"></i></span><p class="mt-3 has-text-grey">正在加载...</p></div>';
    try {
        const res = await fetch('/admin/media-library');
        const files = await res.json();
        container.innerHTML = '';
        if (files.length === 0) {
            container.innerHTML = '<div class="column is-12 has-text-centered p-6"><span class="icon is-large has-text-grey-light"><i class="fas fa-images fa-2x"></i></span><p class="mt-3 has-text-grey">暂无媒体文件</p></div>';
            return;
        }
        files.forEach(file => {
            const col = document.createElement('div');
            col.className = 'column is-2-desktop is-3-tablet is-4-mobile';
            col.innerHTML = `
                <div class="card media-select-item" data-url="${file}" style="cursor: pointer; border: 4px solid transparent; border-radius: 12px; overflow:hidden; transition: all 0.2s;">
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
        container.innerHTML = '<div class="column is-12 has-text-centered p-6"><span class="icon is-large has-text-danger"><i class="fas fa-exclamation-circle fa-2x"></i></span><p class="mt-3 has-text-danger">加载失败</p></div>';
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
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['blockquote', 'code-block'],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });
        quill.root.innerHTML = input.value || "";
        const form = input.closest("form");
        form.addEventListener("submit", function () {
            input.value = quill.root.innerHTML;
        });

        // 调用统一媒体库（支持多选图片）
        const toolbar = quill.getModule("toolbar");
        toolbar.addHandler("image", function () {
            openMediaLibrary(function(urls) {
                            const range = quill.getSelection(true);
                let index = range ? range.index : 0;
                urls.forEach(url => {
                    quill.insertEmbed(index, "image", url);
                    index += 1;
            });
            }, true);
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
