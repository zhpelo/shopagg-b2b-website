<?php
/**
 * 页面模板：产品列表
 * 作用：展示产品分类筛选与产品列表内容。
 * 变量：$categories（分类树）、$current_category（当前分类）、$items（产品列表）。
 * 注意：包含分类树递归渲染函数。
 */
$categories = $categories ?? [];
$currentCategory = $current_category ?? null;

if (!function_exists('renderProductCategoryList')) {
    function renderProductCategoryList($items, $currentCategoryId, $level = 0): void {
        if (empty($items)) return;
        foreach ($items as $cat):
            $isActive = $currentCategoryId === (int)$cat['id'];
            $hasChildren = !empty($cat['children']);
            $paddingLeft = 1 + ($level * 1);
?>
    <a href="<?= h(product_category_url($cat)) ?>"
       class="flex items-center px-4 py-3 text-sm transition-colors <?= $isActive ? 'bg-amber-50 text-amber-600 border-l-4 border-amber-500 font-semibold' : 'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent' ?>"
       style="padding-left: <?= $paddingLeft ?>rem;">
        <?php if ($level > 0): ?>
            <span class="text-gray-300 mr-2">└</span>
        <?php endif; ?>
        <i class="fas fa-<?= $hasChildren ? 'folder' : 'box' ?> w-5 mr-2 <?= $isActive ? 'text-amber-500' : 'text-gray-400' ?>"></i>
        <?= h($cat['name']) ?>
    </a>
    <?php
            if ($hasChildren) {
                renderProductCategoryList($cat['children'], $currentCategoryId, $level + 1);
            }
        endforeach;
    }
}
?>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-brand-700 via-brand-600 to-slate-900 text-white">
    <div class="container mx-auto px-4 py-9 sm:py-12 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-5 overflow-x-auto pb-1 text-sm" aria-label="breadcrumb">
            <ol class="flex min-w-max items-center space-x-2">
                <li><a href="<?= url('/') ?>" class="text-white/80 hover:text-white">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs text-white/60"></i></li>
                <li><a href="<?= url('/products') ?>" class="<?= !$currentCategory ? 'text-white font-medium' : 'text-white/80 hover:text-white' ?>">Products</a></li>
                <?php if ($currentCategory): ?>
                    <li><i class="fas fa-chevron-right text-xs text-white/60"></i></li>
                    <li class="text-white font-medium"><?= h($currentCategory['name']) ?></li>
                <?php endif; ?>
            </ol>
        </nav>
        
        <h1 class="mb-3 text-3xl font-bold leading-tight lg:text-4xl"><?= h($title) ?></h1>
        <p class="max-w-2xl text-sm leading-6 text-white/90 sm:text-base">
            <?php if ($currentCategory && !empty($currentCategory['description'])): ?>
                <?= h($currentCategory['description']) ?>
            <?php else: ?>
                <?= nl2br(h(block('product_list', 'text'))) ?>
            <?php endif; ?>
        </p>
    </div>
</section>

<section class="py-8 lg:py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
            <!-- Sidebar -->
            <aside class="w-full flex-shrink-0 lg:w-64">
                <?php if (!empty($categories)): ?>
                <!-- Categories -->
                <details class="mb-4 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm lg:mb-6 lg:block" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between bg-gradient-to-r from-brand-600 to-slate-800 px-4 py-4 text-white lg:pointer-events-none">
                        <span class="flex items-center font-bold">
                            <i class="fas fa-folder-open mr-2"></i>
                            <?= h(block('product_list', 'categories_title')) ?>
                        </span>
                        <i class="fas fa-chevron-down text-xs lg:hidden"></i>
                    </summary>
                    <div class="max-h-72 overflow-y-auto lg:max-h-none lg:overflow-visible">
                        <a href="<?= url('/products') ?>"
                           class="flex items-center px-4 py-3 text-sm transition-colors <?= !$currentCategory ? 'bg-amber-50 text-amber-600 border-l-4 border-amber-500 font-semibold' : 'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent' ?>">
                            <i class="fas fa-th-large w-5 mr-2 <?= !$currentCategory ? 'text-amber-500' : 'text-gray-400' ?>"></i>
                            <?= h(block('product_list', 'all_text')) ?>
                        </a>
                        <?php renderProductCategoryList($categories, $currentCategory ? (int)$currentCategory['id'] : 0); ?>
                    </div>
                </details>
                <?php endif; ?>

                <!-- Quick Quote Card -->
                <div class="rounded-xl bg-gradient-to-br from-slate-900 to-brand-700 p-5 text-white lg:p-6">
                    <h3 class="font-bold text-lg mb-3 flex items-center">
                        <i class="fas fa-file-invoice mr-2"></i>
                        <?= h(block('product_list', 'quote_title')) ?>
                    </h3>
                    <p class="text-white/90 text-sm mb-4">
                        <?= nl2br(h(block('product_list', 'quote_text'))) ?>
                    </p>
                    <a href="<?= url('/contact') ?>" class="block w-full text-center px-4 py-2.5 border-2 border-white text-white font-medium rounded-lg hover:bg-white hover:text-slate-900 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>
                        <?= h(block('product_list', 'quote_btn')) ?>
                    </a>
                </div>
            </aside>

            <!-- Product Grid -->
            <div class="flex-1">
                <?php if (empty($items)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 py-16 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center rounded-full bg-gray-100 text-gray-400">
                            <i class="fas fa-box-open text-3xl"></i>
                        </div>
                        <p class="text-gray-500 mb-4"><?= h(block('product_list', 'empty_text')) ?></p>
                        <?php if ($currentCategory): ?>
                            <a href="<?= url('/products') ?>" class="inline-flex items-center px-6 py-2.5 bg-amber-100 text-amber-700 font-medium rounded-lg hover:bg-amber-200 transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>
                                <?= h(block('product_list', 'all_text')) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($items as $item): ?>
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow flex flex-col h-full">
                                <a href="<?= h($item['url']) ?>" class="block aspect-[4/3] overflow-hidden bg-gray-100 sm:aspect-square">
                                    <img src="<?= h(get_image_url($item['cover'] ?? null, 400, 400, (string)($item['title'] ?? 'Product'))) ?>" 
                                         alt="<?= h($item['title']) ?>" 
                                         class="w-full h-full object-cover hover:scale-105 transition-transform"
                                         loading="lazy"
                                         decoding="async">
                                </a>
                                <div class="p-5 flex-grow flex flex-col">
                                    <div class="mb-3">
                                        <?php if (!empty($item['category_name'])): ?>
                                            <a href="<?= h(product_category_url($item)) ?>"
                                               class="inline-block px-3 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded-full">
                                                <?= h($item['category_name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="inline-block px-3 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                                                Uncategorized
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                        <a href="<?= h($item['url']) ?>" class="hover:text-brand-600 transition-colors">
                                            <?= h($item['title']) ?>
                                        </a>
                                    </h3>
                                    <p class="text-gray-500 text-sm line-clamp-2 flex-grow mb-4">
                                        <?= h($item['summary']) ?>
                                    </p>
                                    <a href="<?= h($item['url']) ?>" class="block w-full text-center px-4 py-2 border border-brand-600 text-brand-600 font-medium rounded-lg hover:bg-brand-600 hover:text-white transition-colors text-sm">
                                        <?= h(block('product_list', 'detail_text')) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
