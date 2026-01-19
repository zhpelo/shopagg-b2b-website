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
                    <a class="navbar-item <?= $active_group === 'catalog' ? 'is-active' : '' ?>" href="/admin/products">
                        <span class="icon mr-1"><i class="fas fa-box"></i></span>产品中心
                    </a>
                    <a class="navbar-item <?= $active_group === 'cases' ? 'is-active' : '' ?>" href="/admin/cases">
                        <span class="icon mr-1"><i class="fas fa-briefcase"></i></span>案例展示
                    </a>
                    <a class="navbar-item <?= $active_group === 'blog' ? 'is-active' : '' ?>" href="/admin/posts">
                        <span class="icon mr-1"><i class="fas fa-pen-nib"></i></span>内容管理
                    </a>
                    <a class="navbar-item <?= $active_group === 'inbox' ? 'is-active' : '' ?>" href="/admin/messages">
                        <span class="icon mr-1"><i class="fas fa-envelope"></i></span>收件箱
                    </a>
                </div>

                <div class="navbar-end">
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link">
                            <span class="icon mr-1"><i class="fas fa-user-circle"></i></span>
                            <?= h($_SESSION['admin_user'] ?? 'Admin') ?>
                        </a>
                        <div class="navbar-dropdown is-right">
                            <a class="navbar-item" href="/admin/settings">
                                <span class="icon mr-1"><i class="fas fa-cog"></i></span>系统设置
                            </a>
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

<footer class="footer has-background-white border-top py-5">
    <div class="container has-text-centered">
        <p class="is-size-7 has-text-grey">
            &copy; <?= date('Y') ?> SHOPAGG B2B Management Platform. Powered by <a href="https://www.shopagg.com" target="_blank">SHOPAGG</a>.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
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

        // AJAX Image for Quill
        const toolbar = quill.getModule("toolbar");
        toolbar.addHandler("image", function () {
            const inputFile = document.createElement("input");
            inputFile.type = "file";
            inputFile.accept = "image/*";
            inputFile.click();
            inputFile.addEventListener("change", function () {
                const file = inputFile.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append("image", file);
                formData.append("csrf", "<?= csrf_token() ?>");
                fetch("/admin/upload-image", { method: "POST", body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.url) {
                            const range = quill.getSelection(true);
                            quill.insertEmbed(range ? range.index : 0, "image", data.url);
                        } else {
                            alert(data.error || "上传失败");
                        }
                    });
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
