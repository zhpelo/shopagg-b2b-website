<?php
declare(strict_types=1);

namespace App\Plugins;

final class PluginView {
    public function __construct(private string $basePath) {}

    public function render(string $view, array $data = []): string {
        if (!PluginManifest::safeRelativePath($view) || !str_ends_with($view, '.php')) throw new \InvalidArgumentException('插件视图路径无效');
        $file = $this->basePath . '/views/' . ltrim($view, '/');
        $real = realpath($file);
        $views = realpath($this->basePath . '/views');
        if ($real === false || $views === false || !str_starts_with($real, $views . DIRECTORY_SEPARATOR)) throw new \RuntimeException('插件视图不存在');
        ob_start();
        extract($data, EXTR_SKIP);
        include $real;
        return ob_get_clean() ?: '';
    }
}
