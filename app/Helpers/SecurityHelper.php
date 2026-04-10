<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * 安全辅助类
 * 
 * 提供数据库安全和文件访问保护相关的功能
 */
class SecurityHelper {
    
    /**
     * 检查敏感目录是否可公开访问
     * 
     * @return array 安全检查结果
     */
    public static function checkSecurity(): array {
        $results = [];
        
        // 检查 #data 目录
        $dataDir = APP_ROOT . '/#data';
        if (is_dir($dataDir)) {
            $htaccessFile = $dataDir . '/.htaccess';
            if (is_file($htaccessFile)) {
                $results['data_dir'] = ['status' => 'protected', 'message' => '#data 目录受 .htaccess 保护'];
            } else {
                $results['data_dir'] = ['status' => 'warning', 'message' => '#data 目录缺少 .htaccess 保护'];
            }
            
            // 检查数据库文件权限
            $dbFile = $dataDir . '/site.db';
            if (is_file($dbFile)) {
                $perms = fileperms($dbFile);
                $permStr = sprintf('%o', $perms & 0777);
                if ($permStr === '644' || $permStr === '640' || $permStr === '600') {
                    $results['db_permissions'] = ['status' => 'ok', 'message' => '数据库文件权限正常: ' . $permStr];
                } else {
                    $results['db_permissions'] = ['status' => 'warning', 'message' => '数据库文件权限建议设置为 644，当前: ' . $permStr];
                }
            }
        }
        
        // 检查 storage 目录
        $storageDir = APP_ROOT . '/storage';
        if (is_dir($storageDir)) {
            $htaccessFile = $storageDir . '/.htaccess';
            if (is_file($htaccessFile)) {
                $results['storage_dir'] = ['status' => 'protected', 'message' => 'storage 目录受 .htaccess 保护'];
            } else {
                $results['storage_dir'] = ['status' => 'warning', 'message' => 'storage 目录缺少 .htaccess 保护'];
            }
        }
        
        // 检查根目录 .htaccess
        $rootHtaccess = APP_ROOT . '/.htaccess';
        if (is_file($rootHtaccess)) {
            $content = file_get_contents($rootHtaccess);
            if (strpos($content, '#data') !== false || strpos($content, '\.db') !== false) {
                $results['root_htaccess'] = ['status' => 'protected', 'message' => '根目录 .htaccess 包含安全规则'];
            } else {
                $results['root_htaccess'] = ['status' => 'warning', 'message' => '根目录 .htaccess 可能缺少安全规则'];
            }
        } else {
            $results['root_htaccess'] = ['status' => 'danger', 'message' => '根目录缺少 .htaccess 文件'];
        }
        
        return $results;
    }
    
    /**
     * 设置数据库文件的安全权限
     * 
     * @param string $dbFile 数据库文件路径
     * @return bool 是否成功
     */
    public static function secureDatabaseFile(string $dbFile): bool {
        if (!is_file($dbFile)) {
            return false;
        }
        
        // 设置权限为 644 (rw-r--r--)，Web 服务器可读可写，其他用户只读
        return chmod($dbFile, 0644);
    }
    
    /**
     * 获取安全的 HTTP 响应头
     * 
     * @return array 安全头数组
     */
    public static function getSecurityHeaders(): array {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ];
    }
    
    /**
     * 应用安全响应头
     */
    public static function applySecurityHeaders(): void {
        if (headers_sent()) {
            return;
        }
        
        $headers = self::getSecurityHeaders();
        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }
}
