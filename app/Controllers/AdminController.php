<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Category;
use App\Models\CaseModel;
use App\Models\PostModel;
use App\Models\Inquiry;
use App\Models\Message;
use App\Models\User;
use SQLite3;

class AdminController extends Controller {
    private SQLite3 $db;
    private Setting $settingModel;
    private Product $productModel;
    private Category $categoryModel;
    private CaseModel $caseModel;
    private PostModel $postModel;
    private Inquiry $inquiryModel;
    private Message $messageModel;
    private User $userModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->settingModel = new Setting();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->caseModel = new CaseModel();
        $this->postModel = new PostModel();
        $this->inquiryModel = new Inquiry();
        $this->messageModel = new Message();
        $this->userModel = new User();
        
        // Auth check
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($path !== '/admin/login' && !isset($_SESSION['admin_user'])) {
            $this->redirect('/admin/login');
        }

        // Permission check
        if (isset($_SESSION['admin_user'])) {
            $this->checkPermission($path);
        }
    }

    private function checkPermission(string $path): void {
        if ($_SESSION['admin_role'] === 'admin') return;

        $perms = $_SESSION['admin_permissions'] ?? [];
        
        $map = [
            '/admin/products' => 'products',
            '/admin/product-categories' => 'products',
            '/admin/cases' => 'cases',
            '/admin/posts' => 'blog',
            '/admin/post-categories' => 'blog',
            '/admin/messages' => 'inbox',
            '/admin/inquiries' => 'inbox',
            '/admin/settings' => 'settings',
            '/admin/staff' => 'staff',
        ];

        foreach ($map as $prefix => $perm) {
            if (str_starts_with($path, $prefix) && !in_array($perm, $perms)) {
                $this->redirect('/admin');
            }
        }
    }

    // --- Auth ---
    public function login(): void {
        if (isset($_SESSION['admin_user'])) {
            $this->redirect('/admin');
        }
        $this->renderAdmin('登录', $this->renderView('admin/login'), false);
    }

    public function doLogin(): void {
        $username = trim((string)$_POST['username']);
        $password = (string)$_POST['password'];
        $user = $this->userModel->getByUsername($username);
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_user'] = $user['username'];
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_permissions'] = array_filter(explode(',', $user['permissions'] ?? ''));
            $_SESSION['admin_display_name'] = $user['display_name'] ?? $user['username'];
            $this->redirect('/admin');
        }
        $this->renderAdmin('登录', '<div class="notification is-danger">登录失败</div>' . $this->renderView('admin/login'), false);
    }

    public function logout(): void {
        unset($_SESSION['admin_user']);
        $this->redirect('/admin/login');
    }

    // --- Dashboard & Settings ---
    public function dashboard(): void {
        // 基础统计
        $counts = [
            'products' => (int)$this->db->querySingle("SELECT COUNT(*) FROM products"),
            'active_products' => (int)$this->db->querySingle("SELECT COUNT(*) FROM products WHERE status = 'active'"),
            'cases' => (int)$this->db->querySingle("SELECT COUNT(*) FROM cases"),
            'posts' => (int)$this->db->querySingle("SELECT COUNT(*) FROM posts"),
            'messages' => (int)$this->db->querySingle("SELECT COUNT(*) FROM messages"),
            'inquiries' => (int)$this->db->querySingle("SELECT COUNT(*) FROM inquiries"),
            'pending_inquiries' => (int)$this->db->querySingle("SELECT COUNT(*) FROM inquiries WHERE status = 'pending'"),
            'categories' => (int)$this->db->querySingle("SELECT COUNT(*) FROM product_categories WHERE type = 'product' OR type IS NULL"),
            'post_categories' => (int)$this->db->querySingle("SELECT COUNT(*) FROM product_categories WHERE type = 'post'"),
            'users' => (int)$this->db->querySingle("SELECT COUNT(*) FROM users"),
        ];

        // 今日统计
        $today = date('Y-m-d');
        $counts['today_messages'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM messages WHERE DATE(created_at) = '$today'");
        $counts['today_inquiries'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) = '$today'");

        // 本周统计
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $counts['week_messages'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM messages WHERE DATE(created_at) >= '$weekStart' AND DATE(created_at) <= '$weekEnd'");
        $counts['week_inquiries'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) >= '$weekStart' AND DATE(created_at) <= '$weekEnd'");

        // 本月统计
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $counts['month_messages'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM messages WHERE DATE(created_at) >= '$monthStart' AND DATE(created_at) <= '$monthEnd'");
        $counts['month_inquiries'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) >= '$monthStart' AND DATE(created_at) <= '$monthEnd'");

        // 最近30天趋势数据
        $counts['recent_messages'] = [];
        $counts['recent_inquiries'] = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $counts['recent_messages'][] = (int)$this->db->querySingle("SELECT COUNT(*) FROM messages WHERE DATE(created_at) = '$date'");
            $counts['recent_inquiries'][] = (int)$this->db->querySingle("SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) = '$date'");
        }

        // 系统统计
        $counts['total_images'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM product_images");

        // 计算数据库创建日期（近似值）
        $dbFile = __DIR__ . '/../../data/site.db';
        $counts['system_age_days'] = file_exists($dbFile) ? (int)((time() - filectime($dbFile)) / 86400) : 0;

        $this->renderAdmin('仪表盘', $this->renderView('admin/dashboard', ['counts' => $counts]));
    }

    public function settings(): void {
        $tab = $_GET['tab'] ?? 'general';
        $settings = $this->settingModel->getAll();
        
        // Scan for available themes
        $themesDir = __DIR__ . "/../../themes";
        $availableThemes = [];
        if (is_dir($themesDir)) {
            $dirs = array_filter(glob("{$themesDir}/*"), 'is_dir');
            foreach ($dirs as $d) {
                $availableThemes[] = basename($d);
            }
        }
        if (empty($availableThemes)) $availableThemes = ['default'];

        $theme = $settings['theme'] ?? 'default';
        if (!in_array($theme, $availableThemes)) $theme = $availableThemes[0];
        
        // Scan for available languages in current theme
        $langDir = __DIR__ . "/../../themes/{$theme}/lang";
        $availableLangs = [];
        if (is_dir($langDir)) {
            $files = glob("{$langDir}/*.php");
            foreach ($files as $f) {
                $availableLangs[] = basename($f, '.php');
            }
        }
        if (empty($availableLangs)) $availableLangs = ['en'];

        $data = [
            'settings' => $settings,
            'tab' => $tab,
            'available_langs' => $availableLangs,
            'available_themes' => $availableThemes
        ];

        if ($tab === 'translations') {
            $lang = $_GET['lang'] ?? ($availableLangs[0] ?? 'en');
            $file = "{$langDir}/{$lang}.php";
            $translations = [];
            if (file_exists($file)) {
                $translations = include $file;
            }
            $data['translations'] = $translations;
            $data['current_edit_lang'] = $lang;
        }

        $this->renderAdmin('系统设置', $this->renderView('admin/settings', $data));
    }

    public function saveSettings(): void {
        csrf_check();
        $tab = $_POST['tab'] ?? 'general';
        
        if ($tab === 'translations') {
            $lang = $_POST['edit_lang'] ?? 'en';
            $theme = $this->settingModel->get('theme', 'default');
            $dir = __DIR__ . "/../../themes/{$theme}/lang";
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $file = "{$dir}/{$lang}.php";
            
            $newTranslations = $_POST['t'] ?? [];
            $content = "<?php\nreturn " . var_export($newTranslations, true) . ";\n";
            file_put_contents($file, $content);
            
            $this->redirect('/admin/settings?tab=translations&lang=' . $lang);
            return;
        }

        // Special handling for media tab JSON conversion
        if ($tab === 'media') {
            // Handle Company Show
            $showImgs = $_POST['show_img'] ?? [];
            $showTitles = $_POST['show_title'] ?? [];
            $showData = [];
            foreach ($showImgs as $idx => $img) {
                if (!empty($img)) {
                    $showData[] = ['img' => $img, 'title' => $showTitles[$idx] ?? ''];
                }
            }
            $this->settingModel->set('company_show_json', json_encode($showData));

            // Handle Certificates
            $certImgs = $_POST['cert_img'] ?? [];
            $certTitles = $_POST['cert_title'] ?? [];
            $certData = [];
            foreach ($certImgs as $idx => $img) {
                if (!empty($img)) {
                    $certData[] = ['img' => $img, 'title' => $certTitles[$idx] ?? ''];
                }
            }
            $this->settingModel->set('company_certificates_json', json_encode($certData));

            $this->redirect('/admin/settings?tab=media');
            return;
        }

        $groups = [
            'general' => ['site_name', 'site_tagline', 'theme', 'default_lang', 'seo_title', 'seo_keywords', 'seo_description', 'og_image'],
            'company' => ['company_bio', 'company_business_type', 'company_main_products', 'company_year_established', 'company_employees', 'company_address', 'company_plant_area', 'company_registered_capital', 'company_sgs_report', 'company_rating', 'company_response_time'],
            'trade' => ['company_main_markets', 'company_trade_staff', 'company_incoterms', 'company_payment_terms', 'company_lead_time', 'company_overseas_agent', 'company_export_year', 'company_nearest_port', 'company_rd_engineers'],
            'contact' => ['company_email', 'company_phone', 'company_address', 'whatsapp', 'facebook', 'instagram', 'twitter', 'linkedin', 'youtube']
        ];

        $keys = $groups[$tab] ?? [];
        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                $this->settingModel->set($k, is_array($_POST[$k]) ? json_encode($_POST[$k]) : trim((string)$_POST[$k]));
            }
        }
        $this->redirect('/admin/settings?tab=' . $tab);
    }

    // --- Products ---
    public function productList(): void {
        $products = $this->productModel->getList();
        $this->renderAdmin('产品管理', $this->renderView('admin/products/index', ['products' => $products]));
    }

    public function productCreate(): void {
        $categories = $this->categoryModel->getAll();
        $this->renderAdmin('新建产品', $this->renderView('admin/products/form', [
            'action' => '/admin/products/create',
            'categories' => $categories
        ]));
    }

    public function productStore(): void {
        csrf_check();
        $data = $this->getProductFormData();
        $productId = $this->productModel->create($data);
        $this->handleProductPrices($productId);
        $this->redirect('/admin/products');
    }

    public function productEdit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->getById($id);
        if (!$product) $this->redirect('/admin/products');
        
        $categories = $this->categoryModel->getAll();
        $prices = $this->productModel->getPrices($id);
        
        $this->renderAdmin('编辑产品', $this->renderView('admin/products/form', [
            'action' => '/admin/products/edit?id=' . $id,
            'product' => $product,
            'categories' => $categories,
            'prices' => $prices
        ]));
    }

    public function productUpdate(): void {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $data = $this->getProductFormData();
        $this->productModel->update($id, $data);
        $this->handleProductPrices($id);
        $this->redirect('/admin/products');
    }

    public function productDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->productModel->delete($id);
        $this->redirect('/admin/products');
    }

    private function getProductFormData(): array {
        $title = trim((string)$_POST['title']);
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') $slug = slugify($title);
        
        // Handle images from hidden input or newly uploaded
        $images = $_POST['images'] ?? []; // This will be from the UI selection
        if (!is_array($images)) $images = [$images];
        
        // Handle new uploads if any
        if (isset($_FILES['new_images'])) {
            $files = normalize_files_array($_FILES['new_images']);
            foreach ($files as $file) {
                [$ok, $result] = save_uploaded_image($file);
                if ($ok) {
                    $images[] = $result;
                }
            }
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'summary' => trim((string)($_POST['summary'] ?? '')),
            'content' => trim((string)($_POST['content'] ?? '')),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'status' => trim((string)($_POST['status'] ?? 'active')),
            'product_type' => trim((string)($_POST['product_type'] ?? '')),
            'vendor' => trim((string)($_POST['vendor'] ?? '')),
            'tags' => trim((string)($_POST['tags'] ?? '')),
            'images_json' => json_encode(array_values(array_unique(array_filter($images)))),
            'seo_title' => trim((string)($_POST['seo_title'] ?? '')),
            'seo_keywords' => trim((string)($_POST['seo_keywords'] ?? '')),
            'seo_description' => trim((string)($_POST['seo_description'] ?? '')),
        ];
    }

    public function mediaLibrary(): void {
        $dir = __DIR__ . '/../../uploads';
        $files = [];
        if (is_dir($dir)) {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($it as $file) {
                if ($file->isDir()) continue;
                if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file->getFilename())) {
                    $path = str_replace('\\', '/', $file->getPathname());
                    $uploadsPos = strpos($path, '/uploads/');
                    if ($uploadsPos !== false) {
                        $files[] = substr($path, $uploadsPos);
                    }
                }
            }
        }
        // Sort by newest first
        usort($files, function($a, $b) {
            return filemtime(__DIR__ . '/../..' . $b) <=> filemtime(__DIR__ . '/../..' . $a);
        });
        $this->json($files);
    }

    private function handleProductPrices(int $productId): void {
        if (!isset($_POST['price_tiers_enabled'])) return;
        $tiers = normalize_price_tiers($_POST);
        $this->productModel->savePrices($productId, $tiers);
    }

    // --- Product Categories (产品分类) ---
    public function productCategoryList(): void {
        $categories = $this->categoryModel->getTree('product');
        $this->renderAdmin('产品分类', $this->renderView('admin/categories/index', [
            'categories' => $categories,
            'type' => 'product',
            'base_url' => '/admin/product-categories'
        ]));
    }

    public function productCategoryCreate(): void {
        $parentCategories = $this->categoryModel->getFlatTree('product');
        $this->renderAdmin('新建产品分类', $this->renderView('admin/categories/form', [
            'action' => '/admin/product-categories/create',
            'type' => 'product',
            'base_url' => '/admin/product-categories',
            'parent_categories' => $parentCategories
        ]));
    }

    public function productCategoryStore(): void {
        csrf_check();
        $name = trim((string)$_POST['name']);
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') $slug = slugify($name);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $description = trim((string)($_POST['description'] ?? ''));
        $this->categoryModel->create($name, $slug, 'product', $parentId, $description);
        $this->redirect('/admin/product-categories');
    }

    public function productCategoryEdit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $category = $this->categoryModel->getById($id);
        if (!$category) $this->redirect('/admin/product-categories');
        
        $parentCategories = $this->categoryModel->getFlatTree('product');
        $this->renderAdmin('编辑产品分类', $this->renderView('admin/categories/form', [
            'action' => '/admin/product-categories/edit?id=' . $id,
            'category' => $category,
            'type' => 'product',
            'base_url' => '/admin/product-categories',
            'parent_categories' => $parentCategories
        ]));
    }

    public function productCategoryUpdate(): void {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $name = trim((string)$_POST['name']);
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') $slug = slugify($name);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $description = trim((string)($_POST['description'] ?? ''));
        $this->categoryModel->update($id, $name, $slug, $parentId, $description);
        $this->redirect('/admin/product-categories');
    }

    public function productCategoryDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->categoryModel->delete($id);
        $this->redirect('/admin/product-categories');
    }

    // --- Post Categories (文章分类) ---
    public function postCategoryList(): void {
        $categories = $this->categoryModel->getTree('post');
        $this->renderAdmin('文章分类', $this->renderView('admin/categories/index', [
            'categories' => $categories,
            'type' => 'post',
            'base_url' => '/admin/post-categories'
        ]));
    }

    public function postCategoryCreate(): void {
        $parentCategories = $this->categoryModel->getFlatTree('post');
        $this->renderAdmin('新建文章分类', $this->renderView('admin/categories/form', [
            'action' => '/admin/post-categories/create',
            'type' => 'post',
            'base_url' => '/admin/post-categories',
            'parent_categories' => $parentCategories
        ]));
    }

    public function postCategoryStore(): void {
        csrf_check();
        $name = trim((string)$_POST['name']);
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') $slug = slugify($name);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $description = trim((string)($_POST['description'] ?? ''));
        $this->categoryModel->create($name, $slug, 'post', $parentId, $description);
        $this->redirect('/admin/post-categories');
    }

    public function postCategoryEdit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $category = $this->categoryModel->getById($id);
        if (!$category) $this->redirect('/admin/post-categories');
        
        $parentCategories = $this->categoryModel->getFlatTree('post');
        $this->renderAdmin('编辑文章分类', $this->renderView('admin/categories/form', [
            'action' => '/admin/post-categories/edit?id=' . $id,
            'category' => $category,
            'type' => 'post',
            'base_url' => '/admin/post-categories',
            'parent_categories' => $parentCategories
        ]));
    }

    public function postCategoryUpdate(): void {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $name = trim((string)$_POST['name']);
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') $slug = slugify($name);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $description = trim((string)($_POST['description'] ?? ''));
        $this->categoryModel->update($id, $name, $slug, $parentId, $description);
        $this->redirect('/admin/post-categories');
    }

    public function postCategoryDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->categoryModel->delete($id);
        $this->redirect('/admin/post-categories');
    }

    // --- Cases ---
    public function caseList(): void {
        $items = $this->caseModel->getList();
        $this->renderAdmin('案例管理', $this->renderView('admin/crud/index', ['items' => $items, 'label' => '案例', 'base' => '/admin/cases']));
    }

    public function caseCreate(): void {
        $this->renderAdmin('新建案例', $this->renderView('admin/crud/form', ['action' => '/admin/cases/create', 'label' => '案例']));
    }

    public function caseStore(): void {
        csrf_check();
        $data = $this->getGenericFormData();
        $this->caseModel->create($data);
        $this->redirect('/admin/cases');
    }

    public function caseEdit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->caseModel->getById($id);
        if (!$item) $this->redirect('/admin/cases');
        $this->renderAdmin('编辑案例', $this->renderView('admin/crud/form', ['action' => '/admin/cases/edit?id='.$id, 'item' => $item, 'label' => '案例']));
    }

    public function caseUpdate(): void {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $data = $this->getGenericFormData();
        $this->caseModel->update($id, $data);
        $this->redirect('/admin/cases');
    }

    public function caseDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->caseModel->delete($id);
        $this->redirect('/admin/cases');
    }

    // --- Posts ---
    public function postList(): void {
        $items = $this->postModel->getList();
        $categories = $this->categoryModel->getFlatTree('post');
        $this->renderAdmin('博客管理', $this->renderView('admin/posts/index', [
            'items' => $items,
            'categories' => $categories
        ]));
    }

    public function postCreate(): void {
        $categories = $this->categoryModel->getFlatTree('post');
        $this->renderAdmin('新建博客', $this->renderView('admin/posts/form', [
            'action' => '/admin/posts/create',
            'categories' => $categories
        ]));
    }

    public function postStore(): void {
        csrf_check();
        $data = $this->getPostFormData();
        $this->postModel->create($data);
        $this->redirect('/admin/posts');
    }

    public function postEdit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->postModel->getById($id);
        if (!$item) $this->redirect('/admin/posts');
        $categories = $this->categoryModel->getFlatTree('post');
        $this->renderAdmin('编辑博客', $this->renderView('admin/posts/form', [
            'action' => '/admin/posts/edit?id='.$id,
            'item' => $item,
            'categories' => $categories
        ]));
    }

    public function postUpdate(): void {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $data = $this->getPostFormData();
        $this->postModel->update($id, $data);
        $this->redirect('/admin/posts');
    }

    public function postDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->postModel->delete($id);
        $this->redirect('/admin/posts');
    }

    private function getPostFormData(): array {
        $title = trim((string)$_POST['title']);
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') $slug = slugify($title);
        return [
            'title' => $title,
            'slug' => $slug,
            'summary' => trim((string)($_POST['summary'] ?? '')),
            'content' => trim((string)($_POST['content'] ?? '')),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'status' => trim((string)($_POST['status'] ?? 'active')),
            'seo_title' => trim((string)($_POST['seo_title'] ?? '')),
            'seo_keywords' => trim((string)($_POST['seo_keywords'] ?? '')),
            'seo_description' => trim((string)($_POST['seo_description'] ?? '')),
        ];
    }

    private function getGenericFormData(): array {
        $title = trim((string)$_POST['title']);
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') $slug = slugify($title);
        return [
            'title' => $title,
            'slug' => $slug,
            'summary' => trim((string)($_POST['summary'] ?? '')),
            'content' => trim((string)($_POST['content'] ?? '')),
            'seo_title' => trim((string)($_POST['seo_title'] ?? '')),
            'seo_keywords' => trim((string)($_POST['seo_keywords'] ?? '')),
            'seo_description' => trim((string)($_POST['seo_description'] ?? '')),
        ];
    }

    // --- Messages & Inquiries ---
    public function messageList(): void {
        $messages = $this->messageModel->getAll();
        $this->renderAdmin('留言列表', $this->renderView('admin/messages', ['messages' => $messages]));
    }

    public function inquiryList(): void {
        $status = $_GET['status'] ?? '';
        $inquiries = $this->inquiryModel->getList(['status' => $status]);
        $this->renderAdmin('询单管理', $this->renderView('admin/inquiries', [
            'inquiries' => $inquiries,
            'current_status' => $status
        ]));
    }

    public function inquiryUpdateStatus(): void {
        $id = (int)($_GET['id'] ?? 0);
        $status = $_GET['status'] ?? '';
        if ($id && in_array($status, ['pending', 'contacted', 'quoted', 'closed'])) {
            $this->inquiryModel->updateStatus($id, $status);
        }
        $this->redirect('/admin/inquiries');
    }

    public function inquiryExport(): void {
        $inquiries = $this->inquiryModel->getList();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=inquiries_' . date('Ymd') . '.csv');
        $output = fopen('php://output', 'w');
        // 添加 BOM 以支持 Excel 正确识别 UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'Date', 'Product', 'Name', 'Email', 'Company', 'Phone', 'Quantity', 'Status', 'IP', 'Source URL'], ',', '"', '\\');
        foreach ($inquiries as $i) {
            fputcsv($output, [
                $i['id'],
                $i['created_at'],
                $i['product_title'] ?? 'General',
                $i['name'],
                $i['email'],
                $i['company'],
                $i['phone'],
                $i['quantity'],
                $i['status'],
                $i['ip'],
                $i['source_url']
            ], ',', '"', '\\');
        }
        fclose($output);
        exit;
    }

    // --- AJAX ---
    public function uploadImage(): void {
        csrf_check();
        if (!isset($_FILES['image'])) $this->json(['error' => '未选择文件'], 400);
        [$ok, $result] = save_uploaded_image($_FILES['image']);
        if (!$ok) $this->json(['error' => $result], 400);
        $this->json(['url' => $result]);
    }

    // --- Staff Management ---
    public function staffList(): void {
        if ($_SESSION['admin_role'] !== 'admin') {
            $this->redirect('/admin');
        }
        $users = $this->userModel->getAll();
        $this->renderAdmin('员工管理', $this->renderView('admin/staff/index', ['users' => $users]));
    }

    public function staffCreate(): void {
        if ($_SESSION['admin_role'] !== 'admin') $this->redirect('/admin');
        $this->renderAdmin('新增员工', $this->renderView('admin/staff/form', ['action' => '/admin/staff/create']));
    }

    public function staffStore(): void {
        if ($_SESSION['admin_role'] !== 'admin') $this->redirect('/admin');
        csrf_check();
        $data = [
            'username' => trim((string)$_POST['username']),
            'password' => (string)$_POST['password'],
            'display_name' => trim((string)$_POST['display_name']),
            'role' => trim((string)$_POST['role']),
            'permissions' => implode(',', $_POST['permissions'] ?? [])
        ];
        $this->userModel->create($data);
        $this->redirect('/admin/staff');
    }

    public function staffEdit(): void {
        if ($_SESSION['admin_role'] !== 'admin') $this->redirect('/admin');
        $id = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->getById($id);
        if (!$user) $this->redirect('/admin/staff');
        $user['permissions'] = explode(',', $user['permissions'] ?? '');
        $this->renderAdmin('编辑员工', $this->renderView('admin/staff/form', [
            'action' => '/admin/staff/edit?id=' . $id,
            'user' => $user
        ]));
    }

    public function staffUpdate(): void {
        if ($_SESSION['admin_role'] !== 'admin') $this->redirect('/admin');
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $data = [
            'username' => trim((string)$_POST['username']),
            'display_name' => trim((string)$_POST['display_name']),
            'role' => trim((string)$_POST['role']),
            'permissions' => implode(',', $_POST['permissions'] ?? [])
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = (string)$_POST['password'];
        }
        $this->userModel->update($id, $data);
        $this->redirect('/admin/staff');
    }

    public function staffDelete(): void {
        if ($_SESSION['admin_role'] !== 'admin') $this->redirect('/admin');
        $id = (int)($_GET['id'] ?? 0);
        if ($id !== (int)$_SESSION['admin_user_id']) {
            $this->userModel->delete($id);
        }
        $this->redirect('/admin/staff');
    }

    // --- Profile Management ---
    public function profile(): void {
        $user = $this->userModel->getById((int)$_SESSION['admin_user_id']);
        $this->renderAdmin('个人资料', $this->renderView('admin/profile', ['user' => $user]));
    }

    public function profileUpdate(): void {
        csrf_check();
        $id = (int)$_SESSION['admin_user_id'];
        $data = [
            'display_name' => trim((string)$_POST['display_name'])
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = (string)$_POST['password'];
        }
        $this->userModel->update($id, $data);
        $_SESSION['admin_display_name'] = $data['display_name'];
        $this->redirect('/admin/profile');
    }

    // --- Rendering Helpers ---
    private function renderAdmin(string $title, string $content, bool $showNav = true): void {
        include __DIR__ . '/../views/admin/layout.php';
    }

    private function renderView(string $view, array $data = []): string {
        extract($data);
        ob_start();
        include __DIR__ . '/../views/' . $view . '.php';
        return ob_get_clean();
    }
}
