<?php

/**
 * 模板片段：站点头部
 * 作用：输出 HTML 头部、SEO/OG 元信息、全站导航与语言切换入口。
 * 变量：$site（站点设置）、$seo（SEO信息）、$currentTheme（主题名）、$languages/$lang（语言配置）。
 * 注意：由布局模板自动引入，不应独立渲染。
 */

// 获取菜单项（如果没有设置，将使用默认菜单）
$menuItems = get_menu_items('main-nav');
$hasMenu = !empty($menuItems);
$seoTitle = trim((string)($seo['title'] ?? $site['name'] ?? ''));
$seoDescription = default_theme_meta_description((string)($seo['description'] ?? $site['tagline'] ?? ''));
$canonicalUrl = (string)($seo['canonical'] ?? base_url());
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = base_path();
if ($basePath !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
}

$seoContext = [
    'item' => $item ?? null,
    'images' => $images ?? [],
    'price_tiers' => $price_tiers ?? [],
    'cover' => is_array($item ?? null) ? (string)($item['cover'] ?? '') : '',
];
if (str_starts_with($currentPath, '/product/') && !empty($seoContext['item'])) {
    $seoContext['type'] = 'product';
} elseif (str_starts_with($currentPath, '/blog/') && !empty($seoContext['item'])) {
    $seoContext['type'] = 'article';
} elseif (str_starts_with($currentPath, '/case/') && !empty($seoContext['item'])) {
    $seoContext['type'] = 'case';
} elseif (str_starts_with($currentPath, '/page/') && !empty($seoContext['item'])) {
    $seoContext['type'] = 'page';
} else {
    $seoContext['type'] = 'website';
}
$metaImage = default_theme_meta_image($site, $seo, $seoContext);
$ogType = match ($seoContext['type']) {
    'product' => 'product',
    'article', 'case', 'page' => 'article',
    default => 'website',
};
$schemaGraph = default_theme_schema_graph($site, [
    'title' => $seoTitle,
    'description' => $seoDescription,
    'canonical' => $canonicalUrl,
    'image' => $metaImage,
], $seoContext);
?>
<!DOCTYPE html>
<html lang="<?= h($lang ?? 'en') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= h($seoTitle) ?></title>
    <meta name="description" content="<?= h($seoDescription) ?>">
    <meta name="robots" content="<?= h((string)($seo['robots'] ?? 'index,follow')) ?>">
    <meta name="theme-color" content="<?= h(block('brand_colors', 'primary')) ?>">
    <?php if (!empty($seo['keywords'])): ?>
        <meta name="keywords" content="<?= h($seo['keywords']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= h($canonicalUrl) ?>">
    <meta property="og:title" content="<?= h($seoTitle) ?>">
    <meta property="og:description" content="<?= h($seoDescription) ?>">
    <meta property="og:type" content="<?= h($ogType) ?>">
    <meta property="og:url" content="<?= h($canonicalUrl) ?>">
    <meta property="og:site_name" content="<?= h($site['name'] ?? '') ?>">
    <meta property="og:locale" content="<?= h(str_replace('-', '_', (string)($lang ?? 'en'))) ?>">
    <?php if ($metaImage !== '' && !str_starts_with($metaImage, 'data:')): ?>
        <meta property="og:image" content="<?= h($metaImage) ?>">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="<?= h($metaImage) ?>">
    <?php else: ?>
        <meta name="twitter:card" content="summary">
    <?php endif; ?>
    <meta name="twitter:title" content="<?= h($seoTitle) ?>">
    <meta name="twitter:description" content="<?= h($seoDescription) ?>">
    <?php if (!empty($site['favicon'])): ?>
        <link rel="icon" type="image/x-icon" href="<?= h($site['favicon']) ?>">
        <link rel="shortcut icon" href="<?= h($site['favicon']) ?>">
    <?php endif; ?>
    <script type="application/ld+json"><?= json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <?php
        $brandPrimary     = block('brand_colors', 'primary');
        $brandPrimaryDark = block('brand_colors', 'primary_dark');
        $brandInk         = block('brand_colors', 'ink');
    ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '<?= h(default_theme_hex_adjust($brandPrimary, 0.94)) ?>',
                            100: '<?= h(default_theme_hex_adjust($brandPrimary, 0.88)) ?>',
                            200: '<?= h(default_theme_hex_adjust($brandPrimary, 0.73)) ?>',
                            300: '<?= h(default_theme_hex_adjust($brandPrimary, 0.49)) ?>',
                            400: '<?= h(default_theme_hex_adjust($brandPrimary, 0.22)) ?>',
                            500: '<?= h($brandPrimary) ?>',
                            600: '<?= h($brandPrimaryDark) ?>',
                            700: '<?= h(default_theme_hex_adjust($brandPrimaryDark, 0.15, false)) ?>',
                            800: '<?= h(default_theme_hex_adjust($brandPrimaryDark, 0.35, false)) ?>',
                            900: '<?= h($brandInk) ?>',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= get_stylesheet_directory_uri() ?>/style.css">
    <?= get_head_code() ?>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50" aria-label="Main navigation">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-20">
                <!-- Logo -->
                <a href="<?= url('/') ?>" class="flex min-w-0 items-center">
                    <?php if (!empty($site['logo'])): ?>
                        <img src="<?= asset_url(h($site['logo'])) ?>" alt="<?= h($site['name']) ?>" class="h-10 max-w-[180px] object-contain lg:h-14 lg:max-w-[240px]">
                    <?php else: ?>
                        <div class="min-w-0">
                            <div class="truncate text-lg font-bold text-gray-900 lg:text-xl"><?= h($site['name']) ?></div>
                            <div class="hidden truncate text-sm text-gray-500 sm:block"><?= h($site['tagline']) ?></div>
                        </div>
                    <?php endif; ?>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <?php render_menu('main-nav', false); ?>
                    <?= get_google_translate_widget($site, 'px-4 py-2 text-gray-700') ?>
                    <a href="<?= url(block('header', 'cta_url', '/contact')) ?>" class="ml-4 px-6 py-2.5 bg-brand-600 text-white font-medium rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
                        <?= h(block('header', 'cta_text')) ?>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" type="button" class="lg:hidden inline-flex h-11 w-11 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 hover:text-brand-600 focus:outline-none" aria-label="Open navigation menu" aria-controls="mobile-menu" aria-expanded="false">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100 bg-white max-h-[calc(100svh-4rem)] overflow-y-auto">
                <div class="py-4 space-y-2">
                    <?php render_menu('main-nav', true); ?>
                    <div class="px-4 pt-2">
                        <a href="<?= url(block('header', 'cta_url', '/contact')) ?>" class="block w-full text-center px-6 py-2.5 bg-brand-600 text-white font-medium rounded-lg hover:bg-brand-700 transition-colors">
                            <?= h(block('header', 'cta_text')) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            if (!button || !menu) return;

            button.addEventListener('click', function () {
                const isOpen = !menu.classList.contains('hidden');
                menu.classList.toggle('hidden', isOpen);
                button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                document.body.classList.toggle('overflow-hidden', !isOpen);
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    menu.classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        });
    </script>
    <main>
