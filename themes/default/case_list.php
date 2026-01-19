<section class="hero is-info is-bold brand-gradient">
    <div class="hero-body">
        <div class="container">
            <h1 class="title"><?= h($title) ?></h1>
            <p class="subtitle mt-2">见证我们的专业实力，为全球客户创造价值。</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns is-multiline">
            <?php foreach ($items as $item): ?>
                <div class="column is-6">
                    <div class="card soft-card">
                        <?php if (!empty($item['cover'])): ?>
                            <div class="card-image">
                                <a href="<?= h($item['url']) ?>">
                                    <figure class="image is-16by9">
                                        <img src="<?= h($item['cover']) ?>" alt="<?= h($item['title']) ?>" style="object-fit: cover;">
                                    </figure>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="card-content">
                            <p class="title is-4">
                                <a href="<?= h($item['url']) ?>" class="has-text-dark"><?= h($item['title']) ?></a>
                            </p>
                            <p class="content is-size-6 has-text-grey">
                                <?= h($item['summary']) ?>
                            </p>
                            <div class="mt-4">
                                <a href="<?= h($item['url']) ?>" class="button is-link is-light">阅读案例详情 &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

