<?php
/**
 * 轮播图表单页（新建/编辑）
 * @var string $action 表单提交地址
 * @var array|null $slider 轮播图数据（编辑时）
 * @var array $items 轮播图片列表（编辑时）
 */
$isEdit = $slider !== null;
$title = $isEdit ? '编辑轮播图' : '新建轮播图';
?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-violet-600">
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/appearance/sliders') ?>" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 text-white hover:bg-white/30 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white"><?= $title ?></h1>
                <p class="text-indigo-100 mt-1"><?= $isEdit ? '修改轮播图设置和图片' : '创建一个新的轮播图区块' ?></p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= $action ?>" method="post" class="p-6" id="slider-form">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Basic Settings -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                    <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-cog text-indigo-500"></i>
                        基础设置
                    </h2>

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                            轮播图名称 <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" required
                               value="<?= h($slider['name'] ?? '') ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                               placeholder="如：首页轮播图">
                    </div>

                    <!-- Slug -->
                    <div class="mb-4">
                        <label for="slug" class="block text-sm font-semibold text-slate-700 mb-2">
                            标识符 <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="slug" name="slug" required
                               value="<?= h($slider['slug'] ?? '') ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all font-mono text-sm"
                               placeholder="如：home-hero">
                        <p class="text-xs text-slate-500 mt-1.5">
                            <i class="fas fa-info-circle mr-1"></i>
                            用于模板调用，只能包含字母、数字、连字符和下划线
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">
                            描述
                        </label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all resize-none"
                                  placeholder="轮播图的用途说明..."><?= h($slider['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Sort Order -->
                    <div class="mb-4">
                        <label for="sort_order" class="block text-sm font-semibold text-slate-700 mb-2">
                            排序
                        </label>
                        <input type="number" id="sort_order" name="sort_order"
                               value="<?= $slider['sort_order'] ?? 0 ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                               placeholder="0">
                        <p class="text-xs text-slate-500 mt-1.5">数字越小排序越靠前</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">状态</label>
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="active" 
                                       <?= ($slider['status'] ?? 'active') === 'active' ? 'checked' : '' ?>
                                       class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700">启用</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="inactive" 
                                       <?= ($slider['status'] ?? '') === 'inactive' ? 'checked' : '' ?>
                                       class="w-4 h-4 text-slate-400 border-slate-300 focus:ring-slate-400">
                                <span class="text-sm text-slate-700">禁用</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Slider Items -->
            <div class="lg:col-span-2">
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-images text-indigo-500"></i>
                            轮播图片
                        </h2>
                        <button type="button" onclick="addSliderItem()"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-plus"></i>
                            添加图片
                        </button>
                    </div>

                    <p class="text-sm text-slate-500 mb-4">
                        <i class="fas fa-info-circle mr-1"></i>
                        拖拽可调整图片顺序，建议图片尺寸：1920 x 600 像素或更大
                    </p>

                    <!-- Slider Items Container -->
                    <div id="slider-items-container" class="space-y-4">
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $index => $item): ?>
                                <?= renderSliderItem($item, $index) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Empty State -->
                    <div id="empty-items-state" class="text-center py-12 border-2 border-dashed border-slate-300 rounded-xl <?= !empty($items) ? 'hidden' : '' ?>">
                        <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fas fa-image text-2xl"></i>
                        </div>
                        <p class="text-slate-500">还没有添加任何图片</p>
                        <button type="button" onclick="addSliderItem()"
                                class="mt-3 text-indigo-600 font-medium hover:text-indigo-700">
                            添加第一张图片
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 pt-6 border-t border-slate-200 flex items-center justify-end gap-4">
            <a href="<?= url('/admin/appearance/sliders') ?>" 
               class="px-6 py-2.5 text-slate-700 font-medium bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                取消
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/25">
                <i class="fas fa-save mr-2"></i>
                <?= $isEdit ? '保存修改' : '创建轮播图' ?>
            </button>
        </div>
    </form>
</div>

<!-- Slider Item Template -->
<template id="slider-item-template">
    <div class="slider-item bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-start gap-4">
            <!-- Drag Handle -->
            <div class="slider-drag-handle flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 cursor-move hover:bg-slate-200 hover:text-slate-600">
                <i class="fas fa-grip-vertical"></i>
            </div>

            <!-- Image Preview & Selector -->
            <div class="flex-shrink-0">
                <div class="relative w-32 h-20 rounded-lg bg-slate-100 overflow-hidden border border-slate-200">
                    <img src="" alt="" class="slider-preview-image w-full h-full object-cover hidden">
                    <div class="slider-placeholder absolute inset-0 flex items-center justify-center text-slate-400">
                        <i class="fas fa-image text-xl"></i>
                    </div>
                </div>
                <button type="button" onclick="selectImageForItem(this)"
                        class="mt-2 w-full text-xs font-medium text-indigo-600 hover:text-indigo-700 py-1">
                    <i class="fas fa-folder-open mr-1"></i>选择图片
                </button>
                <input type="hidden" name="item_image[]" class="item-image-input" value="">
            </div>

            <!-- Form Fields -->
            <div class="flex-1 min-w-0 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2">
                    <input type="text" name="item_title[]" 
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="主标题">
                </div>
                <div class="md:col-span-2">
                    <input type="text" name="item_subtitle[]" 
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="副标题">
                </div>
                <div>
                    <input type="text" name="item_link_url[]" 
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="链接地址（如：/products）">
                </div>
                <div>
                    <input type="text" name="item_link_text[]" 
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="链接文字（默认：View Details）">
                </div>
                <input type="hidden" name="item_sort_order[]" class="item-sort-order" value="">
            </div>

            <!-- Delete Button -->
            <button type="button" onclick="removeSliderItem(this)"
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
    const container = document.getElementById('slider-items-container');
    if (container && typeof Sortable !== 'undefined') {
        sortableInstance = new Sortable(container, {
            handle: '.slider-drag-handle',
            animation: 150,
            onEnd: updateSortOrders
        });
    }
}

function updateSortOrders() {
    document.querySelectorAll('.slider-item').forEach((item, index) => {
        item.querySelector('.item-sort-order').value = index;
    });
}

function addSliderItem() {
    const template = document.getElementById('slider-item-template');
    const container = document.getElementById('slider-items-container');
    const emptyState = document.getElementById('empty-items-state');
    
    const clone = template.content.cloneNode(true);
    const itemDiv = clone.querySelector('.slider-item');
    
    // Set initial sort order
    const index = container.children.length;
    itemDiv.querySelector('.item-sort-order').value = index;
    
    container.appendChild(itemDiv);
    
    // Hide empty state
    if (emptyState) {
        emptyState.classList.add('hidden');
    }
    
    updateSortOrders();
}

function removeSliderItem(btn) {
    const item = btn.closest('.slider-item');
    item.remove();
    
    // Show empty state if no items
    const container = document.getElementById('slider-items-container');
    const emptyState = document.getElementById('empty-items-state');
    if (container.children.length === 0 && emptyState) {
        emptyState.classList.remove('hidden');
    }
    
    updateSortOrders();
}

function selectImageForItem(btn) {
    const item = btn.closest('.slider-item');
    
    // Use the global media library modal
    if (typeof openMediaLibrary === 'function') {
        openMediaLibrary(function(url) {
            if (url) {
                const input = item.querySelector('.item-image-input');
                const preview = item.querySelector('.slider-preview-image');
                const placeholder = item.querySelector('.slider-placeholder');
                
                input.value = url;
                preview.src = url;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                
                // Update button text
                btn.innerHTML = '<i class="fas fa-folder-open mr-1"></i>更换图片';
            }
        }, false, { type: 'image' });
    }
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
            slugInput.value = slug || 'slider-' + Date.now();
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initSortable();
    
    // Initialize existing item previews
    document.querySelectorAll('.slider-item').forEach(item => {
        const input = item.querySelector('.item-image-input');
        const preview = item.querySelector('.slider-preview-image');
        const placeholder = item.querySelector('.slider-placeholder');
        
        if (input && input.value) {
            preview.src = input.value;
            preview.classList.remove('hidden');
            placeholder?.classList.add('hidden');
        }
    });
});
</script>

<?php
/**
 * 渲染单个轮播图片项
 */
function renderSliderItem(array $item, int $index): string {
    ob_start();
    ?>
    <div class="slider-item bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-start gap-4">
            <!-- Drag Handle -->
            <div class="slider-drag-handle flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 cursor-move hover:bg-slate-200 hover:text-slate-600">
                <i class="fas fa-grip-vertical"></i>
            </div>

            <!-- Image Preview & Selector -->
            <div class="flex-shrink-0">
                <div class="relative w-32 h-20 rounded-lg bg-slate-100 overflow-hidden border border-slate-200">
                    <img src="<?= h($item['image']) ?>" alt="" class="slider-preview-image w-full h-full object-cover <?= empty($item['image']) ? 'hidden' : '' ?>">
                    <div class="slider-placeholder absolute inset-0 flex items-center justify-center text-slate-400 <?= !empty($item['image']) ? 'hidden' : '' ?>">
                        <i class="fas fa-image text-xl"></i>
                    </div>
                </div>
                <button type="button" onclick="selectImageForItem(this)"
                        class="mt-2 w-full text-xs font-medium text-indigo-600 hover:text-indigo-700 py-1">
                    <i class="fas fa-folder-open mr-1"></i><?= empty($item['image']) ? '选择图片' : '更换图片' ?>
                </button>
                <input type="hidden" name="item_image[]" class="item-image-input" value="<?= h($item['image']) ?>">
            </div>

            <!-- Form Fields -->
            <div class="flex-1 min-w-0 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2">
                    <input type="text" name="item_title[]" 
                           value="<?= h($item['title']) ?>"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="主标题">
                </div>
                <div class="md:col-span-2">
                    <input type="text" name="item_subtitle[]" 
                           value="<?= h($item['subtitle']) ?>"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="副标题">
                </div>
                <div>
                    <input type="text" name="item_link_url[]" 
                           value="<?= h($item['link_url']) ?>"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="链接地址（如：/products）">
                </div>
                <div>
                    <input type="text" name="item_link_text[]" 
                           value="<?= h($item['link_text']) ?>"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                           placeholder="链接文字（默认：View Details）">
                </div>
                <input type="hidden" name="item_sort_order[]" class="item-sort-order" value="<?= $item['sort_order'] ?? $index ?>">
            </div>

            <!-- Delete Button -->
            <button type="button" onclick="removeSliderItem(this)"
                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
