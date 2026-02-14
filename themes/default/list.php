<?php
/**
 * 页面模板：通用列表
 * 作用：用于文章/案例等列表页的统一卡片布局展示。
 * 变量：$title（页面标题）、$items（列表数据）、$show_image/$show_category（展示控制）。
 * 注意：$items 需包含 title/url/summary 字段。
 */
?>
<section class="section">
    <div class="container">
        <h1 class="title is-3"><?= h($title) ?></h1>
        <div class="columns is-multiline">
            <?php foreach ($items as $item): ?>
                <div class="column is-4">
                    <div class="card soft-card">
                        <?php if (!empty($show_image) && !empty($item['cover'])): ?>
                            <div class="card-image">
                                <figure class="image is-1by1">
                                    <img src="<?= asset_url(h($item['cover'])) ?>" alt="<?= h($item['title']) ?>">
                                </figure>
                            </div>
                        <?php endif; ?>
                        <div class="card-content">
                            <p class="title is-6"><a href="<?= h($item['url']) ?>"><?= h($item['title']) ?></a></p>
                            <?php if (!empty($show_category)): ?>
                                <span class="tag is-light"><?= h($item['category_name'] ?? '未分类') ?></span>
                            <?php endif; ?>
                            <p class="content "><?= h($item['summary']) ?></p>
                            <a class="button is-small is-link is-light" href="<?= h($item['url']) ?>"><?= h(t('list_read_more')) ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>