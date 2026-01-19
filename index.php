<?php
declare(strict_types=1);

// Single-file B2B website with SQLite + admin + theme system.

// -----------------------------
// Basic setup
// -----------------------------
session_start();
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

$baseDir = __DIR__;
$dataDir = $baseDir . '/data';
$themesDir = $baseDir . '/themes';
$dbFile = $dataDir . '/site.db';
$uploadsDir = $baseDir . '/uploads';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
if (!is_dir($themesDir)) {
    mkdir($themesDir, 0755, true);
}
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// -----------------------------
// Database
// -----------------------------
$db = new SQLite3($dbFile);
$db->exec('PRAGMA foreign_keys = ON;');
$db->exec('PRAGMA journal_mode = WAL;');

$db->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS settings (
    key TEXT PRIMARY KEY,
    value TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    summary TEXT,
    content TEXT,
    cover TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    summary TEXT,
    content TEXT,
    cover TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    summary TEXT,
    content TEXT,
    cover TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS inquiries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    company TEXT,
    phone TEXT,
    message TEXT,
    created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    company TEXT,
    phone TEXT,
    message TEXT,
    created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS product_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    url TEXT NOT NULL,
    sort INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS product_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
");

function ensure_column(SQLite3 $db, string $table, string $column, string $type): void {
    $res = $db->query("PRAGMA table_info($table)");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        if ($row['name'] === $column) {
            return;
        }
    }
    $db->exec("ALTER TABLE $table ADD COLUMN $column $type");
}

ensure_column($db, 'products', 'category_id', 'INTEGER');

// Seed default admin
$adminCount = (int)$db->querySingle("SELECT COUNT(*) FROM users");
if ($adminCount === 0) {
    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password_hash, created_at) VALUES (:u, :p, :c)");
    $stmt->bindValue(':u', 'admin', SQLITE3_TEXT);
    $stmt->bindValue(':p', $passwordHash, SQLITE3_TEXT);
    $stmt->bindValue(':c', gmdate('c'), SQLITE3_TEXT);
    $stmt->execute();
}

// Default settings
function setting_get(SQLite3 $db, string $key, string $default = ''): string {
    $stmt = $db->prepare("SELECT value FROM settings WHERE key = :k");
    $stmt->bindValue(':k', $key, SQLITE3_TEXT);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    return $row ? $row['value'] : $default;
}

function setting_set(SQLite3 $db, string $key, string $value): void {
    $stmt = $db->prepare("INSERT INTO settings (key, value) VALUES (:k, :v)
        ON CONFLICT(key) DO UPDATE SET value = excluded.value");
    $stmt->bindValue(':k', $key, SQLITE3_TEXT);
    $stmt->bindValue(':v', $value, SQLITE3_TEXT);
    $stmt->execute();
}

if (setting_get($db, 'site_name', '') === '') {
    setting_set($db, 'site_name', 'Global B2B Solutions');
    setting_set($db, 'site_tagline', 'Trusted manufacturing partner for global buyers');
    setting_set($db, 'company_about', 'We are a manufacturing and exporting company focused on quality, compliance, and fast delivery for global B2B clients.');
    setting_set($db, 'company_address', 'No. 88, Industrial Park, Shenzhen, China');
    setting_set($db, 'company_email', 'sales@example.com');
    setting_set($db, 'company_phone', '+86-000-0000-0000');
    setting_set($db, 'theme', 'default');
    setting_set($db, 'default_lang', 'en');
    setting_set($db, 'whatsapp', '');
}

// -----------------------------
// Helpers
// -----------------------------
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text ?? '', '-');
    return $text === '' ? 'item' . time() : $text;
}

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function current_path(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    if (!$path) {
        $path = '/';
    }
    return $path;
}

function is_admin(): bool {
    return isset($_SESSION['admin_user']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: /admin/login');
        exit;
    }
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

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function validate_image_upload(array $file): array {
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
    $mime = $info['mime'] ?? '';
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($extMap[$mime])) {
        return [false, '不支持的图片格式'];
    }
    return [true, $extMap[$mime]];
}

function save_uploaded_image(array $file): array {
    [$ok, $extOrError] = validate_image_upload($file);
    if (!$ok) {
        return [false, $extOrError];
    }
    $ext = $extOrError;
    $subDir = '/uploads/' . date('Ym');
    $targetDir = __DIR__ . $subDir;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $filename = uniqid('img_', true) . '.' . $ext;
    $targetPath = $targetDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [false, '保存失败'];
    }
    return [true, $subDir . '/' . $filename];
}

function delete_uploaded_image(string $url): void {
    if (strpos($url, '/uploads/') !== 0) {
        return;
    }
    $path = __DIR__ . $url;
    if (is_file($path)) {
        @unlink($path);
    }
}

function normalize_files_array(array $files): array {
    $normalized = [];
    $names = $files['name'] ?? [];
    if (!is_array($names)) {
        return $normalized;
    }
    $count = count($names);
    for ($i = 0; $i < $count; $i++) {
        if ($names[$i] === '') {
            continue;
        }
        $normalized[] = [
            'name' => $files['name'][$i] ?? '',
            'type' => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error' => $files['error'][$i] ?? 0,
            'size' => $files['size'][$i] ?? 0,
        ];
    }
    return $normalized;
}

function admin_error(string $message): void {
    admin_page('操作失败', '<div class="notification is-danger is-light">' . h($message) . '</div>', true);
    exit;
}

function fetch_categories(SQLite3 $db): array {
    $list = [];
    $res = $db->query("SELECT id,name,slug FROM product_categories ORDER BY id DESC");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $list[] = $row;
    }
    return $list;
}

function process_product_images(SQLite3 $db, int $productId, int $existingCount, bool $requireAtLeastOne): void {
    $files = $_FILES['images'] ?? null;
    if (!$files || !isset($files['name'])) {
        if ($requireAtLeastOne) {
            admin_error('请上传 1-6 张产品图片');
        }
        return;
    }
    $list = normalize_files_array($files);
    $count = count($list);
    if ($count === 0) {
        if ($requireAtLeastOne) {
            admin_error('请上传 1-6 张产品图片');
        }
        return;
    }
    if ($count < 1 || $count > 6) {
        admin_error('产品图片数量需为 1-6 张');
    }
    if ($existingCount + $count > 6) {
        admin_error('产品图片总数不能超过 6 张');
    }
    $sort = $existingCount;
    foreach ($list as $file) {
        [$ok, $result] = save_uploaded_image($file);
        if (!$ok) {
            admin_error($result);
        }
        $stmt = $db->prepare("INSERT INTO product_images (product_id,url,sort,created_at) VALUES (:pid,:url,:sort,:t)");
        $stmt->bindValue(':pid', $productId, SQLITE3_INTEGER);
        $stmt->bindValue(':url', $result, SQLITE3_TEXT);
        $stmt->bindValue(':sort', $sort, SQLITE3_INTEGER);
        $stmt->bindValue(':t', gmdate('c'), SQLITE3_TEXT);
        $stmt->execute();
        $sort++;
    }
}

function available_languages(): array {
    return [
        'en' => 'English',
        'zh' => '中文',
    ];
}

function current_lang(SQLite3 $db): string {
    $langs = available_languages();
    if (isset($_GET['lang']) && isset($langs[$_GET['lang']])) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    $lang = $_SESSION['lang'] ?? setting_get($db, 'default_lang', 'en');
    if (!isset($langs[$lang])) {
        $lang = 'en';
    }
    return $lang;
}

$translations = [
    'en' => [
        'nav_home' => 'Home',
        'nav_products' => 'Products',
        'nav_cases' => 'Cases',
        'nav_blog' => 'Blog',
        'nav_contact' => 'Contact',
        'cta_quote' => 'Request a Quote',
        'home_quality_title' => 'Quality Assurance',
        'home_quality_desc' => 'ISO-aligned production with strict QC before shipment.',
        'home_logistics_title' => 'Global Logistics',
        'home_logistics_desc' => 'On-time delivery with consolidated freight options.',
        'home_support_title' => 'Dedicated Support',
        'home_support_desc' => 'One-to-one account service for long-term buyers.',
        'section_featured_products' => 'Featured Products',
        'section_success_cases' => 'Success Cases',
        'btn_view_all' => 'View All',
        'list_read_more' => 'Read More',
        'detail_send_inquiry' => 'Send Inquiry',
        'form_name' => 'Name',
        'form_email' => 'Email',
        'form_company' => 'Company',
        'form_phone' => 'Phone',
        'form_message' => 'Message',
        'form_requirements' => 'Requirements',
        'btn_send_inquiry' => 'Send Inquiry',
        'contact_title' => 'Contact Us',
        'contact_message' => 'Send Message',
        'thanks_title' => 'Thank you',
        'thanks_desc' => 'We have received your request and will reply within 24 hours.',
        'btn_back_home' => 'Back to Home',
        'not_found_title' => 'Page Not Found',
        'not_found_desc' => 'The page you are looking for does not exist.',
        'btn_go_home' => 'Go Home',
        'products' => 'Products',
        'cases' => 'Success Cases',
        'blog' => 'Blog',
    ],
    'zh' => [
        'nav_home' => '首页',
        'nav_products' => '产品',
        'nav_cases' => '案例',
        'nav_blog' => '博客',
        'nav_contact' => '联系',
        'cta_quote' => '立即询价',
        'home_quality_title' => '品质保障',
        'home_quality_desc' => '符合国际标准的生产流程与出货前严格质检。',
        'home_logistics_title' => '全球物流',
        'home_logistics_desc' => '准时交付，支持多种国际物流方案。',
        'home_support_title' => '专属服务',
        'home_support_desc' => '一对一客户经理，全流程响应。',
        'section_featured_products' => '核心产品',
        'section_success_cases' => '成功案例',
        'btn_view_all' => '查看全部',
        'list_read_more' => '查看详情',
        'detail_send_inquiry' => '发送询单',
        'form_name' => '姓名',
        'form_email' => '邮箱',
        'form_company' => '公司',
        'form_phone' => '电话',
        'form_message' => '留言',
        'form_requirements' => '需求描述',
        'btn_send_inquiry' => '提交询单',
        'contact_title' => '联系我们',
        'contact_message' => '提交留言',
        'thanks_title' => '提交成功',
        'thanks_desc' => '我们已收到您的请求，将在 24 小时内回复。',
        'btn_back_home' => '返回首页',
        'not_found_title' => '页面不存在',
        'not_found_desc' => '您访问的页面不存在。',
        'btn_go_home' => '返回首页',
        'products' => '产品',
        'cases' => '成功案例',
        'blog' => '博客',
    ],
];

function t(string $key, ?string $lang = null): string {
    $lang = $lang ?? ($GLOBALS['current_lang'] ?? 'en');
    $translations = $GLOBALS['translations'] ?? [];
    if (isset($translations[$lang][$key])) {
        return $translations[$lang][$key];
    }
    return $translations['en'][$key] ?? $key;
}

function lang_switch_url(string $lang): string {
    $path = current_path();
    $query = $_GET;
    $query['lang'] = $lang;
    return $path . '?' . http_build_query($query);
}

$GLOBALS['current_lang'] = current_lang($db);

function admin_nav_html(): string {
    $links = [
        '/admin' => '仪表盘',
        '/admin/products' => '产品',
        '/admin/categories' => '产品分类',
        '/admin/cases' => '案例',
        '/admin/posts' => '博客',
        '/admin/messages' => '留言',
        '/admin/inquiries' => '询单',
        '/admin/settings' => '设置',
    ];
    $html = '<div class="navbar-menu is-active"><div class="navbar-start">';
    foreach ($links as $url => $label) {
        $html .= '<a class="navbar-item" href="' . h($url) . '">' . h($label) . '</a>';
    }
    $html .= '</div><div class="navbar-end"><div class="navbar-item"><a class="button is-light" href="/admin/logout">退出登录</a></div></div></div>';
    return $html;
}

function admin_page(string $title, string $content, bool $showNav = true): void {
    $siteName = setting_get($GLOBALS['db'], 'site_name', 'B2B Company');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">';
    echo '<style>body{background:#f5f7fb}.admin-card{box-shadow:0 10px 30px rgba(15,23,42,0.08)}</style>';
    echo '</head><body>';
    if ($showNav) {
        echo '<nav class="navbar is-white is-spaced"><div class="container">';
        echo '<div class="navbar-brand"><a class="navbar-item" href="/admin"><strong> 管理后台 </strong></a></div>';
        echo admin_nav_html();
        echo '</div></nav>';
    }
    echo '<div class="admin-page" style="min-height: 700px;"> <section class="section"><div class="container">';
    echo $content;
    echo '</div></section></div>';
    echo '<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>';
    echo '<script>
      document.addEventListener("DOMContentLoaded", function () {
        const editor = document.getElementById("quill-editor");
        const input = document.getElementById("content-input");
        if (!editor || !input) return;
        const csrfToken = ' . json_encode(csrf_token(), JSON_UNESCAPED_UNICODE) . ';
        const quill = new Quill(editor, {
          theme: "snow",
          placeholder: "请输入内容...",
          modules: {
            toolbar: [
              [{ header: [1, 2, 3, false] }],
              ["bold", "italic", "underline", "strike"],
              [{ list: "ordered" }, { list: "bullet" }],
              ["blockquote", "code-block"],
              ["link", "image"],
              ["clean"]
            ]
          }
        });
        const toolbar = quill.getModule("toolbar");
        toolbar.addHandler("image", function () {
          const inputFile = document.createElement("input");
          inputFile.type = "file";
          inputFile.accept = "image/*";
          inputFile.click();
          inputFile.addEventListener("change", function () {
            const file = inputFile.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append("image", file);
            formData.append("csrf", csrfToken);
            fetch("/admin/upload-image", { method: "POST", body: formData })
              .then(res => res.json())
              .then(data => {
                if (!data || !data.url) {
                  alert(data && data.error ? data.error : "上传失败");
                  return;
                }
                const range = quill.getSelection(true);
                quill.insertEmbed(range ? range.index : 0, "image", data.url, "user");
              })
              .catch(() => alert("上传失败"));
          });
        });
        quill.root.innerHTML = input.value || "";
        const form = input.closest("form");
        if (form) {
          form.addEventListener("submit", function () {
            input.value = quill.root.innerHTML;
          });
        }

        const imageInput = document.querySelector("input[name=\'images[]\']");
        const preview = document.getElementById("product-image-preview");
        if (imageInput && preview) {
          imageInput.addEventListener("change", function () {
            preview.innerHTML = "";
            const files = Array.from(imageInput.files || []);
            files.slice(0, 6).forEach(file => {
              const reader = new FileReader();
              reader.onload = function (e) {
                const div = document.createElement("div");
                div.className = "column is-3";
                div.innerHTML = "<figure class=\\"image is-4by3\\"><img src=\\"" + e.target.result + "\\"></figure>";
                preview.appendChild(div);
              };
              reader.readAsDataURL(file);
            });
          });
        }
      });
    </script>';
    echo '</body></html>';
}

function render(SQLite3 $db, string $view, array $data = []): void {
    $theme = setting_get($db, 'theme', 'default');
    $themesDir = __DIR__ . '/themes';
    $themePath = $themesDir . '/' . $theme;
    if (!is_dir($themePath)) {
        $themePath = $themesDir . '/default';
    }

    $site = [
        'name' => setting_get($db, 'site_name', 'B2B Company'),
        'tagline' => setting_get($db, 'site_tagline', ''),
        'about' => setting_get($db, 'company_about', ''),
        'address' => setting_get($db, 'company_address', ''),
        'email' => setting_get($db, 'company_email', ''),
        'phone' => setting_get($db, 'company_phone', ''),
        'whatsapp' => setting_get($db, 'whatsapp', ''),
    ];

    $seo = $data['seo'] ?? [];
    $canonical = base_url() . current_path();
    if (isset($_GET['lang']) && $_GET['lang'] !== '') {
        $canonical .= '?lang=' . rawurlencode((string)$_GET['lang']);
    }
    $seo = array_merge([
        'title' => $site['name'],
        'description' => $site['tagline'],
        'canonical' => $canonical,
    ], $seo);

    $lang = $GLOBALS['current_lang'] ?? 'en';
    $languages = available_languages();

    $data['lang'] = $lang;
    $data['languages'] = $languages;

    extract($data, EXTR_SKIP);
    include $themePath . '/header.php';
    $viewFile = $themePath . '/' . $view . '.php';
    if (is_file($viewFile)) {
        include $viewFile;
    } else {
        echo '<p>Missing view: ' . h($view) . '</p>';
    }
    include $themePath . '/footer.php';
}

// -----------------------------
// SEO endpoints
// -----------------------------
if (current_path() === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nAllow: /\nSitemap: " . base_url() . "/sitemap.xml\n";
    exit;
}

if (current_path() === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $urls = [];
    $urls[] = base_url() . '/';
    $urls[] = base_url() . '/products';
    $urls[] = base_url() . '/cases';
    $urls[] = base_url() . '/blog';
    $urls[] = base_url() . '/contact';
    $res = $db->query("SELECT slug FROM products");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $urls[] = base_url() . '/product/' . $row['slug'];
    }
    $res = $db->query("SELECT slug FROM cases");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $urls[] = base_url() . '/case/' . $row['slug'];
    }
    $res = $db->query("SELECT slug FROM posts");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $urls[] = base_url() . '/blog/' . $row['slug'];
    }
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($urls as $u) {
        echo "  <url><loc>" . h($u) . "</loc></url>\n";
    }
    echo "</urlset>";
    exit;
}

// -----------------------------
// Routing (pseudo-static)
// -----------------------------
$path = current_path();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Support /index.php?r=/path
if ($path === '/index.php' && isset($_GET['r'])) {
    $path = '/' . ltrim((string)$_GET['r'], '/');
}

// Home
if ($path === '/' || $path === '') {
    $products = [];
    $cases = [];
    $res = $db->query("SELECT id,title,slug,summary FROM products ORDER BY id DESC LIMIT 6");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $products[] = $row;
    }
    $res = $db->query("SELECT id,title,slug,summary FROM cases ORDER BY id DESC LIMIT 6");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $cases[] = $row;
    }
    render($db, 'home', [
        'products' => $products,
        'cases' => $cases,
        'seo' => [
            'title' => setting_get($db, 'site_name'),
            'description' => setting_get($db, 'site_tagline'),
        ],
    ]);
    exit;
}

// Products list
if ($path === '/products') {
    $items = [];
    $res = $db->query("SELECT products.title,products.slug,products.summary,
        product_categories.name AS category_name,
        (SELECT url FROM product_images WHERE product_id = products.id ORDER BY sort ASC, id ASC LIMIT 1) AS cover
        FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
        ORDER BY products.id DESC");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $row['url'] = '/product/' . $row['slug'];
        $items[] = $row;
    }
    render($db, 'list', [
        'title' => t('products'),
        'items' => $items,
        'show_category' => true,
        'show_image' => true,
        'seo' => [
            'title' => t('products') . ' - ' . setting_get($db, 'site_name'),
            'description' => setting_get($db, 'site_tagline'),
        ],
    ]);
    exit;
}

// Cases list
if ($path === '/cases') {
    $items = [];
    $res = $db->query("SELECT title,slug,summary FROM cases ORDER BY id DESC");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $row['url'] = '/case/' . $row['slug'];
        $items[] = $row;
    }
    render($db, 'list', [
        'title' => t('cases'),
        'items' => $items,
        'seo' => [
            'title' => t('cases') . ' - ' . setting_get($db, 'site_name'),
            'description' => setting_get($db, 'site_tagline'),
        ],
    ]);
    exit;
}

// Blog list
if ($path === '/blog') {
    $items = [];
    $res = $db->query("SELECT title,slug,summary FROM posts ORDER BY id DESC");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $row['url'] = '/blog/' . $row['slug'];
        $items[] = $row;
    }
    render($db, 'list', [
        'title' => t('blog'),
        'items' => $items,
        'seo' => [
            'title' => t('blog') . ' - ' . setting_get($db, 'site_name'),
            'description' => setting_get($db, 'site_tagline'),
        ],
    ]);
    exit;
}

// Product detail
if (preg_match('#^/product/([a-z0-9\\-]+)$#', $path, $m)) {
    $slug = $m[1];
    $stmt = $db->prepare("SELECT products.*, product_categories.name AS category_name
        FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
        WHERE products.slug = :s");
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $res = $stmt->execute();
    $item = $res->fetchArray(SQLITE3_ASSOC);
    if ($item) {
        $images = [];
        $stmtImg = $db->prepare("SELECT url FROM product_images WHERE product_id = :pid ORDER BY sort ASC, id ASC");
        $stmtImg->bindValue(':pid', (int)$item['id'], SQLITE3_INTEGER);
        $resImg = $stmtImg->execute();
        while ($row = $resImg->fetchArray(SQLITE3_ASSOC)) {
            $images[] = $row['url'];
        }
        render($db, 'detail', [
            'item' => $item,
            'images' => $images,
            'whatsapp' => setting_get($db, 'whatsapp', ''),
            'inquiry_form' => true,
            'seo' => [
                'title' => $item['title'] . ' - ' . setting_get($db, 'site_name'),
                'description' => $item['summary'] ?: setting_get($db, 'site_tagline'),
            ],
        ]);
        exit;
    }
}

// Case detail
if (preg_match('#^/case/([a-z0-9\\-]+)$#', $path, $m)) {
    $slug = $m[1];
    $stmt = $db->prepare("SELECT * FROM cases WHERE slug = :s");
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $res = $stmt->execute();
    $item = $res->fetchArray(SQLITE3_ASSOC);
    if ($item) {
        render($db, 'detail', [
            'item' => $item,
            'seo' => [
                'title' => $item['title'] . ' - ' . setting_get($db, 'site_name'),
                'description' => $item['summary'] ?: setting_get($db, 'site_tagline'),
            ],
        ]);
        exit;
    }
}

// Blog detail
if (preg_match('#^/blog/([a-z0-9\\-]+)$#', $path, $m)) {
    $slug = $m[1];
    $stmt = $db->prepare("SELECT * FROM posts WHERE slug = :s");
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $res = $stmt->execute();
    $item = $res->fetchArray(SQLITE3_ASSOC);
    if ($item) {
        render($db, 'detail', [
            'item' => $item,
            'seo' => [
                'title' => $item['title'] . ' - ' . setting_get($db, 'site_name'),
                'description' => $item['summary'] ?: setting_get($db, 'site_tagline'),
            ],
        ]);
        exit;
    }
}

// Contact
if ($path === '/contact' && $method === 'GET') {
    render($db, 'contact', [
        'seo' => [
            'title' => 'Contact - ' . setting_get($db, 'site_name'),
            'description' => setting_get($db, 'site_tagline'),
        ],
    ]);
    exit;
}
if ($path === '/contact' && $method === 'POST') {
    csrf_check();
    $stmt = $db->prepare("INSERT INTO messages (name,email,company,phone,message,created_at)
        VALUES (:n,:e,:c,:p,:m,:t)");
    $stmt->bindValue(':n', trim((string)$_POST['name']), SQLITE3_TEXT);
    $stmt->bindValue(':e', trim((string)$_POST['email']), SQLITE3_TEXT);
    $stmt->bindValue(':c', trim((string)($_POST['company'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':p', trim((string)($_POST['phone'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':m', trim((string)$_POST['message']), SQLITE3_TEXT);
    $stmt->bindValue(':t', gmdate('c'), SQLITE3_TEXT);
    $stmt->execute();
    render($db, 'thanks', []);
    exit;
}

// Inquiry
if ($path === '/inquiry' && $method === 'POST') {
    csrf_check();
    $stmt = $db->prepare("INSERT INTO inquiries (product_id,name,email,company,phone,message,created_at)
        VALUES (:pid,:n,:e,:c,:p,:m,:t)");
    $stmt->bindValue(':pid', (int)($_POST['product_id'] ?? 0), SQLITE3_INTEGER);
    $stmt->bindValue(':n', trim((string)$_POST['name']), SQLITE3_TEXT);
    $stmt->bindValue(':e', trim((string)$_POST['email']), SQLITE3_TEXT);
    $stmt->bindValue(':c', trim((string)($_POST['company'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':p', trim((string)($_POST['phone'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':m', trim((string)($_POST['message'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':t', gmdate('c'), SQLITE3_TEXT);
    $stmt->execute();
    render($db, 'thanks', []);
    exit;
}

// -----------------------------
// Admin
// -----------------------------
if ($path === '/admin/login' && $method === 'GET') {
    ob_start();
    echo '<div class="columns is-centered"><div class="column is-4">';
    echo '<div class="box admin-card">';
    echo '<h1 class="title is-4">后台登录</h1>';
    echo '<form method="post" action="/admin/login">';
    echo '<div class="field"><label class="label">用户名</label><div class="control"><input class="input" name="username" required></div></div>';
    echo '<div class="field"><label class="label">密码</label><div class="control"><input class="input" type="password" name="password" required></div></div>';
    echo '<button class="button is-link is-fullwidth" type="submit">登录</button>';
    echo '</form></div></div></div>';
    $content = ob_get_clean();
    admin_page('后台登录', $content, false);
    exit;
}
if ($path === '/admin/login' && $method === 'POST') {
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :u");
    $stmt->bindValue(':u', trim((string)$_POST['username']), SQLITE3_TEXT);
    $res = $stmt->execute();
    $user = $res->fetchArray(SQLITE3_ASSOC);
    if ($user && password_verify((string)$_POST['password'], $user['password_hash'])) {
        $_SESSION['admin_user'] = $user['username'];
        header('Location: /admin');
        exit;
    }
    ob_start();
    echo '<div class="columns is-centered"><div class="column is-4">';
    echo '<div class="notification is-danger is-light">登录失败，请重试。</div>';
    echo '<div class="box admin-card">';
    echo '<h1 class="title is-4">后台登录</h1>';
    echo '<form method="post" action="/admin/login">';
    echo '<div class="field"><label class="label">用户名</label><div class="control"><input class="input" name="username" required></div></div>';
    echo '<div class="field"><label class="label">密码</label><div class="control"><input class="input" type="password" name="password" required></div></div>';
    echo '<button class="button is-link is-fullwidth" type="submit">登录</button>';
    echo '</form></div></div></div>';
    $content = ob_get_clean();
    admin_page('后台登录', $content, false);
    exit;
}
if ($path === '/admin/logout') {
    session_destroy();
    header('Location: /admin/login');
    exit;
}

if ($path === '/admin/upload-image' && $method === 'POST') {
    require_admin();
    csrf_check();
    if (!isset($_FILES['image'])) {
        json_response(['error' => '未选择文件'], 400);
    }
    $file = $_FILES['image'];
    [$ok, $result] = save_uploaded_image($file);
    if (!$ok) {
        json_response(['error' => $result], 400);
    }
    json_response(['url' => $result]);
}

if ($path === '/admin') {
    require_admin();
    $counts = [
        'products' => (int)$db->querySingle("SELECT COUNT(*) FROM products"),
        'cases' => (int)$db->querySingle("SELECT COUNT(*) FROM cases"),
        'posts' => (int)$db->querySingle("SELECT COUNT(*) FROM posts"),
        'messages' => (int)$db->querySingle("SELECT COUNT(*) FROM messages"),
        'inquiries' => (int)$db->querySingle("SELECT COUNT(*) FROM inquiries"),
    ];
    ob_start();
    echo '<h1 class="title is-3">仪表盘</h1>';
    echo '<div class="columns is-multiline">';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">产品</p><p class="title is-4">' . $counts['products'] . '</p><a href="/admin/products" class="button is-small is-link is-light">管理</a></div></div>';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">案例</p><p class="title is-4">' . $counts['cases'] . '</p><a href="/admin/cases" class="button is-small is-link is-light">管理</a></div></div>';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">博客</p><p class="title is-4">' . $counts['posts'] . '</p><a href="/admin/posts" class="button is-small is-link is-light">管理</a></div></div>';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">留言</p><p class="title is-4">' . $counts['messages'] . '</p><a href="/admin/messages" class="button is-small is-link is-light">查看</a></div></div>';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">询单</p><p class="title is-4">' . $counts['inquiries'] . '</p><a href="/admin/inquiries" class="button is-small is-link is-light">查看</a></div></div>';
    echo '</div>';
    $content = ob_get_clean();
    admin_page('仪表盘', $content, true);
    exit;
}

// Admin settings
if ($path === '/admin/settings' && $method === 'GET') {
    require_admin();
    $theme = setting_get($db, 'theme', 'default');
    $langs = available_languages();
    $defaultLang = setting_get($db, 'default_lang', 'en');
    ob_start();
    echo '<h1 class="title is-3">设置</h1>';
    echo '<div class="box admin-card"><form method="post" action="/admin/settings">';
    echo '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
    echo '<div class="columns">';
    echo '<div class="column"><div class="field"><label class="label">网站名称</label><div class="control"><input class="input" name="site_name" value="' . h(setting_get($db, 'site_name')) . '"></div></div></div>';
    echo '<div class="column"><div class="field"><label class="label">标语</label><div class="control"><input class="input" name="site_tagline" value="' . h(setting_get($db, 'site_tagline')) . '"></div></div></div>';
    echo '</div>';
    echo '<div class="field"><label class="label">公司简介</label><div class="control"><textarea class="textarea" name="company_about" rows="4">' . h(setting_get($db, 'company_about')) . '</textarea></div></div>';
    echo '<div class="columns">';
    echo '<div class="column"><div class="field"><label class="label">地址</label><div class="control"><input class="input" name="company_address" value="' . h(setting_get($db, 'company_address')) . '"></div></div></div>';
    echo '<div class="column"><div class="field"><label class="label">邮箱</label><div class="control"><input class="input" name="company_email" value="' . h(setting_get($db, 'company_email')) . '"></div></div></div>';
    echo '<div class="column"><div class="field"><label class="label">电话</label><div class="control"><input class="input" name="company_phone" value="' . h(setting_get($db, 'company_phone')) . '"></div></div></div>';
    echo '</div>';
    echo '<div class="columns">';
    echo '<div class="column"><div class="field"><label class="label">主题</label><div class="control"><input class="input" name="theme" value="' . h($theme) . '"></div><p class="help">主题目录位于 /themes</p></div></div>';
    echo '<div class="column"><div class="field"><label class="label">默认语言</label><div class="control"><div class="select is-fullwidth"><select name="default_lang">';
    foreach ($langs as $code => $label) {
        $selected = $code === $defaultLang ? ' selected' : '';
        echo '<option value="' . h($code) . '"' . $selected . '>' . h($label) . '</option>';
    }
    echo '</select></div></div></div></div>';
    echo '</div>';
    echo '<div class="field"><label class="label">WhatsApp 账号</label><div class="control"><input class="input" name="whatsapp" value="' . h(setting_get($db, 'whatsapp')) . '"></div><p class="help">建议填写国际格式，如 +8613812345678</p></div>';
    echo '<button class="button is-link" type="submit">保存设置</button>';
    echo '</form></div>';
    $content = ob_get_clean();
    admin_page('设置', $content, true);
    exit;
}
if ($path === '/admin/settings' && $method === 'POST') {
    require_admin();
    csrf_check();
    $keys = ['site_name','site_tagline','company_about','company_address','company_email','company_phone','theme','default_lang','whatsapp'];
    foreach ($keys as $k) {
        setting_set($db, $k, trim((string)($_POST[$k] ?? '')));
    }
    header('Location: /admin/settings');
    exit;
}

// Admin CRUD helper
function admin_list(SQLite3 $db, string $table, string $label, string $basePath): void {
    require_admin();
    if ($table === 'products') {
        $res = $db->query("SELECT products.id,products.title,products.slug,products.created_at, product_categories.name AS category_name
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
            ORDER BY products.id DESC");
    } else {
        $res = $db->query("SELECT id,title,slug,created_at FROM $table ORDER BY id DESC");
    }
    ob_start();
    echo '<div class="level"><div class="level-left"><h1 class="title is-3">' . h($label) . '</h1></div>';
    echo '<div class="level-right"><a class="button is-link" href="' . h($basePath) . '/create">新建</a></div></div>';
    echo '<div class="box admin-card"><table class="table is-fullwidth is-striped">';
    if ($table === 'products') {
        echo '<thead><tr><th>标题</th><th>分类</th><th>别名</th><th>创建时间</th><th>操作</th></tr></thead><tbody>';
    } else {
        echo '<thead><tr><th>标题</th><th>别名</th><th>创建时间</th><th>操作</th></tr></thead><tbody>';
    }
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        echo '<tr>';
        echo '<td>' . h($row['title']) . '</td>';
        if ($table === 'products') {
            echo '<td>' . h($row['category_name'] ?? '未分类') . '</td>';
        }
        echo '<td>' . h($row['slug']) . '</td>';
        echo '<td>' . h($row['created_at']) . '</td>';
        echo '<td><a class="button is-small is-light" href="' . h($basePath) . '/edit?id=' . (int)$row['id'] . '">编辑</a> ';
        echo '<a class="button is-small is-danger is-light" href="' . h($basePath) . '/delete?id=' . (int)$row['id'] . '" onclick="return confirm(\'确认删除？\')">删除</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    $content = ob_get_clean();
    admin_page($label, $content, true);
}

function admin_form(string $action, array $item = [], array $options = []): void {
    $title = $item['title'] ?? '';
    $slug = $item['slug'] ?? '';
    $summary = $item['summary'] ?? '';
    $content = $item['content'] ?? '';
    echo '<form method="post" action="' . h($action) . '" enctype="multipart/form-data">';
    echo '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
    echo '<div class="columns">';
    echo '<div class="column"><div class="field"><label class="label">标题</label><div class="control"><input class="input" name="title" value="' . h($title) . '" required></div></div></div>';
    echo '<div class="column"><div class="field"><label class="label">别名</label><div class="control"><input class="input" name="slug" value="' . h($slug) . '"></div><p class="help">留空自动生成</p></div></div></div>';
    echo '</div>';
    echo '<div class="field"><label class="label">摘要</label><div class="control"><textarea class="textarea" name="summary" rows="3">' . h($summary) . '</textarea></div></div>';
    if (($options['type'] ?? '') === 'product') {
        $categories = fetch_categories($GLOBALS['db']);
        $currentCategory = (int)($item['category_id'] ?? 0);
        echo '<div class="field"><label class="label">产品分类</label>';
        echo '<div class="control"><div class="select is-fullwidth"><select name="category_id">';
        echo '<option value="0">未分类</option>';
        foreach ($categories as $cat) {
            $selected = ((int)$cat['id'] === $currentCategory) ? ' selected' : '';
            echo '<option value="' . (int)$cat['id'] . '"' . $selected . '>' . h($cat['name']) . '</option>';
        }
        echo '</select></div></div>';
        echo '<p class="help">如需新增分类，请先到「产品分类」管理</p>';
        echo '</div>';
    }
    echo '<div class="field"><label class="label">内容</label>';
    echo '<div class="control">';
    echo '<textarea id="content-input" class="textarea" name="content" rows="10" style="display:none">' . h($content) . '</textarea>';
    echo '<div id="quill-editor" style="height:360px;background:#fff"></div>';
    echo '</div></div>';
    if (($options['type'] ?? '') === 'product') {
        $images = $options['images'] ?? [];
        echo '<div class="field"><label class="label">产品图片（1-6 张）</label>';
        echo '<div class="control"><input class="input" type="file" name="images[]" accept="image/*" multiple></div>';
        echo '<div class="columns is-multiline" id="product-image-preview" style="margin-top:8px"></div>';
        if (!empty($images)) {
            echo '<p class="help">已上传图片，勾选可删除：</p>';
            echo '<div class="columns is-multiline">';
            foreach ($images as $img) {
                echo '<div class="column is-3">';
                echo '<div class="box">';
                echo '<figure class="image is-4by3"><img src="' . h($img['url']) . '" alt=""></figure>';
                echo '<label class="checkbox"><input type="checkbox" name="remove_images[]" value="' . (int)$img['id'] . '"> 删除</label>';
                echo '</div></div>';
            }
            echo '</div>';
        }
        echo '<p class="help">如不上传新图片，将保留现有图片。</p>';
        echo '</div>';
    }
    echo '<button class="button is-link" type="submit">保存</button>';
    echo '</form>';
}

function admin_category_form(string $action, array $item = []): void {
    $name = $item['name'] ?? '';
    $slug = $item['slug'] ?? '';
    echo '<form method="post" action="' . h($action) . '">';
    echo '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
    echo '<div class="columns">';
    echo '<div class="column"><div class="field"><label class="label">分类名称</label><div class="control"><input class="input" name="name" value="' . h($name) . '" required></div></div></div>';
    echo '<div class="column"><div class="field"><label class="label">别名</label><div class="control"><input class="input" name="slug" value="' . h($slug) . '"></div><p class="help">留空自动生成</p></div></div></div>';
    echo '</div>';
    echo '<button class="button is-link" type="submit">保存</button>';
    echo '</form>';
}

function admin_category_list(SQLite3 $db): void {
    require_admin();
    $res = $db->query("SELECT id,name,slug,created_at FROM product_categories ORDER BY id DESC");
    ob_start();
    echo '<div class="level"><div class="level-left"><h1 class="title is-3">产品分类</h1></div>';
    echo '<div class="level-right"><a class="button is-link" href="/admin/categories/create">新建</a></div></div>';
    echo '<div class="box admin-card"><table class="table is-fullwidth is-striped">';
    echo '<thead><tr><th>名称</th><th>别名</th><th>创建时间</th><th>操作</th></tr></thead><tbody>';
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        echo '<tr>';
        echo '<td>' . h($row['name']) . '</td>';
        echo '<td>' . h($row['slug']) . '</td>';
        echo '<td>' . h($row['created_at']) . '</td>';
        echo '<td><a class="button is-small is-light" href="/admin/categories/edit?id=' . (int)$row['id'] . '">编辑</a> ';
        echo '<a class="button is-small is-danger is-light" href="/admin/categories/delete?id=' . (int)$row['id'] . '" onclick="return confirm(\'确认删除？\')">删除</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    $content = ob_get_clean();
    admin_page('产品分类', $content, true);
}

function admin_category_store(SQLite3 $db): void {
    require_admin();
    csrf_check();
    $name = trim((string)$_POST['name']);
    $slug = trim((string)($_POST['slug'] ?? ''));
    if ($slug === '') {
        $slug = slugify($name);
    }
    $stmt = $db->prepare("INSERT INTO product_categories (name,slug,created_at,updated_at)
        VALUES (:n,:s,:ca,:ua)");
    $stmt->bindValue(':n', $name, SQLITE3_TEXT);
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $stmt->bindValue(':ca', gmdate('c'), SQLITE3_TEXT);
    $stmt->bindValue(':ua', gmdate('c'), SQLITE3_TEXT);
    $stmt->execute();
}

function admin_category_update(SQLite3 $db): void {
    require_admin();
    csrf_check();
    $id = (int)($_GET['id'] ?? 0);
    $name = trim((string)$_POST['name']);
    $slug = trim((string)($_POST['slug'] ?? ''));
    if ($slug === '') {
        $slug = slugify($name);
    }
    $stmt = $db->prepare("UPDATE product_categories SET name=:n, slug=:s, updated_at=:ua WHERE id=:id");
    $stmt->bindValue(':n', $name, SQLITE3_TEXT);
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $stmt->bindValue(':ua', gmdate('c'), SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
}

function admin_category_delete(SQLite3 $db): void {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    $db->exec("UPDATE products SET category_id = 0 WHERE category_id = " . $id);
    $stmt = $db->prepare("DELETE FROM product_categories WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
}

function admin_create(SQLite3 $db, string $table, string $label, string $basePath): void {
    require_admin();
    ob_start();
    echo '<h1 class="title is-3">新建' . h($label) . '</h1>';
    echo '<div class="box admin-card">';
    admin_form($basePath . '/create', [], ['type' => $table === 'products' ? 'product' : '']);
    echo '</div>';
    $content = ob_get_clean();
    admin_page('新建' . $label, $content, true);
}

function admin_store(SQLite3 $db, string $table): void {
    require_admin();
    csrf_check();
    $title = trim((string)$_POST['title']);
    $slug = trim((string)($_POST['slug'] ?? ''));
    if ($slug === '') {
        $slug = slugify($title);
    }
    $columns = "title,slug,summary,content,created_at,updated_at";
    $values = ":t,:s,:sum,:c,:ca,:ua";
    if ($table === 'products') {
        $columns = "title,slug,summary,content,category_id,created_at,updated_at";
        $values = ":t,:s,:sum,:c,:cid,:ca,:ua";
    }
    $stmt = $db->prepare("INSERT INTO $table ($columns) VALUES ($values)");
    $stmt->bindValue(':t', $title, SQLITE3_TEXT);
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $stmt->bindValue(':sum', trim((string)($_POST['summary'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':c', trim((string)($_POST['content'] ?? '')), SQLITE3_TEXT);
    if ($table === 'products') {
        $stmt->bindValue(':cid', (int)($_POST['category_id'] ?? 0), SQLITE3_INTEGER);
    }
    $stmt->bindValue(':ca', gmdate('c'), SQLITE3_TEXT);
    $stmt->bindValue(':ua', gmdate('c'), SQLITE3_TEXT);
    $stmt->execute();
    if ($table === 'products') {
        $productId = (int)$db->lastInsertRowID();
        process_product_images($db, $productId, 0, true);
    }
}

function admin_edit(SQLite3 $db, string $table, string $label, string $basePath): void {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM $table WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $res = $stmt->execute();
    $item = $res->fetchArray(SQLITE3_ASSOC);
    if (!$item) {
        admin_page('未找到', '<div class="notification is-danger is-light">内容不存在。</div>', true);
        return;
    }
    $images = [];
    if ($table === 'products') {
        $stmtImg = $db->prepare("SELECT id,url,sort FROM product_images WHERE product_id = :pid ORDER BY sort ASC, id ASC");
        $stmtImg->bindValue(':pid', $id, SQLITE3_INTEGER);
        $resImg = $stmtImg->execute();
        while ($row = $resImg->fetchArray(SQLITE3_ASSOC)) {
            $images[] = $row;
        }
    }
    ob_start();
    echo '<h1 class="title is-3">编辑' . h($label) . '</h1>';
    echo '<div class="box admin-card">';
    admin_form($basePath . '/edit?id=' . $id, $item, ['type' => $table === 'products' ? 'product' : '', 'images' => $images]);
    echo '</div>';
    $content = ob_get_clean();
    admin_page('编辑' . $label, $content, true);
}

function admin_update(SQLite3 $db, string $table): void {
    require_admin();
    csrf_check();
    $id = (int)($_GET['id'] ?? 0);
    $title = trim((string)$_POST['title']);
    $slug = trim((string)($_POST['slug'] ?? ''));
    if ($slug === '') {
        $slug = slugify($title);
    }
    $sql = "UPDATE $table SET title=:t, slug=:s, summary=:sum, content=:c, updated_at=:ua";
    if ($table === 'products') {
        $sql .= ", category_id=:cid";
    }
    $sql .= " WHERE id=:id";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':t', $title, SQLITE3_TEXT);
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $stmt->bindValue(':sum', trim((string)($_POST['summary'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':c', trim((string)($_POST['content'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':ua', gmdate('c'), SQLITE3_TEXT);
    if ($table === 'products') {
        $stmt->bindValue(':cid', (int)($_POST['category_id'] ?? 0), SQLITE3_INTEGER);
    }
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    if ($table === 'products') {
        $removeIds = $_POST['remove_images'] ?? [];
        if (is_array($removeIds) && !empty($removeIds)) {
            $ids = array_map('intval', $removeIds);
            foreach ($ids as $rid) {
                $stmtImg = $db->prepare("SELECT url FROM product_images WHERE id = :id AND product_id = :pid");
                $stmtImg->bindValue(':id', $rid, SQLITE3_INTEGER);
                $stmtImg->bindValue(':pid', $id, SQLITE3_INTEGER);
                $resImg = $stmtImg->execute();
                $row = $resImg->fetchArray(SQLITE3_ASSOC);
                if ($row) {
                    $stmtDel = $db->prepare("DELETE FROM product_images WHERE id = :id AND product_id = :pid");
                    $stmtDel->bindValue(':id', $rid, SQLITE3_INTEGER);
                    $stmtDel->bindValue(':pid', $id, SQLITE3_INTEGER);
                    $stmtDel->execute();
                    delete_uploaded_image($row['url']);
                }
            }
        }
        $existingCount = (int)$db->querySingle("SELECT COUNT(*) FROM product_images WHERE product_id = " . $id);
        $requireAtLeastOne = $existingCount === 0;
        process_product_images($db, $id, $existingCount, $requireAtLeastOne);
    }
}

function admin_delete(SQLite3 $db, string $table): void {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM $table WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
}

// Products admin
if ($path === '/admin/products') { admin_list($db, 'products', '产品', '/admin/products'); exit; }
if ($path === '/admin/products/create' && $method === 'GET') { admin_create($db, 'products', '产品', '/admin/products'); exit; }
if ($path === '/admin/products/create' && $method === 'POST') { admin_store($db, 'products'); header('Location: /admin/products'); exit; }
if ($path === '/admin/products/edit' && $method === 'GET') { admin_edit($db, 'products', '产品', '/admin/products'); exit; }
if ($path === '/admin/products/edit' && $method === 'POST') { admin_update($db, 'products'); header('Location: /admin/products'); exit; }
if ($path === '/admin/products/delete') { admin_delete($db, 'products'); header('Location: /admin/products'); exit; }

// Product categories admin
if ($path === '/admin/categories') { admin_category_list($db); exit; }
if ($path === '/admin/categories/create' && $method === 'GET') {
    ob_start();
    echo '<h1 class="title is-3">新建产品分类</h1><div class="box admin-card">';
    admin_category_form('/admin/categories/create');
    echo '</div>';
    $content = ob_get_clean();
    admin_page('新建产品分类', $content, true);
    exit;
}
if ($path === '/admin/categories/create' && $method === 'POST') { admin_category_store($db); header('Location: /admin/categories'); exit; }
if ($path === '/admin/categories/edit' && $method === 'GET') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM product_categories WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $res = $stmt->execute();
    $item = $res->fetchArray(SQLITE3_ASSOC);
    if (!$item) {
        admin_page('未找到', '<div class="notification is-danger is-light">内容不存在。</div>', true);
        exit;
    }
    ob_start();
    echo '<h1 class="title is-3">编辑产品分类</h1><div class="box admin-card">';
    admin_category_form('/admin/categories/edit?id=' . $id, $item);
    echo '</div>';
    $content = ob_get_clean();
    admin_page('编辑产品分类', $content, true);
    exit;
}
if ($path === '/admin/categories/edit' && $method === 'POST') { admin_category_update($db); header('Location: /admin/categories'); exit; }
if ($path === '/admin/categories/delete') { admin_category_delete($db); header('Location: /admin/categories'); exit; }

// Cases admin
if ($path === '/admin/cases') { admin_list($db, 'cases', '案例', '/admin/cases'); exit; }
if ($path === '/admin/cases/create' && $method === 'GET') { admin_create($db, 'cases', '案例', '/admin/cases'); exit; }
if ($path === '/admin/cases/create' && $method === 'POST') { admin_store($db, 'cases'); header('Location: /admin/cases'); exit; }
if ($path === '/admin/cases/edit' && $method === 'GET') { admin_edit($db, 'cases', '案例', '/admin/cases'); exit; }
if ($path === '/admin/cases/edit' && $method === 'POST') { admin_update($db, 'cases'); header('Location: /admin/cases'); exit; }
if ($path === '/admin/cases/delete') { admin_delete($db, 'cases'); header('Location: /admin/cases'); exit; }

// Blog admin
if ($path === '/admin/posts') { admin_list($db, 'posts', '博客', '/admin/posts'); exit; }
if ($path === '/admin/posts/create' && $method === 'GET') { admin_create($db, 'posts', '博客', '/admin/posts'); exit; }
if ($path === '/admin/posts/create' && $method === 'POST') { admin_store($db, 'posts'); header('Location: /admin/posts'); exit; }
if ($path === '/admin/posts/edit' && $method === 'GET') { admin_edit($db, 'posts', '博客', '/admin/posts'); exit; }
if ($path === '/admin/posts/edit' && $method === 'POST') { admin_update($db, 'posts'); header('Location: /admin/posts'); exit; }
if ($path === '/admin/posts/delete') { admin_delete($db, 'posts'); header('Location: /admin/posts'); exit; }

// Messages / inquiries admin
if ($path === '/admin/messages') {
    require_admin();
    $res = $db->query("SELECT * FROM messages ORDER BY id DESC");
    ob_start();
    echo '<h1 class="title is-3">留言</h1>';
    echo '<div class="box admin-card"><table class="table is-fullwidth is-striped">';
    echo '<thead><tr><th>姓名</th><th>邮箱</th><th>公司</th><th>内容</th><th>时间</th></tr></thead><tbody>';
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        echo '<tr><td>' . h($row['name']) . '</td><td>' . h($row['email']) . '</td><td>' . h($row['company']) . '</td><td>' . h($row['message']) . '</td><td>' . h($row['created_at']) . '</td></tr>';
    }
    echo '</tbody></table></div>';
    $content = ob_get_clean();
    admin_page('留言', $content, true);
    exit;
}

if ($path === '/admin/inquiries') {
    require_admin();
    $res = $db->query("SELECT inquiries.*, products.title AS product_title FROM inquiries LEFT JOIN products ON products.id = inquiries.product_id ORDER BY inquiries.id DESC");
    ob_start();
    echo '<h1 class="title is-3">询单</h1>';
    echo '<div class="box admin-card"><table class="table is-fullwidth is-striped">';
    echo '<thead><tr><th>姓名</th><th>邮箱</th><th>公司</th><th>产品</th><th>内容</th><th>时间</th></tr></thead><tbody>';
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        echo '<tr><td>' . h($row['name']) . '</td><td>' . h($row['email']) . '</td><td>' . h($row['company']) . '</td><td>' . h($row['product_title'] ?? '') . '</td><td>' . h($row['message']) . '</td><td>' . h($row['created_at']) . '</td></tr>';
    }
    echo '</tbody></table></div>';
    $content = ob_get_clean();
    admin_page('询单', $content, true);
    exit;
}

// 404
http_response_code(404);
render($db, '404', [
    'seo' => [
        'title' => '404 - ' . setting_get($db, 'site_name'),
        'description' => setting_get($db, 'site_tagline'),
    ],
]);
