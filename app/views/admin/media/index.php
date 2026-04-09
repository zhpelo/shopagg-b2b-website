<?php
$listing = $listing ?? [];
$summary = $summary ?? [];
$currentDir = $listing['directory'] ?? '';
$breadcrumbs = $listing['breadcrumbs'] ?? [];
$folders = $listing['folders'] ?? [];
$files = $listing['files'] ?? [];
$folderTree = $listing['folder_tree'] ?? [];
$currentStats = $listing['current_stats'] ?? ['folder_count' => 0, 'file_count' => 0, 'item_count' => 0, 'total_size_formatted' => '0 B'];
$filters = $filters ?? ['search' => '', 'type' => 'all', 'sort' => 'date_desc'];
$searchKeyword = (string)($filters['search'] ?? '');
$typeFilter = (string)($filters['type'] ?? 'all');
$sortFilter = (string)($filters['sort'] ?? 'date_desc');
$parentDir = $currentDir === '' ? '' : dirname($currentDir);
$parentDir = $parentDir === '.' ? '' : $parentDir;

$buildMediaUrl = static function (string $dir) use ($searchKeyword, $typeFilter, $sortFilter): string {
    $query = ['dir' => $dir];
    if ($searchKeyword !== '') {
        $query['search'] = $searchKeyword;
    }
    if ($typeFilter !== 'all') {
        $query['type'] = $typeFilter;
    }
    if ($sortFilter !== 'date_desc') {
        $query['sort'] = $sortFilter;
    }

    return url('/admin/media?' . http_build_query($query));
};

$renderTree = static function (array $nodes) use (&$renderTree, $buildMediaUrl): string {
    if ($nodes === []) {
        return '';
    }

    ob_start();
    echo '<ul class="explorer-tree-list">';
    foreach ($nodes as $node) {
        $classes = ['explorer-tree-node'];
        if (!empty($node['is_current'])) {
            $classes[] = 'is-current';
        }
        if (!empty($node['is_ancestor'])) {
            $classes[] = 'is-ancestor';
        }

        echo '<li class="' . h(implode(' ', $classes)) . '">';
        echo '<a href="' . h($buildMediaUrl((string)($node['directory'] ?? ''))) . '" class="explorer-tree-link">';
        echo '<span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-folder"></i></span>';
        echo '<span>' . h((string)($node['name'] ?? '')) . '</span>';
        echo '</a>';
        echo $renderTree((array)($node['children'] ?? []));
        echo '</li>';
    }
    echo '</ul>';

    return (string)ob_get_clean();
};
?>

<div class="page-header bg-gradient-to-br from-blue-600 to-sky-500 shadow-[0_10px_40px_rgba(31,111,235,0.28)]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="flex items-center gap-3 text-xl font-bold text-white sm:text-2xl">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/16 text-white">
                    <i class="fas fa-folder-open"></i>
                </span>
                <span>媒体资源管理器</span>
            </h1>
            <p class="mt-2 max-w-3xl text-sm text-white/80">你可以在这里管理和浏览你的媒体文件，包括图片、视频和其他文件类型。</p>
        </div>
        <div class="header-actions">
            <a href="<?= url('/admin/media') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-sky-700 shadow-sm transition hover:bg-sky-50">
                <i class="fas fa-home text-xs"></i>
                <span>返回根目录</span>
            </a>
        </div>
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 mb-6">
    <div>
        <div class="card p-5">
            <div class="stat-mini">
                <div class="icon-box" style="background: var(--info-gradient);">
                    <i class="fas fa-file-image"></i>
                </div>
                <div class="stat-info">
                    <div class="value"><?= number_format((int)($summary['image_count'] ?? 0)) ?></div>
                    <div class="label">图片数量</div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="card p-5">
            <div class="stat-mini">
                <div class="icon-box" style="background: var(--warning-gradient)">
                    <i class="fas fa-video text-gray-800"></i>
                </div>
                <div class="stat-info">
                    <div class="value"><?= number_format((int)($summary['video_count'] ?? 0)) ?></div>
                    <div class="label">视频数量</div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="card p-5">
            <div class="stat-mini">
                <div class="icon-box" style="background: var(--success-gradient);">
                    <i class="fas fa-database"></i>
                </div>
                <div class="stat-info">
                    <div class="value"><?= h((string)($summary['total_size_formatted'] ?? '0 B')) ?></div>
                    <div class="label">总存储占用</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="flex items-start justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm mb-6">
        <span><?= h($_GET['success']) ?></span>
        <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full text-emerald-500 transition hover:bg-emerald-100" data-dismiss-parent>
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="flex items-start justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
        <span><?= h($_GET['error']) ?></span>
        <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full text-rose-500 transition hover:bg-rose-100" data-dismiss-parent>
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
<?php endif; ?>

<form method="post" action="<?= url('/admin/media/delete') ?>" id="media-bulk-delete-form" class="hidden">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="dir" value="<?= h($currentDir) ?>">
</form>

<form method="post" action="<?= url('/admin/media/delete') ?>" id="single-media-delete-form" class="hidden">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="dir" value="<?= h($currentDir) ?>">
    <input type="hidden" name="path" id="single-media-delete-path" value="">
</form>

<form method="post" action="<?= url('/admin/media/folder/create') ?>" id="page-media-create-folder-form" class="hidden">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="dir" value="<?= h($currentDir) ?>">
    <input type="hidden" name="folder_name" id="page-media-create-folder-name" value="">
</form>

<form method="post" action="<?= url('/admin/media/upload') ?>" enctype="multipart/form-data" id="page-media-upload-form" class="hidden">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="dir" value="<?= h($currentDir) ?>">
    <input type="file" name="media_files[]" id="page-media-upload-input" multiple accept="image/*,video/mp4,video/webm,video/ogg,video/quicktime">
</form>

<form method="post" action="<?= url('/admin/media/folder/delete') ?>" id="page-media-folder-delete-form" class="hidden">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="directory" id="page-media-folder-delete-dir" value="">
    <input type="hidden" name="parent_dir" value="<?= h($currentDir) ?>">
</form>

<div class="card explorer-shell">
    <div class="explorer-topbar">
        <div class="explorer-topbar-left">
            <span class="inline-flex h-5 w-5 items-center justify-center explorer-app-icon"><i class="fas fa-photo-video"></i></span>
            <strong class="explorer-app-title">媒体库</strong>
            <div class="explorer-nav-buttons flex items-center gap-2">
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50" data-history-nav="back">
                    <i class="fas fa-arrow-left text-xs"></i>
                </button>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50" data-history-nav="forward">
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
                <a href="<?= h($buildMediaUrl($parentDir)) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                    <i class="fas fa-level-up-alt text-xs"></i>
                </a>
            </div>
        </div>
        <div class="explorer-breadcrumbs">
            <nav aria-label="breadcrumbs">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <li class="flex items-center gap-2">
                            <?php if ($index === count($breadcrumbs) - 1): ?>
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-medium text-slate-700" aria-current="page"><?= h($crumb['name']) ?></span>
                            <?php else: ?>
                                <a href="<?= h($buildMediaUrl((string)$crumb['directory'])) ?>" class="rounded-lg px-2.5 py-1 transition hover:bg-slate-100 hover:text-slate-700"><?= h($crumb['name']) ?></a>
                                <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>
    </div>

    <div class="explorer-toolbar">
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-sky-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600" id="page-media-upload-btn">
                <i class="fas fa-upload text-xs"></i>
                <span>上传文件</span>
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50" id="page-media-new-folder-btn">
                <i class="fas fa-folder-plus text-xs"></i>
                <span>新建文件夹</span>
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50" id="page-media-delete-btn" disabled>
                <i class="fas fa-trash text-xs"></i>
                <span id="page-media-delete-label">删除</span>
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-400" disabled>
                <i class="fas fa-i-cursor text-xs"></i>
                <span>重命名</span>
            </button>
            <a href="<?= h($buildMediaUrl($currentDir)) ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                <i class="fas fa-sync-alt text-xs"></i>
                <span>刷新</span>
            </a>
        </div>

        <form method="get" action="<?= url('/admin/media') ?>" class="explorer-filter-bar">
            <input type="hidden" name="dir" value="<?= h($currentDir) ?>">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" type="text" name="search" value="<?= h($searchKeyword) ?>" placeholder="搜索当前目录或原始文件名">
                </div>
                <select name="type" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                    <option value="all" <?= $typeFilter === 'all' ? 'selected' : '' ?>>全部</option>
                    <option value="image" <?= $typeFilter === 'image' ? 'selected' : '' ?>>图片</option>
                    <option value="video" <?= $typeFilter === 'video' ? 'selected' : '' ?>>视频</option>
                </select>
                <select name="sort" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                    <option value="date_desc" <?= $sortFilter === 'date_desc' ? 'selected' : '' ?>>最新上传</option>
                    <option value="date_asc" <?= $sortFilter === 'date_asc' ? 'selected' : '' ?>>最早上传</option>
                    <option value="name_asc" <?= $sortFilter === 'name_asc' ? 'selected' : '' ?>>文件名 A-Z</option>
                    <option value="name_desc" <?= $sortFilter === 'name_desc' ? 'selected' : '' ?>>文件名 Z-A</option>
                    <option value="type_asc" <?= $sortFilter === 'type_asc' ? 'selected' : '' ?>>类型升序</option>
                    <option value="type_desc" <?= $sortFilter === 'type_desc' ? 'selected' : '' ?>>类型降序</option>
                </select>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100">搜索</button>
            </div>
        </form>
    </div>

    <div class="explorer-main">
        <aside class="explorer-sidebar">
            <div class="explorer-tree">
                <a href="<?= h($buildMediaUrl('')) ?>" class="explorer-tree-root <?= $currentDir === '' ? 'is-current' : '' ?>">
                    <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-hdd"></i></span>
                    <span>我的媒体</span>
                </a>
                <?= $renderTree($folderTree) ?>
            </div>
        </aside>

        <section class="explorer-content">
            <div class="explorer-location-bar">
                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-map-marker-alt"></i></span>
                <span>当前位置：<?= h($listing['directory_display'] ?? '/uploads') ?></span>
            </div>

            <?php if (!empty($folders)): ?>
                <div class="explorer-folder-strip">
                    <?php foreach ($folders as $folder): ?>
                        <div class="explorer-folder-chip-wrap inline-flex items-center gap-0">
                            <a href="<?= h($buildMediaUrl((string)$folder['directory'])) ?>" class="explorer-folder-chip rounded-r-none mr-0">
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-folder"></i></span>
                                <span><?= h($folder['name']) ?></span>
                                <small><?= (int)$folder['item_count'] ?> 项</small>
                            </a>
                            <button type="button" class="js-folder-delete-btn inline-flex h-full items-center justify-center rounded-r-xl border border-l-0 border-slate-200 bg-white px-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-500" data-folder-dir="<?= h((string)$folder['directory']) ?>" data-folder-name="<?= h($folder['name']) ?>" data-folder-count="<?= (int)$folder['item_count'] ?>" title="删除文件夹">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="explorer-file-header">
                <label class="explorer-check-cell">
                    <input type="checkbox" id="page-media-toggle-all">
                </label>
                <div>名称</div>
                <div>类型</div>
                <div>大小</div>
                <div>修改时间</div>
                <div>操作</div>
            </div>

            <div class="explorer-file-list" id="page-media-file-list">
                <?php if (empty($files)): ?>
                    <div class="explorer-empty-state">
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fas fa-folder-open text-xl"></i></span>
                        <p class="mt-4 text-sm">当前目录下没有符合筛选条件的媒体文件。</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($files as $file): ?>
                    <div
                        class="explorer-file-row"
                        data-file-path="<?= h($file['public_path']) ?>"
                        data-explorer-file='<?= h(json_encode([
                                                        'name' => $file['original_name'] ?: $file['name'],
                                                        'path' => $file['public_path'],
                                                        'url' => asset_url($file['public_path']),
                                                        'type' => $file['type'],
                                                        'size' => $file['size_formatted'],
                                                        'date' => $file['date'],
                                                        'dimensions' => $file['dimensions'] ?? '',
                                                        'storage_name' => $file['storage_name'] ?? '',
                                                        'directory' => $file['directory'] ?? '',
                                                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'>
                        <label class="explorer-check-cell" >
                            <input type="checkbox" class="js-page-media-checkbox" name="paths[]" form="media-bulk-delete-form" value="<?= h($file['public_path']) ?>">
                        </label>
                        <div class="explorer-name-cell">
                            <div class="explorer-thumb">
                                <?php if ($file['is_image']): ?>
                                    <img src="<?= asset_url($file['public_path']) ?>" alt="<?= h($file['original_name'] ?: $file['name']) ?>" loading="lazy">
                                <?php elseif ($file['is_video']): ?>
                                    <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-video"></i></span>
                                <?php else: ?>
                                    <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-file"></i></span>
                                <?php endif; ?>
                            </div>
                            <div class="explorer-name-meta">
                                <strong title="<?= h($file['original_name'] ?: $file['name']) ?>"><?= h($file['original_name'] ?: $file['name']) ?></strong>
                                <span>存储名：<?= h($file['storage_name'] ?? $file['name']) ?></span>
                            </div>
                        </div>
                        <div><?= $file['is_video'] ? '视频' : ($file['is_image'] ? '图片' : '文件') ?></div>
                        <div><?= h($file['size_formatted']) ?></div>
                        <div><?= h($file['date']) ?></div>
                        <div class="explorer-actions" >
                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700" data-copy-path="<?= h($file['public_path']) ?>">
                                <i class="fas fa-copy text-xs"></i>
                            </button>
                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100 hover:text-rose-600" data-delete-path="<?= h($file['public_path']) ?>">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="explorer-preview-pane">
            <div class="explorer-pane-title">预览 / 属性</div>
            <div class="explorer-preview-empty" id="explorerPreviewEmpty">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fas fa-image text-xl"></i></span>
                <p class="mt-4 text-sm leading-6">选择一个文件后，这里会显示预览和属性。</p>
            </div>
            <div class="explorer-preview-panel hidden" id="explorerPreviewPanel">
                <div class="explorer-preview-box" id="explorerPreviewBox"></div>
                <div class="explorer-preview-meta">
                    <div class="explorer-meta-row">
                        <span>原始文件名</span>
                        <strong id="explorerPreviewName"></strong>
                    </div>
                    <div class="explorer-meta-row">
                        <span>存储文件名</span>
                        <strong id="explorerPreviewStorage"></strong>
                    </div>
                    <div class="explorer-meta-row">
                        <span>类型</span>
                        <strong id="explorerPreviewType"></strong>
                    </div>
                    <div class="explorer-meta-row">
                        <span>所在目录</span>
                        <strong id="explorerPreviewDirectory"></strong>
                    </div>
                    <div class="explorer-meta-row">
                        <span>大小 / 尺寸</span>
                        <strong id="explorerPreviewSize"></strong>
                    </div>
                    <div class="explorer-meta-row">
                        <span>修改时间</span>
                        <strong id="explorerPreviewDate"></strong>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">路径</label>
                        <div class="flex gap-2">
                            <input class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600 outline-none" id="explorerPreviewPath" type="text" readonly>
                            <button type="button" class="inline-flex items-center justify-center rounded-xl bg-sky-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-600" data-copy-target="#explorerPreviewPath">
                                复制
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="explorer-statusbar">
        <span id="page-media-selected-count">已选择 0 个文件</span>
        <span>当前目录 <?= (int)($currentStats['item_count'] ?? 0) ?> 个项目</span>
        <span>图片 <?= (int)($currentStats['file_count'] ?? 0) ?> 个文件</span>
        <span>总大小 <?= h((string)($currentStats['total_size_formatted'] ?? '0 B')) ?></span>
    </div>
</div>

