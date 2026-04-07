<?php
/**
 * 页面模板：产品列表
 * 作用：展示产品分类筛选与产品列表内容。
 * 变量：$categories（分类树）、$current_category（当前分类）、$items（产品列表）。
 * 注意：包含分类树递归渲染函数。
 */
$categories = $categories ?? [];
$currentCategory = $current_category ?? null;

// 递归渲染分类树
function renderProductCategoryList($items, $currentCategoryId, $level = 0) {
    if (empty($items)) return;
    foreach ($items as $cat):
        $isActive = $currentCategoryId === (int)$cat['id'];
        $hasChildren = !empty($cat['children']);
        $paddingLeft = 1 + ($level * 1);
?>
    <a href="<?= url('/products') ?>?category=<?= (int)$cat['id'] ?>" 
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
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-rose-500 to-pink-600 text-white">
    <div class="container mx-auto px-4 lg:px-8 py-12">
        <!-- Breadcrumb -->
        <nav class="text-sm mb-6" aria-label="breadcrumb">
            <ol class="flex items-center space-x-2">
                <li><a href="<?= url('/') ?>" class="text-white/80 hover:text-white">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs text-white/60"></i></li>
                <li><a href="<?= url('/products') ?>" class="<?= !$currentCategory ? 'text-white font-medium' : 'text-white/80 hover:text-white' ?>">Products</a></li>
                <?php if ($currentCategory): ?>
                    <li><i class="fas fa-chevron-right text-xs text-white/60"></i></li>
                    <li class="text-white font-medium"><?= h($currentCategory['name']) ?></li>
                <?php endif; ?>
            </ol>
        </nav>
        
        <h1 class="text-3xl lg:text-4xl font-bold mb-3"><?= h($title) ?></h1>
        <p class="text-white/90 max-w-2xl">
            <?php if ($currentCategory && !empty($currentCategory['description'])): ?>
                <?= h($currentCategory['description']) ?>
            <?php else: ?>
                Browse our full range of products.
            <?php endif; ?>
        </p>
    </div>
</section>

<section class="py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="w-full lg:w-64 flex-shrink-0">
                <?php if (!empty($categories)): ?>
                <!-- Categories -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-4 py-4 bg-gradient-to-r from-amber-400 to-orange-500">
                        <h3 class="font-bold text-white flex items-center">
                            <i class="fas fa-folder-open mr-2"></i>
                            Categories
                        </h3>
                    </div>
                    <a href="<?= url('/products') ?>" 
                       class="flex items-center px-4 py-3 text-sm transition-colors <?= !$currentCategory ? 'bg-amber-50 text-amber-600 border-l-4 border-amber-500 font-semibold' : 'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent' ?>">
                        <i class="fas fa-th-large w-5 mr-2 <?= !$currentCategory ? 'text-amber-500' : 'text-gray-400' ?>"></i>
                        All Products
                    </a>
                    <?php renderProductCategoryList($categories, $currentCategory ? (int)$currentCategory['id'] : 0); ?>
                </div>
                <?php endif; ?>

                <!-- Quick Quote Card -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white">
                    <h3 class="font-bold text-lg mb-3 flex items-center">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Quick Quote
                    </h3>
                    <p class="text-white/90 text-sm mb-4">
                        Found what you need? Send an inquiry to get a quote now.
                    </p>
                    <a href="<?= url('/contact') ?>" class="block w-full text-center px-4 py-2.5 border-2 border-white text-white font-medium rounded-lg hover:bg-white hover:text-indigo-600 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>
                        Contact
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
                        <p class="text-gray-500 mb-4">No products found</p>
                        <?php if ($currentCategory): ?>
                            <a href="<?= url('/products') ?>" class="inline-flex items-center px-6 py-2.5 bg-amber-100 text-amber-700 font-medium rounded-lg hover:bg-amber-200 transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>
                                View All Products
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php foreach ($items as $item): ?>
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow flex flex-col h-full">
                                <a href="<?= h($item['url']) ?>" class="block aspect-square overflow-hidden bg-gray-100">
                                    <img src="<?= get_image_url($item['cover'] ?? null, 400, 400, h($item['title'])) ?>" 
                                         alt="<?= h($item['title']) ?>" 
                                         class="w-full h-full object-cover hover:scale-105 transition-transform">
                                </a>
                                <div class="p-5 flex-grow flex flex-col">
                                    <div class="mb-3">
                                        <?php if (!empty($item['category_name'])): ?>
                                            <a href="<?= url('/products') ?>?category=<?= (int)$item['category_id'] ?>" 
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
                                        View Details
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
