<section class="section">
    <div class="container">
        <!-- 面包屑 -->
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/"><?= h(t('nav_home')) ?></a></li>
                <li><a href="/blog"><?= h(t('blog')) ?></a></li>
                <li class="is-active"><a href="#" aria-current="page"><?= h($item['title']) ?></a></li>
            </ul>
        </nav>

        <div class="columns is-centered">
            <div class="column is-8">
                <!-- 文章头部 -->
                <header class="mb-6">
                    <h1 class="title is-2 mb-4"><?= h($item['title']) ?></h1>
                    <div class="level is-mobile">
                        <div class="level-left">
                            <div class="level-item">
                                <span class="icon-text has-text-grey">
                                    <span class="icon"><i class="far fa-calendar"></i></span>
                                    <span><?= h($item['created_at']) ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </header>

                <?php if (!empty($item['cover'])): ?>
                    <figure class="image mb-6">
                        <img src="<?= h($item['cover']) ?>" alt="<?= h($item['title']) ?>" style="border-radius: 8px;">
                    </figure>
                <?php endif; ?>

                <!-- 文章内容 -->
                <div class="box soft-card p-6">
                    <article class="content is-medium">
                        <?= $item['content'] ?>
                    </article>
                </div>

                <!-- 底部操作 -->
                <div class="mt-6 pt-5" style="border-top: 1px solid #eee;">
                    <div class="level">
                        <div class="level-left">
                            <a href="/blog" class="button is-light">
                                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                                <span><?= h(t('post_back_list')) ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
