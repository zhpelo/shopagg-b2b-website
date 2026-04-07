<?php
declare(strict_types=1);

namespace App\Core;

/**
 * 认证和授权管理类
 * 
 * 处理用户认证、权限检查、会话管理等
 */
class AuthManager {
    
    // 权限路由映射表
    private const PERMISSION_MAP = [
        '/admin/products' => 'products',
        '/admin/product-categories' => 'products',
        '/admin/cases' => 'cases',
        '/admin/posts' => 'blog',
        '/admin/pages' => 'blog',
        '/admin/post-categories' => 'blog',
        '/admin/messages' => 'inbox',
        '/admin/inquiries' => 'inbox',
        '/admin/settings' => 'settings',
        '/admin/staff' => 'staff',
    ];

    // 无需认证的路由白名单
    private const PUBLIC_ROUTES = ['/admin/login'];

    /**
     * 检查用户是否已认证
     * 
     * @return bool true 已认证
     */
    public static function isAuthenticated(): bool {
        return isset($_SESSION['admin_user']);
    }

    /**
     * 获取当前用户角色
     * 
     * @return string|null 用户角色（'admin'、'staff' 等），未认证返回 null
     */
    public static function getUserRole(): ?string {
        return $_SESSION['admin_role'] ?? null;
    }

    /**
     * 获取当前用户 ID
     * 
     * @return int|null 用户 ID，未认证返回 null
     */
    public static function getUserId(): ?int {
        return $_SESSION['admin_user_id'] ?? null;
    }

    /**
     * 获取当前用户名
     * 
     * @return string|null 用户名，未认证返回 null
     */
    public static function getUsername(): ?string {
        return $_SESSION['admin_user'] ?? null;
    }

    /**
     * 获取当前用户权限列表
     * 
     * @return array 权限数组
     */
    public static function getPermissions(): array {
        return array_filter(explode(',', $_SESSION['admin_permissions'] ?? ''));
    }

    /**
     * 检查用户是否拥有指定权限
     * 
     * @param string $permission 权限标识（如 'products'、'blog'）
     * @return bool true 拥有权限
     */
    public static function hasPermission(string $permission): bool {
        // 管理员拥有所有权限
        if (self::getUserRole() === 'admin') {
            return true;
        }
        return in_array($permission, self::getPermissions(), true);
    }

    /**
     * 检查路由是否需要认证
     * 
     * @param string $path 路由路径
     * @return bool true 需要认证
     */
    public static function isProtectedRoute(string $path): bool {
        return !in_array($path, self::PUBLIC_ROUTES, true);
    }

    /**
     * 获取路由所需的权限
     * 
     * @param string $path 路由路径
     * @return string|null 所需权限标识，无对应权限返回 null
     */
    public static function getRequiredPermission(string $path): ?string {
        foreach (self::PERMISSION_MAP as $prefix => $permission) {
            if (str_starts_with($path, $prefix)) {
                return $permission;
            }
        }
        return null;
    }

    /**
     * 验证当前用户是否有权访问指定路由
     * 
     * @param string $path 路由路径
     * @return bool true 有权访问
     */
    public static function canAccessRoute(string $path): bool {
        // 公开路由无需检查
        if (!self::isProtectedRoute($path)) {
            return true;
        }

        // 需要认证
        if (!self::isAuthenticated()) {
            return false;
        }

        // 管理员有权访问所有路由
        if (self::getUserRole() === 'admin') {
            return true;
        }

        // 检查特定权限
        $requiredPermission = self::getRequiredPermission($path);
        if ($requiredPermission === null) {
            return true; // 无明确权限要求的路由可访问
        }

        return self::hasPermission($requiredPermission);
    }

    /**
     * 启动用户会话
     * 
     * @param array $user 用户数据数组，必须包含以下键：
     *   - 'id': 用户 ID
     *   - 'username': 用户名
     *   - 'role': 角色
     *   - 'permissions': 权限（逗号分隔字符串）
     *   - 'display_name': 显示名称（可选）
     * @return void
     */
    public static function startSession(array $user): void {
        $_SESSION['admin_user_id'] = (int)$user['id'];
        $_SESSION['admin_user'] = (string)$user['username'];
        $_SESSION['admin_role'] = (string)$user['role'];
        $_SESSION['admin_permissions'] = (string)($user['permissions'] ?? '');
        $_SESSION['admin_display_name'] = (string)($user['display_name'] ?? $user['username']);
    }

    /**
     * 销毁用户会话
     * 
     * @return void
     */
    public static function destroySession(): void {
        unset($_SESSION['admin_user']);
        unset($_SESSION['admin_user_id']);
        unset($_SESSION['admin_role']);
        unset($_SESSION['admin_permissions']);
        unset($_SESSION['admin_display_name']);
    }

    /**
     * 规范化路由路径
     * 移除 base path，用于权限检查
     * 
     * @param string $path 原始路径
     * @return string 规范化后的路径
     */
    public static function normalizePath(string $path): string {
        $basePath = base_path();
        if ($basePath !== '' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        return $path ?: '/';
    }
}
