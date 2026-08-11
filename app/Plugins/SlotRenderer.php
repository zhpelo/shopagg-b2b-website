<?php
declare(strict_types=1);

namespace App\Plugins;

use App\Plugins\Support\Handler;

final class SlotRenderer {
    public function __construct(private array $listeners, private PluginRegistry $registry) {}

    public function render(string $slotName, array $context = []): string {
        $html = '';
        foreach ($this->listeners as $listener) {
            if (($listener['name'] ?? '') !== $slotName) continue;
            try {
                PluginRuntime::beginInvocation($listener['plugin_id']);
                $result = Handler::invoke($listener['handler'], [$context, PluginRuntime::instance()->context($listener['plugin_id'])]);
                if ($result instanceof \Stringable || is_string($result) || is_numeric($result)) $html .= (string)$result;
            } catch (\Throwable $e) {
                $this->registry->recordFailure($listener['plugin_id'], $e);
            } finally {
                PluginRuntime::endInvocation();
            }
        }
        return $html;
    }
}
