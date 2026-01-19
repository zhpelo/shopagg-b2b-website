<?php
declare(strict_types=1);

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text ?? '', '-');
    return $text === '' ? 'item' . time() : $text;
}

function t(string $key, ?string $lang = null): string {
    global $translations, $current_lang;
    $lang = $lang ?? ($current_lang ?? 'en');
    if (isset($translations[$lang][$key])) {
        return $translations[$lang][$key];
    }
    return $translations['en'][$key] ?? $key;
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

function lang_switch_url(string $lang): string {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $query = $_GET;
    $query['lang'] = $lang;
    return $path . '?' . http_build_query($query);
}

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
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
    $targetDir = __DIR__ . '/..' . $subDir;
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $filename = uniqid('img_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) return [false, '保存失败'];
    return [true, $subDir . '/' . $filename];
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

$translations = [
    'en' => [
        'nav_home' => 'Home', 'nav_products' => 'Products', 'nav_cases' => 'Cases', 'nav_blog' => 'Blog', 'nav_contact' => 'Contact',
        'cta_quote' => 'Request a Quote', 'btn_view_all' => 'View All', 'list_read_more' => 'Read More',
        'products' => 'Products', 'cases' => 'Cases', 'blog' => 'Blog',
        'home_quality_title' => 'Quality Assurance', 'home_quality_desc' => 'ISO-aligned production with strict QC before shipment.',
        'home_logistics_title' => 'Global Logistics', 'home_logistics_desc' => 'On-time delivery with consolidated freight options.',
        'home_support_title' => 'Dedicated Support', 'home_support_desc' => 'One-to-one account service for long-term buyers.',
        'section_featured_products' => 'Featured Products', 'section_success_cases' => 'Success Cases',
        'detail_send_inquiry' => 'Send Inquiry', 'btn_send_inquiry' => 'Send Inquiry',
        'form_name' => 'Name', 'form_email' => 'Email', 'form_company' => 'Company', 'form_phone' => 'Phone', 'form_message' => 'Message', 'form_requirements' => 'Requirements',
        'thanks_title' => 'Thank you', 'thanks_desc' => 'We have received your request and will reply within 24 hours.',
        'btn_back_home' => 'Back to Home', 'not_found_title' => 'Page Not Found', 'not_found_desc' => 'The page you are looking for does not exist.', 'btn_go_home' => 'Go Home',
    ],
    'zh' => [
        'nav_home' => '首页', 'nav_products' => '产品', 'nav_cases' => '案例', 'nav_blog' => '博客', 'nav_contact' => '联系',
        'cta_quote' => '立即询价', 'btn_view_all' => '查看全部', 'list_read_more' => '查看详情',
        'products' => '产品中心', 'cases' => '成功案例', 'blog' => '新闻资讯',
        'home_quality_title' => '品质保障', 'home_quality_desc' => '符合国际标准的生产流程与出货前严格质检。',
        'home_logistics_title' => '全球物流', 'home_logistics_desc' => '准时交付，支持多种国际物流方案。',
        'home_support_title' => '专属服务', 'home_support_desc' => '一对一客户经理，全流程响应。',
        'section_featured_products' => '核心产品', 'section_success_cases' => '成功案例',
        'detail_send_inquiry' => '发送询单', 'btn_send_inquiry' => '提交询单',
        'form_name' => '姓名', 'form_email' => '邮箱', 'form_company' => '公司', 'form_phone' => '电话', 'form_message' => '留言', 'form_requirements' => '需求描述',
        'thanks_title' => '提交成功', 'thanks_desc' => '我们已收到您的请求，将在 24 小时内回复。',
        'btn_back_home' => '返回首页', 'not_found_title' => '页面不存在', 'not_found_desc' => '您访问的页面不存在。', 'btn_go_home' => '返回首页',
    ],
];
