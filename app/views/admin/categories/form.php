<?php
$type = $type ?? 'product';
$isPost = $type === 'post';
$themeColor = $isPost ? '#48c774' : '#ffc107';
$themeGradient = $isPost ? 'linear-gradient(135deg, #48c774 0%, #00d1b2 100%)' : 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)';
$icon = $isPost ? 'fa-newspaper' : 'fa-folder';
$label = $isPost ? '文章分类' : '产品分类';
$baseUrl = $base_url ?? ($isPost ? '/admin/post-categories' : '/admin/product-categories');
$parentCategories = $parent_categories ?? [];
$currentParentId = (int)($category['parent_id'] ?? 0);
$currentId = (int)($category['id'] ?? 0);
?>

<!-- 页面头部 -->
<div class="page-header animate-in" style="background: <?= $themeGradient ?>; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-<?= isset($category) ? 'edit' : 'plus' ?>"></i></span>
                    <?= isset($category) ? '编辑' . $label : '新建' . $label ?>
                </h1>
                <p class="subtitle is-6"><?= isset($category) ? '修改分类信息' : '创建新的' . $label ?></p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="<?= $baseUrl ?>" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<div class="columns">
    <div class="column is-7 animate-in delay-1">
        <form method="post" action="<?= h(url($action)) ?>" class="modern-form">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            
            <div class="admin-card" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box <?= $isPost ? 'success' : 'warning' ?>"><i class="fas <?= $icon ?>"></i></span>
                    分类信息
                </div>
                
                <div class="field">
                    <label class="label">分类名称 <span class="has-text-danger">*</span></label>
                    <div class="control has-icons-left">
                        <input class="input" name="name" value="<?= h($category['name'] ?? '') ?>" required placeholder="输入分类名称">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-tag"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">分类的显示名称</p>
                </div>
                
                <div class="field">
                    <label class="label">别名 (Slug)</label>
                    <div class="control has-icons-left">
                        <input class="input" name="slug" value="<?= h($category['slug'] ?? '') ?>" placeholder="category-slug">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-link"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">用于URL的标识符，留空则自动生成</p>
                </div>

                <div class="field">
                    <label class="label">父级分类</label>
                    <div class="control has-icons-left">
                        <div class="select is-fullwidth">
                            <select name="parent_id">
                                <option value="0">无（顶级分类）</option>
                                <?php foreach ($parentCategories as $pc): 
                                    // 不能选择自己或自己的子分类作为父分类
                                    if ($currentId > 0 && (int)$pc['id'] === $currentId) continue;
                                ?>
                                <option value="<?= (int)$pc['id'] ?>" <?= $currentParentId === (int)$pc['id'] ? 'selected' : '' ?>>
                                    <?= h($pc['display_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-sitemap"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">选择父级分类以创建多级分类结构</p>
                </div>

                <div class="field">
                    <label class="label">分类描述</label>
                    <div class="control">
                        <textarea class="textarea" name="description" rows="3" placeholder="输入分类描述（可选）"><?= h($category['description'] ?? '') ?></textarea>
                    </div>
                    <p class="help has-text-grey">简短描述该分类的内容</p>
                </div>

                <hr style="margin: 1.5rem 0;">
                
                <div class="buttons">
                    <button type="submit" class="button is-<?= $isPost ? 'success' : 'warning' ?>">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span><?= isset($category) ? '保存修改' : '创建分类' ?></span>
                    </button>
                    <a href="<?= $baseUrl ?>" class="button is-light">
                        <span class="icon"><i class="fas fa-times"></i></span>
                        <span>取消</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <div class="column is-5 animate-in delay-2">
        <div class="admin-card" style="padding: 1.5rem;">
            <div class="section-title">
                <span class="icon-box info"><i class="fas fa-info-circle"></i></span>
                分类说明
            </div>
            <div class="content is-size-7">
                <p><strong>多级分类：</strong></p>
                <ul>
                    <li>可以选择父级分类来创建层级结构</li>
                    <li>支持无限层级嵌套</li>
                    <li>删除父分类时，子分类会自动提升到上一级</li>
                </ul>
                <p class="mt-4"><strong>当前分类类型：</strong></p>
                <p>
                    <?php if ($isPost): ?>
                    <span class="tag is-success is-light is-medium">
                        <span class="icon"><i class="fas fa-newspaper"></i></span>
                        <span>文章分类</span>
                    </span>
                    <br><small class="has-text-grey mt-2" style="display: block;">用于组织博客文章</small>
                    <?php else: ?>
                    <span class="tag is-warning is-light is-medium">
                        <span class="icon"><i class="fas fa-box"></i></span>
                        <span>产品分类</span>
                    </span>
                    <br><small class="has-text-grey mt-2" style="display: block;">用于组织产品目录</small>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
