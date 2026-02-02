<?php
declare(strict_types=1);

/**
 * 默认主题 - 主题内辅助函数
 * 此文件在每次渲染主题时自动加载，可以在此定义可重用的模板函数
 * 可以访问数据库模型和助手函数
 */

// 示例：获取最新产品
if (!function_exists('get_latest_products')) {
    function get_latest_products(int $limit = 6): array {
        $productModel = new \App\Models\Product();
        return $productModel->getLatest($limit);
    }
}

// 示例：获取特色产品（带横幅图片的）
if (!function_exists('get_featured_products')) {
    function get_featured_products(int $limit = 6): array {
        $productModel = new \App\Models\Product();
        return $productModel->getFeatured($limit);
    }
}

// 示例：获取最新文章
if (!function_exists('get_latest_posts')) {
    function get_latest_posts(int $limit = 5): array {
        $postModel = new \App\Models\PostModel();
        return $postModel->getLatest($limit);
    }
}

// 示例：获取产品分类
if (!function_exists('get_product_categories')) {
    function get_product_categories(): array {
        $categoryModel = new \App\Models\Category();
        return $categoryModel->getProductCategories();
    }
}

// 示例：获取文章分类
if (!function_exists('get_post_categories')) {
    function get_post_categories(): array {
        $categoryModel = new \App\Models\Category();
        return $categoryModel->getPostCategories();
    }
}

// 示例：渲染产品卡片
if (!function_exists('render_product_card')) {
    function render_product_card(array $product): void {
        $image = !empty($product['banner_image']) ? $product['banner_image'] : $product['image'];
        ?>
        <div class="column is-4">
            <div class="card">
                <div class="card-image">
                    <figure class="image is-4by3">
                        <img src="<?= asset_url(h($image)) ?>" alt="<?= h($product['name']) ?>">
                    </figure>
                </div>
                <div class="card-content">
                    <p class="title is-4"><?= h($product['name']) ?></p>
                    <p class="subtitle is-6"><?= h($product['description']) ?></p>
                </div>
                <footer class="card-footer">
                    <a href="<?= url('/product/' . $product['id']) ?>" class="card-footer-item">查看详情</a>
                </footer>
            </div>
        </div>
        <?php
    }
}

// 示例：渲染文章卡片
if (!function_exists('render_post_card')) {
    function render_post_card(array $post): void {
        ?>
        <div class="column is-4">
            <div class="card">
                <div class="card-content">
                    <p class="title is-4"><a href="<?= url('/post/' . $post['id']) ?>"><?= h($post['title']) ?></a></p>
                    <p class="subtitle is-6">
                        <time datetime="<?= h($post['created_at']) ?>"><?= date('Y-m-d', strtotime($post['created_at'])) ?></time>
                    </p>
                    <div class="content">
                        <?= process_rich_text($post['content'], 150) ?>
                    </div>
                </div>
                <footer class="card-footer">
                    <a href="<?= url('/post/' . $post['id']) ?>" class="card-footer-item">阅读更多</a>
                </footer>
            </div>
        </div>
        <?php
    }
}