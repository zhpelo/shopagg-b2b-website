<?php
declare(strict_types=1);

define('APP_ENTRY_POINT', true);
define('APP_ROOT', dirname(__DIR__));
define('APP_BASE_PATH', '/cms');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = APP_ROOT . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});
require APP_ROOT . '/app/Helpers/Helpers.php';

use App\Core\AdminNavigation;
use App\Plugins\PluginCache;
use App\Plugins\PluginManifest;

function expect(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

function section(array $navigation, string $id): ?array {
    foreach ($navigation['sections'] as $section) {
        if ($section['id'] === $id) return $section;
    }
    return null;
}

$commerceMenu = [[
    'id' => 'orders',
    'section' => 'commerce',
    'order' => 10,
    'label' => '零售订单',
    'url' => '/lighthouse-commerce/manage',
    'icon' => 'cart-shopping',
    'permission' => 'plugin.lighthouse-commerce.manage_orders',
    'active_patterns' => ['/lighthouse-commerce/manage*'],
    'plugin_id' => 'lighthouse-commerce',
]];

$_SESSION = ['admin_role' => 'staff', 'admin_permissions' => 'products'];
$staff = AdminNavigation::build('/cms/admin/products/edit?id=3', $commerceMenu);
expect(section($staff, 'catalog') !== null, '商品 Staff 应看到商品分组');
expect(section($staff, 'commerce') === null, '无订单权限的 Staff 不应看到交易分组');
expect(section($staff, 'system') === null, '商品 Staff 不应看到系统分组');
expect((bool)(section($staff, 'catalog')['active'] ?? false), '商品详情页应激活商品分组');

$_SESSION = ['admin_role' => 'staff', 'admin_permissions' => 'plugin.lighthouse-commerce.manage_orders'];
$orders = AdminNavigation::build('/cms/lighthouse-commerce/manage/orders/LC-1001', $commerceMenu);
expect((bool)(section($orders, 'commerce')['active'] ?? false), '订单详情页应保持交易分组激活');
expect((bool)(section($orders, 'commerce')['items'][0]['active'] ?? false), '订单详情页应保持零售订单激活');

$legacy = $commerceMenu[0];
unset($legacy['id'], $legacy['section'], $legacy['order'], $legacy['active_patterns'], $legacy['permission']);
$_SESSION = ['admin_role' => 'admin', 'admin_permissions' => ''];
$admin = AdminNavigation::build('/legacy/orders', [$legacy], true);
expect(section($admin, 'extensions') !== null, '旧菜单应自动进入扩展分组');
expect((bool)(section($admin, 'extensions')['legacy'] ?? false), '旧 Slot 应显示在扩展分组');

$manifest = [
    'schema_version' => 1,
    'id' => 'navigation-test',
    'name' => 'Navigation Test',
    'vendor' => 'Test',
    'version' => '1.0.0',
    'description' => 'test',
    'requires' => [],
    'autoload' => ['psr4' => ['Test\\Navigation\\' => 'src/']],
    'routes' => [['method' => 'GET', 'path' => '/navigation-test', 'handler' => 'Test\\Navigation\\Page@show', 'layout' => 'admin', 'title' => '测试页面']],
    'admin_menu' => [['id' => 'test', 'section' => 'commerce', 'order' => 20, 'label' => '测试', 'url' => '/navigation-test', 'active_patterns' => ['/navigation-test*']]],
];
expect(PluginManifest::validate($manifest)['valid'], '新后台路由与菜单字段应通过 Manifest 校验');
$manifest['admin_menu'][0]['section'] = 'unknown';
expect(!PluginManifest::validate($manifest)['valid'], '未知后台分组应被 Manifest 校验拒绝');

$cacheFile = sys_get_temp_dir() . '/shopagg-navigation-' . bin2hex(random_bytes(5)) . '.php';
$cache = new PluginCache($cacheFile);
$manifest['admin_menu'][0]['section'] = 'commerce';
$compiled = $cache->rebuild([['plugin_id' => 'navigation-test', 'active_version' => '1.0.0', 'manifest' => $manifest]]);
expect(($compiled['admin_menu'][0]['section'] ?? '') === 'commerce', '缓存应保留插件业务分组');
expect(($compiled['admin_menu'][0]['active_patterns'][0] ?? '') === '/navigation-test*', '缓存应保留激活规则');
expect($cache->rebuild([])['admin_menu'] === [], '插件停用后重建缓存应移除菜单');
@unlink($cacheFile);
@unlink($cacheFile . '.last.php');

echo "admin navigation smoke: ok\n";
