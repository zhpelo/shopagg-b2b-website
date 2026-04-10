<?php
/**
 * 菜单表单页（新建/编辑）- 简化版
 * @var string $action 表单提交地址
 * @var array|null $menu 菜单数据（编辑时）
 * @var array $items 菜单项树形数据（编辑时）
 * @var array $flatItems 菜单项扁平数据（编辑时）
 * @var string|null $success 成功消息
 */
$isEdit = $menu !== null;
$title = $isEdit ? '编辑菜单' : '新建菜单';

// 构建树形结构用于显示
function buildTree(array $items, int $parentId = 0): array {
    $tree = [];
    foreach ($items as $item) {
        if ((int)($item['parent_id'] ?? 0) === $parentId) {
            $children = buildTree($items, (int)$item['id']);
            if ($children) {
                $item['children'] = $children;
            }
            $tree[] = $item;
        }
    }
    return $tree;
}

$treeItems = buildTree($flatItems);

// 获取所有顶级菜单项作为父级选项
$topLevelItems = array_filter($flatItems, fn($item) => ($item['parent_id'] ?? 0) == 0);
?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-violet-600">
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/appearance/menus') ?>" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 text-white hover:bg-white/30 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white"><?= $title ?></h1>
                <p class="text-indigo-100 mt-1"><?= $isEdit ? '修改菜单设置和菜单项' : '创建一个新的导航菜单' ?></p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= $action ?>" method="post" class="p-6" id="menu-form">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span><?= h($_GET['success']) ?></span>
            </div>
        <?php endif; ?>

        <div class="space-y-8">
            <!-- Basic Settings -->
            <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-cog text-indigo-500"></i>
                    基础设置
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                            菜单名称 <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" required
                               value="<?= h($menu['name'] ?? '') ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                               placeholder="如：主导航菜单">
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-semibold text-slate-700 mb-2">
                            标识符 <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="slug" name="slug" required
                               value="<?= h($menu['slug'] ?? '') ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all font-mono text-sm"
                               placeholder="如：main-nav">
                        <p class="text-xs text-slate-500 mt-1.5">
                            用于模板调用，只能包含字母、数字、连字符和下划线
                        </p>
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-semibold text-slate-700 mb-2">
                            菜单位置
                        </label>
                        <select id="location" name="location" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all bg-white">
                            <option value="header" <?= ($menu['location'] ?? 'header') === 'header' ? 'selected' : '' ?>>顶部导航</option>
                            <option value="footer" <?= ($menu['location'] ?? '') === 'footer' ? 'selected' : '' ?>>页脚导航</option>
                            <option value="sidebar" <?= ($menu['location'] ?? '') === 'sidebar' ? 'selected' : '' ?>>侧边栏</option>
                            <option value="other" <?= ($menu['location'] ?? '') === 'other' ? 'selected' : '' ?>>其他</option>
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-sm font-semibold text-slate-700 mb-2">
                            排序
                        </label>
                        <input type="number" id="sort_order" name="sort_order"
                               value="<?= $menu['sort_order'] ?? 0 ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                               placeholder="0">
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-4">
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">
                        描述
                    </label>
                    <textarea id="description" name="description" rows="2"
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all resize-none"
                              placeholder="菜单的用途说明..."><?= h($menu['description'] ?? '') ?></textarea>
                </div>

                <!-- Status -->
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">状态</label>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="active" 
                                   <?= ($menu['status'] ?? 'active') === 'active' ? 'checked' : '' ?>
                                   class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            <span class="text-sm text-slate-700">启用</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="inactive" 
                                   <?= ($menu['status'] ?? '') === 'inactive' ? 'checked' : '' ?>
                                   class="w-4 h-4 text-slate-400 border-slate-300 focus:ring-slate-400">
                            <span class="text-sm text-slate-700">禁用</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-list text-indigo-500"></i>
                        菜单项
                    </h2>
                    <button type="button" onclick="addMenuItem()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus"></i>
                        添加菜单项
                    </button>
                </div>

                <!-- Menu Items Table -->
                <div id="menu-items-container" class="space-y-3">
                    <?php if (!empty($flatItems)): ?>
                        <?php foreach ($flatItems as $idx => $item): 
                            $level = 0;
                            foreach ($flatItems as $p) {
                                if ($p['id'] == $item['parent_id']) {
                                    $level = 1;
                                    break;
                                }
                            }
                        ?>
                            <div class="menu-item bg-white border border-slate-200 rounded-lg p-4" data-id="<?= $item['id'] ?>">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                    <!-- 菜单文字 -->
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-medium text-slate-500 mb-1">菜单文字 <span class="text-rose-500">*</span></label>
                                        <input type="text" name="item_title[]" required
                                               value="<?= h($item['title']) ?>"
                                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                               placeholder="如：产品中心">
                                    </div>
                                    
                                    <!-- 链接地址 -->
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-medium text-slate-500 mb-1">链接地址 <span class="text-rose-500">*</span></label>
                                        <input type="text" name="item_url[]" required
                                               value="<?= h($item['url']) ?>"
                                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                               placeholder="如：/products">
                                    </div>
                                    
                                    <!-- 父级菜单 -->
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-medium text-slate-500 mb-1">父级菜单</label>
                                        <select name="item_parent_id[]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                                            <option value="0">-- 顶级菜单 --</option>
                                            <?php foreach ($topLevelItems as $parentItem): 
                                                if ($parentItem['id'] == $item['id']) continue; // 不能选自己
                                            ?>
                                                <option value="<?= $parentItem['id'] ?>" <?= ($item['parent_id'] ?? 0) == $parentItem['id'] ? 'selected' : '' ?>>
                                                    <?= h($parentItem['title']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- 排序 -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-slate-500 mb-1">排序</label>
                                        <input type="number" name="item_sort_order[]"
                                               value="<?= $item['sort_order'] ?? $idx ?>"
                                               class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                               placeholder="0">
                                    </div>
                                    
                                    <!-- 删除按钮 -->
                                    <div class="md:col-span-1">
                                        <button type="button" onclick="removeMenuItem(this)"
                                                class="w-full px-3 py-2 text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors"
                                                title="删除">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- 展开/收起详情 -->
                                    <div class="md:col-span-12">
                                        <button type="button" onclick="toggleDetails(this)" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                            <i class="fas fa-chevron-down mr-1"></i>更多设置
                                        </button>
                                    </div>
                                    
                                    <!-- 更多选项（默认隐藏） -->
                                    <div class="more-details hidden md:col-span-12 grid grid-cols-1 md:grid-cols-3 gap-3 pt-3 border-t border-slate-100">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1">打开方式</label>
                                            <select name="item_target[]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                                                <option value="_self" <?= ($item['target'] ?? '_self') === '_self' ? 'selected' : '' ?>>当前窗口</option>
                                                <option value="_blank" <?= ($item['target'] ?? '') === '_blank' ? 'selected' : '' ?>>新窗口</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-slate-500 mb-1">CSS 类名</label>
                                            <input type="text" name="item_css_class[]"
                                                   value="<?= h($item['css_class'] ?? '') ?>"
                                                   class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                                   placeholder="自定义CSS类名（可选）">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Empty State -->
                <div id="empty-items-state" class="text-center py-12 border-2 border-dashed border-slate-300 rounded-xl <?= !empty($flatItems) ? 'hidden' : '' ?>">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="fas fa-list text-2xl"></i>
                    </div>
                    <p class="text-slate-500">还没有添加任何菜单项</p>
                    <button type="button" onclick="addMenuItem()"
                            class="mt-3 text-indigo-600 font-medium hover:text-indigo-700">
                        添加第一个菜单项
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 pt-6 border-t border-slate-200 flex items-center justify-end gap-4">
            <a href="<?= url('/admin/appearance/menus') ?>" 
               class="px-6 py-2.5 text-slate-700 font-medium bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                返回列表
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/25">
                <i class="fas fa-save mr-2"></i>
                保存菜单
            </button>
        </div>
    </form>
</div>

<!-- Menu Item Template -->
<template id="menu-item-template">
    <div class="menu-item bg-white border border-slate-200 rounded-lg p-4" data-id="0">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <!-- 菜单文字 -->
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-slate-500 mb-1">菜单文字 <span class="text-rose-500">*</span></label>
                <input type="text" name="item_title[]" required
                       class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                       placeholder="如：产品中心">
            </div>
            
            <!-- 链接地址 -->
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-slate-500 mb-1">链接地址 <span class="text-rose-500">*</span></label>
                <input type="text" name="item_url[]" required
                       class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                       placeholder="如：/products">
            </div>
            
            <!-- 父级菜单 -->
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-slate-500 mb-1">父级菜单</label>
                <select name="item_parent_id[]" class="parent-select w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                    <option value="0">-- 顶级菜单 --</option>
                </select>
            </div>
            
            <!-- 排序 -->
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-500 mb-1">排序</label>
                <input type="number" name="item_sort_order[]" value="0"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                       placeholder="0">
            </div>
            
            <!-- 删除按钮 -->
            <div class="md:col-span-1">
                <button type="button" onclick="removeMenuItem(this)"
                        class="w-full px-3 py-2 text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors"
                        title="删除">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            
            <!-- 展开/收起详情 -->
            <div class="md:col-span-12">
                <button type="button" onclick="toggleDetails(this)" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                    <i class="fas fa-chevron-down mr-1"></i>更多设置
                </button>
            </div>
            
            <!-- 更多选项（默认隐藏） -->
            <div class="more-details hidden md:col-span-12 grid grid-cols-1 md:grid-cols-3 gap-3 pt-3 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">打开方式</label>
                    <select name="item_target[]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                        <option value="_self">当前窗口</option>
                        <option value="_blank">新窗口</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-500 mb-1">CSS 类名</label>
                    <input type="text" name="item_css_class[]"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="自定义CSS类名（可选）">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// 切换详情显示/隐藏
function toggleDetails(btn) {
    const item = btn.closest('.menu-item');
    const details = item.querySelector('.more-details');
    const icon = btn.querySelector('i');
    
    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
        btn.innerHTML = '<i class="fas fa-chevron-up mr-1"></i>收起设置';
    } else {
        details.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
        btn.innerHTML = '<i class="fas fa-chevron-down mr-1"></i>更多设置';
    }
}

// 更新父级选择框选项
function updateParentSelects() {
    const container = document.getElementById('menu-items-container');
    const allTitles = [];
    
    // 收集所有菜单项标题
    container.querySelectorAll('.menu-item').forEach((item, index) => {
        const titleInput = item.querySelector('input[name="item_title[]"]');
        const title = titleInput ? (titleInput.value || '菜单项 ' + (index + 1)) : '菜单项 ' + (index + 1);
        allTitles.push({ index: index, title: title });
    });
    
    // 更新每个选择框
    container.querySelectorAll('.menu-item').forEach((item, itemIndex) => {
        const select = item.querySelector('select[name="item_parent_id[]"]');
        if (!select) return;
        
        const currentValue = select.value;
        
        // 重建选项
        let optionsHtml = '<option value="0">-- 顶级菜单 --</option>';
        allTitles.forEach((t, idx) => {
            if (idx !== itemIndex) { // 不能选自己
                optionsHtml += `<option value="${idx}">${h(t.title)}</option>`;
            }
        });
        
        select.innerHTML = optionsHtml;
        
        // 尝试恢复之前的值
        if (currentValue && currentValue !== '0') {
            const prevIdx = parseInt(currentValue);
            if (prevIdx >= 0 && prevIdx < allTitles.length && prevIdx !== itemIndex) {
                select.value = currentValue;
            } else {
                select.value = '0';
            }
        }
    });
}

// HTML转义
function h(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// 添加菜单项
function addMenuItem() {
    const template = document.getElementById('menu-item-template');
    const container = document.getElementById('menu-items-container');
    const emptyState = document.getElementById('empty-items-state');
    
    const clone = template.content.cloneNode(true);
    const itemDiv = clone.querySelector('.menu-item');
    
    // 设置排序值
    const itemCount = container.querySelectorAll('.menu-item').length;
    const sortInput = itemDiv.querySelector('input[name="item_sort_order[]"]');
    if (sortInput) {
        sortInput.value = itemCount;
    }
    
    container.appendChild(itemDiv);
    
    // 隐藏空状态
    if (emptyState) {
        emptyState.classList.add('hidden');
    }
    
    // 更新父级选择框
    updateParentSelects();
    
    // 聚焦到新输入框
    const newTitleInput = itemDiv.querySelector('input[name="item_title[]"]');
    if (newTitleInput) {
        newTitleInput.focus();
    }
}

// 删除菜单项
function removeMenuItem(btn) {
    const item = btn.closest('.menu-item');
    item.remove();
    
    // 显示空状态
    const container = document.getElementById('menu-items-container');
    const emptyState = document.getElementById('empty-items-state');
    if (container.children.length === 0 && emptyState) {
        emptyState.classList.remove('hidden');
    }
    
    // 更新父级选择框
    updateParentSelects();
}

// Auto-generate slug from name
document.getElementById('name')?.addEventListener('blur', function() {
    const slugInput = document.getElementById('slug');
    if (slugInput && !slugInput.value) {
        const name = this.value.trim();
        if (name) {
            const slug = name.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug || 'menu-' + Date.now();
        }
    }
});

// 监听标题变化，更新父级选择框
document.addEventListener('input', function(e) {
    if (e.target.name === 'item_title[]') {
        updateParentSelects();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateParentSelects();
});
</script>
