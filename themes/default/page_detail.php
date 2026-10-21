<?php
/**
 * 页面模板：独立页面详情
 * 作用：展示后台“页面管理”创建的独立页面内容。
 * 变量：$item（页面数据）。
 */
?>
<section class="py-8 lg:py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <nav class="mb-6 overflow-x-auto pb-1 text-sm" aria-label="<?= h(block('ui_page_detail', 'label_d43fa44c', 'breadcrumb')) ?>">
            <ol class="flex min-w-max items-center gap-2 text-gray-500">
                <li><a href="<?= h(url('/')) ?>" class="hover:text-brand-600"><?= h(block('ui_page_detail', 'label_52f50eae', 'Home')) ?></a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= h(url('/page/' . (string)$item['slug'])) ?>" class="hover:text-brand-600"><?= h(block('ui_page_detail', 'label_e83d9196', 'Page')) ?></a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="font-medium text-gray-900"><?= h($item['title']) ?></li>
            </ol>
        </nav>

        <div class="mx-auto max-w-5xl">
            <header class="mb-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 lg:p-10">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="mb-3 inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-brand-700">
                            <i class="fas fa-file-lines mr-2"></i>
                            <?= h(block('ui_page_detail', 'label_e83d9196', 'Page')) ?>
                        </p>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 lg:text-5xl"><?= h($item['title']) ?></h1>
                        <?php if (!empty($item['summary'])): ?>
                            <p class="mt-4 max-w-3xl text-base leading-7 text-gray-600 lg:text-lg">
                                <?= h($item['summary']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2 text-sm text-gray-500">
                        <p class="flex items-center lg:justify-end">
                            <i class="far fa-calendar mr-2"></i>
                            <?= h(block('ui_page_detail', 'label_6129f1d9', '发布于')) ?> <?= format_date($item['created_at'], 'Y-m-d') ?>
                        </p>
                        <?php if (!empty($item['updated_at']) && $item['updated_at'] !== $item['created_at']): ?>
                            <p class="flex items-center lg:justify-end">
                                <i class="fas fa-rotate mr-2"></i>
                                <?= h(block('ui_page_detail', 'label_cd482da5', '更新于')) ?> <?= format_date($item['updated_at'], 'Y-m-d') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <figure class="mb-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <img src="<?= h(get_image_url($item['cover'] ?? null, 1200, 630, (string)($item['title'] ?? 'Page'))) ?>" alt="<?= h($item['title']) ?>"
                     class="max-h-[520px] w-full object-cover"
                     loading="eager"
                     decoding="async"
                     fetchpriority="high">
            </figure>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 lg:p-10">
                <article class="rich-content">
                    <?= process_rich_text($item['content']) ?>
                </article>
            </div>

            <div class="mt-8 flex flex-col justify-between gap-4 rounded-2xl bg-slate-900 px-6 py-5 text-white shadow-lg lg:flex-row lg:items-center">
                <div>
                    <p class="text-sm uppercase tracking-[0.18em] text-white/60"><?= h(block('ui_page_detail', 'label_a1d0c7a1', 'Need help?')) ?></p>
                    <p class="mt-1 text-lg font-semibold"><?= h(block('ui_page_detail', 'label_ccc7c4c', '如果你对本页面内容有疑问，欢迎直接联系我们。')) ?></p>
                </div>
                <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto lg:flex-wrap">
                    <a href="<?= h(url('/')) ?>" class="inline-flex items-center justify-center rounded-lg border border-white/20 px-4 py-2.5 font-medium text-white transition hover:bg-white/10">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <?= h(block('ui_page_detail', 'label_6c781549', 'Back Home')) ?>
                    </a>
                    <a href="<?= h(url('/contact')) ?>" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-5 py-2.5 font-medium text-white transition hover:bg-brand-700">
                        <i class="fas fa-envelope mr-2"></i>
                        <?= h(block('ui_page_detail', 'label_91dd0cb5', 'Contact Us')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
