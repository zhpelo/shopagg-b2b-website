<?php
/**
 * 菜单表单页（新建/编辑）- 简洁版
 * @var string $action 表单提交地址
 * @var array|null $menu 菜单数据（编辑时）
 * @var array $flatItems 菜单项扁平数据（编辑时）
 * @var string|null $success 成功消息
 */
$isEdit = $menu !== null;
$title = $isEdit ? '编辑菜单：' . h($menu['name']) : '新建菜单';

// 按 parent_id 分组菜单项
$groupedItems = [];
foreach ($flatItems as $item) {
    $parentId = (int)($item['parent_id'] ?? 0);
    if (!isset($groupedItems[$parentId])) {
        $groupedItems[$parentId] = [];
    }
    $groupedItems[$parentId][] = $item;
}

// 获取顶级菜单项
$topLevelItems = $groupedItems[0] ?? [];
?>

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
                    <p class="text-indigo-100 mt-1"><?= $isEdit ? '修改菜单信息和菜单项' : '创建新菜单' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= $action ?>" method="post" class="p-6">
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

        <!-- 菜单项管理 -->
        <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-list text-indigo-500"></i>
                    菜单项
                </h2>
                <span class="text-sm text-slate-500">可添加 <?= count($topLevelItems) ?> 个顶级菜单，每个顶级菜单下可添加子菜单</span>
            </div>

            <?php if (empty($topLevelItems) && $isEdit): ?>
                <div class="text-center py-8 border-2 border-dashed border-slate-300 rounded-xl">
                    <p class="text-slate-500 mb-2">还没有添加任何菜单项</p>
                    <p class="text-sm text-slate-400">请在下方添加第一个顶级菜单</p>
                </div>
            <?php endif; ?>

            <!-- 顶级菜单列表 -->
            <div class="space-y-4">
                <?php 
                $topIndex = 0;
                foreach ($topLevelItems as $topItem): 
                    $children = $groupedItems[$topItem['id']] ?? [];
                ?>
                    <!-- 顶级菜单 -->
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-indigo-50 px-4 py-3 border-b border-indigo-100 flex items-center justify-between">
                            <span class="font-semibold text-indigo-900">
                                <i class="fas fa-bars mr-2"></i>顶级菜单 #<?= $topIndex + 1 ?>: <?= h($topItem['title']) ?>
                            </span>
                            <span class="text-xs text-indigo-600 bg-indigo-100 px-2 py-1 rounded">排序: <?= $topItem['sort_order'] ?? 0 ?></span>
                        </div>
                        
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-3">
                                <input type="hidden" name="items[<?= $topIndex ?>][id]" value="<?= $topItem['id'] ?>">
                                <input type="hidden" name="items[<?= $topIndex ?>][parent_id]" value="0">
                                
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-medium text-slate-500 mb-1">菜单文字</label>
                                    <input type="text" name="items[<?= $topIndex ?>][title]" required
                                           value="<?= h($topItem['title']) ?>"
                                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                                </div>
                                
                                <div class="md:col-span-4">
                                    <label class="block text-xs font-medium text-slate-500 mb-1">链接地址</label>
                                    <input type="text" name="items[<?= $topIndex ?>][url]" required
                                           value="<?= h($topItem['url']) ?>"
                                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                           placeholder="如：/products">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-500 mb-1">排序</label>
                                    <input type="number" name="items[<?= $topIndex ?>][sort_order]"
                                           value="<?= $topItem['sort_order'] ?? 0 ?>"
                                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-500 mb-1">打开方式</label>
                                    <select name="items[<?= $topIndex ?>][target]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                                        <option value="_self" <?= ($topItem['target'] ?? '_self') === '_self' ? 'selected' : '' ?>>当前窗口</option>
                                        <option value="_blank" <?= ($topItem['target'] ?? '') === '_blank' ? 'selected' : '' ?>>新窗口</option>
                                    </select>
                                </div>
                                
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-medium text-slate-500 mb-1">&nbsp;</label>
                                    <a href="<?= url('/admin/appearance/menus/delete-item?id=' . $topItem['id'] . '&menu_id=' . ($menu['id'] ?? 0)) ?>" 
                                       class="inline-flex items-center justify-center w-full px-3 py-2 text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors"
                                       onclick="return confirm('确定删除此菜单项及其子菜单吗？')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- 子菜单列表 -->
                            <?php if (!empty($children)): ?>
                                <div class="mt-4 pl-6 border-l-2 border-slate-200 space-y-3">
                                    <p class="text-xs font-medium text-slate-500 mb-2">子菜单（<?= count($children) ?>个）</p>
                                    <?php 
                                    $childIndex = 0;
                                    foreach ($children as $child): 
                                    ?>
                                        <div class="bg-slate-50 rounded-lg p-3">
                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                                <input type="hidden" name="items[<?= $topIndex ?>][children][<?= $childIndex ?>][id]" value="<?= $child['id'] ?>">
                                                
                                                <div class="md:col-span-3">
                                                    <input type="text" name="items[<?= $topIndex ?>][children][<?= $childIndex ?>][title]" required
                                                           value="<?= h($child['title']) ?>"
                                                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                                           placeholder="子菜单文字">
                                                </div>
                                                
                                                <div class="md:col-span-4">
                                                    <input type="text" name="items[<?= $topIndex ?>][children][<?= $childIndex ?>][url]" required
                                                           value="<?= h($child['url']) ?>"
                                                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                                           placeholder="链接地址">
                                                </div>
                                                
                                                <div class="md:col-span-2">
                                                    <input type="number" name="items[<?= $topIndex ?>][children][<?= $childIndex ?>][sort_order]"
                                                           value="<?= $child['sort_order'] ?? 0 ?>"
                                                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                                           placeholder="排序">
                                                </div>
                                                
                                                <div class="md:col-span-2">
                                                    <select name="items[<?= $topIndex ?>][children][<?= $childIndex ?>][target]" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                                                        <option value="_self" <?= ($child['target'] ?? '_self') === '_self' ? 'selected' : '' ?>>当前窗口</option>
                                                        <option value="_blank" <?= ($child['target'] ?? '') === '_blank' ? 'selected' : '' ?>>新窗口</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="md:col-span-1">
                                                    <a href="<?= url('/admin/appearance/menus/delete-item?id=' . $child['id'] . '&menu_id=' . ($menu['id'] ?? 0)) ?>" 
                                                       class="inline-flex items-center justify-center w-full px-3 py-2 text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors"
                                                       onclick="return confirm('确定删除此子菜单吗？')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php 
                                    $childIndex++;
                                    endforeach; 
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- 添加子菜单按钮 -->
                            <div class="mt-3 pt-3 border-t border-slate-100">
                                <a href="<?= url('/admin/appearance/menus/add-child?parent_id=' . $topItem['id'] . '&menu_id=' . ($menu['id'] ?? 0)) ?>" 
                                   class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                    <i class="fas fa-plus-circle"></i>
                                    添加子菜单
                                </a>
                            </div>
                        </div>
                    </div>
                <?php 
                $topIndex++;
                endforeach; 
                ?>
            </div>

            <!-- 添加顶级菜单按钮 -->
            <?php if ($isEdit): ?>
                <div class="mt-4 text-center">
                    <a href="<?= url('/admin/appearance/menus/add-item?menu_id=' . $menu['id']) ?>" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/25">
                        <i class="fas fa-plus"></i>
                        添加顶级菜单项
                    </a>
                </div>
            <?php else: ?>
                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    请先保存菜单基本信息，然后才能添加菜单项
                </div>
            <?php endif; ?>
        </div>

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

<script>
// 自动生成标识符
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
</script>
