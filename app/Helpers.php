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
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
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
        if (langCode !== 'en') userRequestedTranslation = true;
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
        loadGoogleTranslateScript();
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
