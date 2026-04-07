<?php
/**
 * 模板片段：站点底部
 * 作用：输出公司信息、社交媒体链接、版权信息与全站脚本。
 * 变量：$site（站点设置）。
 * 注意：由布局模板自动引入，不应独立渲染。
 */
?>
</main>

<?php
$whatsappValue = trim((string)($site['whatsapp'] ?? ''));
$whatsappDigits = preg_replace('/\D+/', '', $whatsappValue);
$whatsappHref = '';
if ($whatsappValue !== '') {
    if (preg_match('#^https?://#i', $whatsappValue)) {
        $whatsappHref = $whatsappValue;
    } elseif ($whatsappDigits !== '') {
        $whatsappHref = 'https://wa.me/' . $whatsappDigits;
    }
}

$floatingContacts = [];
if (!empty($site['company_phone'])) {
    $floatingContacts[] = [
        'label' => 'Phone',
        'value' => $site['company_phone'],
        'href' => 'tel:' . $site['company_phone'],
        'icon' => 'fas fa-phone',
        'theme' => 'phone'
    ];
}
if (!empty($site['company_email'])) {
    $floatingContacts[] = [
        'label' => 'Email',
        'value' => $site['company_email'],
        'href' => 'mailto:' . $site['company_email'],
        'icon' => 'fas fa-envelope',
        'theme' => 'email'
    ];
}
if ($whatsappHref !== '') {
    $floatingContacts[] = [
        'label' => 'WhatsApp',
        'value' => $whatsappValue,
        'href' => $whatsappHref,
        'icon' => 'fab fa-whatsapp',
        'theme' => 'whatsapp'
    ];
}
if (!empty($site['company_address'])) {
    $floatingContacts[] = [
        'label' => 'Address',
        'value' => $site['company_address'],
        'href' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($site['company_address']),
        'icon' => 'fas fa-location-dot',
        'theme' => 'address'
    ];
}

$floatingSocialLinks = [];
$socialMap = [
    'facebook' => ['Facebook', 'fab fa-facebook-f'],
    'instagram' => ['Instagram', 'fab fa-instagram'],
    'twitter' => ['Twitter', 'fab fa-twitter'],
    'linkedin' => ['LinkedIn', 'fab fa-linkedin-in'],
    'youtube' => ['YouTube', 'fab fa-youtube'],
];
foreach ($socialMap as $key => [$label, $icon]) {
    if (!empty($site[$key])) {
        $floatingSocialLinks[] = [
            'label' => $label,
            'icon' => $icon,
            'href' => $site[$key],
            'theme' => $key
        ];
    }
}

$hasFloatingContact = !empty($floatingContacts) || !empty($floatingSocialLinks);
?>

<?php if ($hasFloatingContact): ?>
<aside class="site-float-contact" data-float-contact>
    <button
        type="button"
        class="site-float-contact__toggle"
        data-float-contact-toggle
        aria-expanded="false"
        aria-controls="site-float-contact-panel"
    >
        <i class="fas fa-comments"></i>
        <span>Contact</span>
    </button>

    <div class="site-float-contact__panel" id="site-float-contact-panel">
        <div class="site-float-contact__eyebrow">Global Contact</div>
        <h3 class="site-float-contact__title">Talk to our team</h3>
        <p class="site-float-contact__desc">Quick access to your configured contact details and social channels.</p>

        <?php if ($floatingContacts): ?>
            <div class="site-float-contact__group">
                <?php foreach ($floatingContacts as $item): ?>
                    <a
                        href="<?= h($item['href']) ?>"
                        class="site-float-contact__item site-float-contact__item--<?= h($item['theme']) ?>"
                        <?= str_starts_with($item['href'], 'http') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                    >
                        <span class="site-float-contact__icon"><i class="<?= h($item['icon']) ?>"></i></span>
                        <span class="site-float-contact__meta">
                            <span class="site-float-contact__label"><?= h($item['label']) ?></span>
                            <span class="site-float-contact__value"><?= h($item['value']) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($floatingSocialLinks): ?>
            <div class="site-float-contact__social">
                <?php foreach ($floatingSocialLinks as $item): ?>
                    <a
                        href="<?= h($item['href']) ?>"
                        class="site-float-contact__social-link site-float-contact__social-link--<?= h($item['theme']) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="<?= h($item['label']) ?>"
                        title="<?= h($item['label']) ?>"
                    >
                        <span class="site-float-contact__social-icon"><i class="<?= h($item['icon']) ?>"></i></span>
                        <span class="site-float-contact__social-text"><?= h($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="<?= url('/contact') ?>" class="site-float-contact__cta">Send Inquiry</a>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const widget = document.querySelector('[data-float-contact]');
    const toggle = document.querySelector('[data-float-contact-toggle]');
    if (!widget || !toggle) return;

    const closeWidget = () => {
        widget.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    const openWidget = () => {
        widget.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
    };

    toggle.addEventListener('click', function () {
        if (widget.classList.contains('is-open')) {
            closeWidget();
        } else {
            openWidget();
        }
    });

    document.addEventListener('click', function (event) {
        if (!widget.contains(event.target) && window.innerWidth < 1024) {
            closeWidget();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeWidget();
        }
    });
});
</script>
<?php endif; ?>

<!-- Footer -->
<footer class="bg-white border-t border-gray-200 mt-16">
    <div class="container mx-auto px-4 lg:px-8">
        <!-- Footer Top -->
        <div class="py-12 grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Brand Info -->
            <div class="lg:col-span-5">
                <h3 class="text-xl font-bold text-gray-900 mb-2"><?= h($site['name']) ?></h3>
                <p class="text-gray-500 mb-6"><?= h($site['tagline']) ?></p>
                <div class="flex space-x-3">
                    <?php if (!empty($site['facebook'])): ?>
                        <a href="<?= h($site['facebook']) ?>" target="_blank" title="Facebook" 
                           class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:text-brand-600 hover:border-brand-600 transition-all">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($site['instagram'])): ?>
                        <a href="<?= h($site['instagram']) ?>" target="_blank" title="Instagram"
                           class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:text-brand-600 hover:border-brand-600 transition-all">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($site['twitter'])): ?>
                        <a href="<?= h($site['twitter']) ?>" target="_blank" title="Twitter"
                           class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:text-brand-600 hover:border-brand-600 transition-all">
                            <i class="fab fa-twitter"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($site['linkedin'])): ?>
                        <a href="<?= h($site['linkedin']) ?>" target="_blank" title="LinkedIn"
                           class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:text-brand-600 hover:border-brand-600 transition-all">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($site['youtube'])): ?>
                        <a href="<?= h($site['youtube']) ?>" target="_blank" title="YouTube"
                           class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:text-brand-600 hover:border-brand-600 transition-all">
                            <i class="fab fa-youtube"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="lg:col-span-7">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-4">Contact</h4>
                        <div class="space-y-3">
                            <?php if (!empty($site['company_email'])): ?>
                                <a href="mailto:<?= h($site['company_email']) ?>" class="flex items-center text-gray-600 hover:text-brand-600 transition-colors">
                                    <i class="fas fa-envelope w-5 mr-2"></i>
                                    <span><?= h($site['company_email']) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($site['company_phone'])): ?>
                                <a href="tel:<?= h($site['company_phone']) ?>" class="flex items-center text-gray-600 hover:text-brand-600 transition-colors">
                                    <i class="fas fa-phone w-5 mr-2"></i>
                                    <span><?= h($site['company_phone']) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($site['company_address'])): ?>
                                <a href="https://goo.gl/maps/<?= h($site['company_address']) ?>" target="_blank" class="flex items-center text-gray-600 hover:text-brand-600 transition-colors">
                                    <i class="fas fa-map-marker-alt w-5 mr-2"></i>
                                    <span><?= h($site['company_address']) ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-4">Quick Links</h4>
                        <div class="space-y-2">
                            <a href="<?= url('/products') ?>" class="block text-gray-600 hover:text-brand-600 transition-colors">Products</a>
                            <a href="<?= url('/cases') ?>" class="block text-gray-600 hover:text-brand-600 transition-colors">Cases</a>
                            <a href="<?= url('/blog') ?>" class="block text-gray-600 hover:text-brand-600 transition-colors">Blog</a>
                            <a href="<?= url('/about') ?>" class="block text-gray-600 hover:text-brand-600 transition-colors">About Us</a>
                            <a href="<?= url('/contact') ?>" class="block text-gray-600 hover:text-brand-600 transition-colors">Contact</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="py-6 border-t border-gray-100">
            <p class="text-center text-gray-500 text-sm">
                © <?= date('Y') ?> <?= h($site['name']) ?>. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<?= get_footer_code() ?>
</body>
</html>
