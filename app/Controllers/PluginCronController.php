<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Setting;
use App\Plugins\Http\Response;
use App\Plugins\Jobs\JobRunner;

final class PluginCronController {
    public function run(): void {
        $setting = new Setting();
        $expected = $setting->get('plugin_cron_token', '');
        $provided = (string)($_GET['token'] ?? '');
        if ($expected === '' || !hash_equals($expected, $provided)) { Response::json(['error' => 'Unauthorized'], 401)->send(); return; }
        Response::json(['success' => true, 'jobs' => (new JobRunner())->runDue(10)])->send();
    }
}
