<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title><?= h($title) ?></title>
 <script>
 tailwind = window.tailwind || {};
 tailwind.config = {
 theme: {
 extend: {
 fontFamily: {
 sans: ['-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', '"PingFang SC"', '"Hiragino Sans GB"', '"Microsoft YaHei"', '"Helvetica Neue"', 'sans-serif']
 },
 colors: {
 admin: {
 primary: '#667eea',
 primaryDark: '#4c51bf',
 slate: '#0f172a'
 }
 },
 boxShadow: {
 admin: '0 12px 40px rgba(15, 23, 42, 0.12)'
 }
 }
 }
 };
 </script>
 <script src="https://cdn.tailwindcss.com"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.css">
 <link rel="stylesheet" href="<?= url('/app/views/admin/style.css') ?>">
 <style>
 #editor-wrapper {
 border: 1px solid #e5e7eb;
 border-radius: 8px;
 background: #fff;
 overflow: hidden;
 }
 #editor-wrapper .jodit-container:not(.jodit_inline) {
 border: none;
 border-radius: inherit;
 }
 #editor-wrapper .jodit-toolbar__box:not(:empty) {
 border-bottom: 1px solid #e5e7eb;
 background: #fafaf9;
 }
 #editor-wrapper .jodit-workplace {
 min-height: 300px;
 }
 #editor-wrapper .jodit-wysiwyg,
 #editor-wrapper .jodit-source__mirror {
 font-size: 16px;
 line-height: 1.8;
 }
 #editor-wrapper .jodit-status-bar {
 border-top: 1px solid #e5e7eb;
 }
 .jodit_fullsize,
 .jodit-container.jodit_fullsize {
 position: fixed !important;
 inset: 0 !important;
 width: 100vw !important;
 height: 100vh !important;
 max-width: none !important;
 z-index: 100000 !important;
 border-radius: 0 !important;
 background: #fff !important;
 }
 .jodit_fullsize {
 display: flex !important;
 flex-direction: column !important;
 }
 .jodit_fullsize .jodit-workplace {
 flex: 1 1 auto;
 min-height: 0;
 }
 .jodit_fullsize .jodit-wysiwyg,
 .jodit_fullsize .jodit-source__mirror {
 min-height: 100% !important;
 }
 .editor-fullscreen-active {
 overflow: hidden !important;
 }
 .editor-fullscreen-host {
 transform: none !important;
 animation: none !important;
 overflow: visible !important;
 opacity: 1 !important;
 }
 html.jodit_fullsize-box_true,
 body.jodit_fullsize-box_true {
 overflow: hidden;
 }
 </style>
 <script>window.APP_BASE_PATH = '<?= base_path() ?>';</script>
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
 <nav class="admin-navbar border-b border-slate-200 bg-white/95 backdrop-blur" role="navigation" aria-label="main navigation">
 <div class="container flex items-center justify-between gap-6 py-4">
 <div class="flex items-center gap-3">
 <a class="logo-link inline-flex items-center rounded-xl p-2 transition hover:bg-slate-100" href="<?= url('/admin') ?>">
 <img src="https://www.shopagg.com/wp-content/uploads/2024/12/shopagg-logo-b.png" alt="logo" style="height: 36px; max-height: 36px;">
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
 <span class="icon mr-1"><i class="fas fa-home"></i></span>仪表盘
 </a>
 <?php if ($can_access('inbox')): ?>
 <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'inbox' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/messages') ?>">
 <span class="icon mr-1"><i class="fas fa-envelope"></i></span>收件箱
 </a>
 <?php endif; ?>
 <?php if ($can_access('products')): ?>
 <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'catalog' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/products') ?>">
 <span class="icon mr-1"><i class="fas fa-box"></i></span>产品中心
 </a>
 <?php endif; ?>
 <?php if ($can_access('blog') || $can_access('cases')): ?>
 <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'content' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/posts') ?>">
 <span class="icon mr-1"><i class="fas fa-pen-nib"></i></span>内容管理
 </a>
 <?php endif; ?>
 
 <?php if ($user_role === 'admin'): ?>
 <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $active_group === 'staff' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/staff') ?>">
 <span class="icon mr-1"><i class="fas fa-users"></i></span>员工管理
 </a>
 <?php endif; ?>

 <?php if ($can_access('settings')): ?>
 <a class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/settings' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>" href="<?= url('/admin/settings') ?>">
 <span class="icon mr-1"><i class="fas fa-cog"></i></span>系统设置
 </a>
 <?php endif; ?>
 </div>

 <div class="flex items-center justify-end">
 <div class="group relative">
 <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
 <span class="icon mr-1"><i class="fas fa-user-circle"></i></span>
 <?= h($_SESSION['admin_display_name'] ?? $_SESSION['admin_user'] ?? 'Admin') ?>
 <span class="text-xs text-slate-400"><i class="fas fa-chevron-down"></i></span>
 </button>
 <div class="invisible absolute right-0 top-full z-50 mt-2 min-w-[13rem] translate-y-1 rounded-2xl border border-slate-200 bg-white p-2 opacity-0 shadow-xl transition duration-150 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
 <a class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900" href="<?= url('/admin/profile') ?>">
 <span class="icon mr-2"><i class="fas fa-id-card"></i></span>个人资料
 </a>
 <a class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900" href="<?= url('/') ?>" target="_blank">
 <span class="icon mr-2"><i class="fas fa-external-link-alt"></i></span>访问网站
 </a>
 <div class="my-2 h-px bg-slate-200"></div>
 <a class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50" href="<?= url('/admin/logout') ?>">
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
 <nav class="admin-subnav border-b border-slate-200 bg-white">
 <div class="container admin-subnav-container flex items-center overflow-x-auto whitespace-nowrap">
 <?php if ($active_group === 'catalog'): ?>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/products' || str_contains($current_path, '/admin/products/') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/products') ?>">
 <span class="icon mr-1"><i class="fas fa-list"></i></span>产品列表
 </a>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/admin/product-categories') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/product-categories') ?>">
 <span class="icon mr-1"><i class="fas fa-folder"></i></span>产品分类
 </a>
 <?php elseif ($active_group === 'content'): ?>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/posts' || str_contains($current_path, '/admin/posts/') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/posts') ?>">
 <span class="icon mr-1"><i class="fas fa-newspaper"></i></span>文章管理
 </a>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/admin/post-categories') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/post-categories') ?>">
 <span class="icon mr-1"><i class="fas fa-folder"></i></span>文章分类
 </a>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/cases' || str_contains($current_path, '/admin/cases/') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/cases') ?>">
 <span class="icon mr-1"><i class="fas fa-briefcase"></i></span>案例展示
 </a>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/admin/media') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/media') ?>">
 <span class="icon mr-1"><i class="fas fa-photo-video"></i></span>媒体库
 </a>
 <?php elseif ($active_group === 'inbox'): ?>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/messages') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/messages') ?>">
 <span class="icon mr-1"><i class="fas fa-comment-dots"></i></span>联系留言
 </a>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/inquiries') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/inquiries') ?>">
 <span class="icon mr-1"><i class="fas fa-file-invoice"></i></span>询单管理
 </a>
 <?php elseif ($active_group === 'staff'): ?>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= $current_path === '/admin/staff' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/staff') ?>">
 <span class="icon mr-1"><i class="fas fa-list"></i></span>员工列表
 </a>
 <a class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition <?= str_contains($current_path, '/create') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-900' ?>" href="<?= url('/admin/staff/create') ?>">
 <span class="icon mr-1"><i class="fas fa-user-plus"></i></span>新增员工
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
<div class="fixed inset-0 z-[80] hidden items-center justify-center p-4" id="media-library-modal">
 <div class="absolute inset-0 bg-slate-950/60" data-media-modal-close></div>
 <div class="media-library-modal-card relative z-10 flex max-h-[calc(100vh-2rem)] w-full flex-col overflow-hidden rounded-[20px] bg-white shadow-2xl">
 <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-gradient-to-b from-slate-50 to-white px-5 py-4">
 <p class="flex items-center gap-2 text-lg font-bold text-slate-900">
 <span class="icon mr-2"><i class="fas fa-photo-video"></i></span>
 媒体库
 </p>
 <button type="button" class="delete" aria-label="close" data-media-modal-close></button>
 </header>
 <section class="media-library-body overflow-auto">
 <div class="media-library-window">
 <div class="media-library-window-nav">
 <div class="flex items-center gap-2">
 <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" id="media-library-back-btn" disabled>
 <span class="icon"><i class="fas fa-arrow-left"></i></span>
 </button>
 <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" id="media-library-forward-btn" disabled>
 <span class="icon"><i class="fas fa-arrow-right"></i></span>
 </button>
 <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" id="media-library-up-btn">
 <span class="icon"><i class="fas fa-level-up-alt"></i></span>
 </button>
 </div>
 <div class="media-library-toolbar-main">
 <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500" aria-label="breadcrumbs" id="media-library-breadcrumbs"></nav>
 <p class="mt-2 text-xs text-slate-500" id="media-library-directory-label">/uploads</p>
 </div>
 </div>
 <div class="media-library-toolbar">
 <div class="media-library-toolbar-buttons flex flex-wrap items-center justify-end gap-3">
 <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">
 <input class="hidden" type="file" id="media-upload-input" multiple accept="image/*,video/mp4,video/webm,video/ogg,video/quicktime">
 <span class="icon"><i class="fas fa-upload"></i></span>
 <span>上传文件</span>
 </label>
 <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50" id="media-library-new-folder-btn">
 <span class="icon"><i class="fas fa-folder-plus"></i></span>
 <span>新建文件夹</span>
 </button>
 <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50" id="media-library-bulk-delete-btn" disabled>
 <span class="icon"><i class="fas fa-trash"></i></span>
 <span id="media-library-bulk-delete-label">删除</span>
 </button>
 <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-400" disabled>
 <span class="icon"><i class="fas fa-i-cursor"></i></span>
 <span>重命名</span>
 </button>
 <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50" id="media-library-refresh-btn">
 <span class="icon"><i class="fas fa-sync-alt"></i></span>
 <span>刷新</span>
 </button>
 </div>
 <div class="media-library-filter-bar flex flex-1 flex-wrap items-stretch gap-3">
 <input class="min-w-[240px] flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="text" id="media-library-search" placeholder="搜索原始文件名、目录或存储文件名">
 <select id="media-library-type-filter" class="min-w-[120px] rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
 <option value="image">图片</option>
 <option value="all">全部媒体</option>
 <option value="video">视频</option>
 </select>
 <select id="media-library-sort-filter" class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
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
 <div class="media-library-pane-title">文件夹</div>
 <div id="media-library-tree"></div>
 </aside>
 <section class="media-library-files-panel">
 <div class="media-library-file-toolbar">
 <div class="media-library-view-switch flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-1">
 <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition" id="media-library-view-list-btn">
 <span class="icon"><i class="fas fa-list"></i></span>
 </button>
 <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-white hover:text-slate-700" id="media-library-view-tiles-btn">
 <span class="icon"><i class="fas fa-th-large"></i></span>
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
 <div class="media-library-file-col folder">目录</div>
 <div class="media-library-file-col size">大小</div>
 <div class="media-library-file-col date">修改时间</div>
 </div>
 <div id="media-library-list" class="media-library-file-list"></div>
 </section>
 <aside class="media-library-preview-panel">
 <div class="media-library-pane-title">预览 / 属性</div>
 <div class="media-library-details" id="media-library-details-empty">
 <div class="media-library-details-placeholder">
 <span class="icon is-large"><i class="fas fa-image"></i></span>
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
 <footer class="media-library-footer flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4">
 <button type="button" class="hidden items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700" id="confirm-media-selection">确认选择</button>
 <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-media-modal-close>取消</button>
 </footer>
 </div>
</div>

<footer class="footer bg-white py-4" style="border-top: 1px solid #e5e7eb;">
 <div class="container text-center">
 <p class="text-xs text-slate-500">
 &copy; <?= date('Y') ?> SHOPAGG B2B Management Platform. Powered by <a href="https://www.shopagg.com" target="_blank" style="color: #667eea;">SHOPAGG</a>.
 </p>
 </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.js"></script>
<script>
// 子目录部署：前端请求需带 base path
window.APP_BASE_PATH = <?= json_encode(base_path()) ?>;
window.ADMIN_CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
</script>
<script>
// 全局媒体库逻辑
const mediaLibraryState = {
 callback: null,
 multi: false,
 options: {
 type: 'image',
 returnObjects: false
 },
 directory: '',
 search: '',
 type: 'image',
 sort: 'date_desc',
 activeItem: null,
 currentStats: {
 item_count: 0,
 total_size_formatted: '0 B'
 },
 selectedItems: new Map(),
 view: 'list',
 expandedDirectories: new Set(['']),
 fetchTimer: null,
 lastPayload: null,
 history: [],
 historyIndex: -1
};

function rememberExpandedDirectory(directory) {
 mediaLibraryState.expandedDirectories.add('');
 if (!directory) {
 return;
 }

 const parts = String(directory).split('/').filter(Boolean);
 let current = '';
 parts.forEach((part) => {
 current = current ? `${current}/${part}` : part;
 mediaLibraryState.expandedDirectories.add(current);
 });
}

function openMediaLibrary(callback, multi = false, options = {}) {
 mediaLibraryState.callback = callback;
 mediaLibraryState.multi = multi;
 mediaLibraryState.options = {
 type: options.type || 'image',
 returnObjects: !!options.returnObjects
 };
 mediaLibraryState.directory = options.directory || '';
 mediaLibraryState.search = '';
 mediaLibraryState.type = options.type || 'image';
 mediaLibraryState.sort = options.sort || 'date_desc';
 mediaLibraryState.activeItem = null;
 mediaLibraryState.selectedItems = new Map();
 mediaLibraryState.currentStats = {
 item_count: 0,
 total_size_formatted: '0 B'
 };
 mediaLibraryState.view = options.view || 'list';
 mediaLibraryState.expandedDirectories = new Set(['']);
 mediaLibraryState.lastPayload = null;
 rememberExpandedDirectory(mediaLibraryState.directory);
 mediaLibraryState.history = [mediaLibraryState.directory];
 mediaLibraryState.historyIndex = 0;

 const modal = document.getElementById('media-library-modal');
 const confirmBtn = document.getElementById('confirm-media-selection');
 const searchInput = document.getElementById('media-library-search');
 const typeFilter = document.getElementById('media-library-type-filter');
 const sortFilter = document.getElementById('media-library-sort-filter');
 const confirmLabel = document.getElementById('confirm-media-selection');

 if (searchInput) {
 searchInput.value = '';
 }
 if (typeFilter) {
 typeFilter.value = mediaLibraryState.type;
 }
 if (sortFilter) {
 sortFilter.value = mediaLibraryState.sort;
 }

 if (confirmLabel) {
 confirmLabel.classList.remove('hidden');
 confirmLabel.classList.add('inline-flex');
 confirmLabel.disabled = true;
 confirmLabel.textContent = multi ? '插入所选' : '插入';
 }
 modal.classList.remove('hidden');
 modal.classList.add('flex');
 updateMediaLibraryNavButtons();
 updateMediaLibraryViewMode();
 fetchMediaLibrary();
}

function closeMediaLibraryModal() {
 const modal = document.getElementById('media-library-modal');
 if (modal) {
 modal.classList.remove('flex');
 modal.classList.add('hidden');
 }
}

function mediaLibraryApiUrl() {
 const params = new URLSearchParams();
 if (mediaLibraryState.directory) params.set('dir', mediaLibraryState.directory);
 if (mediaLibraryState.search) params.set('search', mediaLibraryState.search);
 if (mediaLibraryState.type) params.set('type', mediaLibraryState.type);
 if (mediaLibraryState.sort) params.set('sort', mediaLibraryState.sort);
 return `${window.APP_BASE_PATH || ''}/admin/media-library?${params.toString()}`;
}

function updateMediaLibraryNavButtons() {
 const backButton = document.getElementById('media-library-back-btn');
 const forwardButton = document.getElementById('media-library-forward-btn');
 const upButton = document.getElementById('media-library-up-btn');

 if (backButton) {
 backButton.disabled = mediaLibraryState.historyIndex <= 0;
 }
 if (forwardButton) {
 forwardButton.disabled = mediaLibraryState.historyIndex >= mediaLibraryState.history.length - 1;
 }
 if (upButton) {
 upButton.disabled = !mediaLibraryState.directory;
 }
}

function setMediaLibraryDirectory(directory, options = {}) {
 const normalized = directory || '';
 const pushHistory = options.pushHistory !== false;

 mediaLibraryState.directory = normalized;
 rememberExpandedDirectory(normalized);
 if (pushHistory) {
 mediaLibraryState.history = mediaLibraryState.history.slice(0, mediaLibraryState.historyIndex + 1);
 mediaLibraryState.history.push(normalized);
 mediaLibraryState.historyIndex = mediaLibraryState.history.length - 1;
 }

 updateMediaLibraryNavButtons();
 fetchMediaLibrary();
}

function renderLibraryStatus(message = '', type = 'info') {
 const status = document.getElementById('media-library-status');
 if (!status) return;

 if (!message) {
 status.className = 'media-library-status hidden';
 status.textContent = '';
 return;
 }

 status.className = `media-library-status is-${type}`;
 status.textContent = message;
}

function updateMediaDetails(item) {
 const emptyPanel = document.getElementById('media-library-details-empty');
 const detailsPanel = document.getElementById('media-library-details-panel');
 const preview = document.getElementById('media-library-details-preview');
 if (!emptyPanel || !detailsPanel || !preview) return;

 if (!item) {
 emptyPanel.classList.remove('hidden');
 detailsPanel.classList.add('hidden');
 preview.innerHTML = '';
 return;
 }

 emptyPanel.classList.add('hidden');
 detailsPanel.classList.remove('hidden');

 const itemUrl = normalizeMediaSelectionUrl(item.public_path || item.url || '');
 if (item.type === 'video') {
 preview.innerHTML = `<video controls playsinline preload="metadata"><source src="${escapeHtmlAttr(itemUrl)}"></video>`;
 } else {
 preview.innerHTML = `<img src="${escapeHtmlAttr(itemUrl)}" alt="${escapeHtmlAttr(item.name || '')}">`;
 }

 document.getElementById('media-details-title').textContent = item.title || item.name || '';
 document.getElementById('media-details-original').textContent = item.original_name || item.name || '';
 document.getElementById('media-details-storage').textContent = item.storage_name || '';
 document.getElementById('media-details-type').textContent = item.type === 'video' ? '视频' : (item.type === 'image' ? '图片' : '文件');
 document.getElementById('media-details-directory').textContent = item.directory ? `/uploads/${item.directory}` : '/uploads';
 document.getElementById('media-details-meta').textContent = [item.dimensions || '', item.size_formatted || item.size || ''].filter(Boolean).join(' · ') || '-';
 document.getElementById('media-details-date').textContent = item.date || '';
 document.getElementById('media-details-path').value = item.public_path || '';
}

function escapeHtmlAttr(value) {
 return String(value || '')
 .replace(/&/g, '&amp;')
 .replace(/"/g, '&quot;')
 .replace(/</g, '&lt;')
 .replace(/>/g, '&gt;');
}

function normalizeMediaSelectionUrl(url) {
 if (!url) return '';
 if (/^(https?:)?\/\//i.test(url) || url.startsWith('data:')) {
 return url;
 }

 return `${window.APP_BASE_PATH || ''}${url}`;
}

function resolveMediaSelectionPayload(items) {
 if (mediaLibraryState.options.returnObjects) {
 return mediaLibraryState.multi ? items : (items[0] || null);
 }

 const urls = items.map((item) => item.public_path || item.url || '');
 return mediaLibraryState.multi ? urls : (urls[0] || '');
}

function selectMediaItem(item, immediate = false) {
 mediaLibraryState.activeItem = item;
 updateMediaDetails(item);

 if (mediaLibraryState.multi) {
 if (mediaLibraryState.selectedItems.has(item.public_path)) {
 mediaLibraryState.selectedItems.delete(item.public_path);
 } else {
 mediaLibraryState.selectedItems.set(item.public_path, item);
 }
 renderMediaLibrarySelection();
 return;
 }

 mediaLibraryState.selectedItems = new Map([[item.public_path, item]]);
 renderMediaLibrarySelection();

 if (immediate && typeof mediaLibraryState.callback === 'function') {
 mediaLibraryState.callback(resolveMediaSelectionPayload([item]));
 closeMediaLibraryModal();
 }
}

function renderMediaLibrarySelection() {
 document.querySelectorAll('.js-media-library-item').forEach((element) => {
 const path = element.dataset.path || '';
 element.classList.toggle('is-selected', mediaLibraryState.selectedItems.has(path));
 element.classList.toggle('is-active', mediaLibraryState.activeItem && mediaLibraryState.activeItem.public_path === path);
 const checkbox = element.querySelector('.js-media-library-selector');
 if (checkbox) {
 checkbox.checked = mediaLibraryState.selectedItems.has(path);
 }
 });
 updateMediaLibraryBatchControls();
}

function updateMediaLibraryBatchControls() {
 const count = mediaLibraryState.selectedItems.size;
 const deleteBtn = document.getElementById('media-library-bulk-delete-btn');
 const deleteLabel = document.getElementById('media-library-bulk-delete-label');
 const confirmBtn = document.getElementById('confirm-media-selection');
 const currentStats = mediaLibraryState.currentStats || {};
 if (deleteBtn && deleteLabel) {
 deleteBtn.disabled = count === 0;
 deleteLabel.textContent = count > 0 ? `删除 (${count})` : '删除';
 }

 const selectedCount = document.getElementById('media-library-selected-count');
 if (selectedCount) {
 selectedCount.textContent = `已选择 ${count} 个文件`;
 }

 const currentCount = document.getElementById('media-library-current-count');
 if (currentCount) {
 currentCount.textContent = `${currentStats.folder_count || 0} 个文件夹，${currentStats.file_count || 0} 个文件`;
 }

 const currentSize = document.getElementById('media-library-current-size');
 if (currentSize) {
 currentSize.textContent = `文件总大小 ${currentStats.total_size_formatted || '0 B'}`;
 }

 if (confirmBtn) {
 confirmBtn.disabled = count === 0;
 confirmBtn.textContent = mediaLibraryState.multi
 ? (count > 0 ? `插入所选 (${count})` : '插入所选')
 : '插入';
 }

 const toggleAll = document.getElementById('media-library-toggle-all');
 if (toggleAll) {
 const files = Array.isArray(mediaLibraryState.lastPayload?.files) ? mediaLibraryState.lastPayload.files : [];
 toggleAll.disabled = !mediaLibraryState.multi;
 toggleAll.checked = mediaLibraryState.multi && files.length > 0 && count === files.length;
 toggleAll.indeterminate = mediaLibraryState.multi && count > 0 && files.length > 0 && count < files.length;
 }
}

function renderMediaLibraryTree(nodes, currentDirectory) {
 const renderNodes = (items) => {
 if (!items.length) {
 return '';
 }

 return `
 <ul class="media-library-tree-list">
 ${items.map((node) => `
 <li class="media-library-tree-node ${node.is_current ? 'is-current' : ''} ${node.is_ancestor ? 'is-ancestor' : ''}">
 <div class="media-library-tree-row">
 <button type="button" class="media-library-tree-toggle ${node.children && node.children.length ? '' : 'is-empty'} ${mediaLibraryState.expandedDirectories.has(node.directory || '') ? 'is-expanded' : ''}" data-directory="${escapeHtmlAttr(node.directory || '')}">
 <span class="icon"><i class="fas fa-caret-right"></i></span>
 </button>
 <a href="#" class="js-media-library-tree-link media-library-tree-link" data-directory="${escapeHtmlAttr(node.directory || '')}">
 <span class="icon"><i class="fas fa-folder"></i></span>
 <span>${escapeHtmlAttr(node.name || '')}</span>
 </a>
 </div>
 <div class="media-library-tree-children ${mediaLibraryState.expandedDirectories.has(node.directory || '') ? 'is-open' : 'is-collapsed'}">
 ${renderNodes(node.children || [])}
 </div>
 </li>
 `).join('')}
 </ul>
 `;
 };

 return `
 <a href="#" class="js-media-library-tree-link media-library-tree-root ${currentDirectory === '' ? 'is-current' : ''}" data-directory="">
 <span class="icon"><i class="fas fa-hdd"></i></span>
 <span>我的媒体</span>
 </a>
 ${renderNodes(nodes || [])}
 `;
}

function updateMediaLibraryViewMode() {
 const listBtn = document.getElementById('media-library-view-list-btn');
 const tilesBtn = document.getElementById('media-library-view-tiles-btn');
 const list = document.getElementById('media-library-list');
 const header = document.getElementById('media-library-file-header');
 const label = document.getElementById('media-library-current-mode-label');

 if (listBtn) {
 listBtn.classList.toggle('bg-indigo-50', mediaLibraryState.view === 'list');
 listBtn.classList.toggle('text-indigo-600', mediaLibraryState.view === 'list');
 listBtn.classList.toggle('text-slate-500', mediaLibraryState.view !== 'list');
 listBtn.classList.toggle('hover:bg-white', mediaLibraryState.view !== 'list');
 listBtn.classList.toggle('hover:text-slate-700', mediaLibraryState.view !== 'list');
 }
 if (tilesBtn) {
 tilesBtn.classList.toggle('bg-indigo-50', mediaLibraryState.view === 'tiles');
 tilesBtn.classList.toggle('text-indigo-600', mediaLibraryState.view === 'tiles');
 tilesBtn.classList.toggle('text-slate-500', mediaLibraryState.view !== 'tiles');
 tilesBtn.classList.toggle('hover:bg-white', mediaLibraryState.view !== 'tiles');
 tilesBtn.classList.toggle('hover:text-slate-700', mediaLibraryState.view !== 'tiles');
 }
 if (list) list.classList.toggle('is-tiles', mediaLibraryState.view === 'tiles');
 if (header) header.classList.toggle('hidden', mediaLibraryState.view === 'tiles');
 if (label) label.textContent = mediaLibraryState.view === 'tiles' ? '缩略图视图' : '列表视图';
}

function activateMediaItem(item) {
 if (!item) {
 return;
 }

 mediaLibraryState.selectedItems = new Map([[item.public_path, item]]);
 renderMediaLibrarySelection();

 if (typeof mediaLibraryState.callback === 'function') {
 mediaLibraryState.callback(resolveMediaSelectionPayload([item]));
 closeMediaLibraryModal();
 }
}

function renderMediaLibrary(payload) {
 const container = document.getElementById('media-library-list');
 const breadcrumbsContainer = document.getElementById('media-library-breadcrumbs');
 const directoryLabel = document.getElementById('media-library-directory-label');
 const treeContainer = document.getElementById('media-library-tree');

 if (!container || !breadcrumbsContainer || !directoryLabel || !treeContainer) {
 return;
 }

 mediaLibraryState.lastPayload = payload;
 mediaLibraryState.currentStats = payload.current_stats || {
 item_count: 0,
 total_size_formatted: '0 B'
 };
 directoryLabel.textContent = payload.current_directory_label || '/uploads';
 breadcrumbsContainer.innerHTML = (payload.breadcrumbs || []).map((crumb, index, arr) => {
 const active = index === arr.length - 1;
 const label = escapeHtmlAttr(crumb.name || '');
 const separator = index > 0 ? '<span class="text-slate-300">/</span>' : '';
 if (active) {
 return `${separator}<span class="font-semibold text-slate-900" aria-current="page">${label}</span>`;
 }
 return `${separator}<a href="#" data-directory="${escapeHtmlAttr(crumb.directory || '')}" class="js-media-breadcrumb transition hover:text-slate-700">${label}</a>`;
 }).join('');

 const folders = payload.folders || [];
 const files = payload.files || [];
 container.innerHTML = '';
 container.classList.toggle('is-tiles', mediaLibraryState.view === 'tiles');
 treeContainer.innerHTML = renderMediaLibraryTree(payload.folder_tree || [], payload.current_directory || '');
 mediaLibraryState.selectedItems = new Map(
 Array.from(mediaLibraryState.selectedItems.entries()).filter(([path]) => files.some((item) => item.public_path === path))
 );

 if (mediaLibraryState.activeItem && !files.some((item) => item.public_path === mediaLibraryState.activeItem.public_path)) {
 mediaLibraryState.activeItem = null;
 updateMediaDetails(null);
 }

 updateMediaLibraryViewMode();

 if (!folders.length && !files.length) {
 container.innerHTML = `
 <div class="media-library-empty">
 <span class="icon is-large"><i class="fas fa-photo-video"></i></span>
 <p class="mt-3">这个目录下还没有符合条件的媒体文件。</p>
 </div>
 `;
 updateMediaDetails(null);
 updateMediaLibraryBatchControls();
 return;
 }

 if (mediaLibraryState.view === 'tiles') {
 folders.forEach((folder) => {
 const tile = document.createElement('button');
 tile.type = 'button';
 tile.className = 'media-library-folder-tile media-library-folder-tile-explorer';
 tile.innerHTML = `
 <div class="media-library-folder-tile-icon">
 <span class="icon is-large"><i class="fas fa-folder"></i></span>
 </div>
 <div class="media-library-tile-body">
 <strong title="${escapeHtmlAttr(folder.name || '')}">${escapeHtmlAttr(folder.name || '')}</strong>
 <span>${Number(folder.item_count || 0)} 个项目</span>
 <span>${escapeHtmlAttr(folder.directory ? `/uploads/${folder.directory}` : '/uploads')}</span>
 </div>
 `;
 tile.addEventListener('click', () => {
 mediaLibraryState.selectedItems.clear();
 mediaLibraryState.activeItem = null;
 updateMediaDetails(null);
 renderMediaLibrarySelection();
 });
 tile.addEventListener('dblclick', () => {
 setMediaLibraryDirectory(folder.directory || '');
 });
 container.appendChild(tile);
 });

 files.forEach((file) => {
 const tile = document.createElement('button');
 tile.type = 'button';
 tile.className = 'media-library-tile js-media-library-item media-library-file-tile';
 tile.dataset.path = file.public_path || '';

 const itemUrl = normalizeMediaSelectionUrl(file.public_path || file.url || '');
 const thumbHtml = file.type === 'video'
 ? `<div class="media-library-thumb"><video preload="metadata"><source src="${escapeHtmlAttr(itemUrl)}"></video><span class="media-library-video-badge"><i class="fas fa-play"></i></span></div>`
 : `<div class="media-library-thumb"><img src="${escapeHtmlAttr(itemUrl)}" alt="${escapeHtmlAttr(file.name || '')}" loading="lazy"></div>`;

 tile.innerHTML = `
 <label class="media-library-select-toggle">
 <input type="checkbox" class="js-media-library-selector" value="${escapeHtmlAttr(file.public_path || '')}">
 <span>选择</span>
 </label>
 ${thumbHtml}
 <div class="media-library-tile-body">
 <strong title="${escapeHtmlAttr(file.original_name || file.name || '')}">${escapeHtmlAttr(file.original_name || file.name || '')}</strong>
 <span>${file.type === 'video' ? '视频' : '图片'} · ${escapeHtmlAttr(file.size_formatted || '')}</span>
 <span title="${escapeHtmlAttr(file.directory ? `/uploads/${file.directory}` : '/uploads')}">${escapeHtmlAttr(file.directory ? `/uploads/${file.directory}` : '/uploads')}</span>
 </div>
 `;

 tile.addEventListener('click', () => {
 selectMediaItem(file, false);
 });
 tile.addEventListener('dblclick', () => {
 activateMediaItem(file);
 });

 const selector = tile.querySelector('.js-media-library-selector');
 if (selector) {
 selector.addEventListener('click', (event) => {
 event.stopPropagation();
 });
 selector.addEventListener('change', (event) => {
 event.stopPropagation();
 mediaLibraryState.activeItem = file;
 updateMediaDetails(file);
 if (!mediaLibraryState.multi) {
 mediaLibraryState.selectedItems = selector.checked
 ? new Map([[file.public_path, file]])
 : new Map();
 } else if (selector.checked) {
 mediaLibraryState.selectedItems.set(file.public_path, file);
 } else {
 mediaLibraryState.selectedItems.delete(file.public_path);
 }
 renderMediaLibrarySelection();
 });
 }

 container.appendChild(tile);
 });

 renderMediaLibrarySelection();
 return;
 }

 folders.forEach((folder) => {
 const row = document.createElement('button');
 row.type = 'button';
 row.className = 'media-library-folder-row';
 row.innerHTML = `
 <div class="media-library-file-col check"></div>
 <div class="media-library-file-col name">
 <span class="media-library-row-icon folder"><i class="fas fa-folder"></i></span>
 <div class="media-library-row-name">
 <strong>${escapeHtmlAttr(folder.name || '')}</strong>
 <span>${Number(folder.item_count || 0)} 个项目</span>
 </div>
 </div>
 <div class="media-library-file-col type">文件夹</div>
 <div class="media-library-file-col folder">${escapeHtmlAttr(folder.directory ? `/uploads/${folder.directory}` : '/uploads')}</div>
 <div class="media-library-file-col size">-</div>
 <div class="media-library-file-col date">${Number(folder.item_count || 0)} 项</div>
 `;
 row.addEventListener('click', () => {
 mediaLibraryState.selectedItems.clear();
 mediaLibraryState.activeItem = null;
 updateMediaDetails(null);
 renderMediaLibrarySelection();
 });
 row.addEventListener('dblclick', () => {
 setMediaLibraryDirectory(folder.directory || '');
 });
 container.appendChild(row);
 });

 files.forEach((file) => {
 const element = document.createElement('button');
 element.type = 'button';
 element.className = 'media-library-file-row js-media-library-item';
 element.dataset.path = file.public_path || '';

 const itemUrl = normalizeMediaSelectionUrl(file.public_path || file.url || '');
 const iconHtml = file.type === 'video'
 ? `<span class="media-library-row-icon video"><i class="fas fa-video"></i></span>`
 : `<span class="media-library-row-icon image"><img src="${escapeHtmlAttr(itemUrl)}" alt="${escapeHtmlAttr(file.name || '')}" loading="lazy"></span>`;

 element.innerHTML = `
 <div class="media-library-file-col check">
 <label class="media-library-select-toggle">
 <input type="checkbox" class="js-media-library-selector" value="${escapeHtmlAttr(file.public_path || '')}">
 </label>
 </div>
 <div class="media-library-file-col name">
 ${iconHtml}
 <div class="media-library-row-name">
 <strong title="${escapeHtmlAttr(file.original_name || file.name || '')}">${escapeHtmlAttr(file.original_name || file.name || '')}</strong>
 <span>存储名：${escapeHtmlAttr(file.storage_name || '')}</span>
 </div>
 </div>
 <div class="media-library-file-col type">${file.type === 'video' ? '视频' : '图片'}</div>
 <div class="media-library-file-col folder">${escapeHtmlAttr(file.directory ? `/uploads/${file.directory}` : '/uploads')}</div>
 <div class="media-library-file-col size">${escapeHtmlAttr(file.size_formatted || '')}</div>
 <div class="media-library-file-col date">${escapeHtmlAttr(file.date || '')}</div>
 `;

 element.addEventListener('click', () => {
 selectMediaItem(file, false);
 });

 element.addEventListener('dblclick', () => {
 activateMediaItem(file);
 });

 const selector = element.querySelector('.js-media-library-selector');
 if (selector) {
 selector.addEventListener('click', (event) => {
 event.stopPropagation();
 });
 selector.addEventListener('change', (event) => {
 event.stopPropagation();
 mediaLibraryState.activeItem = file;
 updateMediaDetails(file);
 if (!mediaLibraryState.multi) {
 mediaLibraryState.selectedItems = selector.checked
 ? new Map([[file.public_path, file]])
 : new Map();
 } else if (selector.checked) {
 mediaLibraryState.selectedItems.set(file.public_path, file);
 } else {
 mediaLibraryState.selectedItems.delete(file.public_path);
 }
 renderMediaLibrarySelection();
 });
 }

 container.appendChild(element);
 });

 breadcrumbsContainer.querySelectorAll('.js-media-breadcrumb').forEach((link) => {
 link.addEventListener('click', (event) => {
 event.preventDefault();
 setMediaLibraryDirectory(link.dataset.directory || '');
 });
 });

 treeContainer.querySelectorAll('.js-media-library-tree-link').forEach((link) => {
 link.addEventListener('click', (event) => {
 event.preventDefault();
 setMediaLibraryDirectory(link.dataset.directory || '');
 });
 });

 treeContainer.querySelectorAll('.media-library-tree-toggle').forEach((toggle) => {
 toggle.addEventListener('click', (event) => {
 event.preventDefault();
 event.stopPropagation();
 const directory = toggle.dataset.directory || '';
 if (toggle.classList.contains('is-empty')) {
 return;
 }
 if (mediaLibraryState.expandedDirectories.has(directory)) {
 mediaLibraryState.expandedDirectories.delete(directory);
 } else {
 rememberExpandedDirectory(directory);
 }
 renderMediaLibrary(mediaLibraryState.lastPayload || payload);
 });
 });

 renderMediaLibrarySelection();
}

async function fetchMediaLibrary() {
 const container = document.getElementById('media-library-list');
 if (!container) return;

 renderLibraryStatus('');
 container.innerHTML = `
 <div class="media-library-empty">
 <span class="icon is-large"><i class="fas fa-spinner fa-pulse"></i></span>
 <p class="mt-3">正在加载媒体库...</p>
 </div>
 `;

 try {
 const res = await fetch(mediaLibraryApiUrl(), {
 headers: {
 'Accept': 'application/json'
 }
 });
 const payload = await res.json();
 if (!res.ok || payload.success === false) {
 throw new Error(payload?.data?.messages?.[0] || '媒体库加载失败');
 }
 renderMediaLibrary(payload);
 } catch (err) {
 container.innerHTML = `
 <div class="media-library-empty text-rose-600">
 <span class="icon is-large"><i class="fas fa-exclamation-circle"></i></span>
 <p class="mt-3">${escapeHtmlAttr(err.message || '加载失败')}</p>
 </div>
 `;
 updateMediaDetails(null);
 }
}

function insertMediaIntoEditor(editor, selection) {
 const items = Array.isArray(selection) ? selection : [selection];
 items.filter(Boolean).forEach((item) => {
 const url = normalizeMediaSelectionUrl(item.public_path || item.url || '');
 if (!url) {
 return;
 }

 if (item.type === 'video') {
 editor.s.insertHTML(`<p><video controls playsinline src="${escapeHtmlAttr(url)}"></video></p>`);
 return;
 }

 if (item.type === 'image') {
 editor.s.insertHTML(`<p><img src="${escapeHtmlAttr(url)}" alt="${escapeHtmlAttr(item.title || item.original_name || '')}"></p>`);
 return;
 }

 editor.s.insertHTML(`<p><a href="${escapeHtmlAttr(url)}" target="_blank" rel="noopener">${escapeHtmlAttr(item.title || item.original_name || url)}</a></p>`);
 });
}

document.addEventListener("DOMContentLoaded", function () {
 // 手机端导航切换
 const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('[data-nav-toggle]'), 0);
 $navbarBurgers.forEach( el => {
 el.addEventListener('click', () => {
 const target = el.dataset.target;
 const $target = document.getElementById(target);
 const isHidden = $target.classList.contains('hidden');
 el.classList.toggle('is-active');
 el.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
 $target.classList.toggle('hidden', !isHidden);
 if (window.innerWidth < 1024) {
 $target.classList.toggle('flex', isHidden);
 }
 });
 });

 // 媒体库通用事件
 const modal = document.getElementById('media-library-modal');
 const closeBtns = modal ? modal.querySelectorAll('[data-media-modal-close]') : [];
 closeBtns.forEach(btn => btn.addEventListener('click', () => closeMediaLibraryModal()));

 const confirmBtn = document.getElementById('confirm-media-selection');
 confirmBtn.addEventListener('click', () => {
 const selected = Array.from(mediaLibraryState.selectedItems.values());
 if (!selected.length) {
 return;
 }
 if (typeof mediaLibraryState.callback === 'function') {
 mediaLibraryState.callback(resolveMediaSelectionPayload(selected));
 }
 closeMediaLibraryModal();
 });

 document.getElementById('media-library-view-list-btn')?.addEventListener('click', function() {
 mediaLibraryState.view = 'list';
 updateMediaLibraryViewMode();
 if (mediaLibraryState.lastPayload) {
 renderMediaLibrary(mediaLibraryState.lastPayload);
 }
 });

 document.getElementById('media-library-view-tiles-btn')?.addEventListener('click', function() {
 mediaLibraryState.view = 'tiles';
 updateMediaLibraryViewMode();
 if (mediaLibraryState.lastPayload) {
 renderMediaLibrary(mediaLibraryState.lastPayload);
 }
 });

 document.getElementById('media-library-toggle-all')?.addEventListener('change', function() {
 if (!mediaLibraryState.multi) {
 this.checked = false;
 return;
 }
 const files = Array.isArray(mediaLibraryState.lastPayload?.files) ? mediaLibraryState.lastPayload.files : [];
 if (this.checked) {
 mediaLibraryState.selectedItems = new Map(files.map((item) => [item.public_path, item]));
 if (!mediaLibraryState.activeItem && files[0]) {
 mediaLibraryState.activeItem = files[0];
 updateMediaDetails(files[0]);
 }
 } else {
 mediaLibraryState.selectedItems.clear();
 if (!mediaLibraryState.multi) {
 mediaLibraryState.activeItem = null;
 updateMediaDetails(null);
 }
 }
 renderMediaLibrarySelection();
 });

 const copyBtn = document.getElementById('media-details-copy-btn');
 if (copyBtn) {
 copyBtn.addEventListener('click', function() {
 const path = document.getElementById('media-details-path')?.value || '';
 if (!path) return;
 navigator.clipboard.writeText(path).then(() => {
 renderLibraryStatus('路径已复制到剪贴板', 'success');
 setTimeout(() => renderLibraryStatus(''), 1600);
 });
 });
 }

 const searchInput = document.getElementById('media-library-search');
 if (searchInput) {
 searchInput.addEventListener('input', function() {
 mediaLibraryState.search = this.value.trim();
 window.clearTimeout(mediaLibraryState.fetchTimer);
 mediaLibraryState.fetchTimer = window.setTimeout(fetchMediaLibrary, 250);
 });
 }

 const typeFilter = document.getElementById('media-library-type-filter');
 if (typeFilter) {
 typeFilter.addEventListener('change', function() {
 mediaLibraryState.type = this.value;
 fetchMediaLibrary();
 });
 }

 const sortFilter = document.getElementById('media-library-sort-filter');
 if (sortFilter) {
 sortFilter.addEventListener('change', function() {
 mediaLibraryState.sort = this.value;
 fetchMediaLibrary();
 });
 }

 document.getElementById('media-library-back-btn')?.addEventListener('click', function() {
 if (mediaLibraryState.historyIndex <= 0) {
 return;
 }
 mediaLibraryState.historyIndex -= 1;
 mediaLibraryState.directory = mediaLibraryState.history[mediaLibraryState.historyIndex] || '';
 updateMediaLibraryNavButtons();
 fetchMediaLibrary();
 });

 document.getElementById('media-library-forward-btn')?.addEventListener('click', function() {
 if (mediaLibraryState.historyIndex >= mediaLibraryState.history.length - 1) {
 return;
 }
 mediaLibraryState.historyIndex += 1;
 mediaLibraryState.directory = mediaLibraryState.history[mediaLibraryState.historyIndex] || '';
 updateMediaLibraryNavButtons();
 fetchMediaLibrary();
 });

 document.getElementById('media-library-up-btn')?.addEventListener('click', function() {
 if (!mediaLibraryState.directory) {
 return;
 }
 const parts = mediaLibraryState.directory.split('/').filter(Boolean);
 parts.pop();
 setMediaLibraryDirectory(parts.join('/'));
 });

 document.getElementById('media-library-refresh-btn')?.addEventListener('click', function() {
 fetchMediaLibrary();
 });

 const newFolderBtn = document.getElementById('media-library-new-folder-btn');
 if (newFolderBtn) {
 newFolderBtn.addEventListener('click', async function() {
 const name = window.prompt('请输入新目录名称');
 if (!name) {
 return;
 }

 const formData = new FormData();
 formData.append('csrf', window.ADMIN_CSRF_TOKEN || '');
 formData.append('dir', mediaLibraryState.directory || '');
 formData.append('folder_name', name.trim());
 formData.append('response_format', 'json');

 try {
 const response = await fetch((window.APP_BASE_PATH || '') + '/admin/media/folder/create', {
 method: 'POST',
 headers: {
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest'
 },
 body: formData
 });
 const payload = await response.json();
 if (!response.ok || payload.success === false) {
 throw new Error(payload?.data?.messages?.[0] || '创建目录失败');
 }
 renderLibraryStatus('目录已创建', 'success');
 fetchMediaLibrary();
 } catch (error) {
 renderLibraryStatus(error.message || '创建目录失败', 'danger');
 }
 });
 }

 const uploadInput = document.getElementById('media-upload-input');
 if (uploadInput) {
 uploadInput.addEventListener('change', async function() {
 const files = Array.from(this.files);
 if (files.length === 0) return;
 const progressContainer = document.getElementById('upload-progress-container');
 const overallProgress = document.getElementById('overall-progress');
 const progressPercent = document.getElementById('upload-progress-percent');
 const statusText = document.getElementById('upload-status-text');

 progressContainer.classList.remove('hidden');
 overallProgress.value = 0;
 progressPercent.innerText = '0%';
 
 try {
 const formData = new FormData();
 files.forEach((file) => formData.append('media_files[]', file));
 formData.append('dir', mediaLibraryState.directory || '');
 formData.append('csrf', window.ADMIN_CSRF_TOKEN || '');
 formData.append('response_format', 'json');

 await new Promise((resolve, reject) => {
 const xhr = new XMLHttpRequest();
 xhr.open('POST', (window.APP_BASE_PATH || '') + '/admin/media/upload', true);
 xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
 xhr.upload.onprogress = (e) => {
 if (e.lengthComputable) {
 const percent = Math.round((e.loaded / e.total) * 100);
 overallProgress.value = percent;
 progressPercent.innerText = percent + '%';
 statusText.innerText = `正在上传 ${files.length} 个文件`;
 }
 };
 xhr.onload = () => {
 try {
 const payload = JSON.parse(xhr.responseText || '{}');
 if (xhr.status >= 200 && xhr.status < 300 && payload.success !== false) {
 resolve(payload);
 return;
 }
 reject(new Error(payload?.data?.messages?.[0] || '上传失败'));
 } catch (error) {
 reject(new Error('上传响应无效'));
 }
 };
 xhr.onerror = () => reject(new Error('上传失败'));
 xhr.send(formData);
 });

 progressContainer.classList.add('hidden');
 renderLibraryStatus('媒体上传成功', 'success');
 fetchMediaLibrary();
 } catch (err) {
 progressContainer.classList.add('hidden');
 renderLibraryStatus(err.message || '上传失败', 'danger');
 }
 this.value = '';
 });
 }

 const bulkDeleteBtn = document.getElementById('media-library-bulk-delete-btn');
 if (bulkDeleteBtn) {
 bulkDeleteBtn.addEventListener('click', async function() {
 const selected = Array.from(mediaLibraryState.selectedItems.values());
 if (!selected.length) {
 return;
 }

 if (!window.confirm(`确定删除选中的 ${selected.length} 个文件吗？此操作无法撤销。`)) {
 return;
 }

 try {
 const formData = new FormData();
 formData.append('csrf', window.ADMIN_CSRF_TOKEN || '');
 formData.append('dir', mediaLibraryState.directory || '');
 formData.append('response_format', 'json');
 selected.forEach((item) => formData.append('paths[]', item.public_path || ''));

 const response = await fetch((window.APP_BASE_PATH || '') + '/admin/media/delete', {
 method: 'POST',
 headers: {
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest'
 },
 body: formData
 });
 const payload = await response.json();
 if (!response.ok || payload.success === false) {
 throw new Error(payload?.data?.messages?.[0] || '删除失败');
 }

 mediaLibraryState.selectedItems.clear();
 mediaLibraryState.activeItem = null;
 updateMediaDetails(null);
 renderLibraryStatus(payload?.data?.messages?.[0] || '删除成功', 'success');
 fetchMediaLibrary();
 } catch (error) {
 renderLibraryStatus(error.message || '删除失败', 'danger');
 }
 });
 }

 document.addEventListener('keydown', function(event) {
 if (!modal || modal.classList.contains('hidden')) {
 return;
 }

 const activeTag = document.activeElement?.tagName?.toLowerCase() || '';
 const isTypingContext = ['input', 'textarea', 'select'].includes(activeTag);

 if (event.key === 'Escape') {
 closeMediaLibraryModal();
 return;
 }

 if (event.key === 'Enter' && !isTypingContext && mediaLibraryState.selectedItems.size > 0) {
 event.preventDefault();
 confirmBtn.click();
 }
 });

 // Jodit 编辑器初始化
 const editorInputs = document.querySelectorAll('.js-rich-editor');

 if (editorInputs.length && window.Jodit) {
 if (window.Jodit.defaultOptions && window.Jodit.defaultOptions.controls) {
 window.Jodit.defaultOptions.controls.mediaImageLibrary = {
 name: 'mediaImageLibrary',
 icon: 'image',
 tooltip: '从媒体库插入图片',
 exec(editor) {
 openMediaLibrary(function(selection) {
 insertMediaIntoEditor(editor, selection);
 }, true, { type: 'image', returnObjects: true });
 }
 };

 window.Jodit.defaultOptions.controls.mediaAssetLibrary = {
 name: 'mediaAssetLibrary',
 icon: 'folder',
 tooltip: '从媒体库插入图片或视频',
 exec(editor) {
 openMediaLibrary(function(selection) {
 insertMediaIntoEditor(editor, selection);
 }, false, { type: 'all', returnObjects: true });
 }
 };
 }

 editorInputs.forEach((input) => {
 const editorHeight = parseInt(input.dataset.editorHeight || '', 10);
 const editor = Jodit.make(input, {
 height: Number.isFinite(editorHeight) && editorHeight > 0 ? editorHeight : 400,
 minHeight: 260,
 globalFullSize: false,
 toolbarAdaptive: false,
 toolbarSticky: false,
 askBeforePasteHTML: false,
 askBeforePasteFromWord: false,
 showCharsCounter: false,
 showWordsCounter: false,
 showXPathInStatusbar: false,
 beautifyHTML: false,
 imageDefaultWidth: null,
 buttons: [
 'source', '|',
 'bold', 'italic', 'underline', 'strikethrough', '|',
 'ul', 'ol', 'outdent', 'indent', '|',
 'font', 'fontsize', 'brush', 'paragraph', '|',
 'mediaImageLibrary', 'mediaAssetLibrary', 'link', 'table', '|',
 'align', 'undo', 'redo', '|',
 'hr', 'eraser', 'fullsize'
 ]
 });

 const fullscreenHostElements = [];
 let current = input.parentElement;
 while (current && current !== document.body) {
 fullscreenHostElements.push(current);
 current = current.parentElement;
 }

 editor.e.on('toggleFullSize', (isFullSize) => {
 document.documentElement.classList.toggle('editor-fullscreen-active', isFullSize);
 document.body.classList.toggle('editor-fullscreen-active', isFullSize);
 fullscreenHostElements.forEach((element) => {
 element.classList.toggle('editor-fullscreen-host', isFullSize);
 });
 });

 const form = input.closest('form');
 if (form) {
 form.addEventListener('submit', function() {
 editor.synchronizeValues();
 });
 }
 });
 }

 // 2. Price Tiers
 const addTierBtn = document.getElementById("add-price-tier");
 const tierWrap = document.getElementById("price-tier-wrap");
 if (addTierBtn && tierWrap) {
 addTierBtn.addEventListener("click", function () {
 const row = document.createElement("div");
 row.className = "price-tier-row mb-3 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_110px_56px] md:items-end";
 row.innerHTML = `
 <label class="space-y-2">
 <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">最小数量</span>
 <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_min[]" type="number" min="1" required>
 </label>
 <label class="space-y-2">
 <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">最大数量</span>
 <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_max[]" type="number" min="1" placeholder="可空">
 </label>
 <label class="space-y-2">
 <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">单价</span>
 <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_value[]" type="number" min="0" step="0.01" required>
 </label>
 <label class="space-y-2">
 <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">货币</span>
 <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_currency[]" value="USD" required>
 </label>
 <div class="flex md:justify-end">
 <button type="button" class="remove-price-tier inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100" aria-label="删除阶梯价格">
 <i class="fas fa-trash-alt text-sm"></i>
 </button>
 </div>
 `;
 tierWrap.appendChild(row);
 });
 tierWrap.addEventListener("click", function (e) {
 const removeButton = e.target.closest(".remove-price-tier");
 if (removeButton) {
 removeButton.closest(".price-tier-row").remove();
 }
 });
 }
});
</script>
</body>
</html>
