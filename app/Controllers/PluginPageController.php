<?php
declare(strict_types=1);

namespace App\Controllers;

final class PluginPageController extends BaseController {
    public function show(string $html, array $seo = []): void {
        $this->renderSite('__plugin_page', ['pluginContent' => $html, 'seo' => $seo]);
    }
}
