<?php
declare(strict_types=1);

function h($s): string {
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text ?? '', '-');
    return $text === '' ? 'item' . time() : $text;
}


function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    $token = $_POST['csrf'] ?? '';
    if (!$token || $token !== ($_SESSION['csrf'] ?? '')) {
        http_response_code(400);
        echo 'Invalid CSRF token';
        exit;
    }
}

function base_path(): string {
    return defined('APP_BASE_PATH') ? (string) APP_BASE_PATH : '';
}

/** 生成带 base path 的 URL，用于二级目录部署 */
function url(string $path = ''): string {
    $base = base_path();
    if ($path === '' || $path === '/') {
        return $base ?: '/';
    }
    return $base . ($path[0] === '/' ? $path : '/' . $path);
}

function lang_switch_url(string $lang): string {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $query = $_GET;
    $query['lang'] = $lang;
    return $path . '?' . http_build_query($query);
}

function get_languages(): array {
    return ['en' => 'English', 'zh' => '中文'];
}

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = base_path();
    return $scheme . '://' . $host . $base;
}

function normalize_files_array(array $files): array {
    $normalized = [];
    $names = $files['name'] ?? [];
    if (!is_array($names)) return $normalized;
    $count = count($names);
    for ($i = 0; $i < $count; $i++) {
        if ($names[$i] === '') continue;
        $normalized[] = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i],
        ];
    }
    return $normalized;
}

function save_uploaded_image(array $file): array {
    if (!empty($file['error'])) return [false, '上传失败'];
    $maxSize = 5 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxSize) return [false, '图片过大，请小于 5MB'];
    $info = getimagesize($file['tmp_name']);
    if ($info === false) return [false, '非法图片'];
    $mime = $info['mime'] ?? '';
    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($extMap[$mime])) return [false, '不支持的图片格式'];
    $ext = $extMap[$mime];
    $subDir = '/uploads/' . date('Ym');
    $targetDir = APP_ROOT . $subDir;
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $filename = uniqid('img_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) return [false, '保存失败'];
    return [true, $subDir . '/' . $filename];
}

function format_date(?string $datetime, string $format = 'Y-m-d H:i:s'): string {
    if (empty($datetime)) return '';
    try {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    } catch (Exception $e) {
        return $datetime;
    }
}

function normalize_price_tiers(array $post): array {
    $mins = $post['price_min'] ?? [];
    $maxs = $post['price_max'] ?? [];
    $prices = $post['price_value'] ?? [];
    $currencies = $post['price_currency'] ?? [];
    if (!is_array($mins)) $mins = [$mins];
    if (!is_array($maxs)) $maxs = [$maxs];
    if (!is_array($prices)) $prices = [$prices];
    if (!is_array($currencies)) $currencies = [$currencies];
    $tiers = [];
    foreach ($mins as $idx => $minVal) {
        $min = (int)trim((string)$minVal);
        $priceRaw = trim((string)($prices[$idx] ?? ''));
        if ($min <= 0 || $priceRaw === '') continue;
        $maxRaw = trim((string)($maxs[$idx] ?? ''));
        $tiers[] = [
            'min_qty' => $min,
            'max_qty' => $maxRaw === '' ? null : (int)$maxRaw,
            'price' => (float)$priceRaw,
            'currency' => trim((string)($currencies[$idx] ?? 'USD')) ?: 'USD',
        ];
    }
    return $tiers;
}

/** 生成带 base path 的资源 URL，用于二级目录部署，支持外部 URL */
function asset_url(string $path): string {
    if (empty($path)) return '';
    if (strpos($path, 'http') === 0 || strpos($path, '//') === 0) return $path;
    return url($path);
}

/** 处理富文本内容中的图片URL，使其兼容子目录部署 */
function process_rich_text(string $html): string {
    return preg_replace_callback('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', function($matches) {
        $src = $matches[1];
        $newSrc = asset_url($src);
        return str_replace('src="' . $src . '"', 'src="' . $newSrc . '"', $matches[0]);
    }, $html);
}

/** 规范化富文本内容中的图片URL，移除 base path 前缀，用于保存到数据库 */
function normalize_rich_text(string $html): string {
    $basePath = defined('APP_BASE_PATH') ? (string) APP_BASE_PATH : '';
    if ($basePath === '') return $html;
    return preg_replace_callback('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', function($matches) use ($basePath) {
        $src = $matches[1];
        if (strpos($src, $basePath) === 0) {
            $newSrc = substr($src, strlen($basePath));
            return str_replace('src="' . $src . '"', 'src="' . $newSrc . '"', $matches[0]);
        }
        return $matches[0];
    }, $html);
}

// --- Theme Helper Functions ---

/**
 * 获取当前活动主题的服务器目录路径
 * 例如: /var/www/html/themes/default/
 */
function get_stylesheet_directory(): string {
    static $theme = null;
    if ($theme === null) {
        $settingModel = new \App\Models\Setting();
        $theme = $settingModel->get('theme', 'default');
    }
    return APP_ROOT . '/themes/' . $theme;
}

/**
 * 获取当前活动主题的 URL 地址
 * 例如: https://yoursite.com/themes/default
 */
function get_stylesheet_directory_uri(): string {
    static $themeURI = null;
    if ($themeURI === null) {
        $settingModel = new \App\Models\Setting();
        $theme = $settingModel->get('theme', 'default');
        $themeURI = base_url() . '/themes/' . $theme;
    }
    return $themeURI;
}

/**
 * 获取产品列表
 *
 * @param array $args 参数支持: limit, category (id), active_only, orderby, order
 * @return array
 */
function get_products(array $args = []): array {
    $limit = isset($args['limit']) ? (int)$args['limit'] : 10;
    $categoryId = isset($args['category']) ? (int)$args['category'] : 0;
    $activeOnly = isset($args['status']) ? ($args['status'] === 'active') : true;

    $productModel = new \App\Models\Product();
    
    if ($categoryId > 0) {
        $items = $productModel->getByCategory($categoryId, $limit);
    } elseif (!empty($args['featured'])) {
        // 如果需要更复杂的筛选（如 featured），这里可以扩展 Model 或直接调用 Model 的特定方法
        $items = $productModel->getFeatured($limit);
    } else {
        // Default to latest
        $items = $productModel->getList($limit, $activeOnly);
    }

    foreach ($items as &$item) {
        $item['url'] = url('/product/' . ($item['slug'] ?? $item['id']));
    }
    
    return $items;
}

/**
 * 获取文章列表
 *
 * @param array $args 参数支持: limit, category (id), type (post_type)
 * @return array
 */
function get_posts(array $args = []): array {
    $limit = isset($args['limit']) ? (int)$args['limit'] : 5;
    $categoryId = isset($args['category']) ? (int)$args['category'] : 0;
    $activeOnly = isset($args['status']) ? ($args['status'] === 'active') : true;

    $postModel = new \App\Models\PostModel();

    if ($categoryId > 0) {
        $items = $postModel->getByCategory($categoryId, $limit);
    } else {
        // 假设 PostModel 有一个类似 getLatest 的方法，或者复用 getList
        $items = $postModel->getList($limit, $activeOnly);
    }

    foreach ($items as &$item) {
        $item['url'] = url('/blog/' . ($item['slug'] ?? $item['id']));
    }

    return $items;
}

/**
 * 获取案例列表
 *
 * @param array $args 参数支持: limit
 * @return array
 */
function get_cases(array $args = []): array {
    $limit = isset($args['limit']) ? (int)$args['limit'] : 6;
    
    $caseModel = new \App\Models\CaseModel();
    $items = $caseModel->getList($limit);

    foreach ($items as &$item) {
        $item['url'] = url('/case/' . ($item['slug'] ?? $item['id']));
    }

    return $items;
}

/**
 * 获取产品分类列表 (Tree 结构)
 * @return array
 */
function get_product_categories(): array {
    $categoryModel = new \App\Models\Category();
    return $categoryModel->getTree('product');
}

/**
 * 获取文章分类列表 (Tree 结构)
 * @return array
 */
function get_post_categories(): array {
    $categoryModel = new \App\Models\Category();
    return $categoryModel->getTree('post');
}
