<?php
/**
 * 页面模板：案例列表
 * 作用：展示客户成功案例列表与摘要信息。
 * 变量：$items（案例列表）、$title（页面标题）。
 */
?>
<!-- Hero Section -->
<section class="bg-gradient-to-r from-slate-900 to-slate-800 text-white">
    <div class="container mx-auto px-4 lg:px-8 py-12">
        <!-- Breadcrumb -->
        <nav class="text-sm mb-6" aria-label="breadcrumb">
            <ol class="flex items-center space-x-2">
                <li><a href="<?= url('/') ?>" class="text-white/80 hover:text-white">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs text-white/60"></i></li>
                <li class="text-white font-medium">Cases</li>
            </ol>
        </nav>
        
        <h1 class="text-3xl lg:text-4xl font-bold mb-3"><?= h($title) ?></h1>
        <p class="text-white/90 max-w-2xl">If you are interested in this solution or have similar needs, please contact our expert team.</p>
    </div>
</section>

<section class="py-12 lg:py-16">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php foreach ($items as $item): ?>
                <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    <?php if (!empty($item['cover'])): ?>
                        <a href="<?= h($item['url']) ?>" class="block aspect-video overflow-hidden">
                            <img src="<?= asset_url(h($item['cover'])) ?>" 
                                 alt="<?= h($item['title']) ?>" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        </a>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            <a href="<?= h($item['url']) ?>" class="hover:text-brand-600 transition-colors">
                                <?= h($item['title']) ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 mb-4 line-clamp-3">
                            <?= h($item['summary']) ?>
                        </p>
                        <a href="<?= h($item['url']) ?>" 
                           class="inline-flex items-center px-4 py-2 bg-brand-100 text-brand-700 rounded-lg font-medium hover:bg-brand-200 transition-colors">
                            Read More →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
