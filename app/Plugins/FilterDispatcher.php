<?php
declare(strict_types=1);

namespace App\Plugins;

use App\Plugins\Support\Handler;

final class FilterDispatcher {
    public function __construct(private array $listeners, private PluginRegistry $registry) {}

    public function apply(string $filterName, mixed $value, array $context = []): mixed {
        foreach ($this->listeners as $listener) {
            if (($listener['name'] ?? '') !== $filterName) continue;
            try {
                PluginRuntime::beginInvocation($listener['plugin_id']);
                $candidate = Handler::invoke($listener['handler'], [$value, $context, PluginRuntime::instance()->context($listener['plugin_id'])]);
                if (get_debug_type($candidate) !== get_debug_type($value) && $value !== null) {
                    throw new \UnexpectedValueException("过滤器 {$filterName} 返回类型不兼容");
                }
                $value = $candidate;
            } catch (\Throwable $e) {
                $this->registry->recordFailure($listener['plugin_id'], $e);
            } finally {
                PluginRuntime::endInvocation();
            }
        }
        return $value;
    }
}
