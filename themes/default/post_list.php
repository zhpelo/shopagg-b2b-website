<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-10">
                <header class="mb-6">
                    <h1 class="title is-2"><?= h($title) ?></h1>
                    <p class="subtitle is-5 has-text-grey">获取最新的行业洞察与公司动态</p>
                </header>

                <div class="post-items">
                    <?php foreach ($items as $item): ?>
                        <div class="box soft-card mb-5 p-5">
                            <div class="columns">
                                <?php if (!empty($item['cover'])): ?>
                                    <div class="column is-4">
                                        <a href="<?= h($item['url']) ?>">
                                            <figure class="image is-3by2">
                                                <img src="<?= h($item['cover']) ?>" alt="<?= h($item['title']) ?>" style="border-radius: 6px; object-fit: cover;">
                                            </figure>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div class="column">
                                    <p class="is-size-7 has-text-grey mb-2"><?= date('Y-m-d', strtotime($item['created_at'])) ?></p>
                                    <h2 class="title is-4 mb-3">
                                        <a href="<?= h($item['url']) ?>" class="has-text-dark"><?= h($item['title']) ?></a>
                                    </h2>
                                    <p class="content is-size-6 has-text-grey mb-4">
                                        <?= h($item['summary']) ?>
                                    </p>
                                    <a href="<?= h($item['url']) ?>" class="has-text-link has-text-weight-bold">阅读全文 &rarr;</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

