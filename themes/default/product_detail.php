<?php
$category = $category ?? null;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<section class="section">
    <div class="container">
        <!-- 面包屑导航 -->
        <nav class="breadcrumb mb-5" aria-label="breadcrumbs">
            <ul>
                <li><a href="<?= url('/') ?>"><?= h(t('nav_home')) ?></a></li>
                <li><a href="<?= url('/products') ?>"><?= h(t('products')) ?></a></li>
                <?php if ($category): ?>
                    <li><a href="<?= url('/products') ?>?category=<?= (int)$category['id'] ?>"><?= h($category['name']) ?></a></li>
                <?php endif; ?>
                <li class="is-active"><a href="#" aria-current="page"><?= h($item['title']) ?></a></li>
            </ul>
        </nav>

        <div class="columns mb-5">
            <div class="column is-6">
                <?php if (!empty($images)): ?>
                    <div class="box soft-card">
                        <!-- Swiper 主图 -->
                        <div class="swiper main-swiper" style="width: 100%; ">
                            <div class="swiper-wrapper">
                                <?php foreach ($images as $img): ?>
                                    <div class="swiper-slide">
                                        <img src="<?= asset_url(h($img)) ?>" alt="<?= h($item['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; cursor: zoom-in;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                        <!-- Swiper 缩略图 -->
                        <div class="swiper thumbs-swiper" style="width: 100%; height: 100px; margin-top: 10px;">
                            <div class="swiper-wrapper">
                                <?php foreach ($images as $img): ?>
                                    <div class="swiper-slide" style="width: 25%; height: 100%;">
                                        <img src="<?= asset_url(h($img)) ?>" alt="<?= h($item['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            <div class="column is-6">
                <div class="box soft-card">
                    <h1 class="title is-3"><?= h($item['title']) ?></h1>
                    <div class="is-flex is-align-items-center mb-3" style="gap: 0.75rem;">
                        <span class="is-size-7 has-text-grey">
                            <i class="far fa-calendar-alt mr-1"></i>
                            <?= format_date($item['created_at'], 'Y-m-d') ?>
                        </span>
                        <?php if ($category): ?>
                            <a href="<?= url('/products') ?>?category=<?= (int)$category['id'] ?>" class="tag is-warning is-light">
                                <span class="icon is-small"><i class="fas fa-folder"></i></span>
                                <span><?= h($category['name']) ?></span>
                            </a>
                        <?php else: ?>
                            <span class="tag is-light"><?= h($item['category_name'] ?? t('product_uncategorized')) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($item['summary'])): ?>
                        <div class="content" style="margin-top:16px"><?= h($item['summary']) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($price_tiers)): ?>
                        <div class="price-tiers-wrap mt-5 mb-4">
                            <div class="columns is-mobile is-variable is-3">
                                <?php foreach ($price_tiers as $tier): ?>
                                    <div class="column">
                                        <div class="price-tier">
                                            <div class="has-text-weight-bold is-size-4" style="color: #333;">
                                                <?= h($tier['currency']) ?>$<?= h((string)$tier['price']) ?>
                                            </div>
                                            <div class="is-size-7 has-text-grey">
                                                <?= number_format((float)$tier['min_qty']) ?><?php if (!empty($tier['max_qty'])): ?>-<?= number_format((float)$tier['max_qty']) ?><?php else: ?>+<?php endif; ?> <?= h(t('product_pieces')) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr class="my-5" style="background-color: #f0f0f0; height: 1px; border: none;">

                    <div class="action-buttons">
                        <div class="columns is-mobile is-variable is-2">
                            <div class="column">
                                <button class="button is-danger is-medium is-fullwidth is-rounded has-text-weight-bold" id="open-inquiry-modal" style="background-color: #d65a53; border: none; height: 50px;">
                                    <?= h(t('detail_send_inquiry')) ?>
                                </button>
                            </div>
                            <div class="column">
                                <?php
                                $wa = $whatsapp ?? '';
                                $waDigits = preg_replace('/\D+/', '', $wa);
                                ?>
                                <a style="background-color: #25D366; color:#fff;" class="button is-white is-medium is-fullwidth is-rounded has-text-weight-bold"
                                    target="_blank" rel="noopener"
                                    href="<?= !empty($waDigits) ? 'https://wa.me/' . h($waDigits) : '#inquiry' ?>"
                                    style="border: 1px solid #333; height: 50px; color: #333;">
                                    <span class="icon" style="color:#fff;">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </span>
                                    <span><?= h(t('chat_now')) ?></span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="sample-info mt-4 is-size-7 has-text-grey-dark">
                        <?= h(t('product_sample_tip')) ?> <span class="has-text-weight-bold">US$ <?= !empty($price_tiers) ? h($price_tiers[0]['currency']) . ' ' . h((string)($price_tiers[0]['price'] * 2)) : '50.00' ?>/<?= h(t('product_pieces')) ?></span> !
                        <a href="#inquiry" class="has-text-weight-bold is-underlined has-text-black" onclick="document.getElementById('open-inquiry-modal').click(); return false;"><?= h(t('product_sample_btn')) ?></a>
                    </div>

                    <!-- 同类产品链接 -->
                    <?php if ($category): ?>
                        <div class="mt-5 pt-4" style="border-top: 1px solid #f0f0f0;">
                            <a href="<?= url('/products') ?>?category=<?= (int)$category['id'] ?>" class="button is-warning is-light is-small">
                                <span class="icon"><i class="fas fa-folder"></i></span>
                                <span><?= h(t('product_more_in_category') ?? '更多同类产品') ?></span>
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <article class="box soft-card">
            <h2 class="title is-4"><?= h(t('detail_intro')) ?></h2>
            <div><?= process_rich_text($item['content']) ?></div>
        </article>
    </div>
</section>
<?php if (!empty($inquiry_form)): ?>
    <div class="modal" id="inquiry-modal">
        <div class="modal-background"></div>
        <div class="modal-content" style="width: 90%; max-width: 800px;">
            <div class="box soft-card p-6 relative">
                <button class="delete is-pulled-right close-inquiry-modal" aria-label="close" style="position: absolute; right: 20px; top: 20px;"></button>
                <div class="columns">
                    <div class="column is-4 is-hidden-mobile">
                        <h2 class="title is-4"><?= h(t('cta_quote')) ?></h2>
                        <p class="subtitle is-6 has-text-grey"><?= h(t('thanks_desc')) ?></p>
                        <div class="mt-5">
                            <div class="is-flex is-align-items-center mb-3">
                                <span class="icon has-text-link mr-2"><i class="fas fa-check-circle"></i></span>
                                <span class="is-size-7"><?= h(t('home_iso')) ?></span>
                            </div>
                            <div class="is-flex is-align-items-center mb-3">
                                <span class="icon has-text-link mr-2"><i class="fas fa-check-circle"></i></span>
                                <span class="is-size-7"><?= h(t('home_oem')) ?></span>
                            </div>
                            <div class="is-flex is-align-items-center">
                                <span class="icon has-text-link mr-2"><i class="fas fa-check-circle"></i></span>
                                <span class="is-size-7"><?= h(t('home_global')) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="column is-8-desktop is-12-mobile">
                        <h2 class="title is-4 is-hidden-tablet"><?= h(t('cta_quote')) ?></h2>
                        <form method="post" action="<?= url('/inquiry') ?>">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="product_id" value="<?= h((string)$item['id']) ?>">

                            <div class="columns">
                                <div class="column">
                                    <div class="field">
                                        <label class="label is-size-7"><?= h(t('form_name_full')) ?> *</label>
                                        <div class="control">
                                            <input class="input" name="name" required placeholder="<?= h(t('form_name_placeholder')) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="field">
                                        <label class="label is-size-7"><?= h(t('form_email_full')) ?> *</label>
                                        <div class="control">
                                            <input class="input" name="email" type="email" required placeholder="<?= h(t('form_email_placeholder')) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="columns">
                                <div class="column">
                                    <div class="field">
                                        <label class="label is-size-7"><?= h(t('form_company')) ?></label>
                                        <div class="control">
                                            <input class="input" name="company" placeholder="<?= h(t('form_company_placeholder')) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="field">
                                        <label class="label is-size-7"><?= h(t('form_quantity')) ?></label>
                                        <div class="control">
                                            <input class="input" name="quantity" placeholder="<?= h(t('form_qty_placeholder')) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label is-size-7"><?= h(t('form_requirements')) ?></label>
                                <div class="control">
                                    <textarea class="textarea" name="message" rows="3" placeholder="<?= h(t('form_req_placeholder')) ?>"></textarea>
                                </div>
                            </div>

                            <div class="field mt-5">
                                <button class="button is-link is-large is-fullwidth" type="submit">
                                    <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                    <span><?= h(t('btn_send_inquiry')) ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($images)): ?>
    <div class="modal" id="image-lightbox">
        <div class="modal-background"></div>
        <div class="modal-content">
            <p class="image">
                <img id="lightbox-image" src="" alt="<?= h($item['title']) ?>">
            </p>
        </div>
        <button class="modal-close is-large" aria-label="close"></button>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        (function() {
            const images = <?= json_encode(array_map(fn($img) => asset_url($img), $images), JSON_UNESCAPED_UNICODE) ?>;
            const lightbox = document.getElementById("image-lightbox");
            const lightboxImage = document.getElementById("lightbox-image");

            // 初始化缩略图 Swiper
            const thumbsSwiper = new Swiper('.thumbs-swiper', {
                spaceBetween: 10,
                slidesPerView: 4,
                freeMode: true,
                watchSlidesProgress: true,
            });

            // 初始化主图 Swiper
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

            // 点击主图打开 lightbox
            mainSwiper.slides.forEach((slide, index) => {
                slide.addEventListener('click', () => {
                    if (lightboxImage) {
                        lightboxImage.src = images[index];
                        lightbox.classList.add('is-active');
                    }
                });
            });

            // 关闭 lightbox
            if (lightbox) {
                lightbox.addEventListener('click', () => lightbox.classList.remove('is-active'));
            }

            // Inquiry Modal
            const inquiryModal = document.getElementById("inquiry-modal");
            const openInquiryBtn = document.getElementById("open-inquiry-modal");
            const closeInquiryBtns = document.querySelectorAll(".close-inquiry-modal, #inquiry-modal .modal-background");

            if (openInquiryBtn && inquiryModal) {
                openInquiryBtn.addEventListener("click", () => inquiryModal.classList.add("is-active"));
            }
            closeInquiryBtns.forEach(btn => {
                btn.addEventListener("click", () => inquiryModal.classList.remove("is-active"));
            });
        })();
    </script>
<?php endif; ?>