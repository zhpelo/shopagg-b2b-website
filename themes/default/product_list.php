<!-- 产品列表顶部 Hero -->
<section class="hero is-light is-small">
    <div class="hero-body">
        <div class="container">
            <h1 class="title is-3"><?= h($title) ?></h1>
            <p class="subtitle is-6 mt-2"><?= h(t('product_list_subtitle')) ?></p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns is-multiline">
            <?php foreach ($items as $item): ?>
                <div class="column is-3">
                    <div class="card soft-card h-100" style="display: flex; flex-direction: column; height: 100%;">
                        <div class="card-image">
                            <a href="<?= h($item['url']) ?>">
                                <figure class="image is-1by1">
                                    <img src="<?= h($item['cover'] ?: '/assets/no-image.png') ?>" alt="<?= h($item['title']) ?>" style="object-fit: cover;">
                                </figure>
                            </a>
                        </div>
                        <div class="card-content" style="flex-grow: 1;">
                            <div class="mb-2">
                                <span class="tag is-info is-light"><?= h($item['category_name'] ?? t('product_uncategorized')) ?></span>
                            </div>
                            <p class="title is-5 mb-2">
                                <a href="<?= h($item['url']) ?>" class="has-text-dark"><?= h($item['title']) ?></a>
                            </p>
                            <p class="content is-size-7 has-text-grey line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 3em;">
                                <?= h($item['summary']) ?>
                            </p>
                        </div>
                        <footer class="card-footer" style="border-top: none; padding: 0 1.5rem 1.5rem;">
                            <a href="<?= h($item['url']) ?>" class="button is-link is-outlined is-fullwidth is-small"><?= h(t('product_view_details')) ?></a>
                        </footer>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
