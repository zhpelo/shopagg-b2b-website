<?php
// 根据label设置不同的颜色主题
$theme_colors = [
 '案例' => ['gradient' => 'linear-gradient(135deg, #17a2b8 0%, #20c997 100%)', 'shadow' => 'rgba(23, 162, 184, 0.3)', 'icon' => 'briefcase', 'box' => 'info'],
 '博客' => ['gradient' => 'linear-gradient(135deg, #28a745 0%, #20c997 100%)', 'shadow' => 'rgba(40, 167, 69, 0.3)', 'icon' => 'pen-nib', 'box' => 'success'],
];
$theme = $theme_colors[$label] ?? ['gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 'shadow' => 'rgba(102, 126, 234, 0.3)', 'icon' => 'file', 'box' => 'primary'];
$isEdit = isset($item);
?>

<!-- 页面头部 -->
<div class="page-header" style="background: <?= $theme['gradient'] ?>; box-shadow: 0 10px 40px <?= $theme['shadow'] ?>;">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div class="flex items-center gap-4">
 <div>
 <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
 <span class="icon mr-2"><i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i></span>
 <?= $isEdit ? '编辑' : '新建' ?><?= h($label) ?>
 </h1>
 <p class="mt-1 text-sm text-white/80"><?= $isEdit ? '修改' . h($label) . '内容' : '创建新的' . h($label) ?></p>
 </div>
 </div>
 <div class="header-actions flex items-center gap-3">
 <a href="<?= url('/admin/cases') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-700 shadow-sm transition hover:bg-slate-50">
 <span class="icon"><i class="fas fa-arrow-left"></i></span>
 <span>返回列表</span>
 </a>
 </div>
 </div>
</div>

<form method="post" action="<?= h(url($action)) ?>">
 <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
 
 <div class="grid gap-6 xl:grid-cols-12">
 <div class="xl:col-span-8">
 <!-- 基本信息 -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5" style="padding: 2rem;">
 <div class="section-title">
 <span class="icon-box <?= $theme['box'] ?>"><i class="fas fa-info-circle"></i></span>
 基本信息
 </div>

 <div class="grid gap-4 md:grid-cols-12">
 <label class="block space-y-2 md:col-span-8">
 <span class="text-sm font-medium text-slate-700">标题</span>
 <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" name="title" value="<?= h($item['title'] ?? '') ?>" placeholder="输入<?= h($label) ?>标题" required>
 </label>
 <label class="block space-y-2 md:col-span-4">
 <span class="text-sm font-medium text-slate-700">别名 (Slug)</span>
 <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" name="slug" value="<?= h($item['slug'] ?? '') ?>" placeholder="auto-generate">
 <span class="text-xs text-slate-500">留空自动生成</span>
 </label>
 </div>

 <label class="mt-4 block space-y-2">
 <span class="text-sm font-medium text-slate-700">摘要</span>
 <textarea class="min-h-[110px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" name="summary" rows="3" placeholder="简短描述<?= h($label) ?>内容"><?= h($item['summary'] ?? '') ?></textarea>
 </label>
 </div>

 <!-- 内容编辑器 -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5" style="padding: 2rem;">
 <div class="section-title">
 <span class="icon-box <?= $theme['box'] ?>"><i class="fas fa-edit"></i></span>
 详细内容
 </div>

 <div id="editor-wrapper">
 <textarea id="content-input" name="content" class="js-rich-editor" data-editor-height="420"><?= h(process_rich_text($item['content'] ?? '')) ?></textarea>
 </div>
 </div>
 </div>

 <div class="xl:col-span-4">
 <!-- 封面图片 -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5" style="padding: 1.5rem;">
 <div class="section-title" style="font-size: 1rem;">
 <span class="icon-box <?= $theme['box'] ?>"><i class="fas fa-image"></i></span>
 封面图片
 </div>
 <div class="space-y-3">
 <input type="hidden" name="cover" id="case-cover-input" value="<?= h($item['cover'] ?? '') ?>">
 <div id="case-cover-preview-wrap" class="mb-3 <?= empty($item['cover'] ?? '') ? 'hidden' : '' ?>">
 <figure class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
 <img id="case-cover-preview" src="<?= asset_url(h($item['cover'] ?? '')) ?>" alt="封面预览" class="aspect-[3/2] w-full object-cover">
 </figure>
 <button type="button" id="case-cover-clear-btn" class="mt-2 inline-flex items-center gap-2 rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-100">清除封面</button>
 </div>
 <div class="flex flex-wrap gap-3">
 <button type="button" id="case-cover-select-btn" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
 <span class="icon"><i class="fas fa-image"></i></span>
 <span>从媒体库选择</span>
 </button>
 </div>
 </div>
 </div>

 <!-- 发布 -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5" style="padding: 1.5rem;">
 <div class="section-title" style="font-size: 1rem;">
 <span class="icon-box <?= $theme['box'] ?>"><i class="fas fa-paper-plane"></i></span>
 发布
 </div>
 
 <div class="mb-4 text-xs leading-6 text-slate-500">
 <p class="flex items-center gap-2">
 <span class="icon is-small"><i class="far fa-clock"></i></span>
 <?= $isEdit ? '上次修改: ' . format_date($item['updated_at'] ?? $item['created_at']) : '准备发布新' . h($label) ?>
 </p>
 </div>
 
 <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition hover:-translate-y-0.5">
 <span class="icon"><i class="fas fa-save"></i></span>
 <span><?= $isEdit ? '保存修改' : '发布' . h($label) ?></span>
 </button>
 
 <a href="<?= url('/admin/cases') ?>" class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
 <span class="icon"><i class="fas fa-times"></i></span>
 <span>取消</span>
 </a>
 </div>

 <!-- SEO 设置 -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5" style="padding: 1.5rem;">
 <div class="section-title" style="font-size: 1rem;">
 <span class="icon-box success"><i class="fas fa-search"></i></span>
 SEO 设置
 </div>
 <p class="mb-3 text-xs text-slate-500">留空则使用标题和摘要</p>

 <div class="space-y-4">
 <label class="block space-y-2">
 <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SEO 标题</span>
 <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" name="seo_title" value="<?= h($item['seo_title'] ?? '') ?>" placeholder="页面标题">
 </label>
 <label class="block space-y-2">
 <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SEO 关键词</span>
 <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" name="seo_keywords" value="<?= h($item['seo_keywords'] ?? '') ?>" placeholder="关键词1, 关键词2">
 </label>
 <label class="block space-y-2">
 <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SEO 描述</span>
 <textarea class="min-h-[96px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" name="seo_description" rows="2" placeholder="页面描述"><?= h($item['seo_description'] ?? '') ?></textarea>
 </label>
 </div>
 </div>

 <!-- 提示 -->
 <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" style="padding: 1.5rem;">
 <div class="section-title" style="font-size: 1rem;">
 <span class="icon-box warning"><i class="fas fa-lightbulb"></i></span>
 写作提示
 </div>
 <div class="text-xs leading-6 text-slate-500">
 <ul class="list-disc space-y-1 pl-5">
 <li>使用清晰简洁的标题</li>
 <li>摘要建议控制在 150 字以内</li>
 <li>可以在编辑器中插入图片</li>
 <li>使用标题和列表增加可读性</li>
 </ul>
 </div>
 </div>
 </div>
 </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
 var coverInput = document.getElementById('case-cover-input');
 var coverPreview = document.getElementById('case-cover-preview');
 var coverPreviewWrap = document.getElementById('case-cover-preview-wrap');
 var coverSelectBtn = document.getElementById('case-cover-select-btn');
 var coverClearBtn = document.getElementById('case-cover-clear-btn');
 if (coverInput && coverSelectBtn) {
 coverSelectBtn.addEventListener('click', function() {
 if (typeof openMediaLibrary === 'function') {
 openMediaLibrary(function(url) {
 coverInput.value = url;
 if (coverPreview) coverPreview.src = url;
 if (coverPreviewWrap) coverPreviewWrap.classList.remove('hidden');
 }, false);
 }
 });
 }
 if (coverClearBtn && coverInput && coverPreview && coverPreviewWrap) {
 coverClearBtn.addEventListener('click', function() {
 coverInput.value = '';
 coverPreview.src = '';
 coverPreviewWrap.classList.add('hidden');
 });
 }
});
</script>
