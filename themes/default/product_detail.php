<?php
/**
 * 页面模板：产品详情
 * 作用：展示产品图片、参数、询盘入口与相关信息。
 * 变量：$item（产品数据）、$images（图片列表）、$category（分类数据）。
 * 注意：存在产品图片时加载 Swiper 轮播依赖。
 */
$category = $category ?? null;
$images = $images ?? [];
$hasGalleryNavigation = count($images) > 1;
$priceMode = normalize_product_price_mode((string)($price_mode ?? $item['price_mode'] ?? 'tier'));
$productSkus = is_array($product_skus ?? null) ? $product_skus : [];
$priceTiers = is_array($price_tiers ?? null) ? $price_tiers : [];
$sampleCurrency = normalize_currency_code((string)($currency ?? $site['currency'] ?? 'USD'), 'USD');
$activePrices = $priceMode === 'sku' ? $productSkus : $priceTiers;
$priceRangeMin = isset($item['price_range_min']) ? (float)$item['price_range_min'] : 0.0;
$priceRangeMax = isset($item['price_range_max']) ? (float)$item['price_range_max'] : 0.0;
if ($priceRangeMin > 0 && $priceRangeMax > 0 && $priceRangeMax < $priceRangeMin) {
    [$priceRangeMin, $priceRangeMax] = [$priceRangeMax, $priceRangeMin];
}
$hasPriceRange = $priceRangeMin > 0 && $priceRangeMax > 0;
$sampleBasePrice = !empty($activePrices) ? (float)($activePrices[0]['price'] ?? 0) : ($hasPriceRange ? $priceRangeMin : 25.0);
$samplePrice = number_format($sampleBasePrice * 2, 2, '.', '');
$related_products = is_array($related_products ?? null) ? $related_products : [];
$manualRelatedIds = trim((string)block('product_detail', 'related_product_ids'));
if ($manualRelatedIds !== '') {
    $manualRelatedProducts = get_products_by_ids($manualRelatedIds, true);
    $currentProductId = (int)($item['id'] ?? 0);
    $related_products = array_values(array_filter($manualRelatedProducts, static function (array $product) use ($currentProductId): bool {
        return (int)($product['id'] ?? 0) !== $currentProductId;
    }));
}
?>
<?php if (!empty($images)): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.css" integrity="sha384-gAPqlBuTCdtVcYt9ocMOYWrnBZ4XSL6q+4eXqwNycOr4iFczhNKtnYhF3NEXJM51" crossorigin="anonymous" referrerpolicy="no-referrer">
<?php endif; ?>

<section class="product-detail-page bg-slate-50 py-6 sm:py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="text-sm mb-6 overflow-x-auto pb-1" aria-label="breadcrumb">
            <ol class="flex min-w-max items-center space-x-2 text-slate-500">
                <li><a href="<?= url('/') ?>" class="hover:text-brand-600">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs text-slate-300"></i></li>
                <li><a href="<?= url('/products') ?>" class="hover:text-brand-600">Products</a></li>
                <?php if ($category): ?>
                    <li><i class="fas fa-chevron-right text-xs text-slate-300"></i></li>
                    <li><a href="<?= h(product_category_url($category)) ?>" class="hover:text-brand-600"><?= h($category['name']) ?></a></li>
                <?php endif; ?>
                <li><i class="fas fa-chevron-right text-xs text-slate-300"></i></li>
                <li class="text-slate-950 font-medium"><?= h($item['title']) ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start lg:gap-8">
            <!-- Product Images -->
            <div class="lg:col-span-7">
                <?php if (!empty($images)): ?>
                    <div class="product-gallery rounded-lg border border-slate-200 bg-white p-2 shadow-sm sm:p-3">
                        <!-- Main Swiper -->
                        <div class="swiper main-swiper product-main-swiper rounded-md overflow-hidden mb-3 bg-slate-100">
                            <div class="swiper-wrapper">
                                <?php foreach ($images as $index => $img): ?>
                                    <div class="swiper-slide aspect-square">
                                        <img src="<?= h(asset_url((string)$img)) ?>"
                                             alt="<?= h($item['title']) ?>"
                                             class="w-full h-full object-cover cursor-zoom-in"
                                             loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                                             decoding="async"
                                             <?= $index === 0 ? 'fetchpriority="high"' : '' ?>>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($hasGalleryNavigation): ?>
                                <button type="button" class="swiper-button-next product-gallery__nav product-gallery__nav--next" aria-label="Next product image"></button>
                                <button type="button" class="swiper-button-prev product-gallery__nav product-gallery__nav--prev" aria-label="Previous product image"></button>
                            <?php endif; ?>
                        </div>
                        <!-- Thumbs Swiper -->
                        <?php if ($hasGalleryNavigation): ?>
                            <div class="swiper thumbs-swiper product-thumbs-swiper">
                                <div class="swiper-wrapper">
                                    <?php foreach ($images as $img): ?>
                                        <div class="swiper-slide product-gallery__thumb cursor-pointer overflow-hidden">
                                            <img src="<?= h(asset_url((string)$img)) ?>"
                                                 alt="<?= h($item['title']) ?>"
                                                 class="w-full h-full object-cover"
                                                 loading="lazy"
                                                 decoding="async">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Product Info -->
            <aside class="product-detail-info lg:col-span-5 lg:row-span-2">
                <div class="product-detail-info__card rounded-lg border border-slate-200 bg-white p-5 shadow-sm lg:p-6">
                    <h1 class="mb-3 text-2xl font-bold leading-tight text-slate-950 lg:text-3xl"><?= h($item['title']) ?></h1>
                    
                    <div class="flex flex-wrap items-center gap-3 mb-4 text-sm text-slate-500">
                        <span class="flex items-center">
                            <i class="far fa-calendar-alt mr-1"></i>
                            <?= format_date($item['created_at'], 'Y-m-d') ?>
                        </span>
                        <?php if ($category): ?>
                            <a href="<?= h(product_category_url($category)) ?>"
                               class="inline-flex items-center px-3 py-1 bg-brand-50 text-brand-700 rounded-full text-xs font-medium">
                                <i class="fas fa-folder mr-1"></i>
                                <?= h($category['name']) ?>
                            </a>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">
                                <?= h($item['category_name'] ?? 'Uncategorized') ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($item['summary'])): ?>
                        <p class="text-slate-600 mb-6 leading-7"><?= h($item['summary']) ?></p>
                    <?php endif; ?>

                    <?php if ($priceMode === 'sku' && !empty($productSkus)): ?>
                        <div class="mb-6 overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-[440px] w-full text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">SKU</th>
                                        <th class="px-4 py-3 font-semibold">MOQ</th>
                                        <th class="px-4 py-3 text-right font-semibold">Price</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach ($productSkus as $sku): ?>
                                        <tr>
                                            <td class="px-4 py-3 font-semibold text-slate-950"><?= h((string)($sku['sku_name'] ?? '')) ?></td>
                                            <td class="px-4 py-3 text-slate-500"><?= h(number_format((float)($sku['min_qty'] ?? 0))) ?>+ Pieces</td>
                                            <td class="px-4 py-3 text-right font-bold text-slate-950"><?= h($sampleCurrency) ?> <?= h((string)($sku['price'] ?? '')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($priceMode === 'tier' && !empty($priceTiers)): ?>
                        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <?php foreach ($priceTiers as $tier): ?>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-center">
                                    <div class="text-lg font-bold text-slate-950 lg:text-xl">
                                        <?= h($sampleCurrency) ?> <?= h((string)$tier['price']) ?>
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        <?= number_format((float)$tier['min_qty']) ?><?php if (!empty($tier['max_qty'])): ?>-<?= number_format((float)$tier['max_qty']) ?><?php else: ?>+<?php endif; ?> Pieces
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($priceMode === 'range' && $hasPriceRange): ?>
                        <div class="mb-6 rounded-lg border border-brand-100 bg-brand-50 p-5">
                            <div class="text-sm font-semibold uppercase tracking-[0.12em] text-brand-600">
                                <?= h(block('product_detail', 'price_range_label')) ?>
                            </div>
                            <div class="mt-2 text-2xl font-bold text-slate-950">
                                <?= h($sampleCurrency) ?> <?= h(number_format($priceRangeMin, 2, '.', '')) ?>
                                <span class="text-slate-400">-</span>
                                <?= h($sampleCurrency) ?> <?= h(number_format($priceRangeMax, 2, '.', '')) ?>
                            </div>
                            <p class="mt-2 text-sm text-brand-700"><?= h(block('product_detail', 'price_range_note')) ?></p>
                        </div>
                    <?php elseif ($priceMode === 'negotiable'): ?>
                        <div class="mb-6 rounded-lg border border-slate-200 bg-slate-50 p-5">
                            <div class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">
                                <?= h(block('product_detail', 'negotiable_label')) ?>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-700"><?= h(block('product_detail', 'negotiable_text')) ?></p>
                        </div>
                    <?php endif; ?>

                    <hr class="border-slate-100 my-6">

                    <!-- Action Buttons -->
                    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button id="open-inquiry-modal" 
                                class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
                            <?= h(block('product_detail', 'inquiry_text')) ?>
                        </button>
                        <?php
                        $wa = $whatsapp ?? '';
                        $waDigits = preg_replace('/\D+/', '', $wa);
                        ?>
                        <a href="<?= h(!empty($waDigits) ? 'https://wa.me/' . $waDigits : '#inquiry') ?>" 
                           target="<?= !empty($waDigits) ? '_blank' : '' ?>"
                           <?= !empty($waDigits) ? 'rel="noopener noreferrer"' : '' ?>
                           class="px-6 py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition-colors inline-flex items-center justify-center gap-2 shadow-sm">
                            <i class="fab fa-whatsapp"></i>
                            <?= h(block('product_detail', 'chat_text')) ?>
                        </a>
                    </div>

                    <?php if ($priceMode === 'negotiable'): ?>
                        <div class="text-sm leading-6 text-slate-600 bg-slate-50 rounded-lg p-4">
                            <?= h(block('product_detail', 'negotiable_text')) ?>
                            <a href="#inquiry" class="font-semibold underline text-slate-950 hover:text-brand-600"
                               onclick="document.getElementById('open-inquiry-modal').click(); return false;"><?= h(block('product_detail', 'inquiry_text')) ?></a>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-slate-600 bg-slate-50 rounded-lg p-4">
                            <?= h(block('product_detail', 'sample_text')) ?> <span class="font-semibold"><?= h($sampleCurrency) ?> <?= h($samplePrice) ?>/Pieces</span>!
                            <a href="#inquiry" class="font-semibold underline text-slate-950 hover:text-brand-600"
                               onclick="document.getElementById('open-inquiry-modal').click(); return false;"><?= h(block('product_detail', 'sample_link_text')) ?></a>
                        </div>
                    <?php endif; ?>

                    <!-- More in category -->
                    <?php if ($category): ?>
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <a href="<?= h(product_category_url($category)) ?>"
                               class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-brand-50 hover:text-brand-700 transition-colors">
                                <i class="fas fa-folder mr-2"></i>
                                <?= h(block('product_detail', 'more_category_text')) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Description -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:col-span-7">
                <h2 class="mb-4 text-xl font-bold text-slate-950"><?= h(block('product_detail', 'description_title')) ?></h2>
                <div class="rich-content">
                    <?= process_rich_text($item['content']) ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
<section class="bg-white py-10 sm:py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <h2 class="text-2xl font-bold text-slate-950 text-center mb-8">
            <i class="fas fa-th-large mr-2 text-brand-600"></i><?= h(block('product_detail', 'related_heading')) ?>
        </h2>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($related_products as $product): ?>
                <a href="<?= h($product['url']) ?>" class="group">
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden hover:border-brand-200 hover:shadow-md transition-all">
                        <div class="aspect-square overflow-hidden bg-slate-100">
                            <?php if (!empty($product['cover'])): ?>
                                <img src="<?= h(asset_url((string)$product['cover'])) ?>" 
                                     alt="<?= h($product['title']) ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                     loading="lazy"
                                     decoding="async">
                            <?php else: ?>
                                <img src="<?= h(get_image_url(null, 400, 400, 'No Image')) ?>"
                                     alt="<?= h($product['title']) ?>" 
                                     class="w-full h-full object-cover"
                                     loading="lazy"
                                     decoding="async">
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-slate-950 line-clamp-1 group-hover:text-brand-600 transition-colors">
                                <?= h($product['title']) ?>
                            </h3>
                            <?php if (!empty($product['summary'])): ?>
                                <p class="text-sm text-slate-500 mt-1 line-clamp-2"><?= h($product['summary']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Inquiry Modal -->
<?php if (!empty($inquiry_form)): ?>
<div id="inquiry-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm close-inquiry-modal"></div>
    <div class="absolute inset-3 flex items-center justify-center md:inset-10 lg:inset-20">
        <div class="max-h-full w-full max-w-4xl overflow-y-auto rounded-lg bg-white shadow-xl">
            <div class="p-5 lg:p-8 relative">
                <button class="close-inquiry-modal absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                    <i class="fas fa-times text-slate-500"></i>
                </button>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Left Info -->
                    <div class="lg:col-span-4 hidden lg:block">
                        <h2 class="text-2xl font-bold text-slate-950 mb-3"><?= h(block('product_detail', 'inquiry_text')) ?></h2>
                        <p class="text-slate-500 mb-6">Thank you for your interest. Our team will review your requirements.</p>
                        <div class="space-y-3">
                            <div class="flex items-center text-slate-700">
                                <i class="fas fa-check-circle text-brand-600 mr-3"></i>
                                ISO Certified
                            </div>
                            <div class="flex items-center text-slate-700">
                                <i class="fas fa-check-circle text-brand-600 mr-3"></i>
                                OEM & ODM
                            </div>
                            <div class="flex items-center text-slate-700">
                                <i class="fas fa-check-circle text-brand-600 mr-3"></i>
                                Global Presence
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form -->
                    <div class="lg:col-span-8">
                        <h2 class="text-2xl font-bold text-slate-950 mb-6 lg:hidden">Request Quote</h2>
                        <form method="post" action="<?= url('/inquiry') ?>">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="product_id" value="<?= h((string)$item['id']) ?>">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Your Name *</label>
                                    <input type="text" name="name" required placeholder="Full Name"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Address *</label>
                                    <input type="email" name="email" required placeholder="example@email.com"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Company</label>
                                    <input type="text" name="company" placeholder="Company Ltd."
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Quantity Needed</label>
                                    <input type="text" name="quantity" placeholder="e.g. 500 units"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Requirements</label>
                                <textarea name="message" rows="4" placeholder="Project requirements, customization, etc."
                                          class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors resize-none"></textarea>
                            </div>

                            <button type="submit" class="w-full px-6 py-3 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors flex items-center justify-center gap-2 shadow-sm">
                                <i class="fas fa-paper-plane"></i>
                                Send My Inquiry
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Lightbox Modal -->
<?php if (!empty($images)): ?>
<div id="image-lightbox" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" onclick="closeLightbox()"></div>
    <div class="absolute inset-4 flex items-center justify-center">
        <img id="lightbox-image" src="" alt="<?= h($item['title']) ?>" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
    <button onclick="closeLightbox()" class="absolute top-4 right-4 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors">
        <i class="fas fa-times text-xl"></i>
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.js" integrity="sha384-2UI1PfnXFjVMQ7/ZDEF70CR943oH3v6uZrFQGGqJYlvhh4g6z6uVktxYbOlAczav" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
(function() {
    const images = <?= json_encode(array_map(fn($img) => asset_url($img), $images), JSON_UNESCAPED_UNICODE) ?>;
    const lightbox = document.getElementById("image-lightbox");
    const lightboxImage = document.getElementById("lightbox-image");

    const hasMultipleImages = images.length > 1;
    let thumbsSwiper = null;
    if (hasMultipleImages && document.querySelector('.thumbs-swiper')) {
        thumbsSwiper = new Swiper('.thumbs-swiper', {
            spaceBetween: 10,
            slidesPerView: Math.min(images.length, 4),
            freeMode: true,
            watchSlidesProgress: true,
            slideToClickedSlide: true,
            breakpoints: {
                640: { slidesPerView: Math.min(images.length, 5) },
                1024: { slidesPerView: Math.min(images.length, 6) }
            }
        });
    }

    // Initialize main Swiper
    const mainSwiper = new Swiper('.main-swiper', {
        spaceBetween: 10,
        navigation: hasMultipleImages ? {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        } : false,
        thumbs: thumbsSwiper ? { swiper: thumbsSwiper } : undefined,
    });

    // Open lightbox
    mainSwiper.slides.forEach((slide, index) => {
        slide.addEventListener('click', () => {
            if (lightboxImage) {
                lightboxImage.src = images[index];
                lightbox.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close lightbox
    window.closeLightbox = function() {
        lightbox.classList.add('hidden');
        document.body.style.overflow = '';
    };
})();
</script>
<?php endif; ?>

<?php if (!empty($inquiry_form)): ?>
<script>
(function() {
    // Inquiry Modal
    const inquiryModal = document.getElementById("inquiry-modal");
    const openInquiryBtn = document.getElementById("open-inquiry-modal");
    const closeInquiryBtns = document.querySelectorAll(".close-inquiry-modal");

    if (openInquiryBtn && inquiryModal) {
        openInquiryBtn.addEventListener("click", () => {
            inquiryModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }
    closeInquiryBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            inquiryModal.classList.add('hidden');
            document.body.style.overflow = '';
        });
    });
})();
</script>
<?php endif; ?>
