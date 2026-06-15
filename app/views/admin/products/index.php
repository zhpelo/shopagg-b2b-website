<?php
$filters = $filters ?? [];
$categories = $categories ?? [];
$counts = array_merge([
    'all' => 0,
    'active' => 0,
    'inactive' => 0,
    'draft' => 0,
    'trash' => 0,
], $counts ?? []);
$returnPath = $returnPath ?? '/admin/products';
$isTrash = !empty($filters['trash']);
$searchKeyword = (string)($filters['q'] ?? '');
$currentStatus = (string)($filters['status'] ?? '');
$currentCategoryId = (int)($filters['category_id'] ?? 0);
$currentSort = (string)($filters['sort'] ?? '');

$buildProductsUrl = static function (array $overrides = []) use ($filters): string {
    $query = array_merge([
        'q' => (string)($filters['q'] ?? ''),
        'status' => (string)($filters['status'] ?? ''),
        'category_id' => (int)($filters['category_id'] ?? 0),
        'sort' => (string)($filters['sort'] ?? ''),
        'trash' => !empty($filters['trash']) ? '1' : '',
    ], $overrides);

    $query = array_filter($query, static fn($value): bool => $value !== '' && $value !== 0 && $value !== '0' && $value !== false && $value !== null);
    return url('/admin/products' . ($query ? '?' . http_build_query($query) : ''));
};

$statusMap = [
    'draft' => ['草稿', 'bg-amber-50 text-amber-700', 'edit'],
    'active' => ['已上架', 'bg-emerald-50 text-emerald-700', 'check-circle'],
    'inactive' => ['已下架', 'bg-slate-100 text-slate-600', 'arrow-down'],
    'archived' => ['已下架', 'bg-slate-100 text-slate-600', 'arrow-down'],
];
?>

<!-- 页面头部 -->
<div class="page-header">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-box"></i></span>
                    <?= $isTrash ? '产品回收站' : '产品管理' ?>
                </h1>
                <p class="mt-1 text-sm text-white/80">
                    当前显示 <?= count($products) ?> 个产品<?= $isTrash ? '，回收站共 ' . (int)$counts['trash'] . ' 个' : '，正常产品共 ' . (int)$counts['all'] . ' 个' ?>
                </p>
            </div>
        </div>
        <div class="header-actions flex flex-wrap items-center gap-3">
            <?php if ($isTrash): ?>
                <a href="<?= url('/admin/products') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-slate-50">
                    <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-arrow-left"></i></span>
                    <span>返回产品列表</span>
                </a>
            <?php else: ?>
                <a href="<?= h($buildProductsUrl(['trash' => '1', 'status' => ''])) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/25 transition hover:bg-white/20">
                    <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-trash-restore"></i></span>
                    <span>回收站 (<?= (int)$counts['trash'] ?>)</span>
                </a>
                <a href="<?= url('/admin/products/create') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-slate-50">
                    <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-plus"></i></span>
                    <span>添加产品</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="mb-5 flex flex-wrap gap-2">
    <a href="<?= url('/admin/products') ?>" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition <?= !$isTrash && $currentStatus === '' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-white text-slate-600 hover:bg-slate-50' ?>">
        全部 <span class="rounded-full bg-white/20 px-2 text-xs"><?= (int)$counts['all'] ?></span>
    </a>
    <a href="<?= h($buildProductsUrl(['status' => 'active', 'trash' => ''])) ?>" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition <?= !$isTrash && $currentStatus === 'active' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white text-slate-600 hover:bg-slate-50' ?>">
        已上架 <span class="rounded-full bg-white/20 px-2 text-xs"><?= (int)$counts['active'] ?></span>
    </a>
    <a href="<?= h($buildProductsUrl(['status' => 'inactive', 'trash' => ''])) ?>" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition <?= !$isTrash && $currentStatus === 'inactive' ? 'bg-slate-700 text-white shadow-lg shadow-slate-500/20' : 'bg-white text-slate-600 hover:bg-slate-50' ?>">
        已下架 <span class="rounded-full bg-white/20 px-2 text-xs"><?= (int)$counts['inactive'] ?></span>
    </a>
    <a href="<?= h($buildProductsUrl(['status' => 'draft', 'trash' => ''])) ?>" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition <?= !$isTrash && $currentStatus === 'draft' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'bg-white text-slate-600 hover:bg-slate-50' ?>">
        草稿 <span class="rounded-full bg-white/20 px-2 text-xs"><?= (int)$counts['draft'] ?></span>
    </a>
    <a href="<?= h($buildProductsUrl(['trash' => '1', 'status' => ''])) ?>" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition <?= $isTrash ? 'bg-rose-600 text-white shadow-lg shadow-rose-500/20' : 'bg-white text-slate-600 hover:bg-slate-50' ?>">
        回收站 <span class="rounded-full bg-white/20 px-2 text-xs"><?= (int)$counts['trash'] ?></span>
    </a>
</div>

<form method="get" action="<?= url('/admin/products') ?>" class="card mb-5 p-5">
    <?php if ($isTrash): ?>
        <input type="hidden" name="trash" value="1">
    <?php endif; ?>
    <div class="grid gap-3 lg:grid-cols-[minmax(0,1.5fr)_minmax(150px,0.7fr)_minmax(180px,0.8fr)_minmax(180px,0.8fr)_auto] lg:items-end">
        <label class="space-y-2">
            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">搜索产品</span>
            <input type="search" name="q" value="<?= h($searchKeyword) ?>" placeholder="标题、Slug、摘要、供应商、标签" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
        </label>
        <?php if (!$isTrash): ?>
            <label class="space-y-2">
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">状态</span>
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">全部状态</option>
                    <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>已上架</option>
                    <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>已下架</option>
                    <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>草稿</option>
                </select>
            </label>
        <?php endif; ?>
        <label class="space-y-2">
            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">分类</span>
            <select name="category_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                <option value="0">全部分类</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= $currentCategoryId === (int)$cat['id'] ? 'selected' : '' ?>>
                        <?= h($cat['display_name'] ?? $cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">排序</span>
            <select name="sort" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                <option value="" <?= $currentSort === '' ? 'selected' : '' ?>>默认排序</option>
                <option value="latest" <?= $currentSort === 'latest' ? 'selected' : '' ?>>最新创建</option>
                <option value="oldest" <?= $currentSort === 'oldest' ? 'selected' : '' ?>>最早创建</option>
                <option value="title_asc" <?= $currentSort === 'title_asc' ? 'selected' : '' ?>>标题 A-Z</option>
                <option value="title_desc" <?= $currentSort === 'title_desc' ? 'selected' : '' ?>>标题 Z-A</option>
                <option value="updated_desc" <?= $currentSort === 'updated_desc' ? 'selected' : '' ?>>最近更新</option>
                <option value="status_asc" <?= $currentSort === 'status_asc' ? 'selected' : '' ?>>按状态</option>
                <option value="category_asc" <?= $currentSort === 'category_asc' ? 'selected' : '' ?>>按分类</option>
                <?php if ($isTrash): ?>
                    <option value="deleted_desc" <?= $currentSort === 'deleted_desc' ? 'selected' : '' ?>>最近删除</option>
                <?php endif; ?>
            </select>
        </label>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
                <i class="fas fa-search text-xs"></i>
                筛选
            </button>
            <a href="<?= $isTrash ? url('/admin/products?trash=1') : url('/admin/products') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                重置
            </a>
        </div>
    </div>
</form>

<?php if (empty($products)): ?>
    <!-- 空状态 -->
    <div class="card">
        <div class="empty-state">
            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-box-open"></i></span>
            <p><?= $isTrash ? '回收站暂无产品' : '没有找到符合条件的产品' ?></p>
            <?php if (!$isTrash): ?>
                <a href="<?= url('/admin/products/create') ?>" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:-translate-y-0.5">
                    <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-plus"></i></span>
                    <span>添加产品</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <form method="post" action="<?= url('/admin/products/bulk') ?>" class="admin-table">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="return_to" value="<?= h($returnPath) ?>">

        <div class="flex flex-col gap-3 border-b border-slate-100 bg-white px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="text-sm text-slate-500">勾选产品后执行批量操作</div>
            <div class="flex flex-wrap gap-2">
                <?php if ($isTrash): ?>
                    <button type="submit" name="bulk_action" value="restore" data-bulk-action class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fas fa-undo text-xs"></i>
                        批量恢复
                    </button>
                    <button type="submit" name="bulk_action" value="permanent_delete" data-bulk-action data-confirm-message="确定要永久删除所选产品吗？此操作不可恢复。" class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fas fa-trash-alt text-xs"></i>
                        批量永久删除
                    </button>
                <?php else: ?>
                    <button type="submit" name="bulk_action" value="activate" data-bulk-action class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fas fa-arrow-up text-xs"></i>
                        批量上架
                    </button>
                    <button type="submit" name="bulk_action" value="deactivate" data-bulk-action class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fas fa-arrow-down text-xs"></i>
                        批量下架
                    </button>
                    <button type="submit" name="bulk_action" value="delete" data-bulk-action data-confirm-message="确定要将所选产品移入回收站吗？" class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fas fa-trash-alt text-xs"></i>
                        批量删除
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- 产品列表 -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-slate-700">
                <thead class="bg-gradient-to-b from-white to-slate-50">
                    <tr>
                        <th class="w-12">
                            <label class="inline-flex cursor-pointer items-center">
                                <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-bulk-select-all>
                            </label>
                        </th>
                        <th>产品信息</th>
                        <th>分类</th>
                        <th>状态</th>
                        <th><?= $isTrash ? '删除时间' : '创建时间' ?></th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $row): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="product_ids[]" value="<?= (int)$row['id'] ?>" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-bulk-item>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <?php $cover = (string)($row['cover'] ?? ''); ?>
                                    <div class="size-14 rounded-[10px] mr-4 shrink-0 flex items-center justify-center" style="background: <?= $cover !== '' ? 'url(' . asset_url($cover) . ') center/cover' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ?>">
                                        <?php if ($cover === ''): ?>
                                            <span class="inline-flex h-5 w-5 items-center justify-center text-white"><i class="fas fa-box"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong><?= h($row['title']) ?></strong>
                                        <p class="text-xs text-slate-500">/product/<?= h($row['slug']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($row['category_name'])): ?>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        <span class="inline-flex h-4 w-4 items-center justify-center mr-1"><i class="fas fa-folder"></i></span>
                                        <?= h($row['category_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">未分类</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $s = $statusMap[$row['status'] ?? 'active'] ?? $statusMap['active']; ?>
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?= $s[1] ?>">
                                    <span class="inline-flex h-4 w-4 items-center justify-center mr-1"><i class="fas fa-<?= $s[2] ?>"></i></span>
                                    <?= $s[0] ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-xs text-slate-500">
                                    <span class="inline-flex h-4 w-4 items-center justify-center"><i class="far fa-calendar-alt"></i></span>
                                    <?= format_date($isTrash ? ($row['deleted_at'] ?? '') : ($row['created_at'] ?? '')) ?>
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <?php if ($isTrash): ?>
                                        <a href="<?= url('/admin/products/restore?id=' . (int)$row['id'] . '&return_to=' . rawurlencode($returnPath)) ?>" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-undo"></i></span>
                                            <span>恢复</span>
                                        </a>
                                        <a href="<?= url('/admin/products/permanent-delete?id=' . (int)$row['id'] . '&return_to=' . rawurlencode($returnPath)) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100" data-confirm-message="确定要永久删除该产品吗？此操作不可恢复。">
                                            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-trash-alt"></i></span>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= url('/product/' . h($row['slug'])) ?>" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900" title="预览">
                                            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-eye"></i></span>
                                        </a>
                                        <a href="<?= url('/admin/products/edit?id=' . (int)$row['id']) ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-edit"></i></span>
                                            <span>编辑</span>
                                        </a>
                                        <a href="<?= url('/admin/products/delete?id=' . (int)$row['id'] . '&return_to=' . rawurlencode($returnPath)) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100" data-confirm-message="确定要将该产品移入回收站吗？">
                                            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-trash-alt"></i></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
<?php endif; ?>
