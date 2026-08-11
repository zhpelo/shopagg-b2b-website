<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthManager;
use App\Core\Controller;
use App\Models\AppStoreThemeInstall;
use App\Models\Setting;
use App\Plugins\PluginManager;
use App\Services\AppStoreClient;

final class AppStoreController extends Controller {
    private const CATALOG_CACHE_KEY = 'app_store_catalog_cache_v1';
    private const CATALOG_CACHE_TTL = 300;

    public function __construct() {
        if (!AuthManager::isAuthenticated()) $this->redirect('/admin/login');
        if (AuthManager::getUserRole() !== 'admin') $this->redirect('/admin');
    }

    public function index(): void {
        $query = trim((string)($_GET['q'] ?? ''));
        $type = in_array($_GET['type'] ?? 'all', ['all', 'plugin', 'theme'], true) ? (string)($_GET['type'] ?? 'all') : 'all';
        $status = in_array($_GET['status'] ?? 'all', ['all', 'available', 'installed', 'update'], true) ? (string)($_GET['status'] ?? 'all') : 'all';
        $price = in_array($_GET['price'] ?? 'all', ['all', 'free', 'paid'], true) ? (string)($_GET['price'] ?? 'all') : 'all';

        $settings = new Setting();
        $client = new AppStoreClient('https://www.shopagg.com/api/shopagg-app-store', $settings->get('app_store_api_token', ''));
        $catalog = $this->loadCatalog($client, $settings, (string)($_GET['refresh'] ?? '') === '1');
        $themeResponse = $catalog['themes'];
        $pluginResponse = $catalog['plugins'];
        $items = array_merge(
            $this->normalizeThemes($themeResponse['themes'] ?? []),
            $this->normalizePlugins($pluginResponse['plugins'] ?? [])
        );

        $counts = ['all' => count($items), 'plugin' => 0, 'theme' => 0, 'installed' => 0, 'update' => 0];
        foreach ($items as $item) {
            $counts[$item['type']]++;
            if ($item['installed']) $counts['installed']++;
            if ($item['needs_update']) $counts['update']++;
        }

        $items = array_values(array_filter($items, static function (array $item) use ($query, $type, $status, $price): bool {
            if ($type !== 'all' && $item['type'] !== $type) return false;
            if ($status === 'installed' && !$item['installed']) return false;
            if ($status === 'available' && $item['installed']) return false;
            if ($status === 'update' && !$item['needs_update']) return false;
            if ($price === 'free' && !$item['is_free']) return false;
            if ($price === 'paid' && $item['is_free']) return false;
            if ($query === '') return true;
            $haystack = mb_strtolower(implode(' ', [$item['name'], $item['slug'], $item['vendor'], $item['description'], implode(' ', $item['tags'])]));
            return str_contains($haystack, mb_strtolower($query));
        }));
        usort($items, static fn(array $a, array $b): int => [$a['type'], mb_strtolower($a['name'])] <=> [$b['type'], mb_strtolower($b['name'])]);

        $this->renderAdmin('应用商店', 'admin/app-store/index', [
            'items' => $items,
            'counts' => $counts,
            'filters' => compact('query', 'type', 'status', 'price'),
            'hasToken' => $client->hasToken(),
            'catalogStatus' => [
                'source' => $catalog['source'],
                'fetched_at' => $catalog['fetched_at'],
                'notice' => $catalog['notice'],
            ],
            'errors' => array_values(array_filter([
                trim((string)($_GET['error'] ?? '')),
                ($themeResponse['ok'] ?? false) ? '' : '主题：' . ($themeResponse['message'] ?? '暂时无法连接应用商店'),
                ($pluginResponse['ok'] ?? false) ? '' : '插件：' . ($pluginResponse['message'] ?? '暂时无法连接应用商店'),
            ])),
        ]);
    }

    private function loadCatalog(AppStoreClient $client, Setting $settings, bool $forceRefresh): array {
        $cached = json_decode($settings->get(self::CATALOG_CACHE_KEY, ''), true);
        $hasCache = is_array($cached)
            && is_array($cached['themes'] ?? null)
            && is_array($cached['plugins'] ?? null)
            && (int)($cached['fetched_at'] ?? 0) > 0;
        $cacheAge = $hasCache ? time() - (int)$cached['fetched_at'] : PHP_INT_MAX;

        if (!$forceRefresh && $hasCache && $cacheAge < self::CATALOG_CACHE_TTL) {
            return [
                'themes' => $cached['themes'],
                'plugins' => $cached['plugins'],
                'source' => 'cache',
                'fetched_at' => (int)$cached['fetched_at'],
                'notice' => '',
            ];
        }

        $remote = $client->listCatalog();
        $themes = $remote['themes'];
        $plugins = $remote['plugins'];
        if (($themes['ok'] ?? false) && ($plugins['ok'] ?? false)) {
            $fetchedAt = time();
            $settings->set(self::CATALOG_CACHE_KEY, json_encode([
                'fetched_at' => $fetchedAt,
                'themes' => $themes,
                'plugins' => $plugins,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
            return [
                'themes' => $themes,
                'plugins' => $plugins,
                'source' => 'remote',
                'fetched_at' => $fetchedAt,
                'notice' => '',
            ];
        }

        if ($hasCache) {
            if (!($themes['ok'] ?? false)) $themes = $cached['themes'];
            if (!($plugins['ok'] ?? false)) $plugins = $cached['plugins'];
            return [
                'themes' => $themes,
                'plugins' => $plugins,
                'source' => 'stale-cache',
                'fetched_at' => (int)$cached['fetched_at'],
                'notice' => '远程目录暂时不可用，当前显示上次成功更新的数据。',
            ];
        }

        return [
            'themes' => $themes,
            'plugins' => $plugins,
            'source' => 'remote-error',
            'fetched_at' => 0,
            'notice' => '',
        ];
    }

    public function legacyPlugins(): void { $this->redirectWithQuery('/admin/app-store/plugins'); }
    public function legacyPluginMarket(): void { $this->redirectWithQuery('/admin/app-store?type=plugin'); }
    public function legacyPluginSettings(): void { $this->redirectWithQuery('/admin/app-store/plugins/settings'); }
    public function legacyThemes(): void { $this->redirectWithQuery('/admin/app-store/themes'); }
    public function legacyThemeUpload(): void { $this->redirectWithQuery('/admin/app-store/themes/upload'); }
    public function legacyThemeDetail(string $id): void { $this->redirectWithQuery('/admin/app-store/themes/' . rawurlencode($id)); }

    private function normalizePlugins(array $resources): array {
        $installed = [];
        foreach ((new PluginManager())->all() as $plugin) $installed[(string)$plugin['plugin_id']] = $plugin;
        $items = [];
        foreach ($resources as $resource) {
            if (!is_array($resource)) continue;
            $metadata = is_array($resource['metadata'] ?? null) ? $resource['metadata'] : [];
            $slug = sanitize_slug_input((string)($resource['plugin_id'] ?? $metadata['plugin_id'] ?? $resource['slug'] ?? ''));
            $local = $installed[$slug] ?? null;
            $remoteVersion = (string)($resource['version'] ?? '');
            $localVersion = (string)($local['installed_version'] ?? '');
            $items[] = $this->normalizeResource($resource, 'plugin', [
                'slug' => $slug,
                'installed' => $local !== null,
                'installed_version' => $localVersion,
                'needs_update' => $local !== null && $remoteVersion !== '' && $localVersion !== '' && version_compare($remoteVersion, $localVersion, '>'),
                'manage_url' => '/admin/app-store/plugins',
                'detail_url' => '',
            ]);
        }
        return $items;
    }

    private function normalizeThemes(array $resources): array {
        $records = (new AppStoreThemeInstall())->allIndexedByResourceId();
        $items = [];
        foreach ($resources as $resource) {
            if (!is_array($resource)) continue;
            $id = (int)($resource['id'] ?? 0);
            $slug = sanitize_slug_input((string)($resource['slug'] ?? ''));
            $record = $records[$id] ?? null;
            $localSlug = (string)($record['theme_slug'] ?? $slug);
            $isInstalled = $localSlug !== '' && is_dir(APP_ROOT . '/themes/' . $localSlug);
            $remoteVersion = (string)($resource['version'] ?? '');
            $localVersion = (string)($record['version'] ?? '');
            $items[] = $this->normalizeResource($resource, 'theme', [
                'slug' => $slug,
                'installed' => $isInstalled,
                'installed_version' => $localVersion,
                'needs_update' => $isInstalled && $remoteVersion !== '' && $localVersion !== '' && version_compare($remoteVersion, $localVersion, '>'),
                'manage_url' => '/admin/app-store/themes',
                'detail_url' => $id > 0 ? '/admin/app-store/themes/' . $id : '',
            ]);
        }
        return $items;
    }

    private function normalizeResource(array $resource, string $type, array $state): array {
        $isFree = !empty($resource['is_free']) || (float)($resource['price'] ?? 0) <= 0;
        $tags = $resource['tags'] ?? $resource['categories'] ?? [];
        if (is_string($tags)) $tags = preg_split('/\s*,\s*/', $tags) ?: [];
        if (!is_array($tags)) $tags = [];
        return array_merge([
            'id' => (int)($resource['id'] ?? 0),
            'type' => $type,
            'name' => (string)($resource['name'] ?? ($type === 'theme' ? '未命名主题' : '未命名插件')),
            'slug' => (string)($state['slug'] ?? ''),
            'vendor' => (string)($resource['vendor'] ?? $resource['author'] ?? $resource['developer'] ?? 'ShopAGG'),
            'version' => (string)($resource['version'] ?? ''),
            'description' => trim((string)($resource['description'] ?? $resource['short_description'] ?? '')),
            'image' => (string)($resource['cover_image'] ?? $resource['banner_image'] ?? $resource['icon_url'] ?? ''),
            'is_free' => $isFree,
            'price_text' => $isFree ? '免费' : (string)($resource['price_formatted'] ?? ('¥' . number_format((float)($resource['price'] ?? 0), 2))),
            'tags' => array_values(array_map(static fn($tag): string => (string)$tag, array_filter($tags, static fn($tag): bool => is_scalar($tag) && (string)$tag !== ''))),
            'installed' => false,
            'installed_version' => '',
            'needs_update' => false,
            'manage_url' => '',
            'detail_url' => '',
        ], $state);
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

    private function redirectWithQuery(string $path): void {
        $query = $_GET;
        $target = $path . ($query !== [] ? (str_contains($path, '?') ? '&' : '?') . http_build_query($query) : '');
        $this->redirect($target);
    }
}
