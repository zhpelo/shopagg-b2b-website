<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Builds the admin navigation from core entries and plugin manifests.
 *
 * Keeping this logic outside the layout gives plugins one predictable extension
 * point and makes permission/active-state behaviour consistent on every page.
 */
final class AdminNavigation {
    public const SECTIONS = [
        'inbox' => ['label' => '收件箱', 'icon' => 'envelope', 'order' => 10],
        'catalog' => ['label' => '商品', 'icon' => 'box', 'order' => 20],
        'commerce' => ['label' => '交易', 'icon' => 'cart-shopping', 'order' => 30],
        'content' => ['label' => '内容', 'icon' => 'pen-nib', 'order' => 40],
        'appearance' => ['label' => '外观', 'icon' => 'paint-brush', 'order' => 50],
        'extensions' => ['label' => '扩展', 'icon' => 'puzzle-piece', 'order' => 60],
        'system' => ['label' => '系统', 'icon' => 'gear', 'order' => 70],
    ];

    /** @return array{dashboard:array,sections:array<int,array>} */
    public static function build(string $currentPath, array $pluginMenus = [], bool $hasLegacyNavigation = false): array {
        $currentPath = self::normalizePath($currentPath);
        $sections = [];
        foreach (self::SECTIONS as $id => $definition) {
            $sections[$id] = $definition + ['id' => $id, 'items' => [], 'active' => false, 'legacy' => false];
        }

        foreach (self::coreItems() as $item) {
            if (!self::canAccess($item)) continue;
            self::append($sections, self::normalizeItem($item, $currentPath, 'core'));
        }

        foreach ($pluginMenus as $index => $menu) {
            if (!is_array($menu)) continue;
            $permission = trim((string)($menu['permission'] ?? ''));
            if ($permission !== '' && !AuthManager::hasPermission($permission)) continue;
            $url = trim((string)($menu['url'] ?? ''));
            if ($url === '' || !str_starts_with($url, '/')) continue;
            $pluginId = preg_replace('/[^a-z0-9-]+/i', '-', (string)($menu['plugin_id'] ?? 'plugin')) ?: 'plugin';
            $menu['id'] = trim((string)($menu['id'] ?? '')) ?: $pluginId . '-' . $index;
            $menu['section'] = isset(self::SECTIONS[(string)($menu['section'] ?? '')])
                ? (string)$menu['section']
                : 'extensions';
            self::append($sections, self::normalizeItem($menu, $currentPath, 'plugin'));
        }

        if ($hasLegacyNavigation) {
            $sections['extensions']['legacy'] = true;
        }

        foreach ($sections as &$section) {
            usort($section['items'], static fn(array $left, array $right): int => [
                (int)$left['order'], mb_strtolower((string)$left['label']), (string)$left['id'],
            ] <=> [
                (int)$right['order'], mb_strtolower((string)$right['label']), (string)$right['id'],
            ]);
            $section['active'] = array_reduce(
                $section['items'],
                static fn(bool $active, array $item): bool => $active || (bool)$item['active'],
                false
            );
        }
        unset($section);

        $sections = array_values(array_filter(
            $sections,
            static fn(array $section): bool => $section['items'] !== [] || $section['legacy']
        ));
        usort($sections, static fn(array $left, array $right): int => [$left['order'], $left['id']] <=> [$right['order'], $right['id']]);

        return [
            'dashboard' => [
                'id' => 'dashboard',
                'label' => '仪表盘',
                'url' => '/admin',
                'icon' => 'house',
                'active' => $currentPath === '/admin',
            ],
            'sections' => $sections,
        ];
    }

    public static function matches(string $path, array $patterns): bool {
        $path = self::normalizePath($path);
        foreach ($patterns as $pattern) {
            $pattern = self::normalizePath((string)$pattern);
            $quoted = preg_quote($pattern, '#');
            if (preg_match('#^' . str_replace('\\*', '.*', $quoted) . '$#D', $path) === 1) return true;
        }
        return false;
    }

    private static function append(array &$sections, array $item): void {
        $sectionId = (string)$item['section'];
        $sections[$sectionId]['items'][] = $item;
    }

    private static function normalizeItem(array $item, string $currentPath, string $source): array {
        $url = self::normalizePath((string)$item['url']);
        $patterns = array_values(array_filter(
            array_map('strval', (array)($item['active_patterns'] ?? [])),
            static fn(string $pattern): bool => $pattern !== '' && str_starts_with($pattern, '/')
        ));
        if ($patterns === []) {
            $patterns = [$url, rtrim($url, '/') . '/*'];
        }
        return [
            'id' => (string)$item['id'],
            'section' => (string)$item['section'],
            'label' => (string)$item['label'],
            'url' => $url,
            'icon' => (string)($item['icon'] ?? 'circle'),
            'order' => (int)($item['order'] ?? 100),
            'active_patterns' => $patterns,
            'active' => self::matches($currentPath, $patterns),
            'source' => $source,
            'plugin_id' => (string)($item['plugin_id'] ?? ''),
        ];
    }

    private static function normalizePath(string $path): string {
        $path = (string)(parse_url($path, PHP_URL_PATH) ?: '/');
        if (function_exists('base_path')) {
            $base = base_path();
            if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
                $path = substr($path, strlen($base)) ?: '/';
            }
        }
        return '/' . ltrim($path, '/');
    }

    private static function canAccess(array $item): bool {
        if (($item['admin_only'] ?? false) && AuthManager::getUserRole() !== 'admin') return false;
        $permission = (string)($item['permission'] ?? '');
        if ($permission !== '' && !AuthManager::hasPermission($permission)) return false;
        $any = (array)($item['any_permission'] ?? []);
        if ($any !== [] && !array_filter($any, static fn(string $permission): bool => AuthManager::hasPermission($permission))) return false;
        return true;
    }

    private static function coreItems(): array {
        return [
            ['id' => 'messages', 'section' => 'inbox', 'label' => '联系留言', 'url' => '/admin/messages', 'icon' => 'comment-dots', 'order' => 10, 'permission' => 'inbox'],
            ['id' => 'inquiries', 'section' => 'inbox', 'label' => '询单管理', 'url' => '/admin/inquiries', 'icon' => 'file-invoice', 'order' => 20, 'permission' => 'inbox'],

            ['id' => 'products', 'section' => 'catalog', 'label' => '商品列表', 'url' => '/admin/products', 'icon' => 'list', 'order' => 10, 'permission' => 'products'],
            ['id' => 'product-categories', 'section' => 'catalog', 'label' => '商品分类', 'url' => '/admin/product-categories', 'icon' => 'folder', 'order' => 20, 'permission' => 'products'],

            ['id' => 'posts', 'section' => 'content', 'label' => '文章管理', 'url' => '/admin/posts', 'icon' => 'newspaper', 'order' => 10, 'permission' => 'blog'],
            ['id' => 'post-categories', 'section' => 'content', 'label' => '文章分类', 'url' => '/admin/post-categories', 'icon' => 'tags', 'order' => 20, 'permission' => 'blog'],
            ['id' => 'pages', 'section' => 'content', 'label' => '页面管理', 'url' => '/admin/pages', 'icon' => 'file-lines', 'order' => 30, 'permission' => 'blog'],
            ['id' => 'cases', 'section' => 'content', 'label' => '案例管理', 'url' => '/admin/cases', 'icon' => 'briefcase', 'order' => 40, 'permission' => 'cases'],
            ['id' => 'media', 'section' => 'content', 'label' => '媒体库', 'url' => '/admin/media', 'icon' => 'photo-film', 'order' => 50, 'any_permission' => ['blog', 'cases']],

            ['id' => 'appearance-blocks', 'section' => 'appearance', 'label' => '模板区块', 'url' => '/admin/appearance/blocks', 'icon' => 'layer-group', 'order' => 10, 'admin_only' => true],
            ['id' => 'appearance-menus', 'section' => 'appearance', 'label' => '菜单管理', 'url' => '/admin/appearance/menus', 'icon' => 'bars', 'order' => 20, 'admin_only' => true],
            ['id' => 'appearance-sliders', 'section' => 'appearance', 'label' => '轮播图', 'url' => '/admin/appearance/sliders', 'icon' => 'images', 'order' => 30, 'admin_only' => true],
            ['id' => 'themes', 'section' => 'appearance', 'label' => '网站模板', 'url' => '/admin/app-store/themes', 'icon' => 'palette', 'order' => 40, 'admin_only' => true],

            ['id' => 'app-store', 'section' => 'extensions', 'label' => '应用商店', 'url' => '/admin/app-store', 'icon' => 'store', 'order' => 10, 'admin_only' => true, 'active_patterns' => ['/admin/app-store']],
            ['id' => 'plugins', 'section' => 'extensions', 'label' => '插件管理', 'url' => '/admin/app-store/plugins', 'icon' => 'plug', 'order' => 20, 'admin_only' => true, 'active_patterns' => ['/admin/app-store/plugins*']],
            ['id' => 'app-store-settings', 'section' => 'extensions', 'label' => '商店账户', 'url' => '/admin/app-store/settings', 'icon' => 'user-gear', 'order' => 30, 'admin_only' => true],

            ['id' => 'settings-general', 'section' => 'system', 'label' => '常规设置', 'url' => '/admin/settings-general', 'icon' => 'sliders', 'order' => 10, 'permission' => 'settings'],
            ['id' => 'settings-company', 'section' => 'system', 'label' => '公司信息', 'url' => '/admin/settings-company', 'icon' => 'building', 'order' => 20, 'permission' => 'settings'],
            ['id' => 'settings-trade', 'section' => 'system', 'label' => '贸易设置', 'url' => '/admin/settings-trade', 'icon' => 'globe', 'order' => 30, 'permission' => 'settings'],
            ['id' => 'settings-media', 'section' => 'system', 'label' => '媒体设置', 'url' => '/admin/settings-media', 'icon' => 'image', 'order' => 40, 'permission' => 'settings'],
            ['id' => 'settings-contact', 'section' => 'system', 'label' => '联系设置', 'url' => '/admin/settings-contact', 'icon' => 'address-book', 'order' => 50, 'permission' => 'settings'],
            ['id' => 'settings-translate', 'section' => 'system', 'label' => '翻译设置', 'url' => '/admin/settings-translate', 'icon' => 'language', 'order' => 60, 'permission' => 'settings'],
            ['id' => 'settings-custom', 'section' => 'system', 'label' => '自定义代码', 'url' => '/admin/settings-custom', 'icon' => 'code', 'order' => 70, 'admin_only' => true],
            ['id' => 'staff', 'section' => 'system', 'label' => '员工管理', 'url' => '/admin/staff', 'icon' => 'users', 'order' => 80, 'admin_only' => true],
            ['id' => 'updater', 'section' => 'system', 'label' => '程序更新', 'url' => '/admin/settings-updater', 'icon' => 'arrows-rotate', 'order' => 90, 'admin_only' => true],
        ];
    }
}
