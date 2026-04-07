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
 * 生成 URL 友好的 slug
 * 
 * 将文本转换为合法的 URL 片段，移除特殊字符并转换为小写
 * 示例：'Hello World!' => 'hello-world'
 * 
 * @param string $text 原始文本
 * @return string URL 友好的文本
 */
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text ?? '', '-');
    return $text === '' ? 'item' . time() : $text;
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

/**
 * 生成语言切换 URL
 * 
 * 保留当前请求参数，仅修改 lang 参数
 * 
 * @param string $lang 语言代码（如 'en'、'zh'）
 * @return string 语言切换 URL
 */
function lang_switch_url(string $lang): string {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $query = $_GET;
    $query['lang'] = $lang;
    return $path . '?' . http_build_query($query);
}

/**
 * 获取支持的语言列表
 * 
 * @return array 语言代码 => 显示名称 的关联数组
 */
function get_languages(): array {
    return ['en' => 'English', 'zh' => '中文'];
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
            'page' => '/',
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
 * 谷歌翻译系统功能：返回可用语言配置
 */
function get_google_translate_languages(array $site = []): array {
    $allLanguages = [
        'en' => ['label' => 'English', 'flag' => 'us'],
        'zh-CN' => ['label' => '简体中文', 'flag' => 'cn'],
        'zh-TW' => ['label' => '繁體中文', 'flag' => 'tw'],
        'ja' => ['label' => '日本語', 'flag' => 'jp'],
        'ko' => ['label' => '한국어', 'flag' => 'kr'],
        'es' => ['label' => 'Español', 'flag' => 'es'],
        'fr' => ['label' => 'Français', 'flag' => 'fr'],
        'de' => ['label' => 'Deutsch', 'flag' => 'de'],
        'it' => ['label' => 'Italiano', 'flag' => 'it'],
        'pt' => ['label' => 'Português', 'flag' => 'pt'],
        'ru' => ['label' => 'Русский', 'flag' => 'ru'],
        'ar' => ['label' => 'العربية', 'flag' => 'sa'],
    ];

    $configured = json_decode($site['translate_languages'] ?? '[]', true);
    if (!is_array($configured) || empty($configured)) {
        $configured = array_keys($allLanguages);
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
        $options .= '<a href="javascript:void(0)" onclick="triggerGoogleTranslate(\'' . h($langCode) . '\')" class="lang-option dropdown-item">'
            . '<span class="fi fi-' . h($langMeta['flag']) . '"></span>'
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
        if (flagEl) flagEl.className = 'fi fi-' + langMeta.flag;
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
