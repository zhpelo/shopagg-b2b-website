<?php
declare(strict_types=1);

namespace App\Plugins;

final class PluginSettings {
    public function __construct(private string $pluginId, private PluginRegistry $registry) {}
    public function get(string $key, mixed $default = null): mixed { return $this->registry->option($this->pluginId, $key, $default); }
    public function set(string $key, mixed $value, bool $autoload = false): void { $this->registry->setOption($this->pluginId, $key, $value, $autoload); }
}
