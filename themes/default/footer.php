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
foreach ([
    'contact_telegram' => ['Telegram', 'fab fa-telegram', 'telegram'],
    'contact_line' => ['LINE', 'fab fa-line', 'line'],
    'contact_vk' => ['VK', 'fab fa-vk', 'vk'],
] as $key => [$label, $icon, $theme]) {
    $value = trim((string)($site[$key] ?? ''));
    $href = default_theme_contact_channel_href($theme, $value);
    if ($href !== '') {
        $floatingContacts[] = [
            'label' => $label,
            'value' => $value,
            'href' => $href,
            'icon' => $icon,
            'theme' => $theme
        ];
    }
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
    'twitter' => ['X (Twitter)', 'fab fa-twitter'],
    'linkedin' => ['LinkedIn', 'fab fa-linkedin-in'],
    'youtube' => ['YouTube', 'fab fa-youtube'],
    'tiktok' => ['TikTok', 'fab fa-tiktok'],
    'pinterest' => ['Pinterest', 'fab fa-pinterest-p'],
    'reddit' => ['Reddit', 'fab fa-reddit-alien'],
    'telegram' => ['Telegram', 'fab fa-telegram'],
    'discord' => ['Discord', 'fab fa-discord'],
    'quora' => ['Quora', 'fab fa-quora'],
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

$footerMenuSlug = trim((string)block('footer', 'quick_links_menu_slug'));
if ($footerMenuSlug === '') {
    try {
        $menuModel = new \App\Models\Menu();
        $menus = $menuModel->getAll();
        if (!empty($menus)) {
            $footerMenuSlug = (string)($menus[0]['slug'] ?? '');
        }
    } catch (\Throwable $e) {
        $footerMenuSlug = '';
    }
}
$footerMenuItems = $footerMenuSlug !== '' ? get_menu_items($footerMenuSlug) : [];
if (empty($footerMenuItems)) {
    $footerMenuItems = [
        ['title' => 'Products', 'url' => '/products', 'target' => '_self', 'children' => []],
        ['title' => 'Cases', 'url' => '/cases', 'target' => '_self', 'children' => []],
        ['title' => 'Blog', 'url' => '/blog', 'target' => '_self', 'children' => []],
        ['title' => 'About Us', 'url' => '/about', 'target' => '_self', 'children' => []],
        ['title' => 'Contact', 'url' => '/contact', 'target' => '_self', 'children' => []],
    ];
}

$renderFooterMenuItems = function(array $items, int $level = 0) use (&$renderFooterMenuItems): void {
    foreach ($items as $fItem):
        $itemUrl = trim((string)($fItem['url'] ?? '#'));
        $href = preg_match('#^(https?:)?//#i', $itemUrl) === 1 ? $itemUrl : url($itemUrl);
        $target = (string)($fItem['target'] ?? '_self');
        $indentClass = $level > 0 ? 'pl-' . min(8, 3 + ($level * 3)) : '';
?>
        <a href="<?= h($href) ?>" class="block <?= h($indentClass) ?> text-gray-600 hover:text-brand-600 transition-colors"
           <?= $target === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= h($fItem['title'] ?? '') ?></a>
        <?php if (!empty($fItem['children'])): ?>
            <?php $renderFooterMenuItems($fItem['children'], $level + 1); ?>
        <?php endif; ?>
<?php
    endforeach;
};
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
        <span><?= h(block('float_contact', 'toggle_text')) ?></span>
    </button>

    <div class="site-float-contact__panel" id="site-float-contact-panel">
        <div class="site-float-contact__eyebrow"><?= h(block('float_contact', 'eyebrow')) ?></div>
        <h3 class="site-float-contact__title"><?= h(block('float_contact', 'title')) ?></h3>
        <p class="site-float-contact__desc"><?= h(block('float_contact', 'desc')) ?></p>

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

        <a href="<?= h(url(block('float_contact', 'cta_url', '/contact'))) ?>" class="site-float-contact__cta"><?= h(block('float_contact', 'cta_text')) ?></a>
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
                <div class="flex flex-wrap gap-3">
                    <?php foreach ($socialMap as $key => [$label, $icon]): ?>
                        <?php if (!empty($site[$key])): ?>
                            <a href="<?= h($site[$key]) ?>" target="_blank" rel="noopener noreferrer" title="<?= h($label) ?>"
                               class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:text-brand-600 hover:border-brand-600 transition-all">
                                <i class="<?= h($icon) ?>"></i>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="lg:col-span-7">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-4"><?= h(block('footer', 'contact_title')) ?></h4>
                        <div class="space-y-3">
                            <?php if (!empty($site['company_email'])): ?>
                                <a href="mailto:<?= h($site['company_email']) ?>" class="flex items-start text-gray-600 hover:text-brand-600 transition-colors">
                                    <i class="fas fa-envelope mt-1 w-5 flex-shrink-0 mr-2"></i>
                                    <span class="min-w-0 break-all"><?= h($site['company_email']) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($site['company_phone'])): ?>
                                <a href="tel:<?= h($site['company_phone']) ?>" class="flex items-start text-gray-600 hover:text-brand-600 transition-colors">
                                    <i class="fas fa-phone mt-1 w-5 flex-shrink-0 mr-2"></i>
                                    <span class="min-w-0 break-words"><?= h($site['company_phone']) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php foreach ([
                                'contact_telegram' => ['Telegram', 'fab fa-telegram', 'telegram'],
                                'contact_line' => ['LINE', 'fab fa-line', 'line'],
                                'contact_vk' => ['VK', 'fab fa-vk', 'vk'],
                            ] as $key => [$label, $icon, $channel]): ?>
                                <?php $channelHref = default_theme_contact_channel_href($channel, $site[$key] ?? ''); ?>
                                <?php if ($channelHref !== ''): ?>
                                    <a href="<?= h($channelHref) ?>" target="_blank" rel="noopener noreferrer" class="flex items-start text-gray-600 hover:text-brand-600 transition-colors">
                                        <i class="<?= h($icon) ?> mt-1 w-5 flex-shrink-0 mr-2"></i>
                                        <span class="min-w-0 break-words"><?= h($label) ?>: <?= h($site[$key]) ?></span>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if (!empty($site['company_address'])): ?>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode((string)$site['company_address']) ?>" target="_blank" rel="noopener noreferrer" class="flex items-start text-gray-600 hover:text-brand-600 transition-colors">
                                    <i class="fas fa-map-marker-alt mt-1 w-5 flex-shrink-0 mr-2"></i>
                                    <span class="min-w-0 break-words"><?= h($site['company_address']) ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-4"><?= h(block('footer', 'quick_links_title')) ?></h4>
                        <div class="space-y-2">
                            <?php $renderFooterMenuItems($footerMenuItems); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="py-6 border-t border-gray-100">
            <p class="text-center text-gray-500 text-sm">
                <?php
                $customCopyright = block('footer', 'copyright');
                if ($customCopyright): ?>
                    <?= h($customCopyright) ?>
                <?php else: ?>
                    &copy; <?= date('Y') ?> <?= h($site['name']) ?>. All rights reserved.
                <?php endif; ?>
            </p>
        </div>
    </div>
</footer>

<?= get_footer_code() ?>
</body>
</html>
