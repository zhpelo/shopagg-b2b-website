<?php
$categories = $categories ?? [];
$currentCategory = $current_category ?? null;

// 递归渲染分类树
function renderCategoryList($items, $currentCategoryId, $level = 0)
{
    if (empty($items)) return;
    foreach ($items as $cat):
        $isActive = $currentCategoryId === (int)$cat['id'];
        $hasChildren = !empty($cat['children']);
        $paddingLeft = 1 + ($level * 1.2);
?>
        <a href="<?= url('/blog') ?>?category=<?= (int)$cat['id'] ?>"
            class="panel-block <?= $isActive ? 'is-active' : '' ?>"
            style="padding-left: <?= $paddingLeft ?>rem;">
            <?php if ($level > 0): ?>
                <span class="has-text-grey-light mr-2">└</span>
            <?php endif; ?>
            <span class="panel-icon">
                <i class="fas fa-<?= $hasChildren ? 'folder' : 'file-alt' ?>" aria-hidden="true"></i>
            </span>
            <?= h($cat['name']) ?>
        </a>
<?php
        if ($hasChildren) {
            renderCategoryList($cat['children'], $currentCategoryId, $level + 1);
        }
    endforeach;
}
?>

<section class="hero is-warning">
    <div class="hero-body">
        <div class="container">
            <!-- 面包屑导航 -->
            <nav class="breadcrumb mb-4" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= url('/') ?>"><?= h(t('nav_home')) ?></a></li>
                    <li class="<?= !$currentCategory ? 'is-active' : '' ?>">
                        <a href="<?= url('/blog') ?>" <?= !$currentCategory ? 'aria-current="page"' : '' ?>><?= h(t('blog')) ?></a>
                    </li>
                    <?php if ($currentCategory): ?>
                        <li class="is-active"><a href="#" aria-current="page"><?= h($currentCategory['name']) ?></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <h1 class="title is-3"><?= h($title) ?></h1>
            <p class="subtitle is-6 mt-2"><?php if ($currentCategory): ?>
                    <?= h($currentCategory['description'] ?? t('post_industry_insights')) ?>
                <?php else: ?>
                    <?= h(t('post_industry_insights')) ?>
                <?php endif; ?></p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">

        <div class="columns">
            <!-- 左侧：文章列表 -->
            <div class="column is-8-desktop is-12-tablet">

                <?php if (empty($items)): ?>
                    <div class="box has-text-centered py-6">
                        <span class="icon is-large has-text-grey-light">
                            <i class="fas fa-file-alt fa-3x"></i>
                        </span>
                        <p class="mt-4 has-text-grey"><?= h(t('post_no_articles') ?? '暂无文章') ?></p>
                        <?php if ($currentCategory): ?>
                            <a href="<?= url('/blog') ?>" class="button is-link is-light mt-4">
                                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                                <span><?= h(t('post_view_all') ?? '查看所有文章') ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="post-items">
                        <?php foreach ($items as $item): ?>
                            <div class="box soft-card mb-5 p-5">
                                <div class="columns">
                                    <?php if (!empty($item['cover'])): ?>
                                        <?php $coverSrc = (strpos($item['cover'], 'http') === 0 || strpos($item['cover'], '//') === 0) ? $item['cover'] : url($item['cover']); ?>
                                        <div class="column is-4">
                                            <a href="<?= h($item['url']) ?>">
                                                <figure class="image is-3by2">
                                                    <img src="<?= h($coverSrc) ?>" alt="<?= h($item['title']) ?>" style="border-radius: 6px; object-fit: cover;">
                                                </figure>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="column">
                                        <div class="is-flex is-align-items-center mb-2" style="gap: 0.75rem;">
                                            <span class="is-size-7 has-text-grey">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                <?= format_date($item['created_at'], 'Y-m-d') ?>
                                            </span>
                                            <?php if (!empty($item['category_name'])): ?>
                                                <a href="<?= url('/blog') ?>?category=<?= (int)$item['category_id'] ?>" class="tag is-link is-light is-small">
                                                    <?= h($item['category_name']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <h2 class="title is-4 mb-3">
                                            <a href="<?= h($item['url']) ?>" class="has-text-dark"><?= h($item['title']) ?></a>
                                        </h2>
                                        <p class="content is-size-6 has-text-grey mb-4">
                                            <?= h($item['summary']) ?>
                                        </p>
                                        <a href="<?= h($item['url']) ?>" class="has-text-link has-text-weight-bold"><?= h(t('post_read_full')) ?> &rarr;</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 右侧：分类侧边栏 -->
            <div class="column is-4-desktop is-12-tablet">
                <?php if (!empty($categories)): ?>
                    <nav class="panel is-link">
                        <p class="panel-heading" style="background: linear-gradient(135deg, #48c774 0%, #00d1b2 100%); border: none;">
                            <span class="icon mr-2"><i class="fas fa-folder-open"></i></span>
                            <?= h(t('post_categories') ?? '文章分类') ?>
                        </p>
                        <a href="<?= url('/blog') ?>" class="panel-block <?= !$currentCategory ? 'is-active' : '' ?>">
                            <span class="panel-icon">
                                <i class="fas fa-list" aria-hidden="true"></i>
                            </span>
                            <?= h(t('post_all_articles') ?? '全部文章') ?>
                        </a>
                        <?php renderCategoryList($categories, $currentCategory ? (int)$currentCategory['id'] : 0); ?>
                    </nav>
                <?php endif; ?>

                <!-- 快速联系卡片 -->
                <div class="box mt-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h3 class="title is-5 has-text-white mb-3">
                        <span class="icon mr-2"><i class="fas fa-headset"></i></span>
                        <?= h(t('post_need_help') ?? '需要帮助？') ?>
                    </h3>
                    <p class="mb-4 is-size-6" style="opacity: 0.9;">
                        <?= h(t('post_contact_desc') ?? '如果您有任何问题，欢迎随时联系我们的专业团队。') ?>
                    </p>
                    <a href="<?= url('/contact') ?>" class="button is-white is-outlined is-fullwidth">
                        <span class="icon"><i class="fas fa-envelope"></i></span>
                        <span><?= h(t('nav_contact')) ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>