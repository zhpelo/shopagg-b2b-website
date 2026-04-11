<!DOCTYPE html>
<html data-app-base-path="<?= h(base_path()) ?>" data-admin-csrf="<?= h(csrf_token()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <script src="<?= url('/assets/admin/base.js') ?>"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.css">
    <link rel="stylesheet" href="<?= url('/assets/admin/rich-content.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/admin/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>

<body class="<?= ($showNav ?? true) ? 'min-h-screen bg-slate-100 text-slate-800' : 'login-page' ?>">
    <?php if ($showNav ?? true):
        $current_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $basePath = base_path();
        if ($basePath !== '' && strpos($current_path, $basePath) === 0) {
            $current_path = substr($current_path, strlen($basePath)) ?: '/';
        }
        $active_group = '';
        if (str_starts_with($current_path, '/admin/products') || str_starts_with($current_path, '/admin/product-categories')) $active_group = 'catalog';
        elseif (str_starts_with($current_path, '/admin/posts') || str_starts_with($current_path, '/admin/post-categories') || str_starts_with($current_path, '/admin/cases') || str_starts_with($current_path, '/admin/pages') || str_starts_with($current_path, '/admin/media')) $active_group = 'content';
        elseif (str_starts_with($current_path, '/admin/messages') || str_starts_with($current_path, '/admin/inquiries')) $active_group = 'inbox';
        elseif (str_starts_with($current_path, '/admin/staff')) $active_group = 'staff';
        elseif (str_starts_with($current_path, '/admin/settings')) $active_group = 'settings';
        elseif (str_starts_with($current_path, '/admin/appearance')) $active_group = 'appearance';

        $user_role = $_SESSION['admin_role'] ?? 'staff';
        $user_perms = $_SESSION['admin_permissions'] ?? [];

        $can_access = function ($perm) use ($user_role, $user_perms) {
            return $user_role === 'admin' || in_array($perm, $user_perms);
        };
        $content_nav_url = $can_access('blog') ? '/admin/posts' : '/admin/cases';
    ?>
        <!-- 第一级主导航 -->
        <nav class="admin-navbar border-b border-slate-200 bg-white/95 backdrop-blur" role="navigation" aria-label="main navigation">
            <div class="container flex items-center justify-between gap-6 py-4">
                <div class="flex items-center gap-3">
                    <a class="logo-link inline-flex items-center rounded-xl p-2 transition" href="<?= url('/admin') ?>">
                        <img src="https://www.shopagg.com/wp-content/uploads/2024/12/shopagg-logo-b.png" alt="logo" class="h-9 max-h-9">
                    </a>
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 lg:hidden" aria-label="menu" aria-expanded="false" data-nav-toggle data-target="adminNavbar">
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                    </button>
                </div>

                <div id="adminNavbar" class="hidden w-full flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-3 shadow-xl lg:flex lg:w-auto lg:flex-1 lg:flex-row lg:items-center lg:justify-between lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none" data-nav-menu>
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:gap-2">
                        <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-home"></i></span>仪表盘
                        </a>
                        <?php if ($can_access('inbox')): ?>
                            <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'inbox' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/messages') ?>">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-envelope"></i></span>收件箱
                            </a>
                        <?php endif; ?>
                        <?php if ($can_access('products')): ?>
                            <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'catalog' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/products') ?>">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-box"></i></span>产品中心
                            </a>
                        <?php endif; ?>
                        <?php if ($can_access('blog') || $can_access('cases')): ?>
                            <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'content' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url($content_nav_url) ?>">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-pen-nib"></i></span>内容管理
                            </a>
                        <?php endif; ?>

                        <?php if ($user_role === 'admin'): ?>
                            <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'staff' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/staff') ?>">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-users"></i></span>员工管理
                            </a>
                            
                        <?php endif; ?>

                        <?php if ($user_role === 'admin'): ?>
                            <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'appearance' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/appearance/sliders') ?>">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-paint-brush"></i></span>外观区块
                            </a>
                        <?php endif; ?>

                        <?php if ($can_access('settings')): ?>
                            <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'settings' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/settings-general') ?>">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-cog"></i></span>系统设置
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center justify-end">
                        <div class="group relative">
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-user-circle"></i></span>
                                <?= h($_SESSION['admin_display_name'] ?? $_SESSION['admin_user'] ?? 'Admin') ?>
                                <span class="text-xs text-slate-400"><i class="fas fa-chevron-down"></i></span>
                            </button>
                            <div class="invisible absolute right-0 top-full z-50 min-w-[13rem] pt-2 opacity-0 transition duration-150 group-hover:visible group-hover:opacity-100">
                            <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                <a class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900" href="<?= url('/admin/profile') ?>">
                                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-id-card"></i></span>个人资料
                                </a>
                                <a class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900" href="<?= url('/') ?>" target="_blank">
                                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-external-link-alt"></i></span>访问网站
                                </a>
                                <div class="my-2 h-px bg-slate-200"></div>
                                <a class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50" href="<?= url('/admin/logout') ?>">
                                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-sign-out-alt"></i></span>退出登录
                                </a>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- 第二级二级导航 -->
        <?php if ($active_group): ?>
            <nav class="admin-subnav border-b border-slate-200 bg-white">
                <div class="container admin-subnav-container flex items-center overflow-x-auto whitespace-nowrap">
                    <?php if ($active_group === 'catalog'): ?>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/products' || str_contains($current_path, '/admin/products/') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/products') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-list"></i></span>产品列表
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/admin/product-categories') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/product-categories') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-folder"></i></span>产品分类
                        </a>
                    <?php elseif ($active_group === 'content'): ?>
                        <?php if ($can_access('blog')): ?>
                            <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/posts' || str_contains($current_path, '/admin/posts/') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/posts') ?>">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-newspaper"></i></span>文章管理
                            </a>
                            <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/pages' || str_contains($current_path, '/admin/pages/') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/pages') ?>">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-file-lines"></i></span>页面管理
                            </a>
                        <?php endif; ?>
                        <?php if ($can_access('cases')): ?>
                            <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/cases' || str_contains($current_path, '/admin/cases/') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/cases') ?>">
                                <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-briefcase"></i></span>案例管理
                            </a>
                        <?php endif; ?>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/admin/media') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/media') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-photo-video"></i></span>媒体库
                        </a>
                    <?php elseif ($active_group === 'inbox'): ?>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/messages') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/messages') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-comment-dots"></i></span>联系留言
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/inquiries') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/inquiries') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-file-invoice"></i></span>询单管理
                        </a>
                    <?php elseif ($active_group === 'staff'): ?>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/staff' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/staff') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-list"></i></span>员工列表
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/create') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/staff/create') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-user-plus"></i></span>新增员工
                        </a>
                    <?php elseif ($active_group === 'appearance'): ?>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_starts_with($current_path, '/admin/appearance/block') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/appearance/blocks') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-puzzle-piece"></i></span>模板区块
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_starts_with($current_path, '/admin/appearance/menus') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/appearance/menus') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-bars"></i></span>菜单管理
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_starts_with($current_path, '/admin/appearance/sliders') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/appearance/sliders') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-images"></i></span>轮播图
                        </a>
                    <?php elseif ($active_group === 'settings'): ?>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/settings-general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/settings-general') ?>">
                            <span class="inline-flex h-4 w-4 items-center justify-center"><i class="fas fa-cog text-xs"></i></span>基础设置
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/settings-company' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/settings-company') ?>">
                            <span class="inline-flex h-4 w-4 items-center justify-center"><i class="fas fa-building text-xs"></i></span>公司简介
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/settings-trade' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/settings-trade') ?>">
                            <span class="inline-flex h-4 w-4 items-center justify-center"><i class="fas fa-globe text-xs"></i></span>贸易能力
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/settings-media' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/settings-media') ?>">
                            <span class="inline-flex h-4 w-4 items-center justify-center"><i class="fas fa-images text-xs"></i></span>公司展示
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/settings-contact' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/settings-contact') ?>">
                            <span class="inline-flex h-4 w-4 items-center justify-center"><i class="fas fa-phone text-xs"></i></span>联系方式
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/settings-translate' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/settings-translate') ?>">
                            <span class="inline-flex h-4 w-4 items-center justify-center"><i class="fas fa-language text-xs"></i></span>翻译设置
                        </a>
                        <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/settings-custom' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/settings-custom') ?>">
                            <span class="inline-flex h-4 w-4 items-center justify-center"><i class="fas fa-code text-xs"></i></span>自定义代码
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/settings-updater' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/settings-updater') ?>">
                            <span class="inline-flex h-5 w-5 items-center justify-center mr-1"><i class="fas fa-sync-alt"></i></span>程序更新
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

    <div class="admin-main">
        <section class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="container">
                <?= $content ?>
            </div>
        </section>
    </div>

    <!-- 统一媒体库模态框 -->
    <div class="fixed inset-0 z-[200000] hidden items-center justify-center p-4" id="media-library-modal">
        <div class="absolute inset-0 bg-slate-950/60" data-media-modal-close></div>
        <div class="media-library-modal-card relative z-10 flex max-h-[calc(100vh-2rem)] w-full flex-col overflow-hidden rounded-[20px] bg-white shadow-2xl">
            <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-gradient-to-b from-slate-50 to-white px-5 py-2">
                <p class="flex items-center gap-2 text-lg font-bold text-slate-900">
                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-photo-video"></i></span>
                    媒体库
                </p>
                <button type="button" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700" aria-label="关闭媒体库" data-media-modal-close>
                    <i class="fas fa-times text-sm"></i>
                </button>
            </header>
            <section class="media-library-body flex-1 min-h-0 overflow-hidden">
                <div class="media-library-window">
                    <div class="media-library-window-nav">
                        <div class="flex items-center gap-2">
                            <button type="button" class="inline-flex h-6 w-6 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" id="media-library-back-btn" disabled>
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-arrow-left"></i></span>
                            </button>
                            <button type="button" class="inline-flex h-6 w-6 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" id="media-library-forward-btn" disabled>
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-arrow-right"></i></span>
                            </button>
                            <button type="button" class="inline-flex h-6 w-6 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" id="media-library-up-btn">
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-level-up-alt"></i></span>
                            </button>
                        </div>
                        <div class="media-library-toolbar-main">
                            <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500" aria-label="breadcrumbs" id="media-library-breadcrumbs"></nav>
                            <p class="text-xs text-slate-500" id="media-library-directory-label">/uploads</p>
                        </div>
                    </div>
                    <div class="media-library-toolbar">
                        <div class="media-library-toolbar-buttons flex flex-wrap items-center justify-end gap-3">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">
                                <input class="hidden" type="file" id="media-upload-input" multiple accept="image/*,video/mp4,video/webm,video/ogg,video/quicktime">
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-upload"></i></span>
                                <span>上传文件</span>
                            </label>
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50" id="media-library-new-folder-btn">
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-folder-plus"></i></span>
                                <span>新建文件夹</span>
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50" id="media-library-bulk-delete-btn" disabled>
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-trash"></i></span>
                                <span id="media-library-bulk-delete-label">删除</span>
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-400" disabled>
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-i-cursor"></i></span>
                                <span>重命名</span>
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50" id="media-library-refresh-btn">
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-sync-alt"></i></span>
                                <span>刷新</span>
                            </button>
                        </div>
                        <div class="media-library-filter-bar flex flex-1 flex-wrap items-stretch gap-3">
                            <input class="min-w-[240px] flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="text" id="media-library-search" placeholder="搜索原始文件名、目录或存储文件名">
                            <select id="media-library-type-filter" class="min-w-[120px] rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                                <option value="image">图片</option>
                                <option value="all">全部媒体</option>
                                <option value="video">视频</option>
                            </select>
                            <select id="media-library-sort-filter" class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                                <option value="date_desc">最新上传</option>
                                <option value="date_asc">最早上传</option>
                                <option value="name_asc">文件名 A-Z</option>
                                <option value="name_desc">文件名 Z-A</option>
                                <option value="type_asc">类型升序</option>
                                <option value="type_desc">类型降序</option>
                            </select>
                        </div>
                    </div>
                    <div id="upload-progress-container" class="mb-4 hidden">
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                            <span id="upload-status-text">正在上传...</span>
                            <span id="upload-progress-percent">0%</span>
                        </div>
                        <progress id="overall-progress" class="h-2 w-full overflow-hidden rounded-full" value="0" max="100">0%</progress>
                    </div>
                    <div id="media-library-status" class="media-library-status hidden"></div>
                    <div class="media-library-window-main">
                        <aside class="media-library-tree-panel">
                            <div id="media-library-tree"></div>
                        </aside>
                        <section class="media-library-files-panel">
                            <div class="media-library-file-toolbar">
                                <div class="media-library-view-switch flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-1">
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition" id="media-library-view-list-btn">
                                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-list"></i></span>
                                    </button>
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-white hover:text-slate-700" id="media-library-view-tiles-btn">
                                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-th-large"></i></span>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500" id="media-library-current-mode-label">列表视图</p>
                            </div>
                            <div class="media-library-file-header" id="media-library-file-header">
                                <div class="media-library-file-col check">
                                    <input type="checkbox" id="media-library-toggle-all">
                                </div>
                                <div class="media-library-file-col name">名称</div>
                                <div class="media-library-file-col type">类型</div>
                                <div class="media-library-file-col size">大小</div>
                                <div class="media-library-file-col date">修改时间</div>
                            </div>
                            <div id="media-library-list" class="media-library-file-list"></div>
                        </section>
                        <aside class="media-library-preview-panel">
                            <div class="media-library-pane-title">预览 / 属性</div>
                            <div class="media-library-details" id="media-library-details-empty">
                                <div class="media-library-details-placeholder">
                                    <span class="inline-flex h-8 w-8 items-center justify-center text-2xl"><i class="fas fa-image"></i></span>
                                    <p class="mt-3">选择一个媒体文件后，这里会显示预览和详情。</p>
                                </div>
                            </div>
                            <div class="media-library-details hidden" id="media-library-details-panel">
                                <div class="media-library-details-preview" id="media-library-details-preview"></div>
                                <div class="media-library-detail-row">
                                    <span>标题</span>
                                    <strong id="media-details-title"></strong>
                                </div>
                                <div class="media-library-detail-row">
                                    <span>原始文件名</span>
                                    <strong id="media-details-original"></strong>
                                </div>
                                <div class="media-library-detail-row">
                                    <span>存储文件名</span>
                                    <strong id="media-details-storage"></strong>
                                </div>
                                <div class="media-library-detail-row">
                                    <span>媒体类型</span>
                                    <strong id="media-details-type"></strong>
                                </div>
                                <div class="media-library-detail-row">
                                    <span>所在目录</span>
                                    <strong id="media-details-directory"></strong>
                                </div>
                                <div class="media-library-detail-row">
                                    <span>尺寸/大小</span>
                                    <strong id="media-details-meta"></strong>
                                </div>
                                <div class="media-library-detail-row">
                                    <span>上传时间</span>
                                    <strong id="media-details-date"></strong>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400" for="media-details-path">路径</label>
                                    <div class="flex items-center gap-2">
                                        <input class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none" type="text" readonly id="media-details-path">
                                        <button type="button" class="inline-flex items-center justify-center rounded-xl bg-sky-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-sky-600" id="media-details-copy-btn">复制</button>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                    <div class="media-library-window-statusbar">
                        <span id="media-library-selected-count">已选择 0 个文件</span>
                        <span id="media-library-current-count">0 个项目</span>
                        <span id="media-library-current-size">总大小 0 B</span>
                    </div>
                </div>
            </section>
            <footer class="media-library-footer flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-2">
                <button type="button" class="hidden items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700" id="confirm-media-selection">确认选择</button>
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-media-modal-close>取消</button>
            </footer>
        </div>
    </div>

    <footer class="footer bg-white py-6 border-t border-gray-200">
        <div class="container text-center">
            <p class="text-md text-slate-500">
                &copy; <?= date('Y') ?> SHOPAGG B2B 企业官网系统. Powered by <a href="https://www.shopagg.com" target="_blank" class="text-indigo-500 hover:text-indigo-600">SHOPAGG</a>.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.js"></script>
</body>

</html>
