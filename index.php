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

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
if (!is_dir($themesDir)) {
    mkdir($themesDir, 0755, true);
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
");

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

function admin_nav_html(): string {
    $links = [
        '/admin' => 'Dashboard',
        '/admin/products' => 'Products',
        '/admin/cases' => 'Cases',
        '/admin/posts' => 'Blog',
        '/admin/messages' => 'Messages',
        '/admin/inquiries' => 'Inquiries',
        '/admin/settings' => 'Settings',
    ];
    $html = '<div class="navbar-menu is-active"><div class="navbar-start">';
    foreach ($links as $url => $label) {
        $html .= '<a class="navbar-item" href="' . h($url) . '">' . h($label) . '</a>';
    }
    $html .= '</div><div class="navbar-end"><div class="navbar-item"><a class="button is-light" href="/admin/logout">Logout</a></div></div></div>';
    return $html;
}

function admin_page(string $title, string $content, bool $showNav = true): void {
    $siteName = setting_get($GLOBALS['db'], 'site_name', 'B2B Company');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">';
    echo '<style>body{background:#f5f7fb}.admin-card{box-shadow:0 10px 30px rgba(15,23,42,0.08)}</style>';
    echo '</head><body>';
    if ($showNav) {
        echo '<nav class="navbar is-white is-spaced"><div class="container">';
        echo '<div class="navbar-brand"><a class="navbar-item" href="/admin"><strong>' . h($siteName) . ' Admin</strong></a></div>';
        echo admin_nav_html();
        echo '</div></nav>';
    }
    echo '<section class="section"><div class="container">';
    echo $content;
    echo '</div></section></body></html>';
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
    ];

    $seo = $data['seo'] ?? [];
    $seo = array_merge([
        'title' => $site['name'],
        'description' => $site['tagline'],
        'canonical' => base_url() . current_path(),
    ], $seo);

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
    $res = $db->query("SELECT title,slug,summary FROM products ORDER BY id DESC");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $row['url'] = '/product/' . $row['slug'];
        $items[] = $row;
    }
    render($db, 'list', [
        'title' => 'Products',
        'items' => $items,
        'seo' => [
            'title' => 'Products - ' . setting_get($db, 'site_name'),
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
        'title' => 'Success Cases',
        'items' => $items,
        'seo' => [
            'title' => 'Cases - ' . setting_get($db, 'site_name'),
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
        'title' => 'Blog',
        'items' => $items,
        'seo' => [
            'title' => 'Blog - ' . setting_get($db, 'site_name'),
            'description' => setting_get($db, 'site_tagline'),
        ],
    ]);
    exit;
}

// Product detail
if (preg_match('#^/product/([a-z0-9\\-]+)$#', $path, $m)) {
    $slug = $m[1];
    $stmt = $db->prepare("SELECT * FROM products WHERE slug = :s");
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $res = $stmt->execute();
    $item = $res->fetchArray(SQLITE3_ASSOC);
    if ($item) {
        render($db, 'detail', [
            'item' => $item,
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
    echo '<h1 class="title is-4">Admin Login</h1>';
    echo '<form method="post" action="/admin/login">';
    echo '<div class="field"><label class="label">Username</label><div class="control"><input class="input" name="username" required></div></div>';
    echo '<div class="field"><label class="label">Password</label><div class="control"><input class="input" type="password" name="password" required></div></div>';
    echo '<button class="button is-link is-fullwidth" type="submit">Login</button>';
    echo '</form></div></div></div>';
    $content = ob_get_clean();
    admin_page('Admin Login', $content, false);
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
    echo '<div class="notification is-danger is-light">Login failed. Please try again.</div>';
    echo '<div class="box admin-card">';
    echo '<h1 class="title is-4">Admin Login</h1>';
    echo '<form method="post" action="/admin/login">';
    echo '<div class="field"><label class="label">Username</label><div class="control"><input class="input" name="username" required></div></div>';
    echo '<div class="field"><label class="label">Password</label><div class="control"><input class="input" type="password" name="password" required></div></div>';
    echo '<button class="button is-link is-fullwidth" type="submit">Login</button>';
    echo '</form></div></div></div>';
    $content = ob_get_clean();
    admin_page('Admin Login', $content, false);
    exit;
}
if ($path === '/admin/logout') {
    session_destroy();
    header('Location: /admin/login');
    exit;
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
    echo '<h1 class="title is-3">Admin Dashboard</h1>';
    echo '<div class="columns is-multiline">';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">Products</p><p class="title is-4">' . $counts['products'] . '</p><a href="/admin/products" class="button is-small is-link is-light">Manage</a></div></div>';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">Cases</p><p class="title is-4">' . $counts['cases'] . '</p><a href="/admin/cases" class="button is-small is-link is-light">Manage</a></div></div>';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">Blog</p><p class="title is-4">' . $counts['posts'] . '</p><a href="/admin/posts" class="button is-small is-link is-light">Manage</a></div></div>';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">Messages</p><p class="title is-4">' . $counts['messages'] . '</p><a href="/admin/messages" class="button is-small is-link is-light">View</a></div></div>';
    echo '<div class="column is-3"><div class="box admin-card"><p class="heading">Inquiries</p><p class="title is-4">' . $counts['inquiries'] . '</p><a href="/admin/inquiries" class="button is-small is-link is-light">View</a></div></div>';
    echo '</div>';
    $content = ob_get_clean();
    admin_page('Admin Dashboard', $content, true);
    exit;
}

// Admin settings
if ($path === '/admin/settings' && $method === 'GET') {
    require_admin();
    $theme = setting_get($db, 'theme', 'default');
    ob_start();
    echo '<h1 class="title is-3">Settings</h1>';
    echo '<div class="box admin-card"><form method="post" action="/admin/settings">';
    echo '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
    echo '<div class="columns">';
    echo '<div class="column"><div class="field"><label class="label">Site Name</label><div class="control"><input class="input" name="site_name" value="' . h(setting_get($db, 'site_name')) . '"></div></div></div>';
    echo '<div class="column"><div class="field"><label class="label">Tagline</label><div class="control"><input class="input" name="site_tagline" value="' . h(setting_get($db, 'site_tagline')) . '"></div></div></div>';
    echo '</div>';
    echo '<div class="field"><label class="label">About</label><div class="control"><textarea class="textarea" name="company_about" rows="4">' . h(setting_get($db, 'company_about')) . '</textarea></div></div>';
    echo '<div class="columns">';
    echo '<div class="column"><div class="field"><label class="label">Address</label><div class="control"><input class="input" name="company_address" value="' . h(setting_get($db, 'company_address')) . '"></div></div></div>';
    echo '<div class="column"><div class="field"><label class="label">Email</label><div class="control"><input class="input" name="company_email" value="' . h(setting_get($db, 'company_email')) . '"></div></div></div>';
    echo '<div class="column"><div class="field"><label class="label">Phone</label><div class="control"><input class="input" name="company_phone" value="' . h(setting_get($db, 'company_phone')) . '"></div></div></div>';
    echo '</div>';
    echo '<div class="field"><label class="label">Theme</label><div class="control"><input class="input" name="theme" value="' . h($theme) . '"></div><p class="help">Theme folder under /themes</p></div>';
    echo '<button class="button is-link" type="submit">Save Settings</button>';
    echo '</form></div>';
    $content = ob_get_clean();
    admin_page('Settings', $content, true);
    exit;
}
if ($path === '/admin/settings' && $method === 'POST') {
    require_admin();
    csrf_check();
    $keys = ['site_name','site_tagline','company_about','company_address','company_email','company_phone','theme'];
    foreach ($keys as $k) {
        setting_set($db, $k, trim((string)($_POST[$k] ?? '')));
    }
    header('Location: /admin/settings');
    exit;
}

// Admin CRUD helper
function admin_list(SQLite3 $db, string $table, string $label, string $basePath): void {
    require_admin();
    $res = $db->query("SELECT id,title,slug,created_at FROM $table ORDER BY id DESC");
    ob_start();
    echo '<div class="level"><div class="level-left"><h1 class="title is-3">' . h($label) . '</h1></div>';
    echo '<div class="level-right"><a class="button is-link" href="' . h($basePath) . '/create">Create</a></div></div>';
    echo '<div class="box admin-card"><table class="table is-fullwidth is-striped">';
    echo '<thead><tr><th>Title</th><th>Slug</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        echo '<tr>';
        echo '<td>' . h($row['title']) . '</td>';
        echo '<td>' . h($row['slug']) . '</td>';
        echo '<td>' . h($row['created_at']) . '</td>';
        echo '<td><a class="button is-small is-light" href="' . h($basePath) . '/edit?id=' . (int)$row['id'] . '">Edit</a> ';
        echo '<a class="button is-small is-danger is-light" href="' . h($basePath) . '/delete?id=' . (int)$row['id'] . '" onclick="return confirm(\'Delete?\')">Delete</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    $content = ob_get_clean();
    admin_page($label, $content, true);
}

function admin_form(string $action, array $item = []): void {
    $title = $item['title'] ?? '';
    $slug = $item['slug'] ?? '';
    $summary = $item['summary'] ?? '';
    $content = $item['content'] ?? '';
    echo '<form method="post" action="' . h($action) . '">';
    echo '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
    echo '<div class="columns">';
    echo '<div class="column"><div class="field"><label class="label">Title</label><div class="control"><input class="input" name="title" value="' . h($title) . '" required></div></div></div>';
    echo '<div class="column"><div class="field"><label class="label">Slug</label><div class="control"><input class="input" name="slug" value="' . h($slug) . '"></div><p class="help">Leave empty to auto-generate</p></div></div></div>';
    echo '</div>';
    echo '<div class="field"><label class="label">Summary</label><div class="control"><textarea class="textarea" name="summary" rows="3">' . h($summary) . '</textarea></div></div>';
    echo '<div class="field"><label class="label">Content</label><div class="control"><textarea class="textarea" name="content" rows="10">' . h($content) . '</textarea></div></div>';
    echo '<button class="button is-link" type="submit">Save</button>';
    echo '</form>';
}

function admin_create(SQLite3 $db, string $table, string $label, string $basePath): void {
    require_admin();
    ob_start();
    echo '<h1 class="title is-3">Create ' . h($label) . '</h1>';
    echo '<div class="box admin-card">';
    admin_form($basePath . '/create');
    echo '</div>';
    $content = ob_get_clean();
    admin_page('Create ' . $label, $content, true);
}

function admin_store(SQLite3 $db, string $table): void {
    require_admin();
    csrf_check();
    $title = trim((string)$_POST['title']);
    $slug = trim((string)($_POST['slug'] ?? ''));
    if ($slug === '') {
        $slug = slugify($title);
    }
    $stmt = $db->prepare("INSERT INTO $table (title,slug,summary,content,created_at,updated_at)
        VALUES (:t,:s,:sum,:c,:ca,:ua)");
    $stmt->bindValue(':t', $title, SQLITE3_TEXT);
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $stmt->bindValue(':sum', trim((string)($_POST['summary'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':c', trim((string)($_POST['content'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':ca', gmdate('c'), SQLITE3_TEXT);
    $stmt->bindValue(':ua', gmdate('c'), SQLITE3_TEXT);
    $stmt->execute();
}

function admin_edit(SQLite3 $db, string $table, string $label, string $basePath): void {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM $table WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $res = $stmt->execute();
    $item = $res->fetchArray(SQLITE3_ASSOC);
    if (!$item) {
        admin_page('Not Found', '<div class="notification is-danger is-light">Item not found.</div>', true);
        return;
    }
    ob_start();
    echo '<h1 class="title is-3">Edit ' . h($label) . '</h1>';
    echo '<div class="box admin-card">';
    admin_form($basePath . '/edit?id=' . $id, $item);
    echo '</div>';
    $content = ob_get_clean();
    admin_page('Edit ' . $label, $content, true);
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
    $stmt = $db->prepare("UPDATE $table SET title=:t, slug=:s, summary=:sum, content=:c, updated_at=:ua WHERE id=:id");
    $stmt->bindValue(':t', $title, SQLITE3_TEXT);
    $stmt->bindValue(':s', $slug, SQLITE3_TEXT);
    $stmt->bindValue(':sum', trim((string)($_POST['summary'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':c', trim((string)($_POST['content'] ?? '')), SQLITE3_TEXT);
    $stmt->bindValue(':ua', gmdate('c'), SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
}

function admin_delete(SQLite3 $db, string $table): void {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM $table WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
}

// Products admin
if ($path === '/admin/products') { admin_list($db, 'products', 'Products', '/admin/products'); exit; }
if ($path === '/admin/products/create' && $method === 'GET') { admin_create($db, 'products', 'Product', '/admin/products'); exit; }
if ($path === '/admin/products/create' && $method === 'POST') { admin_store($db, 'products'); header('Location: /admin/products'); exit; }
if ($path === '/admin/products/edit' && $method === 'GET') { admin_edit($db, 'products', 'Product', '/admin/products'); exit; }
if ($path === '/admin/products/edit' && $method === 'POST') { admin_update($db, 'products'); header('Location: /admin/products'); exit; }
if ($path === '/admin/products/delete') { admin_delete($db, 'products'); header('Location: /admin/products'); exit; }

// Cases admin
if ($path === '/admin/cases') { admin_list($db, 'cases', 'Cases', '/admin/cases'); exit; }
if ($path === '/admin/cases/create' && $method === 'GET') { admin_create($db, 'cases', 'Case', '/admin/cases'); exit; }
if ($path === '/admin/cases/create' && $method === 'POST') { admin_store($db, 'cases'); header('Location: /admin/cases'); exit; }
if ($path === '/admin/cases/edit' && $method === 'GET') { admin_edit($db, 'cases', 'Case', '/admin/cases'); exit; }
if ($path === '/admin/cases/edit' && $method === 'POST') { admin_update($db, 'cases'); header('Location: /admin/cases'); exit; }
if ($path === '/admin/cases/delete') { admin_delete($db, 'cases'); header('Location: /admin/cases'); exit; }

// Blog admin
if ($path === '/admin/posts') { admin_list($db, 'posts', 'Posts', '/admin/posts'); exit; }
if ($path === '/admin/posts/create' && $method === 'GET') { admin_create($db, 'posts', 'Post', '/admin/posts'); exit; }
if ($path === '/admin/posts/create' && $method === 'POST') { admin_store($db, 'posts'); header('Location: /admin/posts'); exit; }
if ($path === '/admin/posts/edit' && $method === 'GET') { admin_edit($db, 'posts', 'Post', '/admin/posts'); exit; }
if ($path === '/admin/posts/edit' && $method === 'POST') { admin_update($db, 'posts'); header('Location: /admin/posts'); exit; }
if ($path === '/admin/posts/delete') { admin_delete($db, 'posts'); header('Location: /admin/posts'); exit; }

// Messages / inquiries admin
if ($path === '/admin/messages') {
    require_admin();
    $res = $db->query("SELECT * FROM messages ORDER BY id DESC");
    ob_start();
    echo '<h1 class="title is-3">Messages</h1>';
    echo '<div class="box admin-card"><table class="table is-fullwidth is-striped">';
    echo '<thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Message</th><th>Time</th></tr></thead><tbody>';
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        echo '<tr><td>' . h($row['name']) . '</td><td>' . h($row['email']) . '</td><td>' . h($row['company']) . '</td><td>' . h($row['message']) . '</td><td>' . h($row['created_at']) . '</td></tr>';
    }
    echo '</tbody></table></div>';
    $content = ob_get_clean();
    admin_page('Messages', $content, true);
    exit;
}

if ($path === '/admin/inquiries') {
    require_admin();
    $res = $db->query("SELECT inquiries.*, products.title AS product_title FROM inquiries LEFT JOIN products ON products.id = inquiries.product_id ORDER BY inquiries.id DESC");
    ob_start();
    echo '<h1 class="title is-3">Inquiries</h1>';
    echo '<div class="box admin-card"><table class="table is-fullwidth is-striped">';
    echo '<thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Product</th><th>Message</th><th>Time</th></tr></thead><tbody>';
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        echo '<tr><td>' . h($row['name']) . '</td><td>' . h($row['email']) . '</td><td>' . h($row['company']) . '</td><td>' . h($row['product_title'] ?? '') . '</td><td>' . h($row['message']) . '</td><td>' . h($row['created_at']) . '</td></tr>';
    }
    echo '</tbody></table></div>';
    $content = ob_get_clean();
    admin_page('Inquiries', $content, true);
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
