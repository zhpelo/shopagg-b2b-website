<!DOCTYPE html>
<html data-app-base-path="<?= h(base_path()) ?>" data-admin-csrf="<?= h(csrf_token()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <script src="<?= url('/assets/admin/base.js').'?v='.APP_VERSION ?>"></script>
    <script src="//cdn.tailwindcss.com/3.4.17" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@4.13.5/es2021/jodit.fat.min.css" integrity="sha384-z1CAdjZT0Ot4X7eoNzGTz5RCl/Gp9y6nEqBwAJVdhr+B/kFJy+M9TIbEmgy9V2L9" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?= url('/assets/admin/rich-content.css').'?v='.APP_VERSION ?>">
    <link rel="stylesheet" href="<?= url('/assets/admin/style.css').'?v='.APP_VERSION ?>">
    <?php if (($showNav ?? true) && class_exists(\App\Plugins\PluginRuntime::class)): ?>
        <?= \App\Plugins\PluginRuntime::instance()->adminAssetTags('head') ?>
    <?php endif; ?>
    <?php if ($showNav ?? true): ?>
        <script>
            (() => {
                if (!matchMedia('(min-width: 1024px)').matches) return;
                let collapsed;
                try { collapsed = localStorage.getItem('shopagg.admin.sidebar.collapsed'); } catch (_) {}
                if (collapsed === null || collapsed === undefined) collapsed = matchMedia('(max-width: 1279px)').matches ? '1' : '0';
                document.documentElement.classList.toggle('admin-sidebar-collapsed', collapsed === '1');
            })();
        </script>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.7/Sortable.min.js" integrity="sha384-DgmC6Xe2bSN2WjTDXzWYbUbxyhNP+NNkGDR/g78pCXV7E7rcVTGxVg0uIVCUUcBc" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body class="<?= ($showNav ?? true) ? 'min-h-screen bg-slate-100 text-slate-800' : 'login-page' ?>">
    <?php if ($showNav ?? true):
        $current_path = \App\Core\AuthManager::normalizePath((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
        $user_role = \App\Core\AuthManager::getUserRole() ?? 'staff';
        $pluginRuntime = class_exists(\App\Plugins\PluginRuntime::class) ? \App\Plugins\PluginRuntime::instance() : null;
        $legacyAdminNavigation = $pluginRuntime ? $pluginRuntime->slots()->render('admin.navigation', ['path' => $current_path, 'role' => $user_role]) : '';
        $adminNavigation = \App\Core\AdminNavigation::build(
            $current_path,
            $pluginRuntime ? (array)($pluginRuntime->cache()['admin_menu'] ?? []) : [],
            trim($legacyAdminNavigation) !== ''
        );
    ?>
        <aside class="admin-sidebar" id="adminSidebar" aria-label="后台导航" data-admin-sidebar>
            <div class="admin-sidebar-header">
                <a class="admin-sidebar-brand" href="<?= url('/admin') ?>" aria-label="返回仪表盘">
                    <img src="<?= url('/assets/admin/images/shopagg-logo.png') ?>" alt="Lighthouse CMS">
                </a>
                <a href="#" class="admin-sidebar-close" aria-label="关闭导航" data-sidebar-dismiss>
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                </a>
            </div>

            <nav class="admin-sidebar-nav" aria-label="后台功能">
                <?php $dashboard = $adminNavigation['dashboard']; ?>
                <a class="admin-sidebar-dashboard<?= $dashboard['active'] ? ' is-active' : '' ?>" href="<?= url($dashboard['url']) ?>" <?= $dashboard['active'] ? 'aria-current="page"' : '' ?> data-tooltip="<?= h($dashboard['label']) ?>">
                    <span class="admin-nav-icon"><i class="fas fa-<?= h($dashboard['icon']) ?>" aria-hidden="true"></i></span>
                    <span class="admin-nav-label"><?= h($dashboard['label']) ?></span>
                </a>

                <?php foreach ($adminNavigation['sections'] as $section): ?>
                    <details class="admin-sidebar-group<?= $section['active'] ? ' is-active' : '' ?>" data-nav-group="<?= h($section['id']) ?>" <?= $section['active'] ? 'open data-active="true"' : '' ?>>
                        <summary aria-expanded="<?= $section['active'] ? 'true' : 'false' ?>" data-tooltip="<?= h($section['label']) ?>">
                            <span class="admin-nav-icon"><i class="fas fa-<?= h($section['icon']) ?>" aria-hidden="true"></i></span>
                            <span class="admin-nav-label"><?= h($section['label']) ?></span>
                            <i class="fas fa-chevron-down admin-nav-chevron" aria-hidden="true"></i>
                        </summary>
                        <div class="admin-sidebar-items">
                            <?php foreach ($section['items'] as $item): ?>
                                <a class="admin-sidebar-item<?= $item['active'] ? ' is-active' : '' ?>" href="<?= h(url($item['url'])) ?>" <?= $item['active'] ? 'aria-current="page"' : '' ?>>
                                    <span class="admin-item-marker" aria-hidden="true"></span>
                                    <span><?= h($item['label']) ?></span>
                                </a>
                            <?php endforeach; ?>
                            <?php if ($section['id'] === 'extensions' && $section['legacy']): ?>
                                <div class="admin-legacy-navigation" data-deprecated-slot="admin.navigation">
                                    <?= $legacyAdminNavigation ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </nav>

            <div class="admin-sidebar-footer">
                <span class="admin-sidebar-version">Lighthouse CMS · v<?= h(APP_VERSION) ?></span>
            </div>
        </aside>
        <a href="#" class="admin-sidebar-backdrop" aria-label="关闭导航" data-sidebar-dismiss></a>

        <header class="admin-topbar">
            <div class="admin-topbar-leading">
                <a href="#adminSidebar" class="admin-sidebar-toggle" role="button" aria-label="切换导航栏" aria-controls="adminSidebar" aria-expanded="true" data-sidebar-toggle>
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </a>
                <div class="admin-topbar-title">
                    <span>后台管理</span>
                    <strong><?= h($title) ?></strong>
                </div>
            </div>
            <div class="admin-topbar-actions">
                <a class="admin-topbar-site" href="<?= url('/') ?>" target="_blank" rel="noopener">
                    <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i><span>访问网站</span>
                </a>
                <div class="admin-profile-menu" data-profile-menu>
                    <button type="button" class="admin-profile-toggle" aria-expanded="false" data-profile-menu-toggle>
                        <i class="fas fa-circle-user" aria-hidden="true"></i>
                        <span><?= h($_SESSION['admin_display_name'] ?? $_SESSION['admin_user'] ?? 'Admin') ?></span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="admin-profile-dropdown">
                        <div>
                            <a href="<?= url('/admin/profile') ?>"><i class="fas fa-id-card" aria-hidden="true"></i>个人资料</a>
                            <button type="button" data-post-action="<?= url('/admin/logout') ?>" data-post-payload="{}"><i class="fas fa-right-from-bracket" aria-hidden="true"></i>退出登录</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    <?php endif; ?>

    <div class="admin-main">
        <section class="admin-content-section px-4 py-6 sm:px-6 lg:px-8">
            <div class="container">
                <?= $content ?>
            </div>
        </section>
    </div>

    <!-- 统一媒体库模态框 -->
    <div class="fixed inset-0 z-[200000] hidden items-center justify-center p-4" id="media-library-modal">
        <div class="absolute inset-0 bg-slate-950/60" data-media-modal-close></div>
        <div class="media-library-modal-card relative z-10 flex max-h-[calc(100vh-2rem)] w-full flex-col overflow-hidden rounded-[20px] bg-white shadow-2xl">
            <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-5 py-2">
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

    <!-- 统一商品选择器模态框 -->
    <div class="fixed inset-0 z-[200000] hidden items-center justify-center p-4" id="product-selector-modal">
        <div class="absolute inset-0 bg-slate-950/60" data-product-selector-close></div>
        <div class="relative z-10 flex max-h-[calc(100vh-2rem)] w-full max-w-6xl flex-col overflow-hidden rounded-[20px] bg-white shadow-2xl">
            <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-5 py-4">
                <div>
                    <p class="flex items-center gap-2 text-lg font-bold text-slate-900">
                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-box"></i></span>
                        选择商品
                    </p>
                    <p class="mt-1 text-sm text-slate-500">搜索并勾选商品后，可回填商品 ID 到当前表单。</p>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700" aria-label="关闭商品选择器" data-product-selector-close>
                    <i class="fas fa-times text-sm"></i>
                </button>
            </header>

            <section class="flex min-h-0 flex-1 flex-col gap-4 overflow-hidden p-5">
                <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_150px_96px]">
                    <input class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="text" id="product-selector-search" placeholder="搜索商品名称、Slug、摘要、标签">
                    <select id="product-selector-category" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="">全部分类</option>
                    </select>
                    <select id="product-selector-status" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="active">已上架</option>
                        <option value="">全部状态</option>
                        <option value="draft">草稿</option>
                        <option value="inactive">未上架</option>
                    </select>
                    <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50" id="product-selector-refresh">
                        <i class="fas fa-sync-alt"></i>
                        刷新
                    </button>
                </div>

                <div id="product-selector-message" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

                <div class="grid min-h-0 flex-1 gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="flex min-h-0 flex-col overflow-hidden rounded-2xl border border-slate-200">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-700" id="product-selector-count">商品列表</p>
                            <div class="flex items-center gap-2">
                                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" id="product-selector-prev">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </button>
                                <span class="min-w-[5.5rem] text-center text-xs font-semibold text-slate-500" id="product-selector-page">1 / 1</span>
                                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" id="product-selector-next">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <div class="min-h-0 flex-1 overflow-y-auto p-3" id="product-selector-list"></div>
                    </div>

                    <aside class="flex min-h-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                        <div class="border-b border-slate-200 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-800">已选择</p>
                            <p class="mt-1 text-xs text-slate-500" id="product-selector-selected-count">0 个商品</p>
                        </div>
                        <div class="min-h-0 flex-1 overflow-y-auto p-3" id="product-selector-selected"></div>
                    </aside>
                </div>
            </section>

            <footer class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-product-selector-close>取消</button>
                <button type="button" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700" id="product-selector-confirm">确认选择</button>
            </footer>
        </div>
    </div>

    <footer class="footer bg-white py-6 border-t border-gray-200">
        <div class="container text-center">
            <p class="text-md text-slate-500">
                Powered by <a href="https://www.shopagg.com" target="_blank" class="text-indigo-500 hover:text-indigo-600 italic font-bold">SHOPAGG</a> v<?= APP_VERSION ?>
            </p>
        </div>
    </footer>

    <?php if (($showNav ?? true) && class_exists(\App\Plugins\PluginRuntime::class)): ?>
        <?= \App\Plugins\PluginRuntime::instance()->adminAssetTags('footer') ?>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/jodit@4.13.5/es2021/jodit.fat.min.js" integrity="sha384-IccFe3rXbed180gRH7yBTP+/cUVVxY0TImIHZ2IPflDaaEY1YoCOVTSZ8a63Sr9J" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>
