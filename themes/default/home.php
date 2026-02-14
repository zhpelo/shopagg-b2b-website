<?php
/**
 * 页面模板：首页
 * 作用：展示轮播产品、优势卖点、精选产品、公司亮点、成功案例与CTA。
 * 变量：$products（产品列表）、$cases（案例列表）、$site（站点设置）。
 * 依赖：get_carousel_products() 获取轮播数据。
 */
$products = $products ?? [];
// 使用优化的轮播产品获取函数
$carouselProducts = get_carousel_products(3);

?>
<?php if($carouselProducts): ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<!-- 1. Hero 轮播（Swiper）：最新 3 个产品（产品展示 + 产品主题 + 产品卖点） -->
<section class="hero hero-swiper is-large is-relative home-hero">
    <div class="swiper hero-swiper-container">
        <div class="swiper-wrapper">
            <?php foreach ($carouselProducts as $p): ?>
                <div class="swiper-slide hero-swiper-slide" style="background-image: url('<?= h($p['banner_image']) ?>');">
                    <div class="hero-swiper-overlay"></div>
                    <div class="hero-body">
                        <div class="container">
                            <div class="columns is-vcentered">
                                <div class="column is-7">
                                    <h1 class="title is-1 has-text-white mb-5 hero-swiper-title line-clamp-2">
                                        <?= h($p['title']) ?>
                                    </h1>
                                    <p class="subtitle is-4 has-text-grey-light mb-6 hero-swiper-desc line-clamp-3">
                                        <?= h( mb_substr(strip_tags($p['summary']), 0, 120) ) ?>
                                    </p>
                                    <div class="buttons hero-cta">
                                        <a class="button is-link is-large px-6" href="<?= $p['url'] ?>"><?= t('view_details')?></a>
                                        <a class="button is-white is-outlined is-large px-6" href="<?= url('/contact') ?>"><?= t('nav_contact') ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($carouselProducts) > 1): ?>
        <div class="swiper-button-prev" aria-label="<?= t('carousel_prev') ?>"></div>
        <div class="swiper-button-next" aria-label="<?= t('carousel_next') ?>"></div>
        <div class="swiper-pagination"></div>
        <?php endif; ?>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.querySelector('.hero-swiper-container');
    if (!el || el.querySelectorAll('.swiper-slide').length === 0) return;
    new Swiper('.hero-swiper-container', {
        effect: 'fade',
        fadeEffect: { crossFade: true },
        loop: el.querySelectorAll('.swiper-slide').length > 1,
        speed: 600,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev'
        },
        a11y: { prevSlideMessage: '<?= t('carousel_prev') ?>', nextSlideMessage: '<?= t('carousel_next') ?>' }
    });
});
</script>
<?php endif; ?>
<!-- 2. Value Proposition (Trust Section) -->
<section class="section py-6 home-trust">
    <div class="container">
        <div class="box soft-card py-6 border-none home-trust-box">
            <div class="columns has-text-centered home-trust-columns">
                <div class="column home-trust-column">
                    <div class="px-4">
                        <span class="icon is-large has-text-link mb-4">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </span>
                        <h3 class="title is-5"><?= h(t('home_quality_title')) ?></h3>
                        <p class="has-text-grey"><?= h(t('home_quality_desc')) ?></p>
                    </div>
                </div>
                <div class="column home-trust-column">
                    <div class="px-4">
                        <span class="icon is-large has-text-link mb-4">
                            <i class="fas fa-globe-americas fa-2x"></i>
                        </span>
                        <h3 class="title is-5"><?= h(t('home_logistics_title')) ?></h3>
                        <p class="has-text-grey"><?= h(t('home_logistics_desc')) ?></p>
                    </div>
                </div>
                <div class="column home-trust-column">
                    <div class="px-4">
                        <span class="icon is-large has-text-link mb-4">
                            <i class="fas fa-user-shield fa-2x"></i>
                        </span>
                        <h3 class="title is-5"><?= h(t('home_support_title')) ?></h3>
                        <p class="has-text-grey"><?= h(t('home_support_desc')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Featured Products Grid -->
<section class="section home-products">
    <div class="container">
        <div class="level mb-6 home-section-head">
            <div class="level-left">
                <div>
                    <h2 class="title is-3 mb-2 home-section-title"><?= h(t('section_featured_products')) ?></h2>
                    <p class="has-text-grey home-section-subtitle"><?= h(t('home_highlights')) ?></p>
                </div>
            </div>
            <div class="level-right">
                <a class="button is-link is-light" href="<?= url('/products') ?>"><?= h(t('btn_view_all')) ?> &rarr;</a>
            </div>
        </div>

        <div class="columns is-multiline">
            <?php foreach ($products as $p): ?>
                <div class="column is-4">
                    <div class="card soft-card h-100 home-product-card">
                        <div class="card-image home-product-image">
                            <a href="<?= h($p['url']) ?>">
                                <figure class="image is-1by1">
                                    <img src="<?= asset_url($p['cover'] ?: '/assets/no-image.png') ?>" alt="<?= h($p['title']) ?>" style="object-fit: cover;">
                                </figure>
                            </a>
                        </div>
                        <div class="card-content home-product-content">
                            <h3 class="title is-5 mb-2 line-clamp-2 home-product-title">
                                <a href="<?= h($p['url']) ?>" class="has-text-dark"><?= h($p['title']) ?></a>
                            </h3>
                            <p class="content has-text-grey line-clamp-3 home-product-summary">
                                <?= h($p['summary']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4. Factory/Company Highlight (Why Us) -->
<section class="section home-why">
    <div class="container">
        <div class="columns is-vcentered">
            <div class="column is-6">
                <h2 class="title is-3 mb-5 home-section-title"><?= h(t('home_why_us')) ?></h2>
                <div class="content has-text-grey is-medium home-why-content">
                    <p><?= h($site['company_bio'] ?? '') ?></p>
                    <ul style="list-style-type: none; margin-left: 0;">
                        <li class="mb-2"><span class="icon has-text-success"><i class="fas fa-check"></i></span> <?= h(t('home_iso')) ?></li>
                        <li class="mb-2"><span class="icon has-text-success"><i class="fas fa-check"></i></span> <?= h(t('home_oem')) ?></li>
                        <li class="mb-2"><span class="icon has-text-success"><i class="fas fa-check"></i></span> <?= h(t('home_rd')) ?></li>
                    </ul>
                </div>
                <a href="<?= url('/about') ?>" class="button is-link is-outlined"><?= h(t('nav_about')) ?></a>
            </div>
            <div class="column is-6">
                <figure class="image is-16by9 box overflow-hidden soft-card home-why-media">
                    <img src="<?= asset_url($site['og_image'] ?? 'https://devtool.tech/api/placeholder/400/300') ?>" alt="Factory" style="object-fit: cover;">
                </figure>
            </div>
        </div>
    </div>
</section>

<!-- 5. Success Cases Banner -->
<section class="section py-6 home-cases">
    <div class="container">
        <div class="has-text-centered mb-6">
            <h2 class="title is-3 home-section-title"><?= t('section_success_cases') ?></h2>
            <p class="subtitle is-6 has-text-grey home-section-subtitle"><?= t('home_global') ?></p>
        </div>
        <div class="columns is-multiline">
            <?php foreach ($cases as $c): ?>
                <div class="column is-4">
                    <a href="<?= h($c['url']) ?>">
                        <div class="card soft-card overflow-hidden home-case-card">
                            <div class="card-image">
                                <figure class="image is-3by2">
                                    <img src="<?= asset_url($c['cover'] ?: 'https://devtool.tech/api/placeholder/400/300') ?>" alt="<?= h($c['title']) ?>" style="object-fit: cover;">
                                </figure>
                            </div>
                            <div class="card-content p-4">
                                <h4 class="title is-6 mb-0 line-clamp-1"><?= h($c['title']) ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 6. Bottom CTA -->
<section class="section pb-6 home-cta">
    <div class="container">
        <div class="box has-text-centered py-6 brand-gradient has-text-white border-none soft-card home-cta-box">
            <h2 class="title is-3 has-text-white my-5"><?= h(t('home_ready_title')) ?></h2>
            <p class="is-5 has-text-grey-light mb-5"><?= h(t('home_ready_desc')) ?></p>
            <div class="buttons is-centered">
                <a href="<?= url('/contact') ?>" class="button is-white is-large px-6"><?= h(t('cta_quote')) ?></a>
                <?php
                $wa = $site['whatsapp'] ?? '';
                $waDigits = preg_replace('/\D+/', '', $wa);
                if (!empty($waDigits)):
                ?>
                    <a href="https://wa.me/<?= h($waDigits) ?>" target="_blank" class="button is-success is-large px-6">
                        <span class="icon"><i class="fab fa-whatsapp"></i></span>
                        <span><?= h(t('chat_now')) ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>