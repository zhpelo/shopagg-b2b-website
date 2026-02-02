# 主题系统重构说明

## 概述

本次重构将前端主题系统设计为更模块化和可扩展的架构，每个主题目录现在需要包含 `functions.php` 和 `style.css` 文件，这些文件会被自动加载。

## 主题结构要求

每个主题目录必须包含以下文件：

```
themes/
├── your_theme/
│   ├── functions.php    # 必需：主题辅助函数和数据库访问
│   ├── style.css        # 必需：主题样式文件
│   ├── header.php       # 必需：页面头部
│   ├── footer.php       # 必需：页面底部
│   └── ...              # 其他模板文件
```

## 自动加载机制

### functions.php
- 在每次渲染主题时自动加载
- 可以定义可重用的模板函数
- 可以访问所有数据库模型和助手函数
- 函数命名建议使用主题前缀避免冲突

### style.css
- 自动加载到页面头部
- 支持主题特定的样式覆盖
- 路径通过 `$currentTheme` 变量动态生成

## 数据库访问示例

在 `functions.php` 中可以这样访问数据库：

```php
// 获取最新产品
function get_latest_products(int $limit = 6): array {
    $productModel = new \App\Models\Product();
    return $productModel->getLatest($limit);
}

// 获取特色产品
function get_featured_products(int $limit = 6): array {
    $productModel = new \App\Models\Product();
    return $productModel->getFeatured($limit);
}

// 获取文章分类
function get_post_categories(): array {
    $categoryModel = new \App\Models\Category();
    return $categoryModel->getPostCategories();
}
```

## 模板函数示例

```php
// 渲染产品卡片
function render_product_card(array $product): void {
    $image = !empty($product['banner_image']) ? $product['banner_image'] : $product['image'];
    ?>
    <div class="card">
        <img src="<?= asset_url(h($image)) ?>" alt="<?= h($product['name']) ?>">
        <h3><a href="<?= url('/product/' . $product['id']) ?>"><?= h($product['name']) ?></a></h3>
    </div>
    <?php
}
```

## 可用资源

在主题函数中可以访问：

- **数据库模型**: `\App\Models\*` (Product, PostModel, Category, CaseModel 等)
- **助手函数**: `url()`, `asset_url()`, `h()`, `process_rich_text()` 等
- **全局变量**: `$currentTheme` (当前主题名称)

## 性能优化

### 数据库查询优化
- **直接数据库过滤**: 在数据库层面过滤数据，而不是在 PHP 中使用 `array_filter`
- **限制查询数量**: 使用 `LIMIT` 子句避免查询过多数据
- **索引优化**: 确保相关字段有适当的数据库索引

### 缓存策略
- **静态缓存**: 在函数中使用 `static` 变量缓存单次请求内的重复查询
- **数据预处理**: 在数据库查询时就格式化数据结构，避免在模板中重复处理

### 示例：优化的轮播产品获取
```php
function get_carousel_products(int $limit = 3): array {
    static $cache = null; // 单次请求缓存

    if ($cache !== null) {
        return $cache;
    }

    $productModel = new \App\Models\Product();

    // 1. 优先获取有横幅图片的产品
    $featuredProducts = $productModel->getFeatured($limit);

    // 2. 补充最新产品（避免重复）
    if (count($featuredProducts) < $limit) {
        $remaining = $limit - count($featuredProducts);
        $excludeIds = array_column($featuredProducts, 'id');

        $latestProducts = $productModel->getLatest($remaining + 10);
        foreach ($latestProducts as $product) {
            if (count($featuredProducts) >= $limit) break;
            if (!in_array($product['id'], $excludeIds)) {
                $featuredProducts[] = $product;
            }
        }
    }

    // 3. 格式化数据结构
    $carouselProducts = [];
    foreach ($featuredProducts as $product) {
        $carouselProducts[] = [
            'id' => $product['id'],
            'title' => $product['title'],
            'summary' => $product['summary'],
            'banner_image' => $product['banner_image'],
            'url' => url('/product/' . $product['id']),
            'image' => $product['cover']
        ];
    }

    $cache = $carouselProducts;
    return $carouselProducts;
}
```

### 最佳实践
1. **避免 N+1 查询**: 使用 JOIN 而不是循环查询
2. **分页加载**: 对于大数据集，使用分页而不是一次性加载所有数据
3. **延迟加载**: 只在需要时加载数据
4. **缓存层**: 考虑添加 Redis 或 Memcached 缓存层
5. **查询优化**: 使用 EXPLAIN 分析查询性能