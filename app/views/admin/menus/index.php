<?php
/**
 * 菜单管理列表页
 * @var array $menus 菜单列表
 */
?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-violet-600">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">菜单管理</h1>
                <p class="text-indigo-100 mt-1">管理网站导航菜单，支持多级菜单结构</p>
            </div>
            <a href="<?= url('/admin/appearance/menus/create') ?>" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-xl shadow-lg hover:bg-indigo-50 transition-colors">
                <i class="fas fa-plus"></i>
                新建菜单
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="p-6">
        <?php if (empty($menus)): ?>
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fas fa-bars text-3xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">暂无菜单</h3>
                <p class="text-slate-500 mb-6">还没有创建任何导航菜单</p>
                <a href="<?= url('/admin/appearance/menus/create') ?>" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus"></i>
                    创建第一个菜单
                </a>
            </div>
        <?php else: ?>
            <!-- Menus Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($menus as $menu): ?>
                    <div class="group border border-slate-200 rounded-xl hover:shadow-lg transition-all duration-300 bg-white overflow-hidden">
                        <!-- Card Header -->
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-bold text-slate-900 truncate">
                                        <?= h($menu['name']) ?>
                                    </h3>
                                    <p class="text-sm text-slate-500 mt-1">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fas fa-link text-xs"></i>
                                            <?= h($menu['slug']) ?>
                                        </span>
                                        <span class="mx-2 text-slate-300">|</span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fas fa-map-marker-alt text-xs"></i>
                                            <?= $menu['location'] === 'header' ? '顶部导航' : ($menu['location'] === 'footer' ? '页脚' : '其他') ?>
                                        </span>
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $menu['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= $menu['status'] === 'active' ? '启用' : '禁用' ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5">
                            <?php if (!empty($menu['description'])): ?>
                                <p class="text-sm text-slate-600 mb-4 line-clamp-2">
                                    <?= h($menu['description']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="flex items-center gap-6 text-sm text-slate-500">
                                <span class="inline-flex items-center gap-2">
                                    <i class="fas fa-list text-slate-400"></i>
                                    <?= $menu['item_count'] ?> 个菜单项
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <i class="fas fa-sort-numeric-down text-slate-400"></i>
                                    排序: <?= $menu['sort_order'] ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/30 flex items-center justify-between">
                            <span class="text-xs text-slate-400">
                                更新于 <?= format_date($menu['updated_at']) ?>
                            </span>
                            <div class="flex items-center gap-2">
                                <a href="<?= url('/admin/appearance/menus/edit?id=' . $menu['id']) ?>" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                                    <i class="fas fa-edit"></i>
                                    编辑
                                </a>
                                <button type="button"
                                        onclick="confirmDelete(<?= $menu['id'] ?>, '<?= h($menu['name']) ?>')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                    删除
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="text-center">
            <div class="w-14 h-14 mx-auto mb-4 flex items-center justify-center rounded-full bg-rose-100 text-rose-600">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">确认删除</h3>
            <p class="text-slate-600 mb-6">
                确定要删除菜单「<span id="delete-menu-name" class="font-medium text-slate-900"></span>」吗？<br>
                <span class="text-rose-500 text-sm">此操作不可恢复，关联的所有菜单项也将被删除。</span>
            </p>
            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="closeDeleteModal()" 
                        class="px-5 py-2.5 text-slate-700 font-medium bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                    取消
                </button>
                <a id="delete-confirm-btn" href="#"
                   class="px-5 py-2.5 text-white font-medium bg-rose-600 rounded-xl hover:bg-rose-700 transition-colors">
                    确认删除
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('delete-menu-name').textContent = name;
    document.getElementById('delete-confirm-btn').href = '<?= url('/admin/appearance/menus/delete') ?>?id=' + id;
    document.getElementById('delete-modal').classList.remove('hidden');
    document.getElementById('delete-modal').classList.add('flex');
}

function closeDeleteModal() {
    document.getElementById('delete-modal').classList.add('hidden');
    document.getElementById('delete-modal').classList.remove('flex');
}
</script>
