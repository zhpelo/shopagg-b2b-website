<?php
declare(strict_types=1);

namespace App\Plugins;

use App\Plugins\Support\Handler;

final class ServiceRegistry {
    private array $instances = [];

    public function __construct(private array $definitions) {}

    public function has(string $serviceId): bool { return $this->definitionsFor($serviceId) !== []; }

    public function get(string $serviceId, ?string $providerId = null): object {
        $definitions = $this->definitionsFor($serviceId);
        if ($providerId !== null) $definitions = array_values(array_filter($definitions, static fn(array $d): bool => ($d['provider'] ?? $d['plugin_id']) === $providerId));
        if (count($definitions) !== 1) throw new \RuntimeException(count($definitions) === 0 ? "服务不存在：{$serviceId}" : "服务 {$serviceId} 有多个提供者，请指定 providerId");
        return $this->instantiate($definitions[0]);
    }

    public function all(string $serviceId): array {
        $services = [];
        foreach ($this->definitionsFor($serviceId) as $definition) {
            $services[(string)($definition['provider'] ?? $definition['plugin_id'])] = $this->instantiate($definition);
        }
        return $services;
    }

    private function definitionsFor(string $id): array {
        return array_values(array_filter($this->definitions, static fn(array $d): bool => ($d['name'] ?? $d['id'] ?? '') === $id));
    }

    private function instantiate(array $definition): object {
        $key = $definition['plugin_id'] . ':' . ($definition['name'] ?? $definition['id'] ?? '') . ':' . ($definition['provider'] ?? 'default');
        if (isset($this->instances[$key])) return $this->instances[$key];
        $class = (string)($definition['class'] ?? '');
        if ($class === '' || !class_exists($class)) throw new \RuntimeException("服务类不存在：{$class}");
        $reflection = new \ReflectionClass($class);
        $instance = $reflection->getConstructor()?->getNumberOfRequiredParameters() === 1
            ? $reflection->newInstance(PluginRuntime::instance()->context($definition['plugin_id']))
            : $reflection->newInstance();
        return $this->instances[$key] = $instance;
    }
}
