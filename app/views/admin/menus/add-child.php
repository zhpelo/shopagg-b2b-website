<?php
/**
 * 添加子菜单项
 * @var int $menuId
 * @var array $parent
 * @var string $action
 */
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-2xl mx-auto">
    <!-- Header -->
    <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-violet-600">
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/appearance/menus/edit?id=' . $menuId) ?>" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 text-white hover:bg-white/30 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">添加子菜单</h1>
                <p class="text-indigo-100 mt-1">在「<?= h($parent['title']) ?>」下添加子菜单</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= $action ?>" method="post" class="p-6">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                <div>
                    <p class="text-amber-800 font-medium">父级菜单</p>
                    <p class="text-amber-700"><?= h($parent['title']) ?> (<?= h($parent['url']) ?>)</p>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    子菜单文字 <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                       placeholder="如：所有产品">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    链接地址 <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="url" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                       placeholder="如：/products/all">
                <p class="text-xs text-slate-500 mt-1">可以是相对路径或完整 URL</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">排序</label>
                    <input type="number" name="sort_order" value="0"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                    <p class="text-xs text-slate-500 mt-1">数字越小越靠前</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">打开方式</label>
                    <select name="target" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                        <option value="_self" selected>当前窗口</option>
                        <option value="_blank">新窗口</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-slate-200">
            <a href="<?= url('/admin/appearance/menus/edit?id=' . $menuId) ?>" 
               class="px-6 py-2.5 text-slate-700 font-medium bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                取消
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/25">
                <i class="fas fa-plus mr-2"></i>
                添加子菜单
            </button>
        </div>
    </form>
</div>
