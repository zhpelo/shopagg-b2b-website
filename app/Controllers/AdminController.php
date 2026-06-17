<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\AuthManager;
use App\Core\MediaManager;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Category;
use App\Models\PostModel;
use App\Models\Inquiry;
use App\Models\Message;
use App\Models\User;
use App\Models\Updater;
use App\Models\Slider;
use App\Models\Menu;
use App\Models\AppStoreThemeInstall;
use App\Services\AppStoreClient;
use SQLite3;

/**
 * 后台管理控制器
 * 
 * 处理后台的产品、内容、消息、设置等全部管理功能
 * 所有方法均受权限控制保护
 */
class AdminController extends Controller {

    // 数据库实例
    private SQLite3 $db;
    
    // 模型实例 - 缓存以减少创建开销
    private Setting $settingModel;
    private Product $productModel;
    private Category $categoryModel;
    private PostModel $postModel;
    private Inquiry $inquiryModel;
    private Message $messageModel;
    private User $userModel;
    private MediaManager $mediaManager;
    private Updater $updater;
    private Slider $sliderModel;
    private Menu $menuModel;
    private AppStoreThemeInstall $appStoreThemeInstallModel;

    private const THEME_REQUIRED_FILES = [
        'header.php',
        'footer.php',
        'home.php',
        'product_list.php',
        'product_detail.php',
        'post_list.php',
        'post_detail.php',
        'case_list.php',
        'case_detail.php',
        'page_detail.php',
        'contact.php',
        'about.php',
        'thanks.php',
        '404.php',
        'list.php',
        'functions.php',
        'blocks.php',
        'style.css',
    ];

    private const THEME_METADATA_FIELDS = [
        'Theme Name' => 'name',
        'Theme URI' => 'theme_uri',
        'Author' => 'author',
        'Author URI' => 'author_uri',
        'Description' => 'description',
        'Version' => 'version',
        'License' => 'license',
    ];

    /**
     * 构造函数 - 初始化模型和执行认证检查
     */
    public function __construct() {
        // 初始化数据库和所有模型
        $this->db = Database::getInstance();
        $this->settingModel = new Setting();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->postModel = new PostModel();
        $this->inquiryModel = new Inquiry();
        $this->messageModel = new Message();
        $this->userModel = new User();
        $this->mediaManager = new MediaManager();
        $this->updater = new Updater();
        $this->sliderModel = new Slider();
        $this->menuModel = new Menu();
        $this->appStoreThemeInstallModel = new AppStoreThemeInstall();
        
        // 执行认证和授权检查
        $this->performAuthChecks();
    }

    /**
     * 执行认证和授权检查
     * 
     * @return void
     */
    private function performAuthChecks(): void {
        // 获取规范化的请求路径
        $path = AuthManager::normalizePath(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

        // 检查是否有权访问该路由
        if (!AuthManager::canAccessRoute($path)) {
            // 未认证则重定向到登录页
            if (!AuthManager::isAuthenticated()) {
                $this->redirect('/admin/login');
            }
            // 无权限则重定向到首页
            $this->redirect('/admin');
        }

        if (str_starts_with($path, '/admin/appearance') && AuthManager::getUserRole() !== 'admin') {
            $this->redirect('/admin');
        }
    }

    /**
     * 显示登录页面
     */
    public function login(): void {
        if (AuthManager::isAuthenticated()) {
            $this->redirect('/admin');
        }
        $this->renderAdmin('登录', $this->renderView('admin/login'), false);
    }

    /**
     * 处理登录请求
     * 
     * @return void
     */
    public function doLogin(): void {
        $username = trim((string)$_POST['username']);
        $password = (string)$_POST['password'];
        
        // 查询用户
        $user = $this->userModel->getByUsername($username);
        
        // 验证密码
        if ($user && password_verify($password, $user['password_hash'])) {
            // 启动会话
            AuthManager::startSession($user);
            $this->redirect('/admin');
        }
        
        // 登录失败
        $errorHtml = '<div class="notification is-danger">登录失败，用户名或密码错误</div>';
        $this->renderAdmin('登录', $errorHtml . $this->renderView('admin/login'), false);
    }

    /**
     * 处理登出请求
     * 
     * @return void
     */
    public function logout(): void {
        AuthManager::destroySession();
        $this->redirect('/admin/login');
    }

    // --- Dashboard & Settings ---
    public function dashboard(): void {
        // 一次查询获取全部基础统计，减少 DB 往返
        $row = $this->db->querySingle(
            "SELECT
                (SELECT COUNT(*) FROM products WHERE deleted_at IS NULL) AS products,
                (SELECT COUNT(*) FROM products WHERE status = 'active' AND deleted_at IS NULL) AS active_products,
                (SELECT COUNT(*) FROM posts WHERE post_type = 'case') AS cases,
                (SELECT COUNT(*) FROM posts WHERE post_type = 'post') AS posts,
                (SELECT COUNT(*) FROM posts WHERE post_type = 'page') AS pages,
                (SELECT COUNT(*) FROM messages) AS messages,
                (SELECT COUNT(*) FROM inquiries) AS inquiries,
                (SELECT COUNT(*) FROM inquiries WHERE status = 'pending') AS pending_inquiries,
                (SELECT COUNT(*) FROM product_categories WHERE type = 'product' OR type IS NULL) AS categories,
                (SELECT COUNT(*) FROM product_categories WHERE type = 'post') AS post_categories,
                (SELECT COUNT(*) FROM users) AS users",
            true
        );
        $counts = $row ? array_map('intval', $row) : array_fill_keys(
            ['products', 'active_products', 'cases', 'posts', 'pages', 'messages', 'inquiries', 'pending_inquiries', 'categories', 'post_categories', 'users'], 0
        );

        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        // 今日：一次查询取留言/询单两列
        $stmt = $this->db->prepare("SELECT
            (SELECT COUNT(*) FROM messages WHERE DATE(created_at) = :d) AS m,
            (SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) = :d) AS i");
        $stmt->bindValue(':d', $today, SQLITE3_TEXT);
        $r = $stmt->execute()->fetchArray(SQLITE3_NUM);
        $counts['today_messages'] = (int)($r[0] ?? 0);
        $counts['today_inquiries'] = (int)($r[1] ?? 0);

        // 本周、本月：按区间各一次查询
        $counts['week_messages'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM messages WHERE DATE(created_at) >= '$weekStart' AND DATE(created_at) <= '$weekEnd'");
        $counts['week_inquiries'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) >= '$weekStart' AND DATE(created_at) <= '$weekEnd'");
        $counts['month_messages'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM messages WHERE DATE(created_at) >= '$monthStart' AND DATE(created_at) <= '$monthEnd'");
        $counts['month_inquiries'] = (int)$this->db->querySingle("SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) >= '$monthStart' AND DATE(created_at) <= '$monthEnd'");

        // 最近30天：两次 GROUP BY 替代 60 次单日查询
        $counts['recent_messages'] = $this->dashboardDailyCounts('messages', 30);
        $counts['recent_inquiries'] = $this->dashboardDailyCounts('inquiries', 30);

        $dbFile = APP_ROOT . '/storage/site.db';
        $counts['system_age_days'] = is_file($dbFile) ? (int)((time() - filectime($dbFile)) / 86400) : 0;

        $this->renderAdmin('仪表盘', $this->renderView('admin/dashboard', ['counts' => $counts]));
    }

    /** 返回最近 N 天每日数量数组，用于仪表盘趋势图；$table 仅允许 messages|inquiries */
    private function dashboardDailyCounts(string $table, int $days = 30): array {
        if ($table !== 'messages' && $table !== 'inquiries') {
            return array_fill(0, $days, 0);
        }
        $stmt = $this->db->prepare("SELECT DATE(created_at) AS d, COUNT(*) AS c FROM $table WHERE created_at >= DATE('now', :offset) GROUP BY d");
        $stmt->bindValue(':offset', "-$days days", SQLITE3_TEXT);
        $res = $stmt->execute();
        $byDate = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $byDate[$row['d']] = (int)$row['c'];
        }
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $out[] = $byDate[$d] ?? 0;
        }
        return $out;
    }

    private function currentAdminPath(): string {
        return AuthManager::normalizePath(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    }

    private function normalizeSettingsTab(string $tab): string {
        return in_array($tab, ['general', 'company', 'trade', 'media', 'contact', 'translate', 'custom'], true)
            ? $tab
            : 'general';
    }

    private function settingsPath(string $tab): string {
        return '/admin/settings-' . $this->normalizeSettingsTab($tab);
    }

    private function settingsTitle(string $tab): string {
        return match ($this->normalizeSettingsTab($tab)) {
            'company' => '公司简介',
            'trade' => '贸易能力',
            'media' => '公司展示',
            'contact' => '联系方式',
            'translate' => '翻译设置',
            'custom' => '自定义代码',
            default => '基础设置',
        };
    }

    /**
     * 统一规范化后台提交的 slug
     */
    private function normalizeSubmittedSlug(?string $slug, string $fallbackSource): string {
        $normalized = sanitize_slug_input((string)$slug);
        if ($normalized === '') {
            $normalized = slugify($fallbackSource);
        }

        return is_valid_slug($normalized) ? $normalized : slugify($fallbackSource);
    }

    private function resolveSettingsTab(): string {
        $path = $this->currentAdminPath();
        if (preg_match('#^/admin/settings-([a-z\-]+)$#', $path, $matches) === 1) {
            return $this->normalizeSettingsTab($matches[1]);
        }

        return 'general';
    }

    public function settings(): void {
        $tab = $this->resolveSettingsTab();
        $settings = $this->settingModel->getAll();

        $availableThemes = array_map(
            static fn(array $theme): string => $theme['slug'],
            $this->getInstalledThemes(true)
        );
        if (empty($availableThemes)) {
            $availableThemes = ['default'];
        }

        $theme = $settings['theme'] ?? 'default';
        if (!in_array($theme, $availableThemes)) $theme = $availableThemes[0];

        $translateLanguageOptions = [
            'en' => 'English',
            'zh-CN' => '简体中文',
            'zh-TW' => '繁體中文',
            'ja' => '日本語',
            'ko' => '한국어',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'ar' => 'العربية',
        ];

        $selectedTranslateLanguages = json_decode($settings['translate_languages'] ?? '[]', true);
        if (!is_array($selectedTranslateLanguages) || empty($selectedTranslateLanguages)) {
            $selectedTranslateLanguages = ['en', 'zh-CN', 'zh-TW', 'ja', 'ko', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ar'];
        }
        if (!in_array('en', $selectedTranslateLanguages, true)) {
            array_unshift($selectedTranslateLanguages, 'en');
        }

        $data = [
            'settings' => $settings,
            'tab' => $tab,
            'available_themes' => $availableThemes,
            'settings_form_action' => $this->settingsPath($tab),
            'settings_section_view' => $tab,
            'translateLanguageOptions' => $translateLanguageOptions,
            'selectedTranslateLanguages' => $selectedTranslateLanguages,
        ];

        $this->renderAdmin('系统设置 - ' . $this->settingsTitle($tab), $this->renderView('admin/settings/page', $data));
    }

    public function saveSettings(): void {
        csrf_check();
        $tab = $this->resolveSettingsTab();

        if ($tab === 'general' && isset($_POST['theme'])) {
            $validThemes = array_map(
                static fn(array $theme): string => $theme['slug'],
                $this->getInstalledThemes(true)
            );
            $submittedTheme = trim((string)$_POST['theme']);
            if (!in_array($submittedTheme, $validThemes, true)) {
                $_POST['theme'] = $this->settingModel->get('theme', 'default');
            }
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

            $this->redirect($this->settingsPath('media'));
            return;
        }

        if ($tab === 'translate') {
            $enabled = ($_POST['translate_enabled'] ?? '0') === '1' ? '1' : '0';
            $autoBrowser = ($_POST['translate_auto_browser'] ?? '0') === '1' ? '1' : '0';

            $languages = $_POST['translate_languages'] ?? [];
            if (!is_array($languages)) {
                $languages = [];
            }
            $languages = array_values(array_unique(array_filter(array_map('trim', $languages))));
            if (!in_array('en', $languages, true)) {
                array_unshift($languages, 'en');
            }

            $this->settingModel->set('translate_enabled', $enabled);
            $this->settingModel->set('translate_auto_browser', $autoBrowser);
            $this->settingModel->set('translate_languages', json_encode($languages, JSON_UNESCAPED_UNICODE));

            $this->redirect($this->settingsPath('translate'));
            return;
        }

        $groups = [
            'general' => ['site_name', 'site_tagline', 'theme', 'site_logo', 'site_favicon', 'seo_title', 'seo_keywords', 'seo_description', 'og_image'],
            'company' => ['company_bio', 'company_business_type', 'company_main_products', 'company_year_established', 'company_employees', 'company_address', 'company_plant_area', 'company_registered_capital', 'company_sgs_report', 'company_rating', 'company_response_time'],
            'trade' => ['company_main_markets', 'company_trade_staff', 'company_incoterms', 'company_payment_terms', 'company_lead_time', 'company_overseas_agent', 'company_export_year', 'company_nearest_port', 'company_rd_engineers'],
            'contact' => ['company_email', 'company_phone', 'company_address', 'whatsapp', 'facebook', 'instagram', 'twitter', 'linkedin', 'youtube'],
            'custom' => ['head_code', 'footer_code']
        ];

        $keys = $groups[$tab] ?? [];
        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                $this->settingModel->set($k, is_array($_POST[$k]) ? json_encode($_POST[$k]) : trim((string)$_POST[$k]));
            }
        }
        $this->redirect($this->settingsPath($tab));
    }

    // --- Products ---
    public function productList(): void {
        $filters = $this->getProductListFilters();
        $products = $this->productModel->getAdminList($filters);
        $categories = $this->categoryModel->getFlatTree('product');
        $counts = $this->productModel->getAdminCounts();

        $this->renderAdmin('产品管理', $this->renderView('admin/products/index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'counts' => $counts,
            'returnPath' => $this->currentAdminRequestPath(),
        ]));
    }

    public function productCreate(): void {
        $categories = $this->categoryModel->getFlatTree('product');
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
        
        $categories = $this->categoryModel->getFlatTree('product');
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
        if ($id > 0) {
            $this->productModel->softDelete($id);
        }
        $this->redirect($this->getProductReturnPath());
    }

    public function productRestore(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->productModel->restore($id);
        }
        $this->redirect($this->getProductReturnPath('/admin/products?trash=1'));
    }

    public function productPermanentDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->productModel->permanentDelete($id);
        }
        $this->redirect($this->getProductReturnPath('/admin/products?trash=1'));
    }

    public function productBulkAction(): void {
        csrf_check();
        $ids = $_POST['product_ids'] ?? [];
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $action = (string)($_POST['bulk_action'] ?? '');
        switch ($action) {
            case 'activate':
                $this->productModel->bulkUpdateStatus($ids, 'active');
                break;
            case 'deactivate':
                $this->productModel->bulkUpdateStatus($ids, 'inactive');
                break;
            case 'delete':
                $this->productModel->bulkSoftDelete($ids);
                break;
            case 'restore':
                $this->productModel->bulkRestore($ids);
                break;
            case 'permanent_delete':
                $this->productModel->bulkPermanentDelete($ids);
                break;
        }

        $this->redirect($this->getProductReturnPath());
    }

    private function getProductListFilters(): array {
        $status = (string)($_GET['status'] ?? '');
        if (!in_array($status, ['active', 'inactive', 'draft', 'archived'], true)) {
            $status = '';
        }

        $sort = (string)($_GET['sort'] ?? '');
        if (!in_array($sort, ['latest', 'oldest', 'title_asc', 'title_desc', 'updated_desc', 'updated_asc', 'status_asc', 'category_asc', 'deleted_desc'], true)) {
            $sort = '';
        }

        return [
            'q' => trim((string)($_GET['q'] ?? '')),
            'status' => $status,
            'category_id' => max(0, (int)($_GET['category_id'] ?? 0)),
            'sort' => $sort,
            'trash' => (string)($_GET['trash'] ?? '') === '1',
        ];
    }

    private function currentAdminRequestPath(): string {
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '/admin/products');
        $path = AuthManager::normalizePath(parse_url($uri, PHP_URL_PATH) ?: '/admin/products');
        $query = (string)(parse_url($uri, PHP_URL_QUERY) ?? '');
        return $path . ($query !== '' ? '?' . $query : '');
    }

    private function getProductReturnPath(string $fallback = '/admin/products'): string {
        $returnTo = trim((string)($_POST['return_to'] ?? $_GET['return_to'] ?? ''));
        if ($returnTo === '') {
            $returnTo = $fallback;
        }

        $path = AuthManager::normalizePath(parse_url($returnTo, PHP_URL_PATH) ?: '');
        if ($path !== '/admin/products') {
            return $fallback;
        }

        $query = (string)(parse_url($returnTo, PHP_URL_QUERY) ?? '');
        return $path . ($query !== '' ? '?' . $query : '');
    }

    private function getProductFormData(): array {
        $title = trim((string)$_POST['title']);
        $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $title);
        
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

        $images = array_values(array_unique(array_filter(array_map('strval', $images))));

        return [
            'title' => $title,
            'slug' => $slug,
            'summary' => trim((string)($_POST['summary'] ?? '')),
            'content' => normalize_rich_text(trim((string)($_POST['content'] ?? ''))),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'status' => trim((string)($_POST['status'] ?? 'active')),
            'product_type' => trim((string)($_POST['product_type'] ?? '')),
            'vendor' => trim((string)($_POST['vendor'] ?? '')),
            'tags' => trim((string)($_POST['tags'] ?? '')),
            'images_json' => json_encode($images),
            'seo_title' => trim((string)($_POST['seo_title'] ?? '')),
            'seo_keywords' => trim((string)($_POST['seo_keywords'] ?? '')),
            'seo_description' => trim((string)($_POST['seo_description'] ?? '')),
        ];
    }

    public function mediaLibrary(): void {
        $this->ensureMediaAccess(true);
        $directory = (string)($_GET['dir'] ?? '');
        $search = trim((string)($_GET['search'] ?? ''));
        $type = trim((string)($_GET['type'] ?? 'all'));
        $sort = trim((string)($_GET['sort'] ?? 'date_desc'));

        try {
            $this->json($this->mediaManager->getLibraryPayload($directory, $search, $type, $sort));
        } catch (\RuntimeException $e) {
            $this->json($this->mediaManager->errorResponse($e->getMessage()), 400);
        }
    }

    // --- Media Management (媒体库管理) ---
    public function mediaList(): void {
        $this->ensureMediaAccess();
        $directory = (string)($_GET['dir'] ?? '');
        $search = trim((string)($_GET['search'] ?? ''));
        $type = trim((string)($_GET['type'] ?? 'all'));
        $sort = trim((string)($_GET['sort'] ?? 'date_desc'));

        try {
            $listing = $this->mediaManager->listDirectory($directory, $search, $type, $sort);
            $summary = $this->mediaManager->summarize();

            $this->renderAdmin('媒体库管理', $this->renderView('admin/media/index', [
                'listing' => $listing,
                'summary' => $summary,
                'filters' => [
                    'search' => $search,
                    'type' => $type,
                    'sort' => $sort,
                ],
            ]));
        } catch (\RuntimeException $e) {
            $this->redirect('/admin/media?error=' . urlencode($e->getMessage()));
        }
    }

    public function mediaDelete(): void {
        $this->ensureMediaAccess();
        csrf_check();
        $directory = (string)($_POST['dir'] ?? '');
        $selectedPaths = $_POST['paths'] ?? [];
        if (!is_array($selectedPaths)) {
            $selectedPaths = [$selectedPaths];
        }
        $selectedPaths = array_values(array_filter(array_map('strval', $selectedPaths)));

        try {
            $message = '文件已删除';
            if ($selectedPaths !== []) {
                $result = $this->mediaManager->deleteFiles($selectedPaths);
                $message = '已删除 ' . $result['deleted_count'] . ' 个文件';
                if ($result['errors'] !== []) {
                    $message .= '，部分文件删除失败';
                }
            } else {
                $singlePath = trim((string)($_POST['path'] ?? ''));
                if ($singlePath === '') {
                    throw new \RuntimeException('请选择要删除的文件');
                }
                $this->mediaManager->deleteFile($singlePath);
            }

            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->successResponse([
                    'messages' => [$message],
                ]));
            }
            $this->redirect('/admin/media?dir=' . urlencode($this->mediaManager->normalizeDirectory($directory)) . '&success=' . urlencode($message));
        } catch (\RuntimeException $e) {
            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->errorResponse($e->getMessage()), 400);
            }
            $this->redirect('/admin/media?dir=' . urlencode($this->safeMediaDirectory($directory)) . '&error=' . urlencode($e->getMessage()));
        }
    }

    public function mediaUpdate(): void
    {
        $this->ensureMediaAccess();
        csrf_check();
        $directory = (string)($_POST['dir'] ?? '');

        try {
            $file = $this->mediaManager->updateFileMetadata(
                (string)($_POST['path'] ?? ''),
                (string)($_POST['title'] ?? ''),
                (string)($_POST['original_name'] ?? '')
            );
            $message = '文件信息已更新';

            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->successResponse([
                    'file' => $file,
                    'messages' => [$message],
                ]));
            }

            $this->redirect('/admin/media?dir=' . urlencode($this->safeMediaDirectory($directory)) . '&success=' . urlencode($message));
        } catch (\RuntimeException $e) {
            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->errorResponse($e->getMessage()), 400);
            }

            $this->redirect('/admin/media?dir=' . urlencode($this->safeMediaDirectory($directory)) . '&error=' . urlencode($e->getMessage()));
        }
    }

    public function mediaFolderCreate(): void
    {
        $this->ensureMediaAccess();
        csrf_check();
        $directory = (string)($_POST['dir'] ?? '');

        try {
            $folder = $this->mediaManager->createFolder($directory, (string)($_POST['folder_name'] ?? ''));
            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->successResponse([
                    'folder' => $folder,
                    'messages' => ['文件夹已创建'],
                ]));
            }
            $this->redirect('/admin/media?dir=' . urlencode($this->safeMediaDirectory($directory)) . '&success=' . urlencode('文件夹已创建'));
        } catch (\RuntimeException $e) {
            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->errorResponse($e->getMessage()), 400);
            }
            $this->redirect('/admin/media?dir=' . urlencode($this->safeMediaDirectory($directory)) . '&error=' . urlencode($e->getMessage()));
        }
    }

    public function mediaFolderDelete(): void
    {
        $this->ensureMediaAccess();
        csrf_check();

        try {
            $this->mediaManager->deleteFolder((string)($_POST['directory'] ?? ''));
            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->successResponse([
                    'messages' => ['文件夹已删除'],
                ]));
            }
            $parentDirectory = dirname($this->safeMediaDirectory((string)($_POST['directory'] ?? '')));
            $parentDirectory = $parentDirectory === '.' ? '' : $parentDirectory;
            $this->redirect('/admin/media?dir=' . urlencode($parentDirectory) . '&success=' . urlencode('文件夹已删除'));
        } catch (\RuntimeException $e) {
            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->errorResponse($e->getMessage()), 400);
            }
            $this->redirect('/admin/media?dir=' . urlencode($this->safeMediaDirectory((string)($_POST['parent_dir'] ?? ''))) . '&error=' . urlencode($e->getMessage()));
        }
    }

    public function mediaUpload(): void
    {
        $this->ensureMediaAccess();
        csrf_check();
        $directory = (string)($_POST['dir'] ?? '');

        try {
            $files = $this->collectUploadedFiles($_FILES);
            $result = $this->mediaManager->uploadFiles($files, $directory, false);
            $message = '成功上传 ' . count($result['uploaded']) . ' 个文件';
            if ($result['messages'] !== []) {
                $message .= '，部分文件失败';
            }
            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->successResponse([
                    'directory' => $result['directory'],
                    'uploaded' => $result['uploaded'],
                    'messages' => array_merge([$message], $result['messages']),
                ]));
            }
            $this->redirect('/admin/media?dir=' . urlencode($this->safeMediaDirectory($directory)) . '&success=' . urlencode($message));
        } catch (\RuntimeException $e) {
            if ($this->wantsJsonResponse()) {
                $this->json($this->mediaManager->errorResponse($e->getMessage()), 400);
            }
            $this->redirect('/admin/media?dir=' . urlencode($this->safeMediaDirectory($directory)) . '&error=' . urlencode($e->getMessage()));
        }
    }

    public function mediaConnector(): void
    {
        $this->ensureMediaAccess(true);
        $action = trim((string)($_REQUEST['action'] ?? 'files'));
        $directory = (string)($_REQUEST['path'] ?? '');
        $mods = $_REQUEST['mods'] ?? [];
        $onlyImages = is_array($mods) ? !empty($mods['onlyImages']) : false;

        try {
            switch ($action) {
                case 'files':
                    $this->json($this->mediaManager->connectorResponse($directory, 'files', $onlyImages));
                    return;

                case 'folders':
                    $this->json($this->mediaManager->connectorResponse($directory, 'folders'));
                    return;

                case 'permissions':
                    $this->json($this->mediaManager->permissionsResponse());
                    return;

                case 'folderCreate':
                    csrf_check();
                    $this->json($this->mediaManager->successResponse([
                        'messages' => ['文件夹已创建'],
                    ] + $this->mediaManager->createFolder($directory, (string)($_REQUEST['name'] ?? ''))));
                    return;

                case 'fileRemove':
                    csrf_check();
                    $path = trim((string)($_REQUEST['path'] ?? ''));
                    $name = trim((string)($_REQUEST['name'] ?? ''));
                    $target = ($path === '' || $path === '/') ? $name : trim($path, '/') . '/' . $name;
                    $this->mediaManager->deleteFile($target);
                    $this->json($this->mediaManager->successResponse([
                        'messages' => ['文件已删除'],
                    ]));
                    return;

                case 'folderRemove':
                    csrf_check();
                    $path = trim((string)($_REQUEST['path'] ?? ''));
                    $name = trim((string)($_REQUEST['name'] ?? ''));
                    $target = ($path === '' || $path === '/') ? $name : trim($path, '/') . '/' . $name;
                    $this->mediaManager->deleteFolder($target);
                    $this->json($this->mediaManager->successResponse([
                        'messages' => ['文件夹已删除'],
                    ]));
                    return;

                case 'fileUpload':
                    csrf_check();
                    $files = $this->collectUploadedFiles($_FILES);
                    $result = $this->mediaManager->uploadFiles($files, $directory, false);
                    $this->json($this->mediaManager->uploadConnectorResponse($result));
                    return;

                case 'getLocalFileByUrl':
                    $file = $this->mediaManager->getPublicPathFromUrl((string)($_REQUEST['url'] ?? ''));
                    if ($file === null) {
                        throw new \RuntimeException('文件不存在');
                    }
                    $this->json($this->mediaManager->successResponse($file));
                    return;
            }

            $this->json($this->mediaManager->errorResponse('不支持的操作'));
        } catch (\RuntimeException $e) {
            $this->json($this->mediaManager->errorResponse($e->getMessage()));
        }
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
            'base_url' => url('/admin/product-categories')
        ]));
    }

    public function productCategoryCreate(): void {
        $parentCategories = $this->categoryModel->getFlatTree('product');
        $this->renderAdmin('新建产品分类', $this->renderView('admin/categories/form', [
            'action' => '/admin/product-categories/create',
            'type' => 'product',
            'base_url' => url('/admin/product-categories'),
            'parent_categories' => $parentCategories
        ]));
    }

    public function productCategoryStore(): void {
        csrf_check();
        $name = trim((string)$_POST['name']);
        $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $name);
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
            'base_url' => url('/admin/product-categories'),
            'parent_categories' => $parentCategories
        ]));
    }

    public function productCategoryUpdate(): void {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $name = trim((string)$_POST['name']);
        $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $name);
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
            'base_url' => url('/admin/post-categories')
        ]));
    }

    public function postCategoryCreate(): void {
        $parentCategories = $this->categoryModel->getFlatTree('post');
        $this->renderAdmin('新建文章分类', $this->renderView('admin/categories/form', [
            'action' => '/admin/post-categories/create',
            'type' => 'post',
            'base_url' => url('/admin/post-categories'),
            'parent_categories' => $parentCategories
        ]));
    }

    public function postCategoryStore(): void {
        csrf_check();
        $name = trim((string)$_POST['name']);
        $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $name);
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
            'base_url' => url('/admin/post-categories'),
            'parent_categories' => $parentCategories
        ]));
    }

    public function postCategoryUpdate(): void {
        csrf_check();
        $id = (int)($_GET['id'] ?? 0);
        $name = trim((string)$_POST['name']);
        $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $name);
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

    private function normalizeContentType(string $type): string
    {
        return in_array($type, ['post', 'case', 'page'], true) ? $type : 'post';
    }

    private function contentConfig(string $type): array
    {
        $type = $this->normalizeContentType($type);

        $configs = [
            'post' => [
                'type' => 'post',
                'singular' => '文章',
                'plural' => '文章',
                'count_unit' => '篇',
                'index_title' => '文章管理',
                'form_create_title' => '新建文章',
                'form_edit_title' => '编辑文章',
                'index_url' => '/admin/posts',
                'create_url' => '/admin/posts/create',
                'edit_url' => '/admin/posts/edit',
                'delete_url' => '/admin/posts/delete',
                'preview_base' => '/blog/',
                'icon' => 'newspaper',
                'header_style' => 'background: linear-gradient(135deg, #00d1b2 0%, #48c774 100%); box-shadow: 0 10px 40px rgba(0, 209, 178, 0.3);',
                'accent_text_class' => 'text-emerald-600',
                'accent_soft_class' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
                'accent_focus_class' => 'focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100',
                'primary_button_class' => 'bg-gradient-to-r from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/25',
                'show_categories' => true,
                'category_manage_url' => '/admin/post-categories',
                'category_manage_label' => '管理文章分类',
                'list_empty_text' => '暂无文章',
                'list_empty_action' => '创建第一篇文章',
                'form_intro_create' => '创建新的博客文章',
                'form_intro_edit' => '修改文章内容',
                'content_section_title' => '文章内容',
                'content_label' => '文章内容',
                'summary_label' => '文章摘要',
                'summary_placeholder' => '输入文章摘要（用于列表展示和 SEO）',
                'slug_placeholder' => 'article-slug',
                'cover_label' => '封面图片',
                'publish_button_create' => '发布文章',
                'tips' => [
                    '标题应简洁明了，便于读者理解',
                    '摘要会显示在文章列表中',
                    '使用分类帮助读者找到相关内容',
                    '草稿状态不会在前台显示',
                ],
            ],
            'case' => [
                'type' => 'case',
                'singular' => '案例',
                'plural' => '案例',
                'count_unit' => '个',
                'index_title' => '案例管理',
                'form_create_title' => '新建案例',
                'form_edit_title' => '编辑案例',
                'index_url' => '/admin/cases',
                'create_url' => '/admin/cases/create',
                'edit_url' => '/admin/cases/edit',
                'delete_url' => '/admin/cases/delete',
                'preview_base' => '/case/',
                'icon' => 'briefcase',
                'header_style' => 'background: linear-gradient(135deg, #06b6d4 0%, #10b981 100%); box-shadow: 0 10px 40px rgba(6, 182, 212, 0.28);',
                'accent_text_class' => 'text-cyan-700',
                'accent_soft_class' => 'bg-cyan-50 text-cyan-700 hover:bg-cyan-100',
                'accent_focus_class' => 'focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100',
                'primary_button_class' => 'bg-gradient-to-r from-cyan-500 to-emerald-500 shadow-lg shadow-cyan-500/25',
                'show_categories' => false,
                'category_manage_url' => null,
                'category_manage_label' => '',
                'list_empty_text' => '暂无案例',
                'list_empty_action' => '创建第一个案例',
                'form_intro_create' => '创建新的案例内容',
                'form_intro_edit' => '修改案例内容',
                'content_section_title' => '案例内容',
                'content_label' => '案例内容',
                'summary_label' => '案例摘要',
                'summary_placeholder' => '输入案例摘要（用于列表展示和 SEO）',
                'slug_placeholder' => 'case-slug',
                'cover_label' => '案例封面',
                'publish_button_create' => '发布案例',
                'tips' => [
                    '标题尽量突出案例亮点与行业场景',
                    '摘要可简要概括客户需求和成果',
                    '封面建议使用高质量项目图片',
                    '草稿状态不会在前台显示',
                ],
            ],
            'page' => [
                'type' => 'page',
                'singular' => '页面',
                'plural' => '页面',
                'count_unit' => '个',
                'index_title' => '页面管理',
                'form_create_title' => '新建页面',
                'form_edit_title' => '编辑页面',
                'index_url' => '/admin/pages',
                'create_url' => '/admin/pages/create',
                'edit_url' => '/admin/pages/edit',
                'delete_url' => '/admin/pages/delete',
                'preview_base' => '/page/',
                'icon' => 'file-lines',
                'header_style' => 'background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); box-shadow: 0 10px 40px rgba(99, 102, 241, 0.28);',
                'accent_text_class' => 'text-indigo-600',
                'accent_soft_class' => 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
                'accent_focus_class' => 'focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100',
                'primary_button_class' => 'bg-gradient-to-r from-indigo-500 to-violet-500 shadow-lg shadow-indigo-500/25',
                'show_categories' => false,
                'category_manage_url' => null,
                'category_manage_label' => '',
                'list_empty_text' => '暂无页面',
                'list_empty_action' => '创建第一个页面',
                'form_intro_create' => '创建新的独立页面',
                'form_intro_edit' => '修改页面内容',
                'content_section_title' => '页面内容',
                'content_label' => '页面内容',
                'summary_label' => '页面摘要',
                'summary_placeholder' => '输入页面摘要（用于列表展示和 SEO）',
                'slug_placeholder' => 'page-slug',
                'cover_label' => '页面封面',
                'publish_button_create' => '发布页面',
                'tips' => [
                    '页面适合放置关于我们、服务、FAQ 等固定内容',
                    '别名建议简短，便于后续作为独立页面路径使用',
                    '摘要可用于页面列表和搜索引擎描述',
                    '草稿状态不会在前台显示',
                ],
            ],
        ];

        return $configs[$type];
    }

    private function renderContentList(string $type): void
    {
        $config = $this->contentConfig($type);
        $items = $this->postModel->getList(0, false, $config['type']);

        $this->renderAdmin(
            $config['index_title'],
            $this->renderView('admin/posts/index', [
                'items' => $items,
                'contentConfig' => $config,
            ])
        );
    }

    private function renderContentForm(string $type, ?array $item = null): void
    {
        $config = $this->contentConfig($type);
        $categories = $config['show_categories'] ? $this->categoryModel->getFlatTree('post') : [];
        $action = $item === null
            ? $config['create_url']
            : $config['edit_url'] . '?id=' . (int)$item['id'];

        $this->renderAdmin(
            $item === null ? $config['form_create_title'] : $config['form_edit_title'],
            $this->renderView('admin/posts/form', [
                'action' => $action,
                'item' => $item,
                'categories' => $categories,
                'contentConfig' => $config,
            ])
        );
    }

    private function getContentFormData(string $type): array
    {
        $config = $this->contentConfig($type);
        $title = trim((string)($_POST['title'] ?? ''));
        $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $title);

        return [
            'title' => $title,
            'slug' => $slug,
            'post_type' => $config['type'],
            'summary' => trim((string)($_POST['summary'] ?? '')),
            'content' => normalize_rich_text(trim((string)($_POST['content'] ?? ''))),
            'cover' => trim((string)($_POST['cover'] ?? '')),
            'category_id' => $config['show_categories'] ? (int)($_POST['category_id'] ?? 0) : 0,
            'status' => trim((string)($_POST['status'] ?? 'active')),
            'seo_title' => trim((string)($_POST['seo_title'] ?? '')),
            'seo_keywords' => trim((string)($_POST['seo_keywords'] ?? '')),
            'seo_description' => trim((string)($_POST['seo_description'] ?? '')),
        ];
    }

    // --- Cases / Posts / Pages ---
    public function caseList(): void
    {
        $this->renderContentList('case');
    }

    public function caseCreate(): void
    {
        $this->renderContentForm('case');
    }

    public function caseStore(): void
    {
        csrf_check();
        $config = $this->contentConfig('case');
        $this->postModel->create($this->getContentFormData('case'));
        $this->redirect($config['index_url']);
    }

    public function caseEdit(): void
    {
        $config = $this->contentConfig('case');
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->postModel->getById($id, 'case');
        if (!$item) {
            $this->redirect($config['index_url']);
        }
        $this->renderContentForm('case', $item);
    }

    public function caseUpdate(): void
    {
        csrf_check();
        $config = $this->contentConfig('case');
        $id = (int)($_GET['id'] ?? 0);
        $this->postModel->update($id, $this->getContentFormData('case'), 'case');
        $this->redirect($config['index_url']);
    }

    public function caseDelete(): void
    {
        $config = $this->contentConfig('case');
        $id = (int)($_GET['id'] ?? 0);
        $this->postModel->delete($id, 'case');
        $this->redirect($config['index_url']);
    }

    public function postList(): void
    {
        $this->renderContentList('post');
    }

    public function postCreate(): void
    {
        $this->renderContentForm('post');
    }

    public function postStore(): void
    {
        csrf_check();
        $config = $this->contentConfig('post');
        $this->postModel->create($this->getContentFormData('post'));
        $this->redirect($config['index_url']);
    }

    public function postEdit(): void
    {
        $config = $this->contentConfig('post');
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->postModel->getById($id, 'post');
        if (!$item) {
            $this->redirect($config['index_url']);
        }
        $this->renderContentForm('post', $item);
    }

    public function postUpdate(): void
    {
        csrf_check();
        $config = $this->contentConfig('post');
        $id = (int)($_GET['id'] ?? 0);
        $this->postModel->update($id, $this->getContentFormData('post'), 'post');
        $this->redirect($config['index_url']);
    }

    public function postDelete(): void
    {
        $config = $this->contentConfig('post');
        $id = (int)($_GET['id'] ?? 0);
        $this->postModel->delete($id, 'post');
        $this->redirect($config['index_url']);
    }

    public function pageList(): void
    {
        $this->renderContentList('page');
    }

    public function pageCreate(): void
    {
        $this->renderContentForm('page');
    }

    public function pageStore(): void
    {
        csrf_check();
        $config = $this->contentConfig('page');
        $this->postModel->create($this->getContentFormData('page'));
        $this->redirect($config['index_url']);
    }

    public function pageEdit(): void
    {
        $config = $this->contentConfig('page');
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->postModel->getById($id, 'page');
        if (!$item) {
            $this->redirect($config['index_url']);
        }
        $this->renderContentForm('page', $item);
    }

    public function pageUpdate(): void
    {
        csrf_check();
        $config = $this->contentConfig('page');
        $id = (int)($_GET['id'] ?? 0);
        $this->postModel->update($id, $this->getContentFormData('page'), 'page');
        $this->redirect($config['index_url']);
    }

    public function pageDelete(): void
    {
        $config = $this->contentConfig('page');
        $id = (int)($_GET['id'] ?? 0);
        $this->postModel->delete($id, 'page');
        $this->redirect($config['index_url']);
    }

    // --- Messages & Inquiries ---
    public function messageList(): void {
        $messages = $this->messageModel->getAll();
        $this->renderAdmin('留言列表', $this->renderView('admin/messages', ['messages' => $messages]));
    }

    public function messageDetail(): void {
        $id = (int)($_GET['id'] ?? 0);
        $message = $this->messageModel->getById($id);
        if (!$message) {
            $this->redirect('/admin/messages');
            return;
        }
        $this->renderAdmin('留言详情', $this->renderView('admin/message_detail', ['message' => $message]));
    }

    public function messageDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->messageModel->delete($id);
        }
        $this->redirect('/admin/messages?success=留言已删除');
    }

    public function inquiryList(): void {
        $status = $_GET['status'] ?? '';
        $inquiries = $this->inquiryModel->getList(['status' => $status]);
        $this->renderAdmin('询单管理', $this->renderView('admin/inquiries', [
            'inquiries' => $inquiries,
            'current_status' => $status
        ]));
    }

    public function inquiryDetail(): void {
        $id = (int)($_GET['id'] ?? 0);
        $inquiry = $this->inquiryModel->getById($id);
        if (!$inquiry) {
            $this->redirect('/admin/inquiries');
            return;
        }
        $this->renderAdmin('询单详情', $this->renderView('admin/inquiry_detail', ['inquiry' => $inquiry]));
    }

    public function inquiryUpdateStatus(): void {
        $id = (int)($_GET['id'] ?? 0);
        $status = $_GET['status'] ?? '';
        $redirect = $_GET['redirect'] ?? '/admin/inquiries';
        if ($id && in_array($status, ['pending', 'contacted', 'quoted', 'closed'])) {
            $this->inquiryModel->updateStatus($id, $status);
        }
        $this->redirect($redirect);
    }

    public function inquiryDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->inquiryModel->delete($id);
        }
        $this->redirect('/admin/inquiries?success=询单已删除');
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
        $this->ensureMediaAccess(true);
        csrf_check();
        try {
            $files = $this->collectUploadedFiles($_FILES);
            $result = $this->mediaManager->uploadFiles($files, null, true);
            $first = $result['uploaded'][0] ?? null;
            if ($first === null) {
                throw new \RuntimeException('上传失败');
            }

            $this->json([
                'url' => $first['public_path'],
                'item' => $first,
            ]);
        } catch (\RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    private function wantsJsonResponse(): bool
    {
        if ((string)($_REQUEST['response_format'] ?? '') === 'json') {
            return true;
        }

        $requestedWith = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        if ($requestedWith === 'xmlhttprequest') {
            return true;
        }

        $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
        return str_contains($accept, 'application/json');
    }

    private function ensureMediaAccess(bool $json = false): void
    {
        if ($_SESSION['admin_role'] === 'admin') {
            return;
        }

        $allowedPermissions = ['products', 'blog', 'cases', 'settings'];
        $userPermissions = array_filter(explode(',', (string)($_SESSION['admin_permissions'] ?? '')));

        foreach ($allowedPermissions as $permission) {
            if (in_array($permission, $userPermissions, true)) {
                return;
            }
        }

        if ($json) {
            $this->json(['success' => false, 'data' => ['messages' => ['无权访问媒体库']]], 403);
        }

        $this->redirect('/admin');
    }

    private function safeMediaDirectory(string $directory): string
    {
        try {
            return $this->mediaManager->normalizeDirectory($directory);
        } catch (\RuntimeException) {
            return '';
        }
    }

    private function collectUploadedFiles(array $uploadedFiles): array
    {
        $collected = [];
        foreach ($uploadedFiles as $fileSpec) {
            $this->flattenUploadedFileSpec($fileSpec, $collected);
        }

        return $collected;
    }

    private function flattenUploadedFileSpec(array $fileSpec, array &$collected): void
    {
        $name = $fileSpec['name'] ?? null;
        if (is_array($name)) {
            foreach (array_keys($name) as $key) {
                $this->flattenUploadedFileSpec([
                    'name' => $fileSpec['name'][$key] ?? '',
                    'type' => $fileSpec['type'][$key] ?? '',
                    'tmp_name' => $fileSpec['tmp_name'][$key] ?? '',
                    'error' => $fileSpec['error'][$key] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $fileSpec['size'][$key] ?? 0,
                ], $collected);
            }
            return;
        }

        if (($fileSpec['name'] ?? '') === '') {
            return;
        }

        $collected[] = $fileSpec;
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
        $username = trim((string)$_POST['username']);
        if ($username !== '' && !preg_match('/^[A-Za-z0-9_]+$/', $username)) {
            $_SESSION['flash_error'] = '用户名仅支持字母、数字和下划线';
            $this->redirect('/admin/staff/edit?id=' . $id);
            return;
        }
        if ($username !== '') {
            $existing = $this->userModel->getByUsername($username);
            if ($existing && (int)$existing['id'] !== $id) {
                $_SESSION['flash_error'] = '该用户名已被使用';
                $this->redirect('/admin/staff/edit?id=' . $id);
                return;
            }
        }
        $data = [
            'username' => $username,
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
        $username = trim((string)($_POST['username'] ?? ''));
        if ($username !== '' && !preg_match('/^[A-Za-z0-9_]+$/', $username)) {
            $_SESSION['flash_error'] = '用户名仅支持字母、数字和下划线';
            $this->redirect('/admin/profile');
            return;
        }
        if ($username !== '') {
            $existing = $this->userModel->getByUsername($username);
            if ($existing && (int)$existing['id'] !== $id) {
                $_SESSION['flash_error'] = '该用户名已被使用';
                $this->redirect('/admin/profile');
                return;
            }
        }
        $data = [
            'display_name' => trim((string)$_POST['display_name'])
        ];
        if ($username !== '') {
            $data['username'] = $username;
        }
        if (!empty($_POST['password'])) {
            $data['password'] = (string)$_POST['password'];
        }
        $this->userModel->update($id, $data);
        $_SESSION['admin_display_name'] = $data['display_name'];
        if (isset($data['username'])) {
            $_SESSION['admin_username'] = $data['username'];
        }
        $this->redirect('/admin/profile');
    }

    // --- Rendering Helpers ---
    private function renderAdmin(string $title, string $content, bool $showNav = true): void {
        include APP_ROOT . '/app/views/admin/layout.php';
    }

    private function renderView(string $view, array $data = []): string {
        extract($data);
        ob_start();
        include APP_ROOT . '/app/views/' . $view . '.php';
        return ob_get_clean();
    }

    // --- Program Updater (程序更新) ---
    
    /**
     * 显示更新管理页面
     */
    public function updaterIndex(): void {
        // 检查权限：仅管理员可访问
        if ($_SESSION['admin_role'] !== 'admin') {
            $this->redirect('/admin');
        }
        
        $checkResult = $this->updater->checkUpdate();
        $releases = $this->updater->getReleases(1, 20);
        $history = $this->updater->getUpdateHistory();
        $backups = $this->updater->getBackups();
        $migrationStatus = $this->updater->getMigrationStatus();
        
        $this->renderAdmin('程序更新', $this->renderView('admin/updater/index', [
            'checkResult' => $checkResult,
            'releases' => $releases,
            'history' => $history,
            'backups' => $backups,
            'migrationStatus' => $migrationStatus,
        ]));
    }
    
    /**
     * AJAX：下载更新包
     */
    public function updaterDownload(): void {
        if ($_SESSION['admin_role'] !== 'admin') {
            $this->json(['success' => false, 'message' => '无权访问'], 403);
        }
        
        csrf_check();
        
        $version = trim((string)($_POST['version'] ?? ''));
        if ($version === '') {
            $this->json(['success' => false, 'message' => '版本号不能为空']);
        }
        
        $result = $this->updater->downloadSourceZip($version);
        $this->json($result);
    }
    
    /**
     * AJAX：安装更新
     */
    public function updaterInstall(): void {
        if ($_SESSION['admin_role'] !== 'admin') {
            $this->json(['success' => false, 'message' => '无权访问'], 403);
        }
        
        csrf_check();
        
        $version = trim((string)($_POST['version'] ?? ''));
        $filepath = trim((string)($_POST['filepath'] ?? ''));
        
        if ($version === '' || $filepath === '') {
            $this->json(['success' => false, 'message' => '参数不完整']);
        }
        
        // 安全检查：确保文件路径在允许的目录内
        $realpath = realpath($filepath);
        $allowedDir = realpath(APP_ROOT . '/storage/updates');
        if ($realpath === false || !str_starts_with($realpath, $allowedDir)) {
            $this->json(['success' => false, 'message' => '无效的文件路径']);
        }
        
        $result = $this->updater->installUpdate($version, $filepath);
        $this->json($result);
    }
    
    /**
     * AJAX：删除备份
     */
    public function updaterDeleteBackup(): void {
        if ($_SESSION['admin_role'] !== 'admin') {
            $this->json(['success' => false, 'message' => '无权访问'], 403);
        }
        
        csrf_check();
        
        $filename = trim((string)($_POST['filename'] ?? ''));
        if ($filename === '') {
            $this->json(['success' => false, 'message' => '文件名不能为空']);
        }
        
        $result = $this->updater->deleteBackup($filename);
        $this->json(['success' => $result]);
    }
    
    /**
     * AJAX：获取迁移状态
     */
    public function updaterMigrationStatus(): void {
        if ($_SESSION['admin_role'] !== 'admin') {
            $this->json(['success' => false, 'message' => '无权访问'], 403);
        }
        
        $status = $this->updater->getMigrationStatus();
        $this->json(['success' => true, 'data' => $status]);
    }
    
    /**
     * AJAX：手动执行数据库迁移
     */
    public function updaterRunMigrations(): void {
        if ($_SESSION['admin_role'] !== 'admin') {
            $this->json(['success' => false, 'message' => '无权访问'], 403);
        }
        
        csrf_check();
        
        $result = $this->updater->runMigrations();
        $this->json($result);
    }

    // --- Appearance / Themes Management (外观区块 - 网站模版) ---

    /**
     * 网站模版列表
     */
    public function themeList(): void {
        $themes = $this->getInstalledThemes();
        $appStore = $this->buildAppStoreThemeState($themes);
        $currentTheme = $this->settingModel->get('theme', 'default');
        $currentThemeName = $currentTheme;
        foreach ($themes as $theme) {
            if ($theme['is_active']) {
                $currentThemeName = $theme['name'];
                break;
            }
        }

        $this->renderAdmin('网站模版', $this->renderView('admin/themes/index', [
            'themes' => $themes,
            'currentTheme' => $currentTheme,
            'currentThemeName' => $currentThemeName,
            'appStore' => $appStore,
        ]));
    }

    /**
     * 网站主题上传页面
     */
    public function themeUploadForm(): void {
        $this->renderAdmin('上传主题', $this->renderView('admin/themes/upload', [
            'requiredFiles' => self::THEME_REQUIRED_FILES,
        ]));
    }

    /**
     * App Store B2B 网站主题详情
     */
    public function themeAppStoreDetail(string $id): void {
        $resourceId = (int)$id;
        if ($resourceId <= 0) {
            $this->redirect('/admin/appearance/themes?error=' . urlencode('请选择要查看的 App Store 主题'));
        }

        $client = $this->makeAppStoreClient();
        $response = $client->getB2BTheme($resourceId);
        if (!$response['ok'] || !is_array($response['resource'] ?? null)) {
            $this->redirect('/admin/appearance/themes?error=' . urlencode($response['message'] ?? '无法获取 App Store 主题详情'));
        }

        $themes = $this->getInstalledThemes();
        $installRecords = $this->appStoreThemeInstallModel->allIndexedByResourceId();
        $resource = $this->enrichAppStoreThemeResource(
            $response['resource'],
            $installRecords,
            $this->indexInstalledThemesBySlug($themes)
        );

        $appStore = [
            'has_token' => $client->hasToken(),
            'masked_token' => $client->maskedToken(),
            'site_domain' => $this->appStoreLicenseDomain(),
            'wechat_pay' => $this->appStoreWechatPayState(),
        ];

        $title = '主题详情 - ' . (string)($resource['name'] ?? 'App Store B2B 网站主题');
        $this->renderAdmin($title, $this->renderView('admin/themes/detail', [
            'theme' => $resource,
            'appStore' => $appStore,
        ]));
    }

    /**
     * 上传网站主题 zip 压缩包
     */
    public function themeUpload(): void {
        csrf_check();

        try {
            $file = $_FILES['theme_zip'] ?? null;
            if (!is_array($file)) {
                throw new \RuntimeException('请选择一个 zip 压缩包');
            }

            $result = $this->installThemeFromUpload($file);
            $this->redirect('/admin/appearance/themes?success=' . urlencode('主题已上传：' . $result['name']));
        } catch (\RuntimeException $e) {
            $this->redirect('/admin/appearance/themes/upload?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * 启用指定主题
     */
    public function themeActivate(): void {
        csrf_check();

        $theme = trim((string)($_POST['theme'] ?? ''));
        $returnTo = $this->safeThemeReturnPath();
        $validThemes = array_map(
            static fn(array $item): string => $item['slug'],
            $this->getInstalledThemes(true)
        );

        if ($theme === '' || !in_array($theme, $validThemes, true)) {
            $this->redirect($this->themePathWithMessage($returnTo, 'error', '无法启用该主题，主题不存在或不符合要求'));
        }

        $this->settingModel->set('theme', $theme);

        $themeName = $theme;
        foreach ($this->getInstalledThemes(true) as $item) {
            if ($item['slug'] === $theme) {
                $themeName = $item['name'];
                break;
            }
        }

        $this->redirect($this->themePathWithMessage($returnTo, 'success', '已启用主题：' . $themeName));
    }

    /**
     * 删除未启用的本地网站主题
     */
    public function themeDelete(): void {
        csrf_check();

        $submittedTheme = trim((string)($_POST['theme'] ?? ''));
        $theme = sanitize_slug_input($submittedTheme);

        try {
            if ($theme === '' || $theme !== $submittedTheme) {
                throw new \RuntimeException('请选择要删除的主题');
            }

            $themePath = $this->resolveDeletableThemePath($theme);
            $themeName = $theme;
            foreach ($this->getInstalledThemes() as $item) {
                if ($item['slug'] === $theme) {
                    $themeName = (string)$item['name'];
                    break;
                }
            }

            $this->deleteDirectory($themePath);
            if (is_dir($themePath)) {
                throw new \RuntimeException('主题目录删除失败，请检查文件权限');
            }

            $this->appStoreThemeInstallModel->deleteByThemeSlug($theme);
            $this->redirect('/admin/appearance/themes?success=' . urlencode('主题已删除：' . $themeName));
        } catch (\RuntimeException $e) {
            $this->redirect('/admin/appearance/themes?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * 保存 App Store 连接配置
     */
    public function themeAppStoreSettings(): void {
        csrf_check();

        $apiToken = trim((string)($_POST['api_token'] ?? ''));
        $clearToken = isset($_POST['clear_token']) && (string)$_POST['clear_token'] === '1';

        if ($clearToken) {
            $this->settingModel->set('app_store_api_token', '');
            unset($_SESSION['app_store_wechat_pay']);
            $this->redirect('/admin/appearance/themes?success=' . urlencode('已解除当前站点的 ShopAGG 账户绑定'));
        }

        if ($apiToken === '') {
            $this->redirect('/admin/appearance/themes?error=' . urlencode('请输入 App Store API Token'));
        }

        $client = new AppStoreClient($this->defaultAppStoreApiBase(), $apiToken);
        $accountResponse = $client->me();
        if (!$accountResponse['ok']) {
            $this->redirect('/admin/appearance/themes?error=' . urlencode('Token 验证失败：' . $this->appStoreResponseMessage($accountResponse)));
        }

        $account = is_array($accountResponse['data'] ?? null) ? $accountResponse['data'] : [];
        $accountLabel = (string)($account['email'] ?? $account['name'] ?? 'ShopAGG 账户');

        $this->settingModel->set('app_store_api_token', $apiToken);

        $this->redirect('/admin/appearance/themes?success=' . urlencode('当前站点已绑定 ShopAGG 账户：' . $accountLabel));
    }

    /**
     * 从 App Store 下载并安装 B2B 主题
     */
    public function themeAppStoreInstall(): void {
        csrf_check();

        $resourceId = (int)($_POST['resource_id'] ?? 0);
        $returnTo = $this->safeThemeReturnPath();
        if ($resourceId <= 0) {
            $this->redirect($this->themePathWithMessage($returnTo, 'error', '请选择要安装的 App Store 主题'));
        }

        $client = $this->makeAppStoreClient();
        $domain = $this->appStoreLicenseDomain();
        $workspace = $this->createTempDirectory('app-store-theme-');
        $zipPath = $workspace . '/theme.zip';
        $redirectUrl = $returnTo;

        try {
            $download = $client->downloadResource($resourceId, $domain);
            if (!$download['ok']) {
                throw new \RuntimeException($this->appStoreResponseMessage($download));
            }

            $payload = is_array($download['data'] ?? null) ? $download['data'] : [];
            $downloadUrl = (string)($payload['download_url'] ?? '');
            if ($downloadUrl === '') {
                throw new \RuntimeException('App Store 未返回可下载地址');
            }

            $resource = is_array($payload['resource'] ?? null) ? $payload['resource'] : [];
            $resourceSlug = sanitize_slug_input((string)($resource['slug'] ?? ''));
            if ($resourceSlug === '') {
                $resourceSlug = 'app-store-theme-' . $resourceId;
            }

            $client->downloadFile($downloadUrl, $zipPath);

            $installed = $this->installThemeFromArchivePath(
                $zipPath,
                (string)($resource['slug'] ?? ('app-store-theme-' . $resourceId)) . '.zip',
                true,
                $resourceSlug
            );

            $this->appStoreThemeInstallModel->saveInstall([
                'resource_id' => $resourceId,
                'resource_slug' => $resourceSlug,
                'theme_slug' => $installed['slug'],
                'name' => (string)($resource['name'] ?? $installed['name']),
                'version' => (string)($resource['version'] ?? $installed['version'] ?? ''),
                'bound_domain' => (string)($payload['resource']['bound_domain'] ?? $domain),
            ]);

            $message = '主题已安装：' . ($resource['name'] ?? $installed['name']);
            $redirectUrl = $this->themePathWithMessage($returnTo, 'success', $message);
        } catch (\RuntimeException $e) {
            $redirectUrl = $this->themePathWithMessage($returnTo, 'error', $e->getMessage());
        } finally {
            $this->deleteDirectory($workspace);
        }

        $this->redirect($redirectUrl);
    }

    /**
     * 为付费 App Store 主题创建订单并进入支付
     */
    public function themeAppStorePurchase(): void {
        csrf_check();

        $resourceId = (int)($_POST['resource_id'] ?? 0);
        $returnTo = $this->safeThemeReturnPath();
        $paymentMethod = (string)($_POST['payment_method'] ?? 'alipay');
        if (!in_array($paymentMethod, ['alipay', 'wechat'], true)) {
            $paymentMethod = 'alipay';
        }

        if ($resourceId <= 0) {
            $this->redirect($this->themePathWithMessage($returnTo, 'error', '请选择要购买的 App Store 主题'));
        }

        $client = $this->makeAppStoreClient();

        try {
            $orderResponse = $client->createOrder($resourceId);
            if (!$orderResponse['ok']) {
                throw new \RuntimeException($this->appStoreResponseMessage($orderResponse));
            }

            $orderPayload = is_array($orderResponse['data'] ?? null) ? $orderResponse['data'] : [];
            if (!empty($orderPayload['owned'])) {
                $this->redirect($this->themePathWithMessage($returnTo, 'success', '该主题已拥有授权，可以直接下载安装'));
            }

            $order = is_array($orderPayload['order'] ?? null) ? $orderPayload['order'] : [];
            $orderId = (string)($order['id'] ?? '');
            if ($orderId === '') {
                throw new \RuntimeException('App Store 订单创建成功但未返回订单号');
            }

            $payResponse = $client->payOrder($orderId, $paymentMethod);
            if (!$payResponse['ok']) {
                throw new \RuntimeException($this->appStoreResponseMessage($payResponse));
            }

            $payPayload = is_array($payResponse['data'] ?? null) ? $payResponse['data'] : [];
            if ($paymentMethod === 'alipay') {
                $formHtml = (string)($payPayload['form_html'] ?? '');
                if ($formHtml === '') {
                    throw new \RuntimeException('支付宝支付表单生成失败');
                }

                $this->renderAppStoreAlipayForm($formHtml);
            }

            $codeUrl = (string)($payPayload['code_url'] ?? '');
            if ($codeUrl === '') {
                throw new \RuntimeException('微信支付二维码生成失败');
            }

            $_SESSION['app_store_wechat_pay'] = [
                'order_id' => $orderId,
                'resource_name' => (string)($orderPayload['resource_name'] ?? 'B2B 网站主题'),
                'code_url' => $codeUrl,
                'created_at' => time(),
            ];

            $this->redirect($this->themePathWithMessage($returnTo, 'success', '微信支付订单已创建，请在页面顶部查看支付链接'));
        } catch (\RuntimeException $e) {
            $this->redirect($this->themePathWithMessage($returnTo, 'error', $e->getMessage()));
        }
    }

    private function buildAppStoreThemeState(array $installedThemes): array {
        $client = $this->makeAppStoreClient();
        $installRecords = $this->appStoreThemeInstallModel->allIndexedByResourceId();
        $installedBySlug = $this->indexInstalledThemesBySlug($installedThemes);

        $state = [
            'has_token' => $client->hasToken(),
            'masked_token' => $client->maskedToken(),
            'site_domain' => $this->appStoreLicenseDomain(),
            'account' => null,
            'account_error' => '',
            'themes' => [],
            'error' => '',
            'wechat_pay' => $this->appStoreWechatPayState(),
        ];

        if ($client->hasToken()) {
            $accountResponse = $client->me();
            if ($accountResponse['ok'] && is_array($accountResponse['data'] ?? null)) {
                $state['account'] = $accountResponse['data'];
            } else {
                $state['account_error'] = $this->appStoreResponseMessage($accountResponse);
            }
        }

        $response = $client->listB2BThemes();
        if (!$response['ok']) {
            $state['error'] = $response['message'] ?: '无法获取 App Store B2B 网站主题';
            return $state;
        }

        foreach ($response['themes'] as $resource) {
            if (!is_array($resource)) {
                continue;
            }

            $state['themes'][] = $this->enrichAppStoreThemeResource($resource, $installRecords, $installedBySlug);
        }

        return $state;
    }

    private function enrichAppStoreThemeResource(array $resource, array $installRecords, array $installedBySlug): array {
        $resourceId = (int)($resource['id'] ?? 0);
        $resourceSlug = sanitize_slug_input((string)($resource['slug'] ?? ''));
        $record = $resourceId > 0 ? ($installRecords[$resourceId] ?? null) : null;
        $knownSlug = (string)($record['theme_slug'] ?? $resourceSlug);
        $localTheme = $installedBySlug[$knownSlug] ?? ($installedBySlug[$resourceSlug] ?? null);
        $installedVersion = (string)($record['version'] ?? ($localTheme['version'] ?? ''));
        $remoteVersion = (string)($resource['version'] ?? '');
        $needsUpdate = $localTheme !== null
            && $installedVersion !== ''
            && $remoteVersion !== ''
            && version_compare($remoteVersion, $installedVersion, '>');

        $resource['_install_record'] = $record;
        $resource['_local_theme'] = $localTheme;
        $resource['_installed'] = $localTheme !== null;
        $resource['_installed_slug'] = (string)($localTheme['slug'] ?? $knownSlug);
        $resource['_installed_version'] = $installedVersion;
        $resource['_needs_update'] = $needsUpdate;

        return $resource;
    }

    private function indexInstalledThemesBySlug(array $installedThemes): array {
        $installedBySlug = [];
        foreach ($installedThemes as $theme) {
            $installedBySlug[(string)$theme['slug']] = $theme;
        }

        return $installedBySlug;
    }

    private function appStoreWechatPayState(): ?array {
        if (isset($_SESSION['app_store_wechat_pay']['created_at']) && (time() - (int)$_SESSION['app_store_wechat_pay']['created_at']) > 1800) {
            unset($_SESSION['app_store_wechat_pay']);
        }

        return is_array($_SESSION['app_store_wechat_pay'] ?? null) ? $_SESSION['app_store_wechat_pay'] : null;
    }

    private function safeThemeReturnPath(string $fallback = '/admin/appearance/themes'): string {
        $returnTo = trim((string)($_POST['return_to'] ?? ''));
        if ($returnTo === '') {
            return $fallback;
        }

        $parts = parse_url($returnTo);
        if (!is_array($parts)) {
            return $fallback;
        }

        $path = (string)($parts['path'] ?? '');
        $basePath = base_path();
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        if (!str_starts_with($path, '/admin/appearance/themes')) {
            return $fallback;
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
        return $path . $query;
    }

    private function themePathWithMessage(string $path, string $key, string $message): string {
        $separator = str_contains($path, '?') ? '&' : '?';
        return $path . $separator . rawurlencode($key) . '=' . urlencode($message);
    }

    private function resolveDeletableThemePath(string $theme): string {
        if ($theme === 'default') {
            throw new \RuntimeException('默认主题不能删除');
        }

        if ($theme === $this->settingModel->get('theme', 'default')) {
            throw new \RuntimeException('当前启用主题不能删除');
        }

        $themesDirectory = $this->getThemesDirectory();
        $basePath = realpath($themesDirectory);
        if ($basePath === false || !is_dir($basePath)) {
            throw new \RuntimeException('themes 目录不存在');
        }

        $themePath = $themesDirectory . '/' . $theme;
        if (!is_dir($themePath)) {
            throw new \RuntimeException('主题目录不存在');
        }

        if (is_link($themePath)) {
            throw new \RuntimeException('不能删除符号链接主题目录');
        }

        $realThemePath = realpath($themePath);
        if ($realThemePath === false || $realThemePath === $basePath || !str_starts_with($realThemePath, $basePath . DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException('主题目录路径不合法');
        }

        return $realThemePath;
    }

    private function makeAppStoreClient(): AppStoreClient {
        return new AppStoreClient(
            $this->defaultAppStoreApiBase(),
            $this->settingModel->get('app_store_api_token', '')
        );
    }

    private function appStoreLicenseDomain(): string {
        return base_url();
    }

    private function defaultAppStoreApiBase(): string {
        return 'http://v3.shopagg.test/api/shopagg-app-store';
    }

    private function appStoreResponseMessage(array $response): string {
        $data = $response['data'] ?? null;
        if (is_array($data) && isset($data['message'])) {
            return (string)$data['message'];
        }

        if (($response['message'] ?? '') !== '') {
            return (string)$response['message'];
        }

        $status = (int)($response['status'] ?? 0);
        return $status > 0 ? 'App Store 请求失败，HTTP ' . $status : 'App Store 请求失败';
    }

    private function renderAppStoreAlipayForm(string $formHtml): void {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>正在跳转支付宝</title>';
        echo '<style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#f8fafc;color:#0f172a;margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh}.box{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:32px;box-shadow:0 18px 50px rgba(15,23,42,.08);max-width:520px;text-align:center}.box p{color:#64748b;line-height:1.7}</style>';
        echo '</head><body><div class="box"><h1>正在跳转支付宝</h1><p>支付完成后返回网站模版页面，刷新后即可下载安装已授权主题。</p>';
        echo $formHtml;
        echo '</div></body></html>';
        exit;
    }

    /**
     * 读取 themes 目录下的主题列表
     */
    private function getInstalledThemes(bool $validOnly = false): array {
        $themesDir = $this->getThemesDirectory();
        $currentTheme = $this->settingModel->get('theme', 'default');
        $themes = [];

        if (!is_dir($themesDir)) {
            return $themes;
        }

        $directories = glob($themesDir . '/*');
        if ($directories === false) {
            return $themes;
        }

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $slug = basename($directory);
            if ($slug === '' || str_starts_with($slug, '.')) {
                continue;
            }

            $theme = $this->inspectThemeDirectory($directory, $slug, $currentTheme);
            if ($validOnly && !$theme['is_valid']) {
                continue;
            }

            $themes[] = $theme;
        }

        usort($themes, static function (array $left, array $right): int {
            if ($left['is_active'] !== $right['is_active']) {
                return $left['is_active'] ? -1 : 1;
            }

            return strnatcasecmp($left['name'], $right['name']);
        });

        return $themes;
    }

    /**
     * 读取单个主题目录信息
     */
    private function inspectThemeDirectory(string $themePath, string $slug, string $currentTheme): array {
        $validation = $this->validateThemeDirectory($themePath);
        $metadata = $validation['metadata'];
        $previewPath = $themePath . '/screenshot.jpg';

        return [
            'slug' => $slug,
            'name' => $metadata['name'] !== '' ? $metadata['name'] : $slug,
            'description' => $metadata['description'],
            'version' => $metadata['version'],
            'author' => $metadata['author'],
            'author_uri' => $metadata['author_uri'],
            'theme_uri' => $metadata['theme_uri'],
            'license' => $metadata['license'],
            'preview_url' => is_file($previewPath) ? asset_url('/themes/' . rawurlencode($slug) . '/screenshot.jpg') : null,
            'is_active' => $slug === $currentTheme,
            'is_valid' => $validation['valid'],
            'missing_files' => $validation['missing_files'],
            'metadata_errors' => $validation['metadata_errors'],
            'errors' => $validation['errors'],
        ];
    }

    /**
     * 校验主题目录是否满足系统要求
     */
    private function validateThemeDirectory(string $themePath): array {
        $missingFiles = [];
        foreach (self::THEME_REQUIRED_FILES as $requiredFile) {
            if (!is_file($themePath . '/' . $requiredFile)) {
                $missingFiles[] = $requiredFile;
            }
        }

        $styleFile = $themePath . '/style.css';
        $metadata = is_file($styleFile)
            ? $this->parseThemeMetadataFromFile($styleFile)
            : $this->emptyThemeMetadata();

        $metadataErrors = [];
        if (($metadata['name'] ?? '') === '') {
            $metadataErrors[] = 'style.css 缺少 Theme Name 头部信息';
        }

        $errors = [];
        if ($missingFiles !== []) {
            $errors[] = '缺少必需文件：' . implode('、', $missingFiles);
        }
        if ($metadataErrors !== []) {
            $errors = array_merge($errors, $metadataErrors);
        }

        return [
            'valid' => $missingFiles === [] && $metadataErrors === [],
            'missing_files' => $missingFiles,
            'metadata_errors' => $metadataErrors,
            'errors' => $errors,
            'metadata' => $metadata,
        ];
    }

    /**
     * 解析 style.css 顶部主题元数据
     */
    private function parseThemeMetadataFromFile(string $styleFile): array {
        $content = file_get_contents($styleFile, false, null, 0, 8192);
        if ($content === false) {
            return $this->emptyThemeMetadata();
        }

        $metadata = $this->emptyThemeMetadata();
        foreach (self::THEME_METADATA_FIELDS as $label => $key) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($label, '/') . ':\s*(.+)$/mi', $content, $matches) === 1) {
                $metadata[$key] = trim((string)$matches[1]);
            }
        }

        return $metadata;
    }

    /**
     * 返回空主题元数据结构
     */
    private function emptyThemeMetadata(): array {
        return [
            'name' => '',
            'theme_uri' => '',
            'author' => '',
            'author_uri' => '',
            'description' => '',
            'version' => '',
            'license' => '',
        ];
    }

    /**
     * 安装上传的主题压缩包
     */
    private function installThemeFromUpload(array $file): array {
        $this->assertValidThemeUpload($file);

        return $this->installThemeFromArchivePath((string)$file['tmp_name'], (string)$file['name']);
    }

    /**
     * 从 zip 文件安装主题。App Store 安装允许覆盖同资源 slug 的已安装主题。
     */
    private function installThemeFromArchivePath(string $archivePath, string $originalName, bool $allowReplace = false, ?string $preferredSlug = null): array {
        $archive = $this->openThemeArchive($archivePath);
        $workspace = $this->createTempDirectory('theme-upload-');
        $replaceWorkspace = null;

        try {
            $this->extractThemeArchive($archive, $workspace);
            $package = $this->findThemePackage($workspace);

            if (!$package['validation']['valid']) {
                throw new \RuntimeException('此文件不符合网站主题的要求：' . implode('；', $package['validation']['errors']));
            }

            $metadata = $package['validation']['metadata'];
            $slug = $preferredSlug !== null && trim($preferredSlug) !== ''
                ? $this->sanitizeThemeDirectoryName($preferredSlug)
                : $this->resolveInstalledThemeSlug($package['path'], $workspace, $originalName, $metadata);

            $targetPath = $this->getThemesDirectory() . '/' . $slug;
            if (file_exists($targetPath)) {
                if (!$allowReplace || !is_dir($targetPath)) {
                    throw new \RuntimeException('此文件不符合网站主题的要求：主题目录已存在，请先删除同名主题');
                }

                $replaceWorkspace = $this->createTempDirectory('theme-replace-');
                $backupPath = $replaceWorkspace . '/' . $slug;
                $this->moveDirectory($targetPath, $backupPath);
            }

            $this->ensureDirectory($this->getThemesDirectory());

            try {
                $this->moveDirectory($package['path'], $targetPath);
            } catch (\RuntimeException $e) {
                if (isset($backupPath) && is_dir($backupPath) && !file_exists($targetPath)) {
                    $this->moveDirectory($backupPath, $targetPath);
                }
                throw $e;
            }

            return [
                'slug' => $slug,
                'name' => $metadata['name'] !== '' ? $metadata['name'] : $slug,
                'version' => $metadata['version'] ?? '',
            ];
        } finally {
            $archive->close();
            $this->deleteDirectory($workspace);
            if ($replaceWorkspace !== null) {
                $this->deleteDirectory($replaceWorkspace);
            }
        }
    }

    /**
     * 选择压缩包中的唯一主题目录
     */
    private function findThemePackage(string $workspace): array {
        $candidates = $this->collectThemeCandidateDirectories($workspace);
        if ($candidates === []) {
            throw new \RuntimeException('此文件不符合网站主题的要求：压缩包中未找到主题目录或 style.css');
        }

        $inspections = [];
        $validPackages = [];

        foreach ($candidates as $candidate) {
            $inspection = [
                'path' => $candidate,
                'validation' => $this->validateThemeDirectory($candidate),
            ];
            $inspections[] = $inspection;

            if ($inspection['validation']['valid']) {
                $validPackages[] = $inspection;
            }
        }

        if (count($validPackages) > 1) {
            throw new \RuntimeException('此文件不符合网站主题的要求：压缩包中包含多个可安装主题');
        }

        if (count($validPackages) === 1) {
            return $validPackages[0];
        }

        usort($inspections, fn(array $left, array $right): int => $this->relativeThemeCandidateDepth($workspace, $left['path']) <=> $this->relativeThemeCandidateDepth($workspace, $right['path']));

        return $inspections[0];
    }

    /**
     * 收集压缩包里包含 style.css 的候选主题目录
     */
    private function collectThemeCandidateDirectories(string $workspace): array {
        $candidates = [];

        if (is_file($workspace . '/style.css')) {
            $candidates[$workspace] = $workspace;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($workspace, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile() || strtolower($fileInfo->getFilename()) !== 'style.css') {
                continue;
            }

            $candidate = $fileInfo->getPath();
            $relativePath = ltrim(str_replace($workspace, '', $candidate), DIRECTORY_SEPARATOR);
            if ($this->isIgnoredThemeExtractPath($relativePath)) {
                continue;
            }

            $candidates[$candidate] = $candidate;
        }

        return array_values($candidates);
    }

    /**
     * 计算候选主题目录相对深度，用于优先取最靠近根目录的候选项
     */
    private function relativeThemeCandidateDepth(string $workspace, string $candidate): int {
        $relativePath = trim(str_replace($workspace, '', $candidate), DIRECTORY_SEPARATOR);
        if ($relativePath === '') {
            return 0;
        }

        return substr_count($relativePath, DIRECTORY_SEPARATOR) + 1;
    }

    /**
     * 检查上传文件基础合法性
     */
    private function assertValidThemeUpload(array $file): void {
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException($this->themeUploadErrorMessage($error));
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \RuntimeException('上传文件无效，请重新选择 zip 压缩包');
        }

        $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension !== 'zip') {
            throw new \RuntimeException('仅支持上传 zip 格式的主题压缩包');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string)$finfo->file($tmpName);
        if ($mimeType !== '' && !str_contains($mimeType, 'zip') && $mimeType !== 'application/octet-stream') {
            throw new \RuntimeException('仅支持上传 zip 格式的主题压缩包');
        }
    }

    /**
     * 打开 zip 压缩包
     */
    private function openThemeArchive(string $tmpName): \ZipArchive {
        $archive = new \ZipArchive();
        if ($archive->open($tmpName) !== true) {
            throw new \RuntimeException('无法读取 zip 压缩包，请检查文件是否损坏');
        }

        return $archive;
    }

    /**
     * 安全解压主题压缩包到临时目录
     */
    private function extractThemeArchive(\ZipArchive $archive, string $workspace): void {
        $this->ensureDirectory($workspace);

        for ($i = 0; $i < $archive->numFiles; $i++) {
            $entryName = $archive->getNameIndex($i);
            if ($entryName === false) {
                continue;
            }

            $normalized = $this->normalizeThemeArchiveEntry($entryName);
            if ($normalized === null || $this->isIgnoredThemeExtractPath($normalized['path'])) {
                continue;
            }

            $targetPath = $workspace . '/' . $normalized['path'];
            if ($normalized['is_dir']) {
                $this->ensureDirectory($targetPath);
                continue;
            }

            $this->ensureDirectory(dirname($targetPath));

            $stream = $archive->getStream($entryName);
            if ($stream === false) {
                throw new \RuntimeException('无法解压主题压缩包，请重新打包后上传');
            }

            $output = fopen($targetPath, 'wb');
            if ($output === false) {
                fclose($stream);
                throw new \RuntimeException('无法写入 themes 临时目录，请检查文件权限');
            }

            stream_copy_to_stream($stream, $output);
            fclose($stream);
            fclose($output);
        }
    }

    /**
     * 规范化 zip 条目路径，阻止目录穿越
     */
    private function normalizeThemeArchiveEntry(string $entryName): ?array {
        $entryName = str_replace('\\', '/', $entryName);
        $isDirectory = str_ends_with($entryName, '/');
        $trimmed = trim($entryName, '/');

        if ($trimmed === '') {
            return null;
        }

        $segments = explode('/', $trimmed);
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new \RuntimeException('此文件不符合网站主题的要求：压缩包包含非法目录结构');
            }
        }

        return [
            'path' => implode('/', $segments),
            'is_dir' => $isDirectory,
        ];
    }

    /**
     * 忽略压缩包中的系统垃圾文件
     */
    private function isIgnoredThemeExtractPath(string $relativePath): bool {
        if ($relativePath === '') {
            return false;
        }

        $segments = explode('/', str_replace('\\', '/', $relativePath));
        foreach ($segments as $segment) {
            if ($segment === '__MACOSX' || $segment === '.DS_Store') {
                return true;
            }
        }

        return false;
    }

    /**
     * 生成最终主题目录名
     */
    private function resolveInstalledThemeSlug(string $candidatePath, string $workspace, string $originalName, array $metadata): string {
        $candidateName = basename($candidatePath);
        if ($candidatePath === $workspace) {
            $candidateName = (string)($metadata['name'] !== '' ? $metadata['name'] : pathinfo($originalName, PATHINFO_FILENAME));
        }

        return $this->sanitizeThemeDirectoryName($candidateName);
    }

    /**
     * 清洗主题目录名，仅保留安全字符
     */
    private function sanitizeThemeDirectoryName(string $name): string {
        $slug = sanitize_slug_input($name);

        if ($slug === '') {
            $slug = 'theme-' . date('YmdHis');
        }

        return $slug;
    }

    /**
     * 创建临时目录
     */
    private function createTempDirectory(string $prefix): string {
        $baseDirectory = APP_ROOT . '/storage/tmp';
        $this->ensureDirectory($baseDirectory);

        $directory = $baseDirectory . '/' . $prefix . bin2hex(random_bytes(8));
        $this->ensureDirectory($directory);

        return $directory;
    }

    /**
     * 确保目录存在
     */
    private function ensureDirectory(string $directory): void {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('无法创建目录：' . $directory);
        }
    }

    /**
     * 移动目录，rename 失败时退回复制
     */
    private function moveDirectory(string $source, string $destination): void {
        if (@rename($source, $destination)) {
            return;
        }

        $this->copyDirectory($source, $destination);
        $this->deleteDirectory($source);
    }

    /**
     * 递归复制目录
     */
    private function copyDirectory(string $source, string $destination): void {
        $this->ensureDirectory($destination);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $destination . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                $this->ensureDirectory($targetPath);
                continue;
            }

            $this->ensureDirectory(dirname($targetPath));
            if (!copy($item->getPathname(), $targetPath)) {
                throw new \RuntimeException('无法复制主题文件：' . $item->getFilename());
            }
        }
    }

    /**
     * 递归删除目录
     */
    private function deleteDirectory(string $directory): void {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
                continue;
            }

            @unlink($item->getPathname());
        }

        @rmdir($directory);
    }

    /**
     * 主题上传错误提示
     */
    private function themeUploadErrorMessage(int $errorCode): string {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => '上传失败：zip 文件超出服务器允许大小',
            UPLOAD_ERR_PARTIAL => '上传失败：zip 文件只上传了一部分',
            UPLOAD_ERR_NO_FILE => '请选择一个 zip 压缩包',
            UPLOAD_ERR_NO_TMP_DIR => '上传失败：服务器缺少临时目录',
            UPLOAD_ERR_CANT_WRITE => '上传失败：服务器无法写入上传文件',
            UPLOAD_ERR_EXTENSION => '上传失败：服务器扩展阻止了文件上传',
            default => '上传失败，请稍后重试',
        };
    }

    /**
     * themes 目录路径
     */
    private function getThemesDirectory(): string {
        return APP_ROOT . '/themes';
    }

    // --- Appearance / Sliders Management (外观区块 - 轮播图管理) ---
    
    /**
     * 轮播图列表
     */
    public function sliderList(): void {
        $sliders = $this->sliderModel->getAll();
        // 为每个轮播图加载图片数量
        foreach ($sliders as &$slider) {
            $slider['item_count'] = count($this->sliderModel->getItems((int)$slider['id']));
        }
        $this->renderAdmin('轮播图管理', $this->renderView('admin/sliders/index', ['sliders' => $sliders]));
    }

    /**
     * 创建轮播图页面
     */
    public function sliderCreate(): void {
        $this->renderAdmin('新建轮播图', $this->renderView('admin/sliders/form', [
            'action' => '/admin/appearance/sliders/create',
            'slider' => null,
            'items' => []
        ]));
    }

    /**
     * 保存新轮播图
     */
    public function sliderStore(): void {
        csrf_check();
        
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $name);
        
        $sliderData = [
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string)($_POST['description'] ?? '')),
            'status' => $_POST['status'] ?? 'active',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
        
        $sliderId = $this->sliderModel->create($sliderData);
        
        // 处理轮播图片
        $this->processSliderItems($sliderId);
        
        $this->redirect('/admin/appearance/sliders');
    }

    /**
     * 编辑轮播图页面
     */
    public function sliderEdit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $slider = $this->sliderModel->getWithItems($id);
        if (!$slider) {
            $this->redirect('/admin/appearance/sliders');
        }
        
        $this->renderAdmin('编辑轮播图', $this->renderView('admin/sliders/form', [
            'action' => '/admin/appearance/sliders/edit?id=' . $id,
            'slider' => $slider,
            'items' => $slider['items'] ?? []
        ]));
    }

    /**
     * 更新轮播图
     */
    public function sliderUpdate(): void {
        csrf_check();
        
        $id = (int)($_GET['id'] ?? 0);
        $slider = $this->sliderModel->getById($id);
        if (!$slider) {
            $this->redirect('/admin/appearance/sliders');
        }
        
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = (string)($slider['slug'] ?? '');
        if ($slug === '') {
            $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $name);
        }
        
        $sliderData = [
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string)($_POST['description'] ?? '')),
            'status' => $_POST['status'] ?? 'active',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
        
        $this->sliderModel->update($id, $sliderData);
        
        // 处理轮播图片（先删除旧的，再添加新的）
        $this->sliderModel->deleteAllItems($id);
        $this->processSliderItems($id);
        
        $this->redirect('/admin/appearance/sliders');
    }

    /**
     * 删除轮播图
     */
    public function sliderDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->sliderModel->delete($id);
        }
        $this->redirect('/admin/appearance/sliders?success=轮播图已删除');
    }

    /**
     * 处理轮播图片数据
     */
    private function processSliderItems(int $sliderId): void {
        $images = $_POST['item_image'] ?? [];
        $titles = $_POST['item_title'] ?? [];
        $subtitles = $_POST['item_subtitle'] ?? [];
        $linkUrls = $_POST['item_link_url'] ?? [];
        $linkTexts = $_POST['item_link_text'] ?? [];
        $sortOrders = $_POST['item_sort_order'] ?? [];
        
        foreach ($images as $index => $image) {
            if (empty($image)) {
                continue;
            }
            
            $itemData = [
                'image' => $image,
                'title' => $titles[$index] ?? '',
                'subtitle' => $subtitles[$index] ?? '',
                'link_url' => $linkUrls[$index] ?? '',
                'link_text' => $linkTexts[$index] ?? 'View Details',
                'sort_order' => (int)($sortOrders[$index] ?? $index),
                'status' => 'active',
            ];
            
            $this->sliderModel->addItem($sliderId, $itemData);
        }
    }

    // --- Appearance / Menus Management (外观区块 - 菜单管理) ---

    // --- Appearance / Blocks Management (外观区块 - 模板区块配置) ---

    /**
     * 模板区块配置页面
     */
    public function blockList(): void {
        $theme = $this->settingModel->get('theme', 'default');
        $definitions = get_block_definitions($theme);
        $userValues = get_user_block_values($theme);

        $this->renderAdmin('模板区块配置', $this->renderView('admin/blocks/index', [
            'definitions' => $definitions,
            'userValues'  => $userValues,
            'theme'       => $theme,
        ]));
    }

    /**
     * 保存模板区块配置
     */
    public function blockSave(): void {
        csrf_check();

        $theme = trim((string)($_POST['theme'] ?? 'default'));
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $theme)) {
            $this->redirect('/admin/appearance/blocks');
            return;
        }

        $definitions = get_block_definitions($theme);
        $existingValues = get_user_block_values($theme);
        $submitted = $_POST['blocks'] ?? [];
        $resetBlocks = $_POST['reset_blocks'] ?? [];

        $newValues = [];

        foreach ($definitions as $blockKey => $block) {
            // 如果勾选了重置此区块，跳过（不保存自定义值）
            if (in_array($blockKey, $resetBlocks, true)) {
                continue;
            }

            $fields = $block['fields'] ?? [];
            foreach ($fields as $fieldKey => $field) {
                $val = trim((string)($submitted[$blockKey][$fieldKey] ?? ''));
                $default = $field['default'] ?? '';
                // 只保存与默认值不同的值
                if ($val !== '' && $val !== $default) {
                    $newValues[$blockKey][$fieldKey] = $val;
                } elseif ($val === '' && isset($existingValues[$blockKey][$fieldKey])) {
                    // 用户清空了，不保存（回退到默认）
                } elseif ($val === $default) {
                    // 与默认值相同，不保存
                }
            }
        }

        save_user_block_values($theme, $newValues);

        $this->redirect('/admin/appearance/blocks?success=' . urlencode('区块配置已保存'));
    }

    /**
     * 菜单列表
     */
    public function menuList(): void {
        $menus = $this->menuModel->getAll();
        foreach ($menus as &$menu) {
            $menu['item_count'] = count($this->menuModel->getItems((int)$menu['id']));
        }
        $this->renderAdmin('菜单管理', $this->renderView('admin/menus/index', ['menus' => $menus]));
    }

    /**
     * 创建菜单页面
     */
    public function menuCreate(): void {
        $this->renderAdmin('新建菜单', $this->renderView('admin/menus/form', [
            'action' => '/admin/appearance/menus/create',
            'menu' => null,
            'flatItems' => []
        ]));
    }

    /**
     * 保存新菜单
     */
    public function menuStore(): void {
        csrf_check();
        
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $name);
        
        $menuData = [
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string)($_POST['description'] ?? '')),
            'status' => $_POST['status'] ?? 'active',
            'sort_order' => 0,
        ];
        
        $menuId = $this->menuModel->create($menuData);
        $this->redirect('/admin/appearance/menus/edit?id=' . $menuId . '&success=' . urlencode('菜单已创建，请添加菜单项'));
    }

    /**
     * 编辑菜单页面
     */
    public function menuEdit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $menu = $this->menuModel->getWithItems($id, false);
        if (!$menu) {
            $this->redirect('/admin/appearance/menus');
        }
        
        $flatItems = $this->menuModel->getItems((int)$menu['id']);
        
        $this->renderAdmin('编辑菜单', $this->renderView('admin/menus/form', [
            'action' => '/admin/appearance/menus/edit?id=' . $id,
            'menu' => $menu,
            'flatItems' => $flatItems
        ]));
    }

    /**
     * 更新菜单 - 支持 JSON 树形结构提交
     */
    public function menuUpdate(): void {
        csrf_check();
        
        $id = (int)($_GET['id'] ?? 0);
        $menu = $this->menuModel->getById($id);
        if (!$menu) {
            $this->redirect('/admin/appearance/menus');
        }
        
        // 更新菜单基本信息
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = $this->normalizeSubmittedSlug((string)($_POST['slug'] ?? ''), $name);
        
        $menuData = [
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string)($_POST['description'] ?? '')),
            'status' => $_POST['status'] ?? 'active',
            'sort_order' => 0,
        ];
        $this->menuModel->update($id, $menuData);
        
        // 处理 JSON 格式的菜单项数据
        $jsonItems = $_POST['menu_items_json'] ?? '';
        if ($jsonItems !== '') {
            $treeItems = json_decode($jsonItems, true);
            if (is_array($treeItems)) {
                // 收集现有项 ID
                $existingItems = $this->menuModel->getItems($id);
                $existingIds = array_column($existingItems, 'id');
                $processedIds = [];
                
                // 递归处理树形结构
                $this->processMenuItemsTree($id, $treeItems, 0, $processedIds);
                
                // 删除不再存在的菜单项
                foreach ($existingIds as $existingId) {
                    if (!in_array((int)$existingId, $processedIds)) {
                        $this->menuModel->deleteItem((int)$existingId);
                    }
                }
            }
        }
        
        $this->redirect('/admin/appearance/menus/edit?id=' . $id . '&success=' . urlencode('菜单已保存'));
    }

    /**
     * 递归处理菜单项树形结构
     */
    private function processMenuItemsTree(int $menuId, array $items, int $parentId, array &$processedIds): void {
        foreach ($items as $sortOrder => $item) {
            $title = trim((string)($item['title'] ?? ''));
            $url = trim((string)($item['url'] ?? ''));
            if ($title === '' || $url === '') {
                continue;
            }
            
            $itemData = [
                'title' => $title,
                'url' => $url,
                'target' => $item['target'] ?? '_self',
                'css_class' => $item['css_class'] ?? '',
                'sort_order' => $sortOrder,
                'parent_id' => $parentId,
            ];
            
            $itemId = (int)($item['id'] ?? 0);
            
            if ($itemId > 0) {
                // 更新已存在的菜单项
                $this->menuModel->updateItem($itemId, $itemData);
                $processedIds[] = $itemId;
            } else {
                // 新增菜单项（ID 为负数或 0 表示新建）
                $itemId = $this->menuModel->addItem($menuId, array_merge($itemData, ['status' => 'active']));
                $processedIds[] = $itemId;
            }
            
            // 处理子项
            if (!empty($item['children']) && is_array($item['children'])) {
                $this->processMenuItemsTree($menuId, $item['children'], $itemId, $processedIds);
            }
        }
    }

    /**
     * 删除菜单
     */
    public function menuDelete(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->menuModel->delete($id);
        }
        $this->redirect('/admin/appearance/menus?success=菜单已删除');
    }
}
