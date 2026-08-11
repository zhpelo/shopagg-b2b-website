<?php
declare(strict_types=1);

namespace App\Plugins;

final class PluginSession {
    public function __construct(private string $pluginId) {}
    public function get(string $key, mixed $default = null): mixed { return $_SESSION['plugins'][$this->pluginId][$key] ?? $default; }
    public function set(string $key, mixed $value): void { $_SESSION['plugins'][$this->pluginId][$key] = $value; }
    public function remove(string $key): void { unset($_SESSION['plugins'][$this->pluginId][$key]); }
    public function clear(): void { unset($_SESSION['plugins'][$this->pluginId]); }
}
