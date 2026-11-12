<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

final class AppStoreCatalog {
    private const CATALOG_CACHE_KEY = 'app_store_catalog_cache_v1';
    private const CATALOG_CACHE_TTL = 300;

    public function __construct(private AppStoreClient $client, private Setting $settings) {}

    public static function pluginId(array $resource): string {
        $metadata = is_array($resource['metadata'] ?? null) ? $resource['metadata'] : [];
        $pluginId = sanitize_slug_input((string)($resource['plugin_id'] ?? ''))
            ?: sanitize_slug_input((string)($metadata['plugin_id'] ?? ''));
        if ($pluginId !== '') {
            return $pluginId;
        }

        $slug = sanitize_slug_input((string)($resource['slug'] ?? ''));
        // AI Deployer was published under a different slug from its plugin.json ID.
        return $slug === 'shopagg-cms-ai-deployer' ? 'shopagg-ai-deployer' : $slug;
    }

    public static function pluginUpdateVersion(array $resource, ?array $installedPlugin): ?string {
        if ($installedPlugin === null || ($installedPlugin['status'] ?? '') === 'removed') {
            return null;
        }

        $remoteVersion = ltrim(trim((string)($resource['version'] ?? '')), 'vV');
        $installedVersion = ltrim(trim((string)($installedPlugin['installed_version'] ?? '')), 'vV');
        if (preg_match('/^\d/', $remoteVersion) !== 1 || preg_match('/^\d/', $installedVersion) !== 1) {
            return null;
        }

        // SemVer build metadata does not affect version precedence.
        $remoteRelease = explode('+', $remoteVersion, 2)[0];
        $installedRelease = explode('+', $installedVersion, 2)[0];
        return version_compare($remoteRelease, $installedRelease, '>') ? $remoteVersion : null;
    }

    public function load(bool $forceRefresh = false): array {
        $cached = json_decode($this->settings->get(self::CATALOG_CACHE_KEY, ''), true);
        $hasCache = is_array($cached)
            && is_array($cached['themes'] ?? null)
            && is_array($cached['plugins'] ?? null)
            && (int)($cached['fetched_at'] ?? 0) > 0;
        $cacheAge = $hasCache ? time() - (int)$cached['fetched_at'] : PHP_INT_MAX;

        // 首次升级时刷新旧的首页缓存，但远程失败时仍可用它兜底。
        if (!$forceRefresh && $hasCache && ($cached['complete'] ?? false) === true && $cacheAge < self::CATALOG_CACHE_TTL) {
            return [
                'themes' => $cached['themes'],
                'plugins' => $cached['plugins'],
                'source' => 'cache',
                'fetched_at' => (int)$cached['fetched_at'],
                'notice' => '',
            ];
        }

        $remote = $this->client->listCatalog();
        $themes = $remote['themes'];
        $plugins = $remote['plugins'];
        if (($themes['ok'] ?? false) && ($plugins['ok'] ?? false)) {
            $fetchedAt = time();
            $this->settings->set(self::CATALOG_CACHE_KEY, json_encode([
                'complete' => true,
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
}
