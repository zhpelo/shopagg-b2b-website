<?php
declare(strict_types=1);

/**
 * 默认主题 - 主题内辅助函数
 * 此文件在每次渲染主题时自动加载，可以在此定义可重用的模板函数
 * 可以访问数据库模型和助手函数
 * 
 * 核心功能函数现在通过 app/Helpers/Helpers.php 统一提供，包括:
 * - get_products()
 * - get_posts()
 * - get_cases()
 * - get_product_categories()
 * - get_post_categories()
 * - get_stylesheet_directory()
 * - get_stylesheet_directory_uri()
 */

/**
 * 获取占位图片URL
 * @param int $width 图片宽度
 * @param int $height 图片高度
 * @param string $text 显示文字
 * @return string 占位图片URL
 */
if (!function_exists('placeholder_url')) {
    function placeholder_url(int $width = 800, int $height = 300, string $text = ''): string {
        $url = "https://devtool.tech/api/placeholder/{$width}/{$height}";
        $params = [];
        if ($text) {
            $params['text'] = urlencode($text);
        }
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }
}

/**
 * 获取图片URL（优先使用实际图片，否则返回占位图）
 * @param string|null $image 实际图片路径
 * @param int $width 占位图宽度
 * @param int $height 占位图高度
 * @param string $text 占位图文字
 * @return string 图片URL
 */
if (!function_exists('get_image_url')) {
    function get_image_url(?string $image, int $width = 800, int $height = 300, string $text = ''): string {
        if (!empty($image)) {
            return asset_url($image);
        }
        return placeholder_url($width, $height, $text);
    }
}

// 获取轮播产品（性能优化版）
// 优先获取有横幅图片的产品，不足时补充最新产品
if (!function_exists('get_carousel_products')) {
    function get_carousel_products(int $limit = 3): array {
        static $cache = null; // 简单静态缓存，避免重复查询

        if ($cache !== null) {
            return $cache;
        }

        // 首先尝试从新的轮播图系统获取
        $sliderItems = get_slider_items('home-hero');
        if (!empty($sliderItems)) {
            $cache = $sliderItems;
            return $sliderItems;
        }

        // 回退到旧的产品轮播逻辑
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

/**
 * 获取轮播图项目（新版轮播图系统）
 * @param string $sliderSlug 轮播图标识符
 * @return array 轮播图片数组
 */
if (!function_exists('get_slider_items')) {
    function get_slider_items(string $sliderSlug): array {
        try {
            $sliderModel = new \App\Models\Slider();
            $slider = $sliderModel->getBySlugWithItems($sliderSlug);
            
            if (!$slider || $slider['status'] !== 'active') {
                return [];
            }
            
            $items = [];
            foreach ($slider['items'] as $item) {
                $items[] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'],
                    'banner_image' => $item['image'],
                    'url' => $item['link_url'] ?: '#',
                    'link_text' => $item['link_text'] ?: 'View Details',
                    'image' => $item['image'],
                ];
            }
            
            return $items;
        } catch (\Exception $e) {
            // 如果发生错误，返回空数组
            return [];
        }
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

/**
 * 获取菜单（新版菜单系统）
 * @param string $menuSlug 菜单标识符
 * @return array 菜单项数组
 */
if (!function_exists('get_menu_items')) {
    function get_menu_items(string $menuSlug = 'main-nav'): array {
        static $cache = [];
        
        if (isset($cache[$menuSlug])) {
            return $cache[$menuSlug];
        }
        
        try {
            $menuModel = new \App\Models\Menu();
            $menu = $menuModel->getBySlugWithItems($menuSlug, true);
            
            if (!$menu || $menu['status'] !== 'active') {
                $cache[$menuSlug] = [];
                return [];
            }
            
            $items = $menu['items'] ?? [];
            
            // 格式化菜单项
            $formatItems = function($items) use (&$formatItems) {
                $result = [];
                foreach ($items as $item) {
                    $formatted = [
                        'id' => $item['id'],
                        'title' => $item['title'],
                        'url' => $item['url'],
                        'target' => $item['target'] ?? '_self',
                        'css_class' => $item['css_class'] ?? '',
                        'children' => []
                    ];
                    
                    if (!empty($item['children'])) {
                        $formatted['children'] = $formatItems($item['children']);
                    }
                    
                    $result[] = $formatted;
                }
                return $result;
            };
            
            $formattedItems = $formatItems($items);
            $cache[$menuSlug] = $formattedItems;
            return $formattedItems;
            
        } catch (\Exception $e) {
            $cache[$menuSlug] = [];
            return [];
        }
    }
}

/**
 * 渲染菜单项
 * @param array $item 菜单项
 * @param bool $isMobile 是否为移动端
 * @return void
 */
if (!function_exists('render_menu_item')) {
    function render_menu_item(array $item, bool $isMobile = false): void {
        $hasChildren = !empty($item['children']);
        $baseClass = $isMobile 
            ? 'block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-brand-600 font-medium rounded-lg transition-colors'
            : 'px-4 py-2 text-gray-700 hover:text-brand-600 font-medium transition-colors';
        
        if (!empty($item['css_class'])) {
            $baseClass .= ' ' . $item['css_class'];
        }
        
        $target = $item['target'] ?? '_self';
        $targetAttr = $target === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
        
        // 处理 URL
        $url = $item['url'];
        if (!str_starts_with($url, 'http') && !str_starts_with($url, '#') && !str_starts_with($url, 'mailto:')) {
            $url = url($url);
        }
        
        if ($hasChildren && !$isMobile) {
            // 桌面端下拉菜单
            ?>
            <div class="relative group">
                <button type="button" class="<?= $baseClass ?> inline-flex items-center gap-1">
                    <?= h($item['title']) ?>
                    <i class="fas fa-chevron-down text-xs transition-transform group-hover:rotate-180"></i>
                </button>
                <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <div class="py-2">
                        <?php foreach ($item['children'] as $child): ?>
                            <?php 
                            $childUrl = $child['url'];
                            if (!str_starts_with($childUrl, 'http') && !str_starts_with($childUrl, '#') && !str_starts_with($childUrl, 'mailto:')) {
                                $childUrl = url($childUrl);
                            }
                            $childTarget = $child['target'] ?? '_self';
                            $childTargetAttr = $childTarget === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                            ?>
                            <a href="<?= $childUrl ?>"<?= $childTargetAttr ?> class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-600">
                                <?= h($child['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php
        } elseif ($hasChildren && $isMobile) {
            // 移动端子菜单
            ?>
            <div>
                <div class="px-4 py-2 text-gray-700 font-medium"><?= h($item['title']) ?></div>
                <div class="pl-4 space-y-1">
                    <?php foreach ($item['children'] as $child): ?>
                        <?php 
                        $childUrl = $child['url'];
                        if (!str_starts_with($childUrl, 'http') && !str_starts_with($childUrl, '#') && !str_starts_with($childUrl, 'mailto:')) {
                            $childUrl = url($childUrl);
                        }
                        $childTarget = $child['target'] ?? '_self';
                        $childTargetAttr = $childTarget === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                        ?>
                        <a href="<?= $childUrl ?>"<?= $childTargetAttr ?> class="block px-4 py-2 text-gray-600 hover:bg-gray-50 hover:text-brand-600 font-medium rounded-lg">
                            <?= h($child['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        } else {
            // 普通菜单项
            ?>
            <a href="<?= $url ?>"<?= $targetAttr ?> class="<?= $baseClass ?>">
                <?= h($item['title']) ?>
            </a>
            <?php
        }
    }
}

/**
 * 渲染完整菜单
 * @param string $menuSlug 菜单标识符
 * @param bool $isMobile 是否为移动端
 * @return void
 */
if (!function_exists('render_menu')) {
    function render_menu(string $menuSlug = 'main-nav', bool $isMobile = false): void {
        $items = get_menu_items($menuSlug);
        
        if (empty($items)) {
            // 使用默认菜单
            $defaultItems = [
                ['title' => 'Home', 'url' => '/', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'Products', 'url' => '/products', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'Cases', 'url' => '/cases', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'Blog', 'url' => '/blog', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'Contact', 'url' => '/contact', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'About Us', 'url' => '/about', 'target' => '_self', 'css_class' => '', 'children' => []],
            ];
            $items = $defaultItems;
        }
        
        foreach ($items as $item) {
            render_menu_item($item, $isMobile);
        }
    }
}
