<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthManager;
use App\Core\Controller;
use App\Core\Database;
use App\Plugins\Jobs\JobRunner;
use App\Plugins\PluginManager;
use App\Plugins\PluginRegistry;
use App\Plugins\PluginRuntime;
use App\Models\Setting;
use App\Services\AppStoreClient;
use SQLite3;

final class PluginAdminController extends Controller {
    private PluginManager $manager;
    private PluginRegistry $registry;
    private SQLite3 $db;

    public function __construct() {
        if (!AuthManager::isAuthenticated()) $this->redirect('/admin/login');
        if (AuthManager::getUserRole() !== 'admin') $this->redirect('/admin');
        $this->manager = new PluginManager();
        $this->registry = new PluginRegistry();
        $this->db = Database::getInstance();
        $settings = new Setting();
        if ($settings->get('plugin_cron_token', '') === '') $settings->set('plugin_cron_token', bin2hex(random_bytes(24)));
    }

    public function index(): void {
        $plugins = $this->manager->all();
        foreach ($plugins as &$plugin) $plugin['available_versions'] = $this->manager->availableVersions((string)$plugin['plugin_id']);
        unset($plugin);
        $jobs = [];
        $result = $this->db->query('SELECT * FROM plugin_jobs ORDER BY id DESC LIMIT 50');
        while ($result && ($row = $result->fetchArray(SQLITE3_ASSOC))) $jobs[] = $row;
        $cronToken = (new Setting())->get('plugin_cron_token', '');
        $this->renderAdmin('插件中心', 'admin/plugins/index', [
            'plugins' => $plugins,
            'diagnostics' => $this->manager->diagnostics(),
            'logs' => $this->registry->logs('', 50),
            'jobs' => $jobs,
            'runtimeCache' => PluginRuntime::instance()->cache(),
            'cronUrl' => base_url() . '/plugin-cron?token=' . rawurlencode($cronToken),
        ]);
    }

    public function upload(): void {
        csrf_check();
        try {
            $file = $_FILES['plugin_zip'] ?? null;
            if (!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string)$file['tmp_name'])) {
                throw new \RuntimeException('请选择有效的插件 ZIP');
            }
            if (strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION)) !== 'zip') throw new \RuntimeException('仅支持 ZIP 插件包');
            $result = $this->manager->installZip((string)$file['tmp_name']);
            if (!$result['valid']) throw new \RuntimeException(implode('；', array_column($result['errors'], 'message')));
            $this->redirect('/admin/app-store/plugins?success=' . urlencode('插件已安装：' . $result['manifest']['name']));
        } catch (\Throwable $e) {
            $this->redirect('/admin/app-store/plugins?error=' . urlencode($e->getMessage()));
        }
    }

    public function market(): void {
        $this->redirect('/admin/app-store?type=plugin');
    }

    public function installMarket(): void {
        csrf_check();
        $resourceId = (int)($_POST['resource_id'] ?? 0);
        $setting = new Setting();
        $client = new AppStoreClient('https://www.shopagg.com/api/shopagg-app-store', $setting->get('app_store_api_token', ''));
        $directory = APP_ROOT . '/storage/tmp/plugins';
        if (!is_dir($directory)) mkdir($directory, 0755, true);
        $zip = $directory . '/market-' . bin2hex(random_bytes(8)) . '.zip';
        try {
            if ($resourceId <= 0) throw new \RuntimeException('资源 ID 无效');
            $download = $client->downloadResource($resourceId, base_url());
            if (!($download['ok'] ?? false)) throw new \RuntimeException((string)($download['message'] ?? '无法下载插件'));
            $payload = is_array($download['data'] ?? null) ? $download['data'] : [];
            $url = (string)($payload['download_url'] ?? '');
            if ($url === '') throw new \RuntimeException('插件市场未返回下载地址');
            $client->downloadFile($url, $zip);
            $expectedHash = strtolower((string)($payload['sha256'] ?? $payload['resource']['sha256'] ?? ''));
            if ($expectedHash !== '' && (preg_match('/^[a-f0-9]{64}$/D', $expectedHash) !== 1 || !hash_equals($expectedHash, strtolower((string)hash_file('sha256', $zip))))) {
                throw new \RuntimeException('插件包 SHA-256 校验失败');
            }
            $result = $this->manager->installZip($zip, 'app-store');
            if (!$result['valid']) throw new \RuntimeException(implode('；', array_column($result['errors'], 'message')));
            $this->redirect('/admin/app-store/plugins?success=' . urlencode('市场插件已安装：' . $result['manifest']['name']));
        } catch (\Throwable $e) {
            $this->redirect('/admin/app-store?type=plugin&error=' . urlencode($e->getMessage()));
        } finally {
            if (is_file($zip)) @unlink($zip);
        }
    }

    public function action(): void {
        csrf_check();
        $id = trim((string)($_POST['plugin_id'] ?? ''));
        $action = (string)($_POST['action'] ?? '');
        try {
            match ($action) {
                'enable' => $this->manager->enable($id),
                'disable' => $this->manager->disable($id),
                'remove' => $this->manager->remove($id, false),
                'purge' => $this->manager->remove($id, true),
                'rollback' => $this->manager->rollback($id, trim((string)($_POST['target_version'] ?? ''))),
                'rebuild' => $this->manager->rebuildCache(),
                default => throw new \RuntimeException('未知插件操作'),
            };
            $this->redirect('/admin/app-store/plugins?success=' . urlencode('操作完成'));
        } catch (\Throwable $e) {
            $this->redirect('/admin/app-store/plugins?error=' . urlencode($e->getMessage()));
        }
    }

    public function settings(): void {
        $id = trim((string)($_GET['id'] ?? ''));
        $plugin = $this->registry->find($id);
        if (!$plugin) $this->redirect('/admin/app-store/plugins?error=' . urlencode('插件不存在'));
        $values = [];
        foreach (($plugin['manifest']['settings'] ?? []) as $key => $field) $values[$key] = $this->registry->option($id, (string)$key, $field['default'] ?? null);
        $this->renderAdmin('插件设置 - ' . $plugin['name'], 'admin/plugins/settings', ['plugin' => $plugin, 'values' => $values]);
    }

    public function saveSettings(): void {
        csrf_check();
        $id = trim((string)($_POST['plugin_id'] ?? ''));
        try {
            $this->manager->saveSettings($id, $_POST);
            $this->redirect('/admin/app-store/plugins/settings?id=' . rawurlencode($id) . '&success=' . urlencode('设置已保存'));
        } catch (\Throwable $e) {
            $this->redirect('/admin/app-store/plugins/settings?id=' . rawurlencode($id) . '&error=' . urlencode($e->getMessage()));
        }
    }

    public function runJobs(): void {
        csrf_check();
        $results = (new JobRunner())->runDue(10);
        $this->redirect('/admin/app-store/plugins?success=' . urlencode('已处理 ' . count($results) . ' 个任务分片'));
    }

    public function jobAction(): void {
        csrf_check();
        $jobId = (int)($_POST['job_id'] ?? 0);
        $action = (string)($_POST['action'] ?? '');
        try {
            $runner = new JobRunner();
            match ($action) {
                'retry' => $runner->retry($jobId),
                'cancel' => $runner->cancel($jobId),
                default => throw new \RuntimeException('未知任务操作'),
            };
            $this->redirect('/admin/app-store/plugins?success=' . urlencode('任务操作完成'));
        } catch (\Throwable $e) {
            $this->redirect('/admin/app-store/plugins?error=' . urlencode($e->getMessage()));
        }
    }

    private function renderAdmin(string $title, string $view, array $data): void {
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean() ?: '';
        $showNav = true;
        require APP_ROOT . '/app/views/admin/layout.php';
    }
}
