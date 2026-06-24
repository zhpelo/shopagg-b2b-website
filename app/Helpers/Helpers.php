<?php
declare(strict_types=1);

/**
 * 全局辅助函数库
 * 
 * 包含系统运行所需的通用函数，按功能分类组织：
 * - 安全函数（HTML转义、CSRF 防护）
 * - URL 和路由函数（路径处理、二级目录支持）
 * - 文件上传处理
 * - 数据格式化函数
 * - 主题和显示相关函数
 * - 数据查询辅助函数
 */

// ============================================================================
// 安全函数 - HTML 转义、输入验证、CSRF 防护
// ============================================================================

/**
 * HTML 特殊字符转义
 * 
 * 防止 XSS 攻击，用于输出用户提交的任何内容
 * 
 * @param mixed $s 待转义的值
 * @return string 转义后的 HTML 安全字符串
 */
function h($s): string {
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * 过滤并规范化用户输入的 slug
 * 
 * 仅保留小写字母、数字和连字符，将其它字符统一转换为连字符。
 * 示例：'Hello World_01!' => 'hello-world-01'
 * 
 * @param string $text 原始文本
 * @return string 规范化后的 slug，可能为空字符串
 */
function sanitize_slug_input(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]+/', '-', $text) ?? '';
    $text = preg_replace('/-+/', '-', $text) ?? '';
    return trim($text, '-');
}

/**
 * 验证 slug 是否符合系统要求
 * 
 * 仅允许小写字母、数字和中间连字符，不允许首尾连字符。
 * 
 * @param string $slug 待验证的 slug
 * @return bool true 表示合法
 */
function is_valid_slug(string $slug): bool {
    return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
}

/**
 * 生成 URL 友好的 slug
 * 
 * 将文本转换为合法的 URL 片段，移除特殊字符并转换为小写。
 * 若无法生成，则使用 item + 时间戳兜底。
 * 
 * @param string $text 原始文本
 * @return string URL 友好的文本
 */
function slugify(string $text): string {
    $slug = sanitize_slug_input($text);
    return $slug !== '' ? $slug : 'item' . time();
}

/**
 * 获取或生成 CSRF 令牌
 * 
 * 跨站请求伪造（CSRF）防护令牌，在表单中必须包含此令牌
 * 
 * @return string CSRF 令牌
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

/**
 * 验证 CSRF 令牌
 * 
 * 在处理 POST 请求前调用此函数检查令牌有效性
 * 若令牌无效则终止请求并返回 400 错误
 * 
 * @return void
 */
function csrf_check(): void {
    $token = $_POST['csrf']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? $_SERVER['HTTP_X_CSRF']
        ?? '';
    if (!$token || $token !== ($_SESSION['csrf'] ?? '')) {
        http_response_code(400);
        echo 'Invalid CSRF token';
        exit;
    }
}

// ============================================================================
// URL 和路由函数 - 路径处理、二级目录支持
// ============================================================================

/**
 * 获取应用 base path（用于二级目录部署）
 * 
 * 返回应用部署的子目录路径，如果应用在根目录部署则返回空字符串
 * 示例：应用部署在 /app/myproject/ 则返回 '/app/myproject'
 * 
 * @return string Base path（不含尾部斜杠）
 */
function base_path(): string {
    return defined('APP_BASE_PATH') ? (string) APP_BASE_PATH : '';
}

/**
 * 生成应用内 URL 路径
 * 
 * 自动添加 base path，用于路由重定向和链接生成
 * 示例：url('/admin') 在二级目录下返回 '/app/myproject/admin'
 * 
 * @param string $path 路由路径（以 / 开头）
 * @return string 完整 URL 路径
 */
function url(string $path = ''): string {
    $base = base_path();
    if ($path === '' || $path === '/') {
        return $base ?: '/';
    }
    return $base . ($path[0] === '/' ? $path : '/' . $path);
}

/**
 * 生成前台产品分类 URL
 */
function product_category_url(array $category): string {
    $slug = trim((string)($category['slug'] ?? $category['category_slug'] ?? ''));
    if ($slug !== '') {
        return url('/product-category/' . rawurlencode($slug));
    }

    return url('/products') . '?category=' . (int)($category['id'] ?? $category['category_id'] ?? 0);
}

/**
 * 获取完整的应用 URL（包含协议和域名）
 * 
 * 自动检测 HTTP/HTTPS 协议，用于生成绝对 URL
 * 示例：返回 https://example.com/app/myproject
 * 
 * @return string 完整应用 URL
 */
function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = base_path();
    return $scheme . '://' . $host . $base;
}

/**
 * 生成资源 URL（支持外部 URL）
 * 
 * 自动添加 base path，但保留 HTTP/HTTPS URL 的原样
 * 
 * @param string $path 资源路径
 * @return string 完整资源 URL
 */
function asset_url(string $path): string {
    if (empty($path)) return '';
    if (strpos($path, 'http') === 0 || strpos($path, '//') === 0) return $path;
    return url($path);
}


// ============================================================================
// 文件上传处理
// ============================================================================

/**
 * 规范化 $_FILES 数组格式
 * 
 * PHP 的 $_FILES 单个/多个文件上传时格式不一致，此函数统一为：
 * [
 *   ['name' => '...', 'type' => '...', 'tmp_name' => '...', 'error' => ..., 'size' => ...],
 *   ...
 * ]
 * 
 * @param array $files $_FILES 数组（如 $_FILES['images']）
 * @return array 规范化后的文件列表
 */
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

/**
 * 保存上传的图片文件
 * 
 * 执行以下验证和处理：
 * - 检查上传错误
 * - 验证文件大小（最大 5MB）
 * - 验证文件是否为有效图片（MIME 类型）
 * - 验证图片格式（JPEG、PNG、GIF、WebP）
 * - 保存到 /uploads/YYYYMM/ 目录
 * 
 * @param array $file 单个文件数组（来自 $_FILES）
 * @return array [success: bool, message: string 或 path: string]
 *   成功: [true, '/uploads/202501/img_xxx.jpg']
 *   失败: [false, '错误信息']
 */
function save_uploaded_image(array $file): array {
    if (!empty($file['error'])) {
        return [false, '上传失败'];
    }
    
    $maxSize = 5 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxSize) {
        return [false, '图片过大，请小于 5MB'];
    }
    
    $info = getimagesize($file['tmp_name']);
    if ($info === false) {
        return [false, '非法图片'];
    }
    
    // 验证 MIME 类型
    $mime = $info['mime'] ?? '';
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    
    if (!isset($extMap[$mime])) {
        return [false, '不支持的图片格式'];
    }
    
    $ext = $extMap[$mime];
    $subDir = '/uploads/' . date('Ym');
    $targetDir = APP_ROOT . $subDir;
    
    // 创建目录
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // 移动文件
    $filename = uniqid('img_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) {
        return [false, '保存失败'];
    }
    
    return [true, $subDir . '/' . $filename];
}

// ============================================================================
// 数据格式化和转换
// ============================================================================

/**
 * 格式化日期时间
 * 
 * 安全的日期格式化，处理无效日期和 NULL 值
 * 
 * @param string|null $datetime 日期时间字符串（支持 MySQL 格式）
 * @param string $format 输出格式（PHP date() 格式）
 * @return string 格式化后的日期，或空字符串
 */
function format_date(?string $datetime, string $format = 'Y-m-d H:i:s'): string {
    if (empty($datetime)) return '';
    try {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * 规范化阶梯价格数据
 * 
 * 将表单提交的多行阶梯价格数据转换为结构化数组
 * 
 * @param array $post $_POST 数据（包含 price_min[]、price_max[]、price_value[]、price_currency[]）
 * @return array 阶梯价格数组，每个元素包含 min_qty、max_qty、price、currency
 */
function normalize_price_tiers(array $post): array {
    $mins = $post['price_min'] ?? [];
    $maxs = $post['price_max'] ?? [];
    $prices = $post['price_value'] ?? [];
    $currencies = $post['price_currency'] ?? [];
    
    // 确保都是数组
    if (!is_array($mins)) $mins = [$mins];
    if (!is_array($maxs)) $maxs = [$maxs];
    if (!is_array($prices)) $prices = [$prices];
    if (!is_array($currencies)) $currencies = [$currencies];
    
    $tiers = [];
    foreach ($mins as $idx => $minVal) {
        $min = (int)trim((string)$minVal);
        $priceRaw = trim((string)($prices[$idx] ?? ''));
        
        // 跳过无效行（最小数量为 0 或价格为空）
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

// ============================================================================
// 富文本内容处理（支持二级目录部署）
// ============================================================================

/**
 * 处理富文本中的图片 URL 以适应二级目录部署
 * 
 * 在输出富文本内容时调用，确保图片 URL 包含 base path
 * 
 * @param string $html HTML 富文本内容
 * @return string 处理后的 HTML，所有图片 src 添加了 base path
 */
function process_rich_text(string $html): string {
    return preg_replace_callback('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', function($matches) {
        $src = $matches[1];
        $newSrc = asset_url($src);
        return str_replace('src="' . $src . '"', 'src="' . $newSrc . '"', $matches[0]);
    }, $html);
}

/**
 * 规范化富文本中的图片 URL 以便存储到数据库
 * 
 * 在保存富文本内容时调用，移除 base path 前缀（便于跨设备迁移）
 * 
 * @param string $html HTML 富文本内容
 * @return string 规范化后的 HTML，移除了 base path 前缀
 */
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

// ============================================================================
// 主题和显示相关函数
// ============================================================================

/**
 * 获取当前活动主题的服务器目录路径
 * 
 * @return string 主题目录的完整服务器路径，如 /var/www/html/themes/default/
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
 * 
 * @return string 主题目录的完整 URL，如 https://example.com/themes/default
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

function get_products_by_ids(array|string $ids, bool $activeOnly = true): array {
    if (is_string($ids)) {
        $ids = preg_split('/[,\s]+/', $ids) ?: [];
    }

    $normalized = [];
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id > 0 && !in_array($id, $normalized, true)) {
            $normalized[] = $id;
        }
    }

    if (empty($normalized)) {
        return [];
    }

    $productModel = new \App\Models\Product();
    $items = $productModel->getByIds($normalized, $activeOnly);

    foreach ($items as &$item) {
        $item['url'] = url('/product/' . ($item['slug'] ?? $item['id']));
    }
    unset($item);

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
    $type = isset($args['type']) ? (string)$args['type'] : 'post';

    $postModel = new \App\Models\PostModel();

    if ($categoryId > 0) {
        $items = $postModel->getByCategory($categoryId, $limit, $type);
    } else {
        $items = $postModel->getList($limit, $activeOnly, $type);
    }

    foreach ($items as &$item) {
        $prefix = match ($type) {
            'case' => '/case/',
            'page' => '/page/',
            default => '/blog/',
        };
        $item['url'] = url($prefix . ($item['slug'] ?? $item['id']));
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
    
    $postModel = new \App\Models\PostModel();
    $items = $postModel->getList($limit, true, 'case');

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

/**
 * 谷歌翻译系统功能：返回 Google Translate 支持的完整目标语种目录。
 */
function get_google_translate_supported_languages(): array {
    static $languages = null;

    if ($languages !== null) {
        return $languages;
    }

    $languages = [
        'ab' => ['label' => 'Abkhaz', 'admin_label' => '阿布哈兹语'],
        'ace' => ['label' => 'Acehnese', 'admin_label' => '亚齐语'],
        'ach' => ['label' => 'Acholi', 'admin_label' => '阿乔利语'],
        'aa' => ['label' => 'Afar', 'admin_label' => '阿法尔语'],
        'af' => ['label' => 'Afrikaans', 'admin_label' => '南非荷兰语'],
        'sq' => ['label' => 'Albanian', 'admin_label' => '阿尔巴尼亚语'],
        'alz' => ['label' => 'Alur', 'admin_label' => '阿卢尔语'],
        'am' => ['label' => 'Amharic', 'admin_label' => '阿姆哈拉语'],
        'ar' => ['label' => 'Arabic', 'admin_label' => '阿拉伯语'],
        'hy' => ['label' => 'Armenian', 'admin_label' => '亚美尼亚语'],
        'as' => ['label' => 'Assamese', 'admin_label' => '阿萨姆语'],
        'av' => ['label' => 'Avar', 'admin_label' => '阿瓦尔语'],
        'awa' => ['label' => 'Awadhi', 'admin_label' => '阿瓦德语'],
        'ay' => ['label' => 'Aymara', 'admin_label' => '艾马拉语'],
        'az' => ['label' => 'Azerbaijani', 'admin_label' => '阿塞拜疆语'],
        'ban' => ['label' => 'Balinese', 'admin_label' => '巴厘语'],
        'bal' => ['label' => 'Baluchi', 'admin_label' => '俾路支语'],
        'bm' => ['label' => 'Bambara', 'admin_label' => '班巴拉语'],
        'bci' => ['label' => 'Baoulé', 'admin_label' => '巴乌雷语'],
        'ba' => ['label' => 'Bashkir', 'admin_label' => '巴什基尔语'],
        'eu' => ['label' => 'Basque', 'admin_label' => '巴斯克语'],
        'btx' => ['label' => 'Batak Karo', 'admin_label' => '巴塔克卡罗语'],
        'bts' => ['label' => 'Batak Simalungun', 'admin_label' => '巴塔克西马隆贡语'],
        'bbc' => ['label' => 'Batak Toba', 'admin_label' => '巴塔克托巴语'],
        'be' => ['label' => 'Belarusian', 'admin_label' => '白俄罗斯语'],
        'bem' => ['label' => 'Bemba', 'admin_label' => '奔巴语'],
        'bn' => ['label' => 'Bengali', 'admin_label' => '孟加拉语'],
        'bew' => ['label' => 'Betawi', 'admin_label' => '巴达维语'],
        'bho' => ['label' => 'Bhojpuri', 'admin_label' => '博杰普尔语'],
        'bik' => ['label' => 'Bikol', 'admin_label' => '比科尔语'],
        'bs' => ['label' => 'Bosnian', 'admin_label' => '波斯尼亚语'],
        'br' => ['label' => 'Breton', 'admin_label' => '布列塔尼语'],
        'bg' => ['label' => 'Bulgarian', 'admin_label' => '保加利亚语'],
        'bua' => ['label' => 'Buryat', 'admin_label' => '布里亚特语'],
        'yue' => ['label' => 'Cantonese', 'admin_label' => '粤语'],
        'ca' => ['label' => 'Catalan', 'admin_label' => '加泰罗尼亚语'],
        'ceb' => ['label' => 'Cebuano', 'admin_label' => '宿务语'],
        'ch' => ['label' => 'Chamorro', 'admin_label' => '查莫罗语'],
        'ce' => ['label' => 'Chechen', 'admin_label' => '车臣语'],
        'ny' => ['label' => 'Chichewa', 'admin_label' => '齐切瓦语'],
        'zh-CN' => ['label' => 'Chinese (Simplified)', 'admin_label' => '中文（简体）'],
        'zh-TW' => ['label' => 'Chinese (Traditional)', 'admin_label' => '中文（繁体）'],
        'chk' => ['label' => 'Chuukese', 'admin_label' => '楚克语'],
        'cv' => ['label' => 'Chuvash', 'admin_label' => '楚瓦什语'],
        'co' => ['label' => 'Corsican', 'admin_label' => '科西嘉语'],
        'crh' => ['label' => 'Crimean Tatar (Cyrillic)', 'admin_label' => '克里米亚鞑靼语（西里尔文）'],
        'crh-Latn' => ['label' => 'Crimean Tatar (Latin)', 'admin_label' => '克里米亚鞑靼语（拉丁文）'],
        'hr' => ['label' => 'Croatian', 'admin_label' => '克罗地亚语'],
        'cs' => ['label' => 'Czech', 'admin_label' => '捷克语'],
        'da' => ['label' => 'Danish', 'admin_label' => '丹麦语'],
        'fa-AF' => ['label' => 'Dari', 'admin_label' => '达里语'],
        'dv' => ['label' => 'Dhivehi', 'admin_label' => '迪维希语'],
        'din' => ['label' => 'Dinka', 'admin_label' => '丁卡语'],
        'doi' => ['label' => 'Dogri', 'admin_label' => '多格拉语'],
        'dov' => ['label' => 'Dombe', 'admin_label' => '恩敦贝语'],
        'nl' => ['label' => 'Dutch', 'admin_label' => '荷兰语'],
        'dyu' => ['label' => 'Dyula', 'admin_label' => '迪尤拉语'],
        'dz' => ['label' => 'Dzongkha', 'admin_label' => '宗卡语'],
        'en' => ['label' => 'English', 'admin_label' => '英语'],
        'eo' => ['label' => 'Esperanto', 'admin_label' => '世界语'],
        'et' => ['label' => 'Estonian', 'admin_label' => '爱沙尼亚语'],
        'ee' => ['label' => 'Ewe', 'admin_label' => '埃维语'],
        'fo' => ['label' => 'Faroese', 'admin_label' => '法罗语'],
        'fj' => ['label' => 'Fijian', 'admin_label' => '斐济语'],
        'tl' => ['label' => 'Filipino', 'admin_label' => '菲律宾语'],
        'fi' => ['label' => 'Finnish', 'admin_label' => '芬兰语'],
        'fon' => ['label' => 'Fon', 'admin_label' => '丰语'],
        'fr' => ['label' => 'French', 'admin_label' => '法语'],
        'fr-CA' => ['label' => 'French (Canada)', 'admin_label' => '法语（加拿大）'],
        'fy' => ['label' => 'Frisian', 'admin_label' => '弗里西语'],
        'fur' => ['label' => 'Friulian', 'admin_label' => '弗留利语'],
        'ff' => ['label' => 'Fulani', 'admin_label' => '富拉尼语'],
        'gaa' => ['label' => 'Ga', 'admin_label' => '加语'],
        'gl' => ['label' => 'Galician', 'admin_label' => '加利西亚语'],
        'ka' => ['label' => 'Georgian', 'admin_label' => '格鲁吉亚语'],
        'de' => ['label' => 'German', 'admin_label' => '德语'],
        'el' => ['label' => 'Greek', 'admin_label' => '希腊语'],
        'gn' => ['label' => 'Guarani', 'admin_label' => '瓜拉尼语'],
        'gu' => ['label' => 'Gujarati', 'admin_label' => '古吉拉特语'],
        'ht' => ['label' => 'Haitian Creole', 'admin_label' => '海地克里奥尔语'],
        'cnh' => ['label' => 'Hakha Chin', 'admin_label' => '哈卡钦语'],
        'ha' => ['label' => 'Hausa', 'admin_label' => '豪萨语'],
        'haw' => ['label' => 'Hawaiian', 'admin_label' => '夏威夷语'],
        'iw' => ['label' => 'Hebrew', 'admin_label' => '希伯来语'],
        'hil' => ['label' => 'Hiligaynon', 'admin_label' => '希利盖农语'],
        'hi' => ['label' => 'Hindi', 'admin_label' => '印地语'],
        'hmn' => ['label' => 'Hmong', 'admin_label' => '苗语'],
        'hu' => ['label' => 'Hungarian', 'admin_label' => '匈牙利语'],
        'hrx' => ['label' => 'Hunsrik', 'admin_label' => '洪斯吕克语'],
        'iba' => ['label' => 'Iban', 'admin_label' => '伊班语'],
        'is' => ['label' => 'Icelandic', 'admin_label' => '冰岛语'],
        'ig' => ['label' => 'Igbo', 'admin_label' => '伊博语'],
        'ilo' => ['label' => 'Ilocano', 'admin_label' => '伊洛卡诺语'],
        'id' => ['label' => 'Indonesian', 'admin_label' => '印尼语'],
        'iu-Latn' => ['label' => 'Inuktut (Latin)', 'admin_label' => '因纽特语（拉丁文）'],
        'iu' => ['label' => 'Inuktut (Syllabics)', 'admin_label' => '因纽特语（音节）'],
        'ga' => ['label' => 'Irish', 'admin_label' => '爱尔兰语'],
        'it' => ['label' => 'Italian', 'admin_label' => '意大利语'],
        'jam' => ['label' => 'Jamaican Patois', 'admin_label' => '牙买加土语'],
        'ja' => ['label' => 'Japanese', 'admin_label' => '日语'],
        'jw' => ['label' => 'Javanese', 'admin_label' => '爪哇语'],
        'kac' => ['label' => 'Jingpo', 'admin_label' => '景颇语'],
        'kl' => ['label' => 'Kalaallisut', 'admin_label' => '格陵兰语'],
        'kn' => ['label' => 'Kannada', 'admin_label' => '卡纳达语'],
        'kr' => ['label' => 'Kanuri', 'admin_label' => '卡努里语'],
        'pam' => ['label' => 'Kapampangan', 'admin_label' => '邦板牙语'],
        'kk' => ['label' => 'Kazakh', 'admin_label' => '哈萨克语'],
        'kha' => ['label' => 'Khasi', 'admin_label' => '卡西语'],
        'km' => ['label' => 'Khmer', 'admin_label' => '高棉语'],
        'cgg' => ['label' => 'Kiga', 'admin_label' => '奇加语'],
        'kg' => ['label' => 'Kikongo', 'admin_label' => '刚果语'],
        'rw' => ['label' => 'Kinyarwanda', 'admin_label' => '卢旺达语'],
        'ktu' => ['label' => 'Kituba', 'admin_label' => '吉土巴语'],
        'trp' => ['label' => 'Kokborok', 'admin_label' => '廓克博若克语'],
        'kv' => ['label' => 'Komi', 'admin_label' => '科米语'],
        'gom' => ['label' => 'Konkani', 'admin_label' => '贡根语'],
        'ko' => ['label' => 'Korean', 'admin_label' => '韩语'],
        'kri' => ['label' => 'Krio', 'admin_label' => '塞拉利昂克里奥尔语'],
        'ku' => ['label' => 'Kurdish (Kurmanji)', 'admin_label' => '库尔德语（库尔曼吉语）'],
        'ckb' => ['label' => 'Kurdish (Sorani)', 'admin_label' => '库尔德语（索拉尼）'],
        'ky' => ['label' => 'Kyrgyz', 'admin_label' => '吉尔吉斯语'],
        'lo' => ['label' => 'Lao', 'admin_label' => '老挝语'],
        'ltg' => ['label' => 'Latgalian', 'admin_label' => '拉特加莱语'],
        'la' => ['label' => 'Latin', 'admin_label' => '拉丁语'],
        'lv' => ['label' => 'Latvian', 'admin_label' => '拉脱维亚语'],
        'lij' => ['label' => 'Ligurian', 'admin_label' => '利古里亚语'],
        'li' => ['label' => 'Limburgish', 'admin_label' => '林堡语'],
        'ln' => ['label' => 'Lingala', 'admin_label' => '林加拉语'],
        'lt' => ['label' => 'Lithuanian', 'admin_label' => '立陶宛语'],
        'lmo' => ['label' => 'Lombard', 'admin_label' => '伦巴第语'],
        'lg' => ['label' => 'Luganda', 'admin_label' => '卢干达语'],
        'luo' => ['label' => 'Luo', 'admin_label' => '卢奥语'],
        'lb' => ['label' => 'Luxembourgish', 'admin_label' => '卢森堡语'],
        'mk' => ['label' => 'Macedonian', 'admin_label' => '马其顿语'],
        'mad' => ['label' => 'Madurese', 'admin_label' => '马都拉语'],
        'mai' => ['label' => 'Maithili', 'admin_label' => '迈蒂利语'],
        'mak' => ['label' => 'Makassar', 'admin_label' => '望加锡语'],
        'mg' => ['label' => 'Malagasy', 'admin_label' => '马尔加什语'],
        'ms' => ['label' => 'Malay', 'admin_label' => '马来语'],
        'ms-Arab' => ['label' => 'Malay (Jawi)', 'admin_label' => '马来语（爪夷文）'],
        'ml' => ['label' => 'Malayalam', 'admin_label' => '马拉雅拉姆语'],
        'mt' => ['label' => 'Maltese', 'admin_label' => '马耳他语'],
        'mam' => ['label' => 'Mam', 'admin_label' => '玛姆语'],
        'gv' => ['label' => 'Manx', 'admin_label' => '马恩岛语'],
        'mi' => ['label' => 'Maori', 'admin_label' => '毛利语'],
        'mr' => ['label' => 'Marathi', 'admin_label' => '马拉地语'],
        'mh' => ['label' => 'Marshallese', 'admin_label' => '马绍尔语'],
        'mwr' => ['label' => 'Marwadi', 'admin_label' => '马尔瓦迪语'],
        'mfe' => ['label' => 'Mauritian Creole', 'admin_label' => '毛里裘斯克里奥耳语'],
        'chm' => ['label' => 'Meadow Mari', 'admin_label' => '草原马里语'],
        'mni-Mtei' => ['label' => 'Meiteilon (Manipuri)', 'admin_label' => '梅泰语（曼尼普尔语）'],
        'min' => ['label' => 'Minang', 'admin_label' => '米南语'],
        'lus' => ['label' => 'Mizo', 'admin_label' => '米佐语'],
        'mn' => ['label' => 'Mongolian', 'admin_label' => '蒙古语'],
        'my' => ['label' => 'Myanmar (Burmese)', 'admin_label' => '缅甸语'],
        'nhe' => ['label' => 'Nahuatl (Eastern Huasteca)', 'admin_label' => '纳瓦特尔语（东部瓦斯特卡）'],
        'ndc-ZW' => ['label' => 'Ndau', 'admin_label' => '恩道语'],
        'nr' => ['label' => 'Ndebele (South)', 'admin_label' => '恩德贝莱语（南部）'],
        'new' => ['label' => 'Nepalbhasa (Newari)', 'admin_label' => '尼泊尔语言（尼瓦尔语）'],
        'ne' => ['label' => 'Nepali', 'admin_label' => '尼泊尔语'],
        'bm-Nkoo' => ['label' => 'NKo', 'admin_label' => '恩科字母（西非书面文字）'],
        'no' => ['label' => 'Norwegian', 'admin_label' => '挪威语'],
        'nus' => ['label' => 'Nuer', 'admin_label' => '努尔语'],
        'oc' => ['label' => 'Occitan', 'admin_label' => '奥克语'],
        'or' => ['label' => 'Odia (Oriya)', 'admin_label' => '奥利亚语'],
        'om' => ['label' => 'Oromo', 'admin_label' => '奥罗莫语'],
        'os' => ['label' => 'Ossetian', 'admin_label' => '奥塞梯语'],
        'pag' => ['label' => 'Pangasinan', 'admin_label' => '邦阿西楠语'],
        'pap' => ['label' => 'Papiamento', 'admin_label' => '帕皮阿门托语'],
        'ps' => ['label' => 'Pashto', 'admin_label' => '普什图语'],
        'fa' => ['label' => 'Persian', 'admin_label' => '波斯语'],
        'pl' => ['label' => 'Polish', 'admin_label' => '波兰语'],
        'pt' => ['label' => 'Portuguese (Brazil)', 'admin_label' => '葡萄牙语（巴西）'],
        'pt-PT' => ['label' => 'Portuguese (Portugal)', 'admin_label' => '葡萄牙语（葡萄牙）'],
        'pa' => ['label' => 'Punjabi (Gurmukhi)', 'admin_label' => '旁遮普语（果鲁穆奇文）'],
        'pa-Arab' => ['label' => 'Punjabi (Shahmukhi)', 'admin_label' => '旁遮普语（沙木基文）'],
        'qu' => ['label' => 'Quechua', 'admin_label' => '克丘亚语'],
        'kek' => ['label' => 'Qʼeqchiʼ', 'admin_label' => '凯克其语'],
        'rom' => ['label' => 'Romani', 'admin_label' => '罗姆语'],
        'ro' => ['label' => 'Romanian', 'admin_label' => '罗马尼亚语'],
        'rn' => ['label' => 'Rundi', 'admin_label' => '隆迪语'],
        'ru' => ['label' => 'Russian', 'admin_label' => '俄语'],
        'se' => ['label' => 'Sami (North)', 'admin_label' => '萨米语（北部）'],
        'sm' => ['label' => 'Samoan', 'admin_label' => '萨摩亚语'],
        'sg' => ['label' => 'Sango', 'admin_label' => '桑戈语'],
        'sa' => ['label' => 'Sanskrit', 'admin_label' => '梵语'],
        'sat-Latn' => ['label' => 'Santali (Latin)', 'admin_label' => '桑塔利语（拉丁文）'],
        'sat' => ['label' => 'Santali (Ol Chiki)', 'admin_label' => '桑塔利语（欧甘文）'],
        'gd' => ['label' => 'Scots Gaelic', 'admin_label' => '苏格兰盖尔语'],
        'nso' => ['label' => 'Sepedi', 'admin_label' => '北索托语'],
        'sr' => ['label' => 'Serbian', 'admin_label' => '塞尔维亚语'],
        'st' => ['label' => 'Sesotho', 'admin_label' => '南索托语'],
        'crs' => ['label' => 'Seychellois Creole', 'admin_label' => '塞舌尔克里奥尔语'],
        'shn' => ['label' => 'Shan', 'admin_label' => '掸语'],
        'sn' => ['label' => 'Shona', 'admin_label' => '修纳语'],
        'scn' => ['label' => 'Sicilian', 'admin_label' => '西西里语'],
        'szl' => ['label' => 'Silesian', 'admin_label' => '西里西亚语'],
        'sd' => ['label' => 'Sindhi', 'admin_label' => '信德语'],
        'si' => ['label' => 'Sinhala', 'admin_label' => '僧伽罗语'],
        'sk' => ['label' => 'Slovak', 'admin_label' => '斯洛伐克语'],
        'sl' => ['label' => 'Slovenian', 'admin_label' => '斯洛文尼亚语'],
        'so' => ['label' => 'Somali', 'admin_label' => '索马里语'],
        'es' => ['label' => 'Spanish', 'admin_label' => '西班牙语'],
        'su' => ['label' => 'Sundanese', 'admin_label' => '巽他语'],
        'sus' => ['label' => 'Susu', 'admin_label' => '苏苏语'],
        'sw' => ['label' => 'Swahili', 'admin_label' => '斯瓦希里语'],
        'ss' => ['label' => 'Swati', 'admin_label' => '斯瓦特语'],
        'sv' => ['label' => 'Swedish', 'admin_label' => '瑞典语'],
        'ty' => ['label' => 'Tahitian', 'admin_label' => '塔希提语'],
        'tg' => ['label' => 'Tajik', 'admin_label' => '塔吉克语'],
        'ber-Latn' => ['label' => 'Tamazight', 'admin_label' => '塔马塞特语'],
        'ber' => ['label' => 'Tamazight (Tifinagh)', 'admin_label' => '塔马齐格特语（提非纳文）'],
        'ta' => ['label' => 'Tamil', 'admin_label' => '泰米尔语'],
        'tt' => ['label' => 'Tatar', 'admin_label' => '鞑靼语'],
        'te' => ['label' => 'Telugu', 'admin_label' => '泰卢固语'],
        'tet' => ['label' => 'Tetum', 'admin_label' => '德顿语'],
        'th' => ['label' => 'Thai', 'admin_label' => '泰语'],
        'bo' => ['label' => 'Tibetan', 'admin_label' => '藏语'],
        'ti' => ['label' => 'Tigrinya', 'admin_label' => '提格里尼亚语'],
        'tiv' => ['label' => 'Tiv', 'admin_label' => '蒂夫语'],
        'tpi' => ['label' => 'Tok Pisin', 'admin_label' => '巴布亚皮钦语'],
        'to' => ['label' => 'Tongan', 'admin_label' => '汤加语'],
        'lua' => ['label' => 'Tshiluba', 'admin_label' => '奇卢伯语'],
        'ts' => ['label' => 'Tsonga', 'admin_label' => '聪加语'],
        'tn' => ['label' => 'Tswana', 'admin_label' => '茨瓦纳语'],
        'tcy' => ['label' => 'Tulu', 'admin_label' => '图鲁语'],
        'tum' => ['label' => 'Tumbuka', 'admin_label' => '图姆布卡语'],
        'tr' => ['label' => 'Turkish', 'admin_label' => '土耳其语'],
        'tk' => ['label' => 'Turkmen', 'admin_label' => '土库曼语'],
        'tyv' => ['label' => 'Tuvan', 'admin_label' => '图瓦语'],
        'ak' => ['label' => 'Twi', 'admin_label' => '契维语'],
        'udm' => ['label' => 'Udmurt', 'admin_label' => '乌德穆尔特语'],
        'uk' => ['label' => 'Ukrainian', 'admin_label' => '乌克兰语'],
        'ur' => ['label' => 'Urdu', 'admin_label' => '乌尔都语'],
        'ug' => ['label' => 'Uyghur', 'admin_label' => '维吾尔语'],
        'uz' => ['label' => 'Uzbek', 'admin_label' => '乌兹别克语'],
        've' => ['label' => 'Venda', 'admin_label' => '文达语'],
        'vec' => ['label' => 'Venetian', 'admin_label' => '威尼斯语'],
        'vi' => ['label' => 'Vietnamese', 'admin_label' => '越南语'],
        'war' => ['label' => 'Waray', 'admin_label' => '瓦瑞语'],
        'cy' => ['label' => 'Welsh', 'admin_label' => '威尔士语'],
        'wo' => ['label' => 'Wolof', 'admin_label' => '沃洛夫语'],
        'xh' => ['label' => 'Xhosa', 'admin_label' => '科萨语'],
        'sah' => ['label' => 'Yakut', 'admin_label' => '雅库特语'],
        'yi' => ['label' => 'Yiddish', 'admin_label' => '意第绪语'],
        'yo' => ['label' => 'Yoruba', 'admin_label' => '约鲁巴语'],
        'yua' => ['label' => 'Yucatec Maya', 'admin_label' => '尤卡坦玛雅语'],
        'zap' => ['label' => 'Zapotec', 'admin_label' => '萨巴特克语'],
        'zu' => ['label' => 'Zulu', 'admin_label' => '祖鲁语'],
    ];

    $flags = [
        'en' => 'us',
        'zh-CN' => 'cn',
        'zh-TW' => 'tw',
        'ja' => 'jp',
        'ko' => 'kr',
        'es' => 'es',
        'fr' => 'fr',
        'de' => 'de',
        'it' => 'it',
        'pt' => 'br',
        'pt-PT' => 'pt',
        'ru' => 'ru',
        'ar' => 'sa',
        'hi' => 'in',
        'bn' => 'bd',
        'id' => 'id',
        'tr' => 'tr',
        'vi' => 'vn',
        'th' => 'th',
        'nl' => 'nl',
        'pl' => 'pl',
        'sv' => 'se',
        'uk' => 'ua',
        'ur' => 'pk',
        'fa' => 'ir',
        'iw' => 'il',
        'tl' => 'ph',
        'ms' => 'my',
        'ro' => 'ro',
        'el' => 'gr',
        'cs' => 'cz',
        'da' => 'dk',
        'fi' => 'fi',
        'no' => 'no',
        'hu' => 'hu',
        'sk' => 'sk',
        'sl' => 'si',
        'hr' => 'hr',
        'sr' => 'rs',
        'bg' => 'bg',
        'lt' => 'lt',
        'lv' => 'lv',
        'et' => 'ee',
    ];

    foreach ($languages as $code => $meta) {
        $languages[$code]['flag'] = $flags[$code] ?? '';
    }

    return $languages;
}

function get_google_translate_default_language_codes(): array {
    return ['en', 'zh-CN', 'zh-TW', 'ja', 'ko', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ar'];
}

function get_google_translate_admin_language_options(): array {
    $options = [];
    foreach (get_google_translate_supported_languages() as $code => $meta) {
        $options[$code] = ($meta['admin_label'] ?? $meta['label']) . ' / ' . $meta['label'] . ' (' . $code . ')';
    }

    return $options;
}

/**
 * 谷歌翻译系统功能：返回前台已启用语言配置
 */
function get_google_translate_languages(array $site = []): array {
    $allLanguages = get_google_translate_supported_languages();
    $configured = json_decode($site['translate_languages'] ?? '[]', true);

    if (!is_array($configured) || empty($configured)) {
        $configured = get_google_translate_default_language_codes();
    }
    if (!in_array('en', $configured, true)) {
        array_unshift($configured, 'en');
    }

    $enabled = [];
    foreach ($configured as $code) {
        if (isset($allLanguages[$code])) {
            $enabled[$code] = $allLanguages[$code];
        }
    }

    return !empty($enabled) ? $enabled : ['en' => $allLanguages['en']];
}

function is_google_translate_enabled(array $site = []): bool {
    return ($site['translate_enabled'] ?? '1') === '1';
}

function is_google_translate_auto_browser(array $site = []): bool {
    return ($site['translate_auto_browser'] ?? '0') === '1';
}

/**
 * 返回 head 自定义代码（原样输出）
 */
function get_head_code(): string {
    static $cached = null;
    if ($cached === null) {
        $settingModel = new \App\Models\Setting();
        $cached = (string)$settingModel->get('head_code', '');
    }
    return $cached;
}

/**
 * 返回 footer 自定义代码（原样输出）
 */
function get_footer_code(): string {
    static $cached = null;
    if ($cached === null) {
        $settingModel = new \App\Models\Setting();
        $cached = (string)$settingModel->get('footer_code', '');
    }
    return $cached;
}

/**
 * 输出谷歌翻译组件（样式/脚本/告警/按钮合一）
 */
function get_google_translate_widget(array $site = [], string $buttonClass = 'button is-white', string $wrapperClass = 'navbar-item'): string {
    if (!is_google_translate_enabled($site)) {
        return '';
    }

    $languageMap = get_google_translate_languages($site);
    $languageMapJson = json_encode($languageMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $autoTranslate = is_google_translate_auto_browser($site) ? 'true' : 'false';

    $safeWrapperClass = h($wrapperClass);
    $safeButtonClass = h($buttonClass);
    $options = '';
    foreach ($languageMap as $langCode => $langMeta) {
        $flagCode = trim((string)($langMeta['flag'] ?? ''));
        $icon = $flagCode !== ''
            ? '<span class="fi fi-' . h($flagCode) . '"></span>'
            : '<i class="fa-solid fa-globe lang-globe-icon"></i>';
        $options .= '<a href="javascript:void(0)" onclick="triggerGoogleTranslate(\'' . h($langCode) . '\')" class="lang-option dropdown-item">'
            . $icon
            . '<span>' . h($langMeta['label']) . '</span>'
            . '</a>';
    }

    return <<<HTML
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/css/flag-icons.min.css">
<style>
    body > .skiptranslate { display: none; }
    .goog-logo-link { display: none !important; }
    .goog-te-gadget { color: transparent !important; }
    .goog-te-banner-frame.skiptranslate { display: none !important; }
    a[href="https://translate.google.com"] { display: none !important; }
    body { top: 0 !important; }
    .goog-tooltip { display: none !important; }
    .goog-te-gadget-simple { display: none !important; }

    #google_translate_element {
        width: 0;
        height: 0;
        overflow: hidden;
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .lang-selector-wrapper { position: relative; display: inline-block; }
    .lang-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 4px;
        background: #fff;
        border: 1px solid #dbdbdb;
        border-radius: 4px;
        box-shadow: 0 8px 16px rgba(10,10,10,0.1);
        display: none;
        z-index: 1000;
        min-width: 150px;
        max-height: 70vh;
        overflow-y: auto;
    }
    .lang-dropdown.is-active { display: block; }
    .lang-option {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        color: #4a4a4a;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .lang-option:hover {
        background-color: #f5f5f5;
        color: #3273dc;
    }
    .fi {
        width: 1.33em !important;
        line-height: 1em !important; }
    .lang-globe-icon {
        width: 1.33em;
        text-align: center;
        color: #64748b;
    }
    .translate-alert {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10000;
        background: #ffdd57;
        color: #363636;
        font-size: 14px;
        padding: 8px 16px;
        text-align: center;
    }
    .translate-alert.is-active { display: block; }
</style>
<div id="translate-network-alert" class="translate-alert">Google翻译服务无法正常访问，请检查您的网络环境</div>
<script>
    var translationEnabled = true;
    var autoTranslateByBrowser = {$autoTranslate};
    var googleTranslateReady = false;
    var googleTranslateScriptLoaded = false;
    var googleTranslateScriptRequested = false;
    var userRequestedTranslation = false;
    var languageMap = {$languageMapJson};

    function showTranslateError() {
        if (!userRequestedTranslation && !getCookie('googtrans')) return;
        var alertEl = document.getElementById('translate-network-alert');
        if (alertEl) alertEl.classList.add('is-active');
    }

    function getCookie(name) {
        var prefix = name + '=';
        var parts = document.cookie.split(';');
        for (var i = 0; i < parts.length; i++) {
            var item = parts[i].trim();
            if (item.indexOf(prefix) === 0) return item.substring(prefix.length);
        }
        return '';
    }

    function getCurrentLanguage() {
        var cookie = getCookie('googtrans');
        if (!cookie) return 'en';
        var segments = cookie.split('/');
        var lang = segments[segments.length - 1] || 'en';
        return languageMap[lang] ? lang : 'en';
    }

    function updateCurrentLanguageUI(langCode) {
        var lang = languageMap[langCode] ? langCode : 'en';
        var langMeta = languageMap[lang];
        var textEl = document.getElementById('current-language-text');
        var flagEl = document.getElementById('current-language-flag');
        if (textEl) textEl.textContent = langMeta.label;
        if (flagEl) flagEl.className = langMeta.flag ? 'fi fi-' + langMeta.flag : 'fa-solid fa-globe lang-globe-icon';
    }

    function clearGoogleTranslateCookie() {
        document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
        if (location.hostname && location.hostname.indexOf('.') > -1) {
            document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 GMT;domain=' + location.hostname + ';path=/';
        }
    }

    function getTranslateSelect() {
        return document.querySelector('select.goog-te-combo');
    }

    function markTranslateReady(maxRetry) {
        var retry = typeof maxRetry === 'number' ? maxRetry : 20;
        var timer = setInterval(function() {
            if (getTranslateSelect()) {
                googleTranslateReady = true;
                clearInterval(timer);
                return;
            }
            retry--;
            if (retry <= 0) clearInterval(timer);
        }, 250);
    }

    function setGoogleTranslateCookie(langCode) {
        if (langCode === 'en') {
            clearGoogleTranslateCookie();
            return;
        }
        var value = '/en/' + langCode;
        document.cookie = 'googtrans=' + value + ';path=/';
        if (location.hostname && location.hostname.indexOf('.') > -1) {
            document.cookie = 'googtrans=' + value + ';domain=' + location.hostname + ';path=/';
        }
    }

    function googleTranslateElementInit() {
        googleTranslateScriptLoaded = true;
        var includedLanguages = Object.keys(languageMap).join(',');
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: includedLanguages,
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
        markTranslateReady(24);
    }

    function loadGoogleTranslateScript() {
        if (googleTranslateScriptLoaded || googleTranslateScriptRequested) return;
        googleTranslateScriptRequested = true;
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';

        script.async = true;
        script.onerror = function() { showTranslateError(); };
        document.head.appendChild(script);

        setTimeout(function() {
            if (!googleTranslateScriptLoaded) showTranslateError();
        }, 6000);
    }

    function detectBrowserLanguage() {
        var browserLang = (navigator.language || navigator.userLanguage || 'en').trim();
        if (languageMap[browserLang]) return browserLang;
        var shortLang = browserLang.split('-')[0];
        for (var code in languageMap) {
            if (code.split('-')[0] === shortLang) return code;
        }
        return 'en';
    }

    function applyAutoTranslateByBrowserLanguage() {
        if (!autoTranslateByBrowser || getCookie('googtrans')) return;
        var target = detectBrowserLanguage();
        if (target !== 'en') triggerGoogleTranslate(target);
    }

    function triggerGoogleTranslate(langCode) {
        if (!languageMap[langCode]) langCode = 'en';
        if (langCode !== 'en') {
            userRequestedTranslation = true;
            loadGoogleTranslateScript();
        }
        updateCurrentLanguageUI(langCode);

        if (langCode === 'en') {
            clearGoogleTranslateCookie();
            var resetSelect = getTranslateSelect();
            if (resetSelect) {
                resetSelect.value = '';
                resetSelect.dispatchEvent(new Event('change'));
            }
            location.reload();
            return;
        }

        var select = getTranslateSelect();
        if (select) {
            select.value = langCode;
            select.dispatchEvent(new Event('change'));
        } else {
            var attempts = 0;
            var retryTimer = setInterval(function() {
                var selectRetry = getTranslateSelect();
                if (selectRetry) {
                    selectRetry.value = langCode;
                    selectRetry.dispatchEvent(new Event('change'));
                    googleTranslateReady = true;
                    clearInterval(retryTimer);
                    return;
                }
                attempts++;
                if (attempts >= 8) {
                    clearInterval(retryTimer);
                    setGoogleTranslateCookie(langCode);
                    location.reload();
                }
            }, 250);
        }

        var dropdown = document.querySelector('.lang-dropdown');
        if (dropdown) dropdown.classList.remove('is-active');
    }

    function toggleLangDropdown() {
        var dropdown = document.querySelector('.lang-dropdown');
        if (dropdown) dropdown.classList.toggle('is-active');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.lang-selector-wrapper')) {
            var dropdown = document.querySelector('.lang-dropdown');
            if (dropdown) dropdown.classList.remove('is-active');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        updateCurrentLanguageUI(getCurrentLanguage());
        if (getCookie('googtrans')) {
            loadGoogleTranslateScript();
        }
        if (autoTranslateByBrowser) {
            setTimeout(function() { applyAutoTranslateByBrowserLanguage(); }, 600);
        }
    });
</script>
<div class="{$safeWrapperClass}">
    <div id="google_translate_element"></div>
    <div class="lang-selector-wrapper">
        <div class="{$safeButtonClass}" onclick="toggleLangDropdown()">
            <span class="icon"><span id="current-language-flag" class="fi fi-us"></span></span>
            <span id="current-language-text">English</span>
            <span class="icon is-small"><i class="fa-solid fa-angle-down"></i></span>
        </div>
        <div class="lang-dropdown box" style="padding: 0.5rem;">
            {$options}
        </div>
    </div>
</div>
HTML;
}

// ============================================================================
// 区块配置函数 - 模板区块可视化编辑系统
// ============================================================================

/**
 * 获取区块字段值
 * 
 * 优先读取用户自定义值，无则回退到模板默认值。
 * 
 * @param string $blockKey 区块标识（如 'home_cta'）
 * @param string $fieldKey 字段标识（如 'heading'）
 * @param string $fallback 最终兜底值（当模板默认也无此字段时）
 * @return string
 */
function block(string $blockKey, string $fieldKey, string $fallback = ''): string {
    static $merged = null;

    if ($merged === null) {
        $merged = _load_merged_blocks();
    }

    return (string)($merged[$blockKey][$fieldKey] ?? $fallback);
}

/**
 * 获取区块所有字段的值（合并后的）
 *
 * @param string $blockKey 区块标识
 * @return array 字段 key => value
 */
function block_all(string $blockKey): array {
    static $merged = null;
    if ($merged === null) {
        $merged = _load_merged_blocks();
    }
    return $merged[$blockKey] ?? [];
}

/**
 * 加载并合并区块配置：模板默认 + 用户自定义
 *
 * @return array [blockKey => [fieldKey => value, ...], ...]
 */
function _load_merged_blocks(): array {
    $theme = _get_current_theme();

    // 1. 模板默认区块配置
    $defaultFile = APP_ROOT . '/themes/' . $theme . '/blocks.php';
    $defaults = [];
    if (is_file($defaultFile)) {
        $raw = include $defaultFile;
        if (is_array($raw)) {
            foreach ($raw as $bk => $block) {
                if (!isset($block['fields']) || !is_array($block['fields'])) continue;
                foreach ($block['fields'] as $fk => $field) {
                    $defaults[$bk][$fk] = $field['default'] ?? '';
                }
            }
        }
    }

    // 2. 用户自定义区块配置
    $userFile = APP_ROOT . '/storage/blocks/' . $theme . '.php';
    $userValues = [];
    if (is_file($userFile)) {
        $userValues = include $userFile;
        if (!is_array($userValues)) {
            $userValues = [];
        }
    }

    // 3. 合并：用户值覆盖默认值
    $merged = $defaults;
    foreach ($userValues as $bk => $fields) {
        if (!is_array($fields)) continue;
        foreach ($fields as $fk => $val) {
            if ((string)$val !== '') {
                $merged[$bk][$fk] = $val;
            }
        }
    }

    return $merged;
}

/**
 * 获取当前主题名称
 */
function _get_current_theme(): string {
    static $theme = null;
    if ($theme === null) {
        try {
            $settingModel = new \App\Models\Setting();
            $theme = $settingModel->get('theme', 'default');
        } catch (\Throwable $e) {
            $theme = 'default';
        }
    }
    return $theme;
}

/**
 * 加载模板的区块定义（带字段元数据，供后台使用）
 *
 * @param string $theme 主题名称
 * @return array 完整区块定义
 */
function get_block_definitions(string $theme = 'default'): array {
    $file = APP_ROOT . '/themes/' . $theme . '/blocks.php';
    if (!is_file($file)) return [];
    $defs = include $file;
    return is_array($defs) ? $defs : [];
}

/**
 * 加载用户自定义区块值
 *
 * @param string $theme 主题名称
 * @return array [blockKey => [fieldKey => value, ...], ...]
 */
function get_user_block_values(string $theme = 'default'): array {
    $file = APP_ROOT . '/storage/blocks/' . $theme . '.php';
    if (!is_file($file)) return [];
    $data = include $file;
    return is_array($data) ? $data : [];
}

/**
 * 保存用户自定义区块值
 *
 * @param string $theme 主题名称
 * @param array $values [blockKey => [fieldKey => value, ...], ...]
 * @return bool
 */
function save_user_block_values(string $theme, array $values): bool {
    $dir = APP_ROOT . '/storage/blocks';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // 安全校验主题名
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $theme)) {
        return false;
    }

    $file = $dir . '/' . $theme . '.php';
    $export = var_export($values, true);
    $content = "<?php\n// 用户自定义区块配置 - 主题: {$theme}\n// 自动生成，请勿手动编辑\n// 最后更新: " . date('Y-m-d H:i:s') . "\nreturn {$export};\n";
    return file_put_contents($file, $content, LOCK_EX) !== false;
}
