<?php
/**
 * 页面模板：首页
 * 作用：展示轮播产品、优势卖点、精选产品、公司亮点、成功案例与CTA。
 * 变量：$products（产品列表）、$cases（案例列表）、$site（站点设置）。
 * 依赖：get_carousel_products() 获取轮播数据。
 */
$products = $products ?? [];
$selectedProductIds = trim((string)block('home_featured', 'product_ids'));
$selectedProducts = $selectedProductIds !== '' ? get_products_by_ids($selectedProductIds, true) : [];
foreach ($selectedProducts as &$selectedProduct) {
    $selectedProduct['url'] = url('/product/' . ($selectedProduct['slug'] ?? $selectedProduct['id']));
}
unset($selectedProduct);
$featuredProducts = !empty($selectedProducts) ? $selectedProducts : $products;
$heroSliderSlug = trim((string)block('home_hero', 'slider_slug'));
$carouselProducts = get_carousel_products(3, $heroSliderSlug !== '' ? $heroSliderSlug : 'home-hero');
$heroAutoplay = block('home_hero', 'autoplay', 'yes') === 'yes';
$heroShowOverlay = block('home_hero', 'show_overlay', 'yes') !== 'no';
$legacyValueProps = block_all('home_value_props');
$valueProps = [
    [
        'icon' => trim((string)($legacyValueProps['item1_icon'] ?? '')) !== '' ? (string)$legacyValueProps['item1_icon'] : block('home_value_props', 'prop_one_icon'),
        'heading' => trim((string)($legacyValueProps['item1_title'] ?? '')) !== '' ? (string)$legacyValueProps['item1_title'] : block('home_value_props', 'prop_one_heading'),
        'desc' => trim((string)($legacyValueProps['item1_desc'] ?? '')) !== '' ? (string)$legacyValueProps['item1_desc'] : block('home_value_props', 'prop_one_desc'),
    ],
    [
        'icon' => trim((string)($legacyValueProps['item2_icon'] ?? '')) !== '' ? (string)$legacyValueProps['item2_icon'] : block('home_value_props', 'prop_two_icon'),
        'heading' => trim((string)($legacyValueProps['item2_title'] ?? '')) !== '' ? (string)$legacyValueProps['item2_title'] : block('home_value_props', 'prop_two_heading'),
        'desc' => trim((string)($legacyValueProps['item2_desc'] ?? '')) !== '' ? (string)$legacyValueProps['item2_desc'] : block('home_value_props', 'prop_two_desc'),
    ],
    [
        'icon' => trim((string)($legacyValueProps['item3_icon'] ?? '')) !== '' ? (string)$legacyValueProps['item3_icon'] : block('home_value_props', 'prop_three_icon'),
        'heading' => trim((string)($legacyValueProps['item3_title'] ?? '')) !== '' ? (string)$legacyValueProps['item3_title'] : block('home_value_props', 'prop_three_heading'),
        'desc' => trim((string)($legacyValueProps['item3_desc'] ?? '')) !== '' ? (string)$legacyValueProps['item3_desc'] : block('home_value_props', 'prop_three_desc'),
    ],
];
?>

<?php if($carouselProducts): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.css" integrity="sha384-gAPqlBuTCdtVcYt9ocMOYWrnBZ4XSL6q+4eXqwNycOr4iFczhNKtnYhF3NEXJM51" crossorigin="anonymous" referrerpolicy="no-referrer">
<!-- Hero 轮播 -->
<section class="hero-banner relative overflow-hidden bg-slate-900">
    <div class="swiper hero-swiper w-full h-full">
        <div class="swiper-wrapper">
            <?php foreach ($carouselProducts as $p): ?>
                <div class="swiper-slide relative">
                    <!-- Background Image -->
                    <div class="absolute inset-0">
                        <img
                            src="<?= h(get_image_url($p['image'] ?? null, 1980, 900)) ?>"
                            alt="<?= h($p['title']) ?>"
                            class="w-full h-full object-cover"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                    </div>
                    <!-- Overlay -->
                    <?php if ($heroShowOverlay): ?>
                        <div class="absolute inset-0 bg-gradient-to-b from-gray-950/80 via-gray-900/55 to-gray-950/45 sm:bg-gradient-to-r sm:from-gray-950/82 sm:via-gray-900/55 sm:to-gray-900/25"></div>
                    <?php endif; ?>
                    <!-- Content -->
                    <div class="relative z-10 mx-auto flex h-full max-w-7xl items-center px-4 py-10 sm:px-6 md:py-16 lg:px-8 lg:py-20">
                        <div class="max-w-2xl pt-4 sm:pt-0">
                            <p class="mb-3 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-white/80 backdrop-blur">
                                <?= h(block('home_hero', 'fallback_label', 'Featured Products')) ?>
                            </p>
                            <h1 class="mb-4 text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl">
                                <?= h($p['title']) ?>
                            </h1>
                            <?php if (!empty($p['subtitle'])): ?>
                                <p class="mb-7 max-w-xl text-base leading-7 text-gray-100 sm:text-lg md:text-xl">
                                    <?= h($p['subtitle']) ?>
                                </p>
                            <?php elseif (!empty($p['summary'])): ?>
                                <p class="mb-7 max-w-xl text-base leading-7 text-gray-100 sm:text-lg md:text-xl">
                                    <?= h(mb_substr(strip_tags($p['summary']), 0, 120)) ?>
                                </p>
                            <?php endif; ?>
                            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:gap-4">
                                <a href="<?= h($p['url']) ?>" class="w-full px-6 py-3 text-center bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors shadow-lg sm:w-auto sm:px-8">
                                    <?= h($p['link_text'] ?? 'View Details') ?>
                                </a>
                                <a href="<?= url('/contact') ?>" class="w-full px-6 py-3 text-center border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-gray-900 transition-colors sm:w-auto sm:px-8">
                                    Contact
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($carouselProducts) > 1): ?>
            <div class="swiper-button-prev !hidden !rounded-full !bg-white/20 !text-white transition-colors hover:!bg-white/35 after:!text-sm after:!font-bold sm:!left-6 sm:!flex sm:!h-12 sm:!w-12"></div>
            <div class="swiper-button-next !hidden !rounded-full !bg-white/20 !text-white transition-colors hover:!bg-white/35 after:!text-sm after:!font-bold sm:!right-6 sm:!flex sm:!h-12 sm:!w-12"></div>
            <div class="swiper-pagination"></div>
        <?php endif; ?>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.js" integrity="sha384-2UI1PfnXFjVMQ7/ZDEF70CR943oH3v6uZrFQGGqJYlvhh4g6z6uVktxYbOlAczav" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.querySelector('.hero-swiper');
    if (!el || el.querySelectorAll('.swiper-slide').length === 0) return;
    new Swiper('.hero-swiper', {
        effect: 'fade',
        fadeEffect: { crossFade: true },
        loop: el.querySelectorAll('.swiper-slide').length > 1,
        speed: 600,
        autoplay: <?= $heroAutoplay ? '{ delay: 5000, disableOnInteraction: false }' : 'false' ?>,
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev'
        },
        a11y: { prevSlideMessage: 'Prev', nextSlideMessage: 'Next' }
    });
});
</script>
<?php endif; ?>

<!-- Value Proposition -->
<section class="relative z-10 px-4 pt-6 sm:-mt-12 sm:pt-0 md:-mt-16 lg:-mt-24">
    <div class="container mx-auto max-w-6xl">
        <div class="rounded-2xl border border-gray-100 bg-white p-3 shadow-xl sm:p-4 lg:p-6">
            <div class="grid grid-cols-1 divide-y divide-gray-100 md:grid-cols-3 md:divide-x md:divide-y-0">
                <?php foreach ($valueProps as $valueProp): ?>
                    <div class="p-5 text-center lg:p-6">
                        <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-brand-50 text-brand-600">
                            <i class="<?= h($valueProp['icon']) ?> text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?= h($valueProp['heading']) ?></h3>
                        <p class="text-gray-500 text-sm"><?= h($valueProp['desc']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-12 lg:py-20">
    <div class="container mx-auto px-4 lg:px-8">
        <!-- Section Header -->
        <div class="mb-8 flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-end sm:justify-between lg:mb-10">
            <div>
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2"><?= h(block('home_featured', 'heading')) ?></h2>
                <p class="text-gray-500"><?= h(block('home_featured', 'subheading')) ?></p>
                <?php if (trim((string)block('home_featured', 'text')) !== ''): ?>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500"><?= nl2br(h(block('home_featured', 'text'))) ?></p>
                <?php endif; ?>
            </div>
            <a href="<?= url('/products') ?>" class="inline-flex w-full items-center justify-center rounded-lg bg-brand-50 px-6 py-2.5 font-medium text-brand-700 transition-colors hover:bg-brand-100 sm:w-auto">
                <?= h(block('home_featured', 'link_text')) ?>
            </a>
        </div>

        <!-- Products Grid -->
        <?php if (empty($featuredProducts)): ?>
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-12 text-center text-sm text-gray-500">
                <?= h(block('home_featured', 'empty_text')) ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($featuredProducts as $p): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">
                    <a href="<?= h($p['url']) ?>" class="block aspect-square overflow-hidden">
                        <img src="<?= h(get_image_url($p['cover'] ?? null, 400, 400, (string)($p['title'] ?? 'Product'))) ?>" 
                             alt="<?= h($p['title']) ?>" 
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                             loading="lazy"
                             decoding="async">
                    </a>
                    <div class="p-5 flex-grow flex flex-col">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                            <a href="<?= h($p['url']) ?>" class="hover:text-brand-600 transition-colors"><?= h($p['title']) ?></a>
                        </h3>
                        <p class="text-gray-500 text-sm line-clamp-3 flex-grow"><?= h($p['summary']) ?></p>
                        <a href="<?= h($p['url']) ?>" class="mt-4 inline-flex items-center justify-center rounded-lg border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-600 transition-colors hover:bg-brand-600 hover:text-white">
                            <?= h(block('home_featured', 'detail_text')) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-12 lg:py-20 bg-gradient-to-br from-slate-50 to-gray-100">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:items-center lg:gap-12">
            <div>
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-6"><?= h(block('home_why_us', 'heading')) ?></h2>
                <div class="rich-content mb-6">
                    <p class="mb-4"><?= h($site['company_bio'] ?? '') ?></p>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center text-gray-700">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check text-sm"></i>
                        </span>
                        <?= h(block('home_why_us', 'badge1')) ?>
                    </li>
                    <li class="flex items-center text-gray-700">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check text-sm"></i>
                        </span>
                        <?= h(block('home_why_us', 'badge2')) ?>
                    </li>
                    <li class="flex items-center text-gray-700">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check text-sm"></i>
                        </span>
                        <?= h(block('home_why_us', 'badge3')) ?>
                    </li>
                </ul>
                <a href="<?= url('/about') ?>" class="inline-flex w-full items-center justify-center rounded-lg border-2 border-brand-600 px-6 py-2.5 font-medium text-brand-600 transition-colors hover:bg-brand-600 hover:text-white sm:w-auto">
                    <?= h(block('home_why_us', 'link_text')) ?>
                </a>
            </div>
            <div class="relative">
                <div class="rounded-2xl overflow-hidden shadow-2xl">
                    <img src="<?= h(get_image_url(block('home_why_us', 'image') ?: ($site['og_image'] ?? null), 800, 400, 'Factory')) ?>" 
                         alt="Factory" 
                         class="aspect-[4/3] w-full object-cover sm:aspect-[16/10]"
                         loading="lazy"
                         decoding="async">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Success Cases -->
<section class="py-12 lg:py-20">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3"><?= h(block('home_cases', 'heading')) ?></h2>
            <p class="text-gray-500"><?= h(block('home_cases', 'subheading')) ?></p>
        </div>
        
        <?php if (empty($cases)): ?>
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-12 text-center text-sm text-gray-500">
                No cases available.
            </div>
        <?php else: ?>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($cases as $c): ?>
                <a href="<?= h($c['url']) ?>" class="group">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="aspect-[3/2] overflow-hidden">
                            <img src="<?= h(get_image_url($c['cover'] ?? null, 600, 400, (string)($c['title'] ?? 'Case'))) ?>" 
                                 alt="<?= h($c['title']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy"
                                 decoding="async">
                        </div>
                        <div class="p-4">
                            <h4 class="font-semibold text-gray-900 line-clamp-1"><?= h($c['title']) ?></h4>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Bottom CTA -->
<section class="py-12 pb-20 lg:py-16">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="relative rounded-2xl overflow-hidden bg-gradient-to-r from-slate-900 to-slate-800 p-6 text-center lg:p-16">
            <!-- Decorative gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent pointer-events-none"></div>
            
            <div class="relative z-10">
                <h2 class="text-2xl lg:text-4xl font-bold text-white mb-4"><?= h(block('home_cta', 'heading')) ?></h2>
                <p class="text-gray-300 text-lg mb-8 max-w-2xl mx-auto"><?= h(block('home_cta', 'text')) ?></p>
                <div class="flex flex-col justify-center gap-3 sm:flex-row sm:flex-wrap sm:gap-4">
                    <a href="<?= url('/contact') ?>" class="w-full px-6 py-3 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-colors shadow-lg sm:w-auto sm:px-8">
                        <?= h(block('home_cta', 'btn1_text')) ?>
                    </a>
                    <?php
                    $wa = $site['whatsapp'] ?? '';
                    $waDigits = preg_replace('/\D+/', '', $wa);
                    if (!empty($waDigits)):
                    ?>
                        <a href="https://wa.me/<?= h($waDigits) ?>" target="_blank" rel="noopener noreferrer"
                           class="inline-flex w-full items-center justify-center gap-2 px-6 py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition-colors shadow-lg sm:w-auto sm:px-8">
                            <i class="fab fa-whatsapp text-xl"></i>
                            <?= h(block('home_cta', 'btn2_text')) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
