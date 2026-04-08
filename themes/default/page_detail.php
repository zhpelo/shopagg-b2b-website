<?php
/**
 * 页面模板：独立页面详情
 * 作用：展示后台“页面管理”创建的独立页面内容。
 * 变量：$item（页面数据）。
 */
?>
<section class="py-8 lg:py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <nav class="mb-6 text-sm" aria-label="breadcrumb">
            <ol class="flex flex-wrap items-center gap-2 text-gray-500">
                <li><a href="<?= url('/') ?>" class="hover:text-brand-600">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= url('/page/' . h($item['slug'])) ?>" class="hover:text-brand-600">Page</a></li>
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
                            Page
                        </p>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 lg:text-5xl"><?= h($item['title']) ?></h1>
                        <?php if (!empty($item['summary'])): ?>
                            <p class="mt-4 max-w-3xl text-base leading-7 text-gray-600 lg:text-lg">
                                <?= h($item['summary']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-2 text-sm text-gray-500">
                        <p class="flex items-center justify-end">
                            <i class="far fa-calendar mr-2"></i>
                            发布于 <?= format_date($item['created_at'], 'Y-m-d') ?>
                        </p>
                        <?php if (!empty($item['updated_at']) && $item['updated_at'] !== $item['created_at']): ?>
                            <p class="flex items-center justify-end">
                                <i class="fas fa-rotate mr-2"></i>
                                更新于 <?= format_date($item['updated_at'], 'Y-m-d') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <?php if (!empty($item['cover'])): ?>
                <figure class="mb-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                    <img src="<?= asset_url(h($item['cover'])) ?>"
                         alt="<?= h($item['title']) ?>"
                         class="max-h-[520px] w-full object-cover">
                </figure>
            <?php endif; ?>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 lg:p-10">
                <article class="rich-content">
                    <?= process_rich_text($item['content']) ?>
                </article>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-slate-900 px-6 py-5 text-white shadow-lg">
                <div>
                    <p class="text-sm uppercase tracking-[0.18em] text-white/60">Need help?</p>
                    <p class="mt-1 text-lg font-semibold">如果你对本页面内容有疑问，欢迎直接联系我们。</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="<?= url('/') ?>" class="inline-flex items-center rounded-lg border border-white/20 px-4 py-2.5 font-medium text-white transition hover:bg-white/10">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back Home
                    </a>
                    <a href="<?= url('/contact') ?>" class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 font-medium text-white transition hover:bg-brand-700">
                        <i class="fas fa-envelope mr-2"></i>
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
