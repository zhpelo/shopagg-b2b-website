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
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="icon mr-2"><i class="fas fa-<?= isset($category) ? 'edit' : 'plus' ?>"></i></span>
                    <?= isset($category) ? '编辑' . $label : '新建' . $label ?>
                </h1>
                <p class="mt-1 text-sm text-white/80"><?= isset($category) ? '修改分类信息' : '创建新的' . $label ?></p>
            </div>
        </div>
        <div class="header-actions flex items-center gap-3">
            <a href="<?= $baseUrl ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold <?= $isPost ? 'text-emerald-600' : 'text-amber-700' ?> shadow-sm transition hover:bg-slate-50">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-12">
    <div class="animate-in delay-1 xl:col-span-7">
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
                
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl <?= $isPost ? 'bg-gradient-to-r from-emerald-500 to-teal-500 shadow-emerald-500/25' : 'bg-gradient-to-r from-amber-400 to-orange-500 shadow-amber-500/25 text-slate-900' ?> px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span><?= isset($category) ? '保存修改' : '创建分类' ?></span>
                    </button>
                    <a href="<?= $baseUrl ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <span class="icon"><i class="fas fa-times"></i></span>
                        <span>取消</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <div class="animate-in delay-2 xl:col-span-5">
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
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                        <span class="icon"><i class="fas fa-newspaper"></i></span>
                        <span>文章分类</span>
                    </span>
                    <br><small class="mt-2 block text-slate-500">用于组织博客文章</small>
                    <?php else: ?>
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">
                        <span class="icon"><i class="fas fa-box"></i></span>
                        <span>产品分类</span>
                    </span>
                    <br><small class="mt-2 block text-slate-500">用于组织产品目录</small>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
