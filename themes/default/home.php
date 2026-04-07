<?php
/**
 * 页面模板：首页
 * 作用：展示轮播产品、优势卖点、精选产品、公司亮点、成功案例与CTA。
 * 变量：$products（产品列表）、$cases（案例列表）、$site（站点设置）。
 * 依赖：get_carousel_products() 获取轮播数据。
 */
$products = $products ?? [];
$carouselProducts = get_carousel_products(3);
?>

<?php if($carouselProducts): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<!-- Hero 轮播 -->
<section class="hero-banner relative overflow-hidden">
    <div class="swiper hero-swiper w-full h-full">
        <div class="swiper-wrapper">
            <?php foreach ($carouselProducts as $p): ?>
                <div class="swiper-slide relative">
                    <!-- Background Image -->
                    <div class="absolute inset-0">
                        <img
                            src="<?= get_image_url($p['banner_image'] ?? null, 1980, 900) ?>"
                            alt="<?= h($p['title']) ?>"
                            class="w-full h-full object-cover"
                        >
                    </div>
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-900/80 via-gray-900/50 to-gray-900/40"></div>
                    <!-- Content -->
                    <div class="relative z-10 container mx-auto px-4 lg:px-8 h-full flex items-center py-12 md:py-16 lg:py-20">
                        <div class="max-w-2xl">
                            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight line-clamp-2">
                                <?= h($p['title']) ?>
                            </h1>
                            <p class="text-lg md:text-xl text-gray-200 mb-8 line-clamp-3">
                                <?= h(mb_substr(strip_tags($p['summary']), 0, 120)) ?>
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <a href="<?= $p['url'] ?>" class="px-8 py-3 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors shadow-lg">
                                    View Details
                                </a>
                                <a href="<?= url('/contact') ?>" class="px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-gray-900 transition-colors">
                                    Contact
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($carouselProducts) > 1): ?>
            <div class="swiper-button-prev !w-12 !h-12 !bg-white/20 !rounded-full !text-white hover:!bg-white/35 transition-colors after:!text-sm after:!font-bold"></div>
            <div class="swiper-button-next !w-12 !h-12 !bg-white/20 !rounded-full !text-white hover:!bg-white/35 transition-colors after:!text-sm after:!font-bold"></div>
            <div class="swiper-pagination"></div>
        <?php endif; ?>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.querySelector('.hero-swiper');
    if (!el || el.querySelectorAll('.swiper-slide').length === 0) return;
    new Swiper('.hero-swiper', {
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
        a11y: { prevSlideMessage: 'Prev', nextSlideMessage: 'Next' }
    });
});
</script>
<?php endif; ?>

<!-- Value Proposition -->
<section class="relative z-10 -mt-12 px-4">
    <div class="container mx-auto max-w-6xl">
        <div class="bg-white rounded-2xl shadow-xl p-6 lg:p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-brand-50 text-brand-600">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Quality Assurance</h3>
                    <p class="text-gray-500 text-sm">ISO-aligned production with strict QC before shipment.</p>
                </div>
                <div class="p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-brand-50 text-brand-600">
                        <i class="fas fa-globe-americas text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Global Logistics</h3>
                    <p class="text-gray-500 text-sm">On-time delivery with consolidated freight options.</p>
                </div>
                <div class="p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-brand-50 text-brand-600">
                        <i class="fas fa-user-shield text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Dedicated Support</h3>
                    <p class="text-gray-500 text-sm">One-to-one account service for long-term buyers.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-16 lg:py-20">
    <div class="container mx-auto px-4 lg:px-8">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 pb-6 border-b border-gray-200">
            <div>
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Featured Products</h2>
                <p class="text-gray-500">Company Highlights</p>
            </div>
            <a href="<?= url('/products') ?>" class="mt-4 sm:mt-0 px-6 py-2.5 bg-brand-50 text-brand-700 font-medium rounded-lg hover:bg-brand-100 transition-colors">
                View All →
            </a>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $p): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">
                    <a href="<?= h($p['url']) ?>" class="block aspect-square overflow-hidden">
                        <img src="<?= get_image_url($p['cover'] ?? null, 400, 400, h($p['title'])) ?>" 
                             alt="<?= h($p['title']) ?>" 
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </a>
                    <div class="p-5 flex-grow flex flex-col">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                            <a href="<?= h($p['url']) ?>" class="hover:text-brand-600 transition-colors"><?= h($p['title']) ?></a>
                        </h3>
                        <p class="text-gray-500 text-sm line-clamp-3 flex-grow"><?= h($p['summary']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 lg:py-20 bg-gradient-to-br from-slate-50 to-gray-100">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-6">Why Choose Us</h2>
                <div class="prose text-gray-600 mb-6">
                    <p class="mb-4"><?= h($site['company_bio'] ?? '') ?></p>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center text-gray-700">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check text-sm"></i>
                        </span>
                        ISO Certified
                    </li>
                    <li class="flex items-center text-gray-700">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check text-sm"></i>
                        </span>
                        OEM & ODM
                    </li>
                    <li class="flex items-center text-gray-700">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check text-sm"></i>
                        </span>
                        R&D Team
                    </li>
                </ul>
                <a href="<?= url('/about') ?>" class="inline-flex items-center px-6 py-2.5 border-2 border-brand-600 text-brand-600 font-medium rounded-lg hover:bg-brand-600 hover:text-white transition-colors">
                    About Us
                </a>
            </div>
            <div class="relative">
                <div class="rounded-2xl overflow-hidden shadow-2xl">
                    <img src="<?= get_image_url($site['og_image'] ?? null, 800, 400, 'Factory') ?>" 
                         alt="Factory" 
                         class="w-full h-auto object-cover">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Success Cases -->
<section class="py-16 lg:py-20">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">Success Cases</h2>
            <p class="text-gray-500">Global Presence</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($cases as $c): ?>
                <a href="<?= h($c['url']) ?>" class="group">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="aspect-[3/2] overflow-hidden">
                            <img src="<?= get_image_url($c['cover'] ?? null, 600, 400, h($c['title'])) ?>" 
                                 alt="<?= h($c['title']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="p-4">
                            <h4 class="font-semibold text-gray-900 line-clamp-1"><?= h($c['title']) ?></h4>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Bottom CTA -->
<section class="py-16 pb-20">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-slate-900 to-slate-800 p-8 lg:p-16 text-center">
            <!-- Decorative gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent pointer-events-none"></div>
            
            <div class="relative z-10">
                <h2 class="text-2xl lg:text-4xl font-bold text-white mb-4">Ready to start your project?</h2>
                <p class="text-gray-300 text-lg mb-8 max-w-2xl mx-auto">Contact us today for a professional quote and expert consultation.</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="<?= url('/contact') ?>" class="px-8 py-3 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-colors shadow-lg">
                        Request Quote
                    </a>
                    <?php
                    $wa = $site['whatsapp'] ?? '';
                    $waDigits = preg_replace('/\D+/', '', $wa);
                    if (!empty($waDigits)):
                    ?>
                        <a href="https://wa.me/<?= h($waDigits) ?>" target="_blank" 
                           class="px-8 py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition-colors shadow-lg inline-flex items-center gap-2">
                            <i class="fab fa-whatsapp text-xl"></i>
                            Chat Now
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
