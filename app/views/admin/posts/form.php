<?php
$isEdit = isset($item);
$categories = $categories ?? [];
$contentConfig = $contentConfig ?? [];
$singular = $contentConfig['singular'] ?? '内容';
$icon = $contentConfig['icon'] ?? 'file-lines';
$indexUrl = $contentConfig['index_url'] ?? '/admin/posts';
$showCategories = (bool)($contentConfig['show_categories'] ?? false);
$categoryManageUrl = $contentConfig['category_manage_url'] ?? null;
$categoryManageLabel = $contentConfig['category_manage_label'] ?? '';
$headerStyle = $contentConfig['header_style'] ?? 'background: linear-gradient(135deg, #00d1b2 0%, #48c774 100%); box-shadow: 0 10px 40px rgba(0, 209, 178, 0.3);';
$accentTextClass = $contentConfig['accent_text_class'] ?? 'text-emerald-600';
$accentFocusClass = $contentConfig['accent_focus_class'] ?? 'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100';
$primaryButtonClass = $contentConfig['primary_button_class'] ?? 'bg-gradient-to-r from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/25';
$summaryLabel = $contentConfig['summary_label'] ?? ($singular . '摘要');
$summaryPlaceholder = $contentConfig['summary_placeholder'] ?? ('输入' . $singular . '摘要');
$contentLabel = $contentConfig['content_label'] ?? ($singular . '内容');
$coverLabel = $contentConfig['cover_label'] ?? '封面图片';
$tips = $contentConfig['tips'] ?? [];
?>

<div class="page-header" style="<?= h($headerStyle) ?>">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i></span>
                    <?= h($isEdit ? ($contentConfig['form_edit_title'] ?? ('编辑' . $singular)) : ($contentConfig['form_create_title'] ?? ('新建' . $singular))) ?>
                </h1>
                <p class="mt-1 text-sm text-white/80"><?= h($isEdit ? ($contentConfig['form_intro_edit'] ?? ('修改' . $singular . '内容')) : ($contentConfig['form_intro_create'] ?? ('创建新的' . $singular))) ?></p>
            </div>
        </div>
        <div class="header-actions flex items-center gap-3">
            <a href="<?= url($indexUrl) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold shadow-sm transition hover:bg-slate-50 <?= h($accentTextClass) ?>">
                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<form method="post" action="<?= h(url($action)) ?>">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

    <div class="grid gap-6 xl:grid-cols-12">
        <div class="xl:col-span-8">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box success"><i class="fas fa-<?= h($icon) ?>"></i></span>
                    <?= h($contentConfig['content_section_title'] ?? ($singular . '内容')) ?>
                </div>

                <div class="space-y-5">
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700"><?= h($singular) ?>标题 <span class="text-rose-500">*</span></span>
                        <span class="relative block">
                            <i class="fas fa-heading pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-base text-slate-700 outline-none transition placeholder:text-slate-400 <?= h($accentFocusClass) ?>" name="title" value="<?= h($item['title'] ?? '') ?>" required placeholder="输入<?= h($singular) ?>标题">
                        </span>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">别名 (Slug)</span>
                        <span class="relative block">
                            <i class="fas fa-link pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 <?= h($accentFocusClass) ?>" name="slug" value="<?= h($item['slug'] ?? '') ?>" placeholder="<?= h($contentConfig['slug_placeholder'] ?? 'auto-slug') ?>">
                        </span>
                        <span class="text-xs text-slate-500">用于 URL 的标识符，留空则自动生成</span>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700"><?= h($summaryLabel) ?></span>
                        <textarea class="min-h-[110px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 <?= h($accentFocusClass) ?>" name="summary" rows="3" placeholder="<?= h($summaryPlaceholder) ?>"><?= h($item['summary'] ?? '') ?></textarea>
                    </label>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="content-input"><?= h($contentLabel) ?></label>
                        <div id="editor-wrapper">
                            <textarea id="content-input" name="content" class="js-rich-editor" data-editor-height="420"><?= h(process_rich_text($item['content'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-4">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="section-title">
                    <span class="icon-box info"><i class="fas fa-cog"></i></span>
                    发布设置
                </div>

                <div class="space-y-5">
                    <?php if ($showCategories): ?>
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-slate-700">文章分类</span>
                            <span class="relative block">
                                <i class="fas fa-folder pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <select class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition <?= h($accentFocusClass) ?>" name="category_id">
                                    <option value="0">未分类</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= (int)$cat['id'] ?>" <?= ((int)($item['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                            <?= h($cat['display_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </span>
                            <?php if ($categoryManageUrl): ?>
                                <a href="<?= url($categoryManageUrl) ?>" target="_blank" class="inline-flex items-center gap-2 text-xs font-medium <?= h($accentTextClass) ?> transition opacity-90 hover:opacity-100">
                                    <i class="fas fa-plus-circle text-[11px]"></i>
                                    <span><?= h($categoryManageLabel) ?></span>
                                </a>
                            <?php endif; ?>
                        </label>
                    <?php endif; ?>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">发布状态</span>
                        <select class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition <?= h($accentFocusClass) ?>" name="status">
                            <option value="draft" <?= ($item['status'] ?? '') === 'draft' ? 'selected' : '' ?>>草稿</option>
                            <option value="active" <?= ($item['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>已发布</option>
                            <option value="inactive" <?= ($item['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>已下架</option>
                        </select>
                    </label>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="cover-input"><?= h($coverLabel) ?></label>
                        <input type="hidden" name="cover" id="cover-input" value="<?= h($item['cover'] ?? '') ?>">
                        <div id="cover-preview-wrap" class="mb-3 <?= empty($item['cover'] ?? '') ? 'hidden' : '' ?>">
                            <figure class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                <img id="cover-preview" src="<?= asset_url($item['cover'] ?? '') ?>" alt="封面预览" class="aspect-[3/2] w-full object-cover">
                            </figure>
                            <button type="button" id="cover-clear-btn" class="mt-2 inline-flex items-center gap-2 rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-100">清除封面</button>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" id="post-cover-select-btn" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-image"></i></span>
                                <span>从媒体库选择</span>
                            </button>
                        </div>
                    </div>
                </div>

                <hr style="margin: 1.5rem 0;">

                <div class="space-y-3">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white transition hover:-translate-y-0.5 <?= h($primaryButtonClass) ?>">
                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-save"></i></span>
                        <span><?= h($isEdit ? '保存修改' : ($contentConfig['publish_button_create'] ?? ('发布' . $singular))) ?></span>
                    </button>
                    <a href="<?= url($indexUrl) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-times"></i></span>
                        <span>取消</span>
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="section-title">
                    <span class="icon-box success"><i class="fas fa-search"></i></span>
                    SEO 设置
                </div>
                <p class="mb-3 text-xs text-slate-500">留空则使用标题和摘要</p>

                <div class="space-y-4">
                    <label class="block space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SEO 标题</span>
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 <?= h($accentFocusClass) ?>" name="seo_title" value="<?= h($item['seo_title'] ?? '') ?>" placeholder="页面标题">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SEO 关键词</span>
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 <?= h($accentFocusClass) ?>" name="seo_keywords" value="<?= h($item['seo_keywords'] ?? '') ?>" placeholder="关键词1, 关键词2">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SEO 描述</span>
                        <textarea class="min-h-[96px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 <?= h($accentFocusClass) ?>" name="seo_description" rows="2" placeholder="页面描述"><?= h($item['seo_description'] ?? '') ?></textarea>
                    </label>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" style="padding: 1.5rem;">
                <div class="section-title">
                    <span class="icon-box warning"><i class="fas fa-lightbulb"></i></span>
                    写作提示
                </div>
                <div class="text-xs leading-6 text-slate-500">
                    <ul class="list-disc space-y-1 pl-5">
                        <?php foreach ($tips as $tip): ?>
                            <li><?= h($tip) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
