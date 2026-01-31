<?php
$category = $category ?? null;
?>
<section class="section">
    <div class="container">
        <!-- 面包屑导航 -->
        <nav class="breadcrumb mb-5" aria-label="breadcrumbs">
            <ul>
                <li><a href="<?= url('/') ?>"><?= h(t('nav_home')) ?></a></li>
                <li><a href="<?= url('/blog') ?>"><?= h(t('blog')) ?></a></li>
                <?php if ($category): ?>
                <li><a href="<?= url('/blog') ?>?category=<?= (int)$category['id'] ?>"><?= h($category['name']) ?></a></li>
                <?php endif; ?>
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
                                    <span><?= format_date($item['created_at'], 'Y-m-d') ?></span>
                                </span>
                            </div>
                            <?php if ($category): ?>
                            <div class="level-item">
                                <a href="<?= url('/blog') ?>?category=<?= (int)$category['id'] ?>" class="tag is-link is-light">
                                    <span class="icon is-small"><i class="fas fa-folder"></i></span>
                                    <span><?= h($category['name']) ?></span>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>

                <?php if (!empty($item['cover'])): ?>
                    <?php $coverSrc = (strpos($item['cover'], 'http') === 0 || strpos($item['cover'], '//') === 0) ? $item['cover'] : url($item['cover']); ?>
                    <figure class="image mb-6">
                        <img src="<?= h($coverSrc) ?>" alt="<?= h($item['title']) ?>" style="border-radius: 8px;">
                    </figure>
                <?php endif; ?>

                <!-- 文章内容 -->
                <div class="box soft-card p-6">
                    <article class="content is-medium">
                        <?= $item['content'] ?>
                    </article>
                </div>

                <!-- 文章标签/分类信息 -->
                <?php if ($category): ?>
                <div class="mt-5 mb-5">
                    <span class="has-text-grey mr-2"><?= h(t('post_category_label') ?? '分类：') ?></span>
                    <a href="<?= url('/blog') ?>?category=<?= (int)$category['id'] ?>" class="tag is-medium is-link is-light">
                        <span class="icon is-small"><i class="fas fa-folder"></i></span>
                        <span><?= h($category['name']) ?></span>
                    </a>
                </div>
                <?php endif; ?>

                <!-- 底部操作 -->
                <div class="mt-6 pt-5" style="border-top: 1px solid #eee;">
                    <div class="level">
                        <div class="level-left">
                            <?php if ($category): ?>
                            <a href="<?= url('/blog') ?>?category=<?= (int)$category['id'] ?>" class="button is-light mr-3">
                                <span class="icon"><i class="fas fa-folder"></i></span>
                                <span><?= h(t('post_more_in_category') ?? '更多同类文章') ?></span>
                            </a>
                            <?php endif; ?>
                            <a href="<?= url('/blog') ?>" class="button is-link is-light">
                                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                                <span><?= h(t('post_back_list')) ?></span>
                            </a>
                        </div>
                        <div class="level-right">
                            <a href="<?= url('/contact') ?>" class="button is-primary">
                                <span class="icon"><i class="fas fa-envelope"></i></span>
                                <span><?= h(t('nav_contact')) ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
