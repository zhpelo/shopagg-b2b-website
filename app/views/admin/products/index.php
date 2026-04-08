<!-- 页面头部 -->
<div class="page-header">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-box"></i></span>
                    产品管理
                </h1>
                <p class="mt-1 text-sm text-white/80">共有 <?= count($products) ?> 个产品</p>
            </div>
        </div>
        <div class="header-actions flex items-center gap-3">
            <a href="<?= url('/admin/products/create') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-slate-50">
                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-plus"></i></span>
                <span>添加产品</span>
            </a>
        </div>
    </div>
</div>

<?php if (empty($products)): ?>
    <!-- 空状态 -->
    <div class="card">
        <div class="empty-state">
            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-box-open"></i></span>
            <p>暂无产品记录</p>
            <a href="<?= url('/admin/products/create') ?>" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:-translate-y-0.5">
                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-plus"></i></span>
                <span>添加第一个产品</span>
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- 产品列表 -->
    <div class="admin-table">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-slate-700">
                <thead class="bg-gradient-to-b from-white to-slate-50">
                    <tr>
                        <th>产品信息</th>
                        <th>分类</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $row): ?>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <?php
                                    $images = json_decode($row['images_json'] ?? '[]', true);
                                    $cover = !empty($images) ? $images[0] : null;
                                    ?>
                                    <div class="size-14 rounded-[10px] mr-4 shrink-0 flex items-center justify-center" style="background: <?= $cover ? 'url(' . asset_url($cover) . ') center/cover' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ?>">
                                        <?php if (!$cover): ?>
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
                                <?php
                                $status_map = [
                                    'draft' => ['草稿', 'bg-amber-50 text-amber-700', 'edit'],
                                    'active' => ['已上架', 'bg-emerald-50 text-emerald-700', 'check-circle'],
                                    'inactive' => ['已下架', 'bg-slate-100 text-slate-600', 'arrow-down'],
                                    'archived' => ['已下架', 'bg-slate-100 text-slate-600', 'arrow-down']
                                ];
                                $s = $status_map[$row['status'] ?? 'active'] ?? $status_map['active'];
                                ?>
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?= $s[1] ?>">
                                    <span class="inline-flex h-4 w-4 items-center justify-center mr-1"><i class="fas fa-<?= $s[2] ?>"></i></span>
                                    <?= $s[0] ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-xs text-slate-500">
                                    <span class="inline-flex h-4 w-4 items-center justify-center"><i class="far fa-calendar-alt"></i></span>
                                    <?= format_date($row['created_at']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= url('/product/' . h($row['slug'])) ?>" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900" title="预览">
                                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= url('/admin/products/edit?id=' . (int)$row['id']) ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-edit"></i></span>
                                        <span>编辑</span>
                                    </a>
                                    <a href="<?= url('/admin/products/delete?id=' . (int)$row['id']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100" data-confirm-message="确定要删除该产品吗？此操作不可恢复。">
                                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-trash-alt"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>