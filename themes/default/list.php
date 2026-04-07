<?php
/**
 * 页面模板：通用列表
 * 作用：用于文章/案例等列表页的统一卡片布局展示。
 * 变量：$title（页面标题）、$items（列表数据）、$show_image/$show_category（展示控制）。
 * 注意：$items 需包含 title/url/summary 字段。
 */
?>
<section class="py-12 lg:py-16">
    <div class="container mx-auto px-4 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8"><?= h($title) ?></h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as $item): ?>
                <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    <?php if (!empty($show_image) && !empty($item['cover'])): ?>
                        <a href="<?= h($item['url']) ?>" class="block aspect-square overflow-hidden">
                            <img src="<?= asset_url(h($item['cover'])) ?>" 
                                 alt="<?= h($item['title']) ?>" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform">
                        </a>
                    <?php endif; ?>
                    <div class="p-5">
                        <h3 class="font-bold text-gray-900 mb-2 line-clamp-1">
                            <a href="<?= h($item['url']) ?>" class="hover:text-brand-600 transition-colors">
                                <?= h($item['title']) ?>
                            </a>
                        </h3>
                        <?php if (!empty($show_category)): ?>
                            <span class="inline-block px-3 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full mb-2">
                                <?= h($item['category_name'] ?? '未分类') ?>
                            </span>
                        <?php endif; ?>
                        <p class="text-gray-600 text-sm line-clamp-3 mb-4"><?= h($item['summary']) ?></p>
                        <a href="<?= h($item['url']) ?>" 
                           class="inline-flex items-center px-4 py-2 bg-brand-100 text-brand-700 text-sm font-medium rounded-lg hover:bg-brand-200 transition-colors">
                            Read More
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
