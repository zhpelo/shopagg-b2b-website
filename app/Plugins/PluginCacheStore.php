<?php
declare(strict_types=1);

namespace App\Plugins;

/** Small persistent cache for shared-host environments without Redis or APCu. */
final class PluginCacheStore {
    public function __construct(private string $pluginId, private PluginRegistry $registry) {}

    public function get(string $key, mixed $default = null): mixed {
        $payload = $this->registry->option($this->pluginId, $this->key($key));
        if (!is_array($payload) || !array_key_exists('value', $payload)) return $default;
        if (($payload['expires_at'] ?? null) !== null && (int)$payload['expires_at'] <= time()) {
            $this->delete($key);
            return $default;
        }
        return $payload['value'];
    }

    public function set(string $key, mixed $value, int $ttlSeconds = 0): void {
        $this->registry->setOption($this->pluginId, $this->key($key), [
            'value' => $value,
            'expires_at' => $ttlSeconds > 0 ? time() + $ttlSeconds : null,
        ]);
    }

    public function remember(string $key, int $ttlSeconds, callable $resolver): mixed {
        $missing = new \stdClass();
        $value = $this->get($key, $missing);
        if ($value !== $missing) return $value;
        $value = $resolver();
        $this->set($key, $value, $ttlSeconds);
        return $value;
    }

    public function delete(string $key): void { $this->registry->deleteOption($this->pluginId, $this->key($key)); }
    public function clear(): void { $this->registry->deleteOptionsByPrefix($this->pluginId, '__cache.'); }

    private function key(string $key): string {
        $key = trim($key);
        if ($key === '' || strlen($key) > 180) throw new \InvalidArgumentException('插件缓存键无效');
        return '__cache.' . $key;
    }
}
