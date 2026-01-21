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
            <a href="<?= h(dirname($action)) ?>" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<form method="post" action="<?= h($action) ?>" class="modern-form">
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
                        <textarea id="content-input" name="content" style="display:none"><?= h($item['content'] ?? '') ?></textarea>
                        <div id="quill-editor" style="height:400px; background:#fff; border-radius: 0 0 10px 10px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="column is-4">
            <!-- 发布 -->
            <div class="admin-card mb-5 animate-in delay-1" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box <?= $theme['box'] ?>"><i class="fas fa-paper-plane"></i></span>
                    发布
                </div>
                
                <div class="content is-size-7 has-text-grey mb-4">
                    <p>
                        <span class="icon is-small"><i class="far fa-clock"></i></span>
                        <?= $isEdit ? '上次修改: ' . h($item['updated_at'] ?? $item['created_at']) : '准备发布新' . h($label) ?>
                    </p>
                </div>
                
                <button type="submit" class="button is-<?= $theme['box'] ?> is-fullwidth">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= $isEdit ? '保存修改' : '发布' . h($label) ?></span>
                </button>
                
                <a href="<?= h(dirname($action)) ?>" class="button is-light is-fullwidth mt-2">
                    <span class="icon"><i class="fas fa-times"></i></span>
                    <span>取消</span>
                </a>
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
