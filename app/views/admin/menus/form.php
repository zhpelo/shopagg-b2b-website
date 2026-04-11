<?php
/**
 * 菜单表单页（新建/编辑）- 可视化拖拽版
 * @var string $action 表单提交地址
 * @var array|null $menu 菜单数据（编辑时）
 * @var array $flatItems 菜单项扁平数据（编辑时）
 */
$isEdit = $menu !== null;
$title = $isEdit ? '编辑菜单：' . h($menu['name']) : '新建菜单';

// 构建树形结构供 JS 使用
$treeItems = [];
$grouped = [];
foreach ($flatItems as $item) {
    $pid = (int)($item['parent_id'] ?? 0);
    $grouped[$pid][] = $item;
}
function buildItemTree(array $grouped, int $parentId = 0): array {
    $tree = [];
    foreach (($grouped[$parentId] ?? []) as $item) {
        $node = [
            'id'         => (int)$item['id'],
            'title'      => $item['title'],
            'url'        => $item['url'],
            'target'     => $item['target'] ?? '_self',
            'css_class'  => $item['css_class'] ?? '',
            'children'   => buildItemTree($grouped, (int)$item['id']),
        ];
        $tree[] = $node;
    }
    return $tree;
}
$treeItems = buildItemTree($grouped);
?>

<style>
.menu-builder-item { transition: background 0.15s, box-shadow 0.15s; }
.menu-builder-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.menu-builder-item.sortable-ghost { opacity: 0.4; background: #e0e7ff; }
.menu-builder-item.sortable-chosen { box-shadow: 0 4px 16px rgba(99,102,241,0.2); }
.menu-builder-children { min-height: 4px; }
.menu-builder-children .menu-builder-item { border-left: 3px solid #a5b4fc; }
.menu-item-collapsed .menu-builder-children,
.menu-item-collapsed .menu-item-body { display: none; }
.menu-item-header .drag-handle { cursor: grab; }
.menu-item-header .drag-handle:active { cursor: grabbing; }
.inline-edit-field { border: 1px solid transparent; background: transparent; padding: 2px 6px; border-radius: 6px; transition: all 0.15s; }
.inline-edit-field:hover { border-color: #cbd5e1; background: #f8fafc; }
.inline-edit-field:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 2px rgba(99,102,241,0.2); outline: none; }
.drop-zone-indicator { border: 2px dashed #a5b4fc; background: #eef2ff; border-radius: 8px; padding: 12px; text-align: center; color: #6366f1; font-size: 13px; margin-top: 8px; transition: all 0.15s; }
</style>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-violet-600">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="<?= url('/admin/appearance/menus') ?>" 
                   class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 text-white hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white"><?= $title ?></h1>
                    <p class="text-indigo-100 mt-1"><?= $isEdit ? '拖拽排序菜单项，调整层级关系' : '创建新菜单' ?></p>
                </div>
            </div>
            <?php if ($isEdit): ?>
            <div id="saveStatus" class="text-white/70 text-sm hidden">
                <i class="fas fa-check-circle mr-1"></i> <span></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Form -->
    <form id="menuForm" action="<?= $action ?>" method="post" class="p-6">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span><?= h($_GET['success']) ?></span>
            </div>
        <?php endif; ?>

        <!-- 基础信息 -->
        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 mb-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fas fa-cog text-indigo-500"></i>
                基础信息
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        菜单名称 <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           value="<?= h($menu['name'] ?? '') ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="如：主导航">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        标识符 <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="slug" required
                           value="<?= h($menu['slug'] ?? '') ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none font-mono text-sm"
                           placeholder="如：main-nav">
                    <p class="text-xs text-slate-500 mt-1">前台模板通过此标识符调用菜单</p>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">描述</label>
                <textarea name="description" rows="2"
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none resize-none"
                          placeholder="菜单用途说明（可选）"><?= h($menu['description'] ?? '') ?></textarea>
            </div>

            <div class="mt-4 flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="status" value="active"
                           <?= ($menu['status'] ?? 'active') === 'active' ? 'checked' : '' ?>
                           class="w-4 h-4 text-indigo-600">
                    <span class="text-sm text-slate-700">启用</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="status" value="inactive"
                           <?= ($menu['status'] ?? '') === 'inactive' ? 'checked' : '' ?>
                           class="w-4 h-4 text-slate-400">
                    <span class="text-sm text-slate-700">禁用</span>
                </label>
            </div>
        </div>

        <!-- 菜单项管理 - 可视化拖拽 -->
        <?php if ($isEdit): ?>
        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-list text-indigo-500"></i>
                    菜单项
                </h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400"><i class="fas fa-grip-vertical mr-1"></i>拖拽排序 · 向右拖变子菜单</span>
                    <button type="button" id="addMenuItemBtn"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus"></i> 添加菜单项
                    </button>
                </div>
            </div>

            <!-- 菜单项列表 -->
            <div id="menuItemsList" class="space-y-2">
                <!-- JS 动态渲染 -->
            </div>

            <div id="emptyState" class="text-center py-10 border-2 border-dashed border-slate-300 rounded-xl hidden">
                <i class="fas fa-mouse-pointer text-3xl text-slate-300 mb-3"></i>
                <p class="text-slate-500 mb-2">还没有菜单项</p>
                <p class="text-sm text-slate-400">点击上方「添加菜单项」开始构建菜单</p>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-list text-indigo-500"></i>
                    菜单项
                </h2>
            </div>
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-center">
                <i class="fas fa-info-circle mr-2"></i>
                请先保存菜单基本信息，然后可以添加和管理菜单项
            </div>
        </div>
        <?php endif; ?>

        <!-- 隐藏字段存放序列化菜单数据 -->
        <input type="hidden" name="menu_items_json" id="menuItemsJson" value="">

        <!-- 提交按钮 -->
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-200">
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

<!-- 添加/编辑菜单项弹窗 -->
<div id="itemModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" id="itemModalOverlay"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative">
            <div class="p-5 border-b border-slate-200">
                <h3 id="itemModalTitle" class="text-lg font-bold text-slate-900">添加菜单项</h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        菜单文字 <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="itemTitle"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="如：首页、产品中心">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        链接地址 <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="itemUrl"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="如：/ 或 /products">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">打开方式</label>
                        <select id="itemTarget"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                            <option value="_self">当前窗口</option>
                            <option value="_blank">新窗口</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">CSS 类名</label>
                        <input type="text" id="itemCssClass"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                               placeholder="可选">
                    </div>
                </div>
            </div>
            <div class="p-5 border-t border-slate-200 flex justify-end gap-3">
                <button type="button" id="itemModalCancel"
                        class="px-5 py-2.5 text-slate-700 font-medium bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                    取消
                </button>
                <button type="button" id="itemModalSave"
                        class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors">
                    确定
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    if (!isEdit) {
        // 新建模式仅做 slug 自动生成
        document.querySelector('input[name="name"]')?.addEventListener('blur', function() {
            const slugInput = document.querySelector('input[name="slug"]');
            if (slugInput && !slugInput.value) {
                const name = this.value.trim();
                if (name) {
                    slugInput.value = name.toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_]+/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            }
        });
        return;
    }

    // ============ 数据 ============
    let menuItems = <?= json_encode($treeItems, JSON_UNESCAPED_UNICODE) ?>;
    let tempIdCounter = Date.now();
    const menuId = <?= (int)($menu['id'] ?? 0) ?>;

    function nextTempId() { return --tempIdCounter; }

    // ============ 渲染 ============
    const listEl = document.getElementById('menuItemsList');
    const emptyEl = document.getElementById('emptyState');
    const jsonInput = document.getElementById('menuItemsJson');
    let sortableInstances = [];

    function renderAll() {
        listEl.innerHTML = '';
        destroySortables();

        if (menuItems.length === 0) {
            emptyEl?.classList.remove('hidden');
        } else {
            emptyEl?.classList.add('hidden');
            menuItems.forEach((item, idx) => {
                listEl.appendChild(createItemEl(item, 0, idx));
            });
        }

        initSortables();
        syncJson();
    }

    function createItemEl(item, depth, index) {
        const el = document.createElement('div');
        el.className = 'menu-builder-item bg-white border border-slate-200 rounded-xl overflow-hidden';
        el.dataset.id = item.id;
        el.dataset.depth = depth;

        const isTopLevel = depth === 0;
        const hasChildren = item.children && item.children.length > 0;
        const depthColor = isTopLevel ? 'bg-indigo-50 border-indigo-100' : 'bg-slate-50 border-slate-100';
        const titleColor = isTopLevel ? 'text-indigo-900' : 'text-slate-700';

        el.innerHTML = `
            <div class="menu-item-header ${depthColor} px-4 py-2.5 border-b flex items-center justify-between gap-3 cursor-pointer select-none">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <span class="drag-handle text-slate-400 hover:text-indigo-500 transition-colors p-1">
                        <i class="fas fa-grip-vertical"></i>
                    </span>
                    <button type="button" class="toggle-btn text-slate-400 hover:text-slate-600 w-5 text-center transition-transform ${hasChildren ? '' : 'invisible'}">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <span class="font-semibold ${titleColor} truncate item-title-display">${escHtml(item.title)}</span>
                    <span class="text-xs text-slate-400 truncate hidden sm:inline">${escHtml(item.url)}</span>
                    ${item.target === '_blank' ? '<span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">新窗口</span>' : ''}
                    ${depth > 0 ? '<span class="text-xs bg-slate-200 text-slate-500 px-1.5 py-0.5 rounded">子菜单</span>' : ''}
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <button type="button" class="edit-btn inline-flex items-center justify-center w-8 h-8 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="编辑">
                        <i class="fas fa-pen text-xs"></i>
                    </button>
                    <button type="button" class="add-child-btn inline-flex items-center justify-center w-8 h-8 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="添加子菜单">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                    <button type="button" class="delete-btn inline-flex items-center justify-center w-8 h-8 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="删除">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </div>
            <div class="menu-builder-children p-2 pl-6 space-y-2" data-parent-id="${item.id}"></div>
        `;

        // 绑定事件
        const header = el.querySelector('.menu-item-header');
        const toggleBtn = el.querySelector('.toggle-btn');
        const editBtn = el.querySelector('.edit-btn');
        const addChildBtn = el.querySelector('.add-child-btn');
        const deleteBtn = el.querySelector('.delete-btn');
        const childrenContainer = el.querySelector('.menu-builder-children');

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            el.classList.toggle('menu-item-collapsed');
            const icon = toggleBtn.querySelector('i');
            icon.style.transform = el.classList.contains('menu-item-collapsed') ? 'rotate(-90deg)' : '';
        });

        editBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            openModal('edit', item);
        });

        addChildBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            openModal('add-child', item);
        });

        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const childCount = countAllChildren(item);
            let msg = '确定删除菜单项「' + item.title + '」吗？';
            if (childCount > 0) {
                msg += '\n\n此操作同时会删除其下的 ' + childCount + ' 个子菜单项。';
            }
            if (confirm(msg)) {
                removeItem(item.id);
                renderAll();
            }
        });

        // 渲染子菜单
        if (item.children) {
            item.children.forEach((child, ci) => {
                childrenContainer.appendChild(createItemEl(child, depth + 1, ci));
            });
        }

        return el;
    }

    function countAllChildren(item) {
        if (!item.children) return 0;
        let count = item.children.length;
        item.children.forEach(c => { count += countAllChildren(c); });
        return count;
    }

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    // ============ 数据操作 ============
    function findItem(items, id) {
        for (const item of items) {
            if (item.id === id) return item;
            if (item.children) {
                const found = findItem(item.children, id);
                if (found) return found;
            }
        }
        return null;
    }

    function findParent(items, id, parent) {
        for (const item of items) {
            if (item.id === id) return parent;
            if (item.children) {
                const found = findParent(item.children, id, item);
                if (found) return found;
            }
        }
        return null;
    }

    function removeItem(id) {
        menuItems = removeFromTree(menuItems, id);
    }

    function removeFromTree(items, id) {
        return items.filter(item => {
            if (item.id === id) return false;
            if (item.children) {
                item.children = removeFromTree(item.children, id);
            }
            return true;
        });
    }

    function addItem(data) {
        menuItems.push({ ...data, children: [] });
    }

    function addChildTo(parentId, data) {
        const parent = findItem(menuItems, parentId);
        if (parent) {
            if (!parent.children) parent.children = [];
            parent.children.push({ ...data, children: [] });
        }
    }

    function updateItem(id, data) {
        const item = findItem(menuItems, id);
        if (item) {
            Object.assign(item, data);
        }
    }

    // ============ 拖拽排序 ============
    function destroySortables() {
        sortableInstances.forEach(s => s.destroy());
        sortableInstances = [];
    }

    function initSortables() {
        // 顶级列表
        const topSortable = new Sortable(listEl, {
            group: 'menu-items',
            animation: 200,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: syncFromDom
        });
        sortableInstances.push(topSortable);

        // 子菜单容器
        listEl.querySelectorAll('.menu-builder-children').forEach(container => {
            const s = new Sortable(container, {
                group: 'menu-items',
                animation: 200,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                fallbackOnBody: true,
                swapThreshold: 0.65,
                onEnd: syncFromDom
            });
            sortableInstances.push(s);
        });
    }

    function syncFromDom() {
        // 从 DOM 重建树形数据
        menuItems = readTreeFromDom(listEl);
        renderAll();
    }

    function readTreeFromDom(container) {
        const items = [];
        const children = container.children;
        for (let i = 0; i < children.length; i++) {
            const el = children[i];
            if (!el.classList.contains('menu-builder-item')) continue;
            const id = parseInt(el.dataset.id);
            const item = findItemGlobal(id);
            if (!item) continue;
            const childContainer = el.querySelector('.menu-builder-children');
            const childItems = childContainer ? readTreeFromDom(childContainer) : [];
            items.push({
                id: item.id,
                title: item.title,
                url: item.url,
                target: item.target,
                css_class: item.css_class || '',
                children: childItems
            });
        }
        return items;
    }

    // 全局搜索（在重建前的 menuItems 中搜索）
    let _allItemsFlat = [];
    function rebuildFlatIndex() {
        _allItemsFlat = [];
        flattenItems(menuItems, _allItemsFlat);
    }
    function flattenItems(items, out) {
        items.forEach(item => {
            out.push(item);
            if (item.children) flattenItems(item.children, out);
        });
    }
    function findItemGlobal(id) {
        return _allItemsFlat.find(i => i.id === id) || null;
    }

    // ============ 弹窗 ============
    const modal = document.getElementById('itemModal');
    const modalTitle = document.getElementById('itemModalTitle');
    const inputTitle = document.getElementById('itemTitle');
    const inputUrl = document.getElementById('itemUrl');
    const inputTarget = document.getElementById('itemTarget');
    const inputCssClass = document.getElementById('itemCssClass');
    const modalSave = document.getElementById('itemModalSave');
    const modalCancel = document.getElementById('itemModalCancel');
    const modalOverlay = document.getElementById('itemModalOverlay');

    let modalMode = 'add'; // add | edit | add-child
    let modalEditItem = null;
    let modalParentItem = null;

    function openModal(mode, item) {
        modalMode = mode;
        modalEditItem = mode === 'edit' ? item : null;
        modalParentItem = mode === 'add-child' ? item : null;

        if (mode === 'edit') {
            modalTitle.textContent = '编辑菜单项';
            inputTitle.value = item.title;
            inputUrl.value = item.url;
            inputTarget.value = item.target || '_self';
            inputCssClass.value = item.css_class || '';
        } else if (mode === 'add-child') {
            modalTitle.textContent = '添加子菜单到「' + item.title + '」';
            inputTitle.value = '';
            inputUrl.value = '';
            inputTarget.value = '_self';
            inputCssClass.value = '';
        } else {
            modalTitle.textContent = '添加菜单项';
            inputTitle.value = '';
            inputUrl.value = '';
            inputTarget.value = '_self';
            inputCssClass.value = '';
        }

        modal.classList.remove('hidden');
        setTimeout(() => inputTitle.focus(), 100);
    }

    function closeModal() {
        modal.classList.add('hidden');
        modalEditItem = null;
        modalParentItem = null;
    }

    modalCancel.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    modalSave.addEventListener('click', () => {
        const title = inputTitle.value.trim();
        const url = inputUrl.value.trim();
        if (!title) { inputTitle.focus(); return; }
        if (!url) { inputUrl.focus(); return; }

        const data = {
            title: title,
            url: url,
            target: inputTarget.value,
            css_class: inputCssClass.value.trim()
        };

        if (modalMode === 'edit' && modalEditItem) {
            updateItem(modalEditItem.id, data);
        } else if (modalMode === 'add-child' && modalParentItem) {
            data.id = nextTempId();
            addChildTo(modalParentItem.id, data);
        } else {
            data.id = nextTempId();
            addItem(data);
        }

        closeModal();
        renderAll();
    });

    // Enter 键保存
    [inputTitle, inputUrl, inputCssClass].forEach(el => {
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); modalSave.click(); }
        });
    });

    // 添加顶级菜单按钮
    document.getElementById('addMenuItemBtn')?.addEventListener('click', () => {
        openModal('add', null);
    });

    // ============ 表单提交 ============
    function syncJson() {
        rebuildFlatIndex();
        jsonInput.value = JSON.stringify(menuItems);
    }

    document.getElementById('menuForm').addEventListener('submit', function() {
        syncJson();
    });

    // ============ 初始化 ============
    rebuildFlatIndex();
    renderAll();

    // slug 自动生成
    document.querySelector('input[name="name"]')?.addEventListener('blur', function() {
        const slugInput = document.querySelector('input[name="slug"]');
        if (slugInput && !slugInput.value) {
            const name = this.value.trim();
            if (name) {
                slugInput.value = name.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_]+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        }
    });
})();
</script>
