<?php
/**
 * 页面模板：产品详情
 * 作用：展示产品图片、参数、询盘入口与相关信息。
 * 变量：$item（产品数据）、$images（图片列表）、$category（分类数据）。
 * 注意：包含 Swiper 轮播依赖。
 */
$category = $category ?? null;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

<section class="py-8">
    <div class="container mx-auto px-4 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="text-sm mb-6" aria-label="breadcrumb">
            <ol class="flex items-center space-x-2 text-gray-500">
                <li><a href="<?= url('/') ?>" class="hover:text-brand-600">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= url('/products') ?>" class="hover:text-brand-600">Products</a></li>
                <?php if ($category): ?>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li><a href="<?= url('/products') ?>?category=<?= (int)$category['id'] ?>" class="hover:text-brand-600"><?= h($category['name']) ?></a></li>
                <?php endif; ?>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-gray-900 font-medium"><?= h($item['title']) ?></li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left: Images & Description -->
            <div class="lg:w-7/12">
                <?php if (!empty($images)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                        <!-- Main Swiper -->
                        <div class="swiper main-swiper rounded-lg overflow-hidden mb-3">
                            <div class="swiper-wrapper">
                                <?php foreach ($images as $img): ?>
                                    <div class="swiper-slide aspect-square">
                                        <img src="<?= asset_url(h($img)) ?>" 
                                             alt="<?= h($item['title']) ?>" 
                                             class="w-full h-full object-cover cursor-zoom-in">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-button-next !w-10 !h-10 !bg-white/80 !rounded-full !text-gray-800 hover:!bg-white"></div>
                            <div class="swiper-button-prev !w-10 !h-10 !bg-white/80 !rounded-full !text-gray-800 hover:!bg-white"></div>
                        </div>
                        <!-- Thumbs Swiper -->
                        <div class="swiper thumbs-swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($images as $img): ?>
                                    <div class="swiper-slide cursor-pointer rounded-lg overflow-hidden border-2 border-transparent hover:border-brand-500 transition-colors">
                                        <img src="<?= asset_url(h($img)) ?>" 
                                             alt="<?= h($item['title']) ?>" 
                                             class="w-full h-full object-cover">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Product Description</h2>
                    <div class="prose max-w-none text-gray-600">
                        <?= process_rich_text($item['content']) ?>
                    </div>
                </div>
            </div>

            <!-- Right: Product Info -->
            <div class="lg:w-5/12">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h1 class="text-2xl font-bold text-gray-900 mb-3"><?= h($item['title']) ?></h1>
                    
                    <div class="flex flex-wrap items-center gap-3 mb-4 text-sm text-gray-500">
                        <span class="flex items-center">
                            <i class="far fa-calendar-alt mr-1"></i>
                            <?= format_date($item['created_at'], 'Y-m-d') ?>
                        </span>
                        <?php if ($category): ?>
                            <a href="<?= url('/products') ?>?category=<?= (int)$category['id'] ?>" 
                               class="inline-flex items-center px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                                <i class="fas fa-folder mr-1"></i>
                                <?= h($category['name']) ?>
                            </a>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                                <?= h($item['category_name'] ?? 'Uncategorized') ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($item['summary'])): ?>
                        <p class="text-gray-600 mb-6"><?= h($item['summary']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($price_tiers)): ?>
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <?php foreach ($price_tiers as $tier): ?>
                                <div class="bg-gray-50 rounded-lg p-4 text-center">
                                    <div class="text-xl font-bold text-gray-900">
                                        <?= h($tier['currency']) ?>$<?= h((string)$tier['price']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= number_format((float)$tier['min_qty']) ?><?php if (!empty($tier['max_qty'])): ?>-<?= number_format((float)$tier['max_qty']) ?><?php else: ?>+<?php endif; ?> Pieces
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <hr class="border-gray-100 my-6">

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <button id="open-inquiry-modal" 
                                class="px-6 py-3 bg-rose-500 text-white font-semibold rounded-lg hover:bg-rose-600 transition-colors shadow-md">
                            Send Inquiry
                        </button>
                        <?php
                        $wa = $whatsapp ?? '';
                        $waDigits = preg_replace('/\D+/', '', $wa);
                        ?>
                        <a href="<?= !empty($waDigits) ? 'https://wa.me/' . h($waDigits) : '#inquiry' ?>" 
                           target="<?= !empty($waDigits) ? '_blank' : '' ?>"
                           class="px-6 py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition-colors shadow-md inline-flex items-center justify-center gap-2">
                            <i class="fab fa-whatsapp"></i>
                            Chat Now
                        </a>
                    </div>

                    <div class="text-sm text-gray-600 bg-gray-50 rounded-lg p-4">
                        Still deciding? Get samples of <span class="font-semibold">US$ <?= !empty($price_tiers) ? h($price_tiers[0]['currency']) . ' ' . h((string)($price_tiers[0]['price'] * 2)) : '50.00' ?>/Pieces</span>!
                        <a href="#inquiry" class="font-semibold underline text-gray-900 hover:text-brand-600" 
                           onclick="document.getElementById('open-inquiry-modal').click(); return false;">Request Sample</a>
                    </div>

                    <!-- More in category -->
                    <?php if ($category): ?>
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <a href="<?= url('/products') ?>?category=<?= (int)$category['id'] ?>" 
                               class="inline-flex items-center px-4 py-2 bg-amber-100 text-amber-700 text-sm font-medium rounded-lg hover:bg-amber-200 transition-colors">
                                <i class="fas fa-folder mr-2"></i>
                                More in this category
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">
            <i class="fas fa-th-large mr-2 text-brand-600"></i>Related Products
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($related_products as $product): ?>
                <a href="<?= h($product['url']) ?>" class="group">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="aspect-square overflow-hidden bg-gray-100">
                            <?php if (!empty($product['cover'])): ?>
                                <img src="<?= asset_url(h($product['cover'])) ?>" 
                                     alt="<?= h($product['title']) ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            <?php else: ?>
                                <img src="<?= placeholder_url(400, 400, 'No Image') ?>" 
                                     alt="<?= h($product['title']) ?>" 
                                     class="w-full h-full object-cover">
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 line-clamp-1 group-hover:text-brand-600 transition-colors">
                                <?= h($product['title']) ?>
                            </h3>
                            <?php if (!empty($product['summary'])): ?>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?= h($product['summary']) ?></p>
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
    <div class="absolute inset-4 md:inset-10 lg:inset-20 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-full overflow-y-auto">
            <div class="p-6 lg:p-8 relative">
                <button class="close-inquiry-modal absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Left Info -->
                    <div class="lg:col-span-4 hidden lg:block">
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">Request Quote</h2>
                        <p class="text-gray-500 mb-6">Thank you for your interest. Our team will review your requirements.</p>
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-check-circle text-brand-600 mr-3"></i>
                                ISO Certified
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-check-circle text-brand-600 mr-3"></i>
                                OEM & ODM
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-check-circle text-brand-600 mr-3"></i>
                                Global Presence
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form -->
                    <div class="lg:col-span-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 lg:hidden">Request Quote</h2>
                        <form method="post" action="<?= url('/inquiry') ?>">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="product_id" value="<?= h((string)$item['id']) ?>">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Your Name *</label>
                                    <input type="text" name="name" required placeholder="Full Name"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                    <input type="email" name="email" required placeholder="example@email.com"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                    <input type="text" name="company" placeholder="Company Ltd."
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Needed</label>
                                    <input type="text" name="quantity" placeholder="e.g. 500 units"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
                                <textarea name="message" rows="4" placeholder="Project requirements, customization, etc."
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors resize-none"></textarea>
                            </div>

                            <button type="submit" class="w-full px-6 py-3 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors flex items-center justify-center gap-2">
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

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
(function() {
    const images = <?= json_encode(array_map(fn($img) => asset_url($img), $images), JSON_UNESCAPED_UNICODE) ?>;
    const lightbox = document.getElementById("image-lightbox");
    const lightboxImage = document.getElementById("lightbox-image");

    // Initialize thumbs Swiper
    const thumbsSwiper = new Swiper('.thumbs-swiper', {
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesProgress: true,
    });

    // Initialize main Swiper
    const mainSwiper = new Swiper('.main-swiper', {
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        thumbs: {
            swiper: thumbsSwiper,
        },
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
