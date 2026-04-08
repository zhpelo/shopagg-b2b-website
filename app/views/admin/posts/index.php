<?php
$contentConfig = $contentConfig ?? [];
$singular = $contentConfig['singular'] ?? '内容';
$indexUrl = $contentConfig['index_url'] ?? '/admin/posts';
$createUrl = $contentConfig['create_url'] ?? '/admin/posts/create';
$editUrl = $contentConfig['edit_url'] ?? '/admin/posts/edit';
$deleteUrl = $contentConfig['delete_url'] ?? '/admin/posts/delete';
$previewBase = $contentConfig['preview_base'] ?? '/blog/';
$showCategories = (bool)($contentConfig['show_categories'] ?? false);
$categoryManageUrl = $contentConfig['category_manage_url'] ?? null;
$categoryManageLabel = $contentConfig['category_manage_label'] ?? '';
$icon = $contentConfig['icon'] ?? 'file-lines';
$headerStyle = $contentConfig['header_style'] ?? 'background: linear-gradient(135deg, #00d1b2 0%, #48c774 100%); box-shadow: 0 10px 40px rgba(0, 209, 178, 0.3);';
$accentTextClass = $contentConfig['accent_text_class'] ?? 'text-emerald-600';
$accentSoftClass = $contentConfig['accent_soft_class'] ?? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100';
$primaryButtonClass = $contentConfig['primary_button_class'] ?? 'bg-gradient-to-r from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/25';
$listEmptyText = $contentConfig['list_empty_text'] ?? '暂无内容';
$listEmptyAction = $contentConfig['list_empty_action'] ?? ('创建第一个' . $singular);
$countUnit = $contentConfig['count_unit'] ?? '个';
?>

<div class="page-header" style="<?= h($headerStyle) ?>">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-<?= h($icon) ?>"></i></span>
                    <?= h($contentConfig['index_title'] ?? ($singular . '管理')) ?>
                </h1>
                <p class="mt-1 text-sm text-white/80">共有 <?= count($items) ?> <?= h($countUnit) . h($singular) ?></p>
            </div>
        </div>
        <div class="header-actions flex flex-wrap items-center gap-3">
            <?php if ($showCategories && $categoryManageUrl): ?>
                <a href="<?= url($categoryManageUrl) ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/50 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                    <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-folder"></i></span>
                    <span><?= h($categoryManageLabel) ?></span>
                </a>
            <?php endif; ?>
            <a href="<?= url($createUrl) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold shadow-sm transition hover:bg-slate-50 <?= h($accentTextClass) ?>">
                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-plus"></i></span>
                <span>新建<?= h($singular) ?></span>
            </a>
        </div>
    </div>
</div>

<?php if (empty($items)): ?>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="empty-state">
            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-<?= h($icon) ?>"></i></span>
            <p><?= h($listEmptyText) ?></p>
            <a href="<?= url($createUrl) ?>" class="mt-4 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 <?= h($primaryButtonClass) ?>">
                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-plus"></i></span>
                <span><?= h($listEmptyAction) ?></span>
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden [&_thead_th]:sticky [&_thead_th]:top-0 [&_thead_th]:border-b [&_thead_th]:border-slate-200 [&_thead_th]:px-5 [&_thead_th]:py-3 [&_thead_th]:text-left [&_thead_th]:text-xs [&_thead_th]:font-bold [&_thead_th]:uppercase [&_thead_th]:tracking-wider [&_thead_th]:text-slate-500 [&_tbody_td]:px-5 [&_tbody_td]:py-4 [&_tbody_td]:align-middle [&_tbody_td]:border-b [&_tbody_td]:border-slate-100 [&_tbody_tr:last-child_td]:border-b-0 [&_tbody_tr:hover]:bg-slate-50">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-slate-700">
                <thead class="bg-gradient-to-b from-white to-slate-50">
                    <tr>
                        <th><?= h($singular) ?>信息</th>
                        <?php if ($showCategories): ?>
                            <th>分类</th>
                        <?php endif; ?>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $row): ?>
                        <?php
                        $cover = $row['cover'] ?? '';
                        $status = $row['status'] ?? 'active';
                        $statusMap = [
                            'draft' => ['草稿', 'bg-amber-50 text-amber-700', 'edit'],
                            'active' => ['已发布', 'bg-emerald-50 text-emerald-700', 'check-circle'],
                            'inactive' => ['已下架', 'bg-slate-100 text-slate-600', 'arrow-down'],
                        ];
                        $resolvedStatus = $statusMap[$status] ?? $statusMap['active'];
                        $previewUrl = $previewBase ? url($previewBase . $row['slug']) : null;
                        ?>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div style="width: 56px; height: 56px; background: <?= $cover ? 'url(' . asset_url($cover) . ') center/cover' : 'linear-gradient(135deg, #cbd5f5 0%, #94a3b8 100%)' ?>; border-radius: 10px; margin-right: 1rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        <?php if (!$cover): ?>
                                            <span class="inline-flex h-5 w-5 items-center justify-center text-white"><i class="fas fa-<?= h($icon) ?>"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong><?= h($row['title']) ?></strong>
                                        <p class="text-xs text-slate-500"><?= h($row['slug']) ?></p>
                                        <?php if (!empty($row['summary'])): ?>
                                            <p class="mt-1 max-w-[300px] truncate text-xs text-slate-400">
                                                <?= h(mb_substr($row['summary'], 0, 60)) ?><?= mb_strlen($row['summary']) > 60 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <?php if ($showCategories): ?>
                                <td>
                                    <?php if (!empty($row['category_name'])): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="inline-flex h-4 w-4 items-center justify-center"><i class="fas fa-folder"></i></span>
                                            <span><?= h($row['category_name']) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">未分类</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td>
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?= h($resolvedStatus[1]) ?>">
                                    <span class="inline-flex h-4 w-4 items-center justify-center"><i class="fas fa-<?= h($resolvedStatus[2]) ?>"></i></span>
                                    <span><?= h($resolvedStatus[0]) ?></span>
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
                                    <?php if ($previewUrl): ?>
                                        <a href="<?= h($previewUrl) ?>" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700 transition hover:bg-cyan-100">
                                            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-eye"></i></span>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= url($editUrl . '?id=' . (int)$row['id']) ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-edit"></i></span>
                                        <span>编辑</span>
                                    </a>
                                    <a href="<?= url($deleteUrl . '?id=' . (int)$row['id']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100" data-confirm-message="确定要删除该<?= h($singular) ?>吗？此操作不可恢复。">
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
