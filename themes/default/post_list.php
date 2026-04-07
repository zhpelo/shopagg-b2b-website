<?php
/**
 * 页面模板：文章列表
 * 作用：展示文章分类筛选、文章列表与侧边栏。
 * 变量：$categories（分类树）、$current_category（当前分类）、$items（文章列表）。
 * 注意：包含分类树递归渲染函数。
 */
$categories = $categories ?? [];
$currentCategory = $current_category ?? null;

// 递归渲染分类树
function renderCategoryList($items, $currentCategoryId, $level = 0)
{
    if (empty($items)) return;
    foreach ($items as $cat):
        $isActive = $currentCategoryId === (int)$cat['id'];
        $hasChildren = !empty($cat['children']);
        $paddingLeft = 1 + ($level * 1);
?>
        <a href="<?= url('/blog') ?>?category=<?= (int)$cat['id'] ?>"
           class="flex items-center px-4 py-3 text-sm transition-colors <?= $isActive ? 'bg-emerald-50 text-emerald-600 border-l-4 border-emerald-500 font-semibold' : 'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent' ?>"
           style="padding-left: <?= $paddingLeft ?>rem;">
            <?php if ($level > 0): ?>
                <span class="text-gray-300 mr-2">└</span>
            <?php endif; ?>
            <i class="fas fa-<?= $hasChildren ? 'folder' : 'file-alt' ?> w-5 mr-2 <?= $isActive ? 'text-emerald-500' : 'text-gray-400' ?>"></i>
            <?= h($cat['name']) ?>
        </a>
<?php
        if ($hasChildren) {
            renderCategoryList($cat['children'], $currentCategoryId, $level + 1);
        }
    endforeach;
}
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-amber-400 to-orange-500 text-white">
    <div class="container mx-auto px-4 lg:px-8 py-12">
        <!-- Breadcrumb -->
        <nav class="text-sm mb-6" aria-label="breadcrumb">
            <ol class="flex items-center space-x-2">
                <li><a href="<?= url('/') ?>" class="text-white/80 hover:text-white">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs text-white/60"></i></li>
                <li><a href="<?= url('/blog') ?>" class="<?= !$currentCategory ? 'text-white font-medium' : 'text-white/80 hover:text-white' ?>">Blog</a></li>
                <?php if ($currentCategory): ?>
                    <li><i class="fas fa-chevron-right text-xs text-white/60"></i></li>
                    <li class="text-white font-medium"><?= h($currentCategory['name']) ?></li>
                <?php endif; ?>
            </ol>
        </nav>
        
        <h1 class="text-3xl lg:text-4xl font-bold mb-3"><?= h($title) ?></h1>
        <p class="text-white/90 max-w-2xl">
            <?php if ($currentCategory): ?>
                <?= h($currentCategory['description'] ?? 'Get the latest industry insights and company news') ?>
            <?php else: ?>
                Get the latest industry insights and company news
            <?php endif; ?>
        </p>
    </div>
</section>

<section class="py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content: Article List -->
            <div class="flex-1 lg:w-8/12">
                <?php if (empty($items)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 py-16 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center rounded-full bg-gray-100 text-gray-400">
                            <i class="fas fa-file-alt text-3xl"></i>
                        </div>
                        <p class="text-gray-500 mb-4">No articles found</p>
                        <?php if ($currentCategory): ?>
                            <a href="<?= url('/blog') ?>" class="inline-flex items-center px-6 py-2.5 bg-brand-100 text-brand-700 font-medium rounded-lg hover:bg-brand-200 transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>
                                View All Articles
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($items as $item): ?>
                            <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                                <div class="flex flex-col md:flex-row">
                                    <?php if (!empty($item['cover'])): ?>
                                        <div class="md:w-1/3 flex-shrink-0">
                                            <a href="<?= h($item['url']) ?>" class="block h-48 md:h-full">
                                                <img src="<?= asset_url($item['cover']) ?>" 
                                                     alt="<?= h($item['title']) ?>" 
                                                     class="w-full h-full object-cover">
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="p-6 flex-grow">
                                        <div class="flex flex-wrap items-center gap-3 mb-3 text-sm text-gray-500">
                                            <span class="flex items-center">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                <?= format_date($item['created_at'], 'Y-m-d') ?>
                                            </span>
                                            <?php if (!empty($item['category_name'])): ?>
                                                <a href="<?= url('/blog') ?>?category=<?= (int)$item['category_id'] ?>" 
                                                   class="inline-flex items-center px-3 py-1 bg-brand-100 text-brand-700 rounded-full text-xs font-medium">
                                                    <?= h($item['category_name']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">
                                            <a href="<?= h($item['url']) ?>" class="hover:text-brand-600 transition-colors">
                                                <?= h($item['title']) ?>
                                            </a>
                                        </h2>
                                        <p class="text-gray-600 mb-4 line-clamp-2">
                                            <?= h($item['summary']) ?>
                                        </p>
                                        <a href="<?= h($item['url']) ?>" class="inline-flex items-center text-brand-600 font-semibold hover:text-brand-700 transition-colors">
                                            Read Full Article →
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="w-full lg:w-4/12 flex-shrink-0">
                <?php if (!empty($categories)): ?>
                <!-- Categories -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-4 py-4 bg-gradient-to-r from-emerald-500 to-teal-600">
                        <h3 class="font-bold text-white flex items-center">
                            <i class="fas fa-folder-open mr-2"></i>
                            Categories
                        </h3>
                    </div>
                    <a href="<?= url('/blog') ?>" 
                       class="flex items-center px-4 py-3 text-sm transition-colors <?= !$currentCategory ? 'bg-emerald-50 text-emerald-600 border-l-4 border-emerald-500 font-semibold' : 'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent' ?>">
                        <i class="fas fa-list w-5 mr-2 <?= !$currentCategory ? 'text-emerald-500' : 'text-gray-400' ?>"></i>
                        All Articles
                    </a>
                    <?php renderCategoryList($categories, $currentCategory ? (int)$currentCategory['id'] : 0); ?>
                </div>
                <?php endif; ?>

                <!-- Quick Contact Card -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white">
                    <h3 class="font-bold text-lg mb-3 flex items-center">
                        <i class="fas fa-headset mr-2"></i>
                        Need Help?
                    </h3>
                    <p class="text-white/90 text-sm mb-4">
                        If you have any questions, feel free to contact our professional team.
                    </p>
                    <a href="<?= url('/contact') ?>" class="block w-full text-center px-4 py-2.5 border-2 border-white text-white font-medium rounded-lg hover:bg-white hover:text-indigo-600 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>
                        Contact
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>
