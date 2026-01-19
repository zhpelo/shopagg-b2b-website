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

function get_languages(): array {
    return ['en' => 'English', 'zh' => '中文'];
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
        'nav_home' => 'Home', 'nav_products' => 'Products', 'nav_cases' => 'Cases', 'nav_blog' => 'Blog', 'nav_contact' => 'Contact', 'nav_about' => 'About Us',
        'cta_quote' => 'Request a Quote', 'btn_view_all' => 'View All', 'list_read_more' => 'Read More',
        'products' => 'Products', 'cases' => 'Cases', 'blog' => 'Blog',
        'home_quality_title' => 'Quality Assurance', 'home_quality_desc' => 'ISO-aligned production with strict QC before shipment.',
        'home_logistics_title' => 'Global Logistics', 'home_logistics_desc' => 'On-time delivery with consolidated freight options.',
        'home_support_title' => 'Dedicated Support', 'home_support_desc' => 'One-to-one account service for long-term buyers.',
        'home_highlights' => 'Company Highlights', 'home_why_us' => 'Why Choose Us',
        'home_iso' => 'ISO Certified', 'home_oem' => 'OEM & ODM', 'home_rd' => 'R&D Team', 'home_global' => 'Global Presence',
        'home_ready_title' => 'Ready to start your project?', 'home_ready_desc' => 'Contact us today for a professional quote and expert consultation.',
        'section_featured_products' => 'Featured Products', 'section_success_cases' => 'Success Cases',
        'detail_send_inquiry' => 'Send Inquiry', 'btn_send_inquiry' => 'Send My Inquiry',
        'form_name' => 'Name', 'form_email' => 'Email', 'form_company' => 'Company', 'form_phone' => 'Phone', 'form_message' => 'Message', 'form_requirements' => 'Requirements',
        'form_quantity' => 'Quantity Needed', 'form_name_full' => 'Your Name', 'form_email_full' => 'Email Address', 'form_req_custom' => 'Requirements / Customization',
        'thanks_title' => 'Inquiry Sent Successfully!', 'thanks_desc' => 'Thank you for your interest. Our team will review your requirements.',
        'thanks_expected' => 'Expected Response Time: We usually reply within 24 hours during business days.',
        'btn_back_home' => 'Back to Home', 'btn_view_more' => 'View More Products',
        'not_found_title' => 'Page Not Found', 'not_found_desc' => 'The page you are looking for does not exist.', 'btn_go_home' => 'Go Home',
        'about_tab_desc' => 'Product Description', 'about_tab_info' => 'Company Info.',
        'about_profile' => 'Company Profile', 'about_gen_info' => 'General Information', 'about_trade_cap' => 'Trade Capacity', 'about_rd_cap' => 'R&D Capacity',
        'about_corp_show' => 'Company Show', 'about_certificates' => 'Certificates', 'about_factory_tour' => 'Book a Factory Tour',
        'about_sgs_verified' => 'items verified by SGS', 'about_all_verified' => 'All information verified by SGS',
        'about_verify_now' => 'Verify Now', 'about_rating' => 'Rating', 'about_resp_time' => 'Avg. Response Time',
        'about_biz_type' => 'Business Type', 'about_main_products' => 'Main Products', 'about_year' => 'Year of Establishment', 'about_employees' => 'Number of Employees',
        'about_address' => 'Address', 'about_plant_area' => 'Plant Area', 'about_capital' => 'Registered Capital', 'about_sgs_report' => 'SGS Audit Report No.',
        'about_main_markets' => 'Main Markets', 'about_trade_staff' => 'Number of Trade Staff', 'about_incoterms' => 'Incoterms', 'about_payment' => 'Terms of Payment',
        'about_lead_time' => 'Average Lead Time', 'about_overseas' => 'Overseas Agent/Branch', 'about_export_year' => 'Export Year', 'about_port' => 'Nearest Port',
        'about_rd_engineers' => 'R&D Engineers', 'about_contact_provider' => 'Contact Provider', 'chat_now' => 'Chat Now',
        'product_price_tiers' => 'Tiered Pricing', 'product_pieces' => 'Pieces', 'product_sample_tip' => 'Still deciding? Get samples of', 'product_sample_btn' => 'Request Sample',
        'product_detail_title' => 'Product Details', 'detail_intro' => 'Product Description',
        'footer_company' => 'Company', 'footer_contact' => 'Contact', 'footer_rights' => 'All rights reserved.',
    ],
    'zh' => [
        'nav_home' => '首页', 'nav_products' => '产品', 'nav_cases' => '案例', 'nav_blog' => '博客', 'nav_contact' => '联系', 'nav_about' => '关于我们',
        'cta_quote' => '立即询价', 'btn_view_all' => '查看全部', 'list_read_more' => '查看详情',
        'products' => '产品中心', 'cases' => '成功案例', 'blog' => '新闻资讯',
        'home_quality_title' => '品质保障', 'home_quality_desc' => '符合国际标准的生产流程与出货前严格质检。',
        'home_logistics_title' => '全球物流', 'home_logistics_desc' => '准时交付，支持多种国际物流方案。',
        'home_support_title' => '专属服务', 'home_support_desc' => '一对一客户经理，全流程响应。',
        'home_highlights' => '公司亮点', 'home_why_us' => '为什么选择我们',
        'home_iso' => 'ISO 认证工厂', 'home_oem' => '支持 OEM/ODM', 'home_rd' => '专业研发团队', 'home_global' => '全球业务覆盖',
        'home_ready_title' => '准备好开始您的项目了吗？', 'home_ready_desc' => '立即联系我们，获取专业报价和咨询服务。',
        'section_featured_products' => '核心产品', 'section_success_cases' => '成功案例',
        'detail_send_inquiry' => '发送询单', 'btn_send_inquiry' => '提交询单',
        'form_name' => '姓名', 'form_email' => '邮箱', 'form_company' => '公司', 'form_phone' => '电话', 'form_message' => '留言', 'form_requirements' => '需求描述',
        'form_quantity' => '采购数量', 'form_name_full' => '您的姓名', 'form_email_full' => '电子邮箱', 'form_req_custom' => '具体需求 / 定制说明',
        'thanks_title' => '询单发送成功！', 'thanks_desc' => '感谢您的关注。我们的销售团队已收到您的信息，将尽快审核您的需求。',
        'thanks_expected' => '预计回复时间：我们通常会在 24 小时内（工作日）回复。',
        'btn_back_home' => '返回首页', 'btn_view_more' => '查看更多产品',
        'not_found_title' => '页面不存在', 'not_found_desc' => '您访问的页面不存在。', 'btn_go_home' => '返回首页',
        'about_tab_desc' => '产品描述', 'about_tab_info' => '公司信息',
        'about_profile' => '公司简介', 'about_gen_info' => '基本信息', 'about_trade_cap' => '贸易能力', 'about_rd_cap' => '研发能力',
        'about_corp_show' => '公司展示', 'about_certificates' => '资质证书', 'about_factory_tour' => '预约验厂',
        'about_sgs_verified' => '项信息已通过 SGS 认证', 'about_all_verified' => '所有信息均已通过 SGS 认证',
        'about_verify_now' => '立即验证', 'about_rating' => '综合评分', 'about_resp_time' => '平均响应时间',
        'about_biz_type' => '业务类型', 'about_main_products' => '主营产品', 'about_year' => '成立年份', 'about_employees' => '员工人数',
        'about_address' => '办公地址', 'about_plant_area' => '厂房面积', 'about_capital' => '注册资本', 'about_sgs_report' => 'SGS 审计报告编号',
        'about_main_markets' => '主要市场', 'about_trade_staff' => '外贸团队人数', 'about_incoterms' => '贸易条款', 'about_payment' => '付款方式',
        'about_lead_time' => '平均交期', 'about_overseas' => '海外代理/分支', 'about_export_year' => '出口开始年份', 'about_port' => '最近港口',
        'about_rd_engineers' => '研发工程师人数', 'about_contact_provider' => '联系供应商', 'chat_now' => '在线洽谈',
        'product_price_tiers' => '阶梯价格', 'product_pieces' => '件/个', 'product_sample_tip' => '还在犹豫？获取样品仅需', 'product_sample_btn' => '申请样品',
        'product_detail_title' => '产品详情', 'detail_intro' => '产品介绍',
        'footer_company' => '公司信息', 'footer_contact' => '联系我们', 'footer_rights' => '版权所有。',
    ],
];
