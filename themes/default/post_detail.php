<?php
/**
 * 页面模板：文章详情
 * 作用：展示文章正文、时间、分类等信息。
 * 变量：$item（文章数据）、$category（分类数据）。
 */
$category = $category ?? null;
$previousPost = $previous_post ?? null;
$nextPost = $next_post ?? null;
$recommendedPosts = $recommended_posts ?? [];
?>
<section class="py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="text-sm mb-6 overflow-x-auto pb-1" aria-label="breadcrumb">
            <ol class="flex min-w-max items-center space-x-2 text-gray-500">
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
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4 leading-tight"><?= h($item['title']) ?></h1>
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
            <figure class="mb-8 rounded-xl overflow-hidden shadow-lg">
                <img src="<?= h(get_image_url($item['cover'] ?? null, 1200, 630, (string)($item['title'] ?? 'Article'))) ?>"
                     alt="<?= h($item['title']) ?>"
                     class="w-full h-auto"
                     loading="eager"
                     decoding="async"
                     fetchpriority="high">
            </figure>

            <!-- Article Content -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:p-10 mb-8">
                <article class="rich-content">
                    <?= process_rich_text($item['content']) ?>
                </article>
            </div>

            <!-- Article Navigation -->
            <?php if ($previousPost || $nextPost): ?>
                <nav class="mb-8 grid gap-4 border-y border-gray-200 py-6 md:grid-cols-2" aria-label="Article navigation">
                    <?php if ($previousPost): ?>
                        <a href="<?= url('/blog/' . $previousPost['slug']) ?>"
                           class="group flex min-h-28 items-center gap-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md">
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition group-hover:bg-brand-100 group-hover:text-brand-700">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span class="min-w-0">
                                <span class="mb-1 block text-sm font-semibold text-gray-500">上一篇</span>
                                <span class="line-clamp-2 text-base font-bold text-gray-900 transition group-hover:text-brand-700"><?= h($previousPost['title']) ?></span>
                            </span>
                        </a>
                    <?php else: ?>
                        <div class="hidden md:block"></div>
                    <?php endif; ?>

                    <?php if ($nextPost): ?>
                        <a href="<?= url('/blog/' . $nextPost['slug']) ?>"
                           class="group flex min-h-28 items-center justify-end gap-4 rounded-xl border border-gray-100 bg-white p-5 text-right shadow-sm transition hover:border-brand-200 hover:shadow-md">
                            <span class="min-w-0">
                                <span class="mb-1 block text-sm font-semibold text-gray-500">下一篇</span>
                                <span class="line-clamp-2 text-base font-bold text-gray-900 transition group-hover:text-brand-700"><?= h($nextPost['title']) ?></span>
                            </span>
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition group-hover:bg-brand-100 group-hover:text-brand-700">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

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
            <div class="flex flex-col justify-between py-6 border-t border-gray-200 gap-4 sm:flex-row sm:flex-wrap sm:items-center">
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
                   class="inline-flex w-full items-center justify-center px-6 py-2 bg-brand-600 text-white rounded-lg font-medium hover:bg-brand-700 transition-colors shadow-md sm:w-auto">
                    <i class="fas fa-envelope mr-2"></i>
                    Contact
                </a>
            </div>

            <!-- Recommended Articles -->
            <?php if (!empty($recommendedPosts)): ?>
                <section class="mt-10 border-t border-gray-200 pt-10" aria-labelledby="recommended-articles">
                    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-brand-600">Recommended</p>
                            <h2 id="recommended-articles" class="text-2xl font-bold text-gray-900">You might also like</h2>
                        </div>
                        <a href="<?= url('/blog') ?>" class="inline-flex items-center text-sm font-semibold text-brand-600 hover:text-brand-700">
                            View all articles
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </a>
                    </div>
                    <div class="grid gap-5 md:grid-cols-3">
                        <?php foreach ($recommendedPosts as $post): ?>
                            <article class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <a href="<?= h($post['url']) ?>" class="block aspect-[4/3] bg-gray-100">
                                    <img src="<?= h(get_image_url($post['cover'] ?? null, 600, 400, (string)($post['title'] ?? 'Article'))) ?>"
                                         alt="<?= h($post['title']) ?>"
                                         class="h-full w-full object-cover"
                                         loading="lazy"
                                         decoding="async">
                                </a>
                                <div class="p-5">
                                    <div class="mb-3 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center">
                                            <i class="far fa-calendar-alt mr-1.5"></i>
                                            <?= format_date($post['created_at'], 'Y-m-d') ?>
                                        </span>
                                        <?php if (!empty($post['category_name'])): ?>
                                            <a href="<?= url('/blog') ?>?category=<?= (int)$post['category_id'] ?>" class="rounded-full bg-brand-100 px-2.5 py-1 font-medium text-brand-700">
                                                <?= h($post['category_name']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="mb-2 line-clamp-2 text-base font-bold leading-snug text-gray-900">
                                        <a href="<?= h($post['url']) ?>" class="hover:text-brand-700"><?= h($post['title']) ?></a>
                                    </h3>
                                    <?php if (!empty($post['summary'])): ?>
                                        <p class="line-clamp-2 text-sm leading-6 text-gray-600"><?= h($post['summary']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>
</section>
