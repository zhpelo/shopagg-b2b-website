<?php
/**
 * 菜单表单页（新建/编辑）
 * @var string $action 表单提交地址
 * @var array|null $menu 菜单数据（编辑时）
 * @var array $items 菜单项树形数据（编辑时）
 * @var array $flatItems 菜单项扁平数据（编辑时）
 * @var string|null $success 成功消息
 */
$isEdit = $menu !== null;
$title = $isEdit ? '编辑菜单' : '新建菜单';

// 递归渲染菜单项
function renderMenuItemRow(array $item, int $level = 0, array $allItems = []): string {
    $itemId = $item['id'] ?? 0;
    
    ob_start();
    ?>
    <div class="menu-item bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-shadow" 
         data-level="<?= $level ?>" 
         data-item-id="<?= $itemId ?>">
        <div class="flex items-start gap-4">
            <!-- Drag Handle -->
            <div class="menu-drag-handle flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 cursor-move hover:bg-slate-200 hover:text-slate-600">
                <i class="fas fa-grip-vertical"></i>
            </div>

            <!-- Form Fields -->
            <div class="flex-1 min-w-0 grid grid-cols-1 md:grid-cols-12 gap-3">
                <!-- Title -->
                <div class="md:col-span-3">
                    <input type="text" name="item_title[]" required
                           value="<?= h($item['title']) ?>"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="菜单文字">
                </div>
                
                <!-- URL -->
                <div class="md:col-span-3">
                    <input type="text" name="item_url[]" required
                           value="<?= h($item['url']) ?>"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="链接地址（如：/products）">
                </div>
                
                <!-- Target -->
                <div class="md:col-span-2">
                    <select name="item_target[]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                        <option value="_self" <?= ($item['target'] ?? '_self') === '_self' ? 'selected' : '' ?>>当前窗口</option>
                        <option value="_blank" <?= ($item['target'] ?? '') === '_blank' ? 'selected' : '' ?>>新窗口</option>
                    </select>
                </div>
                
                <!-- Parent (使用数组索引而不是ID) -->
                <div class="md:col-span-2">
                    <select name="item_parent_index[]" class="parent-select w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                        <option value="-1" <?= ($item['parent_id'] ?? 0) == 0 ? 'selected' : '' ?>>顶级菜单</option>
                        <?php foreach ($allItems as $idx => $pItem): 
                            // 跳过自己
                            if (($pItem['id'] ?? 0) === $itemId && $itemId !== 0) continue;
                            // 只允许二级菜单，跳过已经是子菜单的项
                            if (($pItem['parent_id'] ?? 0) != 0) continue;
                        ?>
                            <option value="<?= $idx ?>" <?= ($item['parent_index'] ?? -1) == $idx ? 'selected' : '' ?>>
                                └─ <?= h($pItem['title'] ?? '菜单项 ' . ($idx + 1)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- CSS Class -->
                <div class="md:col-span-2">
                    <input type="text" name="item_css_class[]"
                           value="<?= h($item['css_class'] ?? '') ?>"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="CSS类名">
                </div>
                
                <input type="hidden" name="item_sort_order[]" class="item-sort-order" value="<?= $item['sort_order'] ?? 0 ?>">
            </div>

            <!-- Delete Button -->
            <button type="button" onclick="removeMenuItem(this)"
                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
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

                <p class="text-sm text-slate-500 mb-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    拖拽可调整菜单项顺序，选择父级可创建二级菜单
                </p>

                <!-- Menu Items Container -->
                <div id="menu-items-container" class="space-y-3">
                    <?php if (!empty($flatItems)): ?>
                        <?php 
                        // 先渲染所有顶级菜单项
                        $indexedItems = [];
                        foreach ($flatItems as $idx => $item) {
                            $item['parent_index'] = -1;
                            // 找到parent_id对应的索引
                            if (!empty($item['parent_id'])) {
                                foreach ($flatItems as $pidx => $pItem) {
                                    if ($pItem['id'] == $item['parent_id']) {
                                        $item['parent_index'] = $pidx;
                                        break;
                                    }
                                }
                            }
                            $indexedItems[$idx] = $item;
                        }
                        foreach ($indexedItems as $idx => $item): 
                            // 只渲染顶级菜单项和parent_id不为0的（按顺序渲染）
                            echo renderMenuItemRow($item, ($item['parent_id'] ?? 0) != 0 ? 1 : 0, $indexedItems);
                        endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Empty State -->
                <div id="empty-items-state" class="text-center py-12 border-2 border-dashed border-slate-300 rounded-xl <?= !empty($items) ? 'hidden' : '' ?>">
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
                取消
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/25">
                <i class="fas fa-save mr-2"></i>
                <?= $isEdit ? '保存修改' : '创建菜单' ?>
            </button>
        </div>
    </form>
</div>

<!-- Menu Item Template -->
<template id="menu-item-template">
    <div class="menu-item bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-shadow" data-level="0" data-item-id="0">
        <div class="flex items-start gap-4">
            <!-- Drag Handle -->
            <div class="menu-drag-handle flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 cursor-move hover:bg-slate-200 hover:text-slate-600">
                <i class="fas fa-grip-vertical"></i>
            </div>

            <!-- Form Fields -->
            <div class="flex-1 min-w-0 grid grid-cols-1 md:grid-cols-12 gap-3">
                <!-- Title -->
                <div class="md:col-span-3">
                    <input type="text" name="item_title[]" required
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="菜单文字">
                </div>
                
                <!-- URL -->
                <div class="md:col-span-3">
                    <input type="text" name="item_url[]" required
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="链接地址（如：/products）">
                </div>
                
                <!-- Target -->
                <div class="md:col-span-2">
                    <select name="item_target[]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                        <option value="_self">当前窗口</option>
                        <option value="_blank">新窗口</option>
                    </select>
                </div>
                
                <!-- Parent (使用数组索引) -->
                <div class="md:col-span-2">
                    <select name="item_parent_index[]" class="parent-select w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                        <option value="-1" selected>顶级菜单</option>
                    </select>
                </div>
                
                <!-- CSS Class -->
                <div class="md:col-span-2">
                    <input type="text" name="item_css_class[]"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="CSS类名">
                </div>
                
                <input type="hidden" name="item_sort_order[]" class="item-sort-order" value="">
            </div>

            <!-- Delete Button -->
            <button type="button" onclick="removeMenuItem(this)"
                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>
</template>

<script>
// Initialize Sortable for drag-and-drop
let sortableInstance = null;

function initSortable() {
    const container = document.getElementById('menu-items-container');
    if (container && typeof Sortable !== 'undefined') {
        sortableInstance = new Sortable(container, {
            handle: '.menu-drag-handle',
            animation: 150,
            onEnd: function() {
                updateSortOrders();
                rebuildParentSelects();
            }
        });
    }
}

function updateSortOrders() {
    document.querySelectorAll('.menu-item').forEach((item, index) => {
        item.querySelector('.item-sort-order').value = index;
    });
}

function addMenuItem() {
    const template = document.getElementById('menu-item-template');
    const container = document.getElementById('menu-items-container');
    const emptyState = document.getElementById('empty-items-state');
    
    const clone = template.content.cloneNode(true);
    const itemDiv = clone.querySelector('.menu-item');
    
    // Set initial sort order
    const index = container.children.length;
    itemDiv.querySelector('.item-sort-order').value = index;
    
    container.appendChild(itemDiv);
    
    // Hide empty state
    if (emptyState) {
        emptyState.classList.add('hidden');
    }
    
    updateSortOrders();
    rebuildParentSelects();
    
    // Focus on the new title input
    const newTitleInput = itemDiv.querySelector('input[name="item_title[]"]');
    if (newTitleInput) {
        newTitleInput.focus();
    }
}

function removeMenuItem(btn) {
    const item = btn.closest('.menu-item');
    const removedIndex = Array.from(item.parentNode.children).indexOf(item);
    item.remove();
    
    // Show empty state if no items
    const container = document.getElementById('menu-items-container');
    const emptyState = document.getElementById('empty-items-state');
    if (container.children.length === 0 && emptyState) {
        emptyState.classList.remove('hidden');
    }
    
    updateSortOrders();
    rebuildParentSelects();
}

// 重建所有父级选择框
function rebuildParentSelects() {
    const container = document.getElementById('menu-items-container');
    const items = container.querySelectorAll('.menu-item');
    
    // 收集所有菜单项的标题
    const itemData = [];
    items.forEach((item, index) => {
        const titleInput = item.querySelector('input[name="item_title[]"]');
        const parentSelect = item.querySelector('select[name="item_parent_index[]"]');
        const currentParentIndex = parentSelect ? parentSelect.value : '-1';
        
        itemData.push({
            index: index,
            title: titleInput ? (titleInput.value || '菜单项 ' + (index + 1)) : '菜单项 ' + (index + 1),
            element: item,
            currentParentIndex: currentParentIndex,
            isTopLevel: currentParentIndex === '-1'
        });
    });
    
    // 重建每个选择框
    items.forEach((item, itemIndex) => {
        const select = item.querySelector('select[name="item_parent_index[]"]');
        if (!select) return;
        
        const currentValue = select.value;
        
        // 构建新的选项HTML
        let optionsHtml = '<option value="-1">顶级菜单</option>';
        
        itemData.forEach((data, idx) => {
            // 跳过自己
            if (idx === itemIndex) return;
            // 只允许设置为顶级菜单的子菜单（防止三级菜单）
            if (!data.isTopLevel) return;
            
            optionsHtml += `<option value="${idx}">└─ ${escapeHtml(data.title)}</option>`;
        });
        
        select.innerHTML = optionsHtml;
        
        // 尝试恢复之前的值
        if (currentValue && currentValue !== '-1') {
            // 检查之前的父级是否还存在
            const parentIdx = parseInt(currentValue);
            if (parentIdx >= 0 && parentIdx < itemData.length && parentIdx !== itemIndex) {
                select.value = currentValue;
            } else {
                select.value = '-1';
            }
        } else {
            select.value = '-1';
        }
    });
}

// HTML转义
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-generate slug from name
document.getElementById('name')?.addEventListener('blur', function() {
    const slugInput = document.getElementById('slug');
    if (slugInput && !slugInput.value) {
        const name = this.value.trim();
        if (name) {
            // Simple slugify
            const slug = name.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug || 'menu-' + Date.now();
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initSortable();
    rebuildParentSelects();
    
    // Update titles in parent selects on input change
    document.getElementById('menu-items-container')?.addEventListener('input', function(e) {
        if (e.target.name === 'item_title[]') {
            rebuildParentSelects();
        }
    });
});
</script>
