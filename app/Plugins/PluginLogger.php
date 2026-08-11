<?php
declare(strict_types=1);

namespace App\Plugins;

final class PluginLogger {
    public function __construct(private string $pluginId, private PluginRegistry $registry) {}
    public function debug(string $message, array $context = []): void { $this->registry->log($this->pluginId, 'debug', $message, $context); }
    public function info(string $message, array $context = []): void { $this->registry->log($this->pluginId, 'info', $message, $context); }
    public function warning(string $message, array $context = []): void { $this->registry->log($this->pluginId, 'warning', $message, $context); }
    public function error(string $message, array $context = []): void { $this->registry->log($this->pluginId, 'error', $message, $context); }
}
