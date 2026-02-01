<?php
$categories = $categories ?? [];
$currentCategory = $current_category ?? null;

// 递归渲染分类树
function renderProductCategoryList($items, $currentCategoryId, $level = 0) {
    if (empty($items)) return;
    foreach ($items as $cat):
        $isActive = $currentCategoryId === (int)$cat['id'];
        $hasChildren = !empty($cat['children']);
        $paddingLeft = 1 + ($level * 1.2);
?>
    <a href="<?= url('/products') ?>?category=<?= (int)$cat['id'] ?>" 
       class="panel-block <?= $isActive ? 'is-active' : '' ?>" 
       style="padding-left: <?= $paddingLeft ?>rem;">
        <?php if ($level > 0): ?>
        <span class="has-text-grey-light mr-2">└</span>
        <?php endif; ?>
        <span class="panel-icon">
            <i class="fas fa-<?= $hasChildren ? 'folder' : 'box' ?>" aria-hidden="true"></i>
        </span>
        <?= h($cat['name']) ?>
    </a>
    <?php
        if ($hasChildren) {
            renderProductCategoryList($cat['children'], $currentCategoryId, $level + 1);
        }
    endforeach;
}
?>

<!-- 产品列表顶部 Hero -->
<section class="hero is-danger">
    <div class="hero-body">
        <div class="container">
            <!-- 面包屑导航 -->
            <nav class="breadcrumb mb-4" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= url('/') ?>"><?= h(t('nav_home')) ?></a></li>
                    <li class="<?= !$currentCategory ? 'is-active' : '' ?>">
                        <a href="<?= url('/products') ?>" <?= !$currentCategory ? 'aria-current="page"' : '' ?>><?= h(t('products')) ?></a>
                    </li>
                    <?php if ($currentCategory): ?>
                    <li class="is-active"><a href="#" aria-current="page"><?= h($currentCategory['name']) ?></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <h1 class="title is-3"><?= h($title) ?></h1>
            <p class="subtitle is-6 mt-2">
                <?php if ($currentCategory && !empty($currentCategory['description'])): ?>
                    <?= h($currentCategory['description']) ?>
                <?php else: ?>
                    <?= h(t('product_list_subtitle')) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns">
            <!-- 左侧：分类侧边栏 -->
            <div class="column is-3-desktop is-12-tablet">
                <?php if (!empty($categories)): ?>
                <nav class="panel is-warning">
                    <p class="panel-heading" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border: none;">
                        <span class="icon mr-2"><i class="fas fa-folder-open"></i></span>
                        <?= h(t('product_categories') ?? '产品分类') ?>
                    </p>
                    <a href="<?= url('/products') ?>" class="panel-block <?= !$currentCategory ? 'is-active' : '' ?>">
                        <span class="panel-icon">
                            <i class="fas fa-th-large" aria-hidden="true"></i>
                        </span>
                        <?= h(t('product_all') ?? '全部产品') ?>
                    </a>
                    <?php renderProductCategoryList($categories, $currentCategory ? (int)$currentCategory['id'] : 0); ?>
                </nav>
                <?php endif; ?>

                <!-- 快速询价卡片 -->
                <div class="box mt-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h3 class="title is-5 has-text-white mb-3">
                        <span class="icon mr-2"><i class="fas fa-file-invoice"></i></span>
                        <?= h(t('product_quick_quote') ?? '快速询价') ?>
                    </h3>
                    <p class="mb-4 is-size-6" style="opacity: 0.9;">
                        <?= h(t('product_quote_desc') ?? '找到心仪的产品了吗？立即发送询单获取报价。') ?>
                    </p>
                    <a href="<?= url('/contact') ?>" class="button is-white is-outlined is-fullwidth">
                        <span class="icon"><i class="fas fa-envelope"></i></span>
                        <span><?= h(t('nav_contact')) ?></span>
                    </a>
                </div>
            </div>

            <!-- 右侧：产品列表 -->
            <div class="column is-9-desktop is-12-tablet">
                <?php if (empty($items)): ?>
                <div class="box has-text-centered py-6">
                    <span class="icon is-large has-text-grey-light">
                        <i class="fas fa-box-open fa-3x"></i>
                    </span>
                    <p class="mt-4 has-text-grey"><?= h(t('product_no_items') ?? '暂无产品') ?></p>
                    <?php if ($currentCategory): ?>
                    <a href="<?= url('/products') ?>" class="button is-warning is-light mt-4">
                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                        <span><?= h(t('product_view_all') ?? '查看所有产品') ?></span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="columns is-multiline">
                    <?php foreach ($items as $item): ?>
                        <div class="column is-4-desktop is-6-tablet">
                            <div class="card soft-card h-100" style="display: flex; flex-direction: column; height: 100%;">
                                <div class="card-image">
                                    <a href="<?= h($item['url']) ?>">
                                        <figure class="image is-1by1">
                                            <img src="<?= asset_url(h($item['cover'] ?: '/assets/no-image.png')) ?>" alt="<?= h($item['title']) ?>" style="object-fit: cover;">
                                        </figure>
                                    </a>
                                </div>
                                <div class="card-content" style="flex-grow: 1;">
                                    <div class="mb-2">
                                        <?php if (!empty($item['category_name'])): ?>
                                        <a href="<?= url('/products') ?>?category=<?= (int)$item['category_id'] ?>" class="tag is-warning is-light">
                                            <?= h($item['category_name']) ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="tag is-light"><?= h(t('product_uncategorized')) ?></span>
                                        <?php endif; ?>
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
