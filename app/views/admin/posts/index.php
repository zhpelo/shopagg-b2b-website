<!-- 页面头部 -->
<div class="page-header" style="background: linear-gradient(135deg, #00d1b2 0%, #48c774 100%); box-shadow: 0 10px 40px rgba(0, 209, 178, 0.3);">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div class="flex items-center gap-4">
 <div>
 <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
 <span class="icon mr-2"><i class="fas fa-newspaper"></i></span>
 博客管理
 </h1>
 <p class="mt-1 text-sm text-white/80">共有 <?= count($items) ?> 篇文章</p>
 </div>
 </div>
 <div class="header-actions flex flex-wrap items-center gap-3">
 <a href="<?= url('/admin/post-categories') ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/50 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
 <span class="icon"><i class="fas fa-folder"></i></span>
 <span>管理分类</span>
 </a>
 <a href="<?= url('/admin/posts/create') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-600 shadow-sm transition hover:bg-slate-50">
 <span class="icon"><i class="fas fa-plus"></i></span>
 <span>新建文章</span>
 </a>
 </div>
 </div>
</div>

<?php if (empty($items)): ?>
<!-- 空状态 -->
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
 <div class="empty-state">
 <span class="icon"><i class="fas fa-file-alt"></i></span>
 <p>暂无博客文章</p>
 <a href="<?= url('/admin/posts/create') ?>" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:-translate-y-0.5">
 <span class="icon"><i class="fas fa-plus"></i></span>
 <span>创建第一篇文章</span>
 </a>
 </div>
</div>
<?php else: ?>
<!-- 文章列表 -->
<div class="modern-table">
 <div class="table-container">
 <table class="min-w-full text-sm text-slate-700">
 <thead>
 <tr>
 <th>文章信息</th>
 <th>分类</th>
 <th>状态</th>
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
 <div style="width: 56px; height: 56px; background: <?= $cover ? 'url(' . asset_url($cover) . ') center/cover' : 'linear-gradient(135deg, #00d1b2 0%, #48c774 100%)' ?>; border-radius: 10px; margin-right: 1rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
 <?php if (!$cover): ?>
 <span class="icon text-white"><i class="fas fa-file-alt"></i></span>
 <?php endif; ?>
 </div>
 <div>
 <strong><?= h($row['title']) ?></strong>
 <p class="text-xs text-slate-500">/blog/<?= h($row['slug']) ?></p>
 <?php if (!empty($row['summary'])): ?>
 <p class="mt-1 max-w-[300px] truncate text-xs text-slate-400">
 <?= h(mb_substr($row['summary'], 0, 60)) ?><?= mb_strlen($row['summary']) > 60 ? '...' : '' ?>
 </p>
 <?php endif; ?>
 </div>
 </div>
 </td>
 <td>
 <?php if (!empty($row['category_name'])): ?>
 <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
 <span class="icon is-small"><i class="fas fa-folder"></i></span>
 <span><?= h($row['category_name']) ?></span>
 </span>
 <?php else: ?>
 <span class="text-xs text-slate-400">未分类</span>
 <?php endif; ?>
 </td>
 <td>
 <?php
 $status = $row['status'] ?? 'active';
 $status_map = [
 'draft' => ['草稿', 'bg-amber-50 text-amber-700', 'edit'],
 'active' => ['已发布', 'bg-emerald-50 text-emerald-700', 'check-circle'],
 'inactive' => ['已下架', 'bg-slate-100 text-slate-600', 'arrow-down']
 ];
 $s = $status_map[$status] ?? $status_map['active'];
 ?>
 <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?= $s[1] ?>">
 <span class="icon is-small"><i class="fas fa-<?= $s[2] ?>"></i></span>
 <span><?= $s[0] ?></span>
 </span>
 </td>
 <td>
 <span class="text-xs text-slate-500">
 <span class="icon is-small"><i class="far fa-calendar-alt"></i></span>
 <?= format_date($row['created_at']) ?>
 </span>
 </td>
 <td>
 <div class="flex flex-wrap gap-2">
 <a href="<?= url('/blog/' . h($row['slug'])) ?>" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700 transition hover:bg-cyan-100">
 <span class="icon"><i class="fas fa-eye"></i></span>
 </a>
 <a href="<?= url('/admin/posts/edit?id=' . (int)$row['id']) ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
 <span class="icon"><i class="fas fa-edit"></i></span>
 <span>编辑</span>
 </a>
 <a href="<?= url('/admin/posts/delete?id=' . (int)$row['id']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100" onclick="return confirm('确定要删除该文章吗？此操作不可恢复。')">
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
