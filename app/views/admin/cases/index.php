<?php
// 根据label设置不同的颜色主题
$theme_colors = [
 '案例' => ['gradient' => 'linear-gradient(135deg, #17a2b8 0%, #20c997 100%)', 'shadow' => 'rgba(23, 162, 184, 0.3)', 'icon' => 'briefcase'],
 '博客' => ['gradient' => 'linear-gradient(135deg, #28a745 0%, #20c997 100%)', 'shadow' => 'rgba(40, 167, 69, 0.3)', 'icon' => 'pen-nib'],
];
$theme = $theme_colors[$label] ?? ['gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 'shadow' => 'rgba(102, 126, 234, 0.3)', 'icon' => 'file'];
?>

<!-- 页面头部 -->
<div class="page-header" style="background: <?= $theme['gradient'] ?>; box-shadow: 0 10px 40px <?= $theme['shadow'] ?>;">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div class="flex items-center gap-4">
 <div>
 <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
 <span class="icon mr-2"><i class="fas fa-<?= $theme['icon'] ?>"></i></span>
 <?= h($label) ?>管理
 </h1>
 <p class="mt-1 text-sm text-white/80">共有 <?= count($items) ?> 个<?= h($label) ?></p>
 </div>
 </div>
 <div class="header-actions flex items-center gap-3">
 <a href="<?= url("/admin/cases/create") ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 shadow-sm transition hover:bg-slate-50">
 <span class="icon"><i class="fas fa-plus"></i></span>
 <span>新建<?= h($label) ?></span>
 </a>
 </div>
 </div>
</div>

<?php if (empty($items)): ?>
<!-- 空状态 -->
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
 <div class="empty-state">
 <span class="icon"><i class="fas fa-<?= $theme['icon'] ?>"></i></span>
 <p>暂无<?= h($label) ?>记录</p>
 <a href="<?= url('/admin/cases/create') ?>" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition hover:-translate-y-0.5">
 <span class="icon"><i class="fas fa-plus"></i></span>
 <span>创建第一个<?= h($label) ?></span>
 </a>
 </div>
</div>
<?php else: ?>
<!-- 列表 -->
<div class="modern-table">
 <div class="table-container">
 <table class="min-w-full text-sm text-slate-700">
 <thead>
 <tr>
 <th>标题</th>
 <th>别名 (Slug)</th>
 <th>创建时间</th>
 <th>操作</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($items as $row): ?>
 <tr>
 <td>
 <div class="flex items-center">
 <?php $cover = $row['cover'] ?? ''; ?>
 <div style="width: 44px; height: 44px; background: <?= $cover ? 'url(' . asset_url($cover) . ') center/cover' : $theme['gradient'] ?>; border-radius: 10px; margin-right: 1rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
 <?php if (!$cover): ?>
 <span class="icon text-white"><i class="fas fa-<?= $theme['icon'] ?>"></i></span>
 <?php endif; ?>
 </div>
 <div>
 <strong><?= h($row['title']) ?></strong>
 <?php if (!empty($row['summary'])): ?>
 <p class="max-w-[300px] truncate text-xs text-slate-500">
 <?= h($row['summary']) ?>
 </p>
 <?php endif; ?>
 </div>
 </div>
 </td>
 <td>
 <code style="background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.8125rem;"><?= h($row['slug']) ?></code>
 </td>
 <td>
 <span class="text-xs text-slate-500">
 <span class="icon is-small"><i class="far fa-calendar-alt"></i></span>
 <?= format_date($row['created_at']) ?>
 </span>
 </td>
 <td>
 <div class="flex flex-wrap gap-2">
 <a href="<?= url('/admin/cases/edit?id='. intval($row['id']) ) ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
 <span class="icon"><i class="fas fa-edit"></i></span>
 <span>编辑</span>
 </a>
 <a href="<?= url('/admin/cases/delete?id='. intval($row['id']) ) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100" onclick="return confirm('确定要删除该<?= h($label) ?>吗？此操作不可恢复。')">
 <span class="icon"><i class="fas fa-trash-alt"></i></span>
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
