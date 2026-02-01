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
<div class="page-header animate-in" style="background: <?= $theme['gradient'] ?>; box-shadow: 0 10px 40px <?= $theme['shadow'] ?>;">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i></span>
                    <?= $isEdit ? '编辑' : '新建' ?><?= h($label) ?>
                </h1>
                <p class="subtitle is-6"><?= $isEdit ? '修改' . h($label) . '内容' : '创建新的' . h($label) ?></p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="<?= url('/admin/cases') ?>" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<form method="post" action="<?= h(url($action)) ?>" class="modern-form">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    
    <div class="columns">
        <div class="column is-8">
            <!-- 基本信息 -->
            <div class="admin-card mb-5 animate-in delay-1" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box <?= $theme['box'] ?>"><i class="fas fa-info-circle"></i></span>
                    基本信息
                </div>
                
                <div class="columns">
                    <div class="column is-8">
                        <div class="field">
                            <label class="label">标题</label>
                            <div class="control">
                                <input class="input" name="title" value="<?= h($item['title'] ?? '') ?>" placeholder="输入<?= h($label) ?>标题" required>
                            </div>
                        </div>
                    </div>
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">别名 (Slug)</label>
                            <div class="control">
                                <input class="input" name="slug" value="<?= h($item['slug'] ?? '') ?>" placeholder="auto-generate">
                            </div>
                            <p class="help">留空自动生成</p>
                        </div>
                    </div>
                </div>
                
                <div class="field">
                    <label class="label">摘要</label>
                    <div class="control">
                        <textarea class="textarea" name="summary" rows="3" placeholder="简短描述<?= h($label) ?>内容"><?= h($item['summary'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- 内容编辑器 -->
            <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box <?= $theme['box'] ?>"><i class="fas fa-edit"></i></span>
                    详细内容
                </div>
                
                <div class="field">
                    <div class="control">
                        <textarea id="content-input" name="content" style="display:none"><?= h(process_rich_text($item['content'] ?? '')) ?></textarea>
                        <div id="quill-editor" style="min-height: 400px; background: white;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="column is-4">
            <!-- 封面图片 -->
            <div class="admin-card mb-5 animate-in delay-1" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box <?= $theme['box'] ?>"><i class="fas fa-image"></i></span>
                    封面图片
                </div>
                <div class="field">
                    <div class="control">
                        <input type="hidden" name="cover" id="case-cover-input" value="<?= h($item['cover'] ?? '') ?>">
                        <div id="case-cover-preview-wrap" class="mb-3 <?= empty($item['cover'] ?? '') ? 'is-hidden' : '' ?>">
                            <figure class="image is-3by2" style="border-radius: 8px; overflow: hidden; max-width: 100%;">
                                <img id="case-cover-preview" src="<?= asset_url(h($item['cover'] ?? '')) ?>" alt="封面预览" style="object-fit: cover; width: 100%; height: 100%;">
                            </figure>
                            <button type="button" id="case-cover-clear-btn" class="button is-small is-light is-danger mt-2">清除封面</button>
                        </div>
                        <div class="buttons">
                            <button type="button" id="case-cover-select-btn" class="button is-light">
                                <span class="icon"><i class="fas fa-image"></i></span>
                                <span>从媒体库选择</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 发布 -->
            <div class="admin-card mb-5 animate-in delay-1" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box <?= $theme['box'] ?>"><i class="fas fa-paper-plane"></i></span>
                    发布
                </div>
                
                <div class="content is-size-7 has-text-grey mb-4">
                    <p>
                        <span class="icon is-small"><i class="far fa-clock"></i></span>
                        <?= $isEdit ? '上次修改: ' . format_date($item['updated_at'] ?? $item['created_at']) : '准备发布新' . h($label) ?>
                    </p>
                </div>
                
                <button type="submit" class="button is-<?= $theme['box'] ?> is-fullwidth">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= $isEdit ? '保存修改' : '发布' . h($label) ?></span>
                </button>
                
                <a href="<?= url('/admin/cases') ?>" class="button is-light is-fullwidth mt-2">
                    <span class="icon"><i class="fas fa-times"></i></span>
                    <span>取消</span>
                </a>
            </div>

            <!-- SEO 设置 -->
            <div class="admin-card mb-5 animate-in delay-2" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box success"><i class="fas fa-search"></i></span>
                    SEO 设置
                </div>
                <p class="is-size-7 has-text-grey mb-3">留空则使用标题和摘要</p>
                
                <div class="field">
                    <label class="label is-size-7">SEO 标题</label>
                    <div class="control">
                        <input class="input" name="seo_title" value="<?= h($item['seo_title'] ?? '') ?>" placeholder="页面标题">
                    </div>
                </div>
                <div class="field">
                    <label class="label is-size-7">SEO 关键词</label>
                    <div class="control">
                        <input class="input" name="seo_keywords" value="<?= h($item['seo_keywords'] ?? '') ?>" placeholder="关键词1, 关键词2">
                    </div>
                </div>
                <div class="field">
                    <label class="label is-size-7">SEO 描述</label>
                    <div class="control">
                        <textarea class="textarea" name="seo_description" rows="2" placeholder="页面描述"><?= h($item['seo_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- 提示 -->
            <div class="admin-card animate-in delay-2" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box warning"><i class="fas fa-lightbulb"></i></span>
                    写作提示
                </div>
                <div class="content is-size-7 has-text-grey">
                    <ul style="margin-left: 0;">
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
                    if (coverPreviewWrap) coverPreviewWrap.classList.remove('is-hidden');
                }, false);
            }
        });
    }
    if (coverClearBtn && coverInput && coverPreview && coverPreviewWrap) {
        coverClearBtn.addEventListener('click', function() {
            coverInput.value = '';
            coverPreview.src = '';
            coverPreviewWrap.classList.add('is-hidden');
        });
    }
});
</script>
