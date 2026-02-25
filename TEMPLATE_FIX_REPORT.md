# 前端模板变量作用域修复总结

## 问题描述
在之前的后端架构优化后，前端模板无法访问控制器传递的数据，导致出现以下错误：
- `Warning: Undefined variable $cases in home.php on line 183`
- `Warning: foreach() argument must be of type array|object, null given`

## 根本原因
在 `Controller::render()` 方法中，存在一个严重的作用域问题：

1. **原始设计中的问题**：
   - `render()` 方法中使用 `extract($data, EXTR_SKIP)` 在 render() 的作用域中提取变量
   - 但 `loadViewFile()` 是一个独立的方法，有自己的作用域
   - 当 `loadViewFile()` 中执行 `include $viewFile` 时，它只能访问 `loadViewFile()` 的局部变量，无法访问 `render()` 作用域中提取的变量
   - 这导致所有模板变量（$cases, $products, $site, $seo 等）都无法访问

## 解决方案
修改 `app/Core/Controller.php`，确保在包含文件之前进行 `extract()` 操作：

### 修改 1: render() 方法
- 为 `loadViewFile()` 和 `loadFileContent()` 传递 `$data` 参数

```php
// 修改前
$pageContent = $this->loadViewFile($view, $actualThemePath, $defaultThemePath);
echo $this->loadFileContent($actualThemePath . '/header.php', $defaultThemePath . '/header.php') ?? '';

// 修改后
$pageContent = $this->loadViewFile($view, $actualThemePath, $defaultThemePath, $data);
echo $this->loadFileContent($actualThemePath . '/header.php', $defaultThemePath . '/header.php', $data) ?? '';
```

### 修改 2: loadViewFile() 方法
- 接收 `$data` 参数
- 在 include 之前执行 `extract($data, EXTR_SKIP)`

```php
private function loadViewFile(string $view, string $themePath, string $fallbackPath, array $data = []): string {
    // ...
    if (is_file($viewFile)) {
        ob_start();
        extract($data, EXTR_SKIP);  // 关键修复
        include $viewFile;
        return ob_get_clean() ?: '';
    }
    // ...
}
```

### 修改 3: loadFileContent() 方法
- 从使用 `file_get_contents()` 改为使用 `include`（确保 PHP 代码执行）
- 接收 `$data` 参数并在 include 之前执行 `extract($data, EXTR_SKIP)`

```php
private function loadFileContent(string $primaryPath, string $fallbackPath, array $data = []): ?string {
    $filePath = is_file($primaryPath) ? $primaryPath : (is_file($fallbackPath) ? $fallbackPath : null);
    
    if ($filePath === null) {
        return null;
    }
    
    ob_start();
    extract($data, EXTR_SKIP);  // 关键修复
    include $filePath;
    return ob_get_clean() ?: null;
}
```

## 验证结果
✅ **所有主要前端页面测试通过**：
- `/` (首页) - ✓ 无错误，$cases 和 $products 正确渲染
- `/products` (产品列表) - ✓ 无错误
- `/blog` (博客) - ✓ 无错误
- `/cases` (案例) - ✓ 无错误
- `/about` (关于) - ✓ 无错误
- `/contact` (联系) - ✓ 无错误
- `/admin/login` (管理登录) - ✓ 无错误

## 受影响的文件
- **修改**: [app/Core/Controller.php](app/Core/Controller.php)
  - 第 25-44 行: render() 方法签名更改
  - 第 91-113 行: loadViewFile() 方法重新实现
  - 第 129-146 行: loadFileContent() 方法重新实现

## 相关文件
- [app/Controllers/BaseController.php](app/Controllers/BaseController.php) - renderSite() 方法
- [app/Controllers/SiteController.php](app/Controllers/SiteController.php) - home() 等视图方法
- [themes/default/home.php](themes/default/home.php) - 前端模板

## 调试过程
1. 发现模板中 `$cases` 为 null/undefined
2. 追踪到 renderSite() 方法中 array_merge 操作
3. 验证 SiteController.home() 确实传递了正确的数据
4. 识别 render() 和 loadViewFile() 之间的作用域分离问题
5. 通过在各个文件加载点进行 extract() 解决问题

## 性能影响
无负面影响。extract() 操作非常高效，且每个请求只执行一次。

## 后续建议
- 如果需要进一步优化，可以考虑使用模板引擎（Twig、Blade）替代 PHPinclude
- 定期进行自动化集成测试，防止此类问题再次发生
