<?php
declare(strict_types=1);

namespace App\Plugins;

use App\Core\AuthManager;
use App\Core\Database;
use App\Core\MediaManager;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Message;
use App\Models\PostModel;
use App\Models\Product;
use App\Models\Setting;
use App\Plugins\Http\Request;
use App\Plugins\Http\Response;
use App\Plugins\Jobs\JobQueue;

final class PluginContext {
    private PluginDatabase $database;
    private PluginSchema $schema;

    public function __construct(
        private string $pluginId,
        private string $version,
        private string $basePath,
        private PluginRuntime $runtime
    ) {
        $this->database = new PluginDatabase($pluginId, Database::getInstance());
        $this->schema = new PluginSchema($this->database);
    }

    public function pluginId(): string { return $this->pluginId; }
    public function version(): string { return $this->version; }
    public function basePath(): string { return $this->basePath; }
    public function database(): PluginDatabase { return $this->database; }
    public function schema(): PluginSchema { return $this->schema; }
    public function transactions(): PluginDatabase { return $this->database; }
    public function settings(): PluginSettings { return new PluginSettings($this->pluginId, $this->runtime->registry()); }
    public function cache(): PluginCacheStore { return new PluginCacheStore($this->pluginId, $this->runtime->registry()); }
    public function session(): PluginSession { return new PluginSession($this->pluginId); }
    public function request(): Request { return Request::capture(); }
    public function responses(): string { return Response::class; }
    public function router(): ?\App\Core\Router { return $this->runtime->router(); }
    public function urls(): object { return new class { public function to(string $path): string { return url($path); } public function base(): string { return base_url(); } }; }
    public function views(): PluginView { return new PluginView($this->basePath); }
    public function theme(): string { return (new Setting())->get('theme', 'default'); }
    public function assets(): PluginAssets { return new PluginAssets($this->pluginId, $this->version); }
    public function forms(): object { return new class { public function csrfField(): string { return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">'; } }; }
    public function media(): MediaManager { return new MediaManager(); }
    public function uploads(): string { return APP_ROOT . '/uploads/plugins/' . $this->pluginId . '/' . $this->version; }
    public function temporaryFiles(): string {
        $path = APP_ROOT . '/storage/tmp/plugins/' . $this->pluginId;
        if (!is_dir($path)) mkdir($path, 0755, true);
        return $path;
    }
    public function events(): EventDispatcher { return $this->runtime->events(); }
    public function filters(): FilterDispatcher { return $this->runtime->filters(); }
    public function slots(): SlotRenderer { return $this->runtime->slots(); }
    public function jobs(): JobQueue { return new JobQueue($this->pluginId); }
    public function scheduler(): JobQueue { return $this->jobs(); }
    public function httpClient(): HttpClient { return new HttpClient(); }
    public function logger(): PluginLogger { return new PluginLogger($this->pluginId, $this->runtime->registry()); }
    public function currentAdmin(): ?array {
        if (!AuthManager::isAuthenticated()) return null;
        return ['id' => AuthManager::getUserId(), 'username' => AuthManager::getUsername(), 'role' => AuthManager::getUserRole(), 'permissions' => AuthManager::getPermissions()];
    }
    public function currentMember(): mixed {
        return $this->runtime->services()->has('member.identity') ? $this->runtime->services()->get('member.identity')->current() : null;
    }
    public function services(): ServiceRegistry { return $this->runtime->services(); }
    public function products(): Product { return new Product(); }
    public function posts(): PostModel { return new PostModel(); }
    public function categories(): Category { return new Category(); }
    public function inquiries(): Inquiry { return new Inquiry(); }
    public function messages(): Message { return new Message(); }
}
