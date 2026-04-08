<?php
/**
 * 页面模板：案例详情
 * 作用：展示单个案例的详情内容与侧边信息。
 * 变量：$item（案例数据）。
 */
?>
<!-- Hero Section -->
<section class="bg-gradient-to-r from-brand-600 to-brand-800 text-white">
    <div class="container mx-auto px-4 lg:px-8 py-12 lg:py-16">
        <div class="flex items-center">
            <div class="max-w-3xl">
                <span class="inline-block px-3 py-1 bg-white/20 text-white text-sm font-medium rounded-full mb-4">
                    Success Case
                </span>
                <h1 class="text-3xl lg:text-5xl font-bold mb-4"><?= h($item['title']) ?></h1>
                <?php if (!empty($item['summary'])): ?>
                    <p class="text-xl text-white/90"><?= h($item['summary']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-12 lg:py-16">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content -->
            <div class="lg:w-8/12">
                <?php if (!empty($item['cover'])): ?>
                    <figure class="rounded-2xl overflow-hidden shadow-lg mb-8">
                        <img src="<?= asset_url(h($item['cover'])) ?>" 
                             alt="<?= h($item['title']) ?>" 
                             class="w-full h-auto">
                    </figure>
                <?php endif; ?>
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Project Details</h2>
                    <article class="rich-content">
                        <?= process_rich_text($item['content']) ?>
                    </article>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:w-4/12">
                <div class="space-y-6">
                    <!-- About This Case -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">About This Case</h3>
                        <div class="mb-4">
                            <label class="block text-sm text-gray-500 mb-1">Publish Time</label>
                            <p class="font-medium text-gray-900"><?= format_date($item['created_at'], 'Y-m-d') ?></p>
                        </div>
                        <hr class="border-gray-100 my-4">
                        <p class="text-gray-600 mb-4">
                            If you are interested in this solution or have similar needs, please contact our expert team.
                        </p>
                        <a href="<?= url('/contact') ?>" 
                           class="block w-full text-center px-6 py-3 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors">
                            Request Quote
                        </a>
                    </div>

                    <!-- Back Button -->
                    <a href="<?= url('/cases') ?>" 
                       class="block w-full text-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                        Back to All Cases
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
