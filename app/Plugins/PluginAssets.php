<?php
declare(strict_types=1);

namespace App\Plugins;

final class PluginAssets {
    public function __construct(private string $pluginId, private string $version) {}
    public function url(string $path): string {
        if (!PluginManifest::safeRelativePath($path)) throw new \InvalidArgumentException('插件资源路径无效');
        return url('/assets/plugins/' . rawurlencode($this->pluginId) . '/' . implode('/', array_map('rawurlencode', explode('/', trim($path, '/'))))) . '?ver=' . rawurlencode($this->version);
    }
}
