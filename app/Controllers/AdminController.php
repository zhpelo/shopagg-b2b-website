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
use App\Models\CaseModel;
use App\Models\PostModel;
use App\Models\Inquiry;
use App\Models\Message;
use App\Models\User;
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
    private CaseModel $caseModel;
    private PostModel $postModel;
    private Inquiry $inquiryModel;
    private Message $messageModel;
    private User $userModel;
    private MediaManager $mediaManager;

    /**
     * 构造函数 - 初始化模型和执行认证检查
     */
    public function __construct() {
        // 初始化数据库和所有模型
        $this->db = Database::getInstance();
        $this->settingModel = new Setting();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->caseModel = new CaseModel();
        $this->postModel = new PostModel();
        $this->inquiryModel = new Inquiry();
        $this->messageModel = new Message();
        $this->userModel = new User();
        $this->mediaManager = new MediaManager();
        
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
                (SELECT COUNT(*) FROM products) AS products,
                (SELECT COUNT(*) FROM products WHERE status = 'active') AS active_products,
                (SELECT COUNT(*) FROM cases) AS cases,
                (SELECT COUNT(*) FROM posts) AS posts,
                (SELECT COUNT(*) FROM messages) AS messages,
                (SELECT COUNT(*) FROM inquiries) AS inquiries,
                (SELECT COUNT(*) FROM inquiries WHERE status = 'pending') AS pending_inquiries,
                (SELECT COUNT(*) FROM product_categories WHERE type = 'product' OR type IS NULL) AS categories,
                (SELECT COUNT(*) FROM product_categories WHERE type = 'post') AS post_categories,
                (SELECT COUNT(*) FROM users) AS users",
            true
        );
        $counts = $row ? array_map('intval', $row) : array_fill_keys(
            ['products', 'active_products', 'cases', 'posts', 'messages', 'inquiries', 'pending_inquiries', 'categories', 'post_categories', 'users'], 0
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

        $dbFile = APP_ROOT . '/#data/site.db';
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
        
        // Scan for available themes
        $themesDir = APP_ROOT . '/themes';
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
            'content' => normalize_rich_text(trim((string)($_POST['content'] ?? ''))),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'status' => trim((string)($_POST['status'] ?? 'active')),
            'product_type' => trim((string)($_POST['product_type'] ?? '')),
            'vendor' => trim((string)($_POST['vendor'] ?? '')),
            'tags' => trim((string)($_POST['tags'] ?? '')),
            'banner_image' => trim((string)($_POST['banner_image'] ?? '')),
            'images_json' => json_encode(array_values(array_unique(array_filter($images)))),
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
            'base_url' => url('/admin/product-categories'),
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
            'base_url' => url('/admin/post-categories'),
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
        $this->renderAdmin('案例管理', $this->renderView('admin/cases/index', ['items' => $items, 'label' => '案例', 'base' => '/admin/cases']));
    }

    public function caseCreate(): void {
        $this->renderAdmin('新建案例', $this->renderView('admin/cases/form', ['action' => '/admin/cases/create', 'label' => '案例']));
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
        $this->renderAdmin('编辑案例', $this->renderView('admin/cases/form', ['action' => '/admin/cases/edit?id='.$id, 'item' => $item, 'label' => '案例']));
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
            'content' => normalize_rich_text(trim((string)($_POST['content'] ?? ''))),
            'cover' => trim((string)($_POST['cover'] ?? '')),
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
            'content' => normalize_rich_text(trim((string)($_POST['content'] ?? ''))),
            'cover' => trim((string)($_POST['cover'] ?? '')),
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
        include APP_ROOT . '/app/views/admin/layout.php';
    }

    private function renderView(string $view, array $data = []): string {
        extract($data);
        ob_start();
        include APP_ROOT . '/app/views/' . $view . '.php';
        return ob_get_clean();
    }
}
