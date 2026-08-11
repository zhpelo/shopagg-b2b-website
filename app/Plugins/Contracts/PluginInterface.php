<?php
declare(strict_types=1);

namespace App\Plugins\Contracts;

use App\Plugins\PluginContext;

interface PluginInterface {
    public function activate(PluginContext $context): void;
    public function deactivate(PluginContext $context): void;
    public function uninstall(PluginContext $context): void;
}
