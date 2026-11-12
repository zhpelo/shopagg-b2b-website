<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthManager;
use App\Core\Controller;
use App\Core\ThemeMetadata;
use App\Models\Setting;
use App\Services\AppStoreCatalog;
use App\Services\AppStoreClient;

final class AppStoreController extends Controller {
    private const PAGE_SIZE = 12;

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
        $catalog = (new AppStoreCatalog($client, $settings))->load(($_GET['refresh'] ?? '') === '1');
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
        usort($items, static fn(array $a, array $b): int =>
            [$a['type'], mb_strtolower($a['name']), $a['id']] <=> [$b['type'], mb_strtolower($b['name']), $b['id']]
        );

        $total = count($items);
        $totalPages = max(1, (int)ceil($total / self::PAGE_SIZE));
        $requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $page = min($requestedPage ?: 1, $totalPages);

        $this->renderAdmin('应用商店', 'admin/app-store/index', [
            'items' => array_slice($items, ($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE),
            'pagination' => compact('page', 'totalPages', 'total'),
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

    public function legacyPlugins(): void { $this->redirectWithQuery('/admin/app-store/plugins'); }
    public function legacyPluginMarket(): void { $this->redirectWithQuery('/admin/app-store?type=plugin'); }
    public function legacyPluginSettings(): void { $this->redirectWithQuery('/admin/app-store/plugins/settings'); }
    public function legacyThemes(): void { $this->redirectWithQuery('/admin/app-store/themes'); }
    public function legacyThemeUpload(): void { $this->redirectWithQuery('/admin/app-store/themes/upload'); }
    public function legacyThemeDetail(string $id): void { $this->redirectWithQuery('/admin/app-store/themes/' . rawurlencode($id)); }

    private function normalizePlugins(array $resources): array {
        $items = [];
        foreach ($resources as $resource) {
            if (!is_array($resource)) continue;
            $slug = AppStoreCatalog::resourceSlug($resource, 'b2b_plugin');
            $localVersion = AppStoreCatalog::installedPluginVersion($slug);
            $items[] = $this->normalizeResource($resource, 'plugin', [
                'slug' => $slug,
                'installed' => $localVersion !== null,
                'installed_version' => $localVersion ?? '',
                'needs_update' => AppStoreCatalog::updateVersion((string)($resource['version'] ?? ''), $localVersion ?? '') !== null,
                'manage_url' => '/admin/app-store/plugins',
                'detail_url' => '/admin/app-store/plugins/' . (int)($resource['id'] ?? 0),
            ]);
        }
        return $items;
    }

    private function normalizeThemes(array $resources): array {
        $directories = glob(APP_ROOT . '/themes/*', GLOB_ONLYDIR) ?: [];
        $installed = array_fill_keys(array_map('basename', $directories), true);
        $items = [];
        foreach ($resources as $resource) {
            if (!is_array($resource)) continue;
            $id = (int)($resource['id'] ?? 0);
            $slug = AppStoreCatalog::resourceSlug($resource, 'b2b_theme');
            $isInstalled = isset($installed[$slug]);
            $localVersion = $isInstalled ? ThemeMetadata::read(APP_ROOT . '/themes/' . $slug . '/style.css')['version'] : '';
            $items[] = $this->normalizeResource($resource, 'theme', [
                'slug' => $slug,
                'installed' => $isInstalled,
                'installed_version' => $localVersion,
                'needs_update' => AppStoreCatalog::updateVersion((string)($resource['version'] ?? ''), $localVersion) !== null,
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
            'vendor' => (string)($resource['vendor'] ?? $resource['author'] ?? $resource['developer'] ?? 'SHOPAGG'),
            'version' => (string)($resource['version'] ?? ''),
            'description' => trim((string)($resource['description'] ?? $resource['short_description'] ?? '')),
            'image' => (string)($resource['cover_image'] ?? $resource['banner_image'] ?? $resource['icon_url'] ?? ''),
            'installs_count' => max(0, (int)($resource['installs_count'] ?? 0)),
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
        \App\Core\AdminPageRenderer::render($title, $content);
    }

    private function redirectWithQuery(string $path): void {
        $query = $_GET;
        $target = $path . ($query !== [] ? (str_contains($path, '?') ? '&' : '?') . http_build_query($query) : '');
        $this->redirect($target);
    }
}
