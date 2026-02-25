<?php
declare(strict_types=1);

namespace App\Core;

/**
 * 基础控制器类
 * 
 * 提供统一的视图渲染、JSON 响应、重定向等功能
 * 所有公开面向用户或管理员的控制器均应继承此类
 */
abstract class Controller {
    
    // 缓存目录路径，用于性能优化
    private static array $pathCache = [];

    /**
     * 渲染前端页面视图
     * 
     * @param string $theme 主题名称（如 'default'）
     * @param string $view 视图文件名（无 .php 后缀）
     * @param array $data 传递给视图的数据
     * @return void
     */
    protected function render(string $theme, string $view, array $data = []): void {
        // 获取主题路径，优先使用指定主题，回退到默认主题
        $themePath = $this->getThemePath($theme);
        $defaultThemePath = $this->getThemePath('default');
        $actualThemePath = is_dir($themePath) ? $themePath : $defaultThemePath;

        // 加载主题的 functions.php（优先当前主题）
        $this->loadThemeFunctions($actualThemePath, $defaultThemePath);

        // 提取数据到作用域，防止变量覆盖
        extract($data, EXTR_SKIP);
        $currentTheme = $theme;

        // 获取页面内容
        $pageContent = $this->loadViewFile($view, $actualThemePath, $defaultThemePath, $data);
        
        // 记录主题用于视图使用
        echo $this->loadFileContent($actualThemePath . '/header.php', $defaultThemePath . '/header.php', $data) ?? '';
        echo $pageContent;
        echo $this->loadFileContent($actualThemePath . '/footer.php', $defaultThemePath . '/footer.php', $data) ?? '';
    }

    /**
     * 返回 JSON 响应
     * 
     * @param array $data 响应数据
     * @param int $status HTTP 状态码
     * @return void
     */
    protected function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 重定向到指定 URL
     * 
     * @param string $url 目标 URL（支持相对路径）
     * @return void
     */
    protected function redirect(string $url): void {
        // 相对路径自动添加 base path 支持二级目录部署
        if ($this->isRelativePath($url)) {
            $url = url($url);
        }
        header("Location: $url");
        exit;
    }

    /**
     * 获取主题路径
     * 
     * @param string $theme 主题名称
     * @return string 主题目录路径
     */
    private function getThemePath(string $theme): string {
        $cacheKey = "theme_path_$theme";
        if (isset(self::$pathCache[$cacheKey])) {
            return self::$pathCache[$cacheKey];
        }
        $path = APP_ROOT . '/themes/' . $theme;
        self::$pathCache[$cacheKey] = $path;
        return $path;
    }

    /**
     * 加载主题的 functions.php
     * 
     * @param string $themePath 主题路径
     * @param string $fallbackPath 备用路径
     * @return void
     */
    private function loadThemeFunctions(string $themePath, string $fallbackPath): void {
        $functionsFile = $themePath . '/functions.php';
        if (!is_file($functionsFile)) {
            $functionsFile = $fallbackPath . '/functions.php';
        }
        if (is_file($functionsFile)) {
            include_once $functionsFile;
        }
    }

    /**
     * 加载视图文件并返回内容
     * 
     * @param string $view 视图名称
     * @param string $themePath 主题路径
     * @param string $fallbackPath 备用路径
     * @param array $data 视图数据
     * @return string 视图内容
     */
    private function loadViewFile(string $view, string $themePath, string $fallbackPath, array $data = []): string {
        $viewFile = $themePath . '/' . $view . '.php';
        if (!is_file($viewFile)) {
            $viewFile = $fallbackPath . '/' . $view . '.php';
        }
        
        if (is_file($viewFile)) {
            ob_start();
            extract($data, EXTR_SKIP);
            include $viewFile;
            return ob_get_clean() ?: '';
        }
        return "<p>Missing view: $view</p>";
    }

    /**
     * 加载文件内容，优先使用主路径，支持 PHP 代码执行
     * 
     * @param string $primaryPath 主文件路径
     * @param string $fallbackPath 备用文件路径
     * @param array $data 视图数据
     * @return string|null 文件内容，不存在返回 null
     */
    private function loadFileContent(string $primaryPath, string $fallbackPath, array $data = []): ?string {
        $filePath = is_file($primaryPath) ? $primaryPath : (is_file($fallbackPath) ? $fallbackPath : null);
        
        if ($filePath === null) {
            return null;
        }
        
        // 让 PHP 代码在当前作用域执行
        ob_start();
        extract($data, EXTR_SKIP);
        include $filePath;
        return ob_get_clean() ?: null;
    }

    /**
     * 判断字符串是否为相对路径
     * 
     * @param string $url 待检查字符串
     * @return bool true 为相对路径
     */
    private function isRelativePath(string $url): bool {
        return strpos($url, 'http') !== 0 && strpos($url, '/') === 0;
    }
}

