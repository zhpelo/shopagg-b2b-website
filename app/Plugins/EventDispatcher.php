<?php
declare(strict_types=1);

namespace App\Plugins;

use App\Plugins\Support\Handler;

final class EventDispatcher {
    public function __construct(private array $listeners, private PluginRegistry $registry) {}

    public function dispatch(string $eventName, mixed $event = null): void {
        foreach ($this->matching($eventName) as $listener) {
            try {
                PluginRuntime::beginInvocation($listener['plugin_id']);
                Handler::invoke($listener['handler'], [$event, PluginRuntime::instance()->context($listener['plugin_id'])]);
            } catch (\Throwable $e) {
                $this->registry->recordFailure($listener['plugin_id'], $e);
            } finally {
                PluginRuntime::endInvocation();
            }
        }
    }

    private function matching(string $name): array {
        return array_values(array_filter($this->listeners, static fn(array $item): bool => ($item['name'] ?? '') === $name));
    }
}
