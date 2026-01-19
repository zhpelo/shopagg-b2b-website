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

    public function __construct() {
        $this->db = Database::getInstance();
        $this->settingModel = new Setting();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->caseModel = new CaseModel();
        $this->postModel = new PostModel();
        $this->inquiryModel = new Inquiry();
        $this->messageModel = new Message();
        
        // Auth check
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($path !== '/admin/login' && !isset($_SESSION['admin_user'])) {
            $this->redirect('/admin/login');
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
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->bindValue(':u', $username);
        $res = $stmt->execute();
        $user = $res->fetchArray(SQLITE3_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_user'] = $user['username'];
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
        $counts = [
            'products' => (int)$this->db->querySingle("SELECT COUNT(*) FROM products"),
            'cases' => (int)$this->db->querySingle("SELECT COUNT(*) FROM cases"),
            'posts' => (int)$this->db->querySingle("SELECT COUNT(*) FROM posts"),
            'messages' => (int)$this->db->querySingle("SELECT COUNT(*) FROM messages"),
            'inquiries' => (int)$this->db->querySingle("SELECT COUNT(*) FROM inquiries"),
        ];
        $this->renderAdmin('仪表盘', $this->renderView('admin/dashboard', ['counts' => $counts]));
    }

    public function settings(): void {
        $settings = $this->settingModel->getAll();
        $this->renderAdmin('设置', $this->renderView('admin/settings', ['settings' => $settings]));
    }

    public function saveSettings(): void {
        csrf_check();
        $keys = [
            'site_name', 'site_tagline', 'company_about', 'company_address', 'company_email', 'company_phone', 
            'theme', 'default_lang', 'whatsapp', 'facebook', 'instagram', 'twitter', 'linkedin', 'youtube',
            'seo_title', 'seo_keywords', 'seo_description', 'og_image'
        ];
        foreach ($keys as $k) {
            $this->settingModel->set($k, trim((string)($_POST[$k] ?? '')));
        }
        $this->redirect('/admin/settings');
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

    // --- Categories ---
    public function categoryList(): void {
        $categories = $this->categoryModel->getAll();
        $this->renderAdmin('产品分类', $this->renderView('admin/categories/index', ['categories' => $categories]));
    }

    public function categoryCreate(): void {
        $this->renderAdmin('新建分类', $this->renderView('admin/categories/form', ['action' => '/admin/categories/create']));
    }

    public function categoryStore(): void {
        csrf_check();
        $name = trim((string)$_POST['name']);
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') $slug = slugify($name);
        $this->categoryModel->create($name, $slug);
        $this->redirect('/admin/categories');
    }

    public function categoryEdit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $category = $this->categoryModel->getById($id);
        if (!$category) $this->redirect('/admin/categories');
        $this->renderAdmin('编辑分类', $this->renderView('admin/categories/form', [
            'action' => '/admin/categories/edit?id=' . $id,
            'category' => $category
        ]));
    }

    public function categoryUpdate(): void {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $name = trim((string)$_POST['name']);
        $slug = trim((string)($_POST['slug'] ?? ''));
        if ($slug === '') $slug = slugify($name);
        $this->categoryModel->update($id, $name, $slug);
        $this->redirect('/admin/categories');
    }

    public function categoryDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->categoryModel->delete($id);
        $this->redirect('/admin/categories');
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
        $this->renderAdmin('博客管理', $this->renderView('admin/crud/index', ['items' => $items, 'label' => '博客', 'base' => '/admin/posts']));
    }

    public function postCreate(): void {
        $this->renderAdmin('新建博客', $this->renderView('admin/crud/form', ['action' => '/admin/posts/create', 'label' => '博客']));
    }

    public function postStore(): void {
        csrf_check();
        $data = $this->getGenericFormData();
        $this->postModel->create($data);
        $this->redirect('/admin/posts');
    }

    public function postEdit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->postModel->getById($id);
        if (!$item) $this->redirect('/admin/posts');
        $this->renderAdmin('编辑博客', $this->renderView('admin/crud/form', ['action' => '/admin/posts/edit?id='.$id, 'item' => $item, 'label' => '博客']));
    }

    public function postUpdate(): void {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $data = $this->getGenericFormData();
        $this->postModel->update($id, $data);
        $this->redirect('/admin/posts');
    }

    public function postDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->postModel->delete($id);
        $this->redirect('/admin/posts');
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
        ];
    }

    // --- Messages & Inquiries ---
    public function messageList(): void {
        $messages = $this->messageModel->getAll();
        $this->renderAdmin('留言列表', $this->renderView('admin/messages', ['messages' => $messages]));
    }

    public function inquiryList(): void {
        $inquiries = $this->inquiryModel->getAll();
        $this->renderAdmin('询单列表', $this->renderView('admin/inquiries', ['inquiries' => $inquiries]));
    }

    // --- AJAX ---
    public function uploadImage(): void {
        csrf_check();
        if (!isset($_FILES['image'])) $this->json(['error' => '未选择文件'], 400);
        [$ok, $result] = save_uploaded_image($_FILES['image']);
        if (!$ok) $this->json(['error' => $result], 400);
        $this->json(['url' => $result]);
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
