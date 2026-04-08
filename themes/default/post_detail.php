<?php
/**
 * 页面模板：文章详情
 * 作用：展示文章正文、时间、分类等信息。
 * 变量：$item（文章数据）、$category（分类数据）。
 */
$category = $category ?? null;
?>
<section class="py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="text-sm mb-6" aria-label="breadcrumb">
            <ol class="flex items-center space-x-2 text-gray-500">
                <li><a href="<?= url('/') ?>" class="hover:text-brand-600">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= url('/blog') ?>" class="hover:text-brand-600">Blog</a></li>
                <?php if ($category): ?>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li><a href="<?= url('/blog') ?>?category=<?= (int)$category['id'] ?>" class="hover:text-brand-600"><?= h($category['name']) ?></a></li>
                <?php endif; ?>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-gray-900 font-medium"><?= h($item['title']) ?></li>
            </ol>
        </nav>

        <div class="max-w-4xl mx-auto">
            <!-- Article Header -->
            <header class="mb-8">
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4"><?= h($item['title']) ?></h1>
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    <span class="flex items-center">
                        <i class="far fa-calendar mr-2"></i>
                        <?= format_date($item['created_at'], 'Y-m-d') ?>
                    </span>
                    <?php if ($category): ?>
                        <a href="<?= url('/blog') ?>?category=<?= (int)$category['id'] ?>" 
                           class="inline-flex items-center px-3 py-1 bg-brand-100 text-brand-700 rounded-full text-xs font-medium">
                            <i class="fas fa-folder mr-1"></i>
                            <?= h($category['name']) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Featured Image -->
            <?php if (!empty($item['cover'])): ?>
                <figure class="mb-8 rounded-xl overflow-hidden shadow-lg">
                    <img src="<?= asset_url(h($item['cover'])) ?>" 
                         alt="<?= h($item['title']) ?>" 
                         class="w-full h-auto">
                </figure>
            <?php endif; ?>

            <!-- Article Content -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:p-10 mb-8">
                <article class="rich-content">
                    <?= process_rich_text($item['content']) ?>
                </article>
            </div>

            <!-- Category Tag -->
            <?php if ($category): ?>
                <div class="mb-8">
                    <span class="text-gray-500 mr-2">Category:</span>
                    <a href="<?= url('/blog') ?>?category=<?= (int)$category['id'] ?>" 
                       class="inline-flex items-center px-4 py-2 bg-brand-100 text-brand-700 rounded-full font-medium hover:bg-brand-200 transition-colors">
                        <i class="fas fa-folder mr-2"></i>
                        <?= h($category['name']) ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Bottom Actions -->
            <div class="flex flex-wrap justify-between items-center py-6 border-t border-gray-200 gap-4">
                <div class="flex flex-wrap gap-3">
                    <?php if ($category): ?>
                        <a href="<?= url('/blog') ?>?category=<?= (int)$category['id'] ?>" 
                           class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            <i class="fas fa-folder mr-2"></i>
                            More in this category
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('/blog') ?>" 
                       class="inline-flex items-center px-4 py-2 bg-brand-100 text-brand-700 rounded-lg font-medium hover:bg-brand-200 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Blog List
                    </a>
                </div>
                <a href="<?= url('/contact') ?>" 
                   class="inline-flex items-center px-6 py-2 bg-brand-600 text-white rounded-lg font-medium hover:bg-brand-700 transition-colors shadow-md">
                    <i class="fas fa-envelope mr-2"></i>
                    Contact
                </a>
            </div>
        </div>
    </div>
</section>
