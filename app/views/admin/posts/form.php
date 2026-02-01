<?php
$isEdit = isset($item);
$categories = $categories ?? [];
?>

<!-- 页面头部 -->
<div class="page-header animate-in" style="background: linear-gradient(135deg, #00d1b2 0%, #48c774 100%); box-shadow: 0 10px 40px rgba(0, 209, 178, 0.3);">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i></span>
                    <?= $isEdit ? '编辑文章' : '新建文章' ?>
                </h1>
                <p class="subtitle is-6"><?= $isEdit ? '修改文章内容' : '创建新的博客文章' ?></p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="<?= url('/admin/posts') ?>" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<form method="post" action="<?= h(url($action)) ?>" class="modern-form">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    
    <div class="columns">
        <!-- 左侧：主要内容 -->
        <div class="column is-8 animate-in delay-1">
            <div class="admin-card" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box success"><i class="fas fa-file-alt"></i></span>
                    文章内容
                </div>
                
                <div class="field">
                    <label class="label">文章标题 <span class="has-text-danger">*</span></label>
                    <div class="control has-icons-left">
                        <input class="input is-medium" name="title" value="<?= h($item['title'] ?? '') ?>" required placeholder="输入文章标题">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-heading"></i>
                        </span>
                    </div>
                </div>
                
                <div class="field">
                    <label class="label">别名 (Slug)</label>
                    <div class="control has-icons-left">
                        <input class="input" name="slug" value="<?= h($item['slug'] ?? '') ?>" placeholder="article-slug">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-link"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">用于URL的标识符，留空则自动生成</p>
                </div>

                <div class="field">
                    <label class="label">文章摘要</label>
                    <div class="control">
                        <textarea class="textarea" name="summary" rows="3" placeholder="输入文章摘要（用于列表展示和SEO）"><?= h($item['summary'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="field">
                    <label class="label">文章内容</label>
                    <div class="control">
                        <textarea id="content-input" name="content" style="display:none"><?= h(process_rich_text($item['content'] ?? '')) ?></textarea>
                        <div id="quill-editor" style="min-height:400px; background:#fff; border-radius: 0 0 10px 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 右侧：设置 -->
        <div class="column is-4 animate-in delay-2">
            <!-- 发布设置 -->
            <div class="admin-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="section-title">
                    <span class="icon-box info"><i class="fas fa-cog"></i></span>
                    发布设置
                </div>
                
                <div class="field">
                    <label class="label">文章分类</label>
                    <div class="control has-icons-left">
                        <div class="select is-fullwidth">
                            <select name="category_id">
                                <option value="0">未分类</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= ((int)($item['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                    <?= h($cat['display_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-folder"></i>
                        </span>
                    </div>
                    <p class="help">
                        <a href="<?= url('/admin/post-categories') ?>" target="_blank" class="has-text-link">
                            <i class="fas fa-plus-circle"></i> 管理文章分类
                        </a>
                    </p>
                </div>

                <div class="field">
                    <label class="label">发布状态</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="status">
                                <option value="draft" <?= ($item['status'] ?? '') === 'draft' ? 'selected' : '' ?>>📝 草稿</option>
                                <option value="active" <?= ($item['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>✅ 已发布</option>
                                <option value="inactive" <?= ($item['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>⬇️ 已下架</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">封面图片</label>
                    <div class="control">
                        <input type="hidden" name="cover" id="cover-input" value="<?= h($item['cover'] ?? '') ?>">
                        <div id="cover-preview-wrap" class="mb-3 <?= empty($item['cover'] ?? '') ? 'is-hidden' : '' ?>">
                            <figure class="image is-3by2" style="border-radius: 8px; overflow: hidden; max-width: 100%;">
                                <img id="cover-preview" src="<?= h($item['cover'] ?? '') ?>" alt="封面预览" style="object-fit: cover; width: 100%; height: 100%;">
                            </figure>
                            <button type="button" id="cover-clear-btn" class="button is-small is-light is-danger mt-2">清除封面</button>
                        </div>
                        <div class="buttons">
                            <button type="button" id="post-cover-select-btn" class="button is-light">
                                <span class="icon"><i class="fas fa-image"></i></span>
                                <span>从媒体库选择</span>
                            </button>
                        </div>
                    </div>
                </div>

                <hr style="margin: 1.5rem 0;">
                
                <div class="buttons">
                    <button type="submit" class="button is-success is-fullwidth">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span><?= $isEdit ? '保存修改' : '发布文章' ?></span>
                    </button>
                </div>
                <a href="<?= url('/admin/posts') ?>" class="button is-light is-fullwidth">
                    <span class="icon"><i class="fas fa-times"></i></span>
                    <span>取消</span>
                </a>
            </div>

            <!-- SEO 设置 -->
            <div class="admin-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="section-title">
                    <span class="icon-box success"><i class="fas fa-search"></i></span>
                    SEO 设置
                </div>
                <p class="is-size-7 has-text-grey mb-3">留空则使用文章标题和摘要</p>
                
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

            <!-- 提示信息 -->
            <div class="admin-card" style="padding: 1.5rem;">
                <div class="section-title">
                    <span class="icon-box warning"><i class="fas fa-lightbulb"></i></span>
                    写作提示
                </div>
                <div class="content is-size-7">
                    <ul>
                        <li>标题应简洁明了，便于读者理解</li>
                        <li>摘要会显示在文章列表中</li>
                        <li>使用分类帮助读者找到相关内容</li>
                        <li>草稿状态不会在前台显示</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quill 由 layout 统一初始化在 #quill-editor，此处仅做封面等本页逻辑

    // 封面：从媒体库选择（单选）
    var coverInput = document.getElementById('cover-input');
    var coverPreview = document.getElementById('cover-preview');
    var coverPreviewWrap = document.getElementById('cover-preview-wrap');
    var coverSelectBtn = document.getElementById('post-cover-select-btn');
    var coverClearBtn = document.getElementById('cover-clear-btn');
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

<style>
#quill-editor {
    font-size: 16px;
    line-height: 1.8;
}
.ql-toolbar.ql-snow {
    border-radius: 8px 8px 0 0;
    border-color: #dbdbdb;
}
.ql-container.ql-snow {
    border-radius: 0 0 8px 8px;
    border-color: #dbdbdb;
}
.ql-editor {
    min-height: 350px;
}
</style>

