<?php
declare(strict_types=1);

/**
 * 默认主题 - 主题内辅助函数
 * 此文件在每次渲染主题时自动加载，可以在此定义可重用的模板函数
 * 可以访问数据库模型和助手函数
 * 
 * 核心功能函数现在通过 app/Helpers.php 统一提供，包括:
 * - get_products()
 * - get_posts()
 * - get_cases()
 * - get_product_categories()
 * - get_post_categories()
 * - get_stylesheet_directory()
 * - get_stylesheet_directory_uri()
 */

// 获取轮播产品（性能优化版）
// 优先获取有横幅图片的产品，不足时补充最新产品
if (!function_exists('get_carousel_products')) {
    function get_carousel_products(int $limit = 3): array {
        static $cache = null; // 简单静态缓存，避免重复查询

        if ($cache !== null) {
            return $cache;
        }

        $featuredProducts = get_products(['limit' => $limit, 'featured' => true]);

        // 2. 如果不够数量，补充最新产品（排除已有横幅图片的产品）
        if (count($featuredProducts) < $limit) {
            $remaining = $limit - count($featuredProducts);
            $excludeIds = array_column($featuredProducts, 'id');

            $latestProducts = get_products(['limit' => $remaining + 10]); // 多取一些用于过滤

            foreach ($latestProducts as $product) {
                if (count($featuredProducts) >= $limit) break;
                if (!in_array($product['id'], $excludeIds)) {
                    $featuredProducts[] = $product;
                }
            }
        }

        // 3. 确保不超过限制数量
        $featuredProducts = array_slice($featuredProducts, 0, $limit);

        // 4. 格式化数据为轮播所需结构
        $carouselProducts = [];
        foreach ($featuredProducts as $product) {
            $carouselProducts[] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'summary' => $product['summary'],
                'banner_image' => $product['banner_image'],
                'url' => url('/product/' . $product['slug']),
                'image' => $product['cover'] // 备用图片
            ];
        }

        $cache = $carouselProducts;
        return $carouselProducts;
    }
}

// 渲染产品卡片 - Tailwind 版本
if (!function_exists('render_product_card')) {
    function render_product_card(array $product): void {
        $image = !empty($product['banner_image']) ? $product['banner_image'] : $product['image'];
        ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow flex flex-col h-full">
            <a href="<?= url('/product/' . $product['slug']) ?>" class="block aspect-square overflow-hidden bg-gray-100">
                <img src="<?= asset_url(h($image)) ?>" 
                     alt="<?= h($product['name']) ?>" 
                     class="w-full h-full object-cover hover:scale-105 transition-transform">
            </a>
            <div class="p-5 flex-grow flex flex-col">
                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    <a href="<?= url('/product/' . $product['slug']) ?>" class="hover:text-brand-600 transition-colors">
                        <?= h($product['name']) ?>
                    </a>
                </h3>
                <p class="text-gray-600 text-sm flex-grow line-clamp-3"><?= h($product['description']) ?></p>
                <a href="<?= url('/product/' . $product['slug']) ?>" 
                   class="mt-4 block w-full text-center px-4 py-2 border border-brand-600 text-brand-600 font-medium rounded-lg hover:bg-brand-600 hover:text-white transition-colors">
                    查看详情
                </a>
            </div>
        </div>
        <?php
    }
}

// 渲染文章卡片 - Tailwind 版本
if (!function_exists('render_post_card')) {
    function render_post_card(array $post): void {
        ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-5">
                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    <a href="<?= url('/post/' . $post['id']) ?>" class="hover:text-brand-600 transition-colors">
                        <?= h($post['title']) ?>
                    </a>
                </h3>
                <p class="text-sm text-gray-500 mb-3">
                    <i class="far fa-calendar-alt mr-1"></i>
                    <?= date('Y-m-d', strtotime($post['created_at'])) ?>
                </p>
                <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                    <?= process_rich_text($post['content'], 150) ?>
                </p>
                <a href="<?= url('/post/' . $post['id']) ?>" 
                   class="inline-flex items-center text-brand-600 font-medium hover:text-brand-700 transition-colors">
                    阅读更多 →
                </a>
            </div>
        </div>
        <?php
    }
}
