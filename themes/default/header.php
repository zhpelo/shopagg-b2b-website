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
?>
<!DOCTYPE html>
<html lang="<?= h($lang ?? 'en') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($seo['title'] ?? $site['name']) ?></title>
    <meta name="description" content="<?= h($seo['description'] ?? $site['tagline']) ?>">
    <?php if (!empty($seo['keywords'])): ?>
        <meta name="keywords" content="<?= h($seo['keywords']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= h($seo['canonical']) ?>">
    <meta property="og:title" content="<?= h($seo['title'] ?? $site['name']) ?>">
    <meta property="og:description" content="<?= h($seo['description'] ?? $site['tagline']) ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($site['og_image'])): ?>
        <meta property="og:image" content="<?= h($site['og_image']) ?>">
    <?php endif; ?>
    <?php if (!empty($site['favicon'])): ?>
        <link rel="icon" type="image/x-icon" href="<?= h($site['favicon']) ?>">
        <link rel="shortcut icon" href="<?= h($site['favicon']) ?>">
    <?php endif; ?>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <?php
        $brandPrimary     = block('brand_colors', 'primary');
        $brandPrimaryDark = block('brand_colors', 'primary_dark');
        $brandInk         = block('brand_colors', 'ink');

        // Generate lighter shades from primary hex
        function _brand_lighten(string $hex, float $factor): string {
            $hex = ltrim($hex, '#');
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $r = (int) round($r + (255 - $r) * $factor);
            $g = (int) round($g + (255 - $g) * $factor);
            $b = (int) round($b + (255 - $b) * $factor);
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        }
        function _brand_darken(string $hex, float $factor): string {
            $hex = ltrim($hex, '#');
            $r = (int) round(hexdec(substr($hex, 0, 2)) * (1 - $factor));
            $g = (int) round(hexdec(substr($hex, 2, 2)) * (1 - $factor));
            $b = (int) round(hexdec(substr($hex, 4, 2)) * (1 - $factor));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        }
    ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '<?= h(_brand_lighten($brandPrimary, 0.94)) ?>',
                            100: '<?= h(_brand_lighten($brandPrimary, 0.88)) ?>',
                            200: '<?= h(_brand_lighten($brandPrimary, 0.73)) ?>',
                            300: '<?= h(_brand_lighten($brandPrimary, 0.49)) ?>',
                            400: '<?= h(_brand_lighten($brandPrimary, 0.22)) ?>',
                            500: '<?= h($brandPrimary) ?>',
                            600: '<?= h($brandPrimaryDark) ?>',
                            700: '<?= h(_brand_darken($brandPrimaryDark, 0.15)) ?>',
                            800: '<?= h(_brand_darken($brandPrimaryDark, 0.35)) ?>',
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
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="<?= url('/') ?>" class="flex items-center">
                    <?php if (!empty($site['logo'])): ?>
                        <img src="<?= asset_url(h($site['logo'])) ?>" alt="<?= h($site['name']) ?>" class="h-12 lg:h-14">
                    <?php else: ?>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900"><?= $site['name'] ?></h1>
                            <div class="text-sm text-gray-500"><?= $site['tagline'] ?></div>
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
                <button id="mobile-menu-btn" class="lg:hidden p-2 text-gray-700 hover:text-brand-600 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100">
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
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
    <main>
